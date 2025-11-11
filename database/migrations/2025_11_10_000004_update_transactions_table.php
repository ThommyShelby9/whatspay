<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Ajouter la colonne related_id si elle n'existe pas
            if (!Schema::hasColumn('transactions', 'related_id')) {
                $table->uuid('related_id')->nullable();
            }
            
            // Modifier la colonne type pour accepter plus de valeurs (PostgreSQL friendly)
            // Pas besoin de modifier le type, juste s'assurer qu'il peut accepter les nouvelles valeurs
        });
        
        // Pour PostgreSQL, on peut simplement utiliser une colonne VARCHAR sans contrainte strict
        // Les nouvelles valeurs seront automatiquement acceptées
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'related_id')) {
                $table->dropColumn('related_id');
            }
        });
    }
};