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
        Schema::create('module_settings', function (Blueprint $table) {
            $table->id();
            
            // Core Modules
            // Supplier, Purchase Order, GRN, GRN Return group
            $table->boolean('supplier')->default(true);
            $table->boolean('purchase_order')->default(true);
            $table->boolean('grn')->default(true);
            $table->boolean('grn_return')->default(true);
            
            // Stock Transfer group
            $table->boolean('stock_transfer_request')->default(true);
            $table->boolean('stock_transfer_receive')->default(true);
            
            // Brand and Type group
            $table->boolean('brand')->default(true);
            $table->boolean('type')->default(true);
            
            // Individual modules
            $table->boolean('tax')->default(true);
            $table->boolean('discount')->default(true);
            $table->boolean('sales_return')->default(true);
            
            // Optional Modules
            $table->boolean('barcode')->default(false);
            $table->boolean('email_notification')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_settings');
    }
};
