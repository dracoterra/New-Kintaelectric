<?php
/**
 * Sistema de caché avanzado
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Advanced_Cache {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Configuración de caché
     * 
     * @var array
     */
    private $config;
    
    /**
     * Estadísticas de caché
     * 
     * @var array
     */
    private $stats;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        $this->load_config();
        $this->stats = array(
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'deletes' => 0
        );
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Hooks de caché
        add_action('init', array($this, 'setup_cache_groups'));
        add_action('wp_loaded', array($this, 'preload_critical_cache'));
        
        // Hooks de limpieza
        add_action('wp_scheduled_delete', array($this, 'cleanup_expired_cache'));
        add_action('woocommerce_product_set_stock', array($this, 'invalidate_product_cache'));
        add_action('woocommerce_order_status_changed', array($this, 'invalidate_order_cache'));
        
        // Hooks de monitoreo
        add_action('wp_footer', array($this, 'add_cache_monitoring'));
        
        // Hooks de optimización
        add_action('wp_ajax_wvp_clear_cache', array($this, 'clear_cache_ajax'));
        add_action('wp_ajax_wvp_cache_stats', array($this, 'get_cache_stats_ajax'));
    }
    
    /**
     * Cargar configuración de caché
     */
    private function load_config() {
        $this->config = array(
            'enabled' => get_option('wvp_cache_enabled', 'yes'),
            'ttl' => get_option('wvp_cache_ttl', 3600),
            'max_size' => get_option('wvp_cache_max_size', 100 * 1024 * 1024), // 100MB
            'compression' => get_option('wvp_cache_compression', 'yes'),
            'preload' => get_option('wvp_cache_preload', 'yes'),
            'monitoring' => get_option('wvp_cache_monitoring', 'yes'),
            'groups' => array(
                'prices' => 'wvp_prices',
                'rates' => 'wvp_rates',
                'products' => 'wvp_products',
                'orders' => 'wvp_orders',
                'calculations' => 'wvp_calculations',
                'queries' => 'wvp_queries'
            )
        );
    }
    
    /**
     * Configurar grupos de caché
     */
    public function setup_cache_groups() {
        if ($this->config['enabled'] !== 'yes') {
            return;
        }
        
        // Configurar grupos de caché
        foreach ($this->config['groups'] as $group) {
            wp_cache_add_global_groups($group);
        }
    }
    
    /**
     * Precargar caché crítico
     */
    public function preload_critical_cache() {
        if ($this->config['preload'] !== 'yes') {
            return;
        }
        
        // Precargar tasa BCV
        $this->preload_bcv_rate();
        
        // Precargar productos populares
        $this->preload_popular_products();
        
        // Precargar configuraciones
        $this->preload_configurations();
    }
    
    /**
     * Precargar tasa BCV
     */
    private function preload_bcv_rate() {
        $cache_key = 'bcv_rate_current';
        $rate = wp_cache_get($cache_key, $this->config['groups']['rates']);
        
        if ($rate === false) {
            if (class_exists('WVP_BCV_Integrator')) {
                $rate = WVP_BCV_Integrator::get_rate();
                if ($rate) {
                    wp_cache_set($cache_key, $rate, $this->config['groups']['rates'], $this->config['ttl']);
                }
            }
        }
    }
    
    /**
     * Precargar productos populares
     */
    private function preload_popular_products() {
        $cache_key = 'popular_products';
        $products = wp_cache_get($cache_key, $this->config['groups']['products']);
        
        if ($products === false) {
            $products = wc_get_products(array(
                'limit' => 20,
                'orderby' => 'popularity',
                'order' => 'DESC',
                'status' => 'publish'
            ));
            
            if (!empty($products)) {
                wp_cache_set($cache_key, $products, $this->config['groups']['products'], $this->config['ttl']);
            }
        }
    }
    
    /**
     * Precargar configuraciones
     */
    private function preload_configurations() {
        $cache_key = 'plugin_config';
        $config = wp_cache_get($cache_key, $this->config['groups']['queries']);
        
        if ($config === false) {
            $config = array(
                'igtf_enabled' => get_option('wvp_igtf_enabled', 'yes'),
                'igtf_rate' => get_option('wvp_igtf_rate', 3.0),
                'currency_switcher_enabled' => get_option('wvp_currency_switcher_enabled', 'yes'),
                'dual_breakdown_enabled' => get_option('wvp_dual_breakdown_enabled', 'yes')
            );
            
            wp_cache_set($cache_key, $config, $this->config['groups']['queries'], $this->config['ttl']);
        }
    }
    
    /**
     * Obtener valor del caché
     */
    public function get($key, $group = 'default') {
        if ($this->config['enabled'] !== 'yes') {
            return false;
        }
        
        $value = wp_cache_get($key, $this->config['groups'][$group] ?? 'default');
        
        if ($value !== false) {
            $this->stats['hits']++;
        } else {
            $this->stats['misses']++;
        }
        
        return $value;
    }
    
    /**
     * Establecer valor en el caché
     */
    public function set($key, $value, $group = 'default', $ttl = null) {
        if ($this->config['enabled'] !== 'yes') {
            return false;
        }
        
        $ttl = $ttl ?? $this->config['ttl'];
        $group = $this->config['groups'][$group] ?? 'default';
        
        // Comprimir valor si está habilitado
        if ($this->config['compression'] === 'yes' && is_string($value)) {
            $value = gzcompress($value, 6);
        }
        
        $result = wp_cache_set($key, $value, $group, $ttl);
        
        if ($result) {
            $this->stats['sets']++;
        }
        
        return $result;
    }
    
    /**
     * Eliminar valor del caché
     */
    public function delete($key, $group = 'default') {
        if ($this->config['enabled'] !== 'yes') {
            return false;
        }
        
        $group = $this->config['groups'][$group] ?? 'default';
        $result = wp_cache_delete($key, $group);
        
        if ($result) {
            $this->stats['deletes']++;
        }
        
        return $result;
    }
    
    /**
     * Limpiar caché completo
     */
    public function clear($group = null) {
        if ($this->config['enabled'] !== 'yes') {
            return false;
        }
        
        if ($group) {
            $group = $this->config['groups'][$group] ?? 'default';
            return wp_cache_flush_group($group);
        } else {
            return wp_cache_flush();
        }
    }
    
    /**
     * Obtener caché con callback
     */
    public function remember($key, $callback, $group = 'default', $ttl = null) {
        $value = $this->get($key, $group);
        
        if ($value !== false) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $group, $ttl);
        
        return $value;
    }
    
    /**
     * Caché de consultas
     */
    public function cache_query($query, $callback, $group = 'queries', $ttl = null) {
        $cache_key = 'query_' . md5(serialize($query));
        
        return $this->remember($cache_key, $callback, $group, $ttl);
    }
    
    /**
     * Caché de cálculos
     */
    public function cache_calculation($key, $callback, $group = 'calculations', $ttl = null) {
        return $this->remember($key, $callback, $group, $ttl);
    }
    
    /**
     * Caché de precios
     */
    public function cache_price($product_id, $callback, $ttl = null) {
        $cache_key = 'price_' . $product_id;
        
        return $this->remember($cache_key, $callback, 'prices', $ttl);
    }
    
    /**
     * Caché de tasas
     */
    public function cache_rate($key, $callback, $ttl = null) {
        return $this->remember($key, $callback, 'rates', $ttl);
    }
    
    /**
     * Invalidar caché de producto
     */
    public function invalidate_product_cache($product_id) {
        $this->delete('price_' . $product_id, 'prices');
        $this->delete('product_' . $product_id, 'products');
        $this->clear('products');
    }
    
    /**
     * Invalidar caché de pedido
     */
    public function invalidate_order_cache($order_id) {
        $this->delete('order_' . $order_id, 'orders');
        $this->clear('orders');
    }
    
    /**
     * Limpiar caché expirado
     */
    public function cleanup_expired_cache() {
        global $wpdb;
        
        // Limpiar transientes expirados
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wvp_%' AND option_value < " . time());
        
        // Obtener lista de timeouts activos para excluirlos
        $timeout_options = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wvp_%'");
        
        if (!empty($timeout_options)) {
            // Extraer nombres de transients correspondientes (removiendo '_timeout')
            $transient_names = array_map(function($name) {
                return str_replace('_timeout_', '_', $name);
            }, $timeout_options);
            
            // Crear placeholders para prepared statement
            $placeholders = implode(',', array_fill(0, count($transient_names), '%s'));
            
            // Eliminar transients que no tienen timeouts activos
            $query = $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wvp_%' AND option_name NOT IN ($placeholders)",
                $transient_names
            );
            $wpdb->query($query);
        } else {
            // Si no hay timeouts activos, eliminar todos los transients
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wvp_%'");
        }
        
        // Limpiar caché de base de datos
        $this->cleanup_database_cache();
        
        // Limpiar caché de archivos
        $this->cleanup_file_cache();
    }
    
    /**
     * Limpiar caché de base de datos
     */
    private function cleanup_database_cache() {
        global $wpdb;
        
        // Limpiar caché de WooCommerce
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wc_%' AND option_value < " . time());
        
        // Obtener timeouts activos de WooCommerce
        $wc_timeout_options = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wc_%'");
        
        if (!empty($wc_timeout_options)) {
            $wc_transient_names = array_map(function($name) {
                return str_replace('_timeout_', '_', $name);
            }, $wc_timeout_options);
            
            $placeholders = implode(',', array_fill(0, count($wc_transient_names), '%s'));
            $query = $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_%' AND option_name NOT IN ($placeholders)",
                $wc_transient_names
            );
            $wpdb->query($query);
        } else {
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_%'");
        }
        
        // Limpiar caché de consultas
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_query_%' AND option_value < " . time());
        
        // Obtener timeouts activos de consultas
        $query_timeout_options = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_query_%'");
        
        if (!empty($query_timeout_options)) {
            $query_transient_names = array_map(function($name) {
                return str_replace('_timeout_', '_', $name);
            }, $query_timeout_options);
            
            $placeholders = implode(',', array_fill(0, count($query_transient_names), '%s'));
            $query = $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_query_%' AND option_name NOT IN ($placeholders)",
                $query_transient_names
            );
            $wpdb->query($query);
        } else {
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_query_%'");
        }
    }
    
    /**
     * Limpiar caché de archivos
     */
    private function cleanup_file_cache() {
        $cache_dir = WP_CONTENT_DIR . '/cache/wvp/';
        
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '*');
            $now = time();
            
            foreach ($files as $file) {
                if (is_file($file) && ($now - filemtime($file)) > $this->config['ttl']) {
                    unlink($file);
                }
            }
        }
    }
    
    /**
     * Añadir monitoreo de caché
     */
    public function add_cache_monitoring() {
        if ($this->config['monitoring'] !== 'yes') {
            return;
        }
        
        ?>
        <script>
        // Monitoreo de caché
        if ('performance' in window) {
            window.addEventListener('load', function() {
                var cacheStats = {
                    hits: <?php echo $this->stats['hits']; ?>,
                    misses: <?php echo $this->stats['misses']; ?>,
                    sets: <?php echo $this->stats['sets']; ?>,
                    deletes: <?php echo $this->stats['deletes']; ?>
                };
                
                // Calcular hit rate
                var total = cacheStats.hits + cacheStats.misses;
                var hitRate = total > 0 ? (cacheStats.hits / total) * 100 : 0;
                
                // Enviar estadísticas al servidor
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=wvp_log_cache_stats&hits=' + cacheStats.hits + '&misses=' + cacheStats.misses + '&hit_rate=' + hitRate + '&nonce=<?php echo wp_create_nonce('wvp_cache_nonce'); ?>'
                });
            });
        }
        </script>
        <?php
    }
    
    /**
     * Obtener estadísticas de caché
     */
    public function get_stats() {
        return array_merge($this->stats, array(
            'hit_rate' => $this->get_hit_rate(),
            'cache_size' => $this->get_cache_size(),
            'enabled' => $this->config['enabled'] === 'yes'
        ));
    }
    
    /**
     * Obtener tasa de aciertos
     */
    private function get_hit_rate() {
        $total = $this->stats['hits'] + $this->stats['misses'];
        return $total > 0 ? ($this->stats['hits'] / $total) * 100 : 0;
    }
    
    /**
     * Obtener tamaño del caché
     */
    private function get_cache_size() {
        global $wpdb;
        
        $size = $wpdb->get_var("SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE option_name LIKE '_transient_wvp_%'");
        return $size ?: 0;
    }
    
    /**
     * Limpiar caché vía AJAX
     */
    public function clear_cache_ajax() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_cache_nonce')) {
            wp_die('Error de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Limpiar caché
        $result = $this->clear();
        
        // Respuesta
        wp_send_json_success(array(
            'message' => $result ? 'Caché limpiado correctamente' : 'Error al limpiar caché'
        ));
    }
    
    /**
     * Obtener estadísticas de caché vía AJAX
     */
    public function get_cache_stats_ajax() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wvp_cache_nonce')) {
            wp_die('Error de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Sin permisos');
        }
        
        // Obtener estadísticas
        $stats = $this->get_stats();
        
        // Respuesta
        wp_send_json_success($stats);
    }
    
    /**
     * Obtener configuración actual
     */
    public function get_config() {
        return $this->config;
    }
    
    /**
     * Actualizar configuración
     */
    public function update_config($new_config) {
        foreach ($new_config as $key => $value) {
            if (array_key_exists($key, $this->config)) {
                $this->config[$key] = $value;
                update_option('wvp_cache_' . $key, $value);
            }
        }
    }
}
