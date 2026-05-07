# コピーフィードバック（トースト）追加・todo登録

> 2026-05-07

---

## 変更内容

### todo 追加（CLAUDE.md）

作業前に以下を todo に登録してから実装した：
- `IDコピー時に「コピーしました」フィードバックを表示する`（今回実装・削除済み）
- `JS を機能単位でファイル分割する`（未着手・残存）
- `CSS をファイル分割する`（未着手・残存）

### コピートースト実装（views/pc.php）

- `showCopyToast()` 関数を追加
  - `#copyToast` 要素に `.show` クラスを付与し 1600ms 後に除去
  - 連打時は `clearTimeout` でタイマーをリセット
- `copyText()` を修正：成功・フォールバック両ルートで `showCopyToast()` を呼ぶ
- `<div id="copyToast">` を `</body>` 直前に追加
- CSS: `.copy-toast` / `.copy-toast.show` を追加
  - 画面下中央にフェードイン・スライドアップするトースト
