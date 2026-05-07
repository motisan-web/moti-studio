# 現在の作業状態

> 最終更新: 2026-05-07

---

## 直近で完了したこと

（評価プロトコル整備 + 投稿評価）

- **worktree廃止** — `.claude/settings.json` に WorktreeCreate ブロックフックを設置。CLAUDE.md にも Git運用ルール追記（mainに直接コミット）
- **Bash全許可** — `.claude/settings.json` の permissions.allow に `Bash(*)`等を追加。確認なしで実行可
- **eval-logic.md 復元・整備** — コメント文数制限を「柔軟」に変更。確定済みルール（humor-reaction・もち関連・一言投稿）追記
- **category-logic.md** — 「言葉」カテゴリ追加
- **context/ 整備** — moti-social-stance / moti-cognitive-style / moti-expression / moti-tech の4ファイル新規作成。投稿IDの記録を必須化
- **評価済み** — aa002c / d5514c / dcf906（このセッション分）を含む計16件完了

---

## 評価進捗

**評価済み（16件）**:
20260421_56345f / 20260506_12af34 / 20260506_210028 / 20260506_263278 / 20260506_341f73 / 20260506_39bd06 / 20260506_419d9d / 20260506_41f626 / 20260506_4da5ca / 20260506_55dca2 / 20260506_5fceae / 20260506_638803 / 20260506_69118b / 20260506_aa002c / 20260506_d5514c / 20260506_dcf906

**未評価（残り15件）**:
72aa8f / 782fe2 / 7e423a / 7f1861 / 887907 / 90a38e / 9c64a9 / a215f8 / e65a26 / e9325a / e9fb34 / ea4b2a / f17e26 / f23ad5 / fb6fe4（すべて 20260506_ プレフィックス）

※ 20260507_ 系: 2ba8ed / 81c896 も残り（CLAUDE.mdの未評価リストからは一旦除外されていたが要確認）

---

## 次にやること

1. **評価の続き** — 上記未評価15件を1件ずつ評価・書き込み
2. **Xserver 初回デプロイ** — FTPアップロード → パーミッション設定 → `tools/deploy-check.php` で確認
3. **data/ サブモジュール化**（todo）
4. **投稿詳細パネルにIDを表示**（todo）
5. **「評価不要」状態を投稿につけられるようにする**（フォームも対応）（todo）

---

## 確定済みの評価ルール

- **1件ずつ確認** → OKが出てから書き込む（連続評価しない）
- **コメント文数は柔軟** — 短い/ユーモア系は1〜2文、思想系は必要なだけ
- **humor投稿** → replies[] に `instruction:"humor-reaction"` のひとことリプライを自動追加
- **もち関連カテゴリ** → 短く乗っかる形、軸3以下でも可
- **感じている投稿** → 姿勢として読み替えない。一般との差・トレードオフを示す
- **カテゴリは comma-separated で渡す** — `set_categories.php --categories='A,B'`（JSON配列ではない）
- **JSON書き込み・読み込みは確認不要**で進める
- 詳細: `.claude-codex/spec/eval-logic.md`

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

api/
  index.php                  ← ルーター
  lib/  handlers/  cli/

.claude/
  settings.json              ← Bash全許可 + WorktreeCreate ブロックフック

.claude-codex/
  CURRENT.md
  spec/
    eval-logic.md / category-logic.md / claude-modes.md / data-schema.md / ...
  context/
    INDEX.md / moti-social-stance.md / moti-cognitive-style.md
    moti-expression.md / moti-tech.md
  change/
    ...

data/                        ← gitignore対象（ローカルのみ）
tools/
```
