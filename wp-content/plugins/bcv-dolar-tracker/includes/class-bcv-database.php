<?php
/**
 * Clase para gestión de base de datos del plugin BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class BCV_Database {
    
    /**
     * Instancia singleton
     * 
     * @var BCV_Database
     */
    private static $instance = null;
    
    /**
     * Nombre de la tabla de precios del dólar
     * 
     * @var string
     */
    private $table_name;
    
    /**
     * Versión actual de la base de datos
     * 
     * @var string
     */
    private $db_version = '1.1.0';
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'bcv_precio_dolar';
        
        // Hook para ejecutar migraciones si es necesario
        add_action('plugins_loaded', array($this, 'check_db_version'));
    }
    
    /**
     * Obtener instancia singleton
     * 
     * @return BCV_Database
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Crear tabla de precios del dólar
     * 
     * @return bool True si se creó correctamente, False en caso contrario
     */
    public function create_price_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // SQL para crear la tabla
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            datatime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            precio decimal(10,4) NOT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_datatime (datatime),
            KEY idx_precio (precio),
            KEY idx_created_at (created_at),
            KEY idx_datatime_precio (datatime, precio),
            KEY idx_precio_datatime (precio, datatime),
            KEY idx_latest_prices (datatime DESC, id DESC)
        ) {$charset_collate};";
        
        // Usar dbDelta para crear la tabla
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        // Verificar si se creó correctamente
        if (empty($wpdb->last_error)) {
            // Actualizar versión de la base de datos
            update_option('bcv_db_version', $this->db_version);
            
            // Log de éxito
            error_log('BCV Dólar Tracker: Tabla de precios creada correctamente');
            return true;
        } else {
            // Log de error
            error_log('BCV Dólar Tracker: Error al crear tabla: ' . $wpdb->last_error);
            return false;
        }
    }
    
    /**
     * Obtener versión actual de la base de datos
     * 
     * @return string Versión de la base de datos
     */
    public function get_db_version() {
        return get_option('bcv_db_version', '0.0.0');
    }
    
    /**
     * Verificar si la tabla existe
     * 
     * @return bool True si la tabla existe, False en caso contrario
     */
    public function table_exists() {
        global $wpdb;
        
        $table_name = $this->table_name;
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )
        );
        
        return $result === $table_name;
    }
    
    /**
     * Verificar versión de la base de datos y ejecutar migraciones si es necesario
     */
    public function check_db_version() {
        $current_version = $this->get_db_version();
        
        if (version_compare($current_version, $this->db_version, '<')) {
            $this->run_migrations($current_version);
        }
    }
    
    /**
     * Ejecutar migraciones si es necesario
     * 
     * @param string $from_version Versión desde la cual migrar
     * @return bool True si se ejecutaron correctamente, False en caso contrario
     */
    public function run_migrations($from_version) {
        global $wpdb;
        
        // Log del inicio de migración
        error_log("BCV Dólar Tracker: Iniciando migración desde versión {$from_version} a {$this->db_version}");
        
        // Si la tabla no existe, crearla
        if (!$this->table_exists()) {
            $result = $this->create_price_table();
            if (!$result) {
                error_log('BCV Dólar Tracker: Error en migración - No se pudo crear la tabla');
                return false;
            }
        }
        
        // Ejecutar migraciones específicas por versión
        $migrations = $this->get_migrations();
        
        foreach ($migrations as $version => $migration) {
            if (version_compare($from_version, $version, '<')) {
                $result = $this->execute_migration($version, $migration);
                if (!$result) {
                    error_log("BCV Dólar Tracker: Error en migración versión {$version}");
                    return false;
                }
            }
        }
        
        // Actualizar versión de la base de datos
        update_option('bcv_db_version', $this->db_version);
        
        error_log("BCV Dólar Tracker: Migración completada exitosamente a versión {$this->db_version}");
        return true;
    }
    
    /**
     * Obtener lista de migraciones disponibles
     * 
     * @return array Array de migraciones con versión como clave
     */
    private function get_migrations() {
        return array(
            '1.0.0' => array(
                'description' => 'Crear tabla inicial de precios del dólar',
                'method' => 'create_initial_table'
            ),
            '1.1.0' => array(
                'description' => 'Crear tabla de logs para sistema de depuración',
                'method' => 'create_logs_table'
            )
        );
    }
    
    /**
     * Ejecutar una migración específica
     * 
     * @param string $version Versión de la migración
     * @param array $migration Datos de la migración
     * @return bool True si se ejecutó correctamente, False en caso contrario
     */
    private function execute_migration($version, $migration) {
        $method = $migration['method'];
        
        if (method_exists($this, $method)) {
            $result = $this->$method();
            if ($result) {
                error_log("BCV Dólar Tracker: Migración {$version} ejecutada correctamente");
                return true;
            } else {
                error_log("BCV Dólar Tracker: Error al ejecutar migración {$version}");
                return false;
            }
        } else {
            error_log("BCV Dólar Tracker: Método de migración {$method} no encontrado");
            return false;
        }
    }
    
    /**
     * Migración inicial - Crear tabla
     * 
     * @return bool True si se creó correctamente, False en caso contrario
     */
    private function create_initial_table() {
        return $this->create_price_table();
    }
    
    /**
     * Crear tabla de logs para sistema de depuración
     * 
     * @return bool True si se creó correctamente, False en caso contrario
     */
    private function create_logs_table() {
        global $wpdb;
        
        $logs_table_name = $wpdb->prefix . 'bcv_logs';
        $charset_collate = $wpdb->get_charset_collate();
        
        // SQL para crear la tabla de logs
        $sql = "CREATE TABLE {$logs_table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            log_level varchar(20) NOT NULL DEFAULT 'INFO',
            context varchar(100) NOT NULL DEFAULT '',
            message text NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_log_level (log_level),
            KEY idx_context (context),
            KEY idx_user_id (user_id),
            KEY idx_created_at (created_at),
            KEY idx_created_at_level (created_at, log_level)
        ) {$charset_collate};";
        
        // Usar dbDelta para crear la tabla
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        // Verificar si se creó correctamente
        if (empty($wpdb->last_error)) {
            error_log('BCV Dólar Tracker: Tabla de logs creada correctamente');
            return true;
        } else {
            error_log('BCV Dólar Tracker: Error al crear tabla de logs: ' . $wpdb->last_error);
            return false;
        }
    }
    
    /**
     * Insertar precio del dólar en la base de datos con lógica inteligente
     * 
     * @param float $precio Precio del dólar
     * @param string $datatime Fecha y hora del precio (opcional, por defecto ahora)
     * @return int|false ID del registro insertado o false si falla
     */
    public function insert_price($precio, $datatime = null) {
        global $wpdb;
        
        // Sanitizar y validar precio usando la clase de seguridad
        $precio = BCV_Security::sanitize_number($precio);
        
        // Validar precio
        if (!is_numeric($precio) || $precio <= 0) {
            BCV_Logger::error('Precio inválido para insertar', array('precio' => $precio));
            BCV_Security::log_security_event('Invalid price insertion attempt', 'Price: ' . $precio);
            return false;
        }
        
        // Validar rango de precio (máximo 1000 USD por razones de seguridad)
        if ($precio > 1000) {
            BCV_Logger::warning('Precio fuera de rango válido', array('precio' => $precio, 'max_allowed' => 1000));
            BCV_Security::log_security_event('Price out of range', 'Price: ' . $precio . ', Max: 1000');
            return false;
        }
        
        // Usar fecha actual si no se proporciona
        if ($datatime === null) {
            $datatime = current_time('mysql');
        } else {
            // Sanitizar fecha si se proporciona
            $datatime = BCV_Security::sanitize_text($datatime);
        }
        
        // Validar formato de fecha si se proporciona
        if ($datatime !== null && !$this->validate_datetime($datatime)) {
            BCV_Logger::error('Formato de fecha inválido', array('datatime' => $datatime));
            BCV_Security::log_security_event('Invalid datetime format', 'DateTime: ' . $datatime);
            return false;
        }
        
        // ===== LÓGICA DE ALMACENAMIENTO INTELIGENTE =====
        
        // Paso 1: Obtener el último registro de la base de datos
        $ultimo_registro = $this->get_latest_price();
        
        // Paso 2: Aplicar lógica condicional
        $debe_guardar = false;
        $razon = '';
        
        if (!$ultimo_registro) {
            // No hay registros previos, guardar siempre
            $debe_guardar = true;
            $razon = 'Primer registro en la base de datos';
        } else {
            $precio_anterior = floatval($ultimo_registro->precio);
            $fecha_anterior = $ultimo_registro->datatime;
            $fecha_anterior_dia = date('Y-m-d', strtotime($fecha_anterior));
            $fecha_hoy = date('Y-m-d');
            
            if ($precio != $precio_anterior) {
                // El precio cambió, guardar siempre
                $debe_guardar = true;
                $razon = "Precio cambió de {$precio_anterior} a {$precio}";
            } elseif ($fecha_anterior_dia < $fecha_hoy) {
                // El precio es igual pero es un día diferente, guardar
                $debe_guardar = true;
                $razon = "Mismo precio ({$precio}) pero nuevo día ({$fecha_hoy})";
            } else {
                // El precio es igual y es el mismo día, no guardar
                $debe_guardar = false;
                $razon = "Precio igual ({$precio}) y mismo día ({$fecha_hoy}), evitando duplicado";
            }
        }
        
        // Log de la decisión
        error_log("BCV Dólar Tracker: Decisión de almacenamiento - {$razon}");
        
        if (!$debe_guardar) {
            error_log("BCV Dólar Tracker: No se guardará el precio - {$razon}");
            return 'skipped'; // Retornar 'skipped' para indicar que no se guardó por lógica inteligente
        }
        
        // Paso 3: Guardar el nuevo registro
        $data = array(
            'datatime' => $datatime,
            'precio' => $precio  // Mantener el valor exacto sin redondear
        );
        
        // Formato de datos para wpdb
        $format = array('%s', '%f');
        
        // Log de inserción
        error_log("BCV Dólar Tracker: Insertando precio: {$precio} en fecha: {$datatime} - {$razon}");
        
        // Insertar en la base de datos
        $result = $wpdb->insert($this->table_name, $data, $format);
        
        if ($result === false) {
            error_log('BCV Dólar Tracker: Error al insertar precio: ' . $wpdb->last_error);
            error_log('BCV Dólar Tracker: Datos que se intentaron insertar: ' . print_r($data, true));
            
            // Registrar error en base de datos
            BCV_Logger::error(BCV_Logger::CONTEXT_DATABASE, 'Error al insertar precio en base de datos: ' . $wpdb->last_error);
            
            return false;
        }
        
        $insert_id = $wpdb->insert_id;
        error_log("BCV Dólar Tracker: Precio insertado exitosamente con ID: {$insert_id} - {$razon}");
        
        // Registrar éxito en base de datos
        BCV_Logger::success(BCV_Logger::CONTEXT_DATABASE, "Nueva tasa {$precio} Bs. guardada en base de datos (ID: {$insert_id})");
        
        // Invalidar caché de estadísticas
        delete_transient('bcv_price_stats');
        
        // Actualizar opción para integración con WooCommerce Venezuela Pro
        $this->update_wvp_integration($precio);
        
        // Limpieza automática de registros antiguos
        $this->auto_cleanup_old_records();
        
        // Verificar que realmente se insertó
        $verification = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $insert_id
        ));
        
        if ($verification) {
            error_log("BCV Dólar Tracker: Verificación exitosa - Registro encontrado en BD");
            error_log("BCV Dólar Tracker: Datos guardados - ID: {$verification->id}, Precio: {$verification->precio}, Fecha: {$verification->datatime}");
        } else {
            error_log("BCV Dólar Tracker: ADVERTENCIA - Registro no encontrado después de inserción");
        }
        
        return $insert_id;
    }
    
    /**
     * Obtener precios del dólar con paginación y filtros
     * 
     * @param array $args Argumentos de consulta
     * @return array Array con datos y paginación
     */
    public function get_prices($args = array()) {
        global $wpdb;
        
        // Argumentos por defecto
        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'orderby' => 'datatime',
            'order' => 'DESC',
            'search' => '',
            'date_from' => '',
            'date_to' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Sanitizar y validar argumentos
        $args['per_page'] = BCV_Security::validate_number_range($args['per_page'], 1, 100);
        $args['page'] = BCV_Security::validate_number_range($args['page'], 1, 1000);
        $args['search'] = BCV_Security::sanitize_text($args['search']);
        $args['date_from'] = BCV_Security::sanitize_text($args['date_from']);
        $args['date_to'] = BCV_Security::sanitize_text($args['date_to']);
        
        // Validar orderby y order para prevenir inyección SQL
        $allowed_orderby = array('datatime', 'precio', 'id');
        $allowed_order = array('ASC', 'DESC');
        
        if (!in_array($args['orderby'], $allowed_orderby)) {
            $args['orderby'] = 'datatime';
        }
        
        if (!in_array(strtoupper($args['order']), $allowed_order)) {
            $args['order'] = 'DESC';
        }
        
        // Construir consulta WHERE
        $where_clauses = array();
        $where_values = array();
        
        if (!empty($args['search'])) {
            $where_clauses[] = 'precio LIKE %s';
            $where_values[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }
        
        if (!empty($args['date_from']) && BCV_Security::validate_date($args['date_from'])) {
            $where_clauses[] = 'datatime >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if (!empty($args['date_to']) && BCV_Security::validate_date($args['date_to'])) {
            $where_clauses[] = 'datatime <= %s';
            $where_values[] = $args['date_to'];
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }
        
        // Consulta para contar total de registros
        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} {$where_sql}";
        if (!empty($where_values)) {
            $count_sql = $wpdb->prepare($count_sql, $where_values);
        }
        
        $total_items = $wpdb->get_var($count_sql);
        
        // Calcular offset para paginación
        $offset = ($args['page'] - 1) * $args['per_page'];
        
        // Consulta principal
        $sql = "SELECT * FROM {$this->table_name} {$where_sql} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d";
        
        $query_values = array_merge($where_values, array($args['per_page'], $offset));
        $sql = $wpdb->prepare($sql, $query_values);
        
        $items = $wpdb->get_results($sql);
        
        return array(
            'items' => $items,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $args['per_page']),
            'current_page' => $args['page'],
            'per_page' => $args['per_page']
        );
    }
    
    /**
     * Obtener el precio más reciente del dólar
     * 
     * @return object|false Objeto con datos del precio o false si no hay datos
     */
    public function get_latest_price() {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table_name} ORDER BY datatime DESC LIMIT 1";
        return $wpdb->get_row($sql);
    }
    
    /**
     * Obtener estadísticas básicas de precios
     * 
     * @param bool $force_refresh Forzar recálculo de estadísticas
     * @return array Array con estadísticas
     */
    public function get_price_stats($force_refresh = false) {
        global $wpdb;
        
        // Verificar si la tabla existe
        if (!$this->table_exists()) {
            return array(
                'total_records' => 0,
                'min_price' => 0,
                'max_price' => 0,
                'avg_price' => 0,
                'first_date' => null,
                'last_date' => null,
                'last_price' => 0
            );
        }
        
        // Intentar obtener desde caché (válido por 15 minutos)
        $cache_key = 'bcv_price_stats';
        if (!$force_refresh) {
            $cached_stats = get_transient($cache_key);
            if ($cached_stats !== false) {
                return $cached_stats;
            }
        }
        
        // Consulta optimizada que obtiene todo en una sola query
        $sql = "SELECT 
                    COUNT(*) as total_records,
                    MIN(precio) as min_price,
                    MAX(precio) as max_price,
                    AVG(precio) as avg_price,
                    MIN(datatime) as first_date,
                    MAX(datatime) as last_date,
                    (SELECT precio FROM {$this->table_name} ORDER BY datatime DESC LIMIT 1) as last_price
                FROM {$this->table_name}";
        
        $stats = $wpdb->get_row($sql, ARRAY_A);
        
        if ($stats && $stats['total_records'] > 0) {
            $stats['last_price'] = $stats['last_price'] ? floatval($stats['last_price']) : 0;
            $stats['min_price'] = floatval($stats['min_price']);
            $stats['max_price'] = floatval($stats['max_price']);
            $stats['avg_price'] = floatval($stats['avg_price']);
        } else {
            // Si no hay registros, establecer valores por defecto
            $stats = array(
                'total_records' => 0,
                'min_price' => 0,
                'max_price' => 0,
                'avg_price' => 0,
                'first_date' => null,
                'last_date' => null,
                'last_price' => 0
            );
        }
        
        // Guardar en caché por 15 minutos
        set_transient($cache_key, $stats, 900);
        
        return $stats;
    }
    
    /**
     * Limpiar registros antiguos (mantener solo los últimos X días)
     * 
     * @param int $days Número de días a mantener
     * @return int|false Número de registros eliminados o false si falla
     */
    public function cleanup_old_records($days = 30) {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Primero contar cuántos registros se van a eliminar
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE datatime < %s",
            $cutoff_date
        ));
        
        if ($count > 0) {
            error_log("BCV Dólar Tracker: Limpiando {$count} registros antiguos (anteriores a {$cutoff_date})");
        }
        
        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE datatime < %s",
                $cutoff_date
            )
        );
        
        if ($result === false) {
            error_log('BCV Dólar Tracker: Error al limpiar registros antiguos: ' . $wpdb->last_error);
            return false;
        }
        
        if ($result > 0) {
            error_log("BCV Dólar Tracker: {$result} registros antiguos eliminados exitosamente");
        }
        
        return $result;
    }
    
    /**
     * Limpieza automática de registros antiguos
     * Se ejecuta cada vez que se inserta un nuevo precio
     */
    private function auto_cleanup_old_records() {
        // Solo limpiar si hay más de 1000 registros
        $total_records = $this->get_total_records();
        
        if ($total_records > 1000) {
            $this->cleanup_old_records(90); // Mantener solo 90 días
        }
    }
    
    /**
     * Obtener total de registros en la tabla
     * 
     * @return int Número total de registros
     */
    private function get_total_records() {
        global $wpdb;
        
        return intval($wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}"));
    }
    
    /**
     * Obtener nombre de la tabla
     * 
     * @return string Nombre de la tabla
     */
    public function get_table_name() {
        return $this->table_name;
    }
    
    /**
     * Eliminar precio por ID
     * 
     * @param int $id ID del registro a eliminar
     * @return bool True si se eliminó correctamente, False en caso contrario
     */
    public function delete_price($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            error_log('BCV Dólar Tracker: Error al eliminar precio con ID ' . $id . ': ' . $wpdb->last_error);
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtener precio por ID
     * 
     * @param int $id ID del registro
     * @return object|false Objeto con datos del precio o false si no existe
     */
    public function get_price_by_id($id) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        );
        
        return $wpdb->get_row($sql);
    }
    
    /**
     * Obtener precio anterior al especificado
     * 
     * @param int $id ID del registro actual
     * @return object|false Objeto con datos del precio anterior o false si no existe
     */
    public function get_previous_price($id) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id < %d ORDER BY id DESC LIMIT 1",
            $id
        );
        
        return $wpdb->get_row($sql);
    }
    
    /**
     * Obtener precio siguiente al especificado
     * 
     * @param int $id ID del registro actual
     * @return object|false Objeto con datos del precio siguiente o false si no existe
     */
    public function get_next_price($id) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id > %d ORDER BY id ASC LIMIT 1",
            $id
        );
        
        return $wpdb->get_row($sql);
    }
    
    /**
     * Actualizar precio existente
     * 
     * @param int $id ID del registro a actualizar
     * @param float $precio Nuevo precio
     * @param string $datatime Nueva fecha y hora (opcional)
     * @return bool True si se actualizó correctamente, False en caso contrario
     */
    public function update_price($id, $precio, $datatime = null) {
        global $wpdb;
        
        $data = array('precio' => $precio);
        
        if ($datatime !== null) {
            $data['datatime'] = $datatime;
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id),
            array('%f'),
            array('%d')
        );
        
        if ($result === false) {
            error_log('BCV Dólar Tracker: Error al actualizar precio con ID ' . $id . ': ' . $wpdb->last_error);
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtener precios por rango de fechas
     * 
     * @param string $start_date Fecha de inicio (YYYY-MM-DD)
     * @param string $end_date Fecha de fin (YYYY-MM-DD)
     * @param int $limit Límite de registros (opcional)
     * @return array Array de precios
     */
    public function get_prices_by_date_range($start_date, $end_date, $limit = null) {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table_name} WHERE DATE(datatime) BETWEEN %s AND %s ORDER BY datatime DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT %d";
            $sql = $wpdb->prepare($sql, $start_date, $end_date, $limit);
        } else {
            $sql = $wpdb->prepare($sql, $start_date, $end_date);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Obtener precios del día actual
     * 
     * @return array Array de precios del día
     */
    public function get_today_prices() {
        $today = date('Y-m-d');
        return $this->get_prices_by_date_range($today, $today);
    }
    
    /**
     * Obtener precios de la semana actual
     * 
     * @return array Array de precios de la semana
     */
    public function get_this_week_prices() {
        $start_of_week = date('Y-m-d', strtotime('monday this week'));
        $end_of_week = date('Y-m-d', strtotime('sunday this week'));
        return $this->get_prices_by_date_range($start_of_week, $end_of_week);
    }
    
    /**
     * Obtener precios del mes actual
     * 
     * @return array Array de precios del mes
     */
    public function get_this_month_prices() {
        $start_of_month = date('Y-m-01');
        $end_of_month = date('Y-m-t');
        return $this->get_prices_by_date_range($start_of_month, $end_of_month);
    }
    
    /**
     * Validar formato de fecha y hora
     * 
     * @param string $datetime Fecha a validar
     * @return bool True si es válida, False en caso contrario
     */
    private function validate_datetime($datetime) {
        if (empty($datetime)) {
            return false;
        }
        
        // Intentar crear un objeto DateTime para validar
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        
        // Verificar que la fecha sea válida y coincida con el formato
        if ($date && $date->format('Y-m-d H:i:s') === $datetime) {
            // Verificar que la fecha no sea futura (más de 1 hora)
            $now = new DateTime();
            $now->add(new DateInterval('PT1H')); // Permitir 1 hora de diferencia
            
            if ($date <= $now) {
                return true;
            } else {
                error_log('BCV Dólar Tracker: Fecha futura no permitida: ' . $datetime);
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Actualizar integración con WooCommerce Venezuela Pro
     * 
     * @param float $precio Precio del dólar a actualizar
     */
    private function update_wvp_integration($precio) {
        // Actualizar la opción que usa WooCommerce Venezuela Pro
        $old_rate = get_option('wvp_bcv_rate', 0);
        update_option('wvp_bcv_rate', $precio);
        
        // Log del cambio de precio
        if ($old_rate != $precio) {
            error_log("BCV Dólar Tracker: Precio actualizado en WVP - Anterior: {$old_rate}, Nuevo: {$precio}");
        }
        
        // Disparar hook para notificar a WooCommerce Venezuela Pro
        do_action('wvp_bcv_rate_updated', $precio, $old_rate);
        
        error_log("BCV Dólar Tracker: Hook wvp_bcv_rate_updated disparado con precio: {$precio}");
    }
}
