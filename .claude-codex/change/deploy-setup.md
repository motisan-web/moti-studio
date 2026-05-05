# デプロイ設定（2026-05-06）

- 本番URL: d00e.motisan.info
- デプロイ方式: FTP（SamKirkland/FTP-Deploy-Action@v4.4.0）
- トリガー: push（main）+ 手動（workflow_dispatch）
- server-dir: ./（FTPユーザーのルート = サブドメインの public_html）
- パーミッション自動修正: なし（初回デプロイ後に手動で644/755設定）
- 除外設定: .git*、.github/、.claude-codex/、CLAUDE.md、data/
- GitHub Secrets: STAGING_SERVER / STAGING_USERNAME / STAGING_PASSWORD
- data/ サブモジュール化: 未実施（別途プライベートリポジトリで対応予定）
