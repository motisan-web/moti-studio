<?php
// GET /api/sync — 本番データを一括返却（ローカル同期用）

if ($method !== 'GET') {
    res_err('Method not allowed', 405);
}

// APIキー認証
$key = $_SERVER['HTTP_X_SYNC_KEY'] ?? '';
if (!hash_equals(SYNC_KEY, $key)) {
    res_err('Unauthorized', 401);
}

// posts
$posts = [];
foreach (glob(DATA_POSTS . '/*.json') as $f) {
    $p = json_read($f);
    if ($p) $posts[$p['id']] = $p;
}

// evals
$evals = [];
foreach (glob(DATA_EVALS . '/*.json') as $f) {
    $e = json_read($f);
    if ($e && isset($e['post_id'])) $evals[$e['post_id']] = $e;
}

// categories
$cat_file = DATA_DIR . '/categories.json';
$categories = json_read($cat_file)['categories'] ?? [];

// reactions
$reactions = json_read(DATA_DIR . '/reactions.json') ?? ['emojis' => []];

res_ok([
    'posts'      => $posts,
    'evals'      => $evals,
    'categories' => $categories,
    'reactions'  => $reactions,
]);
