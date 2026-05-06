<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../lib/json.php';

$opts = getopt('', ['file:']);
$file = $opts['file'] ?? null;

if (!$file) {
    echo json_encode(['error' => '--file が必要です']) . PHP_EOL;
    exit(1);
}

$path = DATA_DRAFTS . '/' . $file;
if (!file_exists($path)) {
    echo json_encode(['error' => 'ファイルが見つかりません: ' . $file]) . PHP_EOL;
    exit(1);
}

$content = file_get_contents($path);

// フロントマターを分離（--- で囲まれた先頭ブロック）
$frontmatter = [];
$body        = $content;

if (str_starts_with(ltrim($content), '---')) {
    $parts = preg_split('/^---\s*$/m', ltrim($content), 3);
    if (count($parts) >= 3) {
        foreach (explode("\n", trim($parts[1])) as $line) {
            if (preg_match('/^(\w+):\s*(.*)$/', trim($line), $m)) {
                $frontmatter[$m[1]] = trim($m[2]);
            }
        }
        $body = trim($parts[2]);
    }
}

if (empty($body)) {
    echo json_encode(['error' => '本文が空です']) . PHP_EOL;
    exit(1);
}

$payload = [
    'account_id'   => $frontmatter['account'] ?? 'moti',
    'title'        => $frontmatter['title']   ?? '',
    'body'         => $body,
    'categories'   => [],
    'repost'       => isset($frontmatter['repost']) && $frontmatter['repost'] === 'true',
    'repost_from'  => $frontmatter['repost_from'] ?? null,
];

// APIを内部呼び出し（直接JSONファイルを書く）
function post_new_id_cli(): string {
    return date('Ymd') . '_' . bin2hex(random_bytes(3));
}

$id  = post_new_id_cli();
$now = date('Y-m-d\TH:i:s');
$post = [
    'id'             => $id,
    'account_id'     => $payload['account_id'],
    'title'          => $payload['title'],
    'body'           => $payload['body'],
    'intent'         => null,
    'url'            => null,
    'categories'     => [],
    'labels'         => [],
    'reactions'      => new stdClass(),
    'comments'       => [],
    'repost'         => $payload['repost'],
    'repost_from'    => $payload['repost_from'],
    'archive_at'     => null,
    'categorized_at' => null,
    'created_at'     => $now,
    'updated_at'     => $now,
];

json_write(DATA_POSTS . "/{$id}.json", $post);

// 投稿済みdraftを moved/ フォルダへ退避
$done_dir = DATA_DRAFTS . '/posted';
if (!is_dir($done_dir)) mkdir($done_dir, 0755, true);
rename($path, $done_dir . '/' . $file);

echo json_encode(['ok' => true, 'id' => $id, 'file' => $file], JSON_UNESCAPED_UNICODE) . PHP_EOL;
