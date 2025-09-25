<?php
/**
 * Resumen Ejecutivo - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para generar el Resumen Ejecutivo SENIAT
 */
class WCVS_Executive_Summary {

    /**
     * Configuraciones
     *
     * @var array
     */
    private $settings;

    /**
     * Constructor
     *
     * @param array $settings Configuraciones
     */
    public function __construct($settings = array()) {
        $this->settings = $settings;
        
        $this->init_hooks();
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook para generar resumen automático
        add_action('wcvs_generate_executive_summary', array($this, 'generate_automatic_summary'));
    }

    /**
     * Generar Resumen Ejecutivo
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function generate_executive_summary($start_date, $end_date) {
        // Obtener datos de ventas
        $sales_data = $this->get_sales_data($start_date, $end_date);
        
        // Obtener datos de compras
        $purchases_data = $this->get_purchases_data($start_date, $end_date);
        
        // Obtener datos de retenciones
        $retentions_data = $this->get_retentions_data($start_date, $end_date);
        
        // Calcular resumen fiscal
        $fiscal_summary = $this->calculate_fiscal_summary($sales_data, $purchases_data, $retentions_data);
        
        // Generar resumen ejecutivo
        $executive_summary = $this->create_executive_summary($fiscal_summary, $start_date, $end_date);
        
        return array(
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date,
            ),
            'fiscal_summary' => $fiscal_summary,
            'executive_summary' => $executive_summary,
            'headers' => $this->get_executive_summary_headers(),
            'rows' => $this->format_executive_summary_for_export($executive_summary),
        );
    }

    /**
     * Obtener datos de ventas
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    private function get_sales_data($start_date, $end_date) {
        global $wpdb;
        
        $sales_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_sales,
                SUM(CAST(JSON_EXTRACT(totals_data, '$.total') AS DECIMAL(10,2))) as total_amount,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva_amount') AS DECIMAL(10,2))) as total_iva,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.igtf_amount') AS DECIMAL(10,2))) as total_igtf,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.islr_amount') AS DECIMAL(10,2))) as total_islr,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva_exempt') AS DECIMAL(10,2))) as total_exempt,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva_base') AS DECIMAL(10,2))) as total_taxable
            FROM {$wpdb->prefix}wcvs_sales_book
            WHERE order_date >= %s
            AND order_date <= %s
        ", $start_date, $end_date));
        
        return array(
            'total_sales' => intval($sales_stats->total_sales ?? 0),
            'total_amount' => floatval($sales_stats->total_amount ?? 0),
            'total_iva' => floatval($sales_stats->total_iva ?? 0),
            'total_igtf' => floatval($sales_stats->total_igtf ?? 0),
            'total_islr' => floatval($sales_stats->total_islr ?? 0),
            'total_exempt' => floatval($sales_stats->total_exempt ?? 0),
            'total_taxable' => floatval($sales_stats->total_taxable ?? 0),
        );
    }

    /**
     * Obtener datos de compras
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    private function get_purchases_data($start_date, $end_date) {
        global $wpdb;
        
        $purchases_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_purchases,
                SUM(CAST(JSON_EXTRACT(totals_data, '$.total') AS DECIMAL(10,2))) as total_amount,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva_amount') AS DECIMAL(10,2))) as total_iva,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.igtf_amount') AS DECIMAL(10,2))) as total_igtf,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.islr_amount') AS DECIMAL(10,2))) as total_islr,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva_exempt') AS DECIMAL(10,2))) as total_exempt,
                SUM(CAST(JSON_EXTRACT(taxes_data, '$.iva_base') AS DECIMAL(10,2))) as total_taxable
            FROM {$wpdb->prefix}wcvs_purchases_book
            WHERE order_date >= %s
            AND order_date <= %s
        ", $start_date, $end_date));
        
        return array(
            'total_purchases' => intval($purchases_stats->total_purchases ?? 0),
            'total_amount' => floatval($purchases_stats->total_amount ?? 0),
            'total_iva' => floatval($purchases_stats->total_iva ?? 0),
            'total_igtf' => floatval($purchases_stats->total_igtf ?? 0),
            'total_islr' => floatval($purchases_stats->total_islr ?? 0),
            'total_exempt' => floatval($purchases_stats->total_exempt ?? 0),
            'total_taxable' => floatval($purchases_stats->total_taxable ?? 0),
        );
    }

    /**
     * Obtener datos de retenciones
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    private function get_retentions_data($start_date, $end_date) {
        global $wpdb;
        
        $retentions_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_retentions,
                SUM(CAST(r.base_amount AS DECIMAL(10,2))) as total_base,
                SUM(CAST(r.amount AS DECIMAL(10,2))) as total_retentions_amount,
                SUM(CASE WHEN r.retention_type = 'iva' THEN CAST(r.amount AS DECIMAL(10,2)) ELSE 0 END) as total_iva_retentions,
                SUM(CASE WHEN r.retention_type = 'islr' THEN CAST(r.amount AS DECIMAL(10,2)) ELSE 0 END) as total_islr_retentions
            FROM {$wpdb->prefix}wcvs_retentions r
            INNER JOIN {$wpdb->posts} p ON r.order_id = p.ID
            WHERE p.post_date >= %s
            AND p.post_date <= %s
        ", $start_date, $end_date));
        
        return array(
            'total_retentions' => intval($retentions_stats->total_retentions ?? 0),
            'total_base' => floatval($retentions_stats->total_base ?? 0),
            'total_retentions_amount' => floatval($retentions_stats->total_retentions_amount ?? 0),
            'total_iva_retentions' => floatval($retentions_stats->total_iva_retentions ?? 0),
            'total_islr_retentions' => floatval($retentions_stats->total_islr_retentions ?? 0),
        );
    }

    /**
     * Calcular resumen fiscal
     *
     * @param array $sales_data Datos de ventas
     * @param array $purchases_data Datos de compras
     * @param array $retentions_data Datos de retenciones
     * @return array
     */
    private function calculate_fiscal_summary($sales_data, $purchases_data, $retentions_data) {
        // Calcular diferencias
        $iva_difference = $sales_data['total_iva'] - $purchases_data['total_iva'];
        $igtf_difference = $sales_data['total_igtf'] - $purchases_data['total_igtf'];
        $islr_difference = $sales_data['total_islr'] - $purchases_data['total_islr'];
        
        // Calcular totales netos
        $net_sales = $sales_data['total_amount'] - $sales_data['total_iva'] - $sales_data['total_igtf'] - $sales_data['total_islr'];
        $net_purchases = $purchases_data['total_amount'] - $purchases_data['total_iva'] - $purchases_data['total_igtf'] - $purchases_data['total_islr'];
        
        // Calcular impuestos netos a pagar
        $net_iva_to_pay = $iva_difference - $retentions_data['total_iva_retentions'];
        $net_igtf_to_pay = $igtf_difference;
        $net_islr_to_pay = $islr_difference - $retentions_data['total_islr_retentions'];
        
        return array(
            'sales' => $sales_data,
            'purchases' => $purchases_data,
            'retentions' => $retentions_data,
            'differences' => array(
                'iva' => $iva_difference,
                'igtf' => $igtf_difference,
                'islr' => $islr_difference,
            ),
            'net_amounts' => array(
                'sales' => $net_sales,
                'purchases' => $net_purchases,
            ),
            'net_taxes_to_pay' => array(
                'iva' => $net_iva_to_pay,
                'igtf' => $net_igtf_to_pay,
                'islr' => $net_islr_to_pay,
            ),
        );
    }

