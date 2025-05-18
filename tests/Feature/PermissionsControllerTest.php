<?php

namespace Modules\Usermanagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\Usermanagement\Models\Permission;
use Modules\Usermanagement\Models\PermissionGroup;
use Modules\Usermanagement\Models\Role;
use Modules\Usermanagement\Models\User;
use Illuminate\Support\Facades\Auth;

class PermissionsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $adminRole;
    protected $permissionGroup;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role
        $this->adminRole = Role::create([
            'name' => 'Admin',
            'guard_name' => 'web'
        ]);

        // Create a permission group for usermanagement permissions
        $usermanagementGroup = PermissionGroup::create(['name' => 'Usermanagement']);

        // Create permissions
        $permissions = [
            'usermanagement.create',
            'usermanagement.read',
            'usermanagement.update',
            'usermanagement.delete',
            'usermanagement.export',
            'usermanagement.store',
            'usermanagement.edit',
            'usermanagement.view',
            'usermanagement.restore'
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
                'permission_group_id' => $usermanagementGroup->id
            ]);
        }

        // Assign permissions to admin role
        $this->adminRole->givePermissionTo($permissions);

        // Create user with admin role
        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);

        $this->user->assignRole($this->adminRole);

        // Create a test permission group
        $this->permissionGroup = PermissionGroup::create(['name' => 'Test Group']);
    }

    /**
     * Test user with permission can view permissions index.
     */
    public function test_user_with_permission_can_view_permissions_index(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('users.permissions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('usermanagement::permissions.index');
    }

    /**
     * Test user without permission cannot view permissions index.
     */
    public function test_user_without_permission_cannot_view_permissions_index(): void
    {
        // Create user without permissions
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password')
        ]);

        $this->actingAs($user);

        $response = $this->get(route('users.permissions.index'));

        $response->assertStatus(403);
    }

    /**
     * Test user with permission can create permission.
     */
    public function test_user_with_permission_can_create_permission(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('users.permissions.create'));

        $response->assertStatus(200);
        $response->assertViewIs('usermanagement::permissions.create');
    }

    /**
     * Test user without permission cannot create permission.
     */
    public function test_user_without_permission_cannot_create_permission(): void
    {
        // Create user without permissions
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user2@example.com',
            'password' => bcrypt('password')
        ]);

        $this->actingAs($user);

        $response = $this->get(route('users.permissions.create'));

        $response->assertStatus(403);
    }

    /**
     * Test user with permission can store permission.
     */
    public function test_user_with_permission_can_store_permission(): void
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 'TestPermission'
        ];

        $response = $this->post(route('users.permissions.store'), $data);

        $response->assertRedirect(route('users.permissions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('permission_groups', ['name' => 'TestPermission']);

        // Check if all the required permissions were created
        $this->assertDatabaseHas('permissions', ['name' => 'testpermission.create']);
        $this->assertDatabaseHas('permissions', ['name' => 'testpermission.read']);
        $this->assertDatabaseHas('permissions', ['name' => 'testpermission.update']);
        $this->assertDatabaseHas('permissions', ['name' => 'testpermission.delete']);
        $this->assertDatabaseHas('permissions', ['name' => 'testpermission.export']);
        $this->assertDatabaseHas('permissions', ['name' => 'testpermission.authorize']);
        $this->assertDatabaseHas('permissions', ['name' => 'testpermission.report']);
    }

    /**
     * Test user without permission cannot store permission.
     */
    public function test_user_without_permission_cannot_store_permission(): void
    {
        // Create user without permissions
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user3@example.com',
            'password' => bcrypt('password')
        ]);

        $this->actingAs($user);

        $data = [
            'name' => 'TestPermission2'
        ];

        $response = $this->post(route('users.permissions.store'), $data);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('permission_groups', ['name' => 'TestPermission2']);
    }

    /**
     * Test user with permission can edit permission.
     */
    public function test_user_with_permission_can_edit_permission(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('users.permissions.edit', $this->permissionGroup->id));

        $response->assertStatus(200);
        $response->assertViewIs('usermanagement::permissions.create');
        $response->assertViewHas('permission', $this->permissionGroup);
    }

    /**
     * Test user without permission cannot edit permission.
     */
    public function test_user_without_permission_cannot_edit_permission(): void
    {
        // Create user without permissions
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user4@example.com',
            'password' => bcrypt('password')
        ]);

        $this->actingAs($user);

        $response = $this->get(route('users.permissions.edit', $this->permissionGroup->id));

        $response->assertStatus(403);
    }

    /**
     * Test user with permission can update permission.
     */
    public function test_user_with_permission_can_update_permission(): void
    {
        $this->actingAs($this->user);

        // Create permissions for the test group
        $permissions = [
            'test group.create',
            'test group.read',
            'test group.update',
            'test group.delete',
            'test group.export',
            'test group.authorize',
            'test group.report'
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
                'permission_group_id' => $this->permissionGroup->id
            ]);
        }

        $data = [
            'name' => 'Updated Group'
        ];

        $response = $this->put(route('users.permissions.update', $this->permissionGroup->id), $data);

        $response->assertRedirect(route('users.permissions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('permission_groups', [
            'id' => $this->permissionGroup->id,
            'name' => 'Updated Group'
        ]);

        // Check if all the permissions were updated
        $this->assertDatabaseHas('permissions', ['name' => 'updated group.create']);
        $this->assertDatabaseHas('permissions', ['name' => 'updated group.read']);
        $this->assertDatabaseHas('permissions', ['name' => 'updated group.update']);
        $this->assertDatabaseHas('permissions', ['name' => 'updated group.delete']);
        $this->assertDatabaseHas('permissions', ['name' => 'updated group.export']);
        $this->assertDatabaseHas('permissions', ['name' => 'updated group.authorize']);
        $this->assertDatabaseHas('permissions', ['name' => 'updated group.report']);
    }

    /**
     * Test user without permission cannot update permission.
     */
    public function test_user_without_permission_cannot_update_permission(): void
    {
        // Create user without permissions
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user5@example.com',
            'password' => bcrypt('password')
        ]);

        $this->actingAs($user);

        $data = [
            'name' => 'Should Not Update'
        ];

        $response = $this->put(route('users.permissions.update', $this->permissionGroup->id), $data);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('permission_groups', [
            'id' => $this->permissionGroup->id,
            'name' => 'Should Not Update'
        ]);
    }

    /**
     * Test user with permission can delete permission.
     */
    public function test_user_with_permission_can_delete_permission(): void
    {
        $this->actingAs($this->user);

        $response = $this->delete(route('users.permissions.destroy', $this->permissionGroup->id));

        $response->assertJson([
            'message' => 'Permission deleted successfully.',
            'success' => true
        ]);

        $this->assertSoftDeleted('permission_groups', ['id' => $this->permissionGroup->id]);
    }

    /**
     * Test user without permission cannot delete permission.
     */
    public function test_user_without_permission_cannot_delete_permission(): void
    {
        // Create user without permissions
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user6@example.com',
            'password' => bcrypt('password')
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('users.permissions.destroy', $this->permissionGroup->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('permission_groups', ['id' => $this->permissionGroup->id, 'deleted_at' => null]);
    }

    /**
     * Test user with permission can restore permission.
     */
    public function test_user_with_permission_can_restore_permission(): void
    {
        $this->actingAs($this->user);

        // First delete the permission group
        $this->permissionGroup->delete();
        $this->assertSoftDeleted('permission_groups', ['id' => $this->permissionGroup->id]);

        $response = $this->get(route('users.permissions.restore', $this->permissionGroup->id));

        $response->assertRedirect(route('users.permissions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('permission_groups', ['id' => $this->permissionGroup->id, 'deleted_at' => null]);
    }

    /**
     * Test user without permission cannot restore permission.
     */
    public function test_user_without_permission_cannot_restore_permission(): void
    {
        // Create user without permissions
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user7@example.com',
            'password' => bcrypt('password')
        ]);

        // First delete the permission group
        $this->permissionGroup->delete();
        $this->assertSoftDeleted('permission_groups', ['id' => $this->permissionGroup->id]);

        $this->actingAs($user);

        $response = $this->get(route('users.permissions.restore', $this->permissionGroup->id));

        $response->assertStatus(403);
        $this->assertSoftDeleted('permission_groups', ['id' => $this->permissionGroup->id]);
    }

    /**
     * Test user with permission can access datatables data.
     */
    public function test_user_with_permission_can_access_datatables_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('users.permissions.datatables') . '?page=1&size=10');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'pageCount',
            'page',
            'totalCount',
            'data'
        ]);
    }

    /**
     * Test user without permission cannot access datatables data.
     */
    public function test_user_without_permission_cannot_access_datatables_data(): void
    {
        // Create user without permissions
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user8@example.com',
            'password' => bcrypt('password')
        ]);

        $this->actingAs($user);

        $response = $this->getJson(route('users.permissions.datatables') . '?page=1&size=10');

        $response->assertStatus(403);
    }

    /**
     * Test datatables search filters permissions correctly.
     */
    public function test_datatables_search_filters_permissions_correctly(): void
    {
        $this->actingAs($this->user);

        // Create additional permission groups for testing search
        PermissionGroup::create(['name' => 'SearchTest1']);
        PermissionGroup::create(['name' => 'SearchTest2']);
        PermissionGroup::create(['name' => 'DifferentName']);

        $response = $this->getJson(route('users.permissions.datatables') . '?page=1&size=10&search=SearchTest');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.name', 'SearchTest1');
        $response->assertJsonPath('data.1.name', 'SearchTest2');
    }

    /**
     * Test datatables sorting works correctly.
     */
    public function test_datatables_sorting_works_correctly(): void
    {
        $this->actingAs($this->user);

        // Create additional permission groups for testing sorting
        PermissionGroup::create(['name' => 'A-Group']);
        PermissionGroup::create(['name' => 'Z-Group']);

        // Test ascending order
        $response = $this->getJson(route('users.permissions.datatables') . '?page=1&size=10&sortField=name&sortOrder=asc');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', 'A-Group');

        // Test descending order
        $response = $this->getJson(route('users.permissions.datatables') . '?page=1&size=10&sortField=name&sortOrder=desc');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', 'Z-Group');
    }

    /**
     * Test export functionality.
     */
    public function test_export_functionality(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('users.permissions.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
