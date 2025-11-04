<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTable extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file_name');
            $table->string('file_type');
            $table->string('original_name');
            $table->string('path');
            $table->enum('state', ['active', 'inactive', 'deleted'])->default('active');
            $table->uuid('user_id')->nullable();
            $table->uuid('task_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
    }
}