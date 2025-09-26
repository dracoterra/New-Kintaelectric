<?php
/**
 * Página de Configuración de Conversores de Moneda
 * Interfaz de administración con control granular y personalización de apariencia
 * 
 * @package WooCommerce_Venezuela_Pro_2025
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Obtener configuraciones actuales
$settings = WVP_Display_Settings::get_settings();
$currency_manager = WVP_Currency_Manager::get_instance();
$rate_info = $currency_manager->get_rate_info();
?>

<div class="wrap wvp-currency-settings">
	<h1><?php _e( 'Configuración de Conversores de Moneda', 'woocommerce-venezuela-pro-2025' ); ?></h1>
	
	<div class="wvp-settings-header">
		<p class="description">
			<?php _e( 'Configura dónde y cómo se muestran los conversores de moneda USD ↔ VES en tu tienda.', 'woocommerce-venezuela-pro-2025' ); ?>
		</p>
		
		<div class="wvp-rate-status">
			<strong><?php _e( 'Tasa BCV Actual:', 'woocommerce-venezuela-pro-2025' ); ?></strong>
			<span class="wvp-rate-value <?php echo $rate_info['is_available'] ? 'available' : 'unavailable'; ?>">
				<?php echo number_format( $rate_info['rate'], 2, ',', '.' ); ?> Bs./USD
			</span>
			<small class="wvp-rate-source">
				(<?php echo $rate_info['source']; ?>
				<?php if ( ! empty( $rate_info['last_update'] ) ): ?>
					- <?php echo date( 'd/m/Y H:i', strtotime( $rate_info['last_update'] ) ); ?>
				<?php endif; ?>)
			</small>
		</div>
	</div>
	
	<form id="wvp-currency-settings-form" method="post">
		<?php wp_nonce_field( 'wvp_currency_settings_nonce', 'wvp_currency_settings_nonce' ); ?>
		
		<div class="wvp-settings-container">
			
			<!-- Sidebar con Tabs -->
			<div class="wvp-settings-sidebar">
				<ul class="wvp-settings-tabs">
					<li><a href="#general" class="active"><?php _e( 'General', 'woocommerce-venezuela-pro-2025' ); ?></a></li>
					<li><a href="#locations"><?php _e( 'Ubicaciones', 'woocommerce-venezuela-pro-2025' ); ?></a></li>
					<li><a href="#appearance"><?php _e( 'Apariencia', 'woocommerce-venezuela-pro-2025' ); ?></a></li>
					<li><a href="#styling"><?php _e( 'Estilos', 'woocommerce-venezuela-pro-2025' ); ?></a></li>
					<li><a href="#responsive"><?php _e( 'Responsivo', 'woocommerce-venezuela-pro-2025' ); ?></a></li>
					<li><a href="#advanced"><?php _e( 'Avanzado', 'woocommerce-venezuela-pro-2025' ); ?></a></li>
				</ul>
				
				<div class="wvp-settings-actions">
					<button type="submit" class="button-primary wvp-save-settings">
						<?php _e( 'Guardar Configuración', 'woocommerce-venezuela-pro-2025' ); ?>
					</button>
					<button type="button" class="button wvp-reset-settings">
						<?php _e( 'Restaurar Defaults', 'woocommerce-venezuela-pro-2025' ); ?>
					</button>
					<button type="button" class="button wvp-preview-settings">
						<?php _e( 'Vista Previa', 'woocommerce-venezuela-pro-2025' ); ?>
					</button>
				</div>
			</div>
			
			<!-- Contenido Principal -->
			<div class="wvp-settings-content">
				
				<!-- Tab: General -->
				<div id="general" class="wvp-settings-panel active">
					<h2><?php _e( 'Configuración General', 'woocommerce-venezuela-pro-2025' ); ?></h2>
					
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e( 'Moneda Base', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<select name="settings[general][base_currency]">
									<option value="USD" <?php selected( WVP_Display_Settings::get_setting( 'general', 'base_currency', 'USD' ), 'USD' ); ?>>
										<?php _e( 'Dólar Americano (USD)', 'woocommerce-venezuela-pro-2025' ); ?>
									</option>
									<option value="VES" <?php selected( WVP_Display_Settings::get_setting( 'general', 'base_currency', 'USD' ), 'VES' ); ?>>
										<?php _e( 'Bolívar Venezolano (VES)', 'woocommerce-venezuela-pro-2025' ); ?>
									</option>
								</select>
								<p class="description"><?php _e( 'Moneda principal para tus productos.', 'woocommerce-venezuela-pro-2025' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Moneda de Visualización', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<select name="settings[general][display_currency]">
									<option value="USD" <?php selected( WVP_Display_Settings::get_setting( 'general', 'display_currency', 'VES' ), 'USD' ); ?>>
										<?php _e( 'Dólar Americano (USD)', 'woocommerce-venezuela-pro-2025' ); ?>
									</option>
									<option value="VES" <?php selected( WVP_Display_Settings::get_setting( 'general', 'display_currency', 'VES' ), 'VES' ); ?>>
										<?php _e( 'Bolívar Venezolano (VES)', 'woocommerce-venezuela-pro-2025' ); ?>
									</option>
								</select>
								<p class="description"><?php _e( 'Moneda que verán tus clientes por defecto.', 'woocommerce-venezuela-pro-2025' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Tasa BCV Manual', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<input type="number" step="0.01" min="0" name="settings[general][manual_rate]" 
									   value="<?php echo esc_attr( WVP_Display_Settings::get_setting( 'general', 'manual_rate', $rate_info['emergency_rate'] ) ); ?>" 
									   class="regular-text">
								<p class="description">
									<?php _e( 'Tasa de respaldo cuando BCV no esté disponible. Se actualizará automáticamente si BCV está activo.', 'woocommerce-venezuela-pro-2025' ); ?>
								</p>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Actualización Automática', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="settings[general][auto_update]" value="1" 
										   <?php checked( WVP_Display_Settings::get_setting( 'general', 'auto_update', true ) ); ?>>
									<?php _e( 'Actualizar automáticamente desde BCV Dólar Tracker', 'woocommerce-venezuela-pro-2025' ); ?>
								</label>
								<p class="description"><?php _e( 'Requiere el plugin BCV Dólar Tracker activo.', 'woocommerce-venezuela-pro-2025' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
				
				<!-- Tab: Ubicaciones -->
				<div id="locations" class="wvp-settings-panel">
					<h2><?php _e( 'Control de Ubicaciones', 'woocommerce-venezuela-pro-2025' ); ?></h2>
					<p class="description"><?php _e( 'Selecciona dónde quieres mostrar los conversores de moneda.', 'woocommerce-venezuela-pro-2025' ); ?></p>
					
					<div class="wvp-location-grid">
						<?php
						$locations = array(
							'single_product' => __( 'Páginas de Producto Individual', 'woocommerce-venezuela-pro-2025' ),
							'shop_loop' => __( 'Lista de Productos (Shop)', 'woocommerce-venezuela-pro-2025' ),
							'cart' => __( 'Carrito de Compras', 'woocommerce-venezuela-pro-2025' ),
							'checkout' => __( 'Proceso de Checkout', 'woocommerce-venezuela-pro-2025' ),
							'mini_cart' => __( 'Mini Carrito', 'woocommerce-venezuela-pro-2025' ),
							'product_category' => __( 'Páginas de Categoría', 'woocommerce-venezuela-pro-2025' ),
							'search_results' => __( 'Resultados de Búsqueda', 'woocommerce-venezuela-pro-2025' ),
							'widget' => __( 'Widgets y Sidebars', 'woocommerce-venezuela-pro-2025' ),
							'header' => __( 'Header', 'woocommerce-venezuela-pro-2025' ),
							'footer' => __( 'Footer', 'woocommerce-venezuela-pro-2025' )
						);
						
						foreach ( $locations as $location => $label ):
							$checked = WVP_Display_Settings::get_setting( 'display_locations', $location, false );
						?>
						<div class="wvp-location-item">
							<label class="wvp-toggle-label">
								<input type="checkbox" name="settings[display_locations][<?php echo $location; ?>]" value="1" <?php checked( $checked ); ?>>
								<span class="wvp-toggle-slider"></span>
								<span class="wvp-toggle-text"><?php echo $label; ?></span>
							</label>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				
				<!-- Tab: Apariencia -->
				<div id="appearance" class="wvp-settings-panel">
					<h2><?php _e( 'Configuración de Apariencia', 'woocommerce-venezuela-pro-2025' ); ?></h2>
					
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e( 'Estilo de Conversor', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<div class="wvp-style-options">
									<?php
									$styles = array(
										'buttons' => __( 'Botones', 'woocommerce-venezuela-pro-2025' ),
										'dropdown' => __( 'Dropdown', 'woocommerce-venezuela-pro-2025' ),
										'toggle' => __( 'Toggle Switch', 'woocommerce-venezuela-pro-2025' ),
										'inline' => __( 'Inline', 'woocommerce-venezuela-pro-2025' )
									);
									
									$current_style = WVP_Display_Settings::get_setting( 'appearance', 'style', 'buttons' );
									
									foreach ( $styles as $style => $label ):
									?>
									<label class="wvp-style-option">
										<input type="radio" name="settings[appearance][style]" value="<?php echo $style; ?>" <?php checked( $current_style, $style ); ?>>
										<span class="wvp-style-preview wvp-style-<?php echo $style; ?>">
											<span class="wvp-style-name"><?php echo $label; ?></span>
										</span>
									</label>
									<?php endforeach; ?>
								</div>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Tema Visual', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<select name="settings[appearance][theme]">
									<?php
									$themes = array(
										'default' => __( 'Por Defecto', 'woocommerce-venezuela-pro-2025' ),
										'minimal' => __( 'Minimalista', 'woocommerce-venezuela-pro-2025' ),
										'modern' => __( 'Moderno', 'woocommerce-venezuela-pro-2025' ),
										'elegant' => __( 'Elegante', 'woocommerce-venezuela-pro-2025' )
									);
									
									$current_theme = WVP_Display_Settings::get_setting( 'appearance', 'theme', 'default' );
									
									foreach ( $themes as $theme => $label ):
									?>
									<option value="<?php echo $theme; ?>" <?php selected( $current_theme, $theme ); ?>><?php echo $label; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Tamaño', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<select name="settings[appearance][size]">
									<?php
									$sizes = array(
										'small' => __( 'Pequeño', 'woocommerce-venezuela-pro-2025' ),
										'medium' => __( 'Mediano', 'woocommerce-venezuela-pro-2025' ),
										'large' => __( 'Grande', 'woocommerce-venezuela-pro-2025' )
									);
									
									$current_size = WVP_Display_Settings::get_setting( 'appearance', 'size', 'medium' );
									
									foreach ( $sizes as $size => $label ):
									?>
									<option value="<?php echo $size; ?>" <?php selected( $current_size, $size ); ?>><?php echo $label; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Posición en Productos', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<select name="settings[appearance][position]">
									<?php
									$positions = array(
										'before_price' => __( 'Antes del precio', 'woocommerce-venezuela-pro-2025' ),
										'after_price' => __( 'Después del precio', 'woocommerce-venezuela-pro-2025' ),
										'replace_price' => __( 'Reemplazar precio', 'woocommerce-venezuela-pro-2025' )
									);
									
									$current_position = WVP_Display_Settings::get_setting( 'appearance', 'position', 'after_price' );
									
									foreach ( $positions as $position => $label ):
									?>
									<option value="<?php echo $position; ?>" <?php selected( $current_position, $position ); ?>><?php echo $label; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Animación', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<select name="settings[appearance][animation]">
									<?php
									$animations = array(
										'none' => __( 'Sin animación', 'woocommerce-venezuela-pro-2025' ),
										'fade' => __( 'Fade', 'woocommerce-venezuela-pro-2025' ),
										'slide' => __( 'Slide', 'woocommerce-venezuela-pro-2025' ),
										'bounce' => __( 'Bounce', 'woocommerce-venezuela-pro-2025' )
									);
									
									$current_animation = WVP_Display_Settings::get_setting( 'appearance', 'animation', 'fade' );
									
									foreach ( $animations as $animation => $label ):
									?>
									<option value="<?php echo $animation; ?>" <?php selected( $current_animation, $animation ); ?>><?php echo $label; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Opciones de Visualización', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<fieldset>
									<label>
										<input type="checkbox" name="settings[appearance][show_labels]" value="1" 
											   <?php checked( WVP_Display_Settings::get_setting( 'appearance', 'show_labels', true ) ); ?>>
										<?php _e( 'Mostrar etiquetas USD/VES', 'woocommerce-venezuela-pro-2025' ); ?>
									</label><br>
									
									<label>
										<input type="checkbox" name="settings[appearance][show_symbols]" value="1" 
											   <?php checked( WVP_Display_Settings::get_setting( 'appearance', 'show_symbols', true ) ); ?>>
										<?php _e( 'Mostrar símbolos de moneda ($, Bs.)', 'woocommerce-venezuela-pro-2025' ); ?>
									</label><br>
									
									<label>
										<input type="checkbox" name="settings[appearance][show_rate]" value="1" 
											   <?php checked( WVP_Display_Settings::get_setting( 'appearance', 'show_rate', false ) ); ?>>
										<?php _e( 'Mostrar tasa BCV', 'woocommerce-venezuela-pro-2025' ); ?>
									</label><br>
									
									<label>
										<input type="checkbox" name="settings[appearance][show_last_update]" value="1" 
											   <?php checked( WVP_Display_Settings::get_setting( 'appearance', 'show_last_update', false ) ); ?>>
										<?php _e( 'Mostrar fecha de última actualización', 'woocommerce-venezuela-pro-2025' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>
					</table>
				</div>
				
				<!-- Tab: Estilos -->
				<div id="styling" class="wvp-settings-panel">
					<h2><?php _e( 'Personalización de Estilos', 'woocommerce-venezuela-pro-2025' ); ?></h2>
					
					<div class="wvp-color-grid">
						<?php
						$colors = array(
							'primary_color' => __( 'Color Primario', 'woocommerce-venezuela-pro-2025' ),
							'secondary_color' => __( 'Color Secundario', 'woocommerce-venezuela-pro-2025' ),
							'text_color' => __( 'Color de Texto', 'woocommerce-venezuela-pro-2025' ),
							'background_color' => __( 'Color de Fondo', 'woocommerce-venezuela-pro-2025' ),
							'border_color' => __( 'Color de Borde', 'woocommerce-venezuela-pro-2025' ),
							'hover_color' => __( 'Color Hover', 'woocommerce-venezuela-pro-2025' ),
							'active_color' => __( 'Color Activo', 'woocommerce-venezuela-pro-2025' )
						);
						
						foreach ( $colors as $color_key => $color_label ):
							$color_value = WVP_Display_Settings::get_setting( 'styling', $color_key, '#0073aa' );
						?>
						<div class="wvp-color-field">
							<label><?php echo $color_label; ?></label>
							<input type="color" name="settings[styling][<?php echo $color_key; ?>]" value="<?php echo esc_attr( $color_value ); ?>" class="wvp-color-picker">
						</div>
						<?php endforeach; ?>
					</div>
					
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e( 'Border Radius', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<input type="text" name="settings[styling][border_radius]" 
									   value="<?php echo esc_attr( WVP_Display_Settings::get_setting( 'styling', 'border_radius', '4px' ) ); ?>" 
									   class="small-text">
								<p class="description"><?php _e( 'Ej: 4px, 0.5rem, 50%', 'woocommerce-venezuela-pro-2025' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Tamaño de Fuente', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<input type="text" name="settings[styling][font_size]" 
									   value="<?php echo esc_attr( WVP_Display_Settings::get_setting( 'styling', 'font_size', '14px' ) ); ?>" 
									   class="small-text">
								<p class="description"><?php _e( 'Ej: 14px, 1rem, 1.2em', 'woocommerce-venezuela-pro-2025' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Peso de Fuente', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<select name="settings[styling][font_weight]">
									<?php
									$font_weights = array(
										'normal' => __( 'Normal', 'woocommerce-venezuela-pro-2025' ),
										'bold' => __( 'Negrita', 'woocommerce-venezuela-pro-2025' ),
										'lighter' => __( 'Más ligera', 'woocommerce-venezuela-pro-2025' ),
										'bolder' => __( 'Más negrita', 'woocommerce-venezuela-pro-2025' )
									);
									
									$current_weight = WVP_Display_Settings::get_setting( 'styling', 'font_weight', 'normal' );
									
									foreach ( $font_weights as $weight => $label ):
									?>
									<option value="<?php echo $weight; ?>" <?php selected( $current_weight, $weight ); ?>><?php echo $label; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'CSS Personalizado', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<textarea name="settings[styling][custom_css]" rows="10" cols="50" class="large-text code"><?php echo esc_textarea( WVP_Display_Settings::get_setting( 'styling', 'custom_css', '' ) ); ?></textarea>
								<p class="description"><?php _e( 'CSS adicional para personalizar la apariencia de los conversores.', 'woocommerce-venezuela-pro-2025' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
				
				<!-- Tab: Responsivo -->
				<div id="responsive" class="wvp-settings-panel">
					<h2><?php _e( 'Configuración Responsiva', 'woocommerce-venezuela-pro-2025' ); ?></h2>
					
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e( 'Estilo en Móvil', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<select name="settings[responsive][mobile_style]">
									<?php
									$mobile_styles = array(
										'compact' => __( 'Compacto', 'woocommerce-venezuela-pro-2025' ),
										'full' => __( 'Completo', 'woocommerce-venezuela-pro-2025' ),
										'hidden' => __( 'Oculto', 'woocommerce-venezuela-pro-2025' )
									);
									
									$current_mobile = WVP_Display_Settings::get_setting( 'responsive', 'mobile_style', 'compact' );
									
									foreach ( $mobile_styles as $style => $label ):
									?>
									<option value="<?php echo $style; ?>" <?php selected( $current_mobile, $style ); ?>><?php echo $label; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Breakpoint Móvil', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<input type="text" name="settings[responsive][breakpoint_mobile]" 
									   value="<?php echo esc_attr( WVP_Display_Settings::get_setting( 'responsive', 'breakpoint_mobile', '768px' ) ); ?>" 
									   class="small-text">
								<p class="description"><?php _e( 'Ancho máximo para dispositivos móviles.', 'woocommerce-venezuela-pro-2025' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Breakpoint Tablet', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<input type="text" name="settings[responsive][breakpoint_tablet]" 
									   value="<?php echo esc_attr( WVP_Display_Settings::get_setting( 'responsive', 'breakpoint_tablet', '1024px' ) ); ?>" 
									   class="small-text">
								<p class="description"><?php _e( 'Ancho máximo para tablets.', 'woocommerce-venezuela-pro-2025' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
				
				<!-- Tab: Avanzado -->
				<div id="advanced" class="wvp-settings-panel">
					<h2><?php _e( 'Configuración Avanzada', 'woocommerce-venezuela-pro-2025' ); ?></h2>
					
					<table class="form-table">
						<tr>
							<th scope="row"><?php _e( 'Duración del Cache', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<input type="number" min="60" max="86400" name="settings[advanced][cache_duration]" 
									   value="<?php echo esc_attr( WVP_Display_Settings::get_setting( 'advanced', 'cache_duration', 3600 ) ); ?>" 
									   class="small-text">
								<span><?php _e( 'segundos', 'woocommerce-venezuela-pro-2025' ); ?></span>
								<p class="description"><?php _e( 'Tiempo que se mantienen en cache las conversiones (60-86400 segundos).', 'woocommerce-venezuela-pro-2025' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Optimizaciones', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<fieldset>
									<label>
										<input type="checkbox" name="settings[advanced][lazy_load]" value="1" 
											   <?php checked( WVP_Display_Settings::get_setting( 'advanced', 'lazy_load', true ) ); ?>>
										<?php _e( 'Carga perezosa de scripts', 'woocommerce-venezuela-pro-2025' ); ?>
									</label><br>
									
									<label>
										<input type="checkbox" name="settings[advanced][minify_css]" value="1" 
											   <?php checked( WVP_Display_Settings::get_setting( 'advanced', 'minify_css', true ) ); ?>>
										<?php _e( 'Minificar CSS en producción', 'woocommerce-venezuela-pro-2025' ); ?>
									</label><br>
									
									<label>
										<input type="checkbox" name="settings[advanced][combine_scripts]" value="1" 
											   <?php checked( WVP_Display_Settings::get_setting( 'advanced', 'combine_scripts', true ) ); ?>>
										<?php _e( 'Combinar scripts para mejor rendimiento', 'woocommerce-venezuela-pro-2025' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php _e( 'Modo Debug', 'woocommerce-venezuela-pro-2025' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="settings[advanced][debug_mode]" value="1" 
										   <?php checked( WVP_Display_Settings::get_setting( 'advanced', 'debug_mode', false ) ); ?>>
									<?php _e( 'Activar modo debug (solo para desarrollo)', 'woocommerce-venezuela-pro-2025' ); ?>
								</label>
								<p class="description"><?php _e( 'Muestra información adicional en la consola del navegador.', 'woocommerce-venezuela-pro-2025' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
				
			</div>
		</div>
		
		<!-- Vista Previa -->
		<div id="wvp-preview-modal" class="wvp-modal" style="display: none;">
			<div class="wvp-modal-content">
				<div class="wvp-modal-header">
					<h3><?php _e( 'Vista Previa del Conversor', 'woocommerce-venezuela-pro-2025' ); ?></h3>
					<button type="button" class="wvp-modal-close">&times;</button>
				</div>
				<div class="wvp-modal-body">
					<div id="wvp-preview-container">
						<!-- La vista previa se cargará aquí vía AJAX -->
					</div>
				</div>
			</div>
		</div>
		
	</form>
</div>

<script>
jQuery(document).ready(function($) {
	// Manejo de tabs
	$('.wvp-settings-tabs a').on('click', function(e) {
		e.preventDefault();
		var target = $(this).attr('href');
		
		$('.wvp-settings-tabs a').removeClass('active');
		$(this).addClass('active');
		
		$('.wvp-settings-panel').removeClass('active');
		$(target).addClass('active');
	});
	
	// Guardar configuración
	$('.wvp-save-settings').on('click', function(e) {
		e.preventDefault();
		
		var formData = $('#wvp-currency-settings-form').serialize();
		
		$.post(ajaxurl, {
			action: 'wvp_save_currency_settings',
			data: formData,
			nonce: '<?php echo wp_create_nonce( 'wvp_currency_settings_ajax' ); ?>'
		}, function(response) {
			if (response.success) {
				alert('<?php _e( 'Configuración guardada correctamente', 'woocommerce-venezuela-pro-2025' ); ?>');
			} else {
				alert('<?php _e( 'Error al guardar la configuración', 'woocommerce-venezuela-pro-2025' ); ?>');
			}
		});
	});
	
	// Restaurar defaults
	$('.wvp-reset-settings').on('click', function(e) {
		e.preventDefault();
		
		if (confirm('<?php _e( '¿Estás seguro de que quieres restaurar la configuración por defecto?', 'woocommerce-venezuela-pro-2025' ); ?>')) {
			$.post(ajaxurl, {
				action: 'wvp_reset_currency_settings',
				nonce: '<?php echo wp_create_nonce( 'wvp_currency_settings_ajax' ); ?>'
			}, function(response) {
				if (response.success) {
					location.reload();
				}
			});
		}
	});
	
	// Vista previa
	$('.wvp-preview-settings').on('click', function(e) {
		e.preventDefault();
		$('#wvp-preview-modal').show();
		
		// Cargar vista previa
		var formData = $('#wvp-currency-settings-form').serialize();
		
		$.post(ajaxurl, {
			action: 'wvp_preview_currency_converter',
			data: formData,
			nonce: '<?php echo wp_create_nonce( 'wvp_currency_settings_ajax' ); ?>'
		}, function(response) {
			if (response.success) {
				$('#wvp-preview-container').html(response.data);
			}
		});
	});
	
	// Cerrar modal
	$('.wvp-modal-close, .wvp-modal').on('click', function(e) {
		if (e.target === this) {
			$('#wvp-preview-modal').hide();
		}
	});
});
</script>
