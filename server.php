<?php

$publicPath = realpath(__DIR__.'/public');
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

if (str_starts_with($uri, '/storage/')) {
    $storageRoot = realpath(__DIR__.'/storage/app/public');

    if ($storageRoot !== false) {
        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, substr($uri, 9));
        $requestedPath = realpath($storageRoot.DIRECTORY_SEPARATOR.$relativePath);
        $storagePrefix = rtrim($storageRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (
            $requestedPath !== false
            && str_starts_with(strtolower($requestedPath), strtolower($storagePrefix))
            && is_file($requestedPath)
        ) {
            header('Content-Type: '.(mime_content_type($requestedPath) ?: 'application/octet-stream'));
            header('Content-Length: '.filesize($requestedPath));
            header('X-Content-Type-Options: nosniff');
            readfile($requestedPath);

            return true;
        }
    }
}

$relativePath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $uri), DIRECTORY_SEPARATOR);
$requestedPath = $publicPath !== false ? realpath($publicPath.DIRECTORY_SEPARATOR.$relativePath) : false;
$publicPrefix = $publicPath !== false ? rtrim($publicPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR : '';

if (
    $requestedPath !== false
    && str_starts_with(strtolower($requestedPath), strtolower($publicPrefix))
    && is_file($requestedPath)
    && ! in_array(strtolower(pathinfo($requestedPath, PATHINFO_EXTENSION)), ['php', 'phtml', 'phar'], true)
) {
    $extension = strtolower(pathinfo($requestedPath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'text/javascript; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8',
        'mjs' => 'text/javascript; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'ttf' => 'font/ttf',
        'wasm' => 'application/wasm',
        'webmanifest' => 'application/manifest+json',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    header('Content-Type: '.($mimeTypes[$extension] ?? mime_content_type($requestedPath) ?: 'application/octet-stream'));
    header('Content-Length: '.filesize($requestedPath));
    header('X-Content-Type-Options: nosniff');

    if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
        readfile($requestedPath);
    }

    return true;
}

require_once __DIR__.'/public/index.php';
