# Wikipedia URL付与・カテゴリ追加・ロジック明文化

> 2026-05-07

---

## 変更内容

### data/posts/20260507_81c896.json

- `url` に Wikipedia URL を追加
  - `https://ja.wikipedia.org/wiki/%E3%83%AC%E3%83%88%E3%83%AA%E3%83%83%E3%82%AF`（レトリック）
- 投稿タイトルが語の定義説明であり、Wikipedia 記事が存在するため付与

### data/posts/20260502_4b6561.json

- `categories` に `"言葉"` を追加（既存: `["健康"]` → `["健康", "言葉"]`）
- フィトケミカルは化学用語の説明投稿であり「言葉」カテゴリに該当

### .claude-codex/spec/url-wiki-logic.md（新規）

- Wikipedia URL を付与するケース・しないケースを定義
- URL フォーマット（パーセントエンコード）を明示
- 「言葉」カテゴリとの関係・運用タイミングを記載
