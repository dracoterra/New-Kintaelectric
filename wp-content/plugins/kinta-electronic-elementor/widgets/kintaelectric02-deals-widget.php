<?php
/**
 * Widget Kintaelectric02 Deals para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Kintaelectric02 Deals
 */
class KEE_Kintaelectric02_Deals_Widget extends \Elementor\Widget_Base {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'kintaelectric02_deals';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('Kintaelectric02 Deals', 'kinta-electric-elementor');
    }

    /**
     * Obtener icono del widget
     */
    public function get_icon() {
        return 'eicon-posts-grid';
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
        return ['deals', 'offers', 'products', 'kintaelectric', 'grid'];
    }

    /**
     * Obtener dependencias de scripts
     */
    public function get_script_depends() {
        return ['kinta-electric-elementor-script'];
    }

    /**
     * Obtener dependencias de estilos
     */
    public function get_style_depends() {
        return ['kinta-electric-elementor-style'];
    }

    /**
     * Registrar controles del widget
     */
    protected function register_controls() {
        
        // Sección de Configuración General
        $this->start_controls_section(
            'section_general',
            [
                'label' => esc_html__('Configuración General', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'columns_desktop',
            [
                'label' => esc_html__('Columnas Desktop', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '4',
                'options' => [
                    '2' => '2 Columnas',
                    '3' => '3 Columnas',
                    '4' => '4 Columnas',
                    '6' => '6 Columnas',
                ],
            ]
        );

        $this->add_control(
            'columns_tablet',
            [
                'label' => esc_html__('Columnas Tablet', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '2',
                'options' => [
                    '1' => '1 Columna',
                    '2' => '2 Columnas',
                    '3' => '3 Columnas',
                ],
            ]
        );

        $this->add_control(
            'columns_mobile',
            [
                'label' => esc_html__('Columnas Mobile', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '1',
                'options' => [
                    '1' => '1 Columna',
                    '2' => '2 Columnas',
                ],
            ]
        );

        $this->end_controls_section();

        // Sección de Items de Deals
        $this->start_controls_section(
            'section_deals',
            [
                'label' => esc_html__('Items de Ofertas', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'card_type',
            [
                'label' => esc_html__('Tipo de Tarjeta', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'button',
                'options' => [
                    'button' => esc_html__('Con Botón (Shop now)', 'kinta-electric-elementor'),
                    'discount' => esc_html__('Con Descuento (Upto X%)', 'kinta-electric-elementor'),
                ],
            ]
        );

        $repeater->add_control(
            'deal_image',
            [
                'label' => esc_html__('Imagen del Producto', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'deal_title',
            [
                'label' => esc_html__('Título Principal', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => 'Catch Big <br><strong>Deals</strong> on the <br>Cameras',
                'rows' => 3,
            ]
        );

        $repeater->add_control(
            'deal_url',
            [
                'label' => esc_html__('URL del Enlace', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://tu-sitio.com', 'kinta-electric-elementor'),
                'default' => [
                    'url' => '#',
                ],
            ]
        );

        // Controles para tarjeta con botón
        $repeater->add_control(
            'button_text',
            [
                'label' => esc_html__('Texto del Botón', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Shop now', 'kinta-electric-elementor'),
                'condition' => [
                    'card_type' => 'button',
                ],
            ]
        );

        // Controles para tarjeta con descuento
        $repeater->add_control(
            'discount_prefix',
            [
                'label' => esc_html__('Prefijo del Descuento', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Upto', 'kinta-electric-elementor'),
                'condition' => [
                    'card_type' => 'discount',
                ],
            ]
        );

        $repeater->add_control(
            'discount_value',
            [
                'label' => esc_html__('Valor del Descuento', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 70,
                'min' => 1,
                'max' => 100,
                'condition' => [
                    'card_type' => 'discount',
                ],
            ]
        );

        $repeater->add_control(
            'discount_suffix',
            [
                'label' => esc_html__('Sufijo del Descuento', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('%', 'kinta-electric-elementor'),
                'condition' => [
                    'card_type' => 'discount',
                ],
            ]
        );

        $this->add_control(
            'deals_list',
            [
                'label' => esc_html__('Lista de Ofertas', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'card_type' => 'button',
                        'deal_title' => 'Catch Big <br><strong>Deals</strong> on the <br>Cameras',
                        'button_text' => esc_html__('Shop now', 'kinta-electric-elementor'),
                    ],
                    [
                        'card_type' => 'discount',
                        'deal_title' => 'Tablets, <br>Smartphones <br><strong>and more</strong>',
                        'discount_prefix' => esc_html__('Upto', 'kinta-electric-elementor'),
                        'discount_value' => 70,
                        'discount_suffix' => esc_html__('%', 'kinta-electric-elementor'),
                    ],
                    [
                        'card_type' => 'button',
                        'deal_title' => 'Shop the <br><strong>Hottest</strong><br> Products',
                        'button_text' => esc_html__('Shop now', 'kinta-electric-elementor'),
                    ],
                    [
                        'card_type' => 'button',
                        'deal_title' => 'Shop the <strong>Hottest</strong><br>Products',
                        'button_text' => esc_html__('Shop now', 'kinta-electric-elementor'),
                    ],
                ],
                'title_field' => '{{{ deal_title.replace(/<[^>]*>/g, "") }}}',
            ]
        );

        $this->end_controls_section();

        // Sección de Estilos
        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__('Estilos', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => esc_html__('Color de Fondo', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .home-v1-da-block' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_background_color',
            [
                'label' => esc_html__('Color de Fondo de Items', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .da-inner' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_border_radius',
            [
                'label' => esc_html__('Radio de Borde', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .da-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'item_padding',
            [
                'label' => esc_html__('Padding de Items', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .da-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Sección de Estilos de Texto
        $this->start_controls_section(
            'section_text_style',
            [
                'label' => esc_html__('Estilos de Texto', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Color del Título', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .da-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => esc_html__('Tipografía del Título', 'kinta-electric-elementor'),
                'selector' => '{{WRAPPER}} .da-text',
            ]
        );

        $this->add_control(
            'action_text_color',
            [
                'label' => esc_html__('Color del Texto de Acción', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .da-action' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'action_typography',
                'label' => esc_html__('Tipografía del Texto de Acción', 'kinta-electric-elementor'),
                'selector' => '{{WRAPPER}} .da-action',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Renderizar widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if (empty($settings['deals_list'])) {
            return;
        }
        
        $column_classes = sprintf(
            'row-cols-%s row-cols-md-%s row-cols-xl-%s',
            $settings['columns_mobile'],
            $settings['columns_tablet'],
            $settings['columns_desktop']
        );
        ?>
        
        <div class="home-v1-da-block">
            <div class="da-block justify-content-between flex-nowrap align-items-stretch overflow-auto row <?php echo esc_attr($column_classes); ?>">
                <?php foreach ($settings['deals_list'] as $deal) : ?>
                    <div class="da">
                        <div class="da-inner p-3 position-relative">
                            <a class="da-media d-flex stretched-link" 
                               href="<?php echo esc_url($deal['deal_url']['url']); ?>"
                               <?php echo $deal['deal_url']['is_external'] ? 'target="_blank"' : ''; ?>
                               <?php echo $deal['deal_url']['nofollow'] ? 'rel="nofollow"' : ''; ?>>
                                <div class="da-media-left me-3">
                                    <?php if (!empty($deal['deal_image']['url'])) : ?>
                                        <img loading="lazy" 
                                             width="173" 
                                             height="118" 
                                             src="<?php echo esc_url($deal['deal_image']['url']); ?>" 
                                             class="attachment-full size-full" 
                                             alt="<?php echo esc_attr(strip_tags($deal['deal_title'])); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="da-media-body">
                                    <div class="da-text">
                                        <?php echo wp_kses_post($deal['deal_title']); ?>
                                    </div>
                                    <div class="da-action">
                                        <?php if (isset($deal['card_type']) && $deal['card_type'] === 'discount') : ?>
                                            <span class="upto">
                                                <span class="prefix"><?php echo esc_html($deal['discount_prefix']); ?></span>
                                                <span class="value"><?php echo esc_html($deal['discount_value']); ?></span>
                                                <span class="suffix"><?php echo esc_html($deal['discount_suffix']); ?></span>
                                            </span>
                                        <?php else : ?>
                                            <?php echo esc_html($deal['button_text']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php
    }

    /**
     * Renderizar widget en modo de edición
     */
    protected function content_template() {
        ?>
        <div class="home-v1-da-block">
            <div class="da-block justify-content-between flex-nowrap align-items-stretch overflow-auto row row-cols-{{{ settings.columns_mobile }}} row-cols-md-{{{ settings.columns_tablet }}} row-cols-xl-{{{ settings.columns_desktop }}}">
                <# if (settings.deals_list && settings.deals_list.length > 0) { #>
                    <# _.each(settings.deals_list, function(deal) { #>
                        <div class="da">
                            <div class="da-inner p-3 position-relative">
                                <a class="da-media d-flex stretched-link" 
                                   href="{{{ deal.deal_url.url }}}"
                                   <# if (deal.deal_url.is_external) { #>target="_blank"<# } #>
                                   <# if (deal.deal_url.nofollow) { #>rel="nofollow"<# } #>>
                                    <div class="da-media-left me-3">
                                        <# if (deal.deal_image.url) { #>
                                            <img loading="lazy" 
                                                 width="173" 
                                                 height="118" 
                                                 src="{{{ deal.deal_image.url }}}" 
                                                 class="attachment-full size-full" 
                                                 alt="{{{ deal.deal_title }}}">
                                        <# } #>
                                    </div>
                                    <div class="da-media-body">
                                        <div class="da-text">
                                            {{{ deal.deal_title }}}
                                        </div>
                                        <div class="da-action">
                                            <# if (deal.card_type === 'discount') { #>
                                                <span class="upto">
                                                    <span class="prefix">{{{ deal.discount_prefix }}}</span>
                                                    <span class="value">{{{ deal.discount_value }}}</span>
                                                    <span class="suffix">{{{ deal.discount_suffix }}}</span>
                                                </span>
                                            <# } else { #>
                                                {{{ deal.button_text }}}
                                            <# } #>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <# }); #>
                <# } #>
            </div>
        </div>
        <?php
    }
}
