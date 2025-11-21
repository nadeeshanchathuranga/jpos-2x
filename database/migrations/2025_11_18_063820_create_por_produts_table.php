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
        Schema::create('por_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('por_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(1); 
            $table->foreignId('measurement_unit_id')->nullable()->constrained('measurement_units')->onDelete('set null');
            $table->timestamps(); 
            
            $table->foreign('por_id')->references('id')->on('pors')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('por_products');
    }
};
