<?php
/**
 * デプロイ前チェックスクリプト
 * CLIで実行: php tools/deploy-check.php
 */

$root = dirname(__DIR__);
$ok   = true;

function check(string $label, bool $pass, string $detail = ''): void {
    global $ok;
    $mark = $pass ? '✅' : '❌';
    echo "{$mark} {$label}" . ($detail ? " — {$detail}" : '') . PHP_EOL;
    if (!$pass) $ok = false;
}

echo "=== moti studio デプロイチェック ===" . PHP_EOL . PHP_EOL;

// PHP バージョン
check('PHP >= 8.1', PHP_VERSION_ID >= 80100, 'PHP ' . PHP_VERSION);

// 必須ファイル
foreach (['index.php', 'config.php', 'api/index.php', 'api/.htaccess', '.htaccess'] as $f) {
    check("ファイル存在: {$f}", file_exists("{$root}/{$f}"));
}

// data/ ディレクトリ構成
foreach (['data', 'data/posts', 'data/accounts', 'data/evals', 'data/drafts', 'data/auth', 'img/reactions'] as $d) {
    $path  = "{$root}/{$d}";
    $exist = is_dir($path);
    check("ディレクトリ: {$d}", $exist);
    if ($exist) {
        check("書き込み可能: {$d}", is_writable($path));
    }
}

// data/auth/ にパスワードファイルがあるか
$authFiles = glob("{$root}/data/auth/*.json");
check('authファイル存在', !empty($authFiles), empty($authFiles) ? 'tools/setup-auth.php を実行してください' : count($authFiles) . ' アカウント');

// categories.json
check('categories.json 存在', file_exists("{$root}/data/categories.json"));

// PHP 拡張（必須）
foreach (['json', 'session', 'hash'] as $ext) {
    check("拡張: {$ext}", extension_loaded($ext));
}
// mbstring は Apache PHPで読まれる場合があるため注意メッセージのみ
if (!extension_loaded('mbstring')) {
    echo "⚠️  拡張: mbstring — CLI未ロード（Apache PHPでは通常ロード済み）\n";
}

// HTTPアクセス遮断確認（ローカルのみ簡易チェック）
$dataHtaccess = "{$root}/.htaccess";
if (file_exists($dataHtaccess)) {
    $content = file_get_contents($dataHtaccess);
    check('.htaccess: data/ 遮断ルール', str_contains($content, 'data/'));
}

echo PHP_EOL;
echo $ok ? '✅ 全チェック通過。デプロイ可能です。' . PHP_EOL
         : '❌ 問題があります。上記を修正してから再確認してください。' . PHP_EOL;
echo PHP_EOL;

// パーミッション設定ガイド（Xserver向け）
echo "=== Xserver パーミッション設定ガイド ===" . PHP_EOL;
echo "以下のコマンドをXserverのSSHまたはFTPで実行してください:" . PHP_EOL . PHP_EOL;
echo "# ファイル: 644" . PHP_EOL;
echo "find . -type f -name '*.php' -exec chmod 644 {} \;" . PHP_EOL;
echo "find . -type f -name '*.json' -exec chmod 644 {} \;" . PHP_EOL;
echo "find . -type f -name '.htaccess' -exec chmod 644 {} \;" . PHP_EOL . PHP_EOL;
echo "# ディレクトリ: 755" . PHP_EOL;
echo "find . -type d -exec chmod 755 {} \;" . PHP_EOL . PHP_EOL;
echo "# 書き込み許可が必要なディレクトリ: 755 (PHPプロセスが書き込む)" . PHP_EOL;
echo "chmod 755 data/ data/posts/ data/evals/ data/accounts/ data/auth/ data/drafts/ data/reactions/" . PHP_EOL;
echo "chmod 755 img/ img/reactions/" . PHP_EOL;
