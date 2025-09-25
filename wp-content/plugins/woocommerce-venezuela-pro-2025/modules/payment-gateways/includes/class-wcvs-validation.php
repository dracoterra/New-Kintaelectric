<?php
/**
 * Sistema de Validación - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para validaciones específicas de Venezuela
 */
class WCVS_Validation {

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
        // Hooks para validación de checkout
        add_action('woocommerce_checkout_process', array($this, 'validate_checkout_fields'));
        add_action('woocommerce_after_checkout_validation', array($this, 'validate_venezuelan_fields'), 10, 2);
        
        // Hooks para validación AJAX
        add_action('wp_ajax_wcvs_validate_rif', array($this, 'ajax_validate_rif'));
        add_action('wp_ajax_wcvs_validate_phone', array($this, 'ajax_validate_phone'));
        add_action('wp_ajax_nopriv_wcvs_validate_rif', array($this, 'ajax_validate_rif'));
        add_action('wp_ajax_nopriv_wcvs_validate_phone', array($this, 'ajax_validate_phone'));
    }

    /**
     * Validar campos de checkout
     */
    public function validate_checkout_fields() {
        // Validar RIF si está presente
        $rif = $_POST['billing_rif'] ?? '';
        if (!empty($rif) && !$this->validate_rif($rif)) {
            wc_add_notice('El RIF ingresado no es válido. Formato correcto: V-12345678-9', 'error');
        }

        // Validar teléfono venezolano
        $phone = $_POST['billing_phone'] ?? '';
        if (!empty($phone) && !$this->validate_venezuelan_phone($phone)) {
            wc_add_notice('El número de teléfono no es válido. Formato correcto: +58-XXX-XXXXXXX', 'error');
        }
    }

    /**
     * Validar campos específicos de Venezuela
     *
     * @param array $data Datos del checkout
     * @param WP_Error $errors Errores
     */
    public function validate_venezuelan_fields($data, $errors) {
        // Validar RIF
        if (isset($data['billing_rif']) && !empty($data['billing_rif'])) {
            if (!$this->validate_rif($data['billing_rif'])) {
                $errors->add('billing_rif', 'El RIF ingresado no es válido.');
            }
        }

        // Validar teléfono venezolano
        if (isset($data['billing_phone']) && !empty($data['billing_phone'])) {
            if (!$this->validate_venezuelan_phone($data['billing_phone'])) {
                $errors->add('billing_phone', 'El número de teléfono no es válido.');
            }
        }
    }

    /**
     * Validar RIF venezolano
     *
     * @param string $rif RIF a validar
     * @return bool
     */
    public function validate_rif($rif) {
        // Limpiar el RIF
        $rif = strtoupper(trim($rif));
        
        // Verificar formato básico: V-12345678-9
        if (!preg_match('/^[VEPGJC][\d]{8}[\d]$/', $rif)) {
            return false;
        }
        
        // Extraer componentes
        $tipo = substr($rif, 0, 1);
        $numero = substr($rif, 1, 8);
        $digito = substr($rif, 9, 1);
        
        // Validar tipo de RIF
        $tipos_validos = array('V', 'E', 'P', 'G', 'J', 'C');
        if (!in_array($tipo, $tipos_validos)) {
            return false;
        }
        
        // Validar dígito verificador
        return $this->validate_rif_digit($numero, $digito);
    }

    /**
     * Validar dígito verificador del RIF
     *
     * @param string $numero Número del RIF
     * @param string $digito Dígito verificador
     * @return bool
     */
    private function validate_rif_digit($numero, $digito) {
        $suma = 0;
        $multiplicadores = array(4, 3, 2, 7, 6, 5, 4, 3, 2);
        
        for ($i = 0; $i < 8; $i++) {
            $suma += intval($numero[$i]) * $multiplicadores[$i + 1];
        }
        
        $resto = $suma % 11;
        $digito_calculado = $resto < 2 ? $resto : 11 - $resto;
        
        return $digito == $digito_calculado;
    }

    /**
     * Validar teléfono venezolano
     *
     * @param string $phone Teléfono a validar
     * @return bool
     */
    public function validate_venezuelan_phone($phone) {
        // Limpiar el teléfono
        $phone = trim($phone);
        
        // Patrones válidos para teléfonos venezolanos
        $patrones = array(
            '/^(\+58|58)?[\s\-]?(\d{3})[\s\-]?(\d{7})$/',  // +58-XXX-XXXXXXX
            '/^(\+58|58)?[\s\-]?(\d{3})[\s\-]?(\d{3})[\s\-]?(\d{4})$/',  // +58-XXX-XXX-XXXX
            '/^(\d{3})[\s\-]?(\d{7})$/',  // XXX-XXXXXXX
            '/^(\d{3})[\s\-]?(\d{3})[\s\-]?(\d{4})$/'  // XXX-XXX-XXXX
        );
        
        foreach ($patrones as $patron) {
            if (preg_match($patron, $phone)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validar email
     *
     * @param string $email Email a validar
     * @return bool
     */
    public function validate_email($email) {
        return is_email($email);
    }

    /**
     * Validar cédula venezolana
     *
     * @param string $cedula Cédula a validar
     * @return bool
     */
    public function validate_cedula($cedula) {
        // Limpiar la cédula
        $cedula = trim($cedula);
        
        // Verificar formato: V-12345678-9
        if (!preg_match('/^V[\d]{8}[\d]$/', $cedula)) {
            return false;
        }
        
        // Extraer componentes
        $numero = substr($cedula, 1, 8);
        $digito = substr($cedula, 9, 1);
        
        // Validar dígito verificador
        return $this->validate_rif_digit($numero, $digito);
    }

    /**
     * Validar pasaporte
     *
     * @param string $pasaporte Pasaporte a validar
     * @return bool
     */
    public function validate_pasaporte($pasaporte) {
        // Limpiar el pasaporte
        $pasaporte = strtoupper(trim($pasaporte));
        
        // Patrones válidos para pasaportes
        $patrones = array(
            '/^[A-Z]{2}[\d]{7}$/',  // AA1234567
            '/^[A-Z]{1}[\d]{8}$/',  // A12345678
            '/^[\d]{9}$/'  // 123456789
        );
        
        foreach ($patrones as $patron) {
            if (preg_match($patron, $pasaporte)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validar dirección venezolana
     *
     * @param string $address Dirección a validar
     * @return bool
     */
    public function validate_address($address) {
        // Limpiar la dirección
        $address = trim($address);
        
        // Verificar que no esté vacía y tenga longitud mínima
        if (empty($address) || strlen($address) < 10) {
            return false;
        }
        
        // Verificar que contenga elementos típicos de dirección venezolana
        $elementos_tipicos = array(
            'av', 'avenida', 'calle', 'carrera', 'urbanización', 'sector',
            'edificio', 'torre', 'piso', 'apartamento', 'casa'
        );
        
        $address_lower = strtolower($address);
        foreach ($elementos_tipicos as $elemento) {
            if (strpos($address_lower, $elemento) !== false) {
                return true;
            }
        }
        
        // Si no contiene elementos típicos, verificar que tenga al menos 3 palabras
        $palabras = explode(' ', $address);
        return count($palabras) >= 3;
    }

    /**
     * Validar código postal venezolano
     *
     * @param string $postcode Código postal a validar
     * @return bool
     */
    public function validate_postcode($postcode) {
        // Limpiar el código postal
        $postcode = trim($postcode);
        
        // Códigos postales venezolanos tienen formato: 1234 o 12345
        return preg_match('/^\d{4,5}$/', $postcode);
    }

    /**
     * Validar estado venezolano
     *
     * @param string $state Estado a validar
     * @return bool
     */
    public function validate_state($state) {
        $estados_venezuela = array(
            'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas',
            'Bolívar', 'Carabobo', 'Cojedes', 'Delta Amacuro',
            'Distrito Capital', 'Falcón', 'Guárico', 'Lara',
            'Mérida', 'Miranda', 'Monagas', 'Nueva Esparta',
            'Portuguesa', 'Sucre', 'Táchira', 'Trujillo',
            'Vargas', 'Yaracuy', 'Zulia'
        );
        
        return in_array($state, $estados_venezuela);
    }

    /**
     * Validar ciudad venezolana
     *
     * @param string $city Ciudad a validar
     * @return bool
     */
    public function validate_city($city) {
        // Limpiar la ciudad
        $city = trim($city);
        
        // Verificar que no esté vacía y tenga longitud mínima
        if (empty($city) || strlen($city) < 2) {
            return false;
        }
        
        // Verificar que contenga solo letras, espacios y caracteres especiales venezolanos
        return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\'\.]+$/', $city);
    }

    /**
     * Validar AJAX RIF
     */
    public function ajax_validate_rif() {
        check_ajax_referer('wcvs_validation_nonce', 'nonce');
        
        $rif = sanitize_text_field($_POST['rif'] ?? '');
        $valid = $this->validate_rif($rif);
        
        wp_send_json(array(
            'valid' => $valid,
            'message' => $valid ? 'RIF válido' : 'RIF inválido'
        ));
    }

    /**
     * Validar AJAX teléfono
     */
    public function ajax_validate_phone() {
        check_ajax_referer('wcvs_validation_nonce', 'nonce');
        
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $valid = $this->validate_venezuelan_phone($phone);
        
        wp_send_json(array(
            'valid' => $valid,
            'message' => $valid ? 'Teléfono válido' : 'Teléfono inválido'
        ));
    }

    /**
     * Formatear RIF
     *
     * @param string $rif RIF a formatear
     * @return string
     */
    public function format_rif($rif) {
        $rif = strtoupper(trim($rif));
        
        if (preg_match('/^([VEPGJC])([\d]{8})([\d])$/', $rif, $matches)) {
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        }
        
        return $rif;
    }

    /**
     * Formatear teléfono venezolano
     *
     * @param string $phone Teléfono a formatear
     * @return string
     */
    public function format_phone($phone) {
        $phone = preg_replace('/[^\d]/', '', $phone);
        
        if (strlen($phone) == 10) {
            return '+58-' . substr($phone, 0, 3) . '-' . substr($phone, 3);
        } elseif (strlen($phone) == 11 && substr($phone, 0, 2) == '58') {
            return '+58-' . substr($phone, 2, 3) . '-' . substr($phone, 5);
        }
        
        return $phone;
    }

    /**
     * Obtener información del RIF
     *
     * @param string $rif RIF
     * @return array
     */
    public function get_rif_info($rif) {
        if (!$this->validate_rif($rif)) {
            return array('valid' => false);
        }
        
        $tipo = substr($rif, 0, 1);
        $tipos_info = array(
            'V' => array('nombre' => 'Persona Natural', 'descripcion' => 'Persona física'),
            'E' => array('nombre' => 'Extranjero', 'descripcion' => 'Persona extranjera'),
            'P' => array('nombre' => 'Pasaporte', 'descripcion' => 'Persona con pasaporte'),
            'G' => array('nombre' => 'Gobierno', 'descripcion' => 'Entidad gubernamental'),
            'J' => array('nombre' => 'Jurídico', 'descripcion' => 'Persona jurídica'),
            'C' => array('nombre' => 'Consorcio', 'descripcion' => 'Consorcio o asociación')
        );
        
        return array(
            'valid' => true,
            'tipo' => $tipo,
            'info' => $tipos_info[$tipo] ?? array('nombre' => 'Desconocido', 'descripcion' => 'Tipo no reconocido')
        );
    }

    /**
     * Validar datos completos de Venezuela
     *
     * @param array $data Datos a validar
     * @return array
     */
    public function validate_venezuelan_data($data) {
        $errors = array();
        
        // Validar RIF
        if (isset($data['rif']) && !empty($data['rif'])) {
            if (!$this->validate_rif($data['rif'])) {
                $errors['rif'] = 'RIF inválido';
            }
        }
        
        // Validar teléfono
        if (isset($data['phone']) && !empty($data['phone'])) {
            if (!$this->validate_venezuelan_phone($data['phone'])) {
                $errors['phone'] = 'Teléfono inválido';
            }
        }
        
        // Validar email
        if (isset($data['email']) && !empty($data['email'])) {
            if (!$this->validate_email($data['email'])) {
                $errors['email'] = 'Email inválido';
            }
        }
        
        // Validar dirección
        if (isset($data['address']) && !empty($data['address'])) {
            if (!$this->validate_address($data['address'])) {
                $errors['address'] = 'Dirección inválida';
            }
        }
        
        // Validar estado
        if (isset($data['state']) && !empty($data['state'])) {
            if (!$this->validate_state($data['state'])) {
                $errors['state'] = 'Estado inválido';
            }
        }
        
        // Validar ciudad
        if (isset($data['city']) && !empty($data['city'])) {
            if (!$this->validate_city($data['city'])) {
                $errors['city'] = 'Ciudad inválida';
            }
        }
        
        // Validar código postal
        if (isset($data['postcode']) && !empty($data['postcode'])) {
            if (!$this->validate_postcode($data['postcode'])) {
                $errors['postcode'] = 'Código postal inválido';
            }
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }
}
