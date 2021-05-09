<?php

use App\Http\Controllers\api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [ApiController::class, 'register']);
Route::post('login', [ApiController::class, 'login']);
Route::get('gas/{refill?}', [ApiController::class, 'gas']);
Route::post('add_address', [ApiController::class, 'addAddress']);
Route::get('get_addresses/{user_id}', [ApiController::class, 'fetchMyAddresses']);
Route::get('get_orders/{user_id}', [ApiController::class, 'fetchMyOrders']);
Route::post('order', [ApiController::class, 'postOrder']);


