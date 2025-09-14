<?php
/**
 * Widget About Text + Accordion Kintaelectric para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget About Text + Accordion Kintaelectric
 */
class KEE_Kintaelectric12_About_Text_Accordion_Widget extends KEE_Base_Widget {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'kintaelectric12_about_text_accordion';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('About Text + Accordion', 'kinta-electric-elementor');
    }

    /**
     * Obtener icono del widget
     */
    public function get_icon() {
        return 'eicon-accordion';
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
        return ['about', 'text', 'accordion', 'faq', 'kintaelectric'];
    }

    /**
     * Obtener dependencias de scripts
     */
    public function get_script_depends() {
        return ['bootstrap-5'];
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
        // Sección de Texto
        $this->start_controls_section(
            'section_text',
            [
                'label' => esc_html__('Contenido de Texto', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'title',
            [
                'label' => esc_html__('Título', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('What we really do?', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'description',
            [
                'label' => esc_html__('Descripción', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna, vitae ultrices lacus purus vitae metus.', 'kinta-electric-elementor'),
                'rows' => 4,
            ]
        );

        $this->add_control(
            'text_items',
            [
                'label' => esc_html__('Elementos de Texto', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'title' => esc_html__('What we really do?', 'kinta-electric-elementor'),
                        'description' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna, vitae ultrices lacus purus vitae metus.', 'kinta-electric-elementor'),
                    ],
                    [
                        'title' => esc_html__('Our Vision', 'kinta-electric-elementor'),
                        'description' => esc_html__('Vestibulum velit nibh, egestas vel faucibus vitae, feugiat sollicitudin urna. Praesent iaculis id ipsum sit amet pretium. Aliquam tristique sapien nec enim euismod, scelerisque facilisis arcu consectetur.', 'kinta-electric-elementor'),
                    ],
                    [
                        'title' => esc_html__('History of the Company', 'kinta-electric-elementor'),
                        'description' => esc_html__('Mauris rhoncus aliquet purus, a ornare nisi euismod in. Interdum et malesuada fames ac ante ipsum primis in faucibus. Etiam imperdiet eu metus vel ornare. Nullam in risus vel orci feugiat vestibulum.', 'kinta-electric-elementor'),
                    ],
                    [
                        'title' => esc_html__('Cooperate with Us!', 'kinta-electric-elementor'),
                        'description' => esc_html__('Donec libero dolor, tincidunt id laoreet vitae, ullamcorper eu tortor. Maecenas pellentesque, dui vitae iaculis mattis, tortor nisi faucibus magna, vitae ultrices lacus purus vitae metus.', 'kinta-electric-elementor'),
                    ],
                ],
                'title_field' => '{{{ title }}}',
            ]
        );

        $this->end_controls_section();

        // Sección de Acordeón
        $this->start_controls_section(
            'section_accordion',
            [
                'label' => esc_html__('Acordeón', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'accordion_title',
            [
                'label' => esc_html__('Título del Acordeón', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('What can we do for you ?', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $accordion_repeater = new \Elementor\Repeater();

        $accordion_repeater->add_control(
            'accordion_title',
            [
                'label' => esc_html__('Título del Item', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Support 24/7', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $accordion_repeater->add_control(
            'accordion_content',
            [
                'label' => esc_html__('Contenido del Item', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Vestibulum velit nibh, egestas vel faucibus vitae, feugiat sollicitudin urna. Praesent iaculis id ipsum sit amet pretium. Aliquam tristique sapien nec enim euismod, scelerisque facilisis arcu consectetur.', 'kinta-electric-elementor'),
                'rows' => 3,
            ]
        );

        $accordion_repeater->add_control(
            'is_active',
            [
                'label' => esc_html__('Activo por defecto', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electric-elementor'),
                'label_off' => esc_html__('No', 'kinta-electric-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'accordion_items',
            [
                'label' => esc_html__('Items del Acordeón', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $accordion_repeater->get_controls(),
                'default' => [
                    [
                        'accordion_title' => esc_html__('Support 24/7', 'kinta-electric-elementor'),
                        'accordion_content' => esc_html__('Vestibulum velit nibh, egestas vel faucibus vitae, feugiat sollicitudin urna. Praesent iaculis id ipsum sit amet pretium. Aliquam tristique sapien nec enim euismod, scelerisque facilisis arcu consectetur.', 'kinta-electric-elementor'),
                        'is_active' => 'yes',
                    ],
                    [
                        'accordion_title' => esc_html__('Best Quality', 'kinta-electric-elementor'),
                        'accordion_content' => esc_html__('Vestibulum velit nibh, egestas vel faucibus vitae, feugiat sollicitudin urna. Praesent iaculis id ipsum sit amet pretium. Aliquam tristique sapien nec enim euismod, scelerisque facilisis arcu consectetur.', 'kinta-electric-elementor'),
                    ],
                    [
                        'accordion_title' => esc_html__('Fastest Delivery', 'kinta-electric-elementor'),
                        'accordion_content' => esc_html__('Vestibulum velit nibh, egestas vel faucibus vitae, feugiat sollicitudin urna. Praesent iaculis id ipsum sit amet pretium. Aliquam tristique sapien nec enim euismod, scelerisque facilisis arcu consectetur.', 'kinta-electric-elementor'),
                    ],
                    [
                        'accordion_title' => esc_html__('Customer Care', 'kinta-electric-elementor'),
                        'accordion_content' => esc_html__('Vestibulum velit nibh, egestas vel faucibus vitae, feugiat sollicitudin urna. Praesent iaculis id ipsum sit amet pretium. Aliquam tristique sapien nec enim euismod, scelerisque facilisis arcu consectetur.', 'kinta-electric-elementor'),
                    ],
                    [
                        'accordion_title' => esc_html__('Over 200 Satisfied Customers', 'kinta-electric-elementor'),
                        'accordion_content' => esc_html__('Vestibulum velit nibh, egestas vel faucibus vitae, feugiat sollicitudin urna. Praesent iaculis id ipsum sit amet pretium. Aliquam tristique sapien nec enim euismod, scelerisque facilisis arcu consectetur.', 'kinta-electric-elementor'),
                    ],
                ],
                'title_field' => '{{{ accordion_title }}}',
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
        <div class="row py-4 py-md-5">
            <!-- Columna de Texto -->
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="text-boxes">
                    <div class="row">
                        <?php if (!empty($settings['text_items'])) : ?>
                            <?php
                            $items = array_chunk($settings['text_items'], 2); // Dividir en grupos de 2
                            foreach ($items as $row) :
                            ?>
                                <?php foreach ($row as $index => $item) : ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="text-item">
                                            <h3 class="highlight mb-3"><?php echo esc_html($item['title']); ?></h3>
                                            <p class="text-muted"><?php echo wp_kses_post(nl2br($item['description'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Columna de Acordeón -->
            <div class="col-lg-5">
                <div class="accordion-container">
                    <h2 class="mb-4"><?php echo esc_html($settings['accordion_title']); ?></h2>
                    <div class="accordion" id="accordion-<?php echo esc_attr($this->get_id()); ?>">
                        <?php if (!empty($settings['accordion_items'])) : ?>
                            <?php foreach ($settings['accordion_items'] as $index => $item) : ?>
                                <?php
                                $unique_id = 'accordion_' . $this->get_id() . '_' . $index;
                                $show_class = $item['is_active'] === 'yes' ? 'show' : '';
                                $expanded = $item['is_active'] === 'yes' ? 'true' : 'false';
                                $collapsed_class = $item['is_active'] !== 'yes' ? 'collapsed' : '';
                                ?>
                                <div class="accordion-item border-0 mb-2">
                                    <h4 class="accordion-header" id="heading-<?php echo esc_attr($unique_id); ?>">
                                        <button class="accordion-button bg-transparent border-0 shadow-none p-0 d-flex align-items-center <?php echo esc_attr($collapsed_class); ?>"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapse-<?php echo esc_attr($unique_id); ?>"
                                                aria-expanded="<?php echo esc_attr($expanded); ?>"
                                                aria-controls="collapse-<?php echo esc_attr($unique_id); ?>">
                                            <div class="accordion-icon me-3 d-flex align-items-center justify-content-center <?php echo $item['is_active'] === 'yes' ? 'bg-warning' : 'bg-secondary'; ?>">
                                                <i class="fas fa-<?php echo $item['is_active'] === 'yes' ? 'minus' : 'plus'; ?> text-white"></i>
                                            </div>
                                            <span class="fw-bold <?php echo $item['is_active'] === 'yes' ? 'text-dark' : 'text-muted'; ?>">
                                                <?php echo esc_html($item['accordion_title']); ?>
                                            </span>
                                        </button>
                                    </h4>
                                    <div id="collapse-<?php echo esc_attr($unique_id); ?>"
                                         class="accordion-collapse collapse <?php echo esc_attr($show_class); ?>"
                                         aria-labelledby="heading-<?php echo esc_attr($unique_id); ?>"
                                         data-bs-parent="#accordion-<?php echo esc_attr($this->get_id()); ?>">
                                        <div class="accordion-body ps-5 pt-2">
                                            <p class="text-muted mb-0"><?php echo wp_kses_post(nl2br($item['accordion_content'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .accordion-icon {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            font-size: 12px;
        }
        .accordion-button:not(.collapsed) .accordion-icon {
            background-color: #ffc107 !important;
        }
        .accordion-button.collapsed .accordion-icon {
            background-color: #6c757d !important;
        }
        .accordion-button:focus {
            box-shadow: none;
        }
        .accordion-button:not(.collapsed) {
            color: #212529;
        }
        .accordion-button.collapsed {
            color: #6c757d;
        }
        </style>
        <?php
    }

    /**
     * Renderizar widget en modo de edición
     */
    protected function content_template() {
        ?>
        <div class="row py-4 py-md-5">
            <!-- Columna de Texto -->
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="text-boxes">
                    <div class="row">
                        <# if (settings.text_items && settings.text_items.length > 0) { #>
                            <# 
                            var items = [];
                            for (var i = 0; i < settings.text_items.length; i += 2) {
                                items.push(settings.text_items.slice(i, i + 2));
                            }
                            #>
                            <# _.each(items, function(row) { #>
                                <# _.each(row, function(item) { #>
                                    <div class="col-md-6 mb-4">
                                        <div class="text-item">
                                            <h3 class="highlight mb-3">{{{ item.title }}}</h3>
                                            <p class="text-muted">{{{ item.description }}}</p>
                                        </div>
                                    </div>
                                <# }); #>
                            <# }); #>
                        <# } #>
                    </div>
                </div>
            </div>

            <!-- Columna de Acordeón -->
            <div class="col-lg-5">
                <div class="accordion-container">
                    <h2 class="mb-4">{{{ settings.accordion_title }}}</h2>
                    <div class="accordion" id="accordion-{{{ view.getID() }}}">
                        <# if (settings.accordion_items && settings.accordion_items.length > 0) { #>
                            <# _.each(settings.accordion_items, function(item, index) { #>
                                <# var uniqueId = 'accordion_' + view.getID() + '_' + index; #>
                                <# var showClass = item.is_active === 'yes' ? 'show' : ''; #>
                                <# var expanded = item.is_active === 'yes' ? 'true' : 'false'; #>
                                <# var collapsedClass = item.is_active !== 'yes' ? 'collapsed' : ''; #>
                                <div class="accordion-item border-0 mb-2">
                                    <h4 class="accordion-header" id="heading-{{{ uniqueId }}}">
                                        <button class="accordion-button bg-transparent border-0 shadow-none p-0 d-flex align-items-center {{{ collapsedClass }}}"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapse-{{{ uniqueId }}}"
                                                aria-expanded="{{{ expanded }}}"
                                                aria-controls="collapse-{{{ uniqueId }}}">
                                            <div class="accordion-icon me-3 d-flex align-items-center justify-content-center {{{ item.is_active === 'yes' ? 'bg-warning' : 'bg-secondary' }}}">
                                                <i class="fas fa-{{{ item.is_active === 'yes' ? 'minus' : 'plus' }}} text-white"></i>
                                            </div>
                                            <span class="fw-bold {{{ item.is_active === 'yes' ? 'text-dark' : 'text-muted' }}}">
                                                {{{ item.accordion_title }}}
                                            </span>
                                        </button>
                                    </h4>
                                    <div id="collapse-{{{ uniqueId }}}"
                                         class="accordion-collapse collapse {{{ showClass }}}"
                                         aria-labelledby="heading-{{{ uniqueId }}}"
                                         data-bs-parent="#accordion-{{{ view.getID() }}}">
                                        <div class="accordion-body ps-5 pt-2">
                                            <p class="text-muted mb-0">{{{ item.accordion_content }}}</p>
                                        </div>
                                    </div>
                                </div>
                            <# }); #>
                        <# } #>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
