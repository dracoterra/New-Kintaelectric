/**
 * Footer Scripts for Kinta Electric Theme
 * 
 * @package kintaelectric
 */

(function() {
    'use strict';
    
    // WooCommerce JS detection
    if (document.body.classList.contains('woocommerce-no-js')) {
        var c = document.body.className;
        c = c.replace(/woocommerce-no-js/, 'woocommerce-js');
        document.body.className = c;
    }
    
    // Slider Revolution error handling
    if (typeof revslider_showDoubleJqueryError === "undefined") {
        function revslider_showDoubleJqueryError(sliderID) {
            console.log("You have some jquery.js library include that comes after the Slider Revolution files js inclusion.");
            console.log("To fix this, you can:");
            console.log("1. Set 'Module General Options' -> 'Advanced' -> 'jQuery & OutPut Filters' -> 'Put JS to Body' to on");
            console.log("2. Find the double jQuery.js inclusion and remove it");
            return "Double Included jQuery Library";
        }
    }
})();
