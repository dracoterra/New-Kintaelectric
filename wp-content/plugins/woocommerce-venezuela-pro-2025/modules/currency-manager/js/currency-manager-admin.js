/**
 * WooCommerce Venezuela Suite 2025 - Currency Manager Admin JavaScript
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * Currency Manager Admin Class
     */
    class WCVSCurrencyManagerAdmin {
        constructor() {
            this.init();
        }

        /**
         * Initialize currency manager admin
         */
        init() {
            this.bindEvents();
            this.loadExchangeRate();
            this.initExchangeRateChart();
        }

        /**
         * Bind events
         */
        bindEvents() {
            $(document).on('click', '.wcvs-refresh-exchange-rate', this.refreshExchangeRate.bind(this));
            $(document).on('click', '.wcvs-test-exchange-source', this.testExchangeSource.bind(this));
            $(document).on('change', '.wcvs-exchange-source-select', this.handleSourceChange.bind(this));
            $(document).on('click', '.wcvs-save-exchange-settings', this.saveExchangeSettings.bind(this));
            $(document).on('click', '.wcvs-reset-exchange-settings', this.resetExchangeSettings.bind(this));
        }

        /**
         * Load exchange rate
         */
        loadExchangeRate() {
            $.ajax({
                url: wcvs_currency_manager_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_exchange_rate',
                    nonce: wcvs_currency_manager_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.displayExchangeRate(response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error loading exchange rate:', error);
                }
            });
        }

        /**
         * Display exchange rate
         */
        displayExchangeRate(data) {
            $('.wcvs-current-rate').text(this.formatPrice(data.rate, 'VES'));
            $('.wcvs-rate-source').text(data.source);
            $('.wcvs-rate-updated').text(data.updated);
            
            // Update chart if available
            if (typeof this.updateChart === 'function') {
                this.updateChart(data);
            }
        }

        /**
         * Refresh exchange rate
         */
        refreshExchangeRate(event) {
            event.preventDefault();
            
            const $button = $(event.target);
            const originalText = $button.text();
            
            $button.text(wcvs_currency_manager_admin.strings.loading).prop('disabled', true);
            
            $.ajax({
                url: wcvs_currency_manager_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_refresh_exchange_rate',
                    nonce: wcvs_currency_manager_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.displayExchangeRate(response.data);
                        this.showMessage(wcvs_currency_manager_admin.strings.success, 'success');
                    } else {
                        this.showMessage(wcvs_currency_manager_admin.strings.error, 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.showMessage(wcvs_currency_manager_admin.strings.error, 'error');
                },
                complete: () => {
                    $button.text(originalText).prop('disabled', false);
                }
            });
        }

        /**
         * Test exchange source
         */
        testExchangeSource(event) {
            event.preventDefault();
            
            const $button = $(event.target);
            const sourceId = $button.data('source');
            const originalText = $button.text();
            
            $button.text(wcvs_currency_manager_admin.strings.loading).prop('disabled', true);
            
            $.ajax({
                url: wcvs_currency_manager_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_test_exchange_source',
                    source_id: sourceId,
                    nonce: wcvs_currency_manager_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showMessage('Fuente ' + sourceId + ' funcionando correctamente', 'success');
                    } else {
                        this.showMessage('Error en fuente ' + sourceId + ': ' + response.data.message, 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.showMessage('Error en fuente ' + sourceId + ': ' + error, 'error');
                },
                complete: () => {
                    $button.text(originalText).prop('disabled', false);
                }
            });
        }

        /**
         * Handle source change
         */
        handleSourceChange(event) {
            const sourceId = $(event.target).val();
            this.loadSourceSettings(sourceId);
        }

        /**
         * Load source settings
         */
        loadSourceSettings(sourceId) {
            $.ajax({
                url: wcvs_currency_manager_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_source_settings',
                    source_id: sourceId,
                    nonce: wcvs_currency_manager_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.displaySourceSettings(response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error loading source settings:', error);
                }
            });
        }

        /**
         * Display source settings
         */
        displaySourceSettings(settings) {
            $('.wcvs-source-settings').html(settings.html);
        }

        /**
         * Save exchange settings
         */
        saveExchangeSettings(event) {
            event.preventDefault();
            
            const $button = $(event.target);
            const originalText = $button.text();
            
            $button.text(wcvs_currency_manager_admin.strings.loading).prop('disabled', true);
            
            const formData = new FormData($('.wcvs-exchange-settings-form')[0]);
            formData.append('action', 'wcvs_save_exchange_settings');
            formData.append('nonce', wcvs_currency_manager_admin.nonce);
            
            $.ajax({
                url: wcvs_currency_manager_admin.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.showMessage(wcvs_currency_manager_admin.strings.success, 'success');
                    } else {
                        this.showMessage(wcvs_currency_manager_admin.strings.error, 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.showMessage(wcvs_currency_manager_admin.strings.error, 'error');
                },
                complete: () => {
                    $button.text(originalText).prop('disabled', false);
                }
            });
        }

        /**
         * Reset exchange settings
         */
        resetExchangeSettings(event) {
            event.preventDefault();
            
            if (confirm('¿Estás seguro de que quieres resetear la configuración de tipos de cambio?')) {
                const $button = $(event.target);
                const originalText = $button.text();
                
                $button.text(wcvs_currency_manager_admin.strings.loading).prop('disabled', true);
                
                $.ajax({
                    url: wcvs_currency_manager_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wcvs_reset_exchange_settings',
                        nonce: wcvs_currency_manager_admin.nonce
                    },
                    success: (response) => {
                        if (response.success) {
                            this.showMessage(wcvs_currency_manager_admin.strings.success, 'success');
                            location.reload();
                        } else {
                            this.showMessage(wcvs_currency_manager_admin.strings.error, 'error');
                        }
                    },
                    error: (xhr, status, error) => {
                        this.showMessage(wcvs_currency_manager_admin.strings.error, 'error');
                    },
                    complete: () => {
                        $button.text(originalText).prop('disabled', false);
                    }
                });
            }
        }

        /**
         * Initialize exchange rate chart
         */
        initExchangeRateChart() {
            if (typeof Chart !== 'undefined') {
                this.loadExchangeRateHistory();
            }
        }

        /**
         * Load exchange rate history
         */
        loadExchangeRateHistory() {
            $.ajax({
                url: wcvs_currency_manager_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_exchange_rate_history',
                    days: 30,
                    nonce: wcvs_currency_manager_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.createExchangeRateChart(response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error loading exchange rate history:', error);
                }
            });
        }

        /**
         * Create exchange rate chart
         */
        createExchangeRateChart(data) {
            const ctx = document.getElementById('wcvs-exchange-rate-chart');
            if (!ctx) return;

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Tipo de Cambio USD/VES',
                        data: data.rates,
                        borderColor: '#0073aa',
                        backgroundColor: 'rgba(0, 115, 170, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return 'Bs. ' + value.toLocaleString('es-VE');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Tipo de cambio: Bs. ' + context.parsed.y.toLocaleString('es-VE');
                                }
                            }
                        }
                    }
                }
            });
        }

        /**
         * Show message
         */
        showMessage(message, type) {
            const $message = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wcvs-admin-header').after($message);
            
            setTimeout(() => {
                $message.fadeOut(() => {
                    $message.remove();
                });
            }, 5000);
        }

        /**
         * Format price
         */
        formatPrice(amount, currency) {
            if (currency === 'VES') {
                return 'Bs. ' + amount.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            } else if (currency === 'USD') {
                return '$' + amount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            
            return amount;
        }
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        new WCVSCurrencyManagerAdmin();
    });

})(jQuery);
