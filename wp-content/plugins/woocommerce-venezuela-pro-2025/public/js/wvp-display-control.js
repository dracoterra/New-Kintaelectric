/**
 * JavaScript para el Sistema de Control de Visualización
 * WooCommerce Venezuela Pro 2025
 */

(function($) {
    'use strict';
    
    // Verificar que tenemos los datos necesarios
    if (typeof wvp_display === 'undefined') {
        console.warn('WVP Display Control: Datos de configuración no encontrados');
        return;
    }
    
    /**
     * Clase principal del controlador de visualización
     */
    class WVP_DisplayController {
        constructor() {
            this.settings = wvp_display.settings || {};
            this.currentRate = parseFloat(wvp_display.currency_rate) || 36.5;
            this.currentCurrency = this.getCookie('wvp_currency') || 'USD';
            this.strings = wvp_display.strings || {};
            
            this.init();
        }
        
        /**
         * Inicializar el controlador
         */
        init() {
            this.bindEvents();
            this.initConverters();
            this.updateAllPrices();
            
            // Debug mode
            if (this.settings.debug_mode) {
                console.log('WVP Display Controller initialized', {
                    settings: this.settings,
                    currentRate: this.currentRate,
                    currentCurrency: this.currentCurrency
                });
            }
        }
        
        /**
         * Vincular eventos
         */
        bindEvents() {
            // Eventos para botones de moneda
            $(document).on('click', '.wvp-currency-btn', (e) => {
                e.preventDefault();
                const currency = $(e.currentTarget).data('currency');
                this.switchCurrency(currency, $(e.currentTarget).closest('.wvp-currency-switcher'));
            });
            
            // Eventos para dropdown de moneda
            $(document).on('change', '.wvp-currency-dropdown', (e) => {
                const currency = $(e.currentTarget).val();
                this.switchCurrency(currency, $(e.currentTarget).closest('.wvp-currency-switcher'));
            });
            
            // Eventos para toggle de moneda
            $(document).on('change', '.wvp-toggle-input', (e) => {
                const currency = $(e.currentTarget).is(':checked') ? 'VES' : 'USD';
                this.switchCurrency(currency, $(e.currentTarget).closest('.wvp-currency-switcher'));
            });
            
            // Eventos para enlaces inline
            $(document).on('click', '.wvp-currency-switch-link', (e) => {
                e.preventDefault();
                const currency = $(e.currentTarget).data('toggle-currency');
                this.switchCurrency(currency, $(e.currentTarget).closest('.wvp-currency-switcher'));
            });
            
            // Eventos para inputs de conversión
            $(document).on('input', '.wvp-converter-input', (e) => {
                this.handleConverterInput($(e.currentTarget));
            });
            
            // Actualizar cuando cambie el carrito (WooCommerce)
            $(document.body).on('updated_cart_totals updated_checkout', () => {
                this.updateCartTotals();
            });
        }
        
        /**
         * Inicializar conversores existentes
         */
        initConverters() {
            $('.wvp-currency-switcher').each((index, element) => {
                this.initSingleConverter($(element));
            });
        }
        
        /**
         * Inicializar un conversor individual
         */
        initSingleConverter($converter) {
            const scope = $converter.data('scope') || 'global';
            const rate = parseFloat($converter.data('rate')) || this.currentRate;
            
            // Actualizar estado visual
            this.updateConverterVisualState($converter);
            
            // Si es scope local, solo afecta precios cercanos
            if (scope === 'local') {
                this.updateLocalPrices($converter);
            }
        }
        
        /**
         * Cambiar moneda
         */
        switchCurrency(newCurrency, $converter) {
            if (newCurrency === this.currentCurrency) {
                return; // No hay cambio
            }
            
            const oldCurrency = this.currentCurrency;
            this.currentCurrency = newCurrency;
            
            // Guardar en cookie
            this.setCookie('wvp_currency', newCurrency, 30);
            
            // Actualizar estado visual del conversor
            this.updateConverterVisualState($converter);
            
            // Determinar scope
            const scope = $converter.data('scope') || 'global';
            
            if (scope === 'global') {
                // Actualizar todos los precios en la página
                this.updateAllPrices();
                this.updateAllConverters(newCurrency);
            } else {
                // Solo actualizar precios locales
                this.updateLocalPrices($converter);
            }
            
            // Animación si está configurada
            this.applyAnimation($converter);
            
            // Trigger custom event
            $(document).trigger('wvp_currency_changed', {
                oldCurrency: oldCurrency,
                newCurrency: newCurrency,
                converter: $converter,
                scope: scope
            });
            
            // Debug
            if (this.settings.debug_mode) {
                console.log('Currency switched', {
                    from: oldCurrency,
                    to: newCurrency,
                    scope: scope
                });
            }
        }
        
        /**
         * Actualizar estado visual del conversor
         */
        updateConverterVisualState($converter) {
            // Actualizar botones
            $converter.find('.wvp-currency-btn').removeClass('active');
            $converter.find(`.wvp-currency-btn[data-currency="${this.currentCurrency}"]`).addClass('active');
            
            // Actualizar dropdown
            $converter.find('.wvp-currency-dropdown').val(this.currentCurrency);
            
            // Actualizar toggle
            $converter.find('.wvp-toggle-input').prop('checked', this.currentCurrency === 'VES');
            
            // Actualizar inline
            const $inline = $converter.find('.wvp-currency-inline');
            if ($inline.length) {
                const otherCurrency = this.currentCurrency === 'USD' ? 'VES' : 'USD';
                $inline.find('.wvp-currency-current').text(this.currentCurrency);
                $inline.find('.wvp-currency-switch-link')
                    .attr('data-toggle-currency', otherCurrency)
                    .text('→ ' + otherCurrency);
            }
        }
        
        /**
         * Actualizar todos los conversores en la página
         */
        updateAllConverters(newCurrency) {
            $('.wvp-currency-switcher').each((index, element) => {
                const $converter = $(element);
                if ($converter.data('scope') !== 'local') {
                    this.updateConverterVisualState($converter);
                }
            });
        }
        
        /**
         * Actualizar todos los precios en la página
         */
        updateAllPrices() {
            // Actualizar precios de WooCommerce
            this.updateWooCommercePrices();
            
            // Actualizar conversores de precio
            this.updatePriceConverters();
            
            // Actualizar totales del carrito
            this.updateCartTotals();
        }
        
        /**
         * Actualizar precios locales (scope local)
         */
        updateLocalPrices($converter) {
            // Buscar precios cercanos al conversor
            const $priceElements = $converter.siblings('.price, .woocommerce-Price-amount, .amount');
            $priceElements.each((index, element) => {
                this.updateSinglePrice($(element));
            });
            
            // También buscar en el contenedor padre
            const $parentPrices = $converter.closest('.product, .cart_item, .order-item')
                .find('.price, .woocommerce-Price-amount, .amount');
            $parentPrices.each((index, element) => {
                this.updateSinglePrice($(element));
            });
        }
        
        /**
         * Actualizar precios de WooCommerce
         */
        updateWooCommercePrices() {
            $('.price, .woocommerce-Price-amount, .amount').each((index, element) => {
                this.updateSinglePrice($(element));
            });
        }
        
        /**
         * Actualizar un precio individual
         */
        updateSinglePrice($priceElement) {
            const originalPrice = $priceElement.data('wvp-original-price');
            const originalCurrency = $priceElement.data('wvp-original-currency') || 'USD';
            
            if (typeof originalPrice === 'undefined') {
                // Guardar precio original la primera vez
                const priceText = $priceElement.text().replace(/[^\d.,]/g, '');
                const price = this.parsePrice(priceText);
                
                if (price > 0) {
                    $priceElement.data('wvp-original-price', price);
                    $priceElement.data('wvp-original-currency', originalCurrency);
                    
                    // Actualizar el precio
                    this.updatePriceDisplay($priceElement, price, originalCurrency);
                }
            } else {
                // Actualizar con precio original guardado
                this.updatePriceDisplay($priceElement, originalPrice, originalCurrency);
            }
        }
        
        /**
         * Actualizar visualización de precio
         */
        updatePriceDisplay($element, originalPrice, originalCurrency) {
            let displayPrice = originalPrice;
            let displayCurrency = originalCurrency;
            
            // Convertir si es necesario
            if (this.currentCurrency !== originalCurrency) {
                displayPrice = this.convertPrice(originalPrice, originalCurrency, this.currentCurrency);
                displayCurrency = this.currentCurrency;
            }
            
            // Formatear precio
            const formattedPrice = this.formatPrice(displayPrice, displayCurrency);
            
            // Actualizar elemento
            if ($element.find('.woocommerce-Price-currencySymbol').length) {
                // WooCommerce price structure
                $element.find('.woocommerce-Price-currencySymbol').text(this.getCurrencySymbol(displayCurrency));
                $element.find('.woocommerce-Price-amount').contents().filter(function() {
                    return this.nodeType === 3; // Text nodes
                }).first().replaceWith(this.formatNumber(displayPrice));
            } else {
                // Simple price element
                $element.html(formattedPrice);
            }
        }
        
        /**
         * Actualizar conversores de precio
         */
        updatePriceConverters() {
            $('.wvp-price-converter').each((index, element) => {
                const $converter = $(element);
                const $input = $converter.find('.wvp-converter-input');
                
                if ($input.length) {
                    this.handleConverterInput($input);
                }
            });
        }
        
        /**
         * Manejar input de conversor
         */
        handleConverterInput($input) {
            const amount = parseFloat($input.val()) || 0;
            const from = $input.data('from') || 'USD';
            const to = $input.data('to') || 'VES';
            
            if (amount > 0) {
                const converted = this.convertPrice(amount, from, to);
                const $result = $input.closest('.wvp-price-converter').find('.wvp-price-converted');
                $result.text(this.formatPrice(converted, to));
            }
        }
        
        /**
         * Actualizar totales del carrito
         */
        updateCartTotals() {
            // Solo si estamos en carrito o checkout
            if (!$('body').hasClass('woocommerce-cart') && !$('body').hasClass('woocommerce-checkout')) {
                return;
            }
            
            // Actualizar total convertido en carrito
            const $cartTotal = $('.cart-subtotal .woocommerce-Price-amount, .order-total .woocommerce-Price-amount').last();
            if ($cartTotal.length) {
                const totalText = $cartTotal.text().replace(/[^\d.,]/g, '');
                const total = this.parsePrice(totalText);
                
                if (total > 0) {
                    const convertedTotal = this.convertPrice(total, 'USD', 'VES');
                    
                    // Actualizar o crear fila de total convertido
                    let $convertedRow = $('.wvp-cart-converter-row');
                    if ($convertedRow.length) {
                        $convertedRow.find('td').html('<strong>' + this.formatPrice(convertedTotal, 'VES') + '</strong>');
                    }
                }
            }
        }
        
        /**
         * Convertir precio entre monedas
         */
        convertPrice(amount, from, to) {
            if (from === to) {
                return amount;
            }
            
            if (from === 'USD' && to === 'VES') {
                return amount * this.currentRate;
            } else if (from === 'VES' && to === 'USD') {
                return amount / this.currentRate;
            }
            
            return amount;
        }
        
        /**
         * Formatear precio
         */
        formatPrice(amount, currency) {
            const symbol = this.getCurrencySymbol(currency);
            const formatted = this.formatNumber(amount);
            
            if (currency === 'USD') {
                return symbol + formatted;
            } else {
                return symbol + ' ' + formatted;
            }
        }
        
        /**
         * Formatear número
         */
        formatNumber(number) {
            return number.toLocaleString('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        /**
         * Obtener símbolo de moneda
         */
        getCurrencySymbol(currency) {
            const symbols = {
                'USD': '$',
                'VES': 'Bs.'
            };
            return symbols[currency] || '';
        }
        
        /**
         * Parsear precio desde texto
         */
        parsePrice(priceText) {
            if (typeof priceText !== 'string') {
                return 0;
            }
            
            // Remover símbolos de moneda y espacios
            let cleanPrice = priceText.replace(/[^\d.,]/g, '');
            
            // Manejar formato venezolano (punto como separador de miles, coma como decimal)
            if (cleanPrice.includes(',') && cleanPrice.includes('.')) {
                // Formato: 1.234.567,89
                cleanPrice = cleanPrice.replace(/\./g, '').replace(',', '.');
            } else if (cleanPrice.includes(',')) {
                // Formato: 1234,89
                cleanPrice = cleanPrice.replace(',', '.');
            }
            
            return parseFloat(cleanPrice) || 0;
        }
        
        /**
         * Aplicar animación
         */
        applyAnimation($converter) {
            const animation = this.settings.animation || 'fade';
            
            if (animation === 'none') {
                return;
            }
            
            const $target = $converter.find('.wvp-price-converted, .price, .woocommerce-Price-amount').first();
            
            if (!$target.length) {
                return;
            }
            
            $target.addClass('wvp-animating');
            
            switch (animation) {
                case 'fade':
                    $target.fadeOut(150).fadeIn(150);
                    break;
                case 'slide':
                    $target.slideUp(150).slideDown(150);
                    break;
                case 'bounce':
                    $target.addClass('wvp-bounce');
                    setTimeout(() => $target.removeClass('wvp-bounce'), 600);
                    break;
            }
            
            setTimeout(() => $target.removeClass('wvp-animating'), 300);
        }
        
        /**
         * Obtener cookie
         */
        getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                return parts.pop().split(';').shift();
            }
            return null;
        }
        
        /**
         * Establecer cookie
         */
        setCookie(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
        }
    }
    
    /**
     * Animaciones CSS adicionales
     */
    const animationCSS = `
        <style>
        .wvp-bounce {
            animation: wvpBounce 0.6s ease;
        }
        
        @keyframes wvpBounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                transform: translate3d(0, -8px, 0);
            }
            70% {
                transform: translate3d(0, -4px, 0);
            }
            90% {
                transform: translate3d(0, -2px, 0);
            }
        }
        
        .wvp-animating {
            pointer-events: none;
        }
        </style>
    `;
    
    // Inyectar CSS de animaciones
    $('head').append(animationCSS);
    
    /**
     * Inicializar cuando el DOM esté listo
     */
    $(document).ready(function() {
        // Inicializar controlador principal
        window.WVP_DisplayController = new WVP_DisplayController();
        
        // Exponer funciones públicas
        window.WVP = window.WVP || {};
        window.WVP.switchCurrency = function(currency) {
            window.WVP_DisplayController.switchCurrency(currency, $('.wvp-currency-switcher').first());
        };
        
        window.WVP.convertPrice = function(amount, from, to) {
            return window.WVP_DisplayController.convertPrice(amount, from, to);
        };
        
        window.WVP.formatPrice = function(amount, currency) {
            return window.WVP_DisplayController.formatPrice(amount, currency);
        };
        
        // Debug info
        if (wvp_display.settings.debug_mode) {
            console.log('WVP Display Control loaded successfully');
            console.log('Available functions: WVP.switchCurrency(), WVP.convertPrice(), WVP.formatPrice()');
        }
    });
    
})(jQuery);
