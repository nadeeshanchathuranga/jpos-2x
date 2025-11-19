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
        Schema::create('ptr_produts', function (Blueprint $table) {
            $table->id();
        $table->unsignedBigInteger('ptr_id');
        $table->unsignedBigInteger('product_id');
        $table->integer('requested_qty')->default(0); 
        $table->string('unit_id')->nullable();
        $table->timestamps();

        $table->foreign('ptr_id')->references('id')->on('ptr')->onDelete('cascade');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ptr_produts');
    }
};
