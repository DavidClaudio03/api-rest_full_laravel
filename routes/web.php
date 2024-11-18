<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Http\Middleware\ApiAutthMiddlwware;
use App\Models\User;

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

//RUTAS DE PRUEBAS CONTROLADORES
/*Route::get('/usuario/pruebas',[UserController::class,'pruebas']);
Route::get('/category/pruebas', [CategoryController::class, 'pruebas']);
Route::get('/post/pruebas', [PostController::class, 'pruebas']);*/

    //RUTA USUARIO
Route::post('/usuario/register', [UserController::class,'register']);
Route::post('/usuario/login', [UserController::class,'login']);
    //RUTA PARA EL TOKEN
Route::put('/usuario/update',[UserController::class,'update']);
//Mejora para la Autentificación
Route::post('/usuario/upload',[UserController::class,'upload'])->middleware(ApiAuthMiddleware::class);

//Para recuperar la imagen
Route::get('/usuario/getImage/{filename}', [UserController::class, 'getImage']);

//Para recuperar los datos del usuario
Route::get('/usuario/detail/{id}', [UserController::class, 'detail']);

//Rutas del controlor de catergorias
Route::resource('/api/category', CategoryController::class);

//Rutas del controlador de posts
Route::resource('/api/post', PostController::class);

//RUTA PARA LA IMAGEN
Route::post('/api/post/upload',[PostController::class,'upload']);
Route::get('/api/post/getImage/{filename}', [PostController::class, 'getImage']);

//RUTAS PARA OBTENER LOS POST POR CATEGORIA O USUARIO
Route::get('/api/post/category/{id}', [PostController::class, 'getPostsByCategory']);
Route::get('/api/post/user/{id}', [PostController::class, 'getPostsByUser']);


