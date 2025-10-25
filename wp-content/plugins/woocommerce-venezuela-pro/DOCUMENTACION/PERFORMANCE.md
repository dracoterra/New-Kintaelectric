# Optimización y Rendimiento - WooCommerce Venezuela Pro

## Estrategia de Optimización

### Objetivos de Rendimiento

#### Métricas Clave
- **Tiempo de carga**: < 2 segundos
- **Uso de memoria**: < 128MB por request
- **Consultas DB**: < 50 por página
- **Tamaño de página**: < 2MB
- **Tiempo de respuesta**: < 500ms para APIs

#### Puntos de Optimización
1. **Caché**: Sistema de caché inteligente
2. **Base de datos**: Optimización de consultas
3. **Assets**: Minificación y compresión
4. **Código**: Optimización de algoritmos
5. **Red**: Reducción de requests

## Sistema de Caché

### Caché Multi-Nivel

#### Caché de Objeto
```php
class WVP_Object_Cache {
    
    private static $cache = array();
    private static $cache_duration = 3600; // 1 hora
    
    /**
     * Obtener del caché
     * 
     * @param string $key Clave del caché
     * @return mixed Datos en caché o null
     */
    public static function get($key) {
        // Verificar caché local
        if (isset(self::$cache[$key])) {
            $data = self::$cache[$key];
            if ($data['expires'] > time()) {
                return $data['value'];
            }
            unset(self::$cache[$key]);
        }
        
        // Verificar caché de WordPress
        $cached = wp_cache_get($key, 'wvp');
        if ($cached !== false) {
            self::$cache[$key] = array(
                'value' => $cached,
                'expires' => time() + self::$cache_duration
            );
            return $cached;
        }
        
        return null;
    }
    
    /**
     * Guardar en caché
     * 
     * @param string $key Clave del caché
     * @param mixed $value Valor a guardar
     * @param int $duration Duración en segundos
     * @return bool True si se guardó
     */
    public static function set($key, $value, $duration = null) {
        $duration = $duration ?: self::$cache_duration;
        
        // Guardar en caché local
        self::$cache[$key] = array(
            'value' => $value,
            'expires' => time() + $duration
        );
        
        // Guardar en caché de WordPress
        return wp_cache_set($key, $value, 'wvp', $duration);
    }
    
    /**
     * Eliminar del caché
     * 
     * @param string $key Clave del caché
     * @return bool True si se eliminó
     */
    public static function delete($key) {
        unset(self::$cache[$key]);
        return wp_cache_delete($key, 'wvp');
    }
    
    /**
     * Limpiar todo el caché
     */
    public static function flush() {
        self::$cache = array();
        wp_cache_flush_group('wvp');
    }
}
```

#### Caché de Transients
```php
class WVP_Transient_Cache {
    
    /**
     * Obtener transient
     * 
     * @param string $key Clave del transient
     * @return mixed Valor o false
     */
    public static function get($key) {
        return get_transient("wvp_{$key}");
    }
    
    /**
     * Guardar transient
     * 
     * @param string $key Clave del transient
     * @param mixed $value Valor a guardar
     * @param int $duration Duración en segundos
     * @return bool True si se guardó
     */
    public static function set($key, $value, $duration = 3600) {
        return set_transient("wvp_{$key}", $value, $duration);
    }
    
    /**
     * Eliminar transient
     * 
     * @param string $key Clave del transient
     * @return bool True si se eliminó
     */
    public static function delete($key) {
        return delete_transient("wvp_{$key}");
    }
}
```

### Caché de BCV
```php
class WVP_BCV_Cache {
    
    private static $cache_duration = 1800; // 30 minutos
    
    /**
     * Obtener tasa BCV del caché
     * 
     * @return float|null Tasa BCV o null
     */
    public static function get_rate() {
        $cached_rate = WVP_Transient_Cache::get('bcv_rate');
        
        if ($cached_rate !== false) {
            return floatval($cached_rate);
        }
        
        // Obtener de la base de datos
        $rate = WVP_BCV_Integrator::get_rate_from_db();
        
        if ($rate !== null) {
            WVP_Transient_Cache::set('bcv_rate', $rate, self::$cache_duration);
        }
        
        return $rate;
    }
    
    /**
     * Invalidar caché de BCV
     */
    public static function invalidate() {
        WVP_Transient_Cache::delete('bcv_rate');
        WVP_Transient_Cache::delete('bcv_last_update');
    }
    
    /**
     * Actualizar caché de BCV
     * 
     * @param float $rate Nueva tasa
     */
    public static function update($rate) {
        WVP_Transient_Cache::set('bcv_rate', $rate, self::$cache_duration);
        WVP_Transient_Cache::set('bcv_last_update', current_time('mysql'), self::$cache_duration);
    }
}
```

