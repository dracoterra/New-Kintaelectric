<?php

namespace kintaelectric\Modules\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use kintaelectric\Includes\Module_Base;
// Removed premium settings controller

/**
 * Theme module
 *
 * @package kintaelectric
 * @subpackage kintaelectricModules
 */
class Module extends Module_Base {
	const kintaelectric_THEME_VERSION_OPTION = 'kintaelectric_theme_version';

	/**
	 * @inheritDoc
	 */
	public static function get_name(): string {
		return 'theme';
	}

	/**
	 * @inheritDoc
	 */
	protected function get_component_ids(): array {
		return [
			'Customizer',
			'Theme_Support',
			'Elementor_Section_Opener',
		];
	}

	/**
	 * Check whether to display the theme's default header & footer.
	 *
	 * @return bool
	 */
	public static function display_header_footer(): bool {
		return true; // Always show header and footer for Kinta Electric
	}

	public function display_header_footer_filter( bool $display ): bool {
		$show = self::display_header_footer();
		return $show ? $display : false;
	}

	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	public function scripts_styles() {
		if ( false ) { // Always load theme CSS for Kinta Electric
			return;
		}

		wp_enqueue_style(
			'kintaelectric',
			kintaelectric_STYLE_URL . 'theme.css',
			[],
			kintaelectric_ELEMENTOR_VERSION
		);

		if ( self::display_header_footer() ) {
			wp_enqueue_style(
				'kintaelectric-header-footer',
				kintaelectric_STYLE_URL . 'header-footer.css',
				[],
				kintaelectric_ELEMENTOR_VERSION
			);
		}
	}

	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	public function content_width() {
		$GLOBALS['content_width'] = apply_filters( 'kintaelectric-theme/content_width', 800 );
	}

	/**
	 * @inheritDoc
	 */
	protected function register_hooks(): void {
		parent::register_hooks();
		add_action( 'after_setup_theme', [ $this, 'content_width' ], 0 );
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts_styles' ] );
		add_filter( 'kintaelectric-theme/display-default-footer', [ $this, 'display_header_footer_filter' ] );
		add_filter( 'kintaelectric-theme/display-default-header', [ $this, 'display_header_footer_filter' ] );
	}
}
