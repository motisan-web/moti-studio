# データスキーマ仕様

> 最終更新: 2026-05-07
> 役割: 技術リファレンス（データ構造を確認・変更するときに読む）

---

## ディレクトリ構成

```
data/
  accounts/
    moti.json
    moti2.json
  posts/
    20260504_abc123.json
    ...
  evals/
    20260504_abc123.json   ← 投稿と同名で対応
    ...
  avatars/
    moti.png
    ...
  drafts/
    *.md                   ← Claude投稿モード用MDファイル
  reactions.json           ← カスタム絵文字の登録リスト
  categories.json          ← カテゴリ一覧
```

---

## accounts/{id}.json

```json
{
  "id": "moti",
  "display_name": "もちさん",
  "icon": "/img/avatars/moti.png",
  "icon_shape": "circle",
  "color": "#7c6aff",
  "created_at": "2026-01-01"
}
```

| フィールド | 型 | 説明 |
|---|---|---|
| `id` | string | システム識別子・ログイン名 |
| `display_name` | string | UI表示名 |
| `icon` | string \| null | ルートからのパス or 外部URL。null でデフォルトアバター |
| `icon_shape` | `"circle"` \| `"square"` \| `"none"` | アバターの表示形状 |
| `color` | string | アカウントのアクセントカラー（HEX） |
| `created_at` | string | ISO 8601 日付 |

---

## posts/{id}.json

```json
{
  "id": "20260504_abc123",
  "account_id": "moti",
  "title": "",
  "body": "...",
  "intent": "...",
  "url": "https://...",
  "categories": ["認知・行動"],
  "labels": [
    {
      "type": "misskey",
      "url": "https://misskey.io/notes/xxx",
      "memo": "2026-05-04 motiアカウントで投稿",
      "added_at": "2026-05-04T12:00:00"
    }
  ],
  "reactions": {
    "💡": 3,
    ":moti_power:": 1
  },
  "comments": [
    { "body": "やっぱり面白い", "created_at": "2026-05-05T10:00:00" }
  ],
  "archive_at": null,
  "strength": null,
  "created_at": "2026-05-04T12:00:00",
  "updated_at": "2026-05-04T12:00:00"
}
```

| フィールド | 型 | 説明 |
|---|---|---|
| `id` | string | `YYYYMMDD_ランダム6文字` |
| `account_id` | string | accounts/ のID |
| `title` | string | 任意。空文字でタイトルなし |
| `body` | string | 本文。Markdown（blockquote / list対応） |
| `intent` | string \| null | 補足・意図・背景メモ |
| `url` | string \| null | 参考URL |
| `categories` | string[] | Claudeが自動付与。複数可 |
| `labels` | Label[] | アクション記録フラグ（後述） |
| `reactions` | object | キー: 絵文字 or `:slug:`、値: カウント数。減算不可 |
| `comments` | Comment[] | もちさんが付ける自己コメント |
| `repost` | bool | 転載投稿かどうか。面白投稿カテゴリに必要 |
| `repost_from` | string \| null | 転載元の説明（任意） |
| `archive_at` | string \| null | アーカイブ期限（ISO 8601）。null で無期限 |
| `strength` | int \| null | 投稿強度 1〜10。Claudeが評価モードで付与。null = 未付与 |
| `categorized_at` | string \| null | カテゴリ付与済み日時。null = 未処理 |
| `created_at` | string | 作成日時 |
| `updated_at` | string | 最終更新日時 |

### Label オブジェクト

| フィールド | 型 | 説明 |
|---|---|---|
| `type` | string | `misskey` / `twitter` / `resolved` / `implemented` / `cancelled` / `verified` |
| `url` | string | 任意 |
| `memo` | string | 任意 |
| `added_at` | string | 追加日時 |

### Comment オブジェクト（もちさん自己コメント）

| フィールド | 型 | 説明 |
|---|---|---|
| `body` | string | コメント本文 |
| `created_at` | string | 作成日時 |

---

## evals/{id}.json

投稿と同名ファイル。Claude生成データをすべてここに格納。

```json
{
  "post_id": "20260504_abc123",
  "evaluation": {
    "comment": "環境設計で行動を誘導するという視点が鮮明です。",
    "axes": [
      { "key": "actionability", "label": "行動性", "score": 88 },
      { "key": "creativity",    "label": "創造性", "score": 76 },
      { "key": "specificity",   "label": "具体性", "score": 72 },
      { "key": "introspection", "label": "内省度", "score": 65 },
      { "key": "humor",         "label": "ユーモア", "score": 58 }
    ],
    "generated_at": "2026-05-04T15:00:00"
  },
  "replies": [
    {
      "id": "reply_001",
      "instruction": "ファクトチェックをして",
      "comments": [
        {
          "label": "ファクトチェック",
          "body": "...",
          "moti_reply": null
        }
      ],
      "requested_at": "2026-05-04T16:00:00",
      "generated_at": "2026-05-04T16:01:00"
    },
    {
      "id": "reply_002",
      "instruction": "3つの異なる視点で語って",
      "comments": [
        { "label": "批判的視点", "body": "...", "moti_reply": null },
        { "label": "共感的視点", "body": "...", "moti_reply": null },
        { "label": "実用的視点", "body": "...", "moti_reply": null }
      ],
      "requested_at": "2026-05-04T17:00:00",
      "generated_at": "2026-05-04T17:01:00"
    }
  ]
}
```

| フィールド | 型 | 説明 |
|---|---|---|
| `evaluation` | object \| null | 5軸評価。未評価時は null |
| `evaluation.comment` | string | Claude評価コメント（文章） |
| `evaluation.axes` | Axis[] | 5軸スコア（17軸候補からClaudeが選択） |
| `replies` | Reply[] | Claudeリプライの履歴 |
| `replies[].instruction` | string | もちさんがClaudeに送った指示内容 |
| `replies[].comments` | ReplyComment[] | Claude生成コメント（複数視点可） |
| `replies[].comments[].moti_reply` | string \| null | もちさんの返信（現在は常にnull、将来用に予約） |

---

## reactions.json

```json
{
  "emojis": [
    { "slug": "moti_power", "image": "/img/reactions/moti_power.gif", "label": "もちパワー" }
  ]
}
```

---

## categories.json

```json
{
  "categories": [
    "思想", "メンタリティー", "ストラテジー", "認知・行動",
    "仕事", "プロジェクト", "やりたい事",
    "AI", "技術", "言語",
    "引用", "ブックマーク", "メモ",
    "健康", "食べ物", "料理", "体験",
    "ゲーム", "お絵描き",
    "人間関係", "お役立ち", "お金・投資",
    "書籍", "マンガ・アニメ", "面白投稿",
    "もちさん設定", "もち関連"
  ]
}
```

- 投稿ごとに複数付与可
- 新規カテゴリはこのファイルに追記してから使用する
- カテゴリ判定ロジックは `spec/category-logic.md` に定義
