<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (
            Schema::hasTable('users') &&
            Schema::hasColumn('users', 'latitude') &&
            Schema::hasColumn('users', 'longitude')
        ) {
            DB::statement("
                ALTER TABLE users
                MODIFY latitude DOUBLE(15,10) NULL,
                MODIFY longitude DOUBLE(15,10) NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('users') &&
            Schema::hasColumn('users', 'latitude') &&
            Schema::hasColumn('users', 'longitude')
        ) {
            DB::statement("
                ALTER TABLE users
                MODIFY latitude DECIMAL(10,7) NULL,
                MODIFY longitude DECIMAL(10,7) NULL
            ");
        }
    }
};