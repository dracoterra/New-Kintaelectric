/**
 * JavaScript para Facturación Electrónica - WooCommerce Venezuela Suite
 */

(function($) {
    'use strict';

    // Objeto principal
    var WCVSElectronicBilling = {
        
        // Inicialización
        init: function() {
            this.bindEvents();
            this.initValidation();
            this.initRIFValidation();
        },

        // Vincular eventos
        bindEvents: function() {
            // Validación de RIF en tiempo real
            $(document).on('input', '#billing_rif', this.validateRIF);
            
            // Generación de factura
            $(document).on('click', '.wcvs-generate-invoice', this.generateInvoice);
            
            // Descarga de factura
            $(document).on('click', '.wcvs-download-invoice', this.downloadInvoice);
            
            // Filtros de facturas
            $(document).on('change', '.wcvs-filter-input', this.filterInvoices);
            
            // Búsqueda de facturas
            $(document).on('input', '.wcvs-search-input', this.searchInvoices);
        },

        // Inicializar validación
        initValidation: function() {
            if (typeof wc_checkout_params !== 'undefined') {
                // Validación personalizada para WooCommerce
                $(document.body).on('checkout_error', this.handleCheckoutError);
            }
        },

        // Inicializar validación de RIF
        initRIFValidation: function() {
            // Validar RIF existente si hay uno
            var rifField = $('#billing_rif');
            if (rifField.length && rifField.val()) {
                this.validateRIF();
            }
        },

        // Validar RIF
        validateRIF: function() {
            var rif = $(this).val().trim().toUpperCase();
            var field = $(this);
            var errorContainer = field.siblings('.wcvs-rif-error');
            
            // Remover mensaje de error anterior
            errorContainer.remove();
            
            if (rif === '') {
                return;
            }
            
            // Patrón para RIF venezolano
            var rifPattern = /^[VEGJ]-[0-9]{8}-[0-9]$/;
            
            if (!rifPattern.test(rif)) {
                field.addClass('wcvs-invalid');
                field.after('<div class="wcvs-rif-error" style="color: #e74c3c; font-size: 12px; margin-top: 5px;">Formato de RIF inválido. Use: X-XXXXXXXX-X</div>');
                return false;
            }
            
            // Validar dígito verificador
            if (!WCVSElectronicBilling.validateRIFChecksum(rif)) {
                field.addClass('wcvs-invalid');
                field.after('<div class="wcvs-rif-error" style="color: #e74c3c; font-size: 12px; margin-top: 5px;">RIF inválido. Verifique el dígito verificador.</div>');
                return false;
            }
            
            field.removeClass('wcvs-invalid');
            field.addClass('wcvs-valid');
            return true;
        },

        // Validar checksum del RIF
        validateRIFChecksum: function(rif) {
            // Extraer números del RIF
            var numbers = rif.replace(/[VEGJ-]/g, '');
            
            if (numbers.length !== 9) {
                return false;
            }
            
            // Calcular checksum
            var sum = 0;
            var multipliers = [4, 3, 2, 7, 6, 5, 4, 3, 2];
            
            for (var i = 0; i < 8; i++) {
                sum += parseInt(numbers[i]) * multipliers[i];
            }
            
            var remainder = sum % 11;
            var checkDigit = remainder < 2 ? remainder : 11 - remainder;
            
            return checkDigit === parseInt(numbers[8]);
        },

        // Manejar error de checkout
        handleCheckoutError: function() {
            // Validar RIF si hay error
            var rifField = $('#billing_rif');
            if (rifField.length && rifField.val()) {
                WCVSElectronicBilling.validateRIF.call(rifField[0]);
            }
        },

        // Generar factura
        generateInvoice: function(e) {
            e.preventDefault();
            
            var button = $(this);
            var orderId = button.data('order-id');
            var originalText = button.text();
            
            // Mostrar estado de carga
            button.text('Generando...').prop('disabled', true);
            
            // Realizar petición AJAX
            $.ajax({
                url: wcvs_electronic_billing.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_generate_invoice',
                    nonce: wcvs_electronic_billing.nonce,
                    order_id: orderId
                },
                success: function(response) {
                    if (response.success) {
                        WCVSElectronicBilling.showMessage('success', response.data.message);
                        
                        // Actualizar interfaz si es necesario
                        if (response.data.invoice_number) {
                            WCVSElectronicBilling.updateInvoiceDisplay(response.data.invoice_number);
                        }
                    } else {
                        WCVSElectronicBilling.showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    WCVSElectronicBilling.showMessage('error', wcvs_electronic_billing.strings.invoice_error);
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
            var format = button.data('format') || 'pdf';
            
            // Crear URL de descarga
            var downloadUrl = wcvs_electronic_billing.ajax_url + '?action=wcvs_download_invoice&order_id=' + orderId + '&format=' + format;
            
            // Abrir en nueva ventana para descarga
            window.open(downloadUrl, '_blank');
        },

        // Filtrar facturas
        filterInvoices: function() {
            var filterType = $(this).data('filter-type');
            var filterValue = $(this).val();
            var table = $('.wcvs-invoices-list table');
            
            table.find('tbody tr').each(function() {
                var row = $(this);
                var cellValue = row.find('td[data-filter="' + filterType + '"]').text().toLowerCase();
                
                if (filterValue === '' || cellValue.includes(filterValue.toLowerCase())) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        },

        // Buscar facturas
        searchInvoices: function() {
            var searchTerm = $(this).val().toLowerCase();
            var table = $('.wcvs-invoices-list table');
            
            table.find('tbody tr').each(function() {
                var row = $(this);
                var rowText = row.text().toLowerCase();
                
                if (rowText.includes(searchTerm)) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        },

        // Mostrar mensaje
        showMessage: function(type, message) {
            var messageHtml = '<div class="wcvs-status-message ' + type + ' wcvs-fade-in">' + message + '</div>';
            
            // Remover mensajes anteriores
            $('.wcvs-status-message').remove();
            
            // Añadir nuevo mensaje
            $('.wcvs-electronic-billing-admin').prepend(messageHtml);
            
            // Auto-remover después de 5 segundos
            setTimeout(function() {
                $('.wcvs-status-message').fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        // Actualizar visualización de factura
        updateInvoiceDisplay: function(invoiceNumber) {
            // Actualizar número de factura si existe el elemento
            var invoiceNumberElement = $('.wcvs-invoice-number');
            if (invoiceNumberElement.length) {
                invoiceNumberElement.text(invoiceNumber);
            }
            
            // Actualizar estado si existe el elemento
            var invoiceStatusElement = $('.wcvs-invoice-status');
            if (invoiceStatusElement.length) {
                invoiceStatusElement.text('Generada').removeClass('pending').addClass('approved');
            }
        },

        // Validar formulario de facturación
        validateBillingForm: function() {
            var isValid = true;
            var rifField = $('#billing_rif');
            
            // Validar RIF
            if (rifField.length && rifField.val()) {
                if (!this.validateRIF.call(rifField[0])) {
                    isValid = false;
                }
            }
            
            return isValid;
        },

        // Formatear RIF automáticamente
        formatRIF: function(input) {
            var value = input.value.replace(/[^VEGJ0-9]/g, '').toUpperCase();
            
            if (value.length > 0) {
                // Añadir guión después del primer carácter
                if (value.length > 1 && value[1] !== '-') {
                    value = value[0] + '-' + value.substring(1);
                }
                
                // Añadir guión antes del último carácter
                if (value.length > 10 && value[value.length - 2] !== '-') {
                    value = value.substring(0, value.length - 1) + '-' + value[value.length - 1];
                }
            }
            
            input.value = value;
        },

        // Inicializar formateo automático de RIF
        initRIFFormatting: function() {
            $(document).on('input', '#billing_rif', function() {
                WCVSElectronicBilling.formatRIF(this);
            });
        },

        // Cargar más facturas (paginación)
        loadMoreInvoices: function() {
            var button = $('.wcvs-load-more');
            var page = parseInt(button.data('page')) || 1;
            var nextPage = page + 1;
            
            button.text('Cargando...').prop('disabled', true);
            
            $.ajax({
                url: wcvs_electronic_billing.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_load_more_invoices',
                    nonce: wcvs_electronic_billing.nonce,
                    page: nextPage
                },
                success: function(response) {
                    if (response.success && response.data.html) {
                        $('.wcvs-invoices-list tbody').append(response.data.html);
                        button.data('page', nextPage);
                        
                        if (!response.data.has_more) {
                            button.hide();
                        }
                    }
                },
                complete: function() {
                    button.text('Cargar más').prop('disabled', false);
                }
            });
        },

        // Exportar facturas
        exportInvoices: function(format) {
            var selectedInvoices = $('.wcvs-invoice-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selectedInvoices.length === 0) {
                this.showMessage('warning', 'Seleccione al menos una factura para exportar.');
                return;
            }
            
            // Crear formulario para exportación
            var form = $('<form>', {
                method: 'POST',
                action: wcvs_electronic_billing.ajax_url,
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
                value: wcvs_electronic_billing.nonce
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
        }
    };

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        WCVSElectronicBilling.init();
        WCVSElectronicBilling.initRIFFormatting();
    });

    // Hacer disponible globalmente
    window.WCVSElectronicBilling = WCVSElectronicBilling;

})(jQuery);
