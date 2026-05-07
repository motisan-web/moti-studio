# 同期仕様

> 最終更新: 2026-05-07

---

## 概要

本番（Xserver）とローカル（XAMPP）の `data/` を HTTP API 経由で同期する。

- 方向: 本番 → ローカル（本番で増えたデータをローカルに取り込む）
- トリガー: `php tools/sync.php` コマンドを手動実行（評価モード前後にも実行）
- 認証: APIキー方式（`X-Sync-Key` ヘッダー）

---

## 本番側エンドポイント

### `GET /api/sync`

**リクエストヘッダー**

```
X-Sync-Key: {SYNC_KEY の値}
```

**レスポンス（200）**

```json
{
  "posts": {
    "20260504_abc123": { ...投稿JSON全フィールド... },
    "20260505_def456": { ... }
  },
  "evals": {
    "20260504_abc123": { ...evalJSON全フィールド... }
  },
  "categories": ["思想", "AI", ...],
  "reactions": {
    "emojis": [{ "slug": "moti_power", "image": "...", "label": "..." }]
  }
}
```

**エラーレスポンス**

```json
{ "error": "Unauthorized" }   // 401: キー不一致
{ "error": "Not found" }      // 404: ルート未定義
```

---

## ローカル側スクリプト

### `php tools/sync.php [--dry-run]`

**処理フロー**

1. `SYNC_ENDPOINT`（config.php）に `GET` リクエスト、`X-Sync-Key` ヘッダー付与
2. レスポンスをパース
3. 各データを以下のマージルールでローカルに書き込む
4. `data/.sync_state.json` に最終同期時刻を記録

**`--dry-run`**

書き込みを行わず、適用予定の変更を標準出力に表示して終了する。

出力例：
```
[DRY RUN] 変更プレビュー

NEW    posts/20260506_xyz789.json
MERGE  posts/20260504_abc123.json
  - body: 本番の方が新しい（updated_at 2026-05-06 > 2026-05-04）
  - comments: 本番に2件追加
  - reactions: 💡 3→5（本番の数を採用）
SKIP   posts/20260501_def456.json  (差分なし)

3件中 2件に変更あり。--dry-run なしで適用されます。
```

---

## マージルール

### posts/{id}.json

| フィールド | ルール |
|---|---|
| `body` / `title` / `intent` / `url` / `archive_at` | `updated_at` が新しい方を採用 |
| `account_id` / `created_at` | `updated_at` が新しい方を採用 |
| `comments` | 両方マージ → `created_at`＋本文先頭32文字のハッシュで重複除去 → `created_at` 昇順ソート |
| `reactions` | 絵文字キーごとに大きい数を採用 |
| `categories` | 和集合（重複除去） |
| `labels` | 両方マージ → `type`＋`added_at` で重複除去 |
| `strength` | null でない方を優先。両方 null でない場合は `updated_at` 新しい方 |
| `categorized_at` | null でない方を優先 |
| `repost` / `repost_from` | `updated_at` が新しい方を採用 |

### evals/{id}.json

| フィールド | ルール |
|---|---|
| `evaluation` | null でない方を優先。両方 null でない場合は `generated_at` 新しい方 |
| `replies` | `id` で重複除去してマージ → `requested_at` 昇順ソート |

### categories.json

和集合（ローカルと本番の両方のカテゴリを保持）

### reactions.json（emojis配列）

`slug` で重複除去してマージ。コンフリクトは本番の値を優先。

---

## 設定値（config.php）

```php
define('SYNC_KEY',      'xxxxxx');                          // 秘密キー（値はconfig.phpのみ）
define('SYNC_ENDPOINT', 'https://d00e.motisan.info/api/sync'); // 本番エンドポイント
```

---

## 同期状態ファイル

`data/.sync_state.json`（gitignore対象）

```json
{
  "last_synced_at": "2026-05-07T12:00:00"
}
```

---

## 認証フロー

1. ローカルの `tools/sync.php` が `SYNC_KEY` を `X-Sync-Key` ヘッダーにセットして本番へ GET
2. 本番の `api/handlers/sync.php` がヘッダーと `SYNC_KEY` 定数を比較
3. 一致しない場合 401 を返して終了
4. セッション認証は不要（APIキーのみ）

---

## ファイル構成

```
config.php                    ← SYNC_KEY / SYNC_ENDPOINT 追加
api/
  handlers/
    sync.php                  ← 本番側エンドポイント（新規）
  index.php                   ← 'sync' ルートを追加
tools/
  sync.php                    ← ローカル側同期スクリプト（新規）
data/
  .sync_state.json            ← 最終同期時刻（gitignore対象）
```
