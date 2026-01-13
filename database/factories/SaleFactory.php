<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Shipping;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $ppnStatus = $this->faker->randomElement(['yes', 'no']);
        $subtotal   = $this->faker->numberBetween(500000, 5000000);
        $ppn        = $ppnStatus === 'yes' ? $subtotal * 0.11 : null;
        $grandTotal = $subtotal + ($ppn ?? 0);

        return [
            'order_number'  => 'SO-' . strtoupper(Str::random(6)),
            'order_date'    => $this->faker->dateTimeBetween('-3 months', 'now'),
            'customer_code' => Customer::inRandomOrder()->first()?->customer_code,
            'ppn_status'    => $ppnStatus,
            'subtotal'      => $subtotal,
            'ppn'           => $ppn,
            'grandtotal'    => $grandTotal,
            'top'           => $this->faker->randomElement([null, 7, 14, 30]),
            'ship_1'        => Shipping::inRandomOrder()->first()?->shipping_code,
            'ship_2'        => Shipping::inRandomOrder()->first()?->shipping_code,
            'note'          => $this->faker->sentence(6),
        ];
    }
}
