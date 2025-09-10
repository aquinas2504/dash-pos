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
        Schema::create('penerimaans', function (Blueprint $table) {
            $table->string('penerimaan_number')->primary();
            $table->date('date');
            $table->string('supplier_code')->nullable();
            $table->string('ppn_status')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();

            // Foreign Key
            $table->foreign('supplier_code')->references('supplier_code')->on('suppliers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaans');
    }
};
