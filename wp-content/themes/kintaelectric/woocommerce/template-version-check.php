<?php
/**
 * Verificación de versiones de plantillas de WooCommerce
 * 
 * @package KintaElectric
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class KintaElectric_Template_Checker {
    
    /**
     * Verificar versiones de plantillas
     */
    public static function check_template_versions() {
        $templates = array(
            'archive-product.php' => array(
                'current' => '8.6.0',
                'required' => '8.6.0',
                'path' => get_template_directory() . '/woocommerce/archive-product.php'
            ),
            'content-product.php' => array(
                'current' => '9.4.0',
                'required' => '9.4.0',
                'path' => get_template_directory() . '/woocommerce/content-product.php'
            ),
            'content-single-product.php' => array(
                'current' => '9.4.0',
                'required' => '9.4.0',
                'path' => get_template_directory() . '/woocommerce/content-single-product.php'
            ),
            'single-product.php' => array(
                'current' => '8.6.0',
                'required' => '8.6.0',
                'path' => get_template_directory() . '/woocommerce/single-product.php'
            )
        );
        
        $results = array();
        
        foreach ($templates as $template => $info) {
            $file_exists = file_exists($info['path']);
            $version_match = true; // Asumimos que están actualizadas
            
            if ($file_exists) {
                $file_content = file_get_contents($info['path']);
                $version_match = strpos($file_content, '@version ' . $info['current']) !== false;
            }
            
            $results[$template] = array(
                'exists' => $file_exists,
                'version_match' => $version_match,
                'status' => $file_exists && $version_match ? 'updated' : 'outdated'
            );
        }
        
        return $results;
    }
    
    /**
     * Mostrar estado de plantillas en admin
     */
    public static function show_template_status() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $results = self::check_template_versions();
        $all_updated = true;
        
        foreach ($results as $template => $info) {
            if ($info['status'] !== 'updated') {
                $all_updated = false;
                break;
            }
        }
        
        if ($all_updated) {
            ?>
            <div class="notice notice-success">
                <p>
                    <strong><?php _e('KintaElectric:', 'kintaelectric'); ?></strong>
                    <?php _e('Todas las plantillas de WooCommerce están actualizadas y son compatibles con HPOS.', 'kintaelectric'); ?>
                </p>
            </div>
            <?php
        } else {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php _e('KintaElectric:', 'kintaelectric'); ?></strong>
                    <?php _e('Algunas plantillas de WooCommerce necesitan actualización para compatibilidad con HPOS.', 'kintaelectric'); ?>
                </p>
                <ul>
                    <?php foreach ($results as $template => $info): ?>
                        <?php if ($info['status'] !== 'updated'): ?>
                            <li><?php echo esc_html($template); ?> - <?php echo $info['exists'] ? __('Versión obsoleta', 'kintaelectric') : __('Archivo faltante', 'kintaelectric'); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        }
    }
}

// Mostrar estado en admin
add_action('admin_notices', array('KintaElectric_Template_Checker', 'show_template_status'));
