/**
 * JavaScript para Visualización de Productos - WooCommerce Venezuela Pro
 * Maneja la interactividad del switcher de moneda y animaciones
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Clase principal para el display de productos
     */
    class WVP_Product_Display {
        constructor() {
            this.currentStyle = wvp_product_display.current_style;
            this.animations = true;
            this.init();
        }
        
        /**
         * Inicializar el display
         */
        init() {
            this.bindEvents();
            this.loadUserPreferences();
            this.initializeAnimations();
            this.setupAccessibility();
        }
        
        /**
         * Vincular eventos
         */
        bindEvents() {
            // Cambio de moneda
            $(document).on('click', '.wvp-currency-switcher button, .wvp-currency-switcher .wvp-currency-option', (e) => {
                e.preventDefault();
                this.switchCurrency($(e.currentTarget));
            });
            
            // Hover effects
            $(document).on('mouseenter', '.wvp-currency-switcher button, .wvp-currency-switcher .wvp-currency-option', (e) => {
                this.handleHover($(e.currentTarget), 'enter');
            });
            
            $(document).on('mouseleave', '.wvp-currency-switcher button, .wvp-currency-switcher .wvp-currency-option', (e) => {
                this.handleHover($(e.currentTarget), 'leave');
            });
            
            // Resize events
            $(window).on('resize', () => {
                this.handleResize();
            });
            
            // Visibility change
            $(document).on('visibilitychange', () => {
                this.handleVisibilityChange();
            });
        }
        
        /**
         * Cambiar moneda
         */
        switchCurrency($button) {
            const $container = $button.closest('.wvp-product-price-container');
            const $switcher = $button.closest('.wvp-currency-switcher');
            const currency = $button.data('currency');
            const priceUsd = $switcher.data('price-usd');
            const priceVes = $switcher.data('price-ves');
            
            if (!currency || !priceUsd || !priceVes) {
                return;
            }
            
            // Actualizar botones activos
            $switcher.find('button, .wvp-currency-option').removeClass('active');
            $button.addClass('active');
            
            // Mostrar/ocultar precios
            this.updatePriceDisplay($container, currency, priceUsd, priceVes);
            
            // Guardar preferencia
            this.saveUserPreference(currency);
            
            // Animación
            if (this.animations) {
                this.animatePriceChange($container);
            }
            
            // Evento personalizado
            $(document).trigger('wvp_currency_changed', {
                currency: currency,
                priceUsd: priceUsd,
                priceVes: priceVes,
                container: $container
            });
        }
        
        /**
         * Actualizar display de precios
         */
        updatePriceDisplay($container, currency, priceUsd, priceVes) {
            const $priceUsd = $container.find('.wvp-price-usd');
            const $priceVes = $container.find('.wvp-price-ves');
            const $conversion = $container.find('.wvp-price-conversion');
            
            if (currency === 'usd') {
                $priceUsd.show();
                $priceVes.hide();
                $conversion.show();
            } else {
                $priceUsd.hide();
                $priceVes.show();
                $conversion.hide();
            }
        }
        
        /**
         * Animar cambio de precio
         */
        animatePriceChange($container) {
            const $priceDisplay = $container.find('.wvp-price-display');
            
            // Efecto de fade
            $priceDisplay.addClass('wvp-loading');
            
            setTimeout(() => {
                $priceDisplay.removeClass('wvp-loading');
            }, 300);
            
            // Efecto de escala
            $priceDisplay.css('transform', 'scale(0.95)');
            
            setTimeout(() => {
                $priceDisplay.css('transform', 'scale(1)');
            }, 150);
        }
        
        /**
         * Manejar hover
         */
        handleHover($button, action) {
            if (action === 'enter') {
                $button.addClass('wvp-hover');
            } else {
                $button.removeClass('wvp-hover');
            }
        }
        
        /**
         * Cargar preferencias del usuario
         */
        loadUserPreferences() {
            // Verificar cookie
            const cookieCurrency = this.getCookie('wvp_preferred_currency');
            if (cookieCurrency) {
                this.setCurrencyPreference(cookieCurrency);
            }
            
            // Verificar meta del usuario (si está logueado)
            if (wvp_product_display.user_id) {
                this.loadUserMeta();
            }
        }
        
        /**
         * Cargar meta del usuario
         */
        loadUserMeta() {
            $.ajax({
                url: wvp_product_display.ajax_url,
                type: 'POST',
                data: {
                    action: 'wvp_get_user_currency',
                    nonce: wvp_product_display.nonce,
                    user_id: wvp_product_display.user_id
                },
                success: (response) => {
                    if (response.success && response.data.currency) {
                        this.setCurrencyPreference(response.data.currency);
                    }
                }
            });
        }
        
        /**
         * Establecer preferencia de moneda
         */
        setCurrencyPreference(currency) {
            $('.wvp-currency-switcher').each((index, element) => {
                const $switcher = $(element);
                const $button = $switcher.find(`[data-currency="${currency}"]`);
                
                if ($button.length) {
                    this.switchCurrency($button);
                }
            });
        }
        
        /**
         * Guardar preferencia del usuario
         */
        saveUserPreference(currency) {
            // Guardar en cookie
            this.setCookie('wvp_preferred_currency', currency, 30);
            
            // Guardar via AJAX si está logueado
            if (wvp_product_display.user_id) {
                $.ajax({
                    url: wvp_product_display.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wvp_save_user_currency',
                        nonce: wvp_product_display.nonce,
                        currency: currency
                    }
                });
            }
        }
        
        /**
         * Inicializar animaciones
         */
        initializeAnimations() {
            // Verificar si las animaciones están habilitadas
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                this.animations = false;
                $('body').addClass('wvp-no-animations');
            }
            
            // Animar elementos al cargar
            this.animateOnLoad();
        }
        
        /**
         * Animar elementos al cargar
         */
        animateOnLoad() {
            if (!this.animations) return;
            
            $('.wvp-product-price-container').each((index, element) => {
                const $container = $(element);
                const delay = index * 100;
                
                setTimeout(() => {
                    $container.addClass('wvp-animate-in');
                }, delay);
            });
        }
        
        /**
         * Configurar accesibilidad
         */
        setupAccessibility() {
            // Añadir atributos ARIA
            $('.wvp-currency-switcher').attr('role', 'tablist');
            $('.wvp-currency-switcher button, .wvp-currency-switcher .wvp-currency-option').attr('role', 'tab');
            
            // Manejar navegación por teclado
            $(document).on('keydown', '.wvp-currency-switcher button, .wvp-currency-switcher .wvp-currency-option', (e) => {
                this.handleKeyboardNavigation(e);
            });
        }
        
        /**
         * Manejar navegación por teclado
         */
        handleKeyboardNavigation(e) {
            const $button = $(e.currentTarget);
            const $switcher = $button.closest('.wvp-currency-switcher');
            const $buttons = $switcher.find('button, .wvp-currency-option');
            const currentIndex = $buttons.index($button);
            
            switch (e.key) {
                case 'ArrowLeft':
                case 'ArrowUp':
                    e.preventDefault();
                    const prevIndex = currentIndex > 0 ? currentIndex - 1 : $buttons.length - 1;
                    $buttons.eq(prevIndex).focus().trigger('click');
                    break;
                    
                case 'ArrowRight':
                case 'ArrowDown':
                    e.preventDefault();
                    const nextIndex = currentIndex < $buttons.length - 1 ? currentIndex + 1 : 0;
                    $buttons.eq(nextIndex).focus().trigger('click');
                    break;
                    
                case 'Enter':
                case ' ':
                    e.preventDefault();
                    $button.trigger('click');
                    break;
            }
        }
        
        /**
         * Manejar redimensionamiento
         */
        handleResize() {
            // Ajustar layout en móviles
            if (window.innerWidth <= 768) {
                $('.wvp-currency-switcher').addClass('wvp-mobile');
            } else {
                $('.wvp-currency-switcher').removeClass('wvp-mobile');
            }
        }
        
        /**
         * Manejar cambio de visibilidad
         */
        handleVisibilityChange() {
            if (document.hidden) {
                // Pausar animaciones cuando la página no es visible
                $('.wvp-product-price-container').addClass('wvp-paused');
            } else {
                // Reanudar animaciones
                $('.wvp-product-price-container').removeClass('wvp-paused');
            }
        }
        
        /**
         * Obtener cookie
         */
        getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
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
        
        /**
         * Obtener estilo actual
         */
        getCurrentStyle() {
            return this.currentStyle;
        }
        
        /**
         * Cambiar estilo
         */
        setStyle(style) {
            this.currentStyle = style;
            $('body').removeClass('wvp-display-style-' + this.currentStyle);
            $('body').addClass('wvp-display-style-' + style);
        }
    }
    
    /**
     * Inicializar cuando el documento esté listo
     */
    $(document).ready(() => {
        // Verificar que el objeto wvp_product_display esté disponible
        if (typeof wvp_product_display !== 'undefined') {
            window.wvpProductDisplay = new WVP_Product_Display();
        }
    });
    
    /**
     * Funciones de utilidad globales
     */
    window.WVP_Product_Display_Utils = {
        /**
         * Formatear precio
         */
        formatPrice: (price, currency = 'USD') => {
            if (currency === 'USD') {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD'
                }).format(price);
            } else {
                return new Intl.NumberFormat('es-VE', {
                    style: 'currency',
                    currency: 'VES'
                }).format(price);
            }
        },
        
        /**
         * Animar elemento
         */
        animateElement: ($element, animation, duration = 300) => {
            $element.addClass(`wvp-animate-${animation}`);
            setTimeout(() => {
                $element.removeClass(`wvp-animate-${animation}`);
            }, duration);
        },
        
        /**
         * Verificar si es móvil
         */
        isMobile: () => {
            return window.innerWidth <= 768;
        },
        
        /**
         * Verificar si es tablet
         */
        isTablet: () => {
            return window.innerWidth > 768 && window.innerWidth <= 1024;
        },
        
        /**
         * Verificar si es desktop
         */
        isDesktop: () => {
            return window.innerWidth > 1024;
        }
    };
    
})(jQuery);
