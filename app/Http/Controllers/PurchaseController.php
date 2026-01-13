<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        // dd($request->all());
        try {
            DB::beginTransaction();

            $request->merge([
                'price' => array_map(fn($p) => $this->parseCurrency($p), $request->price ?? [])
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
                    'qty_unit.*'      => 'required|numeric|min:0.5',
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

                ]);
            }

            // Format nilai numerik
            $subtotal = $this->parseCurrency($request->input('subtotal'));
            $ppn = $this->parseCurrency($request->input('ppn_amount'));
            $grandTotal = $this->parseCurrency($request->input('grand_total'));

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
                    $purchase->ppn = $ppn;
                }
            } else {
                $purchase->ppn_status = $request->input('ppn_option_manual', 'no');
                if ($purchase->ppn_status === 'yes') {
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
                            $price = $this->parseCurrency($request->input('price')[$index] ?? 0);
                            $discount = $request->input('discount')[$index] ?? '0';

                            $total = $this->parseCurrency($request->input('total')[$index] ?? 0);

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

                        // 🔹 Normalisasi qty_unit agar bisa terima desimal (contoh: 2,5)
                        $qtyUnitRaw = $request->input('qty_unit')[$index] ?? 0;
                        $qtyUnit = (float) str_replace(',', '.', $qtyUnitRaw);
                        $purchaseDetail->qty_unit = $qtyUnit;
                        $purchaseDetail->unit = $request->input('unit')[$index] ?? '';

                        // Format price
                        $price = $this->parseCurrency($request->input('price')[$index] ?? 0);
                        $purchaseDetail->price = $price;

                        // Set discount and total
                        $purchaseDetail->discount = $request->input('discount')[$index] ?? '0';
                        $total = $this->parseCurrency($request->input('total')[$index] ?? 0);
                        $purchaseDetail->total = $total;

                        logger()->info('Saving detail', [
                            'price' => $purchaseDetail->price,
                            'total' => $purchaseDetail->total,
                        ]);

                        $purchaseDetail->save();
                    }
                }
            }


            $userId = Auth::id();
            // Hapus draft user untuk form purchase_order
            Draft::where('form_type', 'purchase_order')
                ->where('user_id', $userId) // pastikan hanya draft user ini
                ->delete();


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

    public function delete($order_number)
    {
        $purchase = Purchase::with('purchaseDetail')->findOrFail($order_number);

        // ❌ Jika status bukan Pending
        if ($purchase->status !== 'Pending') {
            return redirect()->back()->with(
                'error',
                'Tidak Bisa Dihapus, PO telah masuk masa penerimaan.'
            );
        }

        DB::transaction(function () use ($purchase) {

            // Ambil semua so_detail yang ada di purchase_details
            $soDetails = $purchase->purchaseDetail
                ->pluck('so_detail')
                ->filter()
                ->unique();

            if ($soDetails->isNotEmpty()) {
                // Update sale_details -> status = Unordered
                SaleDetail::whereIn('id', $soDetails)
                    ->update(['status' => 'Unordered']);
            }

            // Hapus PO (purchase_details ikut terhapus karena cascade)
            $purchase->delete();
        });

        return redirect()->back()->with('success', 'Purchase Order berhasil dihapus.');
    }

    private function parseCurrency($value)
    {
        if ($value === null || $value === '') return '0.00';

        // Keep only digits, dot, comma, minus
        $clean = preg_replace('/[^\d\.\,\-]/u', '', trim($value));

        // If both dot and comma present -> last one is decimal separator
        if (strpos($clean, '.') !== false && strpos($clean, ',') !== false) {
            $lastDot   = strrpos($clean, '.');
            $lastComma = strrpos($clean, ',');
            if ($lastDot > $lastComma) {
                // dot is decimal, remove commas (thousands)
                $clean = str_replace(',', '', $clean);
            } else {
                // comma is decimal -> remove dots and convert comma to dot
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            }
        }
        // Only comma present
        elseif (strpos($clean, ',') !== false) {
            $parts = explode(',', $clean);
            $frac  = end($parts);
            // if fractional part length == 3 -> probably thousand separator (e.g., "100,000")
            if (strlen($frac) === 3) {
                $clean = str_replace(',', '', $clean);
            } else {
                // comma is decimal
                $clean = str_replace(',', '.', $clean);
            }
        }
        // Only dot present
        elseif (strpos($clean, '.') !== false) {
            $parts = explode('.', $clean);
            $frac  = end($parts);
            // if fractional part length == 3 -> probably thousand separator (e.g., "1.000")
            if (strlen($frac) === 3) {
                $clean = str_replace('.', '', $clean);
            } else {
                // dot is decimal -> keep as is
            }
        }

        // Final: return a string formatted with two decimals (safe for DECIMAL(,2))
        return number_format((float) $clean, 2, '.', '');
    }
}
