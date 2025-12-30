<?php
/**
 * Router for PHP built-in server
 * Handles URL rewriting like .htaccess would
 *
 * Usage: php -S localhost:8080 router.php
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Remove query string from path
$path = strtok($path, '?');

// Set up $_GET['url'] for the router (without leading slash)
$url = ltrim($path, '/');
$_GET['url'] = $url;

// Check if it's a real file (css, js, images, etc)
if ($path !== '/' && file_exists(__DIR__ . $path) && !is_dir(__DIR__ . $path)) {
    return false; // Serve the file directly
}

// Check for .php file (removes extension from URL)
$phpFile = __DIR__ . $path . '.php';
if (file_exists($phpFile)) {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
    include $phpFile;
    return true;
}

// Check for index.php in directory
if (is_dir(__DIR__ . $path)) {
    $indexFile = rtrim(__DIR__ . $path, '/') . '/index.php';
    if (file_exists($indexFile)) {
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;
        include $indexFile;
        return true;
    }
}

// Check root index.php
if ($path === '/') {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
    include __DIR__ . '/index.php';
    return true;
}

// 404
http_response_code(404);
echo "Not Found: $path";
