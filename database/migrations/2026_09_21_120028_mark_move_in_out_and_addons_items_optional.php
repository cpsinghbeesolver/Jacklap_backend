<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('master_service_items')
            ->whereIn('name', [
                'Full cleaning',
                'Cabinets and appliances',
                'Wall spot cleaning',
                'Window (interior)',
                'Carpet (basic)',
                'Balcony cleaning',
            ])
            ->update([
                'is_optional' => 1,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('master_service_items')
            ->whereIn('name', [
                'Full cleaning',
                'Cabinets and appliances',
                'Wall spot cleaning',
                'Window (interior)',
                'Carpet (basic)',
                'Balcony cleaning',
            ])
            ->update([
                'is_optional' => 0,
                'updated_at' => now(),
            ]);
    }
};