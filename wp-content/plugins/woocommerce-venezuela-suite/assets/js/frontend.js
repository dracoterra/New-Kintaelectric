/**
 * JavaScript del Frontend - WooCommerce Venezuela Suite
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Objeto principal del frontend
    const WVSFrontend = {
        
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
            // Convertidor de moneda
            $(document).on('input change', '#wvs-amount, #wvs-from-currency, #wvs-to-currency', this.updateCurrencyConverter);
            
            // Actualizar precios dinámicamente
            $(document).on('change', '.wvs-currency-selector', this.updatePriceDisplay);
        },
        
        /**
         * Inicializar componentes
         */
        initComponents: function() {
            this.initCurrencyConverter();
            this.initPriceDisplay();
        },
        
        /**
         * Inicializar convertidor de moneda
         */
        initCurrencyConverter: function() {
            const $converter = $('.wvs-currency-converter');
            if (!$converter.length) {
                return;
            }
            
            // Configurar valores iniciales
            const $amount = $('#wvs-amount');
            const $fromCurrency = $('#wvs-from-currency');
            const $toCurrency = $('#wvs-to-currency');
            
            if ($amount.length && $fromCurrency.length && $toCurrency.length) {
                this.updateCurrencyConverter();
            }
        },
        
        /**
         * Actualizar convertidor de moneda
         */
        updateCurrencyConverter: function() {
            const $amount = $('#wvs-amount');
            const $fromCurrency = $('#wvs-from-currency');
            const $toCurrency = $('#wvs-to-currency');
            const $result = $('#wvs-result');
            
            if (!$amount.length || !$fromCurrency.length || !$toCurrency.length || !$result.length) {
                return;
            }
            
            const amount = parseFloat($amount.val()) || 0;
            const fromCurrency = $fromCurrency.val();
            const toCurrency = $toCurrency.val();
            
            if (amount <= 0) {
                $result.val('');
                return;
            }
            
            // Mostrar estado de carga
            $result.addClass('wvs-loading');
            
            // Realizar conversión
            $.ajax({
                url: wvs_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'wvs_convert_currency',
                    amount: amount,
                    from: fromCurrency,
                    to: toCurrency,
                    nonce: wvs_frontend.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $result.val(response.data.converted_amount);
                    } else {
                        $result.val('Error');
                    }
                },
                error: function() {
                    $result.val('Error');
                },
                complete: function() {
                    $result.removeClass('wvs-loading');
                }
            });
        },
        
        /**
         * Inicializar visualización de precios
         */
        initPriceDisplay: function() {
            // Añadir selector de moneda si no existe
            if ($('.wvs-currency-selector').length === 0) {
                this.addCurrencySelector();
            }
        },
        
        /**
         * Añadir selector de moneda
         */
        addCurrencySelector: function() {
            const $priceContainer = $('.price, .woocommerce-Price-amount');
            if (!$priceContainer.length) {
                return;
            }
            
            const $selector = $(`
                <div class="wvs-currency-selector">
                    <label>
                        <input type="radio" name="wvs-currency" value="usd" checked> USD
                    </label>
                    <label>
                        <input type="radio" name="wvs-currency" value="ves"> VES
                    </label>
                </div>
            `);
            
            $priceContainer.after($selector);
        },
        
        /**
         * Actualizar visualización de precios
         */
        updatePriceDisplay: function() {
            const selectedCurrency = $('input[name="wvs-currency"]:checked').val();
            
            if (selectedCurrency === 'ves') {
                $('.price').addClass('wvs-show-ves');
            } else {
                $('.price').removeClass('wvs-show-ves');
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
        WVSFrontend.init();
    });
    
    // Exponer globalmente para uso externo
    window.WVSFrontend = WVSFrontend;
    
})(jQuery);
