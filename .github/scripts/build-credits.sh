#!/usr/bin/env bash
# Builds the "Reporters" part of the release notes from a credits file (.github/credits/X.Y.Z.txt).
#   build-credits.sh .github/credits/3.9.0.txt
# One line per reporter: source|name|link|issue      (lines starting with # and empty lines are ignored)
# source: github, wordpress.org, forum, blog or other (anything else counts as other). The link and the issue are optional.
# To leave someone out on request, delete the line.
set -u
FILE="${1:-}"
[ -f "$FILE" ] || exit 0
LINES=$(grep -v -E '^[[:space:]]*(#|$)' "$FILE" || true)
[ -n "$LINES" ] || exit 0
echo
echo "## Reporters"
echo
echo "Thanks to the people who reported the problems fixed in this version, by where the report came from."
for pair in "github:GitHub issues" "wordpress.org:WordPress.org support forum" "forum:Forum of the site" "blog:Comments of the blog" "other:Other"; do
  key="${pair%%:*}"
  title="${pair#*:}"
  group=$(echo "$LINES" | awk -F'|' -v k="$key" '
    { s=$1; gsub(/^[ \t]+|[ \t]+$/,"",s); if (s != "github" && s != "wordpress.org" && s != "forum" && s != "blog") s="other"; if (s == k) print }')
  [ -n "$group" ] || continue
  entries=$(echo "$group" | awk -F'|' '
    { name=$2; link=$3; issue=$4
      gsub(/^[ \t]+|[ \t]+$/,"",name); gsub(/^[ \t]+|[ \t]+$/,"",link); gsub(/^[ \t]+|[ \t]+$/,"",issue)
      if (name == "") next
      line="- " name
      if (link != "") line=line " ([source](" link ")"
      if (issue != "") line=line (link != "" ? ", " : " (") issue ")"
      else if (link != "") line=line ")"
      print line }')
  [ -n "$entries" ] || continue
  echo
  echo "### $title"
  echo
  echo "$entries"
done
