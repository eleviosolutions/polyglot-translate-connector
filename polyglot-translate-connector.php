<?php
/**
 * Plugin Name:       Polyglot Translate Connector
 * Plugin URI:        https://polyglot-translate.cloud/wordpress-connector
 * Description:       Polyglot Translate as a native WordPress 7.0 Connector. Self-learning translation API for 90+ languages, BYOK credentials, ready for any plugin.
 * Version:           1.0.0
 * Requires at least: 7.0
 * Requires PHP:      7.4
 * Author:            Elevio Solutions
 * Author URI:        https://polyglot-translate.cloud
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       polyglot-translate-connector
 * Domain Path:       /languages
 *
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

namespace Polyglot\TranslateConnector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'POLYGLOT_CONNECTOR_VERSION', '1.0.0' );
define( 'POLYGLOT_CONNECTOR_FILE', __FILE__ );
define( 'POLYGLOT_CONNECTOR_DIR', plugin_dir_path( __FILE__ ) );
define( 'POLYGLOT_CONNECTOR_URL', plugin_dir_url( __FILE__ ) );
define( 'POLYGLOT_CONNECTOR_DEFAULT_API_BASE', 'https://api.polyglot-translate.cloud' );
define( 'POLYGLOT_CONNECTOR_ID', 'polyglot' );
define( 'POLYGLOT_CONNECTOR_SETTING_NAME', 'connectors_translation_polyglot_api_key' );
define( 'POLYGLOT_CONNECTOR_ENDPOINT_OPTION', 'polyglot_connector_api_base_url' );
define( 'POLYGLOT_CONNECTOR_VALIDATION_OPTION', 'polyglot_connector_last_validation' );

spl_autoload_register( static function ( string $class ): void {
	if ( strpos( $class, __NAMESPACE__ . '\\' ) !== 0 ) {
		return;
	}
	$relative   = substr( $class, strlen( __NAMESPACE__ . '\\' ) );
	$kebab      = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $relative ) );
	$file_path  = POLYGLOT_CONNECTOR_DIR . 'includes/class-' . $kebab . '.php';
	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
} );

// Note: load_plugin_textdomain() intentionally NOT called.
// Since WordPress 4.6, translations hosted on WordPress.org are loaded automatically.
// For manual installs, place .mo files in /wp-content/languages/plugins/.

add_action( 'wp_connectors_init', [ Registrar::class, 'register' ] );

add_action( 'init', [ Validator::class, 'register_hooks' ], 20 );

add_filter( 'site_status_tests', [ SiteHealthCheck::class, 'register_test' ] );
add_filter( 'debug_information', [ SiteHealthCheck::class, 'add_debug_info' ] );

add_action( 'admin_init', [ SettingsExtender::class, 'register_setting' ] );
add_action( 'admin_menu', [ SettingsExtender::class, 'register_advanced_page' ] );

require_once POLYGLOT_CONNECTOR_DIR . 'includes/polyglot-global-functions.php';
