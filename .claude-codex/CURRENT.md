# 現在の作業状態

> 最終更新: 2026-05-07

---

## 直近で完了したこと

（`change/phase3-ui.md`）

- **投稿編集UI** — 詳細パネルに ✏️/🗑 ボタン、editフォーム、submitEdit
- **投稿削除UI** — deletePost（確認後 DELETE、グリッドから除去）
- **アーカイブ期限設定** — 作成・編集フォームに datetime-local + 1週間後ボタン
- **アカウント切替UI** — サイドバークリックでドロップダウン、タイムラインフィルター
- **アカウント情報編集** — display_name / color / icon_shape を編集、サイドバー即時更新
- **レーダーチャート** — SVGで5軸グラフ、evalパネルに表示
- **リアクション絵文字登録UI** — 画像アップロード（mime検証）+ slug登録、削除も可
- **api/handlers/reactions.php** 新規作成（GET/POST/DELETE）
- **PUT posts/{id}** に `categories` を追加
- **img/reactions/** ディレクトリ作成

（`change/sync-and-strength.md` ← 本セッション）

- **同期機能（API方式）** — 本番→ローカルを HTTP API 経由で同期
  - `api/handlers/sync.php` — `GET /api/sync` エンドポイント（APIキー認証）
  - `api/index.php` — `sync` ルート追加
  - `tools/sync.php` — ローカル側CLIスクリプト（旧Web UI版を置き換え）
  - `config.php` — `SYNC_KEY` / `SYNC_ENDPOINT` 追加
- **strength（投稿強度）フィールド** — `posts/{id}.json` に `strength: null | 1-10` 追加
  - `api/cli/write_eval.php` — `--strength` オプション追加（evalと同時にpost.jsonへ書き込み）
- **spec ドキュメント整備**
  - `spec/sync.md` — 同期仕様（エンドポイント・マージルール・設定値）
  - `spec/eval-mode.md` — 評価モード専用ガイド（これ1枚で完結）
  - `spec/data-schema.md` — `strength` フィールド追加

---

## 次にやること

- **SYNC_KEY を設定してデプロイ**（本番の config.php に `SYNC_KEY` 実値を設定 → 動作確認）
- Xserver 初回デプロイ実施（FTPでアップロード → パーミッション設定 → 動作確認）
- data/ サブモジュール化（プライベートリポジトリ作成後に `tools/submodule-setup.sh` 実行）
- スマホ対応（後回し・最終的には作る）

---

## 注意事項・既知の仕様

- **Claude API は使わない**。Claudeがチャットでコマンドを打ちPHPヘルパーを実行する方式
- **評価モード時は `spec/eval-mode.md` だけ読めば動く**（他のspecを読む必要なし）
- **同期コマンド**: `php tools/sync.php`（`--dry-run` で変更プレビューのみ）
- **SYNC_KEY は環境変数 or config.php で設定**（値はファイルに書かず環境変数推奨）
- **リアクションは加算のみ**（誰が押したか管理しない。デモのような active 状態なし）
- **アーカイブ判定**は `archive_at <= now()` を動的にチェック。物理移動はしない
- **`data/auth/`** は `.gitignore` 済み（`data/` 全体が gitignore 対象）
- **スマホ対応は未実装**。意図的に後回し。PCブラウザ専用で開発を進める
- **`tools/seed-posts.php`** は冪等でない（実行のたびに別IDで重複作成される）
- ローカル: http://git15.local / 本番: d00e.motisan.info

---

## 現在のファイル構成

```
config.php                   ← DATA_DIR系パス定数 + SYNC_KEY / SYNC_ENDPOINT
index.php                    ← メインアプリ（認証チェック + ライトテーマ + API連携JS）
index.html                   ← 静的デモ（設計参照用、ライトテーマ済み）

api/
  .htaccess                  ← 全リクエストを index.php へ
  index.php                  ← ルーター
  lib/
    json.php
    response.php
    auth.php
  handlers/
    auth.php
    posts.php                ← react/comment/label サブリソース含む
    accounts.php
    evals.php
    search.php
    reactions.php
    sync.php                 ← GET /api/sync（APIキー認証、本番→ローカル同期用）

data/                        ← gitignore対象（ローカルのみ）
  accounts/moti.json
  auth/moti.json             ← setup-auth.phpで生成
  posts/*.json               ← 各投稿（strengthフィールド追加済み）
  evals/*.json
  reactions.json
  categories.json
  .sync_state.json           ← 最終同期時刻（sync.php が自動更新）

tools/
  setup-auth.php             ← パスワード設定CLI
  seed-posts.php             ← デモデータ投入CLI
  sync.php                   ← 本番→ローカル同期CLI（php tools/sync.php [--dry-run]）
  deploy-check.php
  submodule-setup.sh

api/cli/
  get_uncategorized.php
  get_unevaluated.php
  get_unposted_drafts.php
  get_post.php
  set_categories.php
  write_eval.php             ← --strength オプション追加済み
  post_draft.php

.claude-codex/
  CURRENT.md
  spec/
    ui-layout.md / data-schema.md / auth.md / api.md
    claude-modes.md / category-logic.md / eval-logic.md
    sync.md                  ← 同期仕様（新規）
    eval-mode.md             ← 評価モード専用ガイド（新規・これだけ読めば評価できる）
  change/
    deploy-setup.md
    initial-implementation.md
    phase1-eval-and-cli.md
    phase3-ui.md
```
