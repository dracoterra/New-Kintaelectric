<?php
/**
 * Cache Manager
 * Intelligent caching system for WooCommerce Venezuela Pro 2025
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Cache_Manager {
    
    private static $instance = null;
    private $cache_prefix = 'wvp_';
    private $cache_groups = array();
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_cache_groups();
        $this->init_hooks();
    }
    
    /**
     * Initialize cache groups
     */
    private function init_cache_groups() {
        $this->cache_groups = array(
            'bcv_rates' => array(
                'expiration' => 30 * MINUTE_IN_SECONDS, // 30 minutes
                'description' => 'BCV exchange rates'
            ),
            'product_prices' => array(
                'expiration' => 15 * MINUTE_IN_SECONDS, // 15 minutes
                'description' => 'Converted product prices'
            ),
            'shipping_costs' => array(
                'expiration' => HOUR_IN_SECONDS, // 1 hour
                'description' => 'Calculated shipping costs'
            ),
            'tax_rates' => array(
                'expiration' => DAY_IN_SECONDS, // 1 day
                'description' => 'Tax rate calculations'
            ),
            'seniat_reports' => array(
                'expiration' => 6 * HOUR_IN_SECONDS, // 6 hours
                'description' => 'SENIAT report data'
            ),
            'admin_dashboard' => array(
                'expiration' => 5 * MINUTE_IN_SECONDS, // 5 minutes
                'description' => 'Admin dashboard data'
            )
        );
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'wp_ajax_wvp_clear_cache', array( $this, 'ajax_clear_cache' ) );
        add_action( 'wp_ajax_wvp_clear_cache_group', array( $this, 'ajax_clear_cache_group' ) );
        add_action( 'admin_menu', array( $this, 'add_cache_admin_menu' ), 30 );
        
        // Auto-clear cache on specific events
        add_action( 'woocommerce_product_object_updated_props', array( $this, 'clear_product_cache' ), 10, 2 );
        add_action( 'woocommerce_order_status_changed', array( $this, 'clear_order_cache' ), 10, 3 );
        add_action( 'update_option_wvp_bcv_rate', array( $this, 'clear_bcv_cache' ) );
    }
    
    /**
     * Get cached data
     */
    public function get( $key, $group = 'default' ) {
        $cache_key = $this->get_cache_key( $key, $group );
        return get_transient( $cache_key );
    }
    
    /**
     * Set cached data
     */
    public function set( $key, $data, $group = 'default', $expiration = null ) {
        $cache_key = $this->get_cache_key( $key, $group );
        
        if ( $expiration === null ) {
            $expiration = $this->get_group_expiration( $group );
        }
        
        return set_transient( $cache_key, $data, $expiration );
    }
    
    /**
     * Delete cached data
     */
    public function delete( $key, $group = 'default' ) {
        $cache_key = $this->get_cache_key( $key, $group );
        return delete_transient( $cache_key );
    }
    
    /**
     * Clear all cache for a group
     */
    public function clear_group( $group ) {
        global $wpdb;
        
        $pattern = $this->cache_prefix . $group . '_%';
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . $pattern
        ) );
        
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_' . $pattern
        ) );
        
        do_action( 'wvp_cache_group_cleared', $group );
    }
    
    /**
     * Clear all cache
     */
    public function clear_all() {
        global $wpdb;
        
        $pattern = $this->cache_prefix . '%';
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . $pattern
        ) );
        
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_' . $pattern
        ) );
        
        do_action( 'wvp_cache_cleared' );
    }
    
    /**
     * Get cache statistics
     */
    public function get_stats() {
        global $wpdb;
        
        $stats = array();
        
        foreach ( $this->cache_groups as $group => $config ) {
            $pattern = $this->cache_prefix . $group . '_%';
            
            $count = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . $pattern
            ) );
            
            $size = $wpdb->get_var( $wpdb->prepare(
                "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . $pattern
            ) );
            
            $stats[ $group ] = array(
                'count' => intval( $count ),
                'size' => intval( $size ),
                'size_formatted' => size_format( $size ),
                'description' => $config['description'],
                'expiration' => $config['expiration']
            );
        }
        
        return $stats;
    }
    
    /**
     * Get cache key
     */
    private function get_cache_key( $key, $group ) {
        return $this->cache_prefix . $group . '_' . md5( $key );
    }
    
    /**
     * Get group expiration
     */
    private function get_group_expiration( $group ) {
        if ( isset( $this->cache_groups[ $group ] ) ) {
            return $this->cache_groups[ $group ]['expiration'];
        }
        return HOUR_IN_SECONDS; // Default 1 hour
    }
    
    /**
     * Clear product cache
     */
    public function clear_product_cache( $product, $updated_props ) {
        if ( in_array( 'price', $updated_props ) ) {
            $this->clear_group( 'product_prices' );
        }
    }
    
    /**
     * Clear order cache
     */
    public function clear_order_cache( $order_id, $old_status, $new_status ) {
        $this->clear_group( 'seniat_reports' );
        $this->clear_group( 'admin_dashboard' );
    }
    
    /**
     * Clear BCV cache
     */
    public function clear_bcv_cache() {
        $this->clear_group( 'bcv_rates' );
        $this->clear_group( 'product_prices' );
    }
    
    /**
     * Add cache admin menu
     */
    public function add_cache_admin_menu() {
        add_submenu_page(
            'wvp-dashboard',
            'Gestión de Cache',
            'Cache',
            'manage_options',
            'wvp-cache',
            array( $this, 'cache_admin_page' )
        );
    }
    
    /**
     * Cache admin page
     */
    public function cache_admin_page() {
        $stats = $this->get_stats();
        ?>
        <div class="wrap">
            <h1>🗄️ Gestión de Cache - WooCommerce Venezuela Pro 2025</h1>
            
            <div class="wvp-cache-stats">
                <h2>Estadísticas de Cache</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Descripción</th>
                            <th>Elementos</th>
                            <th>Tamaño</th>
                            <th>Expiración</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $stats as $group => $data ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $group ); ?></strong></td>
                            <td><?php echo esc_html( $data['description'] ); ?></td>
                            <td><?php echo esc_html( $data['count'] ); ?></td>
                            <td><?php echo esc_html( $data['size_formatted'] ); ?></td>
                            <td><?php echo esc_html( human_time_diff( 0, $data['expiration'] ) ); ?></td>
                            <td>
                                <button class="button button-secondary wvp-clear-group" data-group="<?php echo esc_attr( $group ); ?>">
                                    Limpiar Grupo
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="wvp-cache-actions">
                <h2>Acciones de Cache</h2>
                <p>
                    <button class="button button-primary" id="wvp-clear-all-cache">
                        🗑️ Limpiar Todo el Cache
                    </button>
                    <button class="button button-secondary" id="wvp-refresh-stats">
                        🔄 Actualizar Estadísticas
                    </button>
                </p>
            </div>
            
            <div class="wvp-cache-info">
                <h2>Información del Sistema de Cache</h2>
                <p>El sistema de cache inteligente mejora el rendimiento del plugin almacenando datos frecuentemente utilizados.</p>
                <ul>
                    <li><strong>Cache Automático:</strong> Se limpia automáticamente cuando los datos cambian</li>
                    <li><strong>Expiración Inteligente:</strong> Diferentes tiempos de expiración según el tipo de dato</li>
                    <li><strong>Optimización:</strong> Reduce consultas a la base de datos y APIs externas</li>
                </ul>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#wvp-clear-all-cache').on('click', function() {
                if (confirm('¿Estás seguro de que quieres limpiar todo el cache?')) {
                    $.post(ajaxurl, {
                        action: 'wvp_clear_cache',
                        nonce: '<?php echo wp_create_nonce( 'wvp_cache_nonce' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('Cache limpiado exitosamente');
                            location.reload();
                        } else {
                            alert('Error al limpiar el cache');
                        }
                    });
                }
            });
            
            $('.wvp-clear-group').on('click', function() {
                var group = $(this).data('group');
                if (confirm('¿Limpiar cache del grupo "' + group + '"?')) {
                    $.post(ajaxurl, {
                        action: 'wvp_clear_cache_group',
                        group: group,
                        nonce: '<?php echo wp_create_nonce( 'wvp_cache_nonce' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('Cache del grupo limpiado exitosamente');
                            location.reload();
                        } else {
                            alert('Error al limpiar el cache del grupo');
                        }
                    });
                }
            });
            
            $('#wvp-refresh-stats').on('click', function() {
                location.reload();
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX clear cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer( 'wvp_cache_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $this->clear_all();
        wp_send_json_success( 'Cache cleared successfully' );
    }
    
    /**
     * AJAX clear cache group
     */
    public function ajax_clear_cache_group() {
        check_ajax_referer( 'wvp_cache_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $group = sanitize_text_field( $_POST['group'] );
        $this->clear_group( $group );
        wp_send_json_success( 'Cache group cleared successfully' );
    }
}
