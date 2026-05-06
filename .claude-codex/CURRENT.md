# 現在の作業状態

> 最終更新: 2026-05-07

---

## 直近で完了したこと

（`change/phase5-audit-and-eval-protocol.md`）

- **UIとAPIの整合性監査** — APIはあるがUIがない機能を発見・修正
- **検索UI追加** — トップバーに検索フォーム（デバウンス、ESCクリア）
- **ランダムボタン追加** — トップバーに🎲ランダムボタン
- **evalパネルにリプライ表示** — `replies[]` を「Claude リプライ」セクションで表示
- **submitEditのバグ修正** — PUT を2回呼んでいたのを1回に統合
- **spec/api.md 更新** — handlers 実態・PUT categories・reactions/search/random を追加
- **eval-logic.md に評価プロトコル追記** — 評価コメント文章・リプライ文章の詳細プロトコル
- **claude-modes.md のリプライモード詳細化** — replies 追記手順・JSON構造を明文化
- **CLAUDE.md todo 整理** — 完了済み2件を削除（Xserver設定・リアクション絵文字UI）

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

（`change/phase1-eval-and-cli.md`）

- **`spec/eval-logic.md`** — 評価軸選定・スコアリング・コメントルールの設計ドキュメント作成
- **`api/cli/` 全7本実装** — get_uncategorized / get_unevaluated / get_unposted_drafts / get_post / set_categories / write_eval / post_draft
  - post_draft は投稿済みdraftを `data/drafts/posted/` に自動退避

（`change/initial-implementation.md`）

- **ライトテーマ化**（`index.html`）CSS変数をダーク→ライトに全面変換
- **`data/` ディレクトリ構成作成** — accounts/posts/evals/avatars/drafts/auth + 初期JSON
- **`config.php`** — DATA_DIR系のパス定数を一元管理
- **`api/lib/` ユーティリティ** — json.php / response.php / auth.php（ロックアウトロジック込み）
- **APIハンドラー全実装** — auth / posts（react/comment/label含む）/ accounts / evals / search
- **`tools/setup-auth.php`** — bcryptパスワード設定スクリプト（確認入力付き）
- **`tools/seed-posts.php`** — デモ投稿14件+評価データを流し込むスクリプト
- **`index.php` 本実装** — PHP認証チェック + ログイン画面 + JS全面APIコール版
- **動作確認済み** — ログイン・投稿表示・詳細パネル・リアクション・ラベル・コメント

---

## 次にやること

- data/ サブモジュール化（プライベートリポジトリ作成後に `tools/submodule-setup.sh` 実行）
- 実際に「評価して」「リプライして」を試してみてプロトコルを検証・調整
- スマホ対応（後回し・最終的には作る）

---

## 注意事項・既知の仕様

- **Claude API は使わない**。Claudeがチャットでコマンドを打ちPHPヘルパーを実行する方式
- **リアクションは加算のみ**（誰が押したか管理しない。デモのような active 状態なし）
- **アーカイブ判定**は `archive_at <= now()` を動的にチェック。物理移動はしない
- **`data/auth/`** は `.gitignore` 済み（`data/` 全体が gitignore 対象）
- **スマホ対応は未実装**。意図的に後回し。PCブラウザ専用で開発を進める
- **`tools/seed-posts.php`** は冪等でない（実行のたびに別IDで重複作成される）
- ローカル: http://git15.local / 本番: d00e.motisan.info
- **`write_eval.php` は JSON 丸ごと上書き**。リプライ追記時は既存の replies[] を保持して渡すこと
- **カテゴリ管理パネルは閲覧のみ**。`categories.json` へのグローバル追加/削除APIは未実装（投稿フォームから付与した新カテゴリはその投稿のみに反映）

---

## 現在のファイル構成

```
config.php                   ← DATA_DIR系パス定数
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

data/                        ← gitignore対象（ローカルのみ）
  accounts/moti.json
  auth/moti.json             ← setup-auth.phpで生成
  posts/*.json               ← seed-posts.phpで14件生成済み
  evals/*.json
  reactions.json
  categories.json

tools/
  setup-auth.php             ← パスワード設定CLI
  seed-posts.php             ← デモデータ投入CLI

.claude-codex/
  CURRENT.md
  spec/
    ui-layout.md / data-schema.md / auth.md / api.md / claude-modes.md / category-logic.md
  change/
    deploy-setup.md
    initial-implementation.md
```