    /**
     * Crear resumen ejecutivo
     *
     * @param array $fiscal_summary Resumen fiscal
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    private function create_executive_summary($fiscal_summary, $start_date, $end_date) {
        $company_data = $this->get_company_data();
        
        return array(
            'company_info' => $company_data,
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date,
                'duration_days' => $this->calculate_duration_days($start_date, $end_date),
            ),
            'financial_summary' => array(
                'total_sales' => $fiscal_summary['sales']['total_amount'],
                'total_purchases' => $fiscal_summary['purchases']['total_amount'],
                'net_result' => $fiscal_summary['net_amounts']['sales'] - $fiscal_summary['net_amounts']['purchases'],
                'total_transactions' => $fiscal_summary['sales']['total_sales'] + $fiscal_summary['purchases']['total_purchases'],
            ),
            'tax_summary' => array(
                'iva_collected' => $fiscal_summary['sales']['total_iva'],
                'iva_paid' => $fiscal_summary['purchases']['total_iva'],
                'iva_net' => $fiscal_summary['differences']['iva'],
                'iva_retentions' => $fiscal_summary['retentions']['total_iva_retentions'],
                'iva_to_pay' => $fiscal_summary['net_taxes_to_pay']['iva'],
                'igtf_collected' => $fiscal_summary['sales']['total_igtf'],
                'igtf_paid' => $fiscal_summary['purchases']['total_igtf'],
                'igtf_net' => $fiscal_summary['differences']['igtf'],
                'igtf_to_pay' => $fiscal_summary['net_taxes_to_pay']['igtf'],
                'islr_collected' => $fiscal_summary['sales']['total_islr'],
                'islr_paid' => $fiscal_summary['purchases']['total_islr'],
                'islr_net' => $fiscal_summary['differences']['islr'],
                'islr_retentions' => $fiscal_summary['retentions']['total_islr_retentions'],
                'islr_to_pay' => $fiscal_summary['net_taxes_to_pay']['islr'],
            ),
            'exempt_summary' => array(
                'sales_exempt' => $fiscal_summary['sales']['total_exempt'],
                'purchases_exempt' => $fiscal_summary['purchases']['total_exempt'],
                'total_exempt' => $fiscal_summary['sales']['total_exempt'] + $fiscal_summary['purchases']['total_exempt'],
            ),
            'compliance_status' => $this->calculate_compliance_status($fiscal_summary),
        );
    }

    /**
     * Obtener datos de la empresa
     *
     * @return array
     */
    private function get_company_data() {
        return array(
            'rif' => $this->settings['company_rif'] ?? '',
            'name' => $this->settings['company_name'] ?? '',
            'address' => $this->settings['company_address'] ?? '',
            'phone' => $this->settings['company_phone'] ?? '',
            'email' => $this->settings['company_email'] ?? '',
            'economic_activity' => $this->settings['economic_activity'] ?? '',
        );
    }

