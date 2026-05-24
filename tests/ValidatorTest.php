<?php
/**
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Polyglot\TranslateConnector\Validator;

final class ValidatorTest extends TestCase {

	protected function setUp(): void {
		polyglot_test_reset_state();
		putenv( 'POLYGLOT_TRANSLATE_API_KEY' );
		putenv( 'POLYGLOT_TRANSLATE_API_BASE' );
	}

	public function test_empty_key_returns_empty_status(): void {
		$result = Validator::validate_key( '' );
		$this->assertSame( 'empty', $result['status'] );
	}

	public function test_200_response_returns_valid_status(): void {
		polyglot_test_mock_http(
			'GET https://api.polyglot-translate.cloud/v1/health',
			[ 'response' => [ 'code' => 200 ], 'body' => '{"status":"healthy"}' ]
		);
		$result = Validator::validate_key( 'live-key-12345' );
		$this->assertSame( 'valid', $result['status'] );
	}

	public function test_401_response_returns_invalid_status(): void {
		polyglot_test_mock_http(
			'GET https://api.polyglot-translate.cloud/v1/health',
			[ 'response' => [ 'code' => 401 ], 'body' => '{"error":"unauthorized"}' ]
		);
		$result = Validator::validate_key( 'bogus-key' );
		$this->assertSame( 'invalid', $result['status'] );
	}

	public function test_403_response_returns_invalid_status(): void {
		polyglot_test_mock_http(
			'GET https://api.polyglot-translate.cloud/v1/health',
			[ 'response' => [ 'code' => 403 ], 'body' => '{"error":"forbidden"}' ]
		);
		$result = Validator::validate_key( 'suspended-key' );
		$this->assertSame( 'invalid', $result['status'] );
	}

	public function test_500_response_returns_unknown_status(): void {
		polyglot_test_mock_http(
			'GET https://api.polyglot-translate.cloud/v1/health',
			[ 'response' => [ 'code' => 503 ], 'body' => 'Service Unavailable' ]
		);
		$result = Validator::validate_key( 'maybe-good' );
		$this->assertSame( 'unknown', $result['status'] );
	}

	public function test_wp_error_returns_unreachable_status(): void {
		polyglot_test_mock_http(
			'GET https://api.polyglot-translate.cloud/v1/health',
			new WP_Error_Stub( 'http_request_failed', 'Could not resolve host' )
		);
		$result = Validator::validate_key( 'cannot-test' );
		$this->assertSame( 'unreachable', $result['status'] );
		$this->assertStringContainsString( 'Could not resolve host', $result['message'] );
	}

	public function test_on_key_update_records_validation_result(): void {
		polyglot_test_mock_http(
			'GET https://api.polyglot-translate.cloud/v1/health',
			[ 'response' => [ 'code' => 200 ], 'body' => 'ok' ]
		);
		$returned = Validator::on_key_update( 'new-key', '' );
		$this->assertSame( 'new-key', $returned );

		$stored = get_option( POLYGLOT_CONNECTOR_VALIDATION_OPTION );
		$this->assertIsArray( $stored );
		$this->assertSame( 'valid', $stored['status'] );
		$this->assertGreaterThan( 0, $stored['tested_at'] );
	}

	public function test_on_key_update_never_blocks_save_on_invalid(): void {
		polyglot_test_mock_http(
			'GET https://api.polyglot-translate.cloud/v1/health',
			[ 'response' => [ 'code' => 401 ], 'body' => 'no' ]
		);
		$returned = Validator::on_key_update( 'bad-key', '' );
		$this->assertSame( 'bad-key', $returned, 'Bad key must still be returned (warn-not-block strategy)' );

		$stored = get_option( POLYGLOT_CONNECTOR_VALIDATION_OPTION );
		$this->assertSame( 'invalid', $stored['status'] );
	}

	public function test_on_key_update_skips_validation_when_unchanged(): void {
		// No mock registered — would fail with WP_Error if validation ran.
		$returned = Validator::on_key_update( 'same-key', 'same-key' );
		$this->assertSame( 'same-key', $returned );
		$this->assertNull( get_option( POLYGLOT_CONNECTOR_VALIDATION_OPTION, null ) );
	}

	public function test_on_key_update_trims_whitespace(): void {
		polyglot_test_mock_http(
			'GET https://api.polyglot-translate.cloud/v1/health',
			[ 'response' => [ 'code' => 200 ], 'body' => 'ok' ]
		);
		$returned = Validator::on_key_update( '   padded-key   ', '' );
		$this->assertSame( 'padded-key', $returned );
	}

	public function test_get_last_validation_returns_default_when_none_stored(): void {
		$default = Validator::get_last_validation();
		$this->assertSame( 'unknown', $default['status'] );
		$this->assertSame( 0, $default['tested_at'] );
	}
}
