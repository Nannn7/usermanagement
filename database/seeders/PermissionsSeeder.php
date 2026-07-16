<?php

    namespace Modules\Usermanagement\Database\Seeders;

    use Illuminate\Database\Seeder;
    use Modules\Usermanagement\Models\Permission;
    use Modules\Usermanagement\Models\PermissionGroup;

    class PermissionsSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run(): void
        {
            foreach ($this->data() as $value) {
                $permission = Permission::withTrashed()->updateOrCreate(
                    [
                        'name' => $value['name'],
                        'guard_name' => $value['guard_name'],
                    ],
                    [
                        'permission_group_id' => $value['group'],
                    ]
                );

                if ($permission->trashed()) {
                    $permission->restore();
                }
            }
        }

        public function data(): array
        {
            $data = [];
            $groups = PermissionGroup::query()->orderBy('id')->get();

            foreach ($groups as $group) {
                foreach ($this->crudActions($group->name) as $action) {
                    $data[] = [
                        'name' => $action,
                        'guard_name' => 'web',
                        'group' => $group->id,
                    ];
                }
            }

            return $data;
        }

        public function crudActions(string $name): array
        {
            return array_map(
                static fn (string $value): string => $name . '.' . $value,
                ['create', 'read', 'update', 'delete', 'export', 'authorize', 'report', 'restore']
            );
        }
    }
