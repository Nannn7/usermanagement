<?php

    namespace Modules\Usermanagement\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rules\Password;
    use Maatwebsite\Excel\Facades\Excel;
    use Modules\Basicdata\Models\Branch;
    use Modules\Usermanagement\Exports\UsersExport;
    use Modules\Usermanagement\Http\Requests\User as UserRequest;
    use Modules\Usermanagement\Models\Role;
    use Modules\Usermanagement\Models\User;
    use Modules\Usermanagement\Models\Position;
    use Illuminate\Support\Facades\Storage;
    use Modules\Corsec\Models\ApprovalRequest;
    use Modules\Corsec\Models\Directorate;
    use Modules\Corsec\Services\ApprovalRequestService;

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
        protected $user;
        private readonly ApprovalRequestService $approvalService;

        /**
         * UsersController constructor.
         *
         * Initializes the user property with the authenticated user.
         */
        public function __construct()
        {
            // Mengatur middleware auth
            $this->middleware('auth');
            $this->approvalService = app(ApprovalRequestService::class);

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
            if (is_null($this->user) || !$this->user->can('usermanagement.read')) {
                abort(403, 'Sorry! You are not allowed to view users.');
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
            if (is_null($this->user) || !$this->user->can('usermanagement.read')) {
                return response()->json(['message' => 'Sorry! You are not allowed to view users.','success' => false], 403);
            }

            $userTable = (new User())->getTable();
            $query = User::query()->select($userTable . '.*');
            $baseQuery = User::query()->select($userTable . '.*');

            if(!$this->user->hasRole('administrator')){
                $query->whereDoesntHave('roles', function($q){
                    $q->where('name', 'administrator');
                });
                $baseQuery->whereDoesntHave('roles', function($q){
                    $q->where('name', 'administrator');
                });
            }

            // Apply search filter if provided
            $search = trim((string) $request->get('search', ''));
            if ($search !== '') {
                $query->where(function ($builder) use ($userTable, $search) {
                    $builder->where($userTable . '.name', 'ilike', '%' . $search . '%')
                        ->orWhere($userTable . '.email', 'ilike', '%' . $search . '%');
                });
            }

            // Admin first, then apply sorting
            $tableNames = config('permission.table_names', []);
            $columnNames = config('permission.column_names', []);
            $rolesTable = $tableNames['roles'] ?? 'roles';
            $modelHasRoles = $tableNames['model_has_roles'] ?? 'model_has_roles';
            $rolePivot = $columnNames['role_pivot_key'] ?? 'role_id';
            $modelKey = $columnNames['model_morph_key'] ?? 'model_id';

            $adminOrderSql = "case when exists (select 1 from {$modelHasRoles} mhr join {$rolesTable} r on r.id = mhr.{$rolePivot} where mhr.{$modelKey} = {$userTable}.id and mhr.model_type = ? and r.name = ?) then 0 else 1 end";
            $query->orderByRaw($adminOrderSql, [User::class, 'administrator']);

            // Apply sorting if provided
            $sortField = (string) $request->get('sortField', 'directorate');
            $sortOrder = strtolower((string) $request->get('sortOrder', 'asc'));

            if (!in_array($sortOrder, ['asc', 'desc'], true)) {
                $sortOrder = 'asc';
            }

            if ($sortField === 'directorate') {
                $directorateTable = (new Directorate())->getTable();
                $query->leftJoin($directorateTable . ' as directorates', 'directorates.id', '=', $userTable . '.directorate_id')
                    ->select($userTable . '.*')
                    ->orderByRaw('directorates.name is null')
                    ->orderBy('directorates.name', $sortOrder);
            } elseif ($sortField === 'branch') {
                $branchTable = (new Branch())->getTable();
                $query->leftJoin($branchTable . ' as branches', 'branches.id', '=', $userTable . '.branch_id')
                    ->select($userTable . '.*')
                    ->orderByRaw('branches.name is null')
                    ->orderBy('branches.name', $sortOrder);
            } elseif ($sortField === 'position') {
                $positionTable = (new Position())->getTable();
                $query->leftJoin($positionTable . ' as positions', 'positions.id', '=', $userTable . '.position_id')
                    ->select($userTable . '.*')
                    ->orderByRaw('positions.name is null')
                    ->orderBy('positions.name', $sortOrder);
            } elseif (in_array($sortField, ['name', 'email', 'nik'], true)) {
                $query->orderBy($userTable . '.' . $sortField, $sortOrder);
            } else {
                $directorateTable = (new Directorate())->getTable();
                $query->leftJoin($directorateTable . ' as directorates', 'directorates.id', '=', $userTable . '.directorate_id')
                    ->select($userTable . '.*')
                    ->orderByRaw('directorates.name is null')
                    ->orderBy('directorates.name', 'asc');
            }

            $query->orderBy($userTable . '.name', 'asc');
            $totalRecords = $baseQuery->count();
            $filteredRecords = $search !== ''
                ? (clone $query)->distinct()->count($userTable . '.id')
                : $totalRecords;
            $page = max((int) $request->get('page', 1), 1);
            $size = max((int) $request->get('size', 10), 1);

            // Get the data for the current page
            $data = $query
                ->with([
                    'branch:id,name',
                    'directorate:id,name',
                    'position:id,name',
                    'roles:id,name',
                ])
                ->forPage($page, $size)
                ->get();

            // Calculate the page count
            $pageCount = (int) ceil($filteredRecords / max($size, 1));

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
            if (is_null($this->user) || !$this->user->can('usermanagement.update')) {
                abort(403, 'Sorry! You are not allowed to edit users.');
            }

            $user = User::with('roles:id,name')->find($id);
            $roles = Role::query()
                ->when(!$this->user->hasRole('administrator'), function ($query) {
                    $query->where('name', '!=', 'administrator');
                })
                ->orderBy('name')
                ->get(['id', 'name']);
            $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
            $directorates = Directorate::query()->orderBy('name')->get(['id', 'name']);
            $positions = Position::query()->orderBy('level')->orderBy('name')->get(['id', 'name', 'level']);
            return view('usermanagement::users.create', compact('user', 'roles', 'branches', 'directorates', 'positions'));
        }

        /**
         * Remove the specified resource from storage.
         *
         * @param int $id
         *
         * @return \Illuminate\Http\RedirectResponse
         * @throws \Illuminate\Auth\Access\AuthorizationException
         */
        // public function destroy($id)
        // {
        //     if (is_null($this->user) || !$this->user->can('usermanagement.delete')) {
        //         return response()->json(['message' => 'Sorry! You are not allowed to delete users.','success' => false], 403);
        //     }

        //     $user = User::find($id);
        //     $user->delete();

        //     return response()->json(['message' => 'User deleted successfully.', 'success' => true]);
        // }
        public function destroy($id)
        {
            if (is_null($this->user) || !$this->user->can('usermanagement.delete')) {
                return response()->json(['message' => 'Sorry! You are not allowed to delete users.','success' => false], 403);
            }
 
            $user = User::findOrFail($id);

            try {
                $oldPayload = $user->only(['name', 'email', 'nik', 'directorate_id', 'branch_id', 'position_id']);
                $oldPayload['_role_names'] = $user->roles()->pluck('name')->values()->all();

                $this->approvalService->createRequest(
                    User::class,
                    ApprovalRequest::ACTION_DELETE,
                    (string) $user->id,
                    [],
                    $oldPayload,
                    'Pengajuan delete user: ' . $user->name
                );
            } catch (Exception $e) {
                return response()->json(['message' => 'Failed to submit delete request. Please try again.', 'success' => false], 500);
            }
 
            return response()->json(['message' => 'Pengajuan hapus user berhasil dikirim untuk approval.', 'success' => true]);
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
            if (is_null($this->user) || !$this->user->can('usermanagement.restore')) {
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
            if (is_null($this->user) || !$this->user->can('usermanagement.create')) {
                abort(403, 'Sorry! You are not allowed to create a user.');
            }

            $validated = $request->validated();

            if ($validated) {
                $payload = $this->buildUserApprovalPayload($request, $validated);

                $this->approvalService->createRequest(
                    User::class,
                    ApprovalRequest::ACTION_CREATE,
                    null,
                    $payload,
                    null,
                    'Pengajuan create user'
                );

                return redirect()->route('users.index')->with('success', 'Pengajuan user berhasil dikirim untuk approval.');
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
            if (is_null($this->user) || !$this->user->can('usermanagement.create')) {
                abort(403, 'Sorry! You are not allowed to create a user.');
            }

            $roles = Role::query()
                ->when(!$this->user->hasRole('administrator'), function ($query) {
                    $query->where('name', '!=', 'administrator');
                })
                ->orderBy('name')
                ->get(['id', 'name']);
            $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
            $directorates = Directorate::query()->orderBy('name')->get(['id', 'name']);
            $positions = Position::query()->orderBy('level')->orderBy('name')->get(['id', 'name', 'level']);
            return view('usermanagement::users.create', compact('roles', 'branches', 'directorates', 'positions'));
        }

        public function export(Request $request)
        {
            if (is_null($this->user) || !$this->user->can('usermanagement.export')) {
                abort(403, 'Sorry! You are not allowed to export users.');
            }

            // Get search parameter from request
            $search = $request->get('search');

            return Excel::download(new UsersExport($search), 'users.xlsx');
        }

        public function profile()
        {
            $user = Auth::user()->loadMissing([
                'branch:id,name',
                'roles:id,name',
            ]);

            return view('usermanagement::users.profile', compact('user'));
        }

        public function showSignature($id)
        {
            $user = User::findOrFail($id);

            $isOwner = $this->user && (int) $this->user->id === (int) $user->id;
            $canReadAny = $this->user && $this->user->can('usermanagement.read');

            if (!$isOwner && !$canReadAny) {
                abort(403, 'Anda tidak memiliki akses untuk melihat tanda tangan ini.');
            }

            if (!$user->sign) {
                abort(404, 'Tanda tangan tidak ditemukan.');
            }

            $path = 'signatures/' . $user->id . '/' . $user->sign;
            $disk = Storage::disk('private');

            if (!$disk->exists($path)) {
                abort(404, 'Tanda tangan tidak ditemukan.');
            }

            return response()->file($disk->path($path), [
                'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        public function updateProfile(Request $request)
        {
            $user = Auth::user();

            $validatedData = $request->validate([
                'name'  => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'nik'   => 'nullable|string|max:6|unique:users,nik,' . $user->id,
                'sign'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user->name  = $validatedData['name'];
            $user->email = $validatedData['email'];
            $user->nik   = $validatedData['nik'] ?? $user->nik;

            if ($request->hasFile('sign')) {
                // Delete old e-sign if exists
                if ($user->sign) {
                    Storage::disk('private')->delete('signatures/' . $user->id . '/' . $user->sign);
                }

                $sign     = $request->file('sign');
                $signName = time() . '.' . $sign->getClientOriginalExtension();

                Storage::disk('private')->makeDirectory('signatures/' . $user->id);
                $sign->storeAs('signatures/' . $user->id, $signName, 'private');

                $user->sign = $signName;
            }

            $user->save();

            return redirect()->route('users.profile')->with('success', 'Profile updated successfully.');
        }

        public function changePassword(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            ], [
                'password_confirmation' => 'The new password confirmation does not match.',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $user = Auth::user();

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }

            if (Hash::check($request->password, $user->password)) {
                return back()->withErrors(['password' => 'The new password must be different from the current password.'])->withInput();
            }

            $user->password = Hash::make($request->password);
            $user->must_change_password = false;
            $user->password_changed_at = now();
            $user->save();

            return redirect()->route('users.profile')->with('success', 'Password changed successfully.');
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
            if (is_null($this->user) || !$this->user->can('usermanagement.update')) {
                abort(403, 'Sorry! You are not allowed to update users.');
            }

            $validated = $request->validated();

            if ($validated) {
                try {
                    $user = User::findOrFail($id);
                    if ($user && $request->hasFile('sign')) {
                        $sign = $request->file('sign');

                        $signName = time() . '.' . $sign->getClientOriginalExtension();

                        $sign->storeAs(
                            'signatures/' . $user->id . '/',
                            $signName,
                            'private',
                        );

                        $validated['sign'] = $signName;
                    }
                    $payload = $this->buildUserApprovalPayload($request, $validated);

                    $oldPayload = $user->only(array_keys(array_diff_key($payload, array_flip(['_role_names']))));
                    $oldPayload['_role_names'] = $user->roles()->pluck('name')->values()->all();

                    $this->approvalService->createRequest(
                        User::class,
                        ApprovalRequest::ACTION_UPDATE,
                        (string) $user->id,
                        $payload,
                        $oldPayload,
                        'Pengajuan update user'
                    );
                } catch (Exception $e) {
                    return redirect()->back()->withErrors(['error' => 'Failed to update user. Please try again.']);
                }
            }

            return redirect()->route('users.index')->with('success', 'Pengajuan perubahan user berhasil dikirim untuk approval.');
        }

        private function buildUserApprovalPayload(UserRequest $request, array $validated): array
        {
            unset($validated['profile_photo_path']);
            if ($request->hasFile('sign') && !is_string($validated['sign'] ?? null)) {
                unset($validated['sign']);
            }

            $roles = array_values(array_filter((array) $request->input('roles', [])));
            $validated['_role_names'] = $this->resolveRoleNames($roles);

            return $validated;
        }

        private function resolveRoleNames(array $roles): array
        {
            if (empty($roles)) {
                return [];
            }

            $numericRoleIds = array_values(array_filter($roles, fn ($role) => is_numeric($role)));
            $roleNames = array_values(array_filter($roles, fn ($role) => !is_numeric($role)));

            return Role::query()
                ->where(function ($query) use ($numericRoleIds, $roleNames) {
                    if (!empty($numericRoleIds)) {
                        $query->whereIn('id', $numericRoleIds);
                    }

                    if (!empty($roleNames)) {
                        $method = !empty($numericRoleIds) ? 'orWhereIn' : 'whereIn';
                        $query->{$method}('name', $roleNames);
                    }
                })
                ->pluck('name')
                ->values()
                ->all();
        }

    }
