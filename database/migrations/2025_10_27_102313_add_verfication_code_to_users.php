<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_verification_code', 8)->nullable();
            $table->timestamp('email_verification_code_expiry')->nullable();
            $table->string('password_reset_code', 8)->nullable();
            $table->timestamp('password_reset_code_expiry')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verification_code');
            $table->dropColumn('email_verification_code_expiry');
            $table->dropColumn('password_reset_code');
            $table->dropColumn('password_reset_code_expiry');
        });
    }
};
