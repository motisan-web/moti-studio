# CODEX.md 作成

> 2026-05-07

---

## 変更内容

### CODEX.md（新規作成）

Codex エージェント向けのルールドキュメントを作成した。

**記載内容:**
- 役割定義（評価専用、実装・設計禁止）
- 厳守ルール（書き込み禁止ファイル・git禁止・カテゴリ操作禁止）
- 使用可能PHPコマンド3本のみ明記
- 評価フォーマット（`codex_evaluation` フィールド、スコアなし）
  - `context_comment`: 文脈照合コメント
  - `questions`: ソクラテス的問い返し（1〜3問）
- 評価手順（get_unevaluated → get_post → context参照 → 提示 → write_eval）
- 文脈照合コメントルール（差異は「矛盾」でなく「変化の可能性」として問い返す）
- 問い返しのルール・良い例・避ける例
- context ファイルの読み方・読んでいいファイル一覧

**決定した設計:**
- Codex評価は `codex_evaluation` フィールド（Claude評価の `evaluation` フィールドと独立）
- Claude評価済みかどうかに関係なく Codex は評価できる
- Codex は `data/evals/*.json` のみ書き込み可
