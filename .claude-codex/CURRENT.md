# 現在の作業状態

> 最終更新: 2026-05-06

---

## 直近で完了したこと

（`change/phase4-infra.md`）

- **`.htaccess`（ルート）** — `data/` `api/lib/` `api/cli/` `.claude-codex/` への直接HTTPアクセス遮断、Xserver向けPHP設定追加
- **`tools/sync.php`** — セッション認証済みブラウザからアクセスするデータ同期ツール。JSONバンドル形式でエクスポート/インポート（`data/` + `img/reactions/`）
- **`tools/deploy-check.php`** — `php tools/deploy-check.php` で実行するデプロイ前チェック（PHP版・ファイル・ディレクトリ・権限・拡張）
- **`tools/submodule-setup.sh`** — `data/` をプライベートGitリポジトリ化してサブモジュール登録する手順スクリプト

（`change/phase3-ui.md`）

- **投稿編集UI** — 詳細パネルヘッダーに ✏️/🗑 ボタン、`openEdit` / `submitEdit` / `deletePost`
- **アーカイブ期限設定** — 作成・編集フォームに `datetime-local` + 1週間後ボタン
- **アカウント切替UI** — サイドバークリックでドロップダウン、タイムラインをアカウントIDでフィルタリング
- **アカウント情報編集** — 表示名・カラー（カラーピッカー）・アイコン形状を編集、保存後サイドバー即時反映
- **レーダーチャート** — SVGで5軸グラフ、evalパネルのコメント直下に表示
- **リアクション絵文字登録UI** — 画像アップロード（mime検証）+ slug登録、削除も可、パレットに反映
- **`api/handlers/reactions.php`** — GET / POST / DELETE
- **PUT posts/{id}** に `categories` を追加

（`change/phase1-eval-and-cli.md`）

- **`spec/eval-logic.md`** — 17軸評価の選定ルール・スコア基準・コメントトーンを設計
- **`api/cli/` 全7本** — get_uncategorized / get_unevaluated / get_unposted_drafts / get_post / set_categories / write_eval / post_draft

---

## 次にやること

- **Xserver 初回デプロイ** — FTPアップロード → パーミッション設定（644/755）→ `tools/deploy-check.php` で確認 → 動作確認
- **data/ サブモジュール化** — GitHubにプライベートリポジトリ作成後 `bash tools/submodule-setup.sh <URL>` を実行
- **スマホ対応**（後回し・最終的には作る）

---

## 注意事項・既知の仕様

- **Claude API は使わない**。Claudeがチャットでコマンドを実行しPHPヘルパー経由で処理する方式
- **リアクションは加算のみ**（誰が押したか管理しない）
- **アーカイブ判定**は `archive_at <= now()` を動的にチェック。物理移動はしない
- **`data/`** は `.gitignore` 対象（ローカルのみ存在）。`tools/sync.php` でサーバー間同期する
- **スマホ対応は未実装**。意図的に後回し。PCブラウザ専用で開発を進める
- **`tools/seed-posts.php`** は冪等でない（実行のたびに別IDで重複作成される）
- ローカル: http://git15.local / 本番: d00e.motisan.info

---

## 現在のファイル構成

```
.htaccess                    ← data/ 等の直接アクセス遮断・Xserver設定
config.php                   ← DATA_DIR系パス定数
index.php                    ← メインアプリ（認証・3ペインUI・全機能JS）
index.html                   ← 静的デモ（設計参照用）

api/
  .htaccess                  ← 全リクエストを index.php へ
  index.php                  ← ルーター（auth/posts/accounts/evals/search/reactions）
  lib/
    json.php / response.php / auth.php
  handlers/
    auth.php / posts.php / accounts.php / evals.php / search.php / reactions.php
  cli/
    get_uncategorized.php / get_unevaluated.php / get_unposted_drafts.php
    get_post.php / set_categories.php / write_eval.php / post_draft.php

img/
  reactions/                 ← カスタム絵文字画像（web公開）

data/                        ← gitignore対象（ローカルのみ）
  accounts/moti.json
  auth/moti.json
  posts/*.json
  evals/*.json
  reactions.json / categories.json
  drafts/                    ← Claude投稿モード用MD
  drafts/posted/             ← post_draft.php 実行後の退避先

tools/
  setup-auth.php             ← パスワード設定CLI
  seed-posts.php             ← デモデータ投入CLI（冪等でない）
  sync.php                   ← ブラウザからデータ同期（要ログイン）
  deploy-check.php           ← デプロイ前チェックCLI
  submodule-setup.sh         ← data/ サブモジュール化スクリプト

.claude-codex/
  CURRENT.md
  spec/
    ui-layout.md / data-schema.md / auth.md / api.md
    claude-modes.md / category-logic.md / eval-logic.md
  change/
    initial-implementation.md / phase1-eval-and-cli.md
    phase3-ui.md / phase4-infra.md
```
