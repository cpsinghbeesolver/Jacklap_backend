<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Add service_item_id only if not exists
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('services') &&
            !Schema::hasColumn('services', 'service_item_id')
        ) {

            Schema::table('services', function (Blueprint $table) {

                $table->foreignId('service_item_id')
                    ->nullable()
                    ->after('service_id');

                /*
                |--------------------------------------------------------------------------
                | Add foreign key only if not exists
                |--------------------------------------------------------------------------
                */

                $table->foreign('service_item_id')
                    ->references('id')
                    ->on('master_service_items')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Drop only if column exists
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('services') &&
            Schema::hasColumn('services', 'service_item_id')
        ) {

            Schema::table('services', function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Drop FK safely
                |--------------------------------------------------------------------------
                */

                try {
                    $table->dropForeign(['service_item_id']);
                } catch (\Throwable $e) {
                    // Ignore if foreign key does not exist
                }

                $table->dropColumn('service_item_id');
            });
        }
    }
};
