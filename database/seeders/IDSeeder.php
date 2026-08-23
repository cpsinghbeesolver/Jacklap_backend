<?php

namespace Database\Seeders;

use App\Models\IdentityType;
use Illuminate\Database\Seeder;

class IDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idTypes = [
            [
                'name' => 'Work Permit',
                'total_documents' => 2,
                'is_required' => true,
            ],
            [
                'name' => 'Driving License',
                'total_documents' => 2,
                'is_required' => true,
            ],
            [
                'name' => 'Certificates',
                'total_documents' => 2,
                'is_required' => false,
            ],
        ];

        foreach ($idTypes as $idType) {
            IdentityType::updateOrCreate(
                ['name' => $idType['name']],
                [
                    'total_documents' => $idType['total_documents'],
                    'is_required' => $idType['is_required'],
                ]
            );
        }
    }
}