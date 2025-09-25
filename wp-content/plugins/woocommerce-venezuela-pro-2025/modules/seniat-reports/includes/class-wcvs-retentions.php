<?php
/**
 * Sistema de Retenciones - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar retenciones IVA e ISLR
 */
class WCVS_Retentions {

    /**
     * Configuraciones
     *
     * @var array
     */
    private $settings;

    /**
     * Tipos de retenciones
     *
     * @var array
     */
    private $retention_types = array(
        'iva' => 'Retención IVA',
        'islr' => 'Retención ISLR',
    );

    /**
     * Tasas de retención
     *
     * @var array
     */
    private $retention_rates = array(
        'iva' => 75.0,  // 75% del IVA
        'islr' => 100.0, // 100% del ISLR
    );

    /**
     * Montos mínimos para retención
     *
     * @var array
     */
    private $minimum_amounts = array(
        'iva' => 1000.0,  // Bs. 1,000
        'islr' => 500.0,  // Bs. 500
    );

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
        // Hook para procesar retenciones
        add_action('woocommerce_payment_complete', array($this, 'process_retentions'));
        add_action('woocommerce_order_status_completed', array($this, 'process_retentions'));
        
        // Hook para mostrar retenciones en admin
        add_action('woocommerce_admin_order_totals_after_total', array($this, 'display_retentions_admin'));
        
