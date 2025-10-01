/**
 * Shop View Switcher - Electro Theme
 * Maneja el cambio entre diferentes vistas de productos (grid, list, extended, etc.)
 */
jQuery(document).ready(function($) {
    'use strict';

    // Función para actualizar la altura de los productos
    function updateProductHeights() {
        if ($(window).width() > 992) {
            $('[data-bs-toggle="shop-products"] .product-outer').each(function() {
                var $product = $(this);
                // Resetear altura para recalcular
                $product.css('height', 'auto');
            });
            
            // Forzar recálculo de alturas después de un breve delay
            setTimeout(function() {
                $('[data-bs-toggle="shop-products"] .product-outer').each(function() {
                    var $product = $(this);
                    var currentHeight = $product.height();
                    if (currentHeight > 0) {
                        $product.css('height', currentHeight + 'px');
                    }
                });
            }, 100);
        }
    }

    // Función para aplicar la vista seleccionada
    function applyView(viewClass) {
        var $productsContainer = $('[data-bs-toggle="shop-products"]');
        
        if ($productsContainer.length) {
            // Actualizar el atributo data-view
            $productsContainer.attr('data-view', viewClass);
            
            // Actualizar las clases del contenedor de productos
            $productsContainer.removeClass('grid grid-extended list-view list-view-small')
                             .addClass(viewClass);
            
            // Actualizar las clases de los productos individuales
            $productsContainer.find('.product').each(function() {
                var $product = $(this);
                $product.removeClass('grid grid-extended list-view list-view-small')
                        .addClass(viewClass);
            });
            
            // Aplicar estilos específicos según la vista
            applyViewStyles(viewClass);
            
            // Actualizar alturas después del cambio
            setTimeout(function() {
                updateProductHeights();
            }, 100);
        }
    }

    // Función para aplicar estilos específicos según la vista
    function applyViewStyles(viewClass) {
        var $productsContainer = $('[data-bs-toggle="shop-products"]');
        
        // Resetear todas las clases de columnas del contenedor
        $productsContainer.removeClass('row-cols-1 row-cols-2 row-cols-3 row-cols-4 row-cols-5 row-cols-md-1 row-cols-md-2 row-cols-md-3 row-cols-md-4 row-cols-md-5 row-cols-lg-1 row-cols-lg-2 row-cols-lg-3 row-cols-lg-4 row-cols-lg-5 row-cols-xl-1 row-cols-xl-2 row-cols-xl-3 row-cols-xl-4 row-cols-xl-5 row-cols-xxl-1 row-cols-xxl-2 row-cols-xxl-3 row-cols-xxl-4 row-cols-xxl-5');
        
        switch(viewClass) {
            case 'grid':
                // Vista grid: 2 columnas en móvil, 3 en tablet, 5 en desktop
                $productsContainer.addClass('row-cols-2 row-cols-md-3 row-cols-lg-5 row-cols-xl-5 row-cols-xxl-5');
                break;
            case 'grid-extended':
                // Vista grid extendida: 2 columnas en móvil, 3 en tablet, 4 en desktop
                $productsContainer.addClass('row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-4 row-cols-xxl-4');
                break;
            case 'list-view':
                // Vista lista: 1 columna en todos los tamaños
                $productsContainer.addClass('row-cols-1');
                break;
            case 'list-view-small':
                // Vista lista pequeña: 1 columna en todos los tamaños
                $productsContainer.addClass('row-cols-1');
                break;
        }
    }

    // Función para guardar la vista seleccionada en localStorage
    function saveViewToStorage(viewClass) {
        try {
            localStorage.setItem('electro-shop-view', viewClass);
        } catch (e) {
            console.warn('Shop View Switcher: Could not save to localStorage');
        }
    }

    // Función para obtener la vista guardada desde localStorage
    function getViewFromStorage() {
        try {
            return localStorage.getItem('electro-shop-view') || 'grid';
        } catch (e) {
            return 'grid';
        }
    }

    // Función para actualizar el estado activo de los botones
    function updateActiveButton(activeView) {
        $('.shop-view-switcher .nav-link').removeClass('active');
        $('.shop-view-switcher .nav-link[data-archive-class="' + activeView + '"]').addClass('active');
    }

    // Inicializar la vista desde localStorage o URL hash
    function initializeView() {
        var savedView = getViewFromStorage();
        var hashView = window.location.hash.replace('#', '');
        
        // Priorizar hash de URL sobre localStorage
        var initialView = hashView && ['grid', 'grid-extended', 'list-view', 'list-view-small'].includes(hashView) 
                         ? hashView 
                         : savedView;
        
        // Aplicar la vista inicial
        applyView(initialView);
        updateActiveButton(initialView);
        
        // Forzar actualización de alturas después de la inicialización
        setTimeout(function() {
            updateProductHeights();
        }, 500);
    }

    // Event listener para el cambio de vista
    $('.shop-view-switcher').on('click', '.nav-link', function(e) {
        e.preventDefault();
        
        var $this = $(this);
        var viewClass = $this.data('archive-class');
        
        if (viewClass) {
            applyView(viewClass);
            updateActiveButton(viewClass);
            saveViewToStorage(viewClass);
            
            // Actualizar URL hash sin recargar la página
            if (history.pushState) {
                history.pushState(null, null, '#' + viewClass);
            }
        }
    });

    // Manejar cambios en el hash de la URL
    $(window).on('hashchange', function() {
        var hashView = window.location.hash.replace('#', '');
        if (['grid', 'grid-extended', 'list-view', 'list-view-small'].includes(hashView)) {
            applyView(hashView);
            updateActiveButton(hashView);
            saveViewToStorage(hashView);
        }
    });

    // Actualizar alturas en resize
    $(window).on('resize', function() {
        updateProductHeights();
    });

    // Inicializar al cargar la página
    initializeView();

    // Actualizar alturas después de que se carguen las imágenes
    $(window).on('load', function() {
        updateProductHeights();
    });

    // Actualizar alturas cuando se cargan productos via AJAX
    $(document).on('updated_wc_div', function() {
        updateProductHeights();
    });
});
