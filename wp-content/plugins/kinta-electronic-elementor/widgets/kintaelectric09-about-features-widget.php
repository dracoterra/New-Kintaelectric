<?php
/**
 * Widget About Features Kintaelectric para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget About Features Kintaelectric
 */
class KEE_Kintaelectric09_About_Features_Widget extends KEE_Base_Widget {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'kintaelectric09_about_features';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('About Features', 'kinta-electric-elementor');
    }

    /**
     * Obtener icono del widget
     */
    public function get_icon() {
        return 'eicon-columns';
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
        return ['about', 'features', 'columns', 'kintaelectric'];
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

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'image',
            [
                'label' => esc_html__('Imagen', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'title',
            [
                'label' => esc_html__('Título', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('What we really do?', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'description',
            [
                'label' => esc_html__('Descripción', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna, vitae ultrices lacus purus vitae metus.', 'kinta-electric-elementor'),
                'rows' => 4,
            ]
        );

        $this->add_control(
            'features',
            [
                'label' => esc_html__('Características', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'title' => esc_html__('What we really do?', 'kinta-electric-elementor'),
                        'description' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna, vitae ultrices lacus purus vitae metus.', 'kinta-electric-elementor'),
                    ],
                    [
                        'title' => esc_html__('Our Vision', 'kinta-electric-elementor'),
                        'description' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna, vitae ultrices lacus purus vitae metus.', 'kinta-electric-elementor'),
                    ],
                    [
                        'title' => esc_html__('History of Beginning', 'kinta-electric-elementor'),
                        'description' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna, vitae ultrices lacus purus vitae metus.', 'kinta-electric-elementor'),
                    ],
                ],
                'title_field' => '{{{ title }}}',
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
        <div class="row about-features py-4 py-md-5">
            <?php if (!empty($settings['features'])) : ?>
                <?php foreach ($settings['features'] as $feature) : ?>
                    <div class="col-md-4 mb-4">
                        <div class="feature-item">
                            <div class="feature-image mb-3">
                                <img loading="lazy" decoding="async" 
                                     src="<?php echo esc_url($feature['image']['url']); ?>" 
                                     class="img-fluid rounded" 
                                     alt="<?php echo esc_attr($feature['title']); ?>" 
                                     title="<?php echo esc_attr($feature['title']); ?>">
                            </div>

                            <div class="feature-content">
                                <h3 class="h4 mb-3"><?php echo esc_html($feature['title']); ?></h3>
                                <p class="text-muted"><?php echo wp_kses_post(nl2br($feature['description'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Renderizar widget en modo de edición
     */
    protected function content_template() {
        ?>
        <div class="row about-features py-4 py-md-5">
            <# if (settings.features && settings.features.length > 0) { #>
                <# _.each(settings.features, function(feature) { #>
                    <div class="col-md-4 mb-4">
                        <div class="feature-item">
                            <div class="feature-image mb-3">
                                <img loading="lazy" decoding="async" 
                                     src="{{{ feature.image.url }}}" 
                                     class="img-fluid rounded" 
                                     alt="{{{ feature.title }}}" 
                                     title="{{{ feature.title }}}">
                            </div>

                            <div class="feature-content">
                                <h3 class="h4 mb-3">{{{ feature.title }}}</h3>
                                <p class="text-muted">{{{ feature.description }}}</p>
                            </div>
                        </div>
                    </div>
                <# }); #>
            <# } #>
        </div>
        <?php
    }
}
