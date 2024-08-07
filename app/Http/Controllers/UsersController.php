<?php

    namespace Modules\Usermanagement\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Maatwebsite\Excel\Facades\Excel;
    use Modules\Usermanagement\Exports\UsersExport;
    use Modules\Usermanagement\Http\Requests\User as UserRequest;
    use Modules\Usermanagement\Models\Role;
    use Modules\Usermanagement\Models\User;

    /**
     * Class UsersController
     *
     * This controller is responsible for managing user within the application.
     *
     * @package Modules\Usermanagement\Http\Controllers
     */
    class UsersController extends Controller
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
        //        public function __construct()
        //        {
        //            $this->middleware(function ($request, $next) {
        //                $this->user = Auth::guard('web')->user();
        //                return $next($request);
        //            });
        //        }

        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
         * @throws \Illuminate\Auth\Access\AuthorizationException
         */
        public function index()
        {
            if (is_null($this->user) || !$this->user->can('users.view')) {
                //abort(403, 'Sorry! You are not allowed to view users.');
            }

            return view('usermanagement::users.index');
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
            if (is_null($this->user) || !$this->user->can('users.view')) {
                //abort(403, 'Sorry! You are not allowed to view users.');
            }

            // Retrieve data from the database
            $query = User::query();

            // Apply search filter if provided
            if ($request->has('search') && !empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                      ->orWhere('email', 'LIKE', "%$search%");
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
            $users = $query->get();

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
                'data'            => $users,
            ]);
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
            if (is_null($this->user) || !$this->user->can('users.edit')) {
                //abort(403, 'Sorry! You are not allowed to edit users.');
            }

            $user  = User::find($id);
            $roles = Role::all();
            return view('usermanagement::users.create', compact('user', 'roles'));
        }

        /**
         * Update the specified resource in storage.
         *
         * @param \Modules\Usermanagement\Http\Requests\User $request
         * @param int                                        $id
         *
         * @return \Illuminate\Http\RedirectResponse
         * @throws \Illuminate\Auth\Access\AuthorizationException
         */
        public function update(UserRequest $request, $id)
        {
            if (is_null($this->user) || !$this->user->can('users.update')) {
                //abort(403, 'Sorry! You are not allowed to update users.');
            }

            $user = User::find($id);
            $user->update($request->all());

            return redirect()->route('users.index')->with('success', 'User updated successfully.');
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
            if (is_null($this->user) || !$this->user->can('users.delete')) {
                //abort(403, 'Sorry! You are not allowed to delete users.');
            }

            $user = User::find($id);
            $user->delete();

            echo json_encode(['message' => 'User deleted successfully.', 'success' => true]);
        }

        /**
         * Restore the specified resource from storage.
         *
         * @param int $id
         *
         * @return \Illuminate\Http\RedirectResponse
         * @throws \Illuminate\Auth\Access\AuthorizationException
         */
        public function restore($id)
        {
            if (is_null($this->user) || !$this->user->can('users.restore')) {
                abort(403, 'Sorry! You are not allowed to restore users.');
            }

            $user = User::withTrashed()->find($id);
            $user->restore();

            return redirect()->route('users.index')->with('success', 'User restored successfully.');
        }

        /**
         * Store a newly created resource in storage.
         *
         * This function handles the creation of a new user in the application. It validates the incoming request data,
         * creates a new user record in the database, and redirects the user to the users index page with a success message.
         *
         * @param \Modules\Usermanagement\Http\Requests\User $request The incoming request containing the user data.
         *
         * @return \Illuminate\Http\RedirectResponse Redirects to the users index page with a success message upon successful creation.
         * @return \Illuminate\Http\RedirectResponse Redirects to the users create page upon validation failure.
         */
        public function store(UserRequest $request)
        {
            $validated = $request->validated();

            if ($validated) {
                $user = User::create($validated);

                if ($user) {
                    return redirect()->route('users.index')->with('success', 'User created successfully.');
                }
            }

            return redirect()->route('users.create');
        }

        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
         * @throws \Illuminate\Auth\Access\AuthorizationException
         */
        public function create()
        {
            if (is_null($this->user) || !$this->user->can('users.create')) {
                //abort(403, 'Sorry! You are not allowed to create a user.');
            }

            $roles = Role::all();
            return view('usermanagement::users.create', compact('roles'));
        }

        public function export()
        {
            return Excel::download(new UsersExport, 'users.xlsx');
        }

    }
