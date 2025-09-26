<?php
/**
 * Sistema de Configuraciones de Visualización - WooCommerce Venezuela Pro 2025
 * 
 * Maneja todas las configuraciones relacionadas con la visualización de conversores
 * Control granular por contexto y configuración de apariencia
 * 
 * @package WooCommerce_Venezuela_Pro_2025
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Display_Settings {
	
	/**
	 * Instancia única (Singleton)
	 * 
	 * @var WVP_Display_Settings
	 */
	private static $instance = null;
	
	/**
	 * Configuraciones por defecto
	 * 
	 * @var array
	 */
	private $default_settings = array();
	
	/**
	 * Obtener instancia única
	 * 
	 * @return WVP_Display_Settings
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor privado
	 */
	private function __construct() {
		$this->init_default_settings();
		$this->init_hooks();
	}
	
	/**
	 * Inicializar configuraciones por defecto
	 */
	private function init_default_settings() {
		$this->default_settings = array(
			// Control de ubicación/contexto
			'display_locations' => array(
				'single_product' => true,     // Páginas de producto individual
				'shop_loop' => true,          // Lista de productos (shop)
				'cart' => true,               // Carrito de compras
				'checkout' => true,           // Proceso de checkout
				'widget' => false,            // Widgets y sidebars
				'footer' => false,            // Footer
				'header' => false,            // Header
				'mini_cart' => true,          // Mini carrito
				'product_category' => true,   // Páginas de categoría
				'search_results' => true      // Resultados de búsqueda
			),
			
			// Configuración de apariencia
			'appearance' => array(
				'style' => 'buttons',         // buttons, dropdown, toggle, inline
				'theme' => 'default',         // default, minimal, modern, elegant
				'size' => 'medium',           // small, medium, large
				'position' => 'after_price',  // before_price, after_price, replace_price
				'animation' => 'fade',        // none, fade, slide, bounce
				'show_labels' => true,        // Mostrar etiquetas USD/VES
				'show_symbols' => true,       // Mostrar símbolos de moneda
				'show_rate' => false,         // Mostrar tasa BCV
				'show_last_update' => false   // Mostrar última actualización
			),
			
			// Configuración de colores y CSS
			'styling' => array(
				'primary_color' => '#0073aa',
				'secondary_color' => '#00a0d2',
				'text_color' => '#333333',
				'background_color' => '#ffffff',
				'border_color' => '#dddddd',
				'hover_color' => '#005177',
				'active_color' => '#0085ba',
				'border_radius' => '4px',
				'font_size' => '14px',
				'font_weight' => 'normal',
				'custom_css' => ''
			),
			
			// Configuración responsiva
			'responsive' => array(
				'mobile_style' => 'compact',   // compact, full, hidden
				'tablet_style' => 'medium',    // small, medium, large
				'desktop_style' => 'large',    // small, medium, large
				'breakpoint_mobile' => '768px',
				'breakpoint_tablet' => '1024px'
			),
			
			// Configuración avanzada
			'advanced' => array(
				'cache_duration' => 3600,      // Cache en segundos
				'lazy_load' => true,           // Carga perezosa de scripts
				'minify_css' => true,          // Minificar CSS en producción
				'combine_scripts' => true,     // Combinar scripts
				'debug_mode' => false          // Modo debug
			)
		);
	}
	
	/**
	 * Inicializar hooks
	 */
	private function init_hooks() {
		// Hook para guardar configuraciones
		add_action( 'wp_ajax_wvp_save_display_settings', array( $this, 'ajax_save_settings' ) );
		
		// Hook para resetear configuraciones
		add_action( 'wp_ajax_wvp_reset_display_settings', array( $this, 'ajax_reset_settings' ) );
		
		// Hook para exportar configuraciones
		add_action( 'wp_ajax_wvp_export_display_settings', array( $this, 'ajax_export_settings' ) );
		
		// Hook para importar configuraciones
		add_action( 'wp_ajax_wvp_import_display_settings', array( $this, 'ajax_import_settings' ) );
	}
	
	/**
	 * Obtener todas las configuraciones
	 * 
	 * @return array Configuraciones completas
	 */
	public static function get_settings() {
		$instance = self::get_instance();
		$saved_settings = get_option( 'wvp_display_settings', array() );
		
		return wp_parse_args( $saved_settings, $instance->default_settings );
	}
	
	/**
	 * Obtener configuración específica
	 * 
	 * @param string $section Sección de configuración
	 * @param string $key Clave específica (opcional)
	 * @param mixed $default Valor por defecto
	 * @return mixed Valor de configuración
	 */
	public static function get_setting( $section, $key = null, $default = null ) {
		$settings = self::get_settings();
		
		if ( ! isset( $settings[ $section ] ) ) {
			return $default;
		}
		
		if ( $key === null ) {
			return $settings[ $section ];
		}
		
		return isset( $settings[ $section ][ $key ] ) ? $settings[ $section ][ $key ] : $default;
	}
	
	/**
	 * Actualizar configuración
	 * 
	 * @param string $section Sección de configuración
	 * @param string $key Clave específica
	 * @param mixed $value Nuevo valor
	 * @return bool True si se actualizó correctamente
	 */
	public static function update_setting( $section, $key, $value ) {
		$settings = self::get_settings();
		
		if ( ! isset( $settings[ $section ] ) ) {
			$settings[ $section ] = array();
		}
		
		$settings[ $section ][ $key ] = $value;
		
		return update_option( 'wvp_display_settings', $settings );
	}
	
	/**
	 * Verificar si mostrar conversor en contexto específico
	 * 
	 * @param string $context Contexto (single_product, shop_loop, etc.)
	 * @return bool True si debe mostrar
	 */
	public static function should_display_in_context( $context ) {
		return self::get_setting( 'display_locations', $context, false );
	}
	
	/**
	 * Obtener configuración de apariencia
	 * 
	 * @return array Configuración de apariencia
	 */
	public static function get_appearance_settings() {
		return self::get_setting( 'appearance', null, array() );
	}
	
	/**
	 * Obtener configuración de estilos CSS
	 * 
	 * @return array Configuración de estilos
	 */
	public static function get_styling_settings() {
		return self::get_setting( 'styling', null, array() );
	}
	
	/**
	 * Obtener configuración responsiva
	 * 
	 * @return array Configuración responsiva
	 */
	public static function get_responsive_settings() {
		return self::get_setting( 'responsive', null, array() );
	}
	
	/**
	 * Generar CSS personalizado basado en configuraciones
	 * 
	 * @return string CSS personalizado
	 */
	public static function generate_custom_css() {
		$styling = self::get_styling_settings();
		$appearance = self::get_appearance_settings();
		
		$css = "
		/* WVP Currency Converter - Custom Styles */
		.wvp-currency-converter {
			font-size: {$styling['font_size']};
			border-radius: {$styling['border_radius']};
		}
		
		.wvp-currency-btn {
			background-color: {$styling['background_color']};
			color: {$styling['text_color']};
			border: 1px solid {$styling['border_color']};
			border-radius: {$styling['border_radius']};
			font-weight: {$styling['font_weight']};
		}
		
		.wvp-currency-btn:hover {
			background-color: {$styling['hover_color']};
			color: #ffffff;
		}
		
		.wvp-currency-btn.active {
			background-color: {$styling['active_color']};
			color: #ffffff;
		}
		
		.wvp-currency-switcher.wvp-size-small {
			font-size: 12px;
		}
		
		.wvp-currency-switcher.wvp-size-medium {
			font-size: 14px;
		}
		
		.wvp-currency-switcher.wvp-size-large {
			font-size: 16px;
		}
		
		/* Custom CSS from user */
		{$styling['custom_css']}
		";
		
		return $css;
	}
	
	/**
	 * Obtener configuración para JavaScript
	 * 
	 * @return array Configuración para JS
	 */
	public static function get_js_config() {
		$appearance = self::get_appearance_settings();
		$advanced = self::get_setting( 'advanced', null, array() );
		
		return array(
			'style' => $appearance['style'],
			'theme' => $appearance['theme'],
			'animation' => $appearance['animation'],
			'cache_duration' => $advanced['cache_duration'],
			'debug_mode' => $advanced['debug_mode']
		);
	}
	
	/**
	 * AJAX: Guardar configuraciones
	 */
	public function ajax_save_settings() {
		// Verificar nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wvp_display_settings_nonce' ) ) {
			wp_send_json_error( 'Nonce inválido' );
		}
		
		// Verificar permisos
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permisos insuficientes' );
		}
		
		$settings = $_POST['settings'];
		
		// Sanitizar y validar configuraciones
		$settings = $this->sanitize_settings( $settings );
		
		if ( update_option( 'wvp_display_settings', $settings ) ) {
			wp_send_json_success( 'Configuraciones guardadas correctamente' );
		} else {
			wp_send_json_error( 'Error al guardar configuraciones' );
		}
	}
	
	/**
	 * AJAX: Resetear configuraciones
	 */
	public function ajax_reset_settings() {
		// Verificar nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wvp_display_settings_nonce' ) ) {
			wp_send_json_error( 'Nonce inválido' );
		}
		
		// Verificar permisos
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permisos insuficientes' );
		}
		
		if ( update_option( 'wvp_display_settings', $this->default_settings ) ) {
			wp_send_json_success( 'Configuraciones reseteadas correctamente' );
		} else {
			wp_send_json_error( 'Error al resetear configuraciones' );
		}
	}
	
	/**
	 * Sanitizar configuraciones
	 * 
	 * @param array $settings Configuraciones a sanitizar
	 * @return array Configuraciones sanitizadas
	 */
	private function sanitize_settings( $settings ) {
		// Sanitizar ubicaciones de visualización
		if ( isset( $settings['display_locations'] ) ) {
			foreach ( $settings['display_locations'] as $key => $value ) {
				$settings['display_locations'][ $key ] = (bool) $value;
			}
		}
		
		// Sanitizar configuración de apariencia
		if ( isset( $settings['appearance'] ) ) {
			$settings['appearance']['style'] = sanitize_text_field( $settings['appearance']['style'] );
			$settings['appearance']['theme'] = sanitize_text_field( $settings['appearance']['theme'] );
			$settings['appearance']['size'] = sanitize_text_field( $settings['appearance']['size'] );
			$settings['appearance']['position'] = sanitize_text_field( $settings['appearance']['position'] );
			$settings['appearance']['animation'] = sanitize_text_field( $settings['appearance']['animation'] );
		}
		
		// Sanitizar configuración de estilos
		if ( isset( $settings['styling'] ) ) {
			$settings['styling']['primary_color'] = sanitize_hex_color( $settings['styling']['primary_color'] );
			$settings['styling']['secondary_color'] = sanitize_hex_color( $settings['styling']['secondary_color'] );
			$settings['styling']['text_color'] = sanitize_hex_color( $settings['styling']['text_color'] );
			$settings['styling']['background_color'] = sanitize_hex_color( $settings['styling']['background_color'] );
			$settings['styling']['border_color'] = sanitize_hex_color( $settings['styling']['border_color'] );
			$settings['styling']['custom_css'] = wp_strip_all_tags( $settings['styling']['custom_css'] );
		}
		
		return $settings;
	}
	
	/**
	 * Obtener configuraciones por defecto
	 * 
	 * @return array Configuraciones por defecto
	 */
	public function get_default_settings() {
		return $this->default_settings;
	}
	
	/**
	 * Verificar si el modo debug está activo
	 * 
	 * @return bool True si está activo
	 */
	public static function is_debug_mode() {
		return self::get_setting( 'advanced', 'debug_mode', false );
	}
}

// Inicializar la clase
WVP_Display_Settings::get_instance();
