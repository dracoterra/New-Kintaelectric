<?php
/**
 * Módulo de Sistema Fiscal - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar el sistema fiscal venezolano
 */
class WCVS_Tax_System {

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
     * Tasas de impuestos venezolanos
     *
     * @var array
     */
    private $tax_rates = array(
        'iva' => 16.0,      // IVA General
        'igtf' => 3.0,      // IGTF sobre transacciones en moneda extranjera
        'islr' => 0.0,      // ISLR (se calcula según tabla)
    );

    /**
     * Tipos de documentos fiscales
     *
     * @var array
     */
    private $document_types = array(
        'factura' => 'Factura',
        'nota_debito' => 'Nota de Débito',
        'nota_credito' => 'Nota de Crédito',
        'factura_especial' => 'Factura Especial',
        'factura_exportacion' => 'Factura de Exportación',
    );

    /**
     * Estados fiscales
     *
     * @var array
     */
    private $fiscal_statuses = array(
        'pendiente' => 'Pendiente',
        'procesado' => 'Procesado',
        'enviado' => 'Enviado a SENIAT',
        'confirmado' => 'Confirmado por SENIAT',
        'rechazado' => 'Rechazado por SENIAT',
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WCVS_Core::get_instance();
        $this->settings = $this->plugin->get_settings()->get('tax_system', array());
        
        $this->init_hooks();
        $this->init_tax_calculations();
    }

    /**
     * Inicializar el módulo
     */
    public function init() {
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Configurar impuestos de WooCommerce
        $this->setup_woocommerce_taxes();

        // Configurar hooks de WooCommerce
        $this->setup_woocommerce_hooks();

        // Cargar scripts y estilos
        $this->enqueue_assets();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_TAX, 'Módulo Tax System inicializado');
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cuando se activa el módulo
        add_action('wcvs_module_activated', array($this, 'handle_module_activation'), 10, 2);
        
        // Hook para cuando se desactiva el módulo
        add_action('wcvs_module_deactivated', array($this, 'handle_module_deactivation'), 10, 2);
        
        // Hook para cálculo de impuestos
        add_action('woocommerce_cart_calculate_fees', array($this, 'calculate_venezuelan_taxes'));
        
        // Hook para procesamiento de pedidos
        add_action('woocommerce_checkout_order_processed', array($this, 'process_order_taxes'));
        
