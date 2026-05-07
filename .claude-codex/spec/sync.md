# 同期仕様

> 最終更新: 2026-05-07

---

## 概要

本番（Xserver）とローカル（XAMPP）の `data/` を HTTP API 経由で双方向同期する。

- **Pull（本番 → ローカル）**: `php tools/sync.php`
- **Push（ローカル → 本番）**: `php tools/sync.php --push`
- **双方向（Pull → Push）**: `php tools/sync.php --both`
- 認証: APIキー方式（`X-Sync-Key` ヘッダー）
- コンフリクト解決: マージルールで自動解決。ユーザー承認は不要

---

## コンフリクト解決方針

Pull・Push ともに同一のマージルール（後述）を適用する。
ユーザーへの確認は一切行わず、ロジックで完結させる。
これにより `--both` 実行時も完全自動で双方向が揃う。

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

### `POST /api/sync`

ローカルのデータを本番に送り、本番側でマージして保存する。

**リクエストヘッダー**

```
X-Sync-Key: {SYNC_KEY の値}
Content-Type: application/json
```

**リクエストボディ**（GET レスポンスと同じ構造）

```json
{
  "posts": {
    "20260504_abc123": { ...投稿JSON全フィールド... }
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

**処理フロー（本番側）**

1. `X-Sync-Key` を検証（不一致なら 401）
2. リクエストボディをパース
3. 各データをマージルールに従って本番の `data/` に書き込む
4. `data/.sync_state.json` に最終同期時刻を記録

**レスポンス（200）**

```json
{ "status": "ok", "changes": 3 }
```

**エラーレスポンス**

```json
{ "error": "Unauthorized" }     // 401: キー不一致
{ "error": "Invalid JSON" }     // 400: ボディのパース失敗
{ "error": "Not found" }        // 404: ルート未定義
```

---

## ローカル側スクリプト

### `php tools/sync.php [--push] [--both] [--dry-run]`

| フラグ | 動作 |
|---|---|
| （なし） | Pull のみ（本番 → ローカル） |
| `--push` | Push のみ（ローカル → 本番） |
| `--both` | Pull → Push の順に実行（双方向） |
| `--dry-run` | どのモードでも書き込みを行わずプレビューのみ表示 |

**Pull 処理フロー**

1. `SYNC_ENDPOINT` に `GET` リクエスト、`X-Sync-Key` ヘッダー付与
2. レスポンスをパース
3. 各データをマージルールでローカルに書き込む
4. `data/.sync_state.json` に最終同期時刻を記録

**Push 処理フロー**

1. ローカルの全データ（posts / evals / categories / reactions）を収集
2. `SYNC_ENDPOINT` に `POST` リクエスト、`X-Sync-Key` + `Content-Type: application/json`
3. 本番側がマージして書き込む（レスポンスで変更件数を確認）
4. `data/.sync_state.json` に最終同期時刻を記録

**`--both` 処理フロー**

1. Pull 実行（本番 → ローカルにマージ）
2. Push 実行（マージ済みのローカル → 本番に送信）

これにより両端が同じ状態に収束する。

**`--dry-run`**

書き込みを行わず、適用予定の変更を標準出力に表示して終了する。

出力例（Pull）：
```
[DRY RUN] Pull プレビュー

NEW    posts/20260506_xyz789.json
MERGE  posts/20260504_abc123.json
SKIP   posts/20260501_def456.json  (差分なし)

3件中 2件に変更あり。--dry-run なしで適用されます。
```

出力例（Push）：
```
[DRY RUN] Push プレビュー（ローカル → 本番）

SEND   posts/20260506_xyz789.json
SEND   posts/20260504_abc123.json
SKIP   posts/20260501_def456.json  (差分なし)

3件中 2件を送信予定。--dry-run なしで適用されます。
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
    sync.php                  ← 本番側エンドポイント（GET + POST 両対応）
  index.php                   ← 'sync' ルートを追加
tools/
  sync.php                    ← ローカル側同期スクリプト（--push / --both / --dry-run 対応）
data/
  .sync_state.json            ← 最終同期時刻（gitignore対象）
```
