<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../lib/json.php';

$opts     = getopt('', ['id:', 'json:', 'strength:']);
$id       = $opts['id'] ?? null;
$json_str = $opts['json'] ?? null;
$strength = isset($opts['strength']) ? (int)$opts['strength'] : null;

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

if ($strength !== null && ($strength < 1 || $strength > 10)) {
    echo json_encode(['error' => '--strength は 1〜10 の整数で指定してください']) . PHP_EOL;
    exit(1);
}

// eval ファイルに書き込む
$eval_path = DATA_EVALS . "/{$id}.json";
$eval = json_read($eval_path) ?? ['post_id' => $id, 'evaluation' => null, 'replies' => []];

$eval['post_id']    = $id;
$eval['evaluation'] = $input['evaluation'];
if (isset($input['replies'])) {
    $eval['replies'] = $input['replies'];
}

json_write($eval_path, $eval);

// strength を post ファイルに書き込む
if ($strength !== null) {
    $post_path = DATA_POSTS . "/{$id}.json";
    $post = json_read($post_path);
    if ($post) {
        $post['strength']   = $strength;
        $post['updated_at'] = date('Y-m-d\TH:i:s');
        json_write($post_path, $post);
    }
}

echo json_encode(['ok' => true, 'id' => $id, 'strength' => $strength], JSON_UNESCAPED_UNICODE) . PHP_EOL;
