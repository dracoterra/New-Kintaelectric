/**
 * JavaScript para Sistema de Estadísticas
 * 
 * @package WooCommerce_Venezuela_Suite_2025
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Función global para imprimir estadísticas
    window.wcvsPrintStatistics = function() {
        window.print();
    };

    // Función para generar gráficos
    function generateCharts() {
        if (typeof wcvsChartData === 'undefined') {
            return;
        }

        // Gráfico de tendencia de ventas
        generateSalesTrendChart();
        
        // Gráfico de métodos de pago
        generatePaymentMethodsChart();
    }

    // Generar gráfico de tendencia de ventas
    function generateSalesTrendChart() {
        var $chart = $('#sales-trend-chart');
        if ($chart.length === 0) {
            return;
        }

        var data = wcvsChartData.map(function(item) {
            return {
                x: new Date(item.date),
                y: parseFloat(item.sales_usd)
            };
        });

        // Crear gráfico simple con HTML/CSS
        var html = '<div class="wcvs-simple-chart">';
        html += '<div class="wcvs-chart-header">';
        html += '<span class="wcvs-chart-title">Ventas USD por Día</span>';
        html += '</div>';
        html += '<div class="wcvs-chart-bars">';

        var maxValue = Math.max.apply(Math, data.map(function(item) { return item.y; }));
        
        data.forEach(function(item) {
            var percentage = (item.y / maxValue) * 100;
            var date = item.x.toLocaleDateString('es-VE');
            
            html += '<div class="wcvs-chart-bar">';
            html += '<div class="wcvs-bar-fill" style="height: ' + percentage + '%"></div>';
            html += '<div class="wcvs-bar-label">' + date + '</div>';
            html += '<div class="wcvs-bar-value">$' + item.y.toFixed(2) + '</div>';
            html += '</div>';
        });

        html += '</div>';
        html += '</div>';

        $chart.html(html);
    }

    // Generar gráfico de métodos de pago
    function generatePaymentMethodsChart() {
        var $chart = $('#payment-methods-chart');
        if ($chart.length === 0) {
            return;
        }

        // Obtener datos de métodos de pago del HTML
        var paymentData = [];
        $('.wcvs-payment-item').each(function() {
            var $item = $(this);
            var name = $item.find('.wcvs-payment-name').text();
            var percentage = parseFloat($item.find('.wcvs-payment-percentage').text());
            
            if (percentage > 0) {
                paymentData.push({
                    name: name,
                    percentage: percentage
                });
            }
        });

        if (paymentData.length === 0) {
            $chart.html('<div class="wcvs-no-data">No hay datos de métodos de pago disponibles</div>');
            return;
        }

        // Crear gráfico de barras horizontal
        var html = '<div class="wcvs-simple-chart">';
        html += '<div class="wcvs-chart-header">';
        html += '<span class="wcvs-chart-title">Distribución de Métodos de Pago</span>';
        html += '</div>';
        html += '<div class="wcvs-chart-bars-horizontal">';

        paymentData.forEach(function(item) {
            html += '<div class="wcvs-chart-bar-horizontal">';
            html += '<div class="wcvs-bar-label-horizontal">' + item.name + '</div>';
            html += '<div class="wcvs-bar-container-horizontal">';
            html += '<div class="wcvs-bar-fill-horizontal" style="width: ' + item.percentage + '%"></div>';
            html += '<div class="wcvs-bar-value-horizontal">' + item.percentage + '%</div>';
            html += '</div>';
            html += '</div>';
        });

        html += '</div>';
        html += '</div>';

        $chart.html(html);
    }

    // Función para actualizar estadísticas
    function updateStatistics() {
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();
        var period = $('#period').val();

        if (!dateFrom || !dateTo) {
            alert('Por favor seleccione las fechas de inicio y fin.');
            return;
        }

        $.ajax({
            url: wcvs_statistics.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_get_statistics',
                date_from: dateFrom,
                date_to: dateTo,
                period: period,
                nonce: wcvs_statistics.nonce
            },
            beforeSend: function() {
                $('.wcvs-statistics-container').html('<div class="wcvs-loading"><span class="dashicons dashicons-update wcvs-spinning"></span> ' + wcvs_statistics.i18n.generating + '</div>');
            },
            success: function(response) {
                if (response.success) {
                    // Recargar la página con los nuevos datos
                    location.reload();
                } else {
                    $('.wcvs-statistics-container').html('<div class="wcvs-error">' + wcvs_statistics.i18n.error + '</div>');
                }
            },
            error: function() {
                $('.wcvs-statistics-container').html('<div class="wcvs-error">' + wcvs_statistics.i18n.error + '</div>');
            }
        });
    }

    // Función para exportar estadísticas
    function exportStatistics(format) {
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();
        var period = $('#period').val();

        if (!dateFrom || !dateTo) {
            alert('Por favor seleccione las fechas de inicio y fin.');
            return;
        }

        var url = wcvs_statistics.ajax_url + '?action=wcvs_export_statistics&date_from=' + dateFrom + '&date_to=' + dateTo + '&period=' + period + '&format=' + format + '&_wpnonce=' + wcvs_statistics.nonce;
        
        // Crear enlace temporal para descarga
        var link = document.createElement('a');
        link.href = url;
        link.download = 'estadisticas_venezuela_' + dateFrom + '_' + dateTo + '.' + format;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Función para formatear números
    function formatNumber(number, decimals = 2) {
        return parseFloat(number).toLocaleString('es-VE', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    // Función para formatear moneda
    function formatCurrency(amount, currency = 'USD') {
        if (currency === 'USD') {
            return '$' + formatNumber(amount);
        } else if (currency === 'VES') {
            return formatNumber(amount) + ' Bs.';
        }
        return formatNumber(amount);
    }

    // Función para calcular porcentajes
    function calculatePercentage(value, total) {
        if (total === 0) return 0;
        return Math.round((value / total) * 100 * 10) / 10;
    }

    // Función para animar números
    function animateNumber($element, targetValue, duration = 1000) {
        var startValue = 0;
        var increment = targetValue / (duration / 16);
        
        var timer = setInterval(function() {
            startValue += increment;
            if (startValue >= targetValue) {
                startValue = targetValue;
                clearInterval(timer);
            }
            $element.text(formatNumber(startValue));
        }, 16);
    }

    // Función para crear tooltip
    function createTooltip(text) {
        var $tooltip = $('<div class="wcvs-tooltip">' + text + '</div>');
        $('body').append($tooltip);
        
        setTimeout(function() {
            $tooltip.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
        
        return $tooltip;
    }

    // Función para validar fechas
    function validateDates() {
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();
        
        if (dateFrom && dateTo && dateFrom > dateTo) {
            alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
            $('#date_from').val('');
            return false;
        }
        
        // Validar que no sea más de 2 años
        var fromDate = new Date(dateFrom);
        var toDate = new Date(dateTo);
        var diffTime = Math.abs(toDate - fromDate);
        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > 730) { // 2 años
            alert('El rango de fechas no puede ser mayor a 2 años.');
            $('#date_to').val('');
            return false;
        }
        
        return true;
    }

    // Función para crear resumen rápido
    function createQuickSummary() {
        var $summary = $('.wcvs-quick-summary');
        if ($summary.length === 0) {
            return;
        }

        var html = '<div class="wcvs-quick-summary-content">';
        html += '<h3>Resumen Rápido</h3>';
        html += '<div class="wcvs-quick-stats">';
        
        // Estadísticas del día
        html += '<div class="wcvs-quick-stat">';
        html += '<span class="wcvs-quick-label">Hoy:</span>';
        html += '<span class="wcvs-quick-value" id="today-sales">Cargando...</span>';
        html += '</div>';
        
        // Estadísticas de la semana
        html += '<div class="wcvs-quick-stat">';
        html += '<span class="wcvs-quick-label">Esta Semana:</span>';
        html += '<span class="wcvs-quick-value" id="week-sales">Cargando...</span>';
        html += '</div>';
        
        // Estadísticas del mes
        html += '<div class="wcvs-quick-stat">';
        html += '<span class="wcvs-quick-label">Este Mes:</span>';
        html += '<span class="wcvs-quick-value" id="month-sales">Cargando...</span>';
        html += '</div>';
        
        html += '</div>';
        html += '</div>';
        
        $summary.html(html);
        
        // Cargar estadísticas rápidas
        loadQuickStats();
    }

    // Función para cargar estadísticas rápidas
    function loadQuickStats() {
        $.ajax({
            url: wcvs_statistics.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_get_dashboard_stats',
                nonce: wcvs_statistics.nonce
            },
            success: function(response) {
                if (response.success) {
                    var stats = response.data;
                    
                    $('#today-sales').text(formatCurrency(stats.today.sales));
                    $('#week-sales').text(formatCurrency(stats.week.sales));
                    $('#month-sales').text(formatCurrency(stats.month.sales));
                }
            }
        });
    }

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        // Agregar estilos CSS
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .wcvs-statistics-container {
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 30px;
                    margin: 20px 0;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                
                .wcvs-statistics-filters {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    padding: 25px;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    margin-bottom: 25px;
                }
                
                .wcvs-date-filters {
                    display: flex;
                    align-items: center;
                    gap: 20px;
                    flex-wrap: wrap;
                }
                
                .wcvs-date-filters label {
                    font-weight: 600;
                    color: #495057;
                    font-size: 14px;
                }
                
                .wcvs-date-filters input[type="date"],
                .wcvs-date-filters select {
                    padding: 10px 12px;
                    border: 1px solid #ced4da;
                    border-radius: 6px;
                    font-size: 14px;
                    transition: border-color 0.3s ease;
                }
                
                .wcvs-date-filters input[type="date"]:focus,
                .wcvs-date-filters select:focus {
                    border-color: #0073aa;
                    outline: none;
                    box-shadow: 0 0 0 3px rgba(0,115,170,0.1);
                }
                
                .wcvs-date-filters .button {
                    padding: 10px 20px;
                    border-radius: 6px;
                    font-weight: 600;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    transition: all 0.3s ease;
                }
                
                .wcvs-executive-summary {
                    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                    padding: 25px;
                    border-radius: 8px;
                    margin-bottom: 30px;
                    border: 1px solid #90caf9;
                }
                
                .wcvs-executive-summary h2 {
                    margin: 0 0 20px 0;
                    color: #1976d2;
                    font-size: 22px;
                    font-weight: bold;
                }
                
                .wcvs-summary-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                    gap: 20px;
                    margin-top: 20px;
                }
                
                .wcvs-summary-item {
                    background: #fff;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    padding: 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    transition: all 0.3s ease;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                }
                
                .wcvs-summary-item:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                }
                
                .wcvs-summary-label {
                    font-weight: 600;
                    color: #333;
                    font-size: 14px;
                }
                
                .wcvs-summary-value {
                    font-weight: bold;
                    color: #0073aa;
                    font-size: 18px;
                }
                
                .wcvs-charts-section {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
                    gap: 30px;
                    margin: 30px 0;
                }
                
                .wcvs-chart-container {
                    background: #fff;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    padding: 20px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                }
                
                .wcvs-chart-container h3 {
                    margin: 0 0 20px 0;
                    color: #333;
                    font-size: 18px;
                    font-weight: 600;
                }
                
                .wcvs-chart {
                    min-height: 300px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .wcvs-simple-chart {
                    width: 100%;
                }
                
                .wcvs-chart-header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                
                .wcvs-chart-title {
                    font-weight: 600;
                    color: #333;
                    font-size: 16px;
                }
                
                .wcvs-chart-bars {
                    display: flex;
                    align-items: end;
                    justify-content: space-between;
                    height: 200px;
                    gap: 5px;
                }
                
                .wcvs-chart-bar {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    height: 100%;
                }
                
                .wcvs-bar-fill {
                    background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
                    width: 100%;
                    min-height: 10px;
                    border-radius: 4px 4px 0 0;
                    transition: height 0.3s ease;
                }
                
                .wcvs-bar-label {
                    font-size: 10px;
                    color: #666;
                    margin-top: 5px;
                    text-align: center;
                }
                
                .wcvs-bar-value {
                    font-size: 10px;
                    color: #0073aa;
                    font-weight: 600;
                    margin-top: 2px;
                }
                
                .wcvs-chart-bars-horizontal {
                    display: flex;
                    flex-direction: column;
                    gap: 15px;
                }
                
                .wcvs-chart-bar-horizontal {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }
                
                .wcvs-bar-label-horizontal {
                    min-width: 120px;
                    font-size: 14px;
                    color: #333;
                }
                
                .wcvs-bar-container-horizontal {
                    flex: 1;
                    height: 20px;
                    background: #f0f0f0;
                    border-radius: 10px;
                    position: relative;
                    overflow: hidden;
                }
                
                .wcvs-bar-fill-horizontal {
                    background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
                    height: 100%;
                    border-radius: 10px;
                    transition: width 0.3s ease;
                }
                
                .wcvs-bar-value-horizontal {
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 12px;
                    color: #fff;
                    font-weight: 600;
                }
                
                .wcvs-detailed-analysis {
                    margin: 30px 0;
                }
                
                .wcvs-detailed-analysis h3 {
                    margin: 0 0 20px 0;
                    color: #333;
                    font-size: 20px;
                    font-weight: 600;
                }
                
                .wcvs-analysis-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 20px;
                }
                
                .wcvs-analysis-item {
                    background: #fff;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    padding: 20px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                }
                
                .wcvs-analysis-item h4 {
                    margin: 0 0 15px 0;
                    color: #0073aa;
                    font-size: 16px;
                    font-weight: 600;
                }
                
                .wcvs-product-list,
                .wcvs-customer-list,
                .wcvs-payment-list,
                .wcvs-shipping-list {
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                }
                
                .wcvs-product-item,
                .wcvs-customer-item,
                .wcvs-payment-item,
                .wcvs-shipping-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px;
                    background: #f8f9fa;
                    border-radius: 5px;
                    border-left: 4px solid #0073aa;
                }
                
                .wcvs-product-name,
                .wcvs-customer-name,
                .wcvs-payment-name,
                .wcvs-shipping-name {
                    font-weight: 600;
                    color: #333;
                    flex: 1;
                }
                
                .wcvs-product-qty,
                .wcvs-customer-orders,
                .wcvs-payment-count,
                .wcvs-shipping-count {
                    color: #666;
                    font-size: 12px;
                }
                
                .wcvs-product-revenue,
                .wcvs-customer-total,
                .wcvs-payment-percentage,
                .wcvs-shipping-percentage {
                    color: #0073aa;
                    font-weight: 600;
                    font-size: 14px;
                }
                
                .wcvs-statistics-actions {
                    margin-top: 40px;
                    padding-top: 25px;
                    border-top: 2px solid #e0e0e0;
                    text-align: center;
                }
                
                .wcvs-statistics-actions .button {
                    margin: 0 10px;
                    padding: 12px 24px;
                    border-radius: 6px;
                    font-weight: 600;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    transition: all 0.3s ease;
                }
                
                .wcvs-statistics-actions .button-primary {
                    background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
                    border: none;
                    color: #fff;
                }
                
                .wcvs-statistics-actions .button-primary:hover {
                    background: linear-gradient(135deg, #005a87 0%, #004066 100%);
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,115,170,0.3);
                }
                
                .wcvs-statistics-actions .button-secondary {
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    color: #495057;
                }
                
                .wcvs-statistics-actions .button-secondary:hover {
                    background: #e9ecef;
                    border-color: #adb5bd;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                }
                
                .wcvs-loading {
                    text-align: center;
                    padding: 60px 20px;
                    font-size: 16px;
                    color: #0073aa;
                }
                
                .wcvs-loading .dashicons {
                    animation: wcvs-spin 1s linear infinite;
                    margin-right: 10px;
                }
                
                .wcvs-error {
                    text-align: center;
                    padding: 60px 20px;
                    color: #d63638;
                    font-size: 16px;
                    background: #fef2f2;
                    border: 1px solid #fecaca;
                    border-radius: 8px;
                }
                
                .wcvs-no-data {
                    text-align: center;
                    padding: 40px 20px;
                    color: #666;
                    font-style: italic;
                }
                
                .wcvs-spinning {
                    animation: wcvs-spin 1s linear infinite;
                }
                
                @keyframes wcvs-spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                .wcvs-tooltip {
                    position: fixed;
                    background: #333;
                    color: #fff;
                    padding: 8px 12px;
                    border-radius: 4px;
                    font-size: 12px;
                    z-index: 9999;
                    pointer-events: none;
                }
                
                @media print {
                    .wcvs-statistics-actions,
                    .wcvs-statistics-filters {
                        display: none !important;
                    }
                    
                    .wcvs-statistics-container {
                        border: none !important;
                        padding: 0 !important;
                        box-shadow: none !important;
                    }
                    
                    .wcvs-summary-item:hover {
                        transform: none !important;
                        box-shadow: none !important;
                    }
                }
                
                @media (max-width: 768px) {
                    .wcvs-date-filters {
                        flex-direction: column;
                        align-items: stretch;
                        gap: 15px;
                    }
                    
                    .wcvs-date-filters .button {
                        width: 100%;
                        justify-content: center;
                    }
                    
                    .wcvs-summary-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .wcvs-charts-section {
                        grid-template-columns: 1fr;
                    }
                    
                    .wcvs-analysis-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .wcvs-chart-bars {
                        gap: 2px;
                    }
                    
                    .wcvs-bar-label {
                        font-size: 8px;
                    }
                    
                    .wcvs-bar-value {
                        font-size: 8px;
                    }
                }
            `)
            .appendTo('head');
        
        // Generar gráficos cuando estén listos
        setTimeout(function() {
            generateCharts();
        }, 1000);
        
        // Manejar clic en botón de generar estadísticas
        $('.wcvs-date-filters .button').on('click', function(e) {
            e.preventDefault();
            if (validateDates()) {
                updateStatistics();
            }
        });
        
        // Manejar clic en botones de exportar
        $('.wcvs-statistics-actions .button').on('click', function(e) {
            e.preventDefault();
            var format = $(this).data('format') || 'excel';
            exportStatistics(format);
        });
        
        // Validación de fechas en tiempo real
        $('#date_from, #date_to').on('change', function() {
            validateDates();
        });
        
        // Auto-generar estadísticas si hay fechas en la URL
        var urlParams = new URLSearchParams(window.location.search);
        var dateFrom = urlParams.get('date_from');
        var dateTo = urlParams.get('date_to');
        
        if (dateFrom && dateTo) {
            $('#date_from').val(dateFrom);
            $('#date_to').val(dateTo);
        }
        
        // Crear resumen rápido
        createQuickSummary();
    });

})(jQuery);
