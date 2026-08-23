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
                if (!Schema::hasColumn('carts', 'transmission_type')) {
                    $table->integer('transmission_type')
                        ->nullable()
                        ->after('license_types');
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'transmission_type')) {
                    $table->integer('transmission_type')
                        ->nullable()
                        ->after('license_types');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('carts') && Schema::hasColumn('carts', 'transmission_type')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropColumn('transmission_type_id');
            });
        }

        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'transmission_type')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('transmission_type_id');
            });
        }
    }
};