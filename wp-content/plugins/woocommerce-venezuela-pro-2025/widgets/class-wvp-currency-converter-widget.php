<?php
/**
 * Widget Conversor de Moneda - WooCommerce Venezuela Pro 2025
 * 
 * Widget de WordPress para conversión USD ↔ VES
 * Integrado con el sistema centralizado
 * 
 * @package WooCommerce_Venezuela_Pro_2025
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Currency_Converter_Widget extends WP_Widget {
	
	/**
	 * Gestor de monedas
	 * 
	 * @var WVP_Currency_Manager
	 */
	private $currency_manager;
	
	/**
	 * Constructor del widget
	 */
	public function __construct() {
		$this->currency_manager = WVP_Currency_Manager::get_instance();
		
		parent::__construct(
			'wvp_currency_converter',
			__( 'Conversor de Moneda WVP', 'woocommerce-venezuela-pro-2025' ),
			array(
				'description' => __( 'Widget para convertir entre USD y VES usando tasa BCV', 'woocommerce-venezuela-pro-2025' ),
				'classname' => 'wvp-currency-converter-widget'
			)
		);
		
		// Enqueue assets solo cuando el widget esté activo
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
	}
	
	/**
	 * Mostrar widget en el frontend
	 * 
	 * @param array $args Argumentos del widget
	 * @param array $instance Configuración del widget
	 */
	public function widget( $args, $instance ) {
		// Verificar si los widgets están habilitados en la configuración
		if ( ! WVP_Display_Settings::should_display_in_context( 'widget' ) ) {
			return;
		}
		
		$title = apply_filters( 'widget_title', $instance['title'] ?? '' );
		$from_currency = $instance['from_currency'] ?? 'USD';
		$to_currency = $instance['to_currency'] ?? 'VES';
		$default_amount = floatval( $instance['default_amount'] ?? 1 );
		$style = $instance['style'] ?? 'compact';
		$show_rate = $instance['show_rate'] ?? true;
		
		echo $args['before_widget'];
		
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		$this->display_converter( $from_currency, $to_currency, $default_amount, $style, $show_rate );
		
		echo $args['after_widget'];
	}
	
	/**
	 * Formulario de configuración del widget
	 * 
	 * @param array $instance Configuración actual
	 */
	public function form( $instance ) {
		$title = $instance['title'] ?? __( 'Conversor de Moneda', 'woocommerce-venezuela-pro-2025' );
		$from_currency = $instance['from_currency'] ?? 'USD';
		$to_currency = $instance['to_currency'] ?? 'VES';
		$default_amount = $instance['default_amount'] ?? 1;
		$style = $instance['style'] ?? 'compact';
		$show_rate = $instance['show_rate'] ?? true;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Título:', 'woocommerce-venezuela-pro-2025' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'from_currency' ); ?>"><?php _e( 'Moneda origen:', 'woocommerce-venezuela-pro-2025' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'from_currency' ); ?>" name="<?php echo $this->get_field_name( 'from_currency' ); ?>">
				<option value="USD" <?php selected( $from_currency, 'USD' ); ?>><?php _e( 'Dólar Americano (USD)', 'woocommerce-venezuela-pro-2025' ); ?></option>
				<option value="VES" <?php selected( $from_currency, 'VES' ); ?>><?php _e( 'Bolívar Venezolano (VES)', 'woocommerce-venezuela-pro-2025' ); ?></option>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'to_currency' ); ?>"><?php _e( 'Moneda destino:', 'woocommerce-venezuela-pro-2025' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'to_currency' ); ?>" name="<?php echo $this->get_field_name( 'to_currency' ); ?>">
				<option value="USD" <?php selected( $to_currency, 'USD' ); ?>><?php _e( 'Dólar Americano (USD)', 'woocommerce-venezuela-pro-2025' ); ?></option>
				<option value="VES" <?php selected( $to_currency, 'VES' ); ?>><?php _e( 'Bolívar Venezolano (VES)', 'woocommerce-venezuela-pro-2025' ); ?></option>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'default_amount' ); ?>"><?php _e( 'Cantidad por defecto:', 'woocommerce-venezuela-pro-2025' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'default_amount' ); ?>" name="<?php echo $this->get_field_name( 'default_amount' ); ?>" type="number" step="0.01" min="0" value="<?php echo esc_attr( $default_amount ); ?>">
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e( 'Estilo:', 'woocommerce-venezuela-pro-2025' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
				<option value="compact" <?php selected( $style, 'compact' ); ?>><?php _e( 'Compacto', 'woocommerce-venezuela-pro-2025' ); ?></option>
				<option value="full" <?php selected( $style, 'full' ); ?>><?php _e( 'Completo', 'woocommerce-venezuela-pro-2025' ); ?></option>
				<option value="minimal" <?php selected( $style, 'minimal' ); ?>><?php _e( 'Minimalista', 'woocommerce-venezuela-pro-2025' ); ?></option>
			</select>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_rate ); ?> id="<?php echo $this->get_field_id( 'show_rate' ); ?>" name="<?php echo $this->get_field_name( 'show_rate' ); ?>" value="1">
			<label for="<?php echo $this->get_field_id( 'show_rate' ); ?>"><?php _e( 'Mostrar tasa BCV', 'woocommerce-venezuela-pro-2025' ); ?></label>
		</p>
		<?php
	}
	
	/**
	 * Actualizar configuración del widget
	 * 
	 * @param array $new_instance Nueva configuración
	 * @param array $old_instance Configuración anterior
	 * @return array Configuración actualizada
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['from_currency'] = in_array( $new_instance['from_currency'], array( 'USD', 'VES' ) ) ? $new_instance['from_currency'] : 'USD';
		$instance['to_currency'] = in_array( $new_instance['to_currency'], array( 'USD', 'VES' ) ) ? $new_instance['to_currency'] : 'VES';
		$instance['default_amount'] = ! empty( $new_instance['default_amount'] ) ? floatval( $new_instance['default_amount'] ) : 1;
		$instance['style'] = in_array( $new_instance['style'], array( 'compact', 'full', 'minimal' ) ) ? $new_instance['style'] : 'compact';
		$instance['show_rate'] = ! empty( $new_instance['show_rate'] );
		
		return $instance;
	}
	
	/**
	 * Mostrar conversor
	 * 
	 * @param string $from_currency Moneda origen
	 * @param string $to_currency Moneda destino
	 * @param float $default_amount Cantidad por defecto
	 * @param string $style Estilo del widget
	 * @param bool $show_rate Mostrar tasa BCV
	 */
	private function display_converter( $from_currency, $to_currency, $default_amount, $style, $show_rate ) {
		$rate = $this->currency_manager->get_bcv_rate();
		
		if ( ! $this->currency_manager->is_rate_available() ) {
			echo '<div class="wvp-widget-error">';
			echo '<p>' . __( 'Tasa de cambio no disponible', 'woocommerce-venezuela-pro-2025' ) . '</p>';
			echo '</div>';
			return;
		}
		
		$converted_amount = $this->currency_manager->convert_price( $default_amount, $from_currency, $to_currency );
		$widget_id = 'wvp-widget-' . $this->id;
		
		?>
		<div class="wvp-currency-converter-widget wvp-widget-style-<?php echo esc_attr( $style ); ?>" id="<?php echo esc_attr( $widget_id ); ?>">
			<div class="wvp-converter-form">
				
				<?php if ( $style === 'full' ): ?>
				<div class="wvp-form-row">
					<div class="wvp-form-group">
						<label for="<?php echo $widget_id; ?>-amount"><?php _e( 'Cantidad:', 'woocommerce-venezuela-pro-2025' ); ?></label>
						<input type="number" id="<?php echo $widget_id; ?>-amount" class="wvp-form-control wvp-amount-input" value="<?php echo esc_attr( $default_amount ); ?>" step="0.01" min="0">
					</div>
				</div>
				
				<div class="wvp-form-row">
					<div class="wvp-form-group">
						<label for="<?php echo $widget_id; ?>-from"><?php _e( 'De:', 'woocommerce-venezuela-pro-2025' ); ?></label>
						<select id="<?php echo $widget_id; ?>-from" class="wvp-form-control wvp-from-currency">
							<option value="USD" <?php selected( $from_currency, 'USD' ); ?>><?php _e( 'USD', 'woocommerce-venezuela-pro-2025' ); ?></option>
							<option value="VES" <?php selected( $from_currency, 'VES' ); ?>><?php _e( 'VES', 'woocommerce-venezuela-pro-2025' ); ?></option>
						</select>
					</div>
				</div>
				
				<div class="wvp-form-row">
					<div class="wvp-form-group">
						<label for="<?php echo $widget_id; ?>-to"><?php _e( 'A:', 'woocommerce-venezuela-pro-2025' ); ?></label>
						<select id="<?php echo $widget_id; ?>-to" class="wvp-form-control wvp-to-currency">
							<option value="USD" <?php selected( $to_currency, 'USD' ); ?>><?php _e( 'USD', 'woocommerce-venezuela-pro-2025' ); ?></option>
							<option value="VES" <?php selected( $to_currency, 'VES' ); ?>><?php _e( 'VES', 'woocommerce-venezuela-pro-2025' ); ?></option>
						</select>
					</div>
				</div>
				<?php elseif ( $style === 'compact' ): ?>
				<div class="wvp-compact-converter">
					<div class="wvp-input-group">
						<input type="number" id="<?php echo $widget_id; ?>-amount" class="wvp-amount-input" value="<?php echo esc_attr( $default_amount ); ?>" step="0.01" min="0">
						<select id="<?php echo $widget_id; ?>-from" class="wvp-from-currency">
							<option value="USD" <?php selected( $from_currency, 'USD' ); ?>>USD</option>
							<option value="VES" <?php selected( $from_currency, 'VES' ); ?>>VES</option>
						</select>
					</div>
					<div class="wvp-converter-arrow">→</div>
					<div class="wvp-input-group">
						<span class="wvp-result-amount"><?php echo $this->currency_manager->format_currency( $converted_amount, $to_currency, false ); ?></span>
						<select id="<?php echo $widget_id; ?>-to" class="wvp-to-currency">
							<option value="USD" <?php selected( $to_currency, 'USD' ); ?>>USD</option>
							<option value="VES" <?php selected( $to_currency, 'VES' ); ?>>VES</option>
						</select>
					</div>
				</div>
				<?php else: // minimal ?>
				<div class="wvp-minimal-converter">
					<input type="number" id="<?php echo $widget_id; ?>-amount" class="wvp-amount-input" value="<?php echo esc_attr( $default_amount ); ?>" step="0.01" min="0">
					<span class="wvp-currency-from"><?php echo $from_currency; ?></span>
					<span class="wvp-equals">=</span>
					<span class="wvp-result-amount"><?php echo $this->currency_manager->format_currency( $converted_amount, $to_currency ); ?></span>
				</div>
				<?php endif; ?>
				
				<?php if ( $style !== 'minimal' ): ?>
				<div class="wvp-converter-result">
					<div class="wvp-result-display">
						<strong><?php _e( 'Resultado:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
						<span id="<?php echo $widget_id; ?>-result" class="wvp-result-value">
							<?php echo $this->currency_manager->format_currency( $converted_amount, $to_currency ); ?>
						</span>
					</div>
				</div>
				<?php endif; ?>
				
				<?php if ( $show_rate ): ?>
				<div class="wvp-converter-rate">
					<small>
						<?php _e( 'Tasa BCV:', 'woocommerce-venezuela-pro-2025' ); ?>
						<span class="wvp-rate-value"><?php echo number_format( $rate, 2, ',', '.' ); ?></span>
						Bs./USD
					</small>
				</div>
				<?php endif; ?>
				
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			var widgetId = '<?php echo $widget_id; ?>';
			var rate = <?php echo $rate; ?>;
			
			function convertAmount(amount, from, to) {
				if (from === 'USD' && to === 'VES') {
					return amount * rate;
				} else if (from === 'VES' && to === 'USD') {
					return amount / rate;
				}
				return amount;
			}
			
			function formatCurrency(amount, currency) {
				var formatted = amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
				if (currency === 'USD') {
					return '$' + formatted;
				} else if (currency === 'VES') {
					return 'Bs. ' + formatted;
				}
				return formatted;
			}
			
			function updateResult() {
				var amount = parseFloat($('#' + widgetId + '-amount').val()) || 0;
				var from = $('#' + widgetId + '-from').val() || 'USD';
				var to = $('#' + widgetId + '-to').val() || 'VES';
				
				if (amount >= 0) {
					var converted = convertAmount(amount, from, to);
					var formatted = formatCurrency(converted, to);
					
					$('#' + widgetId + '-result').text(formatted);
					$('#' + widgetId + ' .wvp-result-amount').text(formatted);
				}
			}
			
			// Eventos para actualizar resultado
			$('#' + widgetId + '-amount, #' + widgetId + '-from, #' + widgetId + '-to').on('input change', updateResult);
			
			// Actualización inicial
			updateResult();
		});
		</script>
		<?php
	}
	
	/**
	 * Enqueue assets solo si el widget está activo
	 */
	public function maybe_enqueue_assets() {
		if ( is_active_widget( false, false, $this->id_base ) ) {
			wp_enqueue_style(
				'wvp-currency-widget',
				plugin_dir_url( __FILE__ ) . '../public/css/wvp-currency-widget.css',
				array(),
				WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION
			);
		}
	}
}

// Registrar el widget
function wvp_register_currency_converter_widget() {
	register_widget( 'WVP_Currency_Converter_Widget' );
}
add_action( 'widgets_init', 'wvp_register_currency_converter_widget' );
