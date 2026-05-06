<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/lib/json.php';

$account_id = $_SESSION['account_id'] ?? null;
$is_auth    = $account_id !== null;

if ($is_auth) {
    $account    = json_read(DATA_ACCOUNTS . "/{$account_id}.json") ?? ['id' => $account_id, 'display_name' => $account_id, 'color' => '#6b5ce7'];
    $cats_data  = json_read(DATA_DIR . '/categories.json') ?? [];
    $categories = $cats_data['categories'] ?? [];
    $acc_files  = glob(DATA_ACCOUNTS . '/*.json') ?: [];
    $accounts   = array_values(array_filter(array_map('json_read', $acc_files)));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>moti studio</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --sidebar-width: 220px;
      --sidebar-col-width: 56px;
      --panel-width: 500px;
      --header-h: 56px;
      --bg: #f2f2f7;
      --surface: #ffffff;
      --surface2: #f0f0f5;
      --border: #dddde8;
      --text: #1c1c2e;
      --muted: #7070a0;
      --accent: #6b5ce7;
      --accent2: #7c6aff;
      --react-active: #ede9ff;
    }

    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); height: 100vh; display: flex; overflow: hidden; }

    /* ── LOGIN ── */
    .login-wrap { flex: 1; display: flex; align-items: center; justify-content: center; }
    .login-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 40px 36px; width: 340px; display: flex; flex-direction: column; gap: 20px; }
    .login-logo { font-size: 20px; font-weight: 700; color: var(--accent); text-align: center; }
    .login-err { font-size: 12px; color: #e0506a; background: #fff0f3; border: 1px solid #f8c0cc; border-radius: 8px; padding: 8px 12px; display: none; }
    .login-err.show { display: block; }

    /* ── SIDEBAR ── */
    .sidebar { width: var(--sidebar-width); background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; transition: width .2s ease; flex-shrink: 0; overflow: hidden; }
    .sidebar.col { width: var(--sidebar-col-width); }
    .sb-head { display: flex; align-items: center; justify-content: space-between; padding: 0 14px; height: var(--header-h); border-bottom: 1px solid var(--border); flex-shrink: 0; }
    .sidebar.col .sb-head { justify-content: center; }
    .logo { font-weight: 700; font-size: 15px; color: var(--accent); white-space: nowrap; }
    .sidebar.col .logo { display: none; }
    .icon-btn { background: none; border: none; color: var(--muted); cursor: pointer; padding: 5px; border-radius: 6px; line-height: 1; font-size: 15px; }
    .icon-btn:hover { color: var(--text); background: var(--surface2); }
    .sb-account { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-bottom: 1px solid var(--border); }
    .sidebar.col .sb-account { justify-content: center; padding: 12px 0; }
    .avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0; }
    .acc-name { font-size: 13px; font-weight: 600; white-space: nowrap; }
    .sidebar.col .acc-name { display: none; }
    .sb-nav { padding: 8px; flex: 1; overflow-y: auto; }
    .sec-title { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); padding: 12px 10px 4px; white-space: nowrap; }
    .sidebar.col .sec-title { display: none; }
    .nav-item { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 8px; cursor: pointer; font-size: 13px; color: var(--muted); white-space: nowrap; transition: background .12s, color .12s; }
    .nav-item:hover { background: var(--surface2); color: var(--text); }
    .nav-item.active { background: var(--react-active); color: var(--accent); }
    .sidebar.col .nav-item { justify-content: center; padding: 9px 0; }
    .nav-icon { font-size: 15px; flex-shrink: 0; }
    .sidebar.col .nav-label { display: none; }

    /* ── MAIN ── */
    .main-wrap { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }
    .topbar { height: var(--header-h); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 20px; background: var(--surface); flex-shrink: 0; }
    .topbar-title { font-size: 14px; font-weight: 600; color: var(--muted); }
    .post-btn { background: var(--accent); color: #fff; border: none; padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .12s; }
    .post-btn:hover { background: var(--accent2); }
    .content-area { flex: 1; display: flex; overflow: hidden; }

    /* ── MASONRY ── */
    .main-content { flex: 1; overflow-y: auto; padding: 20px; container-type: inline-size; container-name: main; min-width: 0; }
    .posts-grid { columns: 1; column-gap: 14px; }
    @container main (min-width: 500px) { .posts-grid { columns: 2; } }
    @container main (min-width: 780px) { .posts-grid { columns: 3; } }

    /* ── POST CARD ── */
    .post-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 14px; cursor: pointer; transition: border-color .12s, background .12s; display: flex; flex-direction: column; gap: 10px; break-inside: avoid; margin-bottom: 14px; }
    .post-card:hover { border-color: var(--accent); background: var(--surface2); }
    .post-card.active { border-color: var(--accent); }
    .post-meta { display: flex; align-items: center; justify-content: space-between; }
    .post-account { display: flex; align-items: center; gap: 6px; }
    .post-avatar { width: 22px; height: 22px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #fff; }
    .post-acc-name { font-size: 11px; color: var(--muted); }
    .post-date { font-size: 11px; color: var(--muted); }
    .post-title { font-size: 13px; font-weight: 700; line-height: 1.4; }
    .post-body { font-size: 12px; color: var(--text); }
    .post-body .md-line, .d-body .md-line { line-height: 1.75; }
    .post-body .md-li, .d-body .md-li { line-height: 1.75; padding-left: 10px; }
    .post-body blockquote, .d-body blockquote { border-left: 2px solid var(--accent); padding-left: 8px; color: var(--muted); font-style: italic; margin: 2px 0; }
    .more-btn { align-self: flex-start; background: none; border: none; color: var(--accent); font-size: 11px; cursor: pointer; padding: 0; }
    .more-btn:hover { text-decoration: underline; }
    .post-url { font-size: 11px; color: var(--accent); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-decoration: none; }
    .cats { display: flex; flex-wrap: wrap; gap: 4px; }
    .cat-tag { font-size: 10px; padding: 2px 7px; border-radius: 20px; background: var(--surface2); color: var(--muted); border: 1px solid var(--border); }
    .card-labels { display: flex; flex-wrap: wrap; gap: 4px; }
    .label-chip { font-size: 10px; padding: 2px 7px; border-radius: 20px; background: var(--surface2); border: 1px solid var(--border); color: var(--muted); display: flex; align-items: center; gap: 3px; }
    .reactions-row { display: flex; flex-wrap: wrap; gap: 5px; align-items: center; }
    .reaction { display: flex; align-items: center; gap: 3px; padding: 3px 8px; border-radius: 20px; background: var(--surface2); border: 1px solid var(--border); font-size: 12px; cursor: pointer; transition: background .12s, border-color .12s; user-select: none; }
    .reaction:hover { border-color: var(--accent); background: var(--react-active); }
    .r-count { font-size: 11px; color: var(--muted); }
    .palette-btn { width: 26px; height: 26px; border-radius: 50%; border: 1px dashed var(--border); background: none; color: var(--muted); font-size: 14px; cursor: pointer; line-height: 1; display: flex; align-items: center; justify-content: center; transition: border-color .12s, color .12s; }
    .palette-btn:hover { border-color: var(--accent); color: var(--accent); }

    /* ── REACTION PALETTE ── */
    .r-palette { position: fixed; z-index: 200; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 10px; display: none; box-shadow: 0 8px 32px rgba(0,0,0,.12); }
    .r-palette.open { display: block; }
    .palette-label { font-size: 10px; color: var(--muted); margin-bottom: 8px; text-align: center; }
    .palette-grid { display: grid; grid-template-columns: repeat(5, 36px); gap: 3px; }
    .p-emoji { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; transition: background .1s; }
    .p-emoji:hover { background: var(--surface2); }

    /* ── RIGHT PANEL ── */
    .right-panel { width: 0; overflow: hidden; border-left: 1px solid transparent; background: var(--surface); transition: width .22s ease, border-color .22s; display: flex; flex-direction: column; flex-shrink: 0; }
    .right-panel.open { width: var(--panel-width); border-color: var(--border); }
    .panel-head { display: flex; align-items: center; justify-content: space-between; padding: 0 18px; height: var(--header-h); border-bottom: 1px solid var(--border); min-width: var(--panel-width); flex-shrink: 0; }
    .panel-title { font-size: 13px; font-weight: 600; color: var(--muted); }
    .panel-body { flex: 1; overflow-y: auto; padding: 22px; min-width: var(--panel-width); }

    /* detail */
    .detail-head { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
    .detail-head .avatar { width: 38px; height: 38px; font-size: 16px; }
    .d-acc { font-size: 14px; font-weight: 600; }
    .d-date { font-size: 11px; color: var(--muted); margin-top: 2px; }
    .d-title { font-size: 18px; font-weight: 700; margin-bottom: 14px; line-height: 1.4; }
    .d-body { font-size: 13px; color: var(--text); }
    .d-body .md-li { padding-left: 14px; }
    .d-body blockquote { border-left: 3px solid var(--accent); padding-left: 12px; margin: 6px 0; }
    .intent-box { margin-top: 16px; padding: 12px 14px; background: var(--surface2); border-radius: 8px; border-left: 3px solid var(--muted); }
    .intent-label { font-size: 10px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: .07em; margin-bottom: 5px; }
    .intent-text { font-size: 12px; color: var(--muted); line-height: 1.7; }
    .d-url { display: block; margin-top: 12px; font-size: 12px; color: var(--accent); text-decoration: none; word-break: break-all; }
    .d-url:hover { text-decoration: underline; }
    .divider { border: none; border-top: 1px solid var(--border); margin: 20px 0; }

    /* labels */
    .labels-section { margin-bottom: 4px; }
    .labels-head { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); margin-bottom: 10px; }
    .label-row { display: flex; align-items: flex-start; gap: 8px; padding: 10px 12px; background: var(--surface2); border: 1px solid var(--border); border-radius: 8px; margin-bottom: 6px; }
    .label-icon { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
    .label-info { flex: 1; min-width: 0; }
    .label-name { font-size: 12px; font-weight: 600; margin-bottom: 2px; }
    .label-url { font-size: 11px; color: var(--accent); text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
    .label-url:hover { text-decoration: underline; }
    .label-memo { font-size: 11px; color: var(--muted); margin-top: 2px; line-height: 1.5; }
    .label-del { background: none; border: none; color: var(--muted); cursor: pointer; font-size: 12px; padding: 2px 4px; border-radius: 4px; flex-shrink: 0; }
    .label-del:hover { color: #e0506a; }
    .add-label-btn { display: flex; align-items: center; gap: 6px; padding: 8px 12px; border: 1px dashed var(--border); border-radius: 8px; background: none; color: var(--muted); font-size: 12px; cursor: pointer; width: 100%; transition: border-color .12s, color .12s; }
    .add-label-btn:hover { border-color: var(--accent); color: var(--text); }
    .label-form { background: var(--surface2); border: 1px solid var(--border); border-radius: 8px; padding: 12px; margin-top: 8px; display: flex; flex-direction: column; gap: 8px; }
    .label-form-row { display: flex; gap: 6px; }
    .label-form .form-submit-sm { padding: 7px 14px; border-radius: 7px; background: var(--accent); border: none; color: #fff; font-size: 12px; cursor: pointer; }
    .label-form .cancel-btn { padding: 7px 14px; border-radius: 7px; border: 1px solid var(--border); background: none; color: var(--muted); font-size: 12px; cursor: pointer; }

    /* comments */
    .comment-row { padding: 10px 12px; background: var(--surface2); border: 1px solid var(--border); border-radius: 8px; margin-bottom: 6px; display: flex; gap: 8px; align-items: flex-start; }
    .comment-body { font-size: 12px; color: var(--text); flex: 1; line-height: 1.6; }
    .comment-date { font-size: 10px; color: var(--muted); margin-top: 3px; }
    .comment-del { background: none; border: none; color: var(--muted); cursor: pointer; font-size: 12px; padding: 2px 4px; flex-shrink: 0; }
    .comment-del:hover { color: #e0506a; }
    .add-comment-row { display: flex; gap: 6px; margin-top: 6px; }

    /* evaluation */
    .eval-head { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); margin-bottom: 14px; }
    .eval-comment { font-size: 13px; line-height: 1.75; color: var(--text); margin-bottom: 18px; padding: 12px 14px; background: var(--surface2); border-radius: 8px; border-left: 3px solid var(--accent); }
    .eval-axes { display: flex; flex-direction: column; gap: 11px; }
    .eval-row-head { display: flex; justify-content: space-between; margin-bottom: 5px; }
    .eval-axis-label { font-size: 12px; color: var(--text); }
    .eval-score { font-size: 12px; color: var(--accent); font-weight: 600; }
    .eval-bar-bg { height: 4px; background: var(--surface2); border-radius: 2px; overflow: hidden; }
    .eval-bar { height: 100%; background: var(--accent); border-radius: 2px; }
    .eval-none { font-size: 12px; color: var(--muted); }

    /* forms */
    .create-form { display: flex; flex-direction: column; gap: 16px; }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-label { font-size: 10px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: .07em; }
    .form-input, .form-textarea, .form-select { background: var(--surface2); border: 1px solid var(--border); border-radius: 8px; padding: 9px 12px; color: var(--text); font-size: 13px; font-family: inherit; outline: none; transition: border-color .12s; width: 100%; }
    .form-input:focus, .form-textarea:focus, .form-select:focus { border-color: var(--accent); }
    .form-textarea { resize: vertical; min-height: 120px; line-height: 1.65; }
    .form-select option { background: var(--surface2); }
    .cat-toggles { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 8px; }
    .cat-toggle { font-size: 11px; padding: 3px 10px; border-radius: 20px; border: 1px solid var(--border); background: var(--surface2); color: var(--muted); cursor: pointer; transition: all .12s; }
    .cat-toggle.selected { background: var(--react-active); border-color: var(--accent); color: var(--accent); }
    .cat-toggle:hover { border-color: var(--accent); }
    .cat-add-row { display: flex; gap: 6px; }
    .cat-add-btn { flex-shrink: 0; padding: 9px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface2); color: var(--muted); font-size: 12px; cursor: pointer; transition: border-color .12s, color .12s; }
    .cat-add-btn:hover { border-color: var(--accent); color: var(--text); }
    .form-submit { background: var(--accent); color: #fff; border: none; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .12s; }
    .form-submit:hover { background: var(--accent2); }
    .form-submit:disabled { opacity: .5; cursor: not-allowed; }

    .mgmt-section { margin-bottom: 28px; }
    .mgmt-title { font-size: 13px; font-weight: 700; margin-bottom: 12px; }
    .mgmt-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface2); margin-bottom: 6px; font-size: 13px; }
    .mgmt-row-left { display: flex; align-items: center; gap: 8px; }
    .mgmt-count { font-size: 11px; color: var(--muted); }
    .emoji-upload-box { border: 2px dashed var(--border); border-radius: 10px; padding: 24px; text-align: center; color: var(--muted); font-size: 13px; margin-bottom: 10px; }
    .emoji-upload-box span { font-size: 24px; display: block; margin-bottom: 6px; }
    .empty-state { text-align: center; color: var(--muted); padding: 60px 20px; font-size: 13px; }

    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
  </style>
</head>
<body>

<?php if (!$is_auth): ?>

<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">moti studio</div>
    <div class="login-err" id="loginErr"></div>
    <div class="form-group">
      <label class="form-label">アカウントID</label>
      <input class="form-input" id="loginId" type="text" placeholder="moti" autocomplete="username">
    </div>
    <div class="form-group">
      <label class="form-label">パスワード</label>
      <input class="form-input" id="loginPw" type="password" autocomplete="current-password">
    </div>
    <button class="form-submit" id="loginBtn" onclick="doLogin()">ログイン</button>
  </div>
</div>

<script>
  document.getElementById('loginPw').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });

  async function doLogin() {
    const id  = document.getElementById('loginId').value.trim();
    const pw  = document.getElementById('loginPw').value;
    const btn = document.getElementById('loginBtn');
    const err = document.getElementById('loginErr');
    if (!id || !pw) { showErr('アカウントIDとパスワードを入力してください'); return; }

    btn.disabled = true; btn.textContent = '確認中…';
    try {
      const res  = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ account_id: id, password: pw }),
      });
      const json = await res.json();
      if (!res.ok) {
        const msg = json.error + (json.locked_until ? `（${json.locked_until} まで）` : '');
        showErr(msg);
        btn.disabled = false; btn.textContent = 'ログイン';
      } else {
        location.reload();
      }
    } catch {
      showErr('通信エラーが発生しました');
      btn.disabled = false; btn.textContent = 'ログイン';
    }
  }

  function showErr(msg) {
    const el = document.getElementById('loginErr');
    el.textContent = msg; el.classList.add('show');
  }