        // Hook para cambio de estado de pedido
        add_action('woocommerce_order_status_changed', array($this, 'handle_order_status_change'), 10, 3);
    }

    /**
     * Inicializar cálculos de impuestos
     */
    private function init_tax_calculations() {
        // Registrar clases de cálculo de impuestos
        require_once WCVS_PLUGIN_PATH . 'modules/tax-system/includes/class-wcvs-iva-calculator.php';
        require_once WCVS_PLUGIN_PATH . 'modules/tax-system/includes/class-wcvs-igtf-calculator.php';
        require_once WCVS_PLUGIN_PATH . 'modules/tax-system/includes/class-wcvs-islr-calculator.php';
        
        // Inicializar calculadoras
        new WCVS_IVA_Calculator($this->settings);
        new WCVS_IGTF_Calculator($this->settings);
        new WCVS_ISLR_Calculator($this->settings);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_TAX, 'Calculadoras de impuestos inicializadas');
    }

    /**
     * Configurar impuestos de WooCommerce
     */
    private function setup_woocommerce_taxes() {
        // Configurar tasas de impuestos
        $this->register_tax_rates();
        
        // Configurar clases de impuestos
        $this->register_tax_classes();
    }

    /**
     * Registrar tasas de impuestos
     */
    private function register_tax_rates() {
        // IVA General (16%)
        $iva_rate = array(
            'tax_rate_country' => 'VE',
            'tax_rate_state' => '',
            'tax_rate' => $this->tax_rates['iva'],
            'tax_rate_name' => 'IVA',
            'tax_rate_priority' => 1,
            'tax_rate_compound' => 0,
            'tax_rate_shipping' => 1,
            'tax_rate_order' => 1,
            'tax_rate_class' => '',
        );

        // IGTF (3%)
        $igtf_rate = array(
            'tax_rate_country' => 'VE',
            'tax_rate_state' => '',
            'tax_rate' => $this->tax_rates['igtf'],
            'tax_rate_name' => 'IGTF',
            'tax_rate_priority' => 2,
            'tax_rate_compound' => 0,
            'tax_rate_shipping' => 0,
            'tax_rate_order' => 2,
            'tax_rate_class' => 'igtf',
        );

        // Registrar tasas
        $this->insert_tax_rate($iva_rate);
        $this->insert_tax_rate($igtf_rate);
    }

    /**
     * Insertar tasa de impuesto
     *
     * @param array $rate_data Datos de la tasa
     */
    private function insert_tax_rate($rate_data) {
        global $wpdb;
        
        // Verificar si la tasa ya existe
        $existing_rate = $wpdb->get_var($wpdb->prepare(
            "SELECT tax_rate_id FROM {$wpdb->prefix}woocommerce_tax_rates 
             WHERE tax_rate_country = %s AND tax_rate_name = %s",
            $rate_data['tax_rate_country'],
            $rate_data['tax_rate_name']
        ));

        if (!$existing_rate) {
            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_tax_rates',
                $rate_data,
                array('%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s')
            );
        }
    }

    /**
     * Registrar clases de impuestos
     */
    private function register_tax_classes() {
        // Clase IGTF para transacciones en moneda extranjera
        $igtf_class = array(
            'name' => 'IGTF',
            'slug' => 'igtf',
        );

        // Registrar clase si no existe
        if (!taxonomy_exists('product_tax_class')) {
            return;
        }

        $existing_term = get_term_by('slug', $igtf_class['slug'], 'product_tax_class');
        if (!$existing_term) {
            wp_insert_term($igtf_class['name'], 'product_tax_class', array(
                'slug' => $igtf_class['slug'],
            ));
        }
    }

    /**
     * Configurar hooks de WooCommerce
     */
    private function setup_woocommerce_hooks() {
        // Hook para añadir campos fiscales
        add_action('woocommerce_checkout_process', array($this, 'validate_fiscal_data'));
        
        // Hook para guardar datos fiscales
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_fiscal_data'));
        
        // Hook para mostrar datos fiscales en admin
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_fiscal_data_admin'));
        
        // Hook para mostrar datos fiscales en frontend
        add_action('woocommerce_order_details_after_order_table', array($this, 'display_fiscal_data_frontend'));
    }

    /**
     * Calcular impuestos venezolanos
     */
    public function calculate_venezuelan_taxes() {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }

        // Calcular IVA
        $this->calculate_iva($cart);
        
        // Calcular IGTF si es necesario
        $this->calculate_igtf($cart);
        
        // Calcular ISLR si es necesario
        $this->calculate_islr($cart);
    }

    /**
     * Calcular IVA
     *
     * @param WC_Cart $cart Carrito
     */
    private function calculate_iva($cart) {
        $subtotal = $cart->get_subtotal();
        $iva_rate = $this->tax_rates['iva'] / 100;
        $iva_amount = $subtotal * $iva_rate;
        
        if ($iva_amount > 0) {
            $cart->add_fee('IVA (16%)', $iva_amount);
        }
    }

    /**
     * Calcular IGTF
     *
     * @param WC_Cart $cart Carrito
     */
    private function calculate_igtf($cart) {
        // IGTF solo se aplica a transacciones en moneda extranjera
        $currency = get_woocommerce_currency();
        if ($currency !== 'USD') {
            return;
        }

        $subtotal = $cart->get_subtotal();
        $igtf_rate = $this->tax_rates['igtf'] / 100;
        $igtf_amount = $subtotal * $igtf_rate;
        
        if ($igtf_amount > 0) {
            $cart->add_fee('IGTF (3%)', $igtf_amount);
        }
    }

    /**
     * Calcular ISLR
     *
     * @param WC_Cart $cart Carrito
     */
    private function calculate_islr($cart) {
        // ISLR se calcula según tabla progresiva
        $subtotal = $cart->get_subtotal();
        $islr_amount = $this->calculate_islr_amount($subtotal);
        
        if ($islr_amount > 0) {
            $cart->add_fee('ISLR', $islr_amount);
        }
    }

    /**
     * Calcular monto de ISLR
     *
     * @param float $amount Monto
     * @return float
     */
    private function calculate_islr_amount($amount) {
        // Tabla progresiva de ISLR (simplificada)
        $islr_table = array(
            array('min' => 0, 'max' => 1000, 'rate' => 0.06),
            array('min' => 1000, 'max' => 3000, 'rate' => 0.09),
            array('min' => 3000, 'max' => 6000, 'rate' => 0.12),
            array('min' => 6000, 'max' => 10000, 'rate' => 0.16),
            array('min' => 10000, 'max' => 20000, 'rate' => 0.20),
            array('min' => 20000, 'max' => 30000, 'rate' => 0.24),
            array('min' => 30000, 'max' => 50000, 'rate' => 0.29),
            array('min' => 50000, 'max' => 100000, 'rate' => 0.34),
            array('min' => 100000, 'max' => PHP_FLOAT_MAX, 'rate' => 0.34),
        );

        $total_tax = 0;
        $remaining_amount = $amount;

        foreach ($islr_table as $bracket) {
            if ($remaining_amount <= 0) {
                break;
            }

            $bracket_amount = min($remaining_amount, $bracket['max'] - $bracket['min']);
            $bracket_tax = $bracket_amount * $bracket['rate'];
            $total_tax += $bracket_tax;
            $remaining_amount -= $bracket_amount;
        }

        return $total_tax;
    }

    /**
     * Procesar impuestos del pedido
     *
     * @param int $order_id ID del pedido
     */
    public function process_order_taxes($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Calcular impuestos del pedido
        $taxes = $this->calculate_order_taxes($order);
        
        // Guardar impuestos en el pedido
        $this->save_order_taxes($order, $taxes);
        
        // Generar documento fiscal
        $this->generate_fiscal_document($order, $taxes);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_TAX, "Impuestos procesados para pedido #{$order_id}");
    }

    /**
     * Calcular impuestos del pedido
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function calculate_order_taxes($order) {
        $subtotal = $order->get_subtotal();
        $currency = $order->get_currency();
        
        $taxes = array(
            'iva' => array(
                'rate' => $this->tax_rates['iva'],
                'amount' => $subtotal * ($this->tax_rates['iva'] / 100),
                'base' => $subtotal,
            ),
            'igtf' => array(
                'rate' => $this->tax_rates['igtf'],
                'amount' => 0,
                'base' => 0,
            ),
            'islr' => array(
                'rate' => 0,
                'amount' => 0,
                'base' => 0,
            ),
        );

        // Calcular IGTF si es moneda extranjera
        if ($currency === 'USD') {
            $taxes['igtf']['amount'] = $subtotal * ($this->tax_rates['igtf'] / 100);
            $taxes['igtf']['base'] = $subtotal;
        }

        // Calcular ISLR
        $taxes['islr']['amount'] = $this->calculate_islr_amount($subtotal);
        $taxes['islr']['base'] = $subtotal;
        $taxes['islr']['rate'] = $taxes['islr']['amount'] > 0 ? 
            ($taxes['islr']['amount'] / $subtotal) * 100 : 0;

        return $taxes;
    }

    /**
     * Guardar impuestos en el pedido
     *
     * @param WC_Order $order Pedido
     * @param array $taxes Impuestos
     */
    private function save_order_taxes($order, $taxes) {
        foreach ($taxes as $tax_type => $tax_data) {
            $order->update_meta_data("_tax_{$tax_type}_rate", $tax_data['rate']);
            $order->update_meta_data("_tax_{$tax_type}_amount", $tax_data['amount']);
            $order->update_meta_data("_tax_{$tax_type}_base", $tax_data['base']);
        }
        
        $order->save();
    }

    /**
     * Generar documento fiscal
     *
     * @param WC_Order $order Pedido
     * @param array $taxes Impuestos
     */
    private function generate_fiscal_document($order, $taxes) {
        $document_data = array(
            'order_id' => $order->get_id(),
            'document_type' => 'factura',
            'document_number' => $this->generate_document_number(),
            'issue_date' => current_time('Y-m-d H:i:s'),
            'customer_data' => $this->get_customer_fiscal_data($order),
            'items' => $this->get_order_items_data($order),
            'taxes' => $taxes,
            'totals' => $this->get_order_totals_data($order),
            'status' => 'pendiente',
        );

        // Guardar documento fiscal
        $this->save_fiscal_document($document_data);
        
        // Generar PDF si está habilitado
        if ($this->settings['generate_pdf'] ?? false) {
            $this->generate_fiscal_pdf($document_data);
        }
    }

    /**
     * Generar número de documento
     *
     * @return string
     */
    private function generate_document_number() {
        $prefix = $this->settings['document_prefix'] ?? 'FAC';
        $year = date('Y');
        $month = date('m');
        $sequence = $this->get_next_sequence_number();
        
        return sprintf('%s-%s%s-%06d', $prefix, $year, $month, $sequence);
    }

    /**
     * Obtener siguiente número de secuencia
     *
     * @return int
     */
    private function get_next_sequence_number() {
        global $wpdb;
        
        $current_year = date('Y');
        $current_month = date('m');
        
        $last_number = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(CAST(SUBSTRING_INDEX(document_number, '-', -1) AS UNSIGNED)) 
             FROM {$wpdb->prefix}wcvs_fiscal_documents 
             WHERE YEAR(issue_date) = %d AND MONTH(issue_date) = %d",
            $current_year,
            $current_month
        ));
        
        return ($last_number ?? 0) + 1;
    }

    /**
     * Obtener datos fiscales del cliente
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function get_customer_fiscal_data($order) {
        return array(
            'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
            'address' => $order->get_formatted_billing_address(),
            'rif' => $order->get_meta('_billing_rif'),
            'tax_id' => $order->get_meta('_billing_tax_id'),
        );
    }

    /**
     * Obtener datos de items del pedido
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function get_order_items_data($order) {
        $items = array();
        
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $items[] = array(
                'name' => $item->get_name(),
                'sku' => $product ? $product->get_sku() : '',
                'quantity' => $item->get_quantity(),
                'price' => $item->get_total() / $item->get_quantity(),
                'total' => $item->get_total(),
                'tax_class' => $product ? $product->get_tax_class() : '',
            );
        }
        
        return $items;
    }

    /**
     * Obtener datos de totales del pedido
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function get_order_totals_data($order) {
        return array(
            'subtotal' => $order->get_subtotal(),
            'shipping' => $order->get_shipping_total(),
            'taxes' => $order->get_total_tax(),
            'total' => $order->get_total(),
            'currency' => $order->get_currency(),
        );
    }

    /**
     * Guardar documento fiscal
     *
     * @param array $document_data Datos del documento
     */
    private function save_fiscal_document($document_data) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'wcvs_fiscal_documents',
            array(
                'order_id' => $document_data['order_id'],
                'document_type' => $document_data['document_type'],
                'document_number' => $document_data['document_number'],
                'issue_date' => $document_data['issue_date'],
                'customer_data' => maybe_serialize($document_data['customer_data']),
                'items_data' => maybe_serialize($document_data['items']),
                'taxes_data' => maybe_serialize($document_data['taxes']),
                'totals_data' => maybe_serialize($document_data['totals']),
                'status' => $document_data['status'],
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Generar PDF fiscal
     *
     * @param array $document_data Datos del documento
     */
    private function generate_fiscal_pdf($document_data) {
        // Implementar generación de PDF
        // Por ahora solo log
        WCVS_Logger::info(WCVS_Logger::CONTEXT_TAX, "PDF fiscal generado para documento {$document_data['document_number']}");
    }

    /**
     * Validar datos fiscales
     */
    public function validate_fiscal_data() {
        // Validar RIF si es requerido
        if ($this->settings['require_rif'] ?? false) {
            $rif = $_POST['billing_rif'] ?? '';
            if (empty($rif)) {
                wc_add_notice('El RIF es requerido para la facturación.', 'error');
            } elseif (!$this->validate_rif($rif)) {
                wc_add_notice('El RIF ingresado no es válido.', 'error');
            }
        }
    }

    /**
     * Validar RIF venezolano
     *
     * @param string $rif RIF
     * @return bool
     */
    private function validate_rif($rif) {
        // Patrón básico de RIF venezolano
        $pattern = '/^[JGVEPNC]-[0-9]{8}-[0-9]$/';
        return preg_match($pattern, $rif);
    }

    /**
     * Guardar datos fiscales
     *
     * @param int $order_id ID del pedido
     */
    public function save_fiscal_data($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Guardar RIF
        if (isset($_POST['billing_rif'])) {
            $order->update_meta_data('_billing_rif', sanitize_text_field($_POST['billing_rif']));
        }

        // Guardar otros datos fiscales
        $fiscal_fields = array('billing_tax_id', 'billing_fiscal_name', 'billing_fiscal_address');
        foreach ($fiscal_fields as $field) {
            if (isset($_POST[$field])) {
                $order->update_meta_data('_' . $field, sanitize_text_field($_POST[$field]));
            }
        }

        $order->save();
    }

    /**
     * Mostrar datos fiscales en admin
     *
     * @param WC_Order $order Pedido
     */
    public function display_fiscal_data_admin($order) {
        $rif = $order->get_meta('_billing_rif');
        $tax_id = $order->get_meta('_billing_tax_id');
        
        if ($rif || $tax_id) {
            echo '<div class="wcvs-fiscal-data">';
            echo '<h3>Datos Fiscales</h3>';
            if ($rif) {
                echo '<p><strong>RIF:</strong> ' . esc_html($rif) . '</p>';
            }
            if ($tax_id) {
                echo '<p><strong>ID Fiscal:</strong> ' . esc_html($tax_id) . '</p>';
            }
            echo '</div>';
        }
    }

    /**
     * Mostrar datos fiscales en frontend
     *
     * @param WC_Order $order Pedido
     */
    public function display_fiscal_data_frontend($order) {
        $rif = $order->get_meta('_billing_rif');
        if ($rif) {
            echo '<div class="wcvs-fiscal-data-frontend">';
            echo '<h3>Información Fiscal</h3>';
            echo '<p><strong>RIF:</strong> ' . esc_html($rif) . '</p>';
            echo '</div>';
        }
    }

    /**
     * Manejar cambio de estado del pedido
     *
     * @param int $order_id ID del pedido
     * @param string $old_status Estado anterior
     * @param string $new_status Estado nuevo
     */
    public function handle_order_status_change($order_id, $old_status, $new_status) {
        if ($new_status === 'completed') {
            $this->finalize_fiscal_document($order_id);
        }
    }

    /**
     * Finalizar documento fiscal
     *
     * @param int $order_id ID del pedido
     */
    private function finalize_fiscal_document($order_id) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'wcvs_fiscal_documents',
            array('status' => 'procesado'),
            array('order_id' => $order_id),
            array('%s'),
            array('%d')
        );
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_TAX, "Documento fiscal finalizado para pedido #{$order_id}");
    }

    /**
     * Cargar assets (CSS y JS)
     */
    private function enqueue_assets() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_tax_assets'));
    }

    /**
     * Encolar assets de impuestos
     */
    public function enqueue_tax_assets() {
        if (is_checkout()) {
            wp_enqueue_style(
                'wcvs-tax-system',
                WCVS_PLUGIN_URL . 'modules/tax-system/css/wcvs-tax-system.css',
                array(),
                WCVS_VERSION
            );

            wp_enqueue_script(
                'wcvs-tax-system',
                WCVS_PLUGIN_URL . 'modules/tax-system/js/wcvs-tax-system.js',
                array('jquery'),
                WCVS_VERSION,
                true
            );

            wp_localize_script('wcvs-tax-system', 'wcvs_tax_system', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcvs_tax_system_nonce'),
                'strings' => array(
                    'loading' => 'Calculando impuestos...',
                    'error' => 'Error al calcular impuestos',
                    'success' => 'Impuestos calculados correctamente'
                )
            ));
        }
    }

    /**
     * Manejar activación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_activation($module_key, $module_data) {
        if ($module_key === 'tax_system') {
            $this->init();
            WCVS_Logger::success(WCVS_Logger::CONTEXT_TAX, 'Módulo Tax System activado');
        }
    }

    /**
     * Manejar desactivación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_deactivation($module_key, $module_data) {
        if ($module_key === 'tax_system') {
            // Limpiar hooks
            remove_action('woocommerce_cart_calculate_fees', array($this, 'calculate_venezuelan_taxes'));
            remove_action('woocommerce_checkout_order_processed', array($this, 'process_order_taxes'));
            
            WCVS_Logger::info(WCVS_Logger::CONTEXT_TAX, 'Módulo Tax System desactivado');
        }
    }

    /**
     * Obtener estadísticas del módulo
     *
     * @return array
     */
    public function get_module_stats() {
        global $wpdb;
        
        $total_documents = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->prefix}wcvs_fiscal_documents
        ");
        
        $total_tax_collected = $wpdb->get_var("
            SELECT SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva.amount') AS DECIMAL(10,2)))
            FROM {$wpdb->prefix}wcvs_fiscal_documents
        ");

        return array(
            'tax_rates' => $this->tax_rates,
            'document_types' => $this->document_types,
            'fiscal_statuses' => $this->fiscal_statuses,
            'total_documents' => intval($total_documents),
            'total_tax_collected' => floatval($total_tax_collected),
            'settings' => $this->settings
        );
    }
}