## Optimización de Base de Datos

### Consultas Optimizadas

#### Consultas Preparadas
```php
class WVP_Database_Optimizer {
    
    /**
     * Obtener órdenes recientes optimizado
     * 
     * @param int $limit Límite de resultados
     * @param string $status Estado de la orden
     * @return array Órdenes
     */
    public static function get_recent_orders($limit = 10, $status = '') {
        global $wpdb;
        
        $where_clause = '';
        $args = array();
        
        if (!empty($status)) {
            $where_clause = 'AND post_status = %s';
            $args[] = $status;
        }
        
        $args[] = $limit;
        
        $query = $wpdb->prepare("
            SELECT ID, post_title, post_date, post_status
            FROM {$wpdb->posts}
            WHERE post_type = 'shop_order'
            {$where_clause}
            ORDER BY post_date DESC
            LIMIT %d
        ", $args);
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Obtener estadísticas de ventas optimizado
     * 
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array Estadísticas
     */
    public static function get_sales_stats($start_date, $end_date) {
        global $wpdb;
        
        $query = $wpdb->prepare("
            SELECT 
                COUNT(*) as total_orders,
                SUM(CAST(meta_value AS DECIMAL(10,2))) as total_sales
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND pm.meta_key = '_order_total'
            AND p.post_date BETWEEN %s AND %s
        ", $start_date, $end_date);
        
        return $wpdb->get_row($query);
    }
    
    /**
     * Obtener productos más vendidos optimizado
     * 
     * @param int $limit Límite de resultados
     * @return array Productos
     */
    public static function get_best_selling_products($limit = 10) {
        global $wpdb;
        
        $query = $wpdb->prepare("
            SELECT 
                p.ID,
                p.post_title,
                SUM(CAST(meta_value AS SIGNED)) as total_sales
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'product'
            AND pm.meta_key = '_total_sales'
            GROUP BY p.ID
            ORDER BY total_sales DESC
            LIMIT %d
        ", $limit);
        
        return $wpdb->get_results($query);
    }
}
```

#### Índices de Base de Datos
```sql
-- Índices para optimizar consultas del plugin
CREATE INDEX idx_wvp_logs_timestamp ON wp_wvp_logs(timestamp);
CREATE INDEX idx_wvp_logs_level ON wp_wvp_logs(level);
CREATE INDEX idx_wvp_security_logs_timestamp ON wp_wvp_security_logs(timestamp);
CREATE INDEX idx_wvp_security_logs_event_type ON wp_wvp_security_logs(event_type);
CREATE INDEX idx_wvp_security_logs_ip ON wp_wvp_security_logs(ip_address);

-- Índices para WooCommerce
CREATE INDEX idx_shop_order_date ON wp_posts(post_date) WHERE post_type = 'shop_order';
CREATE INDEX idx_shop_order_status ON wp_posts(post_status) WHERE post_type = 'shop_order';
CREATE INDEX idx_product_sales ON wp_postmeta(meta_value) WHERE meta_key = '_total_sales';
```

### Optimización de Consultas
```php
class WVP_Query_Optimizer {
    
    /**
     * Optimizar consulta de productos
     * 
     * @param array $args Argumentos de la consulta
     * @return WP_Query Query optimizada
     */
    public static function optimize_product_query($args) {
        $defaults = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Añadir caché a la consulta
        $args['cache_results'] = true;
        $args['update_post_meta_cache'] = true;
        $args['update_post_term_cache'] = true;
        
        // Optimizar para productos
        $args['meta_query'][] = array(
            'key' => '_stock_status',
            'value' => 'instock',
            'compare' => '='
        );
        
        return new WP_Query($args);
    }
    
    /**
     * Optimizar consulta de órdenes
     * 
     * @param array $args Argumentos de la consulta
     * @return WP_Query Query optimizada
     */
    public static function optimize_order_query($args) {
        $defaults = array(
            'post_type' => 'shop_order',
            'post_status' => 'any',
            'posts_per_page' => 20,
            'meta_query' => array()
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Añadir caché a la consulta
        $args['cache_results'] = true;
        $args['update_post_meta_cache'] = false; // No necesitamos meta de órdenes
        $args['update_post_term_cache'] = false;
        
        return new WP_Query($args);
    }
}
```

