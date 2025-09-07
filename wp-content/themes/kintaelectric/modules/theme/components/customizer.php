<?php
namespace kintaelectric\Modules\Theme\Components;

use kintaelectric\Includes\Utils;
use kintaelectric\Modules\Theme\Classes\Customizer_Action_Links;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Customizer {
	const CUSTOMIZER_SECTION_NAME = 'kintaelectric-options';

	public function register( $wp_customize ): void {
		if ( ! apply_filters( 'kintaelectric-theme/customizer/enable', true ) ) {
			return;
		}

		// Sección principal de Kinta Electric
		$wp_customize->add_section(
			self::CUSTOMIZER_SECTION_NAME,
			[
				'title' => esc_html__( 'Configuración Kinta Electric', 'kintaelectric' ),
				'description' => esc_html__( 'Personaliza tu sitio web de Kinta Electric Venezuela', 'kintaelectric' ),
				'capability' => 'edit_theme_options',
				'priority' => 30,
			]
		);

		// Configuración de colores corporativos
		$wp_customize->add_setting(
			'kintaelectric_primary_color',
			[
				'default' => '#1e40af',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport' => 'postMessage',
			]
		);

		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				$wp_customize,
				'kintaelectric_primary_color',
				[
					'label' => esc_html__( 'Color Primario', 'kintaelectric' ),
					'description' => esc_html__( 'Color azul corporativo de Kinta Electric', 'kintaelectric' ),
					'section' => self::CUSTOMIZER_SECTION_NAME,
					'priority' => 10,
				]
			)
		);

		$wp_customize->add_setting(
			'kintaelectric_secondary_color',
			[
				'default' => '#f59e0b',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport' => 'postMessage',
			]
		);

		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				$wp_customize,
				'kintaelectric_secondary_color',
				[
					'label' => esc_html__( 'Color Secundario', 'kintaelectric' ),
					'description' => esc_html__( 'Color amarillo corporativo de Kinta Electric', 'kintaelectric' ),
					'section' => self::CUSTOMIZER_SECTION_NAME,
					'priority' => 15,
				]
			)
		);

		// Configuración de logo
		$wp_customize->add_setting(
			'kintaelectric_logo',
			[
				'sanitize_callback' => 'absint',
				'transport' => 'postMessage',
			]
		);

		$wp_customize->add_control(
			new \WP_Customize_Media_Control(
				$wp_customize,
				'kintaelectric_logo',
				[
					'label' => esc_html__( 'Logo Kinta Electric', 'kintaelectric' ),
					'description' => esc_html__( 'Sube el logo de tu empresa', 'kintaelectric' ),
					'section' => self::CUSTOMIZER_SECTION_NAME,
					'priority' => 20,
				]
			)
		);

		// Configuración de información de contacto
		$wp_customize->add_setting(
			'kintaelectric_phone',
			[
				'default' => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport' => 'postMessage',
			]
		);

		$wp_customize->add_control(
			'kintaelectric_phone',
			[
				'label' => esc_html__( 'Teléfono de Contacto', 'kintaelectric' ),
				'description' => esc_html__( 'Número de teléfono principal', 'kintaelectric' ),
				'section' => self::CUSTOMIZER_SECTION_NAME,
				'type' => 'text',
				'priority' => 30,
			]
		);

		$wp_customize->add_setting(
			'kintaelectric_email',
			[
				'default' => '',
				'sanitize_callback' => 'sanitize_email',
				'transport' => 'postMessage',
			]
		);

		$wp_customize->add_control(
			'kintaelectric_email',
			[
				'label' => esc_html__( 'Email de Contacto', 'kintaelectric' ),
				'description' => esc_html__( 'Correo electrónico principal', 'kintaelectric' ),
				'section' => self::CUSTOMIZER_SECTION_NAME,
				'type' => 'email',
				'priority' => 35,
			]
		);

		// Configuración de redes sociales
		$wp_customize->add_setting(
			'kintaelectric_facebook',
			[
				'default' => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport' => 'postMessage',
			]
		);

		$wp_customize->add_control(
			'kintaelectric_facebook',
			[
				'label' => esc_html__( 'Facebook', 'kintaelectric' ),
				'section' => self::CUSTOMIZER_SECTION_NAME,
				'type' => 'url',
				'priority' => 40,
			]
		);

		$wp_customize->add_setting(
			'kintaelectric_instagram',
			[
				'default' => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport' => 'postMessage',
			]
		);

		$wp_customize->add_control(
			'kintaelectric_instagram',
			[
				'label' => esc_html__( 'Instagram', 'kintaelectric' ),
				'section' => self::CUSTOMIZER_SECTION_NAME,
				'type' => 'url',
				'priority' => 45,
			]
		);

		// Configuración de WhatsApp
		$wp_customize->add_setting(
			'kintaelectric_whatsapp',
			[
				'default' => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport' => 'postMessage',
			]
		);

		$wp_customize->add_control(
			'kintaelectric_whatsapp',
			[
				'label' => esc_html__( 'WhatsApp', 'kintaelectric' ),
				'description' => esc_html__( 'Número de WhatsApp (ej: +584121234567)', 'kintaelectric' ),
				'section' => self::CUSTOMIZER_SECTION_NAME,
				'type' => 'text',
				'priority' => 50,
			]
		);

		// Configuración de header y footer
		$wp_customize->add_setting(
			'kintaelectric_show_header',
			[
				'default' => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'transport' => 'postMessage',
			]
		);

		$wp_customize->add_control(
			'kintaelectric_show_header',
			[
				'label' => esc_html__( 'Mostrar Header', 'kintaelectric' ),
				'description' => esc_html__( 'Mostrar el header del sitio', 'kintaelectric' ),
				'section' => self::CUSTOMIZER_SECTION_NAME,
				'type' => 'checkbox',
				'priority' => 60,
			]
		);

		$wp_customize->add_setting(
			'kintaelectric_show_footer',
			[
				'default' => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'transport' => 'postMessage',
			]
		);

		$wp_customize->add_control(
			'kintaelectric_show_footer',
			[
				'label' => esc_html__( 'Mostrar Footer', 'kintaelectric' ),
				'description' => esc_html__( 'Mostrar el footer del sitio', 'kintaelectric' ),
				'section' => self::CUSTOMIZER_SECTION_NAME,
				'type' => 'checkbox',
				'priority' => 65,
			]
		);
	}

	public function enqueue_customizer_script() {
		$handle     = 'kintaelectric-customizer';
		$asset_path = kintaelectric_SCRIPTS_PATH . 'kintaelectric-customizer.asset.php';
		$asset_url  = kintaelectric_SCRIPTS_URL;

		if ( ! file_exists( $asset_path ) ) {
			return;
		}

		$asset = require $asset_path;

		wp_enqueue_script(
			$handle,
			$asset_url . 'kintaelectric-customizer.js',
			array_merge( $asset['dependencies'], [ 'wp-util' ] ),
			$asset['version'],
			true
		);

		wp_set_script_translations( $handle, 'kintaelectric' );

		wp_localize_script(
			$handle,
			'ehp_customizer',
			[
				'nonce' => wp_create_nonce( 'updates' ),
				'redirectTo' => Utils::is_hello_plus_active() ? self_admin_url( 'admin.php?page=hello-plus-setup-wizard' ) : '',
			]
		);

		wp_enqueue_style(
			'kintaelectric-customizer',
			kintaelectric_STYLE_URL . 'customizer.css',
			[],
			kintaelectric_ELEMENTOR_VERSION
		);
	}

	public function __construct() {
		add_action( 'customize_controls_enqueue_scripts', [ $this, 'enqueue_customizer_script' ] );
		add_action( 'customize_register', [ $this, 'register' ] );
	}
}
