/**
 * JavaScript para Control de Visualización - WooCommerce Venezuela Pro
 * Maneja shortcodes y control granular de visualización
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Clase principal para control de visualización
     */
    class WVP_Display_Control {
        constructor() {
            this.currentCurrency = wvp_display_control.current_currency;
            this.settings = wvp_display_control.settings;
            this.init();
        }
        
        /**
         * Inicializar
         */
        init() {
            this.bindEvents();
            this.initializeShortcodes();
            this.loadUserPreferences();
        }
        
        /**
         * Vincular eventos
         */
        bindEvents() {
            // Selector de moneda - usar event delegation con scope específico
            $(document).on('click', '.wvp-currency-switcher button, .wvp-currency-switcher .wvp-currency-option', (e) => {
                e.preventDefault();
                e.stopPropagation(); // Prevenir propagación
                this.handleCurrencySwitch($(e.currentTarget));
            });
            
            // Dropdown de moneda
            $(document).on('change', '.wvp-currency-dropdown', (e) => {
                e.stopPropagation();
                this.handleCurrencyDropdown($(e.currentTarget));
            });
            
            // Toggle de moneda
            $(document).on('change', '.wvp-currency-toggle .wvp-toggle-input', (e) => {
                e.stopPropagation();
                this.handleCurrencyToggle($(e.currentTarget));
            });
            
            // Actualizar solo switchers globales cuando cambia la moneda
            $(document).on('wvp:currencyChanged', (e, currency) => {
                this.updateGlobalSwitchers(currency);
            });
        }
        
        /**
         * Inicializar shortcodes
         */
        initializeShortcodes() {
            // Inicializar selectores de moneda existentes
            $('.wvp-currency-switcher').each((index, element) => {
                this.initializeSwitcher($(element));
            });
            
            // Inicializar estados de precios
            this.initializePriceStates();
        }
        
        /**
         * Inicializar estados de precios
         */
        initializePriceStates() {
            $('.wvp-product-price-container').each((index, element) => {
                const $container = $(element);
                const $usdPrice = $container.find('.wvp-price-usd');
                const $vesPrice = $container.find('.wvp-price-ves');
                
                // Forzar estado inicial correcto
                $usdPrice.css('display', 'block');
                $vesPrice.css('display', 'none');
                
                console.log('WVP: Inicializado producto', index, '- USD visible, VES oculto');
            });
        }
        
        /**
         * Inicializar selector individual
         */
        initializeSwitcher($switcher) {
            // Forzar scope local para productos individuales
            const scope = $switcher.hasClass('wvp-scope-global') ? 'global' : 'local';
            const currentCurrency = this.getCurrentCurrency();
            
            // Establecer moneda actual (asegurar mayúsculas)
            $switcher.find('.wvp-currency-btn, .wvp-currency-option').removeClass('active');
            $switcher.find(`[data-currency="USD"]`).addClass('active');
            
            // Siempre configurar como local para productos individuales
            $switcher.attr('data-scope', 'local');
        }
        
        /**
         * Manejar cambio de moneda en botones
         */
        handleCurrencySwitch($button) {
            // Obtener el contenedor del producto más cercano
            const $productContainer = $button.closest('.wvp-product-price-container');
            const $switcher = $button.closest('.wvp-currency-switcher');
            const currency = $button.data('currency');
            
            console.log('WVP: Botón clickeado:', $button[0]);
            console.log('WVP: data-currency:', currency, 'tipo:', typeof currency);
            console.log('WVP: Atributos del botón:', $button[0].attributes);
            
            if (!currency || !$productContainer.length) {
                console.warn('WVP: No se pudo cambiar moneda - datos faltantes o contenedor no encontrado');
                return;
            }
            
            // Prevenir múltiples clics
            if ($button.hasClass('wvp-switching')) {
                return;
            }
            
            $button.addClass('wvp-switching');
            
            // Actualizar solo este switcher específico
            $switcher.find('button, .wvp-currency-option').removeClass('active');
            $button.addClass('active');
            
            // Actualizar solo este producto específico
            this.updateSingleProduct($productContainer, currency);
            
            console.log('WVP: Cambio de moneda individual - Producto:', $productContainer.attr('class'), 'Moneda:', currency);
            
            // Remover clase de switching
            setTimeout(() => {
                $button.removeClass('wvp-switching');
            }, 300);
        }
        
        /**
         * Manejar cambio de moneda en dropdown
         */
        handleCurrencyDropdown($dropdown) {
            const currency = $dropdown.val();
            const $switcher = $dropdown.closest('.wvp-currency-switcher');
            const scope = $switcher.attr('data-scope') || 'global';
            
            if (scope === 'global') {
                this.changeGlobalCurrency(currency);
            } else {
                this.changeLocalCurrency($switcher, currency);
            }
            
            this.saveUserPreference(currency);
        }
        
        /**
         * Manejar cambio de moneda en toggle
         */
        handleCurrencyToggle($toggle) {
            const currency = $toggle.is(':checked') ? 'VES' : 'USD';
            const $switcher = $toggle.closest('.wvp-currency-switcher');
            const scope = $switcher.attr('data-scope') || 'global';
            
            if (scope === 'global') {
                this.changeGlobalCurrency(currency);
            } else {
                this.changeLocalCurrency($switcher, currency);
            }
            
            this.saveUserPreference(currency);
        }
        
        /**
         * Cambiar moneda globalmente (solo para shortcodes globales)
         */
        changeGlobalCurrency(currency) {
            // Solo actualizar switchers marcados como globales
            $('.wvp-currency-switcher[data-scope="global"]').each((index, element) => {
                this.updateSwitcher($(element), currency);
            });
            
            // Disparar evento personalizado
            $(document).trigger('wvp:currencyChanged', [currency]);
        }
        
        /**
         * Actualizar selector individual
         */
        updateSwitcher($switcher, currency) {
            // Actualizar botones
            $switcher.find('.wvp-currency-btn').removeClass('active');
            $switcher.find(`[data-currency="${currency}"]`).addClass('active');
            
            // Actualizar dropdown
            $switcher.find('.wvp-currency-dropdown').val(currency);
            
            // Actualizar toggle
            const $toggle = $switcher.find('.wvp-toggle-input');
            if ($toggle.length) {
                $toggle.prop('checked', currency === 'VES');
            }
        }
        
        /**
         * Actualizar selectores globales (solo para cambios globales)
         */
        updateGlobalSwitchers(currency) {
            // Solo actualizar selectores marcados como globales
            $('.wvp-currency-switcher[data-scope="global"]').each((index, element) => {
                this.updateSwitcher($(element), currency);
            });
        }
        
        /**
         * Actualizar todos los precios (solo productos, no widgets)
         */
        updateAllPrices(currency) {
            // Solo actualizar contenedores de productos, no widgets ni footer
            $('.wvp-product-price-container').not('.widget .wvp-product-price-container, .footer .wvp-product-price-container').each((index, element) => {
                this.updatePriceContainer($(element), currency);
            });
        }
        
        
        /**
         * Actualizar precios cercanos
         */
        updateNearbyPrices($switcher, currency) {
            // Buscar contenedores de precio cercanos
            const $nearbyContainers = $switcher.siblings('.wvp-product-price-container')
                .add($switcher.parent().find('.wvp-product-price-container'))
                .add($switcher.closest('.product').find('.wvp-product-price-container'));
            
            $nearbyContainers.each((index, element) => {
                this.updatePriceContainer($(element), currency);
            });
        }
        
        /**
         * Actualizar un solo producto específico
         */
        updateSingleProduct($productContainer, currency) {
            console.log('WVP: updateSingleProduct ejecutado, moneda:', currency);
            console.log('WVP: Contenedor del producto:', $productContainer[0]);
            
            const $usdPrice = $productContainer.find('.wvp-price-usd');
            const $vesPrice = $productContainer.find('.wvp-price-ves');
            const $conversion = $productContainer.find('.wvp-price-conversion');
            const $rateInfo = $productContainer.find('.wvp-rate-info');
            
            console.log('WVP: Elementos encontrados:');
            console.log('WVP: - USD Price:', $usdPrice.length, $usdPrice[0]);
            console.log('WVP: - VES Price:', $vesPrice.length, $vesPrice[0]);
            console.log('WVP: - Conversion:', $conversion.length, $conversion[0]);
            console.log('WVP: - Rate Info:', $rateInfo.length, $rateInfo[0]);
            
            // Forzar estilos iniciales correctos
            $usdPrice.css('display', 'block');
            $vesPrice.css('display', 'none');
            
            console.log('WVP: Comparando moneda:', currency, 'tipo:', typeof currency);
            
            if (currency === 'USD') {
                console.log('WVP: Cambiando a USD');
                $vesPrice.fadeOut(200, () => {
                    $usdPrice.fadeIn(200);
                    console.log('WVP: USD mostrado, VES oculto');
                });
                $conversion.fadeIn(200);
                if ($rateInfo.length) {
                    $rateInfo.fadeIn(200);
                }
            } else if (currency === 'VES') {
                console.log('WVP: Cambiando a VES');
                $usdPrice.fadeOut(200, () => {
                    $vesPrice.fadeIn(200);
                    console.log('WVP: VES mostrado, USD oculto');
                });
                $conversion.fadeOut(200);
                if ($rateInfo.length) {
                    $rateInfo.fadeOut(200);
                }
            } else {
                console.log('WVP: Moneda no reconocida:', currency, 'tipo:', typeof currency);
            }
        }
        
        /**
         * Actualizar contenedor de precio (método legacy)
         */
        updatePriceContainer($container, currency) {
            this.updateSingleProduct($container, currency);
        }
        
        /**
         * Obtener moneda actual
         */
        getCurrentCurrency() {
            return this.currentCurrency || 'USD';
        }
        
        /**
         * Cargar preferencias del usuario
         */
        loadUserPreferences() {
            const savedCurrency = this.getCookie('wvp_currency');
            if (savedCurrency && savedCurrency !== this.currentCurrency) {
                this.changeGlobalCurrency(savedCurrency);
            }
        }
        
        /**
         * Guardar preferencia del usuario
         */
        saveUserPreference(currency) {
            this.setCookie('wvp_currency', currency, 30); // 30 días
            this.currentCurrency = currency;
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
    
    // Inicializar cuando el documento esté listo
    $(document).ready(() => {
        if (typeof wvp_display_control !== 'undefined') {
            new WVP_Display_Control();
        }
    });
    
})(jQuery);
