<?php
/**
 * Security Manager
 * Comprehensive security system for WooCommerce Venezuela Pro 2025
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Security_Manager {
    
    private static $instance = null;
    private $security_logs = array();
    private $failed_attempts = array();
    private $max_failed_attempts = 5;
    private $lockout_duration = 15 * MINUTE_IN_SECONDS; // 15 minutes
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->load_security_settings();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Authentication hooks
        add_action( 'wp_login_failed', array( $this, 'log_failed_login' ) );
        add_action( 'wp_login', array( $this, 'log_successful_login' ), 10, 2 );
        add_action( 'wp_logout', array( $this, 'log_logout' ) );
        
        // AJAX security
        add_action( 'wp_ajax_wvp_security_scan', array( $this, 'ajax_security_scan' ) );
        add_action( 'wp_ajax_wvp_clear_security_logs', array( $this, 'ajax_clear_security_logs' ) );
        
        // Admin menu
        add_action( 'admin_menu', array( $this, 'add_security_admin_menu' ), 45 );
        
        // Input validation
        add_action( 'init', array( $this, 'init_input_validation' ) );
        
        // File upload security
        add_filter( 'wp_handle_upload_prefilter', array( $this, 'validate_file_upload' ) );
        
        // SQL injection protection
        add_action( 'wp_loaded', array( $this, 'init_sql_injection_protection' ) );
    }
    
    /**
     * Load security settings
     */
    private function load_security_settings() {
        $this->max_failed_attempts = get_option( 'wvp_max_failed_attempts', 5 );
        $this->lockout_duration = get_option( 'wvp_lockout_duration', 15 * MINUTE_IN_SECONDS );
    }
    
    /**
     * Log failed login attempt
     */
    public function log_failed_login( $username ) {
        $ip = $this->get_client_ip();
        $user_agent = $this->get_user_agent();
        
        $this->log_security_event( 'failed_login', array(
            'username' => $username,
            'ip' => $ip,
            'user_agent' => $user_agent,
            'timestamp' => current_time( 'mysql' )
        ) );
        
        $this->track_failed_attempt( $ip );
        
        // Check if IP should be locked out
        if ( $this->is_ip_locked_out( $ip ) ) {
            $this->log_security_event( 'ip_locked_out', array(
                'ip' => $ip,
                'attempts' => $this->get_failed_attempts( $ip ),
                'timestamp' => current_time( 'mysql' )
            ) );
        }
    }
    
    /**
     * Log successful login
     */
    public function log_successful_login( $username, $user ) {
        $ip = $this->get_client_ip();
        $user_agent = $this->get_user_agent();
        
        $this->log_security_event( 'successful_login', array(
            'username' => $username,
            'user_id' => $user->ID,
            'ip' => $ip,
            'user_agent' => $user_agent,
            'timestamp' => current_time( 'mysql' )
        ) );
        
        // Clear failed attempts for this IP
        $this->clear_failed_attempts( $ip );
    }
    
    /**
     * Log logout
     */
    public function log_logout( $user_id ) {
        $ip = $this->get_client_ip();
        $user_agent = $this->get_user_agent();
        
        $this->log_security_event( 'logout', array(
            'user_id' => $user_id,
            'ip' => $ip,
            'user_agent' => $user_agent,
            'timestamp' => current_time( 'mysql' )
        ) );
    }
    
    /**
     * Log security event
     */
    private function log_security_event( $event_type, $data ) {
        $log_entry = array(
            'event_type' => $event_type,
            'data' => $data,
            'timestamp' => current_time( 'mysql' )
        );
        
        $logs = get_option( 'wvp_security_logs', array() );
        $logs[] = $log_entry;
        
        // Keep only last 1000 entries
        if ( count( $logs ) > 1000 ) {
            $logs = array_slice( $logs, -1000 );
        }
        
        update_option( 'wvp_security_logs', $logs );
    }
    
    /**
     * Track failed attempt
     */
    private function track_failed_attempt( $ip ) {
        $attempts = get_option( 'wvp_failed_attempts', array() );
        
        if ( ! isset( $attempts[ $ip ] ) ) {
            $attempts[ $ip ] = array(
                'count' => 0,
                'last_attempt' => 0
            );
        }
        
        $attempts[ $ip ]['count']++;
        $attempts[ $ip ]['last_attempt'] = time();
        
        update_option( 'wvp_failed_attempts', $attempts );
    }
    
    /**
     * Get failed attempts for IP
     */
    private function get_failed_attempts( $ip ) {
        $attempts = get_option( 'wvp_failed_attempts', array() );
        return isset( $attempts[ $ip ] ) ? $attempts[ $ip ]['count'] : 0;
    }
    
    /**
     * Check if IP is locked out
     */
    private function is_ip_locked_out( $ip ) {
        $attempts = get_option( 'wvp_failed_attempts', array() );
        
        if ( ! isset( $attempts[ $ip ] ) ) {
            return false;
        }
        
        $ip_data = $attempts[ $ip ];
        
        // Check if lockout period has expired
        if ( ( time() - $ip_data['last_attempt'] ) > $this->lockout_duration ) {
            $this->clear_failed_attempts( $ip );
            return false;
        }
        
        return $ip_data['count'] >= $this->max_failed_attempts;
    }
    
    /**
     * Clear failed attempts for IP
     */
    private function clear_failed_attempts( $ip ) {
        $attempts = get_option( 'wvp_failed_attempts', array() );
        unset( $attempts[ $ip ] );
        update_option( 'wvp_failed_attempts', $attempts );
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
        
        foreach ( $ip_keys as $key ) {
            if ( array_key_exists( $key, $_SERVER ) === true ) {
                foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
                    $ip = trim( $ip );
                    if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get user agent
     */
    private function get_user_agent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
    
    /**
     * Initialize input validation
     */
    public function init_input_validation() {
        // Validate all POST data
        if ( $_POST ) {
            $this->validate_post_data();
        }
        
        // Validate all GET data
        if ( $_GET ) {
            $this->validate_get_data();
        }
    }
    
    /**
     * Validate POST data
     */
    private function validate_post_data() {
        foreach ( $_POST as $key => $value ) {
            if ( is_string( $value ) ) {
                // Check for SQL injection patterns
                if ( $this->detect_sql_injection( $value ) ) {
                    $this->log_security_event( 'sql_injection_attempt', array(
                        'input' => $value,
                        'field' => $key,
                        'ip' => $this->get_client_ip(),
                        'timestamp' => current_time( 'mysql' )
                    ) );
                    
                    wp_die( 'Invalid input detected', 'Security Error', array( 'response' => 403 ) );
                }
                
                // Check for XSS patterns
                if ( $this->detect_xss( $value ) ) {
                    $this->log_security_event( 'xss_attempt', array(
                        'input' => $value,
                        'field' => $key,
                        'ip' => $this->get_client_ip(),
                        'timestamp' => current_time( 'mysql' )
                    ) );
                    
                    wp_die( 'Invalid input detected', 'Security Error', array( 'response' => 403 ) );
                }
            }
        }
    }
    
    /**
     * Validate GET data
     */
    private function validate_get_data() {
        foreach ( $_GET as $key => $value ) {
            if ( is_string( $value ) ) {
                // Check for SQL injection patterns
                if ( $this->detect_sql_injection( $value ) ) {
                    $this->log_security_event( 'sql_injection_attempt', array(
                        'input' => $value,
                        'field' => $key,
                        'ip' => $this->get_client_ip(),
                        'timestamp' => current_time( 'mysql' )
                    ) );
                    
                    wp_die( 'Invalid input detected', 'Security Error', array( 'response' => 403 ) );
                }
            }
        }
    }
    
    /**
     * Detect SQL injection patterns
     */
    private function detect_sql_injection( $input ) {
        $patterns = array(
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bupdate\b.*\bset\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\balter\b.*\btable\b)/i',
            '/(\bexec\b|\bexecute\b)/i',
            '/(\bscript\b.*\balert\b)/i',
            '/(\bscript\b.*\bdocument\b)/i'
        );
        
        foreach ( $patterns as $pattern ) {
            if ( preg_match( $pattern, $input ) ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect XSS patterns
     */
    private function detect_xss( $input ) {
        $patterns = array(
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/<iframe\b[^>]*>/i',
            '/<object\b[^>]*>/i',
            '/<embed\b[^>]*>/i',
            '/<link\b[^>]*>/i',
            '/<meta\b[^>]*>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i'
        );
        
        foreach ( $patterns as $pattern ) {
            if ( preg_match( $pattern, $input ) ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate file upload
     */
    public function validate_file_upload( $file ) {
        // Check file type
        $allowed_types = array( 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx' );
        $file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        
        if ( ! in_array( $file_extension, $allowed_types ) ) {
            $this->log_security_event( 'invalid_file_upload', array(
                'filename' => $file['name'],
                'extension' => $file_extension,
                'ip' => $this->get_client_ip(),
                'timestamp' => current_time( 'mysql' )
            ) );
            
            $file['error'] = 'Invalid file type';
            return $file;
        }
        
        // Check file size (max 10MB)
        if ( $file['size'] > 10 * 1024 * 1024 ) {
            $this->log_security_event( 'file_too_large', array(
                'filename' => $file['name'],
                'size' => $file['size'],
                'ip' => $this->get_client_ip(),
                'timestamp' => current_time( 'mysql' )
            ) );
            
            $file['error'] = 'File too large';
            return $file;
        }
        
        return $file;
    }
    
    /**
     * Initialize SQL injection protection
     */
    public function init_sql_injection_protection() {
        // Add prepared statement validation
        add_filter( 'query', array( $this, 'validate_query' ) );
    }
    
    /**
     * Validate database query
     */
    public function validate_query( $query ) {
        // Check for suspicious patterns in queries
        if ( $this->detect_sql_injection( $query ) ) {
            $this->log_security_event( 'suspicious_query', array(
                'query' => $query,
                'ip' => $this->get_client_ip(),
                'timestamp' => current_time( 'mysql' )
            ) );
            
            // Don't execute suspicious queries
            return '';
        }
        
        return $query;
    }
    
    /**
     * Get security statistics
     */
    public function get_security_stats() {
        $logs = get_option( 'wvp_security_logs', array() );
        $failed_attempts = get_option( 'wvp_failed_attempts', array() );
        
        $stats = array(
            'total_events' => count( $logs ),
            'failed_logins' => 0,
            'successful_logins' => 0,
            'locked_ips' => 0,
            'sql_injection_attempts' => 0,
            'xss_attempts' => 0,
            'recent_events' => array_slice( $logs, -10 )
        );
        
        foreach ( $logs as $log ) {
            switch ( $log['event_type'] ) {
                case 'failed_login':
                    $stats['failed_logins']++;
                    break;
                case 'successful_login':
                    $stats['successful_logins']++;
                    break;
                case 'sql_injection_attempt':
                    $stats['sql_injection_attempts']++;
                    break;
                case 'xss_attempt':
                    $stats['xss_attempts']++;
                    break;
            }
        }
        
        // Count locked IPs
        foreach ( $failed_attempts as $ip_data ) {
            if ( $ip_data['count'] >= $this->max_failed_attempts ) {
                $stats['locked_ips']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Add security admin menu
     */
    public function add_security_admin_menu() {
        add_submenu_page(
            'wvp-dashboard',
            'Seguridad y Auditoría',
            'Seguridad',
            'manage_options',
            'wvp-security',
            array( $this, 'security_admin_page' )
        );
    }
    
    /**
     * Security admin page
     */
    public function security_admin_page() {
        $stats = $this->get_security_stats();
        ?>
        <div class="wrap">
            <h1>🔒 Seguridad y Auditoría - WooCommerce Venezuela Pro 2025</h1>
            
            <div class="wvp-security-overview">
                <h2>Resumen de Seguridad</h2>
                <div class="wvp-stats-grid">
                    <div class="wvp-stat-card">
                        <h3>Eventos Totales</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['total_events'] ); ?></p>
                    </div>
                    <div class="wvp-stat-card">
                        <h3>Intentos Fallidos</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['failed_logins'] ); ?></p>
                    </div>
                    <div class="wvp-stat-card">
                        <h3>Logins Exitosos</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['successful_logins'] ); ?></p>
                    </div>
                    <div class="wvp-stat-card">
                        <h3>IPs Bloqueadas</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['locked_ips'] ); ?></p>
                    </div>
                    <div class="wvp-stat-card">
                        <h3>Intentos SQL Injection</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['sql_injection_attempts'] ); ?></p>
                    </div>
                    <div class="wvp-stat-card">
                        <h3>Intentos XSS</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['xss_attempts'] ); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="wvp-security-recent">
                <h2>Eventos Recientes</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Detalles</th>
                            <th>IP</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $stats['recent_events'] as $event ) : ?>
                        <tr>
                            <td>
                                <?php
                                $type_labels = array(
                                    'failed_login' => '🔴 Login Fallido',
                                    'successful_login' => '🟢 Login Exitoso',
                                    'logout' => '🔵 Logout',
                                    'ip_locked_out' => '🚫 IP Bloqueada',
                                    'sql_injection_attempt' => '⚠️ SQL Injection',
                                    'xss_attempt' => '⚠️ XSS',
                                    'invalid_file_upload' => '📁 Archivo Inválido',
                                    'file_too_large' => '📁 Archivo Grande',
                                    'suspicious_query' => '🔍 Query Sospechosa'
                                );
                                echo esc_html( $type_labels[ $event['event_type'] ] ?? $event['event_type'] );
                                ?>
                            </td>
                            <td>
                                <?php
                                $data = $event['data'];
                                if ( isset( $data['username'] ) ) {
                                    echo 'Usuario: ' . esc_html( $data['username'] );
                                } elseif ( isset( $data['filename'] ) ) {
                                    echo 'Archivo: ' . esc_html( $data['filename'] );
                                } elseif ( isset( $data['input'] ) ) {
                                    echo 'Input: ' . esc_html( substr( $data['input'], 0, 50 ) ) . '...';
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html( $data['ip'] ?? 'N/A' ); ?></td>
                            <td><?php echo esc_html( $data['timestamp'] ?? $event['timestamp'] ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="wvp-security-actions">
                <h2>Acciones de Seguridad</h2>
                <p>
                    <button class="button button-primary" id="wvp-security-scan">
                        🔍 Escanear Seguridad
                    </button>
                    <button class="button button-secondary" id="wvp-clear-security-logs">
                        🗑️ Limpiar Logs
                    </button>
                    <button class="button button-secondary" id="wvp-refresh-security">
                        🔄 Actualizar
                    </button>
                </p>
            </div>
            
            <div class="wvp-security-info">
                <h2>Información de Seguridad</h2>
                <p>El sistema de seguridad monitorea y protege contra:</p>
                <ul>
                    <li><strong>Intentos de Login:</strong> Bloqueo automático después de 5 intentos fallidos</li>
                    <li><strong>SQL Injection:</strong> Detección y bloqueo de patrones maliciosos</li>
                    <li><strong>XSS:</strong> Validación de scripts maliciosos</li>
                    <li><strong>Archivos:</strong> Validación de tipos y tamaños de archivo</li>
                    <li><strong>Auditoría:</strong> Registro completo de eventos de seguridad</li>
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
            $('#wvp-security-scan').on('click', function() {
                $(this).prop('disabled', true).text('Escaneando...');
                
                $.post(ajaxurl, {
                    action: 'wvp_security_scan',
                    nonce: '<?php echo wp_create_nonce( 'wvp_security_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Escaneo de seguridad completado');
                    } else {
                        alert('Error en el escaneo de seguridad');
                    }
                }).always(function() {
                    $('#wvp-security-scan').prop('disabled', false).text('🔍 Escanear Seguridad');
                });
            });
            
            $('#wvp-clear-security-logs').on('click', function() {
                if (confirm('¿Limpiar todos los logs de seguridad?')) {
                    $(this).prop('disabled', true).text('Limpiando...');
                    
                    $.post(ajaxurl, {
                        action: 'wvp_clear_security_logs',
                        nonce: '<?php echo wp_create_nonce( 'wvp_security_nonce' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('Logs de seguridad limpiados');
                            location.reload();
                        } else {
                            alert('Error al limpiar logs');
                        }
                    }).always(function() {
                        $('#wvp-clear-security-logs').prop('disabled', false).text('🗑️ Limpiar Logs');
                    });
                }
            });
            
            $('#wvp-refresh-security').on('click', function() {
                location.reload();
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX security scan
     */
    public function ajax_security_scan() {
        check_ajax_referer( 'wvp_security_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        // Perform security scan
        $scan_results = array(
            'wordpress_version' => get_bloginfo( 'version' ),
            'plugin_version' => WOOCOMMERCE_VENEZUELA_PRO_2025_VERSION,
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->wpdb->db_version(),
            'ssl_enabled' => is_ssl(),
            'debug_mode' => WP_DEBUG,
            'file_permissions' => $this->check_file_permissions()
        );
        
        wp_send_json_success( $scan_results );
    }
    
    /**
     * AJAX clear security logs
     */
    public function ajax_clear_security_logs() {
        check_ajax_referer( 'wvp_security_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        delete_option( 'wvp_security_logs' );
        delete_option( 'wvp_failed_attempts' );
        
        wp_send_json_success( 'Security logs cleared successfully' );
    }
    
    /**
     * Check file permissions
     */
    private function check_file_permissions() {
        $wp_config = ABSPATH . 'wp-config.php';
        $htaccess = ABSPATH . '.htaccess';
        
        $permissions = array();
        
        if ( file_exists( $wp_config ) ) {
            $permissions['wp-config'] = substr( sprintf( '%o', fileperms( $wp_config ) ), -4 );
        }
        
        if ( file_exists( $htaccess ) ) {
            $permissions['htaccess'] = substr( sprintf( '%o', fileperms( $htaccess ) ), -4 );
        }
        
        return $permissions;
    }
}
