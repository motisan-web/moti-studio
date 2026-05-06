<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../lib/json.php';

$opts = getopt('', ['id:', 'categories:']);
$id         = $opts['id'] ?? null;
$categories = $opts['categories'] ?? null;

if (!$id || $categories === null) {
    echo json_encode(['error' => '--id と --categories が必要です']) . PHP_EOL;
    exit(1);
}

$path = DATA_POSTS . "/{$id}.json";
$post = json_read($path);
if (!$post) {
    echo json_encode(['error' => '投稿が見つかりません']) . PHP_EOL;
    exit(1);
}

$list = array_values(array_filter(array_map('trim', explode(',', $categories))));
$post['categories']     = $list;
$post['categorized_at'] = date('Y-m-d\TH:i:s');
$post['updated_at']     = date('Y-m-d\TH:i:s');

$tmp = $path . '.tmp';
file_put_contents($tmp, json_encode($post, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
rename($tmp, $path);

echo json_encode(['ok' => true, 'id' => $id, 'categories' => $list], JSON_UNESCAPED_UNICODE) . PHP_EOL;
