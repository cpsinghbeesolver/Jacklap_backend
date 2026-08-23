<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_services')) {

            Schema::table('master_services', function (Blueprint $table) {

                if (!Schema::hasColumn('master_services', 'status')) {
                    $table->tinyInteger('status')
                        ->default(0)
                        ->comment('0 = inactive, 1 = active')
                        ->after('name'); // change position if needed
                }

            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('master_services')) {

            Schema::table('master_services', function (Blueprint $table) {

                if (Schema::hasColumn('master_services', 'status')) {
                    $table->dropColumn('status');
                }

            });
        }
    }
};
