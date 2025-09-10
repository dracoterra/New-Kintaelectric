<?php
/**
 * Widget Contact KintaElectric
 * 
 * @package KintaElectronicElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Widget Contact KintaElectric
 */
class KEE_Contact_Kintaelectric_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     */
    public function get_name() {
        return 'contact-kintaelectric';
    }

    /**
     * Get widget title.
     */
    public function get_title() {
        return esc_html__('Contact KintaElectric', 'kinta-electronic-elementor');
    }

    /**
     * Get widget icon.
     */
    public function get_icon() {
        return 'eicon-form-horizontal';
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
        return ['contact', 'form', 'map', 'address', 'kintaelectric', 'message'];
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
            'page_title',
            [
                'label' => esc_html__('Page Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Contact-V2', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'form_title',
            [
                'label' => esc_html__('Form Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Leave us a Message', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'form_description',
            [
                'label' => esc_html__('Form Description', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Aenean massa diam, viverra vitae luctus sed, gravida eget est. Etiam nec ipsum porttitor, consequat libero eu, dignissim eros. Nulla auctor lacinia enim id mollis. Curabitur luctus interdum eleifend. Ut tempor lorem a turpis fermentum.', 'kinta-electronic-elementor'),
                'label_block' => true,
                'rows' => 4,
            ]
        );

        $this->add_control(
            'contact_form_shortcode',
            [
                'label' => esc_html__('Contact Form 7 Shortcode', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('[contact-form-7 id="123" title="Contact form 1"]', 'kinta-electronic-elementor'),
                'description' => esc_html__('Insert your Contact Form 7 shortcode here. Example: [contact-form-7 id="123" title="Contact form 1"]', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        // Company Info Section
        $this->start_controls_section(
            'company_info_section',
            [
                'label' => esc_html__('Company Information', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'address_title',
            [
                'label' => esc_html__('Address Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Our Address', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'company_address',
            [
                'label' => esc_html__('Company Address', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('121 King Street, Melbourne VIC 3000, Australia', 'kinta-electronic-elementor'),
                'label_block' => true,
                'rows' => 3,
            ]
        );

        $this->add_control(
            'phone_number',
            [
                'label' => esc_html__('Phone Number', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Support(+800)856 800 604', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'email_address',
            [
                'label' => esc_html__('Email Address', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('info@electro.com', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'opening_hours_title',
            [
                'label' => esc_html__('Opening Hours Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Opening Hours', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weekday_hours',
            [
                'label' => esc_html__('Weekday Hours', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Monday to Friday: 9am-9pm', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weekend_hours',
            [
                'label' => esc_html__('Weekend Hours', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Saturday to Sunday: 9am-11pm', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'careers_title',
            [
                'label' => esc_html__('Careers Title', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Careers', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'careers_description',
            [
                'label' => esc_html__('Careers Description', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('If you\'re interested in employment opportunities at Electro, please email us:', 'kinta-electronic-elementor'),
                'label_block' => true,
                'rows' => 2,
            ]
        );

        $this->add_control(
            'careers_email',
            [
                'label' => esc_html__('Careers Email', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('contact@yourstore.com', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        // Map Section
        $this->start_controls_section(
            'map_section',
            [
                'label' => esc_html__('Google Map', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_map',
            [
                'label' => esc_html__('Show Google Map', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'kinta-electronic-elementor'),
                'label_off' => esc_html__('Hide', 'kinta-electronic-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'map_embed_url',
            [
                'label' => esc_html__('Map Embed URL', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://www.google.com/maps/embed?...', 'kinta-electronic-elementor'),
                'default' => [
                    'url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.835252972956!2d144.95592398991224!3d-37.817327693787625!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ad65d4c2b349649%3A0xb6899234e561db11!2sEnvato!5e0!3m2!1sen!2sin!4v1575470633967!5m2!1sen!2sin',
                ],
                'condition' => [
                    'show_map' => 'yes',
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
            'section_background_color',
            [
                'label' => esc_html__('Section Background Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-section' => 'background-color: {{VALUE}};',
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
                'name' => 'page_title_typography',
                'label' => esc_html__('Page Title Typography', 'kinta-electronic-elementor'),
                'selector' => '{{WRAPPER}} .page-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'form_title_typography',
                'label' => esc_html__('Form Title Typography', 'kinta-electronic-elementor'),
                'selector' => '{{WRAPPER}} .section-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'form_description_typography',
                'label' => esc_html__('Form Description Typography', 'kinta-electronic-elementor'),
                'selector' => '{{WRAPPER}} .form-description',
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
            'section_padding',
            [
                'label' => esc_html__('Section Padding', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .contact-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'form_margin',
            [
                'label' => esc_html__('Form Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .contact-form-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
        
        ?>
        <style>
        .elementor-shortcode-placeholder {
            background: #f7f7f7;
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
        }
        .elementor-shortcode-placeholder i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
            display: block;
        }
        .elementor-shortcode-placeholder span {
            display: block;
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }
        .elementor-shortcode-placeholder code {
            background: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-family: monospace;
            color: #333;
        }
        </style>
        
        <div class="container contact-section">
            <div class="mb-5">
                <h1 class="text-center page-title"><?php echo esc_html($settings['page_title']); ?></h1>
            </div>
            
            <div class="row mb-10">
                <!-- Form Column -->
                <div class="col-lg-7 col-xl-6 mb-8 mb-lg-0">
                    <div class="mr-xl-6 contact-form">
                        <div class="border-bottom border-color-1 mb-5">
                            <h3 class="section-title mb-0 pb-2 font-size-25"><?php echo esc_html($settings['form_title']); ?></h3>
                        </div>
                        
                        <p class="max-width-830-xl text-gray-90 form-description"><?php echo esc_html($settings['form_description']); ?></p>
                        
                        <?php if (!empty($settings['contact_form_shortcode'])) : ?>
                            <div class="contact-form-wrapper">
                                <?php echo do_shortcode($settings['contact_form_shortcode']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info Column -->
                <div class="col-lg-5 col-xl-6">
                    <?php if ($settings['show_map'] === 'yes' && !empty($settings['map_embed_url']['url'])) : ?>
                        <div class="mb-6">
                            <iframe src="<?php echo esc_url($settings['map_embed_url']['url']); ?>" 
                                    width="100%" 
                                    height="288" 
                                    frameborder="0" 
                                    style="border:0;" 
                                    allowfullscreen=""></iframe>
                        </div>
                    <?php endif; ?>

                    <div class="border-bottom border-color-1 mb-5">
                        <h3 class="section-title mb-0 pb-2 font-size-25"><?php echo esc_html($settings['address_title']); ?></h3>
                    </div>
                    
                    <address class="mb-6 text-lh-23">
                        <?php echo nl2br(esc_html($settings['company_address'])); ?>
                        <div class=""><?php echo esc_html($settings['phone_number']); ?></div>
                        <div class="">Email: <a class="text-blue text-decoration-on" href="mailto:<?php echo esc_attr($settings['email_address']); ?>"><?php echo esc_html($settings['email_address']); ?></a></div>
                    </address>
                    
                    <h5 class="font-size-14 font-weight-bold mb-3"><?php echo esc_html($settings['opening_hours_title']); ?></h5>
                    <div class=""><?php echo esc_html($settings['weekday_hours']); ?></div>
                    <div class="mb-6"><?php echo esc_html($settings['weekend_hours']); ?></div>
                    
                    <h5 class="font-size-14 font-weight-bold mb-3"><?php echo esc_html($settings['careers_title']); ?></h5>
                    <p class="text-gray-90"><?php echo esc_html($settings['careers_description']); ?> <a class="text-blue text-decoration-on" href="mailto:<?php echo esc_attr($settings['careers_email']); ?>"><?php echo esc_html($settings['careers_email']); ?></a></p>
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
        <div class="container contact-section">
            <div class="mb-5">
                <h1 class="text-center page-title">{{{ settings.page_title }}}</h1>
            </div>
            
            <div class="row mb-10">
                <!-- Form Column -->
                <div class="col-lg-7 col-xl-6 mb-8 mb-lg-0">
                    <div class="mr-xl-6 contact-form">
                        <div class="border-bottom border-color-1 mb-5">
                            <h3 class="section-title mb-0 pb-2 font-size-25">{{{ settings.form_title }}}</h3>
                        </div>
                        
                        <p class="max-width-830-xl text-gray-90 form-description">{{{ settings.form_description }}}</p>
                        
                        <# if (settings.contact_form_shortcode) { #>
                            <div class="contact-form-wrapper">
                                <div class="elementor-shortcode-placeholder">
                                    <i class="eicon-form-horizontal"></i>
                                    <span>Contact Form 7 Shortcode:</span>
                                    <code>{{{ settings.contact_form_shortcode }}}</code>
                                </div>
                            </div>
                        <# } else { #>
                            <div class="contact-form-wrapper">
                                <div class="elementor-shortcode-placeholder">
                                    <i class="eicon-form-horizontal"></i>
                                    <span>Please add a Contact Form 7 shortcode in the widget settings</span>
                                </div>
                            </div>
                        <# } #>
                    </div>
                </div>

                <!-- Info Column -->
                <div class="col-lg-5 col-xl-6">
                    <# if (settings.show_map === 'yes' && settings.map_embed_url && settings.map_embed_url.url) { #>
                        <div class="mb-6">
                            <iframe src="{{{ settings.map_embed_url.url }}}" 
                                    width="100%" 
                                    height="288" 
                                    frameborder="0" 
                                    style="border:0;" 
                                    allowfullscreen=""></iframe>
                        </div>
                    <# } #>

                    <div class="border-bottom border-color-1 mb-5">
                        <h3 class="section-title mb-0 pb-2 font-size-25">{{{ settings.address_title }}}</h3>
                    </div>
                    
                    <address class="mb-6 text-lh-23">
                        {{{ settings.company_address }}}
                        <div class="">{{{ settings.phone_number }}}</div>
                        <div class="">Email: <a class="text-blue text-decoration-on" href="mailto:{{{ settings.email_address }}}">{{{ settings.email_address }}}</a></div>
                    </address>
                    
                    <h5 class="font-size-14 font-weight-bold mb-3">{{{ settings.opening_hours_title }}}</h5>
                    <div class="">{{{ settings.weekday_hours }}}</div>
                    <div class="mb-6">{{{ settings.weekend_hours }}}</div>
                    
                    <h5 class="font-size-14 font-weight-bold mb-3">{{{ settings.careers_title }}}</h5>
                    <p class="text-gray-90">{{{ settings.careers_description }}} <a class="text-blue text-decoration-on" href="mailto:{{{ settings.careers_email }}}">{{{ settings.careers_email }}}</a></p>
                </div>
            </div>
        </div>
        <?php
    }
}
