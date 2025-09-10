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
        Schema::create('sale_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->unsignedBigInteger('surat_jalan_detail');
            $table->bigInteger('price');
            $table->string('discount')->default(0);
            $table->bigInteger('total');
            $table->timestamps();

            $table->foreign('invoice_number')->references('invoice_number')->on('sale_invoices')->onDelete('cascade');
            $table->foreign('surat_jalan_detail')->references('id')->on('surat_jalan_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_invoice_details');
    }
};
