<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Add payment tracking field
            if (!Schema::hasColumn('assignments', 'payment_date')) {
                $table->timestamp('payment_date')->nullable()->after('gain');
            }
        });
    }

    public function down()
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'payment_date')) {
                $table->dropColumn('payment_date');
            }
        });
    }
};
