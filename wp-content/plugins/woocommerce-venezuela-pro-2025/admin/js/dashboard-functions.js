/**
 * Dashboard Functions - WooCommerce Venezuela Suite 2025
 * Funciones JavaScript para el dashboard del plugin
 */

jQuery(document).ready(function($) {
    'use strict';

    // Configuración de Moneda Base
    window.wcvsConfigureCurrency = function() {
        if (confirm('¿Configurar moneda base a USD con formato venezolano?')) {
            $.ajax({
                url: wcvs_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_configure_currency',
                    nonce: wcvs_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Moneda configurada correctamente');
                        location.reload();
                    } else {
                        alert('Error al configurar moneda: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error de conexión');
                }
            });
        }
    };

    // Configuración de Impuestos
    window.wcvsConfigureTaxes = function() {
        if (confirm('¿Configurar impuestos IVA (16%) e IGTF (3%)?')) {
            $.ajax({
                url: wcvs_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_configure_taxes',
                    nonce: wcvs_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Impuestos configurados correctamente');
                        location.reload();
                    } else {
                        alert('Error al configurar impuestos: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error de conexión');
                }
            });
        }
    };

    // Configuración de Métodos de Pago
    window.wcvsConfigurePayments = function() {
        if (confirm('¿Habilitar métodos de pago venezolanos?')) {
            $.ajax({
                url: wcvs_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_configure_payments',
                    nonce: wcvs_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Métodos de pago configurados correctamente');
                        location.reload();
                    } else {
                        alert('Error al configurar métodos de pago: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error de conexión');
                }
            });
        }
    };

    // Configuración de Métodos de Envío
    window.wcvsConfigureShipping = function() {
        if (confirm('¿Configurar métodos de envío venezolanos?')) {
            $.ajax({
                url: wcvs_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_configure_shipping',
                    nonce: wcvs_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Métodos de envío configurados correctamente');
                        location.reload();
                    } else {
                        alert('Error al configurar métodos de envío: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error de conexión');
                }
            });
        }
    };

    // Configuración de Ubicación
    window.wcvsConfigureLocation = function() {
        if (confirm('¿Configurar Venezuela como país base?')) {
            $.ajax({
                url: wcvs_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_configure_location',
                    nonce: wcvs_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Ubicación configurada correctamente');
                        location.reload();
                    } else {
                        alert('Error al configurar ubicación: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error de conexión');
                }
            });
        }
    };

    // Configuración de Páginas
    window.wcvsConfigurePages = function() {
        if (confirm('¿Crear páginas necesarias de WooCommerce?')) {
            $.ajax({
                url: wcvs_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_configure_pages',
                    nonce: wcvs_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Páginas creadas correctamente');
                        location.reload();
                    } else {
                        alert('Error al crear páginas: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error de conexión');
                }
            });
        }
    };

    // Actualizar tasa de cambio
    window.wcvsRefreshRate = function() {
        $('.wcvs-refresh-rate').prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Actualizando...');
        
        $.ajax({
            url: wcvs_bcv_widget.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_refresh_rate',
                nonce: wcvs_bcv_widget.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.wcvs-rate-value').html(response.data.rate_display);
                    $('.wcvs-rate-status').html(response.data.status);
                    $('.wcvs-refresh-rate').prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Actualizar');
                } else {
                    alert('Error al actualizar tasa: ' + response.data);
                    $('.wcvs-refresh-rate').prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Actualizar');
                }
            },
            error: function() {
                alert('Error de conexión');
                $('.wcvs-refresh-rate').prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Actualizar');
            }
        });
    };

    // Configuración rápida completa
    window.wcvsQuickConfigure = function() {
        if (confirm('¿Configurar WooCommerce automáticamente para Venezuela? Esto modificará varias configuraciones.')) {
            $('.wcvs-quick-config-button').prop('disabled', true).html('<span class="dashicons dashicons-admin-settings"></span> Configurando...');
            
            $.ajax({
                url: wcvs_quick_config.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_apply_config',
                    nonce: wcvs_quick_config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Configuración completada correctamente');
                        location.reload();
                    } else {
                        alert('Error en la configuración: ' + response.data);
                        $('.wcvs-quick-config-button').prop('disabled', false).html('<span class="dashicons dashicons-admin-settings"></span> Completar Configuración');
                    }
                },
                error: function() {
                    alert('Error de conexión');
                    $('.wcvs-quick-config-button').prop('disabled', false).html('<span class="dashicons dashicons-admin-settings"></span> Completar Configuración');
                }
            });
        }
    };

    // Actualizar estadísticas
    window.wcvsRefreshStats = function() {
        $('.wcvs-stats-actions button').prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Actualizando...');
        
        $.ajax({
            url: wcvs_statistics_dashboard.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_get_dashboard_stats',
                nonce: wcvs_statistics_dashboard.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#today-sales').text(response.data.today_sales);
                    $('#month-sales').text(response.data.month_sales);
                    $('#total-orders').text(response.data.total_orders);
                    $('.wcvs-stats-actions button').prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Actualizar');
                } else {
                    alert('Error al cargar estadísticas: ' + response.data);
                    $('.wcvs-stats-actions button').prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Actualizar');
                }
            },
            error: function() {
                alert('Error de conexión');
                $('.wcvs-stats-actions button').prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Actualizar');
            }
        });
    };

    // Cargar estadísticas al cargar la página
    if (typeof wcvs_statistics_dashboard !== 'undefined') {
        wcvsRefreshStats();
    }
});
