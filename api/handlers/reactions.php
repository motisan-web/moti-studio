<?php
// /api/reactions

auth_check();

$method = $_SERVER['REQUEST_METHOD'];
$REACTIONS_FILE = DATA_DIR . '/reactions.json';
$REACTIONS_IMG  = __DIR__ . '/../../img/reactions';

// GET /api/reactions
if ($method === 'GET') {
    $data = json_read($REACTIONS_FILE) ?? ['emojis' => []];
    res_ok($data);
}

// POST /api/reactions  (multipart: file + slug + label)
if ($method === 'POST') {
    $slug  = trim($_POST['slug']  ?? '');
    $label = trim($_POST['label'] ?? '');

    if ($slug === '') res_err('slug は必須です', 400);
    if (!preg_match('/^[a-z0-9_-]+$/', $slug)) res_err('slug は英小文字・数字・_- のみ使用できます', 400);

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        res_err('画像ファイルが必要です', 400);
    }

    $file     = $_FILES['image'];
    $mime     = mime_content_type($file['tmp_name']);
    $allowed  = ['image/png' => 'png', 'image/gif' => 'gif', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) res_err('PNG / GIF / JPEG / WebP のみ対応しています', 400);

    $ext      = $allowed[$mime];
    $filename = $slug . '.' . $ext;

    if (!is_dir($REACTIONS_IMG)) mkdir($REACTIONS_IMG, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $REACTIONS_IMG . '/' . $filename)) {
        res_err('ファイルの保存に失敗しました', 500);
    }

    $data = json_read($REACTIONS_FILE) ?? ['emojis' => []];

    // 同一 slug は上書き
    $data['emojis'] = array_values(array_filter($data['emojis'], fn($e) => $e['slug'] !== $slug));
    $data['emojis'][] = [
        'slug'  => $slug,
        'image' => '/img/reactions/' . $filename,
        'label' => $label ?: $slug,
    ];

    json_write($REACTIONS_FILE, $data);
    res_ok(['ok' => true, 'slug' => $slug, 'image' => '/img/reactions/' . $filename]);
}

// DELETE /api/reactions/{slug}
$slug = $segments[1] ?? null;
if ($method === 'DELETE' && $slug) {
    $data = json_read($REACTIONS_FILE) ?? ['emojis' => []];
    $data['emojis'] = array_values(array_filter($data['emojis'], fn($e) => $e['slug'] !== $slug));
    json_write($REACTIONS_FILE, $data);
    res_ok(['ok' => true]);
}

res_err('Not found', 404);
