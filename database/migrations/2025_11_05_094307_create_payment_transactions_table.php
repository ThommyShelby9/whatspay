<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('payment_method_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('XOF');
            $table->enum('status', ['PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'CANCELLED'])->default('PENDING');
            $table->string('reference')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('callback_url')->nullable();
            $table->json('payload')->nullable(); // Store request payload
            $table->json('gateway_response')->nullable(); // Store PayPlus response
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
            $table->index(['user_id', 'status']);
            $table->index('reference');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
};