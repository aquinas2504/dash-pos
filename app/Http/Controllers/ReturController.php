<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use Barryvdh\DomPDF\PDF;
use App\Models\ReturSale;
use App\Models\SaleInvoice;
use Illuminate\Http\Request;
use App\Models\ReturPurchase;
use App\Models\ReturSaleDetail;
use App\Models\PenerimaanDetail;
use App\Models\ListReturPurchase;
use Illuminate\Support\Facades\DB;
use App\Models\ReturPurchaseDetail;
use App\Models\HistoryReturPurchase;

class ReturController extends Controller
{
    public function createSaleRetur(Request $request)
    {
        return view('Pages.Retur.create_retur_penjualan');
    }

    public function storeSaleRetur(Request $request)
    {
        $request->validate([
            'retur_number' => 'required|unique:retur_sales,retur_number',
            'date' => 'required|date',
            'invoice_number' => 'required|exists:sale_invoices,invoice_number',
            'details' => 'required|array|min:1',
            'details.*.id_product' => 'required|exists:products,id',
            'details.*.qty' => 'required|integer|min:1',
            'details.*.unit' => 'required|string|max:20',
            'details.*.value' => 'required|numeric|min:0',
            'details.*.note' => 'nullable|string|max:255',
        ]);

        $total = 0;
        foreach ($request->details as $detail) {
            $total += $detail['value'];
        }

        $retur = ReturSale::create([
            'retur_number' => $request->retur_number,
            'date' => $request->date,
            'invoice_number' => $request->invoice_number,
            'total' => $total,
        ]);

        foreach ($request->details as $detail) {
            ReturSaleDetail::create([
                'retur_number' => $retur->retur_number,
                'id_product' => $detail['id_product'],
                'qty' => $detail['qty'],
                'unit' => $detail['unit'],
                'value' => $detail['value'],
                'note' => $detail['note'] ?? null,
            ]);
        }

        return redirect()->route('retur-sales.create')->with('success', 'Retur berhasil disimpan.');
    }

    // Untuk Search Invoice nya
    public function searchSaleRetur(Request $request)
    {
        $query = $request->query('query');

        if (strlen($query) < 2) {
            return response()->json([]); // Jangan kembalikan apapun
        }

        return SaleInvoice::where('invoice_number', 'like', '%' . $query . '%')
            ->limit(10)
            ->get();
    }

    // Untuk dapet detail dari invoice yang telah di search
    public function getDetailSaleRetur($invoiceNumber)
    {
        $invoice = SaleInvoice::with('details.SJDetail.product')
            ->where('invoice_number', $invoiceNumber)
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice tidak ditemukan.'], 404);
        }

        $data = $invoice->details->map(function ($detail) {
            return [
                'product_name' => $detail->SJDetail->product->product_name ?? '-',
                'id_product' => $detail->SJDetail->id_product ?? null,
                'qty_unit' => $detail->SJDetail->qty_unit ?? 0,
                'unit' => $detail->SJDetail->unit ?? '-',
                'price' => $detail->price ?? 0,
                'discount' => $detail->discount ?? null,
            ];
        });

