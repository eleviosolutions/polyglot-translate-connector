<?php
/**
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Polyglot\TranslateConnector\PublicHelpers;

final class PublicHelpersTest extends TestCase {

	protected function setUp(): void {
		polyglot_test_reset_state();
		// Clear environment between tests.
		putenv( 'POLYGLOT_TRANSLATE_API_KEY' );
		putenv( 'POLYGLOT_TRANSLATE_API_BASE' );
	}

	public function test_returns_null_when_no_key_anywhere(): void {
		$this->assertNull( PublicHelpers::polyglot_get_api_key() );
		$this->assertSame( 'none', PublicHelpers::polyglot_get_api_key_source() );
		$this->assertFalse( PublicHelpers::polyglot_is_connected() );
	}

	public function test_reads_from_db_option_when_only_db_set(): void {
		update_option( POLYGLOT_CONNECTOR_SETTING_NAME, 'db-key-12345' );
		$this->assertSame( 'db-key-12345', PublicHelpers::polyglot_get_api_key() );
		$this->assertSame( 'database', PublicHelpers::polyglot_get_api_key_source() );
	}

	public function test_env_var_takes_precedence_over_db_option(): void {
		update_option( POLYGLOT_CONNECTOR_SETTING_NAME, 'db-key-12345' );
		putenv( 'POLYGLOT_TRANSLATE_API_KEY=env-key-99999' );
		$this->assertSame( 'env-key-99999', PublicHelpers::polyglot_get_api_key() );
		$this->assertSame( 'env', PublicHelpers::polyglot_get_api_key_source() );
	}

	public function test_default_api_base_url_when_no_override(): void {
		$this->assertSame(
			'https://api.polyglot-translate.cloud',
			PublicHelpers::polyglot_get_api_base_url()
		);
	}

	public function test_custom_api_base_url_via_db_option(): void {
		update_option( 'polyglot_connector_api_base_url', 'https://staging.example.com/api/' );
		$this->assertSame( 'https://staging.example.com/api', PublicHelpers::polyglot_get_api_base_url() );
	}

	public function test_env_var_takes_precedence_over_db_for_base_url(): void {
		update_option( 'polyglot_connector_api_base_url', 'https://db-override.example.com' );
		putenv( 'POLYGLOT_TRANSLATE_API_BASE=https://env-override.example.com' );
		$this->assertSame( 'https://env-override.example.com', PublicHelpers::polyglot_get_api_base_url() );
	}

	public function test_non_https_base_url_rejected_falls_back_to_default(): void {
		update_option( 'polyglot_connector_api_base_url', 'http://insecure.example.com' );
		$this->assertSame(
			'https://api.polyglot-translate.cloud',
			PublicHelpers::polyglot_get_api_base_url()
		);
	}

	public function test_is_valid_https_url_strict(): void {
		$this->assertTrue( PublicHelpers::is_valid_https_url( 'https://api.polyglot-translate.cloud' ) );
		$this->assertTrue( PublicHelpers::is_valid_https_url( 'https://staging.example.com/api/v1' ) );
		$this->assertFalse( PublicHelpers::is_valid_https_url( 'http://insecure.example.com' ) );
		$this->assertFalse( PublicHelpers::is_valid_https_url( 'ftp://files.example.com' ) );
		$this->assertFalse( PublicHelpers::is_valid_https_url( '/relative/path' ) );
		$this->assertFalse( PublicHelpers::is_valid_https_url( 'not-a-url' ) );
		$this->assertFalse( PublicHelpers::is_valid_https_url( '' ) );
	}

	public function test_global_function_wrappers_exist(): void {
		$this->assertTrue( function_exists( 'polyglot_get_api_key' ) );
		$this->assertTrue( function_exists( 'polyglot_get_api_base_url' ) );
		$this->assertTrue( function_exists( 'polyglot_is_connected' ) );
		$this->assertTrue( function_exists( 'polyglot_get_connector_id' ) );
		$this->assertTrue( function_exists( 'polyglot_get_api_key_source' ) );
	}

	public function test_connector_id_is_stable_constant(): void {
		$this->assertSame( 'polyglot', PublicHelpers::polyglot_get_connector_id() );
		$this->assertSame( 'polyglot', polyglot_get_connector_id() );
	}
}
