# 現在の作業状態

> 最終更新: 2026-05-07

---

## 直近で完了したこと

（評価プロトコル完成 + 全投稿評価完了）

- **未評価9件の評価・カテゴリ付与を完了** — e65a26 / e9325a / e9fb34 / ea4b2a / f17e26 / f23ad5 / fb6fe4 / 2ba8ed / 81c896。全件 `get_unevaluated.php` が空配列を返す状態になった
- **eval-logic.md に2ルールを明文化**
  - カテゴリは評価と同時に必ず付与する（`set_categories.php` で書き込む）
  - 確認が必要なものは先にまとめて1回で聞く。後から割り込まない
- **context ファイル新規作成**
  - `moti-life-goals.md` — 生活設計・将来目標
  - `moti-consumption.md` — 消費・購入・ベストバイ履歴（Claude、家3回など）
- **context ファイル更新**
  - `moti-cognitive-style.md` — 社会構造への批評と美学、知性の多次元性、動機付けられた推論への自覚を追記
  - `moti-expression.md` — 社会観察ユーモア、特殊フォントの別事例を追記
- **INDEX.md** — 新規2ファイルをエントリ追加

---

## 次にやること

1. **data/ サブモジュール化**（todo）
3. **投稿詳細パネルにIDを表示**（todo）
4. **「評価不要」状態を投稿につけられるようにする**（フォームも対応）（todo）
5. **issue: ヘッダー「投稿する」ボタンでフォーム入力がリセットされる**

---

## 確定済みの評価ルール

- **1件ずつ確認** → OKが出てから書き込む（連続評価しない）
- **カテゴリは必ず付与** — 評価案と同時に提示し、OKが出たら `set_categories.php` で書き込む
- **確認をまとめる** — 評価・カテゴリ・context更新案を一括提示してOKを取る
- **コメント文数は柔軟** — 短い/ユーモア系は1〜2文、思想系は必要なだけ
- **humor投稿** → replies[] に `instruction:"humor-reaction"` のひとことリプライを自動追加
- **もち関連カテゴリ** → 短く乗っかる形、軸3以下でも可
- **感じている投稿** → 姿勢として読み替えない。一般との差・トレードオフを示す
- **カテゴリは comma-separated で渡す** — `set_categories.php --categories='A,B'`
- **JSON書き込み・読み込みは確認不要**で進める
- 詳細: `.claude-codex/spec/eval-logic.md`
- もちさん文脈: `.claude-codex/context/INDEX.md` → 該当MDの順に読む

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
    INDEX.md
    moti-social-stance.md / moti-cognitive-style.md
    moti-expression.md / moti-tech.md
    moti-life-goals.md / moti-consumption.md   ← 今回追加
  change/
    ...

data/                        ← gitignore対象（ローカルのみ）
tools/
```
