# CHANGELOG: blocking

Before each commit touching plugin code (not `changelog.txt`, `readme.*` or docs): check whether `changelog.txt` needs a line, and if so write it following the established format. If a notable change is made without `changelog.txt` reflecting it, say so before committing instead of committing silently.

## Hook at every commit

1. Find the version being worked on: branch `release/3.9.0` means the top section `#3.9.0 / xx <month> <year>` of `changelog.txt`. Never create a new section for a commit that does not start a new version cycle.
2. Compare the diff to that section: does the change deserve a line (new, or an edit of an existing line covering the same work), or is it too minor/internal (comment typo, refactoring with no visible effect)?
3. If yes, add it. One line = one change, never merge two distinct changes in one line.
4. If unsure, ask before committing.

## File format

- Sections are `#X.Y.Z / <month> <day>th, <year>`, newest first. The version in progress stays dated `xx <month> <year>` until it is tagged.
- Lines start with `* ` and a category: `New:`, `Improvement:`, `Bug fix:`, `Security:`. A full sentence, no bug description alone: say what works now.
- Technical detail (file, function) is fine at the end of a line when useful to a contributor, but the line must stay readable for a non-developer.
