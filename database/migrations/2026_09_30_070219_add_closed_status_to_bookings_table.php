<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings')) {
            DB::statement("
                ALTER TABLE bookings
                MODIFY COLUMN status ENUM(
                    'pending',
                    'confirmed',
                    'start_journey',
                    'in_progress',
                    'completed',
                    'cancelled',
                    'expired',
                    'closed'
                ) NOT NULL
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings')) {
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
        }
    }
};