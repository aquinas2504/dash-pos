<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ReturSale;
use App\Models\Penerimaan;
use App\Models\SuratJalan;
use App\Models\SaleInvoice;
use Illuminate\Http\Request;
use App\Models\InvoiceDetail;
use App\Models\PenerimaanDetail;
use App\Models\SuratJalanDetail;
use App\Models\SaleInvoiceDetail;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // Create Form Invoice Pembelian
    public function create($penerimaan_number)
    {
        $penerimaan = Penerimaan::with([
            'supplier',
            'details.product'
        ])->where('penerimaan_number', $penerimaan_number)->firstOrFail();

        // Cek apakah ini mode manual
        $isManual = $penerimaan->details->every(function ($detail) {
            return is_null($detail->po_number); // pakai po_number, bukan po_detail
        });

        return view('Pages.Invoice.create_penerimaan', compact('penerimaan', 'isManual'));
    }

    // store Form Invoice Pembelian
    public function store(Request $request)
    {
        // dd($request->all());

        $request->merge([
            'price' => array_map(fn($p) => (int) preg_replace('/\D/', '', $p), $request->price ?? [])
        ]);

        $request->validate([
            'invoice_number' => 'required|unique:invoices,invoice_number',
            'date' => 'required|date',
            'price' => 'required|array',
            'price.*' => 'required|integer|min:1',
            'discount' => 'nullable|array',
            'discount.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // izinkan kosong / kosong string
                    if ($value === null || trim($value) === '') {
                        return;
                    }

                    if (!preg_match('/^\d+([.,]\d+)?(\+\d+([.,]\d+)?)*$/', $value)) {
                        $fail("$attribute harus berupa angka (boleh desimal) atau kombinasi angka dipisahkan '+', contoh: 10,5+2.5");
                        return;
                    }


                    // cek setiap angka 0..100
                    foreach (explode('+', $value) as $rate) {
                        $r = (float) str_replace(',', '.', $rate);
                        if ($r < 0 || $r > 100) {
                            $fail("$attribute harus antara 0 sampai 100% setiap segmen.");
                            return;
                        }
                    }
                },
            ],

        ]);

        try {
            DB::beginTransaction();

            // Ambil penerimaan_number dari form (jika hidden atau implicit)
            $penerimaan = Penerimaan::where('penerimaan_number', $request->penerimaan_number)->firstOrFail();
            $ppnStatus = $penerimaan->ppn_status;

            // Perhitungan total
            $subtotal = 0;
            $detailsData = [];

            foreach ($penerimaan->details as $i => $detail) {
                $price = $request->price[$i] ?? 0;
                $discountStr = $request->discount[$i] ?? '';
                $qty = $detail->qty_unit ?? 0;

                $discountTotalPerUnit = 0;
                $netPrice = $price;

                // Hitung diskon bertingkat
                foreach (explode('+', $discountStr) as $d) {
                    $rate = (float) str_replace(',', '.', $d);
                    if ($rate > 0) {
                        $netPrice -= $netPrice * $rate / 100;
                    }
                }

                $discountTotalPerUnit = $price - $netPrice;
                $total = round($qty * $netPrice);

                $subtotal += $total;

                $detailsData[] = [
                    'penerimaan_detail' => $detail->id,
                    'price' => $price,
                    'discount' => $discountStr,
                    'total' => $total,
                ];
            }

            $dpp = $subtotal;
            $ppn = 0;
            $grandtotal = $subtotal;

            if ($ppnStatus === 'yes') {
                $dpp = round($subtotal / 1.11);
                $ppn = $subtotal - $dpp;
            }

            // Simpan ke tabel invoices
            Invoice::create([
                'invoice_number' => $request->invoice_number,
                'date' => $request->date,
                'penerimaan_number' => $penerimaan->penerimaan_number,
                'dpp' => $ppnStatus === 'yes' ? $dpp : null,
                'ppn' => $ppnStatus === 'yes' ? $ppn : null,
                'grandtotal' => $grandtotal,
            ]);

            $penerimaan->status = 'Difaktur';
            $penerimaan->save();


            // Simpan ke tabel invoice_details
            foreach ($detailsData as $data) {
                $data['invoice_number'] = $request->invoice_number;
                InvoiceDetail::create($data);

                // Ambil Penerimaan Detail
                $penerimaanDetail = PenerimaanDetail::find($data['penerimaan_detail']);
                if ($penerimaanDetail && $penerimaanDetail->product) {
                    $product = $penerimaanDetail->product;

                    $convertedQty = $product->convertToPieces($penerimaanDetail->qty_unit ?? 0, $penerimaanDetail->unit ?? '');

                    $product->increment('total_purchase_qty', $convertedQty);
                    $product->increment('total_purchase_amount', $data['total']);
                }
            }

            DB::commit();

            return redirect()->route('purchaseInvoice.index')->with('success', 'Invoice berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan invoice: ' . $e->getMessage());
        }
    }

    // Index Invoice Pembelian
    public function indexPurchaseInvoice()
    {
        $invoices = Invoice::with('penerimaan.supplier')
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('Pages.Invoice.index_pembelian', compact('invoices'));
    }

    // Edit Invoice Pembelian
    public function editPurchaseInvoice($invoice_number)
    {
        $invoice = Invoice::with([
            'details.penerimaanDetail.product',
            'penerimaan.supplier',
        ])->where('invoice_number', $invoice_number)->firstOrFail();

        return view('Pages.Invoice.edit_pembelian', compact('invoice'));
    }

    // Update Invoice Pembelian
    public function updatePurchaseInvoice(Request $request, $invoice_number)
    {
        $request->merge([
            'price' => array_map(fn($p) => (int) preg_replace('/\D/', '', $p), $request->price ?? [])
        ]);

        $request->validate([
            'date' => 'required|date',
            'price' => 'required|array',
            'price.*' => 'required|integer|min:1',
            'discount' => 'nullable|array',
            'discount.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // izinkan kosong / kosong string
                    if ($value === null || trim($value) === '') {
                        return;
                    }

                    // format dasar: angka atau angka+angka+...
                    if (!preg_match('/^\d+([.,]\d+)?(\+\d+([.,]\d+)?)*$/', $value)) {
                        $fail("$attribute harus berupa angka (boleh desimal) atau kombinasi angka dipisahkan '+', contoh: 10,5+2.5");
                        return;
                    }

                    // cek setiap angka 0..100
                    foreach (explode('+', $value) as $rate) {
                        $r = (float) str_replace(',', '.', $rate);
                        if ($r < 0 || $r > 100) {
                            $fail("$attribute harus antara 0 sampai 100% setiap segmen.");
                            return;
                        }
                    }
                },
            ],

        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::with('details.penerimaanDetail.product')->where('invoice_number', $invoice_number)->firstOrFail();
            $ppnStatus = $invoice->penerimaan->ppn_status;

            // Rollback data lama dari product
            foreach ($invoice->details as $detail) {
                $penerimaanDetail = $detail->penerimaanDetail;
                $product = $penerimaanDetail->product;

                $convertedQty = $product->convertToPieces($penerimaanDetail->qty_unit, $penerimaanDetail->unit);
                $product->decrement('total_purchase_qty', $convertedQty);
                $product->decrement('total_purchase_amount', $detail->total);
            }

            // Hapus semua detail lama
            InvoiceDetail::where('invoice_number', $invoice_number)->delete();

            // Perhitungan ulang
            $subtotal = 0;
            $newDetails = [];

            foreach ($invoice->penerimaan->details as $i => $penerimaanDetail) {
                $price = $request->price[$i] ?? 0;
                $discountStr = $request->discount[$i] ?? '';
                $qty = $penerimaanDetail->qty_unit ?? 0;

                $netPrice = $price;
                foreach (explode('+', $discountStr) as $d) {
                    $rate = (float) str_replace(',', '.', $d);
                    if ($rate > 0) {
                        $netPrice -= $netPrice * $rate / 100;
                    }
                }

                $total = round($qty * $netPrice);
                $subtotal += $total;

                $newDetails[] = [
                    'invoice_number' => $invoice_number,
                    'penerimaan_detail' => $penerimaanDetail->id,
                    'price' => $price,
                    'discount' => $discountStr,
                    'total' => $total,
                ];
            }

            $dpp = $subtotal;
            $ppn = 0;
            if ($ppnStatus === 'yes') {
                $dpp = round($subtotal / 1.11);
                $ppn = $subtotal - $dpp;
            }

            $invoice->update([
                'date' => $request->date,
                'dpp' => $ppnStatus === 'yes' ? $dpp : null,
                'ppn' => $ppnStatus === 'yes' ? $ppn : null,
                'grandtotal' => $subtotal,
            ]);

            foreach ($newDetails as $data) {
                InvoiceDetail::create($data);

                $penerimaanDetail = PenerimaanDetail::find($data['penerimaan_detail']);
                $product = $penerimaanDetail->product;
                $convertedQty = $product->convertToPieces($penerimaanDetail->qty_unit, $penerimaanDetail->unit);
                $product->increment('total_purchase_qty', $convertedQty);
                $product->increment('total_purchase_amount', $data['total']);
            }

            DB::commit();

            return redirect()->route('purchaseInvoice.index', $invoice_number)->with('success', 'Invoice berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }




    // --------------------PENJUALAN-------------------- //

    // Invoice SJ
    public function createSJ($sj_number)
    {
        $SuratJalan = SuratJalan::with([
            'customer',
            'SJdetails.product'
        ])->where('sj_number', $sj_number)->firstOrFail();

        $payments = Payment::all();

        $customerCode = $SuratJalan->customer_code;

        $returSales = ReturSale::where('status', 'Active')
            ->whereHas('invoice.suratjalan', function ($q) use ($customerCode) {
                $q->where('customer_code', $customerCode);
            })
            ->get();


        return view('Pages.Invoice.create_suratjalan', compact('SuratJalan', 'payments', 'returSales'));
    }

    // Store invoice SJ
    public function storeSJ(Request $request)
    {
        $request->merge([
            'price' => array_map(fn($p) => (int) preg_replace('/\D/', '', $p), $request->price ?? [])
        ]);

        // dd($request->all());
        $request->validate([
            'invoice_number' => 'required|unique:sale_invoices,invoice_number',
            'date' => 'required|date',
            'price' => 'required|array',
            'price.*' => 'required|integer|min:1',
            'discount' => 'nullable|array',
            'discount.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // izinkan kosong / kosong string
                    if ($value === null || trim($value) === '') {
                        return;
                    }

                    // format dasar: angka atau angka+angka+...
                    if (!preg_match('/^\d+([.,]\d+)?(\+\d+([.,]\d+)?)*$/', $value)) {
                        $fail("$attribute harus berupa angka (boleh desimal) atau kombinasi angka dipisahkan '+', contoh: 10,5+2.5");
                        return;
                    }

                    // cek setiap angka 0..100
                    foreach (explode('+', $value) as $rate) {
                        $r = (float) str_replace(',', '.', $rate);
                        if ($r < 0 || $r > 100) {
                            $fail("$attribute harus antara 0 sampai 100% setiap segmen.");
                            return;
                        }
                    }
                },
            ],
            'payment_id' => 'nullable|exists:payments,id',
        ]);

        try {
            DB::beginTransaction();

            // Ambil penerimaan_number dari form (jika hidden atau implicit)
            $suratjalan = SuratJalan::where('sj_number', $request->sj_number)->firstOrFail();
            $ppnStatus = $suratjalan->ppn_status;

            // Perhitungan total
            $subtotal = 0;
            $detailsData = [];

            foreach ($suratjalan->SJdetails as $i => $detail) {
                $price = (int) preg_replace('/\D/', '', $request->price[$i] ?? 0);
                $discountStr = (string) ($request->discount[$i] ?? '');
                $qty = $detail->qty_unit ?? 0;

                $discountTotalPerUnit = 0;
                $netPrice = $price;

                // Hitung diskon bertingkat
                foreach (explode('+', $discountStr) as $d) {
                    $rate = (float) str_replace(',', '.', $d);
                    if ($rate > 0) {
                        $netPrice -= $netPrice * $rate / 100;
                    }
                }

                $discountTotalPerUnit = $price - $netPrice;
                $total = round($qty * $netPrice);

                $subtotal += $total;

                $detailsData[] = [
                    'surat_jalan_detail' => $detail->id,
                    'price' => $price,
                    'discount' => $discountStr,
                    'total' => $total,
                ];
            }

            $dpp = $subtotal;
            $ppn = 0;
            $returUsed = (int) $request->retur_deduction ?? 0;
            $grandtotal = max(0, $subtotal - $returUsed);

            if ($ppnStatus === 'yes') {
                $dpp = round($subtotal / 1.11);
                $ppn = $subtotal - $dpp;
            }

            // Simpan ke tabel invoices
            SaleInvoice::create([
                'invoice_number' => $request->invoice_number,
                'date' => $request->date,
                'sj_number' => $suratjalan->sj_number,
                'dpp' => $ppnStatus === 'yes' ? $dpp : null,
                'ppn' => $ppnStatus === 'yes' ? $ppn : null,
                'grandtotal' => $grandtotal,
                'payment_id' => $request->payment_id,
                'retur_used' => $returUsed,
            ]);

            $suratjalan->status = 'Difaktur';
            $suratjalan->save();

            // Untuk Nyimpen data di db Retur Sales
            if ($request->has('selected_returs')) {
                $selectedReturs = $request->selected_returs;

                ReturSale::whereIn('retur_number', $selectedReturs)->update([
                    'status' => 'Used',
                    'used_for' => $request->invoice_number,
                ]);
            }


            // Simpan ke tabel invoice_details
            foreach ($detailsData as $data) {
                $data['invoice_number'] = $request->invoice_number;
                SaleInvoiceDetail::create($data);

                // Ambil SJ Detail
                $sjDetail = SuratJalanDetail::find($data['surat_jalan_detail']);
                if ($sjDetail && $sjDetail->product) {
                    $product = $sjDetail->product;

                    $convertedQty = $product->convertToPieces($sjDetail->qty_unit ?? 0, $sjDetail->unit ?? '');

                    $product->increment('total_sold_qty', $convertedQty);
                    $product->increment('total_sold_amount', $data['total']);
                }
            }

            DB::commit();

            return redirect()->route('saleInvoice.index')->with('success', 'Sale Invoice berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan invoice: ' . $e->getMessage());
        }
    }

    // Index Invoice Penjualan
    public function indexSaleInvoice()
    {
        $invoices = SaleInvoice::with('suratJalan.customer')
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('Pages.Invoice.index_penjualan', compact('invoices'));
    }

    // Edit Sale Invoice
    public function editSaleInvoice($invoice_number)
    {
        $invoice = SaleInvoice::with([
            'details.SJDetail.product',
            'suratjalan.customer',
        ])->where('invoice_number', $invoice_number)->firstOrFail();

        $customerCode = $invoice->suratJalan->customer_code;

        $returSales = ReturSale::where('status', 'Active')
            ->whereHas('invoice.suratjalan', function ($q) use ($customerCode) {
                $q->where('customer_code', $customerCode);
            })
            ->get();


        $payments = Payment::all();

        return view('Pages.Invoice.edit_penjualan', compact('invoice', 'payments', 'returSales'));
    }

    // Update Sale Invoice
    public function updateSaleInvoice(Request $request, $invoice_number)
    {
        $request->merge([
            'price' => array_map(fn($p) => (int) preg_replace('/\D/', '', $p), $request->price ?? [])
        ]);

        $request->validate([
            'date' => 'required|date',
            'price' => 'required|array',
            'price.*' => 'required|integer|min:1',
            'discount' => 'nullable|array',
            'discount.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // izinkan kosong / kosong string
                    if ($value === null || trim($value) === '') {
                        return;
                    }

                    // format dasar: angka atau angka+angka+...
                    if (!preg_match('/^\d+([.,]\d+)?(\+\d+([.,]\d+)?)*$/', $value)) {
                        $fail("$attribute harus berupa angka (boleh desimal) atau kombinasi angka dipisahkan '+', contoh: 10,5+2.5");
                        return;
                    }

                    // cek setiap angka 0..100
                    foreach (explode('+', $value) as $rate) {
                        $r = (float) str_replace(',', '.', $rate);
                        if ($r < 0 || $r > 100) {
                            $fail("$attribute harus antara 0 sampai 100% setiap segmen.");
                            return;
                        }
                    }
                },
            ],
            'payment_id' => 'nullable|exists:payments,id',
        ]);

        try {
            DB::beginTransaction();

            $invoice = SaleInvoice::with('details.SJDetail.product')->where('invoice_number', $invoice_number)->firstOrFail();
            $ppnStatus = $invoice->suratJalan->ppn_status;

            // Rollback data lama dari product
            foreach ($invoice->details as $detail) {
                $sjDetail = $detail->SJDetail;
                $product = $sjDetail->product;

                $convertedQty = $product->convertToPieces($sjDetail->qty_unit, $sjDetail->unit);
                $product->decrement('total_sold_qty', $convertedQty);
                $product->decrement('total_sold_amount', $detail->total);
            }

            // Hapus semua detail lama
            SaleInvoiceDetail::where('invoice_number', $invoice_number)->delete();

            // Perhitungan ulang
            $subtotal = 0;
            $newDetails = [];

            foreach ($invoice->suratJalan->SJdetails as $i => $sjDetail) {
                $price = (int) preg_replace('/\D/', '', $request->price[$i] ?? 0);
                $discountStr = $request->discount[$i] ?? '';
                $qty = $sjDetail->qty_unit ?? 0;

                $netPrice = $price;
                foreach (explode('+', $discountStr) as $d) {
                    $rate = (float) str_replace(',', '.', $d);
                    if ($rate > 0) {
                        $netPrice -= $netPrice * $rate / 100;
                    }
                }

                $total = round($qty * $netPrice);
                $subtotal += $total;

                $newDetails[] = [
                    'invoice_number' => $invoice_number,
                    'surat_jalan_detail' => $sjDetail->id,
                    'price' => $price,
                    'discount' => $discountStr,
                    'total' => $total,
                ];
            }

            $dpp = $subtotal;
            $ppn = 0;
            $returUsed = (int) $request->retur_deduction ?? 0;
            $grandtotal = max(0, $subtotal - $returUsed);

            if ($ppnStatus === 'yes') {
                $dpp = round($subtotal / 1.11);
                $ppn = $subtotal - $dpp;
            }

            $invoice->update([
                'date' => $request->date,
                'dpp' => $ppnStatus === 'yes' ? $dpp : null,
                'ppn' => $ppnStatus === 'yes' ? $ppn : null,
                'grandtotal' => $grandtotal,
                'payment_id' => $request->payment_id,
                'retur_used' => $returUsed,
            ]);

            // Untuk Nyimpen data di db Retur Sales
            if ($request->has('selected_returs')) {
                $selectedReturs = $request->selected_returs;

                ReturSale::whereIn('retur_number', $selectedReturs)->update([
                    'status' => 'Used',
                    'used_for' => $request->invoice_number,
                ]);
            }

            foreach ($newDetails as $data) {
                SaleInvoiceDetail::create($data);

                $sjDetail = SuratJalanDetail::find($data['surat_jalan_detail']);
                $product = $sjDetail->product;
                $convertedQty = $product->convertToPieces($sjDetail->qty_unit, $sjDetail->unit);
                $product->increment('total_sold_qty', $convertedQty);
                $product->increment('total_sold_amount', $data['total']);
            }

            DB::commit();

            return redirect()->route('saleInvoice.index', $invoice_number)->with('success', 'Invoice berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }
}
