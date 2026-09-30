# Contributing

Thanks for helping. Bugs and ideas go in [issues](https://github.com/Weather-Station-Software/live-weather-station/issues); small fixes can come as a PR directly.

## Changelog, new version and convention

Please do NOT touch `changelog.txt` or the version numbers. You can draft changelog lines in the description of your PR, in the format of `changelog.txt`, but do not assume the next version number.

Proposed PRs are systematically merged into a PR named after the next version (for example `release/3.9.0`, titled "WIP 3.9.0"), which stays a draft until everything for that version is ready. When it is, it is merged, the version is tagged and the tag creates a draft GitHub release. Publishing that release deploys the plugin to wordpress.org.
