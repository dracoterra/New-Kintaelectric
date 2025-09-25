<?php
/**
 * Módulo de Facturación Electrónica - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar la facturación electrónica
 */
class WCVS_Electronic_Billing {

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
     * Tipos de documentos electrónicos
     *
     * @var array
     */
    private $document_types = array(
        'invoice' => 'Factura',
        'credit_note' => 'Nota de Crédito',
        'debit_note' => 'Nota de Débito',
        'receipt' => 'Recibo',
        'proforma' => 'Proforma'
    );

    /**
     * Estados de documentos
     *
     * @var array
     */
    private $document_states = array(
        'draft' => 'Borrador',
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
        'cancelled' => 'Cancelado'
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WCVS_Core::get_instance();
        $this->settings = $this->plugin->get_settings()->get('electronic_billing', array());
        
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

        // Configurar hooks de WooCommerce
        $this->setup_woocommerce_hooks();

        // Cargar scripts y estilos
        $this->enqueue_assets();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, 'Módulo Electronic Billing inicializado');
    }

    /**
     * Cargar dependencias
     */
    private function load_dependencies() {
        require_once WCVS_PLUGIN_PATH . 'modules/electronic-billing/includes/class-wcvs-invoice-generator.php';
        require_once WCVS_PLUGIN_PATH . 'modules/electronic-billing/includes/class-wcvs-document-templates.php';
        require_once WCVS_PLUGIN_PATH . 'modules/electronic-billing/includes/class-wcvs-digital-signature.php';
        require_once WCVS_PLUGIN_PATH . 'modules/electronic-billing/includes/class-wcvs-seniat-integration.php';
        require_once WCVS_PLUGIN_PATH . 'modules/electronic-billing/includes/class-wcvs-export-formats.php';
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cuando se activa el módulo
        add_action('wcvs_module_activated', array($this, 'handle_module_activation'), 10, 2);
        
        // Hook para cuando se desactiva el módulo
        add_action('wcvs_module_deactivated', array($this, 'handle_module_deactivation'), 10, 2);
        
        // Hook para generar facturas automáticamente
        add_action('woocommerce_order_status_completed', array($this, 'generate_invoice_automatically'));
        add_action('woocommerce_order_status_processing', array($this, 'generate_invoice_automatically'));
    }

    /**
     * Configurar hooks de WooCommerce
     */
    private function setup_woocommerce_hooks() {
        // Hook para generar facturas
        add_action('woocommerce_checkout_order_processed', array($this, 'create_invoice_draft'));
        
        // Hook para campos adicionales en checkout
        add_action('woocommerce_after_checkout_billing_form', array($this, 'add_billing_fields'));
        
        // Hook para validar datos fiscales
        add_action('woocommerce_checkout_process', array($this, 'validate_billing_data'));
        
        // Hook para mostrar facturas en admin
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_invoice_info_admin'));
        
        // Hook para mostrar facturas en frontend
        add_action('woocommerce_view_order', array($this, 'display_invoice_info_customer'));
        
        // Hook para AJAX
        add_action('wp_ajax_wcvs_generate_invoice', array($this, 'handle_generate_invoice_ajax'));
        add_action('wp_ajax_wcvs_download_invoice', array($this, 'handle_download_invoice_ajax'));
    }

    /**
     * Crear borrador de factura
     *
     * @param int $order_id ID del pedido
     */
    public function create_invoice_draft($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Verificar si ya existe una factura
        $existing_invoice = $order->get_meta('_wcvs_invoice_number');
        if (!empty($existing_invoice)) {
            return;
        }

        // Crear borrador de factura
        $invoice_data = array(
            'order_id' => $order_id,
            'document_type' => 'invoice',
            'status' => 'draft',
            'created_at' => current_time('mysql'),
            'customer_data' => $this->get_customer_data($order),
            'order_data' => $this->get_order_data($order)
        );

        // Guardar datos de la factura
        $order->update_meta_data('_wcvs_invoice_data', $invoice_data);
        $order->update_meta_data('_wcvs_invoice_status', 'draft');
        $order->save();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "Borrador de factura creado para pedido #{$order_id}");
    }

    /**
     * Generar factura automáticamente
     *
     * @param int $order_id ID del pedido
     */
    public function generate_invoice_automatically($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Verificar configuración
        if (!$this->settings['auto_generate_invoices'] ?? false) {
            return;
        }

        // Verificar si ya existe una factura aprobada
        $invoice_status = $order->get_meta('_wcvs_invoice_status');
        if ($invoice_status === 'approved') {
            return;
        }

        // Generar factura
        $this->generate_invoice($order_id);
    }

    /**
     * Generar factura
     *
     * @param int $order_id ID del pedido
     * @return array|false
     */
    public function generate_invoice($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        // Generar número de factura
        $invoice_number = $this->generate_invoice_number();
        
        // Obtener datos de la factura
        $invoice_data = $order->get_meta('_wcvs_invoice_data');
        if (empty($invoice_data)) {
            $invoice_data = array(
                'order_id' => $order_id,
                'document_type' => 'invoice',
                'status' => 'pending',
                'created_at' => current_time('mysql'),
                'customer_data' => $this->get_customer_data($order),
                'order_data' => $this->get_order_data($order)
            );
        }

        // Actualizar datos de la factura
        $invoice_data['invoice_number'] = $invoice_number;
        $invoice_data['status'] = 'pending';
        $invoice_data['generated_at'] = current_time('mysql');

        // Guardar datos de la factura
        $order->update_meta_data('_wcvs_invoice_number', $invoice_number);
        $order->update_meta_data('_wcvs_invoice_data', $invoice_data);
        $order->update_meta_data('_wcvs_invoice_status', 'pending');
        $order->save();

        // Generar documentos
        $this->generate_documents($order_id, $invoice_data);

        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "Factura #{$invoice_number} generada para pedido #{$order_id}");

        return $invoice_data;
    }

    /**
     * Generar número de factura
     *
     * @return string
     */
    private function generate_invoice_number() {
        $prefix = $this->settings['invoice_prefix'] ?? 'FAC';
        $year = date('Y');
        $month = date('m');
        
        // Obtener último número de factura del mes
        $last_invoice = $this->get_last_invoice_number($year, $month);
        $next_number = $last_invoice + 1;
        
        return sprintf('%s-%s%s-%06d', $prefix, $year, $month, $next_number);
    }

    /**
     * Obtener último número de factura del mes
     *
     * @param string $year Año
     * @param string $month Mes
     * @return int
     */
    private function get_last_invoice_number($year, $month) {
        global $wpdb;
        
        $meta_key = '_wcvs_invoice_number';
        $pattern = "%-{$year}{$month}-%";
        
        $result = $wpdb->get_var($wpdb->prepare("
            SELECT MAX(CAST(SUBSTRING_INDEX(meta_value, '-', -1) AS UNSIGNED))
            FROM {$wpdb->postmeta}
            WHERE meta_key = %s
            AND meta_value LIKE %s
        ", $meta_key, $pattern));
        
        return $result ?: 0;
    }

    /**
     * Obtener datos del cliente
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function get_customer_data($order) {
        return array(
            'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
            'address' => $order->get_formatted_billing_address(),
            'rif' => $order->get_meta('_billing_rif'),
            'company' => $order->get_billing_company()
        );
    }

    /**
     * Obtener datos del pedido
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function get_order_data($order) {
        $items = array();
        foreach ($order->get_items() as $item) {
            $items[] = array(
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => $item->get_total(),
                'tax' => $item->get_total_tax()
            );
        }

        return array(
            'items' => $items,
            'subtotal' => $order->get_subtotal(),
            'tax_total' => $order->get_total_tax(),
            'shipping_total' => $order->get_shipping_total(),
            'total' => $order->get_total(),
            'currency' => $order->get_currency(),
            'payment_method' => $order->get_payment_method_title(),
            'date' => $order->get_date_created()->format('Y-m-d H:i:s')
        );
    }

    /**
     * Generar documentos
     *
     * @param int $order_id ID del pedido
     * @param array $invoice_data Datos de la factura
     */
    private function generate_documents($order_id, $invoice_data) {
        // Generar PDF
        $this->generate_pdf($order_id, $invoice_data);
        
        // Generar XML
        $this->generate_xml($order_id, $invoice_data);
        
        // Firmar digitalmente
        $this->sign_document($order_id, $invoice_data);
    }

    /**
     * Generar PDF
     *
     * @param int $order_id ID del pedido
     * @param array $invoice_data Datos de la factura
     */
    private function generate_pdf($order_id, $invoice_data) {
        // Implementar generación de PDF
        // Por ahora, solo registrar en logs
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "PDF generado para factura #{$invoice_data['invoice_number']}");
    }

    /**
     * Generar XML
     *
     * @param int $order_id ID del pedido
     * @param array $invoice_data Datos de la factura
     */
    private function generate_xml($order_id, $invoice_data) {
        // Implementar generación de XML
        // Por ahora, solo registrar en logs
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "XML generado para factura #{$invoice_data['invoice_number']}");
    }

    /**
     * Firmar documento
     *
     * @param int $order_id ID del pedido
     * @param array $invoice_data Datos de la factura
     */
    private function sign_document($order_id, $invoice_data) {
        // Implementar firma digital
        // Por ahora, solo registrar en logs
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "Documento firmado para factura #{$invoice_data['invoice_number']}");
    }

    /**
     * Añadir campos de facturación en checkout
     */
    public function add_billing_fields() {
        ?>
        <div class="wcvs-billing-fields">
            <h3><?php esc_html_e('Información Fiscal', 'wcvs'); ?></h3>
            <p class="form-row form-row-wide">
                <label for="billing_rif"><?php esc_html_e('RIF', 'wcvs'); ?> <span class="required">*</span></label>
                <input type="text" class="input-text" name="billing_rif" id="billing_rif" placeholder="<?php esc_attr_e('Ej: J-12345678-9', 'wcvs'); ?>" required>
            </p>
            <p class="form-row form-row-wide">
                <label for="billing_company"><?php esc_html_e('Razón Social', 'wcvs'); ?></label>
                <input type="text" class="input-text" name="billing_company" id="billing_company" placeholder="<?php esc_attr_e('Nombre de la empresa', 'wcvs'); ?>">
            </p>
        </div>
        <?php
    }

    /**
     * Validar datos de facturación
     */
    public function validate_billing_data() {
        $rif = $_POST['billing_rif'] ?? '';
        
        if (empty($rif)) {
            wc_add_notice('El RIF es obligatorio para la facturación.', 'error');
            return;
        }

        // Validar formato del RIF
        if (!$this->validate_rif_format($rif)) {
            wc_add_notice('El formato del RIF no es válido.', 'error');
            return;
        }
    }

    /**
     * Validar formato del RIF
     *
     * @param string $rif RIF a validar
     * @return bool
     */
    private function validate_rif_format($rif) {
        // Patrón para RIF venezolano: X-XXXXXXXX-X
        $pattern = '/^[VEGJ]-[0-9]{8}-[0-9]$/';
        return preg_match($pattern, $rif);
    }

    /**
     * Mostrar información de factura en admin
     *
     * @param WC_Order $order Pedido
     */
    public function display_invoice_info_admin($order) {
        $invoice_number = $order->get_meta('_wcvs_invoice_number');
        $invoice_status = $order->get_meta('_wcvs_invoice_status');
        
        if (empty($invoice_number)) {
            return;
        }

        ?>
        <div class="wcvs-invoice-info-admin">
            <h3><?php esc_html_e('Información de Factura', 'wcvs'); ?></h3>
            <p><strong><?php esc_html_e('Número de Factura:', 'wcvs'); ?></strong> <?php echo esc_html($invoice_number); ?></p>
            <p><strong><?php esc_html_e('Estado:', 'wcvs'); ?></strong> <?php echo esc_html($this->document_states[$invoice_status] ?? $invoice_status); ?></p>
            <p>
                <button type="button" class="button button-primary" onclick="wcvsDownloadInvoice(<?php echo $order->get_id(); ?>)">
                    <?php esc_html_e('Descargar Factura', 'wcvs'); ?>
                </button>
            </p>
        </div>
        <?php
    }

    /**
     * Mostrar información de factura en frontend
     *
     * @param int $order_id ID del pedido
     */
    public function display_invoice_info_customer($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $invoice_number = $order->get_meta('_wcvs_invoice_number');
        $invoice_status = $order->get_meta('_wcvs_invoice_status');
        
        if (empty($invoice_number)) {
            return;
        }

        ?>
        <div class="wcvs-invoice-info-customer">
            <h2><?php esc_html_e('Factura Electrónica', 'wcvs'); ?></h2>
            <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
                <tfoot>
                    <tr>
                        <th><?php esc_html_e('Número de Factura:', 'wcvs'); ?></th>
                        <td><?php echo esc_html($invoice_number); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Estado:', 'wcvs'); ?></th>
                        <td><?php echo esc_html($this->document_states[$invoice_status] ?? $invoice_status); ?></td>
                    </tr>
                </tfoot>
            </table>
            <p>
                <a href="<?php echo admin_url('admin-ajax.php?action=wcvs_download_invoice&order_id=' . $order_id); ?>" class="button">
                    <?php esc_html_e('Descargar Factura', 'wcvs'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Manejar AJAX para generar factura
     */
    public function handle_generate_invoice_ajax() {
        check_ajax_referer('wcvs_generate_invoice_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('No tienes permisos para generar facturas.', 'wcvs')));
        }

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        if (!$order_id) {
            wp_send_json_error(array('message' => __('ID de pedido inválido.', 'wcvs')));
        }

        $invoice_data = $this->generate_invoice($order_id);
        
        if ($invoice_data) {
            wp_send_json_success(array(
                'message' => __('Factura generada correctamente.', 'wcvs'),
                'invoice_number' => $invoice_data['invoice_number']
            ));
        } else {
            wp_send_json_error(array('message' => __('Error al generar la factura.', 'wcvs')));
        }
    }

    /**
     * Manejar AJAX para descargar factura
     */
    public function handle_download_invoice_ajax() {
        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        if (!$order_id) {
            wp_die(__('ID de pedido inválido.', 'wcvs'));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_die(__('Pedido no encontrado.', 'wcvs'));
        }

        $invoice_number = $order->get_meta('_wcvs_invoice_number');
        if (empty($invoice_number)) {
            wp_die(__('No se encontró factura para este pedido.', 'wcvs'));
        }

        // Generar y descargar PDF
        $this->download_invoice_pdf($order_id, $invoice_number);
    }

    /**
     * Descargar PDF de factura
     *
     * @param int $order_id ID del pedido
     * @param string $invoice_number Número de factura
     */
    private function download_invoice_pdf($order_id, $invoice_number) {
        // Implementar descarga de PDF
        // Por ahora, solo mostrar mensaje
        wp_die(__('Funcionalidad de descarga de PDF en desarrollo.', 'wcvs'));
    }

    /**
     * Cargar assets (CSS y JS)
     */
    private function enqueue_assets() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_billing_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Encolar assets de facturación
     */
    public function enqueue_billing_assets() {
        if (is_checkout()) {
            wp_enqueue_style(
                'wcvs-electronic-billing',
                WCVS_PLUGIN_URL . 'modules/electronic-billing/css/wcvs-electronic-billing.css',
                array(),
                WCVS_VERSION
            );

            wp_enqueue_script(
                'wcvs-electronic-billing',
                WCVS_PLUGIN_URL . 'modules/electronic-billing/js/wcvs-electronic-billing.js',
                array('jquery'),
                WCVS_VERSION,
                true
            );

            wp_localize_script('wcvs-electronic-billing', 'wcvs_electronic_billing', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcvs_electronic_billing_nonce'),
                'strings' => array(
                    'rif_required' => __('El RIF es obligatorio.', 'wcvs'),
                    'rif_invalid' => __('El formato del RIF no es válido.', 'wcvs'),
                    'generating_invoice' => __('Generando factura...', 'wcvs'),
                    'invoice_generated' => __('Factura generada correctamente.', 'wcvs'),
                    'invoice_error' => __('Error al generar la factura.', 'wcvs')
                )
            ));
        }
    }

    /**
     * Encolar assets de admin
     */
    public function enqueue_admin_assets($hook) {
        global $post;
        
        if ($hook == 'post.php' && $post->post_type == 'shop_order') {
            wp_enqueue_script(
                'wcvs-electronic-billing-admin',
                WCVS_PLUGIN_URL . 'modules/electronic-billing/js/wcvs-electronic-billing-admin.js',
                array('jquery'),
                WCVS_VERSION,
                true
            );

            wp_localize_script('wcvs-electronic-billing-admin', 'wcvs_electronic_billing_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcvs_generate_invoice_nonce'),
                'strings' => array(
                    'generating_invoice' => __('Generando factura...', 'wcvs'),
                    'invoice_generated' => __('Factura generada correctamente.', 'wcvs'),
                    'invoice_error' => __('Error al generar la factura.', 'wcvs')
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
        if ($module_key === 'electronic_billing') {
            $this->init();
            WCVS_Logger::success(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, 'Módulo Electronic Billing activado');
        }
    }

    /**
     * Manejar desactivación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_deactivation($module_key, $module_data) {
        if ($module_key === 'electronic_billing') {
            // Limpiar hooks
            remove_action('woocommerce_checkout_order_processed', array($this, 'create_invoice_draft'));
            remove_action('woocommerce_order_status_completed', array($this, 'generate_invoice_automatically'));
            
            WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, 'Módulo Electronic Billing desactivado');
        }
    }

    /**
     * Obtener estadísticas del módulo
     *
     * @return array
     */
    public function get_module_stats() {
        global $wpdb;
        
        $total_invoices = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_wcvs_invoice_number'
        ");

        $pending_invoices = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_wcvs_invoice_status'
            AND meta_value = 'pending'
        ");

        $approved_invoices = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_wcvs_invoice_status'
            AND meta_value = 'approved'
        ");

        return array(
            'total_invoices' => $total_invoices ?: 0,
            'pending_invoices' => $pending_invoices ?: 0,
            'approved_invoices' => $approved_invoices ?: 0,
            'document_types' => $this->document_types,
            'document_states' => $this->document_states,
            'settings' => $this->settings
        );
    }
}
