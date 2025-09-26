<?php
/**
 * Currency Modules Manager
 * Manages activation/deactivation of currency converter modules
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WVP_Currency_Modules_Manager {
	
	private static $instance = null;
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		$this->init_hooks();
	}
	
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 20 );
		add_action( 'admin_post_wvp_save_currency_modules', array( $this, 'save_currency_modules' ) );
	}
	
	/**
	 * Add admin menu for currency modules
	 */
	public function add_admin_menu() {
		// Verificar que el menú padre existe
		global $menu;
		$parent_exists = false;
		
		foreach ( $menu as $menu_item ) {
			if ( isset( $menu_item[2] ) && $menu_item[2] === 'wvp-dashboard' ) {
				$parent_exists = true;
				break;
			}
		}
		
		if ( $parent_exists ) {
			add_submenu_page(
				'wvp-dashboard',
				'Conversores de Moneda',
				'Conversores de Moneda',
				'manage_options',
				'wvp-currency-modules',
				array( $this, 'admin_page' )
			);
		} else {
			// Si el menú padre no existe, crear como menú independiente
			add_menu_page(
				'Conversores de Moneda',
				'Conversores de Moneda',
				'manage_options',
				'wvp-currency-modules',
				array( $this, 'admin_page' ),
				'dashicons-money-alt',
				57
			);
		}
	}
	
	/**
	 * Admin page for currency modules configuration
	 */
	public function admin_page() {
		$active_modules = get_option( 'wvp_currency_modules', array() );
		
		?>
		<div class="wrap">
			<h1>Configuración de Conversores de Moneda</h1>
			<p>Activa o desactiva los diferentes módulos de conversión de moneda según tus necesidades.</p>
			
			<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
				<?php wp_nonce_field( 'wvp_currency_modules_nonce', 'wvp_currency_modules_nonce' ); ?>
				<input type="hidden" name="action" value="wvp_save_currency_modules">
				
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">Conversor Visual</th>
							<td>
								<label>
									<input type="checkbox" name="wvp_currency_modules[]" value="visual_converter" 
										   <?php checked( in_array( 'visual_converter', $active_modules ) ); ?>>
									Mostrar conversor visual con cajas USD/VES en páginas de productos
								</label>
								<p class="description">
									Muestra un conversor visual atractivo con cajas azul (USD) y verde (VES) 
									separadas por un signo igual (=).
								</p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">Conversor con Botones</th>
							<td>
								<label>
									<input type="checkbox" name="wvp_currency_modules[]" value="button_converter" 
										   <?php checked( in_array( 'button_converter', $active_modules ) ); ?>>
									Mostrar conversor con botones USD/VES en páginas de productos
								</label>
								<p class="description">
									Muestra un conversor con botones para alternar entre USD y VES, 
									incluyendo funcionalidad AJAX.
								</p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">Conversiones en Carrito</th>
							<td>
								<label>
									<input type="checkbox" name="wvp_currency_modules[]" value="cart_converter" 
										   <?php checked( in_array( 'cart_converter', $active_modules ) ); ?>>
									Mostrar conversiones VES en carrito y checkout
								</label>
								<p class="description">
									Agrega conversiones automáticas a VES en las páginas de carrito y checkout.
								</p>
							</td>
						</tr>
					</tbody>
				</table>
				
				<?php submit_button( 'Guardar Configuración' ); ?>
			</form>
			
			<div class="wvp-modules-info" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 5px;">
				<h3>Información de los Módulos</h3>
				
				<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 15px;">
					<div style="padding: 15px; background: white; border-radius: 5px; border-left: 4px solid #0073aa;">
						<h4 style="margin-top: 0; color: #0073aa;">Conversor Visual</h4>
						<p style="margin-bottom: 0; font-size: 14px;">
							Ideal para mostrar conversiones de forma clara y atractiva. 
							Perfecto para productos individuales.
						</p>
					</div>
					
					<div style="padding: 15px; background: white; border-radius: 5px; border-left: 4px solid #27ae60;">
						<h4 style="margin-top: 0; color: #27ae60;">Conversor con Botones</h4>
						<p style="margin-bottom: 0; font-size: 14px;">
							Permite a los usuarios alternar entre monedas interactivamente. 
							Incluye funcionalidad AJAX.
						</p>
					</div>
					
					<div style="padding: 15px; background: white; border-radius: 5px; border-left: 4px solid #e74c3c;">
						<h4 style="margin-top: 0; color: #e74c3c;">Conversiones en Carrito</h4>
						<p style="margin-bottom: 0; font-size: 14px;">
							Agrega conversiones automáticas en carrito y checkout. 
							Esencial para el proceso de compra.
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Save currency modules configuration
	 */
	public function save_currency_modules() {
		if ( ! wp_verify_nonce( $_POST['wvp_currency_modules_nonce'], 'wvp_currency_modules_nonce' ) ) {
			wp_die( 'Security check failed' );
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}
		
		$modules = isset( $_POST['wvp_currency_modules'] ) ? $_POST['wvp_currency_modules'] : array();
		update_option( 'wvp_currency_modules', $modules );
		
		wp_redirect( add_query_arg( 'updated', '1', admin_url( 'admin.php?page=wvp-currency-modules' ) ) );
		exit;
	}
}
