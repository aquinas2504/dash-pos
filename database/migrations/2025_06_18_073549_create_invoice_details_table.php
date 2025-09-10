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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->unsignedBigInteger('penerimaan_detail');
            $table->bigInteger('price');
            $table->string('discount')->default(0);
            $table->bigInteger('total');
            $table->integer('qty_retur')->nullable();
            $table->string('status_retur')->default('On');
            $table->timestamps();

            $table->foreign('invoice_number')->references('invoice_number')->on('invoices')->onDelete('cascade');
            $table->foreign('penerimaan_detail')->references('id')->on('penerimaan_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