</script>

<?php else: ?>

<aside class="sidebar" id="sidebar">
  <div class="sb-head">
    <span class="logo">moti studio</span>
    <button class="icon-btn" id="collapseBtn" onclick="toggleSidebar()">◀</button>
  </div>
  <div class="sb-account">
    <div class="avatar" style="background:<?= htmlspecialchars($account['color'] ?? '#6b5ce7') ?>"><?= htmlspecialchars(mb_substr($account['display_name'] ?? $account_id, 0, 1)) ?></div>
    <span class="acc-name"><?= htmlspecialchars($account['display_name'] ?? $account_id) ?></span>
  </div>
  <nav class="sb-nav">
    <div class="sec-title">メニュー</div>
    <div class="nav-item active" id="navTimeline" onclick="navTo(this,'timeline')"><span class="nav-icon">📋</span><span class="nav-label">タイムライン</span></div>
    <div class="nav-item" onclick="navTo(this,'archive')"><span class="nav-icon">📦</span><span class="nav-label">アーカイブ</span></div>
    <div class="sec-title">カテゴリ</div>
    <div class="nav-item active" id="navAll" onclick="filterCat(this,'all')"><span class="nav-icon">🌐</span><span class="nav-label">すべて</span></div>
    <div id="catNavItems"></div>
    <div class="sec-title">管理</div>
    <div class="nav-item" onclick="openCatMgmt()"><span class="nav-icon">🏷</span><span class="nav-label">カテゴリ管理</span></div>
    <div class="nav-item" onclick="openReactMgmt()"><span class="nav-icon">😀</span><span class="nav-label">リアクション管理</span></div>
    <div class="nav-item" onclick="doLogout()"><span class="nav-icon">🚪</span><span class="nav-label">ログアウト</span></div>
  </nav>
