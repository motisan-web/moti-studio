# 現在の作業状態

> 最終更新: 2026-05-07

---

## 直近で完了したこと

### Phase 1 バグ修正（change/phase1-bug-fixes.md）
- **`&#039;` HTMLエンティティ表示バグ修正** — `decodeEntities()` を `mdToHtml` に追加。`data/posts/20260506_a215f8.json` のデータも修正
- **UIカテゴリ作成で `categories.json` が更新されない** — `api/handlers/posts.php` に `categories_merge()` を追加。POST/PUT 時に自動マージ。JS 側も `INIT.categories` を即時更新
- **ヘッダー「投稿する」ボタンでフォームリセット** — `openCreate()` が新規投稿パネル表示中は再初期化しないよう修正

### Phase 2 基盤整備（途中）
- **CLIスクリプト洗い出し完了** — `get_unevaluated.php`（no_eval未考慮・エージェント指定なし）と `post_draft.php`（no_eval/strength未設定）が未対応と判明。修正は次フェーズへ
- **CODEX.md 作成**（change/codex-md-created.md） — Codex評価エージェント向けルールドキュメント。禁止事項・使用可能PHPコマンド・評価フォーマット・手順を定義

### その他
- **投稿ID表示・コピー機能** — 詳細パネルにID表示、クリックでコピー（非HTTPS対応フォールバック付き）
- **「評価不要」フラグ** — `no_eval` フィールド追加。詳細パネルのトグル・作成/編集フォームのチェックボックスから操作可能
- **コピートースト** — コピー時に「コピーしました」トーストを表示
- **eval axes 旧フォーマット修正** — `normalizeAxes()` で object/array 両フォーマットを吸収。`20260506_aa002c` のデータも修正
- **Wikipedia URL付与ロジック** — `spec/url-wiki-logic.md` 新規作成。`20260507_81c896`（レトリック）にURL追加・`20260502_4b6561`（フィトケミカル）に「言葉」カテゴリ追加
- **sync Push 設計** — `spec/sync.md` に双方向同期（`--push` / `--both`）の設計を追記
- **作業順序ルール明文化** — `CLAUDE.md` に「todo追加 → 実装 → change記録」の順序ルールを追記

---

## Codex評価の設計決定事項（重要）

次チャットで Codex 評価関連の実装に入る場合は必読。

| 項目 | 決定内容 |
|---|---|
| 評価フィールド | `evals/{id}.json` に `codex_evaluation` を追加（`evaluation` とは独立） |
| フォーマット | `context_comment`（文脈照合）+ `questions`（ソクラテス問い返し1〜3問）。スコアなし |
| Codexの書き込み範囲 | `data/evals/*.json` のみ。`data/posts/*.json` は読み取り専用 |
| カテゴリ | Codexは関与しない。Claudeが担当 |
| 矛盾の扱い | 「矛盾」と断定せず「変化の可能性」として問い返す。context の記録日を考慮する |
| 運用方式 | ChatGPT（Codex）を手動で立ち上げて CODEX.md を読ませて運用（API連携なし） |
| Codex向けファイル | `CODEX.md`（プロジェクトルート）|

---

## 次にやること（推薦順）

### Phase 2 残り
1. **チップUI共通ドキュメント設計**（対話で決める）— 削除ボタン以外でも使うので設計先行
2. **未対応CLIスクリプト修正** — `get_unevaluated.php`（`--agent` / `no_eval` 対応）・`post_draft.php`（`no_eval` フィールド追加）

### Phase 3（UI改善）
3. 「補足・意図」→「もちさんによる補足・意図」表記変更
4. 削除ボタンにチップUI（アーカイブか完全削除か明示）
5. 「もっと見る→」表記修正
6. カテゴリタグクリックでフィルタリング
7. 表示名ニックネーム/ID選択

### Phase 4（評価システム拡張）
8. `get_unevaluated.php` パラメーター対応
9. 評価表示のエージェント切り替え（Claude評価 / Codex評価）

### Phase 5（メディア・アップロード）
10. カテゴリアイコン（絵文字 or 画像・SVG）
11. アカウントアイコンアップロード
12. 投稿への複数画像添付＋ギャラリー表示

---

## 確定済みの評価ルール（Claude評価）

- **1件ずつ確認** → OKが出てから書き込む
- **カテゴリは必ず付与** — 評価案と同時に提示し、OKが出たら `set_categories.php` で書き込む
- **確認をまとめる** — 評価・カテゴリ・context更新案を一括提示してOKを取る
- **コメント文数は柔軟** — 短い/ユーモア系は1〜2文、思想系は必要なだけ
- **humor投稿** → replies[] に `instruction:"humor-reaction"` のひとことリプライを自動追加
- **カテゴリは comma-separated で渡す** — `set_categories.php --categories='A,B'`
- 詳細: `.claude-codex/spec/eval-logic.md`

---

## 注意事項・既知の仕様

- **Claude API は使わない**。Claudeがチャットでコマンドを実行しPHPヘルパー経由で処理する方式
- **Codex（ChatGPT）はAPIなしの手動運用**。CODEX.md を ChatGPT に読ませて評価させる
- **`data/`** は `.gitignore` 対象（ローカルのみ存在）
- **worktreeは使わない**。常に `main` ブランチに直接コミット
- **作業順序厳守**: todo追加 → 実装 → change記録 の3ステップ。スキップ禁止
- **Wikipedia URLロジック**: 語の定義説明投稿 + Wikipedia記事あり + url=null の場合に付与。詳細は `spec/url-wiki-logic.md`
- **`evals/{id}.json` の旧フォーマット**: axes がオブジェクト形式のデータが一部存在。`normalizeAxes()` で吸収済み
- **テストデータの日付問題**: 既存投稿が直近1週間に集中している。もちさんが手動で `created_at` を修正予定
- ローカル: http://git15.local / 本番: https://d00e.motisan.info

---

## 現在のファイル構成

```
CLAUDE.md                    ← プロジェクトルール・todo・issue（毎チャット必読）
CODEX.md                     ← Codex評価エージェント向けルール（新規追加）
config.php
index.php                    ← エントリポイント（views/pc.php or sp.php に振り分け）
views/
  pc.php                     ← メインUI（認証・3ペイン・全機能JS/CSS インライン）
  sp.php                     ← スマホ用（未実装）

api/
  index.php                  ← ルーター
  lib/  handlers/  cli/

.claude/
  settings.json              ← Bash全許可

.claude-codex/
  CURRENT.md                 ← このファイル
  spec/
    eval-logic.md / category-logic.md / claude-modes.md
    data-schema.md / sync.md / url-wiki-logic.md
  context/
    INDEX.md + 各mdファイル
  change/
    codex-md-created.md      ← 最新
    phase1-bug-fixes.md
    fix-eval-axes-format.md
    ...

data/                        ← gitignore対象（ローカルのみ）
tools/
  sync.php                   ← Pull実装済み。Push（--push/--both）は未実装
```
