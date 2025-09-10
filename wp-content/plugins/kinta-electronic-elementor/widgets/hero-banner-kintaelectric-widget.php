<?php
/**
 * Widget Hero Banner KintaElectric
 * 
 * @package KintaElectronicElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Widget Hero Banner KintaElectric
 */
class KEE_Hero_Banner_Kintaelectric_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     */
    public function get_name() {
        return 'hero-banner-kintaelectric';
    }

    /**
     * Get widget title.
     */
    public function get_title() {
        return esc_html__('Hero Banner KintaElectric', 'kinta-electronic-elementor');
    }

    /**
     * Get widget icon.
     */
    public function get_icon() {
        return 'eicon-banner';
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
        return ['hero', 'banner', 'header', 'kintaelectric', 'background', 'image'];
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

        $this->add_control(
            'hero_title',
            [
                'label' => esc_html__('Hero Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('About Us', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'hero_description',
            [
                'label' => esc_html__('Hero Description', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Passion may be a friendly or eager interest in or admiration for a proposal, cause, discovery, or activity or love to a feeling of unusual excitement.', 'kinta-electronic-elementor'),
                'label_block' => true,
                'rows' => 3,
            ]
        );

        $this->add_control(
            'background_image',
            [
                'label' => esc_html__('Background Image', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'min_height',
            [
                'label' => esc_html__('Minimum Height', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh', '%'],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 800,
                        'step' => 10,
                    ],
                    'vh' => [
                        'min' => 20,
                        'max' => 100,
                        'step' => 5,
                    ],
                    '%' => [
                        'min' => 20,
                        'max' => 100,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 564,
                ],
                'selectors' => [
                    '{{WRAPPER}} .min-height-564' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'max_width',
            [
                'label' => esc_html__('Content Max Width', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 300,
                        'max' => 1200,
                        'step' => 10,
                    ],
                    'em' => [
                        'min' => 20,
                        'max' => 80,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 30,
                        'max' => 100,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 620,
                ],
                'selectors' => [
                    '{{WRAPPER}} .max-width-620-lg' => 'max-width: {{SIZE}}{{UNIT}};',
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
            'background_overlay_color',
            [
                'label' => esc_html__('Background Overlay Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .bg-img-hero::before' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'background_overlay_opacity',
            [
                'label' => esc_html__('Background Overlay Opacity', 'kinta-electronic-elementor'),
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
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .bg-img-hero::before' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->add_control(
            'section_margin',
            [
                'label' => esc_html__('Section Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .mb-14' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Title Style Section
        $this->start_controls_section(
            'title_style_section',
            [
                'label' => esc_html__('Title Style', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Title Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .h1.font-weight-bold' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .h1.font-weight-bold',
            ]
        );

        $this->add_control(
            'title_margin',
            [
                'label' => esc_html__('Title Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .h1.font-weight-bold' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Description Style Section
        $this->start_controls_section(
            'description_style_section',
            [
                'label' => esc_html__('Description Style', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => esc_html__('Description Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .text-gray-39.font-size-18' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .text-gray-39.font-size-18',
            ]
        );

        $this->add_control(
            'description_margin',
            [
                'label' => esc_html__('Description Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .text-gray-39.font-size-18' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Background Image Style Section
        $this->start_controls_section(
            'background_image_style_section',
            [
                'label' => esc_html__('Background Image Style', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'background_position',
            [
                'label' => esc_html__('Background Position', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'center center',
                'options' => [
                    'center center' => esc_html__('Center Center', 'kinta-electronic-elementor'),
                    'center left' => esc_html__('Center Left', 'kinta-electronic-elementor'),
                    'center right' => esc_html__('Center Right', 'kinta-electronic-elementor'),
                    'top center' => esc_html__('Top Center', 'kinta-electronic-elementor'),
                    'top left' => esc_html__('Top Left', 'kinta-electronic-elementor'),
                    'top right' => esc_html__('Top Right', 'kinta-electronic-elementor'),
                    'bottom center' => esc_html__('Bottom Center', 'kinta-electronic-elementor'),
                    'bottom left' => esc_html__('Bottom Left', 'kinta-electronic-elementor'),
                    'bottom right' => esc_html__('Bottom Right', 'kinta-electronic-elementor'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .bg-img-hero' => 'background-position: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'background_size',
            [
                'label' => esc_html__('Background Size', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'cover',
                'options' => [
                    'cover' => esc_html__('Cover', 'kinta-electronic-elementor'),
                    'contain' => esc_html__('Contain', 'kinta-electronic-elementor'),
                    'auto' => esc_html__('Auto', 'kinta-electronic-elementor'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .bg-img-hero' => 'background-size: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'background_attachment',
            [
                'label' => esc_html__('Background Attachment', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'scroll',
                'options' => [
                    'scroll' => esc_html__('Scroll', 'kinta-electronic-elementor'),
                    'fixed' => esc_html__('Fixed', 'kinta-electronic-elementor'),
                    'local' => esc_html__('Local', 'kinta-electronic-elementor'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .bg-img-hero' => 'background-attachment: {{VALUE}};',
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
        
        $background_image_url = !empty($settings['background_image']['url']) ? $settings['background_image']['url'] : '';
        $background_style = $background_image_url ? 'background-image: url(' . esc_url($background_image_url) . ');' : '';
        
        ?>
        <div class="bg-img-hero mb-14" style="<?php echo $background_style; ?>">
            <div class="container">
                <div class="flex-content-center max-width-620-lg flex-column mx-auto text-center min-height-564">
                    <?php if (!empty($settings['hero_title'])) : ?>
                        <h1 class="h1 font-weight-bold"><?php echo esc_html($settings['hero_title']); ?></h1>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['hero_description'])) : ?>
                        <p class="text-gray-39 font-size-18 text-lh-default"><?php echo esc_html($settings['hero_description']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor.
     */
    protected function content_template() {
        ?>
        <div class="bg-img-hero mb-14" style="background-image: url({{{ settings.background_image.url }}});">
            <div class="container">
                <div class="flex-content-center max-width-620-lg flex-column mx-auto text-center min-height-564">
                    <# if (settings.hero_title) { #>
                        <h1 class="h1 font-weight-bold">{{{ settings.hero_title }}}</h1>
                    <# } #>
                    
                    <# if (settings.hero_description) { #>
                        <p class="text-gray-39 font-size-18 text-lh-default">{{{ settings.hero_description }}}</p>
                    <# } #>
                </div>
            </div>
        </div>
        <?php
    }
}