</aside>

<div class="main-wrap">
  <div class="topbar">
    <span class="topbar-title" id="topbarTitle">タイムライン</span>
    <button class="post-btn" onclick="openCreate()">＋ 投稿する</button>
  </div>
  <div class="content-area">
    <main class="main-content">
      <div class="posts-grid" id="postsGrid"><div class="empty-state">読み込み中…</div></div>
    </main>
    <aside class="right-panel" id="rightPanel">
      <div class="panel-head">
        <span class="panel-title" id="panelTitle">投稿詳細</span>
        <button class="icon-btn" onclick="closePanel()">✕</button>
      </div>
      <div class="panel-body" id="panelBody"></div>
    </aside>
  </div>
</div>

<div class="r-palette" id="rPalette">
  <div class="palette-label">リアクションを追加</div>
  <div class="palette-grid" id="paletteGrid"></div>
</div>

<script>
const INIT = {
  account:    <?= json_encode($account,    JSON_UNESCAPED_UNICODE) ?>,
  accounts:   <?= json_encode($accounts,   JSON_UNESCAPED_UNICODE) ?>,
  categories: <?= json_encode($categories, JSON_UNESCAPED_UNICODE) ?>,
};

const CAT_ICONS = {
  '思想':'💭','メンタリティー':'🧘','ストラテジー':'🎯','認知・行動':'🧠',
  '仕事':'💼','プロジェクト':'📁','やりたい事':'⭐','AI':'🤖','技術':'⚙️',
  '言語':'🗣','引用':'💬','ブックマーク':'🔖','メモ':'📝','健康':'🌿',
  '食べ物':'🍱','料理':'👨‍🍳','体験':'🫧','ゲーム':'🎮','お絵描き':'🎨',
  '人間関係':'🤝','お役立ち':'💁','お金・投資':'💰','書籍':'📚',
  'マンガ・アニメ':'🎌','面白投稿':'😂','もちさん設定':'🤫','もち関連':'🫶',
};