        // Hook para mostrar retenciones en frontend
        add_action('woocommerce_order_details_after_order_table', array($this, 'display_retentions_frontend'));
    }

    /**
     * Procesar retenciones
     *
     * @param int $order_id ID del pedido
     */
    public function process_retentions($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Verificar si ya fueron procesadas las retenciones
        if ($order->get_meta('_retentions_processed')) {
            return;
        }

        // Calcular retenciones
        $retentions = $this->calculate_retentions($order);
        
        // Guardar retenciones
        $this->save_retentions($order, $retentions);
        
        // Marcar como procesadas
        $order->update_meta_data('_retentions_processed', true);
        $order->save();
        
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "Retenciones procesadas para pedido: #{$order_id}");
    }

    /**
     * Calcular retenciones
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function calculate_retentions($order) {
        $retentions = array();
        
        // Calcular retención IVA
        $iva_retention = $this->calculate_iva_retention($order);
        if ($iva_retention['amount'] > 0) {
            $retentions['iva'] = $iva_retention;
        }
        
        // Calcular retención ISLR
        $islr_retention = $this->calculate_islr_retention($order);
        if ($islr_retention['amount'] > 0) {
            $retentions['islr'] = $islr_retention;
        }
        
        return $retentions;
    }

    /**
     * Calcular retención IVA
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function calculate_iva_retention($order) {
        $iva_amount = $order->get_meta('_iva_amount');
        $iva_base = $order->get_meta('_iva_base_amount');
        
        // Verificar monto mínimo
        if ($iva_base < $this->minimum_amounts['iva']) {
            return array(
                'type' => 'iva',
                'base_amount' => $iva_base,
                'rate' => $this->retention_rates['iva'],
                'amount' => 0,
                'applies' => false,
                'reason' => 'below_minimum',
            );
        }
        
        $retention_amount = $iva_amount * ($this->retention_rates['iva'] / 100);
        
        return array(
            'type' => 'iva',
            'base_amount' => $iva_base,
            'iva_amount' => $iva_amount,
            'rate' => $this->retention_rates['iva'],
            'amount' => $retention_amount,
            'applies' => true,
        );
    }

    /**
     * Calcular retención ISLR
     *
     * @param WC_Order $order Pedido
     * @return array
     */
    private function calculate_islr_retention($order) {
        $islr_amount = $order->get_meta('_islr_amount');
        $islr_base = $order->get_meta('_islr_base_amount');
        
        // Verificar monto mínimo
        if ($islr_base < $this->minimum_amounts['islr']) {
            return array(
                'type' => 'islr',
                'base_amount' => $islr_base,
                'rate' => $this->retention_rates['islr'],
                'amount' => 0,
                'applies' => false,
                'reason' => 'below_minimum',
            );
        }
        
        $retention_amount = $islr_amount * ($this->retention_rates['islr'] / 100);
        
        return array(
            'type' => 'islr',
            'base_amount' => $islr_base,
            'islr_amount' => $islr_amount,
            'rate' => $this->retention_rates['islr'],
            'amount' => $retention_amount,
            'applies' => true,
        );
    }

    /**
     * Guardar retenciones
     *
     * @param WC_Order $order Pedido
     * @param array $retentions Retenciones
     */
    private function save_retentions($order, $retentions) {
        foreach ($retentions as $type => $retention) {
            $order->update_meta_data("_retention_{$type}_base", $retention['base_amount']);
            $order->update_meta_data("_retention_{$type}_rate", $retention['rate']);
            $order->update_meta_data("_retention_{$type}_amount", $retention['amount']);
            $order->update_meta_data("_retention_{$type}_applies", $retention['applies']);
            
            if (isset($retention['reason'])) {
                $order->update_meta_data("_retention_{$type}_reason", $retention['reason']);
            }
        }
        
        $order->save();
        
        // Guardar en base de datos para reportes
        $this->save_retentions_to_database($order, $retentions);
    }

    /**
     * Guardar retenciones en base de datos
     *
     * @param WC_Order $order Pedido
     * @param array $retentions Retenciones
     */
    private function save_retentions_to_database($order, $retentions) {
        global $wpdb;
        
        foreach ($retentions as $type => $retention) {
            $wpdb->insert(
                $wpdb->prefix . 'wcvs_retentions',
                array(
                    'order_id' => $order->get_id(),
                    'retention_type' => $type,
                    'base_amount' => $retention['base_amount'],
                    'rate' => $retention['rate'],
                    'amount' => $retention['amount'],
                    'applies' => $retention['applies'],
                    'reason' => $retention['reason'] ?? '',
                    'created_at' => current_time('mysql'),
                ),
                array('%d', '%s', '%f', '%f', '%f', '%d', '%s', '%s')
            );
        }
    }

    /**
     * Mostrar retenciones en admin
     *
     * @param int $order_id ID del pedido
     */
    public function display_retentions_admin($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $iva_retention = $this->get_retention_data($order, 'iva');
        $islr_retention = $this->get_retention_data($order, 'islr');
        
        if ($iva_retention || $islr_retention) {
            ?>
            <tr class="wcvs-retentions-section">
                <td class="label" colspan="2"><strong>Retenciones:</strong></td>
            </tr>
            <?php if ($iva_retention): ?>
            <tr class="wcvs-retentions-breakdown">
                <td class="label">Retención IVA (<?php echo esc_html($iva_retention['rate']); ?>%):</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($iva_retention['amount']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($islr_retention): ?>
            <tr class="wcvs-retentions-breakdown">
                <td class="label">Retención ISLR (<?php echo esc_html($islr_retention['rate']); ?>%):</td>
                <td width="1%"></td>
                <td class="total"><?php echo wc_price($islr_retention['amount']); ?></td>
            </tr>
            <?php endif; ?>
            <?php
        }
    }

    /**
     * Mostrar retenciones en frontend
     *
     * @param WC_Order $order Pedido
     */
    public function display_retentions_frontend($order) {
        $iva_retention = $this->get_retention_data($order, 'iva');
        $islr_retention = $this->get_retention_data($order, 'islr');
        
        if ($iva_retention || $islr_retention) {
            ?>
            <div class="wcvs-retentions-frontend">
                <h3>Retenciones Aplicadas</h3>
                <table class="shop_table">
                    <tbody>
                        <?php if ($iva_retention): ?>
                        <tr>
                            <th>Retención IVA (<?php echo esc_html($iva_retention['rate']); ?>%):</th>
                            <td><?php echo wc_price($iva_retention['amount']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($islr_retention): ?>
                        <tr>
                            <th>Retención ISLR (<?php echo esc_html($islr_retention['rate']); ?>%):</th>
                            <td><?php echo wc_price($islr_retention['amount']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    }

    /**
     * Obtener datos de retención
     *
     * @param WC_Order $order Pedido
     * @param string $type Tipo de retención
     * @return array|null
     */
    private function get_retention_data($order, $type) {
        $base_amount = $order->get_meta("_retention_{$type}_base");
        $rate = $order->get_meta("_retention_{$type}_rate");
        $amount = $order->get_meta("_retention_{$type}_amount");
        $applies = $order->get_meta("_retention_{$type}_applies");
        
        if (!$applies || $amount <= 0) {
            return null;
        }
        
        return array(
            'base_amount' => $base_amount,
            'rate' => $rate,
            'amount' => $amount,
        );
    }

    /**
     * Generar reporte de retenciones IVA
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function generate_iva_retentions_report($start_date, $end_date) {
        global $wpdb;
        
        $retentions = $wpdb->get_results($wpdb->prepare("
            SELECT 
                r.*,
                p.post_date as order_date,
                p.ID as order_id
            FROM {$wpdb->prefix}wcvs_retentions r
            INNER JOIN {$wpdb->posts} p ON r.order_id = p.ID
            WHERE r.retention_type = 'iva'
            AND p.post_date >= %s
            AND p.post_date <= %s
            ORDER BY p.post_date ASC
        ", $start_date, $end_date));
        
        $summary = $this->calculate_retentions_summary($retentions);
        
        return array(
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date,
            ),
            'summary' => $summary,
            'retentions' => $retentions,
            'headers' => $this->get_iva_retentions_headers(),
            'rows' => $this->format_iva_retentions_for_export($retentions),
        );
    }

    /**
     * Generar reporte de retenciones ISLR
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function generate_islr_retentions_report($start_date, $end_date) {
        global $wpdb;
        
        $retentions = $wpdb->get_results($wpdb->prepare("
            SELECT 
                r.*,
                p.post_date as order_date,
                p.ID as order_id
            FROM {$wpdb->prefix}wcvs_retentions r
            INNER JOIN {$wpdb->posts} p ON r.order_id = p.ID
            WHERE r.retention_type = 'islr'
            AND p.post_date >= %s
            AND p.post_date <= %s
            ORDER BY p.post_date ASC
        ", $start_date, $end_date));
        
        $summary = $this->calculate_retentions_summary($retentions);
        
        return array(
            'period' => array(
                'start_date' => $start_date,
                'end_date' => $end_date,
            ),
            'summary' => $summary,
            'retentions' => $retentions,
            'headers' => $this->get_islr_retentions_headers(),
            'rows' => $this->format_islr_retentions_for_export($retentions),
        );
    }

    /**
     * Calcular resumen de retenciones
     *
     * @param array $retentions Retenciones
     * @return array
     */
    private function calculate_retentions_summary($retentions) {
        $total_base = 0;
        $total_retentions = 0;
        $total_count = 0;
        
        foreach ($retentions as $retention) {
            $total_base += floatval($retention->base_amount);
            $total_retentions += floatval($retention->amount);
            $total_count++;
        }
        
        return array(
            'total_base_amount' => $total_base,
            'total_retentions' => $total_retentions,
            'total_count' => $total_count,
            'average_retention' => $total_count > 0 ? $total_retentions / $total_count : 0,
        );
    }

    /**
     * Obtener encabezados de retenciones IVA
     *
     * @return array
     */
    private function get_iva_retentions_headers() {
        return array(
            'Número de Factura',
            'Fecha de Factura',
            'Base Imponible IVA',
            'IVA Calculado',
            'Tasa de Retención',
            'Retención IVA',
            'Fecha de Retención',
        );
    }

    /**
     * Obtener encabezados de retenciones ISLR
     *
     * @return array
     */
    private function get_islr_retentions_headers() {
        return array(
            'Número de Factura',
            'Fecha de Factura',
            'Base Imponible ISLR',
            'ISLR Calculado',
            'Tasa de Retención',
            'Retención ISLR',
            'Fecha de Retención',
        );
    }

    /**
     * Formatear retenciones IVA para exportación
     *
     * @param array $retentions Retenciones
     * @return array
     */
    private function format_iva_retentions_for_export($retentions) {
        $rows = array();
        
        foreach ($retentions as $retention) {
            $rows[] = array(
                '#' . $retention->order_id,
                $retention->order_date,
                $retention->base_amount,
                $retention->base_amount * 0.16, // IVA del 16%
                $retention->rate . '%',
                $retention->amount,
                $retention->created_at,
            );
        }
        
        return $rows;
    }

    /**
     * Formatear retenciones ISLR para exportación
     *
     * @param array $retentions Retenciones
     * @return array
     */
    private function format_islr_retentions_for_export($retentions) {
        $rows = array();
        
        foreach ($retentions as $retention) {
            $rows[] = array(
                '#' . $retention->order_id,
                $retention->order_date,
                $retention->base_amount,
                $retention->base_amount * 0.34, // ISLR aproximado
                $retention->rate . '%',
                $retention->amount,
                $retention->created_at,
            );
        }
        
        return $rows;
    }

    /**
     * Exportar reporte de retenciones
     *
     * @param array $retentions_data Datos de retenciones
     * @param string $type Tipo de retención
     * @param string $format Formato (csv, excel, pdf)
     * @return string URL del archivo generado
     */
    public function export_retentions_report($retentions_data, $type, $format = 'csv') {
        $filename = "retenciones_{$type}_" . $retentions_data['period']['start_date'] . '_' . $retentions_data['period']['end_date'] . '.' . $format;
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        switch ($format) {
            case 'csv':
                $this->export_retentions_csv($retentions_data, $file_path);
                break;
            case 'excel':
                $this->export_retentions_excel($retentions_data, $file_path);
                break;
            case 'pdf':
                $this->export_retentions_pdf($retentions_data, $file_path);
                break;
        }
        
        return $upload_dir['url'] . '/' . $filename;
    }

    /**
     * Exportar retenciones a CSV
     *
     * @param array $retentions_data Datos de retenciones
     * @param string $file_path Ruta del archivo
     */
    private function export_retentions_csv($retentions_data, $file_path) {
        $file = fopen($file_path, 'w');
        
        // Encabezados
        fputcsv($file, $retentions_data['headers']);
        
        // Datos
        foreach ($retentions_data['rows'] as $row) {
            fputcsv($file, $row);
        }
        
        // Resumen
        fputcsv($file, array('', '', '', '', '', 'RESUMEN:', '', ''));
        fputcsv($file, array('', '', '', '', '', 'Total Base:', $retentions_data['summary']['total_base_amount'], ''));
        fputcsv($file, array('', '', '', '', '', 'Total Retenciones:', $retentions_data['summary']['total_retentions'], ''));
        fputcsv($file, array('', '', '', '', '', 'Total Facturas:', $retentions_data['summary']['total_count'], ''));
        fputcsv($file, array('', '', '', '', '', 'Promedio Retención:', $retentions_data['summary']['average_retention'], ''));
        
        fclose($file);
    }

    /**
     * Exportar retenciones a Excel
     *
     * @param array $retentions_data Datos de retenciones
     * @param string $file_path Ruta del archivo
     */
    private function export_retentions_excel($retentions_data, $file_path) {
        // Implementar exportación a Excel
        // Por ahora usar CSV
        $this->export_retentions_csv($retentions_data, str_replace('.xlsx', '.csv', $file_path));
    }

    /**
     * Exportar retenciones a PDF
     *
     * @param array $retentions_data Datos de retenciones
     * @param string $file_path Ruta del archivo
     */
    private function export_retentions_pdf($retentions_data, $file_path) {
        // Implementar exportación a PDF
        // Por ahora solo log
        WCVS_Logger::info(WCVS_Logger::CONTEXT_SENIAT, "PDF de retenciones generado: {$file_path}");
    }

    /**
     * Obtener estadísticas de retenciones
     *
     * @param string $start_date Fecha de inicio
     * @param string $end_date Fecha de fin
     * @return array
     */
    public function get_retentions_statistics($start_date, $end_date) {
        global $wpdb;
        
        $stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_retentions,
                SUM(CAST(r.base_amount AS DECIMAL(10,2))) as total_base,
                SUM(CAST(r.amount AS DECIMAL(10,2))) as total_retentions_amount,
                AVG(CAST(r.amount AS DECIMAL(10,2))) as average_retention
            FROM {$wpdb->prefix}wcvs_retentions r
            INNER JOIN {$wpdb->posts} p ON r.order_id = p.ID
            WHERE p.post_date >= %s
            AND p.post_date <= %s
        ", $start_date, $end_date));
        
        return array(
            'total_retentions' => intval($stats->total_retentions ?? 0),
            'total_base' => floatval($stats->total_base ?? 0),
            'total_retentions_amount' => floatval($stats->total_retentions_amount ?? 0),
            'average_retention' => floatval($stats->average_retention ?? 0),
        );
    }

    /**
     * Obtener tasas de retención
     *
     * @return array
     */
    public function get_retention_rates() {
        return $this->retention_rates;
    }

    /**
     * Establecer tasas de retención
     *
     * @param array $rates Tasas
     */
    public function set_retention_rates($rates) {
        $this->retention_rates = array_merge($this->retention_rates, $rates);
    }

    /**
     * Obtener montos mínimos
     *
     * @return array
     */
    public function get_minimum_amounts() {
        return $this->minimum_amounts;
    }

    /**
     * Establecer montos mínimos
     *
     * @param array $amounts Montos
     */
    public function set_minimum_amounts($amounts) {
        $this->minimum_amounts = array_merge($this->minimum_amounts, $amounts);
    }
}
