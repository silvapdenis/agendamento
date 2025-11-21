<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

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

// API status endpoint
Route::get('/status', [HomeController::class, 'status']);

// Debug endpoint
Route::get('/debug', [HomeController::class, 'debug']);

// Database management endpoints (temporary for setup)
Route::get('/db/test', [App\Http\Controllers\DatabaseController::class, 'test']);
Route::get('/db/migrate', [App\Http\Controllers\DatabaseController::class, 'migrate']);
Route::get('/db/seed', [App\Http\Controllers\DatabaseController::class, 'seed']);

// Home page and catch all routes for Vue.js SPA
Route::get('/', [HomeController::class, 'index']);
Route::get('/{any}', [HomeController::class, 'index'])->where('any', '.*');

// Adicione suas rotas adicionais aqui
