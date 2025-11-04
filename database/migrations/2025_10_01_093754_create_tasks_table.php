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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->dateTime('startdate')->nullable();
            $table->dateTime('enddate')->nullable();
            $table->string('name');
            $table->text('descriptipon')->nullable();
            $table->text('files')->nullable();
            $table->string('status')->index('tasks_status_index');
            $table->foreignUuid('client_id')->nullable()->references('id')->on('users');
            $table->dateTime('validation_date')->nullable();
            $table->foreignUuid('validateur_id')->nullable()->references('id')->on('users');
            $table->double('budget')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
