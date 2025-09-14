jQuery(document).ready(function($) {
    // Leer las clases del atributo data-pagi-classes (como hace el template original)
    var pagiClasses = $('.js-slick-carousel').data('pagi-classes');
    
    $('.js-slick-carousel').slick({
        dots: true,
        infinite: true,
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 4000,
        arrows: false,
        // Usar las clases exactas del data-pagi-classes
        dotsClass: pagiClasses || 'slick-dots u-slick__pagination u-slick__pagination--long',
        // Generar spans en lugar de buttons (como espera el CSS original)
        customPaging: function(slider, i) {
            return '<span></span>';
        }
    });

    // Evento init - anima el primer slide
    $('.js-slick-carousel').on('init', function (event, slick) {
        var slide = $(slick.$slides)[slick.currentSlide],
            animatedElements = $(slide).find('[data-scs-animation-in]');
        
        $(animatedElements).each(function () {
            var animationIn = $(this).data('scs-animation-in'),
                animationDelay = $(this).data('scs-animation-delay'),
                animationDuration = $(this).data('scs-animation-duration');
            
            $(this).css({
                'animation-delay': animationDelay + 'ms',
                'animation-duration': animationDuration + 'ms'
            });
            
            $(this).addClass('animated ' + animationIn).css('opacity', 1);
        });
    });
    
    // Evento beforeChange - limpia animaciones previas
    $('.js-slick-carousel').on('beforeChange', function (event, slider, currentSlide, nextSlide) {
        var nxtSlide = $(slider.$slides)[nextSlide],
            slide = $(slider.$slides)[currentSlide],
            animatedElements = $(nxtSlide).find('[data-scs-animation-in]'),
            otherElements = $(slide).find('[data-scs-animation-in]');
        
        $(otherElements).each(function () {
            var animationIn = $(this).data('scs-animation-in');
            
            $(this).removeClass('animated ' + animationIn);
        });
        
        $(animatedElements).each(function () {
            $(this).css('opacity', 0);
        });
    });
    
    // Evento afterChange - anima slides siguientes
    $('.js-slick-carousel').on('afterChange', function (event, slick, currentSlide) {
        var slide = $(slick.$slides)[currentSlide],
            animatedElements = $(slide).find('[data-scs-animation-in]');
        
        $(animatedElements).each(function () {
            var animationIn = $(this).data('scs-animation-in'),
                animationDelay = $(this).data('scs-animation-delay'),
                animationDuration = $(this).data('scs-animation-duration');
            
            $(this).css({
                'animation-delay': animationDelay + 'ms',
                'animation-duration': animationDuration + 'ms'
            });
            
            $(this).addClass('animated ' + animationIn).css('opacity', 1);
        });
    });
    
    // Activar animaciones del primer slide después de inicializar
    setTimeout(function() {
        var firstSlide = $('.js-slick-carousel .slick-active'),
            animatedElements = $(firstSlide).find('[data-scs-animation-in]');
        
        $(animatedElements).each(function () {
            var animationIn = $(this).data('scs-animation-in'),
                animationDelay = $(this).data('scs-animation-delay'),
                animationDuration = $(this).data('scs-animation-duration');
            
            $(this).css({
                'animation-delay': animationDelay + 'ms',
                'animation-duration': animationDuration + 'ms'
            });
            
            $(this).addClass('animated ' + animationIn).css('opacity', 1);
        });
    }, 100);
});
