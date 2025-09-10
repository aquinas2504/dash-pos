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
        Schema::create('sale_invoices', function (Blueprint $table) {
            $table->string('invoice_number')->primary();
            $table->date('date');
            $table->string('sj_number');
            $table->bigInteger('dpp')->nullable();
            $table->bigInteger('ppn')->nullable();
            $table->bigInteger('grandtotal');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->bigInteger('retur_used')->default(0);
            $table->timestamps();

            $table->foreign('sj_number')->references('sj_number')->on('surat_jalans')->onDelete('cascade');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_invoices');
    }
};
