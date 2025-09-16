/**
 * JavaScript para la administración del plugin BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Objeto principal del plugin
    var BCVAdmin = {
        
        /**
         * Inicializar el plugin
         */
        init: function() {
            this.bindEvents();
            this.initTooltips();
            this.initDatePickers();
        },
        
        /**
         * Vincular eventos
         */
        bindEvents: function() {
            // Botón de prueba de scraping
            $(document).on('click', '#test-scraping', this.testScraping);
            
            // Formulario de configuración del cron
            $(document).on('submit', '#cron-settings-form', this.saveCronSettings);
            
            // Botones de acción en lote
            $(document).on('change', '#doaction, #doaction2', this.handleBulkAction);
            
            // Filtros de fecha
            $(document).on('change', 'select[name="date_filter"]', this.handleDateFilter);
            
            // Búsqueda en tiempo real
            $(document).on('input', '#bcv-search-prices', this.handleSearch);
            
            // Confirmación de eliminación
            $(document).on('click', '.delete-precio', this.confirmDelete);
            
            // Actualización automática de estadísticas
            this.startAutoRefresh();
        },
        
        /**
         * Probar scraping manual
         */
        testScraping: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $result = $('#test-result');
            
            // Deshabilitar botón y mostrar estado
            $button.prop('disabled', true).text('Probando...');
            $result.html('<span style="color: #0073aa;">🔄 Ejecutando scraping...</span>');
            
            // Realizar request AJAX
            $.ajax({
                url: bcv_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'bcv_test_scraping',
                    nonce: bcv_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var message = '✅ ';
                        var price = null;
                        
                        if (response.data && typeof response.data === 'object') {
                            message += response.data.message || 'Scraping exitoso';
                            price = response.data.price;
                        } else if (typeof response.data === 'string') {
                            message += response.data;
                        } else {
                            message += 'Scraping exitoso';
                        }
                        
                        if (price) {
                            message += ' - Precio: $' + parseFloat(price).toFixed(4);
                        }
                        
                        $result.html('<span style="color: #3c763d;">' + message + '</span>');
                        
                        // Recargar página después de 2 segundos para mostrar nuevos datos
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        var errorMessage = '❌ ';
                        if (response.data && typeof response.data === 'string') {
                            errorMessage += response.data;
                        } else {
                            errorMessage += 'Error desconocido';
                        }
                        $result.html('<span style="color: #a94442;">' + errorMessage + '</span>');
                    }
                },
                error: function() {
                    $result.html('<span style="color: #a94442;">❌ Error de conexión</span>');
                },
                complete: function() {
                    // Rehabilitar botón
                    $button.prop('disabled', false).text('Probar Scraping Manual');
                }
            });
        },
        
        /**
         * Guardar configuración del cron
         */
        saveCronSettings: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitButton = $form.find('input[type="submit"]');
            var originalText = $submitButton.val();
            
            // VALIDAR FORMULARIO ANTES DE CONTINUAR
            var hours = parseInt($form.find('input[name="cron_hours"]').val()) || 0;
            var minutes = parseInt($form.find('input[name="cron_minutes"]').val()) || 0;
            var seconds = parseInt($form.find('input[name="cron_seconds"]').val()) || 0;
            
            // Validar rangos
            if (hours < 0 || hours > 24) {
                BCVAdmin.showNotice('Las horas deben estar entre 0 y 24', 'error');
                return false;
            }
            if (minutes < 0 || minutes > 59) {
                BCVAdmin.showNotice('Los minutos deben estar entre 0 y 59', 'error');
                return false;
            }
            if (seconds < 0 || seconds > 59) {
                BCVAdmin.showNotice('Los segundos deben estar entre 0 y 59', 'error');
                return false;
            }
            
            // Mínimo 1 minuto
            if (hours === 0 && minutes === 0 && seconds < 60) {
                BCVAdmin.showNotice('El intervalo mínimo debe ser de 1 minuto (60 segundos)', 'warning');
                return false;
            }
            
            // Deshabilitar botón y mostrar estado
            $submitButton.prop('disabled', true).val(bcv_ajax.strings.saving);
            
            // Recopilar datos del formulario
            var formData = {
                action: 'bcv_save_cron_settings',
                nonce: bcv_ajax.nonce,
                enabled: $form.find('input[name="cron_enabled"]').is(':checked') ? 1 : 0,
                hours: hours,
                minutes: minutes,
                seconds: seconds
            };
            
            // Realizar request AJAX
            $.ajax({
                url: bcv_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        BCVAdmin.showNotice(bcv_ajax.strings.saved, 'success');
                        
                        // Actualizar información del cron en la página
                        BCVAdmin.updateCronInfo();
                    } else {
                        BCVAdmin.showNotice(response.data, 'error');
                    }
                },
                error: function() {
                    BCVAdmin.showNotice('Error de conexión', 'error');
                },
                complete: function() {
                    // Rehabilitar botón
                    $submitButton.prop('disabled', false).val(originalText);
                }
            });
        },
        
        /**
         * Manejar acciones en lote
         */
        handleBulkAction: function() {
            var action = $(this).val();
            var $form = $(this).closest('form');
            var $checkboxes = $form.find('input[name="precio[]"]:checked');
            
            if (action === 'delete' && $checkboxes.length > 0) {
                if (confirm('¿Estás seguro de que quieres eliminar ' + $checkboxes.length + ' registro(s)?')) {
                    $form.submit();
                } else {
                    $(this).val('');
                }
            }
        },
        
        /**
         * Manejar filtro de fechas
         */
        handleDateFilter: function() {
            var filter = $(this).val();
            var $form = $(this).closest('form');
            
            if (filter) {
                // Añadir parámetro de filtro al formulario
                var $input = $form.find('input[name="date_filter"]');
                if ($input.length === 0) {
                    $form.append('<input type="hidden" name="date_filter" value="' + filter + '">');
                } else {
                    $input.val(filter);
                }
                
                // Enviar formulario
                $form.submit();
            }
        },
        
        /**
         * Manejar búsqueda en tiempo real
         */
        handleSearch: function() {
            var query = $(this).val();
            var $table = $('.wp-list-table');
            
            // Implementar búsqueda en tiempo real si es necesario
            if (query.length > 2) {
                // Filtrar filas de la tabla
                $table.find('tbody tr').each(function() {
                    var $row = $(this);
                    var text = $row.text().toLowerCase();
                    
                    if (text.indexOf(query.toLowerCase()) > -1) {
                        $row.show();
                    } else {
                        $row.hide();
                    }
                });
            } else if (query.length === 0) {
                // Mostrar todas las filas
                $table.find('tbody tr').show();
            }
        },
        
        /**
         * Confirmar eliminación
         */
        confirmDelete: function(e) {
            if (!confirm('¿Estás seguro de que quieres eliminar este registro?')) {
                e.preventDefault();
                return false;
            }
        },
        
        /**
         * Mostrar notificación
         */
        showNotice: function(message, type) {
            var $notice = $('<div class="bcv-notice ' + type + '">' + message + '</div>');
            
            // Insertar después del título principal
            $('.wrap h1').after($notice);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(function() {
                $notice.fadeOut(500, function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        /**
         * Actualizar información del cron
         */
        updateCronInfo: function() {
            // Esta función se puede implementar para actualizar dinámicamente
            // la información del cron sin recargar la página
            console.log('Actualizando información del cron...');
        },
        
        /**
         * Inicializar tooltips
         */
        initTooltips: function() {
            // Comentado temporalmente - jQuery UI no está disponible
            // $('[title]').tooltip({
            //     position: { my: 'left+5 center', at: 'right center' }
            // });
            console.log('Tooltips deshabilitados temporalmente');
        },
        
        /**
         * Inicializar selectores de fecha
         */
        initDatePickers: function() {
            // Inicializar datepickers si están disponibles
            if ($.fn.datepicker) {
                $('input[type="date"]').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true
                });
            }
        },
        
        /**
         * Iniciar actualización automática
         */
        startAutoRefresh: function() {
            // Actualizar estadísticas cada 30 segundos
            setInterval(function() {
                BCVAdmin.refreshStats();
            }, 30000);
        },
        
        /**
         * Actualizar estadísticas
         */
        refreshStats: function() {
            // Esta función se puede implementar para actualizar
            // estadísticas en tiempo real
            console.log('Actualizando estadísticas...');
        },
        
        /**
         * Validar formulario de configuración
         */
        validateCronForm: function() {
            var $form = $('#cron-settings-form');
            var hours = parseInt($form.find('input[name="cron_hours"]').val()) || 0;
            var minutes = parseInt($form.find('input[name="cron_minutes"]').val()) || 0;
            var seconds = parseInt($form.find('input[name="cron_seconds"]').val()) || 0;
            
            // Mínimo 1 minuto
            if (hours === 0 && minutes === 0 && seconds < 60) {
                BCVAdmin.showNotice('El intervalo mínimo debe ser de 1 minuto', 'warning');
                return false;
            }
            
            return true;
        },
        
        /**
         * Formatear intervalo de tiempo
         */
        formatInterval: function(hours, minutes, seconds) {
            var parts = [];
            
            if (hours > 0) {
                parts.push(hours + ' hora' + (hours > 1 ? 's' : ''));
            }
            
            if (minutes > 0) {
                parts.push(minutes + ' minuto' + (minutes > 1 ? 's' : ''));
            }
            
            if (seconds > 0) {
                parts.push(seconds + ' segundo' + (seconds > 1 ? 's' : ''));
            }
            
            return parts.join(', ') || '0 segundos';
        },
        
        /**
         * Exportar datos
         */
        exportData: function(format) {
            var $form = $('<form method="post" action="' + bcv_ajax.ajax_url + '">');
            $form.append('<input type="hidden" name="action" value="bcv_export_data">');
            $form.append('<input type="hidden" name="format" value="' + format + '">');
            $form.append('<input type="hidden" name="nonce" value="' + bcv_ajax.nonce + '">');
            
            // Añadir filtros actuales
            var search = $('#bcv-search-prices').val();
            if (search) {
                $form.append('<input type="hidden" name="search" value="' + search + '">');
            }
            
            $('body').append($form);
            $form.submit();
            $form.remove();
        }
    };
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        BCVAdmin.init();
    });
    
    // Hacer disponible globalmente
    window.BCVAdmin = BCVAdmin;
    
})(jQuery);
