# evalHtml の axes フォーマット不整合修正

> 2026-05-07

---

## 原因

`20260506_aa002c` の eval データが旧フォーマットで書き込まれていた。

| | 旧フォーマット（バグあり） | 現行フォーマット（正） |
|---|---|---|
| `axes` | `{"humor": 85, "originality": 68}` | `[{"key":"humor","label":"ユーモア","score":85}, ...]` |
| `replies` | `evaluation` の内側 | top-level の `replies[]` |

`evalHtml` は現行フォーマット（配列）を前提にしており、オブジェクトに `.map()` を呼んでエラーになっていた。

---

## 変更内容

### data/evals/20260506_aa002c.json

- `evaluation.axes` をオブジェクト → 配列形式に変換
- `evaluation.replies` を top-level `replies` に移動し `evaluation` 内から削除
- `evaluation.generated_at` を補完（`2026-05-06T00:00:00`）

### views/pc.php

- `normalizeAxes(raw)` 関数を追加
  - 配列ならそのまま返す
  - オブジェクト（旧フォーマット）なら `[{key, label, score}]` に変換してから返す
  - ラベル変換マップを内蔵（17軸分）
- `evalHtml` を修正
  - `ev.axes` の代わりに `normalizeAxes(ev.axes)` の結果 `axesArr` を使用
  - `radarChart` にも `axesArr` を渡すよう変更
