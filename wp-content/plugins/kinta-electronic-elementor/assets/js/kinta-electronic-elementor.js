/**
 * Kinta Electric Elementor - JavaScript del Slider
 * Versión completamente simplificada y estable
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // ==========================================================================
    // Slider Simple y Estable
    // ==========================================================================
    
    var KintaSlider = {
        
        /**
         * Inicializar slider
         */
        init: function() {
            this.initSlider();
        },
        
        /**
         * Inicializar el slider principal
         */
        initSlider: function() {
            var $sliders = $('.js-slick-carousel.u-slick.owl-carousel');
            
            if ($sliders.length === 0) {
                return;
            }
            
            // Verificar que Owl Carousel esté disponible
            if (typeof $.fn.owlCarousel === 'undefined') {
                console.warn('Owl Carousel no está disponible. Reintentando en 1000ms...');
                setTimeout(function() {
                    KintaSlider.initSlider();
                }, 1000);
                return;
            }
            
            // Inicializar cada slider
            $sliders.each(function() {
                var $slider = $(this);
                
                // Evitar inicializar múltiples veces
                if ($slider.hasClass('owl-loaded')) {
                    return;
                }
                
                // Configuración básica y estable
                var sliderConfig = {
                    items: 1,
                    loop: true,
                    nav: true,
                    dots: true,
                    autoplay: true,
                    autoplayTimeout: 5000,
                    autoplayHoverPause: true,
                    smartSpeed: 500,
                    navText: [
                        '<i class="fa fa-angle-left"></i>',
                        '<i class="fa fa-angle-right"></i>'
                    ],
                    responsive: {
                        0: {
                            items: 1,
                            nav: false,
                            dots: true
                        },
                        768: {
                            items: 1,
                            nav: true,
                            dots: true
                        }
                    },
                    onInitialized: function() {
                        // Configurar paginación personalizada
                        KintaSlider.setupCustomPagination($slider);
                    },
                    onChanged: function(event) {
                        // Actualizar clases activas en la paginación personalizada
                        KintaSlider.updatePaginationActive($slider, event.item.index);
                    }
                };
                
                // Inicializar el slider
                try {
                    $slider.owlCarousel(sliderConfig);
                } catch (error) {
                    console.error('Error inicializando slider:', error);
                }
            });
        },
        
        /**
         * Configurar paginación personalizada
         */
        setupCustomPagination: function($slider) {
            var $customPagination = $slider.find('.js-pagination');
            var $owlDots = $slider.find('.owl-dots');
            
            if ($customPagination.length > 0 && $owlDots.length > 0) {
                // Mover los elementos li de Owl a la paginación personalizada
                $owlDots.find('li').each(function() {
                    var $li = $(this);
                    var $span = $li.find('span');
                    
                    // Crear nuevo li con la estructura correcta
                    var $newLi = $('<li role="presentation"></li>');
                    $newLi.append($span);
                    
                    // Agregar clase active si es necesario
                    if ($li.hasClass('active')) {
                        $newLi.addClass('slick-active slick-current');
                    }
                    
                    $customPagination.append($newLi);
                });
                
                // Ocultar la paginación original de Owl
                $owlDots.hide();
                
                // Vincular eventos de click a la paginación personalizada
                $customPagination.find('li').on('click', function() {
                    var index = $(this).index();
                    $slider.trigger('to.owl.carousel', [index, 300]);
                });
            }
        },
        
        /**
         * Actualizar clases activas en la paginación personalizada
         */
        updatePaginationActive: function($slider, activeIndex) {
            var $customPagination = $slider.find('.js-pagination');
            if ($customPagination.length > 0) {
                $customPagination.find('li').removeClass('slick-active slick-current');
                $customPagination.find('li').eq(activeIndex).addClass('slick-active slick-current');
            }
        }
    };

    // ==========================================================================
    // Inicialización
    // ==========================================================================
    
    $(document).ready(function() {
        // Esperar un poco para que todo esté cargado
        setTimeout(function() {
            KintaSlider.init();
        }, 500);
    });
    
    // Re-inicializar cuando se carga contenido dinámico
    $(document).on('DOMNodeInserted', function(e) {
        var $target = $(e.target);
        if ($target.hasClass('js-slick-carousel') || $target.find('.js-slick-carousel').length > 0) {
            setTimeout(function() {
                KintaSlider.init();
            }, 1000);
        }
    });

})(jQuery);