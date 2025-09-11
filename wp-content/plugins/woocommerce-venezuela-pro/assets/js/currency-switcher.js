/**
 * JavaScript para el switcher de moneda
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        initCurrencySwitcher();
    });
    
    /**
     * Inicializar el switcher de moneda
     */
    function initCurrencySwitcher() {
        // Manejar cambio de moneda
        $('.wvp-currency-switcher input[name="wvp_currency"]').on('change', function() {
            handleCurrencyChange($(this));
        });
        
        // Aplicar preferencia guardada
        applySavedCurrencyPreference();
    }
    
    /**
     * Manejar cambio de moneda
     */
    function handleCurrencyChange($input) {
        var currency = $input.val();
        var switcher = $input.closest('.wvp-currency-switcher');
        
        // Validar moneda
        if (!currency || !['USD', 'VES'].includes(currency)) {
            console.error('Moneda inválida:', currency);
            return;
        }
        
        // Mostrar loading
        switcher.addClass('loading');
        
        // Guardar preferencia
        saveCurrencyPreference(currency, function(success) {
            switcher.removeClass('loading');
            
            if (success) {
                // Actualizar display
                updateCurrencyDisplay(switcher, currency);
                
                // Mostrar mensaje de éxito
                showMessage('Moneda cambiada a ' + currency, 'success');
                
                // Recargar página después de un breve delay
                setTimeout(function() {
                    location.reload();
                }, 500);
            } else {
                // Mostrar mensaje de error
                showMessage('Error al cambiar moneda', 'error');
            }
        });
    }
    
    /**
     * Guardar preferencia de moneda
     */
    function saveCurrencyPreference(currency, callback) {
        $.ajax({
            url: wvp_currency.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_save_currency_preference',
                currency: currency,
                nonce: wvp_currency.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Guardar en localStorage como backup
                    localStorage.setItem('wvp_currency', currency);
                    callback(true);
                } else {
                    console.error('Error al guardar preferencia:', response.data);
                    callback(false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                callback(false);
            }
        });
    }
    
    /**
     * Actualizar display de moneda
     */
    function updateCurrencyDisplay(switcher, currency) {
        // Remover clase active de todas las opciones
        switcher.find('.wvp-currency-option').removeClass('active');
        
        // Añadir clase active a la opción seleccionada
        switcher.find('input[value="' + currency + '"]').closest('.wvp-currency-option').addClass('active');
        
        // Actualizar precios si es necesario
        updatePrices(switcher, currency);
    }
    
    /**
     * Actualizar precios según la moneda seleccionada
     */
    function updatePrices(switcher, currency) {
        var priceUsd = parseFloat(switcher.data('price-usd'));
        var priceVes = parseFloat(switcher.data('price-ves'));
        var rate = parseFloat(switcher.data('rate'));
        
        if (!priceUsd || !rate) {
            return;
        }
        
        // Recalcular precio VES si es necesario
        if (!priceVes) {
            priceVes = priceUsd * rate;
        }
        
        // Actualizar precios en el display
        switcher.find('.wvp-currency-price').each(function() {
            var $price = $(this);
            var $option = $price.closest('.wvp-currency-option');
            var optionCurrency = $option.find('input[name="wvp_currency"]').val();
            
            if (optionCurrency === 'USD') {
                $price.text(formatPrice(priceUsd, 'USD'));
            } else if (optionCurrency === 'VES') {
                $price.text(formatPrice(priceVes, 'VES'));
            }
        });
    }
    
    /**
     * Formatear precio según la moneda
     */
    function formatPrice(price, currency) {
        if (currency === 'USD') {
            return '$' + parseFloat(price).toFixed(2);
        } else if (currency === 'VES') {
            return formatVesPrice(price);
        }
        return price;
    }
    
    /**
     * Formatear precio en VES
     */
    function formatVesPrice(price) {
        var formatted = parseFloat(price).toFixed(2);
        formatted = formatted.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return 'Bs. ' + formatted;
    }
    
    /**
     * Aplicar preferencia guardada
     */
    function applySavedCurrencyPreference() {
        var savedCurrency = localStorage.getItem('wvp_currency');
        if (savedCurrency && ['USD', 'VES'].includes(savedCurrency)) {
            $('.wvp-currency-switcher input[value="' + savedCurrency + '"]').prop('checked', true);
            $('.wvp-currency-switcher input[value="' + savedCurrency + '"]').closest('.wvp-currency-option').addClass('active');
        }
    }
    
    /**
     * Mostrar mensaje
     */
    function showMessage(message, type) {
        // Remover mensajes existentes
        $('.wvp-currency-message').remove();
        
        // Crear mensaje
        var $message = $('<div class="wvp-currency-message wvp-message-' + type + '">' + message + '</div>');
        
        // Añadir estilos
        $message.css({
            'position': 'fixed',
            'top': '20px',
            'right': '20px',
            'padding': '10px 15px',
            'border-radius': '4px',
            'color': '#fff',
            'font-weight': 'bold',
            'z-index': '9999',
            'opacity': '0',
            'transition': 'opacity 0.3s ease'
        });
        
        if (type === 'success') {
            $message.css('background-color', '#28a745');
        } else if (type === 'error') {
            $message.css('background-color', '#dc3545');
        }
        
        // Añadir al DOM
        $('body').append($message);
        
        // Mostrar mensaje
        setTimeout(function() {
            $message.css('opacity', '1');
        }, 100);
        
        // Ocultar mensaje después de 3 segundos
        setTimeout(function() {
            $message.css('opacity', '0');
            setTimeout(function() {
                $message.remove();
            }, 300);
        }, 3000);
    }
    
    /**
     * Manejar errores de red
     */
    $(document).ajaxError(function(event, xhr, settings) {
        if (settings.url && settings.url.includes('wvp_save_currency_preference')) {
            showMessage('Error de conexión. Intente nuevamente.', 'error');
        }
    });
    
    /**
     * Validar datos antes de enviar
     */
    function validateCurrencyData(currency) {
        if (!currency) {
            console.error('Moneda no especificada');
            return false;
        }
        
        if (!['USD', 'VES'].includes(currency)) {
            console.error('Moneda inválida:', currency);
            return false;
        }
        
        return true;
    }
    
    /**
     * Debug mode
     */
    if (window.location.search.includes('wvp_debug=1')) {
        console.log('WVP Currency Switcher Debug Mode');
        console.log('Current currency:', wvp_currency.current_currency);
        console.log('AJAX URL:', wvp_currency.ajax_url);
        console.log('Nonce:', wvp_currency.nonce);
    }
    
})(jQuery);