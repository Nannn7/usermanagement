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

class PositionsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminRole;
    protected $position;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permission group first
        $permissionGroup = PermissionGroup::create([
            'name' => 'usermanagement',
            'slug' => 'usermanagement'
        ]);

        // Create permissions with permission_group_id
        Permission::create([
            'name' => 'usermanagement.create',
            'guard_name' => 'web',
            'permission_group_id' => $permissionGroup->id
        ]);
        Permission::create([
            'name' => 'usermanagement.read',
            'guard_name' => 'web',
            'permission_group_id' => $permissionGroup->id
        ]);
        Permission::create([
            'name' => 'usermanagement.update',
            'guard_name' => 'web',
            'permission_group_id' => $permissionGroup->id
        ]);
        Permission::create([
            'name' => 'usermanagement.delete',
            'guard_name' => 'web',
            'permission_group_id' => $permissionGroup->id
        ]);
        Permission::create([
            'name' => 'usermanagement.export',
            'guard_name' => 'web',
            'permission_group_id' => $permissionGroup->id
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
    }

    #[Test]
    public function user_with_permission_can_view_positions_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('users.positions.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_without_permission_cannot_view_positions_index()
    {
        // Create a role without permissions
        $role = Role::create(['name' => 'viewer', 'guard_name' => 'web']);

        // Create a user with the viewer role
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->get(route('users.positions.index'));

        $response->assertStatus(403);
    }

    #[Test]
    public function user_with_permission_can_create_position()
    {
        $response = $this->actingAs($this->user)
            ->get(route('users.positions.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function create_position_form_prefills_next_code()
<<<<<<< HEAD
=======
    {
        Position::create([
            'code' => '018',
            'name' => 'Latest Position',
            'level' => 9
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('users.positions.create'));

        $response->assertStatus(200);
        $response->assertSee('value="019"', false);
    }

    #[Test]
    public function user_without_permission_cannot_create_position()
>>>>>>> b221050f45210fa2b4011fc6874d69ea79756aa8
    {
        Position::create([
            'code' => '018',
            'name' => 'Latest Position',
            'level' => 9
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('users.positions.create'));

        $response->assertStatus(200);
        $response->assertSee('value="019"', false);
    }

    #[Test]
    public function user_without_permission_cannot_create_position()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->get(route('users.positions.create'));

        $response->assertStatus(403);
    }

    #[Test]
    public function user_with_permission_can_store_position()
    {
        $positionData = [
            'code' => 'NEW',
            'name' => 'New Position',
            'level' => 2
        ];

        $response = $this->actingAs($this->user)
            ->post(route('users.positions.store'), $positionData);

        $response->assertRedirect(route('users.positions.index'));
        $this->assertDatabaseHas('positions', $positionData);
    }

    #[Test]
    public function user_without_permission_cannot_store_position()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        $positionData = [
            'code' => 'NEW',
            'name' => 'New Position',
            'level' => 2
        ];

        $response = $this->actingAs($user)
            ->post(route('users.positions.store'), $positionData);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('positions', $positionData);
    }

    #[Test]
    public function user_with_permission_can_edit_position()
    {
        $response = $this->actingAs($this->user)
            ->get(route('users.positions.edit', $this->position->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_without_permission_cannot_edit_position()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->get(route('users.positions.edit', $this->position->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function user_with_permission_can_update_position()
    {
        $updatedData = [
            'code' => 'UPD',
            'name' => 'Updated Position',
            'level' => 3
        ];

        $response = $this->actingAs($this->user)
            ->put(route('users.positions.update', $this->position->id), $updatedData);

        $response->assertRedirect(route('users.positions.index'));
        $this->assertDatabaseHas('positions', $updatedData);
    }

    #[Test]
    public function user_without_permission_cannot_update_position()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        $updatedData = [
            'code' => 'UPD',
            'name' => 'Updated Position',
            'level' => 3
        ];

        $response = $this->actingAs($user)
            ->put(route('users.positions.update', $this->position->id), $updatedData);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('positions', $updatedData);
    }

    #[Test]
    public function user_with_permission_can_delete_position()
    {
        $response = $this->actingAs($this->user)
            ->delete(route('users.positions.destroy', $this->position->id));

        $response->assertRedirect(route('users.positions.index'));
        $this->assertSoftDeleted($this->position);
    }

    #[Test]
    public function user_with_permission_can_delete_position_via_ajax()
<<<<<<< HEAD
=======
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->delete(route('users.positions.destroy', $this->position->id));

        $response->assertOk();
        $response->assertJson([
            'message' => 'Position deleted successfully.',
            'success' => true,
        ]);
        $this->assertSoftDeleted($this->position);
    }

    #[Test]
    public function user_without_permission_cannot_delete_position()
>>>>>>> b221050f45210fa2b4011fc6874d69ea79756aa8
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->delete(route('users.positions.destroy', $this->position->id));

        $response->assertOk();
        $response->assertJson([
            'message' => 'Position deleted successfully.',
            'success' => true,
        ]);
        $this->assertSoftDeleted($this->position);
    }

    #[Test]
    public function user_without_permission_cannot_delete_position()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->delete(route('users.positions.destroy', $this->position->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('positions', ['id' => $this->position->id, 'deleted_at' => null]);
    }

    #[Test]
    public function user_with_permission_can_access_datatables_data()
    {
        $response = $this->actingAs($this->user)
            ->get(route('users.positions.datatables'));

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
            ->get(route('users.positions.datatables'));

        $response->assertStatus(403);
    }

    #[Test]
    public function user_with_permission_can_export_positions()
    {
        $response = $this->actingAs($this->user)
            ->get(route('users.positions.export'));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_without_permission_cannot_export_positions()
    {
        // Create a role with only read permission
        $role = Role::create(['name' => 'reader', 'guard_name' => 'web']);
        $role->givePermissionTo('usermanagement.read');

        // Create a user with the reader role
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->get(route('users.positions.export'));

        $response->assertStatus(403);
    }

    #[Test]
    public function cannot_delete_position_if_it_has_associated_roles()
    {
        // Create a role associated with the position
        $role = Role::create([
            'name' => 'Position-Linked Role',
            'guard_name' => 'web',
            'position_id' => $this->position->id
        ]);

        // Attempt to delete the position
        $response = $this->actingAs($this->user)
            ->delete(route('users.positions.destroy', $this->position->id));

        // Assert that the request is redirected back with an error message
        $response->assertRedirect(route('users.positions.index'));
        $response->assertSessionHas('error');
        
        // Assert that the position still exists in the database (not deleted)
        $this->assertDatabaseHas('positions', [
            'id' => $this->position->id,
            'deleted_at' => null
        ]);
    }
}
