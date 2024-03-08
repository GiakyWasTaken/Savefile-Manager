<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\SavefileController;
use App\Http\Controllers\GameController;

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

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/user', [UserAuthController::class, 'user']);
    Route::get('/logout', [UserAuthController::class, 'logout']);
});

Route::get('savefile', [SavefileController::class, 'list']);
Route::get('savefile/{id}', [SavefileController::class, 'get']);
Route::post('savefile', [SavefileController::class, 'store']);
Route::put('savefile/{id}', [SavefileController::class, 'update']);
Route::delete('savefile/{id}', [SavefileController::class, 'delete']);

Route::get('/game', [GameController::class, 'index']);
Route::get('/game/{id}', [GameController::class, 'show']);
Route::post('/game', [GameController::class, 'create']);
Route::put('/game/{id}', [GameController::class, 'update']);
Route::delete('/game/{id}', [GameController::class, 'delete']);
