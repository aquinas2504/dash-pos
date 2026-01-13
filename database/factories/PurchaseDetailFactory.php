<?php

namespace Database\Factories;
    
use App\Models\PurchaseDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseDetailFactory extends Factory
{
    protected $model = PurchaseDetail::class;

    public function definition(): array
    {
        $price    = $this->faker->numberBetween(10000, 200000);
        $discount = $this->faker->randomElement([null, '5', '10', '10+5']);

        return [
            'price'    => $price,
            'discount' => $discount,
            'total'    => $price, // nanti di override
            'status'   => 'Pending',
        ];
    }
}