const LABEL_TYPES = [
  { key:'misskey',     label:'Misskey投稿済み', icon:'🦋' },
  { key:'twitter',     label:'Twitter投稿済み', icon:'🐦' },
  { key:'resolved',    label:'解決済み',         icon:'✅' },
  { key:'implemented', label:'実装済み',         icon:'🔧' },
  { key:'cancelled',   label:'キャンセル',       icon:'❌' },
  { key:'verified',    label:'検証済み',         icon:'🔬' },
];

const PALETTE_EMOJIS = ['💡','✨','🔥','👀','🤔','💭','🎯','🌟','💪','😂','❤️','🙏','👏','🤯','💯','🦋','🌊','🎵','🍀','⚡'];

let posts           = [];
let currentFilter   = 'all';
let currentView     = 'timeline';
let activeId        = null;
let paletteTargetId = null;
let selectedCats    = [];

// ── API ──────────────────────────────────────────────────

async function api(method, path, body = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const res  = await fetch('/api/' + path, opts);
  const json = await res.json();
  if (!res.ok) throw new Error(json.error || 'エラーが発生しました');
  return json.data;
}

// ── INIT ─────────────────────────────────────────────────

function buildCatNav() {
  document.getElementById('catNavItems').innerHTML = INIT.categories.map(c =>
    `<div class="nav-item" onclick="filterCat(this,'${esc(c)}')">`+
    `<span class="nav-icon">${CAT_ICONS[c] || '🏷'}</span>`+
    `<span class="nav-label">${esc(c)}</span></div>`
  ).join('');
}

