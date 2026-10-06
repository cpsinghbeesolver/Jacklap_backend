<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_concerns') &&
            !Schema::hasColumn('booking_concerns', 'continue_with_service')) {
            
            Schema::table('booking_concerns', function (Blueprint $table) {
                $table->boolean('continue_with_service')
                    ->nullable()
                    ->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_concerns') &&
            Schema::hasColumn('booking_concerns', 'continue_with_service')) {
            
            Schema::table('booking_concerns', function (Blueprint $table) {
                $table->dropColumn('continue_with_service');
            });
        }
    }
};