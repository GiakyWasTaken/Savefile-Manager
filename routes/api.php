<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Savefile;

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

Route::get('/savefile', function () {
    return Savefile::all();
});

Route::get('/savefile/{id}', function ($id) {
    return Savefile::find($id);
});

Route::post('/savefile', function (Request $request) {
    return Savefile::create($request->all());
});

Route::put('/savefile/{id}', function (Request $request, $id) {
    $savefile = Savefile::findOrFail($id);
    $savefile->update($request->all());

    return $savefile;
});

Route::delete('/savefile/{id}', function ($id) {
    Savefile::find($id)->delete();

    return 204;
});
