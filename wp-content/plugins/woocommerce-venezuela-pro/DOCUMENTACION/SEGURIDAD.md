# Seguridad - WooCommerce Venezuela Pro

## Consideraciones de Seguridad

### Amenazas Comunes

#### 1. Inyección SQL
- **Riesgo**: Alto
- **Protección**: Prepared statements, sanitización
- **Implementación**: Uso de `$wpdb->prepare()`

#### 2. Cross-Site Scripting (XSS)
- **Riesgo**: Alto
- **Protección**: Escape de output, validación
- **Implementación**: `esc_html()`, `esc_attr()`, `esc_url()`

#### 3. Cross-Site Request Forgery (CSRF)
- **Riesgo**: Medio
- **Protección**: Nonces, validación
- **Implementación**: `wp_verify_nonce()`

#### 4. Inyección de Archivos
- **Riesgo**: Alto
- **Protección**: Validación de tipos, permisos
- **Implementación**: `wp_check_filetype()`, `current_user_can()`

#### 5. Escalación de Privilegios
- **Riesgo**: Alto
- **Protección**: Verificación de roles
- **Implementación**: `current_user_can()`

## Implementación de Seguridad

### Validación de Datos

#### Sanitización de Entrada
```php
class WVP_Security_Validator {
    
    /**
     * Sanitizar datos de entrada
     * 
     * @param mixed $data Datos a sanitizar
     * @return mixed Datos sanitizados
     */
    public static function sanitize_input($data) {
        if (is_array($data)) {
            return array_map(array(self::class, 'sanitize_input'), $data);
        }
        
        if (is_string($data)) {
            return sanitize_text_field($data);
        }
        
        return $data;
    }
    
    /**
     * Sanitizar email
     * 
     * @param string $email Email a sanitizar
     * @return string Email sanitizado
     */
    public static function sanitize_email($email) {
        return sanitize_email($email);
    }
    
    /**
     * Sanitizar URL
     * 
     * @param string $url URL a sanitizar
     * @return string URL sanitizada
     */
    public static function sanitize_url($url) {
        return esc_url_raw($url);
    }
    
    /**
     * Sanitizar HTML
     * 
     * @param string $html HTML a sanitizar
     * @return string HTML sanitizado
     */
    public static function sanitize_html($html) {
        return wp_kses_post($html);
    }
}
```

#### Validación de Datos
```php
class WVP_Data_Validator {
    
    /**
     * Validar email
     * 
     * @param string $email Email a validar
     * @return bool True si es válido
     */
    public static function validate_email($email) {
        return is_email($email);
    }
    
    /**
     * Validar URL
     * 
     * @param string $url URL a validar
     * @return bool True si es válida
     */
    public static function validate_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validar número
     * 
     * @param mixed $number Número a validar
     * @param float $min Valor mínimo
     * @param float $max Valor máximo
     * @return bool True si es válido
     */
    public static function validate_number($number, $min = null, $max = null) {
        if (!is_numeric($number)) {
            return false;
        }
        
        $number = floatval($number);
        
        if ($min !== null && $number < $min) {
            return false;
        }
        
        if ($max !== null && $number > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar cédula/RIF venezolano
     * 
     * @param string $id Cédula/RIF a validar
     * @return bool True si es válido
     */
    public static function validate_venezuelan_id($id) {
        // Validar formato de cédula (V-12345678)
        if (preg_match('/^V-\d{7,8}$/', $id)) {
            return true;
        }
        
        // Validar formato de RIF (J-12345678-9)
        if (preg_match('/^[JG]-?\d{8}-?\d$/', $id)) {
            return true;
        }
        
        return false;
    }
}
```

### Escape de Output

#### Funciones de Escape
```php
class WVP_Output_Escaper {
    
    /**
     * Escape HTML
     * 
     * @param string $html HTML a escapar
     * @return string HTML escapado
     */
    public static function escape_html($html) {
        return esc_html($html);
    }
    
    /**
     * Escape atributos
     * 
     * @param string $attr Atributo a escapar
     * @return string Atributo escapado
     */
    public static function escape_attr($attr) {
        return esc_attr($attr);
    }
    
    /**
     * Escape URL
     * 
     * @param string $url URL a escapar
     * @return string URL escapada
     */
    public static function escape_url($url) {
        return esc_url($url);
    }
    
    /**
     * Escape JavaScript
     * 
     * @param string $js JavaScript a escapar
     * @return string JavaScript escapado
     */
    public static function escape_js($js) {
        return esc_js($js);
    }
    
    /**
     * Escape para SQL
     * 
     * @param string $sql SQL a escapar
     * @return string SQL escapado
     */
    public static function escape_sql($sql) {
        global $wpdb;
        return $wpdb->esc_like($sql);
    }
}
```

