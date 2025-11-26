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
        Schema::create('drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('form_type'); // contoh: purchase_order, sale_invoice, retur_sales
            $table->unsignedBigInteger('form_id')->nullable(); // kalau draft berasal dari edit existing form
            $table->json('data')->nullable();
            $table->string('url')->nullable(); // simpan halaman asal
            $table->timestamps();

            $table->index(['user_id', 'form_type']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drafts');
    }
};
