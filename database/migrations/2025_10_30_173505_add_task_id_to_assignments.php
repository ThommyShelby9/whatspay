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
            if (!Schema::hasColumn('assignments', 'task_id')) {
                $table->uuid('task_id')->nullable()->after('id');
                $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'task_id')) {
                $table->dropForeign(['task_id']);
                $table->dropColumn('task_id');
            }
        });
    }
};
