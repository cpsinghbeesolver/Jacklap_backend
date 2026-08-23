<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {

            if (!Schema::hasColumn('booking_items', 'total_price')) {
                $table->decimal('total_price', 10, 2)
                      ->default(0)
                      ->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {

            if (Schema::hasColumn('booking_items', 'total_price')) {
                $table->dropColumn('total_price');
            }
        });
    }
};