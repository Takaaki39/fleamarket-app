#!/usr/bin/env bash
set -euo pipefail

# 編集対象の .env ファイル（src配下）
ENV_FILE="src/.env"
ENV_TESTFILE="src/.env.testing"
NEW_VALUE="sk_te"
NEW_VALUE2="st_51SF3asKlmwDzpMVl74PjozjoZNXYNOQ4bwbXcSTgvDsx7vAtB2NQJjtYNSe6scqGIcyqAMCQQ2TusFQ7CAzWX1CY00lMG22acR"
TIMESTAMP="$(date +%Y%m%d%H%M%S)"
BACKUP="${ENV_FILE}.bak.${TIMESTAMP}"

if [ ! -f "$ENV_FILE" ]; then
  echo "error: ${ENV_FILE} が見つかりません。"
  exit 1
fi

# STRIPE_SECRET_KEY= が存在するかチェック
if ! grep -q -E '^[[:space:]]*(export[[:space:]]+)?STRIPE_SECRET_KEY[[:space:]]*=' "$ENV_FILE"; then
  echo "STRIPE_SECRET_KEY= を含む行が ${ENV_FILE} に見つかりませんでした。何もしません。"
  exit 0
fi

# バックアップ作成
# cp "$ENV_FILE" "$BACKUP"
# echo "バックアップを作成しました: $BACKUP"

# GNU sed か BSD sed（macOS）かを判定して、正しく上書き
if sed --version >/dev/null 2>&1; then
  # GNU sed
  sed -E -i "s/^[[:space:]]*(export[[:space:]]+)?(STRIPE_SECRET_KEY[[:space:]]*=[[:space:]]*).*/\1\2${NEW_VALUE}${NEW_VALUE2}/" "$ENV_FILE"
  sed -E -i "s/^[[:space:]]*(export[[:space:]]+)?(STRIPE_SECRET_KEY[[:space:]]*=[[:space:]]*).*/\1\2${NEW_VALUE}${NEW_VALUE2}/" "$ENV_TESTFILE"
else
  # macOS (BSD sed)
  sed -E -i '' "s/^[[:space:]]*(export[[:space:]]+)?(STRIPE_SECRET_KEY[[:space:]]*=[[:space:]]*).*/\1\2${NEW_VALUE}${NEW_VALUE2}/" "$ENV_FILE"
  sed -E -i '' "s/^[[:space:]]*(export[[:space:]]+)?(STRIPE_SECRET_KEY[[:space:]]*=[[:space:]]*).*/\1\2${NEW_VALUE}${NEW_VALUE2}/" "$ENV_TESTFILE"
fi

echo "更新完了: ${ENV_FILE}, ${ENV_TESTFILE}"
echo "→ STRIPE_SECRET_KEY の値を '${NEW_VALUE}${NEW_VALUE2}' に設定しました。"
