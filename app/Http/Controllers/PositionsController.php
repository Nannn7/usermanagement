<?php

    namespace Modules\Usermanagement\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Maatwebsite\Excel\Facades\Excel;
    use Modules\Corsec\Models\ApprovalRequest;
    use Modules\Corsec\Services\ApprovalRequestService;
    use Modules\Usermanagement\Exports\PositionExport;
    use Modules\Usermanagement\Http\Requests\PositionRequest;
    use Modules\Usermanagement\Models\Position;

    /**
     * Class PositionsController
     *
     * This controller is responsible for managing positions within the application.
     *
     * @package Modules\Usermanagement\Http\Controllers
     */
    class PositionsController extends Controller
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
         */
        public function index()
        {
            // Check if the authenticated user has the required permission to view positions
            if (is_null($this->user) || !$this->user->can('usermanagement.read')) {
                abort(403, 'Sorry! You are not allowed to view positions.');
            }

            // Return the view for displaying the positions
            return view('usermanagement::positions.index');
        }

        /**
         * Store a newly created resource in storage.
         *
         * @param \Modules\Usermanagement\Http\Requests\PositionRequest $request
         *
         * @return \Illuminate\Http\RedirectResponse
         */
        public function store(PositionRequest $request)
        {
            // Check if the authenticated user has the required permission to store positions
            if (is_null($this->user) || !$this->user->can('usermanagement.create')) {
                abort(403, 'Sorry! You are not allowed to create positions.');
            }

            // Get validated data
            $validated = $request->validated();

            try {
                $this->approvalService->createRequest(
                    Position::class,
                    ApprovalRequest::ACTION_CREATE,
                    null,
                    $validated,
                    null,
                    'Pengajuan create position'
                );

                // Redirect to the positions index page with a success message
                return redirect()->route('users.positions.index')
                                 ->with('success', 'Pengajuan position berhasil dikirim untuk approval.');
            } catch (Exception $e) {
                // If an error occurs, redirect back with an error message
                return redirect()->back()
                                 ->with('error', 'An error occurred while creating the position: ' . $e->getMessage())
                                 ->withInput();
            }
        }

        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
         */
        public function create()
        {
            // Check if the authenticated user has the required permission to create positions
            if (is_null($this->user) || !$this->user->can('usermanagement.create')) {
                abort(403, 'Sorry! You are not allowed to create positions.');
            }

            $nextCode = $this->nextPositionCode();

            // Return the view for creating a new position
            return view('usermanagement::positions.create', compact('nextCode'));
        }

        /**
         * Show the form for editing the specified resource.
         *
         * @param int $id
         *
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
         */
        public function edit($id)
        {
            // Check if the authenticated user has the required permission to edit positions
            if (is_null($this->user) || !$this->user->can('usermanagement.update')) {
                abort(403, 'Sorry! You are not allowed to edit positions.');
            }

            // Find the position by ID
            $position = Position::findOrFail($id);

            // Return the view for editing the position
            return view('usermanagement::positions.create', compact('position'));
        }

        /**
         * Update the specified resource in storage.
         *
         * @param \Modules\Usermanagement\Http\Requests\PositionRequest $request
         * @param int                                                   $id
         *
         * @return \Illuminate\Http\RedirectResponse
         */
        public function update(PositionRequest $request, $id)
        {
            // Check if the authenticated user has the required permission to update positions
            if (is_null($this->user) || !$this->user->can('usermanagement.update')) {
                abort(403, 'Sorry! You are not allowed to update positions.');
            }

            // Find the position by ID
            $position = Position::findOrFail($id);

            // Get validated data
            $validated = $request->validated();

            try {
                $this->approvalService->createRequest(
                    Position::class,
                    ApprovalRequest::ACTION_UPDATE,
                    (string) $position->id,
                    $validated,
                    $position->only(array_keys($validated)),
                    'Pengajuan update position'
                );

                // Redirect to the positions index page with a success message
                return redirect()->route('users.positions.index')
                                 ->with('success', 'Pengajuan perubahan position berhasil dikirim untuk approval.');
            } catch (Exception $e) {
                // If an error occurs, redirect back with an error message
                return redirect()->back()
                                 ->with('error', 'An error occurred while updating the position: ' . $e->getMessage())
                                 ->withInput();
            }
        }

        /**
         * Remove the specified resource from storage.
         *
         * @param int $id
         *
         * @return \Illuminate\Http\RedirectResponse
         */
        public function destroy(Request $request, $id)
        {
            // Check if the authenticated user has the required permission to delete positions
            if (is_null($this->user) || !$this->user->can('usermanagement.delete')) {
                return response()->json(['message' => 'Sorry! You are not allowed to delete positions.','success' => false], 403);
            }

            // Find the position by ID
            $position = Position::findOrFail($id);

            // Check if the position has associated roles
            if ($position->roles()->exists()) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Cannot delete position because it has associated roles.',
                        'success' => false,
                    ], 422);
                }

                return redirect()->route('users.positions.index')
                                 ->with('error', 'Cannot delete position because it has associated roles.');
            }

            try {
                // If no errors, delete the position from the database
                // $position->delete();
                $oldPayload = $position->only(['code', 'name', 'level']);

                $this->approvalService->createRequest(
                    Position::class,
                    ApprovalRequest::ACTION_DELETE,
                    (string) $position->id,
                    [],
                    $oldPayload,
                    'Pengajuan delete position: ' . $position->name
                );

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Pengajuan hapus position berhasil dikirim untuk approval.',
                        'success' => true,
                    ]);
                }

                // Redirect to the positions index page with a success message
                return redirect()->route('users.positions.index')
                                 ->with('success', 'Pengajuan hapus position berhasil dikirim untuk approval.');
            } catch (Exception $e) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'An error occurred while deleting the position: ' . $e->getMessage(),
                        'success' => false,
                    ], 500);
                }

                // If an error occurs, redirect back with an error message
                return redirect()->route('users.positions.index')
                                 ->with('error', 'An error occurred while deleting the position: ' . $e->getMessage());
            }
        }

        /**
         * Process support datatables ajax request.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function dataForDatatables(Request $request)
        {
            // Check if the authenticated user has the required permission to view positions
            if (is_null($this->user) || !$this->user->can('usermanagement.read')) {
                return response()->json(['message' => 'Sorry! You are not allowed to view positions.','success' => false], 403);
            }

            // Retrieve data from the database
            $query = Position::query()->select(['id', 'code', 'name', 'level']);
            $baseCountQuery = Position::query();

            // Apply search filter if provided
            $search = trim((string) $request->get('search', ''));
            if ($search !== '') {
                $query->whereAny(['code', 'name', 'level'], 'like', '%' . $search . '%');
            }

            // Apply sorting if provided
            if ($request->has('sortOrder') && !empty($request->get('sortOrder'))) {
                $order  = strtolower((string) $request->get('sortOrder'));
                $column = (string) $request->get('sortField');
                $allowedSort = ['code', 'name', 'level'];

                if (!in_array($order, ['asc', 'desc'], true)) {
                    $order = 'asc';
                }

                if (!in_array($column, $allowedSort, true)) {
                    $column = 'name';
                }

                $query->orderBy($column, $order);
            } else {
                $query->orderBy('level')->orderBy('name');
            }

            $totalRecords = $baseCountQuery->count();
            $filteredRecords = $search !== '' ? (clone $query)->count() : $totalRecords;
            $page = max((int) $request->get('page', 1), 1);
            $size = max((int) $request->get('size', 10), 1);

            // Get the data for the current page
            $data = $query->forPage($page, $size)->get();

            // Calculate the page count
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

        /**
         * Export positions to Excel.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
         */
        public function export(Request $request)
        {
            // Check if the authenticated user has the required permission to export positions
            if (is_null($this->user) || !$this->user->can('usermanagement.export')) {
                abort(403, 'Sorry! You are not allowed to export positions.');
            }

            // Get search parameter from request
            $search = $request->get('search');

            return Excel::download(new PositionExport($search), 'positions.xlsx');
        }

        /**
         * Generate the next numeric position code using the current highest stored code.
         */
        private function nextPositionCode(): string
        {
            $numericCodes = Position::withTrashed()
                ->pluck('code')
                ->filter(fn ($code) => is_string($code) && preg_match('/^\d+$/', $code));

            $width = max(3, (int) $numericCodes->map(fn ($code) => strlen($code))->max());
            $maxCode = (int) $numericCodes->map(fn ($code) => (int) $code)->max();

            return str_pad((string) ($maxCode + 1), $width, '0', STR_PAD_LEFT);
        }
    }
