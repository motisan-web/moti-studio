<?php
// GET /api/search?q={query}

auth_check();

if ($method !== 'GET') res_err('Not found', 404);

$q      = trim($_GET['q'] ?? '');
$before = $_GET['before'] ?? null;
$limit  = min((int)($_GET['limit'] ?? 20), 50);

if ($q === '') res_err('q は必須です', 400);

$q_lower = mb_strtolower($q);

$files = glob(DATA_POSTS . '/*.json');
if (!$files) { res_ok(['posts' => [], 'next_cursor' => null, 'has_more' => false]); }

$matched = [];
foreach ($files as $f) {
    $p = json_read($f);
    if (!$p || is_archived($p)) continue;
    $haystack = mb_strtolower(($p['title'] ?? '') . ' ' . ($p['body'] ?? '') . ' ' . ($p['intent'] ?? ''));
    if (str_contains($haystack, $q_lower)) $matched[] = $p;
}

usort($matched, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

if ($before !== null) {
    $matched = array_filter($matched, fn($p) => $p['created_at'] < $before);
    $matched = array_values($matched);
}

$page     = array_slice($matched, 0, $limit + 1);
$has_more = count($page) > $limit;
if ($has_more) array_pop($page);

res_ok([
    'posts'       => $page,
    'next_cursor' => $has_more ? end($page)['created_at'] : null,
    'has_more'    => $has_more,
]);
