=== Polyglot Translate Connector ===
Contributors: polyglottranslate
Tags: translation, translation-api, ai-translation, multilingual, connector
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Self-learning translation API for WordPress 7.0+. 90+ languages, native Connectors integration, secure credential management, any-plugin ready.

== Description ==

**Polyglot Translate Connector** plugs the [Polyglot Translate](https://polyglot-translate.cloud) cloud into the brand-new **WordPress 7.0 Connectors API** — the same screen where you configure Anthropic, OpenAI, and Google. One API key, securely stored, available to every plugin on your site that needs translation.

= Why this exists =

WordPress 7.0 introduced a native way for plugins to share credentials for external services. Instead of every translation-aware plugin shipping its own settings page and asking you to paste the same key over and over, this connector lets you set it **once** in **Settings → Connectors** and have it just work everywhere.

= What makes Polyglot Translate different =

Polyglot is not "another machine translation API." It's a **translation memory that learns from every edit anyone makes on your site or across the network**. Translations get better the more they're used — the system remembers good phrasings, learns your terminology, and progressively replaces machine output with verified high-quality results. You pay per character, not per call, and your translation memory belongs to you (cancel anytime — your data stays).

= What this plugin actually does =

* Registers Polyglot Translate as a `translation`-type connector in the WordPress 7.0 Connectors registry.
* Adds the Polyglot card to **Settings → Connectors**, with a logo, description, "Get your API key" link, and credential field.
* Validates your API key against Polyglot Cloud on save (warns you, doesn't block, if the cloud is unreachable).
* Exposes 5 stable PHP helper functions (`polyglot_get_api_key()`, `polyglot_is_connected()`, `polyglot_get_api_base_url()`, etc.) that **any other plugin on your site can use** to consume Polyglot transparently.
* Adds a Site Health check so you can verify the connection state anytime.
* Supports environment variables and PHP constants for credentials — enterprise-friendly, no API keys in your database if your security policy forbids it.

= What this plugin does NOT do =

* It does **not** translate content. It's a credential + discovery layer. For an end-to-end translation experience (front-end language switcher, glossary, post editor), install the main [Polyglot Translate](https://wordpress.org/plugins/polyglot-translate/) plugin alongside this one.
* It does **not** add visible admin UI beyond the native Connectors screen and one small Advanced settings sub-page for endpoint overrides.
* It does **not** ship any AJAX handlers or REST endpoints of its own.

= For plugin developers — discover Polyglot from your plugin =

If you're building a WordPress plugin (AI content generator, SEO tool, WooCommerce extension, multilingual workflow…) and you want to use Polyglot Cloud as your translation backend, you no longer need to ship your own settings UI or ask users for an API key. This connector handles credential discovery:

`if ( function_exists( 'polyglot_is_connected' ) && polyglot_is_connected() ) {
    $api_key = polyglot_get_api_key();
    $base_url = polyglot_get_api_base_url();
    // Make REST calls against $base_url/v1/translate, etc.
}`

For higher-level conveniences (batching, retry policy, response DTOs), an official `polyglot/wp-sdk` Composer package is planned as a separate library that wraps this connector. Until released, consume the Polyglot Cloud REST API directly using the helpers above.

= Credential precedence =

Highest to lowest:

1. `POLYGLOT_TRANSLATE_API_KEY` environment variable (set in your hosting panel)
2. `POLYGLOT_TRANSLATE_API_KEY` PHP constant (defined in `wp-config.php`)
3. Database option (set via Settings → Connectors)

= Advanced — custom API endpoint =

If you operate a self-hosted Polyglot deployment, a reverse-proxy gateway (Cloudflare Workers, AWS Lambda…), or a staging environment, you can override the API base URL via **Settings → Polyglot Translate**. HTTPS-only.

== Installation ==

1. Install via the Plugins screen, or upload `polyglot-translate-connector` to `/wp-content/plugins/`.
2. Activate the plugin.
3. Go to **Settings → Connectors** and paste your Polyglot Translate API key (get one at [app.polyglot-translate.cloud](https://app.polyglot-translate.cloud)).
4. Done. Any Polyglot-aware plugin on your site can now use the same key.

== Frequently Asked Questions ==

= What is Polyglot Translate? =

Polyglot Translate is a cloud translation service with self-learning translation memory. Unlike generic machine translation APIs, Polyglot remembers good translations across uses, learns your terminology, and progressively replaces machine output with higher-quality verified results. You pay per character, not per call.

= Do I need this plugin if I already use the main Polyglot Translate plugin? =

No. The main plugin handles everything end-to-end. This connector is for two specific audiences: (a) WordPress 7.0+ users who want their Polyglot key to live in the native Connectors registry and be available to multiple plugins, and (b) developers and agencies building custom translation workflows on top of Polyglot Cloud.

= How is this different from the Anthropic, OpenAI, or Google connectors that ship with WordPress 7.0? =

Those connectors expose **AI text generation** providers — they're for plugins that want to generate or rewrite content. This connector exposes a dedicated **translation** provider — purpose-built for high-quality multilingual translation with translation memory. Different tool, different job.

= Will this plugin send translation requests to Polyglot? =

No. It performs exactly one type of network call: a `GET /v1/health` preflight check against Polyglot Cloud whenever you save your API key, to verify the key is valid. Nothing else.

= Is my API key visible to other plugins on the site? =

Yes — by design. This is the same model WordPress core uses for all Connectors (Anthropic, OpenAI, Google, Akismet). Site settings are shared. If you need per-plugin scoping, generate a separate scoped key for each consumer in your Polyglot Dashboard.

= What happens if Polyglot Cloud is temporarily unreachable when I save my key? =

The key is still saved. An admin notice will tell you validation could not complete, and the Site Health check will show "Recommended — unreachable." You can re-test at any time from Site Health.

= Is this affiliated with WordPress.org or Automattic? =

No. WordPress and the WordPress logo are trademarks of the WordPress Foundation. This plugin is developed by Elevio Solutions, the company behind Polyglot Translate.

= Can I use a custom API endpoint? =

Yes. Open **Settings → Polyglot Translate** and enter an HTTPS URL. Useful for self-hosted deployments, reverse proxies, or staging environments.

= Does this plugin send any data to third parties? =

The only outbound call this plugin makes is `GET /v1/health` to Polyglot Cloud (`api.polyglot-translate.cloud`), with your API key in the `Authorization` header — used solely to validate the key on save. No telemetry, no analytics, no other endpoints. See our [privacy policy](https://polyglot-translate.cloud/privacy) for what Polyglot Cloud itself does with translation traffic.

== Screenshots ==

1. Polyglot Translate card in the WordPress 7.0 Settings → Connectors screen, alongside Anthropic, OpenAI, and Google.
2. Connected state — API key entered, validated against Polyglot Cloud, masked in the UI.
3. Site Health debug info section showing connector metadata, key source, and last validation status.
4. Advanced settings — optional custom API endpoint override for self-hosted or reverse-proxy deployments.

== Changelog ==

= 1.0.0 — 2026-05-24 =

* Initial release.
* Registers Polyglot Translate as a `translation`-type connector in the WordPress 7.0 Connectors API.
* Preflight API key validation via `GET /v1/health` (warn-don't-block strategy).
* Public helpers for plugin developers: `polyglot_get_api_key()`, `polyglot_get_api_base_url()`, `polyglot_is_connected()`, `polyglot_get_connector_id()`, `polyglot_get_api_key_source()`.
* Site Health check integration (top-level test + debug info section).
* Secure credential management via Settings → Connectors (with environment variable + PHP constant overrides for enterprise hosting).
* Optional custom API endpoint override via Settings → Polyglot Translate.
* Localization-ready: English source, Serbian (sr_RS) translation included.

== Upgrade Notice ==

= 1.0.0 =

Initial public release.
