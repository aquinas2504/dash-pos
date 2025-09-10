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
        Schema::create('retur_purchase_details', function (Blueprint $table) {
            $table->id();
            $table->string('retur_number'); // FK ke retur_purchases
            $table->unsignedBigInteger('id_product');
            $table->integer('qty');
            $table->string('unit');
            $table->decimal('price', 15, 2);
            $table->string('discount')->default(0);
            $table->decimal('value', 15, 2);
            $table->timestamps();

            $table->foreign('retur_number')->references('retur_number')->on('retur_purchases')->onDelete('cascade');
            $table->foreign('id_product')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_purchase_details');
    }
};
