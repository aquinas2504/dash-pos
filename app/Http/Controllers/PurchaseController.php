<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;


class PurchaseController extends Controller
{
    public function create()
    {
        $suppliers = Supplier::all();

        return view('Pages.Purchase.create', compact('suppliers'));
    }

    public function index(Request $request)
    {
        $purchases = Purchase::with('purchaseDetail', 'supplier')
            ->whereIn('status', ['Pending', 'Diterima Sebagian'])
            ->orderBy('order_date', 'desc') // biar terbaru dulu
            ->paginate(10);

        return view('Pages.Purchase.index', compact('purchases'));
    }


    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->merge([
                'price' => array_map(function ($p) {
                    return (int) str_replace(['Rp. ', '.'], '', $p);
                }, $request->price ?? [])
            ]);


            // Validasi dasar
            $request->validate([
                'order_number' => 'required|unique:purchases,order_number',
                'order_date'   => 'required|date',
                'supplier_code' => 'required|exists:suppliers,supplier_code',
                'po_mode'      => 'required|in:so,manual',
            ]);

            // Tentukan mode (Berdasarkan SO atau Manual)
            $mode = $request->input('po_mode');

            // ========== Validasi Detail Produk ==========
            if ($mode === 'so') {
                $request->validate([
                    'id_sale_detail'   => 'required|array|min:1',
                    'id_sale_detail.*' => 'required|string', // karena bisa berupa "1,2,3"
                    'price'            => 'required|array|min:1',
                    'price.*'          => 'required|numeric|min:1',
                    'discount' => 'nullable|array',
                    'discount.*' => [
                        'nullable',
                        function ($attribute, $value, $fail) {
                            if ($value === null || $value === '') {
                                return; // kalau kosong, skip
                            }

                            // cek format: angka+angka+...
                            if (!preg_match('/^\d{1,3}(\+\d{1,3})*$/', $value)) {
                                $fail("$attribute harus berupa angka atau kombinasi angka dipisahkan dengan '+'");
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

                ]);
            } else {
                $request->validate([
                    'id_product'      => 'required|array|min:1',
                    'id_product.*'    => 'required|exists:products,id',
                    'qty_packing'     => 'required|array|min:1',
                    'qty_packing.*'   => 'required|numeric|min:1',
                    'packing'         => 'required|array|min:1',
                    'packing.*'       => 'required|string',
                    'qty_unit'        => 'required|array|min:1',
                    'qty_unit.*'      => 'required|numeric|min:1',
                    'unit'            => 'required|array|min:1',
                    'unit.*'          => 'required|string',
                    'price'           => 'required|array|min:1',
                    'price.*'         => 'required|numeric|min:1',
                    'discount' => 'nullable|array',
                    'discount.*' => [
                        'nullable',
                        function ($attribute, $value, $fail) {
                            if ($value === null || $value === '') {
                                return; // kalau kosong, skip
                            }

                            // cek format: angka+angka+...
                            if (!preg_match('/^\d{1,3}(\+\d{1,3})*$/', $value)) {
                                $fail("$attribute harus berupa angka atau kombinasi angka dipisahkan dengan '+'");
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

                ]);
            }

            // Format nilai numerik
            $subtotal = str_replace(['Rp. ', '.'], '', $request->input('subtotal'));
            $dpp = str_replace(['Rp. ', '.'], '', $request->input('dpp'));
            $ppn = str_replace(['Rp. ', '.'], '', $request->input('ppn_amount'));
            $grandTotal = str_replace(['Rp. ', '.'], '', $request->input('grand_total'));

            // Buat record Purchase
            $purchase = new Purchase();
            $purchase->order_number = $request->input('order_number');
            $purchase->order_date = $request->input('order_date');
            $purchase->supplier_code = $request->input('supplier_code');
            $purchase->subtotal = $subtotal;
            $purchase->grandtotal = $grandTotal;
            $purchase->note = $request->input('note');

            // Set PPN status berdasarkan mode
            if ($mode === 'so') {
                $purchase->ppn_status = $request->input('ppn_status_so', 'no'); // Default 'no' jika tidak ada
                if ($purchase->ppn_status === 'yes') {
                    $purchase->dpp = $dpp;
                    $purchase->ppn = $ppn;
                }
            } else {
                $purchase->ppn_status = $request->input('ppn_option_manual', 'no');
                if ($purchase->ppn_status === 'yes') {
                    $purchase->dpp = $dpp;
                    $purchase->ppn = $ppn;
                }
            }

            $purchase->save();

            // Process Purchase Details
            if ($mode === 'so') {
                // Mode Berdasarkan SO
                if ($request->has('id_sale_detail')) {
                    foreach ($request->input('id_sale_detail') as $index => $saleDetailIds) {
                        // Handle multiple sale detail IDs (comma-separated)
                        $detailIds = explode(',', $saleDetailIds);

                        foreach ($detailIds as $detailId) {
                            if (empty($detailId)) continue;

                            $saleDetail = SaleDetail::find($detailId);
                            if (!$saleDetail) continue;

                            $qty_unit = $saleDetail->quantity;
                            $price = str_replace(['Rp. ', '.'], '', $request->input('price')[$index] ?? 0);
                            $discount = $request->input('discount')[$index] ?? '0';

                            // Hitung total dengan diskon bertahap
                            $final_price = $this->applyDiscounts($price, $discount);
                            $total = $qty_unit * $final_price;

                            $purchaseDetail = new PurchaseDetail();
                            $purchaseDetail->order_number = $purchase->order_number;
                            $purchaseDetail->so_detail = $detailId;
                            $purchaseDetail->qty_packing = $saleDetail->qty_packing;
                            $purchaseDetail->packing = $saleDetail->packing;
                            $purchaseDetail->qty_unit = $qty_unit;
                            $purchaseDetail->unit = $saleDetail->unit;
                            $purchaseDetail->price = $price;
                            $purchaseDetail->discount = $discount;
                            $purchaseDetail->total = $total;
                            $purchaseDetail->save();

                            $saleDetail->status = 'Ordered';
                            $saleDetail->save();
                        }
                    }
                }
            } else {
                // Mode Manual
                if ($request->has('id_product')) {
                    foreach ($request->input('id_product') as $index => $productId) {
                        $purchaseDetail = new PurchaseDetail();
                        $purchaseDetail->order_number = $purchase->order_number;
                        $purchaseDetail->id_product = $productId;
                        $purchaseDetail->qty_packing = $request->input('qty_packing')[$index] ?? 0;
                        $purchaseDetail->packing = $request->input('packing')[$index] ?? '';
                        $purchaseDetail->qty_unit = $request->input('qty_unit')[$index] ?? 0;
                        $purchaseDetail->unit = $request->input('unit')[$index] ?? '';

                        // Format price
                        $price = str_replace(['Rp. ', '.'], '', $request->input('price')[$index] ?? 0);
                        $purchaseDetail->price = $price;

                        // Set discount and total
                        $purchaseDetail->discount = $request->input('discount')[$index] ?? '0';
                        $total = str_replace(['Rp. ', '.'], '', $request->input('total')[$index] ?? 0);
                        $purchaseDetail->total = $total;

                        $purchaseDetail->save();
                    }
                }
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase order berhasil dibuat!');
        } catch (ValidationException $e) {
            // biarkan Laravel handle validasi error → detail muncul di SweetAlert
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    private function applyDiscounts($price, $discountString)
    {
        $discounts = explode('+', $discountString);
        foreach ($discounts as $d) {
            $price -= ($price * ((float) $d / 100));
        }
        return round($price);
    }
}
