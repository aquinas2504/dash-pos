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
        Schema::create('inventory_adjustment_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('adj_id')
                ->constrained('inventory_adjustments')
                ->onDelete('cascade');

            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            $table->string('ppn'); // yes / no
            $table->integer('qty');
            $table->string('unit'); // Pieces/Lusin/Gross
            $table->double('price')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustment_details');
    }
};
