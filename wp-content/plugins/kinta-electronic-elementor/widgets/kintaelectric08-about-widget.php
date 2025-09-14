<?php
/**
 * Widget About Kintaelectric para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget About Kintaelectric
 */
class KEE_Kintaelectric08_About_Widget extends KEE_Base_Widget {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'kintaelectric08_about';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('About Kintaelectric', 'kinta-electric-elementor');
    }

    /**
     * Obtener icono del widget
     */
    public function get_icon() {
        return 'eicon-info-box';
    }

    /**
     * Obtener categoría del widget
     */
    public function get_categories() {
        return ['kinta-electric'];
    }

    /**
     * Obtener palabras clave del widget
     */
    public function get_keywords() {
        return ['about', 'info', 'kintaelectric', 'header', 'cover'];
    }

    /**
     * Obtener dependencias de scripts
     */
    public function get_script_depends() {
        return [];
    }

    /**
     * Obtener dependencias de estilos
     */
    public function get_style_depends() {
        return [];
    }

    /**
     * Registrar controles del widget
     */
    protected function register_controls() {
        // Sección de Configuración
        $this->start_controls_section(
            'section_config',
            [
                'label' => esc_html__('Configuración', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Control para imagen de fondo
        $this->add_control(
            'background_image',
            [
                'label' => esc_html__('Imagen de Fondo', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        // Control para título
        $this->add_control(
            'title',
            [
                'label' => esc_html__('Título', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('About Us', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        // Control para subtítulo
        $this->add_control(
            'subtitle',
            [
                'label' => esc_html__('Subtítulo', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Passion may be a friendly or eager interest in or admiration for a proposal, cause, discovery, or activity or love to a feeling of unusual excitement.', 'kinta-electric-elementor'),
                'rows' => 3,
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Renderizar widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <header class="entry-header header-with-cover-image" style="background-image: url(<?php echo esc_url($settings['background_image']['url']); ?>);">
            <div class="caption">
                <h1 class="entry-title"><?php echo esc_html($settings['title']); ?></h1>
                <p class="entry-subtitle"><?php echo wp_kses_post(nl2br($settings['subtitle'])); ?></p>
            </div>
        </header><!-- .entry-header -->
        <?php
    }

    /**
     * Renderizar widget en modo de edición
     */
    protected function content_template() {
        ?>
        <header class="entry-header header-with-cover-image" style="background-image: url({{{ settings.background_image.url }}});">
            <div class="caption">
                <h1 class="entry-title">{{{ settings.title }}}</h1>
                <p class="entry-subtitle">{{{ settings.subtitle }}}</p>
            </div>
        </header><!-- .entry-header -->
        <?php
    }
}
