<?php
/**
 * PHPUnit bootstrap — lightweight stub of the minimal WP function surface this plugin uses.
 *
 * NOT a full WP test environment. Integration tests against real WordPress live in CI matrix
 * via wp-env (added in a future version). These unit tests stub just enough core surface
 * to exercise the plugin's own logic.
 *
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

define( 'ABSPATH', __DIR__ . '/' );
define( 'WPINC', 'wp-includes' );

if ( ! defined( 'POLYGLOT_CONNECTOR_TEST_MODE' ) ) {
	define( 'POLYGLOT_CONNECTOR_TEST_MODE', true );
}

global $polyglot_test_options, $polyglot_test_filters, $polyglot_test_actions, $polyglot_test_http_responses, $polyglot_test_admin_notices, $polyglot_test_settings_errors;
$polyglot_test_options          = [];
$polyglot_test_filters          = [];
$polyglot_test_actions          = [];
$polyglot_test_http_responses   = [];
$polyglot_test_admin_notices    = [];
$polyglot_test_settings_errors  = [];

function polyglot_test_reset_state(): void {
	global $polyglot_test_options, $polyglot_test_filters, $polyglot_test_actions, $polyglot_test_http_responses, $polyglot_test_admin_notices, $polyglot_test_settings_errors;
	$polyglot_test_options         = [];
	$polyglot_test_filters         = [];
	$polyglot_test_actions         = [];
	$polyglot_test_http_responses  = [];
	$polyglot_test_admin_notices   = [];
	$polyglot_test_settings_errors = [];
}

function get_option( string $name, $default = false ) {
	global $polyglot_test_options;
	return $polyglot_test_options[ $name ] ?? $default;
}

function update_option( string $name, $value, $autoload = null ): bool {
	global $polyglot_test_options;
	$polyglot_test_options[ $name ] = $value;
	return true;
}

function delete_option( string $name ): bool {
	global $polyglot_test_options;
	unset( $polyglot_test_options[ $name ] );
	return true;
}

function add_action( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ) {
	global $polyglot_test_actions;
	$polyglot_test_actions[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ) {
	global $polyglot_test_filters;
	$polyglot_test_filters[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function apply_filters( string $hook, $value, ...$args ) {
	global $polyglot_test_filters;
	if ( empty( $polyglot_test_filters[ $hook ] ) ) {
		return $value;
	}
	foreach ( $polyglot_test_filters[ $hook ] as $entry ) {
		$value = call_user_func( $entry['callback'], $value, ...$args );
	}
	return $value;
}

function do_action( string $hook, ...$args ): void {
	global $polyglot_test_actions;
	if ( empty( $polyglot_test_actions[ $hook ] ) ) {
		return;
	}
	foreach ( $polyglot_test_actions[ $hook ] as $entry ) {
		call_user_func( $entry['callback'], ...$args );
	}
}

function register_setting( $group, $name, $args = [] ): void {
	// no-op for unit tests
}

function add_settings_section( $id, $title, $callback, $page ): void {}
function add_settings_field( $id, $title, $callback, $page, $section ): void {}
function add_submenu_page( ...$args ): string { return 'stub-hook-suffix'; }

function add_settings_error( $setting, $code, $message ): void {
	global $polyglot_test_settings_errors;
	$polyglot_test_settings_errors[] = compact( 'setting', 'code', 'message' );
}

function current_user_can( string $cap ): bool {
	return true;
}

function plugin_basename( string $file ): string {
	return basename( dirname( $file ) ) . '/' . basename( $file );
}

function plugin_dir_path( string $file ): string {
	return trailingslashit( dirname( $file ) );
}

function plugin_dir_url( string $file ): string {
	return 'https://example.test/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
}

function trailingslashit( string $path ): string {
	return rtrim( $path, '/' ) . '/';
}

function load_plugin_textdomain( ...$args ): bool {
	return true;
}

function __( string $text, string $domain = 'default' ): string {
	return $text;
}

function esc_html__( string $text, string $domain = 'default' ): string {
	return $text;
}

function esc_html( string $text ): string {
	return $text;
}

function esc_attr( string $text ): string {
	return $text;
}

function esc_url( string $url ): string {
	return $url;
}

function admin_url( string $path = '' ): string {
	return 'https://example.test/wp-admin/' . ltrim( $path, '/' );
}

function wp_parse_url( string $url ): array {
	$parsed = parse_url( $url );
	return is_array( $parsed ) ? $parsed : [];
}

function is_wp_error( $thing ): bool {
	return $thing instanceof WP_Error_Stub;
}

function wp_remote_get( string $url, array $args = [] ) {
	global $polyglot_test_http_responses;
	$key = 'GET ' . $url;
	if ( ! isset( $polyglot_test_http_responses[ $key ] ) ) {
		return new WP_Error_Stub( 'http_request_failed', 'No mock registered for ' . $key );
	}
	return $polyglot_test_http_responses[ $key ];
}

function wp_remote_retrieve_response_code( $response ): int {
	if ( is_array( $response ) && isset( $response['response']['code'] ) ) {
		return (int) $response['response']['code'];
	}
	return 0;
}

function wp_json_encode( $value, int $flags = 0 ): string {
	return json_encode( $value, $flags );
}

function get_bloginfo( string $what ): string {
	return $what === 'version' ? '7.0' : '';
}

function method_exists_safe( $object, string $method ): bool {
	return is_object( $object ) && method_exists( $object, $method );
}

class WP_Error_Stub {
	public string $code;
	public string $message;
	public function __construct( string $code, string $message = '' ) {
		$this->code    = $code;
		$this->message = $message;
	}
	public function get_error_message(): string {
		return $this->message;
	}
	public function get_error_code(): string {
		return $this->code;
	}
}

// Helper to register an HTTP response for the next call.
function polyglot_test_mock_http( string $method_url, $response ): void {
	global $polyglot_test_http_responses;
	$polyglot_test_http_responses[ $method_url ] = $response;
}

require_once dirname( __DIR__ ) . '/polyglot-translate-connector.php';
