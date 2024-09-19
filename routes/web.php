<?php

    use Illuminate\Support\Facades\Route;
    use Modules\Usermanagement\Http\Controllers\PermissionsController;
    use Modules\Usermanagement\Http\Controllers\RolesController;
    use Modules\Usermanagement\Http\Controllers\UsersController;

    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | contains the "web" middleware group. Now create something great!
    |
    */
Route::middleware(['auth'])->group(function () {
    Route::name('users.')->prefix('users')->group(function () {
        Route::get('restore/{id}', [UsersController::class,'restore'])->name('restore');
        Route::get('datatables', [UsersController::class, 'dataForDatatables'])->name('datatables');
        Route::get('export', [UsersController::class, 'export'])->name('export');
        Route::get('profile', [UsersController::class, 'profile'])->name('profile');
    });
    Route::resource('users', UsersController::class);

    Route::name('users.')->group(function () {
        Route::name('roles.')->prefix('roles')->group(function () {
            Route::get('restore/{id}', [RolesController::class,'restore'])->name('restore');
            Route::get('datatables', [RolesController::class, 'dataForDatatables'])->name('datatables');
            Route::get('export', [RolesController   ::class, 'export'])->name('export');
        });
        Route::resource('roles', RolesController::class);

        Route::name('permissions.')->prefix('permissions')->group(function () {
            Route::get('restore/{id}', [PermissionsController::class,'restore'])->name('restore');
            Route::get('datatables', [PermissionsController::class, 'dataForDatatables'])->name('datatables');
            Route::get('export', [PermissionsController   ::class, 'export'])->name('export');
        });
        Route::resource('permissions', PermissionsController::class);
    });
});

