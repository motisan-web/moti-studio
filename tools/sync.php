<?php
// tools/sync.php — 本番からローカルへデータを同期する（CLI専用）
// 使い方: php tools/sync.php [--dry-run]

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/lib/json.php';

$dry_run = in_array('--dry-run', $argv);

if ($dry_run) {
    echo "[DRY RUN] 変更プレビュー（書き込みはしません）\n\n";
}

// ── 本番からデータ取得 ────────────────────────────────────────

$ctx = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'header'  => 'X-Sync-Key: ' . SYNC_KEY,
        'timeout' => 30,
    ],
]);

$raw = @file_get_contents(SYNC_ENDPOINT, false, $ctx);
if ($raw === false) {
    echo "エラー: 本番への接続に失敗しました（" . SYNC_ENDPOINT . "）\n";
    exit(1);
}

$remote = json_decode($raw, true);
if (!is_array($remote)) {
    echo "エラー: レスポンスのパースに失敗しました\n";
    exit(1);
}

if (isset($remote['error'])) {
    echo "エラー: " . $remote['error'] . "\n";
    exit(1);
}

$changes = 0;

// ── posts 同期 ────────────────────────────────────────────────

foreach ($remote['posts'] ?? [] as $id => $remote_post) {
    $local_path = DATA_POSTS . "/{$id}.json";
    $local_post = json_read($local_path);

    if ($local_post === null) {
        echo "NEW    posts/{$id}.json\n";
        if (!$dry_run) json_write($local_path, $remote_post);
        $changes++;
        continue;
    }

    $merged = merge_post($local_post, $remote_post);
    if ($merged === null) {
        echo "SKIP   posts/{$id}.json\n";
        continue;
    }

    echo "MERGE  posts/{$id}.json\n";
    if (!$dry_run) json_write($local_path, $merged);
    $changes++;
}

// ── evals 同期 ────────────────────────────────────────────────

foreach ($remote['evals'] ?? [] as $id => $remote_eval) {
    $local_path = DATA_EVALS . "/{$id}.json";
    $local_eval = json_read($local_path);

    if ($local_eval === null) {
        echo "NEW    evals/{$id}.json\n";
        if (!$dry_run) json_write($local_path, $remote_eval);
        $changes++;
        continue;
    }

    $merged = merge_eval($local_eval, $remote_eval);
    if ($merged === null) {
        echo "SKIP   evals/{$id}.json\n";
        continue;
    }

    echo "MERGE  evals/{$id}.json\n";
    if (!$dry_run) json_write($local_path, $merged);
    $changes++;
}

// ── categories 同期 ───────────────────────────────────────────

$cat_path    = DATA_DIR . '/categories.json';
$local_cats  = json_read($cat_path)['categories'] ?? [];
$merged_cats = array_values(array_unique(array_merge($local_cats, $remote['categories'] ?? [])));

if ($merged_cats !== $local_cats) {
    echo "MERGE  categories.json\n";
    if (!$dry_run) json_write($cat_path, ['categories' => $merged_cats]);
    $changes++;
} else {
    echo "SKIP   categories.json\n";
}

// ── reactions 同期 ────────────────────────────────────────────

$react_path    = DATA_DIR . '/reactions.json';
$local_react   = json_read($react_path) ?? ['emojis' => []];
$merged_emojis = merge_emojis($local_react['emojis'] ?? [], $remote['reactions']['emojis'] ?? []);

$local_slugs  = array_column($local_react['emojis'] ?? [], 'slug');
$merged_slugs = array_column($merged_emojis, 'slug');

if ($merged_slugs !== $local_slugs) {
    echo "MERGE  reactions.json\n";
    if (!$dry_run) json_write($react_path, ['emojis' => $merged_emojis]);
    $changes++;
} else {
    echo "SKIP   reactions.json\n";
}

// ── 同期状態を記録 ────────────────────────────────────────────

if (!$dry_run) {
    json_write(DATA_DIR . '/.sync_state.json', ['last_synced_at' => date('Y-m-d\TH:i:s')]);
}

echo "\n";
if ($dry_run) {
    echo "{$changes} 件の変更があります。--dry-run なしで実行すると適用されます。\n";
} else {
    echo "{$changes} 件を同期しました。\n";
}

// ── マージ関数 ────────────────────────────────────────────────

