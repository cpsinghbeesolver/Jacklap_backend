<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {

            if (!Schema::hasColumn('bookings', 'parent_booking_id')) {
                $table->unsignedBigInteger('parent_booking_id')
                      ->nullable()
                      ->after('id');

                $table->foreign('parent_booking_id')
                      ->references('id')
                      ->on('bookings')
                      ->onDelete('cascade');
            }

            if (!Schema::hasColumn('bookings', 'slot_date')) {
                $table->date('slot_date')->nullable()->after('end_datetime');
            }

            if (!Schema::hasColumn('bookings', 'slot_start_time')) {
                $table->time('slot_start_time')->nullable()->after('slot_date');
            }

            if (!Schema::hasColumn('bookings', 'slot_end_time')) {
                $table->time('slot_end_time')->nullable()->after('slot_start_time');
            }

            if (!Schema::hasColumn('bookings', 'slot_index')) {
                $table->unsignedInteger('slot_index')->nullable()->after('slot_end_time');
            }

            if (!Schema::hasColumn('bookings', 'duration_type')) {
                $table->string('duration_type')->nullable()->after('slot_index');
            }

            if (!Schema::hasColumn('bookings', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('duration_type');
            }

            if (!Schema::hasColumn('bookings', 'recurring_weeks')) {
                $table->unsignedInteger('recurring_weeks')->nullable()->after('is_recurring');
            }

            if (!Schema::hasColumn('bookings', 'selected_days')) {
                $table->json('selected_days')->nullable()->after('recurring_weeks');
            }

            if (!Schema::hasColumn('bookings', 'time_slots')) {
                $table->json('time_slots')->nullable()->after('selected_days');
            }

            if (!Schema::hasColumn('bookings', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {

            if (Schema::hasColumn('bookings', 'parent_booking_id')) {
                $table->dropForeign(['parent_booking_id']);
                $table->dropColumn('parent_booking_id');
            }

            foreach ([
                'slot_date', 'slot_start_time', 'slot_end_time', 'slot_index',
                'duration_type', 'is_recurring', 'recurring_weeks',
                'selected_days', 'time_slots', 'cancel_reason',
            ] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
