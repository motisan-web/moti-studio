<?php
// CLIからのみ実行可
if (php_sapi_name() !== 'cli') {
    exit("このスクリプトはCLIからのみ実行できます\n");
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/lib/json.php';

$account_id = $argv[1] ?? null;

if (!$account_id) {
    echo "使い方: php tools/setup-auth.php {account_id}\n";
    echo "例:     php tools/setup-auth.php moti\n";
    exit(1);
}

// アカウント定義ファイルの存在確認
if (!file_exists(DATA_ACCOUNTS . "/{$account_id}.json")) {
    echo "エラー: accounts/{$account_id}.json が見つかりません\n";
    exit(1);
}

// パスワード入力（Windowsでは非表示不可のため注意書きを表示）
$is_win = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
if ($is_win) {
    echo "パスワードを入力してください（Windowsでは入力が見えます）: ";
    $password = trim(fgets(STDIN));
} else {
    echo "パスワードを入力: ";
    system('stty -echo');
    $password = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
}

if (strlen($password) < 8) {
    echo "エラー: パスワードは8文字以上にしてください\n";
    exit(1);
}

// 確認入力
if ($is_win) {
    echo "もう一度入力: ";
    $confirm = trim(fgets(STDIN));
} else {
    echo "もう一度入力: ";
    system('stty -echo');
    $confirm = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
}

if ($password !== $confirm) {
    echo "エラー: パスワードが一致しません\n";
    exit(1);
}

$path = DATA_AUTH . "/{$account_id}.json";
$exists = file_exists($path);

$auth = [
    'password_hash'   => password_hash($password, PASSWORD_BCRYPT),
    'failed_attempts' => 0,
    'last_failed_at'  => null,
    'locked_until'    => null,
];

json_write($path, $auth);
echo ($exists ? "更新" : "作成") . "しました: {$path}\n";
