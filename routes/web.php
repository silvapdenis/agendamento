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

// Route::get('/', function () {
//     return response()->json([
//         'status' => 'success',
//         'message' => 'Medical Appointment Bot API is running!',
//         'timestamp' => now(),
//         'debug' => config('app.debug'),
//         'env' => config('app.env'),
//         'laravel_version' => app()->version()
//     ]);
// });

Route::get('/debug', function () {
    return response()->json([
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'debug_mode' => config('app.debug'),
        'environment' => config('app.env'),
        'database_connection' => config('database.default'),
        'app_key_set' => !empty(config('app.key')),
        'storage_writable' => is_writable(storage_path()),
        'bootstrap_writable' => is_writable(bootstrap_path('cache')),
    ]);
});

// Catch all routes for Vue.js SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// Adicione suas rotas adicionais aqui
