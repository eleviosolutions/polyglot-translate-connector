<?php
/**
 * Uninstall cleanup — removes plugin-owned options when user deletes the plugin.
 *
 * The connector API key option (connectors_translation_polyglot_api_key) is INTENTIONALLY
 * NOT removed here — it is owned by WP core's Connectors API and may still be needed by
 * the AI Client registry or by other Polyglot-aware plugins. Core handles its lifecycle.
 *
 * @package PolyglotTranslateConnector
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'polyglot_connector_api_base_url' );
delete_option( 'polyglot_connector_last_validation' );
