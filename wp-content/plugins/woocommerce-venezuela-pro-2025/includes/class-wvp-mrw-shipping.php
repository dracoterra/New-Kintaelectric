<?php
/**
 * MRW Venezuela Shipping Method
 * Venezuelan shipping company integration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_MRW_Shipping extends WC_Shipping_Method {
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct( $instance_id = 0 ) {
        $this->id = 'wvp_mrw';
        $this->instance_id = absint( $instance_id );
        $this->method_title = 'MRW Venezuela';
        $this->method_description = 'Envío mediante MRW Venezuela';
        
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
                'default' => 'MRW Venezuela',
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
                'default' => '5.00',
                'desc_tip' => true,
            ),
            'free_shipping_cost' => array(
                'title' => 'Envío Gratis Desde',
                'type' => 'number',
                'description' => 'Monto mínimo para envío gratis',
                'default' => '100.00',
                'desc_tip' => true,
            ),
            'weight_rate' => array(
                'title' => 'Tarifa por Peso (por kg)',
                'type' => 'number',
                'description' => 'Costo adicional por kilogramo',
                'default' => '1.50',
                'desc_tip' => true,
            ),
            'states_cost' => array(
                'title' => 'Costos por Estado',
                'type' => 'textarea',
                'description' => 'Costos adicionales por estado (formato: estado:costo)',
                'default' => "Amazonas:10.00\nAnzoátegui:5.00\nApure:8.00\nAragua:3.00\nBarinas:6.00\nBolívar:7.00\nCarabobo:2.00\nCojedes:4.00\nDelta Amacuro:9.00\nDistrito Capital:0.00\nFalcón:5.00\nGuárico:4.00\nLara:4.00\nMérida:6.00\nMiranda:1.00\nMonagas:6.00\nNueva Esparta:8.00\nPortuguesa:5.00\nSucre:7.00\nTáchira:7.00\nTrujillo:6.00\nVargas:2.00\nYaracuy:4.00\nZulia:8.00",
                'desc_tip' => true,
            )
        );
    }
    
    /**
     * Calculate shipping
     */
    public function calculate_shipping( $package = array() ) {
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
        
        // Add weight cost
        $weight = 0;
        foreach ( $package['contents'] as $item ) {
            $weight += $item['data']->get_weight() * $item['quantity'];
        }
        
        if ( $weight > 0 ) {
            $weight_rate = floatval( $this->get_option( 'weight_rate' ) );
            $cost += $weight * $weight_rate;
        }
        
        // Add state cost
        $state_cost = $this->get_state_cost( $package['destination']['state'] );
        $cost += $state_cost;
        
        return $cost;
    }
    
    /**
     * Get state cost
     */
    private function get_state_cost( $state ) {
        $states_cost = $this->get_option( 'states_cost' );
        if ( empty( $states_cost ) ) {
            return 0;
        }
        
        $lines = explode( "\n", $states_cost );
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( empty( $line ) ) {
                continue;
            }
            
            $parts = explode( ':', $line );
            if ( count( $parts ) === 2 ) {
                $state_name = trim( $parts[0] );
                $state_cost = floatval( trim( $parts[1] ) );
                
                if ( $state_name === $state ) {
                    return $state_cost;
                }
            }
        }
        
        return 0;
    }
}
