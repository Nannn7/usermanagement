<?php

namespace Modules\Usermanagement\Database\Seeders;

use Illuminate\Database\Seeder;

class UsermanagementDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PermissionGroupSeeder::class,
            PermissionsSeeder::class,
            PositionsSeeder::class,
            RolesSeeder::class,
            RolePermissionSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
