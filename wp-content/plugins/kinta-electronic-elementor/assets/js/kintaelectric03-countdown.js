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

            // Construir el HTML del countdown con animación suave
            var countdownHTML = '';

            if (daysLeft > 0) {
                countdownHTML += '<span class="days">' +
                    '<span class="value" data-value="' + daysLeft + '">' + daysLeft + '</span>' +
                    '<b>' + kintaelectric03Countdown.texts.days + '</b>' +
                    '</span>';
            }

            if (hoursLeft > 0 || daysLeft > 0) {
                countdownHTML += '<span class="hours">' +
                    '<span class="value" data-value="' + hoursLeft + '">' + hoursLeft + '</span>' +
                    '<b>' + kintaelectric03Countdown.texts.hours + '</b>' +
                    '</span>';
            }

            if (minutesLeft > 0 || hoursLeft > 0 || daysLeft > 0) {
                countdownHTML += '<span class="minutes">' +
                    '<span class="value" data-value="' + minutesLeft + '">' + minutesLeft + '</span>' +
                    '<b>' + kintaelectric03Countdown.texts.mins + '</b>' +
                    '</span>';
            }

            countdownHTML += '<span class="seconds">' +
                '<span class="value" data-value="' + secondsLeft + '">' + secondsLeft + '</span>' +
                '<b>' + kintaelectric03Countdown.texts.secs + '</b>' +
                '</span>';

            // Solo actualizar si hay cambios para evitar parpadeos
            var currentHTML = $countdown.html();
            if (currentHTML !== countdownHTML) {
                $countdown.html(countdownHTML);
                
                // Aplicar animación suave solo a los valores que cambiaron
                $countdown.find('.value').each(function() {
                    var $this = $(this);
                    var newValue = $this.data('value');
                    var currentValue = $this.text();
                    
                    if (currentValue !== newValue.toString()) {
                        $this.addClass('countdown-changing');
                        setTimeout(function() {
                            $this.removeClass('countdown-changing');
                        }, 300);
                    }
                });
            }
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

})(jQuery);
