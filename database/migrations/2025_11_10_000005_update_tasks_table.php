<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add budget tracking fields
            if (!Schema::hasColumn('tasks', 'budget_reserved_at')) {
                $table->timestamp('budget_reserved_at')->nullable()->after('budget');
            }
            
            if (!Schema::hasColumn('tasks', 'budget_released_at')) {
                $table->timestamp('budget_released_at')->nullable()->after('budget_reserved_at');
            }
            
            // Add media type and other fields if they don't exist
            if (!Schema::hasColumn('tasks', 'media_type')) {
                $table->string('media_type')->nullable()->after('type');
            }
            
            if (!Schema::hasColumn('tasks', 'legend')) {
                $table->text('legend')->nullable()->after('url');
            }
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['budget_reserved_at', 'budget_released_at', 'media_type', 'legend']);
        });
    }
};
