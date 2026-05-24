<?php
/**
 * Custom endpoint URL override — advanced setting page for self-hosted / proxy / staging API base URLs.
 *
 * Default base URL is https://api.polyglot-translate.cloud (defined via constant).
 * Enterprise / self-hosted / reverse-proxy deployments can override here.
 *
 * Visible at: Settings → Polyglot Translate (Advanced).
 *
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

namespace Polyglot\TranslateConnector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsExtender {

	private const PAGE_SLUG  = 'polyglot-translate-connector-advanced';
	private const OPTION_GROUP = 'polyglot_translate_connector_advanced';

	public static function register_setting(): void {
		register_setting(
			self::OPTION_GROUP,
			POLYGLOT_CONNECTOR_ENDPOINT_OPTION,
			[
				'type'              => 'string',
				'description'       => __( 'Custom Polyglot Cloud API base URL (advanced).', 'polyglot-translate-connector' ),
				'default'           => '',
				'sanitize_callback' => [ self::class, 'sanitize_endpoint' ],
				'show_in_rest'      => false,
			]
		);

		add_settings_section(
			'polyglot_advanced_section',
			__( 'Advanced — API endpoint override', 'polyglot-translate-connector' ),
			[ self::class, 'render_section_description' ],
			self::PAGE_SLUG
		);

		add_settings_field(
			POLYGLOT_CONNECTOR_ENDPOINT_OPTION,
			__( 'Custom API base URL', 'polyglot-translate-connector' ),
			[ self::class, 'render_endpoint_field' ],
			self::PAGE_SLUG,
			'polyglot_advanced_section'
		);
	}

	public static function register_advanced_page(): void {
		add_submenu_page(
			'options-general.php',
			__( 'Polyglot Translate — Advanced', 'polyglot-translate-connector' ),
			__( 'Polyglot Translate', 'polyglot-translate-connector' ),
			'manage_options',
			self::PAGE_SLUG,
			[ self::class, 'render_page' ]
		);
	}

	public static function sanitize_endpoint( $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}
		if ( ! PublicHelpers::is_valid_https_url( $value ) ) {
			add_settings_error(
				POLYGLOT_CONNECTOR_ENDPOINT_OPTION,
				'invalid_endpoint',
				__( 'Custom API base URL must be a valid HTTPS URL. Reverting to default.', 'polyglot-translate-connector' )
			);
			return '';
		}
		return rtrim( $value, '/' );
	}

	public static function render_section_description(): void {
		echo '<p>';
		esc_html_e(
			'Most users should leave this empty — the plugin will use the default Polyglot Cloud endpoint. Override only if you operate a self-hosted Polyglot deployment, a reverse-proxy gateway, or use a staging environment.',
			'polyglot-translate-connector'
		);
		echo '</p>';
	}

	public static function render_endpoint_field(): void {
		$value   = (string) get_option( POLYGLOT_CONNECTOR_ENDPOINT_OPTION, '' );
		$default = POLYGLOT_CONNECTOR_DEFAULT_API_BASE;
		printf(
			'<input type="url" name="%s" id="%s" value="%s" class="regular-text" placeholder="%s" />',
			esc_attr( POLYGLOT_CONNECTOR_ENDPOINT_OPTION ),
			esc_attr( POLYGLOT_CONNECTOR_ENDPOINT_OPTION ),
			esc_attr( $value ),
			esc_attr( $default )
		);
		echo '<p class="description">';
		printf(
			/* translators: %s: default API base URL */
			esc_html__( 'Default: %s. HTTPS only. Environment variable POLYGLOT_TRANSLATE_API_BASE and PHP constant POLYGLOT_TRANSLATE_API_BASE take precedence over this setting.', 'polyglot-translate-connector' ),
			'<code>' . esc_html( $default ) . '</code>'
		);
		echo '</p>';
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Polyglot Translate — Advanced', 'polyglot-translate-connector' ); ?></h1>
			<p>
				<?php
				printf(
					/* translators: %s: link to Settings → Connectors page */
					esc_html__( 'To configure your API key, go to %s.', 'polyglot-translate-connector' ),
					'<a href="' . esc_url( admin_url( 'options-connectors.php' ) ) . '">' . esc_html__( 'Settings → Connectors', 'polyglot-translate-connector' ) . '</a>'
				);
				?>
			</p>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
