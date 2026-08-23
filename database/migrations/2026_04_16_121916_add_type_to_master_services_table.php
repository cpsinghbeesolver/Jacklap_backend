<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('master_services')) {

            Schema::table('master_services', function (Blueprint $table) {

                if (!Schema::hasColumn('master_services', 'type')) {
                    $table->string('type')
                        ->nullable()
                        ->comment('service | subject | skill')
                        ->after('name');
                }

            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('master_services')) {

            Schema::table('master_services', function (Blueprint $table) {

                if (Schema::hasColumn('master_services', 'type')) {
                    $table->dropColumn('type');
                }

            });
        }
    }
};
