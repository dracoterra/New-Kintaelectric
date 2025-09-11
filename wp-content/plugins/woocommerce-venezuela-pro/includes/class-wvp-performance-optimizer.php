<?php
/**
 * Optimizador de rendimiento
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Performance_Optimizer {
    
    /**
     * Instancia del plugin principal
     * 
     * @var WooCommerce_Venezuela_Pro
     */
    private $plugin;
    
    /**
     * Configuración de optimización
     * 
     * @var array
     */
    private $config;
    
    /**
     * Métricas de rendimiento
     * 
     * @var array
     */
    private $metrics;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        $this->load_config();
        $this->metrics = array();
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Optimizaciones de carga
        add_action('wp_enqueue_scripts', array($this, 'optimize_scripts_loading'), 1);
        add_action('wp_head', array($this, 'add_performance_meta'), 1);
        
        // Optimizaciones de base de datos
        add_action('init', array($this, 'optimize_database_queries'));
        add_action('wp_footer', array($this, 'defer_non_critical_scripts'));
        
        // Optimizaciones de caché
        add_action('wp_loaded', array($this, 'setup_caching'));
        add_action('wp_footer', array($this, 'preload_critical_resources'));
        
        // Optimizaciones de imágenes
        add_filter('wp_get_attachment_image_attributes', array($this, 'optimize_image_attributes'), 10, 3);
        
        // Optimizaciones de WooCommerce
        add_action('woocommerce_init', array($this, 'optimize_woocommerce'));
        
        // Limpieza automática
        add_action('wp_scheduled_delete', array($this, 'cleanup_old_data'));
        
        // Monitoreo de rendimiento
        add_action('wp_footer', array($this, 'add_performance_monitoring'));
    }
    
    /**
     * Cargar configuración de optimización
     */
    private function load_config() {
        $this->config = array(
            'enable_caching' => get_option('wvp_enable_caching', 'yes'),
            'cache_ttl' => get_option('wvp_cache_ttl', 3600),
            'lazy_loading' => get_option('wvp_lazy_loading', 'yes'),
            'minify_assets' => get_option('wvp_minify_assets', 'no'),
            'defer_scripts' => get_option('wvp_defer_scripts', 'yes'),
            'preload_critical' => get_option('wvp_preload_critical', 'yes'),
            'optimize_images' => get_option('wvp_optimize_images', 'yes'),
            'database_optimization' => get_option('wvp_database_optimization', 'yes'),
            'performance_monitoring' => get_option('wvp_performance_monitoring', 'yes')
        );
    }
    
    /**
     * Optimizar carga de scripts
     */
    public function optimize_scripts_loading() {
        // Solo cargar scripts necesarios
        if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) {
            return;
        }
        
        // Cargar scripts de forma asíncrona
        if ($this->config['defer_scripts'] === 'yes') {
            add_filter('script_loader_tag', array($this, 'defer_scripts'), 10, 2);
        }
        
        // Minificar assets si está habilitado
        if ($this->config['minify_assets'] === 'yes') {
            add_filter('script_loader_src', array($this, 'minify_script_src'), 10, 2);
            add_filter('style_loader_src', array($this, 'minify_style_src'), 10, 2);
        }
    }
    
    /**
     * Añadir meta tags de rendimiento
     */
    public function add_performance_meta() {
        if ($this->config['performance_monitoring'] !== 'yes') {
            return;
        }
        
        echo '<!-- WVP Performance Monitoring -->' . "\n";
        echo '<meta name="wvp-version" content="' . WVP_VERSION . '">' . "\n";
        echo '<meta name="wvp-cache-enabled" content="' . ($this->config['enable_caching'] === 'yes' ? 'true' : 'false') . '">' . "\n";
        echo '<meta name="wvp-optimization-level" content="high">' . "\n";
    }
    
    /**
     * Optimizar consultas de base de datos
     */
    public function optimize_database_queries() {
        if ($this->config['database_optimization'] !== 'yes') {
            return;
        }
        
        // Optimizar consultas de WooCommerce
        add_filter('woocommerce_product_query', array($this, 'optimize_product_queries'));
        add_filter('woocommerce_order_query', array($this, 'optimize_order_queries'));
        
        // Optimizar consultas de BCV
        add_filter('wvp_bcv_query', array($this, 'optimize_bcv_queries'));
        
        // Limitar consultas de metadatos
        add_filter('woocommerce_product_data_store_cpt_get_products_query', array($this, 'limit_meta_queries'));
    }
    
    /**
     * Configurar sistema de caché
     */
    public function setup_caching() {
        if ($this->config['enable_caching'] !== 'yes') {
            return;
        }
        
        // Configurar caché de objetos
        if (!wp_using_ext_object_cache()) {
            wp_cache_init();
        }
        
        // Configurar caché de transientes
        add_filter('transient_timeout_wvp_', array($this, 'extend_transient_timeout'));
        
        // Configurar caché de consultas
        add_action('wp_loaded', array($this, 'setup_query_cache'));
    }
    
    /**
     * Diferir scripts no críticos
     */
    public function defer_non_critical_scripts() {
        if ($this->config['defer_scripts'] !== 'yes') {
            return;
        }
        
        ?>
        <script>
        // Diferir scripts no críticos
        document.addEventListener('DOMContentLoaded', function() {
            var scripts = document.querySelectorAll('script[data-defer]');
            scripts.forEach(function(script) {
                var newScript = document.createElement('script');
                newScript.src = script.src;
                newScript.async = true;
                script.parentNode.replaceChild(newScript, script);
            });
        });
        </script>
        <?php
    }
    
    /**
     * Precargar recursos críticos
     */
    public function preload_critical_resources() {
        if ($this->config['preload_critical'] !== 'yes') {
            return;
        }
        
        // Precargar CSS crítico
        $critical_css = array(
            'wvp-currency-switcher',
            'wvp-dual-breakdown',
            'wvp-hybrid-invoicing'
        );
        
        foreach ($critical_css as $handle) {
            if (wp_style_is($handle, 'enqueued')) {
                $src = wp_scripts()->registered[$handle]->src ?? '';
                if ($src) {
                    echo '<link rel="preload" href="' . esc_url($src) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
                }
            }
        }
        
        // Precargar JavaScript crítico
        $critical_js = array(
            'wvp-currency-switcher'
        );
        
        foreach ($critical_js as $handle) {
            if (wp_script_is($handle, 'enqueued')) {
                $src = wp_scripts()->registered[$handle]->src ?? '';
                if ($src) {
                    echo '<link rel="preload" href="' . esc_url($src) . '" as="script">' . "\n";
                }
            }
        }
    }
    
    /**
     * Optimizar atributos de imágenes
     */
    public function optimize_image_attributes($attr, $attachment, $size) {
        if ($this->config['optimize_images'] !== 'yes') {
            return $attr;
        }
        
        // Añadir loading="lazy" para imágenes no críticas
        if (!is_singular() || !is_main_query()) {
            $attr['loading'] = 'lazy';
        }
        
        // Añadir atributos de rendimiento
        $attr['decoding'] = 'async';
        $attr['fetchpriority'] = 'low';
        
        return $attr;
    }
    
    /**
     * Optimizar WooCommerce
     */
    public function optimize_woocommerce() {
        // Deshabilitar funcionalidades no necesarias
        if (!is_admin()) {
            // Deshabilitar scripts de WooCommerce no necesarios
            add_action('wp_enqueue_scripts', array($this, 'disable_unnecessary_woocommerce_scripts'), 99);
            
            // Optimizar consultas de productos
            add_filter('woocommerce_product_query', array($this, 'optimize_product_queries'));
            
            // Optimizar consultas de pedidos
            add_filter('woocommerce_order_query', array($this, 'optimize_order_queries'));
        }
    }
    
    /**
     * Deshabilitar scripts innecesarios de WooCommerce
     */
    public function disable_unnecessary_woocommerce_scripts() {
        // Solo en páginas específicas
        if (!is_woocommerce() && !is_cart() && !is_checkout()) {
            wp_dequeue_script('wc-cart-fragments');
            wp_dequeue_script('wc-add-to-cart');
            wp_dequeue_script('wc-single-product');
        }
    }
    
    /**
     * Optimizar consultas de productos
     */
    public function optimize_product_queries($query) {
        // Limitar campos seleccionados
        $query->set('fields', 'ids');
        
        // Añadir caché a la consulta
        $query->set('cache_results', true);
        
        return $query;
    }
    
    /**
     * Optimizar consultas de pedidos
     */
    public function optimize_order_queries($query) {
        // Desactivado temporalmente por incompatibilidad con WooCommerce 10.1.2
        return $query;
        
        // Verificar que el objeto tenga el método set
        if (!is_object($query) || !method_exists($query, 'set')) {
            return $query;
        }
        
        // Limitar campos seleccionados
        $query->set('fields', 'ids');
        
        // Añadir caché a la consulta
        $query->set('cache_results', true);
        
        return $query;
    }
    
    /**
     * Optimizar consultas de BCV
     */
    public function optimize_bcv_queries($query) {
        // Añadir caché a consultas de BCV
        $query['cache_results'] = true;
        $query['cache_duration'] = $this->config['cache_ttl'];
        
        return $query;
    }
    
    /**
     * Limitar consultas de metadatos
     */
    public function limit_meta_queries($query) {
        // Limitar metadatos a los necesarios
        $query['meta_query'] = array(
            'relation' => 'AND',
            array(
                'key' => '_price',
                'compare' => 'EXISTS'
            )
        );
        
        return $query;
    }
    
    /**
     * Diferir scripts
     */
    public function defer_scripts($tag, $handle) {
        // Scripts a diferir
        $defer_scripts = array(
            'wvp-currency-switcher',
            'wvp-dual-breakdown',
            'wvp-hybrid-invoicing'
        );
        
        if (in_array($handle, $defer_scripts)) {
            return str_replace('<script ', '<script data-defer ', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Minificar fuente de script
     */
    public function minify_script_src($src, $handle) {
        // Solo para scripts del plugin
        if (strpos($handle, 'wvp-') === 0) {
            $src = str_replace('.js', '.min.js', $src);
        }
        
        return $src;
    }
    
    /**
     * Minificar fuente de estilo
     */
    public function minify_style_src($src, $handle) {
        // Solo para estilos del plugin
        if (strpos($handle, 'wvp-') === 0) {
            $src = str_replace('.css', '.min.css', $src);
        }
        
        return $src;
    }
    
    /**
     * Extender timeout de transientes
     */
    public function extend_transient_timeout($timeout) {
        return $this->config['cache_ttl'];
    }
    
    /**
     * Configurar caché de consultas
     */
    public function setup_query_cache() {
        // Configurar caché para consultas frecuentes
        add_action('pre_get_posts', array($this, 'cache_product_queries'));
        add_action('woocommerce_product_query', array($this, 'cache_woocommerce_queries'));
    }
    
    /**
     * Caché de consultas de productos
     */
    public function cache_product_queries($query) {
        if (!is_admin() && $query->is_main_query()) {
            $query->set('cache_results', true);
            $query->set('update_post_meta_cache', false);
            $query->set('update_post_term_cache', false);
        }
    }
    
    /**
     * Caché de consultas de WooCommerce
     */
    public function cache_woocommerce_queries($query) {
        $query->set('cache_results', true);
        $query->set('update_post_meta_cache', false);
        $query->set('update_post_term_cache', false);
    }
    
    /**
     * Limpiar datos antiguos
     */
    public function cleanup_old_data() {
        // Limpiar transientes expirados
        global $wpdb;
        
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wvp_%' AND option_value < " . time());
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wvp_%' AND option_name NOT IN (SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wvp_%')");
        
        // Limpiar logs antiguos
        $this->cleanup_old_logs();
        
        // Limpiar caché de base de datos
        $this->cleanup_database_cache();
    }
    
    /**
     * Limpiar logs antiguos
     */
    private function cleanup_old_logs() {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        
        if (file_exists($log_file) && filesize($log_file) > 10 * 1024 * 1024) { // 10MB
            // Rotar log
            $backup_file = WP_CONTENT_DIR . '/debug-' . date('Y-m-d-H-i-s') . '.log';
            rename($log_file, $backup_file);
            
            // Crear nuevo log
            touch($log_file);
            chmod($log_file, 0644);
        }
    }
    
    /**
     * Limpiar caché de base de datos
     */
    private function cleanup_database_cache() {
        global $wpdb;
        
        // Limpiar caché de consultas
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wc_%' AND option_value < " . time());
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_%' AND option_name NOT IN (SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wc_%')");
    }
    
    /**
     * Añadir monitoreo de rendimiento
     */
    public function add_performance_monitoring() {
        if ($this->config['performance_monitoring'] !== 'yes') {
            return;
        }
        
        ?>
        <script>
        // Monitoreo de rendimiento
        if ('performance' in window) {
            window.addEventListener('load', function() {
                var perfData = performance.getEntriesByType('navigation')[0];
                var loadTime = perfData.loadEventEnd - perfData.loadEventStart;
                var domContentLoaded = perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart;
                
                // Enviar métricas al servidor
                if (loadTime > 0) {
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=wvp_log_performance&load_time=' + loadTime + '&dom_ready=' + domContentLoaded + '&nonce=<?php echo wp_create_nonce('wvp_performance_nonce'); ?>'
                    });
                }
            });
        }
        </script>
        <?php
    }
    
    /**
     * Obtener métricas de rendimiento
     */
    public function get_performance_metrics() {
        return array(
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'database_queries' => get_num_queries(),
            'cache_hits' => wp_cache_get('wvp_cache_hits', 'wvp_performance') ?: 0,
            'cache_misses' => wp_cache_get('wvp_cache_misses', 'wvp_performance') ?: 0
        );
    }
    
    /**
     * Optimizar consulta específica
     */
    public function optimize_query($query, $args = array()) {
        $defaults = array(
            'cache' => true,
            'cache_duration' => $this->config['cache_ttl'],
            'limit' => 100,
            'fields' => 'ids'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        if ($args['cache']) {
            $cache_key = 'wvp_query_' . md5(serialize($query));
            $cached_result = wp_cache_get($cache_key, 'wvp_queries');
            
            if ($cached_result !== false) {
                wp_cache_set('wvp_cache_hits', wp_cache_get('wvp_cache_hits', 'wvp_performance') + 1, 'wvp_performance');
                return $cached_result;
            }
        }
        
        // Ejecutar consulta
        $result = $query;
        
        if ($args['cache']) {
            wp_cache_set($cache_key, $result, 'wvp_queries', $args['cache_duration']);
            wp_cache_set('wvp_cache_misses', wp_cache_get('wvp_cache_misses', 'wvp_performance') + 1, 'wvp_performance');
        }
        
        return $result;
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
                update_option('wvp_' . $key, $value);
            }
        }
    }
}
