<?php
/**
 * Widget About Team Kintaelectric para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget About Team Kintaelectric
 */
class KEE_Kintaelectric10_About_Team_Widget extends KEE_Base_Widget {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'kintaelectric10_about_team';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('About Team', 'kinta-electric-elementor');
    }

    /**
     * Obtener icono del widget
     */
    public function get_icon() {
        return 'eicon-person';
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
        return ['about', 'team', 'members', 'kintaelectric'];
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
            'name',
            [
                'label' => esc_html__('Nombre', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Thomas Snow', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'position',
            [
                'label' => esc_html__('Posición', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('CEO/Founder', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'is_hiring',
            [
                'label' => esc_html__('Es Contratación', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electric-elementor'),
                'label_off' => esc_html__('No', 'kinta-electric-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $repeater->add_control(
            'hiring_text',
            [
                'label' => esc_html__('Texto de Contratación', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('See Details', 'kinta-electric-elementor'),
                'condition' => [
                    'is_hiring' => 'yes',
                ],
            ]
        );

        $repeater->add_control(
            'hiring_url',
            [
                'label' => esc_html__('URL de Contratación', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://tu-sitio.com', 'kinta-electric-elementor'),
                'default' => [
                    'url' => '#',
                ],
                'condition' => [
                    'is_hiring' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'team_members',
            [
                'label' => esc_html__('Miembros del Equipo', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'name' => esc_html__('Thomas Snow', 'kinta-electric-elementor'),
                        'position' => esc_html__('CEO/Founder', 'kinta-electric-elementor'),
                    ],
                    [
                        'name' => esc_html__('Anna Baranov', 'kinta-electric-elementor'),
                        'position' => esc_html__('Client Care', 'kinta-electric-elementor'),
                    ],
                    [
                        'name' => esc_html__('Andre Kowalsy', 'kinta-electric-elementor'),
                        'position' => esc_html__('Support Boss', 'kinta-electric-elementor'),
                    ],
                    [
                        'name' => esc_html__('Pamela Doe', 'kinta-electric-elementor'),
                        'position' => esc_html__('Delivery Driver', 'kinta-electric-elementor'),
                    ],
                    [
                        'name' => esc_html__('Susan McCain', 'kinta-electric-elementor'),
                        'position' => esc_html__('Packaging Girl', 'kinta-electric-elementor'),
                    ],
                    [
                        'is_hiring' => 'yes',
                        'hiring_text' => esc_html__('See Details', 'kinta-electric-elementor'),
                    ],
                ],
                'title_field' => '{{{ name }}}',
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
        <div class="row team-member-wrapper py-5 bg-light">
            <?php if (!empty($settings['team_members'])) : ?>
                <?php foreach ($settings['team_members'] as $member) : ?>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                        <div class="team-member text-center">
                            <?php if ($member['is_hiring'] === 'yes') : ?>
                                <a href="<?php echo esc_url($member['hiring_url']['url']); ?>" class="text-decoration-none">
                                    <div class="team-image mb-3">
                                        <img loading="lazy" decoding="async" 
                                             src="<?php echo esc_url($member['image']['url']); ?>" 
                                             class="img-fluid rounded-circle" 
                                             alt="<?php echo esc_attr($member['hiring_text']); ?>"
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    </div>
                                    <div class="team-info">
                                        <h5 class="mb-1"><?php echo esc_html($member['hiring_text']); ?></h5>
                                        <small class="text-muted"></small>
                                    </div>
                                </a>
                            <?php else : ?>
                                <div class="team-image mb-3">
                                    <img loading="lazy" decoding="async" 
                                         src="<?php echo esc_url($member['image']['url']); ?>" 
                                         class="img-fluid rounded-circle" 
                                         alt="<?php echo esc_attr($member['name']); ?>"
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                </div>
                                <div class="team-info">
                                    <h5 class="mb-1"><?php echo esc_html($member['name']); ?></h5>
                                    <small class="text-muted"><?php echo esc_html($member['position']); ?></small>
                                </div>
                            <?php endif; ?>
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
        <div class="row team-member-wrapper py-5 bg-light">
            <# if (settings.team_members && settings.team_members.length > 0) { #>
                <# _.each(settings.team_members, function(member) { #>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                        <div class="team-member text-center">
                            <# if (member.is_hiring === 'yes') { #>
                                <a href="{{{ member.hiring_url.url }}}" class="text-decoration-none">
                                    <div class="team-image mb-3">
                                        <img loading="lazy" decoding="async" 
                                             src="{{{ member.image.url }}}" 
                                             class="img-fluid rounded-circle" 
                                             alt="{{{ member.hiring_text }}}"
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    </div>
                                    <div class="team-info">
                                        <h5 class="mb-1">{{{ member.hiring_text }}}</h5>
                                        <small class="text-muted"></small>
                                    </div>
                                </a>
                            <# } else { #>
                                <div class="team-image mb-3">
                                    <img loading="lazy" decoding="async" 
                                         src="{{{ member.image.url }}}" 
                                         class="img-fluid rounded-circle" 
                                         alt="{{{ member.name }}}"
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                </div>
                                <div class="team-info">
                                    <h5 class="mb-1">{{{ member.name }}}</h5>
                                    <small class="text-muted">{{{ member.position }}}</small>
                                </div>
                            <# } #>
                        </div>
                    </div>
                <# }); #>
            <# } #>
        </div>
        <?php
    }
}
