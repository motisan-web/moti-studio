# Phase 3: UI補完

> 2026-05-06

## 変更内容

### `index.php` — 大規模追加

**投稿編集・削除**
- 詳細パネルヘッダーに ✏️（編集）・🗑（削除）ボタンを追加（`panelActions` div）
- `openEdit(postId)` — 現在値が入力済みのフォームをパネルで表示
- `submitEdit(postId)` — PUT api/posts/{id} で本文/タイトル/意図/URL/カテゴリ/アーカイブ期限を更新
- `deletePost(postId)` — 確認後 DELETE api/posts/{id}、グリッドから除去

**アーカイブ期限設定**
- 作成フォームと編集フォームの両方に `datetime-local` 入力 + 「1週間後」ボタンを追加
- `setArchiveWeek(inputId)` — 現在時刻 + 7日を自動入力

**アカウント切替UI**
- サイドバーのアカウントエリアをクリックするとドロップダウン表示
- 「すべて」+ 各アカウントを選択してタイムラインをフィルタリング（`currentAccountFilter`）

**アカウント情報編集ページ**
- サイドバーに「アカウント設定」ナビ項目を追加
- `openAccountEdit()` — 表示名・カラー（テキスト＋カラーピッカー）・アイコン形状のフォーム
- `submitAccountEdit()` — PUT api/accounts/{id}、サイドバーの表示も即時更新

**レーダーチャート（5角形グラフ）**
- `radarChart(axes)` — SVGで同心多角形グリッド・軸線・データポリゴンを描画
- 評価パネルのコメント直下に表示、その下に既存のバーチャートを残す

**リアクション絵文字登録UI（機能化）**
- `openReactMgmt()` — カスタム絵文字一覧（削除ボタン付き）+ 登録フォーム
- `onEmojiFileSelect()` — ファイル選択時にプレビュー表示、スラッグを自動補完
- `submitEmoji()` — FormData で POST api/reactions（multipart）
- `deleteCustomEmoji(slug)` — DELETE api/reactions/{slug}
- `buildPalette()` — GET api/reactions でカスタム絵文字を取得してパレットに追加

### `api/handlers/reactions.php` — 新規作成
- GET /api/reactions — reactions.json を返す
- POST /api/reactions — 画像アップロード（mime検証・`img/reactions/`保存）+ JSON登録
- DELETE /api/reactions/{slug} — slug削除

### `api/index.php`
- `reactions` ルートを追加

### `api/handlers/posts.php`
- PUT api/posts/{id} の `$allowed` に `categories` を追加

### `img/reactions/` — ディレクトリ作成
