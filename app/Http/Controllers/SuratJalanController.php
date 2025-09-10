<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Packing;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Shipping;
use App\Models\SaleDetail;
use App\Models\SuratJalan;
use Illuminate\Http\Request;
use App\Models\SuratJalanDetail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; // CURSOR

class SuratJalanController extends Controller
{

    // Index Surat Jalan
    public function index()
    {
        $suratJalans = SuratJalan::with('SJdetails')->latest('ship_date')->paginate(10);

        return view('Pages.SuratJalan.index', compact('suratJalans'));
    }

    // Aseli lupa ini buat fungsi mana
    public function getPendingPurchases()
    {
        $purchases = Purchase::with([
            'supplier',
            'purchaseDetail' => function ($query) {
                $query->where('status', 'Pending')
                    ->with(['saleDetail.product', 'saleDetail.sale.customer']);
            }
        ])
            ->whereHas('purchaseDetail', function ($query) {
                $query->where('status', 'Pending');
            })
            ->get();

        return $purchases->map(function ($purchase) {
            return [
                'po_number' => $purchase->order_number,
                'supplier_name' => $purchase->supplier->supplier_name ?? null,
                'details' => $purchase->purchaseDetail->map(function ($detail) {
                    $saleDetail = $detail->saleDetail;
                    $sale = $saleDetail->sale ?? null;
                    $customer = $sale->customer ?? null;

                    // Hitung total qty yang sudah dikirim
                    $alreadyShipped = $detail->suratJalanDetails()
                        ->selectRaw('SUM(qty_packing) as total_packing, SUM(qty_unit) as total_unit')
                        ->first();

                    $qtyPackingShipped = $alreadyShipped->total_packing ?? 0;
                    $qtyUnitShipped = $alreadyShipped->total_unit ?? 0;

                    return [
                        'so_number' => $sale->order_number ?? null,
                        'customer_name' => $customer->customer_name ?? null,
                        'detail_id' => $saleDetail->id ?? null,
                        'product_name' => $saleDetail->product->product_name ?? 'Unknown',
                        'qty_packing' => $saleDetail->qty_packing - $qtyPackingShipped,
                        'packing' => $saleDetail->packing,
                        'quantity' => $saleDetail->quantity - $qtyUnitShipped,
                        'unit' => $saleDetail->unit,
                    ];
                })
            ];
        });
    }


