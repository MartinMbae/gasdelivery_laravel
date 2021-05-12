<?php

use App\Http\Controllers\AdminController;
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


Route::middleware(['auth', 'verified','isAdmin'])->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::get('orders/{tag?}', [AdminController::class, 'viewOrders']);
    Route::get('users', [AdminController::class, 'viewUsers']);
    Route::get('gas', [AdminController::class, 'viewGas']);
    Route::get('companies', [AdminController::class, 'viewCompanies']);
    Route::post('addCompany', [AdminController::class, 'addCompany']);
    Route::post('editCompany', [AdminController::class, 'editCompany']);
    Route::post('addGas', [AdminController::class, 'addGas']);
    Route::post('editGas', [AdminController::class, 'editGas']);
});
Auth::routes(['verify' => true]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
