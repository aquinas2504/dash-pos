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
        Schema::table('detail_tables', function (Blueprint $table) {
            // === sale_details ===
            Schema::table('sale_details', function (Blueprint $table) {
                $table->decimal('quantity', 8, 2)->change();
            });

            // === purchase_details ===
            Schema::table('purchase_details', function (Blueprint $table) {
                $table->decimal('qty_unit', 8, 2)->nullable()->change();
            });

            // === surat_jalan_details ===
            Schema::table('surat_jalan_details', function (Blueprint $table) {
                $table->decimal('qty_unit', 8, 2)->nullable()->change();
            });

            // === penerimaan_details ===
            Schema::table('penerimaan_details', function (Blueprint $table) {
                $table->decimal('qty_unit', 8, 2)->nullable()->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback ke integer kalau dibutuhkan
        Schema::table('sale_details', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('purchase_details', function (Blueprint $table) {
            $table->integer('qty_unit')->nullable()->change();
        });

        Schema::table('surat_jalan_details', function (Blueprint $table) {
            $table->integer('qty_unit')->nullable()->change();
        });

        Schema::table('penerimaan_details', function (Blueprint $table) {
            $table->integer('qty_unit')->nullable()->change();
        });
    }
};
