# Claude操作モード仕様（カテゴリ付与・投稿・リプライ）

> 最終更新: 2026-05-07
> 役割: 操作ガイド（カテゴリ付与・投稿・リプライ モード時に読む）
> 評価モードは spec/eval-mode.md を参照

---

## PHPヘルパースクリプト一覧

```
api/cli/
  get_uncategorized.php    ← categorized_at が null の投稿IDリストを返す
  get_unevaluated.php      ← evals/{id}.json が存在しない投稿IDリストを返す
  get_unposted_drafts.php  ← data/drafts/ の未投稿MDファイル一覧を返す
  get_post.php             ← 投稿1件のJSONを返す（--id 指定）
  set_categories.php       ← カテゴリを書き込む（--id --categories）
  write_eval.php           ← 評価+strength を書き込む（--id --strength --json）
  post_draft.php           ← draftsのMDを読んで投稿APIを叩く（--file 指定）
tools/
  sync.php                 ← 本番→ローカル同期（php tools/sync.php [--dry-run]）
```

出力はすべてJSON。エラーは `{"error": "..."}` 形式。

---

## 1. カテゴリ付与モード

**読むべきファイル**: このファイルとカテゴリ判定ロジックは `spec/category-logic.md` を参照

**手順**:
1. `php api/cli/get_uncategorized.php` → 未カテゴリ投稿のIDリストを取得
2. 各IDについて `php api/cli/get_post.php --id=xxx` → 投稿内容を取得
3. `category-logic.md` のロジックに従いカテゴリを判定
4. `php api/cli/set_categories.php --id=xxx --categories="思想,認知・行動"` → 書き込み

---

## 2. 投稿モード（draftsから投稿）

**手順**:
1. `php api/cli/get_unposted_drafts.php` → 未投稿MDファイル一覧を取得
2. `php api/cli/post_draft.php --file=xxx.md` → MDを読んでAPIに投稿

**MDフォーマット**:
```markdown
---
title: タイトル（任意）
account: moti
---
本文をここに書く
```

区切り文字 `---` でフロントマターと本文を分離。投稿済みdraftは `data/drafts/posted/` に自動退避される。

---

## 3. リプライモード

**手順**:
1. もちさんから投稿IDと指示を受け取る（例: `20260504_abc123 3つの視点で語って`）
2. `php api/cli/get_post.php --id=20260504_abc123` → 投稿内容を取得
3. 指示に従ってコメントを生成
4. `php api/cli/write_eval.php --id=xxx --json='{"evaluation":null,"replies":[...]}'` でrepliesに追記

---

## 処理フラグの管理

### 投稿JSON内のフラグ

| フィールド | null の意味 | 値がある場合 |
|---|---|---|
| `categorized_at` | 未カテゴリ付与 | 付与済みISO 8601タイムスタンプ |
| `strength` | 未評価 | 1〜10の整数（Claude付与） |

### 評価済みかどうか

`data/evals/{id}.json` が存在し `evaluation` キーが null でなければ評価済み。
