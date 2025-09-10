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
        Schema::create('retur_sales', function (Blueprint $table) {
            $table->string('retur_number')->primary();
            $table->string('invoice_number');
            $table->date('date');
            $table->decimal('total', 15, 2)->default(0);
            $table->string('status')->default('Active');
            $table->string('used_for')->nullable();
            $table->timestamps();

            $table->foreign('invoice_number')->references('invoice_number')->on('sale_invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_sales');
    }
};
