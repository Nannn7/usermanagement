<?php

    namespace Modules\Usermanagement\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Maatwebsite\Excel\Facades\Excel;
    use Modules\Usermanagement\Exports\PermissionExport;
    use Modules\Usermanagement\Http\Requests\PermissionRequest;
    use Modules\Usermanagement\Models\Permission;
    use Modules\Usermanagement\Models\PermissionGroup;

    /**
     * Class PermissionsController
     *
     * This controller is responsible for managing user permissions within the application.
     *
     * @package Modules\Usermanagement\Http\Controllers
     */
    class PermissionsController extends Controller
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
            // Check if the authenticated user has the required permission to view permissions
            if (is_null($this->user) || !$this->user->can('usermanagement.read')) {
                abort(403, 'Sorry! You are not allowed to view permissions.');
            }

            // Return the view for displaying the permissions
            return view('usermanagement::permissions.index');
        }

        /**
         * Store a newly created resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\RedirectResponse
         * @throws \Illuminate\Auth\Access\AuthorizationException
         */
        public function store(PermissionRequest $request)
        {
            // Check if the authenticated user has the required permission to store permissions
            if (is_null($this->user) || !$this->user->can('usermanagement.create')) {
               abort(403, 'Sorry! You are not allowed to create permissions.');
            }

            $validate = $request->validated();

            if($validate){
                try{
                    $group = PermissionGroup::create($validate);
                    $group_name = strtolower($validate['name']);
                    $data       = [
                        $group_name . '.create',
                        $group_name . '.read',
                        $group_name . '.update',
                        $group_name . '.delete',
                        $group_name . '.export',
                        $group_name . '.authorize',
                        $group_name . '.report',
                        $group_name . '.restore'
                    ];

                    foreach ($data as $permission) {
                        Permission::create(['name' => $permission,'guard_name' => 'web', 'permission_group_id' => $group->id]);
                    }

                    return redirect()->route('users.permissions.index')->with('success', 'Permission created successfully.');
                } catch (\Exception $e){
                    return redirect()->route('users.permissions.index')->with('error', 'Failed to create permission: '.$e->getMessage());
                }
            }


            // Redirect back to the permissions index with a success message
            return redirect()->route('users.permissions.index')->with('success', 'Permission created successfully.');
        }

        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
         * @throws \Illuminate\Auth\Access\AuthorizationException
         */
        public function create()
        {
            // Check if the authenticated user has the required permission to create permissions
            if (is_null($this->user) || !$this->user->can('usermanagement.create')) {
                abort(403, 'Sorry! You are not allowed to create permissions.');
            }

            // Return the view for creating a new role
            return view('usermanagement::permissions.create');
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
            // Check if the authenticated user has the required permission to edit permissions
            if (is_null($this->user) || !$this->user->can('usermanagement.update')) {
                abort(403, 'Sorry! You are not allowed to edit permissions.');
            }

            $permission = PermissionGroup::find($id);

            // Return the view for editing the role
            return view('usermanagement::permissions.create', compact('permission'));
        }


        /**
         * Update the specified role in storage.
         *
         * @param \Modules\Usermanagement\Http\Requests\PermissionRequest $request The request object containing the role data.
         * @param int                                               $id      The unique identifier of the role to be updated.
         *
         * @return \Illuminate\Http\RedirectResponse Redirects back to the permissions index with a success message upon successful update.
         *
         * @throws \Illuminate\Auth\Access\AuthorizationException If the authenticated user does not have the required permission to update permissions.
         */
        public function update(PermissionRequest $request, $id)
        {
            // Check if the authenticated user has the required permission to update permissions
            if (is_null($this->user) || !$this->user->can('usermanagement.update')) {
                abort(403, 'Sorry! You are not allowed to update permissions.');
            }

            $validated = $request->validated();

            if ($validated) {
                try {
                    // Process Data
                    $group       = PermissionGroup::find($id);
                    $group->name = $request->name;

                    if ($group->save()) {
                        $group_name  = strtolower($request->name);
                        $permissions = Permission::where('permission_group_id', $group->id)->get();

                        $data = [
                            $group_name . '.create',
                            $group_name . '.read',
                            $group_name . '.update',
                            $group_name . '.delete',
                            $group_name . '.export',
                            $group_name . '.authorize',
                            $group_name . '.report',
                            $group_name . '.restore'
                        ];

                        $i = 0;
                        foreach ($permissions as $permission) {
                            $permission->name = $data[$i];
                            $permission->save();

                            $i++;
                        }
                    }
                    return redirect()->route('users.permissions.index')->with('success', 'Permission updated successfully.');
                } catch (\Exception $e) {
                    return redirect()->route('users.permissions.index')->with('error', 'Failed to update permission: '.$e->getMessage());
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
            // Check if the authenticated user has the required permission to delete permissions
            if (is_null($this->user) || !$this->user->can('usermanagement.delete')) {
                return response()->json(['message' => 'Sorry! You are not allowed to delete permissions.','success' => false]);
            }

            $permission = PermissionGroup::find($id);
            if (!is_null($permission)) {
                if ($permission->delete()) {
                    Permission::where('permission_group_id', $id)->delete();
                }
            }

            // Redirect back to the permissions index with a success message
            return response()->json(['message' => 'Permission deleted successfully.','success' => true]);
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
            // Check if the authenticated user has the required permission to restore permissions
            if (is_null($this->user) || !$this->user->can('usermanagement.restore')) {
                abort(403, 'Sorry! You are not allowed to restore permissions.');
            }

            // Fetch the specified role from the database
            $permission = PermissionGroup::withTrashed()->find($id);
            if(!is_null($permission)) {
                // Check if the permission is already restored
                if ($permission->trashed()) {
                    // Process Data
                    $permission->restore();
                    Permission::withTrashed()->where('permission_group_id', $id)->restore();
                }
            }

            // Redirect back to the permissions index with a success message
            return redirect()->route('users.permissions.index')->with('success', 'Permission restored successfully.');
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
                return response()->json(['message' => 'Sorry! You are not allowed to view permissions.','success' => false]);
            }

            // Retrieve data from the database
            $query = PermissionGroup::query();

            // Apply search filter if provided
            if ($request->has('search') && !empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where('name', 'like', '%' . $search . '%');
            }

            // Apply sorting if provided
            if ($request->has('sortOrder') && !empty($request->get('sortOrder'))) {
                $order  = $request->get('sortOrder');
                $column = $request->get('sortField');
                $query->orderBy($column, $order);
            }

            // Get the total count of records
            $totalRecords = $query->count();

            // Apply pagination if provided
            if ($request->has('page') && $request->has('size')) {
                $page   = $request->get('page');
                $size   = $request->get('size');
                $offset = ($page - 1) * $size; // Calculate the offset

                $query->skip($offset)->take($size);
            }

            // Get the filtered count of records
            $filteredRecords = $query->count();

            // Get the data for the current page
            $data = $query->get();

            $data = $data->map(function ($permission) {
                $permission->roles = $permission->roles($permission);
                return $permission;
            });

            // Calculate the page count
            $pageCount = ceil($totalRecords/$request->get('size'));

            // Calculate the current page number
            $currentPage = 0 + 1;

            // Return the response data as a JSON object
            return response()->json([
                'draw'            => $request->get('draw'),
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'pageCount'       => $pageCount,
                'page'            => $currentPage,
                'totalCount'      => $totalRecords,
                'data'            => $data,
            ]);
        }

        public function export()
        {
            // Check if the authenticated user has the required permission to export permissions
            if (is_null($this->user) || !$this->user->can('usermanagement.export')) {
                abort(403, 'Sorry! You are not allowed to export permissions.');
            }

            return Excel::download(new PermissionExport, 'permissions.xlsx');
        }
    }
