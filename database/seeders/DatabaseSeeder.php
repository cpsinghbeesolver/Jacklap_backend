<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // User::factory(10)->create();

    $this->call([
      RoleTableSeeder::class,
      PermissionTableSeeder::class,
      CreateAdminUserSeeder::class,
      ServiceCategorySeeder::class,
      LicenseTypesSeeder::class,
      ServiceUseCaseSeeder::class,
      IDSeeder::class,
      TransmissionTypeSeeder::class,
      SalonServiceSeeder::class
    ]);
  }
}
