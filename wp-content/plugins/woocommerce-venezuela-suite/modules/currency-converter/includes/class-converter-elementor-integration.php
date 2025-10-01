<?php
/**
 * Currency Converter Elementor Integration - Integración con Elementor
 *
 * @package WooCommerce_Venezuela_Suite\Modules\Currency_Converter
 * @since 1.0.0
 */

// Salir si se accede directamente.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Solo cargar si Elementor está disponible
if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

/**
 * Integración completa con Elementor para el Currency Converter.
 * Proporciona widget nativo de Elementor con controles avanzados.
 */
class Woocommerce_Venezuela_Suite_Converter_Elementor_Integration {

	/** @var self */
	private static $instance = null;

	/** @var array Configuración de Elementor */
	private $config = array();

	/** @var array Widgets registrados */
	private $registered_widgets = array();

	/**
	 * Constructor privado.
	 */
	private function __construct() {
		$this->load_config();
		$this->init_hooks();
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
	 * Carga configuración de Elementor.
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'elementor_enabled' => get_option( 'wvs_converter_elementor_enabled', true ),
			'widget_categories' => get_option( 'wvs_converter_elementor_categories', array( 'woocommerce' ) ),
			'default_settings' => get_option( 'wvs_converter_elementor_defaults', array() ),
			'templates_enabled' => get_option( 'wvs_converter_elementor_templates', true ),
		);
	}

	/**
	 * Inicializa hooks de Elementor.
	 *
	 * @return void
	 */
	private function init_hooks() {
		if ( ! $this->config['elementor_enabled'] ) {
			return;
		}

		add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_widget_categories' ) );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ) );
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_frontend_styles' ) );
		add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	}

	/**
	 * Registra widgets de Elementor.
	 *
	 * @return void
	 */
	public function register_widgets() {
		// Registrar widget principal de Currency Converter
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( 
			new Woocommerce_Venezuela_Suite_Converter_Elementor_Widget() 
		);
		
		// Registrar widget de tasa BCV en vivo
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( 
			new Woocommerce_Venezuela_Suite_BCV_Rate_Elementor_Widget() 
		);
		
		// Registrar widget de historial de tasas
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( 
			new Woocommerce_Venezuela_Suite_Rate_History_Elementor_Widget() 
		);
	}

	/**
	 * Añade categorías de widgets personalizadas.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Manager de elementos.
	 * @return void
	 */
	public function add_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'woocommerce-venezuela-suite',
			array(
				'title' => __( 'WooCommerce Venezuela Suite', 'woocommerce-venezuela-suite' ),
				'icon' => 'fa fa-bolt',
			)
		);
	}

	/**
	 * Enqueue scripts del editor de Elementor.
	 *
	 * @return void
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script(
			'wvs-converter-elementor-editor',
			plugin_dir_url( __FILE__ ) . '../admin/js/converter-elementor-editor.js',
			array( 'jquery', 'elementor-editor' ),
			'1.0.0',
			true
		);
		
		wp_localize_script(
			'wvs-converter-elementor-editor',
			'wvsConverterElementor',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wvs_converter_elementor_nonce' ),
				'themes' => $this->get_available_themes(),
				'defaults' => $this->config['default_settings'],
			)
		);
	}

	/**
	 * Enqueue estilos del frontend.
	 *
	 * @return void
	 */
	public function enqueue_frontend_styles() {
		wp_enqueue_style(
			'wvs-converter-elementor-frontend',
			plugin_dir_url( __FILE__ ) . '../public/css/converter-elementor-frontend.css',
			array(),
			'1.0.0'
		);
	}

	/**
	 * Enqueue scripts del frontend.
	 *
	 * @return void
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'wvs-converter-elementor-frontend',
			plugin_dir_url( __FILE__ ) . '../public/js/converter-elementor-frontend.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);
		
		wp_localize_script(
			'wvs-converter-elementor-frontend',
			'wvsConverterElementorFrontend',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wvs_converter_elementor_frontend_nonce' ),
			)
		);
	}

	/**
	 * Obtiene temas disponibles para Elementor.
	 *
	 * @return array Temas disponibles.
	 */
	private function get_available_themes() {
		$appearance_manager = Woocommerce_Venezuela_Suite_Converter_Appearance_Manager::get_instance();
		$themes = $appearance_manager->get_predefined_themes();
		
		$theme_options = array();
		foreach ( $themes as $theme_id => $theme ) {
			$theme_options[ $theme_id ] = $theme['name'];
		}
		
		return $theme_options;
	}

	/**
	 * Obtiene configuración actual.
	 *
	 * @return array Configuración actual.
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * Actualiza configuración de Elementor.
	 *
	 * @param array $config Nueva configuración.
	 * @return bool True si se actualizó correctamente.
	 */
	public function update_config( $config ) {
		$valid_keys = array(
			'elementor_enabled',
			'widget_categories',
			'default_settings',
			'templates_enabled',
		);

		foreach ( $config as $key => $value ) {
			if ( in_array( $key, $valid_keys ) ) {
				update_option( 'wvs_converter_elementor_' . $key, $value );
				$this->config[ $key ] = $value;
			}
		}

		return true;
	}
}

