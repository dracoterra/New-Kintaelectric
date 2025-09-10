<?php
/**
 * Widget Brand Carousel KintaElectric
 * 
 * @package KintaElectronicElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Widget Brand Carousel KintaElectric
 */
class KEE_Brand_Carousel_Kintaelectric_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     */
    public function get_name() {
        return 'brand-carousel-kintaelectric';
    }

    /**
     * Get widget title.
     */
    public function get_title() {
        return esc_html__('Brand Carousel KintaElectric', 'kinta-electronic-elementor');
    }

    /**
     * Get widget icon.
     */
    public function get_icon() {
        return 'eicon-carousel';
    }

    /**
     * Get widget categories.
     */
    public function get_categories() {
        return ['kinta-electric'];
    }

    /**
     * Get widget keywords.
     */
    public function get_keywords() {
        return ['brand', 'carousel', 'slider', 'logos', 'kintaelectric', 'partners'];
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'brand_image',
            [
                'label' => esc_html__('Brand Image', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'brand_link',
            [
                'label' => esc_html__('Brand Link', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'kinta-electronic-elementor'),
                'default' => [
                    'url' => '#',
                    'is_external' => true,
                    'nofollow' => true,
                    'custom_attributes' => '',
                ],
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'brand_alt',
            [
                'label' => esc_html__('Alt Text', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Brand Logo', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'brands',
            [
                'label' => esc_html__('Brands', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'brand_alt' => esc_html__('Brand 1', 'kinta-electronic-elementor'),
                    ],
                    [
                        'brand_alt' => esc_html__('Brand 2', 'kinta-electronic-elementor'),
                    ],
                    [
                        'brand_alt' => esc_html__('Brand 3', 'kinta-electronic-elementor'),
                    ],
                    [
                        'brand_alt' => esc_html__('Brand 4', 'kinta-electronic-elementor'),
                    ],
                    [
                        'brand_alt' => esc_html__('Brand 5', 'kinta-electronic-elementor'),
                    ],
                    [
                        'brand_alt' => esc_html__('Brand 6', 'kinta-electronic-elementor'),
                    ],
                ],
                'title_field' => '{{{ brand_alt }}}',
            ]
        );

        $this->end_controls_section();

        // Carousel Settings Section
        $this->start_controls_section(
            'carousel_section',
            [
                'label' => esc_html__('Carousel Settings', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'slides_to_show',
            [
                'label' => esc_html__('Slides to Show', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'default' => 5,
            ]
        );

        $this->add_control(
            'slides_to_scroll',
            [
                'label' => esc_html__('Slides to Scroll', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 5,
                'default' => 1,
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => esc_html__('Autoplay', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kinta-electronic-elementor'),
                'label_off' => esc_html__('No', 'kinta-electronic-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'autoplay_speed',
            [
                'label' => esc_html__('Autoplay Speed', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1000,
                'max' => 10000,
                'step' => 500,
                'default' => 3000,
                'condition' => [
                    'autoplay' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Style', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'border_color',
            [
                'label' => esc_html__('Border Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .border-color-4' => 'border-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'padding',
            [
                'label' => esc_html__('Padding', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .py-5' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Image Style Section
        $this->start_controls_section(
            'image_style_section',
            [
                'label' => esc_html__('Image Style', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => esc_html__('Image Size', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 300,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 120,
                ],
                'selectors' => [
                    '{{WRAPPER}} .img-fluid' => 'max-width: {{SIZE}}{{UNIT}}; height: auto;',
                ],
            ]
        );

        $this->add_control(
            'image_opacity',
            [
                'label' => esc_html__('Image Opacity', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .img-fluid' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->add_control(
            'image_hover_opacity',
            [
                'label' => esc_html__('Image Hover Opacity', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 80,
                ],
                'selectors' => [
                    '{{WRAPPER}} .img-fluid:hover' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->add_control(
            'image_transition',
            [
                'label' => esc_html__('Image Transition', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['s'],
                'range' => [
                    's' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 's',
                    'size' => 0.3,
                ],
                'selectors' => [
                    '{{WRAPPER}} .img-fluid' => 'transition: opacity {{SIZE}}s ease;',
                ],
            ]
        );

        $this->end_controls_section();

        // Background Section
        $this->start_controls_section(
            'background_section',
            [
                'label' => esc_html__('Background', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'section_background_color',
            [
                'label' => esc_html__('Section Background Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .bg-gray-1' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'section_padding',
            [
                'label' => esc_html__('Section Padding', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .bg-gray-1' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Carousel Controls Style Section
        $this->start_controls_section(
            'carousel_controls_style_section',
            [
                'label' => esc_html__('Carousel Controls Style', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'arrow_color',
            [
                'label' => esc_html__('Arrow Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slick-arrow' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'arrow_background_color',
            [
                'label' => esc_html__('Arrow Background Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slick-arrow' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'arrow_size',
            [
                'label' => esc_html__('Arrow Size', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .slick-arrow' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        if (empty($settings['brands'])) {
            return;
        }

        $carousel_id = 'brand-carousel-' . $this->get_id();
        $slides_to_show = $settings['slides_to_show'] ?? 5;
        $slides_to_scroll = $settings['slides_to_scroll'] ?? 1;
        $autoplay = $settings['autoplay'] === 'yes' ? 'true' : 'false';
        $autoplay_speed = $settings['autoplay_speed'] ?? 3000;

        ?>
        <!-- Brand Carousel -->
        <div class="mb-8">
            <div class="py-2 border-top border-bottom">
                <div class="js-slick-carousel u-slick my-1" 
                     id="<?php echo esc_attr($carousel_id); ?>"
                     data-slides-show="<?php echo esc_attr($slides_to_show); ?>" 
                     data-slides-scroll="<?php echo esc_attr($slides_to_scroll); ?>" 
                     data-arrows-classes="d-none d-lg-inline-block u-slick__arrow-normal u-slick__arrow-centered--y" 
                     data-arrow-left-classes="fa fa-angle-left u-slick__arrow-classic-inner--left z-index-9" 
                     data-arrow-right-classes="fa fa-angle-right u-slick__arrow-classic-inner--right" 
                     data-autoplay="<?php echo esc_attr($autoplay); ?>"
                     data-autoplay-speed="<?php echo esc_attr($autoplay_speed); ?>"
                     data-responsive='[{
                         "breakpoint": 992,
                         "settings": {
                             "slidesToShow": <?php echo min(2, $slides_to_show); ?>
                         }
                     }, {
                         "breakpoint": 768,
                         "settings": {
                             "slidesToShow": <?php echo min(1, $slides_to_show); ?>
                         }
                     }, {
                         "breakpoint": 554,
                         "settings": {
                             "slidesToShow": <?php echo min(1, $slides_to_show); ?>
                         }
                     }]'>
                    <?php foreach ($settings['brands'] as $brand) : ?>
                        <div class="js-slide">
                            <a href="<?php echo esc_url($brand['brand_link']['url'] ?? '#'); ?>" 
                               class="link-hover__brand"
                               <?php echo isset($brand['brand_link']['is_external']) && $brand['brand_link']['is_external'] ? 'target="_blank"' : ''; ?>
                               <?php echo isset($brand['brand_link']['nofollow']) && $brand['brand_link']['nofollow'] ? 'rel="nofollow"' : ''; ?>>
                                <img class="img-fluid m-auto max-height-50" 
                                     src="<?php echo esc_url($brand['brand_image']['url']); ?>" 
                                     alt="<?php echo esc_attr($brand['brand_alt']); ?>">
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- End Brand Carousel -->
        <?php
    }

    /**
     * Render widget output in the editor.
     */
    protected function content_template() {
        ?>
        <!-- Brand Carousel -->
        <div class="mb-8">
            <div class="py-2 border-top border-bottom">
                <div class="js-slick-carousel u-slick my-1" 
                     data-slides-show="{{{ settings.slides_to_show || 5 }}}" 
                     data-slides-scroll="{{{ settings.slides_to_scroll || 1 }}}" 
                     data-arrows-classes="d-none d-lg-inline-block u-slick__arrow-normal u-slick__arrow-centered--y" 
                     data-arrow-left-classes="fa fa-angle-left u-slick__arrow-classic-inner--left z-index-9" 
                     data-arrow-right-classes="fa fa-angle-right u-slick__arrow-classic-inner--right" 
                     data-autoplay="{{{ settings.autoplay === 'yes' ? 'true' : 'false' }}}"
                     data-autoplay-speed="{{{ settings.autoplay_speed || 3000 }}}"
                     data-responsive='[{
                         "breakpoint": 992,
                         "settings": {
                             "slidesToShow": <# if (settings.slides_to_show > 2) { #>2<# } else { #>{{{ settings.slides_to_show }}}<# } #>
                         }
                     }, {
                         "breakpoint": 768,
                         "settings": {
                             "slidesToShow": 1
                         }
                     }, {
                         "breakpoint": 554,
                         "settings": {
                             "slidesToShow": 1
                         }
                     }]'>
                    <# if (settings.brands && settings.brands.length) { #>
                        <# _.each(settings.brands, function(brand) { #>
                            <div class="js-slide">
                                <a href="{{{ brand.brand_link && brand.brand_link.url ? brand.brand_link.url : '#' }}}" 
                                   class="link-hover__brand"
                                   <# if (brand.brand_link && brand.brand_link.is_external) { #>target="_blank"<# } #>
                                   <# if (brand.brand_link && brand.brand_link.nofollow) { #>rel="nofollow"<# } #>>
                                    <img class="img-fluid m-auto max-height-50" 
                                         src="{{{ brand.brand_image.url }}}" 
                                         alt="{{{ brand.brand_alt }}}">
                                </a>
                            </div>
                        <# }); #>
                    <# } #>
                </div>
            </div>
        </div>
        <!-- End Brand Carousel -->
        <?php
    }
}
