<?php
// /api/evals/*

auth_check();

$post_id = $segments[1] ?? null;
$sub     = $segments[2] ?? null;

if (!$post_id) res_err('Not found', 404);

$eval_path = DATA_EVALS . "/{$post_id}.json";

// GET /api/evals/{id}
if ($method === 'GET' && $sub === null) {
    $eval = json_read($eval_path);
    res_ok($eval);
}

// POST /api/evals/{id}/request
if ($method === 'POST' && $sub === 'request') {
    $body        = req_json();
    $instruction = trim($body['instruction'] ?? '');
    if ($instruction === '') res_err('instruction は必須です', 400);

    $eval = json_read($eval_path) ?? ['post_id' => $post_id, 'evaluation' => null, 'replies' => []];
    $reply_id = 'reply_' . str_pad(count($eval['replies']) + 1, 3, '0', STR_PAD_LEFT);

    $eval['replies'][] = [
        'id'           => $reply_id,
        'instruction'  => $instruction,
        'comments'     => [],
        'requested_at' => now_iso(),
        'generated_at' => null,
    ];
    json_write($eval_path, $eval);

    res_ok(['ok' => true, 'reply_id' => $reply_id]);
}

res_err('Not found', 404);
