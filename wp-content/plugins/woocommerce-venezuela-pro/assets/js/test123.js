// Script test123 - Versión mejorada
console.log('WVP: Script test123 cargado');

// Función para configurar un switcher específico
function setupSwitcher(switcher) {
    const usdButton = switcher.querySelector('.wvp-usd');
    const vesButton = switcher.querySelector('.wvp-ves');
    
    if (usdButton && vesButton) {
        // Remover event listeners existentes para evitar duplicados
        usdButton.replaceWith(usdButton.cloneNode(true));
        vesButton.replaceWith(vesButton.cloneNode(true));
        
        // Obtener referencias frescas después del reemplazo
        const newUsdButton = switcher.querySelector('.wvp-usd');
        const newVesButton = switcher.querySelector('.wvp-ves');
        
        // Configurar USD
        newUsdButton.style.cursor = 'pointer';
        newUsdButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('WVP: Click en USD para switcher específico');
            
            // Buscar precio asociado a este switcher específico
            let priceElement = switcher.previousElementSibling;
            if (!priceElement || !priceElement.classList.contains('woocommerce-Price-amount')) {
                priceElement = switcher.parentElement.querySelector('.woocommerce-Price-amount');
            }
            
            if (priceElement) {
                const usdPrice = switcher.getAttribute('data-price-usd');
                priceElement.innerHTML = usdPrice;
                console.log('WVP: Precio cambiado a USD:', usdPrice);
                
                // Actualizar clases activas
                newUsdButton.classList.add('active');
                newVesButton.classList.remove('active');
            } else {
                console.log('WVP: No se encontró elemento de precio para USD');
            }
        });
        
        // Configurar VES
        newVesButton.style.cursor = 'pointer';
        newVesButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('WVP: Click en VES para switcher específico');
            
            // Buscar precio asociado a este switcher específico
            let priceElement = switcher.previousElementSibling;
            if (!priceElement || !priceElement.classList.contains('woocommerce-Price-amount')) {
                priceElement = switcher.parentElement.querySelector('.woocommerce-Price-amount');
            }
            
            if (priceElement) {
                const vesPrice = switcher.getAttribute('data-price-ves');
                priceElement.innerHTML = vesPrice;
                console.log('WVP: Precio cambiado a VES:', vesPrice);
                
                // Actualizar clases activas
                newVesButton.classList.add('active');
                newUsdButton.classList.remove('active');
            } else {
                console.log('WVP: No se encontró elemento de precio para VES');
            }
        });
        
        console.log('WVP: Switcher configurado correctamente');
    }
}

// Función para verificar y configurar switchers
function checkSwitchers() {
    const switchers = document.querySelectorAll('.wvp-currency-switcher');
    console.log('WVP: Encontrados', switchers.length, 'switchers');
    
    if (switchers.length > 0) {
        // Configurar cada switcher individualmente
        switchers.forEach(function(switcher, index) {
            console.log('WVP: Configurando switcher', index);
            setupSwitcher(switcher);
        });
    }
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkSwitchers);
} else {
    checkSwitchers();
}

// También ejecutar después de un delay para capturar elementos cargados dinámicamente
setTimeout(checkSwitchers, 1000);
