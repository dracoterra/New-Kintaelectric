/**
 * Switcher de moneda simple y robusto
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

console.log('WVP: Currency Switcher cargado');

// Función principal
function initCurrencySwitcher() {
    console.log('WVP: Inicializando switcher de moneda');
    
    // Buscar todos los switchers
    const switchers = document.querySelectorAll('.wvp-currency-switcher');
    console.log('WVP: Encontrados', switchers.length, 'switchers');
    
    if (switchers.length === 0) {
        console.log('WVP: No se encontraron switchers, reintentando en 1 segundo');
        setTimeout(initCurrencySwitcher, 1000);
        return;
    }
    
    // Configurar cada switcher
    switchers.forEach(function(switcher, index) {
        console.log('WVP: Configurando switcher', index);
        setupSwitcher(switcher);
    });
}

// Configurar un switcher individual
function setupSwitcher(switcher) {
    const usdButton = switcher.querySelector('.wvp-usd');
    const vesButton = switcher.querySelector('.wvp-ves');
    
    if (!usdButton || !vesButton) {
        console.log('WVP: Botones no encontrados en switcher');
        return;
    }
    
    // Remover event listeners existentes
    usdButton.replaceWith(usdButton.cloneNode(true));
    vesButton.replaceWith(vesButton.cloneNode(true));
    
    // Obtener referencias frescas
    const newUsdButton = switcher.querySelector('.wvp-usd');
    const newVesButton = switcher.querySelector('.wvp-ves');
    
    // Configurar USD
    newUsdButton.style.cursor = 'pointer';
    newUsdButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('WVP: Click en USD');
        switchToCurrency(switcher, 'usd');
    });
    
    // Configurar VES
    newVesButton.style.cursor = 'pointer';
    newVesButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('WVP: Click en VES');
        switchToCurrency(switcher, 'ves');
    });
    
    console.log('WVP: Switcher configurado correctamente');
}

// Cambiar moneda
function switchToCurrency(switcher, currency) {
    const usdButton = switcher.querySelector('.wvp-usd');
    const vesButton = switcher.querySelector('.wvp-ves');
    
    // Buscar precio asociado
    let priceElement = switcher.previousElementSibling;
    if (!priceElement || !priceElement.classList.contains('woocommerce-Price-amount')) {
        priceElement = switcher.parentElement.querySelector('.woocommerce-Price-amount');
    }
    
    if (!priceElement) {
        console.log('WVP: No se encontró elemento de precio');
        return;
    }
    
    if (currency === 'usd') {
        const usdPrice = switcher.getAttribute('data-price-usd');
        if (usdPrice) {
            priceElement.innerHTML = usdPrice;
            usdButton.classList.add('active');
            vesButton.classList.remove('active');
            console.log('WVP: Cambiado a USD:', usdPrice);
        }
    } else if (currency === 'ves') {
        const vesPrice = switcher.getAttribute('data-price-ves');
        if (vesPrice) {
            priceElement.innerHTML = vesPrice;
            vesButton.classList.add('active');
            usdButton.classList.remove('active');
            console.log('WVP: Cambiado a VES:', vesPrice);
        }
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCurrencySwitcher);
} else {
    initCurrencySwitcher();
}

// Reinicializar cuando se actualice el contenido
document.addEventListener('updated_wc_div', initCurrencySwitcher);
document.addEventListener('updated_cart_totals', initCurrencySwitcher);

console.log('WVP: Currency Switcher inicializado');
