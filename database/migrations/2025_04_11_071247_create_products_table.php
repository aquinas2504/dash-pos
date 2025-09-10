<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); 
            $table->string('product_code')->nullable();
            $table->string('product_name')->unique();
            $table->bigInteger('qty_ppn')->default(0); 
            $table->bigInteger('qty_nonppn')->default(0); 
            $table->unsignedBigInteger('total_sold_qty')->default(0); // dalam Pieces
            $table->unsignedBigInteger('total_sold_amount')->default(0); // dalam Rupiah
            $table->unsignedBigInteger('total_purchase_qty')->default(0); // dalam Pieces
            $table->unsignedBigInteger('total_purchase_amount')->default(0); // dalam Rupiah
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
