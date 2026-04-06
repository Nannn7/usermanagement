<?php

namespace Modules\Usermanagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Usermanagement\Exports\RolesExport;
use Modules\Usermanagement\Http\Requests\RoleRequest;
use Modules\Usermanagement\Models\Permission;
use Modules\Usermanagement\Models\PermissionGroup;
use Modules\Usermanagement\Models\Position;
use Modules\Usermanagement\Models\Role;
use Exception;

/**
 * Class RolesController
 *
 * This controller is responsible for managing user roles within the application.
 *
 * @package Modules\Usermanagement\Http\Controllers
 */
class RolesController extends Controller
{
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected $user;

    /**
     * UsersController constructor.
     *
     * Initializes the user property with the authenticated user.
     */
    public function __construct()
    {
        // Mengatur middleware auth
        $this->middleware('auth');

        // Mengatur user setelah middleware auth dijalankan
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        // Check if the authenticated user has the required permission to view roles
        if (is_null($this->user) || !$this->user->can('usermanagement.read')) {
            abort(403, 'Sorry! You are not allowed to view roles.');
        }

        // Return the view for displaying the roles
        return view('usermanagement::roles.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(RoleRequest $request)
    {
        // Check if the authenticated user has the required permission to store roles
        if (is_null($this->user) || !$this->user->can('usermanagement.create')) {
            abort(403, 'Sorry! You are not allowed to store roles.');
        }

        $validated = $request->validated();

        if ($validated) {
            try {
                // If no errors, save the role to the database
                $role = Role::create($validated);

                $permissions = $request->input('permissions', []);
                // $permissions = Permission::whereIn('id', $permissions)->pluck('name')->toArray();
                if (!empty($permissions)) {
                    $permissions = Permission::whereIn('id', $permissions)->pluck('name')->toArray();
                    // $role = Role::find($role->id);
                    try {
                        $role->syncPermissions($permissions);
                    } catch (Exception $e) {
                        return redirect()
                            ->route('users.roles.index')
                            ->with('error', 'Failed to sync permissions: ' . $e->getMessage());
                    }
                }

                // Redirect back to the roles index with a success message
                return redirect()->route('users.roles.index')->with('success', 'Role created successfully.');
            } catch (Exception $e) {
                // Redirect back to the roles index with an error message
                return redirect()
                    ->route('users.roles.index')
                    ->with('error', 'Failed to create role. Please try again.');
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        // Check if the authenticated user has the required permission to create roles
        if (is_null($this->user) || !$this->user->can('usermanagement.create')) {
            abort(403, 'Sorry! You are not allowed to create roles.');
        }

        $permissiongroups = PermissionGroup::query()
            ->with(['permission:id,name,permission_group_id'])
            ->orderBy('name')
            ->get(['id', 'name']);
        $positions = Position::query()
            ->orderBy('level')
            ->orderBy('name')
            ->get(['id', 'name', 'level']);
        // Return the view for creating a new role
        return view('usermanagement::roles.create', compact('permissiongroups', 'positions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($id)
    {
        // Check if the authenticated user has the required permission to edit roles
        if (is_null($this->user) || !$this->user->can('usermanagement.update')) {
            abort(403, 'Sorry! You are not allowed to edit roles.');
        }

        // Fetch the specified role from the database
        $role = Role::with('permissions:id,name')->findOrFail($id);
        $permissiongroups = PermissionGroup::query()
            ->with(['permission:id,name,permission_group_id'])
            ->orderBy('name')
            ->get(['id', 'name']);
        $positions = Position::query()
            ->orderBy('level')
            ->orderBy('name')
            ->get(['id', 'name', 'level']);
        // Return the view for editing the role
        return view('usermanagement::roles.create', compact('role', 'permissiongroups', 'positions'));
    }


    /**
     * Update the specified role in storage.
     *
     * @param \Modules\Usermanagement\Http\Requests\RoleRequest $request The request object containing the role data.
     * @param int                                               $id      The unique identifier of the role to be updated.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back to the roles index with a success message upon successful update.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException If the authenticated user does not have the required permission to update roles.
     */
    public function update(RoleRequest $request, $id)
    {
        // Check if the authenticated user has the required permission to update roles
        if (is_null($this->user) || !$this->user->can('usermanagement.update')) {
            abort(403, 'Sorry! You are not allowed to update roles.');
        }

        $validated = $request->validated();
        if ($validated) {
            try {
                // If no errors, update the role in the database
                $role = Role::findorFail($id);
                $role->update($validated);

                $permissions = $request->input('permissions', []);
                $permissions = !empty($permissions)
                    ? Permission::whereIn('id', $permissions)->pluck('name')->toArray()
                    : [];

                try {
                    $role->syncPermissions($permissions);
                } catch (Exception $e) {
                    return redirect()
                        ->route('users.roles.index')
                        ->with('error', 'Failed to sync permissions: ' . $e->getMessage());
                }

                // Redirect back to the roles index with a success message
                return redirect()->route('users.roles.index')->with('success', 'Role updated successfully.');
            } catch (Exception $e) {
                // Redirect back to the roles index with an error message
                return redirect()
                    ->route('users.roles.index')
                    ->with('error', 'Failed to update role. Please try again.');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function destroy($id)
    {
        // Check if the authenticated user has the required permission to delete currencies
        if (is_null($this->user) || !$this->user->can('usermanagement.delete')) {
            return response()->json(['success' => false, 'message' => 'Sorry! You are not allowed to delete roles.'], 403);
        }

        try {
            // Delete from database
            $currency = Role::findorFail($id);
            $currency->delete();

            return response()->json(['success' => true, 'message' => 'Role deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete role.']);
        }
    }

    /**
     * Restore a deleted role.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function restore($id)
    {
        // Check if the authenticated user has the required permission to restore roles
        if (is_null($this->user) || !$this->user->can('usermanagement.restore')) {
            abort(403, 'Sorry! You are not allowed to restore roles.');
        }

        // Fetch the specified role from the database
        $role = Role::withTrashed()->find($id);

        // Restore the role
        $role->restore();

        // Redirect back to the roles index with a success message
        return redirect()->route('users.roles.index')->with('success', 'Role restored successfully.');
    }

    /**
     * Process support datatables ajax request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function dataForDatatables(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('usermanagement.read')) {
            return response()->json(['message' => 'Sorry! You are not allowed to view roles.', 'success' => false], 403);
        }

        // Retrieve data from the database
        $query = Role::query();
        $baseQuery = Role::query();

        if (!$this->user->hasRole('administrator')) {
            $query->where('name', '!=', 'administrator');
            $baseQuery->where('name', '!=', 'administrator');
        }

        // Apply search filter if provided
        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('position', function ($query) use ($search) {
                        $query->whereAny(['name', 'level'], 'like', '%' . $search . '%');
                    });
            });
        }

        // Apply sorting if provided
        if ($request->has('sortOrder') && !empty($request->get('sortOrder'))) {
            $order  = strtolower((string) $request->get('sortOrder'));
            $column = (string) $request->get('sortField');

            if (!in_array($order, ['asc', 'desc'], true)) {
                $order = 'asc';
            }

            if ($column === 'position_name') {
                $query->leftJoin('positions', 'roles.position_id', '=', 'positions.id')
                    ->orderBy('positions.name', $order)
                    ->select('roles.*'); // Select only from roles table to avoid column conflicts
            } else if ($column === 'level') {
                $query->leftJoin('positions', 'roles.position_id', '=', 'positions.id')
                    ->orderBy('positions.level', $order)
                    ->select('roles.*'); // Select only from roles table to avoid column conflicts
            } else {
                if ($column === 'name') {
                    $query->orderBy('roles.name', $order);
                } else {
                    $query->orderBy($column, $order);
                }
            }
        } else {
            $query->orderBy('roles.name');
        }

        $totalRecords = $baseQuery->count();
        $filteredRecords = $search !== '' ? (clone $query)->distinct()->count('roles.id') : $totalRecords;
        $page = max((int) $request->get('page', 1), 1);
        $size = max((int) $request->get('size', 10), 1);

        // Get the data for the current page
        $data = $query
            ->with('position:id,name,level')
            ->forPage($page, $size)
            ->get();

        // Calculate the page count - ensure we don't divide by zero
        $pageCount = $size > 0 ? ceil($filteredRecords / $size) : 0;

        // Return the response data as a JSON object
        return response()->json([
            'draw'            => $request->get('draw'),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'pageCount'       => $pageCount,
            'page'            => $page,
            'totalCount'      => $totalRecords,
            'data'            => $data,
        ]);
    }

    public function export()
    {
        if (is_null($this->user) || !$this->user->can('usermanagement.export')) {
            abort(403, 'Sorry! You are not allowed to export roles.');
        }

        return Excel::download(new RolesExport, 'roles.xlsx');
    }
}
