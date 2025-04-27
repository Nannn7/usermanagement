<?php

    namespace Modules\Usermanagement\Database\Seeders;

    use Faker\Generator;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\Hash;
    use Modules\Usermanagement\Models\User;
    use Spatie\Permission\Models\Role;

    class UsersSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run(Generator $faker)
        {
            $roles = Role::all();

            foreach ($roles as $role) {
                $user = User::create([
                    'name'              => $role->name,
                    'email'             => $role->name . '@ag.co.id',
                    'password'          => Hash::make('bagbag'),
                    'branch_id'         => 1,
                    'nik'               => '000000',
                    'email_verified_at' => now(),
                ]);

                $user->assignRole($role);
            }
        }
    }
