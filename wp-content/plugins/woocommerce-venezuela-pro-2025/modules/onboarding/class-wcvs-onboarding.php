<?php
/**
 * Sistema de Onboarding - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar el sistema de onboarding
 */
class WCVS_Onboarding {

    /**
     * Instancia del plugin
     *
     * @var WCVS_Core
     */
    private $plugin;

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Pasos del wizard
     *
     * @var array
     */
    private $wizard_steps = array(
        'welcome' => 'Bienvenida',
        'currency' => 'Configuración de Moneda',
        'payment' => 'Pasarelas de Pago',
        'shipping' => 'Métodos de Envío',
        'taxes' => 'Sistema Fiscal',
        'billing' => 'Facturación Electrónica',
        'notifications' => 'Notificaciones',
        'complete' => 'Finalización'
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WCVS_Core::get_instance();
        $this->settings = $this->plugin->get_settings()->get('onboarding', array());
        
        $this->init_hooks();
    }

    /**
     * Inicializar el módulo
     */
    public function init() {
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Cargar dependencias
        $this->load_dependencies();

        // Configurar hooks
        $this->setup_hooks();

        // Cargar assets
        $this->enqueue_assets();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, 'Módulo Onboarding inicializado');
    }

    /**
     * Cargar dependencias
     */
    private function load_dependencies() {
        require_once WCVS_PLUGIN_PATH . 'modules/onboarding/includes/class-wcvs-wizard.php';
        require_once WCVS_PLUGIN_PATH . 'modules/onboarding/includes/class-wcvs-help-system.php';
        require_once WCVS_PLUGIN_PATH . 'modules/onboarding/includes/class-wcvs-tutorials.php';
        require_once WCVS_PLUGIN_PATH . 'modules/onboarding/includes/class-wcvs-support.php';
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cuando se activa el módulo
        add_action('wcvs_module_activated', array($this, 'handle_module_activation'), 10, 2);
        
        // Hook para cuando se desactiva el módulo
        add_action('wcvs_module_deactivated', array($this, 'handle_module_deactivation'), 10, 2);
        
        // Hook para mostrar onboarding
        add_action('admin_notices', array($this, 'show_onboarding_notice'));
    }

    /**
     * Configurar hooks
     */
    private function setup_hooks() {
        // Hook para añadir página de onboarding
        add_action('admin_menu', array($this, 'add_onboarding_menu'));
        
        // Hook para procesar onboarding
        add_action('wp_ajax_wcvs_process_onboarding', array($this, 'process_onboarding'));
        add_action('wp_ajax_wcvs_skip_onboarding', array($this, 'skip_onboarding'));
        
        // Hook para verificar si necesita onboarding
        add_action('admin_init', array($this, 'check_onboarding_status'));
    }

    /**
     * Verificar estado del onboarding
     */
    public function check_onboarding_status() {
        if (!$this->is_onboarding_completed() && !$this->is_onboarding_skipped()) {
            if (!isset($_GET['page']) || $_GET['page'] !== 'wcvs-onboarding') {
                $this->show_onboarding_notice();
            }
        }
    }

    /**
     * Mostrar aviso de onboarding
     */
    public function show_onboarding_notice() {
        if ($this->is_onboarding_completed() || $this->is_onboarding_skipped()) {
            return;
        }

        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'wcvs') !== false) {
            ?>
            <div class="notice notice-info wcvs-onboarding-notice">
                <div class="wcvs-onboarding-notice-content">
                    <div class="wcvs-onboarding-icon">
                        <span class="dashicons dashicons-admin-tools"></span>
                    </div>
                    <div class="wcvs-onboarding-text">
                        <h3><?php esc_html_e('¡Bienvenido a WooCommerce Venezuela Suite!', 'wcvs'); ?></h3>
                        <p><?php esc_html_e('Configura tu tienda venezolana en pocos pasos con nuestro asistente de configuración.', 'wcvs'); ?></p>
                    </div>
                    <div class="wcvs-onboarding-actions">
                        <a href="<?php echo admin_url('admin.php?page=wcvs-onboarding'); ?>" class="button button-primary">
                            <?php esc_html_e('Comenzar Configuración', 'wcvs'); ?>
                        </a>
                        <button type="button" class="button wcvs-skip-onboarding">
                            <?php esc_html_e('Omitir por ahora', 'wcvs'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Añadir menú de onboarding
     */
    public function add_onboarding_menu() {
        add_submenu_page(
            'wcvs',
            __('Configuración Inicial', 'wcvs'),
            __('Configuración Inicial', 'wcvs'),
            'manage_options',
            'wcvs-onboarding',
            array($this, 'display_onboarding_page')
        );
    }

    /**
     * Mostrar página de onboarding
     */
    public function display_onboarding_page() {
        $current_step = $this->get_current_step();
        ?>
        <div class="wrap wcvs-onboarding-page">
            <div class="wcvs-onboarding-header">
                <h1><?php esc_html_e('Configuración Inicial - WooCommerce Venezuela Suite', 'wcvs'); ?></h1>
                <p><?php esc_html_e('Configura tu tienda venezolana paso a paso', 'wcvs'); ?></p>
            </div>

            <div class="wcvs-onboarding-progress">
                <?php $this->render_progress_bar($current_step); ?>
            </div>

            <div class="wcvs-onboarding-content">
                <?php $this->render_step_content($current_step); ?>
            </div>

            <div class="wcvs-onboarding-navigation">
                <?php $this->render_navigation($current_step); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Renderizar barra de progreso
     */
    private function render_progress_bar($current_step) {
        $steps = array_keys($this->wizard_steps);
        $current_index = array_search($current_step, $steps);
        $progress_percentage = (($current_index + 1) / count($steps)) * 100;
        
        ?>
        <div class="wcvs-progress-bar">
            <div class="wcvs-progress-fill" style="width: <?php echo esc_attr($progress_percentage); ?>%"></div>
        </div>
        <div class="wcvs-progress-steps">
            <?php foreach ($steps as $index => $step): ?>
                <div class="wcvs-progress-step <?php echo $index <= $current_index ? 'completed' : ''; ?> <?php echo $index === $current_index ? 'current' : ''; ?>">
                    <span class="wcvs-step-number"><?php echo $index + 1; ?></span>
                    <span class="wcvs-step-label"><?php echo esc_html($this->wizard_steps[$step]); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Renderizar contenido del paso
     */
    private function render_step_content($current_step) {
        switch ($current_step) {
            case 'welcome':
                $this->render_welcome_step();
                break;
            case 'currency':
                $this->render_currency_step();
                break;
            case 'payment':
                $this->render_payment_step();
                break;
            case 'shipping':
                $this->render_shipping_step();
                break;
            case 'taxes':
                $this->render_taxes_step();
                break;
            case 'billing':
                $this->render_billing_step();
                break;
            case 'notifications':
                $this->render_notifications_step();
                break;
            case 'complete':
                $this->render_complete_step();
                break;
        }
    }

    /**
     * Renderizar paso de bienvenida
     */
    private function render_welcome_step() {
        ?>
        <div class="wcvs-step-content wcvs-welcome-step">
            <div class="wcvs-step-icon">
                <span class="dashicons dashicons-flag"></span>
            </div>
            <h2><?php esc_html_e('¡Bienvenido a WooCommerce Venezuela Suite!', 'wcvs'); ?></h2>
            <p><?php esc_html_e('Te ayudaremos a configurar tu tienda venezolana en pocos pasos. Este proceso tomará aproximadamente 5-10 minutos.', 'wcvs'); ?></p>
            
            <div class="wcvs-features-preview">
                <h3><?php esc_html_e('Lo que configurarás:', 'wcvs'); ?></h3>
                <ul>
                    <li><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e('Conversión automática USD a VES', 'wcvs'); ?></li>
                    <li><span class="dashicons dashicons-cart"></span> <?php esc_html_e('Pasarelas de pago locales', 'wcvs'); ?></li>
                    <li><span class="dashicons dashicons-truck"></span> <?php esc_html_e('Métodos de envío venezolanos', 'wcvs'); ?></li>
                    <li><span class="dashicons dashicons-chart-line"></span> <?php esc_html_e('Sistema fiscal venezolano', 'wcvs'); ?></li>
                    <li><span class="dashicons dashicons-media-document"></span> <?php esc_html_e('Facturación electrónica SENIAT', 'wcvs'); ?></li>
                    <li><span class="dashicons dashicons-bell"></span> <?php esc_html_e('Sistema de notificaciones', 'wcvs'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Renderizar paso de moneda
     */
    private function render_currency_step() {
        ?>
        <div class="wcvs-step-content wcvs-currency-step">
            <h2><?php esc_html_e('Configuración de Moneda', 'wcvs'); ?></h2>
            <p><?php esc_html_e('Configura la conversión automática de precios USD a VES usando la tasa del BCV.', 'wcvs'); ?></p>
            
            <form class="wcvs-onboarding-form" data-step="currency">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="currency_enabled"><?php esc_html_e('Habilitar conversión automática', 'wcvs'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="currency_enabled" name="currency_enabled" value="1" checked>
                            <label for="currency_enabled"><?php esc_html_e('Convertir automáticamente precios USD a VES', 'wcvs'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="currency_display_style"><?php esc_html_e('Estilo de visualización', 'wcvs'); ?></label>
                        </th>
                        <td>
                            <select id="currency_display_style" name="currency_display_style">
                                <option value="minimalist"><?php esc_html_e('Minimalista', 'wcvs'); ?></option>
                                <option value="modern" selected><?php esc_html_e('Moderno', 'wcvs'); ?></option>
                                <option value="elegant"><?php esc_html_e('Elegante', 'wcvs'); ?></option>
                                <option value="compact"><?php esc_html_e('Compacto', 'wcvs'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="currency_update_interval"><?php esc_html_e('Frecuencia de actualización', 'wcvs'); ?></label>
                        </th>
                        <td>
                            <select id="currency_update_interval" name="currency_update_interval">
                                <option value="15"><?php esc_html_e('Cada 15 minutos', 'wcvs'); ?></option>
                                <option value="30" selected><?php esc_html_e('Cada 30 minutos', 'wcvs'); ?></option>
                                <option value="60"><?php esc_html_e('Cada hora', 'wcvs'); ?></option>
                                <option value="120"><?php esc_html_e('Cada 2 horas', 'wcvs'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    /**
     * Renderizar paso de pagos
     */
    private function render_payment_step() {
        ?>
        <div class="wcvs-step-content wcvs-payment-step">
            <h2><?php esc_html_e('Pasarelas de Pago Locales', 'wcvs'); ?></h2>
            <p><?php esc_html_e('Selecciona los métodos de pago que deseas habilitar para tus clientes venezolanos.', 'wcvs'); ?></p>
            
            <form class="wcvs-onboarding-form" data-step="payment">
                <div class="wcvs-payment-methods">
                    <div class="wcvs-payment-method">
                        <input type="checkbox" id="payment_zelle" name="payment_methods[]" value="zelle">
                        <label for="payment_zelle">
                            <span class="wcvs-payment-icon">💳</span>
                            <strong>Zelle</strong>
                            <span class="wcvs-payment-desc"><?php esc_html_e('Transferencias bancarias internacionales', 'wcvs'); ?></span>
                        </label>
                    </div>
                    
                    <div class="wcvs-payment-method">
                        <input type="checkbox" id="payment_binance" name="payment_methods[]" value="binance">
                        <label for="payment_binance">
                            <span class="wcvs-payment-icon">₿</span>
                            <strong>Binance Pay</strong>
                            <span class="wcvs-payment-desc"><?php esc_html_e('Pagos con criptomonedas', 'wcvs'); ?></span>
                        </label>
                    </div>
                    
                    <div class="wcvs-payment-method">
                        <input type="checkbox" id="payment_pago_movil" name="payment_methods[]" value="pago_movil">
                        <label for="payment_pago_movil">
                            <span class="wcvs-payment-icon">📱</span>
                            <strong>Pago Móvil</strong>
                            <span class="wcvs-payment-desc"><?php esc_html_e('Transferencias móviles venezolanas', 'wcvs'); ?></span>
                        </label>
                    </div>
                    
                    <div class="wcvs-payment-method">
                        <input type="checkbox" id="payment_bank_transfer" name="payment_methods[]" value="bank_transfer">
                        <label for="payment_bank_transfer">
                            <span class="wcvs-payment-icon">🏦</span>
                            <strong>Transferencia Bancaria</strong>
                            <span class="wcvs-payment-desc"><?php esc_html_e('Transferencias bancarias locales', 'wcvs'); ?></span>
                        </label>
                    </div>
                    
                    <div class="wcvs-payment-method">
                        <input type="checkbox" id="payment_cash_deposit" name="payment_methods[]" value="cash_deposit">
                        <label for="payment_cash_deposit">
                            <span class="wcvs-payment-icon">💵</span>
                            <strong>Depósito en Efectivo USD</strong>
                            <span class="wcvs-payment-desc"><?php esc_html_e('Depósitos en efectivo en dólares', 'wcvs'); ?></span>
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Renderizar paso de envíos
     */
    private function render_shipping_step() {
        ?>
        <div class="wcvs-step-content wcvs-shipping-step">
            <h2><?php esc_html_e('Métodos de Envío Locales', 'wcvs'); ?></h2>
            <p><?php esc_html_e('Configura los métodos de envío disponibles para Venezuela.', 'wcvs'); ?></p>
            
            <form class="wcvs-onboarding-form" data-step="shipping">
                <div class="wcvs-shipping-methods">
                    <div class="wcvs-shipping-method">
                        <input type="checkbox" id="shipping_mrw" name="shipping_methods[]" value="mrw">
                        <label for="shipping_mrw">
                            <span class="wcvs-shipping-icon">🚚</span>
                            <strong>MRW</strong>
                            <span class="wcvs-shipping-desc"><?php esc_html_e('Servicio de envío nacional', 'wcvs'); ?></span>
                        </label>
                    </div>
                    
                    <div class="wcvs-shipping-method">
                        <input type="checkbox" id="shipping_zoom" name="shipping_methods[]" value="zoom">
                        <label for="shipping_zoom">
                            <span class="wcvs-shipping-icon">⚡</span>
                            <strong>Zoom</strong>
                            <span class="wcvs-shipping-desc"><?php esc_html_e('Envío rápido y confiable', 'wcvs'); ?></span>
                        </label>
                    </div>
                    
                    <div class="wcvs-shipping-method">
                        <input type="checkbox" id="shipping_tealca" name="shipping_methods[]" value="tealca">
                        <label for="shipping_tealca">
                            <span class="wcvs-shipping-icon">📦</span>
                            <strong>Tealca</strong>
                            <span class="wcvs-shipping-desc"><?php esc_html_e('Envío económico para productos pesados', 'wcvs'); ?></span>
                        </label>
                    </div>
                    
                    <div class="wcvs-shipping-method">
                        <input type="checkbox" id="shipping_local_delivery" name="shipping_methods[]" value="local_delivery">
                        <label for="shipping_local_delivery">
                            <span class="wcvs-shipping-icon">🏠</span>
                            <strong>Entrega Local</strong>
                            <span class="wcvs-shipping-desc"><?php esc_html_e('Entrega en tu ciudad', 'wcvs'); ?></span>
                        </label>
                    </div>
                    
                    <div class="wcvs-shipping-method">
                        <input type="checkbox" id="shipping_pickup" name="shipping_methods[]" value="pickup">
                        <label for="shipping_pickup">
                            <span class="wcvs-shipping-icon">🏪</span>
                            <strong>Recogida en Tienda</strong>
                            <span class="wcvs-shipping-desc"><?php esc_html_e('Recogida gratuita en tienda física', 'wcvs'); ?></span>
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Renderizar paso de impuestos
     */
    private function render_taxes_step() {
        ?>
        <div class="wcvs-step-content wcvs-taxes-step">
            <h2><?php esc_html_e('Sistema Fiscal Venezolano', 'wcvs'); ?></h2>
            <p><?php esc_html_e('Configura los impuestos aplicables en Venezuela.', 'wcvs'); ?></p>
            
            <form class="wcvs-onboarding-form" data-step="taxes">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="iva_enabled"><?php esc_html_e('IVA (16%)', 'wcvs'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="iva_enabled" name="iva_enabled" value="1" checked>
                            <label for="iva_enabled"><?php esc_html_e('Aplicar IVA del 16%', 'wcvs'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="igtf_enabled"><?php esc_html_e('IGTF (3%)', 'wcvs'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="igtf_enabled" name="igtf_enabled" value="1">
                            <label for="igtf_enabled"><?php esc_html_e('Aplicar IGTF del 3% para pagos en moneda extranjera', 'wcvs'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="islr_enabled"><?php esc_html_e('ISLR', 'wcvs'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="islr_enabled" name="islr_enabled" value="1">
                            <label for="islr_enabled"><?php esc_html_e('Aplicar retenciones de ISLR', 'wcvs'); ?></label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    /**
     * Renderizar paso de facturación
     */
    private function render_billing_step() {
        ?>
        <div class="wcvs-step-content wcvs-billing-step">
            <h2><?php esc_html_e('Facturación Electrónica SENIAT', 'wcvs'); ?></h2>
            <p><?php esc_html_e('Configura la facturación electrónica para cumplir con las regulaciones venezolanas.', 'wcvs'); ?></p>
            
            <form class="wcvs-onboarding-form" data-step="billing">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="billing_enabled"><?php esc_html_e('Habilitar facturación electrónica', 'wcvs'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="billing_enabled" name="billing_enabled" value="1">
                            <label for="billing_enabled"><?php esc_html_e('Generar facturas electrónicas automáticamente', 'wcvs'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="seniat_integration"><?php esc_html_e('Integración SENIAT', 'wcvs'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="seniat_integration" name="seniat_integration" value="1">
                            <label for="seniat_integration"><?php esc_html_e('Enviar facturas automáticamente a SENIAT', 'wcvs'); ?></label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    /**
     * Renderizar paso de notificaciones
     */
    private function render_notifications_step() {
        ?>
        <div class="wcvs-step-content wcvs-notifications-step">
            <h2><?php esc_html_e('Sistema de Notificaciones', 'wcvs'); ?></h2>
            <p><?php esc_html_e('Configura cómo recibir notificaciones sobre tu tienda.', 'wcvs'); ?></p>
            
            <form class="wcvs-onboarding-form" data-step="notifications">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="email_notifications"><?php esc_html_e('Notificaciones por Email', 'wcvs'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="email_notifications" name="email_notifications" value="1" checked>
                            <label for="email_notifications"><?php esc_html_e('Recibir notificaciones por email', 'wcvs'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="sms_notifications"><?php esc_html_e('Notificaciones por SMS', 'wcvs'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="sms_notifications" name="sms_notifications" value="1">
                            <label for="sms_notifications"><?php esc_html_e('Recibir notificaciones por SMS', 'wcvs'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="push_notifications"><?php esc_html_e('Notificaciones Push', 'wcvs'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="push_notifications" name="push_notifications" value="1">
                            <label for="push_notifications"><?php esc_html_e('Recibir notificaciones push', 'wcvs'); ?></label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    /**
     * Renderizar paso de finalización
     */
    private function render_complete_step() {
        ?>
        <div class="wcvs-step-content wcvs-complete-step">
            <div class="wcvs-step-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <h2><?php esc_html_e('¡Configuración Completada!', 'wcvs'); ?></h2>
            <p><?php esc_html_e('Tu tienda venezolana está lista para recibir clientes. Aquí tienes un resumen de lo que se ha configurado:', 'wcvs'); ?></p>
            
            <div class="wcvs-completion-summary">
                <h3><?php esc_html_e('Resumen de configuración:', 'wcvs'); ?></h3>
                <ul id="wcvs-completion-list">
                    <!-- Se llenará dinámicamente con JavaScript -->
                </ul>
            </div>
            
            <div class="wcvs-next-steps">
                <h3><?php esc_html_e('Próximos pasos:', 'wcvs'); ?></h3>
                <ul>
                    <li><?php esc_html_e('Revisar y ajustar la configuración desde el panel de administración', 'wcvs'); ?></li>
                    <li><?php esc_html_e('Configurar tus productos con precios en USD', 'wcvs'); ?></li>
                    <li><?php esc_html_e('Probar el proceso de compra completo', 'wcvs'); ?></li>
                    <li><?php esc_html_e('Configurar métodos de pago específicos', 'wcvs'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Renderizar navegación
     */
    private function render_navigation($current_step) {
        $steps = array_keys($this->wizard_steps);
        $current_index = array_search($current_step, $steps);
        
        ?>
        <div class="wcvs-navigation">
            <?php if ($current_index > 0): ?>
                <button type="button" class="button wcvs-btn-prev" data-step="<?php echo esc_attr($steps[$current_index - 1]); ?>">
                    <?php esc_html_e('Anterior', 'wcvs'); ?>
                </button>
            <?php endif; ?>
            
            <?php if ($current_index < count($steps) - 1): ?>
                <button type="button" class="button button-primary wcvs-btn-next" data-step="<?php echo esc_attr($steps[$current_index + 1]); ?>">
                    <?php esc_html_e('Siguiente', 'wcvs'); ?>
                </button>
            <?php else: ?>
                <button type="button" class="button button-primary wcvs-btn-complete">
                    <?php esc_html_e('Finalizar Configuración', 'wcvs'); ?>
                </button>
            <?php endif; ?>
            
            <button type="button" class="button wcvs-btn-skip">
                <?php esc_html_e('Omitir Configuración', 'wcvs'); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Obtener paso actual
     */
    private function get_current_step() {
        return isset($_GET['step']) ? sanitize_text_field($_GET['step']) : 'welcome';
    }

    /**
     * Procesar onboarding
     */
    public function process_onboarding() {
        check_ajax_referer('wcvs_onboarding_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos para realizar esta acción.', 'wcvs')));
        }

        $step = sanitize_text_field($_POST['step']);
        $data = $_POST['data'];

        $result = $this->process_step($step, $data);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Procesar paso específico
     */
    private function process_step($step, $data) {
        switch ($step) {
            case 'currency':
                return $this->process_currency_step($data);
            case 'payment':
                return $this->process_payment_step($data);
            case 'shipping':
                return $this->process_shipping_step($data);
            case 'taxes':
                return $this->process_taxes_step($data);
            case 'billing':
                return $this->process_billing_step($data);
            case 'notifications':
                return $this->process_notifications_step($data);
            case 'complete':
                return $this->process_complete_step($data);
            default:
                return array('success' => true, 'message' => __('Paso procesado correctamente.', 'wcvs'));
        }
    }

    /**
     * Procesar paso de moneda
     */
    private function process_currency_step($data) {
        $settings = array(
            'currency_enabled' => isset($data['currency_enabled']),
            'currency_display_style' => $data['currency_display_style'] ?? 'modern',
            'currency_update_interval' => $data['currency_update_interval'] ?? 30
        );

        $this->plugin->get_settings()->set('currency_manager', $settings);
        
        return array(
            'success' => true,
            'message' => __('Configuración de moneda guardada correctamente.', 'wcvs')
        );
    }

    /**
     * Procesar paso de pagos
     */
    private function process_payment_step($data) {
        $payment_methods = $data['payment_methods'] ?? array();
        $settings = array();

        foreach ($payment_methods as $method) {
            $settings[$method . '_enabled'] = true;
        }

        $this->plugin->get_settings()->set('payment_gateways', $settings);
        
        return array(
            'success' => true,
            'message' => __('Pasarelas de pago configuradas correctamente.', 'wcvs')
        );
    }

    /**
     * Procesar paso de envíos
     */
    private function process_shipping_step($data) {
        $shipping_methods = $data['shipping_methods'] ?? array();
        $settings = array();

        foreach ($shipping_methods as $method) {
            $settings[$method . '_enabled'] = true;
        }

        $this->plugin->get_settings()->set('shipping_methods', $settings);
        
        return array(
            'success' => true,
            'message' => __('Métodos de envío configurados correctamente.', 'wcvs')
        );
    }

    /**
     * Procesar paso de impuestos
     */
    private function process_taxes_step($data) {
        $settings = array(
            'enable_iva' => isset($data['iva_enabled']),
            'enable_igtf' => isset($data['igtf_enabled']),
            'enable_islr' => isset($data['islr_enabled'])
        );

        $this->plugin->get_settings()->set('tax_system', $settings);
        
        return array(
            'success' => true,
            'message' => __('Sistema fiscal configurado correctamente.', 'wcvs')
        );
    }

    /**
     * Procesar paso de facturación
     */
    private function process_billing_step($data) {
        $settings = array(
            'billing_enabled' => isset($data['billing_enabled']),
            'seniat_integration' => isset($data['seniat_integration'])
        );

        $this->plugin->get_settings()->set('electronic_billing', $settings);
        
        return array(
            'success' => true,
            'message' => __('Facturación electrónica configurada correctamente.', 'wcvs')
        );
    }

    /**
     * Procesar paso de notificaciones
     */
    private function process_notifications_step($data) {
        $settings = array(
            'email_notifications' => isset($data['email_notifications']),
            'sms_notifications' => isset($data['sms_notifications']),
            'push_notifications' => isset($data['push_notifications'])
        );

        $this->plugin->get_settings()->set('notifications', $settings);
        
        return array(
            'success' => true,
            'message' => __('Sistema de notificaciones configurado correctamente.', 'wcvs')
        );
    }

    /**
     * Procesar paso de finalización
     */
    private function process_complete_step($data) {
        $this->mark_onboarding_completed();
        
        return array(
            'success' => true,
            'message' => __('Configuración completada exitosamente.', 'wcvs'),
            'redirect_url' => admin_url('admin.php?page=wcvs')
        );
    }

    /**
     * Omitir onboarding
     */
    public function skip_onboarding() {
        check_ajax_referer('wcvs_onboarding_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No tienes permisos para realizar esta acción.', 'wcvs')));
        }

        $this->mark_onboarding_skipped();
        
        wp_send_json_success(array(
            'message' => __('Onboarding omitido. Puedes configurar el plugin manualmente desde el panel de administración.', 'wcvs'),
            'redirect_url' => admin_url('admin.php?page=wcvs')
        ));
    }

    /**
     * Marcar onboarding como completado
     */
    private function mark_onboarding_completed() {
        update_option('wcvs_onboarding_completed', true);
        update_option('wcvs_onboarding_completed_date', current_time('mysql'));
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, 'Onboarding completado exitosamente');
    }

    /**
     * Marcar onboarding como omitido
     */
    private function mark_onboarding_skipped() {
        update_option('wcvs_onboarding_skipped', true);
        update_option('wcvs_onboarding_skipped_date', current_time('mysql'));
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, 'Onboarding omitido por el usuario');
    }

    /**
     * Verificar si el onboarding está completado
     */
    private function is_onboarding_completed() {
        return get_option('wcvs_onboarding_completed', false);
    }

    /**
     * Verificar si el onboarding fue omitido
     */
    private function is_onboarding_skipped() {
        return get_option('wcvs_onboarding_skipped', false);
    }

    /**
     * Cargar assets (CSS y JS)
     */
    private function enqueue_assets() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_onboarding_assets'));
    }

    /**
     * Encolar assets de onboarding
     */
    public function enqueue_onboarding_assets($hook) {
        if (strpos($hook, 'wcvs-onboarding') !== false) {
            wp_enqueue_style(
                'wcvs-onboarding',
                WCVS_PLUGIN_URL . 'modules/onboarding/css/wcvs-onboarding.css',
                array(),
                WCVS_VERSION
            );

            wp_enqueue_script(
                'wcvs-onboarding',
                WCVS_PLUGIN_URL . 'modules/onboarding/js/wcvs-onboarding.js',
                array('jquery'),
                WCVS_VERSION,
                true
            );

            wp_localize_script('wcvs-onboarding', 'wcvs_onboarding_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcvs_onboarding_nonce'),
                'strings' => array(
                    'processing' => __('Procesando...', 'wcvs'),
                    'error' => __('Error al procesar el paso.', 'wcvs'),
                    'success' => __('Paso completado correctamente.', 'wcvs')
                )
            ));
        }
    }

    /**
     * Manejar activación del módulo
     */
    public function handle_module_activation($module_key, $module_data) {
        if ($module_key === 'onboarding') {
            $this->init();
            WCVS_Logger::success(WCVS_Logger::CONTEXT_ONBOARDING, 'Módulo Onboarding activado');
        }
    }

    /**
     * Manejar desactivación del módulo
     */
    public function handle_module_deactivation($module_key, $module_data) {
        if ($module_key === 'onboarding') {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, 'Módulo Onboarding desactivado');
        }
    }

    /**
     * Obtener estadísticas del módulo
     */
    public function get_module_stats() {
        return array(
            'onboarding_completed' => $this->is_onboarding_completed(),
            'onboarding_skipped' => $this->is_onboarding_skipped(),
            'completion_date' => get_option('wcvs_onboarding_completed_date'),
            'skip_date' => get_option('wcvs_onboarding_skipped_date'),
            'wizard_steps' => $this->wizard_steps,
            'settings' => $this->settings
        );
    }
}
