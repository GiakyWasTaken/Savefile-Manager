<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hello-world', function () {
    return view('hello');
});

Route::permanentRedirect('/hello', '/hello-world');

Route::get('/calculate', function () {
    $num1 = rand(1, 100);
    $num2 = rand(1, 100);
    return view('calculate', ['num1' => $num1, 'num2' => $num2]);
});

Route::post('/calculate', function () {
    return view('calculate',
    ['num1' => $_POST['num1'],
     'num2' => $_POST['num2'],
     'name' => $_POST['name'],
    ]);
});

Route::get('/database', function () {
    return view('database');
});

Route::get('/savefile', function () {
    return view('savefile');
});
