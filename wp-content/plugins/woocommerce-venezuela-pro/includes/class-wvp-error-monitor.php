<?php
/**
 * Sistema de Monitoreo de Errores
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Error_Monitor {
    
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
        if (class_exists('WooCommerce_Venezuela_Pro')) {
            $this->plugin = WooCommerce_Venezuela_Pro::get_instance();
        } else {
            $this->plugin = null;
        }
        
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_error_monitor_menu'));
        add_action('wp_ajax_wvp_clear_errors', array($this, 'clear_errors'));
        add_action('wp_ajax_wvp_get_error_log', array($this, 'get_error_log'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Registrar error en el log
     * 
     * @param string $message Mensaje de error
     * @param array $context Contexto adicional
     * @param string $level Nivel de error (error, warning, info)
     */
    public static function log_error($message, $context = array(), $level = 'error') {
        $error_data = array(
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'user_id' => get_current_user_id(),
            'url' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
            'ip' => self::get_client_ip()
        );
        
        // Guardar en base de datos
        self::save_error_to_db($error_data);
        
        // También guardar en log de WordPress si es error crítico
        if ($level === 'error') {
            error_log('WVP ERROR: ' . $message . ' | Context: ' . json_encode($context));
        }
    }
    
    /**
     * Guardar error en base de datos
     */
    private static function save_error_to_db($error_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wvp_error_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'timestamp' => $error_data['timestamp'],
                'level' => $error_data['level'],
                'message' => $error_data['message'],
                'context' => json_encode($error_data['context']),
                'user_id' => $error_data['user_id'],
                'url' => $error_data['url'],
                'ip' => $error_data['ip']
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s', '%s')
        );
    }
    
    /**
     * Obtener IP del cliente
     */
    private static function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    /**
     * Obtener resumen de errores
     */
    public function get_error_summary() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wvp_error_logs';
        
        $summary = array(
            'total_errors' => 0,
            'errors_today' => 0,
            'errors_this_week' => 0,
            'errors_by_level' => array(),
            'recent_errors' => array()
        );
        
        // Total de errores
        $summary['total_errors'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // Errores de hoy
        $summary['errors_today'] = $wpdb->get_var("
            SELECT COUNT(*) FROM $table_name 
            WHERE DATE(timestamp) = CURDATE()
        ");
        
        // Errores de esta semana
        $summary['errors_this_week'] = $wpdb->get_var("
            SELECT COUNT(*) FROM $table_name 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        
        // Errores por nivel
        $errors_by_level = $wpdb->get_results("
            SELECT level, COUNT(*) as count 
            FROM $table_name 
            GROUP BY level
        ");
        
        foreach ($errors_by_level as $error) {
            $summary['errors_by_level'][$error->level] = $error->count;
        }
        
        // Errores recientes
        $summary['recent_errors'] = $wpdb->get_results("
            SELECT * FROM $table_name 
            ORDER BY timestamp DESC 
            LIMIT 10
        ");
        
        return $summary;
    }
    
    /**
     * Limpiar errores antiguos
     */
    public function clear_old_errors($days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wvp_error_logs';
        
        $deleted = $wpdb->query($wpdb->prepare("
            DELETE FROM $table_name 
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $days));
        
        return $deleted;
    }
    
    /**
     * Añadir menú de monitoreo de errores
     */
    public function add_error_monitor_menu() {
        add_submenu_page(
            'wvp-settings',
            __('Monitoreo de Errores', 'wvp'),
            __('Monitoreo de Errores', 'wvp'),
            'manage_woocommerce',
            'wvp-error-monitor',
            array($this, 'display_error_monitor_page')
        );
    }
    
    /**
     * Mostrar página de monitoreo de errores
     */
    public function display_error_monitor_page() {
        $summary = $this->get_error_summary();
        ?>
        <div class="wrap">
            <h1><?php _e('Monitoreo de Errores - WooCommerce Venezuela Pro', 'wvp'); ?></h1>
            
            <!-- Resumen de errores -->
            <div class="wvp-error-summary">
                <div class="wvp-summary-cards">
                    <div class="wvp-summary-card">
                        <h3><?php _e('Total de Errores', 'wvp'); ?></h3>
                        <span class="wvp-error-count"><?php echo $summary['total_errors']; ?></span>
                    </div>
                    <div class="wvp-summary-card">
                        <h3><?php _e('Errores Hoy', 'wvp'); ?></h3>
                        <span class="wvp-error-count"><?php echo $summary['errors_today']; ?></span>
                    </div>
                    <div class="wvp-summary-card">
                        <h3><?php _e('Esta Semana', 'wvp'); ?></h3>
                        <span class="wvp-error-count"><?php echo $summary['errors_this_week']; ?></span>
                    </div>
                </div>
                
                <!-- Errores por nivel -->
                <div class="wvp-errors-by-level">
                    <h3><?php _e('Errores por Nivel', 'wvp'); ?></h3>
                    <div class="wvp-level-cards">
                        <?php foreach ($summary['errors_by_level'] as $level => $count): ?>
                        <div class="wvp-level-card wvp-level-<?php echo esc_attr($level); ?>">
                            <span class="wvp-level-name"><?php echo ucfirst($level); ?></span>
                            <span class="wvp-level-count"><?php echo $count; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de errores recientes -->
            <div class="wvp-recent-errors">
                <h3><?php _e('Errores Recientes', 'wvp'); ?></h3>
                <div class="wvp-error-actions">
                    <button type="button" class="button" id="wvp-refresh-errors"><?php _e('Actualizar', 'wvp'); ?></button>
                    <button type="button" class="button" id="wvp-clear-errors"><?php _e('Limpiar Errores Antiguos', 'wvp'); ?></button>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Fecha/Hora', 'wvp'); ?></th>
                            <th><?php _e('Nivel', 'wvp'); ?></th>
                            <th><?php _e('Mensaje', 'wvp'); ?></th>
                            <th><?php _e('Usuario', 'wvp'); ?></th>
                            <th><?php _e('URL', 'wvp'); ?></th>
                            <th><?php _e('Acciones', 'wvp'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wvp-error-log-tbody">
                        <?php foreach ($summary['recent_errors'] as $error): ?>
                        <tr>
                            <td><?php echo esc_html($error->timestamp); ?></td>
                            <td>
                                <span class="wvp-error-level wvp-level-<?php echo esc_attr($error->level); ?>">
                                    <?php echo ucfirst($error->level); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($error->message); ?></td>
                            <td><?php echo $error->user_id ? get_userdata($error->user_id)->display_name : __('Sistema', 'wvp'); ?></td>
                            <td><?php echo esc_html($error->url); ?></td>
                            <td>
                                <button type="button" class="button button-small wvp-view-context" data-context="<?php echo esc_attr($error->context); ?>">
                                    <?php _e('Ver Contexto', 'wvp'); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Modal para mostrar contexto -->
        <div id="wvp-context-modal" class="wvp-modal" style="display: none;">
            <div class="wvp-modal-content">
                <div class="wvp-modal-header">
                    <h3><?php _e('Contexto del Error', 'wvp'); ?></h3>
                    <span class="wvp-modal-close">&times;</span>
                </div>
                <div class="wvp-modal-body">
                    <pre id="wvp-context-content"></pre>
                </div>
            </div>
        </div>
        
        <style>
        .wvp-error-summary {
            margin: 20px 0;
        }
        
        .wvp-summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .wvp-summary-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .wvp-summary-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 14px;
        }
        
        .wvp-error-count {
            font-size: 32px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .wvp-level-cards {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .wvp-level-card {
            padding: 10px 15px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
        }
        
        .wvp-level-error { background: #dc3232; }
        .wvp-level-warning { background: #ffb900; }
        .wvp-level-info { background: #00a0d2; }
        
        .wvp-error-actions {
            margin: 20px 0;
        }
        
        .wvp-error-level {
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        .wvp-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
        }
        
        .wvp-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 80%;
            overflow: hidden;
        }
        
        .wvp-modal-header {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .wvp-modal-close {
            font-size: 24px;
            cursor: pointer;
        }
        
        .wvp-modal-body {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .wvp-modal-body pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Refrescar errores
            $('#wvp-refresh-errors').on('click', function() {
                location.reload();
            });
            
            // Limpiar errores antiguos
            $('#wvp-clear-errors').on('click', function() {
                if (confirm('<?php _e('¿Estás seguro de que quieres limpiar los errores antiguos?', 'wvp'); ?>')) {
                    $.post(ajaxurl, {
                        action: 'wvp_clear_errors',
                        nonce: '<?php echo wp_create_nonce('wvp_clear_errors'); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('<?php _e('Errores antiguos eliminados correctamente.', 'wvp'); ?>');
                            location.reload();
                        } else {
                            alert('<?php _e('Error al limpiar errores.', 'wvp'); ?>');
                        }
                    });
                }
            });
            
            // Ver contexto del error
            $('.wvp-view-context').on('click', function() {
                var context = $(this).data('context');
                $('#wvp-context-content').text(context);
                $('#wvp-context-modal').show();
            });
            
            // Cerrar modal
            $('.wvp-modal-close').on('click', function() {
                $('#wvp-context-modal').hide();
            });
            
            $(document).on('click', '.wvp-modal', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX: Limpiar errores
     */
    public function clear_errors() {
        check_ajax_referer('wvp_clear_errors', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para realizar esta acción.', 'wvp'));
        }
        
        $deleted = $this->clear_old_errors(30);
        
        wp_send_json_success(array(
            'message' => sprintf(__('Se eliminaron %d errores antiguos.', 'wvp'), $deleted)
        ));
    }
    
    /**
     * AJAX: Obtener log de errores
     */
    public function get_error_log() {
        check_ajax_referer('wvp_get_error_log', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para realizar esta acción.', 'wvp'));
        }
        
        $summary = $this->get_error_summary();
        wp_send_json_success($summary);
    }
    
    /**
     * Cargar scripts del admin
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'woocommerce_page_wvp-error-monitor') {
            return;
        }
        
        wp_enqueue_script('jquery');
    }
    
    /**
     * Crear tabla de errores en la base de datos
     */
    public static function create_error_log_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wvp_error_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            timestamp datetime NOT NULL,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            context longtext,
            user_id int(11) DEFAULT NULL,
            url varchar(255) DEFAULT NULL,
            ip varchar(45) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY timestamp (timestamp),
            KEY level (level),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
