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
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->unsignedBigInteger('id_product')->nullable();
            $table->integer('qty_packing');
            $table->string('packing');
            $table->integer('quantity');
            $table->string('unit');
            $table->decimal('price', 15, 2);
            $table->string('discount')->nullable();
            $table->decimal('total', 15, 2);
            $table->string('status')->default('Unordered');

            $table->foreign('order_number')->references('order_number')->on('sales')->onDelete('cascade');
            $table->foreign('id_product')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};