### Protección CSRF

#### Sistema de Nonces
```php
class WVP_CSRF_Protection {
    
    /**
     * Crear nonce
     * 
     * @param string $action Acción asociada
     * @return string Nonce generado
     */
    public static function create_nonce($action) {
        return wp_create_nonce($action);
    }
    
    /**
     * Verificar nonce
     * 
     * @param string $nonce Nonce a verificar
     * @param string $action Acción asociada
     * @return bool True si es válido
     */
    public static function verify_nonce($nonce, $action) {
        return wp_verify_nonce($nonce, $action);
    }
    
    /**
     * Verificar nonce en formulario
     * 
     * @param string $action Acción del formulario
     * @return bool True si es válido
     */
    public static function verify_form_nonce($action) {
        $nonce = $_POST['_wpnonce'] ?? '';
        return self::verify_nonce($nonce, $action);
    }
    
    /**
     * Generar campo nonce para formulario
     * 
     * @param string $action Acción del formulario
     * @return string Campo HTML
     */
    public static function nonce_field($action) {
        return wp_nonce_field($action, '_wpnonce', true, false);
    }
}
```

### Rate Limiting

#### Limitación de Velocidad
```php
class WVP_Rate_Limiter {
    
    /**
     * Verificar límite de velocidad
     * 
     * @param string $identifier Identificador único
     * @param int $max_attempts Máximo intentos
     * @param int $time_window Ventana de tiempo (segundos)
     * @return bool True si está dentro del límite
     */
    public static function check_rate_limit($identifier, $max_attempts = 5, $time_window = 300) {
        $key = "wvp_rate_limit_{$identifier}";
        $attempts = get_transient($key);
        
        if ($attempts === false) {
            set_transient($key, 1, $time_window);
            return true;
        }
        
        if ($attempts >= $max_attempts) {
            return false;
        }
        
        set_transient($key, $attempts + 1, $time_window);
        return true;
    }
    
    /**
     * Bloquear IP
     * 
     * @param string $ip IP a bloquear
     * @param int $duration Duración del bloqueo (segundos)
     */
    public static function block_ip($ip, $duration = 3600) {
        $key = "wvp_blocked_ip_{$ip}";
        set_transient($key, true, $duration);
        
        // Registrar en log de seguridad
        self::log_security_event('ip_blocked', array(
            'ip' => $ip,
            'duration' => $duration,
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Verificar si IP está bloqueada
     * 
     * @param string $ip IP a verificar
     * @return bool True si está bloqueada
     */
    public static function is_ip_blocked($ip) {
        $key = "wvp_blocked_ip_{$ip}";
        return get_transient($key) !== false;
    }
    
    /**
     * Registrar evento de seguridad
     * 
     * @param string $event Tipo de evento
     * @param array $data Datos del evento
     */
    private static function log_security_event($event, $data) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'wvp_security_logs',
            array(
                'timestamp' => current_time('mysql'),
                'event_type' => $event,
                'message' => json_encode($data),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_id' => get_current_user_id()
            )
        );
    }
}
```

### Autenticación y Autorización

#### Verificación de Permisos
```php
class WVP_Permission_Checker {
    
    /**
     * Verificar capacidad de usuario
     * 
     * @param string $capability Capacidad requerida
     * @return bool True si tiene permiso
     */
    public static function check_capability($capability) {
        return current_user_can($capability);
    }
    
    /**
     * Verificar si es administrador
     * 
     * @return bool True si es administrador
     */
    public static function is_admin() {
        return current_user_can('manage_options');
    }
    
    /**
     * Verificar si puede gestionar WooCommerce
     * 
     * @return bool True si puede gestionar
     */
    public static function can_manage_woocommerce() {
        return current_user_can('manage_woocommerce');
    }
    
    /**
     * Verificar si puede ver reportes
     * 
     * @return bool True si puede ver
     */
    public static function can_view_reports() {
        return current_user_can('view_woocommerce_reports');
    }
    
    /**
     * Verificar si puede gestionar órdenes
     * 
     * @return bool True si puede gestionar
     */
    public static function can_manage_orders() {
        return current_user_can('manage_woocommerce_orders');
    }
}
```

### Protección de Archivos

