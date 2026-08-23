<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            DB::statement("
                ALTER TABLE bookings
                MODIFY COLUMN status ENUM(
                    'pending',
                    'confirmed',
                    'start_journey',
                    'in_progress',
                    'completed',
                    'cancelled',
                    'expired'
                ) NOT NULL
            ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE bookings
            MODIFY COLUMN status ENUM(
                'pending',
                'confirmed',
                'start_journey',
                'in_progress',
                'completed',
                'cancelled',
            ) NOT NULL
        ");
    }
};
