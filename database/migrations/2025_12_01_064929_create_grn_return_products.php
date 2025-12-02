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
        Schema::create('grn_return_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grn_return_id');
            $table->unsignedBigInteger('products_id');
            $table->integer('qty');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_return_products');
    }
};
