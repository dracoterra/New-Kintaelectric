/**
 * JavaScript mejorado para UI/UX
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        initUIEnhancements();
    });
    
    /**
     * Inicializar mejoras de UI
     */
    function initUIEnhancements() {
        // Inicializar componentes
        initModals();
        initTooltips();
        initAlerts();
        initProgressBars();
        initSpinners();
        initFormEnhancements();
        initCardInteractions();
        initButtonEnhancements();
        initResponsiveFeatures();
        
        // Inicializar funcionalidades específicas
        initCurrencyConverter();
        initProductQuickInfo();
        initCartEnhancements();
        initCheckoutEnhancements();
    }
    
    /**
     * Inicializar modales
     */
    function initModals() {
        // Abrir modal
        $(document).on('click', '[data-wvp-modal]', function(e) {
            e.preventDefault();
            var modalId = $(this).data('wvp-modal');
            openModal(modalId);
        });
        
        // Cerrar modal
        $(document).on('click', '.wvp-modal-close, .wvp-modal', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Cerrar modal con Escape
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    }
    
    /**
     * Abrir modal
     */
    function openModal(modalId) {
        var modal = $('#' + modalId);
        if (modal.length) {
            modal.addClass('show').fadeIn(300);
            $('body').addClass('wvp-modal-open');
        }
    }
    
    /**
     * Cerrar modal
     */
    function closeModal() {
        $('.wvp-modal.show').removeClass('show').fadeOut(300);
        $('body').removeClass('wvp-modal-open');
    }
    
    /**
     * Inicializar tooltips
     */
    function initTooltips() {
        $('[data-wvp-tooltip]').each(function() {
            var $this = $(this);
            var tooltipText = $this.data('wvp-tooltip');
            
            $this.append('<span class="wvp-tooltip-text">' + tooltipText + '</span>');
        });
    }
    
    /**
     * Inicializar alertas
     */
    function initAlerts() {
        // Auto-ocultar alertas
        $('.wvp-alert[data-auto-hide]').each(function() {
            var $alert = $(this);
            var delay = $alert.data('auto-hide') || 5000;
            
            setTimeout(function() {
                $alert.fadeOut(300, function() {
                    $alert.remove();
                });
            }, delay);
        });
        
        // Cerrar alertas
        $(document).on('click', '.wvp-alert-close', function() {
            $(this).closest('.wvp-alert').fadeOut(300, function() {
                $(this).remove();
            });
        });
    }
    
    /**
     * Inicializar barras de progreso
     */
    function initProgressBars() {
        $('.wvp-progress-bar[data-animate]').each(function() {
            var $bar = $(this);
            var width = $bar.data('width') || 0;
            
            setTimeout(function() {
                $bar.css('width', width + '%');
            }, 100);
        });
    }
    
    /**
     * Inicializar spinners
     */
    function initSpinners() {
        // Mostrar spinner en botones
        $(document).on('click', '.wvp-btn[data-loading]', function() {
            var $btn = $(this);
            var originalText = $btn.text();
            
            $btn.prop('disabled', true)
                .html('<span class="wvp-spinner wvp-spinner-sm"></span> Cargando...');
            
            // Simular carga (remover en implementación real)
            setTimeout(function() {
                $btn.prop('disabled', false).text(originalText);
            }, 2000);
        });
    }
    
    /**
     * Inicializar mejoras de formularios
     */
    function initFormEnhancements() {
        // Validación en tiempo real
        $('.wvp-form-control[data-validate]').on('blur', function() {
            validateField($(this));
        });
        
        // Mejorar selects
        $('.wvp-form-control[type="select"]').each(function() {
            enhanceSelect($(this));
        });
        
        // Mejorar checkboxes y radios
        $('.wvp-form-control[type="checkbox"], .wvp-form-control[type="radio"]').each(function() {
            enhanceCheckboxRadio($(this));
        });
    }
    
    /**
     * Validar campo
     */
    function validateField($field) {
        var value = $field.val();
        var rules = $field.data('validate');
        var isValid = true;
        var errorMessage = '';
        
        if (rules.includes('required') && !value) {
            isValid = false;
            errorMessage = 'Este campo es obligatorio';
        }
        
        if (rules.includes('email') && value && !isValidEmail(value)) {
            isValid = false;
            errorMessage = 'Ingrese un email válido';
        }
        
        if (rules.includes('phone') && value && !isValidPhone(value)) {
            isValid = false;
            errorMessage = 'Ingrese un teléfono válido';
        }
        
        if (rules.includes('cedula') && value && !isValidCedula(value)) {
            isValid = false;
            errorMessage = 'Ingrese una cédula válida';
        }
        
        // Mostrar/ocultar error
        var $error = $field.siblings('.wvp-form-error');
        if (isValid) {
            $field.removeClass('is-invalid').addClass('is-valid');
            $error.remove();
        } else {
            $field.removeClass('is-valid').addClass('is-invalid');
            if ($error.length) {
                $error.text(errorMessage);
            } else {
                $field.after('<div class="wvp-form-error">' + errorMessage + '</div>');
            }
        }
        
        return isValid;
    }
    
    /**
     * Validar email
     */
    function isValidEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    /**
     * Validar teléfono
     */
    function isValidPhone(phone) {
        var re = /^(\+58|0)?[0-9]{10}$/;
        return re.test(phone);
    }
    
    /**
     * Validar cédula
     */
    function isValidCedula(cedula) {
        var re = /^[VEJPG]-?\d{7,9}$/i;
        return re.test(cedula);
    }
    
    /**
     * Mejorar select
     */
    function enhanceSelect($select) {
        // Implementar select personalizado si es necesario
    }
    
    /**
     * Mejorar checkbox y radio
     */
    function enhanceCheckboxRadio($input) {
        // Implementar estilos personalizados si es necesario
    }
    
    /**
     * Inicializar interacciones de tarjetas
     */
    function initCardInteractions() {
        // Efecto hover en tarjetas
        $('.wvp-card').hover(
            function() {
                $(this).addClass('wvp-card-hover');
            },
            function() {
                $(this).removeClass('wvp-card-hover');
            }
        );
        
        // Click en tarjetas
        $('.wvp-card[data-clickable]').on('click', function() {
            var url = $(this).data('url');
            if (url) {
                window.location.href = url;
            }
        });
    }
    
    /**
     * Inicializar mejoras de botones
     */
    function initButtonEnhancements() {
        // Efecto ripple
        $('.wvp-btn').on('click', function(e) {
            var $btn = $(this);
            var $ripple = $('<span class="wvp-ripple"></span>');
            var rect = this.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height);
            var x = e.clientX - rect.left - size / 2;
            var y = e.clientY - rect.top - size / 2;
            
            $ripple.css({
                width: size + 'px',
                height: size + 'px',
                left: x + 'px',
                top: y + 'px'
            });
            
            $btn.append($ripple);
            
            setTimeout(function() {
                $ripple.remove();
            }, 600);
        });
    }
    
    /**
     * Inicializar características responsivas
     */
    function initResponsiveFeatures() {
        // Menú móvil
        $('.wvp-mobile-menu-toggle').on('click', function() {
            $('.wvp-mobile-menu').toggleClass('show');
        });
        
        // Cerrar menú móvil al hacer click fuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.wvp-mobile-menu, .wvp-mobile-menu-toggle').length) {
                $('.wvp-mobile-menu').removeClass('show');
            }
        });
        
        // Ajustar altura de elementos
        $(window).on('resize', function() {
            adjustElementHeights();
        });
        
        adjustElementHeights();
    }
    
    /**
     * Ajustar altura de elementos
     */
    function adjustElementHeights() {
        // Ajustar altura de tarjetas en la misma fila
        $('.wvp-row').each(function() {
            var $cards = $(this).find('.wvp-card');
            var maxHeight = 0;
            
            $cards.each(function() {
                var height = $(this).outerHeight();
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });
            
            $cards.css('min-height', maxHeight + 'px');
        });
    }
    
    /**
     * Inicializar convertidor de moneda
     */
    function initCurrencyConverter() {
        $('.wvp-currency-converter').each(function() {
            var $converter = $(this);
            var $amount = $converter.find('[data-amount]');
            var $from = $converter.find('[data-from]');
            var $to = $converter.find('[data-to]');
            var $result = $converter.find('[data-result]');
            
            function convert() {
                var amount = parseFloat($amount.val()) || 0;
                var from = $from.val();
                var to = $to.val();
                
                if (amount > 0) {
                    // Simular conversión (implementar lógica real)
                    var rate = getExchangeRate(from, to);
                    var result = amount * rate;
                    
                    $result.text(formatCurrency(result, to));
                }
            }
            
            $amount.on('input', convert);
            $from.on('change', convert);
            $to.on('change', convert);
        });
    }
    
    /**
     * Obtener tasa de cambio
     */
    function getExchangeRate(from, to) {
        // Implementar lógica real de obtención de tasas
        if (from === 'USD' && to === 'VES') {
            return 36.0; // Tasa de ejemplo
        } else if (from === 'VES' && to === 'USD') {
            return 1 / 36.0;
        }
        return 1;
    }
    
    /**
     * Formatear moneda
     */
    function formatCurrency(amount, currency) {
        if (currency === 'USD') {
            return '$' + amount.toFixed(2);
        } else if (currency === 'VES') {
            return 'Bs. ' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        return amount.toFixed(2);
    }
    
    /**
     * Inicializar información rápida de productos
     */
    function initProductQuickInfo() {
        $('.wvp-quick-info-btn').on('click', function() {
            var productId = $(this).data('product-id');
            showProductQuickInfo(productId);
        });
    }
    
    /**
     * Mostrar información rápida de producto
     */
    function showProductQuickInfo(productId) {
        // Mostrar modal de carga
        showLoadingModal();
        
        // Obtener información del producto
        $.ajax({
            url: wvp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_get_product_info',
                product_id: productId,
                nonce: wvp_ajax.nonce
            },
            success: function(response) {
                hideLoadingModal();
                
                if (response.success) {
                    showProductInfoModal(response.data);
                } else {
                    showErrorModal('Error al cargar información del producto');
                }
            },
            error: function() {
                hideLoadingModal();
                showErrorModal('Error de conexión');
            }
        });
    }
    
    /**
     * Mostrar modal de información de producto
     */
    function showProductInfoModal(productInfo) {
        var modalHtml = '<div class="wvp-modal" id="product-info-modal">' +
            '<div class="wvp-modal-dialog">' +
            '<div class="wvp-modal-content">' +
            '<div class="wvp-modal-header">' +
            '<h4 class="wvp-modal-title">' + productInfo.name + '</h4>' +
            '<button class="wvp-modal-close">&times;</button>' +
            '</div>' +
            '<div class="wvp-modal-body">' +
            '<p><strong>Precio USD:</strong> $' + productInfo.price_usd.toFixed(2) + '</p>' +
            '<p><strong>Precio VES:</strong> ' + formatCurrency(productInfo.price_ves, 'VES') + '</p>' +
            '<p><strong>Tasa BCV:</strong> ' + productInfo.rate.toFixed(2) + ' Bs./USD</p>' +
            '<p><strong>Stock:</strong> ' + productInfo.stock_status + '</p>' +
            '</div>' +
            '<div class="wvp-modal-footer">' +
            '<button class="wvp-btn wvp-btn-primary" onclick="closeModal()">Cerrar</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        $('body').append(modalHtml);
        openModal('product-info-modal');
    }
    
    /**
     * Inicializar mejoras del carrito
     */
    function initCartEnhancements() {
        // Actualizar cantidad
        $('.wvp-cart-quantity').on('change', function() {
            var $input = $(this);
            var quantity = parseInt($input.val());
            var productId = $input.data('product-id');
            
            if (quantity > 0) {
                updateCartQuantity(productId, quantity);
            }
        });
        
        // Eliminar producto
        $('.wvp-cart-remove').on('click', function() {
            var $btn = $(this);
            var productId = $btn.data('product-id');
            
            if (confirm('¿Está seguro de que desea eliminar este producto?')) {
                removeFromCart(productId);
            }
        });
    }
    
    /**
     * Actualizar cantidad en carrito
     */
    function updateCartQuantity(productId, quantity) {
        // Implementar actualización de cantidad
    }
    
    /**
     * Eliminar del carrito
     */
    function removeFromCart(productId) {
        // Implementar eliminación del carrito
    }
    
    /**
     * Inicializar mejoras del checkout
     */
    function initCheckoutEnhancements() {
        // Validar formulario antes de enviar
        $('.wvp-checkout-form').on('submit', function(e) {
            if (!validateCheckoutForm()) {
                e.preventDefault();
            }
        });
        
        // Calcular envío
        $('.wvp-shipping-calculator').on('submit', function(e) {
            e.preventDefault();
            calculateShipping();
        });
    }
    
    /**
     * Validar formulario de checkout
     */
    function validateCheckoutForm() {
        var isValid = true;
        
        $('.wvp-checkout-form .wvp-form-control[data-validate]').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    /**
     * Calcular envío
     */
    function calculateShipping() {
        var postcode = $('[name="postcode"]').val();
        var city = $('[name="city"]').val();
        var state = $('[name="state"]').val();
        
        if (!postcode || !city || !state) {
            showErrorModal('Por favor complete todos los campos de envío');
            return;
        }
        
        showLoadingModal();
        
        $.ajax({
            url: wvp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wvp_calculate_shipping',
                postcode: postcode,
                city: city,
                state: state,
                nonce: wvp_ajax.nonce
            },
            success: function(response) {
                hideLoadingModal();
                
                if (response.success) {
                    showShippingInfo(response.data);
                } else {
                    showErrorModal('Error al calcular envío');
                }
            },
            error: function() {
                hideLoadingModal();
                showErrorModal('Error de conexión');
            }
        });
    }
    
    /**
     * Mostrar información de envío
     */
    function showShippingInfo(shippingInfo) {
        var infoHtml = '<div class="wvp-shipping-info">' +
            '<h4>Información de Envío</h4>' +
            '<p><strong>Costo:</strong> $' + shippingInfo.cost.toFixed(2) + '</p>' +
            '<p><strong>Tiempo estimado:</strong> ' + shippingInfo.estimated_days + ' días</p>' +
            '<p><strong>Ciudad:</strong> ' + shippingInfo.city + '</p>' +
            '<p><strong>Estado:</strong> ' + shippingInfo.state + '</p>' +
            '</div>';
        
        $('.wvp-shipping-results').html(infoHtml);
    }
    
    /**
     * Mostrar modal de carga
     */
    function showLoadingModal() {
        var modalHtml = '<div class="wvp-modal show" id="loading-modal">' +
            '<div class="wvp-modal-dialog">' +
            '<div class="wvp-modal-content">' +
            '<div class="wvp-modal-body wvp-text-center">' +
            '<div class="wvp-spinner wvp-spinner-lg"></div>' +
            '<p>Cargando...</p>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        $('body').append(modalHtml);
    }
    
    /**
     * Ocultar modal de carga
     */
    function hideLoadingModal() {
        $('#loading-modal').remove();
    }
    
    /**
     * Mostrar modal de error
     */
    function showErrorModal(message) {
        var modalHtml = '<div class="wvp-modal show" id="error-modal">' +
            '<div class="wvp-modal-dialog">' +
            '<div class="wvp-modal-content">' +
            '<div class="wvp-modal-header">' +
            '<h4 class="wvp-modal-title">Error</h4>' +
            '<button class="wvp-modal-close">&times;</button>' +
            '</div>' +
            '<div class="wvp-modal-body">' +
            '<p>' + message + '</p>' +
            '</div>' +
            '<div class="wvp-modal-footer">' +
            '<button class="wvp-btn wvp-btn-primary" onclick="closeModal()">Cerrar</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        $('body').append(modalHtml);
    }
    
    // Exponer funciones globales
    window.openModal = openModal;
    window.closeModal = closeModal;
    
})(jQuery);
