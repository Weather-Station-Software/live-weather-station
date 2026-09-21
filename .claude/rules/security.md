# Security rules (WordPress plugin)

These are blocking in code review. Any change that violates them gets flagged.

## Escape every output

- Text → `esc_html()`
- HTML attributes → `esc_attr()`
- URLs (href, src, action) → `esc_url()`
- Rich content (WYSIWYG, RichText) → `wp_kses_post()`
- `<textarea>` content → `esc_textarea()`
- Inline JS → `esc_js()`

**Never** `echo $attributes['x']` or `echo $var`. Wrap or pre-escape.

## Sanitize every input

- Free text → `sanitize_text_field()`
- Email → `sanitize_email()`
- Integer → `absint()`
- Slug → `sanitize_key()` or `sanitize_title()`
- Rich content → `wp_kses_post()` or `wp_kses()` with a custom allowlist

Sanitize on read, escape on write.

## Nonce every state-changing action

- Forms: `wp_nonce_field( 'action', 'field' )` on render, `check_admin_referer( 'action', 'field' )` on submit.
- AJAX: `wp_create_nonce( 'action' )` + `check_ajax_referer( 'action', 'nonce' )`.
- Links that trigger changes (`admin-post.php?...`): `wp_nonce_url()`.

## Prepare every SQL query

Use `$wpdb->prepare()` with placeholders (`%s`, `%d`, `%f`, `%i` for identifiers on WP 6.2+). Never interpolate `$_GET`, `$_POST`, or user-controlled variables directly into query strings.

## External data ingestion (weather station APIs)

This plugin's whole job is pulling data from external sources — Netatmo,
WeatherFlow/Ambient, BloomSky, OpenWeatherMap, Pioupiou, and clientraw feeds,
each with its own client under `includes/traits/*PluginClient.php` and vendor
SDK under `includes/libraries/<vendor>/`. For each vendor client:

- Sanitize/validate every field pulled from the vendor API or feed
  (temperature, coordinates, station name, free-text fields) before it's
  persisted — never trust the shape or content of a third-party payload.
- Never decode/unserialize a payload twice (once at ingestion, again at
  render time) — pick one normalization point per vendor client and keep it
  there.
- Never store API credentials (tokens, keys) in the clear if avoidable —
  and make sure they're never echoed back via REST responses, dashboard
  widgets, or Site Health debug info.

## Defense in depth

- The standalone bootstrap files (`live-weather-station.php`, `init.php`,
  `autoload.php`, `functions.php`, `uninstall.php`) guard against direct
  access (`if ( ! defined( 'WPINC' ) ) { die; }` or equivalent). Most of
  `includes/classes/` is pure class definitions with no top-level executable
  code, so an `ABSPATH`/`WPINC` guard there is not the same kind of gap —
  see `.claude/ARCHITECTURE.md` for the detail. Don't mass-add guards to
  class files as a "fix" without checking whether the file has any
  top-level executable statement first.
- Sensitive files are blocked by `.claude/settings.json` `permissions.deny`:
  `wp-config*.php`, `wp-admin/**`, `wp-includes/**`, `vendor/**`,
  `node_modules/**`, and `dist/**`/`build/**` if committed.

## Enforcement

- No phpcs/phpstan is configured in this repo today — review these rules by
  reading the diff, not by running a linter. If tooling gets added later,
  wire it in here.
