<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('master_service_items')
            ->where('name', 'Deep Cleaning')
            ->update([
                'is_optional' => 1,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('master_service_items')
            ->where('name', 'Deep Cleaning')
            ->update([
                'is_optional' => 0,
                'updated_at' => now(),
            ]);
    }
};