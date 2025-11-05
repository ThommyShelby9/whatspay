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
        Schema::create('plan_features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plan_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('plan_id')->references('id')->on('plans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};