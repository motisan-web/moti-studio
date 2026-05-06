<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../lib/json.php';

$files = glob(DATA_POSTS . '/*.json') ?: [];
$ids = [];
foreach ($files as $f) {
    $post = json_read($f);
    if ($post && empty($post['categorized_at'])) {
        $ids[] = $post['id'];
    }
}

echo json_encode($ids, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
