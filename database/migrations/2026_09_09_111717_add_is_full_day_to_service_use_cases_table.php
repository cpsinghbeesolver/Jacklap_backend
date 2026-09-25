<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_use_cases') &&
            !Schema::hasColumn('service_use_cases', 'is_full_day')) {

            Schema::table('service_use_cases', function (Blueprint $table) {
                $table->boolean('is_full_day')
                    ->default(false)
                    ->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_use_cases') &&
            Schema::hasColumn('service_use_cases', 'is_full_day')) {

            Schema::table('service_use_cases', function (Blueprint $table) {
                $table->dropColumn('is_full_day');
            });
        }
    }
};