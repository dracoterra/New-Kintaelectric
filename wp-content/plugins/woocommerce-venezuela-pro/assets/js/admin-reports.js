jQuery(document).ready(function($) {
    'use strict';
    
    // Generar Libro de Ventas
    $('#wvp-generate-sales-book').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $form = $('#wvp-reports-form');
        var dateFrom = $('#wvp-date-from').val();
        var dateTo = $('#wvp-date-to').val();
        
        if (!dateFrom || !dateTo) {
            alert('Por favor seleccione un rango de fechas');
            return;
        }
        
        $button.prop('disabled', true).text(wvp_reports.i18n.generating);
        
        $.ajax({
            url: wvp_reports.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_generate_sales_book',
                nonce: wvp_reports.nonce,
                date_from: dateFrom,
                date_to: dateTo
            },
            success: function(response) {
                if (response.success) {
                    $('#wvp-reports-content').html(response.data.html);
                    $('#wvp-reports-results').show();
                    
                    // Configurar exportación CSV
                    $('#wvp-export-sales-csv').on('click', function() {
                        exportToCSV(response.data.data, 'libro_ventas_' + dateFrom + '_' + dateTo + '.csv');
                    });
                } else {
                    alert(response.data.message || wvp_reports.i18n.error);
                }
            },
            error: function() {
                alert(wvp_reports.i18n.error);
            },
            complete: function() {
                $button.prop('disabled', false).text('Generar Libro de Ventas');
            }
        });
    });
    
    // Generar Reporte de IGTF
    $('#wvp-generate-igtf-report').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $form = $('#wvp-reports-form');
        var dateFrom = $('#wvp-date-from').val();
        var dateTo = $('#wvp-date-to').val();
        
        if (!dateFrom || !dateTo) {
            alert('Por favor seleccione un rango de fechas');
            return;
        }
        
        $button.prop('disabled', true).text(wvp_reports.i18n.generating);
        
        $.ajax({
            url: wvp_reports.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_generate_igtf_report',
                nonce: wvp_reports.nonce,
                date_from: dateFrom,
                date_to: dateTo
            },
            success: function(response) {
                if (response.success) {
                    $('#wvp-reports-content').html(response.data.html);
                    $('#wvp-reports-results').show();
                    
                    // Configurar exportación CSV
                    $('#wvp-export-igtf-csv').on('click', function() {
                        exportToCSV(response.data.data, 'reporte_igtf_' + dateFrom + '_' + dateTo + '.csv');
                    });
                } else {
                    alert(response.data.message || wvp_reports.i18n.error);
                }
            },
            error: function() {
                alert(wvp_reports.i18n.error);
            },
            complete: function() {
                $button.prop('disabled', false).text('Generar Reporte de IGTF');
            }
        });
    });
    
    // Función para exportar a CSV
    function exportToCSV(data, filename) {
        if (!data || data.length === 0) {
            alert('No hay datos para exportar');
            return;
        }
        
        var csv = '';
        var headers = Object.keys(data[0]);
        
        // Añadir encabezados
        csv += headers.join(',') + '\n';
        
        // Añadir datos
        data.forEach(function(row) {
            var values = headers.map(function(header) {
                var value = row[header] || '';
                // Escapar comillas y envolver en comillas si contiene comas
                if (typeof value === 'string' && (value.includes(',') || value.includes('"') || value.includes('\n'))) {
                    value = '"' + value.replace(/"/g, '""') + '"';
                }
                return value;
            });
            csv += values.join(',') + '\n';
        });
        
        // Crear y descargar archivo
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Establecer fechas por defecto (último mes)
    var today = new Date();
    var lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
    
    $('#wvp-date-to').val(today.toISOString().split('T')[0]);
    $('#wvp-date-from').val(lastMonth.toISOString().split('T')[0]);
});
