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
        Schema::create('history_retur_purchases', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('invoice_number'); // invoice asal retur
            $table->unsignedBigInteger('id_product');
            $table->string('supplier_code'); 
            $table->integer('qty_retur');
            $table->string('unit');
            $table->timestamps();

            // kalau ada relasi ke products
            $table->foreign('id_product')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('supplier_code')->references('supplier_code')->on('suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_retur_purchases');
    }
};