function buildPalette() {
  document.getElementById('paletteGrid').innerHTML = PALETTE_EMOJIS.map(e =>
    `<div class="p-emoji" onclick="addReaction('${e}')">${e}</div>`).join('');
}

// ── LOAD & RENDER ─────────────────────────────────────────

async function loadPosts() {
  const params = new URLSearchParams({ limit: '50' });
  if (currentFilter !== 'all') params.set('category', currentFilter);
  if (currentView === 'archive') params.set('archive', 'true');

  try {
    const data = await api('GET', 'posts?' + params);
    posts = data.posts;
    renderGrid();
  } catch(err) {
    document.getElementById('postsGrid').innerHTML = `<div class="empty-state">${esc(err.message)}</div>`;
  }
}

function renderGrid() {
  const grid = document.getElementById('postsGrid');
  if (!posts.length) { grid.innerHTML = '<div class="empty-state">投稿がありません</div>'; return; }
  grid.innerHTML = posts.map(renderCard).join('');
}

function renderCard(p) {
  const { html: bodyHtml, truncated } = previewBody(p.body);
  const cats    = (p.categories || []).map(c => `<span class="cat-tag">${esc(c)}</span>`).join('');
  const urlHtml = p.url ? `<a class="post-url" href="${esc(p.url)}" target="_blank" onclick="event.stopPropagation()">🔗 ${esc(p.url)}</a>` : '';
  const more    = truncated ? `<button class="more-btn" onclick="openDetail('${p.id}')">もっと見る →</button>` : '';
  const chips   = (p.labels || []).map(l => {
    const lt = LABEL_TYPES.find(t => t.key === l.type);
    return lt ? `<span class="label-chip">${lt.icon} ${lt.label}</span>` : '';
  }).join('');

  return `<div class="post-card${activeId===p.id?' active':''}" id="card-${p.id}" onclick="openDetail('${p.id}')">
    <div class="post-meta">
      <div class="post-account"><div class="post-avatar">${esc(p.account_id[0])}</div><span class="post-acc-name">@${esc(p.account_id)}</span></div>
      <span class="post-date">${p.created_at.slice(0,10)}</span>
    </div>
    ${p.title ? `<div class="post-title">${esc(p.title)}</div>` : ''}
    <div class="post-body">${bodyHtml}</div>
    ${more}${urlHtml}
    ${cats ? `<div class="cats">${cats}</div>` : ''}
    ${chips ? `<div class="card-labels">${chips}</div>` : ''}
    <div class="reactions-row" onclick="event.stopPropagation()">
      ${reactionsHtml(p)}
      <button class="palette-btn" onclick="openPalette(event,'${p.id}')">＋</button>
    </div>
  </div>`;
}

function reactionsHtml(p) {
  return Object.entries(p.reactions || {}).map(([emoji, count]) =>
    `<span class="reaction" onclick="doReact(event,'${p.id}','${esc(emoji)}')">${emoji}<span class="r-count">${count}</span></span>`
  ).join('');
}

// ── MARKDOWN ─────────────────────────────────────────────

function mdToHtml(text) {
  return (text || '').split('\n').map(line => {
    if (line.startsWith('> ')) return `<blockquote>${esc(line.slice(2))}</blockquote>`;
    if (line.startsWith('- '))  return `<div class="md-li">• ${esc(line.slice(2))}</div>`;
    if (line === '')             return '<div style="height:.5em"></div>';
    return `<div class="md-line">${esc(line)}</div>`;
  }).join('');
}

function previewBody(text, max = 200) {
  if (!text || text.length <= max) return { html: mdToHtml(text), truncated: false };
  let cut = max;
  while (cut > 80 && !['\n','。','！','？'].includes(text[cut])) cut--;
  return { html: mdToHtml(text.slice(0, cut)) + '<div class="md-line" style="color:var(--muted)">…</div>', truncated: true };
}

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── SIDEBAR ──────────────────────────────────────────────

