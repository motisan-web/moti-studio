<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>moti studio</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --accent: #6b5ce7;
      --accent2: #7c6aff;
      --bg: #f2f2f7;
      --surface: #ffffff;
      --surface2: #f0f0f5;
      --border: #dddde8;
      --text: #1c1c2e;
      --muted: #7070a0;
      --danger: #e0506a;
      --header-h: 52px;
    }

    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

    /* ── LOGIN ── */
    .login-wrap { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
    .login-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 32px 24px; width: 100%; max-width: 320px; display: flex; flex-direction: column; gap: 16px; }
    .login-logo { font-size: 20px; font-weight: 700; color: var(--accent); text-align: center; }
    .login-err { font-size: 12px; color: var(--danger); background: #fff0f3; border: 1px solid #f8c0cc; border-radius: 8px; padding: 8px 12px; display: none; }
    .login-err.show { display: block; }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-label { font-size: 10px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: .07em; }
    .form-input { background: var(--surface2); border: 1px solid var(--border); border-radius: 8px; padding: 10px 12px; color: var(--text); font-size: 14px; font-family: inherit; outline: none; width: 100%; }
    .form-input:focus { border-color: var(--accent); }
    .form-submit { background: var(--accent); color: #fff; border: none; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; width: 100%; }

    /* ── WIP ── */
    .wip-wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; padding: 40px 20px; gap: 16px; text-align: center; }
    .wip-icon { font-size: 48px; }
    .wip-title { font-size: 20px; font-weight: 700; color: var(--accent); }
    .wip-note { font-size: 14px; color: var(--muted); line-height: 1.7; }
    .wip-link { display: inline-block; margin-top: 8px; font-size: 13px; color: var(--accent); text-decoration: none; padding: 10px 20px; border: 1px solid var(--accent); border-radius: 20px; }
    .wip-link:hover { background: var(--accent); color: #fff; }
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
        showErr(json.error + (json.locked_until ? `（${json.locked_until} まで）` : ''));
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

<div class="wip-wrap">
  <div class="wip-icon">🚧</div>
  <div class="wip-title">スマホUI 準備中</div>
  <div class="wip-note">モバイル向けUIは現在設計中です。<br>PCブラウザからはフル機能で利用できます。</div>
  <a class="wip-link" href="/">PCレイアウトで開く</a>
</div>

<?php endif; ?>

</body>
</html>