    public function storeSuratJalan(Request $request)
    {
        
        // ✅ Validasi lebih dulu
        $request->validate([
            'sj_number'     => 'required|string|unique:surat_jalans,sj_number',
            'ship_date'     => 'required|date',
            'ship_1'        => 'nullable|string',
            'ship_2'        => 'nullable|string',
            'note'          => 'nullable|string',
            'top'           => 'nullable|string',

            // Array product_details wajib
            'product_details'   => 'required|array|min:1',
            'product_details.*' => 'required|integer|exists:sale_details,id',

            // Qty packing & unit harus numeric ≥ 0
            'qty_packings'      => 'required|array',
            'qty_packings.*'    => 'required|numeric|min:0',
            'qty_units'         => 'required|array',
            'qty_units.*'       => 'required|numeric|min:1',
        ]);
        
        DB::beginTransaction();
        try {
            // Ambil sale dari sale_detail pertama
            $sale = null;
            if (!empty($request->product_details)) {
                $firstDetail = SaleDetail::find($request->product_details[0]);
                if ($firstDetail) {
                    $sale = $firstDetail->sale; // Relasi sale()
                }
            }

            if (!$sale) {
                throw new \Exception("Sales tidak ditemukan dari sale_detail pertama.");
            }

            // Simpan Surat Jalan
            $sj = SuratJalan::create([
                'sj_number' => $request->sj_number,
                'ship_date' => $request->ship_date,
                'ship_1' => $request->ship_1,
                'ship_2' => $request->ship_2,
                'note' => $request->note,
                'top' => $request->top,
                'customer_code' => $request->customer_code,
                'ppn_status' => $sale->ppn_status,
            ]);

            // Simpan detail dan kelompokkan per product_detail
            $details = [];
            foreach ($request->product_details as $index => $detailId) {
                $qtyPacking = $request->qty_packings[$index];
                $qtyUnit = $request->qty_units[$index];

                if ($qtyPacking > 0 || $qtyUnit > 0) {
                    $saleDetail = SaleDetail::find($detailId);
                    if (!$saleDetail) continue;

                    $details[] = [
                        'sj_number' => $sj->sj_number,
                        'so_number' => $saleDetail->order_number, // perubahan utama di sini
                        'id_product' => $saleDetail->id_product,
                        'qty_packing' => $qtyPacking,
                        'packing' => $saleDetail->packing,
                        'qty_unit' => $qtyUnit,
                        'unit' => $saleDetail->unit,
                    ];

                    // 🔹 Konversi qty_unit ke pieces
                    $qtyInPieces = match(strtolower($saleDetail->unit)) {
                        'lusin' => $qtyUnit * 12,
                        'gross' => $qtyUnit * 144,
                        'set', 'pieces' => $qtyUnit,
                        default => $qtyUnit,
                    };

                    // 🔹 Kurangi stok product
                    $product = Product::find($saleDetail->id_product);
                    if ($product) {
                        if ($sale->ppn_status === 'yes') {
                            $product->qty_ppn -= $qtyInPieces;
                        } else {
                            $product->qty_nonppn -= $qtyInPieces;
                        }
                        $product->save();
                    }
                }
            }

            SuratJalanDetail::insert($details);

            // Update status per sale_detail
            foreach ($request->product_details as $index => $detailId) {
                $saleDetail = SaleDetail::find($detailId);
                if (!$saleDetail) continue;

                $totalPackingOrdered = $saleDetail->qty_packing;
                $totalUnitOrdered = $saleDetail->quantity;

                // Ambil semua surat jalan yg mengandung order_number dan id_product yang sama
                $previousShipments = SuratJalanDetail::where('so_number', $saleDetail->order_number)
                    ->where('id_product', $saleDetail->id_product)
                    ->where('packing', $saleDetail->packing)
                    ->where('unit', $saleDetail->unit)
                    ->get();

                $sumPacking = $previousShipments->sum('qty_packing');
                $sumUnit = $previousShipments->sum('qty_unit');

                if ($sumPacking >= $totalPackingOrdered && $sumUnit >= $totalUnitOrdered) {
                    $saleDetail->status = 'Terproses';
                } else {
                    $saleDetail->status = 'Sebagian Terproses';
                }

                $saleDetail->save();
            }

            DB::commit();

            return redirect()->route('pengirimans.index')->with('success', 'Surat Jalan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }


    // Surat Jalan Manual
    public function createManual()
    {
        $shippings = Shipping::all();
        $packings = Packing::pluck('packing_name')->toArray();
        $units = Unit::pluck('unit_name')->toArray();
        $customers = Customer::all();
        return view('Pages.SuratJalan.create_manual', compact('shippings', 'packings', 'units', 'customers'));
    }


    public function storeManual(Request $request)
    {
        // 1. VALIDASI lebih dulu
        $request->validate([
            'sj_number'     => 'required|string|unique:surat_jalans,sj_number',
            'ship_date'     => 'required|date',
            'customer_code' => 'required|string|exists:customers,customer_code',
            'ppn_status'    => 'required|in:yes,no',
            'manual'        => 'required|array|min:1',
            'manual.*.id_product'   => 'required|integer|exists:products,id',
            'manual.*.packing'      => 'required|string',
            'manual.*.qty_packing'  => 'required|numeric|min:1',
            'manual.*.unit'         => 'required|string',
            'manual.*.qty_unit'     => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Simpan Surat Jalan
            $sj = SuratJalan::create([
                'sj_number' => $request->sj_number,
                'ship_date' => $request->ship_date,
                'ship_1' => $request->ship_1 ?: null,
                'ship_2' => $request->ship_2 ?: null,
                'note' => $request->note,
                'top' => $request->top,
                'customer_code' => $request->customer_code,
                'status' => 'Pending',
                'ppn_status'  => $request->ppn_status,
            ]);

            // Simpan detail produk dari $request->manual
            $details = [];
            foreach ($request->manual as $item) {
                $details[] = [
                    'sj_number' => $sj->sj_number,
                    'so_number' => null,
                    'id_product' => $item['id_product'],
                    'packing' => $item['packing'],
                    'qty_packing' => $item['qty_packing'] ?? 0,
                    'unit' => $item['unit'],
                    'qty_unit' => $item['qty_unit'] ?? 0,
                    'status' => 'Pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // 🔹 Konversi qty_unit ke pieces
                $qtyInPieces = match(strtolower($item['unit'])) {
                    'lusin' => ($item['qty_unit'] ?? 0) * 12,
                    'gross' => ($item['qty_unit'] ?? 0) * 144,
                    'set', 'pieces' => $item['qty_unit'] ?? 0,
                    default => $item['qty_unit'] ?? 0,
                };

                // 🔹 Update stok produk (kurangi)
                $product = Product::find($item['id_product']);
                if ($product) {
                    if ($request->ppn_status === 'yes') {
                        $product->qty_ppn -= $qtyInPieces;
                    } else {
                        $product->qty_nonppn -= $qtyInPieces;
                    }
                    $product->save();
                }
            }

            SuratJalanDetail::insert($details);

            DB::commit();
            return redirect()->route('pengirimans.index')->with('success', 'Surat Jalan Manual berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}
