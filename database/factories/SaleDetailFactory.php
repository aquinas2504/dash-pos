<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Packing;
use App\Models\SaleDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleDetailFactory extends Factory
{
    protected $model = SaleDetail::class;

    public function definition(): array
    {
        $product     = Product::inRandomOrder()->first();
        $unit        = Unit::inRandomOrder()->first();
        $packing     = Packing::inRandomOrder()->first();

        $qty         = $this->faker->numberBetween(1, 20);
        $qtyPacking  = $this->faker->numberBetween(1, 5);
        $price       = $this->faker->numberBetween(10000, 200000);
        $discount    = $this->faker->randomElement([null, '5', '10', '10+5']); // support diskon bertingkat
        $total       = $qty * $price;

        if ($discount) {
            $discountParts = explode('+', $discount);
            foreach ($discountParts as $d) {
                $percentage = floatval($d);
                $total -= $total * ($percentage / 100);
            }
        }

        return [
            'order_number' => Sale::inRandomOrder()->first()?->order_number, // nanti override kalau pakai has()
            'id_product'   => $product?->id,
            'product_name' => $product?->product_name,
            'qty_packing'  => $qtyPacking,
            'packing'      => $packing?->packing_name,
            'quantity'     => $qty,
            'unit'         => $unit?->unit_name,
            'price'        => $price,
            'discount'     => $discount,
            'total'        => round($total),
        ];
    }
}
