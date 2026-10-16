#!/usr/bin/env bash
# PostToolUse: php -l on a PHP file the edit just touched.
# Exit 2 when PHP reports a syntax error. Exit 0 when php is missing or the file is not PHP.

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
case "$path" in
  *.php) ;;
  *) exit 0 ;;
esac

if [ ! -f "$path" ]; then
  exit 0
fi

PHP_BIN=""
if command -v php >/dev/null 2>&1; then
  PHP_BIN="php"
fi

if [ -z "$PHP_BIN" ]; then
  exit 0
fi

if ! "$PHP_BIN" -l "$path"; then
  echo "PHP syntax check failed: $path" >&2
  exit 2
fi

exit 0
