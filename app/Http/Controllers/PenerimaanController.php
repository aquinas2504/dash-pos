<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Packing;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Penerimaan;
use Illuminate\Http\Request;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\PenerimaanDetail;    

class PenerimaanController extends Controller
{

    public function index()
    {
        $penerimaans = Penerimaan::with(['supplier', 'details'])
            ->orderByDesc('date')
            ->paginate(10);


        return view('Pages.Penerimaan.index', compact('penerimaans'));
    }

    public function createFromPO($order_number)
    {
        $purchase = Purchase::with(['supplier', 'purchaseDetail.product'])->where('order_number', $order_number)->firstOrFail();

        // 🔹 Step 1: Gabungkan purchase_details berdasarkan id_product + packing + unit
        $groupedPurchase = [];

        foreach ($purchase->purchaseDetail as $detail) {
            $productId = $detail->id_product ?? optional($detail->saleDetail)->id_product;
            $productName = optional($detail->product)->product_name ?? optional($detail->saleDetail->product)->product_name ?? '-';
            $packing = $detail->packing;
            $unit = $detail->unit;

            $key = $productId . '_' . $packing . '_' . $unit;

            if (!isset($groupedPurchase[$key])) {
                $groupedPurchase[$key] = [
                    'id_product' => $productId,
                    'product_name' => $productName,
                    'packing' => $packing,
                    'unit' => $unit,
                    'qty_packing' => 0,
                    'qty_unit' => 0,
                    'po_details' => [],
                ];
            }

            $groupedPurchase[$key]['qty_packing'] += $detail->qty_packing ?? 0;
            $groupedPurchase[$key]['qty_unit'] += $detail->qty_unit ?? 0;
            $groupedPurchase[$key]['po_details'][] = $detail->id;
        }

        // 🔹 Step 2: Gabungkan penerimaan berdasarkan id_product + packing + unit
        $receivedDetails = PenerimaanDetail::where('po_number', $order_number)->get();

        $groupedReceived = [];

        foreach ($receivedDetails as $r) {
            $key = $r->id_product . '_' . $r->packing . '_' . $r->unit;
            if (!isset($groupedReceived[$key])) {
                $groupedReceived[$key] = [
                    'qty_packing' => 0,
                    'qty_unit' => 0,
                ];
            }

            $groupedReceived[$key]['qty_packing'] += $r->qty_packing ?? 0;
            $groupedReceived[$key]['qty_unit'] += $r->qty_unit ?? 0;
        }

        // 🔹 Step 3: Hitung sisa dengan mengurangi total penerimaan
        $groupedFinal = [];

        foreach ($groupedPurchase as $key => $item) {
            $received = $groupedReceived[$key] ?? ['qty_packing' => 0, 'qty_unit' => 0];

            $remainingPacking = max(0, $item['qty_packing'] - $received['qty_packing']);
            $remainingUnit = max(0, $item['qty_unit'] - $received['qty_unit']);

            if ($remainingPacking <= 0 && $remainingUnit <= 0) {
                continue; // Sudah diterima semua
            }

            // Tambahkan total ordered dan total received ke array item
            $item['ordered_packing'] = $item['qty_packing'];
            $item['ordered_unit'] = $item['qty_unit'];
            $item['received_packing'] = $received['qty_packing'];
            $item['received_unit'] = $received['qty_unit'];

            $item['qty_packing'] = $remainingPacking;
            $item['qty_unit'] = $remainingUnit;

            $groupedFinal[] = $item;
        }


        return view('Pages.Penerimaan.create_by_po', [
            'purchase' => $purchase,
            'groupedDetails' => $groupedFinal,
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validasi dasar
            $request->validate([
                'penerimaan_number' => 'required|unique:penerimaans,penerimaan_number',
                'date' => 'required|date',
                'supplier_code' => 'required|exists:suppliers,supplier_code',
                'details' => 'required|array|min:1',
                'details.*.qty_packing' => 'required|integer|min:1',
                'details.*.qty_unit' => 'required|integer|min:1',
            ]);

            $order_number = null;

            // Ambil salah satu detail untuk referensi PO
            foreach ($request->details as $detail) {
                if (!empty($detail['po_details'])) {
                    $firstPoDetail = $detail['po_details'][0];
                    $poDetail = PurchaseDetail::find($firstPoDetail);
                    $order_number = $poDetail?->order_number;
                    break;
                }
            }

            // Ambil status PPN dari Purchase
            $ppn_status = null;
            if ($order_number) {
                $purchase = Purchase::where('order_number', $order_number)->first();
                $ppn_status = $purchase?->ppn_status;
            }

            // Simpan data penerimaan utama
            $penerimaan = Penerimaan::create([
                'penerimaan_number' => $request->penerimaan_number,
                'date' => $request->date,
                'supplier_code' => $request->supplier_code,
                'ppn_status' => $ppn_status,
                'note' => $request->note,
                'status' => 'Pending',
            ]);

            // Simpan detail-detail penerimaan
            foreach ($request->details as $detail) {
                $poDetailIds = $detail['po_details'] ?? [];
                $id_product = $detail['id_product'] ?? null;
                $qty_packing = $detail['qty_packing'] ?? 0;
                $packing = $detail['packing'] ?? null;
                $qty_unit = $detail['qty_unit'] ?? 0;
                $unit = $detail['unit'] ?? null;

                $poDetail = PurchaseDetail::find($poDetailIds[0] ?? null);

                PenerimaanDetail::create([
                    'penerimaan_number' => $penerimaan->penerimaan_number,
                    'po_number' => $poDetail?->order_number,
                    'id_product' => $id_product,
                    'qty_packing' => $qty_packing,
                    'packing' => $packing,
                    'qty_unit' => $qty_unit,
                    'unit' => $unit,
                    'status' => 'Pending',
                ]);

                // 🔹 Konversi qty_unit ke pieces
                $qtyInPieces = match(strtolower($unit)) {
                    'lusin' => $qty_unit * 12,
                    'gross' => $qty_unit * 144,
                    'set', 'pieces' => $qty_unit,
                    default => $qty_unit,
                };

                // 🔹 Update stok product
                if ($id_product) {
                    $product = Product::find($id_product);
                    if ($product) {
                        if ($ppn_status === 'yes') {
                            $product->qty_ppn += $qtyInPieces;
                        } else {
                            $product->qty_nonppn += $qtyInPieces;
                        }
                        $product->save();
                    }
                }

            }

            // Update status PO
            if ($order_number) {
                $purchaseDetails = PurchaseDetail::with('saleDetail')->where('order_number', $order_number)->get();

                $groupedPurchase = [];
                foreach ($purchaseDetails as $detail) {
                    $id_product = $detail->id_product ?? $detail->saleDetail?->id_product;
                    $key = ($id_product ?? '-') . '_' . ($detail->packing ?? '-') . '_' . ($detail->unit ?? '-');

                    if (!isset($groupedPurchase[$key])) {
                        $groupedPurchase[$key] = ['qty_packing' => 0, 'qty_unit' => 0];
                    }

                    $groupedPurchase[$key]['qty_packing'] += $detail->qty_packing ?? 0;
                    $groupedPurchase[$key]['qty_unit'] += $detail->qty_unit ?? 0;
                }

                $receivedDetails = PenerimaanDetail::where('po_number', $order_number)->get();

                $groupedReceived = [];
                foreach ($receivedDetails as $detail) {
                    $key = ($detail->id_product ?? '-') . '_' . ($detail->packing ?? '-') . '_' . ($detail->unit ?? '-');

                    if (!isset($groupedReceived[$key])) {
                        $groupedReceived[$key] = ['qty_packing' => 0, 'qty_unit' => 0];
                    }

                    $groupedReceived[$key]['qty_packing'] += $detail->qty_packing ?? 0;
                    $groupedReceived[$key]['qty_unit'] += $detail->qty_unit ?? 0;
                }

                $hasReceived = count($groupedReceived) > 0;
                $isComplete = true;

                foreach ($groupedPurchase as $key => $expectedQty) {
                    $receivedQty = $groupedReceived[$key] ?? ['qty_packing' => 0, 'qty_unit' => 0];

                    if (
                        ($receivedQty['qty_packing'] < $expectedQty['qty_packing']) ||
                        ($receivedQty['qty_unit'] < $expectedQty['qty_unit'])
                    ) {
                        $isComplete = false;
                        break;
                    }
                }

                $status = 'Pending';
                if ($hasReceived) {
                    $status = $isComplete ? 'Diterima Semua' : 'Diterima Sebagian';
                }

                $updated = Purchase::where('order_number', $order_number)->update(['status' => $status]);
            }

            DB::commit();
            return redirect()->route('penerimaans.index')
                ->with('success', 'Penerimaan barang berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function createManual()
    {
        $packings = Packing::pluck('packing_name')->toArray();
        $units = Unit::pluck('unit_name')->toArray();
        $suppliers = Supplier::all();

        return view('Pages.Penerimaan.create_manual', compact('packings', 'units', 'suppliers'));
    }

    public function storeManual(Request $request)
    {
        DB::beginTransaction();

        // 🔒 Validasi input
        $request->validate([
            'penerimaan_number' => 'required|string|unique:penerimaans,penerimaan_number',
            'date' => 'required|date',
            'supplier_code' => 'required|exists:suppliers,supplier_code',
            'ppn_status' => 'required|in:yes,no',
            'note' => 'nullable|string',

            'manual' => 'required|array|min:1',
            'manual.*.id_product' => 'required|exists:products,id',
            'manual.*.qty_packing' => 'required|integer|min:1',
            'manual.*.packing' => 'required|string',
            'manual.*.qty_unit' => 'required|integer|min:1',
            'manual.*.unit' => 'required|string',
        ]);
        

        try {

            // 1. Simpan ke tabel `penerimaans`
            $penerimaan = Penerimaan::create([
                'penerimaan_number' => $request->penerimaan_number,
                'date' => $request->date,
                'supplier_code' => $request->supplier_code,
                'note' => $request->note,
                'ppn_status' => $request->ppn_status,
            ]);

            // 2. Simpan ke tabel `penerimaan_details`
            foreach ($request->manual as $detail) {
                // Simpan detail penerimaan
                PenerimaanDetail::create([
                    'penerimaan_number' => $penerimaan->penerimaan_number,
                    'id_product' => $detail['id_product'],
                    'qty_packing' => $detail['qty_packing'],
                    'packing' => $detail['packing'],
                    'qty_unit' => $detail['qty_unit'],
                    'unit' => $detail['unit'],
                ]);

                // 🔹 Konversi qty_unit ke pieces
                $qtyInPieces = match(strtolower($detail['unit'])) {
                    'lusin' => $detail['qty_unit'] * 12,
                    'gross' => $detail['qty_unit'] * 144,
                    'set', 'pieces', 'pcs' => $detail['qty_unit'],
                    default => $detail['qty_unit'],
                };

                // 🔹 Update stok product
                $product = Product::find($detail['id_product']);
                if ($product) {
                    if ($request->ppn_status === 'yes') {
                        $product->qty_ppn += $qtyInPieces;
                    } else {
                        $product->qty_nonppn += $qtyInPieces;
                    }
                    $product->save();
                }
            }

            DB::commit();
            return redirect()->route('penerimaans.index')->with('success', 'Penerimaan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