## Optimización de Assets

### Minificación de CSS
```php
class WVP_CSS_Optimizer {
    
    /**
     * Minificar CSS
     * 
     * @param string $css CSS a minificar
     * @return string CSS minificado
     */
    public static function minify($css) {
        // Remover comentarios
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remover espacios innecesarios
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remover espacios alrededor de caracteres especiales
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
        
        // Remover espacios al inicio y final
        $css = trim($css);
        
        return $css;
    }
    
    /**
     * Combinar archivos CSS
     * 
     * @param array $files Archivos CSS
     * @return string CSS combinado
     */
    public static function combine($files) {
        $combined = '';
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $combined .= file_get_contents($file) . "\n";
            }
        }
        
        return self::minify($combined);
    }
    
    /**
     * Generar CSS crítico
     * 
     * @return string CSS crítico
     */
    public static function get_critical_css() {
        $critical_css = '
            .wvp-price-display { display: block; }
            .wvp-igtf-info { margin: 10px 0; }
            .wvp-payment-methods { list-style: none; }
            .wvp-payment-methods li { margin: 5px 0; }
        ';
        
        return self::minify($critical_css);
    }
}
```

### Minificación de JavaScript
```php
class WVP_JS_Optimizer {
    
    /**
     * Minificar JavaScript
     * 
     * @param string $js JavaScript a minificar
     * @return string JavaScript minificado
     */
    public static function minify($js) {
        // Remover comentarios de línea
        $js = preg_replace('/\/\/.*$/m', '', $js);
        
        // Remover comentarios de bloque
        $js = preg_replace('/\/\*.*?\*\//s', '', $js);
        
        // Remover espacios innecesarios
        $js = preg_replace('/\s+/', ' ', $js);
        
        // Remover espacios alrededor de operadores
        $js = preg_replace('/\s*([=+\-*\/<>!&|])\s*/', '$1', $js);
        
        return trim($js);
    }
    
    /**
     * Combinar archivos JavaScript
     * 
     * @param array $files Archivos JavaScript
     * @return string JavaScript combinado
     */
    public static function combine($files) {
        $combined = '';
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $combined .= file_get_contents($file) . ";\n";
            }
        }
        
        return self::minify($combined);
    }
}
```

## Optimización de Código

### Lazy Loading
```php
class WVP_Lazy_Loader {
    
    private static $loaded_components = array();
    
    /**
     * Cargar componente de forma diferida
     * 
     * @param string $component_name Nombre del componente
     * @return object Instancia del componente
     */
    public static function load_component($component_name) {
        if (!isset(self::$loaded_components[$component_name])) {
            $class_name = "WVP_{$component_name}";
            
            if (class_exists($class_name)) {
                self::$loaded_components[$component_name] = new $class_name();
            } else {
                self::$loaded_components[$component_name] = null;
            }
        }
        
        return self::$loaded_components[$component_name];
    }
    
    /**
     * Cargar componente solo cuando sea necesario
     * 
     * @param string $component_name Nombre del componente
     * @param callable $condition Condición para cargar
     * @return object Instancia del componente
     */
    public static function load_on_condition($component_name, $condition) {
        if (call_user_func($condition)) {
            return self::load_component($component_name);
        }
        
        return null;
    }
}
```

