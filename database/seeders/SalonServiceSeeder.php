<?php

namespace Database\Seeders;

use App\Models\MasterService;
use App\Models\MasterServiceItem;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class SalonServiceSeeder extends Seeder
{
    public function run(): void
    {
        $salon = ServiceCategory::where('id',2)->orWhere('slug', 'in-home-on-call-salon')->first();

        if (!$salon) {
            $this->command->warn('Salon service category not found. Run ServiceCategorySeeder first.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | HELPER
        | subject_type: 1=academic, 2=non_academic, 3=Male, 4=Female, 5=Both
        |--------------------------------------------------------------------------
        */
        $createGroup = function (
            string $name,
            string $type,
            int    $subjectType,
            int    $sortOrder,
            array  $items,
            bool   $isDefault = true
        ) use ($salon) {
            $master = MasterService::firstOrCreate(
                [
                    'name'                => $name,
                    'service_category_id' => $salon->id,
                    'type'                => $type,
                    'subject_type'        => $subjectType,
                ],
                [
                    'description' => $name,
                    'is_default'  => $isDefault,
                    'status'      => MasterService::ACTIVE,
                    'sort_order'  => $sortOrder,
                ]
            );

            foreach ($items as $index => $item) {
                $itemName   = is_array($item) ? $item['name']           : $item;
                $isOptional = is_array($item) ? ($item['optional'] ?? false) : false;

                MasterServiceItem::firstOrCreate(
                    [
                        'name'              => $itemName,
                        'master_service_id' => $master->id,
                    ],
                    [
                        'description' => $itemName,
                        'is_optional' => $isOptional,
                        'status'      => 1,
                        'sort_order'  => $index + 1,
                    ]
                );
            }
        };

        /*
        |--------------------------------------------------------------------------
        | MALE SERVICES  (subject_type = 3)
        |--------------------------------------------------------------------------
        */

        // 2.1 Hair Services
        $createGroup('Hair Services', 'service', 3, 1, [
            'Haircut (Basic / Premium / Fade / Taper)',
            'Hair Wash & Styling',
            'Hair Coloring (Men)',
            'Head Shave',
        ]);

        // 2.2 Beard & Grooming
        $createGroup('Beard & Grooming', 'service', 3, 2, [
            'Beard Trim',
            'Beard Styling',
            'Full Beard Grooming',
            'Razor Shave',
            'Hot Towel Shave'
        ]);

        // 2.3 Skin & Face Care
        $createGroup('Skin & Face Care', 'service', 3, 3, [
            'Facial (Basic / Advanced)',
            'Cleanup',
            'Detan Treatment',
        ]);

        // 2.4 Premium Services
        $createGroup('Premium Services', 'service', 3, 4, [
            'Hair + Beard Combo',
            'Luxury Grooming Session',
            'Head Massage',
        ]);

        // 3. Specializations
        $createGroup('Specializations', 'specialization', 3, 5, [
            'Fade Specialist',
            'Beard Styling Expert',
            'Luxury Grooming',
            'Event Grooming (Wedding / Party)',
        ]);

        // 4.1 Basic Grooming Package
        $createGroup('Basic Grooming Package', 'package', 3, 6, [
            'Haircut',
            'Beard Trim',
        ]);

        // 4.2 Premium Grooming Package
        $createGroup('Premium Grooming Package', 'package', 3, 7, [
            'Haircut',
            'Beard Styling',
            'Facial',
        ]);

        // 4.3 Wedding Groom Package
        $createGroup('Wedding Groom Package', 'package', 3, 8, [
            'Premium Haircut',
            'Beard Styling',
            'Facial',
            'Styling Consultation',
        ]);

        // 4.4 Group Grooming Package
        $createGroup('Group Grooming Package', 'package', 3, 9, [
            'Multiple Clients (Events / Weddings)',
        ]);

        // Products
        $createGroup('Products Used', 'product', 3, 10, [
            'Wahl',
            'Andis',
            'American Crew',
            'Babyliss',
            'Proraso',
        ]);

        /*
        |--------------------------------------------------------------------------
        | FEMALE SERVICES  (subject_type = 4)
        |--------------------------------------------------------------------------
        */

        // 2.1 Hair Services
        $createGroup('Hair Services', 'service', 4, 1, [
            'Haircut (Men/Women/Kids)',
            'Hair Styling (Blow Dry, Straightening, Curling)',
            'Hair Spa',
            'Hair Coloring (Full, Root Touch-up, Highlights, Balayage)',
            'Keratin Treatment',
            'Hair Smoothening/Rebonding'
        ]);

        // 2.2 Makeup Services
        $createGroup('Makeup Services', 'service', 4, 2, [
            'Basic Makeup',
            'Party Makeup',
            'Engagement Makeup',
            'Bridal Makeup',
            'HD Makeup',
            'Airbrush Makeup'
        ]);

        // 2.3 Skin & Facial Services
        $createGroup('Skin & Facial Services', 'service', 4, 3, [
            'Cleanup',
            'Basic Facial',
            'Advanced Facial (Gold, Diamond, Hydrating)',
            'Bleach',
            'Detan Treatment'
        ]);

        // 2.4 Nail Services
        $createGroup('Nail Services', 'service', 4, 4, [
            'Manicure',
            'Pedicure',
            'Gel Nails',
            'Nail Art',
        ]);

        // 2.5 Grooming Services
        $createGroup('Grooming Services', 'service', 4, 5, [
            'Threading',
            'Eyebrow Shaping',
            'Upper Lip / Face Wax',
            'Waxing (Full Body / Partial)'
        ]);

        // 3. Specializations
        $createGroup('Specializations', 'specialization', 4, 6, [
            'Bridal Makeup',
            'Party Makeup',
            'Ramp/Show Makeup',
            'Celebrity/Professional Styling',
            'Group Makeup Services',
        ]);

        // 4.1 Bridal Package
        $createGroup('Bridal Package', 'package', 4, 7, [
            ['name' => 'Bridal Makeup',       'optional' => false],
            ['name' => 'Hairstyling',         'optional' => false],
            ['name' => 'Draping',             'optional' => false],
            ['name' => 'Touch-up Kit',        'optional' => false],
            ['name' => 'Pre-Bridal Services', 'optional' => true],
        ]);

        // 4.2 Party Package
        $createGroup('Party Package', 'package', 4, 8, [
            'Makeup + Hairstyling Combo',
            'Quick Grooming',
        ]);

        // 4.3 Group Booking Package
        $createGroup('Group Booking Package', 'package', 4, 9, [
            'Discounted Group Makeup (3-5 People)',
        ]);

        // 4.4 Event / Ramp Show Package
        $createGroup('Event / Ramp Show Package', 'package', 4, 10, [
            'Bulk Service for Events',
        ]);

        // Products
        $createGroup('Products Used', 'product', 4, 11, [
            'MAC',
            'Huda Beauty',
            'Lakme',
            'Maybelline',
            'NYX',
        ]);

        $this->command->info('Salon services seeded successfully.');
    }
}