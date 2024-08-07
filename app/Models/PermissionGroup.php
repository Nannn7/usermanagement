<?php

    namespace Modules\Usermanagement\Models;

    use Spatie\Permission\Models\Role;

    class PermissionGroup extends Base
    {

        protected $fillable = [
            'name',
            'slug'
        ];

        /**
         * Retrieves all permissions associated with a given permission group ID.
         *
         * @param int $id The ID of the permission group.
         *
         * @return \Illuminate\Database\Eloquent\Collection The collection of permissions.
         */
        public static function getpermissionsByGroupId($id)
        {
            return Permission::where('permission_group_id', $id)->get();
        }

        /**
         * Returns a relationship instance for the Permission model.
         *
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function permission()
        {
            return $this->hasMany(Permission::class);
        }

        /**
         * Retrieves the roles associated with a given permission group.
         *
         * @param object $group The permission group object.
         *
         * @return array The array of roles associated with the permission group.
         */
        public function roles($group)
        {
            $permission = Permission::where('permission_group_id', $group->id)->first();

            $data  = [];
            $roles = Role::all();

            foreach ($roles as $role) {
                if ($role->hasPermissionTo($permission->name)) {
                    array_push($data, $role);
                }
            }

            return $data;
        }

    }
