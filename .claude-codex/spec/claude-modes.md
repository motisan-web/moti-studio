# Claude操作モード仕様

> 最終更新: 2026-05-06

Claude APIは使用しない。Claude CLI（チャット）がPHPヘルパーを経由してファイル操作を行う。
直接JSONを大量に読み書きせず、PHPスクリプトを通じて必要な情報だけを取得する。

---

## PHPヘルパースクリプト

```
api/cli/
  get_uncategorized.php    ← categorized_at が null の投稿IDリストを返す
  get_unevaluated.php      ← evals/{id}.json が存在しない投稿IDリストを返す
  get_unposted_drafts.php  ← data/drafts/ の未投稿MDファイル一覧を返す
  get_post.php             ← 投稿1件のJSONを返す（--id 指定）
  set_categories.php       ← カテゴリを書き込む（--id --categories）
  write_eval.php           ← 評価データを書き込む（--id --json）
  post_draft.php           ← draftsのMDを読んで投稿APIを叩く（--file 指定）
```

出力はすべてJSON。エラーは `{"error": "..."}` 形式。

---

## モード一覧

### 1. カテゴリ付与モード

**読むべきファイル**: `.claude-codex/spec/category-logic.md`（作成予定）

**手順**:
1. `php api/cli/get_uncategorized.php` → 未カテゴリ投稿のIDリストを取得
2. 各IDについて `php api/cli/get_post.php --id=xxx` → 投稿内容を取得
3. `category-logic.md` のロジックに従いカテゴリを判定
4. `php api/cli/set_categories.php --id=xxx --categories="思想,認知・行動"` → 書き込み

---

### 2. 評価モード

**読むべきファイル**: `.claude-codex/spec/data-schema.md`（evalスキーマ確認用）

**手順**:
1. `php api/cli/get_unevaluated.php` → 未評価投稿のIDリストを取得
2. 各IDについて `php api/cli/get_post.php --id=xxx` → 投稿内容を取得
3. 17軸候補から5軸を選びスコアと評価コメントを生成
4. `php api/cli/write_eval.php --id=xxx --json='{"evaluation":{...},"replies":[]}'` → 書き込み

---

### 3. 投稿モード（draftsから投稿）

**読むべきファイル**: なし（スキーマはPHPが処理）

**手順**:
1. `php api/cli/get_unposted_drafts.php` → 未投稿MDファイル一覧を取得
2. `php api/cli/post_draft.php --file=xxx.md` → MDを読んでAPIに投稿

MDフォーマット:
```markdown
---
title: タイトル（任意）
account: moti
---
本文をここに書く
```

区切り文字 `---` でフロントマターと本文を分離。

---

### 4. リプライモード

**読むべきファイル**: なし

**手順**:
1. もちさんから投稿IDと指示を受け取る（例: `20260504_abc123 3つの視点で語って`）
2. `php api/cli/get_post.php --id=20260504_abc123` → 投稿内容を取得
3. 指示に従ってコメントを生成
4. `php api/cli/write_eval.php` でrepliesに追記

---

## 処理フラグの管理

### 投稿JSON内のフラグ

```json
{
  "categorized_at": null
}
```

- `null` = 未カテゴリ付与
- timestamp = 付与済み（例: `"2026-05-04T15:00:00"`）

### 評価済みかどうか

`data/evals/{id}.json` が存在し `evaluation` キーがあれば評価済み。
ファイルの存在をフラグとして扱うためJSONへの追記は不要。
