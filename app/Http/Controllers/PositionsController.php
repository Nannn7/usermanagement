<?php

    namespace Modules\Usermanagement\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Maatwebsite\Excel\Facades\Excel;
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
        public $this->user;

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
         */
        public function index()
        {
            // Check if the authenticated user has the required permission to view positions
            if (is_null($this->user) || !$this->user->can('usermanagement.read')) {
                abort(403, 'Sorry! You are not allowed to view positions.');
            }

            // Fetch all positions from the database
            $positions = Position::all();

            // Return the view for displaying the positions
            return view('usermanagement::positions.index', compact('positions'));
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
                // If no errors, save the position to the database
                $position = Position::create($validated);

                // Redirect to the positions index page with a success message
                return redirect()->route('users.positions.index')
                                 ->with('success', 'Position created successfully.');
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

            // Return the view for creating a new position
            return view('usermanagement::positions.create');
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
                // If no errors, update the position in the database
                $position->update($validated);

                // Redirect to the positions index page with a success message
                return redirect()->route('users.positions.index')
                                 ->with('success', 'Position updated successfully.');
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
        public function destroy($id)
        {
            // Check if the authenticated user has the required permission to delete positions
            if (is_null($this->user) || !$this->user->can('usermanagement.delete')) {
                abort(403, 'Sorry! You are not allowed to delete positions.');
            }

            // Find the position by ID
            $position = Position::findOrFail($id);

            // Check if the position has associated roles
            if ($position->roles()->count() > 0) {
                return redirect()->route('users.positions.index')
                                 ->with('error', 'Cannot delete position because it has associated roles.');
            }

            try {
                // If no errors, delete the position from the database
                $position->delete();

                // Redirect to the positions index page with a success message
                return redirect()->route('users.positions.index')
                                 ->with('success', 'Position deleted successfully.');
            } catch (Exception $e) {
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
                abort(403, 'Sorry! You are not allowed to view positions.');
            }

            // Retrieve data from the database
            $query = Position::query();

            // Apply search filter if provided
            if ($request->has('search') && !empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(code) LIKE ?', ['%' . strtolower($search) . '%'])
                      ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                      ->orWhereRaw('CAST(level AS TEXT) LIKE ?', ['%' . $search . '%']);
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
            if ($request->has('page') && $request->has('size')) {
                $page   = $request->get('page');
                $size   = $request->get('size');
                $offset = ($page - 1) * $size; // Calculate the offset

                $query->skip($offset)->take($size);
            }

            // Get the filtered count of records
            $filteredRecords = $query->count();

            // Get the data for the current page
            $positions = $query->get();

            // Calculate the page count
            $size = $request->get('size', 10); // Default to 10 if not set
            $pageCount = $size > 0 ? ceil($totalRecords / $size) : 0;

            // Calculate the current page number
            $currentPage = $request->get('page', 1); // Default to page 1 if not set

            // Return the response data as a JSON object
            return response()->json([
                'draw'            => $request->get('draw'),
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'pageCount'       => $pageCount,
                'page'            => $currentPage,
                'totalCount'      => $totalRecords,
                'data'            => $positions,
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
    }
