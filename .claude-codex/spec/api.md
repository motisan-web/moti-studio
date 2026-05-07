# APIエンドポイント仕様

> 最終更新: 2026-05-07
> 役割: 技術リファレンス（APIを追加・修正するときに読む）

---

## ルーティング方式

フロントコントローラー方式。全リクエストを `api/index.php` で受け、内部でハンドラーに振り分ける。

```
api/
  index.php              ← エントリーポイント（ルーター）
  handlers/
    auth.php
    posts.php
    reactions.php
    comments.php
    labels.php
    evals.php
    search.php
    accounts.php
  lib/
    auth.php             ← セッション検証・ログイン処理
    json.php             ← JSON読み書きユーティリティ
    response.php         ← JSONレスポンス整形
```

### URLルール

```
POST   api/auth/login
POST   api/auth/logout

GET    api/posts
POST   api/posts
GET    api/posts/{id}
PUT    api/posts/{id}
DELETE api/posts/{id}

POST   api/posts/{id}/react
POST   api/posts/{id}/comment
DELETE api/posts/{id}/comment/{index}
POST   api/posts/{id}/label
DELETE api/posts/{id}/label/{index}

GET    api/posts/random
GET    api/search?q={query}

GET    api/evals/{id}
POST   api/evals/{id}/request

GET    api/accounts
GET    api/accounts/{id}
PUT    api/accounts/{id}
```

`.htaccess` ですべて `api/index.php` に向ける。

---

## 共通仕様

- リクエスト/レスポンス: JSON
- 認証が必要なエンドポイントはセッションチェック（未認証は `401`）
- エラーレスポンス形式:

```json
{ "error": "エラーメッセージ" }
```

- 成功レスポンス形式:

```json
{ "data": { ... } }
{ "data": [ ... ] }
```

---

## 認証

### POST api/auth/login
認証不要。

**リクエスト**
```json
{ "account_id": "moti", "password": "xxx" }
```

**レスポンス**
```json
{ "data": { "account_id": "moti", "display_name": "もちさん" } }
```

**エラー**
- `401` パスワード不一致
- `423` ロックアウト中（`locked_until` を含む）

### POST api/auth/logout
**レスポンス**
```json
{ "data": { "ok": true } }
```

---

## 投稿

### GET api/posts
**クエリパラメータ**

| パラメータ | 型 | 説明 |
|---|---|---|
| `before` | string | このcreated_atより古い投稿を取得（カーソル） |
| `limit` | int | 取得件数（デフォルト20、最大50） |
| `category` | string | カテゴリフィルター |
| `account_id` | string | アカウントフィルター |
| `archive` | bool | `true` でアーカイブ済みのみ |

**レスポンス**
```json
{
  "data": {
    "posts": [ { ...post }, ... ],
    "next_cursor": "2026-05-01T10:00:00",
    "has_more": true
  }
}
```

アーカイブ期限を過ぎた投稿はこのAPIがアクセス時に自動でアーカイブ移動処理を行う。

### POST api/posts
**リクエスト**
```json
{
  "account_id": "moti",
  "title": "",
  "body": "...",
  "intent": "...",
  "url": "https://...",
  "categories": ["認知・行動"],
  "archive_at": null
}
```

カテゴリはClaudeによる自動付与のため、空配列で送っても後から付与される。

### GET api/posts/{id}
投稿1件を返す。

### PUT api/posts/{id}
本文・タイトル・intent・url・archive_at を更新可。categories / labels / reactions はそれぞれ専用エンドポイントで操作。

### DELETE api/posts/{id}
物理削除。通常はアーカイブ（`archive_at` 設定）を使い、削除は補助的に用意する。

---

## リアクション

### POST api/posts/{id}/react
カウントのみ加算。減算不可。

**リクエスト**
```json
{ "emoji": "💡" }
{ "emoji": ":moti_power:" }
```

---

## コメント（もちさん自己コメント）

### POST api/posts/{id}/comment
**リクエスト**
```json
{ "body": "やっぱり面白い" }
```

### DELETE api/posts/{id}/comment/{index}
配列インデックス指定で削除。

---

## ラベル

### POST api/posts/{id}/label
**リクエスト**
```json
{ "type": "misskey", "url": "https://...", "memo": "..." }
```

### DELETE api/posts/{id}/label/{index}
配列インデックス指定で削除。

---

## 評価・リプライ（Claude連携）

### GET api/evals/{id}
evals/{id}.json をそのまま返す。ファイルがなければ `{ "data": null }`。

### POST api/evals/{id}/request
Claudeへのリプライ依頼を登録し、非同期でClaude APIを叩く（またはdrafts/に命令ファイルを置く）。

**リクエスト**
```json
{ "instruction": "3つの異なる視点で語って" }
```

**レスポンス**
```json
{ "data": { "ok": true, "reply_id": "reply_003" } }
```

Claude APIとの連携詳細は `spec/claude-integration.md` に定義予定。

---

## 検索

### GET api/search?q={query}
全文検索。`body` と `title` と `intent` を対象にする。カーソルページング対応。

### GET api/posts/random
ランダム1件を返す。アーカイブ済みは除外。

---

## アカウント

### GET api/accounts
全アカウント一覧（パスワード情報は含まない）。

### GET api/accounts/{id}
アカウント情報1件。

### PUT api/accounts/{id}
`display_name` / `icon` / `icon_shape` / `color` を更新可。パスワード変更は別エンドポイントを設ける予定。
