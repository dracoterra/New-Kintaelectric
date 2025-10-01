/**
 * Electro Mode Switcher Widget JavaScript
 * INDEPENDIENTE del switcher original - usa su propia funcionalidad
 * Pero mantiene la MISMA lógica de funcionamiento
 */
jQuery(document).ready(function($) {
    'use strict';

    // Configuración INDEPENDIENTE del switcher original
    const WIDGET_STORAGE_KEY = 'electro-widget-theme-mode';
    const DARK_MODE_CLASS = 'electro-dark';
    const LIGHT_MODE = 'light';
    const DARK_MODE = 'dark';

    // Cache de elementos del DOM
    let $body, $toggle;
    let isChanging = false;
    
    // Función para inicializar elementos DOM
    function initDOMElements() {
        $body = $('body');
        $toggle = $('#electro-mode-toggle-widget');
    }

    // Función para obtener el modo actual desde localStorage
    function getCurrentMode() {
        try {
            // Primero intenta leer del switcher original
            const originalMode = localStorage.getItem('electro-theme-mode');
            if (originalMode) {
                return originalMode;
            }
            // Si no existe, usa el del widget
            return localStorage.getItem(WIDGET_STORAGE_KEY) || LIGHT_MODE;
        } catch (e) {
            return LIGHT_MODE;
        }
    }

    // Función para guardar el modo en localStorage
    function saveMode(mode) {
        try {
            // Guardar en AMBOS storages para sincronización
            localStorage.setItem('electro-theme-mode', mode);
            localStorage.setItem(WIDGET_STORAGE_KEY, mode);
        } catch (e) {
            console.warn('Electro Mode Switcher Widget: Could not save to localStorage');
        }
    }

    // Función para aplicar el modo al body
    function applyMode(mode) {
        if (!$body) return;
        
        if (mode === DARK_MODE) {
            $body.addClass(DARK_MODE_CLASS);
        } else {
            $body.removeClass(DARK_MODE_CLASS);
        }
    }

    // Función para actualizar el estado del toggle
    function updateToggleState(mode) {
        if (!$toggle) return;
        $toggle.prop('checked', mode === DARK_MODE);
    }

    // Función para cambiar el modo
    function switchMode(mode) {
        if (isChanging) return;
        isChanging = true;
        
        applyMode(mode);
        updateToggleState(mode);
        saveMode(mode);
        
        // Disparar evento para sincronizar con switcher original
        $(document).trigger('electro-widget-theme-changed', [mode]);
        
        setTimeout(function() {
            isChanging = false;
        }, 100);
    }

    // Función para inicializar el modo
    function initializeMode() {
        const currentMode = getCurrentMode();
        applyMode(currentMode);
        updateToggleState(currentMode);
    }

    // Event listeners
    function initEventListeners() {
        // Toggle event listener
        if ($toggle.length > 0) {
            $toggle.on('change', function() {
                const newMode = $(this).is(':checked') ? DARK_MODE : LIGHT_MODE;
                switchMode(newMode);
            });
        }
        
        // Escuchar cambios del switcher original
        $(window).on('storage', function(e) {
            if (e.originalEvent.key === 'electro-theme-mode') {
                const newMode = e.originalEvent.newValue || LIGHT_MODE;
                applyMode(newMode);
                updateToggleState(newMode);
            }
        });

        // Escuchar eventos del switcher original
        $(document).on('electro-theme-changed', function(e, mode) {
            if (mode) {
                applyMode(mode);
                updateToggleState(mode);
            }
        });
    }

    // Inicialización
    function init() {
        initDOMElements();
        initializeMode();
        initEventListeners();
    }

    // Re-initialize when widgets are updated (for customizer)
    $(document).on('widget-updated', function(e, widget) {
        if (widget.find('.electro-mode-switcher-widget').length) {
            init();
        }
    });

    // Re-initialize when widgets are added (for customizer)
    $(document).on('widget-added', function(e, widget) {
        if (widget.find('.electro-mode-switcher-widget').length) {
            init();
        }
    });

    // Re-initialize when shortcode content is loaded
    $(document).on('DOMNodeInserted', function(e) {
        if ($(e.target).find('.electro-mode-switcher-widget').length) {
            init();
        }
    });

    // Inicializar
    init();
});
