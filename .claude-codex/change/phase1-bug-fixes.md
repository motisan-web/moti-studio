# Phase 1 バグ修正（3件）

> 2026-05-07

---

## 1. `&#039;` HTMLエンティティがそのまま表示される

**原因**: `20260506_a215f8.json` の body/intent に PHP の `htmlspecialchars` でエンコードされた文字（`&#039;` = `'`）がリテラルで保存されており、JS の `esc()` が `&` を `&amp;` にさらに変換して二重エスケープになっていた。

**修正**:
- `data/posts/20260506_a215f8.json`: `html_entity_decode()` で全フィールドを復元
- `views/pc.php`: `decodeEntities(s)` 関数を追加し、`mdToHtml` の先頭で適用
  - `&amp;` `&lt;` `&gt;` `&quot;` `&#039;` を対応する文字に戻す
  - 同様の旧データが存在しても壊れない防御策

---

## 2. UIからカテゴリを作成しても `categories.json` が更新されない

**原因**: 投稿の POST/PUT 時に投稿 JSON にはカテゴリが保存されるが、マスターリスト `categories.json` の更新処理がなかった。フォームを再度開くと新カテゴリがトグル一覧に出ない。

**修正**:
- `api/handlers/posts.php`: `categories_merge(array $new_cats)` ヘルパーを追加
  - `categories.json` の現在リストに新カテゴリを和集合でマージ、変更があれば書き込む
  - POST（新規作成）・PUT（categories 更新時）の両方で呼び出す
- `views/pc.php`: `submitCreate` / `submitEdit` 完了後に `INIT.categories` にも追記
  - ページリロードなしで次の操作でもトグルに反映される

---

## 3. ヘッダー「投稿する」ボタンで入力中フォームがリセットされる

**原因**: `openCreate()` が無条件にパネルを再初期化していた。入力中に別の投稿カードをクリック後、ヘッダーボタンを押すと内容が消えた。

**修正**:
- `views/pc.php` `openCreate()`: パネルが開いており、タイトルが「新規投稿」の場合は早期 return して再初期化しない
