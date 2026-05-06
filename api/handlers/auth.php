<?php
// POST /api/auth/login
// POST /api/auth/logout

$action = $segments[1] ?? '';

if ($method === 'POST' && $action === 'login') {
    $body       = req_json();
    $account_id = trim($body['account_id'] ?? '');
    $password   = $body['password'] ?? '';

    if ($account_id === '' || $password === '') {
        res_err('account_id と password は必須です', 400);
    }

    $result = auth_login($account_id, $password);

    if (!$result['ok']) {
        $extra = isset($result['locked_until']) ? ['locked_until' => $result['locked_until']] : [];
        res_err($result['error'], $result['status'], $extra);
    }

    $account = json_read(DATA_ACCOUNTS . "/{$account_id}.json");
    res_ok([
        'account_id'   => $account_id,
        'display_name' => $account['display_name'] ?? $account_id,
    ]);
}

if ($method === 'POST' && $action === 'logout') {
    session_destroy();
    res_ok(['ok' => true]);
}

res_err('Not found', 404);
