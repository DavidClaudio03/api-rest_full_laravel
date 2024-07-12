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
Route::get('/pruebas/{nombre?}', function ($nombre = null) {
    $texto = '<h2>Hola mundo</h2>';
    $texto .= 'Nombre: ' . $nombre;
    return view('pruebas', ['texto' => $texto]);
});

//DAVID
//Route::get('/personalidades','PruebasController@index');
Route::get('/personalidades', [App\Http\Controllers\PruebasController::class, 'index']);
Route::get('/testOrm', [App\Http\Controllers\PruebasController::class, 'testOrm']);
