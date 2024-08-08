<?php

    namespace Modules\Usermanagement\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Maatwebsite\Excel\Facades\Excel;
    use Modules\Usermanagement\Exports\RolesExport;
    use Modules\Usermanagement\Http\Requests\RoleRequest;
    use Modules\Usermanagement\Models\Role;

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
         *
        public function __construct()
        {
            $this->middleware(function ($request, $next) {
                $this->user = Auth::guard('web')->user();
                return $next($request);
            });
        }*/

        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
         * @throws \Illuminate\Auth\Access\AuthorizationException
         */
        public function index()
        {
            // Check if the authenticated user has the required permission to view roles
            if (is_null($this->user) || !$this->user->can('roles.view')) {
                //abort(403, 'Sorry! You are not allowed to view roles.');
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
            if (is_null($this->user) || !$this->user->can('roles.store')) {
                //abort(403, 'Sorry! You are not allowed to store roles.');
            }

            // Create a new role using the provided request data
            Role::create($request->all());

            // Redirect back to the roles index with a success message
            return redirect()->route('users.roles.index')->with('success', 'Role created successfully.');
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
            if (is_null($this->user) || !$this->user->can('roles.create')) {
                //abort(403, 'Sorry! You are not allowed to create roles.');
            }

            // Return the view for creating a new role
            return view('usermanagement::roles.create');
        }

        /**
         * Display the specified resource.
         *
         * @param int $id
         *
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
         * @throws \Illuminate\Auth\Access\AuthorizationException
         */
        public function show($id)
        {
            // Check if the authenticated user has the required permission to view roles
            if (is_null($this->user) || !$this->user->can('roles.view')) {
                abort(403, 'Sorry! You are not allowed to view roles.');
            }

            // Fetch the specified role from the database
            $role = Role::find($id);

            // Return the view for displaying the role
            return view('usermanagement::roles.show', compact('role'));
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
            if (is_null($this->user) || !$this->user->can('roles.edit')) {
                //abort(403, 'Sorry! You are not allowed to edit roles.');
            }

            // Fetch the specified role from the database
            $role = Role::find($id);

            // Return the view for editing the role
            return view('usermanagement::roles.create', compact('role'));
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
            if (is_null($this->user) || !$this->user->can('roles.update')) {
                //abort(403, 'Sorry! You are not allowed to update roles.');
            }

            // Fetch the specified role from the database
            $role = Role::find($id);

            // Update the role using the provided request data
            $role->update($request->all());

            // Redirect back to the roles index with a success message
            return redirect()->route('users.roles.index')->with('success', 'Role updated successfully.');
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
            // Check if the authenticated user has the required permission to delete roles
            if (is_null($this->user) || !$this->user->can('roles.delete')) {
                //abort(403, 'Sorry! You are not allowed to delete roles.');
            }

            // Fetch the specified role from the database
            $role = Role::find($id);

            // Delete the role
            $role->delete();

            // Redirect back to the roles index with a success message
            echo json_encode(['message' => 'Role deleted successfully.', 'success' => true]);
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
            if (is_null($this->user) || !$this->user->can('roles.restore')) {
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
            if (is_null($this->user) || !$this->user->can('roles.view')) {
                //abort(403, 'Sorry! You are not allowed to view users.');
            }

            // Retrieve data from the database
            $query = Role::query();

            // Apply search filter if provided
            if ($request->has('search') && !empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                });
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
            if ($request->has('start') && $request->has('length')) {
                $start  = $request->get('start');
                $length = $request->get('length');
                $query->skip($start)->take($length);
            }

            // Get the filtered count of records
            $filteredRecords = $query->count();

            // Get the data for the current page
            $roles = $query->get();

            // Calculate the page count
            $pageCount = ceil($totalRecords);

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
                'data'            => $roles,
            ]);
        }

        public function export()
        {
            return Excel::download(new RolesExport, 'roles.xlsx');
        }
    }
