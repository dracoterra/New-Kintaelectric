<?php
/**
 * Setup Wizard
 * Quick configuration wizard for Venezuelan stores
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Setup_Wizard {
    
    private static $instance = null;
    private $current_step = 1;
    private $total_steps = 6;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'admin_menu', array( $this, 'add_wizard_menu' ), 50 );
        add_action( 'wp_ajax_wvp_wizard_save_step', array( $this, 'ajax_save_step' ) );
        add_action( 'wp_ajax_wvp_wizard_complete', array( $this, 'ajax_complete_wizard' ) );
        add_action( 'wp_ajax_wvp_wizard_skip', array( $this, 'ajax_skip_wizard' ) );
        
        // Show wizard notice if not completed
        add_action( 'admin_notices', array( $this, 'show_wizard_notice' ) );
    }
    
    /**
     * Add wizard menu
     */
    public function add_wizard_menu() {
        add_submenu_page(
            'wvp-dashboard',
            'Asistente de Configuración',
            'Configuración Rápida',
            'manage_options',
            'wvp-wizard',
            array( $this, 'wizard_page' )
        );
    }
    
    /**
     * Show wizard notice
     */
    public function show_wizard_notice() {
        if ( get_option( 'wvp_wizard_completed' ) ) {
            return;
        }
        
        $screen = get_current_screen();
        if ( $screen && strpos( $screen->id, 'wvp-' ) === 0 ) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <strong>🚀 ¡Bienvenido a WooCommerce Venezuela Pro 2025!</strong><br>
                    Completa la configuración rápida para optimizar tu tienda venezolana.
                    <a href="<?php echo admin_url( 'admin.php?page=wvp-wizard' ); ?>" class="button button-primary">
                        Iniciar Configuración
                    </a>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Wizard page
     */
    public function wizard_page() {
        $current_step = isset( $_GET['step'] ) ? intval( $_GET['step'] ) : 1;
        $this->current_step = max( 1, min( $current_step, $this->total_steps ) );
        
        ?>
        <div class="wrap wvp-wizard">
            <div class="wvp-wizard-header">
                <h1>🚀 Asistente de Configuración - WooCommerce Venezuela Pro 2025</h1>
                <div class="wvp-wizard-progress">
                    <?php $this->render_progress_bar(); ?>
                </div>
            </div>
            
            <div class="wvp-wizard-content">
                <?php $this->render_current_step(); ?>
            </div>
            
            <div class="wvp-wizard-navigation">
                <?php $this->render_navigation(); ?>
            </div>
        </div>
        
        <style>
        .wvp-wizard {
            max-width: 800px;
            margin: 20px auto;
        }
        
        .wvp-wizard-header {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .wvp-wizard-progress {
            margin-top: 15px;
        }
        
        .wvp-progress-bar {
            width: 100%;
            height: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .wvp-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0073aa, #00a0d2);
            transition: width 0.3s ease;
            border-radius: 4px;
        }
        
        .wvp-wizard-content {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
            min-height: 400px;
        }
        
        .wvp-wizard-navigation {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .wvp-step-content h2 {
            color: #0073aa;
            margin-bottom: 20px;
        }
        
        .wvp-form-group {
            margin-bottom: 20px;
        }
        
        .wvp-form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .wvp-form-group input,
        .wvp-form-group select,
        .wvp-form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .wvp-form-group .description {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .wvp-checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .wvp-checkbox-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }
        
        .wvp-checkbox-item input[type="checkbox"] {
            margin-right: 10px;
        }
        
        .wvp-button-group {
            display: flex;
            gap: 10px;
        }
        
        .wvp-button-group .button {
            padding: 8px 16px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('.wvp-wizard-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                var currentStep = <?php echo $this->current_step; ?>;
                
                $.post(ajaxurl, {
                    action: 'wvp_wizard_save_step',
                    step: currentStep,
                    data: formData,
                    nonce: '<?php echo wp_create_nonce( 'wvp_wizard_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        if (currentStep < <?php echo $this->total_steps; ?>) {
                            window.location.href = '<?php echo admin_url( 'admin.php?page=wvp-wizard' ); ?>&step=' + (currentStep + 1);
                        } else {
                            // Complete wizard
                            $.post(ajaxurl, {
                                action: 'wvp_wizard_complete',
                                nonce: '<?php echo wp_create_nonce( 'wvp_wizard_nonce' ); ?>'
                            }, function(response) {
                                if (response.success) {
                                    alert('¡Configuración completada exitosamente!');
                                    window.location.href = '<?php echo admin_url( 'admin.php?page=wvp-dashboard' ); ?>';
                                }
                            });
                        }
                    } else {
                        alert('Error al guardar la configuración');
                    }
                });
            });
            
            $('#wvp-skip-wizard').on('click', function() {
                if (confirm('¿Estás seguro de que quieres omitir la configuración?')) {
                    $.post(ajaxurl, {
                        action: 'wvp_wizard_skip',
                        nonce: '<?php echo wp_create_nonce( 'wvp_wizard_nonce' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            window.location.href = '<?php echo admin_url( 'admin.php?page=wvp-dashboard' ); ?>';
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render progress bar
     */
    private function render_progress_bar() {
        $progress = ( $this->current_step / $this->total_steps ) * 100;
        ?>
        <div class="wvp-progress-bar">
            <div class="wvp-progress-fill" style="width: <?php echo $progress; ?>%"></div>
        </div>
        <p style="text-align: center; margin-top: 10px;">
            Paso <?php echo $this->current_step; ?> de <?php echo $this->total_steps; ?> 
            (<?php echo round( $progress ); ?>% completado)
        </p>
        <?php
    }
    
    /**
     * Render current step
     */
    private function render_current_step() {
        switch ( $this->current_step ) {
            case 1:
                $this->render_step_1();
                break;
            case 2:
                $this->render_step_2();
                break;
            case 3:
                $this->render_step_3();
                break;
            case 4:
                $this->render_step_4();
                break;
            case 5:
                $this->render_step_5();
                break;
            case 6:
                $this->render_step_6();
                break;
        }
    }
    
    /**
     * Step 1: Store Information
     */
    private function render_step_1() {
        ?>
        <div class="wvp-step-content">
            <h2>📋 Información de la Tienda</h2>
            <p>Configure la información básica de su tienda venezolana.</p>
            
            <form class="wvp-wizard-form">
                <div class="wvp-form-group">
                    <label for="store_name">Nombre de la Tienda *</label>
                    <input type="text" id="store_name" name="store_name" value="<?php echo esc_attr( get_option( 'blogname' ) ); ?>" required>
                    <div class="description">El nombre que aparecerá en su tienda online</div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="store_email">Email de Contacto *</label>
                    <input type="email" id="store_email" name="store_email" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" required>
                    <div class="description">Email principal para comunicaciones</div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="store_phone">Teléfono de Contacto</label>
                    <input type="tel" id="store_phone" name="store_phone" placeholder="0412-1234567">
                    <div class="description">Número de teléfono venezolano</div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="store_address">Dirección de la Tienda</label>
                    <textarea id="store_address" name="store_address" rows="3" placeholder="Av. Principal, Centro, Caracas"></textarea>
                    <div class="description">Dirección física de su tienda</div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="store_state">Estado</label>
                    <select id="store_state" name="store_state">
                        <option value="">Seleccione un estado</option>
                        <?php
                        $states = array(
                            'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas',
                            'Bolívar', 'Carabobo', 'Cojedes', 'Delta Amacuro',
                            'Distrito Capital', 'Falcón', 'Guárico', 'Lara', 'Mérida',
                            'Miranda', 'Monagas', 'Nueva Esparta', 'Portuguesa',
                            'Sucre', 'Táchira', 'Trujillo', 'Vargas', 'Yaracuy', 'Zulia'
                        );
                        foreach ( $states as $state ) {
                            echo '<option value="' . esc_attr( $state ) . '">' . esc_html( $state ) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Step 2: Currency Settings
     */
    private function render_step_2() {
        ?>
        <div class="wvp-step-content">
            <h2>💰 Configuración de Moneda</h2>
            <p>Configure las monedas y tipos de cambio para su tienda.</p>
            
            <form class="wvp-wizard-form">
                <div class="wvp-form-group">
                    <label for="base_currency">Moneda Base</label>
                    <select id="base_currency" name="base_currency">
                        <option value="USD">Dólar Americano (USD)</option>
                        <option value="VES">Bolívar Venezolano (VES)</option>
                    </select>
                    <div class="description">Moneda principal para sus productos</div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="display_currency">Moneda de Visualización</label>
                    <select id="display_currency" name="display_currency">
                        <option value="VES">Bolívar Venezolano (VES)</option>
                        <option value="USD">Dólar Americano (USD)</option>
                    </select>
                    <div class="description">Moneda que verán sus clientes</div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="bcv_rate">Tasa BCV Manual</label>
                    <input type="number" id="bcv_rate" name="bcv_rate" step="0.01" placeholder="36.50">
                    <div class="description">Tasa de cambio manual (se actualizará automáticamente si BCV está disponible)</div>
                </div>
                
                <div class="wvp-form-group">
                    <label>
                        <input type="checkbox" name="dual_currency" value="1" checked>
                        Mostrar precios en ambas monedas
                    </label>
                    <div class="description">Los clientes verán precios en USD y VES</div>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Step 3: Payment Methods
     */
    private function render_step_3() {
        ?>
        <div class="wvp-step-content">
            <h2>💳 Métodos de Pago</h2>
            <p>Seleccione los métodos de pago que desea habilitar.</p>
            
            <form class="wvp-wizard-form">
                <div class="wvp-checkbox-group">
                    <div class="wvp-checkbox-item">
                        <input type="checkbox" id="pago_movil" name="payment_methods[]" value="pago_movil" checked>
                        <label for="pago_movil">
                            <strong>Pago Móvil</strong><br>
                            <small>Pagos mediante Pago Móvil venezolano</small>
                        </label>
                    </div>
                    
                    <div class="wvp-checkbox-item">
                        <input type="checkbox" id="zelle" name="payment_methods[]" value="zelle">
                        <label for="zelle">
                            <strong>Zelle</strong><br>
                            <small>Transferencias internacionales</small>
                        </label>
                    </div>
                    
                    <div class="wvp-checkbox-item">
                        <input type="checkbox" id="bank_transfer" name="payment_methods[]" value="bank_transfer" checked>
                        <label for="bank_transfer">
                            <strong>Transferencia Bancaria</strong><br>
                            <small>Transferencias bancarias locales</small>
                        </label>
                    </div>
                    
                    <div class="wvp-checkbox-item">
                        <input type="checkbox" id="cash_on_delivery" name="payment_methods[]" value="cash_on_delivery">
                        <label for="cash_on_delivery">
                            <strong>Pago Contra Entrega</strong><br>
                            <small>Pago en efectivo al recibir</small>
                        </label>
                    </div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="pago_movil_phone">Teléfono para Pago Móvil</label>
                    <input type="tel" id="pago_movil_phone" name="pago_movil_phone" placeholder="0412-1234567">
                    <div class="description">Número de teléfono para recibir pagos móviles</div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="pago_movil_cedula">Cédula para Pago Móvil</label>
                    <input type="text" id="pago_movil_cedula" name="pago_movil_cedula" placeholder="V-12345678">
                    <div class="description">Cédula asociada al Pago Móvil</div>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Step 4: Shipping Methods
     */
    private function render_step_4() {
        ?>
        <div class="wvp-step-content">
            <h2>🚚 Métodos de Envío</h2>
            <p>Configure los métodos de envío disponibles para Venezuela.</p>
            
            <form class="wvp-wizard-form">
                <div class="wvp-checkbox-group">
                    <div class="wvp-checkbox-item">
                        <input type="checkbox" id="mrw_shipping" name="shipping_methods[]" value="mrw" checked>
                        <label for="mrw_shipping">
                            <strong>MRW Venezuela</strong><br>
                            <small>Envío nacional mediante MRW</small>
                        </label>
                    </div>
                    
                    <div class="wvp-checkbox-item">
                        <input type="checkbox" id="zoom_shipping" name="shipping_methods[]" value="zoom" checked>
                        <label for="zoom_shipping">
                            <strong>Zoom Venezuela</strong><br>
                            <small>Envío nacional mediante Zoom</small>
                        </label>
                    </div>
                    
                    <div class="wvp-checkbox-item">
                        <input type="checkbox" id="local_delivery" name="shipping_methods[]" value="local_delivery">
                        <label for="local_delivery">
                            <strong>Entrega Local</strong><br>
                            <small>Entrega directa en áreas cercanas</small>
                        </label>
                    </div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="free_shipping_threshold">Envío Gratis Desde</label>
                    <input type="number" id="free_shipping_threshold" name="free_shipping_threshold" step="0.01" placeholder="100.00">
                    <div class="description">Monto mínimo para envío gratis (en USD)</div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="shipping_zones">Zonas de Envío</label>
                    <select id="shipping_zones" name="shipping_zones[]" multiple>
                        <option value="Distrito Capital">Distrito Capital</option>
                        <option value="Miranda">Miranda</option>
                        <option value="Aragua">Aragua</option>
                        <option value="Carabobo">Carabobo</option>
                        <option value="Vargas">Vargas</option>
                        <option value="all">Todos los Estados</option>
                    </select>
                    <div class="description">Estados donde realiza envíos (mantenga presionado Ctrl para seleccionar múltiples)</div>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Step 5: Tax Settings
     */
    private function render_step_5() {
        ?>
        <div class="wvp-step-content">
            <h2>📊 Configuración de Impuestos</h2>
            <p>Configure los impuestos venezolanos aplicables.</p>
            
            <form class="wvp-wizard-form">
                <div class="wvp-form-group">
                    <label>
                        <input type="checkbox" name="enable_iva" value="1" checked>
                        Habilitar IVA (16%)
                    </label>
                    <div class="description">Impuesto al Valor Agregado venezolano</div>
                </div>
                
                <div class="wvp-form-group">
                    <label>
                        <input type="checkbox" name="enable_igtf" value="1">
                        Habilitar IGTF (3%)
                    </label>
                    <div class="description">Impuesto a las Grandes Transacciones Financieras</div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="tax_inclusive">Precios con Impuestos</label>
                    <select id="tax_inclusive" name="tax_inclusive">
                        <option value="yes">Sí, incluir impuestos en precios</option>
                        <option value="no">No, agregar impuestos al final</option>
                    </select>
                    <div class="description">Cómo mostrar los precios a los clientes</div>
                </div>
                
                <div class="wvp-form-group">
                    <label for="business_rif">RIF de la Empresa</label>
                    <input type="text" id="business_rif" name="business_rif" placeholder="J-12345678-9">
                    <div class="description">Registro de Información Fiscal de su empresa</div>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Step 6: Final Configuration
     */
    private function render_step_6() {
        ?>
        <div class="wvp-step-content">
            <h2>🎉 Configuración Final</h2>
            <p>Revise su configuración y complete el asistente.</p>
            
            <div class="wvp-summary">
                <h3>Resumen de Configuración</h3>
                <div class="wvp-summary-item">
                    <strong>Tienda:</strong> <span id="summary-store-name"><?php echo esc_html( get_option( 'blogname' ) ); ?></span>
                </div>
                <div class="wvp-summary-item">
                    <strong>Moneda Base:</strong> <span id="summary-base-currency">USD</span>
                </div>
                <div class="wvp-summary-item">
                    <strong>Moneda de Visualización:</strong> <span id="summary-display-currency">VES</span>
                </div>
                <div class="wvp-summary-item">
                    <strong>Métodos de Pago:</strong> <span id="summary-payment-methods">Pago Móvil, Transferencia Bancaria</span>
                </div>
                <div class="wvp-summary-item">
                    <strong>Métodos de Envío:</strong> <span id="summary-shipping-methods">MRW, Zoom</span>
                </div>
                <div class="wvp-summary-item">
                    <strong>Impuestos:</strong> <span id="summary-taxes">IVA (16%)</span>
                </div>
            </div>
            
            <div class="wvp-final-options">
                <h3>Opciones Adicionales</h3>
                
                <div class="wvp-form-group">
                    <label>
                        <input type="checkbox" name="enable_seniat" value="1" checked>
                        Habilitar Reportes SENIAT
                    </label>
                    <div class="description">Generar reportes fiscales automáticamente</div>
                </div>
                
                <div class="wvp-form-group">
                    <label>
                        <input type="checkbox" name="enable_notifications" value="1" checked>
                        Habilitar Notificaciones
                    </label>
                    <div class="description">Notificaciones por email y WhatsApp</div>
                </div>
                
                <div class="wvp-form-group">
                    <label>
                        <input type="checkbox" name="enable_analytics" value="1" checked>
                        Habilitar Analytics
                    </label>
                    <div class="description">Seguimiento de ventas y estadísticas</div>
                </div>
            </div>
            
            <form class="wvp-wizard-form">
                <input type="hidden" name="final_step" value="1">
            </form>
        </div>
        <?php
    }
    
    /**
     * Render navigation
     */
    private function render_navigation() {
        ?>
        <div class="wvp-button-group">
            <?php if ( $this->current_step > 1 ) : ?>
                <a href="<?php echo admin_url( 'admin.php?page=wvp-wizard&step=' . ( $this->current_step - 1 ) ); ?>" class="button">
                    ← Anterior
                </a>
            <?php endif; ?>
            
            <button type="button" id="wvp-skip-wizard" class="button">
                Omitir Configuración
            </button>
            
            <?php if ( $this->current_step < $this->total_steps ) : ?>
                <button type="submit" form="wvp-wizard-form" class="button button-primary">
                    Siguiente →
                </button>
            <?php else : ?>
                <button type="submit" form="wvp-wizard-form" class="button button-primary">
                    Completar Configuración
                </button>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * AJAX save step
     */
    public function ajax_save_step() {
        check_ajax_referer( 'wvp_wizard_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $step = intval( $_POST['step'] );
        $data = $_POST['data'];
        
        // Parse form data
        parse_str( $data, $form_data );
        
        // Save step data
        update_option( 'wvp_wizard_step_' . $step, $form_data );
        
        wp_send_json_success( 'Step saved successfully' );
    }
    
    /**
     * AJAX complete wizard
     */
    public function ajax_complete_wizard() {
        check_ajax_referer( 'wvp_wizard_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        // Apply all wizard settings
        $this->apply_wizard_settings();
        
        // Mark wizard as completed
        update_option( 'wvp_wizard_completed', true );
        update_option( 'wvp_wizard_completed_date', current_time( 'mysql' ) );
        
        wp_send_json_success( 'Wizard completed successfully' );
    }
    
    /**
     * AJAX skip wizard
     */
    public function ajax_skip_wizard() {
        check_ajax_referer( 'wvp_wizard_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        // Mark wizard as skipped
        update_option( 'wvp_wizard_completed', true );
        update_option( 'wvp_wizard_skipped', true );
        
        wp_send_json_success( 'Wizard skipped successfully' );
    }
    
    /**
     * Apply wizard settings
     */
    private function apply_wizard_settings() {
        // Apply settings from each step
        for ( $step = 1; $step <= $this->total_steps; $step++ ) {
            $step_data = get_option( 'wvp_wizard_step_' . $step, array() );
            
            if ( ! empty( $step_data ) ) {
                $this->apply_step_settings( $step, $step_data );
            }
        }
    }
    
    /**
     * Apply step settings
     */
    private function apply_step_settings( $step, $data ) {
        switch ( $step ) {
            case 1: // Store Information
                if ( isset( $data['store_name'] ) ) {
                    update_option( 'blogname', $data['store_name'] );
                }
                if ( isset( $data['store_email'] ) ) {
                    update_option( 'admin_email', $data['store_email'] );
                }
                if ( isset( $data['store_phone'] ) ) {
                    update_option( 'wvp_store_phone', $data['store_phone'] );
                }
                if ( isset( $data['store_address'] ) ) {
                    update_option( 'wvp_store_address', $data['store_address'] );
                }
                if ( isset( $data['store_state'] ) ) {
                    update_option( 'wvp_store_state', $data['store_state'] );
                }
                break;
                
            case 2: // Currency Settings
                if ( isset( $data['base_currency'] ) ) {
                    update_option( 'woocommerce_currency', $data['base_currency'] );
                }
                if ( isset( $data['display_currency'] ) ) {
                    update_option( 'wvp_display_currency', $data['display_currency'] );
                }
                if ( isset( $data['bcv_rate'] ) ) {
                    update_option( 'wvp_bcv_rate', $data['bcv_rate'] );
                }
                if ( isset( $data['dual_currency'] ) ) {
                    update_option( 'wvp_dual_currency', true );
                }
                break;
                
            case 3: // Payment Methods
                if ( isset( $data['payment_methods'] ) ) {
                    update_option( 'wvp_enabled_payment_methods', $data['payment_methods'] );
                }
                if ( isset( $data['pago_movil_phone'] ) ) {
                    update_option( 'wvp_pago_movil_phone', $data['pago_movil_phone'] );
                }
                if ( isset( $data['pago_movil_cedula'] ) ) {
                    update_option( 'wvp_pago_movil_cedula', $data['pago_movil_cedula'] );
                }
                break;
                
            case 4: // Shipping Methods
                if ( isset( $data['shipping_methods'] ) ) {
                    update_option( 'wvp_enabled_shipping_methods', $data['shipping_methods'] );
                }
                if ( isset( $data['free_shipping_threshold'] ) ) {
                    update_option( 'wvp_free_shipping_threshold', $data['free_shipping_threshold'] );
                }
                if ( isset( $data['shipping_zones'] ) ) {
                    update_option( 'wvp_shipping_zones', $data['shipping_zones'] );
                }
                break;
                
            case 5: // Tax Settings
                if ( isset( $data['enable_iva'] ) ) {
                    update_option( 'wvp_enable_iva', true );
                }
                if ( isset( $data['enable_igtf'] ) ) {
                    update_option( 'wvp_enable_igtf', true );
                }
                if ( isset( $data['tax_inclusive'] ) ) {
                    update_option( 'woocommerce_tax_display_shop', $data['tax_inclusive'] );
                }
                if ( isset( $data['business_rif'] ) ) {
                    update_option( 'wvp_business_rif', $data['business_rif'] );
                }
                break;
                
            case 6: // Final Configuration
                if ( isset( $data['enable_seniat'] ) ) {
                    update_option( 'wvp_enable_seniat', true );
                }
                if ( isset( $data['enable_notifications'] ) ) {
                    update_option( 'wvp_enable_notifications', true );
                }
                if ( isset( $data['enable_analytics'] ) ) {
                    update_option( 'wvp_enable_analytics', true );
                }
                break;
        }
    }
}
