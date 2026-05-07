# スマホ対応UI実装

> 2026-05-07

## 変更内容

### `index.php`

- **モバイルCSSを追加**（`@media (max-width: 767px)`）
  - サイドバー: `position: fixed` のドロワー方式（デフォルト非表示、`mobile-open`クラスで表示）
  - サイドバーオーバーレイ: `.sb-overlay`（タップで閉じる半透明黒幕）
  - 右パネル: `position: fixed; top: 56px; left:0; right:0; bottom:0` でフルスクリーン
  - `.panel-head` / `.panel-body`: `min-width: 0` でPC用の最小幅をリセット
  - 投稿グリッド: 強制1カラム
  - topbar: padding縮小・タイトルflex:1・投稿ボタン小型化
- **ハンバーガーボタン**（`.mobile-menu-btn`）をtopbarに追加、PC幅では`display:none`
- **オーバーレイdiv** `#sbOverlay` をsidebarの直前に追加
- **`openMobileSidebar()` / `closeMobileSidebar()`** 関数を追加
- **navTo / filterCat / openAccountEdit / openCatMgmt / openReactMgmt** の先頭に `closeMobileSidebar()` を追加し、ナビ操作でドロワーが自動で閉じるように

## 意図

PC用3ペインレイアウトを崩さずに、モバイルではサイドバーをドロワー、右パネルをフルスクリーンに変換。
JSとCSSの変更のみで、APIやデータ層には一切触れていない。
