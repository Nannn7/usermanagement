<?php

    namespace Modules\Usermanagement\Database\Seeders;

    use Illuminate\Database\Seeder;
    use Modules\Usermanagement\Models\Position;
    use Modules\Usermanagement\Models\Role;

    class RolesSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run(): void
        {
            foreach ($this->data() as $value) {
                $positionId = null;

                if (!empty($value['position_code'])) {
                    $positionId = Position::withTrashed()
                        ->where('code', $value['position_code'])
                        ->value('id');
                }

                $role = Role::withTrashed()->updateOrCreate(
                    [
                        'name' => $value['name'],
                        'guard_name' => $value['guard_name'],
                    ],
                    [
                        'position_id' => $positionId,
                    ]
                );

                if ($role->trashed()) {
                    $role->restore();
                }
            }
        }

        public function data(): array
        {
            return [
                ['name' => 'administrator', 'guard_name' => 'web', 'position_code' => null],
                ['name' => 'maker', 'guard_name' => 'web', 'position_code' => '001'],
                ['name' => 'checker', 'guard_name' => 'web', 'position_code' => '002'],
                ['name' => 'approver', 'guard_name' => 'web', 'position_code' => '003'],
                ['name' => 'viewer', 'guard_name' => 'web', 'position_code' => null],
            ];
        }
    }
