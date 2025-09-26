<?php
/**
 * Local Delivery Shipping Method
 * For local deliveries in Venezuela
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Local_Delivery_Shipping extends WC_Shipping_Method {
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct( $instance_id = 0 ) {
        $this->id = 'wvp_local_delivery';
        $this->instance_id = absint( $instance_id );
        $this->method_title = 'Entrega Local Venezuela';
        $this->method_description = 'Entrega local en áreas específicas de Venezuela';
        
        $this->supports = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
        );
        
        $this->init();
    }
    
    /**
     * Initialize shipping method
     */
    public function init() {
        $this->init_form_fields();
        $this->init_settings();
        
        $this->title = $this->get_option( 'title' );
        $this->tax_status = $this->get_option( 'tax_status' );
        $this->cost = $this->get_option( 'cost' );
        $this->free_shipping_cost = $this->get_option( 'free_shipping_cost' );
        
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }
    
    /**
     * Initialize form fields
     */
    public function init_form_fields() {
        $this->instance_form_fields = array(
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout',
                'default' => 'Entrega Local',
                'desc_tip' => true,
            ),
            'tax_status' => array(
                'title' => 'Estado de Impuestos',
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'taxable',
                'options' => array(
                    'taxable' => 'Gravable',
                    'none' => 'No Gravable',
                ),
            ),
            'cost' => array(
                'title' => 'Costo Base',
                'type' => 'number',
                'description' => 'Costo base del envío',
                'default' => '2.00',
                'desc_tip' => true,
            ),
            'free_shipping_cost' => array(
                'title' => 'Envío Gratis Desde',
                'type' => 'number',
                'description' => 'Monto mínimo para envío gratis',
                'default' => '50.00',
                'desc_tip' => true,
            ),
            'delivery_areas' => array(
                'title' => 'Áreas de Entrega',
                'type' => 'textarea',
                'description' => 'Áreas donde se realiza entrega local (una por línea)',
                'default' => "Distrito Capital\nMiranda\nAragua\nCarabobo\nVargas",
                'desc_tip' => true,
            ),
            'delivery_time' => array(
                'title' => 'Tiempo de Entrega',
                'type' => 'text',
                'description' => 'Tiempo estimado de entrega',
                'default' => '1-2 días hábiles',
                'desc_tip' => true,
            ),
            'contact_info' => array(
                'title' => 'Información de Contacto',
                'type' => 'textarea',
                'description' => 'Información de contacto para coordinación',
                'default' => 'WhatsApp: +58-412-123-4567\nEmail: entregas@kintaelectric.com',
                'desc_tip' => true,
            )
        );
    }
    
    /**
     * Calculate shipping
     */
    public function calculate_shipping( $package = array() ) {
        // Check if delivery area is supported
        if ( ! $this->is_delivery_area_supported( $package['destination']['state'] ) ) {
            return;
        }
        
        $rate = array(
            'id' => $this->get_rate_id(),
            'label' => $this->title,
            'cost' => $this->calculate_shipping_cost( $package ),
            'package' => $package,
        );
        
        $this->add_rate( $rate );
    }
    
    /**
     * Calculate shipping cost
     */
    private function calculate_shipping_cost( $package ) {
        $cost = floatval( $this->cost );
        
        // Check if free shipping applies
        if ( $this->free_shipping_cost > 0 ) {
            $cart_total = WC()->cart->get_cart_contents_total();
            if ( $cart_total >= $this->free_shipping_cost ) {
                return 0;
            }
        }
        
        return $cost;
    }
    
    /**
     * Check if delivery area is supported
     */
    private function is_delivery_area_supported( $state ) {
        $delivery_areas = $this->get_option( 'delivery_areas' );
        if ( empty( $delivery_areas ) ) {
            return false;
        }
        
        $areas = explode( "\n", $delivery_areas );
        foreach ( $areas as $area ) {
            $area = trim( $area );
            if ( empty( $area ) ) {
                continue;
            }
            
            if ( $area === $state ) {
                return true;
            }
        }
        
        return false;
    }
}
