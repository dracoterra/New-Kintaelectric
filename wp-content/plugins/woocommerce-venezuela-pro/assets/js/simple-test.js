// Script de prueba simple
console.log('WVP: Script simple cargado');

// Buscar switchers
setTimeout(function() {
    const switchers = document.querySelectorAll('.wvp-currency-switcher');
    console.log('WVP: Encontrados', switchers.length, 'switchers');
    
    if (switchers.length > 0) {
        console.log('WVP: Primer switcher:', switchers[0].outerHTML);
        
        // Añadir event listeners simples
        switchers.forEach(function(switcher) {
            const usdButton = switcher.querySelector('.wvp-usd');
            const vesButton = switcher.querySelector('.wvp-ves');
            
            if (usdButton) {
                usdButton.addEventListener('click', function() {
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
                    }
                });
            }
            
            if (vesButton) {
                vesButton.addEventListener('click', function() {
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
                    }
                });
            }
        });
    }
}, 1000);
