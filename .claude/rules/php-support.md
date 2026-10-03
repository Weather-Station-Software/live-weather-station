# PHP support: blocking

The plugin keeps `Requires PHP: 7.1` but only recent PHP versions are supported and tested. Before merging (or proposing to mark ready) a PR that touches PHP code, the CI matrix, `Requires PHP` or the readme, check the rules below.

## What is supported

- **Supported** = the PHP versions still maintained by php.net at release time (https://www.php.net/supported-versions.php).
- **Tested today**: 8.2, 8.3, 8.4 and 8.5. The `CI` workflow (`.github/workflows/ci.yml`) runs `php -l` on 7.1 and 8.2 to 8.5, and PHPCompatibility for `7.1-8.5` and `8.2-8.5`.
- **Floor**: the supported floor becomes **8.3** in 2027 (when 8.2 leaves php.net security support). The 2027 version then drops 8.2 from the matrix and from the readme wording.
- **7.1 syntax stays valid** while `Requires PHP` is 7.1: no typed properties, `fn`, `match`, `?->`, union types, constructor promotion, nor PHP 8-only functions without a guard. Vendored libraries may carry minimal, commented compat patches (`// PHP 8.x compat: ...`).

## Changing `Requires PHP`

- It only changes in an announced release, with a `changelog.txt` line and a `readme.txt` note (FAQ and Upgrade Notice).
- **4.0.0 is reserved** for the future block-editor rebuild. A PHP floor change is never a reason for a major version.

## Keep consistent (all four, same PR)

1. `readme.txt`: `Requires PHP:` and the FAQ / Upgrade Notice wording.
2. `live-weather-station.php`: header `Requires PHP:` (added in 3.9.0).
3. The CI matrix in `.github/workflows/ci.yml` (`php` and PHPCompatibility ranges).
4. `.claude/CLAUDE.md`: the Stack section.

## Before each release

Check https://www.php.net/supported-versions.php and, if a version entered or left the supported list, update the four places above and the changelog. A new PHP release (8.6...) is added to the matrix as soon as `shivammathur/setup-php` supports it; its deprecations are fixed before the release that claims support.
