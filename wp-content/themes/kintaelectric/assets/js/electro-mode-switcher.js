/**
 * Electro Mode Switcher - Optimizado
 * Maneja el cambio entre modo claro y oscuro con persistencia en localStorage
 */
jQuery(document).ready(function($) {
    'use strict';

    // Configuración
    const STORAGE_KEY = 'electro-theme-mode';
    const DARK_MODE_CLASS = 'electro-dark';
    const LIGHT_MODE = 'light';
    const DARK_MODE = 'dark';

    // Cache de elementos del DOM para mejor rendimiento
    let $body, $darkButton, $lightButton;
    let isChanging = false; // Prevenir múltiples cambios simultáneos
    
    // Función para inicializar elementos DOM
    function initDOMElements() {
        $body = $('body');
        $darkButton = $('.electro-mode-switcher-item[data-mode="dark"]');
        $lightButton = $('.electro-mode-switcher-item[data-mode="light"]');
    }

    // Función para obtener el modo actual desde localStorage
    function getCurrentMode() {
        try {
            return localStorage.getItem(STORAGE_KEY) || LIGHT_MODE;
        } catch (e) {
            return LIGHT_MODE;
        }
    }

    // Función para guardar el modo en localStorage
    function saveMode(mode) {
        try {
            localStorage.setItem(STORAGE_KEY, mode);
        } catch (e) {
            console.warn('Electro Mode Switcher: Could not save to localStorage');
        }
    }

    // Función optimizada para aplicar el modo al body
    function applyMode(mode) {
        if (!$body) return;
        
        // Aplicar cambio inmediatamente para mejor rendimiento
        if (mode === DARK_MODE) {
            $body.addClass(DARK_MODE_CLASS);
        } else {
            $body.removeClass(DARK_MODE_CLASS);
        }
    }

    // Función optimizada para actualizar la apariencia de los botones
    function updateButtonStates(mode) {
        if (!$darkButton || !$lightButton) return;
        
        // Aplicar cambio inmediatamente para mejor rendimiento
        if (mode === DARK_MODE) {
            $darkButton.addClass('active');
            $lightButton.removeClass('active');
        } else {
            $lightButton.addClass('active');
            $darkButton.removeClass('active');
        }
    }

    // Función optimizada para cambiar el modo
    function switchMode(mode) {
        // Prevenir múltiples cambios simultáneos
        if (isChanging) return;
        isChanging = true;
        
        // Aplicar cambios inmediatamente sin esperar
        applyMode(mode);
        updateButtonStates(mode);
        saveMode(mode);
        
        // Resetear flag después de un breve delay
        setTimeout(function() {
            isChanging = false;
        }, 100);
    }

    // Función para inicializar el modo
    function initializeMode() {
        const currentMode = getCurrentMode();
        applyMode(currentMode);
        updateButtonStates(currentMode);
    }

    // Event listeners optimizados con delegación de eventos
    function initEventListeners() {
        // Usar delegación de eventos para mejor rendimiento
        $(document).on('click', '.electro-mode-switcher-item[data-mode="dark"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            switchMode(DARK_MODE);
        });

        $(document).on('click', '.electro-mode-switcher-item[data-mode="light"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            switchMode(LIGHT_MODE);
        });

        // Escuchar cambios en localStorage desde otras pestañas
        $(window).on('storage', function(e) {
            if (e.originalEvent.key === STORAGE_KEY) {
                const newMode = e.originalEvent.newValue || LIGHT_MODE;
                applyMode(newMode);
                updateButtonStates(newMode);
            }
        });
    }

    // Función para detectar preferencia del sistema
    function detectSystemPreference() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return DARK_MODE;
        }
        return LIGHT_MODE;
    }

    // Inicialización optimizada
    function init() {
        initDOMElements();
        initializeMode();
        initEventListeners();

        // Aplicar preferencia del sistema si no hay modo guardado
        if (!localStorage.getItem(STORAGE_KEY)) {
            const systemPreference = detectSystemPreference();
            if (systemPreference === DARK_MODE) {
                switchMode(DARK_MODE);
            }
        }

        // Escuchar cambios en la preferencia del sistema
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem(STORAGE_KEY)) {
                    const newMode = e.matches ? DARK_MODE : LIGHT_MODE;
                    switchMode(newMode);
                }
            });
        }
    }

    // Inicializar
    init();
});
