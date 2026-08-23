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
            if (!Schema::hasColumn('bookings', 'is_service_rated')) {
                $table->boolean('is_service_rated')
                    ->default(false)
                    ->after('is_provider_rated');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'is_service_rated') && Schema::hasColumn('bookings', 'is_provider_rated')) {
                $table->dropColumn([
                    'is_service_rated',
                ]);
            }
        });
    }
};
