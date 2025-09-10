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
        Schema::create('invoices', function (Blueprint $table) {
            $table->string('invoice_number')->primary();
            $table->date('date');
            $table->string('penerimaan_number');
            $table->bigInteger('dpp')->nullable();
            $table->bigInteger('ppn')->nullable();
            $table->bigInteger('grandtotal');
            $table->timestamps();

            $table->foreign('penerimaan_number')->references('penerimaan_number')->on('penerimaans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
