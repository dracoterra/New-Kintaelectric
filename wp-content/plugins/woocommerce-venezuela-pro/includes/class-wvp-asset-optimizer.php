<?php
/**
 * Optimizador de assets
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Asset_Optimizer {
    
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
     * Directorio de assets
     * 
     * @var string
     */
    private $assets_dir;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        $this->load_config();
        $this->assets_dir = WVP_PLUGIN_PATH . 'assets/';
        
        // Inicializar hooks
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks de WordPress
     */
    private function init_hooks() {
        // Optimizaciones de CSS
        add_action('wp_enqueue_scripts', array($this, 'optimize_css'), 1);
        add_filter('style_loader_tag', array($this, 'optimize_css_tag'), 10, 2);
        
        // Optimizaciones de JavaScript
        add_action('wp_enqueue_scripts', array($this, 'optimize_js'), 1);
        add_filter('script_loader_tag', array($this, 'optimize_js_tag'), 10, 2);
        
        // Optimizaciones de imágenes
        add_action('wp_enqueue_scripts', array($this, 'optimize_images'), 1);
        
        // Minificación
        add_action('wp_loaded', array($this, 'setup_minification'));
        
        // Compresión
        add_action('wp_loaded', array($this, 'setup_compression'));
        
        // Lazy loading
        add_action('wp_enqueue_scripts', array($this, 'setup_lazy_loading'));
        
        // Preload de recursos críticos
        add_action('wp_head', array($this, 'preload_critical_resources'), 1);
        
        // Limpieza de assets
        add_action('wp_scheduled_delete', array($this, 'cleanup_assets'));
    }
    
    /**
     * Cargar configuración de optimización
     */
    private function load_config() {
        $this->config = array(
            'minify_css' => get_option('wvp_minify_css', 'yes'),
            'minify_js' => get_option('wvp_minify_js', 'yes'),
            'combine_css' => get_option('wvp_combine_css', 'no'),
            'combine_js' => get_option('wvp_combine_js', 'no'),
            'compress_assets' => get_option('wvp_compress_assets', 'yes'),
            'lazy_load_images' => get_option('wvp_lazy_load_images', 'yes'),
            'preload_critical' => get_option('wvp_preload_critical', 'yes'),
            'remove_unused_css' => get_option('wvp_remove_unused_css', 'no'),
            'defer_non_critical_js' => get_option('wvp_defer_non_critical_js', 'yes'),
            'inline_critical_css' => get_option('wvp_inline_critical_css', 'yes')
        );
    }
    
    /**
     * Optimizar CSS
     */
    public function optimize_css() {
        if ($this->config['minify_css'] !== 'yes') {
            return;
        }
        
        // Minificar CSS del plugin
        $this->minify_plugin_css();
        
        // Combinar CSS si está habilitado
        if ($this->config['combine_css'] === 'yes') {
            $this->combine_plugin_css();
        }
    }
    
    /**
     * Minificar CSS del plugin
     */
    private function minify_plugin_css() {
        $css_files = array(
            'currency-switcher.css',
            'dual-breakdown.css',
            'hybrid-invoicing.css',
            'wvp-ui-enhanced.css'
        );
        
        foreach ($css_files as $file) {
            $this->minify_css_file($file);
        }
    }
    
    /**
     * Minificar archivo CSS
     */
    private function minify_css_file($filename) {
        $source_file = $this->assets_dir . 'css/' . $filename;
        $min_file = $this->assets_dir . 'css/' . str_replace('.css', '.min.css', $filename);
        
        if (!file_exists($source_file)) {
            return;
        }
        
        // Verificar si el archivo minificado existe y es más reciente
        if (file_exists($min_file) && filemtime($min_file) > filemtime($source_file)) {
            return;
        }
        
        $css_content = file_get_contents($source_file);
        $minified_css = $this->minify_css_content($css_content);
        
        file_put_contents($min_file, $minified_css);
    }
    
    /**
     * Minificar contenido CSS
     */
    private function minify_css_content($css) {
        // Remover comentarios
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remover espacios en blanco innecesarios
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*{\s*/', '{', $css);
        $css = preg_replace('/;\s*/', ';', $css);
        $css = preg_replace('/\s*}\s*/', '}', $css);
        $css = preg_replace('/\s*,\s*/', ',', $css);
        $css = preg_replace('/\s*:\s*/', ':', $css);
        
        // Remover espacios al final de líneas
        $css = preg_replace('/\s*$/', '', $css);
        
        return trim($css);
    }
    
    /**
     * Combinar CSS del plugin
     */
    private function combine_plugin_css() {
        $css_files = array(
            'currency-switcher.min.css',
            'dual-breakdown.min.css',
            'hybrid-invoicing.min.css',
            'wvp-ui-enhanced.min.css'
        );
        
        $combined_css = '';
        $combined_file = $this->assets_dir . 'css/wvp-combined.min.css';
        
        foreach ($css_files as $file) {
            $file_path = $this->assets_dir . 'css/' . $file;
            if (file_exists($file_path)) {
                $combined_css .= file_get_contents($file_path) . "\n";
            }
        }
        
        if (!empty($combined_css)) {
            file_put_contents($combined_file, $combined_css);
        }
    }
    
    /**
     * Optimizar etiqueta CSS
     */
    public function optimize_css_tag($tag, $handle) {
        // Solo para archivos del plugin
        if (strpos($handle, 'wvp-') !== 0) {
            return $tag;
        }
        
        // Añadir atributos de optimización
        if ($this->config['defer_non_critical_js'] === 'yes' && !$this->is_critical_css($handle)) {
            $tag = str_replace('rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', $tag);
            $tag .= '<noscript><link rel="stylesheet" href="' . esc_url(wp_get_attachment_url($handle)) . '"></noscript>';
        }
        
        return $tag;
    }
    
    /**
     * Verificar si CSS es crítico
     */
    private function is_critical_css($handle) {
        $critical_css = array(
            'wvp-currency-switcher',
            'wvp-ui-enhanced'
        );
        
        return in_array($handle, $critical_css);
    }
    
    /**
     * Optimizar JavaScript
     */
    public function optimize_js() {
        if ($this->config['minify_js'] !== 'yes') {
            return;
        }
        
        // Minificar JavaScript del plugin
        $this->minify_plugin_js();
        
        // Combinar JavaScript si está habilitado
        if ($this->config['combine_js'] === 'yes') {
            $this->combine_plugin_js();
        }
    }
    
    /**
     * Minificar JavaScript del plugin
     */
    private function minify_plugin_js() {
        $js_files = array(
            'currency-switcher.js',
            'wvp-ui-enhanced.js'
        );
        
        foreach ($js_files as $file) {
            $this->minify_js_file($file);
        }
    }
    
    /**
     * Minificar archivo JavaScript
     */
    private function minify_js_file($filename) {
        $source_file = $this->assets_dir . 'js/' . $filename;
        $min_file = $this->assets_dir . 'js/' . str_replace('.js', '.min.js', $filename);
        
        if (!file_exists($source_file)) {
            return;
        }
        
        // Verificar si el archivo minificado existe y es más reciente
        if (file_exists($min_file) && filemtime($min_file) > filemtime($source_file)) {
            return;
        }
        
        $js_content = file_get_contents($source_file);
        $minified_js = $this->minify_js_content($js_content);
        
        file_put_contents($min_file, $minified_js);
    }
    
    /**
     * Minificar contenido JavaScript
     */
    private function minify_js_content($js) {
        // Remover comentarios de línea
        $js = preg_replace('/\/\/.*$/m', '', $js);
        
        // Remover comentarios de bloque
        $js = preg_replace('/\/\*.*?\*\//s', '', $js);
        
        // Remover espacios en blanco innecesarios
        $js = preg_replace('/\s+/', ' ', $js);
        $js = preg_replace('/\s*{\s*/', '{', $js);
        $js = preg_replace('/\s*}\s*/', '}', $js);
        $js = preg_replace('/\s*;\s*/', ';', $js);
        $js = preg_replace('/\s*,\s*/', ',', $js);
        $js = preg_replace('/\s*=\s*/', '=', $js);
        $js = preg_replace('/\s*\+\s*/', '+', $js);
        $js = preg_replace('/\s*-\s*/', '-', $js);
        $js = preg_replace('/\s*\*\s*/', '*', $js);
        $js = preg_replace('/\s*\/\s*/', '/', $js);
        
        // Remover espacios al final de líneas
        $js = preg_replace('/\s*$/', '', $js);
        
        return trim($js);
    }
    
    /**
     * Combinar JavaScript del plugin
     */
    private function combine_plugin_js() {
        $js_files = array(
            'currency-switcher.min.js',
            'wvp-ui-enhanced.min.js'
        );
        
        $combined_js = '';
        $combined_file = $this->assets_dir . 'js/wvp-combined.min.js';
        
        foreach ($js_files as $file) {
            $file_path = $this->assets_dir . 'js/' . $file;
            if (file_exists($file_path)) {
                $combined_js .= file_get_contents($file_path) . ";\n";
            }
        }
        
        if (!empty($combined_js)) {
            file_put_contents($combined_file, $combined_js);
        }
    }
    
    /**
     * Optimizar etiqueta JavaScript
     */
    public function optimize_js_tag($tag, $handle) {
        // Solo para archivos del plugin
        if (strpos($handle, 'wvp-') !== 0) {
            return $tag;
        }
        
        // Añadir atributos de optimización
        if ($this->config['defer_non_critical_js'] === 'yes' && !$this->is_critical_js($handle)) {
            $tag = str_replace('<script ', '<script defer ', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Verificar si JavaScript es crítico
     */
    private function is_critical_js($handle) {
        $critical_js = array(
            'wvp-currency-switcher'
        );
        
        return in_array($handle, $critical_js);
    }
    
    /**
     * Optimizar imágenes
     */
    public function optimize_images() {
        if ($this->config['lazy_load_images'] !== 'yes') {
            return;
        }
        
        // Añadir lazy loading a imágenes del plugin
        add_filter('wp_get_attachment_image_attributes', array($this, 'add_lazy_loading'), 10, 3);
    }
    
    /**
     * Añadir lazy loading a imágenes
     */
    public function add_lazy_loading($attr, $attachment, $size) {
        // Solo para imágenes del plugin
        if (strpos($attachment->post_mime_type, 'image/') !== 0) {
            return $attr;
        }
        
        // Añadir atributos de lazy loading
        $attr['loading'] = 'lazy';
        $attr['decoding'] = 'async';
        
        return $attr;
    }
    
    /**
     * Configurar minificación
     */
    public function setup_minification() {
        if ($this->config['minify_css'] === 'yes' || $this->config['minify_js'] === 'yes') {
            // Programar minificación
            if (!wp_next_scheduled('wvp_minify_assets')) {
                wp_schedule_event(time(), 'hourly', 'wvp_minify_assets');
            }
        }
    }
    
    /**
     * Configurar compresión
     */
    public function setup_compression() {
        if ($this->config['compress_assets'] === 'yes') {
            // Habilitar compresión gzip
            if (!headers_sent()) {
                if (extension_loaded('zlib') && !ob_get_level()) {
                    ob_start('ob_gzhandler');
                }
            }
        }
    }
    
    /**
     * Configurar lazy loading
     */
    public function setup_lazy_loading() {
        if ($this->config['lazy_load_images'] === 'yes') {
            // Añadir script de lazy loading
            wp_add_inline_script('jquery', $this->get_lazy_loading_script());
        }
    }
    
    /**
     * Obtener script de lazy loading
     */
    private function get_lazy_loading_script() {
        return "
        jQuery(document).ready(function($) {
            if ('IntersectionObserver' in window) {
                var imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                $('img[data-src]').each(function() {
                    imageObserver.observe(this);
                });
            }
        });
        ";
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
            'wvp-ui-enhanced'
        );
        
        foreach ($critical_css as $handle) {
            if (wp_style_is($handle, 'enqueued')) {
                $src = wp_styles()->registered[$handle]->src ?? '';
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
     * Limpiar assets
     */
    public function cleanup_assets() {
        // Limpiar archivos minificados antiguos
        $this->cleanup_old_minified_files();
        
        // Limpiar archivos combinados antiguos
        $this->cleanup_old_combined_files();
        
        // Limpiar caché de assets
        $this->cleanup_asset_cache();
    }
    
    /**
     * Limpiar archivos minificados antiguos
     */
    private function cleanup_old_minified_files() {
        $css_dir = $this->assets_dir . 'css/';
        $js_dir = $this->assets_dir . 'js/';
        
        // Limpiar CSS minificado
        $css_files = glob($css_dir . '*.min.css');
        foreach ($css_files as $file) {
            if (filemtime($file) < time() - (7 * 24 * 60 * 60)) { // 7 días
                unlink($file);
            }
        }
        
        // Limpiar JavaScript minificado
        $js_files = glob($js_dir . '*.min.js');
        foreach ($js_files as $file) {
            if (filemtime($file) < time() - (7 * 24 * 60 * 60)) { // 7 días
                unlink($file);
            }
        }
    }
    
    /**
     * Limpiar archivos combinados antiguos
     */
    private function cleanup_old_combined_files() {
        $combined_files = array(
            $this->assets_dir . 'css/wvp-combined.min.css',
            $this->assets_dir . 'js/wvp-combined.min.js'
        );
        
        foreach ($combined_files as $file) {
            if (file_exists($file) && filemtime($file) < time() - (24 * 60 * 60)) { // 1 día
                unlink($file);
            }
        }
    }
    
    /**
     * Limpiar caché de assets
     */
    private function cleanup_asset_cache() {
        $cache_dir = WP_CONTENT_DIR . '/cache/wvp/assets/';
        
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '*');
            $now = time();
            
            foreach ($files as $file) {
                if (is_file($file) && ($now - filemtime($file)) > (24 * 60 * 60)) { // 1 día
                    unlink($file);
                }
            }
        }
    }
    
    /**
     * Obtener estadísticas de assets
     */
    public function get_asset_stats() {
        $stats = array(
            'css_files' => 0,
            'js_files' => 0,
            'minified_css' => 0,
            'minified_js' => 0,
            'combined_css' => 0,
            'combined_js' => 0,
            'total_size' => 0,
            'minified_size' => 0,
            'savings' => 0
        );
        
        // Contar archivos CSS
        $css_files = glob($this->assets_dir . 'css/*.css');
        $stats['css_files'] = count($css_files);
        
        // Contar archivos JavaScript
        $js_files = glob($this->assets_dir . 'js/*.js');
        $stats['js_files'] = count($js_files);
        
        // Contar archivos minificados
        $min_css = glob($this->assets_dir . 'css/*.min.css');
        $min_js = glob($this->assets_dir . 'js/*.min.js');
        $stats['minified_css'] = count($min_css);
        $stats['minified_js'] = count($min_js);
        
        // Contar archivos combinados
        if (file_exists($this->assets_dir . 'css/wvp-combined.min.css')) {
            $stats['combined_css'] = 1;
        }
        if (file_exists($this->assets_dir . 'js/wvp-combined.min.js')) {
            $stats['combined_js'] = 1;
        }
        
        // Calcular tamaños
        foreach ($css_files as $file) {
            $stats['total_size'] += filesize($file);
        }
        foreach ($js_files as $file) {
            $stats['total_size'] += filesize($file);
        }
        
        foreach ($min_css as $file) {
            $stats['minified_size'] += filesize($file);
        }
        foreach ($min_js as $file) {
            $stats['minified_size'] += filesize($file);
        }
        
        // Calcular ahorros
        if ($stats['total_size'] > 0) {
            $stats['savings'] = (($stats['total_size'] - $stats['minified_size']) / $stats['total_size']) * 100;
        }
        
        return $stats;
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