function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  document.getElementById('collapseBtn').textContent = sb.classList.toggle('col') ? '▶' : '◀';
}

function navTo(el, view) {
  currentView = view; currentFilter = 'all';
  document.querySelectorAll('.sb-nav .nav-item').forEach(n => n.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('navAll').classList.add('active');
  const titles = { timeline:'タイムライン', archive:'アーカイブ' };
  document.getElementById('topbarTitle').textContent = titles[view] || view;
  closePanel(); loadPosts();
}

function filterCat(el, cat) {
  currentFilter = cat;
  document.querySelectorAll('.sb-nav .nav-item').forEach(n => n.classList.remove('active'));
  el.classList.add('active');
  if (cat === 'all') document.getElementById('navTimeline').classList.add('active');
  loadPosts();
}

async function doLogout() {
  await api('POST', 'auth/logout');
  location.reload();
}

// ── REACTIONS ────────────────────────────────────────────

async function doReact(e, postId, emoji) {
  e.stopPropagation();
  try {
    const data = await api('POST', `posts/${postId}/react`, { emoji });
    const p = posts.find(p => p.id === postId);
    if (p) {
      p.reactions = data.reactions;
      renderGrid();
      if (activeId === postId) {
        const el = document.getElementById('detailReactions');
        if (el) el.innerHTML = reactionsHtml(p) + `<button class="palette-btn" onclick="openPalette(event,'${p.id}')">＋</button>`;
      }
    }
  } catch(err) { alert(err.message); }
}

// ── PALETTE ──────────────────────────────────────────────

function openPalette(e, postId) {
  e.stopPropagation();
  paletteTargetId = postId;
  const pal = document.getElementById('rPalette');
  pal.classList.add('open');
  const br = e.currentTarget.getBoundingClientRect();
  let top  = br.top - 180;
  if (top < 8) top = br.bottom + 8;
  let left = br.left;
  if (left + 200 > window.innerWidth - 8) left = window.innerWidth - 208;
  pal.style.top = top + 'px'; pal.style.left = left + 'px';
}

async function addReaction(emoji) {
  if (!paletteTargetId) return;
  const id = paletteTargetId;
  closePalette();
  await doReact({ stopPropagation: () => {} }, id, emoji);
}

function closePalette() { document.getElementById('rPalette').classList.remove('open'); paletteTargetId = null; }
document.addEventListener('click', closePalette);

// ── PANEL: DETAIL ─────────────────────────────────────────

async function openDetail(postId) {
  activeId = postId; renderGrid();
  document.getElementById('panelTitle').textContent = '投稿詳細';
  document.getElementById('panelBody').innerHTML = '<div class="empty-state">読み込み中…</div>';
  document.getElementById('rightPanel').classList.add('open');

  try {
    const [post, evalData] = await Promise.all([
      api('GET', `posts/${postId}`),
      api('GET', `evals/${postId}`),
    ]);
    const idx = posts.findIndex(p => p.id === postId);
    if (idx >= 0) posts[idx] = post;
    renderDetail(post, evalData);
  } catch(err) {
    document.getElementById('panelBody').innerHTML = `<div class="empty-state">${esc(err.message)}</div>`;
  }
}

function renderDetail(p, evalData) {
  if (activeId !== p.id) return;
  const intentHtml = p.intent ? `<div class="intent-box"><div class="intent-label">補足・意図</div><div class="intent-text">${esc(p.intent)}</div></div>` : '';
  const urlHtml    = p.url ? `<a class="d-url" href="${esc(p.url)}" target="_blank">🔗 ${esc(p.url)}</a>` : '';
  const cats       = (p.categories || []).map(c => `<span class="cat-tag">${esc(c)}</span>`).join('');

  document.getElementById('panelBody').innerHTML = `
    <div class="detail-head">
      <div class="avatar">${esc(p.account_id[0])}</div>
      <div><div class="d-acc">@${esc(p.account_id)}</div><div class="d-date">${p.created_at.slice(0,10)}</div></div>
    </div>
    ${p.title ? `<div class="d-title">${esc(p.title)}</div>` : ''}
    <div class="d-body">${mdToHtml(p.body)}</div>
    ${intentHtml}${urlHtml}
    <hr class="divider">
    <div id="detailReactions" class="reactions-row" style="margin-bottom:14px">
      ${reactionsHtml(p)}<button class="palette-btn" onclick="openPalette(event,'${p.id}')">＋</button>
    </div>
    ${cats ? `<div class="cats" style="margin-bottom:4px">${cats}</div>` : ''}
    <hr class="divider">
    ${labelsHtml(p)}
    <hr class="divider">
    ${commentsHtml(p)}
    <hr class="divider">
    ${evalHtml(evalData)}`;
}

async function refreshPost(postId) {
  const [post, evalData] = await Promise.all([
    api('GET', `posts/${postId}`),
    api('GET', `evals/${postId}`),
  ]);
  const idx = posts.findIndex(p => p.id === postId);
  if (idx >= 0) posts[idx] = post;
  renderGrid();
  if (activeId === postId) renderDetail(post, evalData);
}

// ── LABELS ───────────────────────────────────────────────

function labelsHtml(p) {
  const rows = (p.labels || []).map((l, i) => {
    const lt = LABEL_TYPES.find(t => t.key === l.type);
    if (!lt) return '';
    return `<div class="label-row">
      <span class="label-icon">${lt.icon}</span>
      <div class="label-info">
        <div class="label-name">${lt.label}</div>
        ${l.url ? `<a class="label-url" href="${esc(l.url)}" target="_blank">${esc(l.url)}</a>` : ''}
        ${l.memo ? `<div class="label-memo">${esc(l.memo)}</div>` : ''}
      </div>
      <button class="label-del" onclick="removeLabel('${p.id}',${i})">✕</button>
    </div>`;
  }).join('');
  return `<div class="labels-section">
    <div class="labels-head">ラベル</div>
    ${rows}
    <button class="add-label-btn" onclick="showLabelForm('${p.id}')">＋ ラベルを追加</button>
    <div id="labelForm-${p.id}"></div>
  </div>`;
}

function showLabelForm(postId) {
  const opts = LABEL_TYPES.map(t => `<option value="${t.key}">${t.icon} ${t.label}</option>`).join('');
  document.getElementById(`labelForm-${postId}`).innerHTML = `
    <div class="label-form">
      <div class="form-group"><label class="form-label">種別</label><select class="form-select" id="lf-type-${postId}">${opts}</select></div>
      <div class="form-group"><label class="form-label">URL（任意）</label><input class="form-input" id="lf-url-${postId}" type="url" placeholder="https://..."></div>
      <div class="form-group"><label class="form-label">メモ（任意）</label><textarea class="form-input" id="lf-memo-${postId}" style="min-height:56px;resize:vertical"></textarea></div>
      <div class="label-form-row">
        <button class="form-submit-sm" onclick="addLabel('${postId}')">追加</button>
        <button class="cancel-btn" onclick="document.getElementById('labelForm-${postId}').innerHTML=''">キャンセル</button>
      </div>
    </div>`;
}

async function addLabel(postId) {
  try {
    await api('POST', `posts/${postId}/label`, {
      type: document.getElementById(`lf-type-${postId}`).value,
      url:  document.getElementById(`lf-url-${postId}`).value.trim(),
      memo: document.getElementById(`lf-memo-${postId}`).value.trim(),
    });
    await refreshPost(postId);
  } catch(err) { alert(err.message); }
}

async function removeLabel(postId, idx) {
  if (!confirm('ラベルを削除しますか？')) return;
  try {
    await api('DELETE', `posts/${postId}/label/${idx}`);
    await refreshPost(postId);
  } catch(err) { alert(err.message); }
}

// ── COMMENTS ─────────────────────────────────────────────

function commentsHtml(p) {
  const rows = (p.comments || []).map((c, i) => `
    <div class="comment-row">
      <div style="flex:1">
        <div class="comment-body">${esc(c.body)}</div>
        <div class="comment-date">${c.created_at.slice(0,16).replace('T',' ')}</div>
      </div>
      <button class="comment-del" onclick="removeComment('${p.id}',${i})">✕</button>
    </div>`).join('');
  return `<div>
    <div class="labels-head">自己コメント</div>
    ${rows}
    <div class="add-comment-row">
      <input class="form-input" id="commentInput-${p.id}" placeholder="突っ込み・補足…"
        onkeydown="if(event.key==='Enter'&&!event.shiftKey)addComment('${p.id}')">
      <button class="cat-add-btn" onclick="addComment('${p.id}')">追加</button>
    </div>
  </div>`;
}

async function addComment(postId) {
  const inp  = document.getElementById(`commentInput-${postId}`);
  const text = inp.value.trim();
  if (!text) return;
  try {
    await api('POST', `posts/${postId}/comment`, { body: text });
    await refreshPost(postId);
  } catch(err) { alert(err.message); }
}

async function removeComment(postId, idx) {
  try {
    await api('DELETE', `posts/${postId}/comment/${idx}`);
    await refreshPost(postId);
  } catch(err) { alert(err.message); }
}

// ── EVAL ─────────────────────────────────────────────────

function evalHtml(evalData) {
  if (!evalData || !evalData.evaluation) {
    return `<div class="eval-head">Claude 評価</div><div class="eval-none">未評価です</div>`;
  }
  const ev   = evalData.evaluation;
  const axes = (ev.axes || []).map(ax => `
    <div>
      <div class="eval-row-head"><span class="eval-axis-label">${esc(ax.label)}</span><span class="eval-score">${ax.score}</span></div>
      <div class="eval-bar-bg"><div class="eval-bar" style="width:${ax.score}%"></div></div>
    </div>`).join('');
  return `<div class="eval-head">Claude 評価</div>
    <div class="eval-comment">${esc(ev.comment)}</div>
    <div class="eval-axes">${axes}</div>`;
}

// ── PANEL: CREATE ─────────────────────────────────────────

function openCreate() {
  activeId = null; selectedCats = []; renderGrid();
  document.getElementById('panelTitle').textContent = '新規投稿';

  const accOpts    = INIT.accounts.map(a =>
    `<option value="${esc(a.id)}"${a.id===INIT.account.id?' selected':''}>${esc(a.display_name)}</option>`).join('');
  const catToggles = INIT.categories.map(c =>
    `<span class="cat-toggle" onclick="toggleCatCreate(this,'${esc(c)}')">${esc(c)}</span>`).join('');

  document.getElementById('panelBody').innerHTML = `
    <div class="create-form">
      <div class="form-group"><label class="form-label">タイトル（任意）</label><input class="form-input" id="cf-title" type="text" placeholder="タイトルを入力..."></div>
      <div class="form-group"><label class="form-label">本文</label><textarea class="form-textarea" id="cf-body" placeholder="思考を書く…（Markdown対応）"></textarea></div>
      <div class="form-group"><label class="form-label">補足・意図（任意）</label><textarea class="form-textarea" id="cf-intent" style="min-height:72px" placeholder="背景・経緯・補足メモ..."></textarea></div>
      <div class="form-group"><label class="form-label">参考URL（任意）</label><input class="form-input" id="cf-url" type="url" placeholder="https://..."></div>
      <div class="form-group">
        <label class="form-label">カテゴリ</label>
        <div class="cat-toggles" id="cfCatToggles">${catToggles}</div>
        <div class="cat-add-row">
          <input class="form-input" id="cf-newcat" type="text" placeholder="新しいカテゴリ...">
          <button class="cat-add-btn" onclick="addCatToCreate()">追加</button>
        </div>
      </div>
      <div class="form-group"><label class="form-label">アカウント</label><select class="form-select" id="cf-account">${accOpts}</select></div>
      <button class="form-submit" id="cfSubmit" onclick="submitCreate()">投稿する</button>
    </div>`;
  document.getElementById('rightPanel').classList.add('open');
}

function toggleCatCreate(el, cat) {
  el.classList.toggle('selected');
  if (el.classList.contains('selected')) selectedCats.push(cat);
  else selectedCats = selectedCats.filter(c => c !== cat);
}

function addCatToCreate() {
  const inp = document.getElementById('cf-newcat'), val = inp.value.trim();
  if (!val) return;
  const el = document.createElement('span');
  el.className = 'cat-toggle selected'; el.textContent = val;
  el.onclick = () => toggleCatCreate(el, val);
  selectedCats.push(val);
  document.getElementById('cfCatToggles').appendChild(el); inp.value = '';
}

async function submitCreate() {
  const body = document.getElementById('cf-body').value.trim();
  if (!body) { alert('本文を入力してください'); return; }
  const btn = document.getElementById('cfSubmit');
  btn.disabled = true; btn.textContent = '投稿中…';
  try {
    await api('POST', 'posts', {
      account_id: document.getElementById('cf-account').value,
      title:      document.getElementById('cf-title').value.trim(),
      body,
      intent:     document.getElementById('cf-intent').value.trim(),
      url:        document.getElementById('cf-url').value.trim(),
      categories: selectedCats,
    });
    closePanel(); await loadPosts();
  } catch(err) {
    alert(err.message);
    btn.disabled = false; btn.textContent = '投稿する';
  }
}

// ── PANEL: MGMT ──────────────────────────────────────────

function openCatMgmt() {
  activeId = null; renderGrid();
  document.getElementById('panelTitle').textContent = 'カテゴリ管理';
  const counts = {};
  posts.forEach(p => (p.categories || []).forEach(c => counts[c] = (counts[c] || 0) + 1));
  const rows = INIT.categories.map(c =>
    `<div class="mgmt-row"><div class="mgmt-row-left"><span>${esc(c)}</span><span class="mgmt-count">${counts[c]||0} 件</span></div></div>`
  ).join('');
  document.getElementById('panelBody').innerHTML = `<div class="mgmt-section"><div class="mgmt-title">カテゴリ一覧</div>${rows}</div>`;
  document.getElementById('rightPanel').classList.add('open');
}

function openReactMgmt() {
  activeId = null; renderGrid();
  document.getElementById('panelTitle').textContent = 'リアクション管理';
  const used = [...new Set(posts.flatMap(p => Object.keys(p.reactions || {})))];
  const rows = used.map(e =>
    `<div class="mgmt-row"><div class="mgmt-row-left"><span style="font-size:20px">${e}</span></div></div>`
  ).join('');
  document.getElementById('panelBody').innerHTML = `
    <div class="mgmt-section"><div class="mgmt-title">使用中のリアクション</div>${rows || '<div class="eval-none">まだありません</div>'}</div>
    <div class="mgmt-section">
      <div class="mgmt-title">カスタム絵文字を追加</div>
      <div class="emoji-upload-box"><span>🖼</span>画像をドロップ（PNG / GIF）<br><span style="font-size:11px">横長・GIF可</span></div>
      <div class="form-group"><label class="form-label">スラッグ</label><input class="form-input" type="text" placeholder=":emoji-name:"></div>
      <button class="form-submit" style="margin-top:8px">登録する</button>
    </div>`;
  document.getElementById('rightPanel').classList.add('open');
}

function closePanel() {
  activeId = null;
  document.getElementById('rightPanel').classList.remove('open');
  renderGrid();
}

// ── BOOT ─────────────────────────────────────────────────

buildCatNav();
buildPalette();
loadPosts();
</script>

<?php endif; ?>
</body>
</html>
