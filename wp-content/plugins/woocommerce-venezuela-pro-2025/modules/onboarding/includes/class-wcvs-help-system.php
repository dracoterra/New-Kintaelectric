<?php
/**
 * Sistema de Ayuda Integrado - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar el sistema de ayuda integrado
 */
class WCVS_Help_System {

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = WCVS_Core::get_instance()->get_settings()->get('help_system', array());
        $this->init_database();
    }

    /**
     * Inicializar base de datos
     */
    private function init_database() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_help_content';
        
        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            $this->create_help_content_table();
        }
    }

    /**
     * Crear tabla de contenido de ayuda
     */
    private function create_help_content_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_help_content';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            content longtext NOT NULL,
            category varchar(100) NOT NULL,
            tags longtext,
            priority int(11) DEFAULT 0,
            is_featured tinyint(1) DEFAULT 0,
            view_count int(11) DEFAULT 0,
            helpful_count int(11) DEFAULT 0,
            not_helpful_count int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category),
            KEY priority (priority),
            KEY is_featured (is_featured),
            KEY view_count (view_count)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Insertar contenido inicial
        $this->insert_initial_content();
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_ONBOARDING, 'Tabla de contenido de ayuda creada');
    }

    /**
     * Insertar contenido inicial
     */
    private function insert_initial_content() {
        $initial_content = array(
            array(
                'title' => 'Configuración Inicial del Plugin',
                'content' => $this->get_initial_setup_content(),
                'category' => 'setup',
                'tags' => json_encode(array('configuración', 'inicial', 'setup')),
                'priority' => 10,
                'is_featured' => 1
            ),
            array(
                'title' => 'Configuración de Moneda USD a VES',
                'content' => $this->get_currency_setup_content(),
                'category' => 'currency',
                'tags' => json_encode(array('moneda', 'conversión', 'BCV', 'USD', 'VES')),
                'priority' => 9,
                'is_featured' => 1
            ),
            array(
                'title' => 'Pasarelas de Pago Locales',
                'content' => $this->get_payment_gateways_content(),
                'category' => 'payments',
                'tags' => json_encode(array('pagos', 'zelle', 'binance', 'pago móvil')),
                'priority' => 8,
                'is_featured' => 1
            ),
            array(
                'title' => 'Métodos de Envío en Venezuela',
                'content' => $this->get_shipping_methods_content(),
                'category' => 'shipping',
                'tags' => json_encode(array('envío', 'MRW', 'Zoom', 'Tealca')),
                'priority' => 7,
                'is_featured' => 1
            ),
            array(
                'title' => 'Sistema Fiscal Venezolano',
                'content' => $this->get_tax_system_content(),
                'category' => 'taxes',
                'tags' => json_encode(array('impuestos', 'IVA', 'IGTF', 'ISLR')),
                'priority' => 6,
                'is_featured' => 1
            ),
            array(
                'title' => 'Facturación Electrónica SENIAT',
                'content' => $this->get_electronic_billing_content(),
                'category' => 'billing',
                'tags' => json_encode(array('facturación', 'SENIAT', 'electrónica')),
                'priority' => 5,
                'is_featured' => 1
            ),
            array(
                'title' => 'Sistema de Notificaciones',
                'content' => $this->get_notifications_content(),
                'category' => 'notifications',
                'tags' => json_encode(array('notificaciones', 'email', 'SMS', 'push')),
                'priority' => 4,
                'is_featured' => 1
            ),
            array(
                'title' => 'Reportes SENIAT',
                'content' => $this->get_seniat_reports_content(),
                'category' => 'reports',
                'tags' => json_encode(array('reportes', 'SENIAT', 'fiscales')),
                'priority' => 3,
                'is_featured' => 0
            ),
            array(
                'title' => 'Solución de Problemas Comunes',
                'content' => $this->get_troubleshooting_content(),
                'category' => 'troubleshooting',
                'tags' => json_encode(array('problemas', 'errores', 'solución')),
                'priority' => 2,
                'is_featured' => 0
            ),
            array(
                'title' => 'Preguntas Frecuentes',
                'content' => $this->get_faq_content(),
                'category' => 'faq',
                'tags' => json_encode(array('preguntas', 'frecuentes', 'FAQ')),
                'priority' => 1,
                'is_featured' => 0
            )
        );

        global $wpdb;
        $table_name = $wpdb->prefix . 'wcvs_help_content';
        
        foreach ($initial_content as $content) {
            $wpdb->insert($table_name, $content);
        }
    }

    /**
     * Obtener contenido de configuración inicial
     */
    private function get_initial_setup_content() {
        return '
        <h2>Configuración Inicial del Plugin</h2>
        <p>El plugin WooCommerce Venezuela Suite está diseñado para facilitar la configuración de tu tienda venezolana. Sigue estos pasos para comenzar:</p>
        
        <h3>1. Activación del Plugin</h3>
        <p>Una vez activado el plugin, verás un aviso de bienvenida que te guiará a través del proceso de configuración inicial.</p>
        
        <h3>2. Wizard de Configuración</h3>
        <p>El wizard te ayudará a configurar:</p>
        <ul>
            <li>Conversión automática de moneda USD a VES</li>
            <li>Pasarelas de pago locales</li>
            <li>Métodos de envío venezolanos</li>
            <li>Sistema fiscal venezolano</li>
            <li>Facturación electrónica SENIAT</li>
            <li>Sistema de notificaciones</li>
        </ul>
        
        <h3>3. Configuración Manual</h3>
        <p>Si prefieres configurar manualmente, puedes acceder a cada módulo desde el menú de administración de WordPress.</p>
        
        <h3>4. Verificación</h3>
        <p>Después de la configuración, verifica que todos los módulos estén funcionando correctamente realizando una compra de prueba.</p>
        ';
    }

    /**
     * Obtener contenido de configuración de moneda
     */
    private function get_currency_setup_content() {
        return '
        <h2>Configuración de Moneda USD a VES</h2>
        <p>El plugin incluye conversión automática de precios USD a VES usando la tasa oficial del Banco Central de Venezuela (BCV).</p>
        
        <h3>Configuración Básica</h3>
        <ol>
            <li>Ve a <strong>WooCommerce > Configuración > Moneda</strong></li>
            <li>Habilita la conversión automática</li>
            <li>Selecciona el estilo de visualización de precios</li>
            <li>Configura la frecuencia de actualización de la tasa</li>
        </ol>
        
        <h3>Estilos de Visualización</h3>
        <ul>
            <li><strong>Minimalista:</strong> Diseño limpio y simple</li>
            <li><strong>Moderno:</strong> Diseño con gradientes y animaciones</li>
            <li><strong>Elegante:</strong> Diseño sofisticado y refinado</li>
            <li><strong>Compacto:</strong> Diseño optimizado para espacios pequeños</li>
        </ul>
        
        <h3>Frecuencia de Actualización</h3>
        <p>Puedes configurar la frecuencia de actualización de la tasa de cambio:</p>
        <ul>
            <li>Cada 15 minutos</li>
            <li>Cada 30 minutos (recomendado)</li>
            <li>Cada hora</li>
            <li>Cada 2 horas</li>
        </ul>
        
        <h3>Widgets Disponibles</h3>
        <p>El plugin incluye widgets para mostrar información de moneda:</p>
        <ul>
            <li><strong>Widget de Moneda:</strong> Muestra la tasa actual y opciones de cambio</li>
            <li><strong>Widget de Tasa:</strong> Muestra solo la tasa de conversión actual</li>
        </ul>
        ';
    }

    /**
     * Obtener contenido de pasarelas de pago
     */
    private function get_payment_gateways_content() {
        return '
        <h2>Pasarelas de Pago Locales</h2>
        <p>El plugin incluye múltiples pasarelas de pago específicas para el mercado venezolano.</p>
        
        <h3>Pasarelas Disponibles</h3>
        
        <h4>Zelle</h4>
        <p>Ideal para transferencias bancarias internacionales en USD.</p>
        <ul>
            <li>Configuración de email de Zelle</li>
            <li>Límites de monto configurables</li>
            <li>Validación automática de pagos</li>
        </ul>
        
        <h4>Binance Pay</h4>
        <p>Para pagos con criptomonedas.</p>
        <ul>
            <li>Soporte para múltiples criptomonedas</li>
            <li>Conversión automática a USD</li>
            <li>Confirmación automática de pagos</li>
        </ul>
        
        <h4>Pago Móvil</h4>
        <p>Transferencias móviles venezolanas.</p>
        <ul>
            <li>Validación de números telefónicos venezolanos</li>
            <li>Límites de monto por transacción</li>
            <li>Sistema de confirmación manual</li>
        </ul>
        
        <h4>Transferencia Bancaria</h4>
        <p>Transferencias bancarias locales.</p>
        <ul>
            <li>Múltiples bancos venezolanos</li>
            <li>Validación de RIF</li>
            <li>Confirmación manual de pagos</li>
        </ul>
        
        <h4>Depósito en Efectivo USD</h4>
        <p>Para depósitos en efectivo en dólares.</p>
        <ul>
            <li>Configuración de ubicaciones de depósito</li>
            <li>Sistema de confirmación con comprobantes</li>
            <li>Validación de montos</li>
        </ul>
        
        <h3>Configuración</h3>
        <ol>
            <li>Ve a <strong>WooCommerce > Pagos</strong></li>
            <li>Habilita las pasarelas que desees usar</li>
            <li>Configura los parámetros específicos de cada pasarela</li>
            <li>Prueba cada método de pago</li>
        </ol>
        ';
    }

    /**
     * Obtener contenido de métodos de envío
     */
    private function get_shipping_methods_content() {
        return '
        <h2>Métodos de Envío en Venezuela</h2>
        <p>El plugin incluye métodos de envío específicos para Venezuela con cálculo automático de costos.</p>
        
        <h3>Métodos Disponibles</h3>
        
        <h4>MRW</h4>
        <p>Servicio de envío nacional confiable.</p>
        <ul>
            <li>Cálculo por peso y volumen</li>
            <li>Cobertura nacional</li>
            <li>Seguimiento de envíos</li>
        </ul>
        
        <h4>Zoom</h4>
        <p>Servicio de envío rápido.</p>
        <ul>
            <li>Entrega rápida</li>
            <li>Cálculo dinámico de costos</li>
            <li>Seguimiento en tiempo real</li>
        </ul>
        
        <h4>Tealca</h4>
        <p>Servicio de envío económico para productos pesados.</p>
        <ul>
            <li>Tarifas competitivas</li>
            <li>Ideal para productos pesados</li>
            <li>Cobertura nacional</li>
        </ul>
        
        <h4>Entrega Local</h4>
        <p>Entrega en tu ciudad.</p>
        <ul>
            <li>Cálculo por distancia</li>
            <li>Entrega personalizada</li>
            <li>Costos reducidos</li>
        </ul>
        
        <h4>Recogida en Tienda</h4>
        <p>Recogida gratuita en tienda física.</p>
        <ul>
            <li>Sin costo de envío</li>
            <li>Recogida inmediata</li>
            <li>Horarios flexibles</li>
        </ul>
        
        <h3>Configuración</h3>
        <ol>
            <li>Ve a <strong>WooCommerce > Envíos</strong></li>
            <li>Habilita los métodos de envío deseados</li>
            <li>Configura las tarifas por peso y volumen</li>
            <li>Establece las zonas de envío</li>
        </ol>
        
        <h3>Sistema de Seguimiento</h3>
        <p>El plugin incluye un sistema de seguimiento que permite:</p>
        <ul>
            <li>Generación automática de números de seguimiento</li>
            <li>Actualización de estados de envío</li>
            <li>Notificaciones automáticas a clientes</li>
            <li>Panel de administración para gestión</li>
        </ul>
        ';
    }

    /**
     * Obtener contenido del sistema fiscal
     */
    private function get_tax_system_content() {
        return '
        <h2>Sistema Fiscal Venezolano</h2>
        <p>El plugin incluye el sistema fiscal completo de Venezuela con cálculos automáticos.</p>
        
        <h3>Impuestos Incluidos</h3>
        
        <h4>IVA (Impuesto al Valor Agregado)</h4>
        <p>Impuesto del 16% aplicable a la mayoría de productos y servicios.</p>
        <ul>
            <li>Cálculo automático del 16%</li>
            <li>Aplicación en productos y servicios</li>
            <li>Exenciones configurables</li>
        </ul>
        
        <h4>IGTF (Impuesto a las Grandes Transacciones Financieras)</h4>
        <p>Impuesto del 3% aplicable a transacciones en moneda extranjera.</p>
        <ul>
            <li>Cálculo automático del 3%</li>
            <li>Aplicación en pagos en moneda extranjera</li>
            <li>Configuración por método de pago</li>
        </ul>
        
        <h4>ISLR (Impuesto Sobre la Renta)</h4>
        <p>Retenciones de ISLR aplicables según la normativa venezolana.</p>
        <ul>
            <li>Cálculo automático de retenciones</li>
            <li>Configuración por tipo de entidad</li>
            <li>Reportes automáticos</li>
        </ul>
        
        <h3>Configuración</h3>
        <ol>
            <li>Ve a <strong>WooCommerce > Impuestos</strong></li>
            <li>Habilita los impuestos que apliquen</li>
            <li>Configura las tasas y exenciones</li>
            <li>Establece las reglas de aplicación</li>
        </ol>
        
        <h3>Reportes Fiscales</h3>
        <p>El plugin genera automáticamente reportes para SENIAT:</p>
        <ul>
            <li>Libro de Ventas</li>
            <li>Libro de Compras</li>
            <li>Retenciones de IVA</li>
            <li>Retenciones de ISLR</li>
            <li>Resumen Ejecutivo</li>
        </ul>
        ';
    }

    /**
     * Obtener contenido de facturación electrónica
     */
    private function get_electronic_billing_content() {
        return '
        <h2>Facturación Electrónica SENIAT</h2>
        <p>El plugin incluye un sistema completo de facturación electrónica compatible con SENIAT.</p>
        
        <h3>Características</h3>
        <ul>
            <li>Generación automática de facturas</li>
            <li>Plantillas personalizables</li>
            <li>Firma digital</li>
            <li>Integración con SENIAT</li>
            <li>Múltiples formatos de exportación</li>
        </ul>
        
        <h3>Configuración</h3>
        <ol>
            <li>Ve a <strong>WooCommerce > Facturación Electrónica</strong></li>
            <li>Configura los datos de tu empresa</li>
            <li>Establece las plantillas de factura</li>
            <li>Configura la integración con SENIAT</li>
        </ol>
        
        <h3>Formatos de Exportación</h3>
        <ul>
            <li>Excel (.xlsx)</li>
            <li>CSV</li>
            <li>PDF</li>
            <li>XML</li>
            <li>JSON</li>
            <li>HTML</li>
        </ul>
        
        <h3>Integración SENIAT</h3>
        <p>El plugin puede enviar facturas automáticamente a SENIAT:</p>
        <ul>
            <li>Envió automático de facturas</li>
            <li>Seguimiento de estado</li>
            <li>Manejo de errores</li>
            <li>Reintentos automáticos</li>
        </ul>
        ';
    }

    /**
     * Obtener contenido de notificaciones
     */
    private function get_notifications_content() {
        return '
        <h2>Sistema de Notificaciones</h2>
        <p>El plugin incluye un sistema completo de notificaciones para mantenerte informado sobre tu tienda.</p>
        
        <h3>Canales de Notificación</h3>
        
        <h4>Email</h4>
        <ul>
            <li>Plantillas personalizables</li>
            <li>Sistema de colas</li>
            <li>Estadísticas de entrega</li>
        </ul>
        
        <h4>SMS</h4>
        <ul>
            <li>Integración con operadoras locales</li>
            <li>Validación de números venezolanos</li>
            <li>Control de costos</li>
        </ul>
        
        <h4>Push Notifications</h4>
        <ul>
            <li>Notificaciones móviles</li>
            <li>Sistema de tokens</li>
            <li>Engagement tracking</li>
        </ul>
        
        <h4>Webhooks</h4>
        <ul>
            <li>Integración con sistemas externos</li>
            <li>Sistema de reintentos</li>
            <li>Firma HMAC para seguridad</li>
        </ul>
        
        <h3>Eventos de Notificación</h3>
        <ul>
            <li>Pedidos creados, procesados, completados</li>
            <li>Pagos recibidos o fallidos</li>
            <li>Facturas generadas</li>
            <li>Envíos creados, despachados, entregados</li>
            <li>Stock bajo</li>
            <li>Cambios de precio</li>
            <li>Actualizaciones de tasa de cambio</li>
            <li>Reportes SENIAT generados</li>
        </ul>
        
        <h3>Configuración</h3>
        <ol>
            <li>Ve a <strong>WooCommerce > Notificaciones</strong></li>
            <li>Habilita los canales deseados</li>
            <li>Configura las plantillas</li>
            <li>Establece los eventos de notificación</li>
        </ol>
        ';
    }

    /**
     * Obtener contenido de reportes SENIAT
     */
    private function get_seniat_reports_content() {
        return '
        <h2>Reportes SENIAT</h2>
        <p>El plugin genera automáticamente todos los reportes requeridos por SENIAT.</p>
        
        <h3>Reportes Disponibles</h3>
        
        <h4>Libro de Ventas</h4>
        <p>Registro detallado de todas las ventas realizadas.</p>
        
        <h4>Libro de Compras</h4>
        <p>Registro de todas las compras y gastos.</p>
        
        <h4>Retenciones de IVA</h4>
        <p>Registro de retenciones de IVA aplicadas.</p>
        
        <h4>Retenciones de ISLR</h4>
        <p>Registro de retenciones de ISLR aplicadas.</p>
        
        <h4>Resumen Ejecutivo</h4>
        <p>Resumen general de la actividad fiscal.</p>
        
        <h3>Generación de Reportes</h3>
        <ol>
            <li>Ve a <strong>WooCommerce > Reportes SENIAT</strong></li>
            <li>Selecciona el tipo de reporte</li>
            <li>Establece el período</li>
            <li>Genera el reporte</li>
            <li>Exporta en el formato requerido</li>
        </ol>
        
        <h3>Formatos de Exportación</h3>
        <ul>
            <li>Excel (.xlsx)</li>
            <li>CSV</li>
            <li>PDF</li>
            <li>XML</li>
            <li>JSON</li>
        </ul>
        ';
    }

    /**
     * Obtener contenido de solución de problemas
     */
    private function get_troubleshooting_content() {
        return '
        <h2>Solución de Problemas Comunes</h2>
        <p>Aquí encontrarás soluciones a los problemas más comunes del plugin.</p>
        
        <h3>Problemas de Moneda</h3>
        
        <h4>Los precios no se convierten</h4>
        <ul>
            <li>Verifica que el módulo de moneda esté activado</li>
            <li>Comprueba que la tasa de cambio esté disponible</li>
            <li>Revisa la configuración de visualización de precios</li>
        </ul>
        
        <h4>La tasa de cambio no se actualiza</h4>
        <ul>
            <li>Verifica la conexión a internet</li>
            <li>Comprueba que el plugin BCV Dólar Tracker esté activo</li>
            <li>Revisa la configuración de frecuencia de actualización</li>
        </ul>
        
        <h3>Problemas de Pagos</h3>
        
        <h4>Los pagos no se procesan</h4>
        <ul>
            <li>Verifica que las pasarelas estén habilitadas</li>
            <li>Comprueba la configuración de cada pasarela</li>
            <li>Revisa los logs de errores</li>
        </ul>
        
        <h4>Los pagos no se confirman</h4>
        <ul>
            <li>Verifica el sistema de confirmación</li>
            <li>Comprueba la configuración de notificaciones</li>
            <li>Revisa los permisos de usuario</li>
        </ul>
        
        <h3>Problemas de Envío</h3>
        
        <h4>Los costos de envío no se calculan</h4>
        <ul>
            <li>Verifica que los métodos de envío estén habilitados</li>
            <li>Comprueba la configuración de tarifas</li>
            <li>Revisa las zonas de envío</li>
        </ul>
        
        <h4>Los números de seguimiento no se generan</h4>
        <ul>
            <li>Verifica que el sistema de seguimiento esté activo</li>
            <li>Comprueba la configuración de métodos de envío</li>
            <li>Revisa los permisos de base de datos</li>
        </ul>
        
        <h3>Problemas de Impuestos</h3>
        
        <h4>Los impuestos no se calculan</h4>
        <ul>
            <li>Verifica que el sistema fiscal esté habilitado</li>
            <li>Comprueba la configuración de tasas</li>
            <li>Revisa las reglas de aplicación</li>
        </ul>
        
        <h4>Los reportes no se generan</h4>
        <ul>
            <li>Verifica que el módulo de reportes esté activo</li>
            <li>Comprueba los permisos de archivo</li>
            <li>Revisa la configuración de exportación</li>
        </ul>
        
        <h3>Problemas de Notificaciones</h3>
        
        <h4>Las notificaciones no se envían</h4>
        <ul>
            <li>Verifica que el sistema de notificaciones esté activo</li>
            <li>Comprueba la configuración de canales</li>
            <li>Revisa los logs de errores</li>
        </ul>
        
        <h4>Los webhooks fallan</h4>
        <ul>
            <li>Verifica la URL del webhook</li>
            <li>Comprueba la configuración de headers</li>
            <li>Revisa el sistema de reintentos</li>
        </ul>
        
        <h3>Logs y Debugging</h3>
        <p>Para diagnosticar problemas:</p>
        <ol>
            <li>Habilita el logging en la configuración del plugin</li>
            <li>Revisa los logs en <strong>WooCommerce > Estado > Logs</strong></li>
            <li>Usa las herramientas de debugging del plugin</li>
            <li>Contacta al soporte técnico si es necesario</li>
        </ol>
        ';
    }

    /**
     * Obtener contenido de FAQ
     */
    private function get_faq_content() {
        return '
        <h2>Preguntas Frecuentes</h2>
        
        <h3>¿El plugin es compatible con mi tema?</h3>
        <p>Sí, el plugin está diseñado para ser compatible con la mayoría de temas de WordPress y WooCommerce. Incluye estilos específicos para temas populares como Storefront, Astra, y otros.</p>
        
        <h3>¿Puedo usar el plugin sin el plugin BCV Dólar Tracker?</h3>
        <p>Sí, el plugin puede funcionar sin BCV Dólar Tracker, pero necesitarás configurar manualmente las tasas de cambio o usar una fuente externa.</p>
        
        <h3>¿El plugin funciona con WooCommerce HPOS?</h3>
        <p>Sí, el plugin es completamente compatible con WooCommerce High-Performance Order Storage (HPOS).</p>
        
        <h3>¿Puedo personalizar las plantillas de email?</h3>
        <p>Sí, el plugin incluye un sistema de plantillas personalizables para emails, SMS y notificaciones push.</p>
        
        <h3>¿El plugin incluye soporte para criptomonedas?</h3>
        <p>Sí, el plugin incluye integración con Binance Pay para pagos con criptomonedas.</p>
        
        <h3>¿Puedo exportar los reportes SENIAT?</h3>
        <p>Sí, el plugin permite exportar reportes en múltiples formatos: Excel, CSV, PDF, XML y JSON.</p>
        
        <h3>¿El plugin es compatible con otros plugins de WooCommerce?</h3>
        <p>El plugin está diseñado para ser compatible con la mayoría de plugins de WooCommerce. Sin embargo, algunos plugins pueden requerir configuración adicional.</p>
        
        <h3>¿Puedo configurar el plugin sin el wizard de onboarding?</h3>
        <p>Sí, puedes omitir el wizard y configurar el plugin manualmente desde el panel de administración.</p>
        
        <h3>¿El plugin incluye soporte técnico?</h3>
        <p>Sí, el plugin incluye un sistema de soporte técnico integrado y documentación completa.</p>
        
        <h3>¿Puedo usar el plugin en múltiples sitios?</h3>
        <p>Esto depende de tu licencia. Consulta los términos de licencia para más información.</p>
        ';
    }

    /**
     * Obtener contenido de ayuda por ID
     *
     * @param int $content_id ID del contenido
     * @return array|false
     */
    public function get_help_content($content_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_help_content';
        
        $result = $wpdb->get_row($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE id = %d
        ", $content_id), ARRAY_A);
        
        if ($result) {
            $result['tags'] = json_decode($result['tags'], true);
            $this->increment_view_count($content_id);
        }
        
        return $result;
    }

    /**
     * Obtener contenido de ayuda por categoría
     *
     * @param string $category Categoría
     * @param int $limit Límite de resultados
     * @return array
     */
    public function get_help_content_by_category($category, $limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_help_content';
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE category = %s
            ORDER BY priority DESC, view_count DESC
            LIMIT %d
        ", $category, $limit), ARRAY_A);
        
        foreach ($results as &$result) {
            $result['tags'] = json_decode($result['tags'], true);
        }
        
        return $results;
    }

    /**
     * Buscar contenido de ayuda
     *
     * @param string $query Consulta de búsqueda
     * @param int $limit Límite de resultados
     * @return array
     */
    public function search_help_content($query, $limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_help_content';
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE title LIKE %s OR content LIKE %s OR tags LIKE %s
            ORDER BY priority DESC, view_count DESC
            LIMIT %d
        ", '%' . $query . '%', '%' . $query . '%', '%' . $query . '%', $limit), ARRAY_A);
        
        foreach ($results as &$result) {
            $result['tags'] = json_decode($result['tags'], true);
        }
        
        return $results;
    }

    /**
     * Obtener contenido destacado
     *
     * @param int $limit Límite de resultados
     * @return array
     */
    public function get_featured_content($limit = 5) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_help_content';
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE is_featured = 1
            ORDER BY priority DESC, view_count DESC
            LIMIT %d
        ", $limit), ARRAY_A);
        
        foreach ($results as &$result) {
            $result['tags'] = json_decode($result['tags'], true);
        }
        
        return $results;
    }

    /**
     * Incrementar contador de vistas
     *
     * @param int $content_id ID del contenido
     */
    private function increment_view_count($content_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_help_content';
        
        $wpdb->query($wpdb->prepare("
            UPDATE {$table_name}
            SET view_count = view_count + 1
            WHERE id = %d
        ", $content_id));
    }

    /**
     * Marcar contenido como útil
     *
     * @param int $content_id ID del contenido
     * @param bool $helpful Si es útil o no
     */
    public function mark_content_helpful($content_id, $helpful) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_help_content';
        
        if ($helpful) {
            $wpdb->query($wpdb->prepare("
                UPDATE {$table_name}
                SET helpful_count = helpful_count + 1
                WHERE id = %d
            ", $content_id));
        } else {
            $wpdb->query($wpdb->prepare("
                UPDATE {$table_name}
                SET not_helpful_count = not_helpful_count + 1
                WHERE id = %d
            ", $content_id));
        }
    }

    /**
     * Obtener estadísticas del sistema de ayuda
     *
     * @return array
     */
    public function get_help_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wcvs_help_content';
        
        $total_content = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$table_name}
        ");
        
        $total_views = $wpdb->get_var("
            SELECT SUM(view_count)
            FROM {$table_name}
        ");
        
        $total_helpful = $wpdb->get_var("
            SELECT SUM(helpful_count)
            FROM {$table_name}
        ");
        
        $content_by_category = $wpdb->get_results("
            SELECT category, COUNT(*) as count
            FROM {$table_name}
            GROUP BY category
        ", ARRAY_A);
        
        return array(
            'total_content' => $total_content ?: 0,
            'total_views' => $total_views ?: 0,
            'total_helpful' => $total_helpful ?: 0,
            'content_by_category' => $content_by_category
        );
    }
}
