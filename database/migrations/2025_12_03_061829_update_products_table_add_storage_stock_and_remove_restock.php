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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'storage_stock_qty')) {
                $table->integer('storage_stock_qty')->default(0)->after('qty');
            }
            if (Schema::hasColumn('products', 're_stock_qty')) {
                $table->dropColumn('re_stock_qty');
            }
            if (Schema::hasColumn('products', 'purchase_to_sales_rate')) {
                $table->dropColumn('purchase_to_sales_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'storage_stock_qty')) {
                $table->dropColumn('storage_stock_qty');
            }
            if (!Schema::hasColumn('products', 're_stock_qty')) {
                $table->integer('re_stock_qty')->nullable();
            }
            if (!Schema::hasColumn('products', 'purchase_to_sales_rate')) {
                $table->decimal('purchase_to_sales_rate', 10, 2)->nullable();
            }
        });
    }
};
