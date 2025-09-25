/**
 * JavaScript para el módulo de onboarding - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Objeto principal del módulo de onboarding
    window.WCVS_Onboarding = {
        
        // Configuración
        config: {
            ajaxUrl: wcvs_onboarding_ajax.ajax_url,
            nonce: wcvs_onboarding_ajax.nonce,
            currentStep: 'welcome',
            totalSteps: 8,
            completedSteps: []
        },

        // Estado del módulo
        state: {
            isLoading: false,
            currentStepData: {},
            allStepsData: {}
        },

        // Inicializar el módulo
        init: function() {
            this.bindEvents();
            this.initializeStep();
            this.updateProgress();
        },

        // Vincular eventos
        bindEvents: function() {
            var self = this;

            // Navegación
            $(document).on('click', '.wcvs-btn-next', function(e) {
                e.preventDefault();
                self.nextStep();
            });

            $(document).on('click', '.wcvs-btn-prev', function(e) {
                e.preventDefault();
                self.prevStep();
            });

            $(document).on('click', '.wcvs-btn-complete', function(e) {
                e.preventDefault();
                self.completeOnboarding();
            });

            $(document).on('click', '.wcvs-btn-skip', function(e) {
                e.preventDefault();
                self.skipOnboarding();
            });

            // Omitir onboarding desde el aviso
            $(document).on('click', '.wcvs-skip-onboarding', function(e) {
                e.preventDefault();
                self.skipOnboardingFromNotice();
            });

            // Cambios en formularios
            $(document).on('change', '.wcvs-onboarding-form input, .wcvs-onboarding-form select', function() {
                self.saveStepData();
            });

            // Teclado
            $(document).on('keydown', function(e) {
                if (e.key === 'Enter' && !self.state.isLoading) {
                    if ($('.wcvs-btn-next').is(':visible')) {
                        self.nextStep();
                    } else if ($('.wcvs-btn-complete').is(':visible')) {
                        self.completeOnboarding();
                    }
                }
            });
        },

        // Inicializar paso actual
        initializeStep: function() {
            var currentStep = this.getCurrentStepFromUrl();
            this.config.currentStep = currentStep;
            this.loadStepData(currentStep);
        },

        // Obtener paso actual desde URL
        getCurrentStepFromUrl: function() {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('step') || 'welcome';
        },

        // Cargar datos del paso
        loadStepData: function(step) {
            var stepData = this.state.allStepsData[step] || {};
            this.state.currentStepData = stepData;
            this.populateStepForm(step, stepData);
        },

        // Poblar formulario del paso
        populateStepForm: function(step, data) {
            var $form = $('.wcvs-onboarding-form[data-step="' + step + '"]');
            if ($form.length === 0) return;

            // Poblar campos del formulario
            $.each(data, function(key, value) {
                var $field = $form.find('[name="' + key + '"]');
                if ($field.length > 0) {
                    if ($field.is(':checkbox')) {
                        $field.prop('checked', value);
                    } else if ($field.is('select')) {
                        $field.val(value);
                    } else {
                        $field.val(value);
                    }
                }
            });
        },

        // Guardar datos del paso
        saveStepData: function() {
            var $form = $('.wcvs-onboarding-form[data-step="' + this.config.currentStep + '"]');
            if ($form.length === 0) return;

            var formData = {};
            $form.find('input, select').each(function() {
                var $field = $(this);
                var name = $field.attr('name');
                var value;

                if ($field.is(':checkbox')) {
                    value = $field.is(':checked');
                } else if ($field.is('select')) {
                    value = $field.val();
                } else {
                    value = $field.val();
                }

                if (name) {
                    if (name.endsWith('[]')) {
                        // Campo de array (como payment_methods[])
                        var arrayName = name.replace('[]', '');
                        if (!formData[arrayName]) {
                            formData[arrayName] = [];
                        }
                        if (value) {
                            formData[arrayName].push(value);
                        }
                    } else {
                        formData[name] = value;
                    }
                }
            });

            this.state.allStepsData[this.config.currentStep] = formData;
        },

        // Siguiente paso
        nextStep: function() {
            if (this.state.isLoading) return;

            this.saveStepData();
            
            if (!this.validateCurrentStep()) {
                return;
            }

            this.processCurrentStep();
        },

        // Paso anterior
        prevStep: function() {
            if (this.state.isLoading) return;

            var steps = ['welcome', 'currency', 'payment', 'shipping', 'taxes', 'billing', 'notifications', 'complete'];
            var currentIndex = steps.indexOf(this.config.currentStep);
            
            if (currentIndex > 0) {
                var prevStep = steps[currentIndex - 1];
                this.navigateToStep(prevStep);
            }
        },

        // Validar paso actual
        validateCurrentStep: function() {
            var $form = $('.wcvs-onboarding-form[data-step="' + this.config.currentStep + '"]');
            if ($form.length === 0) return true;

            var isValid = true;
            var errors = [];

            // Validar campos requeridos
            $form.find('[required]').each(function() {
                var $field = $(this);
                var value = $field.val();
                
                if (!value || value.trim() === '') {
                    isValid = false;
                    errors.push($field.attr('name') + ' es requerido');
                    $field.addClass('error');
                } else {
                    $field.removeClass('error');
                }
            });

            // Validaciones específicas por paso
            switch (this.config.currentStep) {
                case 'payment':
                    if (!$form.find('input[name="payment_methods[]"]:checked').length) {
                        isValid = false;
                        errors.push('Debes seleccionar al menos un método de pago');
                    }
                    break;
                case 'shipping':
                    if (!$form.find('input[name="shipping_methods[]"]:checked').length) {
                        isValid = false;
                        errors.push('Debes seleccionar al menos un método de envío');
                    }
                    break;
            }

            if (!isValid) {
                this.showError('Por favor corrige los siguientes errores: ' + errors.join(', '));
            }

            return isValid;
        },

        // Procesar paso actual
        processCurrentStep: function() {
            var self = this;
            
            this.state.isLoading = true;
            this.showLoading();

            var stepData = this.state.allStepsData[this.config.currentStep] || {};

            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcvs_process_onboarding',
                    nonce: self.config.nonce,
                    step: self.config.currentStep,
                    data: stepData
                },
                success: function(response) {
                    if (response.success) {
                        self.config.completedSteps.push(self.config.currentStep);
                        self.navigateToNextStep();
                        self.showSuccess(response.data.message || 'Paso completado correctamente');
                    } else {
                        self.showError(response.data.message || 'Error al procesar el paso');
                    }
                },
                error: function(xhr, status, error) {
                    self.showError('Error de conexión: ' + error);
                },
                complete: function() {
                    self.state.isLoading = false;
                    self.hideLoading();
                }
            });
        },

        // Navegar al siguiente paso
        navigateToNextStep: function() {
            var steps = ['welcome', 'currency', 'payment', 'shipping', 'taxes', 'billing', 'notifications', 'complete'];
            var currentIndex = steps.indexOf(this.config.currentStep);
            
            if (currentIndex < steps.length - 1) {
                var nextStep = steps[currentIndex + 1];
                this.navigateToStep(nextStep);
            }
        },

        // Navegar a un paso específico
        navigateToStep: function(step) {
            this.config.currentStep = step;
            this.loadStepData(step);
            this.updateProgress();
            this.updateUrl(step);
        },

        // Actualizar URL
        updateUrl: function(step) {
            var url = new URL(window.location);
            url.searchParams.set('step', step);
            window.history.pushState({}, '', url);
        },

        // Completar onboarding
        completeOnboarding: function() {
            var self = this;
            
            if (this.state.isLoading) return;

            this.state.isLoading = true;
            this.showLoading();

            // Guardar datos del paso actual
            this.saveStepData();

            // Procesar paso de finalización
            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcvs_process_onboarding',
                    nonce: self.config.nonce,
                    step: 'complete',
                    data: self.state.allStepsData
                },
                success: function(response) {
                    if (response.success) {
                        self.showSuccess('¡Configuración completada exitosamente!');
                        self.updateCompletionSummary();
                        
                        // Redirigir después de 2 segundos
                        setTimeout(function() {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            }
                        }, 2000);
                    } else {
                        self.showError(response.data.message || 'Error al completar la configuración');
                    }
                },
                error: function(xhr, status, error) {
                    self.showError('Error de conexión: ' + error);
                },
                complete: function() {
                    self.state.isLoading = false;
                    self.hideLoading();
                }
            });
        },

        // Omitir onboarding
        skipOnboarding: function() {
            var self = this;
            
            if (!confirm('¿Estás seguro de que quieres omitir la configuración? Podrás configurar el plugin manualmente más tarde.')) {
                return;
            }

            this.state.isLoading = true;
            this.showLoading();

            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcvs_skip_onboarding',
                    nonce: self.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showSuccess('Configuración omitida. Puedes configurar el plugin manualmente desde el panel de administración.');
                        
                        // Redirigir después de 2 segundos
                        setTimeout(function() {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            }
                        }, 2000);
                    } else {
                        self.showError(response.data.message || 'Error al omitir la configuración');
                    }
                },
                error: function(xhr, status, error) {
                    self.showError('Error de conexión: ' + error);
                },
                complete: function() {
                    self.state.isLoading = false;
                    self.hideLoading();
                }
            });
        },

        // Omitir onboarding desde el aviso
        skipOnboardingFromNotice: function() {
            var self = this;
            
            if (!confirm('¿Estás seguro de que quieres omitir la configuración inicial? Podrás configurar el plugin manualmente más tarde.')) {
                return;
            }

            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcvs_skip_onboarding',
                    nonce: self.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.wcvs-onboarding-notice').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Error al omitir la configuración: ' + (response.data.message || 'Error desconocido'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error de conexión: ' + error);
                }
            });
        },

        // Actualizar progreso
        updateProgress: function() {
            var steps = ['welcome', 'currency', 'payment', 'shipping', 'taxes', 'billing', 'notifications', 'complete'];
            var currentIndex = steps.indexOf(this.config.currentStep);
            var progressPercentage = ((currentIndex + 1) / steps.length) * 100;
            
            $('.wcvs-progress-fill').css('width', progressPercentage + '%');
            
            // Actualizar clases de pasos
            $('.wcvs-progress-step').each(function(index) {
                var $step = $(this);
                $step.removeClass('current completed');
                
                if (index < currentIndex) {
                    $step.addClass('completed');
                } else if (index === currentIndex) {
                    $step.addClass('current');
                }
            });
        },

        // Actualizar resumen de finalización
        updateCompletionSummary: function() {
            var $list = $('#wcvs-completion-list');
            if ($list.length === 0) return;

            var summary = [];
            
            // Moneda
            if (this.state.allStepsData.currency && this.state.allStepsData.currency.currency_enabled) {
                summary.push('Conversión automática USD a VES configurada');
            }
            
            // Pagos
            if (this.state.allStepsData.payment && this.state.allStepsData.payment.payment_methods) {
                var paymentMethods = this.state.allStepsData.payment.payment_methods;
                if (paymentMethods.length > 0) {
                    summary.push(paymentMethods.length + ' pasarela(s) de pago configurada(s)');
                }
            }
            
            // Envíos
            if (this.state.allStepsData.shipping && this.state.allStepsData.shipping.shipping_methods) {
                var shippingMethods = this.state.allStepsData.shipping.shipping_methods;
                if (shippingMethods.length > 0) {
                    summary.push(shippingMethods.length + ' método(s) de envío configurado(s)');
                }
            }
            
            // Impuestos
            if (this.state.allStepsData.taxes) {
                var taxes = [];
                if (this.state.allStepsData.taxes.iva_enabled) taxes.push('IVA');
                if (this.state.allStepsData.taxes.igtf_enabled) taxes.push('IGTF');
                if (this.state.allStepsData.taxes.islr_enabled) taxes.push('ISLR');
                if (taxes.length > 0) {
                    summary.push('Sistema fiscal configurado: ' + taxes.join(', '));
                }
            }
            
            // Facturación
            if (this.state.allStepsData.billing && this.state.allStepsData.billing.billing_enabled) {
                summary.push('Facturación electrónica habilitada');
            }
            
            // Notificaciones
            if (this.state.allStepsData.notifications) {
                var notifications = [];
                if (this.state.allStepsData.notifications.email_notifications) notifications.push('Email');
                if (this.state.allStepsData.notifications.sms_notifications) notifications.push('SMS');
                if (this.state.allStepsData.notifications.push_notifications) notifications.push('Push');
                if (notifications.length > 0) {
                    summary.push('Notificaciones configuradas: ' + notifications.join(', '));
                }
            }
            
            // Generar HTML
            var html = '';
            summary.forEach(function(item) {
                html += '<li>' + item + '</li>';
            });
            
            $list.html(html);
        },

        // Mostrar loading
        showLoading: function() {
            $('.wcvs-onboarding-content').html('<div class="wcvs-loading">Procesando configuración...</div>');
        },

        // Ocultar loading
        hideLoading: function() {
            $('.wcvs-loading').remove();
        },

        // Mostrar error
        showError: function(message) {
            this.showMessage(message, 'error');
        },

        // Mostrar éxito
        showSuccess: function(message) {
            this.showMessage(message, 'success');
        },

        // Mostrar mensaje
        showMessage: function(message, type) {
            var messageClass = 'wcvs-message wcvs-message-' + type;
            var messageHtml = '<div class="' + messageClass + '">' + message + '</div>';
            
            $('.wcvs-onboarding-content').prepend(messageHtml);
            
            setTimeout(function() {
                $('.wcvs-message').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        },

        // Obtener datos de todos los pasos
        getAllStepsData: function() {
            return this.state.allStepsData;
        },

        // Establecer datos de todos los pasos
        setAllStepsData: function(data) {
            this.state.allStepsData = data;
        },

        // Destruir el módulo
        destroy: function() {
            $(document).off('.wcvs-onboarding');
        }
    };

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        if (typeof wcvs_onboarding_ajax !== 'undefined') {
            WCVS_Onboarding.init();
        }
    });

    // Limpiar cuando se abandona la página
    $(window).on('beforeunload', function() {
        WCVS_Onboarding.destroy();
    });

})(jQuery);
