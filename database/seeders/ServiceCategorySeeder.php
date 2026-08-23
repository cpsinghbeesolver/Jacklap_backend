<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\MasterService;
use App\Models\MasterServiceItem;
use App\Models\MaterialType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $serviceCategories = [
            [
                'name' => 'Home Cleaning',
                'price' => 999,
                'image' => 'frontend/images/cleaning.png',
                'sort_order' => 2
            ],
            [
                'name' => 'In-Home / On-Call Salon',
                'price' => 899,
                'image' => 'frontend/images/salon.png',
                'sort_order' => 1
            ],
            [
                'name' => 'Home Tutoring',
                'price' => 799,
                'image' => 'frontend/images/Home Tutoring.png',
                'sort_order' => 3
            ],
            [
                'name' => 'General-In Home Help',
                'price' => 899,
                'image' => 'frontend/images/home_help.png',
                'sort_order' => 4
            ],
            [
                'name' => 'Driver Services',
                'price' => 699,
                'image' => 'frontend/images/driver.png',
                'sort_order' => 5
            ]
        ];

        foreach ($serviceCategories as $category) {

            $serviceCategory = ServiceCategory::firstOrCreate(
                [
                    'slug' => Str::slug($category['name'])
                ],
                [
                    'name' => $category['name'],
                    'price' => $category['price'],
                    'image' => $category['image'],
                    'status' => 1,
                    'sort_order' => $category['sort_order'],
                ]
            );

            if ($serviceCategory->name === 'Home Cleaning') {
 
                // -------------------------------------------------------
                // 1. SERVICE TYPES  →  type = 'service'
                // -------------------------------------------------------
                $propertyTypes = [
                    [
                        'name'     => 'Room Cleaning',
                        'sort_order' => 1
                    ],
                    [
                        'name'     => 'Bathroom Cleaning',
                        'sort_order' => 2
                    ],
                    [
                        'name'     => 'Kitchen  Cleaning',
                        'sort_order' => 3
                    ],
                    [
                        'name'     => 'Mat Cleaning',
                        'sort_order' => 7
                    ]
                ];
                foreach ($propertyTypes as $group) {
 
                    $masterService = MasterService::firstOrCreate(
                        [
                            'name'                => $group['name'],
                            'service_category_id' => $serviceCategory->id,
                            'type'                => 'service',
                        ],
                        [
                            'description' => $group['name'],
                            'is_default'  => 1,
                            'status'      => 1
                        ]
                    );
 
                    // foreach ($group['items'] as $index => $itemName) {
                    //     MasterServiceItem::firstOrCreate(
                    //         [
                    //             'name'              => $itemName,
                    //             'master_service_id' => $masterService->id,
                    //         ],
                    //         [
                    //             'description' => $itemName . ' apartment',
                    //             'status'      => 1,
                    //             'sort_order'  => $index + 1,
                    //         ]
                    //     );
                    // }
                }
 
                // -------------------------------------------------------
                // 2. SERVICE CATEGORIES  →  type = 'service'
                //    Sub-items saved in master_service_items
                // -------------------------------------------------------
                $serviceGroups = [
                    [
                        'name'     => 'Move-in / Move-Out',
                        'sort_order' => 4,
                        'input_type'=> 'radio',
                        'items'    => [
                            'Full cleaning',
                            'Cabinets and appliances',
                            'Wall spot cleaning',
                        ],
                    ],
                    [
                        'name'     => 'Add-ons',
                        'sort_order' => 5,
                        'input_type'=> 'checkbox',
                        'items'    => [
                            'Window (interior)',
                            'Carpet (basic)',
                            'Balcony cleaning',
                        ],
                    ],
                    [
                        'name'     => 'Cleaning Types',
                        'sort_order' => 6,
                        'input_type'=> 'radio',
                        'items'    => [
                            'General Cleaning',
                            'Deep Cleaning'
                        ],
                    ],
                ];
 
                foreach ($serviceGroups as $group) {
 
                    $masterService = MasterService::firstOrCreate(
                        [
                            'name'                => $group['name'],
                            'service_category_id' => $serviceCategory->id,
                            'type'                => 'service',
                            'input_type'          => $group['input_type']
                        ],
                        [
                            'description' => $group['name'] . ' service',
                            'is_default'  => 1,
                            'status'      => 1
                        ]
                    );
 
                    foreach ($group['items'] as $index => $itemName) {
                        MasterServiceItem::firstOrCreate(
                            [
                                'name'              => $itemName,
                                'master_service_id' => $masterService->id,
                            ],
                            [
                                'description' => $itemName . ' service item',
                                'status'      => 1,
                                'sort_order'  => $index + 1,
                            ]
                        );
                    }
                }
 
                // -------------------------------------------------------
                // 3. MATERIALS  →  material_types table
                // -------------------------------------------------------
                $materials = [
                    [
                        'name'       => 'Provider Should Bring Equipment',
                    ],
                    [
                        'name'       => 'I Have Equipment Available',
                    ],
                ];
 
                foreach ($materials as $material) {
                    MaterialType::firstOrCreate(
                        [
                            'name'                => $material['name'],
                            'service_category_id' => $serviceCategory->id,
                        ],
                        [
                            'status'      => 1,
                        ]
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | DEFAULT SERVICES FOR GENERAL HOME HELP
            |--------------------------------------------------------------------------
            */

            if ($serviceCategory->name === 'General-In Home Help') {

                $defaultServices = [
                    'Cooking',
                    'Cleaning',
                    'Laundry',
                    'Housekeeping',
                    'Lawn Care'
                ];

                foreach ($defaultServices as $serviceName) {

                    MasterService::firstOrCreate(
                        [
                            'name' => $serviceName,
                            'service_category_id' => $serviceCategory->id
                        ],
                        [
                            'description' => $serviceName . ' service',
                            'is_default' => 1,
                            'status' => MasterService::ACTIVE,
                            'type' => 'service'
                        ]
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | DEFAULT SUBJECTS FOR HOME TUTORING
            |--------------------------------------------------------------------------
            */

            if ($serviceCategory->name === 'Home Tutoring') {

                $academicSubjects = [
                    'Math',
                    'Science',
                    'English'
                ];

                // Academic Subjects
                foreach ($academicSubjects as $subject) {

                    MasterService::firstOrCreate(
                        [
                            'name' => $subject,
                            'service_category_id' => $serviceCategory->id,
                            'subject_type' => 1
                        ],
                        [
                            'description' => $subject . ' subject',
                            'is_default' => 1,
                            'status' => MasterService::ACTIVE,
                            'type' => 'subject'
                        ]
                    );
                }

                // Non Academic Subjects
                $nonAcademicSubjects = [
                    'Music',
                    'Guitar',
                    'Piano',
                    'Dance',
                    'Coding'
                ];

                foreach ($nonAcademicSubjects as $subject) {

                    MasterService::firstOrCreate(
                        [
                            'name' => $subject,
                            'service_category_id' => $serviceCategory->id,
                            'subject_type' => 2
                        ],
                        [
                            'description' => $subject . ' subject',
                            'is_default' => 1,
                            'status' => MasterService::ACTIVE,
                            'type' => 'subject'
                        ]
                    );
                }
            }
        }
    }
}