<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\LicenseType;
use Illuminate\Support\Facades\Schema;

class LicenseTypesSeeder extends Seeder
{
    public function run()
    {
        LicenseType::firstOrCreate(
            ['name' => 'Class 1'],
            ['description' => 'Semi-trucks / Tractor-trailers']
        );
    
        LicenseType::firstOrCreate(
            ['name' => 'Class 2'],
            ['description' => 'Large Buses (24+ passengers)']
        );

        LicenseType::firstOrCreate(
            ['name' => 'Class 3'],
            ['description' => 'Large Trucks / Dump Trucks']
        );

        LicenseType::firstOrCreate(
            ['name' => 'Class 4'],
            ['description' => 'Ambulances, Taxis & Small Buses']
        );
        LicenseType::firstOrCreate(
            ['name' => 'Class 5'],
            ['description' => 'Probationary / Full']
        );
        LicenseType::firstOrCreate(
            ['name' => 'Class 6'],
            ['description' => 'Motorbikes or Mopeds']
        );
        LicenseType::firstOrCreate(
            ['name' => 'Class 7'],
            ['description' => 'Learner']
        );
        LicenseType::firstOrCreate(
            ['name' => 'Class 8 and 9'],
            ['description' => 'Tractors and Off-Road']
        );
        
    }
}