# CLAUDE.md

This file provides guidance to Claude Code when working with code in this repository.

## What this is

**Weather Station** (`live-weather-station`) — a WordPress plugin that displays
meteorological data from public or personal weather stations (Netatmo,
WeatherFlow, OpenWeatherMap, Pioupiou, and others) on a WordPress site, via
widgets, shortcodes, and blocks. Published on wordpress.org
(`live-weather-station`), maintained solo by Jason Rouet.

## Stack

- WordPress 4.9+, PHP 7.1+ (the `Requires PHP` floor, so first-party syntax stays PHP 7.1
  compatible). PHP 8.2 to 8.5 are supported and tested by CI (`php -l` on 7.1 and 8.2-8.5,
  PHPCompatibility); the supported floor becomes 8.3 in 2027. Policy in
  `.claude/rules/php-support.md`.
- No JS/CSS build step — assets under `admin/{css,js}` and `public/{css,js,font}`
  are committed directly, not compiled.
- No Composer, no `package.json`, no configured linter (phpcs/phpstan) or test
  suite (phpunit) at the time of this scaffold — don't invent commands that
  don't exist; if any of this gets added later, update this section.
- No PSR-4 autoloader — `autoload.php` at the repo root implements a
  hand-rolled `spl_autoload_register` for the `WeatherStation\` namespace.
- Architecture is namespace-based (`WeatherStation\<Area>\<Sub>\ClassName`),
  not a global function-prefix convention — see Architecture below.

## Commands

There is no build/lint/test tooling configured in this repo. Verify changes
by reading the code path and, where practical, exercising it in a real WP
install (see the `run` skill). Do not add `composer lint`/`stan`/`test`
invocations to instructions until such tooling actually exists here.

## Architecture

- One-liner pointer to `.claude/ARCHITECTURE.md` for the full repo layout.
- Namespace root: `WeatherStation\` — every class lives under it
  (`WeatherStation\System\...`, `WeatherStation\UI\...`,
  `WeatherStation\Data\...`, etc.). There is no `{{FUNCTION_PREFIX}}_`-style
  global function prefix; the handful of procedural bootstrap functions in
  `live-weather-station.php` and `init.php` use the historical
  `..._Live_Weather_Station` suffix instead — keep that convention for any
  new top-level bootstrap function, don't switch to a prefix.
- Text-domain: `live-weather-station`.

## Files never to modify

- `wp-config*.php`.
- Any committed build output (none currently committed — if one appears,
  treat it as generated and rebuild instead of hand-editing).
- Third-party plugins/dependencies.
- `wp-admin/**`, `wp-includes/**`, `vendor/**`, `node_modules/**` — also
  denied in `.claude/settings.json`.

## Git workflow

Default branch: `main`. Public repo (Weather-Station-Software org) — no
issue-first requirement.

One branch and one draft PR per version: `release/X.Y.Z`, PR titled with
the branch name. All the work of that version is committed there (no swarm of
independent PRs; external PRs are folded into it). When everything is ready
and verified: flip the PR to ready, merge, tag `X.Y.Z` (the `Release` workflow
creates a draft GitHub release), edit the notes, publish (this triggers the
wordpress.org deploy). Always check which PR/branch is current before
assuming `main` is the most advanced code.

## Pointers

- **Always loaded**: `.claude/rules/security.md`, `.claude/rules/a11y.md`,
  `.claude/rules/changelog.md`, `.claude/rules/version-bump.md`,
  `.claude/rules/php-support.md` — blocking.
- **Architecture**: `.claude/ARCHITECTURE.md`.
- **Skills** (on demand, add as needed): none yet — list them here as
  they're created, don't duplicate their content in this file.
