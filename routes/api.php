<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\WishController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/wishes/public/{path}', [WishController::class, 'getWishImagePath'])->where('path', '.+')->name('wish.image');
Route::get('/wishes/{userId}', [WishController::class, 'wishes']);
Route::middleware('auth:sanctum')->post('/wishes/{userId}/add', [WishController::class, 'saveWish']);
Route::middleware('auth:sanctum')->post('/wishes/{userId}/delete', [WishController::class, 'deleteWish']);
Route::middleware('auth:sanctum')->post('/wishes/{userId}/claim', [WishController::class, 'claimWish']);
Route::middleware('auth:sanctum')->post('/wishes/{userId}/unclaim', [WishController::class, 'unclaimWish']);
Route::middleware('auth:sanctum')->post('/wishes/{userId}/toggle-favorite', [WishController::class, 'toggleFavorite']);
Route::middleware('auth:sanctum')->put('/wishes/{userId}/update', [WishController::class, 'update']);

Route::middleware('auth:sanctum')->get('/account/claimed-wishes', [UserController::class, 'userClaimedWishes']);
Route::middleware('auth:sanctum')->post('/account/update', [UserController::class, 'update']);
