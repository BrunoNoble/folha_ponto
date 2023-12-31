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


Route::get('/',[\App\Http\Controllers\FolhaPontosController::class, 'index']);

Route::get('/filtro', [\App\Http\Controllers\FolhaPontosController::class,'filtro'])->name('filtro');
Route::get('/ponto',[\App\Http\Controllers\FolhaPontosController::class,'registerPonto'])->name('ponto');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');



Route::get('/holidays', [\App\Http\Controllers\HolidaysController::class, 'getHolidays']);


