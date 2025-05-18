<?php

namespace Modules\Usermanagement\Tests\Feature;

use Tests\TestCase;
use Modules\Usermanagement\Models\Position;
use Modules\Usermanagement\Models\User;
use Modules\Usermanagement\Models\Role;
use Modules\Usermanagement\Models\Permission;
use Modules\Usermanagement\Models\PermissionGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class RolesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminRole;
    protected $position;
    protected $testRole;
    protected $permissionGroup;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permission group first
        $this->permissionGroup = PermissionGroup::create([
            'name' => 'usermanagement',
            'slug' => 'usermanagement'
        ]);

        // Create permissions with permission_group_id
        Permission::create([
            'name' => 'usermanagement.create',
            'guard_name' => 'web',
            'permission_group_id' => $this->permissionGroup->id
        ]);
        Permission::create([
            'name' => 'usermanagement.read',
            'guard_name' => 'web',
            'permission_group_id' => $this->permissionGroup->id
        ]);
        Permission::create([
            'name' => 'usermanagement.update',
            'guard_name' => 'web',
            'permission_group_id' => $this->permissionGroup->id
        ]);
        Permission::create([
            'name' => 'usermanagement.delete',
            'guard_name' => 'web',
            'permission_group_id' => $this->permissionGroup->id
        ]);
        Permission::create([
            'name' => 'usermanagement.export',
            'guard_name' => 'web',
            'permission_group_id' => $this->permissionGroup->id
        ]);
        Permission::create([
            'name' => 'usermanagement.restore',
            'guard_name' => 'web',
            'permission_group_id' => $this->permissionGroup->id
        ]);

        // Create admin role with all permissions
        $this->adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminRole->givePermissionTo(Permission::all());

        // Create a user with admin role
        $this->user = User::factory()->create();
        $this->user->assignRole($this->adminRole);

        // Create a position for testing
        $this->position = Position::create([
            'code' => 'TEST',
            'name' => 'Test Position',
            'level' => 1
        ]);

        // Create a test role for testing
        $this->testRole = Role::create([
            'name' => 'test-role',
            'guard_name' => 'web',
            'position_id' => $this->position->id
        ]);
        $this->testRole->givePermissionTo('usermanagement.read');
    }

    #[Test]
    public function user_with_permission_can_view_roles_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('users.roles.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_without_permission_cannot_view_roles_index()
    {
        // Create a role without permissions
        $role = Role::create(['name' => 'viewer', 'guard_name' => 'web']);

        // Create a user with the viewer role
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->get(route('users.roles.index'));

        $response->assertStatus(403);
    }

    #[Test]
    public function user_with_permission_can_create_role()
    {
        $response = $this->actingAs($this->user)
            ->get(route('users.roles.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_without_permission_cannot_create_role()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->get(route('users.roles.create'));

        $response->assertStatus(403);
    }

    #[Test]
    public function user_with_permission_can_store_role()
    {
        $permissions = Permission::where('name', 'usermanagement.read')->pluck('id')->toArray();

        $roleData = [
            'name' => 'New Role',
            'guard_name' => 'web',
            'position_id' => $this->position->id,
            'permissions' => $permissions
        ];

        $response = $this->actingAs($this->user)
            ->post(route('users.roles.store'), $roleData);

        $response->assertRedirect(route('users.roles.index'));
        $this->assertDatabaseHas('roles', [
            'name' => 'New Role',
            'position_id' => $this->position->id
        ]);

        // Check if permission was assigned
        $newRole = Role::where('name', 'New Role')->first();
        $this->assertTrue($newRole->hasPermissionTo('usermanagement.read'));
    }

    #[Test]
    public function user_without_permission_cannot_store_role()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        $permissions = Permission::where('name', 'usermanagement.read')->pluck('id')->toArray();

        $roleData = [
            'name' => 'New Role',
            'guard_name' => 'web',
            'position_id' => $this->position->id,
            'permissions' => $permissions
        ];

        $response = $this->actingAs($user)
            ->post(route('users.roles.store'), $roleData);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('roles', ['name' => 'New Role']);
    }

    #[Test]
    public function user_with_permission_can_edit_role()
    {
        $response = $this->actingAs($this->user)
            ->get(route('users.roles.edit', $this->testRole->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_without_permission_cannot_edit_role()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->get(route('users.roles.edit', $this->testRole->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function user_with_permission_can_update_role()
    {
        $permissions = Permission::whereIn('name', ['usermanagement.read', 'usermanagement.update'])->pluck('id')->toArray();

        $updatedData = [
            'name' => 'Updated Role',
            'guard_name' => 'web',
            'position_id' => $this->position->id,
            'permissions' => $permissions
        ];

        $response = $this->actingAs($this->user)
            ->put(route('users.roles.update', $this->testRole->id), $updatedData);

        $response->assertRedirect(route('users.roles.index'));
        $this->assertDatabaseHas('roles', [
            'id' => $this->testRole->id,
            'name' => 'Updated Role',
            'position_id' => $this->position->id
        ]);

        // Check if permissions were updated
        $updatedRole = Role::find($this->testRole->id);
        $this->assertTrue($updatedRole->hasPermissionTo('usermanagement.read'));
        $this->assertTrue($updatedRole->hasPermissionTo('usermanagement.update'));
    }

    #[Test]
    public function user_without_permission_cannot_update_role()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        $permissions = Permission::whereIn('name', ['usermanagement.read', 'usermanagement.update'])->pluck('id')->toArray();

        $updatedData = [
            'name' => 'Updated Role',
            'guard_name' => 'web',
            'position_id' => $this->position->id,
            'permissions' => $permissions
        ];

        $response = $this->actingAs($user)
            ->put(route('users.roles.update', $this->testRole->id), $updatedData);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('roles', [
            'id' => $this->testRole->id,
            'name' => 'Updated Role'
        ]);
    }

    #[Test]
    public function user_with_permission_can_delete_role()
    {
        $response = $this->actingAs($this->user)
            ->delete(route('users.roles.destroy', $this->testRole->id));

        // The destroy method returns JSON response
        $response->assertJson([
            'message' => 'Role deleted successfully.',
            'success' => true
        ]);

        $this->assertSoftDeleted($this->testRole);
    }

    #[Test]
    public function user_without_permission_cannot_delete_role()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->delete(route('users.roles.destroy', $this->testRole->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('roles', [
            'id' => $this->testRole->id,
            'deleted_at' => null
        ]);
    }

    #[Test]
    public function user_with_permission_can_restore_role()
    {
        // First soft delete the role
        $this->testRole->delete();
        $this->assertSoftDeleted($this->testRole);

        $response = $this->actingAs($this->user)
            ->get(route('users.roles.restore', $this->testRole->id));

        $response->assertRedirect(route('users.roles.index'));
        $this->assertDatabaseHas('roles', [
            'id' => $this->testRole->id,
            'deleted_at' => null
        ]);
    }

    #[Test]
    public function user_without_permission_cannot_restore_role()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        // First soft delete the role
        $this->testRole->delete();
        $this->assertSoftDeleted($this->testRole);

        $response = $this->actingAs($user)
            ->get(route('users.roles.restore', $this->testRole->id));

        $response->assertStatus(403);
        $this->assertSoftDeleted($this->testRole);
    }

    #[Test]
    public function user_with_permission_can_access_datatables_data()
    {
        $response = $this->actingAs($this->user)
            ->get(route('users.roles.datatables'));

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

    #[Test]
    public function user_without_permission_cannot_access_datatables_data()
    {
        // Create a role without permissions
        $role = Role::create(['name' => 'viewer', 'guard_name' => 'web']);

        // Create a user with the viewer role
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->get(route('users.roles.datatables'));

        $response->assertStatus(403);
    }

    #[Test]
    public function user_with_permission_can_export_roles()
    {
        $response = $this->actingAs($this->user)
            ->get(route('users.roles.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    #[Test]
    public function datatables_search_filters_roles_correctly()
    {
        // Create additional roles for testing search
        Role::create([
            'name' => 'searchable-role',
            'guard_name' => 'web',
            'position_id' => $this->position->id
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('users.roles.datatables', ['search' => 'searchable']));

        $response->assertStatus(200);
        $responseData = json_decode($response->getContent(), true);

        // Check that the search returned the correct role
        $this->assertGreaterThan(0, $responseData['recordsFiltered']);
        $foundSearchableRole = false;
        foreach ($responseData['data'] as $role) {
            if ($role['name'] === 'searchable-role') {
                $foundSearchableRole = true;
                break;
            }
        }
        $this->assertTrue($foundSearchableRole);
    }

    #[Test]
    public function datatables_sorting_works_correctly()
    {
        // Create additional roles for testing sorting
        Role::create([
            'name' => 'A-role', // Should come first in ascending order
            'guard_name' => 'web',
            'position_id' => $this->position->id
        ]);

        Role::create([
            'name' => 'Z-role', // Should come last in ascending order
            'guard_name' => 'web',
            'position_id' => $this->position->id
        ]);

        // Test ascending order
        $response = $this->actingAs($this->user)
            ->get(route('users.roles.datatables', [
                'sortField' => 'name',
                'sortOrder' => 'asc'
            ]));

        $response->assertStatus(200);
        $responseData = json_decode($response->getContent(), true);

        // Check that the first role is 'A-role'
        $this->assertEquals('A-role', $responseData['data'][0]['name']);

        // Test descending order
        $response = $this->actingAs($this->user)
            ->get(route('users.roles.datatables', [
                'sortField' => 'name',
                'sortOrder' => 'desc'
            ]));

        $response->assertStatus(200);
        $responseData = json_decode($response->getContent(), true);

        // Check that the first role is 'Z-role'
        $this->assertEquals('Z-role', $responseData['data'][0]['name']);
    }
}
