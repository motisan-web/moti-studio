# Phase 1: 評価設計ドキュメント + CLIヘルパー実装

> 2026-05-06

## 変更内容

### 追加: `.claude-codex/spec/eval-logic.md`
- 17軸候補の説明・選定ルール・スコアリング基準・コメントルールを定義
- 評価済み判定基準（evalファイル存在 + evaluation非null）を明記
- write_eval.php に渡すJSONフォーマットを例示

### 追加: `api/cli/get_uncategorized.php`
- `categorized_at` が null の投稿IDを配列で返す

### 追加: `api/cli/get_unevaluated.php`
- `data/evals/{id}.json` が存在しないか `evaluation` が null の投稿IDを配列で返す

### 追加: `api/cli/get_unposted_drafts.php`
- `data/drafts/*.md` のファイル名一覧を返す

### 追加: `api/cli/get_post.php`
- `--id` で指定した投稿JSONを整形出力

### 追加: `api/cli/set_categories.php`
- `--id` と `--categories`（カンマ区切り）でカテゴリを上書き
- `categorized_at` と `updated_at` を自動更新

### 追加: `api/cli/write_eval.php`
- `--id` と `--json` で評価データを `data/evals/{id}.json` に書き込み
- ファイルが存在しない場合は新規作成、あれば evaluation を上書き

### 追加: `api/cli/post_draft.php`
- `--file` で指定したMDを読んでフロントマター（title/account/repost/repost_from）を解析
- `data/posts/` に投稿JSONを直接作成
- 投稿済みdraftを `data/drafts/posted/` に退避

## 動作確認
- get_post / set_categories / write_eval / post_draft を実際に実行して確認済み
