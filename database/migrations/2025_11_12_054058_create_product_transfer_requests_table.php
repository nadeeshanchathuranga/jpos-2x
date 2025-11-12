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
        Schema::create('product_transfer_requests', function (Blueprint $table) {
             $table->id();
        $table->string('transfer_no')->unique()->comment('Transfer request reference number');
        
        $table->unsignedBigInteger('requested_by')->nullable()->comment('User who created the request');
        $table->unsignedBigInteger('approved_by')->nullable()->comment('User who approved the request');
        $table->date('request_date')->nullable();
        $table->date('approved_date')->nullable();
        $table->text('remarks')->nullable();
        $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_transfer_requests');
    }
};