#### Validación de Archivos
```php
class WVP_File_Validator {
    
    /**
     * Validar tipo de archivo
     * 
     * @param array $file Archivo a validar
     * @param array $allowed_types Tipos permitidos
     * @return bool True si es válido
     */
    public static function validate_file_type($file, $allowed_types = array()) {
        if (empty($allowed_types)) {
            $allowed_types = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx');
        }
        
        $file_type = wp_check_filetype($file['name']);
        $extension = strtolower($file_type['ext']);
        
        return in_array($extension, $allowed_types);
    }
    
    /**
     * Validar tamaño de archivo
     * 
     * @param array $file Archivo a validar
     * @param int $max_size Tamaño máximo (bytes)
     * @return bool True si es válido
     */
    public static function validate_file_size($file, $max_size = 5242880) { // 5MB
        return $file['size'] <= $max_size;
    }
    
    /**
     * Validar archivo completo
     * 
     * @param array $file Archivo a validar
     * @param array $options Opciones de validación
     * @return array Resultado de validación
     */
    public static function validate_file($file, $options = array()) {
        $defaults = array(
            'allowed_types' => array('jpg', 'jpeg', 'png', 'pdf'),
            'max_size' => 5242880,
            'check_mime' => true
        );
        
        $options = wp_parse_args($options, $defaults);
        $errors = array();
        
        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error en la subida del archivo';
            return array('valid' => false, 'errors' => $errors);
        }
        
        // Verificar tipo
        if (!self::validate_file_type($file, $options['allowed_types'])) {
            $errors[] = 'Tipo de archivo no permitido';
        }
        
        // Verificar tamaño
        if (!self::validate_file_size($file, $options['max_size'])) {
            $errors[] = 'Archivo demasiado grande';
        }
        
        // Verificar MIME type
        if ($options['check_mime']) {
            $mime_type = wp_check_filetype($file['name']);
            if (!$mime_type['type']) {
                $errors[] = 'Tipo MIME no válido';
            }
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }
}
```

### Logging de Seguridad

#### Sistema de Logs de Seguridad
```php
class WVP_Security_Logger {
    
    /**
     * Registrar evento de seguridad
     * 
     * @param string $event Tipo de evento
     * @param string $message Mensaje del evento
     * @param array $context Contexto adicional
     */
    public static function log_security_event($event, $message, $context = array()) {
        global $wpdb;
        
        $log_data = array(
            'timestamp' => current_time('mysql'),
            'event_type' => $event,
            'message' => $message,
            'context' => json_encode($context),
            'ip_address' => self::get_client_ip(),
            'user_id' => get_current_user_id(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'url' => $_SERVER['REQUEST_URI'] ?? ''
        );
        
        $wpdb->insert(
            $wpdb->prefix . 'wvp_security_logs',
            $log_data
        );
    }
    
    /**
     * Obtener IP del cliente
     * 
     * @return string IP del cliente
     */
    private static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
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
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Obtener logs de seguridad
     * 
     * @param string $event_type Tipo de evento (opcional)
     * @param int $limit Límite de resultados
     * @return array Logs de seguridad
     */
    public static function get_security_logs($event_type = null, $limit = 100) {
        global $wpdb;
        
        $where = '';
        if ($event_type) {
            $where = $wpdb->prepare("WHERE event_type = %s", $event_type);
        }
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}wvp_security_logs
            {$where}
            ORDER BY timestamp DESC
            LIMIT %d
        ", $limit));
    }
}
```

### Protección de Base de Datos

#### Consultas Seguras
```php
class WVP_Database_Security {
    
    /**
     * Ejecutar consulta preparada
     * 
     * @param string $query Consulta SQL
     * @param array $args Argumentos para la consulta
     * @return mixed Resultado de la consulta
     */
    public static function prepare_query($query, $args = array()) {
        global $wpdb;
        
        if (empty($args)) {
            return $wpdb->query($query);
        }
        
        return $wpdb->query($wpdb->prepare($query, $args));
    }
    
    /**
     * Obtener resultado de consulta preparada
     * 
     * @param string $query Consulta SQL
     * @param array $args Argumentos para la consulta
     * @return array Resultados
     */
    public static function get_results($query, $args = array()) {
        global $wpdb;
        
        if (empty($args)) {
            return $wpdb->get_results($query);
        }
        
        return $wpdb->get_results($wpdb->prepare($query, $args));
    }
    
    /**
     * Obtener variable de consulta preparada
     * 
     * @param string $query Consulta SQL
     * @param array $args Argumentos para la consulta
     * @return mixed Resultado
     */
    public static function get_var($query, $args = array()) {
        global $wpdb;
        
        if (empty($args)) {
            return $wpdb->get_var($query);
        }
        
        return $wpdb->get_var($wpdb->prepare($query, $args));
    }
}
```