        return response()->json($data);
    }

    // Untuk load history retur
    public function getReturHistory($invoice)
    {
        $returs = ReturSale::with('details.product')
            ->where('invoice_number', $invoice)
            ->orderByDesc('date')
            ->get();

        return response()->json($returs);
    }

    // untuk cari invoice number berdasarkan customer dan product
    public function customer(Request $request)
    {
        $query = $request->get('q');
        return Customer::where('customer_name', 'like', "%$query%")
            ->limit(10)
            ->get(['customer_name as name']);
    }

    public function product(Request $request)
    {
        $query = $request->get('q');
        return Product::where('product_name', 'like', "%$query%")
            ->limit(10)
            ->get(['product_name as name']);
    }

    public function invoiceByProductAndCustomer(Request $request)
    {
        $customer = $request->get('customer');
        $product = $request->get('product');

        $invoices = SaleInvoice::with(['details.SJDetail.product', 'suratJalan.customer'])
            ->whereHas('details.SJDetail.product', function ($q) use ($product) {
                $q->where('product_name', 'like', "%$product%");
            })
            ->whereHas('suratJalan.customer', function ($q) use ($customer) {
                $q->where('customer_name', 'like', "%$customer%");
            })
            ->get();

        return $invoices->map(function ($inv) {
            return [
                'invoice_number' => $inv->invoice_number,
                'date' => $inv->date,
                'customer_name' => $inv->suratJalan->customer->customer_name ?? '-'
            ];
        });
    }



    //----------------------------------------------------------------------------------//
    //----------------- Retur Pembelian Logic -----------------//

    // Search Invoice by product name
    public function searchInvoice(Request $request)
    {
        $keyword = $request->get('keyword');

        $results = DB::table('invoice_details as id')
            ->join('invoices as i', 'i.invoice_number', '=', 'id.invoice_number')
            ->join('penerimaan_details as pd', 'pd.id', '=', 'id.penerimaan_detail')
            ->join('penerimaans as p', 'p.penerimaan_number', '=', 'i.penerimaan_number')
            ->join('suppliers as s', 's.supplier_code', '=', 'p.supplier_code')
            ->join('products as pr', 'pr.id', '=', 'pd.id_product')
            ->select(
                'id.id as invoice_detail_id',
                'i.invoice_number',
                'i.date',
                'pd.id as penerimaan_detail_id', // <<< ADDED (optional)
                'pd.qty_unit',
                'pd.unit',
                'id.price',
                'id.discount',
                'id.qty_retur',
                'pr.product_name',
                'pr.id as product_id',
                's.supplier_name',
                's.supplier_code'
            )
            ->where('pr.product_name', 'like', "%$keyword%")
            ->where('id.status_retur', 'On')
            ->get();

        return response()->json($results);
    }


    // Store Save List
    public function store(Request $request)
    {
        // logger($request->all()); // masuk ke storage/logs/laravel.log
        // return response()->json(['debug' => $request->all()]);

        $request->validate([
            'data' => 'required|array|min:1',
            'data.*.id_product' => 'required|integer',
            'data.*.supplier_code' => 'required|string',
            'data.*.qty' => 'required|integer|min:1',
            'data.*.unit' => 'required|string',
            'data.*.price' => 'required|numeric',
            'data.*.discount' => 'nullable|string',
            'data.*.invoice_detail_id' => 'required|integer|exists:invoice_details,id',
        ]);

        // Helper function: konversi ke Pieces
        $convertToPieces = function ($qty, $unit) {
            $factor = [
                'Pieces' => 1,
                'Set' => 1,
                'Lusin' => 12,
                'Gross' => 144
            ];
            return $qty * ($factor[$unit] ?? 1);
        };

        // Helper untuk convert price
        $convertPriceToBase = function ($price, $unit) {
            $factor = [
                'Pieces' => 1,
                'Set'    => 1,
                'Lusin'  => 12,
                'Gross'  => 144,
            ];

            return $price / ($factor[$unit] ?? 1);
        };


        // Helper function: konversi dari Pieces ke unit dengan preferensi user
        $convertFromPieces = function ($qtyPieces, $preferredUnit = 'Pieces') {
            $factor = [
                'Gross' => 144,
                'Lusin' => 12,
                'Set' => 1,
                'Pieces' => 1
            ];

            // 1. Kalau pas dibagi preferensi user → pakai itu
            if ($qtyPieces % $factor[$preferredUnit] === 0) {
                return [
                    'qty' => $qtyPieces / $factor[$preferredUnit],
                    'unit' => $preferredUnit
                ];
            }

            // 2. Kalau tidak pas → coba cari unit yg lebih besar (Gross → Lusin → Set)
            foreach (['Gross', 'Lusin', 'Set'] as $unit) {
                if ($qtyPieces % $factor[$unit] === 0) {
                    return [
                        'qty' => $qtyPieces / $factor[$unit],
                        'unit' => $unit
                    ];
                }
            }

            // 3. Kalau tetap nggak bisa → fallback ke Pieces
            return [
                'qty' => $qtyPieces,
                'unit' => 'Pieces'
            ];
        };

        $errors = [];

        foreach ($request->data as $row) {
            $newQtyInPieces = $convertToPieces($row['qty'], $row['unit']);
            $newPricePerPiece = $convertPriceToBase($row['price'], $row['unit']);

            // === (A) Validasi dulu ===
            $inv = DB::table('invoice_details as id')
                ->join('invoices as i', 'i.invoice_number', '=', 'id.invoice_number')
                ->join('penerimaan_details as pd', 'pd.id', '=', 'id.penerimaan_detail')
                ->join('products as p', 'p.id', '=', 'pd.id_product') // pastikan ini sesuai
                ->where('id.id', $row['invoice_detail_id'])
                ->select('id.id', 'id.qty_retur', 'id.status_retur', 'id.invoice_number', 'pd.qty_unit', 'pd.unit', 'p.product_name')
                ->first();

            if ($inv) {
                $qtyPenerimaanInPieces = $convertToPieces($inv->qty_unit, $inv->unit);
                $prev = (int) ($inv->qty_retur ?? 0);
                $newTotal = $prev + $newQtyInPieces;

                if ($newTotal > $qtyPenerimaanInPieces) {
                    $errors[] = [
                        'product' => $inv->product_name,
                        'message' => "Qty retur melebihi qty penerimaan"
                    ];
                    continue;
                }

                // update invoice_details
                $updateData = ['qty_retur' => $newTotal];
                if ($newTotal == $qtyPenerimaanInPieces) {
                    $updateData['status_retur'] = 'Off';
                }
                DB::table('invoice_details')->where('id', $inv->id)->update($updateData);
            }

            // === (B) Baru simpan/update ListReturPurchase ===
            $existing = ListReturPurchase::where('id_product', $row['id_product'])
                ->where('supplier_code', $row['supplier_code'])
                ->first();

            if ($existing) {
                $existingQtyInPieces = $convertToPieces($existing->qty, $existing->unit);
                $totalQtyInPieces = $existingQtyInPieces + $newQtyInPieces;

                $converted = $convertFromPieces($totalQtyInPieces, $row['unit']);

                // === ambil harga lama (sudah disimpan per Piece/Set) ===
                $oldPricePerPiece = $existing->price;

                // === tentukan harga tertinggi ===
                $finalPricePerPiece = max($oldPricePerPiece, $newPricePerPiece);

                $existing->update([
                    'qty' => $converted['qty'],
                    'unit' => $converted['unit'],
                    'price' => $finalPricePerPiece,
                    'discount' => $row['discount'] ?? $existing->discount,
                ]);
            } else {
                ListReturPurchase::create([
                    'supplier_code' => $row['supplier_code'],
                    'id_product' => $row['id_product'],
                    'qty' => $row['qty'],
                    'unit' => $row['unit'],
                    'price' => $newPricePerPiece,
                    'discount' => $row['discount'] ?? 0,
                ]);
            }

            // === (C) Simpan ke HistoryReturPurchase ===
            HistoryReturPurchase::create([
                'date'          => now()->toDateString(),
                'invoice_number' => $inv->invoice_number ?? null,
                'id_product'    => $row['id_product'],
                'supplier_code' => $row['supplier_code'],
                'qty_retur'     => $row['qty'],
                'unit'          => $row['unit'],
            ]);
        }


        if (!empty($errors)) {
            return response()->json([
                'status' => 'error',
                'errors' => $errors
            ], 422);
        }


        return response()->json(['status' => 'success', 'message' => 'Data retur berhasil disimpan!']);
    }


    // halaman index retur pembelian 
    public function index(Request $request)
    {
        $query = ListReturPurchase::with('product', 'supplier');

        // filter supplier jika ada search
        if ($request->has('supplier_code') && $request->supplier_code != '') {
            $query->where('supplier_code', $request->supplier_code);
        }

        $returs = $query->get();
        $suppliers = Supplier::all();

        return view('Pages.Retur.create_retur_pembelian', compact('returs', 'suppliers'));
    }

    public function indexHistoryReturPurchase()
    {
        // Ambil history dengan relasi product + supplier
        $histories = HistoryReturPurchase::with(['product', 'supplier'])
            ->orderBy('date', 'desc')
            ->paginate(20); // pagination biar rapi

        return view('Pages.Retur.history_retur_purchase', compact('histories'));
    }

    public function storePurchaseRetur(Request $request)
    {
        $request->validate([
            'retur_number' => 'required|string|unique:retur_purchases,retur_number',
            'date' => 'required|date',
            'supplier_code' => 'required|string',
            'data' => 'required|array|min:1',
            'data.*.id_product' => 'required|integer',
            'data.*.qty' => 'required|integer|min:1',
            'data.*.unit' => 'required|string',
            'data.*.price' => 'required|numeric',
            'data.*.discount' => 'nullable|string',
        ]);

        // ✅ Ambil supplier dari semua baris
        $supplierCodes = collect($request->data)->pluck('supplier_code')->unique();

        if ($supplierCodes->count() > 1) {
            return back()->withErrors(['data' => 'Semua data retur harus berasal dari 1 supplier saja.']);
        }

        // ✅ Pastikan supplier header sama dengan supplier detail
        if ($supplierCodes->first() !== $request->supplier_code) {
            return back()->withErrors(['supplier_code' => 'Supplier retur tidak sesuai dengan detail barang.']);
        }

        // Simpan ke retur_purchases (header)
        ReturPurchase::create([
            'retur_number' => $request->retur_number,
            'date' => $request->date,
            'supplier_code' => $request->supplier_code,
            'note' => $request->note ?? null,
        ]);

        // Simpan ke retur_purchase_details (detail)
        foreach ($request->data as $item) {
            $factor = [
                'Pieces' => 1,
                'Set'    => 1,
                'Lusin'  => 12,
                'Gross'  => 144,
            ];
            $displayPrice = $item['price']; // dari hidden input sudah dikali faktor

            ReturPurchaseDetail::create([
                'retur_number' => $request->retur_number,
                'id_product'   => $item['id_product'],
                'qty'          => $item['qty'],
                'unit'         => $item['unit'],
                'price'        => $displayPrice,
                'discount'     => $item['discount'] ?? 0,
                'value'        => $item['value'],
            ]);

            // 🔥 Hapus data dari list_retur_purchases
            ListReturPurchase::where('id_product', $item['id_product'])
                ->where('supplier_code', $item['supplier_code'])
                ->delete();
        }

        return redirect()->route('retur-purchases.index')->with('success', 'Data retur berhasil disimpan.');
    }

    public function indexPurchaseRetur()
    {
        $returs = ReturPurchase::with('supplier')->orderBy('date', 'desc')->get();
        return view('Pages.Retur.index_retur_purchase', compact('returs'));
    }
}
