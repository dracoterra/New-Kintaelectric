/**
 * JavaScript para los reportes fiscales de WooCommerce Venezuela Pro
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Inicializar funcionalidades de reportes fiscales
    initFiscalReports();
    
    /**
     * Inicializar funcionalidades de reportes fiscales
     */
    function initFiscalReports() {
        // Manejar envío de formulario de filtros
        $('.wvp-filters form').on('submit', function(e) {
            e.preventDefault();
            generateReport();
        });
        
        // Manejar clics en botones de acción
        $(document).on('click', '.wvp-report-actions button', function(e) {
            e.preventDefault();
            
            var action = $(this).text().trim();
            
            switch (action) {
                case 'Exportar a CSV':
                    exportToCSV();
                    break;
                case 'Imprimir':
                    printReport();
                    break;
            }
        });
        
        // Generar reporte inicial si hay fechas
        if ($('#date_from').val() && $('#date_to').val()) {
            generateReport();
        }
    }
    
    /**
     * Generar reporte fiscal
     */
    function generateReport() {
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();
        
        if (!dateFrom || !dateTo) {
            showNotice('error', 'Por favor, seleccione las fechas de inicio y fin');
            return;
        }
        
        if (new Date(dateFrom) > new Date(dateTo)) {
            showNotice('error', 'La fecha de inicio no puede ser posterior a la fecha de fin');
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: wvp_fiscal_reports.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_generate_fiscal_report',
                nonce: wvp_fiscal_reports.nonce,
                date_from: dateFrom,
                date_to: dateTo
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    updateReportContent(response.data);
                } else {
                    showNotice('error', wvp_fiscal_reports.i18n.error + ': ' + response.data.message);
                }
            },
            error: function() {
                hideLoading();
                showNotice('error', wvp_fiscal_reports.i18n.error + ': Error de conexión');
            }
        });
    }
    
    /**
     * Actualizar contenido del reporte
     */
    function updateReportContent(data) {
        // Actualizar resumen
        $('.wvp-summary-value').each(function() {
            var card = $(this).closest('.wvp-summary-card');
            var title = card.find('h3').text();
            
            switch (title) {
                case 'Total de Pedidos':
                    $(this).text(data.total_orders);
                    break;
                case 'Total IVA (USD)':
                    $(this).text(formatCurrency(data.total_tax));
                    break;
                case 'Total IGTF (USD)':
                    $(this).text(formatCurrency(data.total_igtf));
                    break;
                case 'Total General (USD)':
                    $(this).text(formatCurrency(data.total_general));
                    break;
            }
        });
        
        // Actualizar desglose mensual
        var tbody = $('.wvp-report-details tbody');
        tbody.empty();
        
        if (data.monthly_breakdown && Object.keys(data.monthly_breakdown).length > 0) {
            $.each(data.monthly_breakdown, function(month, monthData) {
                var row = $('<tr></tr>');
                row.append('<td>' + formatMonth(month) + '</td>');
                row.append('<td>' + monthData.orders + '</td>');
                row.append('<td>' + formatCurrency(monthData.tax) + '</td>');
                row.append('<td>' + formatCurrency(monthData.igtf) + '</td>');
                row.append('<td>' + formatCurrency(monthData.total) + '</td>');
                tbody.append(row);
            });
        } else {
            tbody.append('<tr><td colspan="5" style="text-align: center; color: #999;">No hay datos para el período seleccionado</td></tr>');
        }
    }
    
    /**
     * Exportar a CSV
     */
    function exportToCSV() {
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();
        
        if (!dateFrom || !dateTo) {
            showNotice('error', 'Por favor, seleccione las fechas antes de exportar');
            return;
        }
        
        // Crear URL de exportación
        var exportUrl = wvp_fiscal_reports.ajax_url + '?action=wvp_export_fiscal_report&date_from=' + dateFrom + '&date_to=' + dateTo + '&nonce=' + wvp_fiscal_reports.nonce;
        
        // Descargar archivo
        window.open(exportUrl, '_blank');
    }
    
    /**
     * Imprimir reporte
     */
    function printReport() {
        var printContent = $('.wvp-fiscal-report').html();
        var printWindow = window.open('', '_blank');
        
        printWindow.document.write(`
            <html>
                <head>
                    <title>Reporte Fiscal Venezuela</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .wvp-summary-cards { display: flex; gap: 20px; margin-bottom: 20px; }
                        .wvp-summary-card { border: 1px solid #ddd; padding: 15px; text-align: center; flex: 1; }
                        .wvp-summary-value { font-size: 24px; font-weight: bold; color: #007cba; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    <h1>Reporte Fiscal Venezuela</h1>
                    <p>Período: ${formatDate($('#date_from').val())} al ${formatDate($('#date_to').val())}</p>
                    ${printContent}
                </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.print();
    }
    
    /**
     * Mostrar indicador de carga
     */
    function showLoading() {
        $('.wvp-report-content').html('<div style="text-align: center; padding: 40px;"><div class="spinner is-active"></div><p>' + wvp_fiscal_reports.i18n.generating + '</p></div>');
    }
    
    /**
     * Ocultar indicador de carga
     */
    function hideLoading() {
        // El contenido se actualiza en updateReportContent
    }
    
    /**
     * Formatear moneda
     */
    function formatCurrency(amount) {
        return new Intl.NumberFormat('es-VE', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2
        }).format(amount);
    }
    
    /**
     * Formatear mes
     */
    function formatMonth(month) {
        var date = new Date(month + '-01');
        return date.toLocaleDateString('es-VE', { year: 'numeric', month: 'long' });
    }
    
    /**
     * Formatear fecha
     */
    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString('es-VE');
    }
    
    /**
     * Mostrar notificación
     */
    function showNotice(type, message) {
        // Remover notificaciones anteriores
        $('.wvp-notice').remove();
        
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible wvp-notice"><p>' + message + '</p></div>');
        
        $('.wrap h1').after(notice);
        
        // Auto-ocultar después de 5 segundos
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
    
    /**
     * Funciones globales
     */
    window.wvpExportReport = exportToCSV;
    window.wvpPrintReport = printReport;
});
