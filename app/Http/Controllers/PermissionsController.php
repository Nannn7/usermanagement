<?php

    namespace Modules\Usermanagement\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;

    class PermissionsController extends Controller
    {

        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\View\View
         * @return \Illuminate\Contracts\View\Factory
         */
        public function index()
        {
            return view('usermanagement::index');
        }


        /**
         * Show the form for creating a new resource.
         *
         * This function is responsible for displaying the form to create a new resource.
         * It returns the view for creating a new resource, which is located at 'usermanagement::create'.
         *
         * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
         * @return \Illuminate\Contracts\View\View
         */
        public function create()
        {
            return view('usermanagement::create');
        }


        public function store(Request $request)
        : RedirectResponse
        {
            //
        }


        public function show($id)
        {
            return view('usermanagement::show');
        }


        public function edit($id)
        {
            return view('usermanagement::edit');
        }


        public function update(Request $request, $id)
        : RedirectResponse
        {
            //
        }


        public function destroy($id)
        {
            //
        }
    }
