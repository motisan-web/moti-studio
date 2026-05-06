<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../lib/json.php';

$opts = getopt('', ['id:', 'json:']);
$id       = $opts['id'] ?? null;
$json_str = $opts['json'] ?? null;

if (!$id || $json_str === null) {
    echo json_encode(['error' => '--id と --json が必要です']) . PHP_EOL;
    exit(1);
}

$input = json_decode($json_str, true);
if (!is_array($input)) {
    echo json_encode(['error' => '--json のパースに失敗しました']) . PHP_EOL;
    exit(1);
}

if (!isset($input['evaluation'])) {
    echo json_encode(['error' => 'evaluation キーが必要です']) . PHP_EOL;
    exit(1);
}

$path = DATA_EVALS . "/{$id}.json";
$eval = json_read($path) ?? ['post_id' => $id, 'evaluation' => null, 'replies' => []];

$eval['post_id']    = $id;
$eval['evaluation'] = $input['evaluation'];
if (isset($input['replies'])) {
    $eval['replies'] = $input['replies'];
}

json_write($path, $eval);

echo json_encode(['ok' => true, 'id' => $id], JSON_UNESCAPED_UNICODE) . PHP_EOL;
