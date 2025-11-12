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
         Schema::create('grn_products', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('grn_id');
        $table->unsignedBigInteger('product_id');
        $table->integer('qty')->default(0);
        $table->decimal('purchase_price', 10, 2)->nullable();
        $table->decimal('selling_price', 10, 2)->nullable();
        $table->decimal('discount', 10, 2)->nullable();
        $table->decimal('total', 15, 2)->default(0);
        $table->timestamps();

        $table->foreign('grn_id')->references('id')->on('grns')->onDelete('cascade');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_products');
    }
};
