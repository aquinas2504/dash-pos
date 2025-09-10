<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Database\Seeder;

class SOSeeder extends Seeder
{

    public function run(): void
    {
        Sale::factory()
            ->count(200) // bikin 10 SO
            ->has(SaleDetail::factory()->count(3), 'saleDetail') // tiap SO ada 3 produk
            ->create();
    }
}
