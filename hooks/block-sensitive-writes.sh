#!/usr/bin/env bash
# PreToolUse: allow the edit (exit 0) or block it (exit 2).
# Blocks secret and credential paths. Does not block CLAUDE.local.md.

input=$(cat)

extract_path() {
  if command -v python >/dev/null 2>&1; then
    printf '%s' "$input" | python -c 'import json,sys
try:
    data=json.load(sys.stdin)
except Exception:
    sys.exit(0)
tool=data.get("tool_input") or {}
print(tool.get("file_path") or tool.get("path") or "")'
    return
  fi
  if command -v py >/dev/null 2>&1; then
    printf '%s' "$input" | py -3 -c 'import json,sys
try:
    data=json.load(sys.stdin)
except Exception:
    sys.exit(0)
tool=data.get("tool_input") or {}
print(tool.get("file_path") or tool.get("path") or "")'
    return
  fi
  printf '%s' "$input" | sed -n 's/.*"file_path"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' | head -n 1
}

path=$(extract_path)
if [ -z "$path" ]; then
  exit 0
fi

normalized=$(printf '%s' "$path" | tr '\\' '/')
base=$(basename "$normalized")

case "$normalized" in
  */.git/*|*/.git) echo "Blocked write under .git: $path" >&2; exit 2 ;;
esac

case "$base" in
  .env|.env.*|wp-config.php|id_rsa|id_ed25519|*.pem|*.p12|*.pfx|*.key)
    echo "Blocked write to sensitive file: $path" >&2
    exit 2
    ;;
esac

case "$normalized" in
  *credentials*|*secrets*)
    echo "Blocked write to sensitive path: $path" >&2
    exit 2
    ;;
esac

exit 0
