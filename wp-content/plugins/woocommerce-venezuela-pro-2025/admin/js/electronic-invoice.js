/**
 * JavaScript para Facturación Electrónica
 * 
 * @package WooCommerce_Venezuela_Suite_2025
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Función para regenerar factura
    window.wcvsRegenerateInvoice = function(orderId) {
        if (!confirm('¿Está seguro de que desea regenerar la factura? Esto sobrescribirá la factura existente.')) {
            return;
        }

        $.ajax({
            url: wcvs_electronic_invoice.ajax_url,
            type: 'POST',
            data: {
                action: 'wcvs_generate_invoice',
                order_id: orderId,
                nonce: wcvs_electronic_invoice.nonce
            },
            beforeSend: function() {
                // Mostrar indicador de carga
                $('body').append('<div id="wcvs-loading" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 20px; border-radius: 5px;"><span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> ' + wcvs_electronic_invoice.i18n.generating + '</div></div>');
            },
            success: function(response) {
                if (response.success) {
                    alert(wcvs_electronic_invoice.i18n.success);
                    location.reload();
                } else {
                    alert(wcvs_electronic_invoice.i18n.error + ': ' + response.data);
                }
            },
            error: function() {
                alert(wcvs_electronic_invoice.i18n.error);
            },
            complete: function() {
                $('#wcvs-loading').remove();
            }
        });
    };

    // Función para validar RIF
    function validateRIF(rif) {
        // Patrón para RIF: J-12345678-9
        var pattern = /^J-[0-9]{8}-[0-9]$/;
        return pattern.test(rif.toUpperCase());
    }

    // Función para validar Cédula
    function validateCedula(cedula) {
        // Patrón para Cédula: V-12345678
        var pattern = /^V-[0-9]{8}$/;
        return pattern.test(cedula.toUpperCase());
    }

    // Función para formatear RIF
    function formatRIF(input) {
        var value = input.value.replace(/\D/g, '');
        if (value.length > 0) {
            value = 'J-' + value;
        }
        if (value.length > 9) {
            value = value.substring(0, 9) + '-' + value.substring(9);
        }
        input.value = value.toUpperCase();
    }

    // Función para formatear Cédula
    function formatCedula(input) {
        var value = input.value.replace(/\D/g, '');
        if (value.length > 0) {
            value = 'V-' + value;
        }
        input.value = value.toUpperCase();
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
                
                .wcvs-fiscal-fields {
                    background: #f9f9f9;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                
                .wcvs-fiscal-fields h3 {
                    margin-top: 0;
                    color: #0073aa;
                }
                
                .wcvs-fiscal-fields .form-row {
                    margin-bottom: 15px;
                }
                
                .wcvs-fiscal-fields label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: bold;
                }
                
                .wcvs-fiscal-fields input,
                .wcvs-fiscal-fields select,
                .wcvs-fiscal-fields textarea {
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 3px;
                }
                
                .wcvs-fiscal-fields .description {
                    font-size: 12px;
                    color: #666;
                    margin-top: 5px;
                }
                
                .wcvs-validation-error {
                    color: #d63638;
                    font-size: 12px;
                    margin-top: 5px;
                }
                
                .wcvs-validation-success {
                    color: #00a32a;
                    font-size: 12px;
                    margin-top: 5px;
                }
                
                .wcvs-price-info {
                    background: #f0f8ff;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 10px 0;
                }
                
                .wcvs-price-info h4 {
                    margin-top: 0;
                    color: #0073aa;
                }
                
                .wcvs-invoice-actions {
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                }
                
                .wcvs-invoice-actions .button {
                    margin-right: 10px;
                }
            `)
            .appendTo('head');

        // Validación en tiempo real para RIF
        $('input[name="billing_rif"]').on('input', function() {
            var input = $(this);
            var value = input.val();
            
            if (value.length > 0) {
                if (validateRIF(value)) {
                    input.removeClass('error').addClass('valid');
                    input.next('.wcvs-validation-error').remove();
                    input.after('<span class="wcvs-validation-success">✓ RIF válido</span>');
                } else {
                    input.removeClass('valid').addClass('error');
                    input.next('.wcvs-validation-success').remove();
                    input.after('<span class="wcvs-validation-error">✗ Formato inválido. Use: J-12345678-9</span>');
                }
            } else {
                input.removeClass('error valid');
                input.next('.wcvs-validation-error, .wcvs-validation-success').remove();
            }
        });

        // Validación en tiempo real para Cédula
        $('input[name="billing_cedula"]').on('input', function() {
            var input = $(this);
            var value = input.val();
            
            if (value.length > 0) {
                if (validateCedula(value)) {
                    input.removeClass('error').addClass('valid');
                    input.next('.wcvs-validation-error').remove();
                    input.after('<span class="wcvs-validation-success">✓ Cédula válida</span>');
                } else {
                    input.removeClass('valid').addClass('error');
                    input.next('.wcvs-validation-success').remove();
                    input.after('<span class="wcvs-validation-error">✗ Formato inválido. Use: V-12345678</span>');
                }
            } else {
                input.removeClass('error valid');
                input.next('.wcvs-validation-error, .wcvs-validation-success').remove();
            }
        });

        // Formateo automático para RIF
        $('input[name="billing_rif"]').on('input', function() {
            formatRIF(this);
        });

        // Formateo automático para Cédula
        $('input[name="billing_cedula"]').on('input', function() {
            formatCedula(this);
        });

        // Validación del formulario antes del envío
        $('form.checkout').on('checkout_place_order', function() {
            var tipoCliente = $('select[name="billing_tipo_cliente"]').val();
            var rif = $('input[name="billing_rif"]').val();
            var cedula = $('input[name="billing_cedula"]').val();
            var nombreCompleto = $('input[name="billing_nombre_completo"]').val();
            
            var errors = [];
            
            if (tipoCliente === 'persona_natural' || tipoCliente === 'persona_juridica') {
                if (tipoCliente === 'persona_natural') {
                    if (!cedula) {
                        errors.push('La Cédula de Identidad es obligatoria para Persona Natural.');
                    } else if (!validateCedula(cedula)) {
                        errors.push('El formato de la Cédula no es válido.');
                    }
                } else if (tipoCliente === 'persona_juridica') {
                    if (!rif) {
                        errors.push('El RIF es obligatorio para Persona Jurídica.');
                    } else if (!validateRIF(rif)) {
                        errors.push('El formato del RIF no es válido.');
                    }
                }
                
                if (!nombreCompleto) {
                    errors.push('El Nombre Completo es obligatorio para la facturación.');
                }
            }
            
            if (errors.length > 0) {
                alert('Errores en los datos fiscales:\n\n' + errors.join('\n'));
                return false;
            }
        });

        // Mostrar/ocultar campos según tipo de cliente
        $('select[name="billing_tipo_cliente"]').on('change', function() {
            var tipoCliente = $(this).val();
            var rifField = $('input[name="billing_rif"]').closest('.form-row');
            var cedulaField = $('input[name="billing_cedula"]').closest('.form-row');
            
            if (tipoCliente === 'persona_natural') {
                cedulaField.show();
                rifField.hide();
            } else if (tipoCliente === 'persona_juridica') {
                rifField.show();
                cedulaField.hide();
            } else {
                rifField.hide();
                cedulaField.hide();
            }
        });

        // Inicializar visibilidad de campos
        $('select[name="billing_tipo_cliente"]').trigger('change');
    });

})(jQuery);
