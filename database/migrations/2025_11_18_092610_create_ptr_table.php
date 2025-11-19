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
        Schema::create('ptr', function (Blueprint $table) {
              $table->id();
        $table->string('transfer_no')->unique()->comment('Transfer request reference number'); 
        $table->date('request_date')->nullable(); 
        $table->text('remarks')->nullable();
        $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ptr');
    }
};
