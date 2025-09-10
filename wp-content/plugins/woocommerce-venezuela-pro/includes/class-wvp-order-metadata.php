<?php
/**
 * Clase para guardar metadatos venezolanos en pedidos
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Order_Metadata {
    
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
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Guardar metadatos cuando se actualiza el pedido
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_venezuela_metadata'));
        
        // Guardar metadatos cuando se procesa el pago
        add_action('woocommerce_payment_complete', array($this, 'save_payment_metadata'));
        
        // Guardar metadatos cuando se cambia el estado del pedido
        add_action('woocommerce_order_status_changed', array($this, 'save_status_change_metadata'), 10, 3);
        
        // Añadir metadatos a la API de pedidos
        add_filter('woocommerce_rest_prepare_shop_order_object', array($this, 'add_venezuela_data_to_api'), 10, 3);
    }
    
    /**
     * Guardar metadatos venezolanos en el pedido
     * 
     * @param int $order_id ID del pedido
     */
    public function save_venezuela_metadata($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        // Guardar tasa BCV actual
        $this->save_bcv_rate($order_id);
        
        // Guardar información de IGTF
        $this->save_igtf_info($order_id);
        
        // Guardar información de pago
        $this->save_payment_info($order_id);
        
        // Guardar timestamp de procesamiento
        update_post_meta($order_id, '_wvp_processed_at', current_time('mysql'));
        
        // Log de guardado
        error_log("WVP Order Metadata: Metadatos venezolanos guardados para pedido #{$order_id}");
    }
    
    /**
     * Guardar tasa BCV en el pedido
     * 
     * @param int $order_id ID del pedido
     */
    private function save_bcv_rate($order_id) {
        $bcv_rate = WVP_BCV_Integrator::get_rate();
        
        if ($bcv_rate && $bcv_rate > 0) {
            update_post_meta($order_id, '_bcv_rate_at_purchase', $bcv_rate);
            update_post_meta($order_id, '_bcv_rate_date', current_time('mysql'));
            
            // Guardar información adicional de la tasa
            $rate_info = WVP_BCV_Integrator::get_rate_info();
            if ($rate_info) {
                update_post_meta($order_id, '_bcv_rate_info', $rate_info);
            }
        }
    }
    
    /**
     * Guardar información de IGTF en el pedido
     * 
     * @param int $order_id ID del pedido
     */
    private function save_igtf_info($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        // Verificar si se debe aplicar IGTF
        $payment_method = $order->get_payment_method();
        $should_apply_igtf = $this->should_apply_igtf($payment_method);
        
        if ($should_apply_igtf) {
            $order_total = $order->get_total('raw');
            $igtf_rate = $this->get_igtf_rate();
            $igtf_amount = ($order_total * $igtf_rate) / 100;
            
            update_post_meta($order_id, '_igtf_applied', 'yes');
            update_post_meta($order_id, '_igtf_rate', $igtf_rate);
            update_post_meta($order_id, '_igtf_amount', $igtf_amount);
            update_post_meta($order_id, '_igtf_calculated_at', current_time('mysql'));
        } else {
            update_post_meta($order_id, '_igtf_applied', 'no');
            update_post_meta($order_id, '_igtf_rate', 0);
            update_post_meta($order_id, '_igtf_amount', 0);
        }
    }
    
    /**
     * Guardar información de pago en el pedido
     * 
     * @param int $order_id ID del pedido
     */
    private function save_payment_info($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        $payment_method = $order->get_payment_method();
        
        // Guardar método de pago
        update_post_meta($order_id, '_wvp_payment_method', $payment_method);
        
        // Guardar información específica según el método de pago
        switch ($payment_method) {
            case 'wvp_zelle':
                $this->save_zelle_info($order_id);
                break;
            case 'wvp_pago_movil':
                $this->save_pago_movil_info($order_id);
                break;
        }
    }
    
    /**
     * Guardar información específica de Zelle
     * 
     * @param int $order_id ID del pedido
     */
    private function save_zelle_info($order_id) {
        $confirmation_number = sanitize_text_field($_POST['wvp_zelle_confirmation_number'] ?? '');
        
        if (!empty($confirmation_number)) {
            update_post_meta($order_id, '_payment_reference', $confirmation_number);
            update_post_meta($order_id, '_zelle_confirmation', $confirmation_number);
        }
        
        // Guardar configuración de Zelle
        $zelle_settings = get_option('woocommerce_wvp_zelle_settings', array());
        if (!empty($zelle_settings['zelle_email'])) {
            update_post_meta($order_id, '_zelle_email', $zelle_settings['zelle_email']);
        }
    }
    
    /**
     * Guardar información específica de Pago Móvil
     * 
     * @param int $order_id ID del pedido
     */
    private function save_pago_movil_info($order_id) {
        $confirmation_number = sanitize_text_field($_POST['wvp_pago_movil_confirmation_number'] ?? '');
        
        if (!empty($confirmation_number)) {
            update_post_meta($order_id, '_payment_reference', $confirmation_number);
            update_post_meta($order_id, '_pago_movil_confirmation', $confirmation_number);
        }
        
        // Guardar configuración de Pago Móvil
        $pago_movil_settings = get_option('woocommerce_wvp_pago_movil_settings', array());
        if (!empty($pago_movil_settings['bank_name'])) {
            update_post_meta($order_id, '_bank_name', $pago_movil_settings['bank_name']);
        }
        if (!empty($pago_movil_settings['bank_ci'])) {
            update_post_meta($order_id, '_bank_ci', $pago_movil_settings['bank_ci']);
        }
        if (!empty($pago_movil_settings['bank_phone'])) {
            update_post_meta($order_id, '_bank_phone', $pago_movil_settings['bank_phone']);
        }
        
        // Guardar total en bolívares
        $bcv_rate = WVP_BCV_Integrator::get_rate();
        if ($bcv_rate && $bcv_rate > 0) {
            $order = wc_get_order($order_id);
            $order_total = $order->get_total('raw');
            $ves_total = WVP_BCV_Integrator::convert_to_ves($order_total, $bcv_rate);
            
            if ($ves_total) {
                update_post_meta($order_id, '_total_ves', $ves_total);
                update_post_meta($order_id, '_total_ves_formatted', WVP_BCV_Integrator::format_ves_price($ves_total));
            }
        }
    }
    
    /**
     * Guardar metadatos cuando se procesa el pago
     * 
     * @param int $order_id ID del pedido
     */
    public function save_payment_metadata($order_id) {
        // Actualizar timestamp de pago
        update_post_meta($order_id, '_wvp_payment_processed_at', current_time('mysql'));
        
        // Guardar información de la sesión
        $this->save_session_info($order_id);
    }
    
    /**
     * Guardar metadatos cuando cambia el estado del pedido
     * 
     * @param int $order_id ID del pedido
     * @param string $old_status Estado anterior
     * @param string $new_status Estado nuevo
     */
    public function save_status_change_metadata($order_id, $old_status, $new_status) {
        // Guardar historial de cambios de estado
        $status_history = get_post_meta($order_id, '_wvp_status_history', true);
        
        if (!is_array($status_history)) {
            $status_history = array();
        }
        
        $status_history[] = array(
            'from' => $old_status,
            'to' => $new_status,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        );
        
        update_post_meta($order_id, '_wvp_status_history', $status_history);
        
        // Guardar timestamp del último cambio
        update_post_meta($order_id, '_wvp_last_status_change', current_time('mysql'));
    }
    
    /**
     * Guardar información de la sesión
     * 
     * @param int $order_id ID del pedido
     */
    private function save_session_info($order_id) {
        if (WC()->session) {
            $session_data = array(
                'chosen_payment_method' => WC()->session->get('chosen_payment_method'),
                'chosen_shipping_method' => WC()->session->get('chosen_shipping_method'),
                'customer_id' => WC()->session->get('customer_id'),
                'session_timestamp' => current_time('mysql')
            );
            
            update_post_meta($order_id, '_wvp_session_data', $session_data);
        }
    }
    
    /**
     * Añadir datos venezolanos a la API de pedidos
     * 
     * @param WP_REST_Response $response Respuesta de la API
     * @param WC_Order $order Pedido
     * @param WP_REST_Request $request Solicitud
     * @return WP_REST_Response Respuesta modificada
     */
    public function add_venezuela_data_to_api($response, $order, $request) {
        $venezuela_data = $this->get_order_venezuela_summary($order->get_id());
        
        if (!empty($venezuela_data)) {
            $response->data['venezuela_data'] = $venezuela_data;
        }
        
        return $response;
    }
    
    /**
     * Obtener resumen de datos venezolanos del pedido
     * 
     * @param int $order_id ID del pedido
     * @return array Resumen de datos
     */
    public function get_order_venezuela_summary($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return array();
        }
        
        $cedula_rif = get_post_meta($order_id, '_billing_cedula_rif', true);
        $bcv_rate = get_post_meta($order_id, '_bcv_rate_at_purchase', true);
        $payment_reference = get_post_meta($order_id, '_payment_reference', true);
        $igtf_amount = get_post_meta($order_id, '_igtf_amount', true);
        $igtf_applied = get_post_meta($order_id, '_igtf_applied', true);
        $total_ves = get_post_meta($order_id, '_total_ves', true);
        $total_ves_formatted = get_post_meta($order_id, '_total_ves_formatted', true);
        
        return array(
            'cedula_rif' => $cedula_rif,
            'bcv_rate' => $bcv_rate ? floatval($bcv_rate) : null,
            'payment_reference' => $payment_reference,
            'igtf_amount' => $igtf_amount ? floatval($igtf_amount) : null,
            'igtf_applied' => $igtf_applied === 'yes',
            'total_ves' => $total_ves ? floatval($total_ves) : null,
            'total_ves_formatted' => $total_ves_formatted,
            'has_venezuela_data' => !empty($cedula_rif) || !empty($bcv_rate) || !empty($payment_reference)
        );
    }
    
    /**
     * Verificar si se debe aplicar IGTF
     * 
     * @param string $payment_method Método de pago
     * @return bool True si se debe aplicar IGTF
     */
    private function should_apply_igtf($payment_method) {
        $gateway_settings = get_option('woocommerce_' . $payment_method . '_settings', array());
        
        return isset($gateway_settings['apply_igtf']) && $gateway_settings['apply_igtf'] === 'yes';
    }
    
    /**
     * Obtener tasa de IGTF
     * 
     * @return float Tasa de IGTF
     */
    private function get_igtf_rate() {
        $rate = $this->plugin->get_option('igtf_rate');
        
        if ($rate === null) {
            return 3.0; // Tasa por defecto
        }
        
        return floatval($rate);
    }
    
    /**
     * Obtener estadísticas de metadatos venezolanos
     * 
     * @param string $date_from Fecha desde (opcional)
     * @param string $date_to Fecha hasta (opcional)
     * @return array Estadísticas
     */
    public function get_metadata_stats($date_from = null, $date_to = null) {
        global $wpdb;
        
        $where_clause = "WHERE p.post_type = 'shop_order' AND p.post_status != 'trash'";
        $where_values = array();
        
        if ($date_from) {
            $where_clause .= " AND p.post_date >= %s";
            $where_values[] = $date_from;
        }
        
        if ($date_to) {
            $where_clause .= " AND p.post_date <= %s";
            $where_values[] = $date_to;
        }
        
        $sql = "
            SELECT 
                COUNT(DISTINCT p.ID) as total_orders,
                COUNT(DISTINCT CASE WHEN pm1.meta_value IS NOT NULL THEN p.ID END) as orders_with_cedula,
                COUNT(DISTINCT CASE WHEN pm2.meta_value IS NOT NULL THEN p.ID END) as orders_with_bcv_rate,
                COUNT(DISTINCT CASE WHEN pm3.meta_value = 'yes' THEN p.ID END) as orders_with_igtf,
                COUNT(DISTINCT CASE WHEN pm4.meta_value IS NOT NULL THEN p.ID END) as orders_with_payment_ref
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_billing_cedula_rif'
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_bcv_rate_at_purchase'
            LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_igtf_applied'
            LEFT JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_payment_reference'
            {$where_clause}
        ";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $result = $wpdb->get_row($sql, ARRAY_A);
        
        return array(
            'total_orders' => intval($result['total_orders']),
            'orders_with_cedula' => intval($result['orders_with_cedula']),
            'orders_with_bcv_rate' => intval($result['orders_with_bcv_rate']),
            'orders_with_igtf' => intval($result['orders_with_igtf']),
            'orders_with_payment_ref' => intval($result['orders_with_payment_ref']),
            'cedula_percentage' => $result['total_orders'] > 0 ? round(($result['orders_with_cedula'] / $result['total_orders']) * 100, 2) : 0,
            'bcv_rate_percentage' => $result['total_orders'] > 0 ? round(($result['orders_with_bcv_rate'] / $result['total_orders']) * 100, 2) : 0,
            'igtf_percentage' => $result['total_orders'] > 0 ? round(($result['orders_with_igtf'] / $result['total_orders']) * 100, 2) : 0,
            'payment_ref_percentage' => $result['total_orders'] > 0 ? round(($result['orders_with_payment_ref'] / $result['total_orders']) * 100, 2) : 0
        );
    }
}
