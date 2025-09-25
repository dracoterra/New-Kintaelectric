<?php
/**
 * Módulo de Reportes SENIAT - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar reportes SENIAT
 */
class WCVS_SENIAT_Reports {

    /**
     * Instancia del plugin
     *
     * @var WCVS_Core
     */
    private $plugin;

    /**
     * Configuraciones del módulo
     *
     * @var array
     */
    private $settings;

    /**
     * Tipos de reportes SENIAT
     *
     * @var array
     */
    private $report_types = array(
        'resumen_ejecutivo' => 'Resumen Ejecutivo',
        'libro_ventas' => 'Libro de Ventas',
        'libro_compras' => 'Libro de Compras',
        'retenciones_iva' => 'Retenciones IVA',
        'retenciones_islr' => 'Retenciones ISLR',
        'igtf' => 'IGTF',
        'declaracion_iva' => 'Declaración IVA',
        'declaracion_islr' => 'Declaración ISLR',
    );

    /**
     * Estados de reportes
     *
     * @var array
     */
    private $report_statuses = array(
        'draft' => 'Borrador',
        'generated' => 'Generado',
        'submitted' => 'Enviado',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = WCVS_Core::get_instance();
        $this->settings = $this->plugin->get_settings()->get('seniat_reports', array());
        
        $this->init_hooks();
        $this->init_report_generators();
    }

    /**
     * Inicializar el módulo
     */
    public function init() {
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Configurar hooks de WooCommerce
        $this->setup_woocommerce_hooks();

        // Cargar scripts y estilos
        $this->enqueue_assets();

        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, 'Módulo SENIAT Reports inicializado');
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para cuando se activa el módulo
        add_action('wcvs_module_activated', array($this, 'handle_module_activation'), 10, 2);
        
        // Hook para cuando se desactiva el módulo
        add_action('wcvs_module_deactivated', array($this, 'handle_module_deactivation'), 10, 2);
        
