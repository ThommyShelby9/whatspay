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
        Schema::table('assignments', function (Blueprint $table) {
            // 1) Supprimer la contrainte existante
            $table->dropForeign(['agent_id']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            // 2) Rendre la colonne nullable
            $table->uuid('agent_id')->nullable()->change();

            // 3) Recréer la contrainte avec ON DELETE SET NULL
            $table->foreign('agent_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // À toi d’ajouter le rollback si nécessaire
            $table->dropForeign(['agent_id']);
        });
    }
};
