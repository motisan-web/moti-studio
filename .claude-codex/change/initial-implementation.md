# 初期実装

> 2026-05-06

## 変更内容

### UIライトテーマ化
- `index.html` の CSS変数をダーク→ライトに変換
- `--bg: #f2f2f7` / `--surface: #ffffff` / `--text: #1c1c2e` など
- パレットのシャドウも `rgba(0,0,0,.12)` に調整

### data/ ディレクトリ構成作成
- `data/accounts/moti.json` 初期アカウント定義
- `data/reactions.json` 空の絵文字リスト
- `data/categories.json` 27カテゴリ定義
- `data/posts/` `data/evals/` `data/avatars/` `data/drafts/` `data/auth/` 空ディレクトリ作成

### config.php
- `DATA_DIR` `DATA_POSTS` `DATA_EVALS` `DATA_AUTH` `DATA_ACCOUNTS` `DATA_DRAFTS` `DATA_AVATARS` の定数定義

### api/lib/ ユーティリティ実装
- `json.php`: `json_read` / `json_write` / `json_list` / `post_new_id` / `now_iso`
- `response.php`: `res_ok` / `res_err` / `req_json`
- `auth.php`: `auth_check` / `auth_login` / `auth_account_id`、ロックアウトロジック込み

### API実装
- `api/index.php`: フロントコントローラー、URIパースして handlers/ に振り分け
- `api/.htaccess`: 全リクエストを index.php へ
- `api/handlers/auth.php`: POST login / logout
- `api/handlers/posts.php`: GET/POST/PUT/DELETE + react/comment/label サブリソース
- `api/handlers/accounts.php`: GET一覧 / GET単体 / PUT更新
- `api/handlers/evals.php`: GET / POST request
- `api/handlers/search.php`: GET ?q= 全文検索

### tools/ セットアップスクリプト
- `tools/setup-auth.php`: bcryptハッシュ生成・`data/auth/{id}.json` 作成、確認入力付き
- `tools/seed-posts.php`: デモ投稿14件 + 評価データを data/posts/ data/evals/ に流し込み

### index.php 本実装
- PHP認証チェック → 未認証時はログイン画面のみ表示
- PHP が `INIT.categories` / `INIT.accounts` をJSに注入
- JS全面書き換え: 全操作を `fetch()` 経由のAPIコールに
- 投稿一覧・詳細・リアクション・ラベル・自己コメント・評価表示を実装

## 動作確認
- http://git15.local/ でログイン・投稿表示・詳細パネル・リアクション・ラベル・コメント動作確認済み
