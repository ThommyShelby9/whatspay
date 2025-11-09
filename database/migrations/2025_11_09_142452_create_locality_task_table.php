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
        Schema::create('locality_task', function (Blueprint $table) {
            $table->uuid('task_id');
            $table->bigInteger('locality_id'); // Modifié pour utiliser bigint
            $table->timestamps();
            
            $table->primary(['task_id', 'locality_id']);
            
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks')
                  ->onDelete('cascade');
                  
            $table->foreign('locality_id')
                  ->references('id')
                  ->on('localities')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locality_task');
    }
};