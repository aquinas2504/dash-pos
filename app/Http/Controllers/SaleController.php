<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Unit;
use App\Models\Packing;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use App\Models\SuratJalanDetail;
use App\Http\Controllers\Controller;

class SaleController extends Controller
{
    public function create()
    {
        $shippings = Shipping::all();

        return view('Pages.Sale.create', compact('shippings'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        // Validasi data
        $request->validate([
            'order_number' => 'required|string|unique:sales,order_number',
            'date' => 'required|date',
            'customer_code' => 'required|string|exists:customers,customer_code',
            'ppn' => 'required|in:yes,no',
            'note' => 'nullable|string',
            'qty' => 'required|array',
            'qty.*' => 'required|numeric|min:0.5',
            'unit' => 'required|array',
            'unit_price' => 'required|array',
            'unit_price.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $numeric = (int) preg_replace('/\D/', '', $value); // hilangkan Rp, titik, koma
                    if ($numeric < 1) {
                        $fail("$attribute minimal 1.");
                    }
                },
            ],
            'discount' => 'nullable|array',
            'discount.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '') {
                        return; // kalau kosong, skip
                    }

                    // cek format: angka+angka+...
                    if (!preg_match('/^\d{1,3}([.,]\d+)?(\+\d{1,3}([.,]\d+)?)*$/', $value)) {
                        $fail("$attribute harus berupa angka atau kombinasi angka dipisahkan dengan '+' (boleh pakai desimal dengan titik atau koma).");
                        return;
                    }


                    // cek semua nilai diskon antara 0–100
                    foreach (explode('+', $value) as $rate) {
                        if ((int) $rate < 0 || (int) $rate > 100) {
                            $fail("$attribute harus antara 0 sampai 100%");
                        }
                    }
                },
            ],
            'qty_packing' => 'required|array',
            'qty_packing.*' => 'required|integer|min:1',
            'packing' => 'required|array',
            'ship_1' => 'nullable|exists:shippings,shipping_code',
            'ship_2' => 'nullable|exists:shippings,shipping_code',
            'top' => 'nullable|numeric|min:0',
        ]);

        // Ambil nilai numerik dari subtotal, ppn, dan grand_total (hilangkan 'Rp.' dan titik)
        $subtotal = (int) str_replace(['Rp. ', '.', ','], '', $request->input('subtotal'));
        $ppn = $request->input('ppn') === 'yes' ? (int) str_replace(['Rp. ', '.', ','], '', $request->input('ppn_amount')) : null;
        $grandTotal = (int) str_replace(['Rp. ', '.', ','], '', $request->input('grand_total'));

        // Simpan ke tabel sales
        $sale = Sale::create([
            'order_number' => $request->order_number,
            'order_date' => $request->date,
            'customer_code' => $request->customer_code,
            'ppn_status' => $request->ppn,
            'subtotal' => $subtotal,
            'ppn' => $ppn,
            'grandtotal' => $grandTotal,
            'note' => $request->note,
            'top' => $request->top,
            'ship_1' => $request->ship_1,
            'ship_2' => $request->ship_2,
        ]);

        // Loop untuk menyimpan data ke sale_details
        foreach ($request->qty as $index => $qty) {
            $qty = (float) str_replace(',', '.', $qty); // ubah 2,5 jadi 2.5

            $unitId = $request->unit[$index];
            $packingId = $request->packing[$index];
            $qtyPacking = $request->qty_packing[$index];
            $unit_price_raw = str_replace(['Rp. ', '.', ','], '', $request->unit_price[$index]);
            $discount = $request->discount[$index];

            $price = (int) $unit_price_raw;
            $total = $qty * $price;

            if (!empty($discount)) {
                $discountParts = explode('+', $discount);
                foreach ($discountParts as $d) {
                    $percentage = floatval(str_replace(',', '.', $d));
                    $total -= $total * ($percentage / 100);
                }
            }

            // Ambil nama dari ID
            $unitName = Unit::find($unitId)?->unit_name ?? '';
            $packingName = Packing::find($packingId)?->packing_name ?? '';

            SaleDetail::create([
                'order_number' => $sale->order_number,
                'id_product' => $request->input('id_product')[$index],
                'qty_packing' => $qtyPacking,
                'packing' => $packingName, // Simpan nama
                'quantity' => $qty,
                'unit' => $unitName, // Simpan nama
                'price' => $price,
                'discount' => $discount,
                'total' => round($total),
            ]);
        }



        return redirect()->route('sales.ordered')->with('success', 'Sale order created successfully.');
    }

    // PO Berdasarkan SO
    public function getPendingSales(Request $request)
    {
        $ppn = $request->query('ppn'); // yes, no, or null
        $salesQuery = Sale::with(['customer', 'saleDetail' => function ($q) {
            $q->where('status', 'Unordered')->with('product');
        }])
            ->where('status', 'Pending')
            ->whereHas('saleDetail', function ($query) {
                $query->where('status', 'Unordered');
            });
        if (in_array($ppn, ['yes', 'no'])) {
            $salesQuery->where('ppn_status', $ppn);
        }

        $sales = $salesQuery->get();

        return $sales->map(function ($sale) {
            return [
                'order_number' => $sale->order_number,
                'order_date' => $sale->order_date,
                'customer_name' => $sale->customer->customer_name ?? null,
                'details' => $sale->saleDetail->map(function ($d) {
                    return [
                        'detail_id' => $d->id,
                        'product_id' => $d->id_product,
                        'product_name' => $d->product->product_name ?? 'Unknown',
                        'product_code' => $d->product->product_code ?? 'Unknown',
                        'unit' => $d->unit,
                        'quantity' => (float) $d->quantity, 
                        'qty_packing' => $d->qty_packing,
                        'packing' => $d->packing,
                    ];
                })
            ];
        });
    }

    // PO Manual
    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $keywords = explode(' ', $keyword);

        $query = Product::query();
        foreach ($keywords as $k) {
            $query->where(function($q) use ($k) {
                $q->where('product_name', 'like', "%$k%");
            });
        }

        return $query->limit(10)->get();
    }

    // PO
    public function getPackings(Product $product)
    {
        return response()->json([
            'all_packings' => Packing::all(['id as packing_id', 'packing_name']),
            'all_units' => Unit::all(['id as unit_id', 'unit_name']),
            'product_packings' => $product->productPackings()
                ->with(['packing:id,packing_name', 'unit:id,unit_name']) // tambah eager load
                ->get()
                ->map(function ($pp) {
                    return [
                        'packing_id' => $pp->packing_id,
                        'unit_id' => $pp->unit_id,
                        'packing_name' => $pp->packing->packing_name,
                        'unit_name' => $pp->unit->unit_name,
                        'conversion_value' => $pp->conversion_value
                    ];
                })
        ]);
    }


    // Index yang berisi seluruh data SO
    public function orderedSales()
    {
        $details = SaleDetail::whereIn('status', ['Unordered', 'Ordered', 'Sebagian Terproses', 'Terproses'])
            ->with([
                'sale.customer',
                'product',
                'purchaseDetail.purchase.supplier'
            ])
            ->get();

        $grouped = $details->groupBy('order_number');

        $orderedSales = [];

        foreach ($grouped as $orderNumber => $group) {
            $first = $group->first();

            // --- Status Pesanan ---
            $total = $group->count();
            $unorderedCount = $group->where('status', 'Unordered')->count();
            $orderedCount = $group->whereIn('status', ['Ordered', 'Sebagian Terproses', 'Terproses'])->count();

            if ($unorderedCount === $total) {
                $statusPesanan = 'Menunggu Pesanan';
            } elseif ($unorderedCount === 0) {
                $statusPesanan = 'Sudah Dipesan Semua';
            } else {
                $statusPesanan = 'Sebagian Dipesan<br><small style="color: #888;">(' . $orderedCount . '/' . $total . ' Dipesan)</small>';
            }


            // --- Status Pengiriman ---
            $terprosesCount = $group->where('status', 'Terproses')->count();
            $sebagianTerprosesCount = $group->where('status', 'Sebagian Terproses')->count();

            if ($terprosesCount === $total) {
                $statusPengiriman = 'Terproses';
            } elseif ($terprosesCount === 0 && $sebagianTerprosesCount === 0) {
                $statusPengiriman = 'Menunggu Pengiriman';
            } else {
                $statusPengiriman = 'Sebagian Terproses';
            }

            $orderedSales[] = [
                'order_number' => $orderNumber,
                'order_date' => $first->sale->order_date,
                'customer_name' => $first->sale->customer->customer_name ?? '-',
                'status_pesanan' => $statusPesanan,
                'status_pengiriman' => $statusPengiriman,
            ];
        }

        // ubah ke collection biar bisa paginate
        $orderedSales = collect($orderedSales);

        // urutkan dari order_date terbaru
        $orderedSales = $orderedSales->sortByDesc('order_date')->values();

        // paginate manual (10 per halaman)
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $pagedData = $orderedSales->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $orderedSalesPagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $orderedSales->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('Pages.Sale.ordered', compact('orderedSalesPagination'));
    }

    // Untuk melihat detail suatu SO yang mengarah pada form pembuatan surat jalan
    public function showSaleDetails($order_number)
    {
        $sale = Sale::with('customer')->where('order_number', $order_number)->firstOrFail();

        $details = SaleDetail::with('product')
            ->where('order_number', $order_number)
            ->whereIn('status', ['Ordered', 'Sebagian Terproses'])
            ->get()
            ->map(function ($detail) {
                // Cari semua surat jalan yang cocok (secara manual)
                $shippedDetails = SuratJalanDetail::where('so_number', $detail->order_number)
                    ->where('id_product', $detail->id_product)
                    ->where('packing', $detail->packing)
                    ->where('unit', $detail->unit)
                    ->get();

                $shippedPacking = $shippedDetails->sum('qty_packing');
                $shippedUnit = $shippedDetails->sum('qty_unit');

                $detail->remaining_packing = max(0, $detail->qty_packing - $shippedPacking);
                $detail->remaining_unit = max(0, $detail->quantity - $shippedUnit);

                return $detail;
            });


        // Tidak perlu groupedBySupplier
        $shippings = Shipping::all();

        // Ambil data shipping dan term of payment
        $selectedShip1 = $sale->ship_1;
        $selectedShip2 = $sale->ship_2;
        $termOfPayment = $sale->top;

        return view('Pages.Sale.details', compact(
            'sale',
            'details',
            'shippings',
            'selectedShip1',
            'selectedShip2',
            'termOfPayment'
        ));
    }
}
