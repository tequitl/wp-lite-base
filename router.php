<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$full = __DIR__ . $path;

// Servir archivos reales directamente
if ($path !== '/' && file_exists($full)) {
    return false;
}

// Forzar WordPress como front-controller
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';