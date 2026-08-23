<?php

namespace Database\Seeders;

use App\Models\ServiceUseCase;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceUseCaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    $data = [
        'Personal driver for a few hours',
        'Full-day driver hire',
        'Driving client’s car for servicing',
        'Emergency driving situations',
        'Errand-based driving tasks',
    ];

    foreach ($data as $title) {
        ServiceUseCase::firstOrCreate(
            [
                'title' => $title,
                'service_category_id' => 5
            ],
            [] // nothing to update for now
        );
    }
}
}
