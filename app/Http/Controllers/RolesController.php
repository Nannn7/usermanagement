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
        public $user;

        /**
         * UsersController constructor.
         *
         * Initializes the user property with the authenticated user.
         */
        public function __construct()
        {
            $this->user = Auth::guard('web')->user();
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

            // Fetch all roles from the database
            $roles = Role::all();

            // Return the view for displaying the roles
            return view('usermanagement::roles.index', compact('roles'));
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

                    $permissions = $request->input('permissions');
                    $permissions = Permission::whereIn('id', $permissions)->pluck('name')->toArray();
                    if (!empty($permissions)) {
                        $role = Role::find($role->id);
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

            $permissiongroups = PermissionGroup::all();
            $positions        = Position::all();
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
            $role             = Role::find($id);
            $permissions      = Permission::all();
            $permissiongroups = PermissionGroup::all();
            $positions        = Position::all();
            // Return the view for editing the role
            return view('usermanagement::roles.create', compact('role', 'permissions', 'permissiongroups', 'positions'));
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
                    $role = Role::find($id);
                    $role->update($request->all());

                    $permissions = $request->input('permissions');
                    $permissions = Permission::whereIn('id', $permissions)->pluck('name')->toArray();
                    if (!empty($permissions)) {
                        $role = Role::find($role->id);
                        try {
                            $role->syncPermissions($permissions);
                        } catch (Exception $e) {
                            return redirect()
                                ->route('users.roles.index')
                                ->with('error', 'Failed to sync permissions: ' . $e->getMessage());
                        }

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
                $currency = Role::find($id);
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
                abort(403, 'Sorry! You are not allowed to view users.');
            }

            // Retrieve data from the database
            $query = Role::query();

            // Apply search filter if provided
            if ($request->has('search') && !empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                      ->orWhereHas('position', function ($query) use ($search) {
                          $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                                ->orWhereRaw('CAST(level AS TEXT) LIKE ?', ['%' . $search . '%']);
                      });
                });
            }

            // Apply sorting if provided
            if ($request->has('sortOrder') && !empty($request->get('sortOrder'))) {
                $order  = $request->get('sortOrder');
                $column = $request->get('sortField');

                // Handle sorting for position-related columns
                if ($column === 'position_name') {
                    $query->leftJoin('positions', 'roles.position_id', '=', 'positions.id')
                          ->orderByRaw('LOWER(positions.name) ' . $order)
                          ->select('roles.*'); // Select only from roles table to avoid column conflicts
                } else if ($column === 'level') {
                    $query->leftJoin('positions', 'roles.position_id', '=', 'positions.id')
                          ->orderBy('positions.level', $order)
                          ->select('roles.*'); // Select only from roles table to avoid column conflicts
                } else {
                    // Make sorting case-insensitive for string columns
                    if ($column === 'name') {
                        $query->orderByRaw('LOWER(roles.name) ' . $order);
                    } else {
                        $query->orderBy($column, $order);
                    }
                }
            }

            // Create a copy of the query for counting
            $countQuery = clone $query;

            // Get the total count of records (without joins to avoid duplicates)
            $totalRecords = Role::count();

            // Apply pagination if provided
            if ($request->has('page') && $request->has('size')) {
                $page   = $request->get('page');
                $size   = $request->get('size');
                $offset = ($page - 1) * $size; // Calculate the offset

                $query->skip($offset)->take($size);
            }

            // Get the filtered count of records - use distinct to avoid duplicates from joins
            $filteredRecords = $countQuery->distinct()->count('roles.id');

            // Get the data for the current page
            $roles = $query->with('position')->get();

            // Calculate the page count - ensure we don't divide by zero
            $pageSize = $request->get('size', 10); // Default to 10 if not provided
            $pageCount = $pageSize > 0 ? ceil($totalRecords / $pageSize) : 0;

            // Calculate the current page number
            $currentPage = $request->get('page') ?: 1;

            // Return the response data as a JSON object
            return response()->json([
                'draw'            => $request->get('draw'),
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'pageCount'       => $pageCount,
                'page'            => $currentPage,
                'totalCount'      => $totalRecords,
                'data'            => $roles,
            ]);
        }

        public function export()
        {
            return Excel::download(new RolesExport, 'roles.xlsx');
        }
    }
