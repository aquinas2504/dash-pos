<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentDetail;

class InventoryAdjustmentController extends Controller
{
    public function create()
    {
        $products = Product::orderBy('product_name')->get();

        return view('Pages.Product.Adjustment', compact('products'));
    }

    private function convertToPieces($qty, $unit)
    {
        return match ($unit) {
            'Pieces' => $qty,
            'Lusin' => $qty * 12,
            'Gross' => $qty * 144,
            default => 0,
        };
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $adjustment = InventoryAdjustment::create([
                'date' => $request->date,
                'type' => $request->type,
                'reason' => $request->reason,
                'total_value' => str_replace('.', '', $request->total_value),
            ]);

            foreach ($request->product_id as $key => $productId) {

                $product = Product::findOrFail($productId);

                $qty = $request->qty[$key];
                $unit = $request->unit[$key];
                $ppn = $request->ppn[$key];
                
                $price = str_replace('.', '', $request->price[$key]);
                $price = (float) $price;

                $convertedQty = $this->convertToPieces($qty, $unit);

                InventoryAdjustmentDetail::create([
                    'adj_id' => $adjustment->id,
                    'product_id' => $productId,
                    'ppn' => $ppn,
                    'qty' => $qty,
                    'unit' => $unit,
                    'price' => $price,
                ]);

                $value = $qty * $price;

                /*
                |--------------------------------------------------------------------------
                | PLUS
                |--------------------------------------------------------------------------
                */

                if ($request->type == 'Plus') {

                    if ($ppn == 'yes') {
                        $product->qty_ppn += $convertedQty;
                    } else {
                        $product->qty_nonppn += $convertedQty;
                    }

                    $product->total_purchase_qty += $convertedQty;
                    $product->total_purchase_amount += $value;
                }

                /*
                |--------------------------------------------------------------------------
                | MINUS
                |--------------------------------------------------------------------------
                */ else {

                    if ($ppn == 'yes') {

                        if ($product->qty_ppn < $convertedQty) {
                            return back()->with('error', 'Stock PPN tidak mencukupi');
                        }

                        $product->qty_ppn -= $convertedQty;
                    } else {

                        if ($product->qty_nonppn < $convertedQty) {
                            return back()->with('error', 'Stock Non PPN tidak mencukupi');
                        }

                        $product->qty_nonppn -= $convertedQty;
                    }

                    $product->total_sold_qty += $convertedQty;
                    $product->total_sold_amount += $value;
                }

                $product->save();
            }

            DB::commit();

            return redirect()->back()->with('success', 'Inventory Adjustment berhasil dibuat');
        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }
}
