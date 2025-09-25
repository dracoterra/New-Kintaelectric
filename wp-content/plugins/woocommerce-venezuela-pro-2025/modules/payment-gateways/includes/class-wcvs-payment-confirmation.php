<?php
/**
 * Sistema de Confirmación de Pagos - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para manejar la confirmación de pagos
 */
class WCVS_Payment_Confirmation {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hooks para confirmación de pagos
        add_action('wp_ajax_wcvs_confirm_payment', array($this, 'ajax_confirm_payment'));
        add_action('wp_ajax_wcvs_reject_payment', array($this, 'ajax_reject_payment'));
        add_action('wp_ajax_wcvs_upload_payment_proof', array($this, 'ajax_upload_payment_proof'));
        
        // Hooks para admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Hooks para emails
        add_action('wcvs_payment_confirmed', array($this, 'send_confirmation_email'), 10, 2);
        add_action('wcvs_payment_rejected', array($this, 'send_rejection_email'), 10, 2);
    }

    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Confirmación de Pagos',
            'Confirmación de Pagos',
            'manage_woocommerce',
            'wcvs-payment-confirmation',
            array($this, 'admin_page')
        );
    }

    /**
     * Encolar scripts de administración
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'woocommerce_page_wcvs-payment-confirmation') {
            wp_enqueue_script(
                'wcvs-payment-confirmation-admin',
                WCVS_PLUGIN_URL . 'modules/payment-gateways/js/wcvs-payment-confirmation-admin.js',
                array('jquery'),
                WCVS_VERSION,
                true
            );

            wp_localize_script('wcvs-payment-confirmation-admin', 'wcvs_payment_confirmation', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcvs_payment_confirmation_nonce'),
                'strings' => array(
                    'confirm' => 'Confirmar Pago',
                    'reject' => 'Rechazar Pago',
                    'loading' => 'Procesando...',
                    'success' => 'Operación exitosa',
                    'error' => 'Error en la operación'
                )
            ));
        }
    }

    /**
     * Página de administración
     */
    public function admin_page() {
        $orders = $this->get_pending_payment_orders();
        
        echo '<div class="wrap">';
        echo '<h1>Confirmación de Pagos</h1>';
        
        if (empty($orders)) {
            echo '<p>No hay pedidos pendientes de confirmación de pago.</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Pedido</th>';
            echo '<th>Cliente</th>';
            echo '<th>Método de Pago</th>';
            echo '<th>Monto</th>';
            echo '<th>Fecha</th>';
            echo '<th>Comprobante</th>';
            echo '<th>Acciones</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($orders as $order) {
                $this->render_order_row($order);
            }
            
            echo '</tbody>';
            echo '</table>';
        }
        
        echo '</div>';
    }

    /**
     * Renderizar fila de pedido
     *
     * @param WC_Order $order Pedido
     */
    private function render_order_row($order) {
        $payment_method = $order->get_payment_method();
        $payment_method_title = $order->get_payment_method_title();
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $total = $order->get_total();
        $currency = $order->get_currency();
        $date = $order->get_date_created()->date('Y-m-d H:i:s');
        
        echo '<tr>';
        echo '<td><a href="' . admin_url('post.php?post=' . $order->get_id() . '&action=edit') . '">#' . $order->get_id() . '</a></td>';
        echo '<td>' . esc_html($customer_name) . '</td>';
        echo '<td>' . esc_html($payment_method_title) . '</td>';
        echo '<td>' . $currency . ' ' . number_format($total, 2) . '</td>';
        echo '<td>' . esc_html($date) . '</td>';
        echo '<td>' . $this->get_payment_proof_link($order) . '</td>';
        echo '<td>';
        echo '<button class="button button-primary wcvs-confirm-payment" data-order-id="' . $order->get_id() . '">Confirmar</button> ';
        echo '<button class="button button-secondary wcvs-reject-payment" data-order-id="' . $order->get_id() . '">Rechazar</button>';
        echo '</td>';
        echo '</tr>';
    }

    /**
     * Obtener enlace al comprobante de pago
     *
     * @param WC_Order $order Pedido
     * @return string
     */
    private function get_payment_proof_link($order) {
        $proof_url = $order->get_meta('_payment_proof_url');
        
        if ($proof_url) {
            return '<a href="' . esc_url($proof_url) . '" target="_blank">Ver Comprobante</a>';
        }
        
        return 'Sin comprobante';
    }

    /**
     * Obtener pedidos pendientes de confirmación
     *
     * @return array
     */
    private function get_pending_payment_orders() {
        $args = array(
            'status' => 'pending',
            'payment_method' => array('wcvs_zelle', 'wcvs_binance', 'wcvs_pago_movil', 'wcvs_transferencias', 'wcvs_cash_deposit'),
            'limit' => 50,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        return wc_get_orders($args);
    }

    /**
     * Confirmar pago via AJAX
     */
    public function ajax_confirm_payment() {
        check_ajax_referer('wcvs_payment_confirmation_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        $order_id = intval($_POST['order_id'] ?? 0);
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error('Pedido no encontrado');
        }
        
        // Confirmar el pago
        $result = $this->confirm_payment($order);
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Rechazar pago via AJAX
     */
    public function ajax_reject_payment() {
        check_ajax_referer('wcvs_payment_confirmation_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permisos insuficientes');
        }
        
        $order_id = intval($_POST['order_id'] ?? 0);
        $reason = sanitize_text_field($_POST['reason'] ?? '');
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error('Pedido no encontrado');
        }
        
        // Rechazar el pago
        $result = $this->reject_payment($order, $reason);
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Subir comprobante de pago via AJAX
     */
    public function ajax_upload_payment_proof() {
        check_ajax_referer('wcvs_payment_confirmation_nonce', 'nonce');
        
        $order_id = intval($_POST['order_id'] ?? 0);
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error('Pedido no encontrado');
        }
        
        // Verificar que el usuario sea el propietario del pedido
        if ($order->get_customer_id() != get_current_user_id()) {
            wp_send_json_error('No tienes permisos para subir comprobantes de este pedido');
        }
        
        // Procesar la subida del archivo
        $result = $this->upload_payment_proof($order);
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Confirmar pago
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    public function confirm_payment($order) {
        try {
            // Cambiar estado a procesando
            $order->update_status('processing', 'Pago confirmado por administrador.');
            
            // Añadir nota de administración
            $order->add_order_note('Pago confirmado por administrador.', true);
            
            // Actualizar meta del pedido
            $order->update_meta_data('_payment_confirmed', true);
            $order->update_meta_data('_payment_confirmed_by', get_current_user_id());
            $order->update_meta_data('_payment_confirmed_at', current_time('mysql'));
            $order->save();
            
            // Enviar email de confirmación
            do_action('wcvs_payment_confirmed', $order, get_current_user_id());
            
            // Log de la confirmación
            WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Pago confirmado para pedido #{$order->get_id()}");
            
            return array(
                'success' => true,
                'message' => 'Pago confirmado exitosamente'
            );
            
        } catch (Exception $e) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_PAYMENTS, "Error al confirmar pago: " . $e->getMessage());
            
            return array(
                'success' => false,
                'message' => 'Error al confirmar el pago: ' . $e->getMessage()
            );
        }
    }

    /**
     * Rechazar pago
     *
     * @param WC_Order $order Pedido
     * @param string $reason Razón del rechazo
     * @return array
     */
    public function reject_payment($order, $reason = '') {
        try {
            // Cambiar estado a cancelado
            $order->update_status('cancelled', 'Pago rechazado por administrador.');
            
            // Añadir nota de administración
            $note = 'Pago rechazado por administrador.';
            if (!empty($reason)) {
                $note .= ' Razón: ' . $reason;
            }
            $order->add_order_note($note, true);
            
            // Actualizar meta del pedido
            $order->update_meta_data('_payment_rejected', true);
            $order->update_meta_data('_payment_rejected_by', get_current_user_id());
            $order->update_meta_data('_payment_rejected_at', current_time('mysql'));
            $order->update_meta_data('_payment_rejection_reason', $reason);
            $order->save();
            
            // Restaurar stock
            $order->restore_order_stock();
            
            // Enviar email de rechazo
            do_action('wcvs_payment_rejected', $order, get_current_user_id());
            
            // Log del rechazo
            WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Pago rechazado para pedido #{$order->get_id()}. Razón: {$reason}");
            
            return array(
                'success' => true,
                'message' => 'Pago rechazado exitosamente'
            );
            
        } catch (Exception $e) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_PAYMENTS, "Error al rechazar pago: " . $e->getMessage());
            
            return array(
                'success' => false,
                'message' => 'Error al rechazar el pago: ' . $e->getMessage()
            );
        }
    }

    /**
     * Subir comprobante de pago
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    public function upload_payment_proof($order) {
        try {
            // Verificar que se haya subido un archivo
            if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
                return array(
                    'success' => false,
                    'message' => 'Error al subir el archivo'
                );
            }
            
            $file = $_FILES['payment_proof'];
            
            // Validar tipo de archivo
            $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf');
            if (!in_array($file['type'], $allowed_types)) {
                return array(
                    'success' => false,
                    'message' => 'Tipo de archivo no permitido. Solo se permiten imágenes y PDFs.'
                );
            }
            
            // Validar tamaño del archivo (5MB máximo)
            if ($file['size'] > 5 * 1024 * 1024) {
                return array(
                    'success' => false,
                    'message' => 'El archivo es demasiado grande. Máximo 5MB.'
                );
            }
            
            // Subir archivo
            $upload_dir = wp_upload_dir();
            $payment_proof_dir = $upload_dir['basedir'] . '/wcvs-payment-proofs/';
            
            if (!file_exists($payment_proof_dir)) {
                wp_mkdir_p($payment_proof_dir);
            }
            
            $filename = 'payment-proof-' . $order->get_id() . '-' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filepath = $payment_proof_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $file_url = $upload_dir['baseurl'] . '/wcvs-payment-proofs/' . $filename;
                
                // Actualizar meta del pedido
                $order->update_meta_data('_payment_proof_url', $file_url);
                $order->update_meta_data('_payment_proof_filename', $filename);
                $order->update_meta_data('_payment_proof_uploaded_at', current_time('mysql'));
                $order->save();
                
                // Añadir nota
                $order->add_order_note('Comprobante de pago subido por el cliente.');
                
                // Log de la subida
                WCVS_Logger::info(WCVS_Logger::CONTEXT_PAYMENTS, "Comprobante de pago subido para pedido #{$order->get_id()}");
                
                return array(
                    'success' => true,
                    'message' => 'Comprobante subido exitosamente',
                    'file_url' => $file_url
                );
            } else {
                return array(
                    'success' => false,
                    'message' => 'Error al mover el archivo'
                );
            }
            
        } catch (Exception $e) {
            WCVS_Logger::error(WCVS_Logger::CONTEXT_PAYMENTS, "Error al subir comprobante: " . $e->getMessage());
            
            return array(
                'success' => false,
                'message' => 'Error al subir el comprobante: ' . $e->getMessage()
            );
        }
    }

    /**
     * Enviar email de confirmación
     *
     * @param WC_Order $order Pedido
     * @param int $admin_id ID del administrador
     */
    public function send_confirmation_email($order, $admin_id) {
        $subject = "Pago Confirmado - Pedido #{$order->get_id()}";
        
        $message = "Estimado/a {$order->get_billing_first_name()},\n\n";
        $message .= "Tu pago ha sido confirmado exitosamente.\n\n";
        $message .= "Detalles del pedido:\n";
        $message .= "Pedido #{$order->get_id()}\n";
        $message .= "Total: {$order->get_currency()} {$order->get_total()}\n";
        $message .= "Método de pago: {$order->get_payment_method_title()}\n\n";
        $message .= "Tu pedido está siendo procesado y recibirás una notificación cuando esté listo para envío.\n\n";
        $message .= "Si tienes alguna pregunta, no dudes en contactarnos.\n\n";
        $message .= "Saludos,\n";
        $message .= "Equipo de Kinta Electric";
        
        wp_mail($order->get_billing_email(), $subject, $message);
    }

    /**
     * Enviar email de rechazo
     *
     * @param WC_Order $order Pedido
     * @param int $admin_id ID del administrador
     */
    public function send_rejection_email($order, $admin_id) {
        $reason = $order->get_meta('_payment_rejection_reason');
        
        $subject = "Pago Rechazado - Pedido #{$order->get_id()}";
        
        $message = "Estimado/a {$order->get_billing_first_name()},\n\n";
        $message .= "Lamentamos informarte que tu pago ha sido rechazado.\n\n";
        
        if (!empty($reason)) {
            $message .= "Razón: {$reason}\n\n";
        }
        
        $message .= "Detalles del pedido:\n";
        $message .= "Pedido #{$order->get_id()}\n";
        $message .= "Total: {$order->get_currency()} {$order->get_total()}\n";
        $message .= "Método de pago: {$order->get_payment_method_title()}\n\n";
        $message .= "Por favor, contacta con nosotros para resolver este problema o realizar un nuevo pago.\n\n";
        $message .= "Si tienes alguna pregunta, no dudes en contactarnos.\n\n";
        $message .= "Saludos,\n";
        $message .= "Equipo de Kinta Electric";
        
        wp_mail($order->get_billing_email(), $subject, $message);
    }

    /**
     * Obtener estadísticas de confirmación
     *
     * @return array
     */
    public function get_confirmation_stats() {
        global $wpdb;
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_pending,
                SUM(CASE WHEN post_status = 'wc-processing' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN post_status = 'wc-cancelled' THEN 1 ELSE 0 END) as rejected
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND pm.meta_key = '_payment_method'
            AND pm.meta_value IN ('wcvs_zelle', 'wcvs_binance', 'wcvs_pago_movil', 'wcvs_transferencias', 'wcvs_cash_deposit')
            AND p.post_status IN ('wc-pending', 'wc-processing', 'wc-cancelled')
        ");
        
        return array(
            'total_pending' => intval($stats->total_pending),
            'confirmed' => intval($stats->confirmed),
            'rejected' => intval($stats->rejected),
            'confirmation_rate' => $stats->total_pending > 0 ? 
                round(($stats->confirmed / $stats->total_pending) * 100, 2) : 0
        );
    }
}
