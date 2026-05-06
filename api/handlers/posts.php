<?php
// /api/posts/*

$id  = $segments[1] ?? null;
$sub = $segments[2] ?? null;
$idx = isset($segments[3]) ? (int)$segments[3] : null;

// ── helpers ──────────────────────────────────────────────

function post_path(string $id): string {
    return DATA_POSTS . "/{$id}.json";
}

function post_load(string $id): ?array {
    return json_read(post_path($id));
}

function post_save(array $post): void {
    $post['updated_at'] = now_iso();
    json_write(post_path($post['id']), $post);
}

function is_archived(array $post): bool {
    return !empty($post['archive_at']) && now_iso() >= $post['archive_at'];
}

function posts_load_all(): array {
    $files = glob(DATA_POSTS . '/*.json');
    if (!$files) return [];
    $posts = [];
    foreach ($files as $f) {
        $p = json_read($f);
        if ($p) $posts[] = $p;
    }
    // 新しい順
    usort($posts, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
    return $posts;
}

// ── GET /api/posts/random ────────────────────────────────

if ($method === 'GET' && $id === 'random') {
    auth_check();
    $all = array_filter(posts_load_all(), fn($p) => !is_archived($p));
    if (empty($all)) res_ok(null);
    $all = array_values($all);
    res_ok($all[array_rand($all)]);
}

// ── sub-resource routes ───────────────────────────────────

if ($id !== null && $sub !== null) {
    auth_check();

    $post = post_load($id);
    if (!$post) res_err('投稿が見つかりません', 404);

    // POST /api/posts/{id}/react
    if ($method === 'POST' && $sub === 'react') {
        $body  = req_json();
        $emoji = trim($body['emoji'] ?? '');
        if ($emoji === '') res_err('emoji は必須です', 400);

        if (!isset($post['reactions'][$emoji])) {
            $post['reactions'][$emoji] = 0;
        }
        $post['reactions'][$emoji]++;
        post_save($post);
        res_ok(['reactions' => $post['reactions']]);
    }

    // POST /api/posts/{id}/comment
    if ($method === 'POST' && $sub === 'comment') {
        $body = req_json();
        $text = trim($body['body'] ?? '');
        if ($text === '') res_err('body は必須です', 400);

        $post['comments'][] = ['body' => $text, 'created_at' => now_iso()];
        post_save($post);
        res_ok(['comments' => $post['comments']]);
    }

    // DELETE /api/posts/{id}/comment/{index}
    if ($method === 'DELETE' && $sub === 'comment' && $idx !== null) {
        if (!isset($post['comments'][$idx])) res_err('コメントが見つかりません', 404);
        array_splice($post['comments'], $idx, 1);
        post_save($post);
        res_ok(['comments' => $post['comments']]);
    }

    // POST /api/posts/{id}/label
    if ($method === 'POST' && $sub === 'label') {
        $body  = req_json();
        $type  = $body['type'] ?? '';
        $valid = ['misskey', 'twitter', 'resolved', 'implemented', 'cancelled', 'verified'];
        if (!in_array($type, $valid, true)) res_err('type が不正です', 400);

        $post['labels'][] = [
            'type'     => $type,
            'url'      => trim($body['url']  ?? ''),
            'memo'     => trim($body['memo'] ?? ''),
            'added_at' => now_iso(),
        ];
        post_save($post);
        res_ok(['labels' => $post['labels']]);
    }

    // DELETE /api/posts/{id}/label/{index}
    if ($method === 'DELETE' && $sub === 'label' && $idx !== null) {
        if (!isset($post['labels'][$idx])) res_err('ラベルが見つかりません', 404);
        array_splice($post['labels'], $idx, 1);
        post_save($post);
        res_ok(['labels' => $post['labels']]);
    }

    res_err('Not found', 404);
}

// ── collection routes ─────────────────────────────────────

if ($id === null) {

    // GET /api/posts
    if ($method === 'GET') {
        auth_check();

        $before     = $_GET['before']     ?? null;
        $limit      = min((int)($_GET['limit'] ?? 20), 50);
        $cat        = $_GET['category']   ?? null;
        $account_id = $_GET['account_id'] ?? null;
        $archive    = isset($_GET['archive']) && $_GET['archive'] === 'true';

        $all = posts_load_all();

        $filtered = array_filter($all, function ($p) use ($before, $cat, $account_id, $archive) {
            if ($archive !== is_archived($p)) return false;
            if ($before !== null && $p['created_at'] >= $before) return false;
            if ($cat !== null && !in_array($cat, $p['categories'] ?? [], true)) return false;
            if ($account_id !== null && $p['account_id'] !== $account_id) return false;
            return true;
        });

        $page  = array_slice(array_values($filtered), 0, $limit + 1);
        $has_more = count($page) > $limit;
        if ($has_more) array_pop($page);

        res_ok([
            'posts'       => $page,
            'next_cursor' => $has_more ? end($page)['created_at'] : null,
            'has_more'    => $has_more,
        ]);
    }

    // POST /api/posts
    if ($method === 'POST') {
        auth_check();

        $body = req_json();
        $text = trim($body['body'] ?? '');
        if ($text === '') res_err('body は必須です', 400);

        $account_id = trim($body['account_id'] ?? auth_account_id());
        if ($account_id === '') res_err('account_id は必須です', 400);

        $id   = post_new_id();
        $now  = now_iso();
        $post = [
            'id'             => $id,
            'account_id'     => $account_id,
            'title'          => trim($body['title']   ?? ''),
            'body'           => $text,
            'intent'         => trim($body['intent']  ?? '') ?: null,
            'url'            => trim($body['url']     ?? '') ?: null,
            'categories'     => $body['categories']  ?? [],
            'labels'         => [],
            'reactions'      => (object)[],
            'comments'       => [],
            'repost'         => (bool)($body['repost'] ?? false),
            'repost_from'    => trim($body['repost_from'] ?? '') ?: null,
            'archive_at'     => $body['archive_at']  ?? null,
            'categorized_at' => null,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        json_write(post_path($id), $post);
        res_ok($post, 201);
    }

    res_err('Not found', 404);
}

// ── single resource routes ────────────────────────────────

$post = post_load($id);
if (!$post) res_err('投稿が見つかりません', 404);

// GET /api/posts/{id}
if ($method === 'GET') {
    auth_check();
    res_ok($post);
}

// PUT /api/posts/{id}
if ($method === 'PUT') {
    auth_check();

    $body    = req_json();
    $allowed = ['title', 'body', 'intent', 'url', 'archive_at'];
    foreach ($allowed as $key) {
        if (array_key_exists($key, $body)) {
            $post[$key] = is_string($body[$key]) ? trim($body[$key]) : $body[$key];
        }
    }
    post_save($post);
    res_ok($post);
}

// DELETE /api/posts/{id}
if ($method === 'DELETE') {
    auth_check();
    unlink(post_path($id));
    // eval も削除
    $eval_path = DATA_EVALS . "/{$id}.json";
    if (file_exists($eval_path)) unlink($eval_path);
    res_ok(['ok' => true]);
}

res_err('Not found', 404);
