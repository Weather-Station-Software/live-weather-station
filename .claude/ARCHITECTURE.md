# Architecture

How this repository is laid out and why. For day-to-day commands see
`CLAUDE.md`.

## Repository layout

```text
.
├── .claude/                     # Claude Code config
│   ├── CLAUDE.md                # Session-start context (auto-loaded)
│   ├── ARCHITECTURE.md          # This file
│   ├── rules/                   # Auto-loaded every session: security, a11y
│   ├── guardrails.json          # piloté par `.claude/guardrails.json` (plugin guardrails de jaz-ai)
│   ├── skills/                  # On-demand skills, add as they're written
│   └── settings.json            # Permissions allow/deny + hooks wiring
├── .github/workflows/           # CI — currently just the wp.org release deploy
├── .wordpress-org/              # Plugin directory assets (banners, screenshots)
├── live-weather-station.php     # Plugin bootstrap: header, activation/
│                                 # deactivation/uninstall hook registration
├── init.php                     # Loads functions.php + autoload.php, defines
│                                 # run_Live_Weather_Station()
├── autoload.php                 # Hand-rolled spl_autoload_register for the
│                                 # WeatherStation\ namespace (no Composer)
├── functions.php                # Global helper functions used before the
│                                 # autoloader/class layer is available
├── uninstall.php                # WP-standard uninstall entry point
├── includes/                    # Namespaced classes (WeatherStation\...)
│   ├── classes/                 # UI/business-logic classes (142 files) —
│   │                             # one class per screen/feature area
│   ├── system/                  # Cross-cutting infra: Cache, Logger,
│   │                             # Environment, BackgroundProcessManager
│   ├── process/                 # Import/export pipelines (CSV/DSV/NDJSON,
│   │                             # Netatmo/Pioupiou station importers)
│   ├── traits/                  # Shared behavior mixed into station/vendor
│   │                             # client classes (Ambient, BloomSky, etc.)
│   └── libraries/                # Vendored third-party SDKs per data
│                                  # source (ambient, bloomsky, netatmo, owm,
│                                  # piou, solaris, misc)
├── admin/                       # wp-admin side: SystemPluginAdmin.php +
│                                 # css/js/partials for settings screens
├── public/                      # Front-end side: SystemPluginFrontend.php +
│                                 # css/js/font/partials for rendered widgets
├── languages/                   # .pot/.po/.mo translation catalogs
└── readme.txt / readme.md       # wordpress.org plugin readme + GitHub readme
```

## `includes/` — modules

| Dir | Responsibility |
| ---- | --------------- |
| `includes/classes/` | One class per admin screen, widget, dashboard panel, or computation (analytics, ephemeris, health, history). |
| `includes/system/` | Infra shared across the plugin: caching, logging, environment detection, background jobs, storage. |
| `includes/process/` | Data import/export pipelines — per-format exporters/importers and per-vendor station importers (Netatmo, Pioupiou). |
| `includes/traits/` | Traits mixed into per-vendor client classes, and a few generic utility traits (`CommonUtilities`, `DataArraysGenerator`). |
| `includes/libraries/` | Vendored third-party API SDKs, one subdir per data source — treat as third-party, don't refactor to house style. |

**Convention:** this plugin is class-based (`WeatherStation\...` namespace),
not procedural — the classic `if ( ! defined( 'ABSPATH' ) ) { exit; }` guard
that `rules/security.md` asks for on procedural `inc/`/`includes/` files
applies here mainly to the few standalone bootstrap files
(`live-weather-station.php`, `init.php`, `autoload.php`, `functions.php`,
`uninstall.php`) rather than to every file under `includes/classes/` — those
are pure class definitions with no top-level executable code. Only 3 files
in the tree currently carry an explicit `ABSPATH` check
(`includes/classes/I18nHelper.php`, `includes/classes/SystemPluginStats.php`,
`includes/system/Storage.php`); this is a pre-existing, deliberate pattern,
not a gap to mass-fix.

## Design constraints to preserve

- No Composer/PSR-4 autoloader by design — `autoload.php` is a hand-rolled
  `spl_autoload_register`. Don't introduce a `composer.json` autoloader
  without discussing it; wordpress.org SVN deploy packages the repo as-is.
- No JS/CSS build pipeline — assets in `admin/{css,js}` and
  `public/{css,js,font}` are committed source, not build output. Don't wire
  a bundler in without asking; it would change the release packaging story.
- `includes/libraries/` holds vendored third-party SDKs — treat as
  read-only/upstream code, not house style to enforce.

## CI/CD

- `.github/workflows/deploy-new-release.yml` — on a published GitHub
  release, runs `jaz-on/wordpress-actions/dotorg-plugin-deploy` to push the
  tag to the wordpress.org SVN repo. No test/lint job runs in CI today.

## Known pitfalls

- No automated tests or static analysis exist yet — changes need manual
  verification against a real WordPress install with at least one
  configured data source (see the `run` skill for launching one).
- `readme.txt` (wordpress.org format) and `readme.md` (GitHub) are both
  maintained by hand and can drift — check both when bumping the version.
