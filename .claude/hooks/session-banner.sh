#!/usr/bin/env bash
# .claude/hooks/session-banner.sh
#
# SessionStart hook — prints a compact context banner when Claude Code
# opens a session on this repo. Exit 0 always so it never blocks startup.
#
# Adapted from wpfr-2026's version (Valentin Grenier) for Weather Station.

set -u

cd "$(git rev-parse --show-toplevel 2>/dev/null || echo .)" || exit 0

php_version=$(php -r 'echo PHP_VERSION;' 2>/dev/null || echo 'not installed')
node_version=$(node --version 2>/dev/null || echo 'not installed')
branch=$(git branch --show-current 2>/dev/null || echo 'unknown')

echo "────────────────────────────────────────────────────────────────"
echo "  Weather Station (live-weather-station) — session context"
echo "────────────────────────────────────────────────────────────────"
printf "  PHP:          %s\n" "$php_version"
printf "  Node:         %s\n" "$node_version"
printf "  Branch:       %s\n" "$branch"
echo "  Recent commits:"
git log --oneline -3 2>/dev/null | sed 's/^/    /'
echo "────────────────────────────────────────────────────────────────"

exit 0
