<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'platform_fee_value')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->decimal('platform_fee_value', 10, 2)
                    ->nullable()
                    ->after('platform_fee');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'platform_fee_value')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('platform_fee_value');
            });
        }
    }
};