### Optimización de Algoritmos
```php
class WVP_Algorithm_Optimizer {
    
    /**
     * Calcular IGTF optimizado
     * 
     * @param float $amount Cantidad base
     * @param float $rate Tasa de IGTF
     * @return float Cantidad de IGTF
     */
    public static function calculate_igtf_optimized($amount, $rate) {
        // Usar multiplicación directa en lugar de división
        return $amount * ($rate / 100);
    }
    
    /**
     * Convertir moneda optimizado
     * 
     * @param float $amount Cantidad
     * @param float $rate Tasa de cambio
     * @return float Cantidad convertida
     */
    public static function convert_currency_optimized($amount, $rate) {
        // Usar multiplicación directa
        return $amount * $rate;
    }
    
    /**
     * Formatear precio optimizado
     * 
     * @param float $amount Cantidad
     * @param string $currency Moneda
     * @return string Precio formateado
     */
    public static function format_price_optimized($amount, $currency = 'VES') {
        // Usar sprintf para mejor rendimiento
        $formatted = sprintf('%.2f', $amount);
        $formatted = str_replace('.', ',', $formatted);
        
        switch ($currency) {
            case 'VES':
                return "Bs. {$formatted}";
            case 'USD':
                return "\${$formatted}";
            default:
                return "{$formatted} {$currency}";
        }
    }
}
```

## Optimización de Red

### Reducción de Requests
```php
class WVP_Network_Optimizer {
    
    /**
     * Combinar requests de API
     * 
     * @param array $requests Requests a combinar
     * @return array Respuestas combinadas
     */
    public static function combine_api_requests($requests) {
        $responses = array();
        
        // Usar cURL multi para requests paralelos
        $multi_handle = curl_multi_init();
        $curl_handles = array();
        
        foreach ($requests as $key => $request) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request['url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            if (isset($request['headers'])) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $request['headers']);
            }
            
            curl_multi_add_handle($multi_handle, $ch);
            $curl_handles[$key] = $ch;
        }
        
        // Ejecutar requests
        $running = null;
        do {
            curl_multi_exec($multi_handle, $running);
            curl_multi_select($multi_handle);
        } while ($running > 0);
        
        // Obtener respuestas
        foreach ($curl_handles as $key => $ch) {
            $responses[$key] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($multi_handle, $ch);
            curl_close($ch);
        }
        
        curl_multi_close($multi_handle);
        
        return $responses;
    }
    
    /**
     * Cachear requests de API
     * 
     * @param string $url URL de la API
     * @param int $cache_duration Duración del caché
     * @return mixed Respuesta de la API
     */
    public static function cached_api_request($url, $cache_duration = 3600) {
        $cache_key = 'api_' . md5($url);
        $cached_response = WVP_Transient_Cache::get($cache_key);
        
        if ($cached_response !== false) {
            return $cached_response;
        }
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'WooCommerce Venezuela Pro'
            )
        ));
        
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            WVP_Transient_Cache::set($cache_key, $body, $cache_duration);
            return $body;
        }
        
        return null;
    }
}
```

## Monitoreo de Rendimiento

### Métricas de Rendimiento
```php
class WVP_Performance_Monitor {
    
    private static $start_time;
    private static $start_memory;
    
    /**
     * Iniciar monitoreo
     */
    public static function start_monitoring() {
        self::$start_time = microtime(true);
        self::$start_memory = memory_get_usage();
    }
    
    /**
     * Finalizar monitoreo
     * 
     * @return array Métricas de rendimiento
     */
    public static function end_monitoring() {
        $end_time = microtime(true);
        $end_memory = memory_get_usage();
        
        return array(
            'execution_time' => $end_time - self::$start_time,
            'memory_used' => $end_memory - self::$start_memory,
            'peak_memory' => memory_get_peak_usage(),
            'database_queries' => get_num_queries()
        );
    }
    
    /**
     * Obtener métricas de rendimiento
     * 
     * @return array Métricas
     */
    public static function get_performance_metrics() {
        return array(
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'database_queries' => get_num_queries(),
            'cache_hit_rate' => self::get_cache_hit_rate(),
            'bcv_rate_age' => self::get_bcv_rate_age()
        );
    }
    
    /**
     * Obtener tasa de aciertos del caché
     * 
     * @return float Tasa de aciertos
     */
    private static function get_cache_hit_rate() {
        $hits = get_option('wvp_cache_hits', 0);
        $misses = get_option('wvp_cache_misses', 0);
        
        if ($hits + $misses === 0) {
            return 0;
        }
        
        return $hits / ($hits + $misses);
    }
    
    /**
     * Obtener edad de la tasa BCV
     * 
     * @return int Edad en segundos
     */
    private static function get_bcv_rate_age() {
        $last_update = get_option('wvp_bcv_last_update');
        if (!$last_update) {
            return 0;
        }
        
        return time() - strtotime($last_update);
    }
}
```

