# Phase 4: インフラ

> 2026-05-06

## 変更内容

### `.htaccess`（ルート）更新
- `Options -Indexes` 追加（ディレクトリ一覧禁止）
- `data/`, `api/lib/`, `api/cli/`, `.claude-codex/` への直接HTTPアクセスを遮断
- Xserver向け PHP 設定（display_errors Off / log_errors On / UTF-8）

### `tools/sync.php` 新規作成
- セッション認証必須（未ログインは `/` にリダイレクト）
- **エクスポート**: `data/` + `img/reactions/` を走査してbase64エンコードしたJSONバンドルをダウンロード
- **インポート**: JSONバンドルをアップロードするとファイルを復元（パストラバーサル防止付き）
- 同期手順を UI 内に表示（本番→ローカル / ローカル→本番）

### `tools/deploy-check.php` 新規作成
- CLI (`php tools/deploy-check.php`) で実行するデプロイ前チェック
- PHP バージョン / 必要ファイル / ディレクトリ存在・書き込み権限 / 拡張チェック
- Xserver パーミッション設定ガイドを出力（644/755コマンド）

### `tools/submodule-setup.sh` 新規作成
- `data/` をプライベートリポジトリとして git init → push → 親へサブモジュール登録
- Usage: `bash tools/submodule-setup.sh git@github.com:motisan-web/15-data.git`
- 今後の更新手順もスクリプト末尾に記載

## 動作確認
- `php tools/deploy-check.php` → 全項目 ✅（mbstring は Apache PHP でロード済みのため ⚠️ のみ）
