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
        Schema::create('penerimaan_details', function (Blueprint $table) {
            $table->id();
            $table->string('penerimaan_number');
            $table->string('po_number')->nullable(); 
            $table->unsignedBigInteger('id_product')->nullable();
            $table->integer('qty_packing')->nullable();
            $table->string('packing')->nullable();
            $table->integer('qty_unit')->nullable();
            $table->string('unit')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('penerimaan_number')->references('penerimaan_number')->on('penerimaans')->onDelete('cascade');
            $table->foreign('po_number')->references('order_number')->on('purchases')->onDelete('set null');
            $table->foreign('id_product')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_details');
    }
};
