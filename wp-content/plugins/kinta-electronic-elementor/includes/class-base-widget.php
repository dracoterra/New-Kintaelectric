<?php
/**
 * Clase base para widgets de Kinta Electric Elementor
 * 
 * Esta clase proporciona dependencias estándar del tema para todos los widgets
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase base para widgets de Kinta Electric
 */
abstract class KEE_Base_Widget extends \Elementor\Widget_Base {

    /**
     * Obtener dependencias de scripts estándar del tema
     * 
     * @return array
     */
    public function get_script_depends() {
        $dependencies = ['bootstrap-bundle', 'electro-main'];
        
        // Agregar dependencias específicas del widget si las hay
        $widget_dependencies = $this->get_widget_script_depends();
        if (!empty($widget_dependencies)) {
            $dependencies = array_merge($dependencies, $widget_dependencies);
        }
        
        return $dependencies;
    }

    /**
     * Obtener dependencias de estilos estándar del tema
     * 
     * @return array
     */
    public function get_style_depends() {
        $dependencies = ['electro-style'];
        
        // Agregar dependencias específicas del widget si las hay
        $widget_dependencies = $this->get_widget_style_depends();
        if (!empty($widget_dependencies)) {
            $dependencies = array_merge($dependencies, $widget_dependencies);
        }
        
        return $dependencies;
    }

    /**
     * Obtener dependencias de scripts específicas del widget
     * 
     * Los widgets pueden sobrescribir este método para agregar sus propias dependencias
     * 
     * @return array
     */
    protected function get_widget_script_depends() {
        return [];
    }

    /**
     * Obtener dependencias de estilos específicas del widget
     * 
     * Los widgets pueden sobrescribir este método para agregar sus propias dependencias
     * 
     * @return array
     */
    protected function get_widget_style_depends() {
        return [];
    }

    /**
     * Verificar si WooCommerce está activo
     * 
     * @return bool
     */
    protected function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Verificar si YITH WooCommerce Wishlist está activo
     * 
     * @return bool
     */
    protected function is_yith_wishlist_active() {
        return defined('YITH_WCWL') && YITH_WCWL;
    }

    /**
     * Verificar si YITH WooCommerce Compare está activo
     * 
     * @return bool
     */
    protected function is_yith_compare_active() {
        return defined('YITH_WOOCOMPARE') && YITH_WOOCOMPARE;
    }

    /**
     * Obtener productos en oferta para selectores
     * 
     * @return array
     */
    protected function get_products_on_sale() {
        if (!$this->is_woocommerce_active()) {
            return [];
        }

        $products = wc_get_products([
            'status' => 'publish',
            'on_sale' => true,
            'limit' => -1,
        ]);

        $options = [];
        foreach ($products as $product) {
            $options[$product->get_id()] = $product->get_name();
        }

        return $options;
    }

    /**
     * Obtener categorías de productos para selectores
     * 
     * @return array
     */
    protected function get_product_categories() {
        if (!$this->is_woocommerce_active()) {
            return [];
        }

        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ]);

        $options = [];
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $options[$category->term_id] = $category->name;
            }
        }

        return $options;
    }

    /**
     * Renderizar botón de Wishlist de YITH
     * 
     * @param int $product_id
     * @return string
     */
    protected function render_yith_wishlist_button($product_id) {
        if (!$this->is_yith_wishlist_active()) {
            return '';
        }

        // Intentar usar la función directa primero
        if (function_exists('yith_wcwl_add_to_wishlist_button')) {
            ob_start();
            yith_wcwl_add_to_wishlist_button();
            return ob_get_clean();
        }

        // Fallback a shortcode
        if (function_exists('do_shortcode')) {
            return do_shortcode('[yith_wcwl_add_to_wishlist product_id="' . $product_id . '"]');
        }

        // Fallback manual con nonce
        $nonce = wp_create_nonce('add_to_wishlist');
        return sprintf(
            '<a href="#" class="add_to_wishlist" data-product-id="%d" data-nonce="%s" title="%s">
                <i class="fa fa-heart"></i>
            </a>',
            $product_id,
            $nonce,
            __('Agregar a Lista de Deseos', 'kinta-electric-elementor')
        );
    }

    /**
     * Renderizar botón de Compare de YITH
     * 
     * @param int $product_id
     * @return string
     */
    protected function render_yith_compare_button($product_id) {
        if (!$this->is_yith_compare_active()) {
            return '';
        }

        // Intentar usar la función directa primero
        if (function_exists('yith_woocompare_add_compare_button')) {
            ob_start();
            yith_woocompare_add_compare_button();
            return ob_get_clean();
        }

        // Fallback a shortcode
        if (function_exists('do_shortcode')) {
            return do_shortcode('[yith_compare_button product="' . $product_id . '"]');
        }

        return '';
    }

    /**
     * Obtener imagen del producto con tamaño específico
     * 
     * @param int $product_id
     * @param string $size
     * @return string
     */
    protected function get_product_image($product_id, $size = 'woocommerce_thumbnail') {
        if (!$this->is_woocommerce_active()) {
            return '';
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return '';
        }

        return $product->get_image($size);
    }

    /**
     * Obtener precio del producto
     * 
     * @param int $product_id
     * @return string
     */
    protected function get_product_price($product_id) {
        if (!$this->is_woocommerce_active()) {
            return '';
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return '';
        }

        return $product->get_price_html();
    }

    /**
     * Obtener título del producto
     * 
     * @param int $product_id
     * @return string
     */
    protected function get_product_title($product_id) {
        if (!$this->is_woocommerce_active()) {
            return '';
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return '';
        }

        return $product->get_name();
    }

    /**
     * Obtener URL del producto
     * 
     * @param int $product_id
     * @return string
     */
    protected function get_product_url($product_id) {
        if (!$this->is_woocommerce_active()) {
            return '#';
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return '#';
        }

        return $product->get_permalink();
    }
}
