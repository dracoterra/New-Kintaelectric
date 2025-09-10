// Script alert
alert('WVP: Script alert cargado');

// Función para verificar switchers
function checkSwitchers() {
    const switchers = document.querySelectorAll('.wvp-currency-switcher');
    alert('WVP: Encontrados ' + switchers.length + ' switchers');
    
    if (switchers.length > 0) {
        alert('WVP: Primer switcher HTML: ' + switchers[0].outerHTML);
        
        // Añadir event listeners
        switchers.forEach(function(switcher, index) {
            alert('WVP: Procesando switcher ' + index);
            
            const usdButton = switcher.querySelector('.wvp-usd');
            const vesButton = switcher.querySelector('.wvp-ves');
            
            if (usdButton) {
                usdButton.style.cursor = 'pointer';
                usdButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    alert('WVP: Click en USD');
                    
                    // Buscar precio
                    let priceElement = switcher.previousElementSibling;
                    if (!priceElement || !priceElement.classList.contains('woocommerce-Price-amount')) {
                        priceElement = switcher.parentElement.querySelector('.woocommerce-Price-amount');
                    }
                    
                    if (priceElement) {
                        const usdPrice = switcher.getAttribute('data-price-usd');
                        priceElement.innerHTML = usdPrice;
                        alert('WVP: Precio cambiado a USD: ' + usdPrice);
                    } else {
                        alert('WVP: No se encontró elemento de precio para USD');
                    }
                });
            }
            
            if (vesButton) {
                vesButton.style.cursor = 'pointer';
                vesButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    alert('WVP: Click en VES');
                    
                    // Buscar precio
                    let priceElement = switcher.previousElementSibling;
                    if (!priceElement || !priceElement.classList.contains('woocommerce-Price-amount')) {
                        priceElement = switcher.parentElement.querySelector('.woocommerce-Price-amount');
                    }
                    
                    if (priceElement) {
                        const vesPrice = switcher.getAttribute('data-price-ves');
                        priceElement.innerHTML = vesPrice;
                        alert('WVP: Precio cambiado a VES: ' + vesPrice);
                    } else {
                        alert('WVP: No se encontró elemento de precio para VES');
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
