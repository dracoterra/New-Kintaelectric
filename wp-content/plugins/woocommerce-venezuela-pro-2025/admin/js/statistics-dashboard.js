/**
 * JavaScript para Estadísticas del Dashboard
 * 
 * @package WooCommerce_Venezuela_Suite_2025
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Función global para refrescar estadísticas
    window.wcvsRefreshStats = function() {
        loadDashboardStatistics();
    };

    // Función para cargar estadísticas del dashboard
    function loadDashboardStatistics() {
        $.ajax({
            url: wcvs_statistics_dashboard.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_get_dashboard_stats',
                nonce: wcvs_statistics_dashboard.nonce
            },
            beforeSend: function() {
                // Mostrar estado de carga
                $('.wcvs-quick-stat-value').text('Cargando...');
                $('.wcvs-quick-stat-orders').text('...');
            },
            success: function(response) {
                if (response.success) {
                    updateStatisticsDisplay(response.data);
                } else {
                    showStatisticsError();
                }
            },
            error: function() {
                showStatisticsError();
            }
        });
    }

    // Función para actualizar la visualización de estadísticas
    function updateStatisticsDisplay(stats) {
        // Actualizar estadísticas de hoy
        $('#today-sales').text(formatCurrency(stats.today.sales));
        $('#today-orders').text(stats.today.orders + ' pedidos');
        
        // Actualizar estadísticas de la semana
        $('#week-sales').text(formatCurrency(stats.week.sales));
        $('#week-orders').text(stats.week.orders + ' pedidos');
        
        // Actualizar estadísticas del mes
        $('#month-sales').text(formatCurrency(stats.month.sales));
        $('#month-orders').text(stats.month.orders + ' pedidos');
        
        // Actualizar estadísticas del año
        $('#year-sales').text(formatCurrency(stats.year.sales));
        $('#year-orders').text(stats.year.orders + ' pedidos');
        
        // Animar números
        animateStatistics();
    }

    // Función para mostrar error en estadísticas
    function showStatisticsError() {
        $('.wcvs-quick-stat-value').text('Error');
        $('.wcvs-quick-stat-orders').text('No disponible');
    }

    // Función para formatear moneda
    function formatCurrency(amount) {
        if (amount === 0) return '$0.00';
        return '$' + parseFloat(amount).toLocaleString('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Función para animar estadísticas
    function animateStatistics() {
        $('.wcvs-quick-stat-value').each(function() {
            var $element = $(this);
            var text = $element.text();
            var value = parseFloat(text.replace(/[$,]/g, ''));
            
            if (!isNaN(value) && value > 0) {
                animateNumber($element, value);
            }
        });
    }

    // Función para animar números
    function animateNumber($element, targetValue) {
        var startValue = 0;
        var duration = 1000;
        var increment = targetValue / (duration / 16);
        
        var timer = setInterval(function() {
            startValue += increment;
            if (startValue >= targetValue) {
                startValue = targetValue;
                clearInterval(timer);
            }
            $element.text(formatCurrency(startValue));
        }, 16);
    }

    // Función para crear tooltip de estadísticas
    function createStatisticsTooltip($element, content) {
        $element.hover(
            function() {
                var $tooltip = $('<div class="wcvs-statistics-tooltip">' + content + '</div>');
                $('body').append($tooltip);
                
                var offset = $element.offset();
                $tooltip.css({
                    position: 'absolute',
                    top: offset.top - $tooltip.outerHeight() - 10,
                    left: offset.left + ($element.outerWidth() / 2) - ($tooltip.outerWidth() / 2),
                    zIndex: 9999
                });
            },
            function() {
                $('.wcvs-statistics-tooltip').remove();
            }
        );
    }

    // Función para configurar tooltips
    function setupTooltips() {
        // Tooltip para estadísticas de hoy
        createStatisticsTooltip($('#today-sales'), 'Ventas totales del día de hoy');
        createStatisticsTooltip($('#today-orders'), 'Número de pedidos del día de hoy');
        
        // Tooltip para estadísticas de la semana
        createStatisticsTooltip($('#week-sales'), 'Ventas totales de los últimos 7 días');
        createStatisticsTooltip($('#week-orders'), 'Número de pedidos de los últimos 7 días');
        
        // Tooltip para estadísticas del mes
        createStatisticsTooltip($('#month-sales'), 'Ventas totales del mes actual');
        createStatisticsTooltip($('#month-orders'), 'Número de pedidos del mes actual');
        
        // Tooltip para estadísticas del año
        createStatisticsTooltip($('#year-sales'), 'Ventas totales del año actual');
        createStatisticsTooltip($('#year-orders'), 'Número de pedidos del año actual');
    }

    // Función para crear indicadores de tendencia
    function createTrendIndicators() {
        // Esta función podría comparar con períodos anteriores
        // Por ahora, solo agregamos clases CSS para indicar tendencias
        $('.wcvs-quick-stat-item').each(function() {
            var $item = $(this);
            var $value = $item.find('.wcvs-quick-stat-value');
            var value = parseFloat($value.text().replace(/[$,]/g, ''));
            
            if (value > 0) {
                $item.addClass('wcvs-stat-positive');
            } else {
                $item.addClass('wcvs-stat-zero');
            }
        });
    }

    // Función para configurar auto-actualización
    function setupAutoRefresh() {
        // Actualizar estadísticas cada 5 minutos
        setInterval(function() {
            loadDashboardStatistics();
        }, 300000); // 5 minutos
    }

    // Función para configurar eventos de clic
    function setupClickEvents() {
        // Clic en estadísticas para ir a página completa
        $('.wcvs-quick-stat-item').on('click', function() {
            var period = $(this).find('.wcvs-quick-stat-label').text().toLowerCase();
            var url = wcvs_statistics_dashboard.ajax_url.replace('admin-ajax.php', 'admin.php') + '?page=wcvs-statistics';
            
            // Agregar parámetros de fecha según el período
            var today = new Date();
            var dateFrom, dateTo;
            
            switch(period) {
                case 'hoy':
                    dateFrom = dateTo = today.toISOString().split('T')[0];
                    break;
                case 'esta semana':
                    var weekStart = new Date(today);
                    weekStart.setDate(today.getDate() - today.getDay());
                    dateFrom = weekStart.toISOString().split('T')[0];
                    dateTo = today.toISOString().split('T')[0];
                    break;
                case 'este mes':
                    dateFrom = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    dateTo = today.toISOString().split('T')[0];
                    break;
                case 'este año':
                    dateFrom = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
                    dateTo = today.toISOString().split('T')[0];
                    break;
                default:
                    return;
            }
            
            url += '&date_from=' + dateFrom + '&date_to=' + dateTo;
            window.location.href = url;
        });
    }

    // Función para configurar efectos visuales
    function setupVisualEffects() {
        // Efecto hover en elementos de estadísticas
        $('.wcvs-quick-stat-item').hover(
            function() {
                $(this).addClass('wcvs-stat-hover');
            },
            function() {
                $(this).removeClass('wcvs-stat-hover');
            }
        );
        
        // Efecto de carga en botón de actualizar
        $('.wcvs-statistics-actions .button').on('click', function() {
            var $button = $(this);
            var $icon = $button.find('.dashicons');
            
            $icon.addClass('wcvs-spinning');
            $button.prop('disabled', true);
            
            setTimeout(function() {
                $icon.removeClass('wcvs-spinning');
                $button.prop('disabled', false);
            }, 2000);
        });
    }

    // Función para configurar responsividad
    function setupResponsiveness() {
        // Ajustar layout en pantallas pequeñas
        function adjustLayout() {
            if ($(window).width() < 768) {
                $('.wcvs-quick-stats-grid').addClass('wcvs-mobile-layout');
            } else {
                $('.wcvs-quick-stats-grid').removeClass('wcvs-mobile-layout');
            }
        }
        
        $(window).on('resize', adjustLayout);
        adjustLayout();
    }

    // Función para configurar accesibilidad
    function setupAccessibility() {
        // Agregar atributos ARIA
        $('.wcvs-quick-stat-item').attr('role', 'button');
        $('.wcvs-quick-stat-item').attr('tabindex', '0');
        
        // Soporte para teclado
        $('.wcvs-quick-stat-item').on('keypress', function(e) {
            if (e.which === 13 || e.which === 32) { // Enter o Espacio
                $(this).click();
            }
        });
        
        // Agregar labels descriptivos
        $('.wcvs-quick-stat-item').each(function() {
            var $item = $(this);
            var label = $item.find('.wcvs-quick-stat-label').text();
            var value = $item.find('.wcvs-quick-stat-value').text();
            var orders = $item.find('.wcvs-quick-stat-orders').text();
            
            $item.attr('aria-label', label + ': ' + value + ', ' + orders);
        });
    }

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        // Agregar estilos CSS
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .wcvs-statistics-widget {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    padding: 25px;
                    margin: 20px 0;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                
                .wcvs-statistics-header {
                    text-align: center;
                    margin-bottom: 25px;
                }
                
                .wcvs-statistics-header h2 {
                    color: #0073aa;
                    margin: 0 0 10px 0;
                    font-size: 20px;
                    font-weight: 600;
                }
                
                .wcvs-statistics-header p {
                    color: #666;
                    margin: 0;
                    font-size: 14px;
                }
                
                .wcvs-quick-stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 20px;
                    margin: 25px 0;
                }
                
                .wcvs-quick-stat-item {
                    background: #fff;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    padding: 20px;
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    transition: all 0.3s ease;
                    cursor: pointer;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                }
                
                .wcvs-quick-stat-item:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                    border-color: #0073aa;
                }
                
                .wcvs-quick-stat-item.wcvs-stat-hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                }
                
                .wcvs-quick-stat-icon {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                    font-size: 20px;
                }
                
                .wcvs-quick-stat-info {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    gap: 5px;
                }
                
                .wcvs-quick-stat-label {
                    font-weight: 600;
                    color: #333;
                    font-size: 14px;
                }
                
                .wcvs-quick-stat-value {
                    font-weight: bold;
                    color: #0073aa;
                    font-size: 18px;
                }
                
                .wcvs-quick-stat-orders {
                    color: #666;
                    font-size: 12px;
                }
                
                .wcvs-statistics-actions {
                    text-align: center;
                    margin-top: 25px;
                    padding-top: 20px;
                    border-top: 1px solid #dee2e6;
                }
                
                .wcvs-statistics-actions .button {
                    margin: 0 10px;
                    padding: 10px 20px;
                    border-radius: 6px;
                    font-weight: 600;
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
                
                .wcvs-statistics-tooltip {
                    background: #333;
                    color: #fff;
                    padding: 8px 12px;
                    border-radius: 4px;
                    font-size: 12px;
                    pointer-events: none;
                    z-index: 9999;
                }
                
                .wcvs-stat-positive {
                    border-left: 4px solid #00a32a;
                }
                
                .wcvs-stat-zero {
                    border-left: 4px solid #dba617;
                }
                
                .wcvs-spinning {
                    animation: wcvs-spin 1s linear infinite;
                }
                
                @keyframes wcvs-spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                .wcvs-mobile-layout {
                    grid-template-columns: 1fr !important;
                }
                
                @media (max-width: 768px) {
                    .wcvs-quick-stats-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .wcvs-quick-stat-item {
                        padding: 15px;
                    }
                    
                    .wcvs-quick-stat-icon {
                        width: 40px;
                        height: 40px;
                        font-size: 16px;
                    }
                    
                    .wcvs-quick-stat-value {
                        font-size: 16px;
                    }
                    
                    .wcvs-statistics-actions .button {
                        margin: 5px;
                        padding: 8px 16px;
                        font-size: 14px;
                    }
                }
                
                @media (max-width: 480px) {
                    .wcvs-statistics-widget {
                        padding: 20px;
                    }
                    
                    .wcvs-quick-stat-item {
                        padding: 12px;
                        gap: 10px;
                    }
                    
                    .wcvs-quick-stat-icon {
                        width: 35px;
                        height: 35px;
                        font-size: 14px;
                    }
                    
                    .wcvs-quick-stat-value {
                        font-size: 14px;
                    }
                    
                    .wcvs-quick-stat-label {
                        font-size: 12px;
                    }
                    
                    .wcvs-quick-stat-orders {
                        font-size: 10px;
                    }
                }
            `)
            .appendTo('head');
        
        // Cargar estadísticas iniciales
        loadDashboardStatistics();
        
        // Configurar funcionalidades
        setupTooltips();
        setupClickEvents();
        setupVisualEffects();
        setupResponsiveness();
        setupAccessibility();
        setupAutoRefresh();
        
        // Configurar indicadores de tendencia después de cargar datos
        setTimeout(function() {
            createTrendIndicators();
        }, 1000);
    });

})(jQuery);
