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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('category_id')->nullable();
            $table->integer('qty')->default(0)->comment('Current stock quantity');
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('return_product', 10, 2)->default(0)->comment('Returned quantity');
            $table->string('purchase_unit')->nullable();
            $table->string('sales_unit')->nullable();
            $table->string('transfer_unit')->nullable();
            $table->decimal('conversion', 10, 2)->nullable()->comment('Unit conversion');
            $table->boolean('status')->default(1)->comment('1 = Active, 0 = Inactive');
            $table->timestamps();

        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
