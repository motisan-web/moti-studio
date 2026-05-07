<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/lib/json.php';
require_once __DIR__ . '/lib/response.php';
require_once __DIR__ . '/lib/auth.php';

// URI から /api/ 以降のパスを取得
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_pos = strpos($uri, '/api/');
$path     = $base_pos !== false ? substr($uri, $base_pos + 5) : '';
$path     = trim($path, '/');
$segments = $path !== '' ? explode('/', $path) : [];

$method   = $_SERVER['REQUEST_METHOD'];
$resource = $segments[0] ?? '';

match ($resource) {
    'auth'      => require __DIR__ . '/handlers/auth.php',
    'posts'     => require __DIR__ . '/handlers/posts.php',
    'accounts'  => require __DIR__ . '/handlers/accounts.php',
    'evals'     => require __DIR__ . '/handlers/evals.php',
    'search'    => require __DIR__ . '/handlers/search.php',
    'reactions' => require __DIR__ . '/handlers/reactions.php',
    'sync'      => require __DIR__ . '/handlers/sync.php',
    default     => res_err('Not found', 404),
};
