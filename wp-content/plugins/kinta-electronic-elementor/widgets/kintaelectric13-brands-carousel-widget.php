<?php
/**
 * Widget Brands Carousel Kintaelectric para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Brands Carousel Kintaelectric
 */
class KEE_Kintaelectric13_Brands_Carousel_Widget extends KEE_Base_Widget {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'kintaelectric13_brands_carousel';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('Brands Carousel', 'kinta-electric-elementor');
    }

    /**
     * Obtener icono del widget
     */
    public function get_icon() {
        return 'eicon-carousel';
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
        return ['brands', 'carousel', 'logos', 'kintaelectric'];
    }

    /**
     * Obtener dependencias de scripts
     */
    public function get_script_depends() {
        return ['jquery', 'owl-carousel'];
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

        // Título de la sección
        $this->add_control(
            'section_title',
            [
                'label' => esc_html__('Título de la Sección', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Brands Carousel', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'brand_image',
            [
                'label' => esc_html__('Imagen de la Marca', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'brand_name',
            [
                'label' => esc_html__('Nombre de la Marca', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Brand Name', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'brand_url',
            [
                'label' => esc_html__('URL de la Marca', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://brand-website.com', 'kinta-electric-elementor'),
                'default' => [
                    'url' => '#',
                ],
            ]
        );

        $this->add_control(
            'brands',
            [
                'label' => esc_html__('Marcas', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'brand_name' => esc_html__('Acer', 'kinta-electric-elementor'),
                    ],
                    [
                        'brand_name' => esc_html__('Apple', 'kinta-electric-elementor'),
                    ],
                    [
                        'brand_name' => esc_html__('Asus', 'kinta-electric-elementor'),
                    ],
                    [
                        'brand_name' => esc_html__('Dell', 'kinta-electric-elementor'),
                    ],
                    [
                        'brand_name' => esc_html__('HP', 'kinta-electric-elementor'),
                    ],
                    [
                        'brand_name' => esc_html__('Microsoft', 'kinta-electric-elementor'),
                    ],
                ],
                'title_field' => '{{{ brand_name }}}',
            ]
        );

        $this->end_controls_section();

        // Sección de Configuración del Carousel
        $this->start_controls_section(
            'section_carousel_config',
            [
                'label' => esc_html__('Configuración del Carousel', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => esc_html__('Autoplay', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electric-elementor'),
                'label_off' => esc_html__('No', 'kinta-electric-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'autoplay_speed',
            [
                'label' => esc_html__('Velocidad de Autoplay (ms)', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3000,
                'condition' => [
                    'autoplay' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_nav',
            [
                'label' => esc_html__('Mostrar Navegación', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electric-elementor'),
                'label_off' => esc_html__('No', 'kinta-electric-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_dots',
            [
                'label' => esc_html__('Mostrar Puntos', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electric-elementor'),
                'label_off' => esc_html__('No', 'kinta-electric-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Renderizar widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $carousel_id = 'owl-brands-' . $this->get_id();
        ?>
        <section class="brands-carousel py-5">
            <h2 class="sr-only"><?php echo esc_html($settings['section_title']); ?></h2>
            <div class="container">
                <div id="<?php echo esc_attr($carousel_id); ?>" 
                     class="owl-brands owl-carousel electro-owl-carousel owl-outer-nav"
                     data-ride="owl-carousel" 
                     data-carousel-selector="self"
                     data-carousel-options='{
                         "items": 5,
                         "navRewind": true,
                         "autoplayHoverPause": true,
                         "nav": <?php echo $settings['show_nav'] === 'yes' ? 'true' : 'false'; ?>,
                         "stagePadding": 1,
                         "dots": <?php echo $settings['show_dots'] === 'yes' ? 'true' : 'false'; ?>,
                         "rtl": false,
                         "navText": ["<i class=\"fa fa-chevron-left\"></i>", "<i class=\"fa fa-chevron-right\"></i>"],
                         "touchDrag": false,
                         "autoplay": <?php echo $settings['autoplay'] === 'yes' ? 'true' : 'false'; ?>,
                         "autoplayTimeout": <?php echo esc_attr($settings['autoplay_speed']); ?>,
                         "responsive": {
                             "0": {"items": 1},
                             "480": {"items": 2},
                             "768": {"items": 2},
                             "992": {"items": 3},
                             "1200": {"items": 5}
                         }
                     }'>
                    <?php if (!empty($settings['brands'])) : ?>
                        <?php foreach ($settings['brands'] as $brand) : ?>
                            <div class="item">
                                <a href="<?php echo esc_url($brand['brand_url']['url']); ?>" 
                                   <?php echo $brand['brand_url']['is_external'] ? 'target="_blank"' : ''; ?>
                                   <?php echo $brand['brand_url']['nofollow'] ? 'rel="nofollow"' : ''; ?>>
                                    <figure>
                                        <figcaption class="text-overlay">
                                            <div class="info">
                                                <h4><?php echo esc_html($brand['brand_name']); ?></h4>
                                            </div>
                                        </figcaption>
                                        <img src="<?php echo esc_url($brand['brand_image']['url']); ?>" 
                                             alt="<?php echo esc_attr($brand['brand_name']); ?>" 
                                             width="200" height="60"
                                             class="img-fluid desaturate">
                                    </figure>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * Renderizar widget en modo de edición
     */
    protected function content_template() {
        ?>
        <section class="brands-carousel py-5">
            <h2 class="sr-only">{{{ settings.section_title }}}</h2>
            <div class="container">
                <div id="owl-brands-{{{ view.getID() }}}" 
                     class="owl-brands owl-carousel electro-owl-carousel owl-outer-nav"
                     data-ride="owl-carousel" 
                     data-carousel-selector="self"
                     data-carousel-options='{
                         "items": 5,
                         "navRewind": true,
                         "autoplayHoverPause": true,
                         "nav": {{{ settings.show_nav === 'yes' ? 'true' : 'false' }}},
                         "stagePadding": 1,
                         "dots": {{{ settings.show_dots === 'yes' ? 'true' : 'false' }}},
                         "rtl": false,
                         "navText": ["<i class=\"fa fa-chevron-left\"></i>", "<i class=\"fa fa-chevron-right\"></i>"],
                         "touchDrag": false,
                         "autoplay": {{{ settings.autoplay === 'yes' ? 'true' : 'false' }}},
                         "autoplayTimeout": {{{ settings.autoplay_speed }}},
                         "responsive": {
                             "0": {"items": 1},
                             "480": {"items": 2},
                             "768": {"items": 2},
                             "992": {"items": 3},
                             "1200": {"items": 5}
                         }
                     }'>
                    <# if (settings.brands && settings.brands.length > 0) { #>
                        <# _.each(settings.brands, function(brand) { #>
                            <div class="item">
                                <a href="{{{ brand.brand_url.url }}}" 
                                   <# if (brand.brand_url.is_external) { #>target="_blank"<# } #>
                                   <# if (brand.brand_url.nofollow) { #>rel="nofollow"<# } #>>
                                    <figure>
                                        <figcaption class="text-overlay">
                                            <div class="info">
                                                <h4>{{{ brand.brand_name }}}</h4>
                                            </div>
                                        </figcaption>
                                        <img src="{{{ brand.brand_image.url }}}" 
                                             alt="{{{ brand.brand_name }}}" 
                                             width="200" height="60"
                                             class="img-fluid desaturate">
                                    </figure>
                                </a>
                            </div>
                        <# }); #>
                    <# } #>
                </div>
            </div>
        </section>
        <?php
    }
}
