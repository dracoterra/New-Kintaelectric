<?php
/**
 * Venezuelan Data Validator
 * Specific validation for Venezuelan data formats
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Venezuelan_Validator {
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'woocommerce_checkout_process', array( $this, 'validate_checkout_data' ) );
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_venezuelan_fields' ), 10, 2 );
        add_filter( 'woocommerce_process_checkout_field_billing_phone', array( $this, 'validate_phone_number' ) );
        add_filter( 'woocommerce_process_checkout_field_billing_postcode', array( $this, 'validate_postal_code' ) );
    }
    
    /**
     * Validate Venezuelan RIF (Registro de Información Fiscal)
     */
    public function validate_rif( $rif ) {
        // Remove spaces and convert to uppercase
        $rif = strtoupper( preg_replace( '/\s+/', '', $rif ) );
        
        // Check format: Letter followed by 8-9 digits
        if ( ! preg_match( '/^[VEPGJC][0-9]{8,9}$/', $rif ) ) {
            return false;
        }
        
        // Extract letter and numbers
        $letter = $rif[0];
        $numbers = substr( $rif, 1 );
        
        // Calculate check digit
        $multipliers = array( 4, 3, 2, 7, 6, 5, 4, 3, 2 );
        $sum = 0;
        
        for ( $i = 0; $i < strlen( $numbers ); $i++ ) {
            $sum += intval( $numbers[$i] ) * $multipliers[$i];
        }
        
        $remainder = $sum % 11;
        $check_digit = ( $remainder < 2 ) ? $remainder : 11 - $remainder;
        
        // Validate check digit
        return $check_digit == intval( substr( $numbers, -1 ) );
    }
    
    /**
     * Validate Venezuelan phone number
     */
    public function validate_phone_number( $phone ) {
        // Remove spaces, dashes, and parentheses
        $phone = preg_replace( '/[\s\-\(\)]/', '', $phone );
        
        // Check if it starts with country code
        if ( strpos( $phone, '+58' ) === 0 ) {
            $phone = substr( $phone, 3 );
        } elseif ( strpos( $phone, '58' ) === 0 ) {
            $phone = substr( $phone, 2 );
        }
        
        // Venezuelan mobile numbers start with 04 and have 11 digits total
        if ( preg_match( '/^04\d{9}$/', $phone ) ) {
            return true;
        }
        
        // Venezuelan landline numbers start with 02 and have 10 digits total
        if ( preg_match( '/^02\d{8}$/', $phone ) ) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Validate Venezuelan postal code
     */
    public function validate_postal_code( $postal_code ) {
        // Venezuelan postal codes are 4 digits
        return preg_match( '/^\d{4}$/', $postal_code );
    }
    
    /**
     * Validate Venezuelan bank account number
     */
    public function validate_bank_account( $account_number, $bank_code ) {
        // Remove spaces and dashes
        $account_number = preg_replace( '/[\s\-]/', '', $account_number );
        
        // Different banks have different account number formats
        $bank_formats = array(
            'banesco' => '/^\d{20}$/',           // 20 digits
            'mercantil' => '/^\d{20}$/',        // 20 digits
            'bbva' => '/^\d{20}$/',             // 20 digits
            'venezuela' => '/^\d{20}$/',        // 20 digits
            'bnc' => '/^\d{20}$/',              // 20 digits
            '100banco' => '/^\d{20}$/',         // 20 digits
            'banplus' => '/^\d{20}$/',          // 20 digits
            'bod' => '/^\d{20}$/',              // 20 digits
            'exterior' => '/^\d{20}$/',         // 20 digits
            'plaza' => '/^\d{20}$/',            // 20 digits
            'sofitasa' => '/^\d{20}$/',         // 20 digits
            'venezolano' => '/^\d{20}$/'        // 20 digits
        );
        
        if ( isset( $bank_formats[ $bank_code ] ) ) {
            return preg_match( $bank_formats[ $bank_code ], $account_number );
        }
        
        return false;
    }
    
    /**
     * Validate Venezuelan state
     */
    public function validate_state( $state ) {
        $venezuelan_states = array(
            'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas',
            'Bolívar', 'Carabobo', 'Cojedes', 'Delta Amacuro',
            'Distrito Capital', 'Falcón', 'Guárico', 'Lara', 'Mérida',
            'Miranda', 'Monagas', 'Nueva Esparta', 'Portuguesa',
            'Sucre', 'Táchira', 'Trujillo', 'Vargas', 'Yaracuy', 'Zulia'
        );
        
        return in_array( $state, $venezuelan_states );
    }
    
    /**
     * Validate Venezuelan city
     */
    public function validate_city( $city, $state ) {
        // This would typically check against a database of Venezuelan cities
        // For now, we'll do basic validation
        return ! empty( $city ) && strlen( $city ) >= 2;
    }
    
    /**
     * Validate Venezuelan address
     */
    public function validate_address( $address ) {
        // Basic address validation
        if ( empty( $address ) || strlen( $address ) < 10 ) {
            return false;
        }
        
        // Check for common Venezuelan address patterns
        $patterns = array(
            '/\b(av|avenida|calle|carretera|urbanización|sector|zona)\b/i',
            '/\b(centro|este|oeste|norte|sur)\b/i',
            '/\b(edificio|torre|apartamento|casa|local)\b/i'
        );
        
        foreach ( $patterns as $pattern ) {
            if ( preg_match( $pattern, $address ) ) {
                return true;
            }
        }
        
        return true; // Allow other formats
    }
    
    /**
     * Validate checkout data
     */
    public function validate_checkout_data() {
        $errors = array();
        
        // Validate billing phone
        $phone = $_POST['billing_phone'] ?? '';
        if ( ! empty( $phone ) && ! $this->validate_phone_number( $phone ) ) {
            $errors[] = 'Número de teléfono inválido. Use el formato venezolano: 0412-1234567';
        }
        
        // Validate billing postal code
        $postal_code = $_POST['billing_postcode'] ?? '';
        if ( ! empty( $postal_code ) && ! $this->validate_postal_code( $postal_code ) ) {
            $errors[] = 'Código postal inválido. Use 4 dígitos.';
        }
        
        // Validate billing state
        $state = $_POST['billing_state'] ?? '';
        if ( ! empty( $state ) && ! $this->validate_state( $state ) ) {
            $errors[] = 'Estado inválido. Seleccione un estado venezolano válido.';
        }
        
        // Validate billing address
        $address = $_POST['billing_address_1'] ?? '';
        if ( ! empty( $address ) && ! $this->validate_address( $address ) ) {
            $errors[] = 'Dirección inválida. Proporcione una dirección completa.';
        }
        
        // Add errors to WooCommerce
        foreach ( $errors as $error ) {
            wc_add_notice( $error, 'error' );
        }
    }
    
    /**
     * Validate Venezuelan fields
     */
    public function validate_venezuelan_fields( $data, $errors ) {
        // Check if RIF field exists and validate it
        if ( isset( $_POST['billing_rif'] ) && ! empty( $_POST['billing_rif'] ) ) {
            if ( ! $this->validate_rif( $_POST['billing_rif'] ) ) {
                $errors->add( 'billing_rif', 'RIF inválido. Use el formato: V-12345678' );
            }
        }
        
        // Validate payment method specific data
        $payment_method = $_POST['payment_method'] ?? '';
        
        if ( $payment_method === 'wvp_pago_movil' ) {
            $this->validate_pago_movil_data( $errors );
        } elseif ( $payment_method === 'wvp_bank_transfer' ) {
            $this->validate_bank_transfer_data( $errors );
        }
    }
    
    /**
     * Validate Pago Móvil data
     */
    private function validate_pago_movil_data( $errors ) {
        $phone = $_POST['wvp_pago_movil_phone'] ?? '';
        $cedula = $_POST['wvp_pago_movil_cedula'] ?? '';
        $bank = $_POST['wvp_pago_movil_bank'] ?? '';
        
        if ( empty( $phone ) ) {
            $errors->add( 'pago_movil_phone', 'Número de teléfono es requerido para Pago Móvil' );
        } elseif ( ! $this->validate_phone_number( $phone ) ) {
            $errors->add( 'pago_movil_phone', 'Número de teléfono inválido para Pago Móvil' );
        }
        
        if ( empty( $cedula ) ) {
            $errors->add( 'pago_movil_cedula', 'Cédula es requerida para Pago Móvil' );
        } elseif ( ! $this->validate_cedula( $cedula ) ) {
            $errors->add( 'pago_movil_cedula', 'Cédula inválida. Use el formato: V-12345678' );
        }
        
        if ( empty( $bank ) ) {
            $errors->add( 'pago_movil_bank', 'Banco es requerido para Pago Móvil' );
        }
    }
    
    /**
     * Validate bank transfer data
     */
    private function validate_bank_transfer_data( $errors ) {
        $bank = $_POST['wvp_bank_transfer_bank'] ?? '';
        $reference = $_POST['wvp_bank_transfer_reference'] ?? '';
        $date = $_POST['wvp_bank_transfer_date'] ?? '';
        
        if ( empty( $bank ) ) {
            $errors->add( 'bank_transfer_bank', 'Banco es requerido para transferencia bancaria' );
        }
        
        if ( empty( $reference ) ) {
            $errors->add( 'bank_transfer_reference', 'Número de referencia es requerido' );
        } elseif ( ! preg_match( '/^\d{6,20}$/', $reference ) ) {
            $errors->add( 'bank_transfer_reference', 'Número de referencia inválido' );
        }
        
        if ( empty( $date ) ) {
            $errors->add( 'bank_transfer_date', 'Fecha de transferencia es requerida' );
        } elseif ( strtotime( $date ) > time() ) {
            $errors->add( 'bank_transfer_date', 'La fecha de transferencia no puede ser futura' );
        }
    }
    
    /**
     * Validate Venezuelan cedula
     */
    public function validate_cedula( $cedula ) {
        // Remove spaces and dashes
        $cedula = strtoupper( preg_replace( '/[\s\-]/', '', $cedula ) );
        
        // Check format: V followed by 7-8 digits
        if ( ! preg_match( '/^V\d{7,8}$/', $cedula ) ) {
            return false;
        }
        
        // Extract numbers
        $numbers = substr( $cedula, 1 );
        
        // Calculate check digit (similar to RIF validation)
        $multipliers = array( 3, 2, 7, 6, 5, 4, 3, 2 );
        $sum = 0;
        
        for ( $i = 0; $i < strlen( $numbers ) - 1; $i++ ) {
            $sum += intval( $numbers[$i] ) * $multipliers[$i];
        }
        
        $remainder = $sum % 11;
        $check_digit = ( $remainder < 2 ) ? $remainder : 11 - $remainder;
        
        // Validate check digit
        return $check_digit == intval( substr( $numbers, -1 ) );
    }
    
    /**
     * Format Venezuelan phone number
     */
    public function format_phone_number( $phone ) {
        // Remove all non-numeric characters
        $phone = preg_replace( '/\D/', '', $phone );
        
        // Add country code if not present
        if ( strlen( $phone ) === 10 && substr( $phone, 0, 2 ) === '04' ) {
            $phone = '58' . $phone;
        } elseif ( strlen( $phone ) === 9 && substr( $phone, 0, 1 ) === '4' ) {
            $phone = '5804' . $phone;
        }
        
        // Format as +58-412-123-4567
        if ( strlen( $phone ) === 12 && substr( $phone, 0, 2 ) === '58' ) {
            $country_code = substr( $phone, 0, 2 );
            $area_code = substr( $phone, 2, 3 );
            $first_part = substr( $phone, 5, 3 );
            $second_part = substr( $phone, 8, 4 );
            
            return '+' . $country_code . '-' . $area_code . '-' . $first_part . '-' . $second_part;
        }
        
        return $phone;
    }
    
    /**
     * Format Venezuelan RIF
     */
    public function format_rif( $rif ) {
        // Remove spaces and convert to uppercase
        $rif = strtoupper( preg_replace( '/\s+/', '', $rif ) );
        
        // Add dash if not present
        if ( strlen( $rif ) >= 9 && strpos( $rif, '-' ) === false ) {
            $letter = $rif[0];
            $numbers = substr( $rif, 1 );
            $rif = $letter . '-' . $numbers;
        }
        
        return $rif;
    }
    
    /**
     * Get Venezuelan states list
     */
    public function get_venezuelan_states() {
        return array(
            'Amazonas' => 'Amazonas',
            'Anzoátegui' => 'Anzoátegui',
            'Apure' => 'Apure',
            'Aragua' => 'Aragua',
            'Barinas' => 'Barinas',
            'Bolívar' => 'Bolívar',
            'Carabobo' => 'Carabobo',
            'Cojedes' => 'Cojedes',
            'Delta Amacuro' => 'Delta Amacuro',
            'Distrito Capital' => 'Distrito Capital',
            'Falcón' => 'Falcón',
            'Guárico' => 'Guárico',
            'Lara' => 'Lara',
            'Mérida' => 'Mérida',
            'Miranda' => 'Miranda',
            'Monagas' => 'Monagas',
            'Nueva Esparta' => 'Nueva Esparta',
            'Portuguesa' => 'Portuguesa',
            'Sucre' => 'Sucre',
            'Táchira' => 'Táchira',
            'Trujillo' => 'Trujillo',
            'Vargas' => 'Vargas',
            'Yaracuy' => 'Yaracuy',
            'Zulia' => 'Zulia'
        );
    }
    
    /**
     * Get Venezuelan banks list
     */
    public function get_venezuelan_banks() {
        return array(
            'banesco' => 'Banesco',
            'mercantil' => 'Mercantil Banco Universal',
            'bbva' => 'BBVA Provincial',
            'venezuela' => 'Banco de Venezuela',
            'bnc' => 'Banco Nacional de Crédito',
            '100banco' => '100% Banco',
            'banplus' => 'Banplus',
            'bod' => 'BOD',
            'exterior' => 'Banco del Exterior',
            'plaza' => 'Banco Plaza',
            'sofitasa' => 'Sofitasa',
            'venezolano' => 'Banco Venezolano de Crédito'
        );
    }
}
