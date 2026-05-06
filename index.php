<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/lib/json.php';

$account_id = $_SESSION['account_id'] ?? null;
$is_auth    = $account_id !== null;

if ($is_auth) {
    $account    = json_read(DATA_ACCOUNTS . "/{$account_id}.json") ?? ['id' => $account_id, 'display_name' => $account_id, 'color' => '#6b5ce7'];
    $cats_data  = json_read(DATA_DIR . '/categories.json') ?? [];
    $categories = $cats_data['categories'] ?? [];
    $acc_files  = glob(DATA_ACCOUNTS . '/*.json') ?: [];
    $accounts   = array_values(array_filter(array_map('json_read', $acc_files)));
}

$ua    = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_sp = isset($_GET['sp']) || preg_match('/iPhone|Android.*Mobile|Windows Phone/i', $ua);

include __DIR__ . '/views/' . ($is_sp ? 'sp' : 'pc') . '.php';
