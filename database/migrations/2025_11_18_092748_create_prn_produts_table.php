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
        Schema::create('prn_produts', function (Blueprint $table) {
            $table->id();
    $table->unsignedBigInteger('prn_id');
    $table->unsignedBigInteger('product_id');
    $table->integer('quantity');
    $table->decimal('unit_price', 15, 2)->nullable();
    $table->decimal('total', 15, 2)->nullable();
    $table->timestamps();

    $table->foreign('ptr_id')->references('id')->on('prns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prt_produts');
    }
};
