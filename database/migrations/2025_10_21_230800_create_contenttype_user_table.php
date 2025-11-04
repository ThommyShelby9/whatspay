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
        Schema::create('contenttype_user', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('contenttype_id')->nullable()->references('id')->on('contenttypes');
            $table->foreignUuid('user_id')->nullable()->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contenttype_user');
    }
};
