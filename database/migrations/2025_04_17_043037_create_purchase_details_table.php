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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->string('order_number'); 
            $table->unsignedBigInteger('so_detail')->nullable(); 
            $table->unsignedBigInteger('id_product')->nullable(); 
            $table->integer('qty_packing')->nullable();
            $table->string('packing')->nullable();
            $table->integer('qty_unit')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('discount')->nullable();
            $table->decimal('total', 15, 2);
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->foreign('order_number')->references('order_number')->on('purchases')->onDelete('cascade');
            $table->foreign('so_detail')->references('id')->on('sale_details')->onDelete('set null');
            $table->foreign('id_product')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
