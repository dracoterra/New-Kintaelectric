/**
 * Kinta Electric Elementor - JavaScript del Slider
 * Versión simplificada sin animaciones problemáticas
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // ==========================================================================
    // Configuración del Slider
    // ==========================================================================
    
    var KintaSlider = {
        
        /**
         * Inicializar slider
         */
        init: function() {
            this.initSlider();
            this.bindEvents();
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
                console.warn('Owl Carousel no está disponible. Reintentando en 500ms...');
                setTimeout(function() {
                    KintaSlider.initSlider();
                }, 500);
                return;
            }
            
            // Inicializar cada slider
            $sliders.each(function() {
                var $slider = $(this);
                
                // Evitar inicializar múltiples veces
                if ($slider.hasClass('owl-loaded')) {
                    return;
                }
                
                // Configuración básica del slider
                var sliderConfig = {
                    items: 1,
                    loop: true,
                    nav: true,
                    dots: true,
                    autoplay: true,
                    autoplayTimeout: 5000,
                    autoplayHoverPause: true,
                    animateOut: 'fadeOut',
                    animateIn: 'fadeIn',
                    smartSpeed: 1000,
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
                        KintaSlider.setupCustomNavigation($slider);
                    }
                };
                
                // Inicializar el slider
                try {
                    $slider.owlCarousel(sliderConfig);
                    console.log('Slider inicializado correctamente:', $slider);
                } catch (error) {
                    console.error('Error inicializando slider:', error);
                }
            });
        },
        
        /**
         * Configurar paginación personalizada
         */
        setupCustomPagination: function($slider) {
            var $pagination = $slider.find('[data-pagi-classes]');
            if ($pagination.length > 0) {
                var classes = $pagination.data('pagi-classes');
                $pagination.addClass(classes);
                
                // Mover la paginación de Owl a la posición de Slick
                $slider.find('.owl-dots').removeClass('owl-dots').addClass('u-slick__pagination u-slick__pagination--long');
                $pagination.append($slider.find('.u-slick__pagination'));
            }
        },
        
        /**
         * Configurar navegación personalizada
         */
        setupCustomNavigation: function($slider) {
            var $nav = $slider.find('.owl-nav');
            if ($nav.length > 0) {
                $nav.addClass('u-slick__arrow u-slick__arrow--v1');
                $nav.find('.owl-prev').addClass('u-slick__arrow--left');
                $nav.find('.owl-next').addClass('u-slick__arrow--right');
            }
        },
        
        /**
         * Vincular eventos
         */
        bindEvents: function() {
            // Re-inicializar slider cuando se carga contenido dinámico
            $(document).on('DOMNodeInserted', function(e) {
                var $target = $(e.target);
                if ($target.hasClass('js-slick-carousel') || $target.find('.js-slick-carousel').length > 0) {
                    setTimeout(function() {
                        KintaSlider.initSlider();
                    }, 100);
                }
            });
            
            // Re-inicializar slider en Elementor (con verificación robusta)
            if (typeof elementorFrontend !== 'undefined' && 
                elementorFrontend.hooks && 
                typeof elementorFrontend.hooks.addAction === 'function') {
                try {
                    elementorFrontend.hooks.addAction('frontend/element_ready/global', function($scope) {
                        if ($scope.find('.js-slick-carousel').length > 0) {
                            setTimeout(function() {
                                KintaSlider.initSlider();
                            }, 100);
                        }
                    });
                } catch (error) {
                    console.warn('Error con Elementor hooks:', error);
                }
            }
            
            // Manejar resize para responsive
            $(window).on('resize', function() {
                $('.js-slick-carousel.owl-carousel').each(function() {
                    var $slider = $(this);
                    if ($slider.hasClass('owl-loaded')) {
                        $slider.trigger('refresh.owl.carousel');
                    }
                });
            });
        }
    };

    // ==========================================================================
    // Inicialización
    // ==========================================================================
    
    $(document).ready(function() {
        try {
            KintaSlider.init();
        } catch (error) {
            console.error('Error inicializando KintaSlider:', error);
        }
    });
    
    // Hacer disponible globalmente
    window.KintaSlider = KintaSlider;

})(jQuery);