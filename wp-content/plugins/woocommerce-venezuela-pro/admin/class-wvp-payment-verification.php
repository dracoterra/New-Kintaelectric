<?php
/**
 * Clase para verificación de pagos pendientes
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Payment_Verification {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Obtener instancia del plugin de forma segura
        if (class_exists('WooCommerce_Venezuela_Pro')) {
            $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        } else {
            $this->plugin = null;
        }
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Añadir menú de verificación de pagos
        add_action('admin_menu', array($this, 'add_verification_menu'));
        
        // Manejar AJAX para verificar pagos
        add_action('wp_ajax_wvp_verify_payment', array($this, 'verify_payment'));
        add_action('wp_ajax_wvp_upload_proof', array($this, 'upload_payment_proof'));
        
        // Enqueue scripts y estilos
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Añadir menú de verificación de pagos
     */
    public function add_verification_menu() {
        add_submenu_page(
            'woocommerce',
            __('Verificar Pagos', 'wvp'),
            __('Verificar Pagos', 'wvp'),
            'manage_woocommerce',
            'wvp-verify-payments',
            array($this, 'render_verification_page')
        );
    }
    
    /**
     * Renderizar página de verificación de pagos
     */
    public function render_verification_page() {
        // Obtener pedidos pendientes
        $pending_orders = $this->get_pending_orders();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Centro de Conciliación de Pagos', 'wvp'); ?></h1>
            
            <div class="wvp-verification-container">
                <div class="wvp-verification-header">
                    <h2><?php _e('Pedidos Pendientes de Verificación', 'wvp'); ?></h2>
                    <p><?php _e('Verifique los pagos de los pedidos en estado "En Espera" y confirme su recepción.', 'wvp'); ?></p>
                </div>
                
                <?php if (empty($pending_orders)): ?>
                    <div class="wvp-no-pending-orders">
                        <p><?php _e('No hay pedidos pendientes de verificación en este momento.', 'wvp'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="wvp-pending-orders-table">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('N° Pedido', 'wvp'); ?></th>
                                    <th><?php _e('Cliente', 'wvp'); ?></th>
                                    <th><?php _e('Monto', 'wvp'); ?></th>
                                    <th><?php _e('Método de Pago', 'wvp'); ?></th>
                                    <th><?php _e('Referencia de Pago', 'wvp'); ?></th>
                                    <th><?php _e('Fecha', 'wvp'); ?></th>
                                    <th><?php _e('Acciones', 'wvp'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_orders as $order): ?>
                                    <?php $this->render_order_row($order); ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizar fila de pedido en la tabla
     */
    private function render_order_row($order) {
        $payment_reference = $order->get_meta('_payment_confirmation');
        $payment_method = $order->get_payment_method_title();
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $order_date = $order->get_date_created()->format('d/m/Y H:i');
        
        ?>
        <tr data-order-id="<?php echo $order->get_id(); ?>">
            <td>
                <strong>#<?php echo $order->get_id(); ?></strong>
            </td>
            <td>
                <?php echo esc_html($customer_name); ?><br>
                <small><?php echo esc_html($order->get_billing_email()); ?></small>
            </td>
            <td>
                <strong><?php echo $order->get_formatted_order_total(); ?></strong>
            </td>
            <td>
                <?php echo esc_html($payment_method); ?>
            </td>
            <td>
                <?php if ($payment_reference): ?>
                    <code><?php echo esc_html($payment_reference); ?></code>
                <?php else: ?>
                    <em><?php _e('Sin referencia', 'wvp'); ?></em>
                <?php endif; ?>
            </td>
            <td>
                <?php echo esc_html($order_date); ?>
            </td>
            <td>
                <div class="wvp-verification-actions">
                    <button type="button" 
                            class="button button-primary wvp-verify-payment-btn" 
                            data-order-id="<?php echo $order->get_id(); ?>">
                        <?php _e('Verificar Pago', 'wvp'); ?>
                    </button>
                    
                    <div class="wvp-upload-proof" style="margin-top: 5px;">
                        <input type="file" 
                               id="wvp-proof-<?php echo $order->get_id(); ?>" 
                               class="wvp-proof-file" 
                               data-order-id="<?php echo $order->get_id(); ?>"
                               accept="image/*,.pdf">
                        <button type="button" 
                                class="button button-secondary wvp-upload-proof-btn" 
                                data-order-id="<?php echo $order->get_id(); ?>">
                            <?php _e('Subir Comprobante', 'wvp'); ?>
                        </button>
                    </div>
                </div>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Obtener pedidos pendientes
     */
    private function get_pending_orders() {
        $args = array(
            'status' => 'on-hold',
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        return wc_get_orders($args);
    }
    
    /**
     * Enqueue scripts y estilos administrativos
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'woocommerce_page_wvp-verify-payments') {
            return;
        }
        
        wp_enqueue_script(
            'wvp-verification-admin',
            WVP_PLUGIN_URL . 'assets/js/admin-verification.js',
            array('jquery'),
            WVP_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wvp-verification-admin',
            WVP_PLUGIN_URL . 'assets/css/admin-verification.css',
            array(),
            WVP_VERSION
        );
        
        wp_localize_script('wvp-verification-admin', 'wvp_verification', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvp_verification_nonce'),
            'i18n' => array(
                'verifying' => __('Verificando pago...', 'wvp'),
                'uploading' => __('Subiendo comprobante...', 'wvp'),
                'success' => __('Pago verificado correctamente', 'wvp'),
                'error' => __('Error al verificar el pago', 'wvp'),
                'upload_success' => __('Comprobante subido correctamente', 'wvp'),
                'upload_error' => __('Error al subir el comprobante', 'wvp'),
                'confirm_verification' => __('¿Está seguro de que desea verificar este pago?', 'wvp')
            )
        ));
    }
    
    /**
     * Verificar pago via AJAX
     */
    public function verify_payment() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_verification_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad', 'wvp')));
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'wvp')));
        }
        
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error(array('message' => __('Pedido no encontrado', 'wvp')));
        }
        
        // Cambiar estado del pedido a "Procesando"
        $order->update_status('processing', __('Pago verificado y confirmado', 'wvp'));
        
        // Añadir nota privada
        $current_user = wp_get_current_user();
        $note = sprintf(
            __('Pago verificado por %s el %s', 'wvp'),
            $current_user->display_name,
            current_time('d/m/Y H:i:s')
        );
        $order->add_order_note($note, false, true);
        
        // Guardar metadato de verificación
        $order->update_meta_data('_payment_verified', true);
        $order->update_meta_data('_payment_verified_by', $current_user->ID);
        $order->update_meta_data('_payment_verified_date', current_time('mysql'));
        
        $order->save();
        
        wp_send_json_success(array(
            'message' => __('Pago verificado correctamente', 'wvp'),
            'order_id' => $order_id
        ));
    }
    
    /**
     * Subir comprobante de pago via AJAX
     */
    public function upload_payment_proof() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_verification_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad', 'wvp')));
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Sin permisos', 'wvp')));
        }
        
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error(array('message' => __('Pedido no encontrado', 'wvp')));
        }
        
        // Verificar que se subió un archivo
        if (!isset($_FILES['proof_file']) || $_FILES['proof_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => __('Error al subir el archivo', 'wvp')));
        }
        
        $file = $_FILES['proof_file'];
        
        // Validar tipo de archivo
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf');
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(array('message' => __('Tipo de archivo no permitido. Solo se permiten imágenes y PDFs', 'wvp')));
        }
        
        // Validar tamaño (máximo 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            wp_send_json_error(array('message' => __('El archivo es demasiado grande. Máximo 5MB', 'wvp')));
        }
        
        // Crear directorio de uploads si no existe
        $upload_dir = wp_upload_dir();
        $wvp_dir = $upload_dir['basedir'] . '/wvp-payment-proofs/';
        if (!file_exists($wvp_dir)) {
            wp_mkdir_p($wvp_dir);
        }
        
        // Generar nombre único para el archivo
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = 'proof_' . $order_id . '_' . time() . '.' . $file_extension;
        $file_path = $wvp_dir . $file_name;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $file_url = $upload_dir['baseurl'] . '/wvp-payment-proofs/' . $file_name;
            
            // Guardar URL del comprobante en el pedido
            $order->update_meta_data('_payment_proof_url', $file_url);
            $order->save();
            
            // Añadir nota al pedido
            $note = sprintf(
                __('Comprobante de pago subido: %s', 'wvp'),
                '<a href="' . esc_url($file_url) . '" target="_blank">Ver comprobante</a>'
            );
            $order->add_order_note($note, false, true);
            
            wp_send_json_success(array(
                'message' => __('Comprobante subido correctamente', 'wvp'),
                'file_url' => $file_url
            ));
        } else {
            wp_send_json_error(array('message' => __('Error al guardar el archivo', 'wvp')));
        }
    }
}
