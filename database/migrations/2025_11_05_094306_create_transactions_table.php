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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('wallet_id');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['Crédit', 'Débit'])->default('Crédit');
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED', 'CANCELLED'])->default('PENDING');
            $table->string('description')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('receipt_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('wallet_id')->references('id')->on('wallets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};