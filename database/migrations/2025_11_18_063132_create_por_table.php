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
        Schema::create('por', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id'); // who created the order
        $table->string('order_number')->unique();
        $table->date('order_date');
        $table->unsignedBigInteger('supplier_id')->nullable();
        $table->decimal('total_amount', 15, 2)->default(0);
        $table->string('status')->default('pending')->comment('pending, completed, received, cancelled'); 
        $table->unsignedBigInteger('release_user_id')->nullable();
        $table->unsignedBigInteger('receive_user_id')->nullable();
        $table->timestamps();
        // foreign keys
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('release_user_id')->references('id')->on('users')->onDelete('set null');
        $table->foreign('receive_user_id')->references('id')->on('users')->onDelete('set null');
        $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('por');
    }
};
