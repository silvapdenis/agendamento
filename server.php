<?php
/**
 * Ultra-simple PHP router for Railway
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Serve static files directly
if ($path !== '/' && file_exists(__DIR__ . '/public' . $path)) {
    return false;
}

// Route everything else to Laravel
$_SERVER['SCRIPT_NAME'] = '/index.php';
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';