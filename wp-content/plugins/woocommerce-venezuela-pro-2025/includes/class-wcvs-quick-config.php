<?php
/**
 * Clase para Configuración Rápida Automática de WooCommerce
 * 
 * @package WooCommerce_Venezuela_Suite_2025
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WCVS_Quick_Config {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WCVS_Core
     */
    private $core;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->core = WCVS_Core::get_instance();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // AJAX para configuración rápida
        add_action('wp_ajax_wcvs_quick_config', array($this, 'ajax_quick_config'));
        add_action('wp_ajax_wcvs_reset_config', array($this, 'ajax_reset_config'));
        add_action('wp_ajax_wcvs_get_config_status', array($this, 'ajax_get_config_status'));
        
        // Añadir estilos para configuración rápida
        add_action('admin_enqueue_scripts', array($this, 'enqueue_quick_config_scripts'));
    }
    
    /**
     * Configuración rápida automática de WooCommerce
     */
    public function quick_configure_woocommerce() {
        $results = array(
            'success' => true,
            'configurations' => array(),
            'errors' => array()
        );
        
        try {
            // 1. Configurar moneda base
            $this->configure_base_currency($results);
            
            // 2. Configurar impuestos
            $this->configure_taxes($results);
            
            // 3. Configurar métodos de pago
            $this->configure_payment_methods($results);
            
            // 4. Configurar métodos de envío
            $this->configure_shipping_methods($results);
            
            // 5. Configurar ubicación
            $this->configure_location($results);
            
            // 6. Configurar formato de precios
            $this->configure_price_format($results);
            
            // 7. Configurar checkout
            $this->configure_checkout($results);
            
            // 8. Configurar emails
            $this->configure_emails($results);
            
            // 9. Configurar páginas
            $this->configure_pages($results);
            
            // 10. Configurar permisos
            $this->configure_permissions($results);
            
        } catch (Exception $e) {
            $results['success'] = false;
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Configurar moneda base
     */
    private function configure_base_currency(&$results) {
        // Configurar moneda base a USD
        update_option('woocommerce_currency', 'USD');
        $results['configurations'][] = 'Moneda base configurada a USD';
        
        // Configurar posición de moneda
        update_option('woocommerce_currency_pos', 'left');
        $results['configurations'][] = 'Posición de moneda configurada';
        
        // Configurar separadores
        update_option('woocommerce_price_thousand_sep', ',');
        update_option('woocommerce_price_decimal_sep', '.');
        update_option('woocommerce_price_num_decimals', 2);
        $results['configurations'][] = 'Separadores de precio configurados';
    }
    
    /**
     * Configurar impuestos
     */
    private function configure_taxes(&$results) {
        // Habilitar impuestos
        update_option('woocommerce_calc_taxes', 'yes');
        $results['configurations'][] = 'Cálculo de impuestos habilitado';
        
        // Configurar IVA (16%)
        $this->create_tax_rate('IVA Venezuela', 'VAT', 16, 'standard', 'VES');
        $results['configurations'][] = 'IVA Venezuela (16%) configurado';
        
        // Configurar IGTF (3%)
        $this->create_tax_rate('IGTF Venezuela', 'IGTF', 3, 'reduced', 'VES');
        $results['configurations'][] = 'IGTF Venezuela (3%) configurado';
        
        // Configurar ubicación de impuestos
        update_option('woocommerce_tax_based_on', 'billing');
        update_option('woocommerce_shipping_tax_class', '');
        $results['configurations'][] = 'Configuración de ubicación de impuestos';
    }
    
    /**
     * Crear tasa de impuesto
     */
    private function create_tax_rate($name, $tax_class, $rate, $type, $currency) {
        global $wpdb;
        
        // Verificar si ya existe
        $existing = $wpdb->get_var($wpdb->prepare("
            SELECT tax_rate_id 
            FROM {$wpdb->prefix}woocommerce_tax_rates 
            WHERE tax_rate_name = %s
        ", $name));
        
        if ($existing) {
            return $existing;
        }
        
        // Crear nueva tasa
        $wpdb->insert(
            $wpdb->prefix . 'woocommerce_tax_rates',
            array(
                'tax_rate_country' => 'VE',
                'tax_rate_state' => '',
                'tax_rate' => $rate,
                'tax_rate_name' => $name,
                'tax_rate_priority' => 1,
                'tax_rate_compound' => 0,
                'tax_rate_shipping' => 1,
                'tax_rate_order' => 0,
                'tax_rate_class' => $tax_class
            )
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Configurar métodos de pago
     */
    private function configure_payment_methods(&$results) {
        // Habilitar transferencia bancaria
        update_option('woocommerce_bacs_settings', array(
            'enabled' => 'yes',
            'title' => 'Transferencia Bancaria',
            'description' => 'Realiza tu pago directamente en nuestra cuenta bancaria.',
            'instructions' => 'Por favor transfiere el importe exacto a la cuenta bancaria indicada.',
            'account_details' => ''
        ));
        $results['configurations'][] = 'Transferencia bancaria habilitada';
        
        // Habilitar pago contra entrega
        update_option('woocommerce_cod_settings', array(
            'enabled' => 'yes',
            'title' => 'Pago Contra Entrega',
            'description' => 'Paga cuando recibas tu pedido.',
            'instructions' => 'Paga en efectivo cuando recibas tu pedido.',
            'enable_for_methods' => array('local_pickup', 'local_delivery')
        ));
        $results['configurations'][] = 'Pago contra entrega habilitado';
        
        // Deshabilitar PayPal por defecto (no es común en Venezuela)
        update_option('woocommerce_paypal_settings', array(
            'enabled' => 'no'
        ));
        $results['configurations'][] = 'PayPal deshabilitado (no común en Venezuela)';
    }
    
    /**
     * Configurar métodos de envío
     */
    private function configure_shipping_methods(&$results) {
        // Habilitar envío gratuito
        update_option('woocommerce_free_shipping_settings', array(
            'enabled' => 'yes',
            'title' => 'Envío Gratuito',
            'requires' => 'min_amount',
            'min_amount' => '100',
            'ignore_discounts' => 'no'
        ));
        $results['configurations'][] = 'Envío gratuito configurado (mínimo $100)';
        
        // Configurar envío plano
        update_option('woocommerce_flat_rate_settings', array(
            'enabled' => 'yes',
            'title' => 'Envío Estándar',
            'tax_status' => 'taxable',
            'cost' => '10',
            'type' => 'per_order'
        ));
        $results['configurations'][] = 'Envío estándar configurado';
        
        // Configurar recogida local
        update_option('woocommerce_local_pickup_settings', array(
            'enabled' => 'yes',
            'title' => 'Recogida en Tienda',
            'tax_status' => 'none',
            'cost' => '0'
        ));
        $results['configurations'][] = 'Recogida en tienda configurada';
    }
    
    /**
     * Configurar ubicación
     */
    private function configure_location(&$results) {
        // Configurar país base
        update_option('woocommerce_default_country', 'VE');
        $results['configurations'][] = 'País base configurado a Venezuela';
        
        // Configurar estado base
        update_option('woocommerce_default_country', 'VE:DC');
        $results['configurations'][] = 'Estado base configurado a Distrito Capital';
        
        // Configurar ubicación de la tienda
        update_option('woocommerce_store_address', 'Caracas');
        update_option('woocommerce_store_city', 'Caracas');
        update_option('woocommerce_store_postcode', '1010');
        $results['configurations'][] = 'Ubicación de la tienda configurada';
    }
    
    /**
     * Configurar formato de precios
     */
    private function configure_price_format(&$results) {
        // Configurar formato de precio
        update_option('woocommerce_price_format', '%1$s%2$s');
        $results['configurations'][] = 'Formato de precio configurado';
        
        // Configurar símbolos de moneda
        update_option('woocommerce_currency_symbol', '$');
        $results['configurations'][] = 'Símbolo de moneda configurado';
    }
    
    /**
     * Configurar checkout
     */
    private function configure_checkout(&$results) {
        // Configurar términos y condiciones
        update_option('woocommerce_terms_page_id', $this->create_terms_page());
        $results['configurations'][] = 'Página de términos y condiciones creada';
        
        // Configurar política de privacidad
        update_option('woocommerce_privacy_policy_page_id', $this->create_privacy_page());
        $results['configurations'][] = 'Página de política de privacidad creada';
        
        // Configurar checkout
        update_option('woocommerce_checkout_privacy_policy_text', 'Tu información personal será utilizada para procesar tu pedido.');
        update_option('woocommerce_checkout_terms_and_conditions_checkbox_text', 'He leído y acepto los términos y condiciones');
        $results['configurations'][] = 'Textos de checkout configurados';
    }
    
    /**
     * Configurar emails
     */
    private function configure_emails(&$results) {
        // Configurar email de nuevo pedido
        update_option('woocommerce_new_order_settings', array(
            'enabled' => 'yes',
            'subject' => '[{site_title}] Nuevo pedido #{order_number}',
            'heading' => 'Nuevo pedido',
            'email_type' => 'html'
        ));
        $results['configurations'][] = 'Email de nuevo pedido configurado';
        
        // Configurar email de pedido completado
        update_option('woocommerce_customer_completed_order_settings', array(
            'enabled' => 'yes',
            'subject' => '[{site_title}] Tu pedido #{order_number} ha sido completado',
            'heading' => 'Pedido completado',
            'email_type' => 'html'
        ));
        $results['configurations'][] = 'Email de pedido completado configurado';
    }
    
    /**
     * Configurar páginas
     */
    private function configure_pages(&$results) {
        // Crear páginas necesarias si no existen
        $pages = array(
            'shop' => 'Tienda',
            'cart' => 'Carrito',
            'checkout' => 'Finalizar Compra',
            'myaccount' => 'Mi Cuenta'
        );
        
        foreach ($pages as $key => $title) {
            $page_id = get_option('woocommerce_' . $key . '_page_id');
            if (!$page_id || !get_post($page_id)) {
                $page_id = $this->create_page($title, $key);
                update_option('woocommerce_' . $key . '_page_id', $page_id);
                $results['configurations'][] = "Página {$title} creada";
            }
        }
    }
    
    /**
     * Configurar permisos
     */
    private function configure_permissions(&$results) {
        // Configurar permisos de usuario
        $customer_role = get_role('customer');
        if ($customer_role) {
            $customer_role->add_cap('read');
            $results['configurations'][] = 'Permisos de cliente configurados';
        }
        
        // Configurar permisos de administrador
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('manage_woocommerce');
            $results['configurations'][] = 'Permisos de administrador configurados';
        }
    }
    
    /**
     * Crear página
     */
    private function create_page($title, $slug) {
        $page = array(
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => $slug
        );
        
        $page_id = wp_insert_post($page);
        
        if ($slug === 'shop') {
            update_option('page_on_front', $page_id);
            update_option('show_on_front', 'page');
        }
        
        return $page_id;
    }
    
    /**
     * Crear página de términos y condiciones
     */
    private function create_terms_page() {
        $content = '
        <h2>Términos y Condiciones</h2>
        <p>Al realizar una compra en nuestra tienda, usted acepta los siguientes términos y condiciones:</p>
        
        <h3>1. Productos</h3>
        <p>Los productos mostrados en nuestra tienda están sujetos a disponibilidad.</p>
        
        <h3>2. Precios</h3>
        <p>Los precios están expresados en dólares estadounidenses (USD) y pueden incluir impuestos aplicables.</p>
        
        <h3>3. Pagos</h3>
        <p>Aceptamos los métodos de pago especificados en el proceso de checkout.</p>
        
        <h3>4. Envíos</h3>
        <p>Los tiempos de envío son estimados y pueden variar según la ubicación.</p>
        
        <h3>5. Devoluciones</h3>
        <p>Las devoluciones están sujetas a nuestra política de devoluciones.</p>
        ';
        
        $page = array(
            'post_title' => 'Términos y Condiciones',
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'terminos-y-condiciones'
        );
        
        return wp_insert_post($page);
    }
    
    /**
     * Crear página de política de privacidad
     */
    private function create_privacy_page() {
        $content = '
        <h2>Política de Privacidad</h2>
        <p>Respetamos su privacidad y nos comprometemos a proteger su información personal.</p>
        
        <h3>Información que Recopilamos</h3>
        <p>Recopilamos información que usted nos proporciona directamente, como su nombre, dirección de email y dirección de envío.</p>
        
        <h3>Cómo Utilizamos su Información</h3>
        <p>Utilizamos su información para procesar pedidos, comunicarnos con usted y mejorar nuestros servicios.</p>
        
        <h3>Protección de Datos</h3>
        <p>Implementamos medidas de seguridad para proteger su información personal.</p>
        
        <h3>Contacto</h3>
        <p>Si tiene preguntas sobre esta política de privacidad, contáctenos.</p>
        ';
        
        $page = array(
            'post_title' => 'Política de Privacidad',
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'politica-de-privacidad'
        );
        
        return wp_insert_post($page);
    }
    
    /**
     * Obtener estado de configuración
     */
    public function get_configuration_status() {
        $status = array(
            'currency' => $this->check_currency_config(),
            'taxes' => $this->check_taxes_config(),
            'payments' => $this->check_payments_config(),
            'shipping' => $this->check_shipping_config(),
            'location' => $this->check_location_config(),
            'pages' => $this->check_pages_config()
        );
        
        $total_configs = count($status);
        $configured_configs = array_sum(array_map(function($config) {
            return $config['configured'] ? 1 : 0;
        }, $status));
        
        $status['overall'] = array(
            'configured' => $configured_configs,
            'total' => $total_configs,
            'percentage' => $total_configs > 0 ? round(($configured_configs / $total_configs) * 100) : 0
        );
        
        return $status;
    }
    
    /**
     * Verificar configuración de moneda
     */
    private function check_currency_config() {
        return array(
            'configured' => get_option('woocommerce_currency') === 'USD',
            'details' => array(
                'currency' => get_option('woocommerce_currency', 'No configurado'),
                'position' => get_option('woocommerce_currency_pos', 'No configurado'),
                'symbol' => get_option('woocommerce_currency_symbol', 'No configurado')
            )
        );
    }
    
    /**
     * Verificar configuración de impuestos
     */
    private function check_taxes_config() {
        global $wpdb;
        
        $tax_rates = $wpdb->get_results("
            SELECT tax_rate_name, tax_rate 
            FROM {$wpdb->prefix}woocommerce_tax_rates 
            WHERE tax_rate_country = 'VE'
        ");
        
        $has_iva = false;
        $has_igtf = false;
        
        foreach ($tax_rates as $rate) {
            if (strpos($rate->tax_rate_name, 'IVA') !== false) {
                $has_iva = true;
            }
            if (strpos($rate->tax_rate_name, 'IGTF') !== false) {
                $has_igtf = true;
            }
        }
        
        return array(
            'configured' => get_option('woocommerce_calc_taxes') === 'yes' && $has_iva && $has_igtf,
            'details' => array(
                'enabled' => get_option('woocommerce_calc_taxes') === 'yes',
                'iva_configured' => $has_iva,
                'igtf_configured' => $has_igtf,
                'rates_count' => count($tax_rates)
            )
        );
    }
    
    /**
     * Verificar configuración de pagos
     */
    private function check_payments_config() {
        $bacs_enabled = get_option('woocommerce_bacs_settings')['enabled'] ?? 'no';
        $cod_enabled = get_option('woocommerce_cod_settings')['enabled'] ?? 'no';
        
        return array(
            'configured' => $bacs_enabled === 'yes' || $cod_enabled === 'yes',
            'details' => array(
                'bank_transfer' => $bacs_enabled === 'yes',
                'cash_on_delivery' => $cod_enabled === 'yes'
            )
        );
    }
    
    /**
     * Verificar configuración de envíos
     */
    private function check_shipping_config() {
        $free_shipping = get_option('woocommerce_free_shipping_settings')['enabled'] ?? 'no';
        $flat_rate = get_option('woocommerce_flat_rate_settings')['enabled'] ?? 'no';
        
        return array(
            'configured' => $free_shipping === 'yes' || $flat_rate === 'yes',
            'details' => array(
                'free_shipping' => $free_shipping === 'yes',
                'flat_rate' => $flat_rate === 'yes'
            )
        );
    }
    
    /**
     * Verificar configuración de ubicación
     */
    private function check_location_config() {
        $country = get_option('woocommerce_default_country', '');
        
        return array(
            'configured' => strpos($country, 'VE:') === 0,
            'details' => array(
                'country' => $country,
                'store_address' => get_option('woocommerce_store_address', 'No configurado')
            )
        );
    }
    
    /**
     * Verificar configuración de páginas
     */
    private function check_pages_config() {
        $shop_page = get_option('woocommerce_shop_page_id');
        $cart_page = get_option('woocommerce_cart_page_id');
        $checkout_page = get_option('woocommerce_checkout_page_id');
        $myaccount_page = get_option('woocommerce_myaccount_page_id');
        
        return array(
            'configured' => $shop_page && $cart_page && $checkout_page && $myaccount_page,
            'details' => array(
                'shop' => $shop_page ? 'Configurada' : 'No configurada',
                'cart' => $cart_page ? 'Configurada' : 'No configurada',
                'checkout' => $checkout_page ? 'Configurada' : 'No configurada',
                'myaccount' => $myaccount_page ? 'Configurada' : 'No configurada'
            )
        );
    }
    
    /**
     * AJAX para configuración rápida
     */
    public function ajax_quick_config() {
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_quick_config_nonce')) {
            wp_die('Nonce inválido');
        }
        
        $results = $this->quick_configure_woocommerce();
        
        wp_send_json($results);
    }
    
    /**
     * AJAX para resetear configuración
     */
    public function ajax_reset_config() {
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_reset_config_nonce')) {
            wp_die('Nonce inválido');
        }
        
        // Resetear configuraciones específicas
        $reset_options = array(
            'woocommerce_currency',
            'woocommerce_calc_taxes',
            'woocommerce_default_country'
        );
        
        foreach ($reset_options as $option) {
            delete_option($option);
        }
        
        wp_send_json_success('Configuración reseteada');
    }
    
    /**
     * AJAX para obtener estado de configuración
     */
    public function ajax_get_config_status() {
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcvs_config_status_nonce')) {
            wp_die('Nonce inválido');
        }
        
        $status = $this->get_configuration_status();
        
        wp_send_json_success($status);
    }
    
    /**
     * Cargar scripts para configuración rápida
     */
    public function enqueue_quick_config_scripts($hook) {
        if (strpos($hook, 'wcvs-') === false) {
            return;
        }
        
        wp_enqueue_script('wcvs-quick-config', plugin_dir_url(__FILE__) . '../admin/js/quick-config.js', array('jquery'), WCVS_VERSION, true);
        
        wp_localize_script('wcvs-quick-config', 'wcvs_quick_config', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcvs_quick_config_nonce'),
            'reset_nonce' => wp_create_nonce('wcvs_reset_config_nonce'),
            'status_nonce' => wp_create_nonce('wcvs_config_status_nonce'),
            'i18n' => array(
                'configuring' => __('Configurando WooCommerce...', 'woocommerce-venezuela-pro-2025'),
                'success' => __('Configuración completada exitosamente', 'woocommerce-venezuela-pro-2025'),
                'error' => __('Error en la configuración', 'woocommerce-venezuela-pro-2025'),
                'reset_success' => __('Configuración reseteada', 'woocommerce-venezuela-pro-2025')
            )
        ));
    }
}
