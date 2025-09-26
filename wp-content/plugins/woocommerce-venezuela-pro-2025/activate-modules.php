<?php
/**
 * Temporary script to activate currency modules
 */

// Load WordPress
require_once '../../../wp-config.php';

// Activate all currency modules
$modules = array('visual_converter', 'button_converter', 'cart_converter');
update_option('wvp_currency_modules', $modules);

echo "Currency modules activated successfully:\n";
foreach ($modules as $module) {
    echo "- " . $module . "\n";
}

echo "\nYou can now refresh the cart page to see the conversions.\n";
?>
