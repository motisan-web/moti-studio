# Phase 5: UIとAPIの整合性監査・評価プロトコル整備

> 2026-05-07

---

## 実施内容

### CLAUDE.md todo 整理
- Xserver パーミッション設定: 完了済みのため削除
- リアクション絵文字登録UI: Phase 3 で実装済みのため削除

### UIバグ修正 — index.php
- `submitEdit()` が `PUT /api/posts/{id}` を2回連続で呼んでいたのを1回に統合
  - 1回目: title/body/intent/url/archive_at
  - 2回目: categories（別途、コメント付きで実装されていた）
  - categories は PUT の allowed フィールドに含まれているため、1回のリクエストにまとめた

### UI追加 — index.php（APIはあったがUIがなかった機能）

**検索機能**
- トップバーに検索入力欄を追加（デバウンス300ms）
- ESCキーで検索クリア
- `GET /api/search?q=` を呼び出し、結果をグリッドに表示
- タイトルバーに「「{q}」の検索結果」と表示

**ランダム取得**
- トップバーに「🎲 ランダム」ボタンを追加
- `GET /api/posts/random` を呼び出し、詳細パネルで表示

### UI追加 — evalHtml() のリプライ表示
- `evalData.replies[]` がある場合、評価パネルの下部に「Claude リプライ」セクションを追加
- 各リプライの instruction・生成日時・comments（label + body + moti_reply）を表示
- `moti_reply` がある場合はインデントして表示

### spec/api.md 更新
- handlers リストを実態に合わせた（comments.php / labels.php は存在しない → posts.php 統合）
- `PUT /api/posts/{id}` の categories 更新に関する説明を実態に合わせた
- URLルール一覧に reactions・search・random のエンドポイントを追加
- カスタム絵文字 API の節を新設

### spec/eval-logic.md 追記
- **評価コメントの文章プロトコル**: 1〜3文構成、各文の役割、トーン、NG例と修正例、例文
- **リプライコメントの文章プロトコル**: 共通ルール、指示パターン別（3視点・ファクトチェック・深掘り・もちさん視点）

### spec/claude-modes.md 補足
- リプライモードの手順を大幅に詳細化
  - 既存 eval ファイルの読み取り方
  - replies への追記時の注意（丸ごと上書き方式）
  - reply_id の命名規則
  - リプライJSONの完全な構造例
