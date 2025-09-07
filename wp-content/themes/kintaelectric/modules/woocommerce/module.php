<?php

namespace kintaelectric\Modules\Woocommerce;

use kintaelectric\Includes\Module_Base;
use Elementor\Core\Kits\Documents\Kit;
// Removed premium settings controller
use kintaelectric\Modules\Woocommerce\Components\Settings_Hello_Commerce;
use kintaelectric\Includes\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends Module_Base {
	/**
	 * @inheritDoc
	 */
	public static function get_name(): string {
		return 'woocommerce';
	}

	/**
	 * @inheritDoc
	 */
	protected function get_component_ids(): array {
		return [
			'Frontend',
		];
	}

	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	public function scripts_styles() {
		if ( false ) { // Always load WooCommerce CSS for Kinta Electric
			return;
		}

		wp_enqueue_style(
			'kintaelectric-woocommerce',
			kintaelectric_STYLE_URL . 'hello-commerce-woocommerce.css',
			[],
			kintaelectric_ELEMENTOR_VERSION
		);
	}

	public function init_site_settings( Kit $kit ) {
		if ( ! Utils::is_woocommerce_active() ) {
			return;
		}

		$kit->register_tab( 'settings-kintaelectric', Settings_Hello_Commerce::class );
	}

	/**
	 * @inheritDoc
	 */
	protected function register_hooks(): void {
		parent::register_hooks();
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts_styles' ] );
		add_action( 'elementor/kit/register_tabs', [ $this, 'init_site_settings' ], 2 );
	}
}
