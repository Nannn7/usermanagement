<?php

namespace Modules\Usermanagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Usermanagement\Models\User;
use Modules\Usermanagement\Database\Seeders\RolesSeeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleSeeder = new RolesSeeder();
        $rolesData = $roleSeeder->data();

        foreach ($rolesData as $roleData) {
            if ($roleData['name'] === 'administrator') {
                $user = User::firstOrCreate(
                    ['email' => $roleData['name'] . '@ag.co.id'],
                    [
                        'name' => $roleData['name'],
                        'password' => Hash::make('bagbag'),
                        'branch_id' => 1,
                        'nik' => '000000',
                        'email_verified_at' => now(),
                    ]
                );

                $role = \Spatie\Permission\Models\Role::firstOrCreate(
                    ['name' => $roleData['name']],
                    ['guard_name' => 'web']
                );

                $user->assignRole($role);
            }
        }
    }
}
