<?php
/**
 * Zelle Payment Gateway
 * International payment method popular in Venezuela
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Zelle_Gateway extends WC_Payment_Gateway {
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->id = 'wvp_zelle';
        $this->icon = '';
        $this->has_fields = true;
        $this->method_title = 'Zelle Venezuela';
        $this->method_description = 'Permite pagos mediante Zelle (requiere cuenta bancaria en USA)';
        
        // Load settings
        $this->init_form_fields();
        $this->init_settings();
        
        // Define user set variables
        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->enabled = $this->get_option( 'enabled' );
        
        // Save settings
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }
    
    /**
     * Initialize Gateway Settings Form Fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Habilitar/Deshabilitar',
                'type' => 'checkbox',
                'label' => 'Habilitar Zelle',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout',
                'default' => 'Zelle',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => 'Descripción',
                'type' => 'textarea',
                'description' => 'Descripción que el usuario ve durante el checkout',
                'default' => 'Paga usando Zelle (requiere cuenta bancaria en USA)'
            ),
            'zelle_email' => array(
                'title' => 'Email de Zelle',
                'type' => 'email',
                'description' => 'Email asociado a la cuenta de Zelle',
                'default' => '',
                'desc_tip' => true,
            ),
            'bank_name' => array(
                'title' => 'Nombre del Banco',
                'type' => 'text',
                'description' => 'Nombre del banco en USA',
                'default' => '',
                'desc_tip' => true,
            ),
            'account_holder' => array(
                'title' => 'Titular de la Cuenta',
                'type' => 'text',
                'description' => 'Nombre del titular de la cuenta',
                'default' => '',
                'desc_tip' => true,
            )
        );
    }
    
    /**
     * Payment form on checkout page
     */
    public function payment_fields() {
        if ( $this->description ) {
            echo wpautop( wptexturize( $this->description ) );
        }
        
        echo '<div class="wvp-zelle-fields">';
        echo '<p class="form-row form-row-wide">';
        echo '<label for="' . $this->id . '_email">Email de Zelle <span class="required">*</span></label>';
        echo '<input type="email" class="input-text" name="' . $this->id . '_email" id="' . $this->id . '_email" placeholder="usuario@email.com" />';
        echo '</p>';
        
        echo '<p class="form-row form-row-wide">';
        echo '<label for="' . $this->id . '_phone">Teléfono (opcional)</label>';
        echo '<input type="tel" class="input-text" name="' . $this->id . '_phone" id="' . $this->id . '_phone" placeholder="+1-555-123-4567" />';
        echo '</p>';
        
        echo '<div class="wvp-zelle-info">';
        echo '<h4>Información para el Pago:</h4>';
        echo '<p><strong>Email:</strong> ' . esc_html( $this->get_option( 'zelle_email' ) ) . '</p>';
        echo '<p><strong>Banco:</strong> ' . esc_html( $this->get_option( 'bank_name' ) ) . '</p>';
        echo '<p><strong>Titular:</strong> ' . esc_html( $this->get_option( 'account_holder' ) ) . '</p>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Process the payment
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );
        
        // Get payment data
        $email = sanitize_email( $_POST[ $this->id . '_email' ] );
        $phone = sanitize_text_field( $_POST[ $this->id . '_phone' ] );
        
        // Validate data
        if ( empty( $email ) || ! is_email( $email ) ) {
            wc_add_notice( 'Por favor, ingresa un email válido para Zelle.', 'error' );
            return array(
                'result' => 'fail',
                'redirect' => ''
            );
        }
        
        // Store payment data
        $order->update_meta_data( '_zelle_email', $email );
        if ( ! empty( $phone ) ) {
            $order->update_meta_data( '_zelle_phone', $phone );
        }
        $order->update_meta_data( '_zelle_reference', $this->generate_reference() );
        $order->save();
        
        // Mark as pending payment
        $order->update_status( 'pending', 'Esperando confirmación de pago Zelle' );
        
        // Reduce stock levels
        wc_reduce_stock_levels( $order_id );
        
        // Remove cart
        WC()->cart->empty_cart();
        
        // Return success
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url( $order )
        );
    }
    
    /**
     * Generate payment reference
     */
    private function generate_reference() {
        return 'ZL' . date( 'Ymd' ) . rand( 1000, 9999 );
    }
}
