<?php
/**
 * Gestor de Impuestos para Venezuela (IVA e IGTF)
 * Integrado con el sistema nativo de WooCommerce
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Tax_Manager {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Configuración de IVA
     * 
     * @var array
     */
    private $iva_config;
    
    /**
     * Configuración de IGTF
     * 
     * @var array
     */
    private $igtf_config;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        $this->load_config();
        
        // Inicializar hooks
        $this->init_hooks();
        
        // NO configurar tasas automáticamente - se hace manualmente desde el admin
        // Esto previene creación infinita de tasas
    }
    
    /**
     * Cargar configuración
     */
    private function load_config() {
        $this->iva_config = array(
            'enabled' => get_option('wvp_iva_enabled', 'yes'),
            'rate' => floatval(get_option('wvp_iva_rate', 16.0)),
            'tax_class' => get_option('wvp_iva_tax_class', 'standard')
        );
        
        $this->igtf_config = array(
            'enabled' => get_option('wvp_igtf_enabled', 'yes'),
            'rate' => floatval(get_option('wvp_igtf_rate', 3.0)),
            'payment_methods' => get_option('wvp_igtf_payment_methods', array('wvp_efectivo'))
        );
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Hook para aplicar IGTF como fee en el carrito (solo si está habilitado)
        if ($this->igtf_config['enabled'] === 'yes') {
            add_action('woocommerce_cart_calculate_fees', array($this, 'apply_igtf_fee'), 20);
        }
        
        // Hook para guardar datos de impuestos en la orden
        add_action('woocommerce_checkout_create_order', array($this, 'save_tax_data_to_order'), 10, 2);
        
        // Hook para guardar datos de impuestos después de crear la orden
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_tax_data_after_order'), 10, 2);
        
        // Hook para actualizar datos cuando se recalculan impuestos
        add_action('woocommerce_order_after_calculate_totals', array($this, 'update_tax_data_on_recalculate'), 10, 2);
    }
    
    /**
     * Configurar tasas de impuestos en WooCommerce
     * Usa el sistema nativo de WooCommerce para IVA
     * Este método debe ser llamado manualmente desde el admin
     * 
     * @return array Resultado de la operación
     */
    public function setup_woocommerce_tax_rates() {
        // Asegurar que los impuestos estén habilitados en WooCommerce
        if (get_option('woocommerce_calc_taxes') !== 'yes') {
            update_option('woocommerce_calc_taxes', 'yes');
        }
        
        // Verificar si ya existe una tasa de IVA para Venezuela con los mismos parámetros
        global $wpdb;
        $tax_rates_table = $wpdb->prefix . 'woocommerce_tax_rates';
        
        // Buscar tasa exacta: VE, sin estado, nombre IVA
        // La clase de impuesto puede ser vacía (standard) o el valor configurado
        $tax_class_condition = '';
        if ($this->iva_config['tax_class'] === 'standard' || empty($this->iva_config['tax_class'])) {
            $tax_class_condition = "AND (tax_rate_class = '' OR tax_rate_class IS NULL)";
        } else {
            $tax_class_condition = $wpdb->prepare("AND tax_rate_class = %s", $this->iva_config['tax_class']);
        }
        
        $existing_rate = $wpdb->get_row("
            SELECT tax_rate_id, tax_rate 
            FROM {$tax_rates_table}
            WHERE tax_rate_country = 'VE'
            AND tax_rate_state = ''
            AND tax_rate_name = 'IVA'
            {$tax_class_condition}
            LIMIT 1
        ");
        
        if ($existing_rate) {
            // Ya existe una tasa, actualizar si es necesario
            $existing_rate_value = floatval($existing_rate->tax_rate);
            if (abs($existing_rate_value - $this->iva_config['rate']) > 0.01) {
                // La tasa cambió, actualizar
                WC_Tax::_update_tax_rate($existing_rate->tax_rate_id, array(
                    'tax_rate' => $this->iva_config['rate'],
                    'tax_rate_name' => 'IVA'
                ));
                return array(
                    'success' => true,
                    'message' => __('Tasa de IVA actualizada correctamente', 'wvp'),
                    'action' => 'updated',
                    'rate_id' => $existing_rate->tax_rate_id
                );
            } else {
                // La tasa ya existe y es correcta
                return array(
                    'success' => true,
                    'message' => __('La tasa de IVA ya está configurada correctamente', 'wvp'),
                    'action' => 'exists',
                    'rate_id' => $existing_rate->tax_rate_id
                );
            }
        } else {
            // No existe, crear nueva tasa
            $tax_rate = array(
                'tax_rate_country' => 'VE',
                'tax_rate_state' => '',
                'tax_rate' => $this->iva_config['rate'],
                'tax_rate_name' => 'IVA',
                'tax_rate_priority' => 1,
                'tax_rate_compound' => 0,
                'tax_rate_shipping' => 1, // Aplicar IVA a envíos
                'tax_rate_order' => 0,
                'tax_rate_class' => $this->iva_config['tax_class']
            );
            
            $rate_id = WC_Tax::_insert_tax_rate($tax_rate);
            
            return array(
                'success' => true,
                'message' => __('Tasa de IVA creada correctamente', 'wvp'),
                'action' => 'created',
                'rate_id' => $rate_id
            );
        }
    }
    
    /**
     * Limpiar tasas duplicadas de IVA
     * Elimina todas las tasas de IVA para Venezuela excepto la primera
     * 
     * @return array Resultado de la operación
     */
    public function cleanup_duplicate_tax_rates() {
        global $wpdb;
        $tax_rates_table = $wpdb->prefix . 'woocommerce_tax_rates';
        
        // Buscar todas las tasas de IVA para Venezuela con los mismos parámetros
        // Agrupar por país, estado, nombre y clase para encontrar duplicados exactos
        $duplicate_rates = $wpdb->get_results("
            SELECT tax_rate_id, tax_rate_country, tax_rate_state, tax_rate_name, tax_rate_class
            FROM {$tax_rates_table}
            WHERE tax_rate_country = 'VE'
            AND tax_rate_state = ''
            AND tax_rate_name = 'IVA'
            ORDER BY tax_rate_id ASC
        ");
        
        if (count($duplicate_rates) <= 1) {
            return array(
                'success' => true,
                'message' => __('No hay tasas duplicadas', 'wvp'),
                'deleted' => 0
            );
        }
        
        // Mantener la primera, eliminar las demás
        $first_rate_id = $duplicate_rates[0]->tax_rate_id;
        $deleted_count = 0;
        $deleted_ids = array();
        
        for ($i = 1; $i < count($duplicate_rates); $i++) {
            $rate_id = $duplicate_rates[$i]->tax_rate_id;
            WC_Tax::_delete_tax_rate($rate_id);
            $deleted_ids[] = $rate_id;
            $deleted_count++;
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('Se eliminaron %d tasas duplicadas. Se mantuvo la tasa ID: %d', 'wvp'), $deleted_count, $first_rate_id),
            'deleted' => $deleted_count,
            'kept_rate_id' => $first_rate_id,
            'deleted_ids' => $deleted_ids
        );
    }
    
    /**
     * Aplicar IGTF como fee en el carrito
     * Solo se aplica si está habilitado y el método de pago corresponde
     */
    public function apply_igtf_fee($cart) {
        // Validaciones básicas
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        if (!$cart || $cart->is_empty()) {
            return;
        }
        
        // Verificar que IGTF esté habilitado
        if ($this->igtf_config['enabled'] !== 'yes') {
            return;
        }
        
        // Obtener método de pago seleccionado
        $payment_method = WC()->session->get('chosen_payment_method');
        if (empty($payment_method)) {
            return;
        }
        
        // Verificar si el método de pago aplica IGTF
        if (!in_array($payment_method, $this->igtf_config['payment_methods'])) {
            return;
        }
        
        // Calcular base para IGTF: subtotal + envío (antes de impuestos)
        $subtotal = $cart->get_subtotal();
        $shipping_total = $cart->get_shipping_total();
        $igtf_base = $subtotal + $shipping_total;
        
        // Calcular IGTF
        $igtf_amount = ($igtf_base * $this->igtf_config['rate']) / 100;
        
        // Redondear a 2 decimales
        $igtf_amount = round($igtf_amount, 2);
        
        // Solo agregar si el monto es mayor a 0
        if ($igtf_amount > 0) {
            $cart->add_fee(
                sprintf(__('IGTF (%s%%)', 'wvp'), number_format($this->igtf_config['rate'], 2)),
                $igtf_amount,
                false // IGTF no es gravable (no se aplica IVA sobre IGTF)
            );
        }
    }
    
    /**
     * Guardar datos de impuestos en la orden
     */
    public function save_tax_data_to_order($order, $data) {
        if (!$order) {
            return;
        }
        
        // Guardar configuración aplicada
        $order->update_meta_data('_wvp_iva_rate', $this->iva_config['rate']);
        $order->update_meta_data('_wvp_iva_enabled', $this->iva_config['enabled']);
        $order->update_meta_data('_wvp_igtf_rate', $this->igtf_config['rate']);
        $order->update_meta_data('_wvp_igtf_enabled', $this->igtf_config['enabled']);
        
        // Guardar método de pago
        $payment_method = isset($data['payment_method']) ? $data['payment_method'] : '';
        $order->update_meta_data('_wvp_payment_method', $payment_method);
    }
    
    /**
     * Guardar datos de impuestos después de crear la orden
     * Usa los datos reales calculados por WooCommerce
     */
    public function save_tax_data_after_order($order_id, $data) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Obtener IVA desde los impuestos calculados por WooCommerce
        $iva_total = $this->get_iva_from_order($order);
        
        // Obtener IGTF desde los fees
        $igtf_total = $this->get_igtf_from_order($order);
        
        // Guardar totales
        $order->update_meta_data('_wvp_iva_total', $iva_total);
        $order->update_meta_data('_wvp_igtf_total', $igtf_total);
        $order->update_meta_data('_wvp_subtotal_before_taxes', $order->get_subtotal());
        
        // Guardar tasa de cambio si está disponible
        if (class_exists('WVP_BCV_Integrator')) {
            $rate = WVP_BCV_Integrator::get_rate();
            if ($rate && $rate > 0) {
                $order->update_meta_data('_exchange_rate_at_purchase', $rate);
                $order->update_meta_data('_exchange_rate_date', current_time('Y-m-d H:i:s'));
            }
        }
        
        $order->save();
    }
    
    /**
     * Actualizar datos de impuestos cuando se recalculan totales
     */
    public function update_tax_data_on_recalculate($and_taxes, $order) {
        if (!$order) {
            return;
        }
        
        // Recalcular y guardar datos
        $iva_total = $this->get_iva_from_order($order);
        $igtf_total = $this->get_igtf_from_order($order);
        
        $order->update_meta_data('_wvp_iva_total', $iva_total);
        $order->update_meta_data('_wvp_igtf_total', $igtf_total);
        $order->update_meta_data('_wvp_subtotal_before_taxes', $order->get_subtotal());
        
        $order->save();
    }
    
    /**
     * Obtener IVA desde una orden
     * Usa los impuestos calculados por WooCommerce
     * 
     * @param WC_Order|int $order Orden o ID de orden
     * @return float Total de IVA
     */
    public function get_iva_from_order($order) {
        if (is_numeric($order)) {
            $order = wc_get_order($order);
        }
        
        if (!$order) {
            return 0;
        }
        
        // Intentar obtener del meta guardado primero
        $iva_total = $order->get_meta('_wvp_iva_total');
        if ($iva_total !== '' && $iva_total !== null) {
            return floatval($iva_total);
        }
        
        // Calcular desde los impuestos de WooCommerce
        // Sumar todos los impuestos que no sean IGTF
        $tax_totals = $order->get_tax_totals();
        $iva_total = 0;
        
        foreach ($tax_totals as $tax) {
            // Excluir IGTF (IGTF se maneja como fee, no como tax)
            if (stripos($tax->label, 'IGTF') === false) {
                $iva_total += floatval($tax->amount);
            }
        }
        
        return $iva_total;
    }
    
    /**
     * Obtener IGTF desde una orden
     * IGTF se guarda como fee, no como tax
     * 
     * @param WC_Order|int $order Orden o ID de orden
     * @return float Total de IGTF
     */
    public function get_igtf_from_order($order) {
        if (is_numeric($order)) {
            $order = wc_get_order($order);
        }
        
        if (!$order) {
            return 0;
        }
        
        // Intentar obtener del meta guardado primero
        $igtf_total = $order->get_meta('_wvp_igtf_total');
        if ($igtf_total !== '' && $igtf_total !== null) {
            return floatval($igtf_total);
        }
        
        // Calcular desde los fees de la orden
        $igtf_total = 0;
        foreach ($order->get_fees() as $fee) {
            // Buscar fees que contengan "IGTF" en el nombre
            if (stripos($fee->get_name(), 'IGTF') !== false) {
                $igtf_total += floatval($fee->get_total());
            }
        }
        
        return $igtf_total;
    }
    
    /**
     * Obtener subtotal antes de impuestos de una orden
     * 
     * @param WC_Order|int $order Orden o ID de orden
     * @return float Subtotal
     */
    public function get_order_subtotal_before_taxes($order) {
        if (is_numeric($order)) {
            $order = wc_get_order($order);
        }
        
        if (!$order) {
            return 0;
        }
        
        // Intentar obtener del meta guardado
        $subtotal = $order->get_meta('_wvp_subtotal_before_taxes');
        if ($subtotal !== '' && $subtotal !== null) {
            return floatval($subtotal);
        }
        
        // Usar el subtotal de la orden
        return floatval($order->get_subtotal());
    }
    
    /**
     * Obtener total de IVA de una orden (alias para compatibilidad)
     */
    public function get_order_iva_total($order) {
        return $this->get_iva_from_order($order);
    }
    
    /**
     * Obtener total de IGTF de una orden (alias para compatibilidad)
     */
    public function get_order_igtf_total($order) {
        return $this->get_igtf_from_order($order);
    }
}
