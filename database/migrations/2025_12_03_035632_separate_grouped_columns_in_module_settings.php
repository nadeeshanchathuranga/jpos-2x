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
        // Drop grouped columns only if they exist
        Schema::table('module_settings', function (Blueprint $table) {
            if (Schema::hasColumn('module_settings', 'supplier_purchase')) {
                $table->dropColumn('supplier_purchase');
            }
            if (Schema::hasColumn('module_settings', 'stock_transfer')) {
                $table->dropColumn('stock_transfer');
            }
            if (Schema::hasColumn('module_settings', 'brand_type')) {
                $table->dropColumn('brand_type');
            }
        });

        // Add separate columns only if they don't exist
        Schema::table('module_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('module_settings', 'supplier')) {
                $table->boolean('supplier')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'purchase_order')) {
                $table->boolean('purchase_order')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'grn')) {
                $table->boolean('grn')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'grn_return')) {
                $table->boolean('grn_return')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'stock_transfer_request')) {
                $table->boolean('stock_transfer_request')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'stock_transfer_receive')) {
                $table->boolean('stock_transfer_receive')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'brand')) {
                $table->boolean('brand')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'type')) {
                $table->boolean('type')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop separated columns only if they exist
        Schema::table('module_settings', function (Blueprint $table) {
            $columnsToCheck = [
                'supplier',
                'purchase_order',
                'grn',
                'grn_return',
                'stock_transfer_request',
                'stock_transfer_receive',
                'brand',
                'type'
            ];

            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('module_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Restore grouped columns only if they don't exist
        Schema::table('module_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('module_settings', 'supplier_purchase')) {
                $table->boolean('supplier_purchase')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'stock_transfer')) {
                $table->boolean('stock_transfer')->default(true);
            }
            if (!Schema::hasColumn('module_settings', 'brand_type')) {
                $table->boolean('brand_type')->default(true);
            }
        });
    }
};
