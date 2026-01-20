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
        if (!Schema::hasColumn('purchase_order_requests', 'supplier_id')) {
            Schema::table('purchase_order_requests', function (Blueprint $table) {
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('purchase_order_requests', 'supplier_id')) {
            Schema::table('purchase_order_requests', function (Blueprint $table) {
                $table->dropConstrainedForeignId('supplier_id');
            });
        }
    }
};
