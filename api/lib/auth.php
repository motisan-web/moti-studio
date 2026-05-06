<?php

const AUTH_MAX_ATTEMPTS    = 5;
const AUTH_LOCKOUT_MINUTES = 15;

function auth_check(): void {
    if (empty($_SESSION['account_id'])) {
        res_err('認証が必要です', 401);
    }
}

function auth_account_id(): string {
    return $_SESSION['account_id'] ?? '';
}

function auth_login(string $account_id, string $password): array {
    $path = DATA_AUTH . "/{$account_id}.json";

    if (!file_exists($path)) {
        // アカウント存在有無を隠すため同じメッセージを返す
        return ['ok' => false, 'error' => 'パスワードが違います', 'status' => 401];
    }

    $auth = json_read($path);
    $now  = now_iso();

    // ロックアウト確認
    if (!empty($auth['locked_until'])) {
        if ($now < $auth['locked_until']) {
            return ['ok' => false, 'error' => 'ロックアウト中です', 'locked_until' => $auth['locked_until'], 'status' => 423];
        }
        // 期限切れ → リセット
        $auth['failed_attempts'] = 0;
        $auth['last_failed_at']  = null;
        $auth['locked_until']    = null;
    }

    // パスワード検証
    if (!password_verify($password, $auth['password_hash'])) {
        $auth['failed_attempts']++;
        $auth['last_failed_at'] = $now;

        if ($auth['failed_attempts'] >= AUTH_MAX_ATTEMPTS) {
            $auth['locked_until'] = date('Y-m-d\TH:i:s', strtotime('+' . AUTH_LOCKOUT_MINUTES . ' minutes'));
            json_write($path, $auth);
            return ['ok' => false, 'error' => 'ロックアウト中です', 'locked_until' => $auth['locked_until'], 'status' => 423];
        }

        json_write($path, $auth);
        return ['ok' => false, 'error' => 'パスワードが違います', 'status' => 401];
    }

    // 成功
    $auth['failed_attempts'] = 0;
    $auth['last_failed_at']  = null;
    $auth['locked_until']    = null;
    json_write($path, $auth);

    $_SESSION['account_id'] = $account_id;
    return ['ok' => true];
}
