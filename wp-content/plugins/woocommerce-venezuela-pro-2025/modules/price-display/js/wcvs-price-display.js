/**
 * JavaScript para Visualización de Precios - WooCommerce Venezuela Suite
 * 
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Clase principal para manejo de precios
     */
    class WCVSPriceDisplay {
        constructor() {
            this.currentCurrency = 'USD';
            this.targetCurrency = 'VES';
            this.conversionRate = null;
            this.isLoading = false;
            this.cache = new Map();
            
            this.init();
        }

        /**
         * Inicializar el sistema
         */
        init() {
            this.bindEvents();
            this.loadConversionRate();
            this.setupAutoRefresh();
        }

        /**
         * Vincular eventos
         */
        bindEvents() {
            // Selector de moneda
            $(document).on('click', '.wcvs-currency-btn', this.handleCurrencySwitch.bind(this));
            $(document).on('change', '.wcvs-currency-select', this.handleCurrencySelect.bind(this));
            $(document).on('change', '.wcvs-toggle-input', this.handleCurrencyToggle.bind(this));

            // Actualización automática de precios
            $(document).on('wcvs_rate_updated', this.handleRateUpdate.bind(this));
        }

        /**
         * Manejar cambio de moneda con botones
         */
        handleCurrencySwitch(event) {
            event.preventDefault();
            
            const $button = $(event.currentTarget);
            const currency = $button.data('currency');
            
            if (currency === this.currentCurrency) {
                return;
            }

            this.switchCurrency(currency);
        }

        /**
         * Manejar cambio de moneda con select
         */
        handleCurrencySelect(event) {
            const currency = $(event.currentTarget).val();
            this.switchCurrency(currency);
        }

        /**
         * Manejar toggle de moneda
         */
        handleCurrencyToggle(event) {
            const currency = $(event.currentTarget).is(':checked') ? 'VES' : 'USD';
            this.switchCurrency(currency);
        }

        /**
         * Cambiar moneda
         */
        switchCurrency(currency) {
            if (this.isLoading) {
                return;
            }

            this.currentCurrency = currency;
            this.updateCurrencyButtons();
            this.updatePrices();
        }

        /**
         * Actualizar botones de moneda
         */
        updateCurrencyButtons() {
            $('.wcvs-currency-btn').removeClass('active');
            $(`.wcvs-currency-btn[data-currency="${this.currentCurrency}"]`).addClass('active');
            
            $('.wcvs-currency-select').val(this.currentCurrency);
            $('.wcvs-toggle-input').prop('checked', this.currentCurrency === 'VES');
        }

        /**
         * Actualizar precios
         */
        updatePrices() {
            $('.wcvs-price-display').each((index, element) => {
                this.updatePriceElement($(element));
            });
        }

        /**
         * Actualizar elemento de precio
         */
        updatePriceElement($element) {
            const style = this.getElementStyle($element);
            const context = this.getElementContext($element);
            
            if (this.currentCurrency === 'USD') {
                this.showUSDPrice($element, style, context);
            } else {
                this.showVESPrice($element, style, context);
            }
        }

        /**
         * Mostrar precio en USD
         */
        showUSDPrice($element, style, context) {
            const $usdPrice = $element.find('.wcvs-price-usd');
            const $vesPrice = $element.find('.wcvs-price-ves');
            const $separator = $element.find('.wcvs-price-separator');
            
            if ($usdPrice.length) {
                $usdPrice.show();
                this.animatePrice($usdPrice);
            }
            
            if ($vesPrice.length) {
                $vesPrice.hide();
            }
            
            if ($separator.length) {
                $separator.hide();
            }
        }

        /**
         * Mostrar precio en VES
         */
        showVESPrice($element, style, context) {
            const $usdPrice = $element.find('.wcvs-price-usd');
            const $vesPrice = $element.find('.wcvs-price-ves');
            const $separator = $element.find('.wcvs-price-separator');
            
            if ($vesPrice.length) {
                $vesPrice.show();
                this.animatePrice($vesPrice);
            }
            
            if ($usdPrice.length) {
                $usdPrice.hide();
            }
            
            if ($separator.length) {
                $separator.hide();
            }
        }

        /**
         * Animar precio
         */
        animatePrice($element) {
            if (!wcvs_price_display.animation_enabled) {
                return;
            }

            $element.addClass('wcvs-price-animate');
            setTimeout(() => {
                $element.removeClass('wcvs-price-animate');
            }, 300);
        }

        /**
         * Obtener estilo del elemento
         */
        getElementStyle($element) {
            const classList = $element.attr('class') || '';
            const match = classList.match(/wcvs-style-(\w+)/);
            return match ? match[1] : 'minimalist';
        }

        /**
         * Obtener contexto del elemento
         */
        getElementContext($element) {
            const classList = $element.attr('class') || '';
            const match = classList.match(/wcvs-context-(\w+)/);
            return match ? match[1] : 'general';
        }

        /**
         * Cargar tasa de conversión
         */
        loadConversionRate() {
            if (this.cache.has('conversion_rate')) {
                this.conversionRate = this.cache.get('conversion_rate');
                return;
            }

            this.isLoading = true;
            this.showLoadingState();

            $.ajax({
                url: wcvs_price_display.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_conversion_rate',
                    nonce: wcvs_price_display.nonce
                },
                success: (response) => {
                    if (response.success && response.data.rate) {
                        this.conversionRate = response.data.rate;
                        this.cache.set('conversion_rate', this.conversionRate);
                        this.updateConversionRateDisplay();
                    }
                },
                error: () => {
                    this.showErrorState();
                },
                complete: () => {
                    this.isLoading = false;
                    this.hideLoadingState();
                }
            });
        }

        /**
         * Manejar actualización de tasa
         */
        handleRateUpdate(event, newRate, oldRate) {
            this.conversionRate = newRate;
            this.cache.set('conversion_rate', newRate);
            this.updateConversionRateDisplay();
            this.updatePrices();
        }

        /**
         * Actualizar visualización de tasa
         */
        updateConversionRateDisplay() {
            if (!this.conversionRate) {
                return;
            }

            $('.wcvs-conversion-rate .wcvs-rate-value').each((index, element) => {
                $(element).text(this.formatRate(this.conversionRate));
            });
        }

        /**
         * Formatear tasa
         */
        formatRate(rate) {
            return parseFloat(rate).toLocaleString('es-VE', {
                minimumFractionDigits: 4,
                maximumFractionDigits: 4
            });
        }

        /**
         * Mostrar estado de carga
         */
        showLoadingState() {
            $('.wcvs-price-display').addClass('wcvs-loading');
            $('.wcvs-conversion-rate').addClass('wcvs-loading');
        }

        /**
         * Ocultar estado de carga
         */
        hideLoadingState() {
            $('.wcvs-price-display').removeClass('wcvs-loading');
            $('.wcvs-conversion-rate').removeClass('wcvs-loading');
        }

        /**
         * Mostrar estado de error
         */
        showErrorState() {
            $('.wcvs-price-display').addClass('wcvs-error');
            $('.wcvs-conversion-rate').addClass('wcvs-error');
        }

        /**
         * Configurar actualización automática
         */
        setupAutoRefresh() {
            // Actualizar cada 5 minutos
            setInterval(() => {
                this.loadConversionRate();
            }, 300000);
        }

        /**
         * Convertir precio
         */
        convertPrice(usdPrice) {
            if (!this.conversionRate || !usdPrice) {
                return usdPrice;
            }

            return usdPrice * this.conversionRate;
        }

        /**
         * Formatear precio VES
         */
        formatVESPrice(price) {
            return parseFloat(price).toLocaleString('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        /**
         * Formatear precio USD
         */
        formatUSDPrice(price) {
            return parseFloat(price).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }

    /**
     * Clase para manejo de widgets
     */
    class WCVSWidgetManager {
        constructor() {
            this.init();
        }

        /**
         * Inicializar widgets
         */
        init() {
            this.setupCurrencyWidgets();
            this.setupConversionRateWidgets();
        }

        /**
         * Configurar widgets de moneda
         */
        setupCurrencyWidgets() {
            $('.wcvs-currency-widget').each((index, element) => {
                this.initCurrencyWidget($(element));
            });
        }

        /**
         * Inicializar widget de moneda
         */
        initCurrencyWidget($widget) {
            const $switcher = $widget.find('.wcvs-currency-switcher');
            const $rate = $widget.find('.wcvs-conversion-rate');

            if ($switcher.length) {
                $switcher.on('click', '.wcvs-currency-btn', (event) => {
                    const currency = $(event.currentTarget).data('currency');
                    this.updateWidgetCurrency($widget, currency);
                });
            }
        }

        /**
         * Actualizar moneda del widget
         */
        updateWidgetCurrency($widget, currency) {
            $widget.find('.wcvs-currency-btn').removeClass('active');
            $widget.find(`.wcvs-currency-btn[data-currency="${currency}"]`).addClass('active');
        }

        /**
         * Configurar widgets de tasa de conversión
         */
        setupConversionRateWidgets() {
            $('.wcvs-conversion-rate-widget').each((index, element) => {
                this.initConversionRateWidget($(element));
            });
        }

        /**
         * Inicializar widget de tasa de conversión
         */
        initConversionRateWidget($widget) {
            const autoRefresh = $widget.data('auto-refresh');
            const refreshInterval = $widget.data('refresh-interval') || 300000;

            if (autoRefresh) {
                setInterval(() => {
                    this.refreshConversionRate($widget);
                }, refreshInterval);
            }
        }

        /**
         * Actualizar tasa de conversión
         */
        refreshConversionRate($widget) {
            $.ajax({
                url: wcvs_price_display.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_get_conversion_rate',
                    nonce: wcvs_price_display.nonce
                },
                success: (response) => {
                    if (response.success && response.data.rate) {
                        $widget.find('.wcvs-rate-value').text(
                            parseFloat(response.data.rate).toLocaleString('es-VE', {
                                minimumFractionDigits: 4,
                                maximumFractionDigits: 4
                            })
                        );
                    }
                }
            });
        }
    }

    /**
     * Clase para manejo de shortcodes
     */
    class WCVSShortcodeManager {
        constructor() {
            this.init();
        }

        /**
         * Inicializar shortcodes
         */
        init() {
            this.setupPriceSwitcher();
            this.setupPriceDisplay();
            this.setupCurrencyBadge();
        }

        /**
         * Configurar selector de precio
         */
        setupPriceSwitcher() {
            $('.wcvs-price-switcher').each((index, element) => {
                this.initPriceSwitcher($(element));
            });
        }

        /**
         * Inicializar selector de precio
         */
        initPriceSwitcher($element) {
            $element.on('click', '.wcvs-currency-btn', (event) => {
                const currency = $(event.currentTarget).data('currency');
                this.updatePriceSwitcher($element, currency);
            });
        }

        /**
         * Actualizar selector de precio
         */
        updatePriceSwitcher($element, currency) {
            $element.find('.wcvs-currency-btn').removeClass('active');
            $element.find(`.wcvs-currency-btn[data-currency="${currency}"]`).addClass('active');
        }

        /**
         * Configurar visualización de precio
         */
        setupPriceDisplay() {
            $('.wcvs-price-display').each((index, element) => {
                this.initPriceDisplay($(element));
            });
        }

        /**
         * Inicializar visualización de precio
         */
        initPriceDisplay($element) {
            // La funcionalidad se maneja en la clase principal
        }

        /**
         * Configurar badge de moneda
         */
        setupCurrencyBadge() {
            $('.wcvs-currency-badge').each((index, element) => {
                this.initCurrencyBadge($(element));
            });
        }

        /**
         * Inicializar badge de moneda
         */
        initCurrencyBadge($element) {
            // Los badges son estáticos, no requieren inicialización especial
        }
    }

    /**
     * Inicializar cuando el documento esté listo
     */
    $(document).ready(() => {
        // Verificar que las variables estén disponibles
        if (typeof wcvs_price_display === 'undefined') {
            console.warn('WCVS Price Display: Variables de configuración no disponibles');
            return;
        }

        // Inicializar clases principales
        window.wcvsPriceDisplay = new WCVSPriceDisplay();
        window.wcvsWidgetManager = new WCVSWidgetManager();
        window.wcvsShortcodeManager = new WCVSShortcodeManager();

        // Evento personalizado para inicialización completa
        $(document).trigger('wcvs_price_display_ready');
    });

    /**
     * Funciones de utilidad globales
     */
    window.WCVSUtils = {
        /**
         * Convertir precio USD a VES
         */
        convertUSDToVES: function(usdPrice) {
            if (window.wcvsPriceDisplay) {
                return window.wcvsPriceDisplay.convertPrice(usdPrice);
            }
            return usdPrice;
        },

        /**
         * Formatear precio VES
         */
        formatVESPrice: function(price) {
            return parseFloat(price).toLocaleString('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        /**
         * Formatear precio USD
         */
        formatUSDPrice: function(price) {
            return parseFloat(price).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        /**
         * Obtener tasa de conversión actual
         */
        getCurrentRate: function() {
            if (window.wcvsPriceDisplay) {
                return window.wcvsPriceDisplay.conversionRate;
            }
            return null;
        }
    };

})(jQuery);
