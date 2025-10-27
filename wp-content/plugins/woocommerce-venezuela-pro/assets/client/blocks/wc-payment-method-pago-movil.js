/**
 * WooCommerce Blocks Payment Method - Pago Móvil
 * 
 * @package WooCommerce_Venezuela_Pro
 */

(function() {
    'use strict';
    
    const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
    const { __ } = window.wp.i18n;
    const { getPaymentMethodData } = window.wc.wcSettings;
    const { decodeEntities } = window.wp.htmlEntities;
    const { sanitizeHTML } = window.wc.sanitize;
    const { RawHTML } = window.wp.element;
    const { jsx } = window.ReactJSXRuntime;
    
    // Get payment method data
    const paymentMethodData = getPaymentMethodData('wvp_pago_movil', {});
    const title = decodeEntities(paymentMethodData?.title) || __('Pago Móvil', 'woocommerce-venezuela-pro');
    const description = paymentMethodData?.description || '';
    
    // Create content component
    const Content = () => jsx(RawHTML, { children: sanitizeHTML(description) });
    
    registerPaymentMethod({
        name: 'wvp_pago_movil',
        label: title,
        content: jsx(Content, {}),
        edit: jsx(Content, {}),
        canMakePayment: () => true,
        ariaLabel: title,
        supports: {
            features: paymentMethodData?.supports || ['products']
        }
    });
})();


