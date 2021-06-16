<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\IBController;
use App\Http\Controllers\MemberController;
use App\Models\Member;
use App\Models\NAB;
use App\Models\UnitTransaction;
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

Route::prefix('v1')->group(function () {
    // Unprotected Routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    // Protected Routes
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });

        Route::prefix('user')->group(function () {
            Route::post('add', [MemberController::class, 'add']);
        });

        Route::prefix('ib')->group(function () {
            Route::post('updateTotalBalance', [IBController::class, 'updateTotalBalance']);
            Route::get('listNAB', [IBController::class, 'listNAB']);
            Route::post('topup', [IBController::class, 'topup']);
            Route::post('withdraw', [IBController::class, 'withdraw']);
            Route::get('member', [IBController::class, 'member']);
        });
    });
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
