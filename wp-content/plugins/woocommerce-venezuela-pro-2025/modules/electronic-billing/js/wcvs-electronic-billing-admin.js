/**
 * JavaScript para Administración de Facturación Electrónica - WooCommerce Venezuela Suite
 */

(function($) {
    'use strict';

    // Objeto principal para admin
    var WCVSElectronicBillingAdmin = {
        
        // Inicialización
        init: function() {
            this.bindEvents();
            this.initDashboard();
            this.initInvoicesList();
        },

        // Vincular eventos
        bindEvents: function() {
            // Generación de factura desde admin
            $(document).on('click', '#wcvs-generate-invoice-button', this.generateInvoice);
            
            // Descarga de factura
            $(document).on('click', '.wcvs-download-invoice', this.downloadInvoice);
            
            // Envío a SENIAT
            $(document).on('click', '.wcvs-send-to-seniat', this.sendToSENIAT);
            
            // Filtros de facturas
            $(document).on('change', '.wcvs-filter-select', this.filterInvoices);
            
            // Búsqueda de facturas
            $(document).on('input', '.wcvs-search-input', this.searchInvoices);
            
            // Selección masiva
            $(document).on('change', '#wcvs-select-all', this.toggleSelectAll);
            
            // Exportación masiva
            $(document).on('click', '.wcvs-export-button', this.exportInvoices);
            
            // Configuración
            $(document).on('click', '.wcvs-save-config', this.saveConfiguration);
            
            // Prueba de conexión SENIAT
            $(document).on('click', '#wcvs-test-seniat', this.testSENIATConnection);
        },

        // Inicializar dashboard
        initDashboard: function() {
            this.loadDashboardStats();
            this.initCharts();
        },

        // Inicializar lista de facturas
        initInvoicesList: function() {
            this.loadInvoicesList();
            this.initPagination();
        },

        // Cargar estadísticas del dashboard
        loadDashboardStats: function() {
            $.ajax({
                url: wcvs_electronic_billing_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_dashboard_stats',
                    nonce: wcvs_electronic_billing_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WCVSElectronicBillingAdmin.updateDashboardStats(response.data);
                    }
                }
            });
        },

        // Actualizar estadísticas del dashboard
        updateDashboardStats: function(stats) {
            $('.wcvs-stat-total-invoices .wcvs-stat-number').text(stats.total_invoices || 0);
            $('.wcvs-stat-pending-invoices .wcvs-stat-number').text(stats.pending_invoices || 0);
            $('.wcvs-stat-approved-invoices .wcvs-stat-number').text(stats.approved_invoices || 0);
            $('.wcvs-stat-seniat-sent .wcvs-stat-number').text(stats.seniat_sent || 0);
        },

        // Inicializar gráficos
        initCharts: function() {
            // Cargar datos para gráficos
            $.ajax({
                url: wcvs_electronic_billing_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_chart_data',
                    nonce: wcvs_electronic_billing_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WCVSElectronicBillingAdmin.createCharts(response.data);
                    }
                }
            });
        },

        // Crear gráficos
        createCharts: function(data) {
            // Gráfico de facturas por mes
            if (data.invoices_by_month && typeof Chart !== 'undefined') {
                var ctx = document.getElementById('wcvs-invoices-chart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.invoices_by_month.labels,
                            datasets: [{
                                label: 'Facturas Generadas',
                                data: data.invoices_by_month.data,
                                borderColor: '#0073aa',
                                backgroundColor: 'rgba(0, 115, 170, 0.1)',
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }
        },

        // Cargar lista de facturas
        loadInvoicesList: function() {
            var filters = this.getCurrentFilters();
            
            $.ajax({
                url: wcvs_electronic_billing_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_invoices_list',
                    nonce: wcvs_electronic_billing_admin.nonce,
                    filters: filters
                },
                success: function(response) {
                    if (response.success) {
                        WCVSElectronicBillingAdmin.updateInvoicesList(response.data);
                    }
                }
            });
        },

        // Actualizar lista de facturas
        updateInvoicesList: function(data) {
            var tbody = $('.wcvs-invoices-list tbody');
            tbody.empty();
            
            if (data.invoices && data.invoices.length > 0) {
                data.invoices.forEach(function(invoice) {
                    var row = WCVSElectronicBillingAdmin.createInvoiceRow(invoice);
                    tbody.append(row);
                });
            } else {
                tbody.append('<tr><td colspan="7" class="text-center">No se encontraron facturas</td></tr>');
            }
            
            // Actualizar paginación
            WCVSElectronicBillingAdmin.updatePagination(data.pagination);
        },

        // Crear fila de factura
        createInvoiceRow: function(invoice) {
            var statusClass = 'wcvs-invoice-status-' + invoice.status;
            var statusText = WCVSElectronicBillingAdmin.getStatusText(invoice.status);
            
            var row = $('<tr>');
            row.append('<td><input type="checkbox" class="wcvs-invoice-checkbox" value="' + invoice.order_id + '"></td>');
            row.append('<td>' + invoice.invoice_number + '</td>');
            row.append('<td>' + invoice.customer_name + '</td>');
            row.append('<td>' + invoice.total + '</td>');
            row.append('<td><span class="wcvs-invoice-status ' + statusClass + '">' + statusText + '</span></td>');
            row.append('<td>' + invoice.generated_at + '</td>');
            
            var actions = '<div class="wcvs-invoice-actions">';
            actions += '<button class="button button-small wcvs-download-invoice" data-order-id="' + invoice.order_id + '" data-format="pdf">PDF</button>';
            actions += '<button class="button button-small wcvs-download-invoice" data-order-id="' + invoice.order_id + '" data-format="xml">XML</button>';
            
            if (invoice.status === 'approved') {
                actions += '<button class="button button-small wcvs-send-to-seniat" data-order-id="' + invoice.order_id + '">SENIAT</button>';
            }
            
            actions += '</div>';
            row.append('<td>' + actions + '</td>');
            
            return row;
        },

        // Obtener texto del estado
        getStatusText: function(status) {
            var statusTexts = {
                'draft': 'Borrador',
                'pending': 'Pendiente',
                'approved': 'Aprobada',
                'rejected': 'Rechazada',
                'cancelled': 'Cancelada'
            };
            
            return statusTexts[status] || status;
        },

        // Obtener filtros actuales
        getCurrentFilters: function() {
            return {
                status: $('.wcvs-filter-status').val(),
                date_from: $('.wcvs-filter-date-from').val(),
                date_to: $('.wcvs-filter-date-to').val(),
                search: $('.wcvs-search-input').val()
            };
        },

        // Actualizar paginación
        updatePagination: function(pagination) {
            var paginationContainer = $('.wcvs-pagination');
            paginationContainer.empty();
            
            if (pagination.total_pages > 1) {
                for (var i = 1; i <= pagination.total_pages; i++) {
                    var pageClass = i === pagination.current_page ? 'current' : '';
                    var pageButton = $('<button>', {
                        class: 'button ' + pageClass,
                        text: i,
                        'data-page': i
                    });
                    
                    if (i !== pagination.current_page) {
                        pageButton.on('click', function() {
                            WCVSElectronicBillingAdmin.loadPage($(this).data('page'));
                        });
                    }
                    
                    paginationContainer.append(pageButton);
                }
            }
        },

        // Cargar página específica
        loadPage: function(page) {
            $('.wcvs-pagination').data('current-page', page);
            this.loadInvoicesList();
        },

        // Inicializar paginación
        initPagination: function() {
            $(document).on('click', '.wcvs-pagination button', function() {
                var page = $(this).data('page');
                WCVSElectronicBillingAdmin.loadPage(page);
            });
        },

        // Generar factura
        generateInvoice: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var orderId = button.data('order-id');
            var originalText = button.text();
            
            button.text('Generando...').prop('disabled', true);
            
            $.ajax({
                url: wcvs_electronic_billing_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_generate_invoice',
                    nonce: wcvs_electronic_billing_admin.nonce,
                    order_id: orderId
                },
                success: function(response) {
                    if (response.success) {
                        WCVSElectronicBillingAdmin.showMessage('success', response.data.message);
                        WCVSElectronicBillingAdmin.loadInvoicesList();
                    } else {
                        WCVSElectronicBillingAdmin.showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    WCVSElectronicBillingAdmin.showMessage('error', 'Error al generar la factura');
                },
                complete: function() {
                    button.text(originalText).prop('disabled', false);
                }
            });
        },

        // Descargar factura
        downloadInvoice: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var orderId = button.data('order-id');
            var format = button.data('format');
            
            var downloadUrl = wcvs_electronic_billing_admin.ajax_url + '?action=wcvs_download_invoice&order_id=' + orderId + '&format=' + format;
            window.open(downloadUrl, '_blank');
        },

        // Enviar a SENIAT
        sendToSENIAT: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var orderId = button.data('order-id');
            var originalText = button.text();
            
            button.text('Enviando...').prop('disabled', true);
            
            $.ajax({
                url: wcvs_electronic_billing_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_send_to_seniat',
                    nonce: wcvs_electronic_billing_admin.nonce,
                    order_id: orderId
                },
                success: function(response) {
                    if (response.success) {
                        WCVSElectronicBillingAdmin.showMessage('success', response.data.message);
                        WCVSElectronicBillingAdmin.loadInvoicesList();
                    } else {
                        WCVSElectronicBillingAdmin.showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    WCVSElectronicBillingAdmin.showMessage('error', 'Error al enviar a SENIAT');
                },
                complete: function() {
                    button.text(originalText).prop('disabled', false);
                }
            });
        },

        // Filtrar facturas
        filterInvoices: function() {
            WCVSElectronicBillingAdmin.loadInvoicesList();
        },

        // Buscar facturas
        searchInvoices: function() {
            clearTimeout(WCVSElectronicBillingAdmin.searchTimeout);
            WCVSElectronicBillingAdmin.searchTimeout = setTimeout(function() {
                WCVSElectronicBillingAdmin.loadInvoicesList();
            }, 500);
        },

        // Alternar selección masiva
        toggleSelectAll: function() {
            var isChecked = $(this).is(':checked');
            $('.wcvs-invoice-checkbox').prop('checked', isChecked);
        },

        // Exportar facturas
        exportInvoices: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var format = button.data('format');
            var selectedInvoices = $('.wcvs-invoice-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selectedInvoices.length === 0) {
                WCVSElectronicBillingAdmin.showMessage('warning', 'Seleccione al menos una factura para exportar.');
                return;
            }
            
            var form = $('<form>', {
                method: 'POST',
                action: wcvs_electronic_billing_admin.ajax_url,
                target: '_blank'
            });
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'action',
                value: 'wcvs_export_invoices'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'nonce',
                value: wcvs_electronic_billing_admin.nonce
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'format',
                value: format
            }));
            
            selectedInvoices.forEach(function(invoiceId) {
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'invoice_ids[]',
                    value: invoiceId
                }));
            });
            
            $('body').append(form);
            form.submit();
            form.remove();
        },

        // Guardar configuración
        saveConfiguration: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var originalText = button.text();
            var form = button.closest('form');
            var formData = form.serialize();
            
            button.text('Guardando...').prop('disabled', true);
            
            $.ajax({
                url: wcvs_electronic_billing_admin.ajax_url,
                type: 'POST',
                data: formData + '&action=wcvs_save_configuration&nonce=' + wcvs_electronic_billing_admin.nonce,
                success: function(response) {
                    if (response.success) {
                        WCVSElectronicBillingAdmin.showMessage('success', 'Configuración guardada correctamente');
                    } else {
                        WCVSElectronicBillingAdmin.showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    WCVSElectronicBillingAdmin.showMessage('error', 'Error al guardar la configuración');
                },
                complete: function() {
                    button.text(originalText).prop('disabled', false);
                }
            });
        },

        // Probar conexión SENIAT
        testSENIATConnection: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var originalText = button.text();
            
            button.text('Probando...').prop('disabled', true);
            
            $.ajax({
                url: wcvs_electronic_billing_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_test_seniat_connection',
                    nonce: wcvs_electronic_billing_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WCVSElectronicBillingAdmin.showMessage('success', response.data.message);
                    } else {
                        WCVSElectronicBillingAdmin.showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    WCVSElectronicBillingAdmin.showMessage('error', 'Error al probar la conexión');
                },
                complete: function() {
                    button.text(originalText).prop('disabled', false);
                }
            });
        },

        // Mostrar mensaje
        showMessage: function(type, message) {
            var messageHtml = '<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>';
            
            // Remover mensajes anteriores
            $('.notice').remove();
            
            // Añadir nuevo mensaje
            $('.wrap h1').after(messageHtml);
            
            // Auto-remover después de 5 segundos
            setTimeout(function() {
                $('.notice').fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        WCVSElectronicBillingAdmin.init();
    });

    // Hacer disponible globalmente
    window.WCVSElectronicBillingAdmin = WCVSElectronicBillingAdmin;

})(jQuery);
