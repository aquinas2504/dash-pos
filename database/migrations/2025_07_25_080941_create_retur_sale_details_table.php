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
        Schema::create('retur_sale_details', function (Blueprint $table) {
            $table->id();
            $table->string('retur_number');
            $table->unsignedBigInteger('id_product');
            $table->integer('qty');
            $table->string('unit');
            $table->decimal('value', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('retur_number')->references('retur_number')->on('retur_sales')->onDelete('cascade');
            $table->foreign('id_product')->references('id')->on('products')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_sale_details');
    }
};
