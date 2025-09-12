/**
 * Kintaelectric03 Countdown Timer
 * 
 * JavaScript funcional para el countdown timer del widget de ofertas
 *
 * @package kinta-electric-elementor
 */

(function($) {
    'use strict';

    // Inicializar countdown cuando el documento esté listo
    $(document).ready(function() {
        initCountdownTimers();
    });

    // Reinicializar cuando se cargan nuevos elementos (Elementor)
    $(window).on('elementor/frontend/init', function() {
        initCountdownTimers();
    });

    function initCountdownTimers() {
        $('.deal-countdown-timer .deal-countdown').each(function() {
            var $countdown = $(this);
            var days = parseInt($countdown.data('days')) || 7;
            
            // Solo inicializar si no se ha inicializado antes
            if (!$countdown.hasClass('countdown-initialized')) {
                startCountdown($countdown, days);
                $countdown.addClass('countdown-initialized');
            }
        });
    }

    function startCountdown($countdown, days) {
        // Calcular la fecha de finalización
        var endDate = new Date();
        endDate.setDate(endDate.getDate() + days);
        endDate.setHours(23, 59, 59, 999); // Final del día

        // Función para actualizar el countdown
        function updateCountdown() {
            var now = new Date().getTime();
            var distance = endDate.getTime() - now;

            if (distance < 0) {
                // El countdown ha terminado
                $countdown.html('<span class="countdown-expired">' + 
                    kintaelectric03Countdown.texts.expired + '</span>');
                return;
            }

            // Calcular días, horas, minutos y segundos
            var daysLeft = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hoursLeft = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutesLeft = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var secondsLeft = Math.floor((distance % (1000 * 60)) / 1000);

            // Construir el HTML del countdown simple - sin animaciones
            var countdownHTML = '';

            if (daysLeft > 0) {
                countdownHTML += '<span class="days">' +
                    '<span class="value">' + daysLeft + '</span>' +
                    '<b>' + kintaelectric03Countdown.texts.days + '</b>' +
                    '</span>';
            }

            if (hoursLeft > 0 || daysLeft > 0) {
                countdownHTML += '<span class="hours">' +
                    '<span class="value">' + hoursLeft + '</span>' +
                    '<b>' + kintaelectric03Countdown.texts.hours + '</b>' +
                    '</span>';
            }

            if (minutesLeft > 0 || hoursLeft > 0 || daysLeft > 0) {
                countdownHTML += '<span class="minutes">' +
                    '<span class="value">' + minutesLeft + '</span>' +
                    '<b>' + kintaelectric03Countdown.texts.mins + '</b>' +
                    '</span>';
            }

            countdownHTML += '<span class="seconds">' +
                '<span class="value">' + secondsLeft + '</span>' +
                '<b>' + kintaelectric03Countdown.texts.secs + '</b>' +
                '</span>';

            // Actualizar sin animaciones
            $countdown.html(countdownHTML);
        }

        // Actualizar inmediatamente
        updateCountdown();

        // Actualizar cada segundo
        var countdownInterval = setInterval(updateCountdown, 1000);

        // Limpiar el intervalo cuando el elemento se elimine del DOM
        $countdown.on('remove', function() {
            clearInterval(countdownInterval);
        });
    }

    // Función para reinicializar countdowns en elementos específicos
    window.kintaelectric03ReinitCountdown = function($container) {
        if ($container) {
            $container.find('.deal-countdown-timer .deal-countdown').each(function() {
                var $countdown = $(this);
                var days = parseInt($countdown.data('days')) || 7;
                
                // Limpiar estado anterior
                $countdown.removeClass('countdown-initialized');
                $countdown.off('remove');
                
                // Reinicializar
                startCountdown($countdown, days);
                $countdown.addClass('countdown-initialized');
            });
        }
    };

    // Manejo de errores para YITH Wishlist y Compare
    $(document).ready(function() {
        // Interceptar errores de AJAX
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            if (settings.url && settings.url.includes('admin-ajax.php')) {
                console.log('Error AJAX detectado:', {
                    url: settings.url,
                    status: xhr.status,
                    error: thrownError
                });
            }
        });
        
        // Manejo de botones YITH con nonce correcto
        $(document).on('click', '.add_to_wishlist', function(e) {
            e.preventDefault();
            var $button = $(this);
            var productId = $button.data('product-id');
            var nonce = $button.data('nonce');
            
            if (productId && nonce) {
                // Enviar petición AJAX con nonce correcto
                jQuery.ajax({
                    url: kintaelectric03Countdown.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'add_to_wishlist',
                        product_id: productId,
                        nonce: nonce
                    },
                    dataType: 'json',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    }
                })
                .done(function(response) {
                    console.log('Wishlist AJAX exitoso:', response);
                    if (response.result) {
                        $button.text('✓ Añadido').addClass('added');
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Wishlist AJAX falló:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                });
            } else {
                console.log('Botón Wishlist sin datos necesarios:', $button.attr('class'));
            }
        });
        
        // Manejo de botones Compare
        $(document).on('click', '.compare', function(e) {
            e.preventDefault();
            var $button = $(this);
            var productId = $button.data('product-id');
            
            if (productId) {
                console.log('Botón Compare clickeado para producto:', productId);
                // Aquí se puede implementar la lógica de compare si es necesaria
            } else {
                console.log('Botón Compare sin product-id:', $button.attr('class'));
            }
        });
    });

})(jQuery);
