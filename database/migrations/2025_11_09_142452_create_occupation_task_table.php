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
        Schema::create('occupation_task', function (Blueprint $table) {
            $table->uuid('task_id');
            $table->uuid('occupation_id'); // Supposé être UUID basé sur le modèle
            $table->timestamps();
            
            $table->primary(['task_id', 'occupation_id']);
            
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks')
                  ->onDelete('cascade');
                  
            $table->foreign('occupation_id')
                  ->references('id')
                  ->on('occupations')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupation_task');
    }
};