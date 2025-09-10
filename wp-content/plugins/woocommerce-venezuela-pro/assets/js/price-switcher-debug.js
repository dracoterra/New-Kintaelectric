/**
 * Script de debug para el switcher de moneda
 */

console.log('WVP DEBUG: Script de debug cargado');

// Verificar jQuery
if (typeof jQuery === 'undefined') {
    console.error('WVP DEBUG: jQuery no está disponible');
} else {
    console.log('WVP DEBUG: jQuery está disponible');
}

// Verificar si el script se ejecuta
console.log('WVP DEBUG: Script ejecutándose');

// Función simple para probar
function testSwitcher() {
    console.log('WVP DEBUG: Función testSwitcher ejecutada');
    
    // Buscar switchers
    const switchers = document.querySelectorAll('.wvp-currency-switcher');
    console.log('WVP DEBUG: Encontrados', switchers.length, 'switchers');
    
    if (switchers.length > 0) {
        console.log('WVP DEBUG: Primer switcher HTML:', switchers[0].outerHTML);
    }
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', testSwitcher);
} else {
    testSwitcher();
}

// También ejecutar después de un delay
setTimeout(testSwitcher, 1000);
