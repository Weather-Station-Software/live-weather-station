# Version bump: blocking

Before merging (or proposing to mark ready) a PR that changes the version number, check that all four places carry the same number:

1. `live-weather-station.php`: header `Version:`.
2. `init.php`: `LWS_VERSION`.
3. `readme.txt`: `Stable tag:`.
4. `changelog.txt`: top section `#X.Y.Z / ...`.

The `Release` workflow (`.github/workflows/release.yml`) refuses a tag when they differ. A mismatch left 3.8.14 undeployed on wordpress.org, so also report any pre-existing mismatch instead of ignoring it.
