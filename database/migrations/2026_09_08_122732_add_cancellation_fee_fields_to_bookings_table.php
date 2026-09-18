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
            if (!Schema::hasColumn('bookings', 'cancellation_fee_required')) {
                $table->boolean('cancellation_fee_required')
                    ->default(false)
                    ->after('cancel_reason');
            }

            if (!Schema::hasColumn('bookings', 'cancellation_fee_amount')) {
                $table->decimal('cancellation_fee_amount', 10, 2)
                    ->nullable()
                    ->after('cancellation_fee_required');
            }

            if (!Schema::hasColumn('bookings', 'cancellation_fee_paid')) {
                $table->boolean('cancellation_fee_paid')
                    ->default(false)
                    ->after('cancellation_fee_amount');
            }

            if (!Schema::hasColumn('bookings', 'cancellation_fee_paid_at')) {
                $table->timestamp('cancellation_fee_paid_at')
                    ->nullable()
                    ->after('cancellation_fee_paid');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('bookings', 'cancellation_fee_paid_at')) {
                $columns[] = 'cancellation_fee_paid_at';
            }

            if (Schema::hasColumn('bookings', 'cancellation_fee_paid')) {
                $columns[] = 'cancellation_fee_paid';
            }

            if (Schema::hasColumn('bookings', 'cancellation_fee_amount')) {
                $columns[] = 'cancellation_fee_amount';
            }

            if (Schema::hasColumn('bookings', 'cancellation_fee_required')) {
                $columns[] = 'cancellation_fee_required';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};