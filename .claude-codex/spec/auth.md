# 認証仕様

> 最終更新: 2026-05-06

---

## 方式

- アカウントごとのパスワード認証
- PHPの `$_SESSION` によるセッション管理
- 既存セッションの強制ログアウトなし（複数端末での同時ログイン可）

---

## ファイル構成

```
data/
  accounts/moti.json    ← 表示情報のみ（gitignore対象外）
  auth/moti.json        ← 認証情報（gitignore対象）
```

`data/auth/` は `.gitignore` に追加する。

---

## data/auth/{id}.json

```json
{
  "password_hash": "$2y$10$...",
  "failed_attempts": 0,
  "last_failed_at": null,
  "locked_until": null
}
```

| フィールド | 型 | 説明 |
|---|---|---|
| `password_hash` | string | `password_hash()` でbcryptハッシュ化 |
| `failed_attempts` | int | 現在の連続失敗回数 |
| `last_failed_at` | string \| null | 最終失敗日時（ISO 8601） |
| `locked_until` | string \| null | ロック解除日時。null なら未ロック |

---

## ロックアウトロジック

- 失敗5回でロックアウト
- `locked_until` = 最終失敗時刻 + 15分
- ロック中はパスワード検証をスキップしてエラーを返す
- ロック期限を過ぎたらカウントリセット（次回ログイン試行時に自動解除）
- ログイン成功時は `failed_attempts` と `last_failed_at` をリセット

---

## セッション

- PHP標準セッション（`session_start()`）
- セッションに格納する情報: `account_id`
- セッション有効期限: PHPデフォルト（ブラウザ閉じるまで）
- ログアウト: `session_destroy()`