## Configuración de Seguridad

### Configuraciones Recomendadas

#### wp-config.php
```php
// Configuraciones de seguridad
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);
define('FORCE_SSL_ADMIN', true);
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// Configuraciones de autenticación
define('AUTH_KEY', 'tu-clave-secreta-aqui');
define('SECURE_AUTH_KEY', 'tu-clave-secreta-aqui');
define('LOGGED_IN_KEY', 'tu-clave-secreta-aqui');
define('NONCE_KEY', 'tu-clave-secreta-aqui');
define('AUTH_SALT', 'tu-clave-secreta-aqui');
define('SECURE_AUTH_SALT', 'tu-clave-secreta-aqui');
define('LOGGED_IN_SALT', 'tu-clave-secreta-aqui');
define('NONCE_SALT', 'tu-clave-secreta-aqui');
```

#### Configuración del Plugin
```php
// Configuraciones de seguridad del plugin
$security_settings = array(
    'rate_limiting' => true,
    'max_attempts' => 5,
    'lockout_duration' => 300,
    'log_security_events' => true,
    'sanitize_inputs' => true,
    'validate_outputs' => true,
    'block_suspicious_ips' => true,
    'require_https' => true
);
```

## Monitoreo de Seguridad

### Alertas de Seguridad
```php
class WVP_Security_Alerts {
    
    /**
     * Enviar alerta de seguridad
     * 
     * @param string $level Nivel de alerta
     * @param string $message Mensaje de alerta
     * @param array $context Contexto adicional
     */
    public static function send_alert($level, $message, $context = array()) {
        // Registrar en log
        WVP_Security_Logger::log_security_event($level, $message, $context);
        
        // Enviar email si es crítico
        if ($level === 'critical') {
            self::send_email_alert($message, $context);
        }
        
        // Enviar a Slack si está configurado
        if (get_option('wvp_slack_webhook')) {
            self::send_slack_alert($level, $message, $context);
        }
    }
    
    /**
     * Enviar alerta por email
     * 
     * @param string $message Mensaje
     * @param array $context Contexto
     */
    private static function send_email_alert($message, $context) {
        $admin_email = get_option('admin_email');
        $subject = 'WVP Security Alert: ' . $message;
        $body = "Security Alert: {$message}\n\nContext: " . json_encode($context);
        
        wp_mail($admin_email, $subject, $body);
    }
    
    /**
     * Enviar alerta a Slack
     * 
     * @param string $level Nivel
     * @param string $message Mensaje
     * @param array $context Contexto
     */
    private static function send_slack_alert($level, $message, $context) {
        $webhook_url = get_option('wvp_slack_webhook');
        
        $payload = array(
            'text' => "WVP Security Alert: {$level}",
            'attachments' => array(
                array(
                    'color' => self::get_alert_color($level),
                    'fields' => array(
                        array(
                            'title' => 'Message',
                            'value' => $message,
                            'short' => false
                        ),
                        array(
                            'title' => 'Context',
                            'value' => json_encode($context),
                            'short' => false
                        )
                    )
                )
            )
        );
        
        wp_remote_post($webhook_url, array(
            'body' => json_encode($payload),
            'headers' => array('Content-Type' => 'application/json')
        ));
    }
    
    /**
     * Obtener color de alerta
     * 
     * @param string $level Nivel de alerta
     * @return string Color hexadecimal
     */
    private static function get_alert_color($level) {
        $colors = array(
            'critical' => '#ff0000',
            'high' => '#ff6600',
            'medium' => '#ffaa00',
            'low' => '#00aa00',
            'info' => '#0066cc'
        );
        
        return $colors[$level] ?? '#666666';
    }
}
```

## Conclusión

La implementación de seguridad del plugin WooCommerce Venezuela Pro incluye:

- ✅ **Validación completa**: Entrada y salida de datos
- ✅ **Protección CSRF**: Nonces y validación
- ✅ **Rate limiting**: Prevención de ataques
- ✅ **Autenticación**: Verificación de permisos
- ✅ **Logging**: Registro de eventos de seguridad
- ✅ **Monitoreo**: Alertas automáticas
- ✅ **Base de datos**: Consultas preparadas
- ✅ **Archivos**: Validación de subidas

Esta implementación proporciona múltiples capas de seguridad para proteger el plugin y los datos de los usuarios.
