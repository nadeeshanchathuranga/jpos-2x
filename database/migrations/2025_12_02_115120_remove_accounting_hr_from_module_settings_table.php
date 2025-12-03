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
            if (Schema::hasColumn('module_settings', 'accounting')) {
                $table->dropColumn('accounting');
            }
            if (Schema::hasColumn('module_settings', 'hr')) {
                $table->dropColumn('hr');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('module_settings', 'accounting')) {
                $table->boolean('accounting')->default(false);
            }
            if (!Schema::hasColumn('module_settings', 'hr')) {
                $table->boolean('hr')->default(false);
            }
        });
    }
};
