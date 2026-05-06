#!/bin/bash
# data/ をプライベートリポジトリとしてサブモジュール化する手順
#
# 前提: GitHub にプライベートリポジトリを作成済みであること
# 例:   https://github.com/motisan-web/15-data (プライベート)
#
# Usage: bash tools/submodule-setup.sh <GitHub-repo-URL>
# 例:    bash tools/submodule-setup.sh git@github.com:motisan-web/15-data.git

set -e

REPO_URL="${1:-}"
ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DATA_DIR="${ROOT_DIR}/data"

if [ -z "$REPO_URL" ]; then
    echo "Usage: bash tools/submodule-setup.sh <GitHub-repo-URL>"
    echo "例:    bash tools/submodule-setup.sh git@github.com:motisan-web/15-data.git"
    exit 1
fi

echo "=== data/ サブモジュール化セットアップ ==="
echo "リポジトリURL: ${REPO_URL}"
echo ""

# Step 1: data/ を git リポジトリとして初期化
echo "[1/5] data/ を git リポジトリとして初期化..."
cd "$DATA_DIR"
git init
git add .
git commit -m "初期データ"

# Step 2: リモートを追加してプッシュ
echo "[2/5] リモートに push..."
git remote add origin "$REPO_URL"
git branch -M main
git push -u origin main

# Step 3: 親リポジトリ側の .git/modules から data/ を除去（サブモジュール登録のため）
echo "[3/5] 親リポジトリにサブモジュールとして登録..."
cd "$ROOT_DIR"

# data/ を一度 git の管理から外す（既存のトラッキングがある場合）
git rm -r --cached data/ 2>/dev/null || true

# サブモジュールとして追加
git submodule add "$REPO_URL" data/

# Step 4: .gitmodules を確認
echo "[4/5] .gitmodules 確認..."
cat .gitmodules

# Step 5: コミット
echo "[5/5] 親リポジトリにコミット..."
git add .gitmodules
git commit -m "data/ をサブモジュールとして登録"

echo ""
echo "✅ セットアップ完了！"
echo ""
echo "=== 今後の使い方 ==="
echo "データを更新した後: (data/ の中で)"
echo "  cd data/"
echo "  git add . && git commit -m '更新内容'"
echo "  git push"
echo ""
echo "別の環境でクローンするとき:"
echo "  git clone --recurse-submodules <親リポジトリURL>"
echo "  # または既存クローン済みの場合:"
echo "  git submodule update --init"
