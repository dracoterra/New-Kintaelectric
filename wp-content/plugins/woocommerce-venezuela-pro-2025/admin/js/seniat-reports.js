/**
 * JavaScript para Reportes SENIAT
 * 
 * @package WooCommerce_Venezuela_Suite_2025
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Función para imprimir reporte
    window.wcvsPrintReport = function() {
        window.print();
    };

    // Función para regenerar factura
    window.wcvsRegenerateInvoice = function(orderId) {
        if (!confirm('¿Está seguro de que desea regenerar la factura? Esto sobrescribirá la factura existente.')) {
            return;
        }

        $.ajax({
            url: wcvs_seniat_reports.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_generate_invoice',
                order_id: orderId,
                nonce: wcvs_seniat_reports.nonce
            },
            beforeSend: function() {
                // Mostrar indicador de carga
                $('body').append('<div id="wcvs-loading" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 20px; border-radius: 5px;"><span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> ' + wcvs_seniat_reports.i18n.generating + '</div></div>');
            },
            success: function(response) {
                if (response.success) {
                    alert(wcvs_seniat_reports.i18n.success);
                    location.reload();
                } else {
                    alert(wcvs_seniat_reports.i18n.error + ': ' + response.data);
                }
            },
            error: function() {
                alert(wcvs_seniat_reports.i18n.error);
            },
            complete: function() {
                $('#wcvs-loading').remove();
            }
        });
    };

    // Función para generar reporte vía AJAX
    function generateReport() {
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();

        if (!dateFrom || !dateTo) {
            alert('Por favor seleccione las fechas de inicio y fin.');
            return;
        }

        $.ajax({
            url: wcvs_seniat_reports.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_generate_seniat_report',
                date_from: dateFrom,
                date_to: dateTo,
                nonce: wcvs_seniat_reports.nonce
            },
            beforeSend: function() {
                $('.wcvs-report-content').html('<div class="wcvs-loading"><span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> ' + wcvs_seniat_reports.i18n.generating + '</div>');
            },
            success: function(response) {
                if (response.success) {
                    // El reporte se mostrará automáticamente al recargar la página
                    location.reload();
                } else {
                    $('.wcvs-report-content').html('<div class="wcvs-error">' + wcvs_seniat_reports.i18n.error + '</div>');
                }
            },
            error: function() {
                $('.wcvs-report-content').html('<div class="wcvs-error">' + wcvs_seniat_reports.i18n.error + '</div>');
            }
        });
    }

    // Función para exportar reporte
    function exportReport(format) {
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();

        if (!dateFrom || !dateTo) {
            alert('Por favor seleccione las fechas de inicio y fin.');
            return;
        }

        var url = wcvs_seniat_reports.ajax_url + '?action=wcvs_export_seniat_report&date_from=' + dateFrom + '&date_to=' + dateTo + '&format=' + format + '&_wpnonce=' + wcvs_seniat_reports.nonce;
        
        // Crear enlace temporal para descarga
        var link = document.createElement('a');
        link.href = url;
        link.download = 'reporte_seniat_' + dateFrom + '_' + dateTo + '.' + format;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        // Agregar estilos CSS
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                .wcvs-loading {
                    text-align: center;
                    padding: 40px;
                    font-size: 16px;
                }
                
                .wcvs-error {
                    text-align: center;
                    padding: 40px;
                    color: #d63638;
                    font-size: 16px;
                }
                
                .wcvs-report-filters {
                    background: #f9f9f9;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    margin-bottom: 20px;
                }
                
                .wcvs-date-filters {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    flex-wrap: wrap;
                }
                
                .wcvs-date-filters label {
                    font-weight: bold;
                    color: #333;
                }
                
                .wcvs-date-filters input[type="date"] {
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 3px;
                }
                
                .wcvs-date-filters .button {
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                
                .wcvs-summary-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 15px;
                    margin: 20px 0;
                }
                
                .wcvs-summary-item {
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    padding: 15px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .wcvs-summary-label {
                    font-weight: bold;
                    color: #333;
                }
                
                .wcvs-summary-value {
                    font-weight: bold;
                    color: #0073aa;
                    font-size: 18px;
                }
                
                .wcvs-table-container {
                    overflow-x: auto;
                    margin: 20px 0;
                }
                
                .wcvs-fiscal-table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 12px;
                }
                
                .wcvs-fiscal-table th,
                .wcvs-fiscal-table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                
                .wcvs-fiscal-table th {
                    background: #0073aa;
                    color: #fff;
                    font-weight: bold;
                }
                
                .wcvs-fiscal-table tbody tr:nth-child(even) {
                    background: #f9f9f9;
                }
                
                .wcvs-total-row {
                    background: #e7f3ff !important;
                    font-weight: bold;
                }
                
                .wcvs-report-actions {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    text-align: center;
                }
                
                .wcvs-report-actions .button {
                    margin: 0 10px;
                }
                
                @media print {
                    .wcvs-report-actions,
                    .wcvs-report-filters {
                        display: none !important;
                    }
                    
                    .wcvs-fiscal-report-container {
                        border: none !important;
                        padding: 0 !important;
                    }
                }
            `)
            .appendTo('head');

        // Manejar clic en botón de generar reporte
        $('.wcvs-date-filters .button').on('click', function(e) {
            e.preventDefault();
            generateReport();
        });

        // Manejar clic en botones de exportar
        $('.wcvs-report-actions .button').on('click', function(e) {
            e.preventDefault();
            var format = $(this).data('format') || 'excel';
            exportReport(format);
        });

        // Validación de fechas
        $('#date_from, #date_to').on('change', function() {
            var dateFrom = $('#date_from').val();
            var dateTo = $('#date_to').val();
            
            if (dateFrom && dateTo && dateFrom > dateTo) {
                alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
                $(this).val('');
            }
        });

        // Auto-generar reporte si hay fechas en la URL
        var urlParams = new URLSearchParams(window.location.search);
        var dateFrom = urlParams.get('date_from');
        var dateTo = urlParams.get('date_to');
        
        if (dateFrom && dateTo) {
            $('#date_from').val(dateFrom);
            $('#date_to').val(dateTo);
        }
    });

})(jQuery);
