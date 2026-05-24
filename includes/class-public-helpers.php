<?php
/**
 * Public helpers — global functions other plugins use to discover and consume the Polyglot connector.
 *
 * Stable API surface. Functions defined here are the contract for downstream plugin developers
 * and the polyglot/wp-sdk Composer package.
 *
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

namespace Polyglot\TranslateConnector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PublicHelpers {

	/**
	 * Reads API key honoring WP 7.0 Connectors precedence: env > constant > DB option.
	 *
	 * Mirrors core's _wp_connectors_get_api_key_source() but returns the actual value,
	 * not just the source label.
	 */
	public static function polyglot_get_api_key(): ?string {
		$env_value = getenv( 'POLYGLOT_TRANSLATE_API_KEY' );
		if ( false !== $env_value && '' !== $env_value ) {
			return (string) $env_value;
		}

		if ( defined( 'POLYGLOT_TRANSLATE_API_KEY' ) ) {
			$const_value = constant( 'POLYGLOT_TRANSLATE_API_KEY' );
			if ( is_string( $const_value ) && '' !== $const_value ) {
				return $const_value;
			}
		}

		$db_value = get_option( POLYGLOT_CONNECTOR_SETTING_NAME, '' );
		if ( is_string( $db_value ) && '' !== $db_value ) {
			return $db_value;
		}

		return null;
	}

	/**
	 * Reads the configured API base URL (BYOK endpoint override) or default.
	 */
	public static function polyglot_get_api_base_url(): string {
		$env_value = getenv( 'POLYGLOT_TRANSLATE_API_BASE' );
		if ( false !== $env_value && self::is_valid_https_url( $env_value ) ) {
			return rtrim( $env_value, '/' );
		}

		if ( defined( 'POLYGLOT_TRANSLATE_API_BASE' ) ) {
			$const_value = constant( 'POLYGLOT_TRANSLATE_API_BASE' );
			if ( is_string( $const_value ) && self::is_valid_https_url( $const_value ) ) {
				return rtrim( $const_value, '/' );
			}
		}

		$db_value = get_option( POLYGLOT_CONNECTOR_ENDPOINT_OPTION, '' );
		if ( is_string( $db_value ) && self::is_valid_https_url( $db_value ) ) {
			return rtrim( $db_value, '/' );
		}

		return POLYGLOT_CONNECTOR_DEFAULT_API_BASE;
	}

	public static function polyglot_is_connected(): bool {
		$key = self::polyglot_get_api_key();
		if ( null === $key ) {
			return false;
		}
		$validation = Validator::get_last_validation();
		return in_array( $validation['status'], [ 'valid', 'unknown', 'unreachable' ], true );
	}

	public static function polyglot_get_connector_id(): string {
		return POLYGLOT_CONNECTOR_ID;
	}

	public static function polyglot_get_api_key_source(): string {
		$env_value = getenv( 'POLYGLOT_TRANSLATE_API_KEY' );
		if ( false !== $env_value && '' !== $env_value ) {
			return 'env';
		}
		if ( defined( 'POLYGLOT_TRANSLATE_API_KEY' ) ) {
			$const_value = constant( 'POLYGLOT_TRANSLATE_API_KEY' );
			if ( is_string( $const_value ) && '' !== $const_value ) {
				return 'constant';
			}
		}
		$db_value = get_option( POLYGLOT_CONNECTOR_SETTING_NAME, '' );
		if ( is_string( $db_value ) && '' !== $db_value ) {
			return 'database';
		}
		return 'none';
	}

	public static function is_valid_https_url( string $url ): bool {
		$parsed = wp_parse_url( $url );
		if ( ! is_array( $parsed ) || empty( $parsed['scheme'] ) || empty( $parsed['host'] ) ) {
			return false;
		}
		return 'https' === strtolower( $parsed['scheme'] );
	}
}
