<?php
/**
 * Simple PHP server entry point for Railway deployment
 * This avoids Laravel's ServeCommand issues
 */

// Set the public directory as the document root
$publicPath = __DIR__ . '/public';

// Get the requested URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If it's a file in public directory, serve it directly
if ($uri !== '/' && file_exists($publicPath . $uri)) {
    return false; // Let PHP's built-in server handle it
}

// Otherwise, route through Laravel's index.php
require_once $publicPath . '/index.php';