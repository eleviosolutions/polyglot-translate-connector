<?php
/**
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Polyglot\TranslateConnector\SiteHealthCheck;

final class SiteHealthCheckTest extends TestCase {

	protected function setUp(): void {
		polyglot_test_reset_state();
		putenv( 'POLYGLOT_TRANSLATE_API_KEY' );
	}

	public function test_register_test_adds_polyglot_to_direct_tests(): void {
		$result = SiteHealthCheck::register_test( [ 'direct' => [], 'async' => [] ] );
		$this->assertArrayHasKey( 'polyglot_connector', $result['direct'] );
		$this->assertSame( 'Polyglot Translate connector', $result['direct']['polyglot_connector']['label'] );
	}

	public function test_run_test_recommends_when_no_key_configured(): void {
		$result = SiteHealthCheck::run_test();
		$this->assertSame( 'recommended', $result['status'] );
		$this->assertSame( 'orange', $result['badge']['color'] );
		$this->assertStringContainsString( 'No Polyglot Translate API key', $result['description'] );
	}

	public function test_run_test_critical_when_validation_invalid(): void {
		update_option( POLYGLOT_CONNECTOR_SETTING_NAME, 'bad-key' );
		update_option(
			POLYGLOT_CONNECTOR_VALIDATION_OPTION,
			[ 'status' => 'invalid', 'message' => 'nope', 'tested_at' => time() ]
		);
		$result = SiteHealthCheck::run_test();
		$this->assertSame( 'critical', $result['status'] );
		$this->assertSame( 'red', $result['badge']['color'] );
	}

	public function test_run_test_recommended_when_unreachable(): void {
		update_option( POLYGLOT_CONNECTOR_SETTING_NAME, 'maybe-good-key' );
		update_option(
			POLYGLOT_CONNECTOR_VALIDATION_OPTION,
			[ 'status' => 'unreachable', 'message' => 'timeout', 'tested_at' => time() ]
		);
		$result = SiteHealthCheck::run_test();
		$this->assertSame( 'recommended', $result['status'] );
	}

	public function test_run_test_good_when_validation_valid(): void {
		update_option( POLYGLOT_CONNECTOR_SETTING_NAME, 'good-key' );
		update_option(
			POLYGLOT_CONNECTOR_VALIDATION_OPTION,
			[ 'status' => 'valid', 'message' => 'ok', 'tested_at' => time() ]
		);
		$result = SiteHealthCheck::run_test();
		$this->assertSame( 'good', $result['status'] );
		$this->assertSame( 'blue', $result['badge']['color'] );
	}

	public function test_add_debug_info_includes_all_fields(): void {
		$result = SiteHealthCheck::add_debug_info( [] );
		$this->assertArrayHasKey( 'polyglot-translate-connector', $result );
		$fields = $result['polyglot-translate-connector']['fields'];
		$this->assertArrayHasKey( 'version', $fields );
		$this->assertArrayHasKey( 'api_base_url', $fields );
		$this->assertArrayHasKey( 'key_source', $fields );
		$this->assertArrayHasKey( 'last_status', $fields );
		$this->assertArrayHasKey( 'last_tested_at', $fields );
	}

	public function test_debug_info_reflects_db_key_source(): void {
		update_option( POLYGLOT_CONNECTOR_SETTING_NAME, 'db-key' );
		$result = SiteHealthCheck::add_debug_info( [] );
		$this->assertSame( 'database', $result['polyglot-translate-connector']['fields']['key_source']['value'] );
	}
}
