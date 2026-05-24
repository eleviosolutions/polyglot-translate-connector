<?php
/**
 * @package PolyglotTranslateConnector
 */

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Polyglot\TranslateConnector\Registrar;

final class RegistrarTest extends TestCase {

	protected function setUp(): void {
		polyglot_test_reset_state();
	}

	public function test_registers_with_correct_id_and_shape(): void {
		$registry = new class {
			public array $registered = [];
			public function register( string $id, array $args ): void {
				$this->registered[ $id ] = $args;
			}
		};

		Registrar::register( $registry );

		$this->assertArrayHasKey( 'polyglot', $registry->registered, 'Connector ID must be "polyglot"' );

		$args = $registry->registered['polyglot'];
		$this->assertSame( 'Polyglot Translate', $args['name'] );
		$this->assertSame( 'translation', $args['type'] );
		$this->assertNotEmpty( $args['description'] );
		$this->assertStringContainsString( '.svg', $args['logo_url'], 'Logo should be SVG' );
	}

	public function test_authentication_shape_is_api_key_with_three_sources(): void {
		$registry = new class {
			public array $registered = [];
			public function register( string $id, array $args ): void {
				$this->registered[ $id ] = $args;
			}
		};

		Registrar::register( $registry );
		$auth = $registry->registered['polyglot']['authentication'];

		$this->assertSame( 'api_key', $auth['method'] );
		$this->assertSame( 'connectors_translation_polyglot_api_key', $auth['setting_name'] );
		$this->assertSame( 'POLYGLOT_TRANSLATE_API_KEY', $auth['constant_name'] );
		$this->assertSame( 'POLYGLOT_TRANSLATE_API_KEY', $auth['env_var_name'] );
		$this->assertSame(
			'https://app.polyglot-translate.cloud/dashboard/connections',
			$auth['credentials_url']
		);
	}

	public function test_is_active_callback_returns_true_when_constant_defined(): void {
		$registry = new class {
			public array $registered = [];
			public function register( string $id, array $args ): void {
				$this->registered[ $id ] = $args;
			}
		};

		Registrar::register( $registry );
		$is_active = $registry->registered['polyglot']['plugin']['is_active'];

		$this->assertIsCallable( $is_active );
		$this->assertTrue( $is_active(), 'Should be true because POLYGLOT_CONNECTOR_VERSION is defined' );
	}

	public function test_silently_skips_when_registry_missing_register_method(): void {
		$bad_registry = new stdClass();
		// Should not throw.
		Registrar::register( $bad_registry );
		$this->addToAssertionCount( 1 );
	}

	public function test_silently_skips_when_registry_is_not_object(): void {
		Registrar::register( null );
		Registrar::register( 'string' );
		Registrar::register( [] );
		$this->addToAssertionCount( 3 );
	}
}
