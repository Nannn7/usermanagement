<?php

    namespace Modules\Usermanagement\Database\Seeders;

    use Illuminate\Database\Seeder;
    use Modules\Usermanagement\Models\PermissionGroup;

    class PermissionGroupSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run(): void
        {
            foreach ($this->data() as $value) {
                $group = PermissionGroup::withTrashed()->updateOrCreate(
                    ['name' => $value['name']],
                    ['slug' => $value['slug']]
                );

                if ($group->trashed()) {
                    $group->restore();
                }
            }
        }

        public function data(): array
        {
            return [
                ['name' => 'usermanagement', 'slug' => 'usermanagement'],
            ];
        }
    }
