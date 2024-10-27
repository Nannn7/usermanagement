<?php

    namespace Modules\Usermanagement\Database\Seeders;

    use Illuminate\Database\Seeder;
    use Modules\Usermanagement\Models\PermissionGroup;
    use Spatie\Permission\Models\Permission;
    use Spatie\Permission\Models\Role;

    class PermissionsSeeder extends Seeder
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
                $permission = Permission::updateOrCreate([
                    'name'       => $value['name'],
                    'guard_name' => 'web' // or 'api
                ], [
                    'permission_group_id' => $value['group']
                ]);

                $roles = Role::all();
                foreach ($roles as $role) {
                    $role->givePermissionTo($permission);
                }

            }
        }

        public function data()
        {
            $data = [];
            // list of model permission
            $groups = PermissionGroup::all();

            foreach ($groups as $group) {
                foreach ($this->crudActions($group->name) as $action) {
                    $data[] = ['name' => $action, 'group' => $group->id];
                }
            }

            return $data;
        }

        public function crudActions($name)
        {
            $actions = [];
            // list of permission actions
            $crud = ['create', 'read', 'update', 'delete','export', 'authorize', 'report'];


            foreach ($crud as $value) {
                $actions[] = $name . '.' . $value;
            }

            return $actions;
        }
    }
