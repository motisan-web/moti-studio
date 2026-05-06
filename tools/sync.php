<?php
/**
 * データ同期ツール
 * ローカル ↔ 本番 の data/ + img/reactions/ を JSON バンドルで同期する
 * セッション認証必須（ログイン済みのブラウザでアクセス）
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/lib/json.php';

if (!isset($_SESSION['account_id'])) {
    header('Location: /');
    exit;
}

$ROOT         = dirname(__DIR__);
$IMG_DIR      = $ROOT . '/img/reactions';
$action       = $_GET['action'] ?? '';
$importResult = null;
$importError  = null;

// ── BUNDLE HELPERS ────────────────────────────────────────

function bundle_scan(): array {
    global $IMG_DIR;
    $files = [];

    // data/ 以下すべて
    if (is_dir(DATA_DIR)) {
        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(DATA_DIR, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iter as $file) {
            if (!$file->isFile()) continue;
            $rel = 'data/' . str_replace('\\', '/', substr($file->getRealPath(), strlen(DATA_DIR) + 1));
            $files[$rel] = [
                'content'  => base64_encode(file_get_contents($file->getRealPath())),
                'modified' => filemtime($file->getRealPath()),
                'size'     => $file->getSize(),
            ];
        }
    }

    // img/reactions/ 以下すべて
    if (is_dir($IMG_DIR)) {
        foreach (glob($IMG_DIR . '/*') as $f) {
            if (!is_file($f)) continue;
            $rel = 'img/reactions/' . basename($f);
            $files[$rel] = [
                'content'  => base64_encode(file_get_contents($f)),
                'modified' => filemtime($f),
                'size'     => filesize($f),
            ];
        }
    }

    return $files;
}

function bundle_restore(array $bundle): array {
    global $ROOT;
    $restored = 0;
    $errors   = [];

    foreach ($bundle['files'] as $rel => $info) {
        // パストラバーサル防止
        if (strpos($rel, '..') !== false) continue;
        if (!preg_match('#^(data/|img/reactions/)#', $rel)) continue;

        $dest = $ROOT . '/' . $rel;
        $dir  = dirname($dest);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        if (file_put_contents($dest, base64_decode($info['content'])) === false) {
            $errors[] = $rel;
        } else {
            $restored++;
        }
    }

    return ['restored' => $restored, 'errors' => $errors];
}

// ── EXPORT ────────────────────────────────────────────────

if ($action === 'export') {
    $bundle   = [
        'version'     => 1,
        'exported_at' => date('Y-m-d\TH:i:s'),
        'host'        => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'files'       => bundle_scan(),
    ];
    $filename = 'moti-data-' . date('Ymd-His') . '.json';
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo json_encode($bundle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ── IMPORT ────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_FILES['bundle']) || $_FILES['bundle']['error'] !== UPLOAD_ERR_OK) {
        $importError = 'ファイルのアップロードに失敗しました (error: ' . ($_FILES['bundle']['error'] ?? 'none') . ')';
    } else {
        $raw    = file_get_contents($_FILES['bundle']['tmp_name']);
        $bundle = json_decode($raw, true);

        if (!is_array($bundle) || ($bundle['version'] ?? 0) !== 1 || !isset($bundle['files'])) {
            $importError = 'バンドルファイルの形式が不正です';
        } else {
            $result       = bundle_restore($bundle);
            $importResult = $result;
        }
    }
}

// ── STATS ─────────────────────────────────────────────────

$stats = [];
$dirs  = [
    DATA_POSTS    => ['label' => '投稿',     'ext' => '*.json'],
    DATA_EVALS    => ['label' => '評価',     'ext' => '*.json'],
    DATA_ACCOUNTS => ['label' => 'アカウント','ext' => '*.json'],
    DATA_DRAFTS   => ['label' => 'Drafts',   'ext' => '*.md'],
    $IMG_DIR      => ['label' => 'カスタム絵文字', 'ext' => '*'],
];
foreach ($dirs as $dir => ['label' => $label, 'ext' => $ext]) {
    $count = is_dir($dir) ? count(glob($dir . '/' . $ext)) : 0;
    $stats[] = ['label' => $label, 'count' => $count];
}

$totalFiles  = count(bundle_scan());
$totalSizeKB = round(array_sum(array_column(bundle_scan(), 'size')) / 1024, 1);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>データ同期 — moti studio</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f2f2f7; color: #1c1c2e; padding: 40px 20px; }
    .wrap { max-width: 640px; margin: 0 auto; }
    h1 { font-size: 20px; font-weight: 700; color: #6b5ce7; margin-bottom: 6px; }
    .subtitle { font-size: 13px; color: #7070a0; margin-bottom: 32px; }
    .card { background: #fff; border: 1px solid #dddde8; border-radius: 14px; padding: 24px; margin-bottom: 20px; }
    .card-title { font-size: 14px; font-weight: 700; margin-bottom: 14px; }
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 10px; }
    .stat-box { background: #f0f0f5; border-radius: 8px; padding: 12px; text-align: center; }
    .stat-num { font-size: 22px; font-weight: 700; color: #6b5ce7; }
    .stat-label { font-size: 11px; color: #7070a0; margin-top: 2px; }
    .btn { display: inline-block; padding: 10px 22px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; }
    .btn-primary { background: #6b5ce7; color: #fff; }
    .btn-primary:hover { background: #7c6aff; }
    .btn-outline { background: none; border: 1px solid #dddde8; color: #1c1c2e; margin-left: 10px; }
    .btn-outline:hover { border-color: #6b5ce7; color: #6b5ce7; }
    .upload-area { border: 2px dashed #dddde8; border-radius: 10px; padding: 28px; text-align: center; cursor: pointer; transition: border-color .15s; margin-bottom: 14px; }
    .upload-area:hover { border-color: #6b5ce7; }
    .upload-area input { display: none; }
    .upload-filename { font-size: 12px; color: #7070a0; margin-top: 6px; }
    .alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
    .alert-ok  { background: #edfcf0; border: 1px solid #a8e6ba; color: #1a6b35; }
    .alert-err { background: #fff0f3; border: 1px solid #f8c0cc; color: #9b2335; }
    .how-to { font-size: 12px; color: #7070a0; line-height: 1.9; }
    .how-to ol { padding-left: 18px; }
    .how-to code { background: #f0f0f5; padding: 1px 5px; border-radius: 4px; font-family: monospace; }
    .back-link { font-size: 13px; color: #6b5ce7; text-decoration: none; }
    .back-link:hover { text-decoration: underline; }
    .warn-box { background: #fff8e1; border: 1px solid #ffe082; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #7a5c00; margin-bottom: 14px; }
  </style>
</head>
<body>
<div class="wrap">
  <a class="back-link" href="/">← タイムラインに戻る</a>
  <h1 style="margin-top:20px">データ同期</h1>
  <p class="subtitle">ローカル ↔ 本番 の data/ を JSON バンドルで同期します</p>

  <?php if ($importResult): ?>
  <div class="alert alert-ok">
    ✅ <?= $importResult['restored'] ?> ファイルを復元しました。
    <?php if ($importResult['errors']): ?>
      （書き込み失敗: <?= implode(', ', $importResult['errors']) ?>）
    <?php endif; ?>
    <a href="/" style="color:#1a6b35;margin-left:8px">アプリに戻る →</a>
  </div>
  <?php endif; ?>

  <?php if ($importError): ?>
  <div class="alert alert-err">❌ <?= htmlspecialchars($importError) ?></div>
  <?php endif; ?>

  <!-- 現在のデータ概要 -->
  <div class="card">
    <div class="card-title">現在のデータ（<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'this server') ?>）</div>
    <div class="stats-grid">
      <?php foreach ($stats as $s): ?>
      <div class="stat-box">
        <div class="stat-num"><?= $s['count'] ?></div>
        <div class="stat-label"><?= htmlspecialchars($s['label']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="font-size:12px;color:#7070a0;margin-top:8px">合計 <?= $totalFiles ?> ファイル / 約 <?= $totalSizeKB ?> KB</div>
  </div>

  <!-- エクスポート -->
  <div class="card">
    <div class="card-title">📦 エクスポート（このサーバーのデータをDL）</div>
    <div class="warn-box">⚠️ バンドルには認証ファイル（パスワードハッシュ）も含まれます。取り扱いに注意してください。</div>
    <a class="btn btn-primary" href="?action=export">バンドルをダウンロード (.json)</a>
  </div>

  <!-- インポート -->
  <div class="card">
    <div class="card-title">📥 インポート（バンドルからデータを復元）</div>
    <p style="font-size:12px;color:#7070a0;margin-bottom:14px">既存ファイルは上書きされます。インポート前にエクスポートでバックアップを取ることを推奨します。</p>
    <form method="post" enctype="multipart/form-data">
      <label class="upload-area" id="dropArea">
        <input type="file" name="bundle" accept=".json" id="bundleFile" onchange="onFileSelect(this)">
        <div style="font-size:24px">📂</div>
        <div style="font-size:13px;margin-top:6px">バンドルファイル(.json)を選択</div>
        <div class="upload-filename" id="fileName">ファイルが選択されていません</div>
      </label>
      <button class="btn btn-primary" type="submit" id="importBtn" disabled>インポートする</button>
    </form>
  </div>

  <!-- 使い方 -->
  <div class="card">
    <div class="card-title">📋 同期手順</div>
    <div class="how-to">
      <strong>本番 → ローカルに同期（本番の最新データをローカルに反映）</strong>
      <ol>
        <li>本番サーバー（<code>d00e.motisan.info/tools/sync.php</code>）でエクスポート</li>
        <li>ダウンロードした .json をローカルの同期ページ（<code>git15.local/tools/sync.php</code>）でインポート</li>
      </ol>
      <br>
      <strong>ローカル → 本番に同期（ローカルのデータを本番に反映）</strong>
      <ol>
        <li>ローカル（<code>git15.local/tools/sync.php</code>）でエクスポート</li>
        <li>本番サーバー（<code>d00e.motisan.info/tools/sync.php</code>）でインポート</li>
      </ol>
    </div>
  </div>
</div>

<script>
function onFileSelect(input) {
  const f = input.files[0];
  document.getElementById('fileName').textContent = f ? f.name : 'ファイルが選択されていません';
  document.getElementById('importBtn').disabled = !f;
}
</script>
</body>
</html>
