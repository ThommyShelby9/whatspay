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
        Schema::create('rolerights', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('role_id')->nullable()->references('id')->on('roles');
            $table->string('right')->index('rolerights_right_index');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('right_role');
    }
};
