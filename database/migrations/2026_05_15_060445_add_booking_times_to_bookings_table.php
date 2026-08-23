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
        Schema::table('bookings', function (Blueprint $table) {

            if (!Schema::hasColumn('bookings', 'booking_start_time')) {
                $table->dateTime('booking_start_time')
                    ->nullable()
                    ->after('status');
            }

            if (!Schema::hasColumn('bookings', 'booking_end_time')) {
                $table->dateTime('booking_end_time')
                    ->nullable()
                    ->after('booking_start_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {

            if (Schema::hasColumn('bookings', 'booking_start_time')) {
                $table->dropColumn('booking_start_time');
            }

            if (Schema::hasColumn('bookings', 'booking_end_time')) {
                $table->dropColumn('booking_end_time');
            }
        });
    }
};
