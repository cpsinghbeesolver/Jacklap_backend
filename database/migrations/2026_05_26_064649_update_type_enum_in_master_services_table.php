<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('master_services') &&
            Schema::hasColumn('master_services', 'type')
        ) {

            /*
            |--------------------------------------------------------------------------
            | Fix invalid existing values first
            |--------------------------------------------------------------------------
            */

            DB::table('master_services')
            ->whereNull('type')
            ->orWhereNotIn('type', [
                'service',
                'subject',
                'skill',
                'property'
            ])
            ->update([
                'type' => 'service'
            ]);

            /*
            |--------------------------------------------------------------------------
            | Modify ENUM safely
            |--------------------------------------------------------------------------
            */

            DB::statement("
                ALTER TABLE master_services
                MODIFY COLUMN type
                ENUM(
                    'service',
                    'subject',
                    'skill',
                    'property'
                )
                NOT NULL
                DEFAULT 'service'
            ");
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('master_services') &&
            Schema::hasColumn('master_services', 'type')
        ) {

            /*
            |--------------------------------------------------------------------------
            | Replace property before rollback
            |--------------------------------------------------------------------------
            */

            DB::table('master_services')
                ->where('type', 'property')
                ->update([
                    'type' => 'service'
                ]);

            /*
            |--------------------------------------------------------------------------
            | Revert ENUM
            |--------------------------------------------------------------------------
            */

            DB::statement("
                ALTER TABLE master_services
                MODIFY COLUMN type
                ENUM(
                    'service',
                    'subject',
                    'skill'
                )
                NOT NULL
                DEFAULT 'service'
            ");
        }
    }
};