<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('carts') && !Schema::hasColumn('carts', 'service_requirements')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->text('service_requirements')->nullable()->after('tax');
            });
        }

        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'service_requirements')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->text('service_requirements')->nullable()->after('tax');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('carts') && Schema::hasColumn('carts', 'service_requirements')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropColumn('service_requirements');
            });
        }

        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'service_requirements')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('service_requirements');
            });
        }
    }
};