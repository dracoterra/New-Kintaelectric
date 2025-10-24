/**
 * JavaScript para Aplicación Dinámica de Estilos - WooCommerce Venezuela Pro
 * Aplica estilos dinámicamente según las configuraciones de apariencia
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Clase para aplicación dinámica de estilos
     */
    class WVP_Appearance_Dynamic {
        constructor() {
            this.settings = null;
            this.init();
        }
        
        /**
         * Inicializar
         */
        init() {
            this.loadSettings();
            this.applyDynamicStyles();
            this.bindEvents();
        }
        
        /**
         * Cargar configuraciones
         */
        loadSettings() {
            // Cargar configuraciones desde AJAX
            $.ajax({
                url: wvp_appearance_dynamic.ajax_url,
                type: 'POST',
                data: {
                    action: 'wvp_get_appearance_settings',
                    nonce: wvp_appearance_dynamic.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.settings = response.data;
                        this.applyDynamicStyles();
                    }
                },
                error: () => {
                    // Fallback con configuraciones por defecto
                    this.settings = {
                        display_style: 'minimal',
                        primary_color: '#007cba',
                        secondary_color: '#005a87',
                        success_color: '#28a745',
                        warning_color: '#ffc107',
                        font_family: 'system',
                        font_size: 'medium',
                        font_weight: '400',
                        text_transform: 'none',
                        padding: 'medium',
                        margin: 'medium',
                        border_radius: 'medium',
                        shadow: 'small'
                    };
                    this.applyDynamicStyles();
                }
            });
        }
        
        /**
         * Aplicar estilos dinámicos
         */
        applyDynamicStyles() {
            if (!this.settings) return;
            
            // Crear o actualizar estilos dinámicos
            this.createDynamicCSS();
            
            // Aplicar clases de estilo
            this.applyStyleClasses();
            
            // Aplicar colores dinámicos
            this.applyDynamicColors();
            
            // Aplicar fuentes dinámicas
            this.applyDynamicFonts();
            
            // Aplicar espaciado dinámico
            this.applyDynamicSpacing();
        }
        
        /**
         * Crear CSS dinámico
         */
        createDynamicCSS() {
            let dynamicCSS = '';
            
            // Variables CSS
            dynamicCSS += ':root {';
            dynamicCSS += `--wvp-primary-color: ${this.settings.primary_color};`;
            dynamicCSS += `--wvp-secondary-color: ${this.settings.secondary_color};`;
            dynamicCSS += `--wvp-success-color: ${this.settings.success_color};`;
            dynamicCSS += `--wvp-warning-color: ${this.settings.warning_color};`;
            
            // Convertir colores a RGB para transparencias
            const primaryRGB = this.hexToRgb(this.settings.primary_color);
            if (primaryRGB) {
                dynamicCSS += `--wvp-primary-color-rgb: ${primaryRGB.r}, ${primaryRGB.g}, ${primaryRGB.b};`;
            }
            
            dynamicCSS += '}';
            
            // Aplicar CSS dinámico
            this.injectCSS(dynamicCSS);
        }
        
        /**
         * Aplicar clases de estilo
         */
        applyStyleClasses() {
            // Remover clases de estilo anteriores
            $('.wvp-product-price-container').removeClass('wvp-minimal wvp-modern wvp-elegant wvp-compact wvp-vintage wvp-futuristic wvp-advanced-minimal');
            
            // Aplicar nueva clase de estilo
            $('.wvp-product-price-container').addClass('wvp-' + this.settings.display_style);
        }
        
        /**
         * Aplicar colores dinámicos
         */
        applyDynamicColors() {
            // Aplicar colores a elementos específicos
            $('.wvp-currency-switcher button.active, .wvp-currency-switcher .wvp-currency-option.active').css({
                'background-color': this.settings.primary_color,
                'border-color': this.settings.primary_color,
                'color': '#ffffff'
            });
            
            $('.wvp-price-ves').css('color', this.settings.secondary_color);
            
            $('.wvp-price-conversion').css({
                'background-color': this.addAlpha(this.settings.primary_color, 0.1),
                'border-color': this.settings.primary_color,
                'color': this.settings.secondary_color
            });
            
            $('.wvp-rate-info').css('color', this.settings.secondary_color);
        }
        
        /**
         * Aplicar fuentes dinámicas
         */
        applyDynamicFonts() {
            const fontFamilies = {
                'system': '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                'arial': 'Arial, sans-serif',
                'helvetica': 'Helvetica, Arial, sans-serif',
                'georgia': 'Georgia, serif',
                'times': '"Times New Roman", Times, serif',
                'verdana': 'Verdana, sans-serif',
                'tahoma': 'Tahoma, sans-serif',
                'trebuchet': '"Trebuchet MS", sans-serif',
                'courier': '"Courier New", monospace',
                'monospace': 'monospace'
            };
            
            const fontSizes = {
                'small': '12px',
                'medium': '14px',
                'large': '16px',
                'xlarge': '18px',
                'xxlarge': '20px'
            };
            
            const fontStyles = {
                'font-family': fontFamilies[this.settings.font_family] || fontFamilies['system'],
                'font-size': fontSizes[this.settings.font_size] || fontSizes['medium'],
                'font-weight': this.settings.font_weight,
                'text-transform': this.settings.text_transform
            };
            
            $('.wvp-price-usd, .wvp-price-ves').css(fontStyles);
            $('.wvp-currency-switcher button, .wvp-currency-switcher .wvp-currency-option').css({
                'font-family': fontStyles['font-family'],
                'font-weight': this.settings.font_weight,
                'text-transform': this.settings.text_transform
            });
        }
        
        /**
         * Aplicar espaciado dinámico
         */
        applyDynamicSpacing() {
            const paddings = {
                'none': '0px',
                'small': '5px',
                'medium': '10px',
                'large': '15px',
                'xlarge': '20px'
            };
            
            const margins = {
                'none': '0px',
                'small': '5px',
                'medium': '10px',
                'large': '15px',
                'xlarge': '20px'
            };
            
            const borderRadiuses = {
                'none': '0px',
                'small': '3px',
                'medium': '6px',
                'large': '12px',
                'xlarge': '20px',
                'round': '50px'
            };
            
            const shadows = {
                'none': 'none',
                'small': '0 2px 4px rgba(0,0,0,0.1)',
                'medium': '0 4px 8px rgba(0,0,0,0.15)',
                'large': '0 8px 16px rgba(0,0,0,0.2)',
                'glow': '0 0 10px rgba(0,115,170,0.3)'
            };
            
            $('.wvp-price-conversion').css({
                'padding': paddings[this.settings.padding] || paddings['medium'],
                'margin': margins[this.settings.margin] || margins['medium'],
                'border-radius': borderRadiuses[this.settings.border_radius] || borderRadiuses['medium'],
                'box-shadow': shadows[this.settings.shadow] || shadows['small']
            });
            
            $('.wvp-product-price-container').css({
                'margin': margins[this.settings.margin] || margins['medium']
            });
        }
        
        /**
         * Vincular eventos
         */
        bindEvents() {
            // Escuchar cambios en configuraciones
            $(document).on('wvp:appearanceSettingsChanged', (e, newSettings) => {
                this.settings = newSettings;
                this.applyDynamicStyles();
            });
            
            // Escuchar cambios de moneda para aplicar estilos
            $(document).on('wvp:currencyChanged', (e, currency) => {
                this.updateCurrencyStyles(currency);
            });
        }
        
        /**
         * Actualizar estilos según moneda
         */
        updateCurrencyStyles(currency) {
            if (currency === 'VES') {
                $('.wvp-price-conversion').fadeOut(200);
                $('.wvp-rate-info').fadeOut(200);
            } else {
                $('.wvp-price-conversion').fadeIn(200);
                $('.wvp-rate-info').fadeIn(200);
            }
        }
        
        /**
         * Inyectar CSS dinámico
         */
        injectCSS(css) {
            // Remover CSS anterior
            $('#wvp-dynamic-styles').remove();
            
            // Crear nuevo elemento de estilo
            $('<style id="wvp-dynamic-styles">' + css + '</style>').appendTo('head');
        }
        
        /**
         * Convertir hex a RGB
         */
        hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        }
        
        /**
         * Agregar transparencia a color
         */
        addAlpha(color, alpha) {
            const rgb = this.hexToRgb(color);
            if (rgb) {
                return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${alpha})`;
            }
            return color;
        }
        
        /**
         * Actualizar configuraciones
         */
        updateSettings(newSettings) {
            this.settings = { ...this.settings, ...newSettings };
            this.applyDynamicStyles();
        }
        
        /**
         * Obtener configuraciones actuales
         */
        getSettings() {
            return this.settings;
        }
    }
    
    // Inicializar cuando el documento esté listo
    $(document).ready(() => {
        if (typeof wvp_appearance_dynamic !== 'undefined') {
            window.wvpAppearanceDynamic = new WVP_Appearance_Dynamic();
        }
    });
    
})(jQuery);
