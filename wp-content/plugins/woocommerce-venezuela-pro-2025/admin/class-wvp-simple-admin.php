<?php
/**
 * Simple Admin functionality for WooCommerce Venezuela Pro 2025
 *
 * @package    Woocommerce_Venezuela_Pro_2025
 * @subpackage Woocommerce_Venezuela_Pro_2025/admin
 */

class WVP_Simple_Admin {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style( 
            $this->plugin_name . '-admin', 
            plugin_dir_url( __FILE__ ) . 'css/wvp-simple-admin.css', 
            array(), 
            $this->version, 
            'all' 
        );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script( 
            $this->plugin_name . '-admin', 
            plugin_dir_url( __FILE__ ) . 'js/wvp-simple-admin.js', 
            array( 'jquery' ), 
            $this->version, 
            false 
        );
    }

    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        add_menu_page(
            'WooCommerce Venezuela Pro 2025',
            'WVP 2025',
            'manage_woocommerce',
            'wvp-dashboard',
            array( $this, 'display_dashboard_page' ),
            'dashicons-admin-site-alt3',
            56
        );

        add_submenu_page(
            'wvp-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_woocommerce',
            'wvp-dashboard',
            array( $this, 'display_dashboard_page' )
        );

        add_submenu_page(
            'wvp-dashboard',
            'Configuración',
            'Configuración',
            'manage_woocommerce',
            'wvp-settings',
            array( $this, 'display_settings_page' )
        );

        add_submenu_page(
            'wvp-dashboard',
            'SENIAT',
            'SENIAT',
            'manage_woocommerce',
            'wvp-seniat',
            array( $this, 'display_seniat_page' )
        );
    }

    /**
     * Display dashboard page.
     */
    public function display_dashboard_page() {
        ?>
        <div class="wrap">
            <h1>WooCommerce Venezuela Pro 2025</h1>
            
            <div class="wvp-dashboard">
                <div class="wvp-card">
                    <h2>Estado del Plugin</h2>
                    <p>WooCommerce Venezuela Pro 2025 está activo y funcionando correctamente.</p>
                    
                    <h3>Información del Sistema</h3>
                    <ul>
                        <li><strong>Versión del Plugin:</strong> <?php echo esc_html( $this->version ); ?></li>
                        <li><strong>WooCommerce:</strong> 
                            <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                                <span style="color: green;">Activo</span>
                            <?php else : ?>
                                <span style="color: red;">No Activo</span>
                            <?php endif; ?>
                        </li>
                        <li><strong>BCV Dólar Tracker:</strong>
                            <?php if ( class_exists( 'BCV_Dolar_Tracker' ) ) : ?>
                                <span style="color: green;">Disponible</span>
                            <?php else : ?>
                                <span style="color: orange;">No Disponible</span>
                            <?php endif; ?>
                        </li>
                    </ul>

                    <?php if ( class_exists( 'BCV_Dolar_Tracker' ) ) : ?>
                        <h3>Tasa BCV Actual</h3>
                        <?php
                        $current_rate = get_option( 'bcv_dolar_rate', 0 );
                        if ( $current_rate > 0 ) :
                        ?>
                            <p><strong>Tasa actual:</strong> <?php echo number_format( $current_rate, 2, ',', '.' ); ?> VES por USD</p>
                        <?php else : ?>
                            <p><strong>Tasa actual:</strong> No disponible</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="wvp-card">
                    <h2>Funcionalidades Activas</h2>
                    <ul>
                        <li>✅ Conversión de moneda USD/VES</li>
                        <li>✅ Cálculo de impuestos venezolanos (IVA, IGTF)</li>
                        <li>✅ Métodos de pago locales (Pago Móvil)</li>
                        <li>✅ Métodos de envío venezolanos</li>
                        <li>✅ Sistema de exportación SENIAT</li>
                    </ul>
                </div>

                <div class="wvp-card">
                    <h2>Acciones Rápidas</h2>
                    <p>
                        <a href="<?php echo admin_url( 'admin.php?page=wvp-settings' ); ?>" class="button button-primary">
                            Configurar Plugin
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=wvp-seniat' ); ?>" class="button">
                            Exportar SENIAT
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Display settings page.
     */
    public function display_settings_page() {
        // Handle form submission
        if ( isset( $_POST['submit'] ) && wp_verify_nonce( $_POST['wvp_settings_nonce'], 'wvp_settings' ) ) {
            update_option( 'wvp_usd_rate', sanitize_text_field( $_POST['wvp_usd_rate'] ) );
            update_option( 'wvp_iva_rate', sanitize_text_field( $_POST['wvp_iva_rate'] ) );
            update_option( 'wvp_igtf_rate', sanitize_text_field( $_POST['wvp_igtf_rate'] ) );
            echo '<div class="notice notice-success"><p>Configuración guardada correctamente.</p></div>';
        }

        $usd_rate = get_option( 'wvp_usd_rate', '36.50' );
        $iva_rate = get_option( 'wvp_iva_rate', '16' );
        $igtf_rate = get_option( 'wvp_igtf_rate', '3' );
        ?>
        <div class="wrap">
            <h1>Configuración WVP 2025</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field( 'wvp_settings', 'wvp_settings_nonce' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wvp_usd_rate">Tasa USD Base</label>
                        </th>
                        <td>
                            <input type="number" step="0.01" id="wvp_usd_rate" name="wvp_usd_rate" 
                                   value="<?php echo esc_attr( $usd_rate ); ?>" class="regular-text" />
                            <p class="description">Tasa base del dólar en VES (se actualiza automáticamente con BCV)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wvp_iva_rate">Tasa IVA (%)</label>
                        </th>
                        <td>
                            <input type="number" step="0.01" id="wvp_iva_rate" name="wvp_iva_rate" 
                                   value="<?php echo esc_attr( $iva_rate ); ?>" class="regular-text" />
                            <p class="description">Tasa del Impuesto al Valor Agregado</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wvp_igtf_rate">Tasa IGTF (%)</label>
                        </th>
                        <td>
                            <input type="number" step="0.01" id="wvp_igtf_rate" name="wvp_igtf_rate" 
                                   value="<?php echo esc_attr( $igtf_rate ); ?>" class="regular-text" />
                            <p class="description">Tasa del Impuesto a las Grandes Transacciones Financieras</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button( 'Guardar Configuración' ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Display SENIAT page.
     */
    public function display_seniat_page() {
        ?>
        <div class="wrap">
            <h1>Exportación SENIAT</h1>
            
            <div class="wvp-seniat">
                <div class="wvp-card">
                    <h2>Libro de Ventas</h2>
                    <p>Exportar datos de ventas para cumplimiento fiscal SENIAT.</p>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field( 'wvp_seniat_export', 'wvp_seniat_nonce' ); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="start_date">Fecha Inicio</label>
                                </th>
                                <td>
                                    <input type="date" id="start_date" name="start_date" 
                                           value="<?php echo date( 'Y-m-01' ); ?>" class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="end_date">Fecha Fin</label>
                                </th>
                                <td>
                                    <input type="date" id="end_date" name="end_date" 
                                           value="<?php echo date( 'Y-m-d' ); ?>" class="regular-text" />
                                </td>
                            </tr>
                        </table>
                        
                        <p>
                            <button type="submit" name="export_iva" class="button button-primary">
                                Exportar IVA
                            </button>
                            <button type="submit" name="export_igtf" class="button">
                                Exportar IGTF
                            </button>
                            <button type="submit" name="export_invoices" class="button">
                                Exportar Facturas
                            </button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}
