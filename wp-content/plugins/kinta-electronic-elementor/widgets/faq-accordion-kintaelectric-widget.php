<?php
/**
 * Widget FAQ Accordion KintaElectric
 * 
 * @package KintaElectronicElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Widget FAQ Accordion KintaElectric
 */
class KEE_FAQ_Accordion_Kintaelectric_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     */
    public function get_name() {
        return 'faq-accordion-kintaelectric';
    }

    /**
     * Get widget title.
     */
    public function get_title() {
        return esc_html__('FAQ Accordion KintaElectric', 'kinta-electronic-elementor');
    }

    /**
     * Get widget icon.
     */
    public function get_icon() {
        return 'eicon-accordion';
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
        return ['faq', 'accordion', 'questions', 'answers', 'kintaelectric', 'help', 'shipping'];
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
            'section_title',
            [
                'label' => esc_html__('Section Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Frequently Asked Questions', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'section_subtitle',
            [
                'label' => esc_html__('Section Subtitle', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('This Agreement was last modified on 18th february 2019', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        // Shipping Information Section
        $this->start_controls_section(
            'shipping_section',
            [
                'label' => esc_html__('Shipping Information', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'shipping_section_title',
            [
                'label' => esc_html__('Shipping Section Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Shipping Information', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $shipping_repeater = new \Elementor\Repeater();

        $shipping_repeater->add_control(
            'shipping_question',
            [
                'label' => esc_html__('Shipping Question', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('What is your shipping question?', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $shipping_repeater->add_control(
            'shipping_answer',
            [
                'label' => esc_html__('Shipping Answer', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'shipping_info_items',
            [
                'label' => esc_html__('Shipping Info Items', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $shipping_repeater->get_controls(),
                'default' => [
                    [
                        'shipping_question' => esc_html__('What Shipping Methods Are Available?', 'kinta-electronic-elementor'),
                        'shipping_answer' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sapien lorem, consectetur et turpis id, blandit interdum metus. Morbi sed ligula id elit mollis efficitur ut nec ligula. Proin erat magna, pellentesque at elementum at, eleifend a tortor.', 'kinta-electronic-elementor'),
                    ],
                    [
                        'shipping_question' => esc_html__('How Long Will it Take To Get My Package?', 'kinta-electronic-elementor'),
                        'shipping_answer' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sapien lorem, consectetur et turpis id, blandit interdum metus. Morbi sed ligula id elit mollis efficitur ut nec ligula. Proin erat magna, pellentesque at elementum at, eleifend a tortor.', 'kinta-electronic-elementor'),
                    ],
                    [
                        'shipping_question' => esc_html__('How Do I Track My Order?', 'kinta-electronic-elementor'),
                        'shipping_answer' => esc_html__('Integer ex turpis, venenatis vitae nibh vel, vestibulum maximus quam. Ut pretium orci ac vestibulum porttitor. Fusce tempus diam quis justo porttitor gravida.', 'kinta-electronic-elementor'),
                    ],
                    [
                        'shipping_question' => esc_html__('Do I Need A Account To Place Order?', 'kinta-electronic-elementor'),
                        'shipping_answer' => esc_html__('Integer ex turpis, venenatis vitae nibh vel, vestibulum maximus quam. Ut pretium orci ac vestibulum porttitor. Fusce tempus diam quis justo porttitor gravida.', 'kinta-electronic-elementor'),
                    ],
                ],
                'title_field' => '{{{ shipping_question }}}',
            ]
        );

        $this->end_controls_section();

        // FAQ Accordion Section
        $this->start_controls_section(
            'faq_section',
            [
                'label' => esc_html__('FAQ Accordion', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'faq_second_title',
            [
                'label' => esc_html__('FAQ Second Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('FAQ Second Version', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $faq_repeater = new \Elementor\Repeater();

        $faq_repeater->add_control(
            'question',
            [
                'label' => esc_html__('Question', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('What is your question?', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $faq_repeater->add_control(
            'answer',
            [
                'label' => esc_html__('Answer', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $faq_repeater->add_control(
            'is_open',
            [
                'label' => esc_html__('Open by Default', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kinta-electronic-elementor'),
                'label_off' => esc_html__('No', 'kinta-electronic-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'faq_items',
            [
                'label' => esc_html__('FAQ Items', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $faq_repeater->get_controls(),
                'default' => [
                    [
                        'question' => esc_html__('What Shipping Methods Are Available?', 'kinta-electronic-elementor'),
                        'answer' => esc_html__('In egestas, libero vitae scelerisque tristique, turpis augue faucibus dolor, at aliquet ligula massa at justo. Donec viverra tortor quis tortor pretium, in pretium risus finibus. Integer viverra pretium auctor. Aliquam eget convallis eros, varius sagittis nulla. Suspendisse potenti. Aenean consequat ex sit amet metus ultrices tristique. Nam ac nunc augue. Suspendisse finibus in dolor eget volutpat.', 'kinta-electronic-elementor'),
                        'is_open' => 'yes',
                    ],
                    [
                        'question' => esc_html__('How Long Will it Take To Get My Package?', 'kinta-electronic-elementor'),
                        'answer' => esc_html__('In egestas, libero vitae scelerisque tristique, turpis augue faucibus dolor, at aliquet ligula massa at justo. Donec viverra tortor quis tortor pretium, in pretium risus finibus. Integer viverra pretium auctor. Aliquam eget convallis eros, varius sagittis nulla. Suspendisse potenti. Aenean consequat ex sit amet metus ultrices tristique. Nam ac nunc augue. Suspendisse finibus in dolor eget volutpat.', 'kinta-electronic-elementor'),
                        'is_open' => 'no',
                    ],
                    [
                        'question' => esc_html__('How Do I Track My Order?', 'kinta-electronic-elementor'),
                        'answer' => esc_html__('In egestas, libero vitae scelerisque tristique, turpis augue faucibus dolor, at aliquet ligula massa at justo. Donec viverra tortor quis tortor pretium, in pretium risus finibus. Integer viverra pretium auctor. Aliquam eget convallis eros, varius sagittis nulla. Suspendisse potenti. Aenean consequat ex sit amet metus ultrices tristique. Nam ac nunc augue. Suspendisse finibus in dolor eget volutpat.', 'kinta-electronic-elementor'),
                        'is_open' => 'no',
                    ],
                    [
                        'question' => esc_html__('How Do I Place an Order?', 'kinta-electronic-elementor'),
                        'answer' => esc_html__('In egestas, libero vitae scelerisque tristique, turpis augue faucibus dolor, at aliquet ligula massa at justo. Donec viverra tortor quis tortor pretium, in pretium risus finibus. Integer viverra pretium auctor. Aliquam eget convallis eros, varius sagittis nulla. Suspendisse potenti. Aenean consequat ex sit amet metus ultrices tristique. Nam ac nunc augue. Suspendisse finibus in dolor eget volutpat.', 'kinta-electronic-elementor'),
                        'is_open' => 'no',
                    ],
                    [
                        'question' => esc_html__('How Should I to Contact if I Have Any Queries?', 'kinta-electronic-elementor'),
                        'answer' => esc_html__('In egestas, libero vitae scelerisque tristique, turpis augue faucibus dolor, at aliquet ligula massa at justo. Donec viverra tortor quis tortor pretium, in pretium risus finibus. Integer viverra pretium auctor. Aliquam eget convallis eros, varius sagittis nulla. Suspendisse potenti. Aenean consequat ex sit amet metus ultrices tristique. Nam ac nunc augue. Suspendisse finibus in dolor eget volutpat.', 'kinta-electronic-elementor'),
                        'is_open' => 'no',
                    ],
                    [
                        'question' => esc_html__('Do I Need an Account to Place an Order?', 'kinta-electronic-elementor'),
                        'answer' => esc_html__('In egestas, libero vitae scelerisque tristique, turpis augue faucibus dolor, at aliquet ligula massa at justo. Donec viverra tortor quis tortor pretium, in pretium risus finibus. Integer viverra pretium auctor. Aliquam eget convallis eros, varius sagittis nulla. Suspendisse potenti. Aenean consequat ex sit amet metus ultrices tristique. Nam ac nunc augue. Suspendisse finibus in dolor eget volutpat.', 'kinta-electronic-elementor'),
                        'is_open' => 'no',
                    ],
                ],
                'title_field' => '{{{ question }}}',
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
            'title_color',
            [
                'label' => esc_html__('Title Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} h1' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'subtitle_color',
            [
                'label' => esc_html__('Subtitle Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .text-gray-44' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'shipping_title_color',
            [
                'label' => esc_html__('Shipping Title Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'question_color',
            [
                'label' => esc_html__('Question Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .font-size-18.font-weight-semi-bold.text-gray-39' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'answer_color',
            [
                'label' => esc_html__('Answer Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .text-gray-90' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'border_color',
            [
                'label' => esc_html__('Border Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .border-color-1' => 'border-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->end_controls_section();

        // Typography Section
        $this->start_controls_section(
            'typography_section',
            [
                'label' => esc_html__('Typography', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'main_title_typography',
                'label' => esc_html__('Main Title Typography', 'kinta-electronic-elementor'),
                'selector' => '{{WRAPPER}} h1',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'subtitle_typography',
                'label' => esc_html__('Subtitle Typography', 'kinta-electronic-elementor'),
                'selector' => '{{WRAPPER}} .text-gray-44',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'shipping_title_typography',
                'label' => esc_html__('Shipping Title Typography', 'kinta-electronic-elementor'),
                'selector' => '{{WRAPPER}} .section-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'question_typography',
                'label' => esc_html__('Question Typography', 'kinta-electronic-elementor'),
                'selector' => '{{WRAPPER}} .font-size-18.font-weight-semi-bold.text-gray-39',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'answer_typography',
                'label' => esc_html__('Answer Typography', 'kinta-electronic-elementor'),
                'selector' => '{{WRAPPER}} .text-gray-90',
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
            'section_margin',
            [
                'label' => esc_html__('Section Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .mb-12' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'shipping_section_margin',
            [
                'label' => esc_html__('Shipping Section Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .mb-8' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'accordion_margin',
            [
                'label' => esc_html__('Accordion Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .mb-12' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .container' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
        $accordion_id = 'basicsAccordion-' . $this->get_id();

        ?>
        <div class="container">
            <div class="mb-12 text-center">
                <h1><?php echo esc_html($settings['section_title']); ?></h1>
                <?php if (!empty($settings['section_subtitle'])) : ?>
                    <p class="text-gray-44"><?php echo esc_html($settings['section_subtitle']); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($settings['shipping_info_items'])) : ?>
                <div class="border-bottom border-color-1 mb-8 rounded-0">
                    <h3 class="section-title mb-0 pb-2 font-size-25">
                        <?php echo esc_html($settings['shipping_section_title']); ?>
                    </h3>
                </div>
                <div class="row mb-8">
                    <?php foreach ($settings['shipping_info_items'] as $item) : ?>
                        <div class="col-lg-6 mb-5 mb-lg-8">
                            <h3 class="font-size-18 font-weight-semi-bold text-gray-39 mb-4">
                                <?php echo esc_html($item['shipping_question']); ?>
                            </h3>
                            <p class="text-gray-90">
                                <?php echo esc_html($item['shipping_answer']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($settings['faq_items'])) : ?>
                <div class="mb-12 text-center">
                    <h1><?php echo esc_html($settings['faq_second_title']); ?></h1>
                </div>
                
                <!-- Basics Accordion -->
                <div id="<?php echo esc_attr($accordion_id); ?>" class="mb-12">
                    <?php foreach ($settings['faq_items'] as $index => $item) : ?>
                        <!-- Card -->
                        <div class="card mb-3 border-top-0 border-left-0 border-right-0 border-color-1 rounded-0">
                            <div class="card-header card-collapse bg-transparent-on-hover border-0" id="basicsHeading<?php echo $index; ?>">
                                <h5 class="mb-0">
                                    <button type="button" 
                                            class="px-0 btn btn-link btn-block d-flex justify-content-between card-btn py-3 font-size-25 border-0 <?php echo $item['is_open'] === 'yes' ? '' : 'collapsed'; ?>" 
                                            data-toggle="collapse" 
                                            data-target="#basicsCollapse<?php echo $index; ?>" 
                                            aria-expanded="<?php echo $item['is_open'] === 'yes' ? 'true' : 'false'; ?>" 
                                            aria-controls="basicsCollapse<?php echo $index; ?>">
                                        <?php echo esc_html($item['question']); ?>
                                        <span class="card-btn-arrow">
                                            <i class="fas fa-chevron-down text-gray-90 font-size-18"></i>
                                        </span>
                                    </button>
                                </h5>
                            </div>
                            <div id="basicsCollapse<?php echo $index; ?>" 
                                 class="collapse <?php echo $item['is_open'] === 'yes' ? 'show' : ''; ?>" 
                                 aria-labelledby="basicsHeading<?php echo $index; ?>" 
                                 data-parent="#<?php echo esc_attr($accordion_id); ?>">
                                <div class="card-body pl-0 <?php echo $index === count($settings['faq_items']) - 1 ? '' : 'pb-8'; ?>">
                                    <p class="mb-0"><?php echo esc_html($item['answer']); ?></p>
                                </div>
                            </div>
                        </div>
                        <!-- End Card -->
                    <?php endforeach; ?>
                </div>
                <!-- End Basics Accordion -->
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor.
     */
    protected function content_template() {
        ?>
        <div class="container">
            <div class="mb-12 text-center">
                <h1>{{{ settings.section_title }}}</h1>
                <# if (settings.section_subtitle) { #>
                    <p class="text-gray-44">{{{ settings.section_subtitle }}}</p>
                <# } #>
            </div>
            
            <# if (settings.shipping_info_items && settings.shipping_info_items.length) { #>
                <div class="border-bottom border-color-1 mb-8 rounded-0">
                    <h3 class="section-title mb-0 pb-2 font-size-25">
                        {{{ settings.shipping_section_title }}}
                    </h3>
                </div>
                <div class="row mb-8">
                    <# _.each(settings.shipping_info_items, function(item) { #>
                        <div class="col-lg-6 mb-5 mb-lg-8">
                            <h3 class="font-size-18 font-weight-semi-bold text-gray-39 mb-4">
                                {{{ item.shipping_question }}}
                            </h3>
                            <p class="text-gray-90">
                                {{{ item.shipping_answer }}}
                            </p>
                        </div>
                    <# }); #>
                </div>
            <# } #>

            <# if (settings.faq_items && settings.faq_items.length) { #>
                <div class="mb-12 text-center">
                    <h1>{{{ settings.faq_second_title }}}</h1>
                </div>
                
                <!-- Basics Accordion -->
                <div id="basicsAccordion-{{ view.getID() }}" class="mb-12">
                    <# _.each(settings.faq_items, function(item, index) { #>
                        <!-- Card -->
                        <div class="card mb-3 border-top-0 border-left-0 border-right-0 border-color-1 rounded-0">
                            <div class="card-header card-collapse bg-transparent-on-hover border-0" id="basicsHeading{{ index }}">
                                <h5 class="mb-0">
                                    <button type="button" 
                                            class="px-0 btn btn-link btn-block d-flex justify-content-between card-btn py-3 font-size-25 border-0 <# if (item.is_open !== 'yes') { #>collapsed<# } #>" 
                                            data-toggle="collapse" 
                                            data-target="#basicsCollapse{{ index }}" 
                                            aria-expanded="<# if (item.is_open === 'yes') { #>true<# } else { #>false<# } #>" 
                                            aria-controls="basicsCollapse{{ index }}">
                                        {{{ item.question }}}
                                        <span class="card-btn-arrow">
                                            <i class="fas fa-chevron-down text-gray-90 font-size-18"></i>
                                        </span>
                                    </button>
                                </h5>
                            </div>
                            <div id="basicsCollapse{{ index }}" 
                                 class="collapse <# if (item.is_open === 'yes') { #>show<# } #>" 
                                 aria-labelledby="basicsHeading{{ index }}" 
                                 data-parent="#basicsAccordion-{{ view.getID() }}">
                                <div class="card-body pl-0 <# if (index === settings.faq_items.length - 1) { #><# } else { #>pb-8<# } #>">
                                    <p class="mb-0">{{{ item.answer }}}</p>
                                </div>
                            </div>
                        </div>
                        <!-- End Card -->
                    <# }); #>
                </div>
                <!-- End Basics Accordion -->
            <# } #>
        </div>
        <?php
    }
}
