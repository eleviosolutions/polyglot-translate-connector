=== Polyglot Translate Connector ===
Contributors: eleviosolutions
Tags: translation, connectors, ai, multilingual, byok
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Registers Polyglot Translate as a translation provider in WordPress 7.0 Connectors. BYOK — bring your own API key.

== Description ==

**Polyglot Translate Connector** is a lightweight plugin that registers [Polyglot Translate](https://polyglot-translate.cloud) as a `translation` connector inside the new WordPress 7.0 Connectors API. It adds Polyglot to your **Settings → Connectors** screen alongside Anthropic, OpenAI, and Google — giving you one familiar place to manage your translation backend credentials.

This plugin does **not** perform translations itself. Instead, it exposes Polyglot Cloud as a translation provider that any compatible WordPress plugin can discover and consume. If you want a turn-key translation experience (frontend language switcher, glossary, admin editor), install [Polyglot Translate](https://wordpress.org/plugins/polyglot-translate/) as well.

**Use this plugin if you:**

* Want to securely manage your Polyglot API key from the native WP 7.0 Connectors UI (with automatic masking in REST responses).
* Run an enterprise or agency-built WordPress site where developers consume Polyglot directly via custom code, REST calls, or the `polyglot/wp-sdk` Composer package — and don't need our full plugin UI.
* Want other plugins (AI content generators, SEO tools, WooCommerce extensions, etc.) to be able to discover Polyglot as a translation backend on your site, share the same key, and stay in sync.

**BYOK (Bring Your Own Key) workflow:**

1. Sign up at [app.polyglot-translate.cloud](https://app.polyglot-translate.cloud) and generate an API key.
2. Install this plugin.
3. Open **Settings → Connectors**, find "Polyglot Translate", paste your key.
4. Done. The key is now available to any WordPress plugin via the `polyglot_get_api_key()` helper function — and lives encrypted-at-rest via the WordPress core Connectors machinery.

**Credential precedence (highest to lowest):**

1. `POLYGLOT_TRANSLATE_API_KEY` environment variable (set via your hosting panel)
2. `POLYGLOT_TRANSLATE_API_KEY` PHP constant (defined in `wp-config.php`)
3. Database option set via Settings → Connectors

**Advanced — custom API endpoint:**

If you operate a self-hosted Polyglot deployment, a reverse-proxy gateway (Cloudflare Workers, AWS Lambda, etc.), or a staging environment, you can override the API base URL via **Settings → Polyglot Translate**. HTTPS-only.

== Installation ==

1. Upload `polyglot-translate-connector` to `/wp-content/plugins/`, or install via the Plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings → Connectors** and paste your Polyglot Translate API key.
4. (Optional) Open **Site Health → Info → Polyglot Translate Connector** to verify the connection state.

== Frequently Asked Questions ==

= Do I need this plugin if I already have Polyglot Translate installed? =

No — but you can use both. The main Polyglot Translate plugin gives you a full end-user UI. This connector plugin is for developers and operators who want only the credential management layer.

= Will this plugin send translation requests? =

No. It performs only ONE network call: a `GET /v1/health` preflight against Polyglot Cloud each time you save your API key, to verify it's valid. The result is cached for 5 minutes.

= How do other plugins use this connector? =

Other plugins call the global helper `polyglot_get_api_key()` to retrieve the key (with full env/constant/DB precedence), then make REST API calls against Polyglot Cloud directly — or use the official `polyglot/wp-sdk` Composer package for higher-level conveniences (batching, retry, error normalization).

= Is my API key visible to other plugins on the site? =

Yes. This is the same model WordPress core uses for all Connectors (Anthropic, OpenAI, etc.) — site settings are shared. If you need per-plugin scoping, generate a separate scoped key for each consumer in your Polyglot Dashboard.

= What happens if Polyglot Cloud is temporarily unreachable when I save my key? =

The key is still saved. An admin notice will warn you that validation could not complete, and the Site Health check will show "Recommended — unreachable." You can re-test at any time from Site Health.

= Can I use a custom API endpoint? =

Yes. Open **Settings → Polyglot Translate** and enter an HTTPS URL. Useful for self-hosted deployments, reverse proxies, or staging environments.

== Screenshots ==

1. Polyglot Translate connector card in the WordPress 7.0 Settings → Connectors screen, alongside Anthropic, OpenAI, and Google.
2. Connected state with masked API key.
3. Site Health debug info section showing connector metadata, key source, and last validation timestamp.

== Changelog ==

= 1.0.0 — 2026-05-?? =

* Initial release.
* Registers Polyglot Translate as a `translation` type connector in WordPress 7.0 Connectors API.
* Preflight API key validation via `GET /v1/health` (warn-don't-block strategy).
* Public helpers: `polyglot_get_api_key()`, `polyglot_get_api_base_url()`, `polyglot_is_connected()`, `polyglot_get_connector_id()`, `polyglot_get_api_key_source()`.
* Site Health check integration (top-level test + debug info).
* BYOK endpoint URL override via Settings → Polyglot Translate.
* Localization-ready (English, Serbian).

== Upgrade Notice ==

= 1.0.0 =

Initial release.
