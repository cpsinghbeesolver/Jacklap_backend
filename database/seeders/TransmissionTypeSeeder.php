<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransmissionType;

class TransmissionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            1 => 'Manual',
            2 => 'Automatic',
            3 => 'Both',
        ];

        foreach ($types as $id => $name) {
            TransmissionType::firstOrCreate(
                ['id' => $id],
                ['name' => $name]
            );
        }
    }
}