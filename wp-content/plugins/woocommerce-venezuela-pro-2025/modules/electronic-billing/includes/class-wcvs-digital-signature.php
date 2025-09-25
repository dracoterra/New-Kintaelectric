<?php
/**
 * Sistema de Firma Digital - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar la firma digital de documentos
 */
class WCVS_Digital_Signature {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Tipos de firma digital
     *
     * @var array
     */
    private $signature_types = array(
        'simple' => 'Firma Simple',
        'advanced' => 'Firma Avanzada',
        'certified' => 'Firma Certificada'
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('electronic_billing', array());
    }

    /**
     * Firmar documento
     *
     * @param string $document_content Contenido del documento
     * @param string $signature_type Tipo de firma
     * @return array|false
     */
    public function sign_document($document_content, $signature_type = 'simple') {
        if (!isset($this->signature_types[$signature_type])) {
            return false;
        }

        // Generar hash del documento
        $document_hash = $this->generate_document_hash($document_content);
        
        // Generar firma digital
        $signature = $this->generate_signature($document_hash, $signature_type);
        
        // Crear metadatos de la firma
        $signature_metadata = array(
            'type' => $signature_type,
            'hash' => $document_hash,
            'signature' => $signature,
            'timestamp' => current_time('mysql'),
            'algorithm' => $this->get_signature_algorithm($signature_type),
            'certificate' => $this->get_certificate_info($signature_type)
        );

        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, "Documento firmado digitalmente con tipo: {$signature_type}");

