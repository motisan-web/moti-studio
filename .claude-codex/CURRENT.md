# 現在の作業状態

> 最終更新: 2026-05-06

---

## 直近で完了したこと

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

残りの主要 todo：

1. **分析JSONのスキーマ設計・実装**（5角形グラフUI）
2. **リアクション絵文字の登録UI実装**（画像アップロード→`data/reactions.json`登録）
3. **Xserverデプロイ準備**（パーミッション設定 644/755、.htaccess確認）
4. **data/ サブモジュール化**（プライベートリポジトリ）
5. **スマホ対応**（後回し・最終的には作る）

---

## 注意事項・既知の仕様

- **Claude API は使わない**。Claudeがチャットでコマンドを打ちPHPヘルパーを実行する方式
- **リアクションは加算のみ**（誰が押したか管理しない。デモのような active 状態なし）
- **アーカイブ判定**は `archive_at <= now()` を動的にチェック。物理移動はしない
- **`data/auth/`** は `.gitignore` 済み（`data/` 全体が gitignore 対象）
- **スマホ対応は未実装**。意図的に後回し。PCブラウザ専用で開発を進める
- **`tools/seed-posts.php`** は冪等でない（実行のたびに別IDで重複作成される）
- ローカル: http://git15.local / 本番: d00e.motisan.info

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
