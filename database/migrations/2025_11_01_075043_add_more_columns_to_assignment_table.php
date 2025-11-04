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
// Migration pour ajouter des colonnes à la table assignments
Schema::table('assignments', function (Blueprint $table) {
    $table->integer('expected_views')->nullable();
    $table->integer('expected_gain')->nullable();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment', function (Blueprint $table) {
            //
        });
    }
};
