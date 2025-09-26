/**
 * Modern UI Components JavaScript
 * Interactive components for WooCommerce Venezuela Pro 2025
 */

(function($) {
    'use strict';

    // Modern UI Namespace
    window.WVP_ModernUI = {
        
        // Initialize all components
        init: function() {
            this.initModals();
            this.initAlerts();
            this.initForms();
            this.initButtons();
            this.initCards();
            this.initProgressBars();
            this.initTooltips();
            this.initNotifications();
        },

        // Modal Component
        initModals: function() {
            // Create modal HTML structure
            this.createModalHTML();
            
            // Modal triggers
            $(document).on('click', '[data-wvp-modal]', function(e) {
                e.preventDefault();
                var modalId = $(this).data('wvp-modal');
                WVP_ModernUI.openModal(modalId);
            });

            // Close modal triggers
            $(document).on('click', '.wvp-modal-close, .wvp-modal-overlay', function(e) {
                if (e.target === this) {
                    WVP_ModernUI.closeModal();
                }
            });

            // Close modal with escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    WVP_ModernUI.closeModal();
                }
            });
        },

        createModalHTML: function() {
            if ($('#wvp-modal-container').length === 0) {
                $('body').append(`
                    <div id="wvp-modal-container" class="wvp-modal-overlay">
                        <div class="wvp-modal">
                            <div class="wvp-modal-header">
                                <h3 class="wvp-modal-title"></h3>
                                <button class="wvp-modal-close">&times;</button>
                            </div>
                            <div class="wvp-modal-body"></div>
                            <div class="wvp-modal-footer"></div>
                        </div>
                    </div>
                `);
            }
        },

        openModal: function(modalId, options = {}) {
            var $modal = $('#wvp-modal-container');
            var $title = $modal.find('.wvp-modal-title');
            var $body = $modal.find('.wvp-modal-body');
            var $footer = $modal.find('.wvp-modal-footer');

            // Set modal content
            if (options.title) {
                $title.text(options.title);
            }
            if (options.content) {
                $body.html(options.content);
            }
            if (options.footer) {
                $footer.html(options.footer);
            }

            // Show modal
            $modal.addClass('active');
            $('body').addClass('wvp-modal-open');
        },

        closeModal: function() {
            var $modal = $('#wvp-modal-container');
            $modal.removeClass('active');
            $('body').removeClass('wvp-modal-open');
        },

        // Alert Component
        initAlerts: function() {
            // Auto-dismiss alerts
            $(document).on('click', '.wvp-alert .wvp-alert-close', function() {
                $(this).closest('.wvp-alert').fadeOut(300, function() {
                    $(this).remove();
                });
            });

            // Auto-dismiss after delay
            $('.wvp-alert[data-auto-dismiss]').each(function() {
                var delay = $(this).data('auto-dismiss') || 5000;
                var $alert = $(this);
                setTimeout(function() {
                    $alert.fadeOut(300, function() {
                        $alert.remove();
                    });
                }, delay);
            });
        },

        showAlert: function(message, type = 'info', options = {}) {
            var alertClass = 'wvp-alert-' + type;
            var icon = this.getAlertIcon(type);
            var autoDismiss = options.autoDismiss !== false;
            var dismissible = options.dismissible !== false;

            var alertHTML = `
                <div class="wvp-alert ${alertClass} wvp-fade-in" ${autoDismiss ? 'data-auto-dismiss="5000"' : ''}>
                    <span class="wvp-alert-icon">${icon}</span>
                    <span class="wvp-alert-message">${message}</span>
                    ${dismissible ? '<button class="wvp-alert-close">&times;</button>' : ''}
                </div>
            `;

            var $container = options.container ? $(options.container) : $('body');
            $container.prepend(alertHTML);
        },

        getAlertIcon: function(type) {
            var icons = {
                success: '✅',
                warning: '⚠️',
                error: '❌',
                info: 'ℹ️'
            };
            return icons[type] || icons.info;
        },

        // Form Component
        initForms: function() {
            // Form validation
            $(document).on('submit', '.wvp-form', function(e) {
                if (!WVP_ModernUI.validateForm($(this))) {
                    e.preventDefault();
                    return false;
                }
            });

            // Real-time validation
            $(document).on('blur', '.wvp-form-input, .wvp-form-select, .wvp-form-textarea', function() {
                WVP_ModernUI.validateField($(this));
            });

            // Form loading states
            $(document).on('submit', '.wvp-form', function() {
                var $form = $(this);
                var $submitBtn = $form.find('[type="submit"]');
                
                if ($submitBtn.length) {
                    $submitBtn.prop('disabled', true);
                    $submitBtn.data('original-text', $submitBtn.text());
                    $submitBtn.html('<span class="wvp-spinner wvp-spinner-sm"></span> Procesando...');
                }
            });
        },

        validateForm: function($form) {
            var isValid = true;
            var $fields = $form.find('.wvp-form-input, .wvp-form-select, .wvp-form-textarea');

            $fields.each(function() {
                if (!WVP_ModernUI.validateField($(this))) {
                    isValid = false;
                }
            });

            return isValid;
        },

        validateField: function($field) {
            var value = $field.val();
            var required = $field.prop('required');
            var type = $field.attr('type') || $field.prop('tagName').toLowerCase();
            var isValid = true;
            var errorMessage = '';

            // Required validation
            if (required && (!value || value.trim() === '')) {
                isValid = false;
                errorMessage = 'Este campo es requerido';
            }

            // Type-specific validation
            if (isValid && value) {
                switch (type) {
                    case 'email':
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                            isValid = false;
                            errorMessage = 'Ingrese un email válido';
                        }
                        break;
                    case 'tel':
                        if (!/^[\d\s\-\+\(\)]+$/.test(value)) {
                            isValid = false;
                            errorMessage = 'Ingrese un teléfono válido';
                        }
                        break;
                    case 'number':
                        if (isNaN(value)) {
                            isValid = false;
                            errorMessage = 'Ingrese un número válido';
                        }
                        break;
                }
            }

            // Show/hide error
            this.showFieldError($field, isValid ? '' : errorMessage);

            return isValid;
        },

        showFieldError: function($field, message) {
            var $error = $field.siblings('.wvp-form-error');
            
            if (message) {
                if ($error.length === 0) {
                    $error = $('<div class="wvp-form-error"></div>');
                    $field.after($error);
                }
                $error.text(message);
                $field.addClass('wvp-field-error');
            } else {
                $error.remove();
                $field.removeClass('wvp-field-error');
            }
        },

        // Button Component
        initButtons: function() {
            // Loading states
            $(document).on('click', '.wvp-btn[data-loading]', function() {
                var $btn = $(this);
                var originalText = $btn.text();
                var loadingText = $btn.data('loading') || 'Cargando...';
                
                $btn.prop('disabled', true);
                $btn.data('original-text', originalText);
                $btn.html(`<span class="wvp-spinner wvp-spinner-sm"></span> ${loadingText}`);
            });

            // Reset loading state
            this.resetButtonLoading = function($btn) {
                var originalText = $btn.data('original-text');
                if (originalText) {
                    $btn.prop('disabled', false);
                    $btn.text(originalText);
                    $btn.removeData('original-text');
                }
            };
        },

        // Card Component
        initCards: function() {
            // Card interactions
            $(document).on('click', '.wvp-card[data-href]', function(e) {
                if (!$(e.target).closest('a, button, input, select, textarea').length) {
                    window.location.href = $(this).data('href');
                }
            });

            // Card loading states
            this.showCardLoading = function($card) {
                $card.addClass('wvp-card-loading');
                $card.append('<div class="wvp-loading-overlay"><span class="wvp-spinner wvp-spinner-lg"></span></div>');
            };

            this.hideCardLoading = function($card) {
                $card.removeClass('wvp-card-loading');
                $card.find('.wvp-loading-overlay').remove();
            };
        },

        // Progress Bar Component
        initProgressBars: function() {
            // Animate progress bars when they come into view
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var $progressBar = $(entry.target).find('.wvp-progress-bar');
                        var width = $progressBar.data('width') || $progressBar.attr('aria-valuenow');
                        if (width) {
                            $progressBar.css('width', width + '%');
                        }
                    }
                });
            });

            $('.wvp-progress').each(function() {
                observer.observe(this);
            });
        },

        // Tooltip Component
        initTooltips: function() {
            $(document).on('mouseenter', '[data-wvp-tooltip]', function() {
                var $element = $(this);
                var text = $element.data('wvp-tooltip');
                var position = $element.data('tooltip-position') || 'top';
                
                var $tooltip = $('<div class="wvp-tooltip wvp-tooltip-' + position + '">' + text + '</div>');
                $('body').append($tooltip);
                
                // Position tooltip
                var elementRect = this.getBoundingClientRect();
                var tooltipRect = $tooltip[0].getBoundingClientRect();
                
                switch (position) {
                    case 'top':
                        $tooltip.css({
                            top: elementRect.top - tooltipRect.height - 8,
                            left: elementRect.left + (elementRect.width - tooltipRect.width) / 2
                        });
                        break;
                    case 'bottom':
                        $tooltip.css({
                            top: elementRect.bottom + 8,
                            left: elementRect.left + (elementRect.width - tooltipRect.width) / 2
                        });
                        break;
                    case 'left':
                        $tooltip.css({
                            top: elementRect.top + (elementRect.height - tooltipRect.height) / 2,
                            left: elementRect.left - tooltipRect.width - 8
                        });
                        break;
                    case 'right':
                        $tooltip.css({
                            top: elementRect.top + (elementRect.height - tooltipRect.height) / 2,
                            left: elementRect.right + 8
                        });
                        break;
                }
                
                $element.data('wvp-tooltip-element', $tooltip);
            });

            $(document).on('mouseleave', '[data-wvp-tooltip]', function() {
                var $element = $(this);
                var $tooltip = $element.data('wvp-tooltip-element');
                if ($tooltip) {
                    $tooltip.remove();
                    $element.removeData('wvp-tooltip-element');
                }
            });
        },

        // Notification Component
        initNotifications: function() {
            this.createNotificationContainer();
        },

        createNotificationContainer: function() {
            if ($('#wvp-notifications').length === 0) {
                $('body').append('<div id="wvp-notifications" class="wvp-notifications-container"></div>');
            }
        },

        showNotification: function(message, type = 'info', options = {}) {
            var duration = options.duration || 5000;
            var dismissible = options.dismissible !== false;
            var icon = this.getAlertIcon(type);
            
            var notificationHTML = `
                <div class="wvp-notification wvp-notification-${type} wvp-slide-up" data-duration="${duration}">
                    <span class="wvp-notification-icon">${icon}</span>
                    <span class="wvp-notification-message">${message}</span>
                    ${dismissible ? '<button class="wvp-notification-close">&times;</button>' : ''}
                </div>
            `;

            var $notification = $(notificationHTML);
            $('#wvp-notifications').append($notification);

            // Auto-dismiss
            if (duration > 0) {
                setTimeout(function() {
                    $notification.fadeOut(300, function() {
                        $notification.remove();
                    });
                }, duration);
            }

            // Manual dismiss
            $notification.find('.wvp-notification-close').on('click', function() {
                $notification.fadeOut(300, function() {
                    $notification.remove();
                });
            });

            return $notification;
        },

        // Utility Methods
        formatCurrency: function(amount, currency = 'USD') {
            var formatter = new Intl.NumberFormat('es-VE', {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: 2
            });
            return formatter.format(amount);
        },

        formatNumber: function(number, decimals = 2) {
            return new Intl.NumberFormat('es-VE', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        },

        debounce: function(func, wait) {
            var timeout;
            return function executedFunction() {
                var later = function() {
                    clearTimeout(timeout);
                    func.apply(this, arguments);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        throttle: function(func, limit) {
            var inThrottle;
            return function() {
                var args = arguments;
                var context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(function() {
                        inThrottle = false;
                    }, limit);
                }
            };
        },

        // AJAX Helper
        ajax: function(options) {
            var defaults = {
                type: 'POST',
                dataType: 'json',
                beforeSend: function() {
                    if (options.loading) {
                        WVP_ModernUI.showCardLoading($(options.loading));
                    }
                },
                complete: function() {
                    if (options.loading) {
                        WVP_ModernUI.hideCardLoading($(options.loading));
                    }
                },
                success: function(response) {
                    if (response.success) {
                        if (options.successMessage) {
                            WVP_ModernUI.showNotification(options.successMessage, 'success');
                        }
                        if (options.success) {
                            options.success(response);
                        }
                    } else {
                        var errorMessage = response.data || 'Error en la operación';
                        WVP_ModernUI.showNotification(errorMessage, 'error');
                        if (options.error) {
                            options.error(response);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = 'Error de conexión: ' + error;
                    WVP_ModernUI.showNotification(errorMessage, 'error');
                    if (options.error) {
                        options.error(xhr, status, error);
                    }
                }
            };

            $.ajax($.extend(defaults, options));
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        WVP_ModernUI.init();
    });

    // Add CSS for additional components
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .wvp-field-error {
                border-color: var(--wvp-error-color) !important;
                box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1) !important;
            }
            
            .wvp-tooltip {
                position: absolute;
                background: var(--wvp-text-primary);
                color: white;
                padding: 8px 12px;
                border-radius: var(--wvp-border-radius-sm);
                font-size: var(--wvp-font-size-xs);
                z-index: 10000;
                pointer-events: none;
                white-space: nowrap;
            }
            
            .wvp-tooltip::after {
                content: '';
                position: absolute;
                border: 5px solid transparent;
            }
            
            .wvp-tooltip-top::after {
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border-top-color: var(--wvp-text-primary);
            }
            
            .wvp-tooltip-bottom::after {
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                border-bottom-color: var(--wvp-text-primary);
            }
            
            .wvp-tooltip-left::after {
                left: 100%;
                top: 50%;
                transform: translateY(-50%);
                border-left-color: var(--wvp-text-primary);
            }
            
            .wvp-tooltip-right::after {
                right: 100%;
                top: 50%;
                transform: translateY(-50%);
                border-right-color: var(--wvp-text-primary);
            }
            
            .wvp-notifications-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
            }
            
            .wvp-notification {
                background: var(--wvp-bg-primary);
                border: 1px solid var(--wvp-border-color);
                border-radius: var(--wvp-border-radius);
                box-shadow: var(--wvp-shadow-md);
                padding: var(--wvp-spacing-md);
                margin-bottom: var(--wvp-spacing-sm);
                display: flex;
                align-items: center;
                position: relative;
            }
            
            .wvp-notification-success {
                border-left: 4px solid var(--wvp-success-color);
            }
            
            .wvp-notification-warning {
                border-left: 4px solid var(--wvp-warning-color);
            }
            
            .wvp-notification-error {
                border-left: 4px solid var(--wvp-error-color);
            }
            
            .wvp-notification-info {
                border-left: 4px solid var(--wvp-info-color);
            }
            
            .wvp-notification-icon {
                margin-right: var(--wvp-spacing-sm);
                font-size: var(--wvp-font-size-md);
            }
            
            .wvp-notification-message {
                flex: 1;
                font-size: var(--wvp-font-size-sm);
            }
            
            .wvp-notification-close {
                background: none;
                border: none;
                font-size: var(--wvp-font-size-lg);
                cursor: pointer;
                color: var(--wvp-text-secondary);
                padding: 0;
                margin-left: var(--wvp-spacing-sm);
            }
            
            .wvp-modal-open {
                overflow: hidden;
            }
            
            .wvp-card-loading {
                position: relative;
                pointer-events: none;
            }
            
            .wvp-card-loading * {
                opacity: 0.6;
            }
            
            @media (max-width: 768px) {
                .wvp-notifications-container {
                    left: 20px;
                    right: 20px;
                    max-width: none;
                }
            }
        `)
        .appendTo('head');

})(jQuery);