function merge_post(array $local, array $remote): ?array {
    $remote_newer = ($remote['updated_at'] ?? '') > ($local['updated_at'] ?? '');
    $base  = $remote_newer ? $remote : $local;
    $other = $remote_newer ? $local  : $remote;
    $changed = ($local['updated_at'] ?? '') !== ($remote['updated_at'] ?? '');

    // comments: 両方マージ
    $merged_comments = merge_comments($local['comments'] ?? [], $remote['comments'] ?? []);
    if ($merged_comments !== ($base['comments'] ?? [])) {
        $base['comments'] = $merged_comments;
        $changed = true;
    }

    // reactions: 絵文字ごとに大きい数を採用
    $merged_reactions = merge_reactions($local['reactions'] ?? [], $remote['reactions'] ?? []);
    if ($merged_reactions !== ($base['reactions'] ?? [])) {
        $base['reactions'] = $merged_reactions;
        $changed = true;
    }

    // categories: 和集合
    $merged_cats = array_values(array_unique(array_merge(
        $local['categories'] ?? [],
        $remote['categories'] ?? []
    )));
    if ($merged_cats !== ($base['categories'] ?? [])) {
        $base['categories'] = $merged_cats;
        $changed = true;
    }

    // labels: type+added_at で重複除去してマージ
    $merged_labels = merge_labels($local['labels'] ?? [], $remote['labels'] ?? []);
    if ($merged_labels !== ($base['labels'] ?? [])) {
        $base['labels'] = $merged_labels;
        $changed = true;
    }

    // strength: null でない方を優先、両方 null でなければ updated_at 新しい方
    $ls = $local['strength'] ?? null;
    $rs = $remote['strength'] ?? null;
    if ($ls !== $rs) {
        $base['strength'] = ($ls !== null && $rs !== null)
            ? ($remote_newer ? $rs : $ls)
            : ($ls ?? $rs);
        $changed = true;
    }

    // categorized_at: null でない方を優先
    $lc = $local['categorized_at'] ?? null;
    $rc = $remote['categorized_at'] ?? null;
    if ($lc !== $rc) {
        $base['categorized_at'] = $lc ?? $rc;
        $changed = true;
    }

    return $changed ? $base : null;
}

function merge_eval(array $local, array $remote): ?array {
    $changed = false;
    $base = $local;

    // evaluation: null でない方を優先、両方あれば generated_at 新しい方
    $le = $local['evaluation'] ?? null;
    $re = $remote['evaluation'] ?? null;
    if ($le !== $re) {
        if ($le === null) {
            $base['evaluation'] = $re;
        } elseif ($re !== null && ($re['generated_at'] ?? '') > ($le['generated_at'] ?? '')) {
            $base['evaluation'] = $re;
        }
        $changed = true;
    }

    // replies: id で重複除去してマージ
    $merged_replies = merge_replies($local['replies'] ?? [], $remote['replies'] ?? []);
    if ($merged_replies !== ($local['replies'] ?? [])) {
        $base['replies'] = $merged_replies;
        $changed = true;
    }

    return $changed ? $base : null;
}

function merge_comments(array $a, array $b): array {
    $map = [];
    foreach (array_merge($a, $b) as $c) {
        $key = ($c['created_at'] ?? '') . '|' . substr(md5($c['body'] ?? ''), 0, 8);
        $map[$key] = $c;
    }
    usort($map, fn($x, $y) => strcmp($x['created_at'] ?? '', $y['created_at'] ?? ''));
    return array_values($map);
}

function merge_reactions(array $a, array $b): array {
    $result = $a;
    foreach ($b as $emoji => $count) {
        $result[$emoji] = max($result[$emoji] ?? 0, (int)$count);
    }
    return $result;
}

function merge_labels(array $a, array $b): array {
    $map = [];
    foreach (array_merge($a, $b) as $l) {
        $key = ($l['type'] ?? '') . '|' . ($l['added_at'] ?? '');
        $map[$key] = $l;
    }
    return array_values($map);
}

function merge_replies(array $a, array $b): array {
    $map = [];
    foreach (array_merge($a, $b) as $r) {
        $map[$r['id'] ?? uniqid()] = $r;
    }
    usort($map, fn($x, $y) => strcmp($x['requested_at'] ?? '', $y['requested_at'] ?? ''));
    return array_values($map);
}

function merge_emojis(array $local, array $remote): array {
    $map = [];
    foreach ($local as $e) {
        $map[$e['slug']] = $e;
    }
    foreach ($remote as $e) {
        $map[$e['slug']] = $e;  // 本番を優先
    }
    return array_values($map);
}