### Optimización Automática
```php
class WVP_Auto_Optimizer {
    
    /**
     * Optimizar automáticamente
     */
    public static function auto_optimize() {
        // Limpiar caché antiguo
        self::cleanup_old_cache();
        
        // Optimizar base de datos
        self::optimize_database();
        
        // Limpiar logs antiguos
        self::cleanup_old_logs();
        
        // Optimizar transients
        self::optimize_transients();
    }
    
    /**
     * Limpiar caché antiguo
     */
    private static function cleanup_old_cache() {
        global $wpdb;
        
        // Limpiar transients expirados
        $wpdb->query("
            DELETE FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_timeout_wvp_%'
            AND option_value < UNIX_TIMESTAMP()
        ");
        
        $wpdb->query("
            DELETE FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_wvp_%'
            AND option_name NOT IN (
                SELECT CONCAT('_transient_', SUBSTRING(option_name, 12))
                FROM {$wpdb->options}
                WHERE option_name LIKE '_transient_timeout_wvp_%'
            )
        ");
    }
    
    /**
     * Optimizar base de datos
     */
    private static function optimize_database() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'wvp_logs',
            $wpdb->prefix . 'wvp_stats',
            $wpdb->prefix . 'wvp_error_logs',
            $wpdb->prefix . 'wvp_security_logs'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("OPTIMIZE TABLE $table");
        }
    }
    
    /**
     * Limpiar logs antiguos
     */
    private static function cleanup_old_logs() {
        global $wpdb;
        
        $retention_days = get_option('wvp_log_retention_days', 30);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        $wpdb->query($wpdb->prepare("
            DELETE FROM {$wpdb->prefix}wvp_error_logs
            WHERE timestamp < %s
        ", $cutoff_date));
        
        $wpdb->query($wpdb->prepare("
            DELETE FROM {$wpdb->prefix}wvp_security_logs
            WHERE timestamp < %s
        ", $cutoff_date));
    }
    
    /**
     * Optimizar transients
     */
    private static function optimize_transients() {
        global $wpdb;
        
        // Limpiar transients huérfanos
        $wpdb->query("
            DELETE FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_wvp_%'
            AND option_name NOT IN (
                SELECT CONCAT('_transient_', SUBSTRING(option_name, 12))
                FROM {$wpdb->options}
                WHERE option_name LIKE '_transient_timeout_wvp_%'
            )
        ");
    }
}
```

## Configuración de Rendimiento

### Configuraciones Recomendadas

#### wp-config.php
```php
// Configuraciones de rendimiento
define('WP_CACHE', true);
define('COMPRESS_CSS', true);
define('COMPRESS_SCRIPTS', true);
define('ENFORCE_GZIP', true);
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// Configuraciones de base de datos
define('WP_ALLOW_REPAIR', false);
define('WP_POST_REVISIONS', 3);
define('AUTOSAVE_INTERVAL', 300);
define('WP_CRON_LOCK_TIMEOUT', 60);
```

#### Configuración del Plugin
```php
// Configuraciones de rendimiento del plugin
$performance_settings = array(
    'cache_enabled' => true,
    'cache_duration' => 3600,
    'minify_css' => true,
    'minify_js' => true,
    'lazy_loading' => true,
    'database_optimization' => true,
    'log_retention_days' => 30,
    'auto_cleanup' => true
);
```

## Conclusión

La optimización de rendimiento del plugin WooCommerce Venezuela Pro incluye:

- ✅ **Sistema de caché**: Multi-nivel e inteligente
- ✅ **Optimización de DB**: Consultas preparadas e índices
- ✅ **Minificación**: CSS y JavaScript
- ✅ **Lazy loading**: Carga diferida de componentes
- ✅ **Optimización de red**: Requests combinados
- ✅ **Monitoreo**: Métricas de rendimiento
- ✅ **Auto-optimización**: Limpieza automática
- ✅ **Configuración**: Ajustes de rendimiento

Esta implementación asegura un rendimiento óptimo del plugin en producción.
