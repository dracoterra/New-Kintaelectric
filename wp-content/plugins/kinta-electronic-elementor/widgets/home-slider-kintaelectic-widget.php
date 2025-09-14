<?php
/**
 * Widget Home Slider Kintaelectic para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Home Slider Kintaelectic
 */
class KEE_Home_Slider_Kintaelectic_Widget extends KEE_Base_Widget {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'home_slider_kintaelectic';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('Home Slider Kintaelectic', 'kinta-electric-elementor');
    }

    /**
     * Obtener icono del widget
     */
    public function get_icon() {
        return 'eicon-slider-push';
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
        return ['slider', 'home', 'kintaelectic', 'hero', 'banner'];
    }

    /**
     * Obtener dependencias de scripts
     */
    public function get_script_depends() {
        return ['jquery', 'slick-carousel', 'kinta-home-slider-js'];
    }

    /**
     * Obtener dependencias de estilos
     */
    public function get_style_depends() {
        return ['slick-carousel', 'slick-theme', 'animate-css', 'kinta-home-slider-css'];
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

        // Control para imagen de fondo única del slider
        $this->add_control(
            'background_image',
            [
                'label' => esc_html__('Imagen de Fondo del Slider', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'title',
            [
                'label' => esc_html__('Título Principal', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('THE NEW STANDARD', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'subtitle',
            [
                'label' => esc_html__('Subtítulo', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('UNDER FAVORABLE SMARTWATCHES', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'price_from',
            [
                'label' => esc_html__('Precio Principal', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('299', 'kinta-electric-elementor'),
            ]
        );

        $repeater->add_control(
            'price_cents',
            [
                'label' => esc_html__('Centavos', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('.00', 'kinta-electric-elementor'),
            ]
        );

        $repeater->add_control(
            'button_text',
            [
                'label' => esc_html__('Texto del Botón', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Start Shopping', 'kinta-electric-elementor'),
            ]
        );

        $repeater->add_control(
            'button_url',
            [
                'label' => esc_html__('URL del Botón', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://tu-sitio.com', 'kinta-electric-elementor'),
                'default' => [
                    'url' => '#',
                ],
            ]
        );

        $repeater->add_control(
            'slide_image',
            [
                'label' => esc_html__('Imagen del Slide', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'slides',
            [
                'label' => esc_html__('Slides', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'title' => esc_html__('THE NEW STANDARD', 'kinta-electric-elementor'),
                        'subtitle' => esc_html__('UNDER FAVORABLE SMARTWATCHES', 'kinta-electric-elementor'),
                        'price_from' => '299',
                        'price_cents' => '.00',
                        'button_text' => esc_html__('Start Shopping', 'kinta-electric-elementor'),
                    ],
                    [
                        'title' => esc_html__('BEST PRICE', 'kinta-electric-elementor'),
                        'subtitle' => esc_html__('UNDER FAVORABLE SMARTWATCHES', 'kinta-electric-elementor'),
                        'price_from' => '399',
                        'price_cents' => '.00',
                        'button_text' => esc_html__('Start Shopping', 'kinta-electric-elementor'),
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
        <!-- Slider Section -->
        <div class="mb-5">
            <div class="bg-img-hero" style="background-image: url(<?php echo esc_url($settings['background_image']['url']); ?>);">
                <div class="container min-height-420 overflow-hidden">
                    <div class="js-slick-carousel u-slick" data-pagi-classes="text-center position-absolute right-0 bottom-0 left-0 u-slick__pagination u-slick__pagination--long justify-content-start mb-3 mb-md-4 offset-xl-3 pl-2 pb-1">
                        <?php if (!empty($settings['slides'])) : ?>
                            <?php foreach ($settings['slides'] as $slide) : ?>
                                <div class="js-slide bg-img-hero-center">
                                    <div class="row min-height-420 py-7 py-md-0">
                                        <div class="offset-xl-3 col-xl-4 col-6 mt-md-8">
                                            <h1 class="font-size-64 text-lh-57 font-weight-light" data-scs-animation-in="fadeInUp">
                                                <?php echo esc_html($slide['title']); ?>
                                            </h1>
                                            <h6 class="font-size-15 font-weight-bold mb-3" data-scs-animation-in="fadeInUp" data-scs-animation-delay="200">
                                                <?php echo esc_html($slide['subtitle']); ?>
                                            </h6>
                                            <div class="mb-4" data-scs-animation-in="fadeInUp" data-scs-animation-delay="300">
                                                <span class="font-size-13">FROM</span>
                                                <div class="font-size-50 font-weight-bold text-lh-45">
                                                    <sup class="">$</sup><?php echo esc_html($slide['price_from']); ?><sup class=""><?php echo esc_html($slide['price_cents']); ?></sup>
                                                </div>
                                            </div>
                                            <a href="<?php echo esc_url($slide['button_url']['url']); ?>" 
                                               class="btn btn-primary transition-3d-hover rounded-lg font-weight-normal py-2 px-md-7 px-3 font-size-16" 
                                               data-scs-animation-in="fadeInUp" 
                                               data-scs-animation-delay="400"
                                               <?php echo $slide['button_url']['is_external'] ? 'target="_blank"' : ''; ?>
                                               <?php echo $slide['button_url']['nofollow'] ? 'rel="nofollow"' : ''; ?>>
                                                <?php echo esc_html($slide['button_text']); ?>
                                            </a>
                                        </div>
                                        <div class="col-xl-5 col-6 d-flex align-items-center" data-scs-animation-in="zoomIn" data-scs-animation-delay="500">
                                            <img class="img-fluid" src="<?php echo esc_url($slide['slide_image']['url']); ?>" alt="Image Description">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Slider Section -->
        <?php
    }

    /**
     * Renderizar widget en modo de edición
     */
    protected function content_template() {
        ?>
        <!-- Slider Section -->
        <div class="mb-5">
            <div class="bg-img-hero" style="background-image: url({{{ settings.background_image.url }}});">
                <div class="container min-height-420 overflow-hidden">
                    <div class="js-slick-carousel u-slick" data-pagi-classes="text-center position-absolute right-0 bottom-0 left-0 u-slick__pagination u-slick__pagination--long justify-content-start mb-3 mb-md-4 offset-xl-3 pl-2 pb-1">
                        <# if (settings.slides && settings.slides.length > 0) { #>
                            <# _.each(settings.slides, function(slide) { #>
                                <div class="js-slide bg-img-hero-center">
                                    <div class="row min-height-420 py-7 py-md-0">
                                        <div class="offset-xl-3 col-xl-4 col-6 mt-md-8">
                                            <h1 class="font-size-64 text-lh-57 font-weight-light" data-scs-animation-in="fadeInUp">
                                                {{{ slide.title }}}
                                            </h1>
                                            <h6 class="font-size-15 font-weight-bold mb-3" data-scs-animation-in="fadeInUp" data-scs-animation-delay="200">
                                                {{{ slide.subtitle }}}
                                            </h6>
                                            <div class="mb-4" data-scs-animation-in="fadeInUp" data-scs-animation-delay="300">
                                                <span class="font-size-13">FROM</span>
                                                <div class="font-size-50 font-weight-bold text-lh-45">
                                                    <sup class="">$</sup>{{{ slide.price_from }}}<sup class="">{{{ slide.price_cents }}}</sup>
                                                </div>
                                            </div>
                                            <a href="{{{ slide.button_url.url }}}" 
                                               class="btn btn-primary transition-3d-hover rounded-lg font-weight-normal py-2 px-md-7 px-3 font-size-16" 
                                               data-scs-animation-in="fadeInUp" 
                                               data-scs-animation-delay="400"
                                               <# if (slide.button_url.is_external) { #>target="_blank"<# } #>
                                               <# if (slide.button_url.nofollow) { #>rel="nofollow"<# } #>>
                                                {{{ slide.button_text }}}
                                            </a>
                                        </div>
                                        <div class="col-xl-5 col-6 d-flex align-items-center" data-scs-animation-in="zoomIn" data-scs-animation-delay="500">
                                            <img class="img-fluid" src="{{{ slide.slide_image.url }}}" alt="Image Description">
                                        </div>
                                    </div>
                                </div>
                            <# }); #>
                        <# } #>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Slider Section -->
        <?php
    }
}