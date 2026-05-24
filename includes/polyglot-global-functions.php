<?php
/**
 * Global helper functions — stable public API for other plugins to consume the connector.
 *
 * These intentionally live in the GLOBAL namespace (not namespaced) so plugin developers
 * can call them with their natural names. The implementations delegate to PublicHelpers.
 *
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'polyglot_get_api_key' ) ) {
	/**
	 * @return string|null API key from env/constant/DB option, or null if not configured.
	 */
	function polyglot_get_api_key(): ?string {
		return \Polyglot\TranslateConnector\PublicHelpers::polyglot_get_api_key();
	}
}

if ( ! function_exists( 'polyglot_get_api_base_url' ) ) {
	function polyglot_get_api_base_url(): string {
		return \Polyglot\TranslateConnector\PublicHelpers::polyglot_get_api_base_url();
	}
}

if ( ! function_exists( 'polyglot_is_connected' ) ) {
	function polyglot_is_connected(): bool {
		return \Polyglot\TranslateConnector\PublicHelpers::polyglot_is_connected();
	}
}

if ( ! function_exists( 'polyglot_get_connector_id' ) ) {
	function polyglot_get_connector_id(): string {
		return \Polyglot\TranslateConnector\PublicHelpers::polyglot_get_connector_id();
	}
}

if ( ! function_exists( 'polyglot_get_api_key_source' ) ) {
	function polyglot_get_api_key_source(): string {
		return \Polyglot\TranslateConnector\PublicHelpers::polyglot_get_api_key_source();
	}
}
