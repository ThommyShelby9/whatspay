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
        Schema::create('assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->dateTime('assignment_date')->nullable();
            $table->foreignUuid('assigner_id')->nullable()->references('id')->on('users');
            $table->dateTime('response_date')->nullable();
            $table->foreignUuid('agent_id')->nullable()->references('id')->on('users');
            $table->string('status')->index('assignments_status_index');
            $table->dateTime('submission_date')->nullable();
            $table->integer('vues')->default(0);
            $table->text('files')->nullable();
            $table->double('gain')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
