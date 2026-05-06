# Phase 6: PC/SP ビュー分離

> 2026-05-07

## 変更内容

### `index.php` 全面書き換え
- ルーターのみに特化。HTML/CSS/JSはすべて views/ に移動
- UA判定: `iPhone|Android.*Mobile|Windows Phone` に一致 → `views/sp.php`
- `?sp=1` クエリパラメータ付きでも SP ビューを強制表示（PC上でのプレビュー用）
- それ以外 → `views/pc.php`

### `views/pc.php` 新規作成
- 旧 `index.php` の HTML/CSS/JS をそのまま移植
- モバイル対応試みで追加した不要コードを除去
  - `body[data-sp]` CSS ルール群
  - `@media (max-width: 767px)` ブロック
  - `.sb-overlay` / `.mobile-menu-btn` CSS・HTML
  - `openMobileSidebar` / `closeMobileSidebar` JS 関数
  - 各 navTo 等の `closeMobileSidebar()` 呼び出し
- PC専用3ペインUIとして完全動作する状態を維持

### `views/sp.php` 新規作成
- ログインフォーム（認証なし時）: pc.php と同等、モバイル向けに padding 調整
- 認証後: 「スマホUI 準備中」プレースホルダー + PCで開くリンク
- SP UIは今後ゼロから再設計・実装する

## 意図

CSS メディアクエリでの「PCレイアウトに追記する」方式が実質的に機能しなかったため、
PC/SP を完全に別ファイルで管理する構成に切り替え。
SP UIは後から views/sp.php を独立して設計・実装できる。
