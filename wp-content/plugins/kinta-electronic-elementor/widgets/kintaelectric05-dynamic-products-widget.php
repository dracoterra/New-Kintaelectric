<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class KEE_Kintaelectric05_Dynamic_Products_Widget extends KEE_Base_Widget
{
    public function get_name()
    {
        return 'kintaelectric05-dynamic-products';
    }

    public function get_title()
    {
        return esc_html__('Kinta Electric 05 - Dynamic Products Carousel', 'kinta-electronic-elementor');
    }

    public function get_icon()
    {
        return 'eicon-products';
    }

    public function get_categories()
    {
        return ['kinta-electric'];
    }

    public function get_keywords()
    {
        return ['products', 'carousel', 'woocommerce', 'kinta', 'slider', 'best sellers'];
    }

    protected function register_controls()
    {
        // Widget deshabilitado - sin controles
    }

    protected function get_widget_script_depends()
    {
        return [];
    }

    protected function get_widget_style_depends()
    {
        return [];
    }

    protected function render()
    {
        echo '<div class="elementor-alert elementor-alert-info">' . 
             esc_html__('Este widget ha sido deshabilitado temporalmente.', 'kinta-electronic-elementor') . 
             '</div>';
    }
}
