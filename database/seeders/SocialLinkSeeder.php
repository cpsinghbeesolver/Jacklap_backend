<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SocialLink;

class SocialLinkSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            [
                'name' => 'LinkedIn',
                'icon' => 'fa-brands fa-linkedin',
                'url' => '#',
                'sort_order' => 1
            ],
            [
                'name' => 'Instagram',
                'icon' => 'fa-brands fa-instagram',
                'url' => '#',
                'sort_order' => 2
            ],
            [
                'name' => 'Facebook',
                'icon' => 'fa-brands fa-facebook-f',
                'url' => '#',
                'sort_order' => 3
            ],
            [
                'name' => 'Twitter',
                'icon' => 'fa-brands fa-twitter',
                'url' => '#',
                'sort_order' => 4
            ],
        ];

        foreach ($links as $link) {
            SocialLink::firstOrCreate(
                ['name' => $link['name']],
                [
                    'icon' => $link['icon'],
                    'url' => $link['url'],
                    'status' => 1,
                    'sort_order' => $link['sort_order'],
                ]
            );
        }
    }
}