<?php
/**
 * Integración de Pago Móvil para WooCommerce Blocks
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.3.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

use Automattic\WooCommerce\Blocks\Assets\Api;
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WVP_Blocks_Integration_Pago_Movil extends AbstractPaymentMethodType {
    
    protected $name = 'wvp_pago_movil';
    
    private $asset_api;
    
    public function __construct(Api $asset_api) {
        $this->asset_api = $asset_api;
    }
    
    public function initialize() {
        $this->settings = get_option('woocommerce_wvp_pago_movil_settings', array());
    }
    
    public function is_active() {
        return isset($this->settings['enabled']) && $this->settings['enabled'] === 'yes';
    }
    
    public function get_payment_method_script_handles() {
        // Registrar script para el gateway de Pago Móvil
        $script_handle = 'wc-payment-method-pago-movil';
        
        // Ruta relativa desde el plugin de WooCommerce
        $script_path = WVP_PLUGIN_PATH . 'assets/client/blocks/wc-payment-method-pago-movil.js';
        
        if (file_exists($script_path)) {
            // Registramos el script usando wp_register_script tradicional
            wp_register_script(
                $script_handle,
                WVP_PLUGIN_URL . 'assets/client/blocks/wc-payment-method-pago-movil.js',
                array('wc-blocks-registry', 'wc-settings'),
                WVP_VERSION,
                true
            );
        }
        
        return array($script_handle);
    }
    
    public function get_payment_method_data() {
        // Obtener las cuentas de Pago Móvil
        $accounts_json = $this->get_setting('pago_movil_accounts', '[]');
        $accounts = json_decode($accounts_json, true);
        if (!is_array($accounts)) {
            $accounts = array();
        }
        
        $data = array(
            'title'       => $this->get_setting('title', 'Pago Móvil'),
            'description' => $this->get_setting('description', ''),
            'accounts'    => $accounts,
            'supports'    => array('products')
        );
        
        return $data;
    }
}

