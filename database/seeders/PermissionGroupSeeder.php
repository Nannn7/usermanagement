<?php

    namespace Modules\Usermanagement\Database\Seeders;

    use Illuminate\Database\Seeder;
    use Illuminate\Support\Str;
    use Modules\Usermanagement\Models\PermissionGroup;

    class PermissionGroupSeeder extends Seeder
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
                PermissionGroup::updateOrCreate([
                    'name'       => $value['name'],
                    'slug'       => Str::slug($value['name'])
                ]);
            }
        }

        public function data()
        {
            return [
                ['name' => 'usermanagement'],
                ['name' => 'basic-data'],
                ['name' => 'permohonan'],
                ['name' => 'admin'],
                ['name' => 'senior-officer'],
                ['name' => 'penilai'],
                ['name' => 'surveyor']
            ];
        }
    }
