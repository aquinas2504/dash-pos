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
        Schema::create('list_retur_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_product'); 
            $table->string('supplier_code'); 
            $table->integer('qty'); 
            $table->string('unit'); 
            $table->decimal('price', 15, 2);
            $table->string('discount')->default(0);
            $table->timestamps();

            // Relasi
            $table->foreign('id_product')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('supplier_code')->references('supplier_code')->on('suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_retur_purchases');
    }
};
