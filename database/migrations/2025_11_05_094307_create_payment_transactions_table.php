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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('payment_method_id');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('XOF');
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED', 'CANCELLED'])->default('PENDING');
            $table->string('reference')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('callback_url')->nullable();
            $table->json('payload')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};