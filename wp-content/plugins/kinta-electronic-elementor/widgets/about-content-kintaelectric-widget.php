<?php
/**
 * Widget About Content KintaElectric
 * 
 * @package KintaElectronicElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Widget About Content KintaElectric
 */
class KEE_About_Content_Kintaelectric_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     */
    public function get_name() {
        return 'about-content-kintaelectric';
    }

    /**
     * Get widget title.
     */
    public function get_title() {
        return esc_html__('About Content KintaElectric', 'kinta-electronic-elementor');
    }

    /**
     * Get widget icon.
     */
    public function get_icon() {
        return 'eicon-text';
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
        return ['about', 'content', 'company', 'kintaelectric', 'information', 'services', 'accordion'];
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
            'content_title',
            [
                'label' => esc_html__('Content Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Content Title', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'content_text',
            [
                'label' => esc_html__('Content Text', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'about_contents',
            [
                'label' => esc_html__('About Contents', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'content_title' => esc_html__('What we really do?', 'kinta-electronic-elementor'),
                        'content_text' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna, vitae ultrices lacus purus vitae metus. Ut nec odio facilisis, ultricies nunc eget, fringilla orci.', 'kinta-electronic-elementor'),
                    ],
                    [
                        'content_title' => esc_html__('Our Vision', 'kinta-electronic-elementor'),
                        'content_text' => esc_html__('Vestibulum velit nibh, egestas vel faucibus vitae, feugiat sollicitudin urna. Praesent iaculis id ipsum sit amet pretium. Aliquam tristique sapien nec enim euismod, scelerisque facilisis arcu consectetur. Vestibulum velit nibh, egestas vel faucibus vitae.', 'kinta-electronic-elementor'),
                    ],
                    [
                        'content_title' => esc_html__('History of the Company', 'kinta-electronic-elementor'),
                        'content_text' => esc_html__('Mauris rhoncus aliquet purus, a ornare nisi euismod in. Interdum et malesuada fames ac ante ipsum primis in faucibus. Etiam imperdiet eu metus vel ornare. Nullam in risus vel orci feugiat vestibulum. In sed aliquam mi. Nullam condimentum sollicitudin dui.', 'kinta-electronic-elementor'),
                    ],
                    [
                        'content_title' => esc_html__('Cooperate with Us!', 'kinta-electronic-elementor'),
                        'content_text' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna, vitae ultrices lacus purus vitae metus. Ut nec odio facilisis, ultricies nunc eget, fringilla orci.', 'kinta-electronic-elementor'),
                    ],
                ],
                'title_field' => '{{{ content_title }}}',
            ]
        );

        $this->add_control(
            'right_column_title',
            [
                'label' => esc_html__('Right Column Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('What can we do for you ?', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        // Services Accordion Section
        $this->start_controls_section(
            'services_section',
            [
                'label' => esc_html__('Services Accordion', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $services_repeater = new \Elementor\Repeater();

        $services_repeater->add_control(
            'service_title',
            [
                'label' => esc_html__('Service Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Service Title', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $services_repeater->add_control(
            'service_description',
            [
                'label' => esc_html__('Service Description', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $services_repeater->add_control(
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
            'services',
            [
                'label' => esc_html__('Services', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $services_repeater->get_controls(),
                'default' => [
                    [
                        'service_title' => esc_html__('Support 24/7', 'kinta-electronic-elementor'),
                        'service_description' => esc_html__('Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven\'t heard of them accusamus labore sustainable VHS.', 'kinta-electronic-elementor'),
                        'is_open' => 'yes',
                    ],
                    [
                        'service_title' => esc_html__('Best Quality', 'kinta-electronic-elementor'),
                        'service_description' => esc_html__('Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven\'t heard of them accusamus labore sustainable VHS.', 'kinta-electronic-elementor'),
                        'is_open' => 'no',
                    ],
                    [
                        'service_title' => esc_html__('Fastest Delivery', 'kinta-electronic-elementor'),
                        'service_description' => esc_html__('Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven\'t heard of them accusamus labore sustainable VHS.', 'kinta-electronic-elementor'),
                        'is_open' => 'no',
                    ],
                    [
                        'service_title' => esc_html__('Customer Care', 'kinta-electronic-elementor'),
                        'service_description' => esc_html__('Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven\'t heard of them accusamus labore sustainable VHS.', 'kinta-electronic-elementor'),
                        'is_open' => 'no',
                    ],
                    [
                        'service_title' => esc_html__('Over 200 Satisfied Customers', 'kinta-electronic-elementor'),
                        'service_description' => esc_html__('Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven\'t heard of them accusamus labore sustainable VHS.', 'kinta-electronic-elementor'),
                        'is_open' => 'no',
                    ],
                ],
                'title_field' => '{{{ service_title }}}',
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
                    '{{WRAPPER}} .font-size-18.font-weight-semi-bold.text-gray-39' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => esc_html__('Text Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .text-gray-90' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'card_border_color',
            [
                'label' => esc_html__('Card Border Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .border-color-4' => 'border-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label' => esc_html__('Button Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .btn-link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => esc_html__('Icon Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fas' => 'color: {{VALUE}};',
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
                'name' => 'title_typography',
                'label' => esc_html__('Title Typography', 'kinta-electronic-elementor'),
                'selector' => '{{WRAPPER}} .font-size-18.font-weight-semi-bold.text-gray-39',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'label' => esc_html__('Text Typography', 'kinta-electronic-elementor'),
                'selector' => '{{WRAPPER}} .text-gray-90',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'accordion_title_typography',
                'label' => esc_html__('Accordion Title Typography', 'kinta-electronic-elementor'),
                'selector' => '{{WRAPPER}} .btn-link',
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
            'content_margin',
            [
                'label' => esc_html__('Content Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .mb-5.mb-lg-8' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_margin',
            [
                'label' => esc_html__('Title Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .font-size-18.font-weight-semi-bold.text-gray-39' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'text_margin',
            [
                'label' => esc_html__('Text Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .text-gray-90' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .ml-lg-8' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
        $accordion_id = 'about-accordion-' . $this->get_id();

        ?>
        <div class="container mb-8 mb-lg-0">
            <div class="row mb-8">
                <div class="col-lg-7">
                    <div class="row">
                        <?php if (!empty($settings['about_contents'])) : ?>
                            <?php foreach ($settings['about_contents'] as $content) : ?>
                                <div class="col-lg-6 mb-5 mb-lg-8">
                                    <h3 class="font-size-18 font-weight-semi-bold text-gray-39 mb-4">
                                        <?php echo esc_html($content['content_title']); ?>
                                    </h3>
                                    <p class="text-gray-90">
                                        <?php echo esc_html($content['content_text']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="ml-lg-8">
                        <h3 class="font-size-18 font-weight-semi-bold text-gray-39 mb-4">
                            <?php echo esc_html($settings['right_column_title']); ?>
                        </h3>
                        
                        <?php if (!empty($settings['services'])) : ?>
                            <!-- Basics Accordion -->
                            <div id="<?php echo esc_attr($accordion_id); ?>" class="about-accordion">
                                <?php foreach ($settings['services'] as $index => $service) : ?>
                                    <!-- Card -->
                                    <div class="card mb-4 border-color-4 rounded-0">
                                        <div class="card-header card-collapse border-color-4" id="basicsHeading<?php echo $index; ?>">
                                            <h5 class="mb-0">
                                                <button type="button" 
                                                        class="btn btn-link btn-block flex-horizontal-center card-btn p-0 font-size-18" 
                                                        data-toggle="collapse" 
                                                        data-target="#basicsCollapse<?php echo $index; ?>" 
                                                        aria-expanded="<?php echo $service['is_open'] === 'yes' ? 'true' : 'false'; ?>" 
                                                        aria-controls="basicsCollapse<?php echo $index; ?>">
                                                    <span class="border border-color-5 rounded font-size-12 mr-5">
                                                        <i class="fas fa-plus"></i>
                                                        <i class="fas fa-minus"></i>
                                                    </span>
                                                    <?php echo esc_html($service['service_title']); ?>
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="basicsCollapse<?php echo $index; ?>" 
                                             class="collapse <?php echo $service['is_open'] === 'yes' ? 'show' : ''; ?>" 
                                             aria-labelledby="basicsHeading<?php echo $index; ?>" 
                                             data-parent="#<?php echo esc_attr($accordion_id); ?>">
                                            <div class="card-body">
                                                <p class="mb-0"><?php echo esc_html($service['service_description']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Card -->
                                <?php endforeach; ?>
                            </div>
                            <!-- End Basics Accordion -->
                        <?php endif; ?>
                    </div>
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
        <div class="container mb-8 mb-lg-0">
            <div class="row mb-8">
                <div class="col-lg-7">
                    <div class="row">
                        <# if (settings.about_contents && settings.about_contents.length) { #>
                            <# _.each(settings.about_contents, function(content) { #>
                                <div class="col-lg-6 mb-5 mb-lg-8">
                                    <h3 class="font-size-18 font-weight-semi-bold text-gray-39 mb-4">
                                        {{{ content.content_title }}}
                                    </h3>
                                    <p class="text-gray-90">
                                        {{{ content.content_text }}}
                                    </p>
                                </div>
                            <# }); #>
                        <# } #>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="ml-lg-8">
                        <h3 class="font-size-18 font-weight-semi-bold text-gray-39 mb-4">
                            {{{ settings.right_column_title }}}
                        </h3>
                        
                        <# if (settings.services && settings.services.length) { #>
                            <!-- Basics Accordion -->
                            <div id="about-accordion-{{ view.getID() }}" class="about-accordion">
                                <# _.each(settings.services, function(service, index) { #>
                                    <!-- Card -->
                                    <div class="card mb-4 border-color-4 rounded-0">
                                        <div class="card-header card-collapse border-color-4" id="basicsHeading{{ index }}">
                                            <h5 class="mb-0">
                                                <button type="button" 
                                                        class="btn btn-link btn-block flex-horizontal-center card-btn p-0 font-size-18" 
                                                        data-toggle="collapse" 
                                                        data-target="#basicsCollapse{{ index }}" 
                                                        aria-expanded="<# if (service.is_open === 'yes') { #>true<# } else { #>false<# } #>" 
                                                        aria-controls="basicsCollapse{{ index }}">
                                                    <span class="border border-color-5 rounded font-size-12 mr-5">
                                                        <i class="fas fa-plus"></i>
                                                        <i class="fas fa-minus"></i>
                                                    </span>
                                                    {{{ service.service_title }}}
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="basicsCollapse{{ index }}" 
                                             class="collapse <# if (service.is_open === 'yes') { #>show<# } #>" 
                                             aria-labelledby="basicsHeading{{ index }}" 
                                             data-parent="#about-accordion-{{ view.getID() }}">
                                            <div class="card-body">
                                                <p class="mb-0">{{{ service.service_description }}}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Card -->
                                <# }); #>
                            </div>
                            <!-- End Basics Accordion -->
                        <# } #>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
