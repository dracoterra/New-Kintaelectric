<?php
/**
 * Bank Transfer Payment Gateway
 * Venezuelan bank transfer method
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Bank_Transfer_Gateway extends WC_Payment_Gateway {
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->id = 'wvp_bank_transfer';
        $this->icon = '';
        $this->has_fields = true;
        $this->method_title = 'Transferencia Bancaria Venezuela';
        $this->method_description = 'Permite pagos mediante transferencia bancaria venezolana';
        
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
                'label' => 'Habilitar Transferencia Bancaria',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'description' => 'Título que el usuario ve durante el checkout',
                'default' => 'Transferencia Bancaria',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => 'Descripción',
                'type' => 'textarea',
                'description' => 'Descripción que el usuario ve durante el checkout',
                'default' => 'Realiza una transferencia bancaria a nuestras cuentas'
            ),
            'bank_accounts' => array(
                'title' => 'Cuentas Bancarias',
                'type' => 'textarea',
                'description' => 'Información de las cuentas bancarias (una por línea)',
                'default' => "Banesco: 0134-1234-56-1234567890\nMercantil: 0105-1234-56-1234567890\nBBVA: 0108-1234-56-1234567890"
            ),
            'transfer_instructions' => array(
                'title' => 'Instrucciones de Transferencia',
                'type' => 'textarea',
                'description' => 'Instrucciones adicionales para el cliente',
                'default' => 'Realiza la transferencia y envía el comprobante por WhatsApp o email.'
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
        
        echo '<div class="wvp-bank-transfer-fields">';
        
        // Show bank accounts
        $bank_accounts = $this->get_option( 'bank_accounts' );
        if ( $bank_accounts ) {
            echo '<div class="wvp-bank-accounts">';
            echo '<h4>Cuentas Bancarias Disponibles:</h4>';
            echo '<ul>';
            $accounts = explode( "\n", $bank_accounts );
            foreach ( $accounts as $account ) {
                if ( trim( $account ) ) {
                    echo '<li>' . esc_html( trim( $account ) ) . '</li>';
                }
            }
            echo '</ul>';
            echo '</div>';
        }
        
        // Transfer instructions
        $instructions = $this->get_option( 'transfer_instructions' );
        if ( $instructions ) {
            echo '<div class="wvp-transfer-instructions">';
            echo '<h4>Instrucciones:</h4>';
            echo '<p>' . esc_html( $instructions ) . '</p>';
            echo '</div>';
        }
        
        // Form fields
        echo '<p class="form-row form-row-wide">';
        echo '<label for="' . $this->id . '_bank">Banco Seleccionado <span class="required">*</span></label>';
        echo '<select name="' . $this->id . '_bank" id="' . $this->id . '_bank">';
        echo '<option value="">Selecciona un banco</option>';
        if ( $bank_accounts ) {
            $accounts = explode( "\n", $bank_accounts );
            foreach ( $accounts as $account ) {
                if ( trim( $account ) ) {
                    $bank_name = explode( ':', $account )[0];
                    echo '<option value="' . esc_attr( $bank_name ) . '">' . esc_html( trim( $account ) ) . '</option>';
                }
            }
        }
        echo '</select>';
        echo '</p>';
        
        echo '<p class="form-row form-row-wide">';
        echo '<label for="' . $this->id . '_reference">Número de Referencia/Comprobante <span class="required">*</span></label>';
        echo '<input type="text" class="input-text" name="' . $this->id . '_reference" id="' . $this->id . '_reference" placeholder="Número de referencia de la transferencia" />';
        echo '</p>';
        
        echo '<p class="form-row form-row-wide">';
        echo '<label for="' . $this->id . '_date">Fecha de Transferencia <span class="required">*</span></label>';
        echo '<input type="date" class="input-text" name="' . $this->id . '_date" id="' . $this->id . '_date" />';
        echo '</p>';
        
        echo '</div>';
    }
    
    /**
     * Process the payment
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );
        
        // Get payment data
        $bank = sanitize_text_field( $_POST[ $this->id . '_bank' ] );
        $reference = sanitize_text_field( $_POST[ $this->id . '_reference' ] );
        $date = sanitize_text_field( $_POST[ $this->id . '_date' ] );
        
        // Validate data
        if ( empty( $bank ) || empty( $reference ) || empty( $date ) ) {
            wc_add_notice( 'Por favor, completa todos los campos de la transferencia bancaria.', 'error' );
            return array(
                'result' => 'fail',
                'redirect' => ''
            );
        }
        
        // Store payment data
        $order->update_meta_data( '_bank_transfer_bank', $bank );
        $order->update_meta_data( '_bank_transfer_reference', $reference );
        $order->update_meta_data( '_bank_transfer_date', $date );
        $order->save();
        
        // Mark as pending payment
        $order->update_status( 'pending', 'Esperando verificación de transferencia bancaria' );
        
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
}