/**
 * Widget principal de Currency Converter para Elementor.
 */
class Woocommerce_Venezuela_Suite_Converter_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Obtiene nombre del widget.
	 *
	 * @return string Nombre del widget.
	 */
	public function get_name() {
		return 'wvs-currency-converter';
	}

	/**
	 * Obtiene título del widget.
	 *
	 * @return string Título del widget.
	 */
	public function get_title() {
		return __( 'Currency Converter', 'woocommerce-venezuela-suite' );
	}

	/**
	 * Obtiene icono del widget.
	 *
	 * @return string Icono del widget.
	 */
	public function get_icon() {
		return 'eicon-exchange';
	}

	/**
	 * Obtiene categorías del widget.
	 *
	 * @return array Categorías del widget.
	 */
	public function get_categories() {
		return array( 'woocommerce-venezuela-suite', 'woocommerce' );
	}

	/**
	 * Obtiene palabras clave del widget.
	 *
	 * @return array Palabras clave.
	 */
	public function get_keywords() {
		return array( 'currency', 'converter', 'usd', 'ves', 'venezuela', 'exchange' );
	}

	/**
	 * Registra controles del widget.
	 *
	 * @return void
	 */
	protected function _register_controls() {
		// Sección de contenido
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Contenido', 'woocommerce-venezuela-suite' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label' => __( 'Título', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Convertidor de Moneda', 'woocommerce-venezuela-suite' ),
				'placeholder' => __( 'Ingresa el título', 'woocommerce-venezuela-suite' ),
			)
		);

		$this->add_control(
			'description',
			array(
				'label' => __( 'Descripción', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'Convierte entre USD y Bolívares Venezolanos', 'woocommerce-venezuela-suite' ),
				'placeholder' => __( 'Ingresa la descripción', 'woocommerce-venezuela-suite' ),
			)
		);

		$this->add_control(
			'default_amount',
			array(
				'label' => __( 'Cantidad por Defecto', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 100,
				'min' => 0,
				'step' => 0.01,
			)
		);

		$this->add_control(
			'default_from_currency',
			array(
				'label' => __( 'Moneda Origen por Defecto', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'USD',
				'options' => array(
					'USD' => __( 'USD - Dólar Americano', 'woocommerce-venezuela-suite' ),
					'VES' => __( 'VES - Bolívar Venezolano', 'woocommerce-venezuela-suite' ),
				),
			)
		);

		$this->end_controls_section();

		// Sección de apariencia
		$this->start_controls_section(
			'appearance_section',
			array(
				'label' => __( 'Apariencia', 'woocommerce-venezuela-suite' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'theme',
			array(
				'label' => __( 'Tema', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'minimal',
				'options' => array(
					'minimal' => __( 'Minimal', 'woocommerce-venezuela-suite' ),
					'modern' => __( 'Modern', 'woocommerce-venezuela-suite' ),
					'classic' => __( 'Classic', 'woocommerce-venezuela-suite' ),
					'venezuelan' => __( 'Venezuelan', 'woocommerce-venezuela-suite' ),
					'custom' => __( 'Personalizado', 'woocommerce-venezuela-suite' ),
				),
			)
		);

		$this->add_control(
			'background_color',
			array(
				'label' => __( 'Color de Fondo', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
				'condition' => array(
					'theme' => 'custom',
				),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label' => __( 'Color de Texto', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#333333',
				'condition' => array(
					'theme' => 'custom',
				),
			)
		);

		$this->add_control(
			'border_radius',
			array(
				'label' => __( 'Radio del Borde', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range' => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'default' => array(
					'unit' => 'px',
					'size' => 8,
				),
				'condition' => array(
					'theme' => 'custom',
				),
			)
		);

		$this->end_controls_section();

		// Sección de configuración avanzada
		$this->start_controls_section(
			'advanced_section',
			array(
				'label' => __( 'Configuración Avanzada', 'woocommerce-venezuela-suite' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_rate_info',
			array(
				'label' => __( 'Mostrar Información de Tasa', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Sí', 'woocommerce-venezuela-suite' ),
				'label_off' => __( 'No', 'woocommerce-venezuela-suite' ),
				'return_value' => 'yes',
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_update_time',
			array(
				'label' => __( 'Mostrar Hora de Actualización', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Sí', 'woocommerce-venezuela-suite' ),
				'label_off' => __( 'No', 'woocommerce-venezuela-suite' ),
				'return_value' => 'yes',
				'default' => 'no',
			)
		);

		$this->add_control(
			'auto_update',
			array(
				'label' => __( 'Actualización Automática', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Sí', 'woocommerce-venezuela-suite' ),
				'label_off' => __( 'No', 'woocommerce-venezuela-suite' ),
				'return_value' => 'yes',
				'default' => 'yes',
			)
		);

		$this->add_control(
			'update_interval',
			array(
				'label' => __( 'Intervalo de Actualización (minutos)', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 30,
				'min' => 1,
				'max' => 1440,
				'condition' => array(
					'auto_update' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Renderiza el widget.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$converter_public = Woocommerce_Venezuela_Suite_Converter_Public::get_instance();
		$converter_public->render_elementor_widget( $settings );
	}

	/**
	 * Renderiza el widget en modo de vista previa.
	 *
	 * @return void
	 */
	protected function _content_template() {
		?>
		<div class="wvs-converter-elementor-preview">
			<h3>{{{ settings.title }}}</h3>
			<p>{{{ settings.description }}}</p>
			<div class="converter-form">
				<input type="number" value="{{{ settings.default_amount }}}" placeholder="Cantidad">
				<select>
					<option value="USD" {{{ settings.default_from_currency === 'USD' ? 'selected' : '' }}}>USD</option>
					<option value="VES" {{{ settings.default_from_currency === 'VES' ? 'selected' : '' }}}>VES</option>
				</select>
				<button>Convertir</button>
			</div>
			<div class="converter-result">
				<span class="result-amount">0.00</span>
				<span class="result-currency">VES</span>
			</div>
		</div>
		<?php
	}
}

/**
 * Widget de tasa BCV en vivo para Elementor.
 */
class Woocommerce_Venezuela_Suite_BCV_Rate_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Obtiene nombre del widget.
	 *
	 * @return string Nombre del widget.
	 */
	public function get_name() {
		return 'wvs-bcv-rate';
	}

	/**
	 * Obtiene título del widget.
	 *
	 * @return string Título del widget.
	 */
	public function get_title() {
		return __( 'BCV Rate Live', 'woocommerce-venezuela-suite' );
	}

	/**
	 * Obtiene icono del widget.
	 *
	 * @return string Icono del widget.
	 */
	public function get_icon() {
		return 'eicon-clock';
	}

	/**
	 * Obtiene categorías del widget.
	 *
	 * @return array Categorías del widget.
	 */
	public function get_categories() {
		return array( 'woocommerce-venezuela-suite', 'woocommerce' );
	}

	/**
	 * Registra controles del widget.
	 *
	 * @return void
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Contenido', 'woocommerce-venezuela-suite' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label' => __( 'Título', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Tasa BCV en Vivo', 'woocommerce-venezuela-suite' ),
			)
		);

		$this->add_control(
			'show_timestamp',
			array(
				'label' => __( 'Mostrar Timestamp', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Sí', 'woocommerce-venezuela-suite' ),
				'label_off' => __( 'No', 'woocommerce-venezuela-suite' ),
				'return_value' => 'yes',
				'default' => 'yes',
			)
		);

		$this->add_control(
			'auto_refresh',
			array(
				'label' => __( 'Actualización Automática', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Sí', 'woocommerce-venezuela-suite' ),
				'label_off' => __( 'No', 'woocommerce-venezuela-suite' ),
				'return_value' => 'yes',
				'default' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Renderiza el widget.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$bcv_core = Woocommerce_Venezuela_Suite_BCV_Integration_Core::get_instance();
		$current_rate = $bcv_core->get_current_rate();
		
		?>
		<div class="wvs-bcv-rate-widget">
			<h3><?php echo esc_html( $settings['title'] ); ?></h3>
			<div class="rate-display">
				<span class="rate-value"><?php echo esc_html( number_format( $current_rate, 2, ',', '.' ) ); ?></span>
				<span class="rate-currency">VES/USD</span>
			</div>
			<?php if ( $settings['show_timestamp'] === 'yes' ) : ?>
				<div class="rate-timestamp">
					<?php echo esc_html( current_time( 'd/m/Y H:i' ) ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}

/**
 * Widget de historial de tasas para Elementor.
 */
class Woocommerce_Venezuela_Suite_Rate_History_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Obtiene nombre del widget.
	 *
	 * @return string Nombre del widget.
	 */
	public function get_name() {
		return 'wvs-rate-history';
	}

	/**
	 * Obtiene título del widget.
	 *
	 * @return string Título del widget.
	 */
	public function get_title() {
		return __( 'Rate History', 'woocommerce-venezuela-suite' );
	}

	/**
	 * Obtiene icono del widget.
	 *
	 * @return string Icono del widget.
	 */
	public function get_icon() {
		return 'eicon-chart-line';
	}

	/**
	 * Obtiene categorías del widget.
	 *
	 * @return array Categorías del widget.
	 */
	public function get_categories() {
		return array( 'woocommerce-venezuela-suite', 'woocommerce' );
	}

	/**
	 * Registra controles del widget.
	 *
	 * @return void
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Contenido', 'woocommerce-venezuela-suite' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label' => __( 'Título', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Historial de Tasas BCV', 'woocommerce-venezuela-suite' ),
			)
		);

		$this->add_control(
			'days',
			array(
				'label' => __( 'Días a Mostrar', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 7,
				'min' => 1,
				'max' => 30,
			)
		);

		$this->add_control(
			'chart_type',
			array(
				'label' => __( 'Tipo de Gráfico', 'woocommerce-venezuela-suite' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'line',
				'options' => array(
					'line' => __( 'Línea', 'woocommerce-venezuela-suite' ),
					'bar' => __( 'Barras', 'woocommerce-venezuela-suite' ),
					'table' => __( 'Tabla', 'woocommerce-venezuela-suite' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Renderiza el widget.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		?>
		<div class="wvs-rate-history-widget">
			<h3><?php echo esc_html( $settings['title'] ); ?></h3>
			<div class="rate-history-container" data-days="<?php echo esc_attr( $settings['days'] ); ?>" data-type="<?php echo esc_attr( $settings['chart_type'] ); ?>">
				<div class="loading"><?php esc_html_e( 'Cargando historial...', 'woocommerce-venezuela-suite' ); ?></div>
			</div>
		</div>
		<?php
	}
}
