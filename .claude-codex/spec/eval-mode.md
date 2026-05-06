# 評価モード専用ガイド

> 最終更新: 2026-05-07
> このドキュメント1枚だけ読めば評価モードを実行できる。他のspecを読む必要はない。

---

## 評価モードの全手順

### Step 1: 同期（本番 → ローカル）

```bash
php tools/sync.php
```

### Step 2: 未評価投稿を取得

```bash
php api/cli/get_unevaluated.php
```

→ 評価が必要な投稿IDの配列が返る。

### Step 3: 各投稿を取得して評価・strength付与

```bash
php api/cli/get_post.php --id=20260504_abc123
```

投稿を読んで以下を決定する：

**評価（5軸）:**
- 17軸候補から投稿の核心に合う5軸を選ぶ（毎回選び直す、固定しない）
- スコアは 0〜100 整数。選んだ軸は基本 50 以上にする（低ければ別の軸へ）
- 評価コメントは 1〜3 文。もちさんに語りかける形式

**strength（投稿強度）:**
- 1〜10 の整数で投稿全体の密度・深さを判定

| strength | 目安 |
|---|---|
| 1〜2 | 一言メモ・感情のみ・意味が薄い |
| 3〜4 | 短い思いつき・日常の記録 |
| 5〜6 | 一定の考察・読む価値がある |
| 7〜8 | 構造的・具体的・論理がある |
| 9〜10 | 文章量多・深く考えられた・ユニーク |

### Step 4: 評価を書き込む

```bash
php api/cli/write_eval.php \
  --id=20260504_abc123 \
  --strength=7 \
  --json='{"evaluation":{"comment":"...","axes":[...],"generated_at":"2026-05-07T12:00:00"},"replies":[]}'
```

**渡すJSONの形式:**

```json
{
  "evaluation": {
    "comment": "環境設計で行動を誘導するという視点が鮮明です。",
    "axes": [
      { "key": "actionability", "label": "行動性",  "score": 88 },
      { "key": "creativity",    "label": "創造性",  "score": 76 },
      { "key": "specificity",   "label": "具体性",  "score": 72 },
      { "key": "introspection", "label": "内省度",  "score": 65 },
      { "key": "humor",         "label": "ユーモア","score": 58 }
    ],
    "generated_at": "2026-05-07T12:00:00"
  },
  "replies": []
}
```

- `replies` は既存リプライを保持するために渡す（新規評価時は空配列 `[]`）
- `--strength` は 1〜10 の整数

### Step 5: 全件完了後に同期（ローカル → 保存）

```bash
php tools/sync.php
```

---

## 17軸候補（参照用）

| key | label | 説明 |
|---|---|---|
| `empathy` | 共感性 | 読者が「わかる」と思えるか |
| `originality` | 独自性 | 他にない視点・表現か |
| `specificity` | 具体性 | 具体的な事例・数値・場面があるか |
| `introspection` | 内省度 | 自分自身への深い問いかけがあるか |
| `expandability` | 発展可能性 | 読後にアイデアが広がるか |
| `emotion` | 感情強度 | 感情が前面に出ているか |
| `criticality` | 批評性 | 物事を批判的・多角的に見ているか |
| `humor` | ユーモア | 笑いや軽さがあるか |
| `sociality` | 社会接続性 | 社会・他者・世界との繋がりがあるか |
| `actionability` | 行動性 | 具体的な行動・習慣設計につながるか |
| `expertise` | 専門性 | 専門知識・技術的な深さがあるか |
| `clarity` | 明瞭性 | 読みやすく、言いたいことが明確か |
| `curiosity` | 好奇心 | 「なぜ？」「どうなる？」という探求心があるか |
| `depth` | 思考深度 | 表面でなく深く考えているか |
| `vulnerability` | 素直さ | 弱さ・迷い・不安を正直に書いているか |
| `timeliness` | 時事性 | 今の時代・状況と関連しているか |
| `creativity` | 創造性 | 新しい組み合わせ・発明的な発想があるか |

---

## 軸選定のコツ

- 最も強く現れている軸を優先する（投稿の主題と一致させる）
- 似た軸（例: `depth` と `introspection`）は1つだけ選ぶ
- `humor` / `vulnerability` は明確に現れているときだけ選ぶ
- 選んだ軸のスコアが 30 以下になるなら別の軸に変える
- 思考が深いが内省 or 発展どちらか迷う → 自分を掘るなら `introspection`、外に広がるなら `expandability`

---

## CLIヘルパー一覧（参照用）

```bash
php api/cli/get_uncategorized.php          # categorized_at が null の投稿IDリスト
php api/cli/get_unevaluated.php            # 未評価の投稿IDリスト
php api/cli/get_post.php --id=xxx          # 投稿1件取得
php api/cli/set_categories.php --id=xxx --categories="思想,AI"  # カテゴリ書き込み
php api/cli/write_eval.php --id=xxx --strength=7 --json='...'   # 評価+strength書き込み
php tools/sync.php                         # 本番→ローカル同期
php tools/sync.php --dry-run               # 同期プレビュー（書き込みなし）
```
