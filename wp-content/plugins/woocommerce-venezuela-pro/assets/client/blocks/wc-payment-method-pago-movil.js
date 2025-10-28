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
    const { useState } = window.React;
    const { createElement: el } = window.wp.element;
    
    // Get payment method data
    const paymentMethodData = getPaymentMethodData('wvp_pago_movil', {});
    const title = decodeEntities(paymentMethodData?.title) || __('Pago Móvil', 'woocommerce-venezuela-pro');
    const description = paymentMethodData?.description || '';
    const accounts = paymentMethodData?.accounts || [];
    
    // Banks list mapping
    const banksList = {
        '0102': 'Banco de Venezuela',
        '0104': 'Venezolano de Crédito',
        '0105': 'Mercantil',
        '0134': 'Banesco',
        '0168': 'Bancrecer'
    };
    
    // Get account bank name
    const getBankName = (bankCode) => {
        return banksList[bankCode] || bankCode;
    };
    
    // Create content component with bank selection
    const Content = () => {
        // Build HTML for bank selection
        let html = '';
        
        if (description) {
            html += '<p>' + description + '</p>';
        }
        
        if (accounts && accounts.length > 0) {
            html += '<div class="wvp-pago-movil-accounts-blocks" style="margin: 20px 0;">';
            html += '<label style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 16px;">' + __('Selecciona tu banco:', 'woocommerce-venezuela-pro') + '</label>';
            
            accounts.forEach((account, index) => {
                const bankCode = account.bank || account.banco || '';
                const bankName = getBankName(bankCode);
                
                html += '<div class="wvp-account-option" data-account-index="' + index + '" style="border: 2px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; cursor: pointer;">';
                html += '<label style="display: flex; align-items: center; cursor: pointer; margin: 0;">';
                html += '<input type="radio" name="wvp_pago_movil_account_blocks" value="' + index + '" data-account-id="' + index + '" required style="margin-right: 15px; width: 20px; height: 20px;">';
                html += '<div style="flex: 1;"><strong>' + bankName + '</strong></div>';
                html += '</label>';
                html += '</div>';
            });
            
            html += '<input type="hidden" id="wvp_pago_movil_selected_account_id_blocks" name="wvp_pago_movil_selected_account_id_blocks" value="">';
            html += '</div>';
            
            // Add JavaScript for interaction
            html += '<script>document.addEventListener("DOMContentLoaded", function() { if (typeof jQuery !== "undefined") { jQuery(function($) { var selectedBank = ""; $(".wvp-account-option input").on("change", function() { selectedBank = $(this).val(); $("#wvp_pago_movil_selected_account_id_blocks").val(selectedBank); $(this).closest(".wvp-account-option").css("border-color", "#5cb85c").css("background", "#f0fff0"); $(".wvp-account-option").not($(this).closest(".wvp-account-option")).css("border-color", "#ddd").css("background", "#fff"); }); $(document.body).on("updated_checkout", function() { var currentBank = $(".wvp-account-option input:checked").val(); if (currentBank) { $("#wvp_pago_movil_selected_account_id_blocks").val(currentBank); } }); }); } });</script>';
        } else {
            html += '<div style="border: 1px solid #f0ad4e; padding: 15px; background: #fcf8e3; border-radius: 4px;">';
            html += '<p><strong>⚠️ ' + __('No hay cuentas configuradas.', 'woocommerce-venezuela-pro') + '</strong></p>';
            html += '</div>';
        }
        
        return el(RawHTML, {}, html);
    };
    
    registerPaymentMethod({
        name: 'wvp_pago_movil',
        label: title,
        content: el(Content, {}),
        edit: el(Content, {}),
        canMakePayment: () => true,
        ariaLabel: title,
        supports: {
            features: paymentMethodData?.supports || ['products']
        }
    });
})();


