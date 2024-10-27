<?php

    namespace Modules\Usermanagement\Database\Seeders;

    use Illuminate\Database\Seeder;
    use Spatie\Permission\Models\Role;

    class RolesSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run()
        {
            $data = $this->data();

            foreach ($data as $value) {
                Role::create([
                    'name'       => $value['name'],
                    'guard_name' => 'web',
                ]);
            }
        }

        public function data()
        {
            return [
                ['name' => 'administrator']
            ];
        }
    }
