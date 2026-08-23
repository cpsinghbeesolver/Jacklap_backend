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
            if (!Schema::hasColumn('bookings', 'is_seeker_rated')) {
                $table->boolean('is_seeker_rated')
                    ->default(false)
                    ->after('booking_end_time');
            }
            if (!Schema::hasColumn('bookings', 'is_provider_rated')) {
                $table->boolean('is_provider_rated')
                ->default(false)
                ->after('is_seeker_rated');
            }
        });

        Schema::table('booking_items', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_items', 'is_reviewed')) {
                $table->boolean('is_reviewed')
                    ->default(false)
                    ->after('total_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'is_seeker_rated') && Schema::hasColumn('bookings', 'is_provider_rated')) {
                $table->dropColumn([
                    'is_seeker_rated',
                    'is_provider_rated',
                ]);
            }
        });

        Schema::table('booking_items', function (Blueprint $table) {
            if(Schema::hasColumn('booking_items', 'is_reviewed')) {
                $table->dropColumn('is_reviewed');
            }
        });
    }
};
