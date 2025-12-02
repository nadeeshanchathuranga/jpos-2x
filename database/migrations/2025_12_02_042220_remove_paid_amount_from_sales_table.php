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
        Schema::table('sales', function (Blueprint $table) {
              $table->dropColumn('paid_amount');
              $table->dropColumn('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
             $table->decimal('paid_amount', 10, 2)->nullable();
              $table->integer('payment_type')->default(0)->comment('0 = Cash, 1 = Card, 2 = Credit');

        });
    }
};
