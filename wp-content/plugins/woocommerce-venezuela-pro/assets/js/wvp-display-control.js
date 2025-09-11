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
            // Selector de moneda global
            $(document).on('click', '.wvp-currency-switcher .wvp-currency-btn', (e) => {
                e.preventDefault();
                this.handleCurrencySwitch($(e.currentTarget));
            });
            
            // Dropdown de moneda
            $(document).on('change', '.wvp-currency-dropdown', (e) => {
                this.handleCurrencyDropdown($(e.currentTarget));
            });
            
            // Toggle de moneda
            $(document).on('change', '.wvp-currency-toggle .wvp-toggle-input', (e) => {
                this.handleCurrencyToggle($(e.currentTarget));
            });
            
            // Actualizar todos los switchers cuando cambia la moneda
            $(document).on('wvp:currencyChanged', (e, currency) => {
                this.updateAllSwitchers(currency);
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
        }
        
        /**
         * Inicializar selector individual
         */
        initializeSwitcher($switcher) {
            const scope = $switcher.hasClass('wvp-scope-global') ? 'global' : 'local';
            const currentCurrency = this.getCurrentCurrency();
            
            // Establecer moneda actual
            $switcher.find('.wvp-currency-btn').removeClass('active');
            $switcher.find(`[data-currency="${currentCurrency}"]`).addClass('active');
            
            // Configurar alcance
            if (scope === 'global') {
                $switcher.attr('data-scope', 'global');
            } else {
                $switcher.attr('data-scope', 'local');
            }
        }
        
        /**
         * Manejar cambio de moneda en botones
         */
        handleCurrencySwitch($button) {
            const $switcher = $button.closest('.wvp-currency-switcher');
            const currency = $button.data('currency');
            const scope = $switcher.attr('data-scope') || 'global';
            
            if (!currency) {
                console.warn('WVP: No se pudo cambiar moneda - datos faltantes');
                return;
            }
            
            // Prevenir múltiples clics
            if ($button.hasClass('wvp-switching')) {
                return;
            }
            
            $button.addClass('wvp-switching');
            
            // Actualizar botones activos en este switcher
            $switcher.find('.wvp-currency-btn').removeClass('active');
            $button.addClass('active');
            
            // Cambiar moneda según el alcance
            if (scope === 'global') {
                this.changeGlobalCurrency(currency);
            } else {
                this.changeLocalCurrency($switcher, currency);
            }
            
            // Guardar preferencia
            this.saveUserPreference(currency);
            
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
         * Cambiar moneda globalmente
         */
        changeGlobalCurrency(currency) {
            // Actualizar todos los switchers globales
            $('.wvp-currency-switcher[data-scope="global"]').each((index, element) => {
                this.updateSwitcher($(element), currency);
            });
            
            // Actualizar precios en toda la página
            this.updateAllPrices(currency);
            
            // Disparar evento personalizado
            $(document).trigger('wvp:currencyChanged', [currency]);
        }
        
        /**
         * Cambiar moneda localmente
         */
        changeLocalCurrency($switcher, currency) {
            // Solo actualizar este switcher y productos cercanos
            this.updateSwitcher($switcher, currency);
            this.updateNearbyPrices($switcher, currency);
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
         * Actualizar todos los selectores
         */
        updateAllSwitchers(currency) {
            $('.wvp-currency-switcher').each((index, element) => {
                this.updateSwitcher($(element), currency);
            });
        }
        
        /**
         * Actualizar todos los precios
         */
        updateAllPrices(currency) {
            $('.wvp-product-price-container').each((index, element) => {
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
         * Actualizar contenedor de precio
         */
        updatePriceContainer($container, currency) {
            const $usdPrice = $container.find('.wvp-price-usd');
            const $vesPrice = $container.find('.wvp-price-ves');
            const $conversion = $container.find('.wvp-price-conversion');
            const $rateInfo = $container.find('.wvp-rate-info');
            
            if (currency === 'USD') {
                $vesPrice.fadeOut(200, () => {
                    $usdPrice.fadeIn(200);
                });
                $conversion.fadeIn(200);
                if ($rateInfo.length) {
                    $rateInfo.fadeIn(200);
                }
            } else {
                $usdPrice.fadeOut(200, () => {
                    $vesPrice.fadeIn(200);
                });
                $conversion.fadeOut(200);
                if ($rateInfo.length) {
                    $rateInfo.fadeOut(200);
                }
            }
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
