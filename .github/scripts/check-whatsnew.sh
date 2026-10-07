#!/usr/bin/env bash
# Checks the "See what's new" link of the plugin (LIVE_WEATHER_STATION_WHATSNEW in init.php).
#   check-whatsnew.sh warn [init.php]     the link must be the article of the current major.minor version (a warning if not)
#   check-whatsnew.sh release [init.php]  same, as an error, and the page must answer HTTP 200
set -u
MODE="${1:-warn}"
INIT="${2:-init.php}"
URL=$(sed -n "s/^define('LIVE_WEATHER_STATION_WHATSNEW', '\(.*\)');/\1/p" "$INIT")
VERSION=$(sed -n "s/^define('LIVE_WEATHER_STATION_VERSION', '\(.*\)');/\1/p" "$INIT")
MINOR=$(echo "$VERSION" | cut -d. -f1,2 | tr . -)
fail() {
  if [ "$MODE" = release ]; then echo "::error::$1"; exit 1; fi
  echo "::warning::$1"
}
if [ -z "$URL" ] || [ -z "$VERSION" ]; then echo "::error::LIVE_WEATHER_STATION_WHATSNEW or LIVE_WEATHER_STATION_VERSION not found in $INIT"; exit 1; fi
if ! echo "$URL" | grep -Eq "(^|[^0-9])${MINOR}([^0-9]|\$)"; then
  fail "The \"See what's new\" link ($URL) is not the article of version ${VERSION}. Until it is updated, the plugin falls back to the GitHub release notes."
fi
if [ "$MODE" = release ]; then
  CODE=$(curl -s -o /dev/null -L --max-time 20 -w '%{http_code}' "$URL")
  if [ "$CODE" != 200 ]; then echo "::error::The \"See what's new\" link ($URL) answers HTTP $CODE: publish the article before releasing."; exit 1; fi
fi
echo "What's new link: $URL"
