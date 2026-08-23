<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('carts')) {

            Schema::table('carts', function (Blueprint $table) {

                // duration type (single / multiple)
                if (!Schema::hasColumn('carts', 'duration_type')) {
                    $table->enum('duration_type', ['single_day', 'multiple_days'])
                          ->default('single_day')
                          ->after('service_category_id');
                }

                // recurring flag
                if (!Schema::hasColumn('carts', 'is_recurring')) {
                    $table->boolean('is_recurring')
                          ->default(false)
                          ->after('duration_type');
                }

                // number of weeks
                if (!Schema::hasColumn('carts', 'recurring_weeks')) {
                    $table->integer('recurring_weeks')
                          ->nullable()
                          ->after('is_recurring');
                }

                // selected days (mon, tue etc)
                if (!Schema::hasColumn('carts', 'selected_days')) {
                    $table->json('selected_days')
                          ->nullable()
                          ->after('recurring_weeks');
                }

                // flexible time slots
                if (!Schema::hasColumn('carts', 'time_slots')) {
                    $table->json('time_slots')
                          ->nullable()
                          ->after('selected_days');
                }

            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('carts')) {

            Schema::table('carts', function (Blueprint $table) {

                if (Schema::hasColumn('carts', 'time_slots')) {
                    $table->dropColumn('time_slots');
                }

                if (Schema::hasColumn('carts', 'selected_days')) {
                    $table->dropColumn('selected_days');
                }

                if (Schema::hasColumn('carts', 'recurring_weeks')) {
                    $table->dropColumn('recurring_weeks');
                }

                if (Schema::hasColumn('carts', 'is_recurring')) {
                    $table->dropColumn('is_recurring');
                }

                if (Schema::hasColumn('carts', 'duration_type')) {
                    $table->dropColumn('duration_type');
                }

            });
        }
    }
};
