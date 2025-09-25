<?php
/**
 * Sistema de Tutoriales Interactivos - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar tutoriales interactivos
 */
class WCVS_Tutorials {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Tutoriales disponibles
     *
     * @var array
     */
    private $tutorials = array(
        'currency_setup' => array(
            'title' => 'Configuración de Moneda',
            'description' => 'Aprende a configurar la conversión automática USD a VES',
            'steps' => 5,
            'estimated_time' => '5 minutos'
        ),
        'payment_gateways' => array(
            'title' => 'Pasarelas de Pago',
            'description' => 'Configura las pasarelas de pago locales',
            'steps' => 4,
            'estimated_time' => '8 minutos'
        ),
        'shipping_methods' => array(
            'title' => 'Métodos de Envío',
            'description' => 'Configura los métodos de envío venezolanos',
            'steps' => 3,
            'estimated_time' => '6 minutos'
        ),
        'tax_system' => array(
            'title' => 'Sistema Fiscal',
            'description' => 'Configura los impuestos venezolanos',
            'steps' => 3,
            'estimated_time' => '4 minutos'
        ),
        'electronic_billing' => array(
            'title' => 'Facturación Electrónica',
            'description' => 'Configura la facturación electrónica SENIAT',
            'steps' => 4,
            'estimated_time' => '7 minutos'
        ),
        'notifications' => array(
            'title' => 'Sistema de Notificaciones',
            'description' => 'Configura las notificaciones de tu tienda',
            'steps' => 3,
            'estimated_time' => '5 minutos'
        )
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('tutorials', array());
        $this->init_database();
    }

