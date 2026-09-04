<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'platform_fee')) {
                $table->decimal('platform_fee', 10, 2)
                    ->default(0)
                    ->after('tax');
            }

            if (!Schema::hasColumn('bookings', 'platform_fee_type')) {
                $table->string('platform_fee_type')
                    ->nullable()
                    ->after('platform_fee');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'platform_fee_type')) {
                $table->dropColumn('platform_fee_type');
            }

            if (Schema::hasColumn('bookings', 'platform_fee')) {
                $table->dropColumn('platform_fee');
            }
        });
    }
};