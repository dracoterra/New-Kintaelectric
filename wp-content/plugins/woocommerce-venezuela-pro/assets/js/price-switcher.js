/**
 * JavaScript para el switcher de moneda interactivo
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Verificar si jQuery está disponible
if (typeof jQuery === 'undefined') {
    console.error('WVP: jQuery no está disponible');
} else {
    console.log('WVP: jQuery disponible, inicializando switcher');
}

// Verificar si el script se está cargando
console.log('WVP: Script de switcher de moneda cargado correctamente');

jQuery(document).ready(function($) {
    'use strict';
    
    console.log('WVP: Document ready ejecutado');
    console.log('WVP: jQuery disponible:', typeof $ !== 'undefined');
    
    // Clave para localStorage
    const STORAGE_KEY = 'wvp_currency_preference';
    
    /**
     * Inicializar el switcher de moneda
     */
    function initCurrencySwitcher() {
        console.log('WVP: Inicializando switcher de moneda');
        
        // Aplicar preferencia guardada
        applySavedPreference();
        
        // Configurar event listeners
        setupEventListeners();
        
        console.log('WVP: Switcher de moneda inicializado correctamente');
    }
    
    /**
     * Aplicar preferencia guardada en localStorage
     */
    function applySavedPreference() {
        const savedCurrency = localStorage.getItem(STORAGE_KEY);
        if (savedCurrency && (savedCurrency === 'usd' || savedCurrency === 'ves')) {
            console.log('WVP: Aplicando preferencia guardada:', savedCurrency);
            switchToCurrency(savedCurrency);
        }
    }
    
    /**
     * Configurar event listeners para los botones
     */
    function setupEventListeners() {
        // Event listener para botón USD
        $(document).on('click', '.wvp-usd', function(e) {
            e.preventDefault();
            console.log('WVP: Click en botón USD');
            switchToCurrency('usd');
        });
        
        // Event listener para botón VES
        $(document).on('click', '.wvp-ves', function(e) {
            e.preventDefault();
            console.log('WVP: Click en botón VES');
            switchToCurrency('ves');
        });
    }
    
    /**
     * Cambiar a una moneda específica
     * 
     * @param {string} currency - 'usd' o 'ves'
     */
    function switchToCurrency(currency) {
        console.log('WVP: Cambiando a moneda:', currency);
        
        // Guardar preferencia en localStorage
        localStorage.setItem(STORAGE_KEY, currency);
        
        // Procesar todos los switchers de moneda
        const switchers = $('.wvp-currency-switcher');
        console.log('WVP: Encontrados', switchers.length, 'switchers');
        
        switchers.each(function() {
            const $switcher = $(this);
            const $usdButton = $switcher.find('.wvp-usd');
            const $vesButton = $switcher.find('.wvp-ves');
            
            // Buscar el precio original - puede estar antes o después del switcher
            let $priceElement = $switcher.prev('.price, .woocommerce-Price-amount, .amount');
            
            // Si no se encuentra antes, buscar después
            if ($priceElement.length === 0) {
                $priceElement = $switcher.next('.price, .woocommerce-Price-amount, .amount');
            }
            
            // Si aún no se encuentra, buscar en el contenedor padre
            if ($priceElement.length === 0) {
                $priceElement = $switcher.parent().find('.price, .woocommerce-Price-amount, .amount').first();
            }
            
            if ($priceElement.length === 0) {
                console.log('WVP: No se encontró elemento de precio');
                console.log('WVP: Switcher HTML:', $switcher[0].outerHTML);
                console.log('WVP: Elementos padre:', $switcher.parent().find('*').length);
                return;
            }
            
            console.log('WVP: Elemento de precio encontrado:', $priceElement[0].outerHTML);
            
            if (currency === 'usd') {
                // Mostrar precio original en USD
                const originalPrice = $priceElement.html();
                $priceElement.html(originalPrice);
                
                // Actualizar clases activas
                $usdButton.addClass('active');
                $vesButton.removeClass('active');
                
                console.log('WVP: Precio cambiado a USD');
            } else if (currency === 'ves') {
                // Mostrar precio en VES
                const vesPrice = $switcher.data('price-ves');
                $priceElement.html(vesPrice);
                
                // Actualizar clases activas
                $vesButton.addClass('active');
                $usdButton.removeClass('active');
                
                console.log('WVP: Precio cambiado a VES:', vesPrice);
            }
        });
    }
    
    /**
     * Verificar si hay switchers de moneda en la página
     */
    function checkForPriceContainers() {
        const switchers = $('.wvp-currency-switcher');
        console.log('WVP: Buscando switchers... Encontrados:', switchers.length);
        if (switchers.length > 0) {
            console.log('WVP: Encontrados', switchers.length, 'switchers de moneda');
            return true;
        }
        return false;
    }
    
    /**
     * Función de inicialización con retry
     */
    function initWithRetry() {
        if (checkForPriceContainers()) {
            initCurrencySwitcher();
        } else {
            // Retry después de 500ms si no se encuentran contenedores
            setTimeout(function() {
                if (checkForPriceContainers()) {
                    initCurrencySwitcher();
                } else {
                    console.log('WVP: No se encontraron contenedores de precio después del retry');
                }
            }, 500);
        }
    }
    
    // Inicializar cuando el DOM esté listo
    initWithRetry();
    
    // Reinicializar cuando se actualice el contenido (para AJAX)
    $(document.body).on('updated_wc_div', function() {
        console.log('WVP: Contenido actualizado, reinicializando switcher');
        initWithRetry();
    });
    
    // Reinicializar cuando se carguen los bloques de WooCommerce
    $(document.body).on('wc-blocks_checkout_updated', function() {
        console.log('WVP: Bloques de checkout actualizados, reinicializando switcher');
        initWithRetry();
    });
    
    // Reinicializar cuando se actualice el carrito
    $(document.body).on('updated_cart_totals', function() {
        console.log('WVP: Totales del carrito actualizados, reinicializando switcher');
        initWithRetry();
    });
    
    // Debug: Log de inicialización
    console.log('WVP: Script de switcher de moneda cargado');
});
