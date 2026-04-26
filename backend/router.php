<?php
/**
 * router.php — PHP built-in dev server router
 *
 * Run from ANY directory:
 *   php -S localhost:8000 C:\path\to\backend\router.php
 *   php -S localhost:8000 /path/to/backend/router.php
 *
 * Or from the backend\ folder:
 *   php -S localhost:8000 router.php
 *
 * __DIR__ is always the absolute directory containing THIS file,
 * so the paths below work no matter where you run the command from.
 */

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . DIRECTORY_SEPARATOR . 'public' . $uri;

// Serve real static files (images, favicon, etc.) unchanged
if ($uri !== '/' && is_file($file)) {
    return false;
}

// All API and app requests → entry point
require __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';