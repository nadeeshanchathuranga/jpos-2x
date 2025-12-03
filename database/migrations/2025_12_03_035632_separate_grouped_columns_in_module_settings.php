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
        Schema::table('module_settings', function (Blueprint $table) {
            // Drop grouped columns
            $table->dropColumn(['supplier_purchase', 'stock_transfer', 'brand_type']);
        });

        Schema::table('module_settings', function (Blueprint $table) {
            // Add separate columns for supplier/purchase group
            $table->boolean('supplier')->default(true)->after('id');
            $table->boolean('purchase_order')->default(true)->after('supplier');
            $table->boolean('grn')->default(true)->after('purchase_order');
            $table->boolean('grn_return')->default(true)->after('grn');
            
            // Add separate columns for stock transfer group
            $table->boolean('stock_transfer_request')->default(true)->after('grn_return');
            $table->boolean('stock_transfer_receive')->default(true)->after('stock_transfer_request');
            
            // Add separate columns for brand/type group
            $table->boolean('brand')->default(true)->after('stock_transfer_receive');
            $table->boolean('type')->default(true)->after('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_settings', function (Blueprint $table) {
            // Drop separated columns
            $table->dropColumn([
                'supplier',
                'purchase_order',
                'grn',
                'grn_return',
                'stock_transfer_request',
                'stock_transfer_receive',
                'brand',
                'type'
            ]);
        });

        Schema::table('module_settings', function (Blueprint $table) {
            // Restore grouped columns
            $table->boolean('supplier_purchase')->default(true);
            $table->boolean('stock_transfer')->default(true);
            $table->boolean('brand_type')->default(true);
        });
    }
};
