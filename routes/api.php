<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Savefile;
use App\Http\Controllers\SavefileController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/database', function () {
    return view('database');
});

Route::get('savefile', [SavefileController::class, 'index']);
Route::get('savefile/{id}', [SavefileController::class, 'show']);
Route::post('savefile', [SavefileController::class, 'store']);
Route::put('savefile/{id}', [SavefileController::class, 'update']);
Route::delete('savefile/{id}', [SavefileController::class, 'delete']);
