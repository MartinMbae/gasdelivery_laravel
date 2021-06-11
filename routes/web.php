<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\api\MpesaController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('/create_storage', function () {
    Artisan::call('storage:link');
    return "Symlink was set successfully";
});

Route::middleware(['auth', 'isAdmin', 'verified'])->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::get('orders/{tag?}', [AdminController::class, 'viewOrders']);
    Route::get('users', [AdminController::class, 'viewUsers']);
    Route::get('gas', [AdminController::class, 'viewGas']);
    Route::get('test', [HomeController::class, 'test']);
    Route::get('companies', [AdminController::class, 'viewCompanies']);
    Route::get('payments', [AdminController::class, 'payments']);
    Route::post('addCompany', [AdminController::class, 'addCompany']);
    Route::post('change_password', [AdminController::class, 'changePassword']);
    Route::post('editCompany', [AdminController::class, 'editCompany']);
    Route::post('addGas', [AdminController::class, 'addGas']);
    Route::post('editGas', [AdminController::class, 'editGas']);
    Route::post('complete_order', [AdminController::class, 'completeOrder']);
    Route::post('cancel_order', [AdminController::class, 'cancelOrder']);
});
Auth::routes(['verify' => true]);

//Route::get('test', [MpesaController::class, 'test']);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