        return $signature_metadata;
    }

    /**
     * Generar hash del documento
     *
     * @param string $document_content Contenido del documento
     * @return string
     */
    private function generate_document_hash($document_content) {
        // Normalizar contenido del documento
        $normalized_content = $this->normalize_document_content($document_content);
        
        // Generar hash SHA-256
        return hash('sha256', $normalized_content);
    }

    /**
     * Normalizar contenido del documento
     *
     * @param string $document_content Contenido del documento
     * @return string
     */
    private function normalize_document_content($document_content) {
        // Remover espacios en blanco extra
        $normalized = preg_replace('/\s+/', ' ', $document_content);
        
        // Remover caracteres de control
        $normalized = preg_replace('/[\x00-\x1F\x7F]/', '', $normalized);
        
        // Convertir a minúsculas para consistencia
        $normalized = strtolower($normalized);
        
        return trim($normalized);
    }

    /**
     * Generar firma digital
     *
     * @param string $document_hash Hash del documento
     * @param string $signature_type Tipo de firma
     * @return string
     */
    private function generate_signature($document_hash, $signature_type) {
        switch ($signature_type) {
            case 'simple':
                return $this->generate_simple_signature($document_hash);
            case 'advanced':
                return $this->generate_advanced_signature($document_hash);
            case 'certified':
                return $this->generate_certified_signature($document_hash);
            default:
                return $this->generate_simple_signature($document_hash);
        }
    }

    /**
     * Generar firma simple
     *
     * @param string $document_hash Hash del documento
     * @return string
     */
    private function generate_simple_signature($document_hash) {
        // Firma simple usando clave privada de la empresa
        $private_key = $this->get_company_private_key();
        $signature_data = $document_hash . $private_key . current_time('timestamp');
        
        return hash('sha256', $signature_data);
    }

    /**
     * Generar firma avanzada
     *
     * @param string $document_hash Hash del documento
     * @return string
     */
    private function generate_advanced_signature($document_hash) {
        // Firma avanzada con algoritmo RSA
        $private_key = $this->get_company_private_key();
        $signature_data = $document_hash . $private_key . current_time('timestamp');
        
        // Simular firma RSA (en producción usaría OpenSSL)
        $signature = hash('sha256', $signature_data);
        
        // Añadir metadatos adicionales
        $advanced_signature = array(
            'signature' => $signature,
            'algorithm' => 'RSA-SHA256',
            'timestamp' => current_time('timestamp'),
            'key_id' => $this->get_key_id()
        );
        
        return base64_encode(json_encode($advanced_signature));
    }

    /**
     * Generar firma certificada
     *
     * @param string $document_hash Hash del documento
     * @return string
     */
    private function generate_certified_signature($document_hash) {
        // Firma certificada con certificado digital
        $certificate = $this->get_digital_certificate();
        $private_key = $this->get_company_private_key();
        
        $signature_data = $document_hash . $private_key . $certificate . current_time('timestamp');
        $signature = hash('sha256', $signature_data);
        
        // Crear firma certificada completa
        $certified_signature = array(
            'signature' => $signature,
            'algorithm' => 'RSA-SHA256',
            'timestamp' => current_time('timestamp'),
            'certificate' => $certificate,
            'key_id' => $this->get_key_id(),
            'issuer' => $this->get_certificate_issuer(),
            'valid_from' => $this->get_certificate_valid_from(),
            'valid_to' => $this->get_certificate_valid_to()
        );
        
        return base64_encode(json_encode($certified_signature));
    }

    /**
     * Obtener clave privada de la empresa
     *
     * @return string
     */
    private function get_company_private_key() {
        $private_key = $this->settings['digital_signature']['private_key'] ?? '';
        
        if (empty($private_key)) {
            // Generar clave privada por defecto (en producción sería más seguro)
            $private_key = $this->generate_default_private_key();
        }
        
        return $private_key;
    }

    /**
     * Generar clave privada por defecto
     *
     * @return string
     */
    private function generate_default_private_key() {
        // En producción, esto debería ser generado de forma segura
        $company_data = $this->settings['company_data'] ?? array();
        $company_name = $company_data['name'] ?? 'Mi Empresa';
        $company_rif = $company_data['rif'] ?? 'J-00000000-0';
        
        return hash('sha256', $company_name . $company_rif . 'WCVS_PRIVATE_KEY');
    }

    /**
     * Obtener ID de la clave
     *
     * @return string
     */
    private function get_key_id() {
        $key_id = $this->settings['digital_signature']['key_id'] ?? '';
        
        if (empty($key_id)) {
            $key_id = 'WCVS_' . substr(md5($this->get_company_private_key()), 0, 8);
        }
        
        return $key_id;
    }

    /**
     * Obtener certificado digital
     *
     * @return string
     */
    private function get_digital_certificate() {
        $certificate = $this->settings['digital_signature']['certificate'] ?? '';
        
        if (empty($certificate)) {
            // Generar certificado por defecto (en producción sería un certificado real)
            $certificate = $this->generate_default_certificate();
        }
        
        return $certificate;
    }

    /**
     * Generar certificado por defecto
     *
     * @return string
     */
    private function generate_default_certificate() {
        $company_data = $this->settings['company_data'] ?? array();
        $company_name = $company_data['name'] ?? 'Mi Empresa';
        $company_rif = $company_data['rif'] ?? 'J-00000000-0';
        
        $certificate_data = array(
            'subject' => $company_name,
            'rif' => $company_rif,
            'issuer' => 'WCVS Digital Certificate Authority',
            'valid_from' => date('Y-m-d'),
            'valid_to' => date('Y-m-d', strtotime('+1 year')),
            'serial_number' => 'WCVS-' . wp_rand(100000, 999999)
        );
        
        return base64_encode(json_encode($certificate_data));
    }

    /**
     * Obtener emisor del certificado
     *
     * @return string
     */
    private function get_certificate_issuer() {
        return $this->settings['digital_signature']['certificate_issuer'] ?? 'WCVS Digital Certificate Authority';
    }

    /**
     * Obtener fecha de validez desde
     *
     * @return string
     */
    private function get_certificate_valid_from() {
        return $this->settings['digital_signature']['certificate_valid_from'] ?? date('Y-m-d');
    }

    /**
     * Obtener fecha de validez hasta
     *
     * @return string
     */
    private function get_certificate_valid_to() {
        return $this->settings['digital_signature']['certificate_valid_to'] ?? date('Y-m-d', strtotime('+1 year'));
    }

    /**
     * Obtener algoritmo de firma
     *
     * @param string $signature_type Tipo de firma
     * @return string
     */
    private function get_signature_algorithm($signature_type) {
        $algorithms = array(
            'simple' => 'SHA-256',
            'advanced' => 'RSA-SHA256',
            'certified' => 'RSA-SHA256'
        );
        
        return $algorithms[$signature_type] ?? 'SHA-256';
    }

    /**
     * Obtener información del certificado
     *
     * @param string $signature_type Tipo de firma
     * @return array
     */
    private function get_certificate_info($signature_type) {
        if ($signature_type === 'certified') {
            return array(
                'issuer' => $this->get_certificate_issuer(),
                'valid_from' => $this->get_certificate_valid_from(),
                'valid_to' => $this->get_certificate_valid_to(),
                'serial_number' => 'WCVS-' . wp_rand(100000, 999999)
            );
        }
        
        return array();
    }

    /**
     * Verificar firma digital
     *
     * @param string $document_content Contenido del documento
     * @param array $signature_metadata Metadatos de la firma
     * @return bool
     */
    public function verify_signature($document_content, $signature_metadata) {
        // Generar hash del documento
        $document_hash = $this->generate_document_hash($document_content);
        
        // Verificar que el hash coincida
        if ($document_hash !== $signature_metadata['hash']) {
            return false;
        }
        
        // Verificar firma según el tipo
        switch ($signature_metadata['type']) {
            case 'simple':
                return $this->verify_simple_signature($document_hash, $signature_metadata);
            case 'advanced':
                return $this->verify_advanced_signature($document_hash, $signature_metadata);
            case 'certified':
                return $this->verify_certified_signature($document_hash, $signature_metadata);
            default:
                return false;
        }
    }

    /**
     * Verificar firma simple
     *
     * @param string $document_hash Hash del documento
     * @param array $signature_metadata Metadatos de la firma
     * @return bool
     */
    private function verify_simple_signature($document_hash, $signature_metadata) {
        $private_key = $this->get_company_private_key();
        $signature_data = $document_hash . $private_key . $signature_metadata['timestamp'];
        $expected_signature = hash('sha256', $signature_data);
        
        return $expected_signature === $signature_metadata['signature'];
    }

    /**
     * Verificar firma avanzada
     *
     * @param string $document_hash Hash del documento
     * @param array $signature_metadata Metadatos de la firma
     * @return bool
     */
    private function verify_advanced_signature($document_hash, $signature_metadata) {
        $signature_data = json_decode(base64_decode($signature_metadata['signature']), true);
        if (!$signature_data) {
            return false;
        }
        
        $private_key = $this->get_company_private_key();
        $expected_signature_data = $document_hash . $private_key . $signature_data['timestamp'];
        $expected_signature = hash('sha256', $expected_signature_data);
        
        return $expected_signature === $signature_data['signature'];
    }

    /**
     * Verificar firma certificada
     *
     * @param string $document_hash Hash del documento
     * @param array $signature_metadata Metadatos de la firma
     * @return bool
     */
    private function verify_certified_signature($document_hash, $signature_metadata) {
        $signature_data = json_decode(base64_decode($signature_metadata['signature']), true);
        if (!$signature_data) {
            return false;
        }
        
        // Verificar certificado
        if (!$this->verify_certificate($signature_data['certificate'])) {
            return false;
        }
        
        $private_key = $this->get_company_private_key();
        $expected_signature_data = $document_hash . $private_key . $signature_data['certificate'] . $signature_data['timestamp'];
        $expected_signature = hash('sha256', $expected_signature_data);
        
        return $expected_signature === $signature_data['signature'];
    }

    /**
     * Verificar certificado digital
     *
     * @param string $certificate Certificado
     * @return bool
     */
    private function verify_certificate($certificate) {
        $certificate_data = json_decode(base64_decode($certificate), true);
        if (!$certificate_data) {
            return false;
        }
        
        // Verificar fecha de validez
        $current_date = date('Y-m-d');
        if ($current_date < $certificate_data['valid_from'] || $current_date > $certificate_data['valid_to']) {
            return false;
        }
        
        // Verificar emisor
        $expected_issuer = $this->get_certificate_issuer();
        if ($certificate_data['issuer'] !== $expected_issuer) {
            return false;
        }
        
        return true;
    }

    /**
     * Generar firma para documento XML
     *
     * @param string $xml_content Contenido XML
     * @param string $signature_type Tipo de firma
     * @return string|false
     */
    public function sign_xml_document($xml_content, $signature_type = 'simple') {
        // Generar firma digital
        $signature_metadata = $this->sign_document($xml_content, $signature_type);
        if (!$signature_metadata) {
            return false;
        }
        
        // Crear elemento de firma XML
        $signature_element = $this->create_xml_signature_element($signature_metadata);
        
        // Insertar firma en el XML
        $signed_xml = $this->insert_signature_in_xml($xml_content, $signature_element);
        
        return $signed_xml;
    }

    /**
     * Crear elemento de firma XML
     *
     * @param array $signature_metadata Metadatos de la firma
     * @return string
     */
    private function create_xml_signature_element($signature_metadata) {
        $signature_xml = '<Signature>';
        $signature_xml .= '<Type>' . esc_html($signature_metadata['type']) . '</Type>';
        $signature_xml .= '<Hash>' . esc_html($signature_metadata['hash']) . '</Hash>';
        $signature_xml .= '<Signature>' . esc_html($signature_metadata['signature']) . '</Signature>';
        $signature_xml .= '<Timestamp>' . esc_html($signature_metadata['timestamp']) . '</Timestamp>';
        $signature_xml .= '<Algorithm>' . esc_html($signature_metadata['algorithm']) . '</Algorithm>';
        
        if (!empty($signature_metadata['certificate'])) {
            $signature_xml .= '<Certificate>' . esc_html($signature_metadata['certificate']) . '</Certificate>';
        }
        
        $signature_xml .= '</Signature>';
        
        return $signature_xml;
    }

    /**
     * Insertar firma en XML
     *
     * @param string $xml_content Contenido XML
     * @param string $signature_element Elemento de firma
     * @return string
     */
    private function insert_signature_in_xml($xml_content, $signature_element) {
        // Buscar el elemento raíz
        $xml = new SimpleXMLElement($xml_content);
        
        // Añadir elemento de firma
        $signature = $xml->addChild('Signature');
        $signature_data = new SimpleXMLElement($signature_element);
        
        // Copiar elementos de la firma
        foreach ($signature_data->children() as $child) {
            $signature->addChild($child->getName(), $child);
        }
        
        return $xml->asXML();
    }

    /**
     * Obtener tipos de firma disponibles
     *
     * @return array
     */
    public function get_available_signature_types() {
        return $this->signature_types;
    }

    /**
     * Configurar firma digital
     *
     * @param array $config Configuración
     * @return bool
     */
    public function configure_signature($config) {
        $settings = WCVS_Core::get_instance()->get_settings();
        $electronic_billing_settings = $settings->get('electronic_billing', array());
        $electronic_billing_settings['digital_signature'] = $config;
        $settings->set('electronic_billing', $electronic_billing_settings);

        WCVS_Logger::info(WCVS_Logger::CONTEXT_ELECTRONIC_BILLING, 'Configuración de firma digital actualizada');

        return true;
    }

    /**
     * Obtener configuración de firma digital
     *
     * @return array
     */
    public function get_signature_config() {
        return $this->settings['digital_signature'] ?? array();
    }

    /**
     * Validar configuración de firma digital
     *
     * @param array $config Configuración
     * @return array
     */
    public function validate_signature_config($config) {
        $errors = array();
        
        if (empty($config['private_key'])) {
            $errors[] = 'La clave privada es obligatoria';
        }
        
        if (empty($config['key_id'])) {
            $errors[] = 'El ID de la clave es obligatorio';
        }
        
        if (isset($config['certificate']) && empty($config['certificate'])) {
            $errors[] = 'El certificado digital es obligatorio para firmas certificadas';
        }
        
        return $errors;
    }
}
