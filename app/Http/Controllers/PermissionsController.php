<?php

    namespace Modules\Usermanagement\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Str;
    use Maatwebsite\Excel\Facades\Excel;
    use Modules\Corsec\Models\ApprovalRequest;
    use Modules\Corsec\Services\ApprovalRequestService;
    use Modules\Usermanagement\Exports\PermissionExport;
    use Modules\Usermanagement\Http\Requests\PermissionRequest;
    use Modules\Usermanagement\Models\Permission;
    use Modules\Usermanagement\Models\PermissionGroup;
    use Modules\Usermanagement\Models\Role;

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
                    $payload = $this->buildPermissionGroupApprovalPayload($validate['name']);
                    $this->approvalService->createRequest(
                        PermissionGroup::class,
                        ApprovalRequest::ACTION_CREATE,
                        null,
                        $payload,
                        null,
                        'Pengajuan create permission group'
                    );

                    return redirect()->route('users.permissions.index')->with('success', 'Pengajuan permission berhasil dikirim untuk approval.');
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
                    $group       = PermissionGroup::find($id);
                    $payload = $this->buildPermissionGroupApprovalPayload($validated['name']);
                    $oldPayload = $group->only(['name', 'slug']);
                    $oldPayload['_permission_names'] = Permission::where('permission_group_id', $group->id)
                        ->orderBy('id')
                        ->pluck('name')
                        ->values()
                        ->all();

                    $this->approvalService->createRequest(
                        PermissionGroup::class,
                        ApprovalRequest::ACTION_UPDATE,
                        (string) $group->id,
                        $payload,
                        $oldPayload,
                        'Pengajuan update permission group'
                    );

                    return redirect()->route('users.permissions.index')->with('success', 'Pengajuan perubahan permission berhasil dikirim untuk approval.');
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
                return response()->json(['message' => 'Sorry! You are not allowed to view permissions.','success' => false], 403);
            }

            // Retrieve data from the database
            $query = PermissionGroup::query()
                ->with(['permission:id,name,permission_group_id']);
            $baseQuery = PermissionGroup::query();

            // Apply search filter if provided
            $search = trim((string) $request->get('search', ''));
            if ($search !== '') {
                $query->where('name', 'like', '%' . $search . '%');
            }

            // Apply sorting if provided
            if ($request->has('sortOrder') && !empty($request->get('sortOrder'))) {
                $order  = strtolower((string) $request->get('sortOrder'));
                $column = (string) $request->get('sortField');

                if (!in_array($order, ['asc', 'desc'], true)) {
                    $order = 'asc';
                }

                if ($column !== 'name') {
                    $column = 'name';
                }

                $query->orderBy($column, $order);
            } else {
                $query->orderBy('name');
            }

            $totalRecords = $baseQuery->count();
            $filteredRecords = $search !== '' ? (clone $query)->count() : $totalRecords;
            $page = max((int) $request->get('page', 1), 1);
            $size = max((int) $request->get('size', 10), 1);

            $roles = Role::query()
                ->with('permissions:id,name')
                ->get(['id', 'name']);

            $data = $query
                ->forPage($page, $size)
                ->get(['id', 'name'])
                ->map(function ($permissionGroup) use ($roles) {
                    $permissionNames = $permissionGroup->permission->pluck('name')->filter();

                    $permissionGroup->roles = $roles
                        ->filter(function ($role) use ($permissionNames) {
                            return $role->permissions
                                ->pluck('name')
                                ->intersect($permissionNames)
                                ->isNotEmpty();
                        })
                        ->values()
                        ->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'name' => $role->name,
                            ];
                        });

                    return $permissionGroup;
                });

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

        public function export()
        {
            // Check if the authenticated user has the required permission to export permissions
            if (is_null($this->user) || !$this->user->can('usermanagement.export')) {
                abort(403, 'Sorry! You are not allowed to export permissions.');
            }

            return Excel::download(new PermissionExport, 'permissions.xlsx');
        }

        private function buildPermissionGroupApprovalPayload(string $name): array
        {
            $groupName = strtolower($name);

            return [
                'name' => $name,
                'slug' => Str::slug($name),
                '_permission_names' => [
                    $groupName . '.create',
                    $groupName . '.read',
                    $groupName . '.update',
                    $groupName . '.delete',
                    $groupName . '.export',
                    $groupName . '.authorize',
                    $groupName . '.report',
                    $groupName . '.restore',
                ],
            ];
        }
    }
