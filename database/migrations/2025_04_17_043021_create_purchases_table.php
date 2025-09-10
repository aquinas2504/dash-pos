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
        Schema::create('purchases', function (Blueprint $table) {
            $table->string('order_number')->primary();
            $table->date('order_date');
            $table->string('supplier_code')->nullable();
            $table->string('ppn_status')->nullable();
            $table->decimal('subtotal', 18, 2);
            $table->decimal('dpp', 18, 2)->nullable();
            $table->decimal('ppn', 18, 2)->nullable();
            $table->decimal('grandtotal', 18, 2);
            $table->string('note')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
            
            $table->foreign('supplier_code')->references('supplier_code')->on('suppliers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
