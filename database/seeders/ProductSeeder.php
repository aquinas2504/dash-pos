<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use App\Models\ProductValue;
use App\Models\ProductPacking;
use App\Models\Packing;
use App\Models\Unit;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. Load JSON ---
        $productsJson = json_decode(File::get(database_path('seeders/data/products.json')), true);
        $packingsJson = json_decode(File::get(database_path('seeders/data/product_packings.json')), true);

        // --- 2. Seed Products + ProductValues ---
        foreach ($productsJson as $p) {
            Product::create([
                'product_code' => $p['product_code'] ?? null,
                'product_name' => $p['product_name'],
            ]);
        }

        // --- 3. Seed ProductPackings ---
        foreach ($packingsJson as $pp) {
            $productId = $pp['product_id'];

            // Cari ID packing & unit dari master table
            $packing = Packing::where('packing_name', $pp['packing_name'])->first();
            $unit    = Unit::where('unit_name', $pp['unit_name'])->first();

            if (!$packing || !$unit) {
                continue; // skip kalau ada mismatch
            }

            ProductPacking::create([
                'product_id'       => $productId,
                'packing_id'       => $packing->id,
                'unit_id'          => $unit->id,
                'conversion_value' => $pp['conversion_value'],
            ]);
        }
    }
}
