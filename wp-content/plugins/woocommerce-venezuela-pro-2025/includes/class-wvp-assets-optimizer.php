<?php
/**
 * Assets Optimizer
 * CSS and JavaScript optimization for WooCommerce Venezuela Pro 2025
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Assets_Optimizer {
    
    private static $instance = null;
    private $minify_enabled = true;
    private $combine_enabled = true;
    private $cache_version = '1.0.0';
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_settings();
        $this->init_hooks();
    }
    
    /**
     * Initialize settings
     */
    private function init_settings() {
        $this->minify_enabled = get_option( 'wvp_minify_assets', true );
        $this->combine_enabled = get_option( 'wvp_combine_assets', true );
        $this->cache_version = get_option( 'wvp_assets_version', '1.0.0' );
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        if ( $this->minify_enabled ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'optimize_frontend_assets' ), 999 );
            add_action( 'admin_enqueue_scripts', array( $this, 'optimize_admin_assets' ), 999 );
        }
        
        add_action( 'wp_ajax_wvp_clear_assets_cache', array( $this, 'ajax_clear_assets_cache' ) );
        add_action( 'wp_ajax_wvp_regenerate_assets', array( $this, 'ajax_regenerate_assets' ) );
        add_action( 'admin_menu', array( $this, 'add_assets_admin_menu' ), 40 );
    }
    
    /**
     * Optimize frontend assets
     */
    public function optimize_frontend_assets() {
        if ( ! $this->minify_enabled ) {
            return;
        }
        
        // Get all enqueued styles
        global $wp_styles;
        $wvp_styles = array();
        
        foreach ( $wp_styles->registered as $handle => $style ) {
            if ( strpos( $handle, 'wvp-' ) === 0 || strpos( $style->src, 'woocommerce-venezuela-pro-2025' ) !== false ) {
                $wvp_styles[ $handle ] = $style;
            }
        }
        
        // Get all enqueued scripts
        global $wp_scripts;
        $wvp_scripts = array();
        
        foreach ( $wp_scripts->registered as $handle => $script ) {
            if ( strpos( $handle, 'wvp-' ) === 0 || strpos( $script->src, 'woocommerce-venezuela-pro-2025' ) !== false ) {
                $wvp_scripts[ $handle ] = $script;
            }
        }
        
        // Combine and minify styles
        if ( $this->combine_enabled && ! empty( $wvp_styles ) ) {
            $this->combine_styles( $wvp_styles );
        }
        
        // Combine and minify scripts
        if ( $this->combine_enabled && ! empty( $wvp_scripts ) ) {
            $this->combine_scripts( $wvp_scripts );
        }
    }
    
    /**
     * Optimize admin assets
     */
    public function optimize_admin_assets() {
        if ( ! $this->minify_enabled ) {
            return;
        }
        
        // Similar optimization for admin assets
        $this->optimize_frontend_assets();
    }
    
    /**
     * Combine styles
     */
    private function combine_styles( $styles ) {
        $combined_content = '';
        $combined_deps = array();
        
        foreach ( $styles as $handle => $style ) {
            // Remove from queue
            wp_dequeue_style( $handle );
            
            // Get file content
            $file_path = $this->get_file_path_from_url( $style->src );
            if ( $file_path && file_exists( $file_path ) ) {
                $content = file_get_contents( $file_path );
                $combined_content .= $this->minify_css( $content );
                $combined_deps = array_merge( $combined_deps, $style->deps );
            }
        }
        
        if ( ! empty( $combined_content ) ) {
            // Save combined file
            $combined_file = $this->save_combined_asset( 'wvp-combined.css', $combined_content );
            
            if ( $combined_file ) {
                // Enqueue combined file
                wp_enqueue_style(
                    'wvp-combined',
                    $combined_file,
                    array_unique( $combined_deps ),
                    $this->cache_version
                );
            }
        }
    }
    
    /**
     * Combine scripts
     */
    private function combine_scripts( $scripts ) {
        $combined_content = '';
        $combined_deps = array();
        
        foreach ( $scripts as $handle => $script ) {
            // Remove from queue
            wp_dequeue_script( $handle );
            
            // Get file content
            $file_path = $this->get_file_path_from_url( $script->src );
            if ( $file_path && file_exists( $file_path ) ) {
                $content = file_get_contents( $file_path );
                $combined_content .= $this->minify_js( $content );
                $combined_deps = array_merge( $combined_deps, $script->deps );
            }
        }
        
        if ( ! empty( $combined_content ) ) {
            // Save combined file
            $combined_file = $this->save_combined_asset( 'wvp-combined.js', $combined_content );
            
            if ( $combined_file ) {
                // Enqueue combined file
                wp_enqueue_script(
                    'wvp-combined',
                    $combined_file,
                    array_unique( $combined_deps ),
                    $this->cache_version,
                    true
                );
            }
        }
    }
    
    /**
     * Minify CSS
     */
    private function minify_css( $css ) {
        // Remove comments
        $css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
        
        // Remove unnecessary whitespace
        $css = preg_replace( '/\s+/', ' ', $css );
        $css = preg_replace( '/;\s*}/', '}', $css );
        $css = preg_replace( '/\s*{\s*/', '{', $css );
        $css = preg_replace( '/;\s*/', ';', $css );
        
        return trim( $css );
    }
    
    /**
     * Minify JavaScript
     */
    private function minify_js( $js ) {
        // Remove single-line comments
        $js = preg_replace( '~//.*$~m', '', $js );
        
        // Remove multi-line comments
        $js = preg_replace( '~/\*.*?\*/~s', '', $js );
        
        // Remove unnecessary whitespace
        $js = preg_replace( '/\s+/', ' ', $js );
        $js = preg_replace( '/;\s*/', ';', $js );
        
        return trim( $js );
    }
    
    /**
     * Get file path from URL
     */
    private function get_file_path_from_url( $url ) {
        $upload_dir = wp_upload_dir();
        $base_url = $upload_dir['baseurl'];
        
        if ( strpos( $url, $base_url ) === 0 ) {
            return str_replace( $base_url, $upload_dir['basedir'], $url );
        }
        
        // Try plugin directory
        $plugin_url = plugin_dir_url( dirname( __FILE__ ) );
        if ( strpos( $url, $plugin_url ) === 0 ) {
            return str_replace( $plugin_url, plugin_dir_path( dirname( __FILE__ ) ), $url );
        }
        
        return false;
    }
    
    /**
     * Save combined asset
     */
    private function save_combined_asset( $filename, $content ) {
        $upload_dir = wp_upload_dir();
        $assets_dir = $upload_dir['basedir'] . '/wvp-assets';
        
        if ( ! file_exists( $assets_dir ) ) {
            wp_mkdir_p( $assets_dir );
        }
        
        $file_path = $assets_dir . '/' . $filename;
        $file_url = $upload_dir['baseurl'] . '/wvp-assets/' . $filename;
        
        if ( file_put_contents( $file_path, $content ) ) {
            return $file_url;
        }
        
        return false;
    }
    
    /**
     * Get assets statistics
     */
    public function get_assets_stats() {
        $upload_dir = wp_upload_dir();
        $assets_dir = $upload_dir['basedir'] . '/wvp-assets';
        
        $stats = array(
            'minify_enabled' => $this->minify_enabled,
            'combine_enabled' => $this->combine_enabled,
            'cache_version' => $this->cache_version,
            'combined_files' => array(),
            'total_size' => 0
        );
        
        if ( file_exists( $assets_dir ) ) {
            $files = glob( $assets_dir . '/*' );
            foreach ( $files as $file ) {
                if ( is_file( $file ) ) {
                    $filename = basename( $file );
                    $size = filesize( $file );
                    $stats['combined_files'][ $filename ] = array(
                        'size' => $size,
                        'size_formatted' => size_format( $size ),
                        'modified' => date( 'Y-m-d H:i:s', filemtime( $file ) )
                    );
                    $stats['total_size'] += $size;
                }
            }
        }
        
        $stats['total_size_formatted'] = size_format( $stats['total_size'] );
        
        return $stats;
    }
    
    /**
     * Clear assets cache
     */
    public function clear_assets_cache() {
        $upload_dir = wp_upload_dir();
        $assets_dir = $upload_dir['basedir'] . '/wvp-assets';
        
        if ( file_exists( $assets_dir ) ) {
            $files = glob( $assets_dir . '/*' );
            foreach ( $files as $file ) {
                if ( is_file( $file ) ) {
                    unlink( $file );
                }
            }
        }
        
        // Update cache version
        $new_version = time();
        update_option( 'wvp_assets_version', $new_version );
        $this->cache_version = $new_version;
    }
    
    /**
     * Add assets admin menu
     */
    public function add_assets_admin_menu() {
        add_submenu_page(
            'wvp-dashboard',
            'Optimización de Assets',
            'Assets',
            'manage_options',
            'wvp-assets',
            array( $this, 'assets_admin_page' )
        );
    }
    
    /**
     * Assets admin page
     */
    public function assets_admin_page() {
        $stats = $this->get_assets_stats();
        ?>
        <div class="wrap">
            <h1>⚡ Optimización de Assets - WooCommerce Venezuela Pro 2025</h1>
            
            <div class="wvp-assets-settings">
                <h2>Configuración de Optimización</h2>
                <form method="post" action="options.php">
                    <?php settings_fields( 'wvp_assets_settings' ); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Minificación</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wvp_minify_assets" value="1" <?php checked( $stats['minify_enabled'] ); ?> />
                                    Habilitar minificación de CSS y JavaScript
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Combinación</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wvp_combine_assets" value="1" <?php checked( $stats['combine_enabled'] ); ?> />
                                    Combinar archivos CSS y JavaScript
                                </label>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button( 'Guardar Configuración' ); ?>
                </form>
            </div>
            
            <div class="wvp-assets-stats">
                <h2>Estadísticas de Assets</h2>
                <div class="wvp-stats-grid">
                    <div class="wvp-stat-card">
                        <h3>Tamaño Total</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['total_size_formatted'] ); ?></p>
                    </div>
                    <div class="wvp-stat-card">
                        <h3>Archivos Combinados</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( count( $stats['combined_files'] ) ); ?></p>
                    </div>
                    <div class="wvp-stat-card">
                        <h3>Versión de Cache</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['cache_version'] ); ?></p>
                    </div>
                </div>
            </div>
            
            <?php if ( ! empty( $stats['combined_files'] ) ) : ?>
            <div class="wvp-assets-files">
                <h2>Archivos Combinados</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Archivo</th>
                            <th>Tamaño</th>
                            <th>Modificado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $stats['combined_files'] as $filename => $file_data ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $filename ); ?></strong></td>
                            <td><?php echo esc_html( $file_data['size_formatted'] ); ?></td>
                            <td><?php echo esc_html( $file_data['modified'] ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <div class="wvp-assets-actions">
                <h2>Acciones de Assets</h2>
                <p>
                    <button class="button button-primary" id="wvp-clear-assets-cache">
                        🗑️ Limpiar Cache de Assets
                    </button>
                    <button class="button button-secondary" id="wvp-regenerate-assets">
                        🔄 Regenerar Assets
                    </button>
                    <button class="button button-secondary" id="wvp-refresh-stats">
                        🔄 Actualizar Estadísticas
                    </button>
                </p>
            </div>
            
            <div class="wvp-assets-info">
                <h2>Información de Optimización</h2>
                <p>El sistema de optimización de assets mejora el rendimiento del sitio web:</p>
                <ul>
                    <li><strong>Minificación:</strong> Reduce el tamaño de archivos CSS y JavaScript</li>
                    <li><strong>Combinación:</strong> Combina múltiples archivos en uno solo</li>
                    <li><strong>Cache:</strong> Genera versiones cacheadas para mejor rendimiento</li>
                    <li><strong>Compresión:</strong> Reduce el tiempo de carga de las páginas</li>
                </ul>
            </div>
        </div>
        
        <style>
        .wvp-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .wvp-stat-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .wvp-stat-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .wvp-stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
            margin: 0;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#wvp-clear-assets-cache').on('click', function() {
                if (confirm('¿Limpiar cache de assets?')) {
                    $(this).prop('disabled', true).text('Limpiando...');
                    
                    $.post(ajaxurl, {
                        action: 'wvp_clear_assets_cache',
                        nonce: '<?php echo wp_create_nonce( 'wvp_assets_nonce' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('Cache de assets limpiado exitosamente');
                            location.reload();
                        } else {
                            alert('Error al limpiar cache de assets');
                        }
                    }).always(function() {
                        $('#wvp-clear-assets-cache').prop('disabled', false).text('🗑️ Limpiar Cache de Assets');
                    });
                }
            });
            
            $('#wvp-regenerate-assets').on('click', function() {
                if (confirm('¿Regenerar assets optimizados?')) {
                    $(this).prop('disabled', true).text('Regenerando...');
                    
                    $.post(ajaxurl, {
                        action: 'wvp_regenerate_assets',
                        nonce: '<?php echo wp_create_nonce( 'wvp_assets_nonce' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('Assets regenerados exitosamente');
                            location.reload();
                        } else {
                            alert('Error al regenerar assets');
                        }
                    }).always(function() {
                        $('#wvp-regenerate-assets').prop('disabled', false).text('🔄 Regenerar Assets');
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
     * AJAX clear assets cache
     */
    public function ajax_clear_assets_cache() {
        check_ajax_referer( 'wvp_assets_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $this->clear_assets_cache();
        wp_send_json_success( 'Assets cache cleared successfully' );
    }
    
    /**
     * AJAX regenerate assets
     */
    public function ajax_regenerate_assets() {
        check_ajax_referer( 'wvp_assets_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $this->clear_assets_cache();
        wp_send_json_success( 'Assets regenerated successfully' );
    }
}
