<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class POSeeder extends Seeder
{
    public function run(): void
    {
        // ambil sale yang masih punya detail belum diorder
        $sales = Sale::whereHas('saleDetail', function ($q) {
            $q->where('status', 'Unordered');
        })->get();

        foreach ($sales as $sale) {

            // ambil detail SO yang belum diorder
            $saleDetails = $sale->saleDetail
                ->where('status', 'Unordered');

            if ($saleDetails->isEmpty()) {
                continue;
            }

            DB::transaction(function () use ($sale, $saleDetails) {

                // 1️⃣ buat Purchase
                $purchase = Purchase::factory()->create([
                    'ppn_status' => $sale->ppn_status,
                ]);

                $subtotal = 0;

                // 2️⃣ buat PurchaseDetail dari SaleDetail
                foreach ($saleDetails as $detail) {

                    $price    = fake()->numberBetween(10000, 200000);
                    $discount = fake()->randomElement([null, '5', '10', '10+5']);
                    $total    = $detail->quantity * $price;

                    if ($discount) {
                        foreach (explode('+', $discount) as $d) {
                            $total -= $total * ((float)$d / 100);
                        }
                    }

                    PurchaseDetail::create([
                        'order_number' => $purchase->order_number,
                        'so_detail'    => $detail->id,
                        'id_product'   => $detail->id_product,
                        'qty_packing'  => $detail->qty_packing,
                        'packing'      => $detail->packing,
                        'qty_unit'     => $detail->quantity,
                        'unit'         => $detail->unit,
                        'price'        => $price,
                        'discount'     => $discount,
                        'total'        => round($total),
                    ]);

                    $subtotal += $total;

                    // 3️⃣ update sale_detail
                    $detail->update([
                        'status' => 'Ordered'
                    ]);
                }

                // 4️⃣ hitung PPN & Grandtotal
                $ppn = $purchase->ppn_status === 'yes'
                    ? $subtotal * 0.11
                    : null;

                $purchase->update([
                    'subtotal'   => round($subtotal),
                    'ppn'        => $ppn,
                    'grandtotal' => round($subtotal + ($ppn ?? 0)),
                ]);
            });
        }
    }
}

