<?php
/**
 * Connector registration with WP 7.0 Connectors API.
 *
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

namespace Polyglot\TranslateConnector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Registrar {

	public static function register( $registry ): void {
		if ( ! is_object( $registry ) || ! method_exists( $registry, 'register' ) ) {
			return;
		}

		$registry->register(
			POLYGLOT_CONNECTOR_ID,
			[
				'name'        => __( 'Polyglot Translate', 'polyglot-translate-connector' ),
				'description' => __(
					'Self-learning translation memory with multi-provider cascade. 90+ languages with native-quality output for Serbian, Croatian, Slovenian, Bulgarian, and other SEE languages.',
					'polyglot-translate-connector'
				),
				'logo_url'    => POLYGLOT_CONNECTOR_URL . 'assets/polyglot-logo.svg',
				'type'        => 'translation',
				'plugin'      => [
					'file'      => plugin_basename( POLYGLOT_CONNECTOR_FILE ),
					'is_active' => static function (): bool {
						return defined( 'POLYGLOT_CONNECTOR_VERSION' );
					},
				],
				'authentication' => [
					'method'          => 'api_key',
					'credentials_url' => 'https://app.polyglot-translate.cloud/dashboard/connections',
					'setting_name'    => POLYGLOT_CONNECTOR_SETTING_NAME,
					'constant_name'   => 'POLYGLOT_TRANSLATE_API_KEY',
					'env_var_name'    => 'POLYGLOT_TRANSLATE_API_KEY',
				],
			]
		);
	}
}
