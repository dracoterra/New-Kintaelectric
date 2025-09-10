<?php
/**
 * Widget About Cards KintaElectric
 * 
 * @package KintaElectronicElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Widget About Cards KintaElectric
 */
class KEE_About_Cards_Kintaelectric_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     */
    public function get_name() {
        return 'about-cards-kintaelectric';
    }

    /**
     * Get widget title.
     */
    public function get_title() {
        return esc_html__('About Cards KintaElectric', 'kinta-electronic-elementor');
    }

    /**
     * Get widget icon.
     */
    public function get_icon() {
        return 'eicon-cards';
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
        return ['about', 'cards', 'content', 'kintaelectric', 'vision', 'history'];
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
            'card_image',
            [
                'label' => esc_html__('Card Image', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'card_title',
            [
                'label' => esc_html__('Card Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Card Title', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'card_description',
            [
                'label' => esc_html__('Card Description', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Card description text goes here.', 'kinta-electronic-elementor'),
                'label_block' => true,
                'rows' => 4,
            ]
        );

        $repeater->add_control(
            'card_link',
            [
                'label' => esc_html__('Card Link', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'kinta-electronic-elementor'),
                'default' => [
                    'url' => '',
                    'is_external' => false,
                    'nofollow' => false,
                ],
            ]
        );

        $this->add_control(
            'about_cards',
            [
                'label' => esc_html__('About Cards', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'card_title' => esc_html__('What we really do?', 'kinta-electronic-elementor'),
                        'card_description' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna,vitae ultrices lacus purus vitae metus.', 'kinta-electronic-elementor'),
                    ],
                    [
                        'card_title' => esc_html__('Our Vision', 'kinta-electronic-elementor'),
                        'card_description' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna,vitae ultrices lacus purus vitae metus.', 'kinta-electronic-elementor'),
                    ],
                    [
                        'card_title' => esc_html__('History of Beginning', 'kinta-electronic-elementor'),
                        'card_description' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna,vitae ultrices lacus purus vitae metus.', 'kinta-electronic-elementor'),
                    ],
                ],
                'title_field' => '{{{ card_title }}}',
            ]
        );

        $this->end_controls_section();

        // Layout Section
        $this->start_controls_section(
            'layout_section',
            [
                'label' => esc_html__('Layout', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => esc_html__('Columns', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [
                    '1' => esc_html__('1', 'kinta-electronic-elementor'),
                    '2' => esc_html__('2', 'kinta-electronic-elementor'),
                    '3' => esc_html__('3', 'kinta-electronic-elementor'),
                    '4' => esc_html__('4', 'kinta-electronic-elementor'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .about-card-item' => 'flex: 0 0 calc(100% / {{VALUE}}); max-width: calc(100% / {{VALUE}});',
                ],
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => esc_html__('Image Size', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'medium',
                'options' => [
                    'thumbnail' => esc_html__('Thumbnail', 'kinta-electronic-elementor'),
                    'medium' => esc_html__('Medium', 'kinta-electronic-elementor'),
                    'large' => esc_html__('Large', 'kinta-electronic-elementor'),
                    'full' => esc_html__('Full', 'kinta-electronic-elementor'),
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
            'card_background_color',
            [
                'label' => esc_html__('Card Background Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .card' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'card_border_color',
            [
                'label' => esc_html__('Card Border Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .card' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'card_border_width',
            [
                'label' => esc_html__('Card Border Width', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .card' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'card_border_radius',
            [
                'label' => esc_html__('Card Border Radius', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .card' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .card',
            ]
        );

        $this->add_control(
            'card_padding',
            [
                'label' => esc_html__('Card Padding', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .card-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .font-size-18.font-weight-semi-bold' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .font-size-18.font-weight-semi-bold',
            ]
        );

        $this->add_control(
            'title_margin',
            [
                'label' => esc_html__('Title Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .font-size-18.font-weight-semi-bold' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .text-gray-90' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .text-gray-90',
            ]
        );

        $this->add_control(
            'description_margin',
            [
                'label' => esc_html__('Description Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .text-gray-90' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
            'image_border_radius',
            [
                'label' => esc_html__('Image Border Radius', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .img-fluid' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow',
                'selector' => '{{WRAPPER}} .img-fluid',
            ]
        );

        $this->end_controls_section();

        // Spacing Section
        $this->start_controls_section(
            'spacing_section',
            [
                'label' => esc_html__('Spacing', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'row_margin',
            [
                'label' => esc_html__('Row Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .row' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'card_margin',
            [
                'label' => esc_html__('Card Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .about-card-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
        
        if (empty($settings['about_cards'])) {
            return;
        }
        
        ?>
        <div class="container">
            <div class="row">
                <?php foreach ($settings['about_cards'] as $card) : ?>
                    <div class="col-md-4 mb-4 mb-md-0 about-card-item">
                        <div class="card mb-3 border-0 text-center rounded-0">
                            <?php if (!empty($card['card_image']['url'])) : ?>
                                <img class="img-fluid mb-3" 
                                     src="<?php echo esc_url($card['card_image']['url']); ?>" 
                                     alt="<?php echo esc_attr($card['card_title']); ?>">
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="font-size-18 font-weight-semi-bold mb-3">
                                    <?php if (!empty($card['card_link']['url'])) : ?>
                                        <a href="<?php echo esc_url($card['card_link']['url']); ?>" 
                                           <?php echo $card['card_link']['is_external'] ? 'target="_blank"' : ''; ?>
                                           <?php echo $card['card_link']['nofollow'] ? 'rel="nofollow"' : ''; ?>>
                                            <?php echo esc_html($card['card_title']); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo esc_html($card['card_title']); ?>
                                    <?php endif; ?>
                                </h5>
                                
                                <p class="text-gray-90 max-width-334 mx-auto">
                                    <?php echo esc_html($card['card_description']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor.
     */
    protected function content_template() {
        ?>
        <div class="container">
            <div class="row">
                <# if (settings.about_cards && settings.about_cards.length) { #>
                    <# _.each(settings.about_cards, function(card) { #>
                        <div class="col-md-4 mb-4 mb-md-0 about-card-item">
                            <div class="card mb-3 border-0 text-center rounded-0">
                                <# if (card.card_image && card.card_image.url) { #>
                                    <img class="img-fluid mb-3" 
                                         src="{{{ card.card_image.url }}}" 
                                         alt="{{{ card.card_title }}}">
                                <# } #>
                                
                                <div class="card-body">
                                    <h5 class="font-size-18 font-weight-semi-bold mb-3">
                                        <# if (card.card_link && card.card_link.url) { #>
                                            <a href="{{{ card.card_link.url }}}">
                                                {{{ card.card_title }}}
                                            </a>
                                        <# } else { #>
                                            {{{ card.card_title }}}
                                        <# } #>
                                    </h5>
                                    
                                    <p class="text-gray-90 max-width-334 mx-auto">
                                        {{{ card.card_description }}}
                                    </p>
                                </div>
                            </div>
                        </div>
                    <# }); #>
                <# } #>
            </div>
        </div>
        <?php
    }
}

