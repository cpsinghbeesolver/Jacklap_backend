<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'purpose')) {
                $table->string('purpose')
                    ->default('booking')
                    ->after('booking_id');
            }

            if (!Schema::hasColumn('payments', 'cancellation_reason')) {
                $table->string('cancellation_reason')
                    ->nullable()
                    ->after('purpose');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('payments', 'cancellation_reason')) {
                $columns[] = 'cancellation_reason';
            }

            if (Schema::hasColumn('payments', 'purpose')) {
                $columns[] = 'purpose';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};