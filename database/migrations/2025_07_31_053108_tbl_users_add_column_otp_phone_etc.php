<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->unique()->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'otp')) {
                $table->string('otp')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'country_code')) {
                $table->string('country_code',16)->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp');
            }
            if (!Schema::hasColumn('users', 'image')) {
                $table->string('image')->nullable()->after('otp_expires_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'otp')) {
                $table->dropColumn('otp');
            }
            if (Schema::hasColumn('users', 'country_code')) {
                $table->dropColumn('country_code');
            }
            if (Schema::hasColumn('users', 'otp_expires_at')) {
                $table->dropColumn('otp_expires_at');
            }
            if (Schema::hasColumn('users', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
