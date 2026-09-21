#!/usr/bin/env bash
# .claude/hooks/lint-edited.sh
#
# PostToolUse hook — after Claude Edits or Writes a file, run the
# appropriate linter/formatter on it, and flag a missing ABSPATH/WPINC
# guard on standalone (non-class) WordPress PHP files. Non-blocking: never
# fails even if issues are found; it just surfaces output so Claude sees it
# in the next tool result.
#
# Matched tools: Edit, Write.
# Base: wpfr-2026's lint-edited.sh (Valentin Grenier). Adds the ABSPATH
# check — a gap found repeatedly across jardin-*/wpis-* repos that phpcs
# alone doesn't catch. This repo has no phpcs/prettier configured, so those
# steps below are no-ops until such tooling is added.

set -u

payload=$(cat)

if command -v jq >/dev/null 2>&1; then
    file=$(printf '%s' "$payload" | jq -r '.tool_input.file_path // empty')
else
    file=$(printf '%s' "$payload" | python3 -c 'import json,sys; d=json.load(sys.stdin); print(d.get("tool_input",{}).get("file_path",""))' 2>/dev/null)
fi

[ -z "$file" ] && exit 0
[ ! -f "$file" ] && exit 0

cd "$(git rev-parse --show-toplevel 2>/dev/null || echo .)" || exit 0

case "$file" in
    *.php)
        # ABSPATH/WPINC guard check — only for standalone bootstrap-style
        # files at the repo root or a direct includes/*.php file (dirname
        # exactly "includes"), not for the namespaced classes nested under
        # includes/classes|system|process|traits (bash case globs match
        # "/" too, so this needs an explicit dirname check, not a glob).
        file_dir=$(dirname "$file")
        check_guard=0
        case "$file" in
            live-weather-station.php|init.php|autoload.php|functions.php|uninstall.php)
                check_guard=1
                ;;
        esac
        [ "$file_dir" = "includes" ] && check_guard=1

        if [ "$check_guard" -eq 1 ]; then
            if ! grep -qE "defined\( *['\"](ABSPATH|WPINC)['\"] *\)" "$file" 2>/dev/null; then
                echo "⚠ $file: no ABSPATH/WPINC guard (defined('ABSPATH') || exit;) — check if this file has top-level executable code"
            fi
        fi

        if [ -x vendor/bin/phpcs ]; then
            vendor/bin/phpcs "$file" 2>&1 | tail -20 || true
        fi
        ;;
    *.js|*.scss|*.md|*.json)
        prettier="./node_modules/.bin/prettier"
        if [ -x "$prettier" ]; then
            "$prettier" --write "$file" 2>&1 | tail -5 || true
        fi
        ;;
esac

exit 0
