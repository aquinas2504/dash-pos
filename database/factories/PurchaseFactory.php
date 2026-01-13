<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'order_number'  => 'PO-' . strtoupper(Str::random(6)),
            'order_date'    => $this->faker->dateTimeBetween('-3 months', 'now'),
            'supplier_code' => Supplier::inRandomOrder()->first()?->supplier_code,
            'ppn_status'    => null, // diisi di Seeder
            'subtotal'      => 0,
            'ppn'           => null,
            'grandtotal'    => 0,
            'note'          => $this->faker->sentence(6),
            'status'        => 'Pending',
        ];
    }
}

