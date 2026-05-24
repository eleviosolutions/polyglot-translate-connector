# Polyglot Translate Connector for WordPress

> Registers [Polyglot Translate](https://polyglot-translate.cloud) as a `translation` provider in the WordPress 7.0 Connectors API. BYOK credential management for any WP plugin that wants to use Polyglot Cloud as a translation backend.

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![Requires WordPress 7.0+](https://img.shields.io/badge/WordPress-7.0%2B-blue.svg)](https://wordpress.org/)
[![Requires PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://www.php.net/)

## What this plugin does

WordPress 7.0 introduced the **Connectors API** — a native way to manage external service credentials (AI providers, spam filtering, etc.) from a single `Settings → Connectors` screen. This plugin registers Polyglot Translate as a `translation` type connector, exposing the Polyglot API key field next to Anthropic, OpenAI, Google.

**Three key benefits:**

1. **For site owners:** familiar UI for managing the Polyglot API key, with automatic REST-response masking and env-var/PHP-constant override support (no API keys in your database if your security policy forbids it).
2. **For plugin developers:** a stable PHP helper API to discover Polyglot on a site (`polyglot_get_api_key()`, `polyglot_is_connected()`) without each plugin shipping its own settings screen.
3. **For agencies & enterprises:** install just this lightweight connector (200 LOC PHP, no UI clutter) and consume Polyglot via your own custom code or the [`polyglot/wp-sdk`](https://github.com/eleviosolutions/polyglot-wp-sdk) Composer package.

## What this plugin does NOT do

- ❌ Does **not** translate content. Install the full [Polyglot Translate](https://wordpress.org/plugins/polyglot-translate/) plugin for end-user translation features.
- ❌ Does **not** add any admin UI beyond the native Connectors page and a small Advanced settings sub-page for endpoint overrides.
- ❌ Does **not** expose REST endpoints or AJAX handlers.
- ❌ Does **not** store anything in your database beyond two small options (endpoint override + last validation result).

## Install

### From WordPress.org (recommended)

Search "Polyglot Translate Connector" in your WP admin → Plugins → Add New → Install → Activate.

### From source

```bash
cd /wp-content/plugins/
git clone https://github.com/eleviosolutions/polyglot-translate-connector.git
```

Then activate via the WordPress admin.

## Usage — for site owners

1. Generate an API key at [app.polyglot-translate.cloud](https://app.polyglot-translate.cloud).
2. Activate this plugin.
3. **Settings → Connectors** → find Polyglot Translate → paste key → Save.
4. Verify connection state in **Tools → Site Health → Info → Polyglot Translate Connector**.

## Usage — for plugin developers

If you are building a WordPress plugin and want to use Polyglot Cloud as your translation backend, this connector gives you a discovery mechanism that doesn't require your users to manually configure Polyglot in your plugin's own UI.

```php
// In your plugin code:
if ( function_exists( 'polyglot_is_connected' ) && polyglot_is_connected() ) {
    $api_key  = polyglot_get_api_key();
    $base_url = polyglot_get_api_base_url();

    // Make REST calls directly:
    $response = wp_remote_post(
        $base_url . '/v1/translate',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode([
                'text'         => 'Save changes',
                'sourceLang'   => 'en',
                'targetLangs'  => [ 'de', 'sr-Latn' ],
            ]),
            'timeout' => 10,
        ]
    );
}
```

For higher-level conveniences (batching, retry policy, error normalization, response DTOs), use the official [`polyglot/wp-sdk`](https://github.com/eleviosolutions/polyglot-wp-sdk) Composer package (separate repo, separate release cycle).

## Public API — stable surface

| Function | Returns | Description |
|---|---|---|
| `polyglot_get_api_key(): ?string` | API key or `null` | Reads from env → constant → DB option, honoring WP 7.0 Connectors precedence. |
| `polyglot_get_api_base_url(): string` | Base URL string | Reads endpoint override or returns default `https://api.polyglot-translate.cloud`. |
| `polyglot_is_connected(): bool` | true/false | True if a key exists AND last validation status is not "invalid". |
| `polyglot_get_connector_id(): string` | Connector ID string | Returns `polyglot` — the registered connector ID. |
| `polyglot_get_api_key_source(): string` | Source label | Returns `env`, `constant`, `database`, or `none`. |

These functions are guaranteed stable across 1.x. Breaking changes only on major version bumps (semver).

## Credential precedence

Highest to lowest:

1. `POLYGLOT_TRANSLATE_API_KEY` environment variable
2. `POLYGLOT_TRANSLATE_API_KEY` PHP constant (in `wp-config.php`)
3. Database option (set via Settings → Connectors)

Same for the optional API base URL override (`POLYGLOT_TRANSLATE_API_BASE`).

## Development

```bash
composer install
composer test       # PHPUnit
composer lint       # WordPress Coding Standards
composer lint:fix   # Auto-fix WPCS issues
```

CI runs on every push via GitHub Actions (`.github/workflows/test.yml`).

## Contributing

Bug reports, feature requests, and PRs welcome at [github.com/eleviosolutions/polyglot-translate-connector](https://github.com/eleviosolutions/polyglot-translate-connector).

## License

GPL v2 or later — see [LICENSE](./LICENSE).

## Related projects

- [Polyglot Translate](https://wordpress.org/plugins/polyglot-translate/) — full translation plugin (consumer of this connector).
- [`polyglot/wp-sdk`](https://github.com/eleviosolutions/polyglot-wp-sdk) — Composer SDK for plugin developers.
- [Polyglot Cloud](https://polyglot-translate.cloud) — the translation API service this connector points to.
