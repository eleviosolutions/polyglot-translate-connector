<?php
/**
 * API key validator — preflight check against Polyglot Cloud /v1/health.
 *
 * Warn-not-block strategy: invalid keys are still saved (so user can fix later),
 * but admin notice surfaces the failure. Last-validation result is cached for
 * 5 minutes to avoid N+1 HTTP calls on repeated saves.
 *
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

namespace Polyglot\TranslateConnector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Validator {

	private const HEALTH_PATH      = '/v1/health';
	private const TIMEOUT_SECONDS  = 4;
	private const CACHE_TTL_SECONDS = 300;

	public static function register_hooks(): void {
		add_filter( 'pre_update_option_' . POLYGLOT_CONNECTOR_SETTING_NAME, [ self::class, 'on_key_update' ], 10, 2 );
		add_action( 'admin_notices', [ self::class, 'maybe_render_admin_notice' ] );
	}

	/**
	 * Hook into option save. Triggers preflight + records result. Never blocks save.
	 *
	 * @param mixed $new_value Incoming option value.
	 * @param mixed $old_value Existing option value.
	 * @return mixed The (possibly trimmed) new value.
	 */
	public static function on_key_update( $new_value, $old_value ) {
		if ( ! is_string( $new_value ) ) {
			return $new_value;
		}
		$new_value = trim( $new_value );

		if ( '' === $new_value || $new_value === $old_value ) {
			return $new_value;
		}

		$result = self::validate_key( $new_value );
		update_option(
			POLYGLOT_CONNECTOR_VALIDATION_OPTION,
			[
				'status'    => $result['status'],
				'message'   => $result['message'],
				'tested_at' => time(),
			],
			false
		);

		return $new_value;
	}

	/**
	 * Performs a live HTTP preflight against /v1/health with the given key.
	 *
	 * @return array{status:string,message:string}
	 */
	public static function validate_key( string $api_key ): array {
		if ( '' === $api_key ) {
			return [
				'status'  => 'empty',
				'message' => __( 'No API key configured.', 'polyglot-translate-connector' ),
			];
		}

		$base   = PublicHelpers::polyglot_get_api_base_url();
		$url    = rtrim( $base, '/' ) . self::HEALTH_PATH;
		$args   = [
			'timeout' => self::TIMEOUT_SECONDS,
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'User-Agent'    => sprintf(
					'PolyglotConnectorWP/%s; wp=%s; php=%s',
					POLYGLOT_CONNECTOR_VERSION,
					get_bloginfo( 'version' ),
					PHP_VERSION
				),
			],
		];

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return [
				'status'  => 'unreachable',
				'message' => sprintf(
					/* translators: %s: error description from wp_remote_get */
					__( 'Could not reach Polyglot Cloud: %s. Key was saved — you can re-test from Site Health.', 'polyglot-translate-connector' ),
					$response->get_error_message()
				),
			];
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( 200 === $code ) {
			return [
				'status'  => 'valid',
				'message' => __( 'API key validated successfully.', 'polyglot-translate-connector' ),
			];
		}

		if ( 401 === $code || 403 === $code ) {
			return [
				'status'  => 'invalid',
				'message' => __( 'Polyglot Cloud rejected this API key (401/403). Please double-check it on app.polyglot-translate.cloud.', 'polyglot-translate-connector' ),
			];
		}

		return [
			'status'  => 'unknown',
			'message' => sprintf(
				/* translators: %d: HTTP status code returned by Polyglot Cloud /v1/health */
				__( 'Unexpected response from Polyglot Cloud (HTTP %d). Key was saved — try again later.', 'polyglot-translate-connector' ),
				$code
			),
		];
	}

	public static function get_last_validation(): array {
		$stored = get_option( POLYGLOT_CONNECTOR_VALIDATION_OPTION, [] );
		if ( ! is_array( $stored ) || empty( $stored['status'] ) ) {
			return [
				'status'    => 'unknown',
				'message'   => __( 'No validation has been performed yet.', 'polyglot-translate-connector' ),
				'tested_at' => 0,
			];
		}
		return $stored;
	}

	public static function maybe_render_admin_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$validation = self::get_last_validation();
		if ( ! in_array( $validation['status'], [ 'invalid', 'unreachable', 'unknown' ], true ) ) {
			return;
		}

		// Only surface the notice if the test was recent (last 10 minutes).
		if ( ( time() - (int) ( $validation['tested_at'] ?? 0 ) ) > 600 ) {
			return;
		}

		$class = 'invalid' === $validation['status'] ? 'notice-error' : 'notice-warning';
		printf(
			'<div class="notice %s is-dismissible"><p><strong>%s:</strong> %s</p></div>',
			esc_attr( $class ),
			esc_html__( 'Polyglot Translate Connector', 'polyglot-translate-connector' ),
			esc_html( $validation['message'] )
		);
	}
}
