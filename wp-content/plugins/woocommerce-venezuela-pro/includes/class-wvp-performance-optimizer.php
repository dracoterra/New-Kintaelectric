<?php
/**
 * Optimizador de Rendimiento - WooCommerce Venezuela Pro
 * Optimiza el rendimiento del sistema de CSS dinámico
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
     * Instancia única
     */
    private static $instance = null;
    
    /**
     * Configuraciones de optimización
     */
    private $optimization_settings = null;
    
    /**
     * Constructor privado
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Obtener instancia única
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializar
     */
    public function init() {
        // Cargar configuraciones de optimización
        $this->load_optimization_settings();
        
        // Aplicar optimizaciones
        $this->apply_optimizations();
        
        // Hook para limpiar caché cuando sea necesario
        add_action('wvp_clear_all_cache', array($this, 'clear_all_cache'));
        
        // Hook para optimizar CSS
        add_action('wvp_optimize_css', array($this, 'optimize_css'));
    }
    
    /**
     * Cargar configuraciones de optimización
     */
    private function load_optimization_settings() {
        $this->optimization_settings = array(
            'css_cache_duration' => get_option('wvp_css_cache_duration', HOUR_IN_SECONDS),
            'enable_css_minification' => get_option('wvp_enable_css_minification', true),
            'enable_css_compression' => get_option('wvp_enable_css_compression', true),
            'enable_lazy_loading' => get_option('wvp_enable_lazy_loading', false),
            'enable_critical_css' => get_option('wvp_enable_critical_css', false),
            'max_css_size' => get_option('wvp_max_css_size', 50000), // 50KB
            'enable_css_preload' => get_option('wvp_enable_css_preload', true)
        );
    }
    
    /**
     * Aplicar optimizaciones
     */
    private function apply_optimizations() {
        // Optimizar CSS si está habilitado
        if ($this->optimization_settings['enable_css_minification']) {
            add_filter('wvp_generated_css', array($this, 'minify_css'));
        }
        
        // Comprimir CSS si está habilitado
        if ($this->optimization_settings['enable_css_compression']) {
            add_filter('wvp_generated_css', array($this, 'compress_css'));
        }
        
        // Preload CSS si está habilitado
        if ($this->optimization_settings['enable_css_preload']) {
            add_action('wp_head', array($this, 'add_css_preload'), 1);
        }
        
        // CSS crítico si está habilitado
        if ($this->optimization_settings['enable_critical_css']) {
            add_action('wp_head', array($this, 'add_critical_css'), 2);
        }
    }
    
    /**
     * Minificar CSS
     */
    public function minify_css($css) {
        // Remover comentarios
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remover espacios en blanco innecesarios
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remover espacios alrededor de caracteres especiales
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
        
        // Remover punto y coma innecesario antes de }
        $css = preg_replace('/;}/', '}', $css);
        
        // Remover espacios al inicio y final
        $css = trim($css);
        
        return $css;
    }
    
    /**
     * Comprimir CSS
     */
    public function compress_css($css) {
        // Aplicar minificación primero
        $css = $this->minify_css($css);
        
        // Comprimir variables CSS repetidas
        $css = $this->compress_css_variables($css);
        
        // Optimizar selectores
        $css = $this->optimize_selectors($css);
        
        return $css;
    }
    
    /**
     * Comprimir variables CSS
     */
    private function compress_css_variables($css) {
        // Buscar variables CSS repetidas
        preg_match_all('/--wvp-[^:]+:\s*([^;]+);/', $css, $matches);
        
        if (!empty($matches[1])) {
            $values = array_count_values($matches[1]);
            
            // Si un valor se repite más de 3 veces, crear una variable común
            foreach ($values as $value => $count) {
                if ($count > 3) {
                    $common_var = '--wvp-common-' . md5($value);
                    $css = str_replace($value, 'var(' . $common_var . ')', $css);
                    $css = ':root { ' . $common_var . ': ' . $value . '; }' . $css;
                }
            }
        }
        
        return $css;
    }
    
    /**
     * Optimizar selectores
     */
    private function optimize_selectors($css) {
        // Agrupar selectores similares
        $css = preg_replace('/([^{}]+)\{\s*([^{}]+)\s*\}/', '$1{$2}', $css);
        
        // Remover selectores vacíos
        $css = preg_replace('/[^{}]*\{\s*\}/', '', $css);
        
        return $css;
    }
    
    /**
     * Agregar preload de CSS
     */
    public function add_css_preload() {
        if (class_exists('WVP_Dynamic_CSS_Generator')) {
            $generator = WVP_Dynamic_CSS_Generator::get_instance();
            $css = $generator->get_generated_css();
            
            if ($css && strlen($css) > 1000) { // Solo para CSS grande
                echo '<link rel="preload" href="data:text/css;base64,' . base64_encode($css) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
            }
        }
    }
    
    /**
     * Agregar CSS crítico
     */
    public function add_critical_css() {
        if (class_exists('WVP_Dynamic_CSS_Generator')) {
            $generator = WVP_Dynamic_CSS_Generator::get_instance();
            $css = $generator->get_generated_css();
            
            if ($css) {
                // Extraer solo CSS crítico (variables y estilos base)
                $critical_css = $this->extract_critical_css($css);
                
                if ($critical_css) {
                    echo '<style id="wvp-critical-css">' . $critical_css . '</style>';
                }
            }
        }
    }
    
    /**
     * Extraer CSS crítico
     */
    private function extract_critical_css($css) {
        $critical_css = '';
        
        // Variables CSS (siempre críticas)
        if (preg_match('/:root\s*\{[^}]+\}/', $css, $matches)) {
            $critical_css .= $matches[0];
        }
        
        // Estilos base (siempre críticos)
        if (preg_match('/\.wvp-product-price-container[^{]*\{[^}]+\}/', $css, $matches)) {
            $critical_css .= $matches[0];
        }
        
        // Estilos de precios (críticos)
        if (preg_match('/\.wvp-price-[^{]*\{[^}]+\}/', $css, $matches)) {
            $critical_css .= $matches[0];
        }
        
        return $critical_css;
    }
    
    /**
     * Limpiar todo el caché
     */
    public function clear_all_cache() {
        // Limpiar caché de CSS dinámico
        if (class_exists('WVP_Dynamic_CSS_Generator')) {
            $generator = WVP_Dynamic_CSS_Generator::get_instance();
            $generator->clear_css_cache();
        }
        
        // Limpiar caché de WordPress
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Limpiar caché de transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wvp_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wvp_%'");
        
        // Limpiar caché de archivos
        $this->clear_file_cache();
    }
    
    /**
     * Limpiar caché de archivos
     */
    private function clear_file_cache() {
        $cache_dir = WP_CONTENT_DIR . '/cache/wvp/';
        
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
    
    /**
     * Optimizar CSS
     */
    public function optimize_css() {
        if (class_exists('WVP_Dynamic_CSS_Generator')) {
            $generator = WVP_Dynamic_CSS_Generator::get_instance();
            
            // Forzar regeneración
            $generator->force_regenerate();
            
            // Aplicar optimizaciones
            $css = $generator->get_generated_css();
            $optimized_css = $this->compress_css($css);
            
            // Guardar CSS optimizado
            $this->save_optimized_css($optimized_css);
        }
    }
    
    /**
     * Guardar CSS optimizado
     */
    private function save_optimized_css($css) {
        $cache_dir = WP_CONTENT_DIR . '/cache/wvp/';
        
        if (!is_dir($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
        
        $cache_file = $cache_dir . 'optimized-css.css';
        file_put_contents($cache_file, $css);
        
        // Guardar hash para verificar cambios
        $hash_file = $cache_dir . 'css-hash.txt';
        file_put_contents($hash_file, md5($css));
    }
    
    /**
     * Obtener CSS optimizado
     */
    public function get_optimized_css() {
        $cache_dir = WP_CONTENT_DIR . '/cache/wvp/';
        $cache_file = $cache_dir . 'optimized-css.css';
        
        if (file_exists($cache_file)) {
            return file_get_contents($cache_file);
        }
        
        return null;
    }
    
    /**
     * Verificar si el CSS necesita optimización
     */
    public function needs_optimization() {
        if (class_exists('WVP_Dynamic_CSS_Generator')) {
            $generator = WVP_Dynamic_CSS_Generator::get_instance();
            $css = $generator->get_generated_css();
            
            // Verificar tamaño
            if (strlen($css) > $this->optimization_settings['max_css_size']) {
                return true;
            }
            
            // Verificar si hay CSS optimizado
            $optimized_css = $this->get_optimized_css();
            if (!$optimized_css) {
                return true;
            }
            
            // Verificar si el CSS ha cambiado
            $cache_dir = WP_CONTENT_DIR . '/cache/wvp/';
            $hash_file = $cache_dir . 'css-hash.txt';
            
            if (file_exists($hash_file)) {
                $saved_hash = file_get_contents($hash_file);
                $current_hash = md5($css);
                
                if ($saved_hash !== $current_hash) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Obtener estadísticas de rendimiento
     */
    public function get_performance_stats() {
        $stats = array();
        
        if (class_exists('WVP_Dynamic_CSS_Generator')) {
            $generator = WVP_Dynamic_CSS_Generator::get_instance();
            $css = $generator->get_generated_css();
            
            $stats['css_size'] = strlen($css);
            $stats['css_size_kb'] = round(strlen($css) / 1024, 2);
            $stats['needs_optimization'] = $this->needs_optimization();
            $stats['optimization_enabled'] = $this->optimization_settings['enable_css_minification'];
            $stats['compression_enabled'] = $this->optimization_settings['enable_css_compression'];
            $stats['preload_enabled'] = $this->optimization_settings['enable_css_preload'];
            $stats['critical_css_enabled'] = $this->optimization_settings['enable_critical_css'];
        }
        
        return $stats;
    }
    
    /**
     * Configurar optimizaciones automáticas
     */
    public function setup_auto_optimization() {
        // Optimizar CSS automáticamente si es necesario
        if ($this->needs_optimization()) {
            $this->optimize_css();
        }
        
        // Limpiar caché antiguo
        $this->cleanup_old_cache();
    }
    
    /**
     * Limpiar caché antiguo
     */
    private function cleanup_old_cache() {
        $cache_dir = WP_CONTENT_DIR . '/cache/wvp/';
        
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '*');
            $max_age = $this->optimization_settings['css_cache_duration'];
            
            foreach ($files as $file) {
                if (is_file($file) && (time() - filemtime($file)) > $max_age) {
                    unlink($file);
                }
            }
        }
    }
}

// Inicializar el optimizador de rendimiento
WVP_Performance_Optimizer::get_instance();
?>