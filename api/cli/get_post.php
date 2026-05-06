<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../lib/json.php';

$opts = getopt('', ['id:']);
$id = $opts['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => '--id が必要です']) . PHP_EOL;
    exit(1);
}

$post = json_read(DATA_POSTS . "/{$id}.json");
if (!$post) {
    echo json_encode(['error' => '投稿が見つかりません']) . PHP_EOL;
    exit(1);
}

echo json_encode($post, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
