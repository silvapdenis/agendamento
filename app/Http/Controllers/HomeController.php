<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application home page
     */
    public function index()
    {
        return view('app');
    }

    /**
     * Show debug information
     */
    public function debug()
    {
        return response()->json([
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'debug_mode' => config('app.debug'),
            'environment' => config('app.env'),
            'database_connection' => config('database.default'),
            'app_key_set' => !empty(config('app.key')),
            'storage_writable' => is_writable(storage_path()),
            'bootstrap_writable' => is_writable(bootstrap_path('cache')),
            'timestamp' => now(),
        ]);
    }

    /**
     * API status endpoint
     */
    public function status()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Medical Appointment Bot API is running!',
            'timestamp' => now(),
            'debug' => config('app.debug'),
            'env' => config('app.env'),
            'laravel_version' => app()->version()
        ]);
    }
}