<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCampaignColumnsToTasksTable extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'media_type')) {
                $table->string('media_type')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'locality_id')) {
                $table->string('locality_id')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'occupation_id')) {
                $table->string('occupation_id')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'legend')) {
                $table->text('legend')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'url')) {
                $table->string('url')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $columns = ['media_type', 'locality_id', 'occupation_id', 'legend', 'url'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}