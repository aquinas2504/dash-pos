<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ConvertExcelToJson extends Command
{
    protected $signature = 'convert:excel {file}';
    protected $description = 'Convert Excel ke JSON (products, product_packings, product_values)';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return;
        }

        $rows = Excel::toArray([], $filePath)[0]; // ambil sheet pertama
        $rows = array_slice($rows, 1); // skip header

        $products = [];
        $packings = [];

        $productIds = [];
        $productIdCounter = 1;
        $packingIdCounter = 1;

        foreach ($rows as $row) {
            $product_code   = $row[0] ?? null;
            $product_name   = $row[2] ?? null;
            $unit_name      = $row[3] ?? null;
            $conversion_val = $row[4] ?? null;
            $packing_name   = $row[5] ?? null;

            if (!$product_name) continue; // skip kalau kosong

            if (!isset($productIds[$product_name])) {
                $productIds[$product_name] = $productIdCounter;

                $products[] = [
                    'id' => $productIdCounter,
                    'product_code' => $product_code ?: null,
                    'product_name' => $product_name,
                ];

                $productIdCounter++;
            }

            if ($unit_name && $packing_name && $conversion_val) {
                $packings[] = [
                    'id' => $packingIdCounter++,
                    'product_id' => $productIds[$product_name],
                    'packing_name' => $packing_name,
                    'unit_name' => $unit_name,
                    'conversion_value' => (int) $conversion_val,
                ];
            }
        }

        // simpan JSON di storage/app/
        file_put_contents(storage_path('app/products.json'), json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents(storage_path('app/product_packings.json'), json_encode($packings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("✅ Convert selesai! File JSON ada di storage/app/");
    }
}
