<?php
/**
 * Monitor de Errores - WooCommerce Venezuela Pro
 * Página de administración para monitorear errores del plugin
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Error_Monitor {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_wvp_get_error_report', array($this, 'get_error_report'));
        add_action('wp_ajax_wvp_clear_errors', array($this, 'clear_errors'));
        add_action('wp_ajax_wvp_clear_debug_log', array($this, 'clear_debug_log'));
        
        // Agregar página de monitoreo de errores
        add_action('admin_menu', array($this, 'add_error_monitor_page'));
    }
    
    /**
     * Agregar página de monitoreo de errores
     */
    public function add_error_monitor_page() {
        add_submenu_page(
            'wvp-dashboard',
            'Monitor de Errores',
            'Monitor de Errores',
            'manage_woocommerce',
            'wvp-error-monitor',
            array($this, 'display_error_monitor_page')
        );
    }
    
    /**
     * Mostrar página de monitoreo de errores
     */
    public function display_error_monitor_page() {
        ?>
        <div class="wrap">
            <h1>Monitor de Errores - WooCommerce Venezuela Pro</h1>
            
            <div class="notice notice-info">
                <p><strong>Información:</strong> Esta página permite monitorear y gestionar los errores del plugin.</p>
            </div>
            
            <div id="wvp-error-monitor-content">
                <div class="wvp-loading">
                    <p>Cargando información de errores...</p>
                </div>
            </div>
            
            <h2>Acciones</h2>
            <p>
                <button type="button" id="wvp-refresh-errors" class="button button-primary">
                    Actualizar Errores
                </button>
                <button type="button" id="wvp-clear-errors" class="button button-secondary">
                    Limpiar Errores
                </button>
                <button type="button" id="wvp-export-errors" class="button button-secondary">
                    Exportar Reporte
                </button>
                <button type="button" id="wvp-clear-debug-log" class="button button-secondary">
                    Limpiar Debug Log
                </button>
            </p>
            
            <div id="wvp-error-actions-results"></div>
            
            <script>
            jQuery(document).ready(function($) {
                // Cargar errores al inicio
                loadErrorReport();
                
                // Botón de actualizar
                $('#wvp-refresh-errors').on('click', function() {
                    loadErrorReport();
                });
                
                // Botón de limpiar errores
                $('#wvp-clear-errors').on('click', function() {
                    if (confirm('¿Estás seguro de que quieres limpiar todos los errores?')) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'wvp_clear_errors'
                            },
                            success: function(response) {
                                $('#wvp-error-actions-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                                loadErrorReport();
                            },
                            error: function() {
                                $('#wvp-error-actions-results').html('<div class="notice notice-error"><p>Error al limpiar errores</p></div>');
                            }
                        });
                    }
                });
                
                // Botón de exportar
                $('#wvp-export-errors').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_get_error_report'
                        },
                        success: function(response) {
                            var report = JSON.stringify(response.data, null, 2);
                            var blob = new Blob([report], {type: 'application/json'});
                            var url = window.URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = 'wvp-error-report-' + new Date().toISOString().split('T')[0] + '.json';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            window.URL.revokeObjectURL(url);
                        },
                        error: function() {
                            $('#wvp-error-actions-results').html('<div class="notice notice-error"><p>Error al exportar reporte</p></div>');
                        }
                    });
                });
                
                // Botón de limpiar debug.log
                $('#wvp-clear-debug-log').on('click', function() {
                    if (confirm('¿Estás seguro de que quieres limpiar el debug.log? Se creará un backup automáticamente.')) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'wvp_clear_debug_log'
                            },
                            success: function(response) {
                                $('#wvp-error-actions-results').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                                loadErrorReport();
                            },
                            error: function() {
                                $('#wvp-error-actions-results').html('<div class="notice notice-error"><p>Error al limpiar debug.log</p></div>');
                            }
                        });
                    }
                });
                
                function loadErrorReport() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wvp_get_error_report'
                        },
                        success: function(response) {
                            displayErrorReport(response.data);
                        },
                        error: function() {
                            $('#wvp-error-monitor-content').html('<div class="notice notice-error"><p>Error al cargar información de errores</p></div>');
                        }
                    });
                }
                
                function displayErrorReport(data) {
                    var html = '';
                    
                    // Estadísticas
                    html += '<h2>Estadísticas de Errores</h2>';
                    html += '<div class="wvp-error-stats">';
                    html += '<div class="wvp-stat-box">';
                    html += '<h3>Total de Errores</h3>';
                    html += '<span class="wvp-stat-number">' + data.stats.total + '</span>';
                    html += '</div>';
                    html += '<div class="wvp-stat-box">';
                    html += '<h3>Errores Fatales</h3>';
                    html += '<span class="wvp-stat-number wvp-fatal">' + data.stats.fatal + '</span>';
                    html += '</div>';
                    html += '<div class="wvp-stat-box">';
                    html += '<h3>Advertencias</h3>';
                    html += '<span class="wvp-stat-number wvp-warning">' + data.stats.warning + '</span>';
                    html += '</div>';
                    html += '<div class="wvp-stat-box">';
                    html += '<h3>Avisos</h3>';
                    html += '<span class="wvp-stat-number wvp-notice">' + data.stats.notice + '</span>';
                    html += '</div>';
                    html += '<div class="wvp-stat-box">';
                    html += '<h3>Recientes (1h)</h3>';
                    html += '<span class="wvp-stat-number wvp-recent">' + data.stats.recent + '</span>';
                    html += '</div>';
                    html += '</div>';
                    
                    // Lista de errores
                    if (data.errors && data.errors.length > 0) {
                        html += '<h2>Lista de Errores</h2>';
                        html += '<table class="widefat fixed striped">';
                        html += '<thead>';
                        html += '<tr>';
                        html += '<th>Tipo</th>';
                        html += '<th>Mensaje</th>';
                        html += '<th>Archivo</th>';
                        html += '<th>Línea</th>';
                        html += '<th>Fecha</th>';
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        
                        data.errors.forEach(function(error) {
                            var typeClass = '';
                            var typeText = '';
                            
                            if (error.fatal) {
                                typeClass = 'wvp-fatal';
                                typeText = 'FATAL';
                            } else if (error.type === E_WARNING) {
                                typeClass = 'wvp-warning';
                                typeText = 'WARNING';
                            } else if (error.type === E_NOTICE) {
                                typeClass = 'wvp-notice';
                                typeText = 'NOTICE';
                            } else {
                                typeClass = 'wvp-error';
                                typeText = 'ERROR';
                            }
                            
                            html += '<tr>';
                            html += '<td><span class="wvp-error-type ' + typeClass + '">' + typeText + '</span></td>';
                            html += '<td>' + escapeHtml(error.message) + '</td>';
                            html += '<td>' + escapeHtml(error.file) + '</td>';
                            html += '<td>' + error.line + '</td>';
                            html += '<td>' + new Date(error.timestamp * 1000).toLocaleString() + '</td>';
                            html += '</tr>';
                        });
                        
                        html += '</tbody>';
                        html += '</table>';
                    } else {
                        html += '<div class="notice notice-success"><p>No hay errores registrados.</p></div>';
                    }
                    
                    $('#wvp-error-monitor-content').html(html);
                }
                
                function escapeHtml(text) {
                    var map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };
                    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
                }
            });
            </script>
            
            <style>
            .wvp-error-stats {
                display: flex;
                gap: 20px;
                margin: 20px 0;
            }
            
            .wvp-stat-box {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 20px;
                text-align: center;
                min-width: 120px;
            }
            
            .wvp-stat-box h3 {
                margin: 0 0 10px 0;
                font-size: 14px;
                color: #666;
            }
            
            .wvp-stat-number {
                font-size: 24px;
                font-weight: bold;
                color: #333;
            }
            
            .wvp-stat-number.wvp-fatal {
                color: #d63638;
            }
            
            .wvp-stat-number.wvp-warning {
                color: #dba617;
            }
            
            .wvp-stat-number.wvp-notice {
                color: #72aee6;
            }
            
            .wvp-stat-number.wvp-recent {
                color: #00a32a;
            }
            
            .wvp-error-type {
                padding: 4px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: bold;
                text-transform: uppercase;
            }
            
            .wvp-error-type.wvp-fatal {
                background: #d63638;
                color: #fff;
            }
            
            .wvp-error-type.wvp-warning {
                background: #dba617;
                color: #fff;
            }
            
            .wvp-error-type.wvp-notice {
                background: #72aee6;
                color: #fff;
            }
            
            .wvp-error-type.wvp-error {
                background: #d63638;
                color: #fff;
            }
            
            .wvp-loading {
                text-align: center;
                padding: 40px;
                color: #666;
            }
            </style>
        </div>
        <?php
    }
    
    /**
     * Obtener reporte de errores via AJAX
     */
    public function get_error_report() {
        if (class_exists('WVP_Error_Handler')) {
            $error_handler = WVP_Error_Handler::get_instance();
            $report = $error_handler->generate_error_report();
            
            wp_send_json_success($report);
        } else {
            wp_send_json_error('Manejador de errores no disponible');
        }
    }
    
    /**
     * Limpiar debug.log via AJAX
     */
    public function clear_debug_log() {
        if (class_exists('WVP_Debug_Log_Cleaner')) {
            $cleaner = WVP_Debug_Log_Cleaner::get_instance();
            $result = $cleaner->manual_clean();
            
            wp_send_json_success($result);
        } else {
            wp_send_json_error('Limpiador de debug log no disponible');
        }
    }
}

// Inicializar solo en admin
if (is_admin()) {
    new WVP_Error_Monitor();
}
?>