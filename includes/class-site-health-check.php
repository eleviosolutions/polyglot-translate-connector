<?php
/**
 * Site Health check — surfaces Polyglot connector state in /wp-admin/site-health.php.
 *
 * Provides both a top-level test card and a debug-info section (right column).
 *
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

namespace Polyglot\TranslateConnector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SiteHealthCheck {

	public static function register_test( array $tests ): array {
		$tests['direct']['polyglot_connector'] = [
			'label' => __( 'Polyglot Translate connector', 'polyglot-translate-connector' ),
			'test'  => [ self::class, 'run_test' ],
		];
		return $tests;
	}

	public static function run_test(): array {
		$key        = PublicHelpers::polyglot_get_api_key();
		$validation = Validator::get_last_validation();
		$source     = PublicHelpers::polyglot_get_api_key_source();

		$result = [
			'label'       => __( 'Polyglot Translate connector', 'polyglot-translate-connector' ),
			'status'      => 'good',
			'badge'       => [
				'label' => __( 'Connectors', 'polyglot-translate-connector' ),
				'color' => 'blue',
			],
			'description' => '',
			'actions'     => sprintf(
				'<p><a href="%s">%s</a></p>',
				esc_url( admin_url( 'options-connectors.php' ) ),
				esc_html__( 'Open Connectors settings', 'polyglot-translate-connector' )
			),
			'test'        => 'polyglot_connector',
		];

		if ( null === $key ) {
			$result['status']      = 'recommended';
			$result['badge']['color'] = 'orange';
			$result['description']  = '<p>' . esc_html__(
				'No Polyglot Translate API key configured. Plugins that depend on this connector will not function until a key is added in Settings → Connectors, or via the POLYGLOT_TRANSLATE_API_KEY environment variable or PHP constant.',
				'polyglot-translate-connector'
			) . '</p>';
			return $result;
		}

		if ( 'invalid' === $validation['status'] ) {
			$result['status']      = 'critical';
			$result['badge']['color'] = 'red';
			$result['description']  = '<p>' . esc_html(
				sprintf(
					/* translators: %s: key source label (env, constant, database) */
					__( 'The configured Polyglot API key (source: %s) was rejected by the cloud. Translation calls will fail until a valid key is configured.', 'polyglot-translate-connector' ),
					$source
				)
			) . '</p>';
			return $result;
		}

		if ( 'unreachable' === $validation['status'] ) {
			$result['status']      = 'recommended';
			$result['badge']['color'] = 'orange';
			$result['description']  = '<p>' . esc_html__(
				'Polyglot Cloud could not be reached during the last validation. The key is saved but its validity is unknown.',
				'polyglot-translate-connector'
			) . '</p>';
			return $result;
		}

		$result['description'] = '<p>' . esc_html(
			sprintf(
				/* translators: %s: key source label (env, constant, database) */
				__( 'Polyglot Translate connector is configured and reachable (key source: %s).', 'polyglot-translate-connector' ),
				$source
			)
		) . '</p>';

		return $result;
	}

	public static function add_debug_info( array $info ): array {
		$validation = Validator::get_last_validation();
		$source     = PublicHelpers::polyglot_get_api_key_source();
		$base       = PublicHelpers::polyglot_get_api_base_url();

		$info['polyglot-translate-connector'] = [
			'label'       => __( 'Polyglot Translate Connector', 'polyglot-translate-connector' ),
			'description' => __( 'Connector metadata for the Polyglot Translate translation provider.', 'polyglot-translate-connector' ),
			'fields'      => [
				'version'        => [
					'label' => __( 'Plugin version', 'polyglot-translate-connector' ),
					'value' => POLYGLOT_CONNECTOR_VERSION,
				],
				'api_base_url'   => [
					'label'   => __( 'API base URL', 'polyglot-translate-connector' ),
					'value'   => $base,
					'private' => false,
				],
				'key_source'     => [
					'label' => __( 'API key source', 'polyglot-translate-connector' ),
					'value' => $source,
				],
				'last_status'    => [
					'label' => __( 'Last validation status', 'polyglot-translate-connector' ),
					'value' => $validation['status'] ?? 'unknown',
				],
				'last_tested_at' => [
					'label' => __( 'Last validation timestamp', 'polyglot-translate-connector' ),
					'value' => isset( $validation['tested_at'] ) && (int) $validation['tested_at'] > 0
						? gmdate( 'Y-m-d H:i:s', (int) $validation['tested_at'] ) . ' UTC'
						: __( 'Never', 'polyglot-translate-connector' ),
				],
			],
		];
		return $info;
	}
}
