<?php
/**
 * Sistema de Exportación SENIAT
 * 
 * @package Woocommerce_Venezuela_Pro_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_SENIAT_Exporter {
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_seniat_menu' ), 20 );
        add_action( 'wp_ajax_wvp_export_seniat', array( $this, 'ajax_export_seniat' ) );
        add_action( 'wp_ajax_wvp_generate_invoice', array( $this, 'ajax_generate_invoice' ) );
    }
    
    /**
     * Agregar menú SENIAT al admin
     */
    public function add_seniat_menu() {
        add_submenu_page(
            'wvp-dashboard',
            'Exportación SENIAT',
            'SENIAT',
            'manage_woocommerce',
            'wvp-seniat',
            array( $this, 'seniat_page' )
        );
    }
    
    /**
     * Página principal de SENIAT
     */
    public function seniat_page() {
        ?>
        <div class="wrap wvp-seniat-page">
            <h1>📊 Exportación SENIAT - WooCommerce Venezuela Pro 2025</h1>
            
            <div class="wvp-seniat-header">
                <div class="wvp-seniat-info">
                    <h2>🏛️ Cumplimiento Fiscal Venezolano</h2>
                    <p>Sistema completo para generar reportes y facturas requeridos por SENIAT</p>
                </div>
            </div>
            
            <div class="wvp-seniat-sections">
                <!-- Libro de Ventas -->
                <div class="wvp-seniat-section">
                    <div class="wvp-section-header">
                        <h3>📊 Libro de Ventas</h3>
                        <p>Exportación completa de todas las ventas por período</p>
                    </div>
                    
                    <div class="wvp-export-form">
                        <form id="wvp-sales-book-form">
                            <div class="wvp-form-row">
                                <div class="wvp-form-group">
                                    <label for="start_date">Fecha Inicio:</label>
                                    <input type="date" id="start_date" name="start_date" required>
                                </div>
                                <div class="wvp-form-group">
                                    <label for="end_date">Fecha Fin:</label>
                                    <input type="date" id="end_date" name="end_date" required>
                                </div>
                            </div>
                            
                            <div class="wvp-form-row">
                                <div class="wvp-form-group">
                                    <label for="export_format">Formato de Exportación:</label>
                                    <select id="export_format" name="export_format">
                                        <option value="csv">CSV (Excel)</option>
                                        <option value="xml">XML</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </div>
                                <div class="wvp-form-group">
                                    <label for="include_details">Incluir Detalles:</label>
                                    <select id="include_details" name="include_details">
                                        <option value="summary">Solo Resumen</option>
                                        <option value="detailed">Detallado</option>
                                        <option value="full">Completo</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="wvp-form-actions">
                                <button type="submit" class="wvp-btn wvp-btn-primary">
                                    📊 Generar Libro de Ventas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Reporte de IGTF -->
                <div class="wvp-seniat-section">
                    <div class="wvp-section-header">
                        <h3>💰 Reporte de IGTF</h3>
                        <p>Cálculo y exportación del Impuesto a las Grandes Transacciones Financieras</p>
                    </div>
                    
                    <div class="wvp-export-form">
                        <form id="wvp-igtf-form">
                            <div class="wvp-form-row">
                                <div class="wvp-form-group">
                                    <label for="igtf_start_date">Fecha Inicio:</label>
                                    <input type="date" id="igtf_start_date" name="start_date" required>
                                </div>
                                <div class="wvp-form-group">
                                    <label for="igtf_end_date">Fecha Fin:</label>
                                    <input type="date" id="igtf_end_date" name="end_date" required>
                                </div>
                            </div>
                            
                            <div class="wvp-form-actions">
                                <button type="submit" class="wvp-btn wvp-btn-primary">
                                    💰 Generar Reporte IGTF
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Declaración de IVA -->
                <div class="wvp-seniat-section">
                    <div class="wvp-section-header">
                        <h3>📋 Declaración de IVA</h3>
                        <p>Reporte mensual de Impuesto al Valor Agregado</p>
                    </div>
                    
                    <div class="wvp-export-form">
                        <form id="wvp-iva-form">
                            <div class="wvp-form-row">
                                <div class="wvp-form-group">
                                    <label for="iva_month">Mes:</label>
                                    <select id="iva_month" name="month" required>
                                        <option value="01">Enero</option>
                                        <option value="02">Febrero</option>
                                        <option value="03">Marzo</option>
                                        <option value="04">Abril</option>
                                        <option value="05">Mayo</option>
                                        <option value="06">Junio</option>
                                        <option value="07">Julio</option>
                                        <option value="08">Agosto</option>
                                        <option value="09">Septiembre</option>
                                        <option value="10">Octubre</option>
                                        <option value="11">Noviembre</option>
                                        <option value="12">Diciembre</option>
                                    </select>
                                </div>
                                <div class="wvp-form-group">
                                    <label for="iva_year">Año:</label>
                                    <select id="iva_year" name="year" required>
                                        <?php
                                        $current_year = date('Y');
                                        for ($i = $current_year; $i >= $current_year - 5; $i--) {
                                            echo "<option value='$i'>$i</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="wvp-form-actions">
                                <button type="submit" class="wvp-btn wvp-btn-primary">
                                    📋 Generar Declaración IVA
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Facturas Impresas -->
                <div class="wvp-seniat-section">
                    <div class="wvp-section-header">
                        <h3>📄 Facturas Impresas</h3>
                        <p>Generación de facturas para entrega física a SENIAT</p>
                    </div>
                    
                    <div class="wvp-export-form">
                        <form id="wvp-invoices-form">
                            <div class="wvp-form-row">
                                <div class="wvp-form-group">
                                    <label for="invoice_start_date">Fecha Inicio:</label>
                                    <input type="date" id="invoice_start_date" name="start_date" required>
                                </div>
                                <div class="wvp-form-group">
                                    <label for="invoice_end_date">Fecha Fin:</label>
                                    <input type="date" id="invoice_end_date" name="end_date" required>
                                </div>
                            </div>
                            
                            <div class="wvp-form-row">
                                <div class="wvp-form-group">
                                    <label for="invoice_format">Formato:</label>
                                    <select id="invoice_format" name="format">
                                        <option value="pdf">PDF Individual</option>
                                        <option value="pdf_batch">PDF Lote</option>
                                        <option value="print">Imprimir Directo</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="wvp-form-actions">
                                <button type="submit" class="wvp-btn wvp-btn-primary">
                                    📄 Generar Facturas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            
            <!-- Área de Resultados -->
            <div id="wvp-export-results" class="wvp-export-results" style="display: none;">
                <h3>📊 Resultados de Exportación</h3>
                <div id="wvp-results-content"></div>
            </div>
        </div>
        
        <?php
    }
    
    /**
     * AJAX: Exportar datos SENIAT
     */
    public function ajax_export_seniat() {
        // Verificar nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wvp_seniat_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Nonce inválido' ) );
            return;
        }
        
        // Verificar permisos
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => 'No tienes permisos para realizar esta acción' ) );
            return;
        }
        
        // Validar datos de entrada
        $export_type = sanitize_text_field( $_POST['export_type'] ?? '' );
        if ( empty( $export_type ) ) {
            wp_send_json_error( array( 'message' => 'Tipo de exportación requerido' ) );
            return;
        }
        
        try {
            switch ( $export_type ) {
                case 'sales_book':
                    $start_date = sanitize_text_field( $_POST['start_date'] ?? '' );
                    $end_date = sanitize_text_field( $_POST['end_date'] ?? '' );
                    $format = sanitize_text_field( $_POST['export_format'] ?? 'csv' );
                    
                    if ( empty( $start_date ) || empty( $end_date ) ) {
                        wp_send_json_error( array( 'message' => 'Fechas de inicio y fin requeridas' ) );
                        return;
                    }
                    
                    $result = $this->generate_sales_book( $start_date, $end_date, $format );
                    break;
                    
                case 'igtf':
                    $start_date = sanitize_text_field( $_POST['start_date'] ?? '' );
                    $end_date = sanitize_text_field( $_POST['end_date'] ?? '' );
                    $format = sanitize_text_field( $_POST['export_format'] ?? 'csv' );
                    
                    if ( empty( $start_date ) || empty( $end_date ) ) {
                        wp_send_json_error( array( 'message' => 'Fechas de inicio y fin requeridas' ) );
                        return;
                    }
                    
                    $result = $this->generate_igtf_report( $start_date, $end_date, $format );
                    break;
                    
                case 'iva':
                    $month = sanitize_text_field( $_POST['month'] ?? '' );
                    $year = sanitize_text_field( $_POST['year'] ?? '' );
                    $format = sanitize_text_field( $_POST['export_format'] ?? 'csv' );
                    
                    if ( empty( $month ) || empty( $year ) ) {
                        wp_send_json_error( array( 'message' => 'Mes y año requeridos' ) );
                        return;
                    }
                    
                    $result = $this->generate_iva_declaration( $month, $year, $format );
                    break;
                    
                case 'invoices':
                    $start_date = sanitize_text_field( $_POST['start_date'] ?? '' );
                    $end_date = sanitize_text_field( $_POST['end_date'] ?? '' );
                    $format = sanitize_text_field( $_POST['format'] ?? 'pdf' );
                    
                    if ( empty( $start_date ) || empty( $end_date ) ) {
                        wp_send_json_error( array( 'message' => 'Fechas de inicio y fin requeridas' ) );
                        return;
                    }
                    
                    $result = $this->generate_invoices( $start_date, $end_date, $format );
                    break;
                    
                default:
                    wp_send_json_error( array( 'message' => 'Tipo de exportación no válido: ' . $export_type ) );
                    return;
            }
            
            wp_send_json_success( $result );
            
        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => 'Error interno: ' . $e->getMessage() ) );
        }
    }
    
    /**
     * Generar Libro de Ventas
     */
    private function generate_sales_book( $start_date, $end_date, $format ) {
        // Obtener órdenes del período
        $orders = wc_get_orders( array(
            'limit' => -1,
            'status' => array( 'wc-completed', 'wc-processing' ),
            'date_created' => $start_date . '...' . $end_date
        ));
        
        $sales_data = array();
        $total_usd = 0;
        $total_ves = 0;
        $bcv_rate = get_option( 'wvp_emergency_rate', 36.5 );
        
        foreach ( $orders as $order ) {
            $order_data = array(
                'order_id' => $order->get_id(),
                'date' => $order->get_date_created()->format( 'Y-m-d H:i:s' ),
                'customer' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'customer_rif' => $order->get_meta( '_billing_rif' ) ?: 'N/A',
                'subtotal_usd' => $order->get_subtotal(),
                'tax_usd' => $order->get_total_tax(),
                'total_usd' => $order->get_total(),
                'subtotal_ves' => $order->get_subtotal() * $bcv_rate,
                'tax_ves' => $order->get_total_tax() * $bcv_rate,
                'total_ves' => $order->get_total() * $bcv_rate,
                'payment_method' => $order->get_payment_method_title(),
                'status' => $order->get_status()
            );
            
            $sales_data[] = $order_data;
            $total_usd += $order->get_total();
            $total_ves += $order->get_total() * $bcv_rate;
        }
        
        // Generar archivo según formato
        switch ( $format ) {
            case 'csv':
                return $this->generate_csv_export( $sales_data, 'libro_ventas_' . $start_date . '_' . $end_date );
            case 'xml':
                return $this->generate_xml_export( $sales_data, 'libro_ventas_' . $start_date . '_' . $end_date );
            case 'pdf':
                return $this->generate_pdf_export( $sales_data, 'libro_ventas_' . $start_date . '_' . $end_date );
            default:
                return array(
                    'html' => '<div class="wvp-success"><h4>📊 Libro de Ventas Generado</h4><p><strong>Período:</strong> ' . $start_date . ' a ' . $end_date . '</p><p><strong>Total USD:</strong> $' . number_format( $total_usd, 2 ) . '</p><p><strong>Total VES:</strong> ' . number_format( $total_ves, 2 ) . '</p><p><strong>Tasa BCV:</strong> ' . number_format( $bcv_rate, 2 ) . '</p><p><strong>Registros:</strong> ' . count( $sales_data ) . '</p></div>'
                );
        }
    }
    
    
    /**
     * Generar exportación CSV
     */
    private function generate_csv_export( $data, $filename ) {
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/wvp-exports/' . $filename . '.csv';
        
        // Crear directorio si no existe
        if ( ! file_exists( dirname( $file_path ) ) ) {
            wp_mkdir_p( dirname( $file_path ) );
        }
        
        $file = fopen( $file_path, 'w' );
        
        // Headers CSV
        fputcsv( $file, array(
            'Pedido', 'Fecha', 'Cliente', 'RIF', 'Subtotal USD', 'Impuestos USD', 'Total USD',
            'Subtotal VES', 'Impuestos VES', 'Total VES', 'Método Pago', 'Estado'
        ));
        
        // Datos
        foreach ( $data as $row ) {
            fputcsv( $file, array(
                $row['order_id'],
                $row['date'],
                $row['customer'],
                $row['customer_rif'],
                $row['subtotal_usd'],
                $row['tax_usd'],
                $row['total_usd'],
                $row['subtotal_ves'],
                $row['tax_ves'],
                $row['total_ves'],
                $row['payment_method'],
                $row['status']
            ));
        }
        
        fclose( $file );
        
        return array(
            'file_url' => $upload_dir['baseurl'] . '/wvp-exports/' . $filename . '.csv',
            'file_path' => $file_path,
            'filename' => $filename . '.csv',
            'html' => '<div class="wvp-success"><h4>✅ Archivo CSV generado exitosamente</h4><p><a href="' . $upload_dir['baseurl'] . '/wvp-exports/' . $filename . '.csv" class="wvp-btn wvp-btn-primary" download>📥 Descargar CSV</a></p></div>'
        );
    }
    
    /**
     * Generar exportación XML
     */
    private function generate_xml_export( $data, $filename ) {
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/wvp-exports/' . $filename . '.xml';
        
        // Crear directorio si no existe
        if ( ! file_exists( dirname( $file_path ) ) ) {
            wp_mkdir_p( dirname( $file_path ) );
        }
        
        $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><libro_ventas></libro_ventas>' );
        $xml->addAttribute( 'fecha_generacion', current_time( 'Y-m-d H:i:s' ) );
        $xml->addAttribute( 'total_registros', count( $data ) );
        
        foreach ( $data as $sale ) {
            $venta = $xml->addChild( 'venta' );
            $venta->addChild( 'pedido', $sale['order_id'] );
            $venta->addChild( 'fecha', $sale['date'] );
            $venta->addChild( 'cliente', htmlspecialchars( $sale['customer'] ) );
            $venta->addChild( 'rif', $sale['customer_rif'] );
            $venta->addChild( 'subtotal_usd', $sale['subtotal_usd'] );
            $venta->addChild( 'impuestos_usd', $sale['tax_usd'] );
            $venta->addChild( 'total_usd', $sale['total_usd'] );
            $venta->addChild( 'subtotal_ves', $sale['subtotal_ves'] );
            $venta->addChild( 'impuestos_ves', $sale['tax_ves'] );
            $venta->addChild( 'total_ves', $sale['total_ves'] );
            $venta->addChild( 'metodo_pago', htmlspecialchars( $sale['payment_method'] ) );
            $venta->addChild( 'estado', $sale['status'] );
        }
        
        $xml->asXML( $file_path );
        
        return array(
            'file_url' => $upload_dir['baseurl'] . '/wvp-exports/' . $filename . '.xml',
            'file_path' => $file_path,
            'filename' => $filename . '.xml',
            'html' => '<div class="wvp-success"><h4>✅ Archivo XML generado exitosamente</h4><p><a href="' . $upload_dir['baseurl'] . '/wvp-exports/' . $filename . '.xml" class="wvp-btn wvp-btn-primary" download>📥 Descargar XML</a></p></div>'
        );
    }
    
    /**
     * Generar exportación PDF
     */
    private function generate_pdf_export( $data, $filename ) {
        // Generar HTML optimizado para impresión/PDF
        $html = $this->generate_printable_html( $data, $filename );
        
        // Guardar archivo HTML para impresión
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/wvp-exports/' . $filename . '.html';
        
        // Crear directorio si no existe
        if ( ! file_exists( dirname( $file_path ) ) ) {
            wp_mkdir_p( dirname( $file_path ) );
        }
        
        file_put_contents( $file_path, $html );
        
        return array(
            'html' => '<div class="wvp-success"><h4>📄 Reporte PDF Generado</h4><p><strong>Archivo:</strong> ' . $filename . '.html</p><p><strong>Ubicación:</strong> ' . $upload_dir['baseurl'] . '/wvp-exports/' . $filename . '.html</p><div style="margin-top: 15px;"><a href="' . $upload_dir['baseurl'] . '/wvp-exports/' . $filename . '.html" target="_blank" class="wvp-btn wvp-btn-primary">📄 Ver Reporte</a> <button class="wvp-btn wvp-btn-secondary" onclick="window.print()">🖨️ Imprimir</button></div></div>',
            'file_url' => $upload_dir['baseurl'] . '/wvp-exports/' . $filename . '.html'
        );
    }
    
    /**
     * Generar HTML optimizado para impresión
     */
    private function generate_printable_html( $data, $filename ) {
        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $filename . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1e3a8a; padding-bottom: 20px; }
        .header h1 { color: #1e3a8a; margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        .summary { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .summary h3 { margin-top: 0; color: #1e3a8a; }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .summary-item { background: white; padding: 15px; border-radius: 6px; text-align: center; }
        .summary-item strong { display: block; font-size: 18px; color: #1e3a8a; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #1e3a8a; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
        @media print { body { margin: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 ' . str_replace('_', ' ', ucwords($filename)) . '</h1>
        <p>Generado el ' . current_time('d/m/Y H:i:s') . '</p>
        <p>WooCommerce Venezuela Pro 2025</p>
    </div>';
        
        if ( ! empty( $data ) ) {
            // Calcular totales
            $total_usd = 0;
            $total_ves = 0;
            $bcv_rate = get_option( 'wvp_emergency_rate', 36.5 );
            
            foreach ( $data as $item ) {
                if ( isset( $item['total_usd'] ) ) {
                    $total_usd += $item['total_usd'];
                    $total_ves += $item['total_ves'] ?? ($item['total_usd'] * $bcv_rate);
                }
            }
            
            $html .= '<div class="summary">
                <h3>📈 Resumen General</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <strong>' . count( $data ) . '</strong>
                        <span>Registros</span>
                    </div>
                    <div class="summary-item">
                        <strong>$' . number_format( $total_usd, 2 ) . '</strong>
                        <span>Total USD</span>
                    </div>
                    <div class="summary-item">
                        <strong>' . number_format( $total_ves, 2 ) . '</strong>
                        <span>Total VES</span>
                    </div>
                    <div class="summary-item">
                        <strong>' . number_format( $bcv_rate, 2 ) . '</strong>
                        <span>Tasa BCV</span>
                    </div>
                </div>
            </div>';
            
            // Tabla de datos
            $html .= '<table>
                <thead>
                    <tr>';
            
            if ( isset( $data[0] ) ) {
                foreach ( array_keys( $data[0] ) as $key ) {
                    $html .= '<th>' . ucwords( str_replace( '_', ' ', $key ) ) . '</th>';
                }
            }
            
            $html .= '</tr>
                </thead>
                <tbody>';
            
            foreach ( $data as $item ) {
                $html .= '<tr>';
                foreach ( $item as $value ) {
                    if ( is_numeric( $value ) && strpos( $value, '.' ) !== false ) {
                        $html .= '<td>' . number_format( $value, 2 ) . '</td>';
                    } else {
                        $html .= '<td>' . esc_html( $value ) . '</td>';
                    }
                }
                $html .= '</tr>';
            }
            
            $html .= '</tbody>
            </table>';
        } else {
            $html .= '<div class="summary">
                <h3>📊 Sin Datos</h3>
                <p>No se encontraron registros para el período seleccionado.</p>
            </div>';
        }
        
        $html .= '<div class="footer">
            <p>Reporte generado por WooCommerce Venezuela Pro 2025</p>
            <p>Para cumplimiento fiscal con SENIAT</p>
        </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Generar reporte IGTF
     */
    private function generate_igtf_report( $start_date, $end_date, $format ) {
        $igtf_rate = get_option( 'wvp_igtf_rate', 3 );
        
        // Obtener órdenes del período
        $orders = wc_get_orders( array(
            'limit' => -1,
            'status' => array( 'wc-completed', 'wc-processing' ),
            'date_created' => $start_date . '...' . $end_date
        ));
        
        $igtf_data = array();
        $total_igtf_usd = 0;
        $total_igtf_ves = 0;
        $bcv_rate = get_option( 'wvp_emergency_rate', 36.5 );
        
        foreach ( $orders as $order ) {
            $order_total = $order->get_total();
            
            // Calcular IGTF (3% sobre el total)
            $igtf_amount = $order_total * ( $igtf_rate / 100 );
            
            $igtf_data[] = array(
                'order_id' => $order->get_id(),
                'date' => $order->get_date_created()->format( 'Y-m-d H:i:s' ),
                'customer' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'customer_rif' => $order->get_meta( '_billing_rif' ) ?: 'N/A',
                'order_total_usd' => $order_total,
                'igtf_amount_usd' => $igtf_amount,
                'igtf_amount_ves' => $igtf_amount * $bcv_rate,
                'payment_method' => $order->get_payment_method_title(),
                'status' => $order->get_status()
            );
            
            $total_igtf_usd += $igtf_amount;
            $total_igtf_ves += $igtf_amount * $bcv_rate;
        }
        
        // Generar archivo según formato
        switch ( $format ) {
            case 'csv':
                return $this->generate_igtf_csv_export( $igtf_data, $total_igtf_usd, $total_igtf_ves, $igtf_rate, $start_date, $end_date );
            case 'xml':
                return $this->generate_igtf_xml_export( $igtf_data, $total_igtf_usd, $total_igtf_ves, $igtf_rate, $start_date, $end_date );
            default:
                return array(
                    'html' => '<div class="wvp-success"><h4>💰 Reporte IGTF Generado</h4><p><strong>Período:</strong> ' . $start_date . ' a ' . $end_date . '</p><p><strong>Tasa IGTF:</strong> ' . $igtf_rate . '%</p><p><strong>Total IGTF USD:</strong> $' . number_format( $total_igtf_usd, 2 ) . '</p><p><strong>Total IGTF VES:</strong> ' . number_format( $total_igtf_ves, 2 ) . '</p><p><strong>Registros:</strong> ' . count( $igtf_data ) . '</p></div>'
                );
        }
    }
    
    /**
     * Generar declaración IVA
     */
    private function generate_iva_declaration( $month, $year, $format ) {
        $iva_rate = get_option( 'wvp_iva_rate', 16 );
        
        // Calcular fechas del mes
        $start_date = $year . '-' . str_pad( $month, 2, '0', STR_PAD_LEFT ) . '-01';
        $end_date = date( 'Y-m-t', strtotime( $start_date ) );
        
        // Obtener órdenes del mes
        $orders = wc_get_orders( array(
            'limit' => -1,
            'status' => array( 'wc-completed', 'wc-processing' ),
            'date_created' => $start_date . '...' . $end_date
        ));
        
        $iva_data = array();
        $total_sales_usd = 0;
        $total_iva_usd = 0;
        $total_sales_ves = 0;
        $total_iva_ves = 0;
        $bcv_rate = get_option( 'wvp_emergency_rate', 36.5 );
        
        foreach ( $orders as $order ) {
            $subtotal = $order->get_subtotal();
            $tax_amount = $order->get_total_tax();
            $total = $order->get_total();
            
            $iva_data[] = array(
                'order_id' => $order->get_id(),
                'date' => $order->get_date_created()->format( 'Y-m-d H:i:s' ),
                'customer' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'customer_rif' => $order->get_meta( '_billing_rif' ) ?: 'N/A',
                'subtotal_usd' => $subtotal,
                'iva_amount_usd' => $tax_amount,
                'total_usd' => $total,
                'subtotal_ves' => $subtotal * $bcv_rate,
                'iva_amount_ves' => $tax_amount * $bcv_rate,
                'total_ves' => $total * $bcv_rate,
                'payment_method' => $order->get_payment_method_title(),
                'status' => $order->get_status()
            );
            
            $total_sales_usd += $subtotal;
            $total_iva_usd += $tax_amount;
            $total_sales_ves += $subtotal * $bcv_rate;
            $total_iva_ves += $tax_amount * $bcv_rate;
        }
        
        // Generar archivo según formato
        switch ( $format ) {
            case 'csv':
                return $this->generate_iva_csv_export( $iva_data, $total_sales_usd, $total_iva_usd, $total_sales_ves, $total_iva_ves, $iva_rate, $month, $year );
            case 'xml':
                return $this->generate_iva_xml_export( $iva_data, $total_sales_usd, $total_iva_usd, $total_sales_ves, $total_iva_ves, $iva_rate, $month, $year );
            default:
                return array(
                    'html' => '<div class="wvp-success"><h4>📋 Declaración IVA Generada</h4><p><strong>Período:</strong> ' . $month . '/' . $year . '</p><p><strong>Tasa IVA:</strong> ' . $iva_rate . '%</p><p><strong>Ventas Netas USD:</strong> $' . number_format( $total_sales_usd, 2 ) . '</p><p><strong>IVA Cobrado USD:</strong> $' . number_format( $total_iva_usd, 2 ) . '</p><p><strong>Ventas Netas VES:</strong> ' . number_format( $total_sales_ves, 2 ) . '</p><p><strong>IVA Cobrado VES:</strong> ' . number_format( $total_iva_ves, 2 ) . '</p><p><strong>Registros:</strong> ' . count( $iva_data ) . '</p></div>'
                );
        }
    }
    
    /**
     * Generar facturas
     */
    private function generate_invoices( $start_date, $end_date, $format ) {
        // Obtener órdenes del período
        $orders = wc_get_orders( array(
            'limit' => -1,
            'status' => array( 'wc-completed', 'wc-processing' ),
            'date_created' => $start_date . '...' . $end_date
        ));
        
        $invoice_data = array();
        $total_invoices = 0;
        $bcv_rate = get_option( 'wvp_emergency_rate', 36.5 );
        
        foreach ( $orders as $order ) {
            $invoice_data[] = array(
                'order_id' => $order->get_id(),
                'invoice_number' => 'FAC-' . str_pad( $order->get_id(), 6, '0', STR_PAD_LEFT ),
                'date' => $order->get_date_created()->format( 'Y-m-d H:i:s' ),
                'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'customer_rif' => $order->get_meta( '_billing_rif' ) ?: 'N/A',
                'customer_address' => $order->get_billing_address_1() . ', ' . $order->get_billing_city(),
                'subtotal_usd' => $order->get_subtotal(),
                'tax_usd' => $order->get_total_tax(),
                'total_usd' => $order->get_total(),
                'subtotal_ves' => $order->get_subtotal() * $bcv_rate,
                'tax_ves' => $order->get_total_tax() * $bcv_rate,
                'total_ves' => $order->get_total() * $bcv_rate,
                'payment_method' => $order->get_payment_method_title(),
                'status' => $order->get_status(),
                'items' => $this->get_order_items( $order )
            );
            
            $total_invoices++;
        }
        
        // Generar archivo según formato
        switch ( $format ) {
            case 'pdf':
                return $this->generate_invoice_pdf_export( $invoice_data, $start_date, $end_date );
            case 'pdf_batch':
                return $this->generate_invoice_batch_pdf_export( $invoice_data, $start_date, $end_date );
            case 'print':
                return $this->generate_invoice_print_export( $invoice_data, $start_date, $end_date );
            default:
                return array(
                    'html' => '<div class="wvp-success"><h4>📄 Facturas Generadas</h4><p><strong>Período:</strong> ' . $start_date . ' a ' . $end_date . '</p><p><strong>Total Facturas:</strong> ' . $total_invoices . '</p><p><strong>Formato:</strong> ' . $format . '</p><p><strong>Tasa BCV:</strong> ' . number_format( $bcv_rate, 2 ) . '</p><div style="margin-top: 15px;"><button class="wvp-btn wvp-btn-primary" onclick="wvpPrintInvoices()">🖨️ Imprimir Facturas</button></div></div>'
                );
        }
    }
    
    /**
     * Obtener items de una orden
     */
    private function get_order_items( $order ) {
        $items = array();
        foreach ( $order->get_items() as $item ) {
            $product = $item->get_product();
            $items[] = array(
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'price_usd' => $item->get_total() / $item->get_quantity(),
                'total_usd' => $item->get_total(),
                'sku' => $product ? $product->get_sku() : 'N/A'
            );
        }
        return $items;
    }
    
    /**
     * Generar exportación CSV para IGTF
     */
    private function generate_igtf_csv_export( $data, $total_usd, $total_ves, $rate, $start_date, $end_date ) {
        $upload_dir = wp_upload_dir();
        $filename = 'reporte_igtf_' . $start_date . '_' . $end_date . '.csv';
        $file_path = $upload_dir['basedir'] . '/wvp-exports/' . $filename;
        
        if ( ! file_exists( dirname( $file_path ) ) ) {
            wp_mkdir_p( dirname( $file_path ) );
        }
        
        $file = fopen( $file_path, 'w' );
        
        // Headers CSV
        fputcsv( $file, array(
            'Pedido', 'Fecha', 'Cliente', 'RIF', 'Total Orden USD', 'IGTF USD', 'IGTF VES', 'Método Pago', 'Estado'
        ));
        
        // Datos
        foreach ( $data as $row ) {
            fputcsv( $file, array(
                $row['order_id'],
                $row['date'],
                $row['customer'],
                $row['customer_rif'],
                $row['order_total_usd'],
                $row['igtf_amount_usd'],
                $row['igtf_amount_ves'],
                $row['payment_method'],
                $row['status']
            ));
        }
        
        fclose( $file );
        
        return array(
            'file_url' => $upload_dir['baseurl'] . '/wvp-exports/' . $filename,
            'file_path' => $file_path,
            'filename' => $filename,
            'html' => '<div class="wvp-success"><h4>✅ Reporte IGTF CSV generado exitosamente</h4><p><strong>Tasa IGTF:</strong> ' . $rate . '%</p><p><strong>Total IGTF USD:</strong> $' . number_format( $total_usd, 2 ) . '</p><p><strong>Total IGTF VES:</strong> ' . number_format( $total_ves, 2 ) . '</p><p><a href="' . $upload_dir['baseurl'] . '/wvp-exports/' . $filename . '" class="wvp-btn wvp-btn-primary" download>📥 Descargar CSV</a></p></div>'
        );
    }
    
    /**
     * Generar exportación XML para IGTF
     */
    private function generate_igtf_xml_export( $data, $total_usd, $total_ves, $rate, $start_date, $end_date ) {
        $upload_dir = wp_upload_dir();
        $filename = 'reporte_igtf_' . $start_date . '_' . $end_date . '.xml';
        $file_path = $upload_dir['basedir'] . '/wvp-exports/' . $filename;
        
        if ( ! file_exists( dirname( $file_path ) ) ) {
            wp_mkdir_p( dirname( $file_path ) );
        }
        
        $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><reporte_igtf></reporte_igtf>' );
        $xml->addAttribute( 'fecha_generacion', current_time( 'Y-m-d H:i:s' ) );
        $xml->addAttribute( 'periodo', $start_date . ' a ' . $end_date );
        $xml->addAttribute( 'tasa_igtf', $rate );
        $xml->addAttribute( 'total_igtf_usd', $total_usd );
        $xml->addAttribute( 'total_igtf_ves', $total_ves );
        
        foreach ( $data as $row ) {
            $igtf = $xml->addChild( 'igtf' );
            $igtf->addChild( 'pedido', $row['order_id'] );
            $igtf->addChild( 'fecha', $row['date'] );
            $igtf->addChild( 'cliente', htmlspecialchars( $row['customer'] ) );
            $igtf->addChild( 'rif', $row['customer_rif'] );
            $igtf->addChild( 'total_orden_usd', $row['order_total_usd'] );
            $igtf->addChild( 'igtf_usd', $row['igtf_amount_usd'] );
            $igtf->addChild( 'igtf_ves', $row['igtf_amount_ves'] );
            $igtf->addChild( 'metodo_pago', htmlspecialchars( $row['payment_method'] ) );
            $igtf->addChild( 'estado', $row['status'] );
        }
        
        $xml->asXML( $file_path );
        
        return array(
            'file_url' => $upload_dir['baseurl'] . '/wvp-exports/' . $filename,
            'file_path' => $file_path,
            'filename' => $filename,
            'html' => '<div class="wvp-success"><h4>✅ Reporte IGTF XML generado exitosamente</h4><p><strong>Tasa IGTF:</strong> ' . $rate . '%</p><p><strong>Total IGTF USD:</strong> $' . number_format( $total_usd, 2 ) . '</p><p><strong>Total IGTF VES:</strong> ' . number_format( $total_ves, 2 ) . '</p><p><a href="' . $upload_dir['baseurl'] . '/wvp-exports/' . $filename . '" class="wvp-btn wvp-btn-primary" download>📥 Descargar XML</a></p></div>'
        );
    }
    
    /**
     * Generar exportación CSV para IVA
     */
    private function generate_iva_csv_export( $data, $total_sales_usd, $total_iva_usd, $total_sales_ves, $total_iva_ves, $rate, $month, $year ) {
        $upload_dir = wp_upload_dir();
        $filename = 'declaracion_iva_' . $year . '_' . str_pad( $month, 2, '0', STR_PAD_LEFT ) . '.csv';
        $file_path = $upload_dir['basedir'] . '/wvp-exports/' . $filename;
        
        if ( ! file_exists( dirname( $file_path ) ) ) {
            wp_mkdir_p( dirname( $file_path ) );
        }
        
        $file = fopen( $file_path, 'w' );
        
        // Headers CSV
        fputcsv( $file, array(
            'Pedido', 'Fecha', 'Cliente', 'RIF', 'Subtotal USD', 'IVA USD', 'Total USD', 'Subtotal VES', 'IVA VES', 'Total VES', 'Método Pago', 'Estado'
        ));
        
        // Datos
        foreach ( $data as $row ) {
            fputcsv( $file, array(
                $row['order_id'],
                $row['date'],
                $row['customer'],
                $row['customer_rif'],
                $row['subtotal_usd'],
                $row['iva_amount_usd'],
                $row['total_usd'],
                $row['subtotal_ves'],
                $row['iva_amount_ves'],
                $row['total_ves'],
                $row['payment_method'],
                $row['status']
            ));
        }
        
        fclose( $file );
        
        return array(
            'file_url' => $upload_dir['baseurl'] . '/wvp-exports/' . $filename,
            'file_path' => $file_path,
            'filename' => $filename,
            'html' => '<div class="wvp-success"><h4>✅ Declaración IVA CSV generada exitosamente</h4><p><strong>Período:</strong> ' . $month . '/' . $year . '</p><p><strong>Tasa IVA:</strong> ' . $rate . '%</p><p><strong>Ventas Netas USD:</strong> $' . number_format( $total_sales_usd, 2 ) . '</p><p><strong>IVA Cobrado USD:</strong> $' . number_format( $total_iva_usd, 2 ) . '</p><p><a href="' . $upload_dir['baseurl'] . '/wvp-exports/' . $filename . '" class="wvp-btn wvp-btn-primary" download>📥 Descargar CSV</a></p></div>'
        );
    }
    
    /**
     * Generar exportación XML para IVA
     */
    private function generate_iva_xml_export( $data, $total_sales_usd, $total_iva_usd, $total_sales_ves, $total_iva_ves, $rate, $month, $year ) {
        $upload_dir = wp_upload_dir();
        $filename = 'declaracion_iva_' . $year . '_' . str_pad( $month, 2, '0', STR_PAD_LEFT ) . '.xml';
        $file_path = $upload_dir['basedir'] . '/wvp-exports/' . $filename;
        
        if ( ! file_exists( dirname( $file_path ) ) ) {
            wp_mkdir_p( dirname( $file_path ) );
        }
        
        $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><declaracion_iva></declaracion_iva>' );
        $xml->addAttribute( 'fecha_generacion', current_time( 'Y-m-d H:i:s' ) );
        $xml->addAttribute( 'periodo', $month . '/' . $year );
        $xml->addAttribute( 'tasa_iva', $rate );
        $xml->addAttribute( 'total_ventas_usd', $total_sales_usd );
        $xml->addAttribute( 'total_iva_usd', $total_iva_usd );
        $xml->addAttribute( 'total_ventas_ves', $total_sales_ves );
        $xml->addAttribute( 'total_iva_ves', $total_iva_ves );
        
        foreach ( $data as $row ) {
            $venta = $xml->addChild( 'venta' );
            $venta->addChild( 'pedido', $row['order_id'] );
            $venta->addChild( 'fecha', $row['date'] );
            $venta->addChild( 'cliente', htmlspecialchars( $row['customer'] ) );
            $venta->addChild( 'rif', $row['customer_rif'] );
            $venta->addChild( 'subtotal_usd', $row['subtotal_usd'] );
            $venta->addChild( 'iva_usd', $row['iva_amount_usd'] );
            $venta->addChild( 'total_usd', $row['total_usd'] );
            $venta->addChild( 'subtotal_ves', $row['subtotal_ves'] );
            $venta->addChild( 'iva_ves', $row['iva_amount_ves'] );
            $venta->addChild( 'total_ves', $row['total_ves'] );
            $venta->addChild( 'metodo_pago', htmlspecialchars( $row['payment_method'] ) );
            $venta->addChild( 'estado', $row['status'] );
        }
        
        $xml->asXML( $file_path );
        
        return array(
            'file_url' => $upload_dir['baseurl'] . '/wvp-exports/' . $filename,
            'file_path' => $file_path,
            'filename' => $filename,
            'html' => '<div class="wvp-success"><h4>✅ Declaración IVA XML generada exitosamente</h4><p><strong>Período:</strong> ' . $month . '/' . $year . '</p><p><strong>Tasa IVA:</strong> ' . $rate . '%</p><p><strong>Ventas Netas USD:</strong> $' . number_format( $total_sales_usd, 2 ) . '</p><p><strong>IVA Cobrado USD:</strong> $' . number_format( $total_iva_usd, 2 ) . '</p><p><a href="' . $upload_dir['baseurl'] . '/wvp-exports/' . $filename . '" class="wvp-btn wvp-btn-primary" download>📥 Descargar XML</a></p></div>'
        );
    }
    
    /**
     * Generar exportación PDF para facturas (placeholder)
     */
    private function generate_invoice_pdf_export( $data, $start_date, $end_date ) {
        return array(
            'html' => '<div class="wvp-info"><h4>📄 Facturas PDF</h4><p>La funcionalidad de PDF estará disponible en la próxima versión.</p><p><strong>Total Facturas:</strong> ' . count( $data ) . '</p></div>'
        );
    }
    
    /**
     * Generar exportación PDF en lote para facturas (placeholder)
     */
    private function generate_invoice_batch_pdf_export( $data, $start_date, $end_date ) {
        return array(
            'html' => '<div class="wvp-info"><h4>📄 Facturas PDF Lote</h4><p>La funcionalidad de PDF en lote estará disponible en la próxima versión.</p><p><strong>Total Facturas:</strong> ' . count( $data ) . '</p></div>'
        );
    }
    
    /**
     * Generar exportación para impresión directa de facturas
     */
    private function generate_invoice_print_export( $data, $start_date, $end_date ) {
        $html = '<div class="wvp-success"><h4>📄 Facturas Listas para Imprimir</h4>';
        $html .= '<p><strong>Período:</strong> ' . $start_date . ' a ' . $end_date . '</p>';
        $html .= '<p><strong>Total Facturas:</strong> ' . count( $data ) . '</p>';
        $html .= '<div style="margin-top: 15px;">';
        
        foreach ( $data as $invoice ) {
            $html .= '<div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px;">';
            $html .= '<h5>Factura #' . $invoice['invoice_number'] . '</h5>';
            $html .= '<p><strong>Cliente:</strong> ' . $invoice['customer_name'] . '</p>';
            $html .= '<p><strong>RIF:</strong> ' . $invoice['customer_rif'] . '</p>';
            $html .= '<p><strong>Fecha:</strong> ' . $invoice['date'] . '</p>';
            $html .= '<p><strong>Total USD:</strong> $' . number_format( $invoice['total_usd'], 2 ) . '</p>';
            $html .= '<p><strong>Total VES:</strong> ' . number_format( $invoice['total_ves'], 2 ) . '</p>';
            $html .= '<button class="wvp-btn wvp-btn-secondary" onclick="window.print()">🖨️ Imprimir</button>';
            $html .= '</div>';
        }
        
        $html .= '</div></div>';
        
        return array( 'html' => $html );
    }
}

// Inicializar el exportador SENIAT
WVP_SENIAT_Exporter::get_instance();
