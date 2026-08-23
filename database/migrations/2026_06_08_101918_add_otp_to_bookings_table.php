<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('bookings') &&
            !Schema::hasColumn('bookings', 'otp')
        ) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('otp', 10)
                    ->nullable()
                    ->after('status');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('bookings') &&
            Schema::hasColumn('bookings', 'otp')
        ) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('otp');
            });
        }
    }
};