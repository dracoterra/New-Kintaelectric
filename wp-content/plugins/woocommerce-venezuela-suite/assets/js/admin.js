/**
 * JavaScript del Admin - WooCommerce Venezuela Suite
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Objeto principal del admin
    const WVSAdmin = {
        
        /**
         * Inicializar
         */
        init: function() {
            this.bindEvents();
            this.initComponents();
        },
        
        /**
         * Vincular eventos
         */
        bindEvents: function() {
            // Toggle de módulos
            $(document).on('click', '.wvs-toggle-module', this.toggleModule);
            
            // Guardar configuración
            $(document).on('submit', '#wvs-settings-form', this.saveSettings);
            
            // Actualizar datos del dashboard
            $(document).on('click', '.wvs-refresh-data', this.refreshDashboardData);
        },
        
        /**
         * Inicializar componentes
         */
        initComponents: function() {
            this.initTooltips();
            this.initNotifications();
        },
        
        /**
         * Alternar módulo
         */
        toggleModule: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const moduleKey = $button.data('module');
            const action = $button.data('action');
            
            // Confirmar acción
            if (!confirm(wvs_admin.strings.confirm)) {
                return;
            }
            
            // Mostrar estado de carga
            $button.prop('disabled', true).addClass('wvs-loading');
            
            // Realizar petición AJAX
            $.ajax({
                url: wvs_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wvs_toggle_module',
                    module_key: moduleKey,
                    action_type: action,
                    nonce: wvs_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WVSAdmin.showNotification(response.data.message, 'success');
                        WVSAdmin.updateModuleCard(moduleKey, response.data.module_info);
                    } else {
                        WVSAdmin.showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    WVSAdmin.showNotification(wvs_admin.strings.error, 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).removeClass('wvs-loading');
                }
            });
        },
        
        /**
         * Actualizar tarjeta de módulo
         */
        updateModuleCard: function(moduleKey, moduleInfo) {
            const $card = $(`.wvs-module-card[data-module="${moduleKey}"]`);
            
            if (!$card.length) {
                return;
            }
            
            // Actualizar estado
            const $status = $card.find('.wvs-module-status');
            if (moduleInfo.active) {
                $status.html('<span class="wvs-status-active">Activo</span>');
                $card.addClass('active').removeClass('inactive');
            } else {
                $status.html('<span class="wvs-status-inactive">Inactivo</span>');
                $card.addClass('inactive').removeClass('active');
            }
            
            // Actualizar botón
            const $button = $card.find('.wvs-toggle-module');
            if (moduleInfo.can_activate) {
                $button.text(wvs_admin.strings.activate)
                       .removeClass('button-secondary')
                       .addClass('button-primary')
                       .data('action', 'activate');
            } else if (moduleInfo.can_deactivate) {
                $button.text(wvs_admin.strings.deactivate)
                       .removeClass('button-primary')
                       .addClass('button-secondary')
                       .data('action', 'deactivate');
            } else {
                $button.text('No Disponible').prop('disabled', true);
            }
        },
        
        /**
         * Guardar configuración
         */
        saveSettings: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitButton = $form.find('button[type="submit"]');
            
            // Mostrar estado de carga
            $submitButton.prop('disabled', true).text(wvs_admin.strings.loading);
            
            // Recopilar datos del formulario
            const formData = $form.serializeArray();
            const data = {
                action: 'wvs_save_settings',
                nonce: wvs_admin.nonce
            };
            
            // Convertir array a objeto
            $.each(formData, function(i, field) {
                data[field.name] = field.value;
            });
            
            // Realizar petición AJAX
            $.ajax({
                url: wvs_admin.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        WVSAdmin.showNotification(response.data.message, 'success');
                    } else {
                        WVSAdmin.showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    WVSAdmin.showNotification(wvs_admin.strings.error, 'error');
                },
                complete: function() {
                    $submitButton.prop('disabled', false).text(wvs_admin.strings.save);
                }
            });
        },
        
        /**
         * Actualizar datos del dashboard
         */
        refreshDashboardData: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            
            // Mostrar estado de carga
            $button.prop('disabled', true).text(wvs_admin.strings.loading);
            
            // Realizar petición AJAX
            $.ajax({
                url: wvs_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wvs_get_dashboard_data',
                    nonce: wvs_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WVSAdmin.updateDashboard(response.data);
                        WVSAdmin.showNotification('Datos actualizados correctamente', 'success');
                    } else {
                        WVSAdmin.showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    WVSAdmin.showNotification(wvs_admin.strings.error, 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Actualizar');
                }
            });
        },
        
        /**
         * Actualizar dashboard
         */
        updateDashboard: function(data) {
            // Actualizar estadísticas
            $('.wvs-stat-card').each(function() {
                const $card = $(this);
                const $value = $card.find('h3');
                const $label = $card.find('p');
                
                // Identificar tipo de estadística por el texto del label
                const labelText = $label.text();
                
                if (labelText.includes('Ventas')) {
                    $value.text(data.total_sales);
                } else if (labelText.includes('Pedidos')) {
                    $value.text(data.total_orders.toLocaleString());
                } else if (labelText.includes('Tasa')) {
                    $value.text(data.bcv_rate.toLocaleString('es-VE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' Bs.');
                } else if (labelText.includes('Módulos')) {
                    $value.text(data.active_modules.length);
                }
            });
            
            // Actualizar gráficos si existen
            if (typeof Chart !== 'undefined') {
                WVSAdmin.updateCharts(data);
            }
        },
        
        /**
         * Actualizar gráficos
         */
        updateCharts: function(data) {
            // Actualizar gráfico de ventas
            const salesChart = Chart.getChart('sales-chart');
            if (salesChart) {
                salesChart.data.labels = data.sales_chart.labels;
                salesChart.data.datasets[0].data = data.sales_chart.data;
                salesChart.update();
            }
            
            // Actualizar gráfico de métodos de pago
            const paymentChart = Chart.getChart('payment-methods-chart');
            if (paymentChart) {
                paymentChart.data.labels = data.payment_methods.labels;
                paymentChart.data.datasets[0].data = data.payment_methods.data;
                paymentChart.update();
            }
        },
        
        /**
         * Mostrar notificación
         */
        showNotification: function(message, type = 'success') {
            // Remover notificaciones existentes
            $('.wvs-notification').remove();
            
            // Crear nueva notificación
            const $notification = $(`
                <div class="wvs-notification ${type}">
                    <p>${message}</p>
                </div>
            `);
            
            // Añadir al DOM
            $('body').append($notification);
            
            // Auto-remover después de 5 segundos
            setTimeout(function() {
                $notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        /**
         * Inicializar tooltips
         */
        initTooltips: function() {
            // Usar tooltips nativos de WordPress si están disponibles
            if (typeof wp !== 'undefined' && wp.tooltip) {
                wp.tooltip.init();
            }
        },
        
        /**
         * Inicializar sistema de notificaciones
         */
        initNotifications: function() {
            // Verificar si hay notificaciones pendientes
            if (typeof wvs_admin.notifications !== 'undefined') {
                wvs_admin.notifications.forEach(function(notification) {
                    WVSAdmin.showNotification(notification.message, notification.type);
                });
            }
        },
        
        /**
         * Utilidades
         */
        utils: {
            
            /**
             * Formatear número como moneda
             */
            formatCurrency: function(amount, currency = 'USD') {
                if (currency === 'USD') {
                    return '$' + parseFloat(amount).toFixed(2);
                } else if (currency === 'VES') {
                    return parseFloat(amount).toLocaleString('es-VE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' Bs.';
                }
                return amount;
            },
            
            /**
             * Formatear número con separadores
             */
            formatNumber: function(number) {
                return parseFloat(number).toLocaleString('es-VE');
            },
            
            /**
             * Debounce function
             */
            debounce: function(func, wait, immediate) {
                let timeout;
                return function() {
                    const context = this;
                    const args = arguments;
                    const later = function() {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    const callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func.apply(context, args);
                };
            }
        }
    };
    
    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        WVSAdmin.init();
    });
    
    // Exponer globalmente para uso externo
    window.WVSAdmin = WVSAdmin;
    
})(jQuery);
