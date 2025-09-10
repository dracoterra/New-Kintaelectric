<?php
/**
 * Widget Team Members KintaElectric
 * 
 * @package KintaElectronicElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Widget Team Members KintaElectric
 */
class KEE_Team_Members_Kintaelectric_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     */
    public function get_name() {
        return 'team-members-kintaelectric';
    }

    /**
     * Get widget title.
     */
    public function get_title() {
        return esc_html__('Team Members KintaElectric', 'kinta-electronic-elementor');
    }

    /**
     * Get widget icon.
     */
    public function get_icon() {
        return 'eicon-person';
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
        return ['team', 'members', 'staff', 'kintaelectric', 'personnel'];
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
            'member_image',
            [
                'label' => esc_html__('Member Image', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'member_name',
            [
                'label' => esc_html__('Member Name', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Team Member', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'member_position',
            [
                'label' => esc_html__('Member Position', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Position', 'kinta-electronic-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'is_see_details',
            [
                'label' => esc_html__('Is "See Details" Link', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kinta-electronic-elementor'),
                'label_off' => esc_html__('No', 'kinta-electronic-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => esc_html__('Enable this if this member should show as "See Details" link', 'kinta-electronic-elementor'),
            ]
        );

        $repeater->add_control(
            'member_link',
            [
                'label' => esc_html__('Member Link', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'kinta-electronic-elementor'),
                'default' => [
                    'url' => '',
                    'is_external' => true,
                    'nofollow' => true,
                    'custom_attributes' => '',
                ],
                'label_block' => true,
                'condition' => [
                    'is_see_details' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'team_members',
            [
                'label' => esc_html__('Team Members', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'member_name' => esc_html__('Thomas Snow', 'kinta-electronic-elementor'),
                        'member_position' => esc_html__('CEO/Founder', 'kinta-electronic-elementor'),
                        'is_see_details' => 'no',
                    ],
                    [
                        'member_name' => esc_html__('Anna Baranov', 'kinta-electronic-elementor'),
                        'member_position' => esc_html__('Client Care', 'kinta-electronic-elementor'),
                        'is_see_details' => 'no',
                    ],
                    [
                        'member_name' => esc_html__('Andre Kowalsy', 'kinta-electronic-elementor'),
                        'member_position' => esc_html__('Support Boss', 'kinta-electronic-elementor'),
                        'is_see_details' => 'no',
                    ],
                    [
                        'member_name' => esc_html__('Pamela Doe', 'kinta-electronic-elementor'),
                        'member_position' => esc_html__('Delivery Driver', 'kinta-electronic-elementor'),
                        'is_see_details' => 'no',
                    ],
                    [
                        'member_name' => esc_html__('Susan McCain', 'kinta-electronic-elementor'),
                        'member_position' => esc_html__('Packaging Girl', 'kinta-electronic-elementor'),
                        'is_see_details' => 'no',
                    ],
                    [
                        'member_name' => esc_html__('See Details', 'kinta-electronic-elementor'),
                        'member_position' => '',
                        'is_see_details' => 'yes',
                        'member_link' => ['url' => '#'],
                    ],
                ],
                'title_field' => '{{{ member_name }}}',
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
            'background_color',
            [
                'label' => esc_html__('Background Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .bg-gray-1' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .py-12' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Member Name Style Section
        $this->start_controls_section(
            'member_name_style_section',
            [
                'label' => esc_html__('Member Name Style', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'member_name_color',
            [
                'label' => esc_html__('Name Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .font-size-18.font-weight-semi-bold' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'member_name_typography',
                'selector' => '{{WRAPPER}} .font-size-18.font-weight-semi-bold',
            ]
        );

        $this->add_control(
            'member_name_margin',
            [
                'label' => esc_html__('Name Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .font-size-18.font-weight-semi-bold' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Member Position Style Section
        $this->start_controls_section(
            'member_position_style_section',
            [
                'label' => esc_html__('Member Position Style', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'member_position_color',
            [
                'label' => esc_html__('Position Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .text-gray-41' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'member_position_typography',
                'selector' => '{{WRAPPER}} .text-gray-41',
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
                    '{{WRAPPER}} .img-fluid.rounded-circle' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; object-fit: cover;',
                ],
            ]
        );

        $this->add_control(
            'image_border_radius',
            [
                'label' => esc_html__('Border Radius', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 50,
                ],
                'selectors' => [
                    '{{WRAPPER}} .img-fluid.rounded-circle' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'image_margin',
            [
                'label' => esc_html__('Image Margin', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .img-fluid.rounded-circle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Link Style Section
        $this->start_controls_section(
            'link_style_section',
            [
                'label' => esc_html__('Link Style', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('link_styles');

        $this->start_controls_tab(
            'link_normal',
            [
                'label' => esc_html__('Normal', 'kinta-electronic-elementor'),
            ]
        );

        $this->add_control(
            'link_color',
            [
                'label' => esc_html__('Link Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'link_hover',
            [
                'label' => esc_html__('Hover', 'kinta-electronic-elementor'),
            ]
        );

        $this->add_control(
            'link_hover_color',
            [
                'label' => esc_html__('Link Hover Color', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        if (empty($settings['team_members'])) {
            return;
        }

        ?>
        <div class="bg-gray-1 py-12 mb-10 mb-lg-15">
            <div class="container">
                <div class="row">
                    <?php foreach ($settings['team_members'] as $member) : ?>
                        <div class="col-md-4 mb-5 mb-xl-0 col-xl text-center">
                            <?php if ($member['is_see_details'] === 'yes' && !empty($member['member_link']['url'])) : ?>
                                <a href="<?php echo esc_url($member['member_link']['url']); ?>" 
                                   <?php echo $member['member_link']['is_external'] ? 'target="_blank"' : ''; ?>
                                   <?php echo $member['member_link']['nofollow'] ? 'rel="nofollow"' : ''; ?>>
                            <?php endif; ?>
                            
                            <?php if (!empty($member['member_image']['url'])) : ?>
                                <img class="img-fluid mb-3 rounded-circle" 
                                     src="<?php echo esc_url($member['member_image']['url']); ?>" 
                                     alt="<?php echo esc_attr($member['member_name']); ?>">
                            <?php endif; ?>
                            
                            <h2 class="font-size-18 font-weight-semi-bold mb-0"><?php echo esc_html($member['member_name']); ?></h2>
                            
                            <?php if (!empty($member['member_position'])) : ?>
                                <span class="text-gray-41"><?php echo esc_html($member['member_position']); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($member['is_see_details'] === 'yes' && !empty($member['member_link']['url'])) : ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
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
        <div class="bg-gray-1 py-12 mb-10 mb-lg-15">
            <div class="container">
                <div class="row">
                    <# if (settings.team_members && settings.team_members.length) { #>
                        <# _.each(settings.team_members, function(member) { #>
                            <div class="col-md-4 mb-5 mb-xl-0 col-xl text-center">
                                <# if (member.is_see_details === 'yes' && member.member_link && member.member_link.url) { #>
                                    <a href="{{{ member.member_link.url }}}" 
                                       <# if (member.member_link.is_external) { #>target="_blank"<# } #>
                                       <# if (member.member_link.nofollow) { #>rel="nofollow"<# } #>>
                                <# } #>
                                
                                <# if (member.member_image && member.member_image.url) { #>
                                    <img class="img-fluid mb-3 rounded-circle" 
                                         src="{{{ member.member_image.url }}}" 
                                         alt="{{{ member.member_name }}}">
                                <# } #>
                                
                                <h2 class="font-size-18 font-weight-semi-bold mb-0">{{{ member.member_name }}}</h2>
                                
                                <# if (member.member_position) { #>
                                    <span class="text-gray-41">{{{ member.member_position }}}</span>
                                <# } #>
                                
                                <# if (member.is_see_details === 'yes' && member.member_link && member.member_link.url) { #>
                                    </a>
                                <# } #>
                            </div>
                        <# }); #>
                    <# } #>
                </div>
            </div>
        </div>
        <?php
    }
}
