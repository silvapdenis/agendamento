<?php
/**
 * Simple PHP router for Laravel on Railway
 */

// Get the requested path
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Serve static files from public directory
if ($path !== '/' && file_exists(__DIR__ . '/public' . $path)) {
    return false; // Let PHP built-in server handle static files
}

// Set up Laravel environment
chdir(__DIR__);
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';

// Include Laravel entry point
require_once __DIR__ . '/public/index.php';