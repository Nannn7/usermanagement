<?php

namespace Modules\Usermanagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Usermanagement\Models\Permission;
use Modules\Usermanagement\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->data() as $roleName => $permissionNames) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if (!$role) {
                continue;
            }

            if ($roleName === 'administrator') {
                $permissionNames = Permission::query()
                    ->where('guard_name', 'web')
                    ->pluck('name')
                    ->all();
            }

            $role->syncPermissions($permissionNames);
        }
    }

    public function data(): array
    {
        return [
            'administrator' => [],
            'maker' => [
                'corsec.create',
                'corsec.delete',
                'corsec.export',
                'corsec.read',
                'corsec.report',
                'corsec.update',
            ],
            'checker' => [
                'corsec.authorize',
                'corsec.export',
                'corsec.read',
                'corsec.report',
                'corsec.restore',
                'corsec.update',
            ],
            'approver' => [
                'corsec.authorize',
                'corsec.read',
                'corsec.report',
                'corsec.update',
            ],
            'viewer' => [
                'corsec.read',
                'corsec.update',
            ],
        ];
    }
}
