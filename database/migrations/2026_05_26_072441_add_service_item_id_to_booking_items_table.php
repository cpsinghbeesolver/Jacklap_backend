<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_items')) {

            Schema::table('booking_items', function (Blueprint $table) {

                // -------------------------
                // service_item_id
                // -------------------------
                if (!Schema::hasColumn('booking_items', 'service_item_id')) {

                    $table->unsignedBigInteger('service_item_id')
                        ->nullable()
                        ->after('service_id');

                    $table->index('service_item_id');
                }

                // -------------------------
                // class_name
                // -------------------------
                if (!Schema::hasColumn('booking_items', 'class_name')) {

                    $table->string('class_name')
                        ->nullable()
                        ->after('service_item_id');

                    $table->index('class_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_items')) {

            Schema::table('booking_items', function (Blueprint $table) {

                if (Schema::hasColumn('booking_items', 'service_item_id')) {
                    $table->dropIndex(['service_item_id']);
                    $table->dropColumn('service_item_id');
                }

                if (Schema::hasColumn('booking_items', 'class_name')) {
                    $table->dropIndex(['class_name']);
                    $table->dropColumn('class_name');
                }
            });
        }
    }
};
