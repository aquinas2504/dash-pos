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
        Schema::create('retur_purchases', function (Blueprint $table) {
            $table->string('retur_number')->primary(); // PK cukup retur_number, aman aja
            $table->date('date');
            $table->string('supplier_code');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('supplier_code')->references('supplier_code')->on('suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_purchases');
    }
};
