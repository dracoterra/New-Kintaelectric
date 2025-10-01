<?php
/**
 * Currency Converter Appearance Manager - Gestor de apariencia avanzado
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Currency_Converter
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gestor de apariencia avanzado para el Currency Converter.
 * Proporciona temas predefinidos, editor CSS y personalización visual.
 */
class Woocommerce_Venezuela_Suite_Converter_Appearance_Manager {

	/** @var self */
	private static $instance = null;

	/** @var array Configuración de apariencia */
	private $config = array();

	/** @var array Temas predefinidos */
	private $predefined_themes = array();

	/** @var array Estilos personalizados */
	private $custom_styles = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->load_config();
		$this->init_predefined_themes();
		$this->load_custom_styles();
	}

	/**
	 * Singleton.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Carga configuración de apariencia.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'current_theme' => get_option( 'wvs_converter_current_theme', 'minimal' ),
			'custom_css_enabled' => get_option( 'wvs_converter_custom_css_enabled', false ),
			'custom_css' => get_option( 'wvs_converter_custom_css', '' ),
			'responsive_enabled' => get_option( 'wvs_converter_responsive_enabled', true ),
			'animations_enabled' => get_option( 'wvs_converter_animations_enabled', true ),
			'icon_set' => get_option( 'wvs_converter_icon_set', 'default' ),
			'color_scheme' => get_option( 'wvs_converter_color_scheme', 'default' ),
		);
	}

	/**
	 * Inicializa temas predefinidos.
	 *
	 * @return void
	 */
	private function init_predefined_themes() {
		$this->predefined_themes = array(
			'minimal' => array(
				'name' => __( 'Minimal', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Diseño limpio y minimalista', 'woocommerce-venezuela-suite' ),
				'css' => $this->get_minimal_theme_css(),
				'colors' => array(
					'primary' => '#333333',
					'secondary' => '#666666',
					'background' => '#ffffff',
					'border' => '#e0e0e0',
				),
				'typography' => array(
					'font_family' => 'inherit',
					'font_size' => '14px',
					'font_weight' => 'normal',
				),
			),
			'modern' => array(
				'name' => __( 'Modern', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Diseño moderno con gradientes', 'woocommerce-venezuela-suite' ),
				'css' => $this->get_modern_theme_css(),
				'colors' => array(
					'primary' => '#007cba',
					'secondary' => '#005177',
					'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
					'border' => 'rgba(255,255,255,0.2)',
				),
				'typography' => array(
					'font_family' => 'Arial, sans-serif',
					'font_size' => '16px',
					'font_weight' => '500',
				),
			),
			'classic' => array(
				'name' => __( 'Classic', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Diseño clásico y elegante', 'woocommerce-venezuela-suite' ),
				'css' => $this->get_classic_theme_css(),
				'colors' => array(
					'primary' => '#2c3e50',
					'secondary' => '#7f8c8d',
					'background' => '#ecf0f1',
					'border' => '#bdc3c7',
				),
				'typography' => array(
					'font_family' => 'Georgia, serif',
					'font_size' => '15px',
					'font_weight' => 'normal',
				),
			),
			'venezuelan' => array(
				'name' => __( 'Venezuelan', 'woocommerce-venezuela-suite' ),
				'description' => __( 'Diseño con colores de la bandera venezolana', 'woocommerce-venezuela-suite' ),
				'css' => $this->get_venezuelan_theme_css(),
				'colors' => array(
					'primary' => '#ffd700',
					'secondary' => '#0033a0',
					'background' => '#ce1126',
					'border' => '#ffd700',
				),
				'typography' => array(
					'font_family' => 'Arial, sans-serif',
					'font_size' => '16px',
					'font_weight' => 'bold',
				),
			),
		);
	}

	/**
	 * Carga estilos personalizados.
	 *
	 * @return void
	 */
	private function load_custom_styles() {
		$this->custom_styles = array(
			'converter_widget' => get_option( 'wvs_converter_widget_css', '' ),
			'converter_button' => get_option( 'wvs_converter_button_css', '' ),
			'converter_input' => get_option( 'wvs_converter_input_css', '' ),
			'converter_result' => get_option( 'wvs_converter_result_css', '' ),
		);
	}

	/**
	 * Obtiene CSS del tema minimal.
	 *
	 * @return string CSS del tema minimal.
	 */
	private function get_minimal_theme_css() {
		return '
		.wvs-converter-minimal {
			background: #ffffff;
			border: 1px solid #e0e0e0;
			border-radius: 8px;
			padding: 20px;
			font-family: inherit;
			font-size: 14px;
			color: #333333;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}
		
		.wvs-converter-minimal .converter-title {
			font-size: 16px;
			font-weight: 600;
			margin-bottom: 15px;
			color: #333333;
		}
		
		.wvs-converter-minimal .converter-input {
			width: 100%;
			padding: 10px;
			border: 1px solid #e0e0e0;
			border-radius: 4px;
			font-size: 14px;
			margin-bottom: 10px;
		}
		
		.wvs-converter-minimal .converter-button {
			background: #333333;
			color: #ffffff;
			border: none;
			padding: 10px 20px;
			border-radius: 4px;
			cursor: pointer;
			font-size: 14px;
			transition: background 0.3s ease;
		}
		
		.wvs-converter-minimal .converter-button:hover {
			background: #555555;
		}
		
		.wvs-converter-minimal .converter-result {
			margin-top: 15px;
			padding: 10px;
			background: #f8f9fa;
			border-radius: 4px;
			font-weight: 500;
		}
		';
	}

	/**
	 * Obtiene CSS del tema modern.
	 *
	 * @return string CSS del tema modern.
	 */
	private function get_modern_theme_css() {
		return '
		.wvs-converter-modern {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border: none;
			border-radius: 12px;
			padding: 25px;
			font-family: Arial, sans-serif;
			font-size: 16px;
			color: #ffffff;
			box-shadow: 0 8px 32px rgba(0,0,0,0.2);
		}
		
		.wvs-converter-modern .converter-title {
			font-size: 20px;
			font-weight: 500;
			margin-bottom: 20px;
			color: #ffffff;
			text-align: center;
		}
		
		.wvs-converter-modern .converter-input {
			width: 100%;
			padding: 12px 15px;
			border: 2px solid rgba(255,255,255,0.2);
			border-radius: 8px;
			font-size: 16px;
			margin-bottom: 15px;
			background: rgba(255,255,255,0.1);
			color: #ffffff;
		}
		
		.wvs-converter-modern .converter-input::placeholder {
			color: rgba(255,255,255,0.7);
		}
		
		.wvs-converter-modern .converter-button {
			background: rgba(255,255,255,0.2);
			color: #ffffff;
			border: 2px solid rgba(255,255,255,0.3);
			padding: 12px 25px;
			border-radius: 8px;
			cursor: pointer;
			font-size: 16px;
			font-weight: 500;
			transition: all 0.3s ease;
			backdrop-filter: blur(10px);
		}
		
		.wvs-converter-modern .converter-button:hover {
			background: rgba(255,255,255,0.3);
			transform: translateY(-2px);
		}
		
		.wvs-converter-modern .converter-result {
			margin-top: 20px;
			padding: 15px;
			background: rgba(255,255,255,0.1);
			border-radius: 8px;
			font-weight: 500;
			text-align: center;
			backdrop-filter: blur(10px);
		}
		';
	}

	/**
	 * Obtiene CSS del tema classic.
	 *
	 * @return string CSS del tema classic.
	 */
	private function get_classic_theme_css() {
		return '
		.wvs-converter-classic {
			background: #ecf0f1;
			border: 2px solid #bdc3c7;
			border-radius: 6px;
			padding: 25px;
			font-family: Georgia, serif;
			font-size: 15px;
			color: #2c3e50;
			box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
		}
		
		.wvs-converter-classic .converter-title {
			font-size: 18px;
			font-weight: normal;
			margin-bottom: 20px;
			color: #2c3e50;
			border-bottom: 1px solid #bdc3c7;
			padding-bottom: 10px;
		}
		
		.wvs-converter-classic .converter-input {
			width: 100%;
			padding: 12px;
			border: 2px solid #bdc3c7;
			border-radius: 3px;
			font-size: 15px;
			font-family: Georgia, serif;
			margin-bottom: 15px;
			background: #ffffff;
		}
		
		.wvs-converter-classic .converter-button {
			background: #2c3e50;
			color: #ffffff;
			border: none;
			padding: 12px 25px;
			border-radius: 3px;
			cursor: pointer;
			font-size: 15px;
			font-family: Georgia, serif;
			transition: background 0.3s ease;
		}
		
		.wvs-converter-classic .converter-button:hover {
			background: #34495e;
		}
		
		.wvs-converter-classic .converter-result {
			margin-top: 20px;
			padding: 15px;
			background: #ffffff;
			border: 1px solid #bdc3c7;
			border-radius: 3px;
			font-weight: normal;
		}
		';
	}

	/**
	 * Obtiene CSS del tema venezuelan.
	 *
	 * @return string CSS del tema venezuelan.
	 */
	private function get_venezuelan_theme_css() {
		return '
		.wvs-converter-venezuelan {
			background: #ce1126;
			border: 3px solid #ffd700;
			border-radius: 10px;
			padding: 25px;
			font-family: Arial, sans-serif;
			font-size: 16px;
			color: #ffd700;
			box-shadow: 0 4px 15px rgba(0,0,0,0.3);
		}
		
		.wvs-converter-venezuelan .converter-title {
			font-size: 20px;
			font-weight: bold;
			margin-bottom: 20px;
			color: #ffd700;
			text-align: center;
			text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
		}
		
		.wvs-converter-venezuelan .converter-input {
			width: 100%;
			padding: 15px;
			border: 2px solid #ffd700;
			border-radius: 5px;
			font-size: 16px;
			font-weight: bold;
			margin-bottom: 15px;
			background: rgba(255,255,255,0.9);
			color: #0033a0;
		}
		
		.wvs-converter-venezuelan .converter-button {
			background: #ffd700;
			color: #0033a0;
			border: 2px solid #ffd700;
			padding: 15px 30px;
			border-radius: 5px;
			cursor: pointer;
			font-size: 16px;
			font-weight: bold;
			transition: all 0.3s ease;
			width: 100%;
		}
		
		.wvs-converter-venezuelan .converter-button:hover {
			background: #0033a0;
			color: #ffd700;
		}
		
		.wvs-converter-venezuelan .converter-result {
			margin-top: 20px;
			padding: 15px;
			background: rgba(255,255,255,0.1);
			border: 2px solid #ffd700;
			border-radius: 5px;
			font-weight: bold;
			text-align: center;
			font-size: 18px;
		}
		';
	}

	/**
	 * Aplica un tema predefinido.
	 *
	 * @param string $theme_name Nombre del tema.
	 * @return bool True si se aplicó correctamente.
	 */
	public function apply_theme( $theme_name ) {
		if ( ! isset( $this->predefined_themes[ $theme_name ] ) ) {
			return false;
		}
		
		update_option( 'wvs_converter_current_theme', $theme_name );
		$this->config['current_theme'] = $theme_name;
		
		// Generar y guardar CSS del tema
		$theme_css = $this->predefined_themes[ $theme_name ]['css'];
		update_option( 'wvs_converter_theme_css', $theme_css );
		
		return true;
	}

	/**
	 * Obtiene el CSS del tema actual.
	 *
	 * @return string CSS del tema actual.
	 */
	public function get_current_theme_css() {
		$current_theme = $this->config['current_theme'];
		
		if ( isset( $this->predefined_themes[ $current_theme ] ) ) {
			return $this->predefined_themes[ $current_theme ]['css'];
		}
		
		return '';
	}

	/**
	 * Genera CSS personalizado basado en configuración.
	 *
	 * @param array $custom_config Configuración personalizada.
	 * @return string CSS personalizado.
	 */
	public function generate_custom_css( $custom_config = array() ) {
		$config = wp_parse_args( $custom_config, array(
			'background_color' => '#ffffff',
			'text_color' => '#333333',
			'border_color' => '#e0e0e0',
			'button_background' => '#007cba',
			'button_text_color' => '#ffffff',
			'border_radius' => '8px',
			'padding' => '20px',
			'font_size' => '14px',
			'font_family' => 'inherit',
			'box_shadow' => '0 2px 4px rgba(0,0,0,0.1)',
		) );
		
		$css = "
		.wvs-converter-custom {
			background: {$config['background_color']};
			color: {$config['text_color']};
			border: 1px solid {$config['border_color']};
			border-radius: {$config['border_radius']};
			padding: {$config['padding']};
			font-family: {$config['font_family']};
			font-size: {$config['font_size']};
			box-shadow: {$config['box_shadow']};
		}
		
		.wvs-converter-custom .converter-title {
			color: {$config['text_color']};
			font-size: calc({$config['font_size']} + 2px);
			font-weight: 600;
			margin-bottom: 15px;
		}
		
		.wvs-converter-custom .converter-input {
			width: 100%;
			padding: 10px;
			border: 1px solid {$config['border_color']};
			border-radius: calc({$config['border_radius']} / 2);
			font-size: {$config['font_size']};
			font-family: {$config['font_family']};
			margin-bottom: 10px;
			background: {$config['background_color']};
			color: {$config['text_color']};
		}
		
		.wvs-converter-custom .converter-button {
			background: {$config['button_background']};
			color: {$config['button_text_color']};
			border: none;
			padding: 10px 20px;
			border-radius: calc({$config['border_radius']} / 2);
			cursor: pointer;
			font-size: {$config['font_size']};
			font-family: {$config['font_family']};
			transition: all 0.3s ease;
		}
		
		.wvs-converter-custom .converter-button:hover {
			opacity: 0.9;
			transform: translateY(-1px);
		}
		
		.wvs-converter-custom .converter-result {
			margin-top: 15px;
			padding: 10px;
			background: {$config['background_color']};
			border: 1px solid {$config['border_color']};
			border-radius: calc({$config['border_radius']} / 2);
			font-weight: 500;
			color: {$config['text_color']};
		}
		";
		
		return $css;
	}

	/**
	 * Aplica CSS personalizado.
	 *
	 * @param string $custom_css CSS personalizado.
	 * @return bool True si se aplicó correctamente.
	 */
	public function apply_custom_css( $custom_css ) {
		update_option( 'wvs_converter_custom_css', $custom_css );
		$this->config['custom_css'] = $custom_css;
		
		return true;
	}

	/**
	 * Habilita/deshabilita CSS personalizado.
	 *
	 * @param bool $enabled Estado habilitado.
	 * @return bool True si se actualizó correctamente.
	 */
	public function toggle_custom_css( $enabled ) {
		update_option( 'wvs_converter_custom_css_enabled', $enabled );
		$this->config['custom_css_enabled'] = $enabled;
		
		return true;
	}

	/**
	 * Genera CSS responsive.
	 *
	 * @return string CSS responsive.
	 */
	public function generate_responsive_css() {
		if ( ! $this->config['responsive_enabled'] ) {
			return '';
		}
		
		return '
		@media (max-width: 768px) {
			.wvs-converter-widget {
				padding: 15px;
				font-size: 14px;
			}
			
			.wvs-converter-widget .converter-title {
				font-size: 16px;
			}
			
			.wvs-converter-widget .converter-input {
				padding: 8px;
				font-size: 14px;
			}
			
			.wvs-converter-widget .converter-button {
				padding: 8px 16px;
				font-size: 14px;
				width: 100%;
			}
		}
		
		@media (max-width: 480px) {
			.wvs-converter-widget {
				padding: 10px;
				font-size: 13px;
			}
			
			.wvs-converter-widget .converter-title {
				font-size: 15px;
			}
			
			.wvs-converter-widget .converter-input {
				padding: 6px;
				font-size: 13px;
			}
			
			.wvs-converter-widget .converter-button {
				padding: 6px 12px;
				font-size: 13px;
			}
		}
		';
	}

	/**
	 * Genera CSS de animaciones.
	 *
	 * @return string CSS de animaciones.
	 */
	public function generate_animations_css() {
		if ( ! $this->config['animations_enabled'] ) {
			return '';
		}
		
		return '
		@keyframes wvs-fadeIn {
			from { opacity: 0; transform: translateY(10px); }
			to { opacity: 1; transform: translateY(0); }
		}
		
		@keyframes wvs-slideIn {
			from { transform: translateX(-100%); }
			to { transform: translateX(0); }
		}
		
		@keyframes wvs-pulse {
			0% { transform: scale(1); }
			50% { transform: scale(1.05); }
			100% { transform: scale(1); }
		}
		
		.wvs-converter-widget {
			animation: wvs-fadeIn 0.5s ease-out;
		}
		
		.wvs-converter-widget .converter-button:hover {
			animation: wvs-pulse 0.3s ease-in-out;
		}
		
		.wvs-converter-widget .converter-result {
			animation: wvs-fadeIn 0.3s ease-out;
		}
		';
	}

	/**
	 * Obtiene todos los CSS combinados.
	 *
	 * @return string CSS combinado.
	 */
	public function get_combined_css() {
		$css_parts = array();
		
		// CSS del tema actual
		$theme_css = $this->get_current_theme_css();
		if ( $theme_css ) {
			$css_parts[] = $theme_css;
		}
		
		// CSS personalizado si está habilitado
		if ( $this->config['custom_css_enabled'] && $this->config['custom_css'] ) {
			$css_parts[] = $this->config['custom_css'];
		}
		
		// CSS responsive
		$responsive_css = $this->generate_responsive_css();
		if ( $responsive_css ) {
			$css_parts[] = $responsive_css;
		}
		
		// CSS de animaciones
		$animations_css = $this->generate_animations_css();
		if ( $animations_css ) {
			$css_parts[] = $animations_css;
		}
		
		return implode( "\n", $css_parts );
	}

	/**
	 * Obtiene temas predefinidos disponibles.
	 *
	 * @return array Temas predefinidos.
	 */
	public function get_predefined_themes() {
		return $this->predefined_themes;
	}

	/**
	 * Obtiene información del tema actual.
	 *
	 * @return array|null Información del tema actual.
	 */
	public function get_current_theme_info() {
		$current_theme = $this->config['current_theme'];
		return $this->predefined_themes[ $current_theme ] ?? null;
	}

	/**
	 * Valida CSS personalizado.
	 *
	 * @param string $css CSS a validar.
	 * @return array Resultado de la validación.
	 */
	public function validate_custom_css( $css ) {
		$errors = array();
		$warnings = array();
		
		// Verificar sintaxis básica
		if ( ! preg_match( '/^[^{}]*\{[^{}]*\}[^{}]*$/', $css ) ) {
			$errors[] = __( 'Sintaxis CSS inválida', 'woocommerce-venezuela-suite' );
		}
		
		// Verificar propiedades peligrosas
		$dangerous_properties = array( 'eval', 'expression', 'javascript:', 'vbscript:' );
		foreach ( $dangerous_properties as $prop ) {
			if ( stripos( $css, $prop ) !== false ) {
				$errors[] = sprintf( __( 'Propiedad peligrosa detectada: %s', 'woocommerce-venezuela-suite' ), $prop );
			}
		}
		
		// Verificar selectores válidos
		if ( preg_match( '/[^a-zA-Z0-9\s\.#:\[\]()_-]/', $css ) ) {
			$warnings[] = __( 'Caracteres no estándar detectados en CSS', 'woocommerce-venezuela-suite' );
		}
		
		return array(
			'valid' => empty( $errors ),
			'errors' => $errors,
			'warnings' => $warnings,
		);
	}

	/**
	 * Actualiza configuración de apariencia.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'current_theme',
			'custom_css_enabled',
			'custom_css',
			'responsive_enabled',
			'animations_enabled',
			'icon_set',
			'color_scheme',
		);

		foreach ( $config as $key => $value ) {
			if ( in_array( $key, $valid_keys ) ) {
				update_option( 'wvs_converter_' . $key, $value );
				$this->config[ $key ] = $value;
			}
		}

		return true;
	}

	/**
	 * Obtiene configuración actual.
	 *
	 * @return array Configuración actual.
	 */
	public function get_config() {
		return $this->config;
	}
}
