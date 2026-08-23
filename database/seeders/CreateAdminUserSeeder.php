<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Super Admin',
            'password' => bcrypt('123456')]
        );

        $role = Role::where(['name' => 'admin'])->first();

        $permissions = Permission::pluck('id', 'id')->all();

        $role->syncPermissions($permissions);

        $user1->assignRole([$role->id]);

        $user2 = User::firstOrCreate(
            ['email' => 'admin@jacklap.com'],
            ['name' => 'Super Admin',
            'password' => bcrypt('McNG3t3^')]
        );

        $role = Role::where(['name' => 'admin'])->first();

        $permissions = Permission::pluck('id', 'id')->all();

        $role->syncPermissions($permissions);

        $user2->assignRole([$role->id]);
    }
}