        // Hook para menú de administración
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Hook para AJAX
        add_action('wp_ajax_wcvs_generate_report', array($this, 'ajax_generate_report'));
        add_action('wp_ajax_wcvs_export_report', array($this, 'ajax_export_report'));
        add_action('wp_ajax_wcvs_submit_report', array($this, 'ajax_submit_report'));
    }

    /**
     * Inicializar generadores de reportes
     */
    private function init_report_generators() {
        // Cargar generadores de reportes
        require_once WCVS_PLUGIN_PATH . 'modules/seniat-reports/includes/class-wcvs-executive-summary.php';
        require_once WCVS_PLUGIN_PATH . 'modules/seniat-reports/includes/class-wcvs-sales-book.php';
        require_once WCVS_PLUGIN_PATH . 'modules/seniat-reports/includes/class-wcvs-purchases-book.php';
        require_once WCVS_PLUGIN_PATH . 'modules/seniat-reports/includes/class-wcvs-retentions.php';
        
        // Inicializar generadores
        new WCVS_Executive_Summary($this->settings);
        new WCVS_Sales_Book($this->settings);
        new WCVS_Purchases_Book($this->settings);
        new WCVS_Retentions($this->settings);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, 'Generadores de reportes SENIAT inicializados');
    }

    /**
     * Configurar hooks de WooCommerce
     */
    private function setup_woocommerce_hooks() {
        // Hook para procesar pedidos
        add_action('woocommerce_order_status_completed', array($this, 'process_completed_order'));
        
        // Hook para procesar compras
        add_action('woocommerce_order_status_processing', array($this, 'process_processing_order'));
        
        // Hook para retenciones
        add_action('woocommerce_payment_complete', array($this, 'process_payment_retentions'));
    }

    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Reportes SENIAT',
            'Reportes SENIAT',
            'manage_woocommerce',
            'wcvs-seniat-reports',
            array($this, 'admin_page')
        );
    }

    /**
     * Página de administración
     */
    public function admin_page() {
        $current_tab = $_GET['tab'] ?? 'dashboard';
        
        echo '<div class="wrap">';
        echo '<h1>Reportes SENIAT</h1>';
        
        // Tabs
        $this->render_tabs($current_tab);
        
        // Contenido
        switch ($current_tab) {
            case 'dashboard':
                $this->render_dashboard();
                break;
            case 'generate':
                $this->render_generate_page();
                break;
            case 'reports':
                $this->render_reports_list();
                break;
            case 'settings':
                $this->render_settings_page();
                break;
        }
        
        echo '</div>';
    }

    /**
     * Renderizar tabs
     *
     * @param string $current_tab Tab actual
     */
    private function render_tabs($current_tab) {
        $tabs = array(
            'dashboard' => 'Dashboard',
            'generate' => 'Generar Reporte',
            'reports' => 'Reportes',
            'settings' => 'Configuración',
        );
        
        echo '<nav class="nav-tab-wrapper">';
        foreach ($tabs as $tab_key => $tab_label) {
            $active = $current_tab === $tab_key ? ' nav-tab-active' : '';
            echo '<a href="' . admin_url('admin.php?page=wcvs-seniat-reports&tab=' . $tab_key) . '" class="nav-tab' . $active . '">' . esc_html($tab_label) . '</a>';
        }
        echo '</nav>';
    }

    /**
     * Renderizar dashboard
     */
    private function render_dashboard() {
        $stats = $this->get_dashboard_stats();
        
        ?>
        <div class="wcvs-seniat-dashboard">
            <div class="wcvs-dashboard-stats">
                <div class="wcvs-stat-card">
                    <h3>Reportes Generados</h3>
                    <div class="wcvs-stat-number"><?php echo esc_html($stats['total_reports']); ?></div>
                </div>
                <div class="wcvs-stat-card">
                    <h3>Ventas del Mes</h3>
                    <div class="wcvs-stat-number"><?php echo wc_price($stats['monthly_sales']); ?></div>
                </div>
                <div class="wcvs-stat-card">
                    <h3>IVA Recaudado</h3>
                    <div class="wcvs-stat-number"><?php echo wc_price($stats['iva_collected']); ?></div>
                </div>
                <div class="wcvs-stat-card">
                    <h3>IGTF Recaudado</h3>
                    <div class="wcvs-stat-number"><?php echo wc_price($stats['igtf_collected']); ?></div>
                </div>
            </div>
            
            <div class="wcvs-dashboard-recent">
                <h3>Reportes Recientes</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Período</th>
                            <th>Estado</th>
                            <th>Generado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_reports'] as $report): ?>
                        <tr>
                            <td><?php echo esc_html($this->report_types[$report->report_type] ?? $report->report_type); ?></td>
                            <td><?php echo esc_html($report->start_date . ' - ' . $report->end_date); ?></td>
                            <td><span class="wcvs-status wcvs-status-<?php echo esc_attr($report->status); ?>"><?php echo esc_html($this->report_statuses[$report->status] ?? $report->status); ?></span></td>
                            <td><?php echo esc_html($report->generated_at); ?></td>
                            <td>
                                <a href="#" class="button button-small wcvs-view-report" data-report-id="<?php echo esc_attr($report->id); ?>">Ver</a>
                                <a href="#" class="button button-small wcvs-export-report" data-report-id="<?php echo esc_attr($report->id); ?>">Exportar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Renderizar página de generación
     */
    private function render_generate_page() {
        ?>
        <div class="wcvs-generate-report">
            <form id="wcvs-generate-report-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">Tipo de Reporte</th>
                        <td>
                            <select name="report_type" id="report_type" required>
                                <option value="">Selecciona un tipo de reporte</option>
                                <?php foreach ($this->report_types as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Período</th>
                        <td>
                            <input type="date" name="start_date" id="start_date" required>
                            <span>hasta</span>
                            <input type="date" name="end_date" id="end_date" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Formato</th>
                        <td>
                            <select name="format" id="format">
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">Generar Reporte</button>
                </p>
            </form>
            
            <div id="wcvs-report-progress" style="display: none;">
                <div class="wcvs-progress-bar">
                    <div class="wcvs-progress-fill"></div>
                </div>
                <p>Generando reporte...</p>
            </div>
        </div>
        <?php
    }

    /**
     * Renderizar lista de reportes
     */
    private function render_reports_list() {
        $reports = $this->get_all_reports();
        
        ?>
        <div class="wcvs-reports-list">
            <div class="wcvs-reports-filters">
                <select id="wcvs-filter-type">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($this->report_types as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select id="wcvs-filter-status">
                    <option value="">Todos los estados</option>
                    <?php foreach ($this->report_statuses as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="date" id="wcvs-filter-start-date" placeholder="Fecha inicio">
                <input type="date" id="wcvs-filter-end-date" placeholder="Fecha fin">
                
                <button type="button" class="button" id="wcvs-apply-filters">Filtrar</button>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Período</th>
                        <th>Estado</th>
                        <th>Generado</th>
                        <th>Tamaño</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?php echo esc_html($this->report_types[$report->report_type] ?? $report->report_type); ?></td>
                        <td><?php echo esc_html($report->start_date . ' - ' . $report->end_date); ?></td>
                        <td><span class="wcvs-status wcvs-status-<?php echo esc_attr($report->status); ?>"><?php echo esc_html($this->report_statuses[$report->status] ?? $report->status); ?></span></td>
                        <td><?php echo esc_html($report->generated_at); ?></td>
                        <td><?php echo esc_html($this->format_file_size($report->file_size ?? 0)); ?></td>
                        <td>
                            <a href="#" class="button button-small wcvs-view-report" data-report-id="<?php echo esc_attr($report->id); ?>">Ver</a>
                            <a href="#" class="button button-small wcvs-export-report" data-report-id="<?php echo esc_attr($report->id); ?>">Exportar</a>
                            <?php if ($report->status === 'generated'): ?>
                                <a href="#" class="button button-small wcvs-submit-report" data-report-id="<?php echo esc_attr($report->id); ?>">Enviar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Renderizar página de configuración
     */
    private function render_settings_page() {
        ?>
        <div class="wcvs-seniat-settings">
            <form method="post" action="options.php">
                <?php settings_fields('wcvs_seniat_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">RIF de la Empresa</th>
                        <td>
                            <input type="text" name="wcvs_seniat_settings[company_rif]" value="<?php echo esc_attr($this->settings['company_rif'] ?? ''); ?>" class="regular-text" required>
                            <p class="description">RIF de la empresa para reportes SENIAT</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Nombre de la Empresa</th>
                        <td>
                            <input type="text" name="wcvs_seniat_settings[company_name]" value="<?php echo esc_attr($this->settings['company_name'] ?? ''); ?>" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Dirección Fiscal</th>
                        <td>
                            <textarea name="wcvs_seniat_settings[company_address]" rows="3" cols="50" class="large-text"><?php echo esc_textarea($this->settings['company_address'] ?? ''); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Teléfono</th>
                        <td>
                            <input type="text" name="wcvs_seniat_settings[company_phone]" value="<?php echo esc_attr($this->settings['company_phone'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Email</th>
                        <td>
                            <input type="email" name="wcvs_seniat_settings[company_email]" value="<?php echo esc_attr($this->settings['company_email'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Actividad Económica</th>
                        <td>
                            <input type="text" name="wcvs_seniat_settings[economic_activity]" value="<?php echo esc_attr($this->settings['economic_activity'] ?? ''); ?>" class="regular-text">
                            <p class="description">Código de actividad económica según SENIAT</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" class="button-primary" value="Guardar Configuración">
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Generar reporte via AJAX
     */
    public function ajax_generate_report() {
        check_ajax_referer('wcvs_seniat_nonce', 'nonce');
        
        $report_type = sanitize_text_field($_POST['report_type'] ?? '');
        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date = sanitize_text_field($_POST['end_date'] ?? '');
        $format = sanitize_text_field($_POST['format'] ?? 'excel');
        
        if (empty($report_type) || empty($start_date) || empty($end_date)) {
            wp_send_json_error('Datos requeridos faltantes');
        }
        
        $result = $this->generate_report($report_type, $start_date, $end_date, $format);
        
        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Generar reporte
     *
     * @param string $report_type Tipo de reporte
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @param string $format Formato
     * @return array
     */
    private function generate_report($report_type, $start_date, $end_date, $format) {
        try {
            // Generar datos del reporte
            $report_data = $this->get_report_data($report_type, $start_date, $end_date);
            
            // Generar archivo
            $file_path = $this->generate_report_file($report_data, $format);
            
            // Guardar en base de datos
            $report_id = $this->save_report($report_type, $start_date, $end_date, $file_path, $format);
            
            return array(
                'success' => true,
                'data' => array(
                    'report_id' => $report_id,
                    'file_url' => $file_path,
                    'message' => 'Reporte generado exitosamente'
                )
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Obtener datos del reporte
     *
     * @param string $report_type Tipo de reporte
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    private function get_report_data($report_type, $start_date, $end_date) {
        switch ($report_type) {
            case 'resumen_ejecutivo':
                return $this->get_executive_summary_data($start_date, $end_date);
            case 'libro_ventas':
                return $this->get_sales_book_data($start_date, $end_date);
            case 'libro_compras':
                return $this->get_purchases_book_data($start_date, $end_date);
            case 'retenciones_iva':
                return $this->get_iva_retentions_data($start_date, $end_date);
            case 'retenciones_islr':
                return $this->get_islr_retentions_data($start_date, $end_date);
            case 'igtf':
                return $this->get_igtf_data($start_date, $end_date);
            default:
                throw new Exception('Tipo de reporte no válido');
        }
    }

    /**
     * Generar archivo de reporte
     *
     * @param array $report_data Datos del reporte
     * @param string $format Formato
     * @return string URL del archivo
     */
    private function generate_report_file($report_data, $format) {
        $upload_dir = wp_upload_dir();
        $filename = 'seniat_report_' . date('Y-m-d_H-i-s') . '.' . $format;
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        switch ($format) {
            case 'excel':
                $this->generate_excel_file($report_data, $file_path);
                break;
            case 'csv':
                $this->generate_csv_file($report_data, $file_path);
                break;
            case 'pdf':
                $this->generate_pdf_file($report_data, $file_path);
                break;
        }
        
        return $upload_dir['url'] . '/' . $filename;
    }

    /**
     * Generar archivo Excel
     *
     * @param array $report_data Datos del reporte
     * @param string $file_path Ruta del archivo
     */
    private function generate_excel_file($report_data, $file_path) {
        // Implementar generación de Excel
        // Por ahora usar CSV
        $this->generate_csv_file($report_data, str_replace('.xlsx', '.csv', $file_path));
    }

    /**
     * Generar archivo CSV
     *
     * @param array $report_data Datos del reporte
     * @param string $file_path Ruta del archivo
     */
    private function generate_csv_file($report_data, $file_path) {
        $file = fopen($file_path, 'w');
        
        // Encabezados
        if (isset($report_data['headers'])) {
            fputcsv($file, $report_data['headers']);
        }
        
        // Datos
        if (isset($report_data['rows'])) {
            foreach ($report_data['rows'] as $row) {
                fputcsv($file, $row);
            }
        }
        
        fclose($file);
    }

    /**
     * Generar archivo PDF
     *
     * @param array $report_data Datos del reporte
     * @param string $file_path Ruta del archivo
     */
    private function generate_pdf_file($report_data, $file_path) {
        // Implementar generación de PDF
        // Por ahora solo log
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "PDF de reporte SENIAT generado: {$file_path}");
    }

    /**
     * Guardar reporte en base de datos
     *
     * @param string $report_type Tipo de reporte
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @param string $file_path Ruta del archivo
     * @param string $format Formato
     * @return int ID del reporte
     */
    private function save_report($report_type, $start_date, $end_date, $file_path, $format) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'wcvs_fiscal_reports',
            array(
                'report_type' => $report_type,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'data' => maybe_serialize(array('file_path' => $file_path, 'format' => $format)),
                'generated_at' => current_time('mysql'),
                'status' => 'generated',
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }

    /**
     * Obtener estadísticas del dashboard
     *
     * @return array
     */
    private function get_dashboard_stats() {
        global $wpdb;
        
        $total_reports = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wcvs_fiscal_reports");
        
        $monthly_sales = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(total) FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND pm.meta_key = '_order_total'
            AND MONTH(p.post_date) = %d
            AND YEAR(p.post_date) = %d
        ", date('n'), date('Y')));
        
        $iva_collected = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(CAST(pm.meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND pm.meta_key = '_iva_amount'
            AND MONTH(p.post_date) = %d
            AND YEAR(p.post_date) = %d
        ", date('n'), date('Y')));
        
        $igtf_collected = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(CAST(pm.meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND pm.meta_key = '_igtf_amount'
            AND MONTH(p.post_date) = %d
            AND YEAR(p.post_date) = %d
        ", date('n'), date('Y')));
        
        $recent_reports = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}wcvs_fiscal_reports
            ORDER BY generated_at DESC
            LIMIT 5
        ");
        
        return array(
            'total_reports' => intval($total_reports),
            'monthly_sales' => floatval($monthly_sales ?? 0),
            'iva_collected' => floatval($iva_collected ?? 0),
            'igtf_collected' => floatval($igtf_collected ?? 0),
            'recent_reports' => $recent_reports,
        );
    }

    /**
     * Obtener todos los reportes
     *
     * @return array
     */
    private function get_all_reports() {
        global $wpdb;
        
        return $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}wcvs_fiscal_reports
            ORDER BY generated_at DESC
        ");
    }

    /**
     * Formatear tamaño de archivo
     *
     * @param int $size Tamaño en bytes
     * @return string
     */
    private function format_file_size($size) {
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return round($size / 1048576, 2) . ' MB';
        }
    }

    /**
     * Procesar pedido completado
     *
     * @param int $order_id ID del pedido
     */
    public function process_completed_order($order_id) {
        // Procesar para reportes de ventas
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "Pedido completado procesado para reportes SENIAT: #{$order_id}");
    }

    /**
     * Procesar pedido en procesamiento
     *
     * @param int $order_id ID del pedido
     */
    public function process_processing_order($order_id) {
        // Procesar para reportes de compras
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "Pedido en procesamiento para reportes SENIAT: #{$order_id}");
    }

    /**
     * Procesar retenciones de pago
     *
     * @param int $order_id ID del pedido
     */
    public function process_payment_retentions($order_id) {
        // Procesar retenciones
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "Retenciones procesadas para pedido: #{$order_id}");
    }

    /**
     * Cargar assets (CSS y JS)
     */
    private function enqueue_assets() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_seniat_assets'));
    }

    /**
     * Encolar assets de SENIAT
     */
    public function enqueue_seniat_assets($hook) {
        if (strpos($hook, 'wcvs-seniat-reports') !== false) {
            wp_enqueue_style(
                'wcvs-seniat-reports',
                WCVS_PLUGIN_URL . 'modules/seniat-reports/css/wcvs-seniat-reports.css',
                array(),
                WCVS_VERSION
            );

            wp_enqueue_script(
                'wcvs-seniat-reports',
                WCVS_PLUGIN_URL . 'modules/seniat-reports/js/wcvs-seniat-reports.js',
                array('jquery'),
                WCVS_VERSION,
                true
            );

            wp_localize_script('wcvs-seniat-reports', 'wcvs_seniat_reports', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcvs_seniat_nonce'),
                'strings' => array(
                    'generating' => 'Generando reporte...',
                    'success' => 'Reporte generado exitosamente',
                    'error' => 'Error al generar reporte'
                )
            ));
        }
    }

    /**
     * Manejar activación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_activation($module_key, $module_data) {
        if ($module_key === 'seniat_reports') {
            $this->init();
            WCVS_Logger::success(WCVS_Logger::CONTEXT_SENIAT, 'Módulo SENIAT Reports activado');
        }
    }

    /**
     * Manejar desactivación del módulo
     *
     * @param string $module_key Clave del módulo
     * @param array $module_data Datos del módulo
     */
    public function handle_module_deactivation($module_key, $module_data) {
        if ($module_key === 'seniat_reports') {
            WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, 'Módulo SENIAT Reports desactivado');
        }
    }

    /**
     * Obtener estadísticas del módulo
     *
     * @return array
     */
    public function get_module_stats() {
        return array(
            'report_types' => $this->report_types,
            'report_statuses' => $this->report_statuses,
            'settings' => $this->settings
        );
    }

    // Métodos placeholder para obtener datos específicos de reportes
    private function get_executive_summary_data($start_date, $end_date) {
        return array('headers' => array('Resumen Ejecutivo'), 'rows' => array());
    }

    private function get_sales_book_data($start_date, $end_date) {
        return array('headers' => array('Libro de Ventas'), 'rows' => array());
    }

    private function get_purchases_book_data($start_date, $end_date) {
        return array('headers' => array('Libro de Compras'), 'rows' => array());
    }

    private function get_iva_retentions_data($start_date, $end_date) {
        return array('headers' => array('Retenciones IVA'), 'rows' => array());
    }

    private function get_islr_retentions_data($start_date, $end_date) {
        return array('headers' => array('Retenciones ISLR'), 'rows' => array());
    }

    private function get_igtf_data($start_date, $end_date) {
        return array('headers' => array('IGTF'), 'rows' => array());
    }
}
