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
        // Vérifier si la table plans existe, si non la créer d'abord
        if (!Schema::hasTable('plans')) {
            Schema::create('plans', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 15, 2);
                $table->integer('duration_days')->default(30);
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::create('plan_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('plan_id');
            $table->uuid('transaction_id')->nullable();
            $table->enum('status', ['ACTIVE', 'CANCELLED', 'EXPIRED'])->default('ACTIVE');
            $table->timestamp('started_at');
            $table->timestamp('valid_until');
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('plan_id')->references('id')->on('plans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_subscriptions');
    }
};