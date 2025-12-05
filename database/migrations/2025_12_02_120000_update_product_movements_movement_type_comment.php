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
        Schema::table('product_movements', function (Blueprint $table) {
            // Update the comment to include the BRN Return type (5)
            $table->integer('movement_type')
                ->default(0)
                ->comment('0 = Purchase, 1 = Purchase Return, 2 = Transfer, 3 = Sale, 4 = Sale Return, 5 = BRN Return')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_movements', function (Blueprint $table) {
            $table->integer('movement_type')
                ->default(0)
                ->comment('0 = Purchase, 1 = Purchase Return, 2 = Transfer, 3 = Sale, 4 = Sale Return')
                ->change();
        });
    }
};
