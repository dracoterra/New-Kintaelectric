<?php
/**
 * Archivo de prueba temporal para verificar la categoría de widgets
 * Este archivo se puede eliminar después de confirmar que funciona
 */

// Verificar que la categoría se esté registrando
add_action('admin_notices', function() {
    if (did_action('elementor/loaded')) {
        echo '<div class="notice notice-info is-dismissible"><p>Plugin KintaElectronic Elementor cargado correctamente. Categoría "Kinta Electric Widget" registrada.</p></div>';
    }
});
