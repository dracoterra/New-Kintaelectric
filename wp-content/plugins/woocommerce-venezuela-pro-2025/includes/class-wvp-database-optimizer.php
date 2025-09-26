<?php
/**
 * Database Optimizer
 * Database optimization and maintenance for WooCommerce Venezuela Pro 2025
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Database_Optimizer {
    
    private static $instance = null;
    private $wpdb;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'wp_ajax_wvp_optimize_database', array( $this, 'ajax_optimize_database' ) );
        add_action( 'wp_ajax_wvp_clean_old_data', array( $this, 'ajax_clean_old_data' ) );
        add_action( 'admin_menu', array( $this, 'add_database_admin_menu' ), 35 );
        
        // Schedule automatic maintenance
        add_action( 'wvp_daily_maintenance', array( $this, 'daily_maintenance' ) );
        
        if ( ! wp_next_scheduled( 'wvp_daily_maintenance' ) ) {
            wp_schedule_event( time(), 'daily', 'wvp_daily_maintenance' );
        }
    }
    
    /**
     * Daily maintenance tasks
     */
    public function daily_maintenance() {
        // Clean old transients
        $this->clean_old_transients();
        
        // Clean old logs
        $this->clean_old_logs();
        
        // Optimize tables
        $this->optimize_tables();
        
        do_action( 'wvp_daily_maintenance_completed' );
    }
    
    /**
     * Clean old transients
     */
    public function clean_old_transients() {
        $this->wpdb->query(
            "DELETE FROM {$this->wpdb->options} 
             WHERE option_name LIKE '_transient_timeout_%' 
             AND option_value < UNIX_TIMESTAMP()"
        );
        
        $this->wpdb->query(
            "DELETE FROM {$this->wpdb->options} 
             WHERE option_name LIKE '_transient_%' 
             AND option_name NOT IN (
                 SELECT CONCAT('_transient_', SUBSTRING(option_name, 19))
                 FROM {$this->wpdb->options}
                 WHERE option_name LIKE '_transient_timeout_%'
             )"
        );
    }
    
    /**
     * Clean old logs
     */
    public function clean_old_logs() {
        // Clean old debug logs (older than 30 days)
        $log_file = WP_CONTENT_DIR . '/debug.log';
        if ( file_exists( $log_file ) ) {
            $file_time = filemtime( $log_file );
            if ( $file_time && ( time() - $file_time ) > ( 30 * DAY_IN_SECONDS ) ) {
                // Archive old log
                $archive_file = WP_CONTENT_DIR . '/debug-' . date( 'Y-m-d', $file_time ) . '.log';
                copy( $log_file, $archive_file );
                file_put_contents( $log_file, '' );
            }
        }
        
        // Clean old plugin logs from database
        $this->wpdb->query(
            "DELETE FROM {$this->wpdb->options} 
             WHERE option_name LIKE 'wvp_log_%' 
             AND option_value < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
    }
    
    /**
     * Optimize database tables
     */
    public function optimize_tables() {
        $tables = array(
            $this->wpdb->posts,
            $this->wpdb->postmeta,
            $this->wpdb->options,
            $this->wpdb->usermeta,
            $this->wpdb->users,
            $this->wpdb->comments,
            $this->wpdb->commentmeta
        );
        
        // Add WooCommerce tables if they exist
        if ( class_exists( 'WooCommerce' ) ) {
            $wc_tables = array(
                $this->wpdb->prefix . 'woocommerce_order_items',
                $this->wpdb->prefix . 'woocommerce_order_itemmeta',
                $this->wpdb->prefix . 'woocommerce_tax_rates',
                $this->wpdb->prefix . 'woocommerce_tax_rate_locations',
                $this->wpdb->prefix . 'woocommerce_shipping_zones',
                $this->wpdb->prefix . 'woocommerce_shipping_zone_locations',
                $this->wpdb->prefix . 'woocommerce_shipping_zone_methods',
                $this->wpdb->prefix . 'woocommerce_payment_tokens',
                $this->wpdb->prefix . 'woocommerce_payment_tokenmeta'
            );
            
            $tables = array_merge( $tables, $wc_tables );
        }
        
        foreach ( $tables as $table ) {
            if ( $this->table_exists( $table ) ) {
                $this->wpdb->query( "OPTIMIZE TABLE {$table}" );
            }
        }
    }
    
    /**
     * Get database statistics
     */
    public function get_database_stats() {
        $stats = array();
        
        // Get table sizes
        $tables = $this->wpdb->get_results(
            "SELECT 
                table_name,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                table_rows
             FROM information_schema.tables 
             WHERE table_schema = '{$this->wpdb->dbname}'
             ORDER BY (data_length + index_length) DESC"
        );
        
        $stats['tables'] = $tables;
        
        // Get total database size
        $total_size = $this->wpdb->get_var(
            "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) 
             FROM information_schema.tables 
             WHERE table_schema = '{$this->wpdb->dbname}'"
        );
        
        $stats['total_size'] = $total_size;
        
        // Get transient count
        $transient_count = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->wpdb->options} 
             WHERE option_name LIKE '_transient_%'"
        );
        
        $stats['transient_count'] = $transient_count;
        
        // Get orphaned transients
        $orphaned_transients = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->wpdb->options} 
             WHERE option_name LIKE '_transient_%' 
             AND option_name NOT LIKE '_transient_timeout_%'
             AND option_name NOT IN (
                 SELECT CONCAT('_transient_', SUBSTRING(option_name, 19))
                 FROM {$this->wpdb->options}
                 WHERE option_name LIKE '_transient_timeout_%'
             )"
        );
        
        $stats['orphaned_transients'] = $orphaned_transients;
        
        return $stats;
    }
    
    /**
     * Check if table exists
     */
    private function table_exists( $table ) {
        $result = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM information_schema.tables 
                 WHERE table_schema = %s AND table_name = %s",
                $this->wpdb->dbname,
                $table
            )
        );
        
        return $result > 0;
    }
    
    /**
     * Add database admin menu
     */
    public function add_database_admin_menu() {
        add_submenu_page(
            'wvp-dashboard',
            'Optimización de Base de Datos',
            'Base de Datos',
            'manage_options',
            'wvp-database',
            array( $this, 'database_admin_page' )
        );
    }
    
    /**
     * Database admin page
     */
    public function database_admin_page() {
        $stats = $this->get_database_stats();
        ?>
        <div class="wrap">
            <h1>🗄️ Optimización de Base de Datos - WooCommerce Venezuela Pro 2025</h1>
            
            <div class="wvp-database-overview">
                <h2>Resumen de la Base de Datos</h2>
                <div class="wvp-stats-grid">
                    <div class="wvp-stat-card">
                        <h3>Tamaño Total</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['total_size'] ); ?> MB</p>
                    </div>
                    <div class="wvp-stat-card">
                        <h3>Transients</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['transient_count'] ); ?></p>
                    </div>
                    <div class="wvp-stat-card">
                        <h3>Transients Huérfanos</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['orphaned_transients'] ); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="wvp-database-tables">
                <h2>Tablas de la Base de Datos</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Tabla</th>
                            <th>Tamaño (MB)</th>
                            <th>Filas</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $stats['tables'] as $table ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $table->table_name ); ?></strong></td>
                            <td><?php echo esc_html( $table->size_mb ); ?></td>
                            <td><?php echo esc_html( number_format( $table->table_rows ) ); ?></td>
                            <td>
                                <?php if ( $table->size_mb > 100 ) : ?>
                                    <span style="color: orange;">⚠️ Grande</span>
                                <?php elseif ( $table->size_mb > 50 ) : ?>
                                    <span style="color: yellow;">⚡ Mediano</span>
                                <?php else : ?>
                                    <span style="color: green;">✅ Óptimo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="wvp-database-actions">
                <h2>Acciones de Optimización</h2>
                <p>
                    <button class="button button-primary" id="wvp-optimize-database">
                        🔧 Optimizar Base de Datos
                    </button>
                    <button class="button button-secondary" id="wvp-clean-old-data">
                        🗑️ Limpiar Datos Antiguos
                    </button>
                    <button class="button button-secondary" id="wvp-refresh-stats">
                        🔄 Actualizar Estadísticas
                    </button>
                </p>
            </div>
            
            <div class="wvp-database-info">
                <h2>Información de Mantenimiento</h2>
                <p>El sistema realiza automáticamente las siguientes tareas de mantenimiento:</p>
                <ul>
                    <li><strong>Limpieza Diaria:</strong> Transients expirados y logs antiguos</li>
                    <li><strong>Optimización:</strong> Tablas de la base de datos</li>
                    <li><strong>Archivado:</strong> Logs antiguos se archivan automáticamente</li>
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
            $('#wvp-optimize-database').on('click', function() {
                if (confirm('¿Estás seguro de que quieres optimizar la base de datos? Esto puede tomar varios minutos.')) {
                    $(this).prop('disabled', true).text('Optimizando...');
                    
                    $.post(ajaxurl, {
                        action: 'wvp_optimize_database',
                        nonce: '<?php echo wp_create_nonce( 'wvp_database_nonce' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('Base de datos optimizada exitosamente');
                            location.reload();
                        } else {
                            alert('Error al optimizar la base de datos');
                        }
                    }).always(function() {
                        $('#wvp-optimize-database').prop('disabled', false).text('🔧 Optimizar Base de Datos');
                    });
                }
            });
            
            $('#wvp-clean-old-data').on('click', function() {
                if (confirm('¿Limpiar datos antiguos y transients huérfanos?')) {
                    $(this).prop('disabled', true).text('Limpiando...');
                    
                    $.post(ajaxurl, {
                        action: 'wvp_clean_old_data',
                        nonce: '<?php echo wp_create_nonce( 'wvp_database_nonce' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('Datos antiguos limpiados exitosamente');
                            location.reload();
                        } else {
                            alert('Error al limpiar datos antiguos');
                        }
                    }).always(function() {
                        $('#wvp-clean-old-data').prop('disabled', false).text('🗑️ Limpiar Datos Antiguos');
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
     * AJAX optimize database
     */
    public function ajax_optimize_database() {
        check_ajax_referer( 'wvp_database_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $this->optimize_tables();
        wp_send_json_success( 'Database optimized successfully' );
    }
    
    /**
     * AJAX clean old data
     */
    public function ajax_clean_old_data() {
        check_ajax_referer( 'wvp_database_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $this->clean_old_transients();
        $this->clean_old_logs();
        wp_send_json_success( 'Old data cleaned successfully' );
    }
}
