/**
 * WooCommerce Venezuela Suite 2025 - Admin JavaScript
 *
 * Funcionalidad JavaScript para el panel de administración
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        WCVS_Admin.init();
    });

    // Main Admin object
    window.WCVS_Admin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.initTabs();
            this.initModuleToggle();
            this.initSettingsForm();
            this.initHelpSystem();
            this.initNotifications();
        },

        /**
         * Initialize tab functionality
         */
        initTabs: function() {
            $('.wcvs-tab-link').on('click', function(e) {
                e.preventDefault();
                
                var target = $(this).attr('href');
                
                // Update active tab
                $('.wcvs-tab-link').removeClass('active');
                $(this).addClass('active');
                
                // Update active panel
                $('.wcvs-tab-panel').removeClass('active');
                $(target).addClass('active');
            });
        },

        /**
         * Initialize module toggle functionality
         */
        initModuleToggle: function() {
            $('.wcvs-toggle-module').on('click', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var moduleId = $button.data('module');
                var action = $button.data('action');
                
                // Show confirmation
                var confirmMessage = action === 'activate' ? 
                    wcvs_admin.strings.confirm_activate : 
                    wcvs_admin.strings.confirm_deactivate;
                
                if (!confirm(confirmMessage)) {
                    return;
                }
                
                // Show loading state
                $button.prop('disabled', true).text(wcvs_admin.strings.saving);
                
                // Make AJAX request
                $.ajax({
                    url: wcvs_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wcvs_toggle_module',
                        module_id: moduleId,
                        action_type: action,
                        nonce: wcvs_admin.toggle_module_nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update button state
                            var newAction = action === 'activate' ? 'deactivate' : 'activate';
                            var newText = action === 'activate' ? 'Desactivar' : 'Activar';
                            var newClass = action === 'activate' ? 'button-secondary' : 'button-primary';
                            
                            $button
                                .data('action', newAction)
                                .text(newText)
                                .removeClass('button-primary button-secondary')
                                .addClass(newClass);
                            
                            // Update module card
                            var $card = $button.closest('.wcvs-module-card');
                            $card.removeClass('active inactive').addClass(newAction === 'activate' ? 'active' : 'inactive');
                            
                            // Show success notification
                            WCVS_Admin.showNotification(response.data.message, 'success');
                        } else {
                            WCVS_Admin.showNotification(response.data, 'error');
                        }
                    },
                    error: function() {
                        WCVS_Admin.showNotification(wcvs_admin.strings.error, 'error');
                    },
                    complete: function() {
                        $button.prop('disabled', false);
                    }
                });
            });
        },

        /**
         * Initialize settings form
         */
        initSettingsForm: function() {
            $('#wcvs-settings-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $submitButton = $form.find('input[type="submit"]');
                
                // Show loading state
                $submitButton.prop('disabled', true).val(wcvs_admin.strings.saving);
                
                // Collect form data
                var formData = $form.serializeArray();
                var settings = {};
                
                $.each(formData, function(i, field) {
                    var parts = field.name.match(/^([^[]+)\[([^\]]+)\]$/);
                    if (parts) {
                        if (!settings[parts[1]]) {
                            settings[parts[1]] = {};
                        }
                        settings[parts[1]][parts[2]] = field.value;
                    }
                });
                
                // Make AJAX request
                $.ajax({
                    url: wcvs_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wcvs_save_settings',
                        settings: settings,
                        nonce: wcvs_admin.save_settings_nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            WCVS_Admin.showNotification(response.data.message, 'success');
                        } else {
                            WCVS_Admin.showNotification(response.data, 'error');
                        }
                    },
                    error: function() {
                        WCVS_Admin.showNotification(wcvs_admin.strings.error, 'error');
                    },
                    complete: function() {
                        $submitButton.prop('disabled', false).val('Guardar Configuración');
                    }
                });
            });
        },

        /**
         * Initialize help system
         */
        initHelpSystem: function() {
            $('.wcvs-help-link').on('click', function(e) {
                e.preventDefault();
                
                var moduleId = $(this).data('module');
                
                // Make AJAX request to get help data
                $.ajax({
                    url: wcvs_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wcvs_get_help',
                        help_type: 'module',
                        module_id: moduleId,
                        nonce: wcvs_admin.get_help_nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            WCVS_Admin.showHelpModal(response.data);
                        } else {
                            WCVS_Admin.showNotification(response.data, 'error');
                        }
                    },
                    error: function() {
                        WCVS_Admin.showNotification(wcvs_admin.strings.error, 'error');
                    }
                });
            });
        },

        /**
         * Show help modal
         */
        showHelpModal: function(helpData) {
            var modalHtml = '<div class="wcvs-help-modal">' +
                '<div class="wcvs-help-modal-content">' +
                    '<div class="wcvs-help-modal-header">' +
                        '<h2>' + helpData.title + '</h2>' +
                        '<button class="wcvs-help-modal-close">&times;</button>' +
                    '</div>' +
                    '<div class="wcvs-help-modal-body">' +
                        '<p>' + helpData.description + '</p>';
            
            // Add WooCommerce settings links
            if (helpData.woocommerce_settings && helpData.woocommerce_settings.length > 0) {
                modalHtml += '<h3>Configuraciones de WooCommerce</h3><ul>';
                $.each(helpData.woocommerce_settings, function(i, setting) {
                    modalHtml += '<li><a href="' + setting.url + '" target="_blank">' + setting.title + '</a> - ' + setting.description + '</li>';
                });
                modalHtml += '</ul>';
            }
            
            // Add configuration steps
            if (helpData.configuration_steps && helpData.configuration_steps.length > 0) {
                modalHtml += '<h3>Pasos de Configuración</h3><ol>';
                $.each(helpData.configuration_steps, function(i, step) {
                    modalHtml += '<li>' + step + '</li>';
                });
                modalHtml += '</ol>';
            }
            
            // Add common issues
            if (helpData.common_issues && helpData.common_issues.length > 0) {
                modalHtml += '<h3>Problemas Comunes</h3><dl>';
                $.each(helpData.common_issues, function(i, issue) {
                    modalHtml += '<dt>' + issue.problem + '</dt><dd>' + issue.solution + '</dd>';
                });
                modalHtml += '</dl>';
            }
            
            modalHtml += '</div></div></div>';
            
            // Add modal to page
            $('body').append(modalHtml);
            
            // Show modal
            $('.wcvs-help-modal').fadeIn();
            
            // Close modal handlers
            $('.wcvs-help-modal-close, .wcvs-help-modal').on('click', function(e) {
                if (e.target === this) {
                    $('.wcvs-help-modal').fadeOut(function() {
                        $(this).remove();
                    });
                }
            });
        },

        /**
         * Initialize notifications
         */
        initNotifications: function() {
            // Auto-hide notifications after 5 seconds
            setTimeout(function() {
                $('.wcvs-notification').fadeOut();
            }, 5000);
        },

        /**
         * Show notification
         */
        showNotification: function(message, type) {
            var notificationHtml = '<div class="wcvs-notification ' + type + '">' + message + '</div>';
            
            // Add to top of page
            $('.wrap').prepend(notificationHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.wcvs-notification').fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Show loading state
         */
        showLoading: function($element) {
            $element.addClass('wcvs-loading');
        },

        /**
         * Hide loading state
         */
        hideLoading: function($element) {
            $element.removeClass('wcvs-loading');
        }
    };

})(jQuery);

// Add modal styles dynamically
jQuery(document).ready(function($) {
    var modalStyles = `
        <style>
        .wcvs-help-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: none;
        }
        
        .wcvs-help-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 4px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .wcvs-help-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ccd0d4;
        }
        
        .wcvs-help-modal-header h2 {
            margin: 0;
            color: #1d2327;
        }
        
        .wcvs-help-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #646970;
        }
        
        .wcvs-help-modal-close:hover {
            color: #1d2327;
        }
        
        .wcvs-help-modal-body {
            padding: 20px;
        }
        
        .wcvs-help-modal-body h3 {
            color: #1d2327;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        .wcvs-help-modal-body ul,
        .wcvs-help-modal-body ol {
            margin-left: 20px;
        }
        
        .wcvs-help-modal-body li {
            margin-bottom: 8px;
        }
        
        .wcvs-help-modal-body dt {
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 5px;
        }
        
        .wcvs-help-modal-body dd {
            margin-bottom: 15px;
            margin-left: 20px;
        }
        </style>
    `;
    
    $('head').append(modalStyles);
});
