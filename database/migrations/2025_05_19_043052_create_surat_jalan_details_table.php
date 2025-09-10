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
        Schema::create('surat_jalan_details', function (Blueprint $table) {
            $table->id();
            $table->string('sj_number');
            $table->string('so_number')->nullable();
            $table->unsignedBigInteger('id_product')->nullable();
            $table->integer('qty_packing')->nullable();
            $table->string('packing')->nullable();
            $table->integer('qty_unit')->nullable();
            $table->string('unit')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->foreign('sj_number')->references('sj_number')->on('surat_jalans')->onDelete('cascade');
            $table->foreign('so_number')->references('order_number')->on('sales')->onDelete('set null');
            $table->foreign('id_product')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_jalan_details');
    }
};
