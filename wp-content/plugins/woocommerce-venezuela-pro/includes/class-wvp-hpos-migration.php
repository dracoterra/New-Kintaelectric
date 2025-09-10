<?php
/**
 * Clase para migración de datos a HPOS
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_HPOS_Migration {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Obtener instancia del plugin de forma segura
        if (class_exists('WooCommerce_Venezuela_Pro')) {
            $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        } else {
            $this->plugin = null;
        }
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Hook para migrar datos cuando se active HPOS
        add_action('woocommerce_hpos_enabled', array($this, 'migrate_data_to_hpos'));
        
        // Hook para verificar migración en admin
        add_action('admin_init', array($this, 'check_migration_status'));
        
        // Hook para mostrar aviso de migración
        add_action('admin_notices', array($this, 'show_migration_notice'));
    }
    
    /**
     * Migrar datos a HPOS
     */
    public function migrate_data_to_hpos() {
        if (!$this->is_migration_needed()) {
            return;
        }
        
        error_log('WVP: Iniciando migración de datos a HPOS');
        
        // Obtener todos los pedidos que necesitan migración
        $orders = $this->get_orders_needing_migration();
        
        $migrated = 0;
        $errors = 0;
        
        foreach ($orders as $order_id) {
            if ($this->migrate_order_data($order_id)) {
                $migrated++;
            } else {
                $errors++;
            }
        }
        
        // Marcar migración como completada
        update_option('wvp_hpos_migration_completed', true);
        update_option('wvp_hpos_migration_date', current_time('mysql'));
        update_option('wvp_hpos_migration_stats', array(
            'migrated' => $migrated,
            'errors' => $errors,
            'total' => count($orders)
        ));
        
        error_log("WVP: Migración completada - Migrados: $migrated, Errores: $errors");
    }
    
    /**
     * Verificar si se necesita migración
     * 
     * @return bool True si se necesita migración
     */
    private function is_migration_needed() {
        // Verificar si HPOS está activo
        if (!WVP_HPOS_Compatibility::is_hpos_enabled()) {
            return false;
        }
        
        // Verificar si ya se completó la migración
        if (get_option('wvp_hpos_migration_completed', false)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtener pedidos que necesitan migración
     * 
     * @return array IDs de pedidos
     */
    private function get_orders_needing_migration() {
        global $wpdb;
        
        // Buscar pedidos que tienen metadatos en post_meta pero no en order_meta
        $query = "
            SELECT DISTINCT p.ID 
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND pm.meta_key IN ('_billing_cedula_rif', '_bcv_rate_at_purchase', '_igtf_amount', '_payment_verified')
            AND p.ID NOT IN (
                SELECT DISTINCT order_id 
                FROM {$wpdb->prefix}wc_orders_meta 
                WHERE meta_key IN ('_billing_cedula_rif', '_bcv_rate_at_purchase', '_igtf_amount', '_payment_verified')
            )
            ORDER BY p.ID DESC
            LIMIT 1000
        ";
        
        return $wpdb->get_col($query);
    }
    
    /**
     * Migrar datos de un pedido específico
     * 
     * @param int $order_id ID del pedido
     * @return bool True si la migración fue exitosa
     */
    private function migrate_order_data($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return false;
        }
        
        try {
            // Obtener metadatos de post_meta
            $cedula_rif = get_post_meta($order_id, '_billing_cedula_rif', true);
            $bcv_rate = get_post_meta($order_id, '_bcv_rate_at_purchase', true);
            $igtf_amount = get_post_meta($order_id, '_igtf_amount', true);
            $payment_verified = get_post_meta($order_id, '_payment_verified', true);
            $payment_reference = get_post_meta($order_id, '_payment_reference', true);
            $payment_type = get_post_meta($order_id, '_payment_type', true);
            $seniat_control_number = get_post_meta($order_id, '_seniat_control_number', true);
            
            // Migrar a order_meta si no existen
            if ($cedula_rif && !$order->get_meta('_billing_cedula_rif')) {
                $order->update_meta_data('_billing_cedula_rif', $cedula_rif);
            }
            
            if ($bcv_rate && !$order->get_meta('_bcv_rate_at_purchase')) {
                $order->update_meta_data('_bcv_rate_at_purchase', floatval($bcv_rate));
            }
            
            if ($igtf_amount && !$order->get_meta('_igtf_amount')) {
                $order->update_meta_data('_igtf_amount', floatval($igtf_amount));
            }
            
            if ($payment_verified && !$order->get_meta('_payment_verified')) {
                $order->update_meta_data('_payment_verified', $payment_verified);
            }
            
            if ($payment_reference && !$order->get_meta('_payment_reference')) {
                $order->update_meta_data('_payment_reference', $payment_reference);
            }
            
            if ($payment_type && !$order->get_meta('_payment_type')) {
                $order->update_meta_data('_payment_type', $payment_type);
            }
            
            if ($seniat_control_number && !$order->get_meta('_seniat_control_number')) {
                $order->update_meta_data('_seniat_control_number', $seniat_control_number);
            }
            
            // Guardar cambios
            $order->save();
            
            return true;
            
        } catch (Exception $e) {
            error_log("WVP: Error migrando pedido $order_id: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar estado de migración
     */
    public function check_migration_status() {
        if (!$this->is_migration_needed()) {
            return;
        }
        
        // Ejecutar migración en background
        wp_schedule_single_event(time(), 'wvp_migrate_to_hpos');
    }
    
    /**
     * Mostrar aviso de migración
     */
    public function show_migration_notice() {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        
        $migration_completed = get_option('wvp_hpos_migration_completed', false);
        $hpos_enabled = WVP_HPOS_Compatibility::is_hpos_enabled();
        
        if (!$hpos_enabled) {
            return;
        }
        
        if (!$migration_completed) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php _e('WooCommerce Venezuela Pro:', 'wvp'); ?></strong>
                    <?php _e('Se está ejecutando la migración de datos a HPOS. Esto puede tomar unos minutos.', 'wvp'); ?>
                </p>
            </div>
            <?php
        } else {
            $stats = get_option('wvp_hpos_migration_stats', array());
            if (!empty($stats)) {
                ?>
                <div class="notice notice-success">
                    <p>
                        <strong><?php _e('WooCommerce Venezuela Pro:', 'wvp'); ?></strong>
                        <?php printf(
                            __('Migración a HPOS completada exitosamente. %d pedidos migrados, %d errores.', 'wvp'),
                            $stats['migrated'],
                            $stats['errors']
                        ); ?>
                    </p>
                </div>
                <?php
            }
        }
    }
    
    /**
     * Ejecutar migración programada
     */
    public function run_scheduled_migration() {
        $this->migrate_data_to_hpos();
    }
}
