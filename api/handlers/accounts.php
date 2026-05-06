<?php
// /api/accounts/*

auth_check();

$acc_id = $segments[1] ?? null;

// GET /api/accounts
if ($method === 'GET' && $acc_id === null) {
    $files = glob(DATA_ACCOUNTS . '/*.json');
    $list  = $files ? array_map(fn($f) => json_read($f), $files) : [];
    res_ok(array_values(array_filter($list)));
}

// GET /api/accounts/{id}
if ($method === 'GET' && $acc_id !== null) {
    $acc = json_read(DATA_ACCOUNTS . "/{$acc_id}.json");
    if (!$acc) res_err('アカウントが見つかりません', 404);
    res_ok($acc);
}

// PUT /api/accounts/{id}
if ($method === 'PUT' && $acc_id !== null) {
    $acc = json_read(DATA_ACCOUNTS . "/{$acc_id}.json");
    if (!$acc) res_err('アカウントが見つかりません', 404);

    $body    = req_json();
    $allowed = ['display_name', 'icon', 'icon_shape', 'color'];
    foreach ($allowed as $key) {
        if (array_key_exists($key, $body)) $acc[$key] = $body[$key];
    }
    json_write(DATA_ACCOUNTS . "/{$acc_id}.json", $acc);
    res_ok($acc);
}

res_err('Not found', 404);
