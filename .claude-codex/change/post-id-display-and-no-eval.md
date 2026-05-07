# 投稿ID表示・評価不要フラグ・クリップボード修正

> 2026-05-07

---

## 変更内容

### 投稿IDを詳細パネルに表示（c63651a）

- `views/pc.php` `renderDetail` — ヘッダー直下に `.post-id-row` を追加
  - ID をモノスペースで表示、クリックでコピー
- CSS: `.post-id-row` / `.post-id-label` / `.post-id-value` を追加

### 「評価不要」フラグ（c63651a）

- `api/handlers/posts.php`
  - POST（新規作成）: `no_eval: (bool)($body['no_eval'] ?? false)` を追加
  - PUT（更新）: `$allowed` 配列に `'no_eval'` を追加
- `views/pc.php`
  - `renderDetail`: カテゴリ行の下に「評価不要 / 評価する」トグルボタンを追加
    - クリックで `toggleNoEval(postId, current)` → PUT → `refreshPost`
  - `openCreate` フォーム: 「評価不要にする」チェックボックス `#cf-no-eval` を追加
  - `submitCreate`: `no_eval: document.getElementById('cf-no-eval').checked` を送信
  - `openEdit` フォーム: `#ef-no-eval` チェックボックスを追加（既存値を `checked` に反映）
  - `submitEdit`: `no_eval: document.getElementById('ef-no-eval').checked` を送信
- `.claude-codex/spec/data-schema.md`: `no_eval` フィールドを定義に追加

### クリップボード非HTTPS対応（今回）

- `views/pc.php` に `copyText(text)` / `copyTextFallback(text)` を追加
  - `navigator.clipboard.writeText` が使えない環境（HTTP/ローカル）では `execCommand('copy')` にフォールバック
- `renderDetail` の `onclick` を `navigator.clipboard.writeText(...)` から `copyText(...)` に変更

---

## 作業順序の反省

前回（c63651a）は todo 立て → 実装 の順は守ったが、**change ログを書かずに終了した**。
本来は「todo → 実装 → change 記録」の 3 ステップが必須。
CLAUDE.md の「作業順序ルール」に明文化して再発防止。
