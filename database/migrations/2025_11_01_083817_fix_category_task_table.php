<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixCategoryTaskTable extends Migration
{
    public function up()
    {
        // Option 1: Si la table n'existe pas
        if (!Schema::hasTable('category_task')) {
            Schema::create('category_task', function (Blueprint $table) {
                $table->uuid('category_id');
                $table->uuid('task_id');
                $table->timestamps();
                
                $table->primary(['category_id', 'task_id']);
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            });
        } 
        // Option 2: Si la table existe mais a une colonne avec un nom différent
        else if (Schema::hasColumn('category_task', 'id_category') && !Schema::hasColumn('category_task', 'category_id')) {
            Schema::table('category_task', function (Blueprint $table) {
                $table->renameColumn('id_category', 'category_id');
            });
        }
        // Option 3: Si la table existe mais n'a pas la colonne du tout
        else if (!Schema::hasColumn('category_task', 'category_id')) {
            Schema::table('category_task', function (Blueprint $table) {
                $table->uuid('category_id');
            });
        }
    }

    public function down()
    {
        // Code pour annuler les modifications si nécessaire
    }
}