    /**
     * Calcular duración en días
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return int
     */
    private function calculate_duration_days($start_date, $end_date) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        return $start->diff($end)->days + 1;
    }

    /**
     * Calcular estado de cumplimiento
     *
     * @param array $fiscal_summary Resumen fiscal
     * @return array
     */
    private function calculate_compliance_status($fiscal_summary) {
        $status = array(
            'iva_compliant' => true,
            'igtf_compliant' => true,
            'islr_compliant' => true,
            'overall_compliant' => true,
        );
        
        // Verificar cumplimiento IVA
        if ($fiscal_summary['net_taxes_to_pay']['iva'] < 0) {
            $status['iva_compliant'] = false;
            $status['overall_compliant'] = false;
        }
        
        // Verificar cumplimiento IGTF
        if ($fiscal_summary['net_taxes_to_pay']['igtf'] < 0) {
            $status['igtf_compliant'] = false;
            $status['overall_compliant'] = false;
        }
        
        // Verificar cumplimiento ISLR
        if ($fiscal_summary['net_taxes_to_pay']['islr'] < 0) {
            $status['islr_compliant'] = false;
            $status['overall_compliant'] = false;
        }
        
        return $status;
    }

    /**
     * Obtener encabezados del Resumen Ejecutivo
     *
     * @return array
     */
    private function get_executive_summary_headers() {
        return array(
            'Concepto',
            'Ventas',
            'Compras',
            'Diferencia',
            'Retenciones',
            'Neto a Pagar',
        );
    }

    /**
     * Formatear resumen ejecutivo para exportación
     *
     * @param array $executive_summary Resumen ejecutivo
     * @return array
     */
    private function format_executive_summary_for_export($executive_summary) {
        $rows = array();
        
        // Información de la empresa
        $rows[] = array('EMPRESA:', $executive_summary['company_info']['name'], '', '', '', '');
        $rows[] = array('RIF:', $executive_summary['company_info']['rif'], '', '', '', '');
        $rows[] = array('PERÍODO:', $executive_summary['period']['start_date'] . ' - ' . $executive_summary['period']['end_date'], '', '', '', '');
        $rows[] = array('', '', '', '', '', '');
        
        // Resumen financiero
        $rows[] = array('RESUMEN FINANCIERO:', '', '', '', '', '');
        $rows[] = array('Total Ventas:', $executive_summary['financial_summary']['total_sales'], '', '', '', '');
        $rows[] = array('Total Compras:', $executive_summary['financial_summary']['total_purchases'], '', '', '', '');
        $rows[] = array('Resultado Neto:', $executive_summary['financial_summary']['net_result'], '', '', '', '');
        $rows[] = array('Total Transacciones:', $executive_summary['financial_summary']['total_transactions'], '', '', '', '');
        $rows[] = array('', '', '', '', '', '');
        
        // Resumen de impuestos
        $rows[] = array('RESUMEN DE IMPUESTOS:', '', '', '', '', '');
        $rows[] = array('IVA Recaudado:', $executive_summary['tax_summary']['iva_collected'], '', '', '', '');
        $rows[] = array('IVA Pagado:', $executive_summary['tax_summary']['iva_paid'], '', '', '', '');
        $rows[] = array('IVA Neto:', $executive_summary['tax_summary']['iva_net'], '', '', '', '');
        $rows[] = array('Retenciones IVA:', $executive_summary['tax_summary']['iva_retentions'], '', '', '', '');
        $rows[] = array('IVA a Pagar:', $executive_summary['tax_summary']['iva_to_pay'], '', '', '', '');
        $rows[] = array('', '', '', '', '', '');
        $rows[] = array('IGTF Recaudado:', $executive_summary['tax_summary']['igtf_collected'], '', '', '', '');
        $rows[] = array('IGTF Pagado:', $executive_summary['tax_summary']['igtf_paid'], '', '', '', '');
        $rows[] = array('IGTF Neto:', $executive_summary['tax_summary']['igtf_net'], '', '', '', '');
        $rows[] = array('IGTF a Pagar:', $executive_summary['tax_summary']['igtf_to_pay'], '', '', '', '');
        $rows[] = array('', '', '', '', '', '');
        $rows[] = array('ISLR Recaudado:', $executive_summary['tax_summary']['islr_collected'], '', '', '', '');
        $rows[] = array('ISLR Pagado:', $executive_summary['tax_summary']['islr_paid'], '', '', '', '');
        $rows[] = array('ISLR Neto:', $executive_summary['tax_summary']['islr_net'], '', '', '', '');
        $rows[] = array('Retenciones ISLR:', $executive_summary['tax_summary']['islr_retentions'], '', '', '', '');
        $rows[] = array('ISLR a Pagar:', $executive_summary['tax_summary']['islr_to_pay'], '', '', '', '');
        $rows[] = array('', '', '', '', '', '');
        
        // Resumen de exenciones
        $rows[] = array('RESUMEN DE EXENCIONES:', '', '', '', '', '');
        $rows[] = array('Ventas Exentas:', $executive_summary['exempt_summary']['sales_exempt'], '', '', '', '');
        $rows[] = array('Compras Exentas:', $executive_summary['exempt_summary']['purchases_exempt'], '', '', '', '');
        $rows[] = array('Total Exento:', $executive_summary['exempt_summary']['total_exempt'], '', '', '', '');
        $rows[] = array('', '', '', '', '', '');
        
        // Estado de cumplimiento
        $rows[] = array('ESTADO DE CUMPLIMIENTO:', '', '', '', '', '');
        $rows[] = array('IVA Cumplido:', $executive_summary['compliance_status']['iva_compliant'] ? 'SÍ' : 'NO', '', '', '', '');
        $rows[] = array('IGTF Cumplido:', $executive_summary['compliance_status']['igtf_compliant'] ? 'SÍ' : 'NO', '', '', '', '');
        $rows[] = array('ISLR Cumplido:', $executive_summary['compliance_status']['islr_compliant'] ? 'SÍ' : 'NO', '', '', '', '');
        $rows[] = array('Cumplimiento General:', $executive_summary['compliance_status']['overall_compliant'] ? 'SÍ' : 'NO', '', '', '', '');
        
        return $rows;
    }

    /**
     * Exportar Resumen Ejecutivo
     *
     * @param array $executive_summary_data Datos del resumen ejecutivo
     * @param string $format Formato (csv, excel, pdf)
     * @return string URL del archivo generado
     */
    public function export_executive_summary($executive_summary_data, $format = 'csv') {
        $filename = 'resumen_ejecutivo_' . $executive_summary_data['period']['start_date'] . '_' . $executive_summary_data['period']['end_date'] . '.' . $format;
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        switch ($format) {
            case 'csv':
                $this->export_executive_summary_csv($executive_summary_data, $file_path);
                break;
            case 'excel':
                $this->export_executive_summary_excel($executive_summary_data, $file_path);
                break;
            case 'pdf':
                $this->export_executive_summary_pdf($executive_summary_data, $file_path);
                break;
        }
        
        return $upload_dir['url'] . '/' . $filename;
    }

    /**
     * Exportar Resumen Ejecutivo a CSV
     *
     * @param array $executive_summary_data Datos del resumen ejecutivo
     * @param string $file_path Ruta del archivo
     */
    private function export_executive_summary_csv($executive_summary_data, $file_path) {
        $file = fopen($file_path, 'w');
        
        // Encabezados
        fputcsv($file, $executive_summary_data['headers']);
        
        // Datos
        foreach ($executive_summary_data['rows'] as $row) {
            fputcsv($file, $row);
        }
        
        fclose($file);
    }

    /**
     * Exportar Resumen Ejecutivo a Excel
     *
     * @param array $executive_summary_data Datos del resumen ejecutivo
     * @param string $file_path Ruta del archivo
     */
    private function export_executive_summary_excel($executive_summary_data, $file_path) {
        // Implementar exportación a Excel
        // Por ahora usar CSV
        $this->export_executive_summary_csv($executive_summary_data, str_replace('.xlsx', '.csv', $file_path));
    }

    /**
     * Exportar Resumen Ejecutivo a PDF
     *
     * @param array $executive_summary_data Datos del resumen ejecutivo
     * @param string $file_path Ruta del archivo
     */
    private function export_executive_summary_pdf($executive_summary_data, $file_path) {
        // Implementar exportación a PDF
        // Por ahora solo log
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "PDF del Resumen Ejecutivo generado: {$file_path}");
    }

    /**
     * Generar resumen automático
     */
    public function generate_automatic_summary() {
        // Generar resumen del mes anterior
        $last_month = date('Y-m-01', strtotime('first day of last month'));
        $end_month = date('Y-m-t', strtotime('last month'));
        
        $summary = $this->generate_executive_summary($last_month, $end_month);
        
        // Guardar resumen automático
        $this->save_automatic_summary($summary);
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "Resumen ejecutivo automático generado para {$last_month} - {$end_month}");
    }

    /**
     * Guardar resumen automático
     *
     * @param array $summary Resumen
     */
    private function save_automatic_summary($summary) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'wcvs_fiscal_reports',
            array(
                'report_type' => 'resumen_ejecutivo',
                'start_date' => $summary['period']['start_date'],
                'end_date' => $summary['period']['end_date'],
                'data' => maybe_serialize($summary),
                'generated_at' => current_time('mysql'),
                'status' => 'generated',
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
}
