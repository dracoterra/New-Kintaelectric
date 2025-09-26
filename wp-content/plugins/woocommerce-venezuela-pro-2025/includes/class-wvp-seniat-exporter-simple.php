    /**
     * Generar HTML completo para SENIAT - VERSIÓN SIMPLIFICADA MEJORADA
     */
    private function generate_seniat_html( $orders, $start_date, $end_date, $bcv_rate ) {
        $total_usd = 0;
        $total_iva = 0;
        $total_igtf = 0;
        
        // Calcular totales
        foreach ( $orders as $order ) {
            $total_usd += $order->get_total();
            $total_iva += $order->get_total() * 0.16; // IVA 16%
            $total_igtf += $order->get_total() * 0.03; // IGTF 3%
        }
        
        $total_ves = $total_usd * $bcv_rate;
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte SENIAT - ' . $start_date . ' a ' . $end_date . '</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5; 
            color: #333; 
            line-height: 1.6;
        }
        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
            background: white; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            border-radius: 5px; 
            overflow: hidden;
        }
        .header { 
            background: #2c3e50; 
            color: white; 
            text-align: center; 
            padding: 30px 20px; 
        }
        .header h1 { 
            font-size: 2em; 
            margin: 0 0 10px 0; 
            font-weight: bold; 
        }
        .header h2 { 
            font-size: 1.2em; 
            margin: 0; 
            font-weight: normal; 
            opacity: 0.9;
        }
        .content { padding: 30px; }
        .company-info { 
            background: #ecf0f1; 
            padding: 20px; 
            margin-bottom: 25px; 
            border-radius: 5px; 
            border-left: 4px solid #3498db;
        }
        .company-info h3 { 
            color: #2c3e50; 
            margin-top: 0; 
            font-size: 1.3em; 
        }
        .company-grid {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        .company-row {
            display: table-row;
        }
        .company-item {
            display: table-cell;
            padding: 10px;
            vertical-align: top;
            width: 25%;
        }
        .company-item strong {
            color: #34495e;
            display: block;
            margin-bottom: 5px;
            font-size: 0.9em;
        }
        .summary { 
            background: #e8f4fd; 
            padding: 25px; 
            margin-bottom: 25px; 
            border-radius: 5px; 
            border: 1px solid #bdc3c7;
        }
        .summary h3 { 
            color: #2980b9; 
            margin-top: 0; 
            font-size: 1.4em; 
            text-align: center;
            margin-bottom: 20px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-row {
            display: table-row;
        }
        .summary-item {
            display: table-cell;
            padding: 15px;
            text-align: center;
            vertical-align: top;
            width: 16.66%;
        }
        .summary-item strong {
            display: block;
            color: #2980b9;
            font-size: 0.9em;
            margin-bottom: 8px;
        }
        .summary-item .value {
            font-size: 1.2em;
            font-weight: bold;
            color: #2c3e50;
        }
        .orders-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 25px; 
            background: white;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .orders-table th { 
            background: #34495e; 
            color: white; 
            padding: 12px 8px; 
            text-align: left; 
            font-weight: bold;
            font-size: 0.9em;
        }
        .orders-table td { 
            padding: 10px 8px; 
            border-bottom: 1px solid #ecf0f1; 
            font-size: 0.9em;
        }
        .orders-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .totals { 
            background: #e8f5e8; 
            padding: 25px; 
            border-radius: 5px; 
            border: 1px solid #27ae60;
        }
        .totals h3 { 
            color: #27ae60; 
            margin-top: 0; 
            font-size: 1.4em; 
            text-align: center;
            margin-bottom: 20px;
        }
        .totals-grid {
            display: table;
            width: 100%;
        }
        .totals-row {
            display: table-row;
        }
        .totals-item {
            display: table-cell;
            padding: 15px;
            text-align: center;
            vertical-align: top;
            width: 20%;
        }
        .totals-item strong {
            display: block;
            color: #27ae60;
            font-size: 0.9em;
            margin-bottom: 8px;
        }
        .totals-item .value {
            font-size: 1.2em;
            font-weight: bold;
            color: #2c3e50;
        }
        .footer { 
            background: #ecf0f1; 
            padding: 20px; 
            text-align: center; 
            border-top: 1px solid #bdc3c7;
            color: #7f8c8d;
        }
        .footer p {
            margin: 5px 0;
            font-size: 0.9em;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-completed { background: #d5f4e6; color: #27ae60; }
        .badge-processing { background: #fef9e7; color: #f39c12; }
        .badge-pending { background: #fadbd8; color: #e74c3c; }
        @media print {
            body { background: white; }
            .container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏛️ REPORTE FISCAL SENIAT</h1>
            <h2>Período: ' . $start_date . ' a ' . $end_date . '</h2>
        </div>
        
        <div class="content">
            <div class="company-info">
                <h3>🏢 Información de la Empresa</h3>
                <div class="company-grid">
                    <div class="company-row">
                        <div class="company-item">
                            <strong>RIF</strong>
                            J-12345678-9
                        </div>
                        <div class="company-item">
                            <strong>Razón Social</strong>
                            Kinta Electric C.A.
                        </div>
                        <div class="company-item">
                            <strong>Dirección</strong>
                            Caracas, Venezuela
                        </div>
                        <div class="company-item">
                            <strong>Teléfono</strong>
                            +58 212 123-4567
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="summary">
                <h3>📊 Resumen del Período</h3>
                <div class="summary-grid">
                    <div class="summary-row">
                        <div class="summary-item">
                            <strong>Total Órdenes</strong>
                            <div class="value">' . count( $orders ) . '</div>
                        </div>
                        <div class="summary-item">
                            <strong>Total USD</strong>
                            <div class="value">$' . number_format( $total_usd, 2 ) . '</div>
                        </div>
                        <div class="summary-item">
                            <strong>Total VES</strong>
                            <div class="value">' . number_format( $total_ves, 2 ) . ' VES</div>
                        </div>
                        <div class="summary-item">
                            <strong>Tasa BCV</strong>
                            <div class="value">' . $bcv_rate . '</div>
                        </div>
                        <div class="summary-item">
                            <strong>IVA (16%)</strong>
                            <div class="value">$' . number_format( $total_iva, 2 ) . '</div>
                        </div>
                        <div class="summary-item">
                            <strong>IGTF (3%)</strong>
                            <div class="value">$' . number_format( $total_igtf, 2 ) . '</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <table class="orders-table">
                <thead>
                    <tr>
                        <th># Orden</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>RIF/CI</th>
                        <th>Total USD</th>
                        <th>Total VES</th>
                        <th>IVA</th>
                        <th>IGTF</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ( $orders as $order ) {
            $order_total = $order->get_total();
            $order_ves = $order_total * $bcv_rate;
            $order_iva = $order_total * 0.16;
            $order_igtf = $order_total * 0.03;
            
            // Determinar clase de badge según estado
            $status_class = 'badge-pending';
            $status_text = $order->get_status();
            if ( $status_text === 'completed' ) {
                $status_class = 'badge-completed';
                $status_text = 'Completado';
            } elseif ( $status_text === 'processing' ) {
                $status_class = 'badge-processing';
                $status_text = 'Procesando';
            }
            
            $html .= '<tr>
                <td><strong>#' . $order->get_id() . '</strong></td>
                <td>' . $order->get_date_created()->format( 'd/m/Y' ) . '</td>
                <td>' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '</td>
                <td>' . ($order->get_meta( '_billing_rif' ) ?: 'N/A') . '</td>
                <td><strong>$' . number_format( $order_total, 2 ) . '</strong></td>
                <td><strong>' . number_format( $order_ves, 2 ) . '</strong></td>
                <td>$' . number_format( $order_iva, 2 ) . '</td>
                <td>$' . number_format( $order_igtf, 2 ) . '</td>
                <td><span class="badge ' . $status_class . '">' . $status_text . '</span></td>
            </tr>';
        }
        
        $html .= '</tbody>
            </table>
            
            <div class="totals">
                <h3>💰 Totales Fiscales</h3>
                <div class="totals-grid">
                    <div class="totals-row">
                        <div class="totals-item">
                            <strong>Total Ventas USD</strong>
                            <div class="value">$' . number_format( $total_usd, 2 ) . '</div>
                        </div>
                        <div class="totals-item">
                            <strong>Total Ventas VES</strong>
                            <div class="value">' . number_format( $total_ves, 2 ) . ' VES</div>
                        </div>
                        <div class="totals-item">
                            <strong>Total IVA</strong>
                            <div class="value">$' . number_format( $total_iva, 2 ) . '</div>
                        </div>
                        <div class="totals-item">
                            <strong>Total IGTF</strong>
                            <div class="value">$' . number_format( $total_igtf, 2 ) . '</div>
                        </div>
                        <div class="totals-item">
                            <strong>Total a Declarar</strong>
                            <div class="value">$' . number_format( $total_iva + $total_igtf, 2 ) . '</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>📋 Este reporte ha sido generado automáticamente por WooCommerce Venezuela Pro 2025</strong></p>
            <p>📅 Fecha de generación: ' . current_time( 'Y-m-d H:i:s' ) . '</p>
            <p>🏛️ Para uso exclusivo de SENIAT - Cumplimiento fiscal venezolano</p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
