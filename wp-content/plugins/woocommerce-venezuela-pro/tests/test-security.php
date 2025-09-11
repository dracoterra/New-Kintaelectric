<?php
/**
 * Pruebas de Seguridad para WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Security_Tests {
    
    /**
     * Ejecutar todas las pruebas de seguridad
     */
    public static function run_all_tests() {
        echo "<h2>🧪 Pruebas de Seguridad - WooCommerce Venezuela Pro</h2>\n";
        
        $tests = array(
            'test_csrf_validation',
            'test_input_sanitization',
            'test_confirmation_validation',
            'test_rate_limiting',
            'test_user_permissions',
            'test_order_validation',
            'test_amount_validation',
            'test_cedula_rif_validation',
            'test_email_validation',
            'test_phone_validation'
        );
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $test) {
            echo "<h3>Ejecutando: {$test}</h3>\n";
            
            try {
                $result = self::$test();
                if ($result) {
                    echo "✅ <strong>PASÓ</strong><br>\n";
                    $passed++;
                } else {
                    echo "❌ <strong>FALLÓ</strong><br>\n";
                    $failed++;
                }
            } catch (Exception $e) {
                echo "❌ <strong>ERROR:</strong> " . $e->getMessage() . "<br>\n";
                $failed++;
            }
            
            echo "<br>\n";
        }
        
        echo "<h3>📊 Resumen de Pruebas</h3>\n";
        echo "✅ Pasaron: {$passed}<br>\n";
        echo "❌ Fallaron: {$failed}<br>\n";
        echo "📈 Total: " . ($passed + $failed) . "<br>\n";
        
        if ($failed === 0) {
            echo "<div style='color: green; font-weight: bold;'>🎉 ¡Todas las pruebas de seguridad pasaron!</div>\n";
        } else {
            echo "<div style='color: red; font-weight: bold;'>⚠️ Algunas pruebas fallaron. Revisar implementación.</div>\n";
        }
    }
    
    /**
     * Prueba de validación CSRF
     */
    public static function test_csrf_validation() {
        // Simular nonce válido
        $valid_nonce = wp_create_nonce('woocommerce-process_checkout');
        $result1 = WVP_Security_Validator::validate_nonce($valid_nonce, 'woocommerce-process_checkout');
        
        // Simular nonce inválido
        $result2 = WVP_Security_Validator::validate_nonce('invalid_nonce', 'woocommerce-process_checkout');
        
        // Simular nonce vacío
        $result3 = WVP_Security_Validator::validate_nonce('', 'woocommerce-process_checkout');
        
        echo "Nonce válido: " . ($result1 ? 'SÍ' : 'NO') . "<br>\n";
        echo "Nonce inválido: " . ($result2 ? 'SÍ' : 'NO') . "<br>\n";
        echo "Nonce vacío: " . ($result3 ? 'SÍ' : 'NO') . "<br>\n";
        
        return $result1 && !$result2 && !$result3;
    }
    
    /**
     * Prueba de sanitización de entrada
     */
    public static function test_input_sanitization() {
        $malicious_input = '<script>alert("XSS")</script>TEST123';
        $sanitized = WVP_Security_Validator::sanitize_input($malicious_input);
        
        echo "Entrada maliciosa: " . esc_html($malicious_input) . "<br>\n";
        echo "Salida sanitizada: " . esc_html($sanitized) . "<br>\n";
        
        $has_script = strpos($sanitized, '<script>') !== false;
        $has_test = strpos($sanitized, 'TEST123') !== false;
        
        echo "Contiene script: " . ($has_script ? 'SÍ' : 'NO') . "<br>\n";
        echo "Contiene TEST123: " . ($has_test ? 'SÍ' : 'NO') . "<br>\n";
        
        return !$has_script && $has_test;
    }
    
    /**
     * Prueba de validación de confirmación
     */
    public static function test_confirmation_validation() {
        $valid_confirmations = array('ABC123', '123456', 'TEST-123', 'A1B2C3');
        $invalid_confirmations = array('', '123', 'ABC', 'ABC12345678901234567890', 'ABC@123', 'ABC 123');
        
        $valid_results = array();
        $invalid_results = array();
        
        foreach ($valid_confirmations as $conf) {
            $result = WVP_Security_Validator::validate_confirmation($conf);
            $valid_results[] = $result;
            echo "Confirmación válida '{$conf}': " . ($result ? 'SÍ' : 'NO') . "<br>\n";
        }
        
        foreach ($invalid_confirmations as $conf) {
            $result = WVP_Security_Validator::validate_confirmation($conf);
            $invalid_results[] = !$result; // Debe ser false
            echo "Confirmación inválida '{$conf}': " . ($result ? 'SÍ' : 'NO') . "<br>\n";
        }
        
        return !in_array(false, $valid_results) && !in_array(false, $invalid_results);
    }
    
    /**
     * Prueba de rate limiting
     */
    public static function test_rate_limiting() {
        $user_id = 999999; // ID de prueba
        $action = 'test_payment_attempt';
        
        // Limpiar límites previos
        WVP_Rate_Limiter::clear_rate_limit($user_id, $action);
        
        // Hacer 6 intentos (límite es 5)
        $results = array();
        for ($i = 0; $i < 6; $i++) {
            $result = WVP_Rate_Limiter::check_rate_limit($user_id, $action, 5, 300);
            $results[] = $result;
            echo "Intento " . ($i + 1) . ": " . ($result ? 'PERMITIDO' : 'BLOQUEADO') . "<br>\n";
        }
        
        // Los primeros 5 deben ser permitidos, el 6to bloqueado
        $expected = array(true, true, true, true, true, false);
        
        echo "Resultados esperados: " . implode(', ', array_map(function($v) { return $v ? 'true' : 'false'; }, $expected)) . "<br>\n";
        echo "Resultados obtenidos: " . implode(', ', array_map(function($v) { return $v ? 'true' : 'false'; }, $results)) . "<br>\n";
        
        return $results === $expected;
    }
    
    /**
     * Prueba de validación de permisos de usuario
     */
    public static function test_user_permissions() {
        // Simular usuario con permisos
        $current_user = wp_get_current_user();
        $has_permissions = WVP_Security_Validator::validate_user_permissions();
        
        echo "Usuario actual: " . $current_user->user_login . "<br>\n";
        echo "Tiene permisos: " . ($has_permissions ? 'SÍ' : 'NO') . "<br>\n";
        
        return $has_permissions;
    }
    
    /**
     * Prueba de validación de pedido
     */
    public static function test_order_validation() {
        // Crear pedido de prueba
        $order = wc_create_order();
        $order->set_status('pending');
        $order->save();
        
        $valid_status = WVP_Security_Validator::validate_order_status($order, 'pending');
        $invalid_status = WVP_Security_Validator::validate_order_status($order, 'completed');
        $null_order = WVP_Security_Validator::validate_order_status(null, 'pending');
        
        echo "Estado válido: " . ($valid_status ? 'SÍ' : 'NO') . "<br>\n";
        echo "Estado inválido: " . ($invalid_status ? 'SÍ' : 'NO') . "<br>\n";
        echo "Pedido nulo: " . ($null_order ? 'SÍ' : 'NO') . "<br>\n";
        
        // Limpiar pedido de prueba
        wp_delete_post($order->get_id(), true);
        
        return $valid_status && !$invalid_status && !$null_order;
    }
    
    /**
     * Prueba de validación de montos
     */
    public static function test_amount_validation() {
        $amount = 100.50;
        $min_amount = 50.00;
        $max_amount = 200.00;
        
        $valid_amount = WVP_Security_Validator::validate_amount($amount, $min_amount, $max_amount);
        $too_low = WVP_Security_Validator::validate_amount(25.00, $min_amount, $max_amount);
        $too_high = WVP_Security_Validator::validate_amount(250.00, $min_amount, $max_amount);
        $negative = WVP_Security_Validator::validate_amount(-10.00, $min_amount, $max_amount);
        
        echo "Monto válido (100.50): " . ($valid_amount ? 'SÍ' : 'NO') . "<br>\n";
        echo "Monto muy bajo (25.00): " . ($too_low ? 'SÍ' : 'NO') . "<br>\n";
        echo "Monto muy alto (250.00): " . ($too_high ? 'SÍ' : 'NO') . "<br>\n";
        echo "Monto negativo (-10.00): " . ($negative ? 'SÍ' : 'NO') . "<br>\n";
        
        return $valid_amount && !$too_low && !$too_high && !$negative;
    }
    
    /**
     * Prueba de validación de cédula/RIF
     */
    public static function test_cedula_rif_validation() {
        $valid_cedulas = array('V-12345678', 'J-12345678-9', 'v-12345678', 'j-12345678-9');
        $invalid_cedulas = array('', 'V12345678', 'V-1234567', 'V-123456789', 'X-12345678', 'V-12345678-');
        
        $valid_results = array();
        $invalid_results = array();
        
        foreach ($valid_cedulas as $cedula) {
            $result = WVP_Security_Validator::validate_cedula_rif($cedula);
            $valid_results[] = $result;
            echo "Cédula válida '{$cedula}': " . ($result ? 'SÍ' : 'NO') . "<br>\n";
        }
        
        foreach ($invalid_cedulas as $cedula) {
            $result = WVP_Security_Validator::validate_cedula_rif($cedula);
            $invalid_results[] = !$result; // Debe ser false
            echo "Cédula inválida '{$cedula}': " . ($result ? 'SÍ' : 'NO') . "<br>\n";
        }
        
        return !in_array(false, $valid_results) && !in_array(false, $invalid_results);
    }
    
    /**
     * Prueba de validación de email
     */
    public static function test_email_validation() {
        $valid_emails = array('test@example.com', 'user.name@domain.co.uk', 'admin@site.org');
        $invalid_emails = array('', 'invalid-email', '@domain.com', 'user@', 'user@domain');
        
        $valid_results = array();
        $invalid_results = array();
        
        foreach ($valid_emails as $email) {
            $result = WVP_Security_Validator::validate_email($email);
            $valid_results[] = $result;
            echo "Email válido '{$email}': " . ($result ? 'SÍ' : 'NO') . "<br>\n";
        }
        
        foreach ($invalid_emails as $email) {
            $result = WVP_Security_Validator::validate_email($email);
            $invalid_results[] = !$result; // Debe ser false
            echo "Email inválido '{$email}': " . ($result ? 'SÍ' : 'NO') . "<br>\n";
        }
        
        return !in_array(false, $valid_results) && !in_array(false, $invalid_results);
    }
    
    /**
     * Prueba de validación de teléfono venezolano
     */
    public static function test_phone_validation() {
        $valid_phones = array('+584121234567', '04121234567', '04241234567', '04161234567');
        $invalid_phones = array('', '123456789', '0412123456', '041212345678', '+58412123456', '0412123456a');
        
        $valid_results = array();
        $invalid_results = array();
        
        foreach ($valid_phones as $phone) {
            $result = WVP_Security_Validator::validate_venezuelan_phone($phone);
            $valid_results[] = $result;
            echo "Teléfono válido '{$phone}': " . ($result ? 'SÍ' : 'NO') . "<br>\n";
        }
        
        foreach ($invalid_phones as $phone) {
            $result = WVP_Security_Validator::validate_venezuelan_phone($phone);
            $invalid_results[] = !$result; // Debe ser false
            echo "Teléfono inválido '{$phone}': " . ($result ? 'SÍ' : 'NO') . "<br>\n";
        }
        
        return !in_array(false, $valid_results) && !in_array(false, $invalid_results);
    }
}

// Función para ejecutar las pruebas desde el admin
if (is_admin()) {
    add_action('wp_ajax_wvp_run_security_tests', function() {
        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        WVP_Security_Tests::run_all_tests();
        wp_die();
    });
}
