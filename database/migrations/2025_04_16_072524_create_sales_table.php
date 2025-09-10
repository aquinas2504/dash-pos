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
        Schema::create('sales', function (Blueprint $table) {
            $table->string('order_number')->primary();
            $table->date('order_date');
            $table->string('customer_code')->nullable();
            $table->string('ppn_status');
            $table->decimal('subtotal', 18, 2);
            $table->decimal('dpp', 18, 2)->nullable();
            $table->decimal('ppn', 18, 2)->nullable();
            $table->decimal('grandtotal', 18, 2);
            $table->string('top')->nullable();
            $table->string('ship_1')->nullable();
            $table->string('ship_2')->nullable();
            $table->string('note')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->foreign('customer_code')->references('customer_code')->on('customers')->onDelete('set null');
            $table->foreign('ship_1')->references('shipping_code')->on('shippings')->onDelete('set null');
            $table->foreign('ship_2')->references('shipping_code')->on('shippings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
