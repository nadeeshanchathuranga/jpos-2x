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
          Schema::create('product_transfer_request_products', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('transfer_request_id');
        $table->unsignedBigInteger('product_id');
        $table->integer('requested_qty')->default(0);
        $table->integer('approved_qty')->nullable();
        $table->decimal('purchase_price', 10, 2)->nullable();
        $table->decimal('selling_price', 10, 2)->nullable();
        $table->string('unit_id')->nullable();
        $table->timestamps();

        $table->foreign('transfer_request_id')->references('id')->on('product_transfer_requests')->onDelete('cascade');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_transfer_request_products');
    }
};