    /**
     * Inicializar base de datos
     */
    private function init_database() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_tutorial_progress';
        
        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            $this->create_tutorial_progress_table();
        }
    }

    /**
     * Crear tabla de progreso de tutoriales
     */
    private function create_tutorial_progress_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_tutorial_progress';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            tutorial_id varchar(100) NOT NULL,
            current_step int(11) DEFAULT 0,
            completed_steps longtext,
            is_completed tinyint(1) DEFAULT 0,
            started_at datetime DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_tutorial (user_id, tutorial_id),
            KEY user_id (user_id),
            KEY tutorial_id (tutorial_id),
            KEY is_completed (is_completed)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, 'Tabla de progreso de tutoriales creada');
    }

    /**
     * Obtener tutorial por ID
     *
     * @param string $tutorial_id ID del tutorial
     * @return array|false
     */
    public function get_tutorial($tutorial_id) {
        if (!isset($this->tutorials[$tutorial_id])) {
            return false;
        }

        $tutorial = $this->tutorials[$tutorial_id];
        $tutorial['id'] = $tutorial_id;
        $tutorial['steps'] = $this->get_tutorial_steps($tutorial_id);
        
        return $tutorial;
    }

    /**
     * Obtener pasos del tutorial
     *
     * @param string $tutorial_id ID del tutorial
     * @return array
     */
    private function get_tutorial_steps($tutorial_id) {
        switch ($tutorial_id) {
            case 'currency_setup':
                return $this->get_currency_setup_steps();
            case 'payment_gateways':
                return $this->get_payment_gateways_steps();
            case 'shipping_methods':
                return $this->get_shipping_methods_steps();
            case 'tax_system':
                return $this->get_tax_system_steps();
            case 'electronic_billing':
                return $this->get_electronic_billing_steps();
            case 'notifications':
                return $this->get_notifications_steps();
            default:
                return array();
        }
    }

    /**
     * Obtener pasos de configuración de moneda
     */
    private function get_currency_setup_steps() {
        return array(
            array(
                'id' => 1,
                'title' => 'Acceder a la configuración de moneda',
                'description' => 'Ve a WooCommerce > Configuración > Moneda',
                'action' => 'navigate',
                'target' => 'admin.php?page=wc-settings&tab=currency',
                'hint' => 'Busca la sección "WooCommerce Venezuela Suite" en la página de configuración'
            ),
            array(
                'id' => 2,
                'title' => 'Habilitar conversión automática',
                'description' => 'Activa la conversión automática de USD a VES',
                'action' => 'click',
                'target' => '#currency_enabled',
                'hint' => 'Marca la casilla "Habilitar conversión automática"'
            ),
            array(
                'id' => 3,
                'title' => 'Seleccionar estilo de visualización',
                'description' => 'Elige cómo se mostrarán los precios a tus clientes',
                'action' => 'select',
                'target' => '#currency_display_style',
                'hint' => 'Selecciona el estilo que mejor se adapte a tu tienda'
            ),
            array(
                'id' => 4,
                'title' => 'Configurar frecuencia de actualización',
                'description' => 'Establece cada cuánto se actualizará la tasa de cambio',
                'action' => 'select',
                'target' => '#currency_update_interval',
                'hint' => 'Recomendamos actualizar cada 30 minutos'
            ),
            array(
                'id' => 5,
                'title' => 'Guardar configuración',
                'description' => 'Guarda los cambios realizados',
                'action' => 'click',
                'target' => '#submit',
                'hint' => 'Haz clic en "Guardar cambios" para aplicar la configuración'
            )
        );
    }

    /**
     * Obtener pasos de pasarelas de pago
     */
    private function get_payment_gateways_steps() {
        return array(
            array(
                'id' => 1,
                'title' => 'Acceder a la configuración de pagos',
                'description' => 'Ve a WooCommerce > Pagos',
                'action' => 'navigate',
                'target' => 'admin.php?page=wc-settings&tab=checkout',
                'hint' => 'Busca la sección "Pasarelas de pago"'
            ),
            array(
                'id' => 2,
                'title' => 'Habilitar pasarelas locales',
                'description' => 'Activa las pasarelas de pago que deseas usar',
                'action' => 'click',
                'target' => '.wcvs-payment-gateway',
                'hint' => 'Habilita las pasarelas que mejor se adapten a tu negocio'
            ),
            array(
                'id' => 3,
                'title' => 'Configurar parámetros específicos',
                'description' => 'Configura los parámetros de cada pasarela habilitada',
                'action' => 'configure',
                'target' => '.wcvs-payment-settings',
                'hint' => 'Configura los datos específicos de cada pasarela'
            ),
            array(
                'id' => 4,
                'title' => 'Probar métodos de pago',
                'description' => 'Realiza una compra de prueba para verificar el funcionamiento',
                'action' => 'test',
                'target' => 'shop',
                'hint' => 'Realiza una compra de prueba para verificar que todo funcione correctamente'
            )
        );
    }

    /**
     * Obtener pasos de métodos de envío
     */
    private function get_shipping_methods_steps() {
        return array(
            array(
                'id' => 1,
                'title' => 'Acceder a la configuración de envíos',
                'description' => 'Ve a WooCommerce > Envíos',
                'action' => 'navigate',
                'target' => 'admin.php?page=wc-settings&tab=shipping',
                'hint' => 'Busca la sección "Métodos de envío"'
            ),
            array(
                'id' => 2,
                'title' => 'Habilitar métodos de envío locales',
                'description' => 'Activa los métodos de envío que deseas usar',
                'action' => 'click',
                'target' => '.wcvs-shipping-method',
                'hint' => 'Habilita los métodos de envío que mejor se adapten a tu negocio'
            ),
            array(
                'id' => 3,
                'title' => 'Configurar tarifas de envío',
                'description' => 'Establece las tarifas por peso y volumen',
                'action' => 'configure',
                'target' => '.wcvs-shipping-rates',
                'hint' => 'Configura las tarifas según el peso y volumen de tus productos'
            )
        );
    }

    /**
     * Obtener pasos del sistema fiscal
     */
    private function get_tax_system_steps() {
        return array(
            array(
                'id' => 1,
                'title' => 'Acceder a la configuración de impuestos',
                'description' => 'Ve a WooCommerce > Impuestos',
                'action' => 'navigate',
                'target' => 'admin.php?page=wc-settings&tab=tax',
                'hint' => 'Busca la sección "Sistema Fiscal Venezolano"'
            ),
            array(
                'id' => 2,
                'title' => 'Habilitar impuestos venezolanos',
                'description' => 'Activa los impuestos que aplican a tu negocio',
                'action' => 'click',
                'target' => '.wcvs-tax-option',
                'hint' => 'Habilita IVA, IGTF e ISLR según corresponda'
            ),
            array(
                'id' => 3,
                'title' => 'Configurar tasas de impuestos',
                'description' => 'Establece las tasas de impuestos aplicables',
                'action' => 'configure',
                'target' => '.wcvs-tax-rates',
                'hint' => 'Configura las tasas según la normativa venezolana'
            )
        );
    }

    /**
     * Obtener pasos de facturación electrónica
     */
    private function get_electronic_billing_steps() {
        return array(
            array(
                'id' => 1,
                'title' => 'Acceder a la configuración de facturación',
                'description' => 'Ve a WooCommerce > Facturación Electrónica',
                'action' => 'navigate',
                'target' => 'admin.php?page=wcvs-electronic-billing',
                'hint' => 'Busca la sección "Facturación Electrónica"'
            ),
            array(
                'id' => 2,
                'title' => 'Configurar datos de la empresa',
                'description' => 'Ingresa los datos de tu empresa para la facturación',
                'action' => 'configure',
                'target' => '.wcvs-company-data',
                'hint' => 'Ingresa RIF, nombre de la empresa y datos de contacto'
            ),
            array(
                'id' => 3,
                'title' => 'Configurar plantillas de factura',
                'description' => 'Personaliza las plantillas de factura',
                'action' => 'configure',
                'target' => '.wcvs-invoice-templates',
                'hint' => 'Personaliza el diseño y contenido de las facturas'
            ),
            array(
                'id' => 4,
                'title' => 'Configurar integración SENIAT',
                'description' => 'Establece la conexión con SENIAT',
                'action' => 'configure',
                'target' => '.wcvs-seniat-integration',
                'hint' => 'Configura los datos de conexión con SENIAT'
            )
        );
    }

    /**
     * Obtener pasos de notificaciones
     */
    private function get_notifications_steps() {
        return array(
            array(
                'id' => 1,
                'title' => 'Acceder a la configuración de notificaciones',
                'description' => 'Ve a WooCommerce > Notificaciones',
                'action' => 'navigate',
                'target' => 'admin.php?page=wcvs-notifications',
                'hint' => 'Busca la sección "Sistema de Notificaciones"'
            ),
            array(
                'id' => 2,
                'title' => 'Habilitar canales de notificación',
                'description' => 'Activa los canales que deseas usar',
                'action' => 'click',
                'target' => '.wcvs-notification-channel',
                'hint' => 'Habilita email, SMS, push y webhooks según necesites'
            ),
            array(
                'id' => 3,
                'title' => 'Configurar eventos de notificación',
                'description' => 'Establece qué eventos generarán notificaciones',
                'action' => 'configure',
                'target' => '.wcvs-notification-events',
                'hint' => 'Selecciona los eventos que deseas monitorear'
            )
        );
    }

    /**
     * Iniciar tutorial
     *
     * @param string $tutorial_id ID del tutorial
     * @param int $user_id ID del usuario
     * @return array
     */
    public function start_tutorial($tutorial_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $tutorial = $this->get_tutorial($tutorial_id);
        if (!$tutorial) {
            return array('success' => false, 'message' => 'Tutorial no encontrado');
        }

        // Verificar si ya existe progreso
        $progress = $this->get_tutorial_progress($tutorial_id, $user_id);
        if ($progress) {
            return array(
                'success' => true,
                'tutorial' => $tutorial,
                'progress' => $progress,
                'message' => 'Tutorial reanudado'
            );
        }

        // Crear nuevo progreso
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcvs_tutorial_progress';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'tutorial_id' => $tutorial_id,
                'current_step' => 0,
                'completed_steps' => json_encode(array()),
                'is_completed' => 0,
                'started_at' => current_time('mysql')
            )
        );

        if ($result !== false) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, "Tutorial iniciado: {$tutorial_id} para usuario {$user_id}");
            return array(
                'success' => true,
                'tutorial' => $tutorial,
                'progress' => $this->get_tutorial_progress($tutorial_id, $user_id),
                'message' => 'Tutorial iniciado correctamente'
            );
        }

        return array('success' => false, 'message' => 'Error al iniciar tutorial');
    }

    /**
     * Obtener progreso del tutorial
     *
     * @param string $tutorial_id ID del tutorial
     * @param int $user_id ID del usuario
     * @return array|false
     */
    public function get_tutorial_progress($tutorial_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wcvs_tutorial_progress';
        
        $result = $wpdb->get_row($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE user_id = %d AND tutorial_id = %s
        ", $user_id, $tutorial_id), ARRAY_A);

        if ($result) {
            $result['completed_steps'] = json_decode($result['completed_steps'], true);
        }

        return $result;
    }

    /**
     * Completar paso del tutorial
     *
     * @param string $tutorial_id ID del tutorial
     * @param int $step_id ID del paso
     * @param int $user_id ID del usuario
     * @return array
     */
    public function complete_tutorial_step($tutorial_id, $step_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $progress = $this->get_tutorial_progress($tutorial_id, $user_id);
        if (!$progress) {
            return array('success' => false, 'message' => 'Progreso no encontrado');
        }

        $completed_steps = $progress['completed_steps'];
        if (!in_array($step_id, $completed_steps)) {
            $completed_steps[] = $step_id;
        }

        $tutorial = $this->get_tutorial($tutorial_id);
        $is_completed = count($completed_steps) >= count($tutorial['steps']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'wcvs_tutorial_progress';
        
        $update_data = array(
            'current_step' => $step_id,
            'completed_steps' => json_encode($completed_steps),
            'is_completed' => $is_completed ? 1 : 0
        );

        if ($is_completed) {
            $update_data['completed_at'] = current_time('mysql');
        }

        $result = $wpdb->update(
            $table_name,
            $update_data,
            array(
                'user_id' => $user_id,
                'tutorial_id' => $tutorial_id
            )
        );

        if ($result !== false) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, "Paso completado: {$tutorial_id} paso {$step_id} para usuario {$user_id}");
            return array(
                'success' => true,
                'is_completed' => $is_completed,
                'message' => $is_completed ? 'Tutorial completado' : 'Paso completado'
            );
        }

        return array('success' => false, 'message' => 'Error al completar paso');
    }

    /**
     * Obtener todos los tutoriales
     *
     * @return array
     */
    public function get_all_tutorials() {
        $tutorials = array();
        
        foreach ($this->tutorials as $id => $tutorial) {
            $tutorial['id'] = $id;
            $tutorial['steps'] = $this->get_tutorial_steps($id);
            $tutorials[] = $tutorial;
        }
        
        return $tutorials;
    }

    /**
     * Obtener tutoriales completados por usuario
     *
     * @param int $user_id ID del usuario
     * @return array
     */
    public function get_completed_tutorials($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wcvs_tutorial_progress';
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE user_id = %d AND is_completed = 1
            ORDER BY completed_at DESC
        ", $user_id), ARRAY_A);

        $tutorials = array();
        foreach ($results as $result) {
            $tutorial = $this->get_tutorial($result['tutorial_id']);
            if ($tutorial) {
                $tutorial['progress'] = $result;
                $tutorials[] = $tutorial;
            }
        }

        return $tutorials;
    }

    /**
     * Obtener tutoriales en progreso por usuario
     *
     * @param int $user_id ID del usuario
     * @return array
     */
    public function get_in_progress_tutorials($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wcvs_tutorial_progress';
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE user_id = %d AND is_completed = 0
            ORDER BY started_at DESC
        ", $user_id), ARRAY_A);

        $tutorials = array();
        foreach ($results as $result) {
            $tutorial = $this->get_tutorial($result['tutorial_id']);
            if ($tutorial) {
                $tutorial['progress'] = $result;
                $tutorials[] = $tutorial;
            }
        }

        return $tutorials;
    }

    /**
     * Obtener estadísticas de tutoriales
     *
     * @return array
     */
    public function get_tutorial_stats() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wcvs_tutorial_progress';
        
        $total_tutorials = count($this->tutorials);
        $total_started = $wpdb->get_var("SELECT COUNT(DISTINCT tutorial_id) FROM {$table_name}");
        $total_completed = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE is_completed = 1");
        $total_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$table_name}");
        
        $tutorial_stats = $wpdb->get_results("
            SELECT tutorial_id, COUNT(*) as started, SUM(is_completed) as completed
            FROM {$table_name}
            GROUP BY tutorial_id
        ", ARRAY_A);

        return array(
            'total_tutorials' => $total_tutorials,
            'total_started' => $total_started ?: 0,
            'total_completed' => $total_completed ?: 0,
            'total_users' => $total_users ?: 0,
            'tutorial_stats' => $tutorial_stats
        );
    }

    /**
     * Reiniciar tutorial
     *
     * @param string $tutorial_id ID del tutorial
     * @param int $user_id ID del usuario
     * @return array
     */
    public function reset_tutorial($tutorial_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wcvs_tutorial_progress';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'current_step' => 0,
                'completed_steps' => json_encode(array()),
                'is_completed' => 0,
                'started_at' => current_time('mysql'),
                'completed_at' => null
            ),
            array(
                'user_id' => $user_id,
                'tutorial_id' => $tutorial_id
            )
        );

        if ($result !== false) {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, "Tutorial reiniciado: {$tutorial_id} para usuario {$user_id}");
            return array('success' => true, 'message' => 'Tutorial reiniciado correctamente');
        }

        return array('success' => false, 'message' => 'Error al reiniciar tutorial');
    }
}
