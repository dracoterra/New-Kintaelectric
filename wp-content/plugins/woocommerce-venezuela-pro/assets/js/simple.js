// Script simple
console.log('WVP: Script simple cargado');

// Función para verificar switchers
function checkSwitchers() {
    const switchers = document.querySelectorAll('.wvp-currency-switcher');
    console.log('WVP: Encontrados', switchers.length, 'switchers');
    
    if (switchers.length > 0) {
        console.log('WVP: Primer switcher HTML:', switchers[0].outerHTML);
        
        // Añadir event listeners
        switchers.forEach(function(switcher, index) {
            console.log('WVP: Procesando switcher', index);
            
            const usdButton = switcher.querySelector('.wvp-usd');
            const vesButton = switcher.querySelector('.wvp-ves');
            
            if (usdButton) {
                usdButton.style.cursor = 'pointer';
                usdButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('WVP: Click en USD');
                    
                    // Buscar precio
                    let priceElement = switcher.previousElementSibling;
                    if (!priceElement || !priceElement.classList.contains('woocommerce-Price-amount')) {
                        priceElement = switcher.parentElement.querySelector('.woocommerce-Price-amount');
                    }
                    
                    if (priceElement) {
                        const usdPrice = switcher.getAttribute('data-price-usd');
                        priceElement.innerHTML = usdPrice;
                        console.log('WVP: Precio cambiado a USD:', usdPrice);
                    } else {
                        console.log('WVP: No se encontró elemento de precio para USD');
                    }
                });
            }
            
            if (vesButton) {
                vesButton.style.cursor = 'pointer';
                vesButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('WVP: Click en VES');
                    
                    // Buscar precio
                    let priceElement = switcher.previousElementSibling;
                    if (!priceElement || !priceElement.classList.contains('woocommerce-Price-amount')) {
                        priceElement = switcher.parentElement.querySelector('.woocommerce-Price-amount');
                    }
                    
                    if (priceElement) {
                        const vesPrice = switcher.getAttribute('data-price-ves');
                        priceElement.innerHTML = vesPrice;
                        console.log('WVP: Precio cambiado a VES:', vesPrice);
                    } else {
                        console.log('WVP: No se encontró elemento de precio para VES');
                    }
                });
            }
        });
    }
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkSwitchers);
} else {
    checkSwitchers();
}

// También ejecutar después de un delay
setTimeout(checkSwitchers, 1000);
setTimeout(checkSwitchers, 3000);
