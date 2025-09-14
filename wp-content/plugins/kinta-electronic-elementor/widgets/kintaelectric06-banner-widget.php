<?php
/**
 * Widget Kintaelectric06 Banner para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Kintaelectric06 Banner
 */
class KEE_Kintaelectric06_Banner_Widget extends KEE_Base_Widget {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'kintaelectric06_banner';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('Kintaelectric06 Banner', 'kinta-electric-elementor');
    }

    /**
     * Obtener icono del widget
     */
    public function get_icon() {
        return 'eicon-image';
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
        return ['banner', 'image', 'advertisement', 'kintaelectric', 'fullbanner'];
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
        // Sección de Configuración del Banner
        $this->start_controls_section(
            'section_banner_config',
            [
                'label' => esc_html__('Configuración del Banner', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Control para imagen del banner
        $this->add_control(
            'banner_image',
            [
                'label' => esc_html__('Imagen del Banner', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        // Control para URL del enlace
        $this->add_control(
            'banner_link',
            [
                'label' => esc_html__('Enlace del Banner', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://tu-sitio.com', 'kinta-electric-elementor'),
                'default' => [
                    'url' => '#',
                ],
            ]
        );

        // Control para texto alternativo
        $this->add_control(
            'banner_alt',
            [
                'label' => esc_html__('Texto Alternativo', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Banner promocional', 'kinta-electric-elementor'),
                'placeholder' => esc_html__('Ingresa el texto alternativo', 'kinta-electric-elementor'),
            ]
        );

        $this->end_controls_section();

        // Sección de Estilos
        $this->start_controls_section(
            'section_banner_style',
            [
                'label' => esc_html__('Estilos del Banner', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Control para margen inferior
        $this->add_responsive_control(
            'banner_margin_bottom',
            [
                'label' => esc_html__('Margen Inferior', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 39,
                ],
                'selectors' => [
                    '{{WRAPPER}} .home-v1-fullbanner-ad' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Control para animación
        $this->add_control(
            'banner_animation',
            [
                'label' => esc_html__('Animación', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'fadeIn',
                'options' => [
                    'none' => esc_html__('Sin animación', 'kinta-electric-elementor'),
                    'fadeIn' => esc_html__('Fade In', 'kinta-electric-elementor'),
                    'slideInUp' => esc_html__('Slide In Up', 'kinta-electric-elementor'),
                    'slideInDown' => esc_html__('Slide In Down', 'kinta-electric-elementor'),
                    'slideInLeft' => esc_html__('Slide In Left', 'kinta-electric-elementor'),
                    'slideInRight' => esc_html__('Slide In Right', 'kinta-electric-elementor'),
                ],
            ]
        );

        // Control para duración de animación
        $this->add_control(
            'animation_duration',
            [
                'label' => esc_html__('Duración de Animación (ms)', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1000,
                'min' => 100,
                'max' => 5000,
                'step' => 100,
                'condition' => [
                    'banner_animation!' => 'none',
                ],
            ]
        );

        $this->end_controls_section();

        // Sección de Estilos de Imagen
        $this->start_controls_section(
            'section_image_style',
            [
                'label' => esc_html__('Estilos de Imagen', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Control para border radius
        $this->add_responsive_control(
            'image_border_radius',
            [
                'label' => esc_html__('Border Radius', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .home-v1-fullbanner-ad img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Control para sombra
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow',
                'label' => esc_html__('Sombra de la Imagen', 'kinta-electric-elementor'),
                'selector' => '{{WRAPPER}} .home-v1-fullbanner-ad img',
            ]
        );

        // Control para hover effects
        $this->add_control(
            'image_hover_effect',
            [
                'label' => esc_html__('Efecto Hover', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => esc_html__('Sin efecto', 'kinta-electric-elementor'),
                    'scale' => esc_html__('Escalar', 'kinta-electric-elementor'),
                    'opacity' => esc_html__('Opacidad', 'kinta-electric-elementor'),
                    'brightness' => esc_html__('Brillo', 'kinta-electric-elementor'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Renderizar widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        // Obtener imagen
        $image = $settings['banner_image'];
        if (!$image || !$image['url']) {
            return;
        }

        // Obtener enlace
        $link = $settings['banner_link'];
        $link_url = $link['url'] ? $link['url'] : '#';
        $link_target = $link['is_external'] ? ' target="_blank"' : '';
        $link_nofollow = $link['nofollow'] ? ' rel="nofollow"' : '';

        // Obtener texto alternativo
        $alt_text = $settings['banner_alt'] ? $settings['banner_alt'] : 'Banner promocional';

        // Obtener animación
        $animation = $settings['banner_animation'];
        $animation_duration = $settings['animation_duration'];

        // Obtener efecto hover
        $hover_effect = $settings['image_hover_effect'];

        // Clases CSS
        $wrapper_classes = ['home-v1-banner-block', 'animate-in-view'];
        if ($animation !== 'none') {
            $wrapper_classes[] = 'animate-' . $animation;
        }

        $image_classes = ['img-fluid'];
        if ($hover_effect !== 'none') {
            $image_classes[] = 'hover-' . $hover_effect;
        }

        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>" 
             data-animation="<?php echo esc_attr($animation); ?>"
             <?php if ($animation !== 'none' && $animation_duration): ?>
             style="animation-duration: <?php echo esc_attr($animation_duration); ?>ms;"
             <?php endif; ?>>
            <div class="home-v1-fullbanner-ad fullbanner-ad">
                <a href="<?php echo esc_url($link_url); ?>"<?php echo $link_target . $link_nofollow; ?>>
                    <img src="<?php echo esc_url($image['url']); ?>" 
                         class="<?php echo esc_attr(implode(' ', $image_classes)); ?>"
                         alt="<?php echo esc_attr($alt_text); ?>">
                </a>
            </div>
        </div>

        <style>
        .home-v1-banner-block {
            position: relative;
            overflow: hidden;
        }

        .home-v1-fullbanner-ad {
            position: relative;
            display: block;
        }

        .home-v1-fullbanner-ad img {
            width: 100%;
            height: auto;
            display: block;
            transition: all 0.3s ease;
        }

        /* Efectos hover */
        .hover-scale:hover {
            transform: scale(1.05);
        }

        .hover-opacity:hover {
            opacity: 0.8;
        }

        .hover-brightness:hover {
            filter: brightness(1.1);
        }

        /* Animaciones */
        .animate-fadeIn {
            opacity: 0;
            animation: fadeIn 1s ease-in-out forwards;
        }

        .animate-slideInUp {
            opacity: 0;
            transform: translateY(30px);
            animation: slideInUp 1s ease-in-out forwards;
        }

        .animate-slideInDown {
            opacity: 0;
            transform: translateY(-30px);
            animation: slideInDown 1s ease-in-out forwards;
        }

        .animate-slideInLeft {
            opacity: 0;
            transform: translateX(-30px);
            animation: slideInLeft 1s ease-in-out forwards;
        }

        .animate-slideInRight {
            opacity: 0;
            transform: translateX(30px);
            animation: slideInRight 1s ease-in-out forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .home-v1-fullbanner-ad {
                margin-bottom: 20px;
            }
        }
        </style>
        <?php
    }

    /**
     * Renderizar widget en modo de edición
     */
    protected function content_template() {
        ?>
        <#
        var image = settings.banner_image;
        var link = settings.banner_link;
        var altText = settings.banner_alt || 'Banner promocional';
        var animation = settings.banner_animation;
        var animationDuration = settings.animation_duration;
        var hoverEffect = settings.image_hover_effect;
        
        var wrapperClasses = ['home-v1-banner-block', 'animate-in-view'];
        if (animation !== 'none') {
            wrapperClasses.push('animate-' + animation);
        }
        
        var imageClasses = ['img-fluid'];
        if (hoverEffect !== 'none') {
            imageClasses.push('hover-' + hoverEffect);
        }
        
        var linkUrl = link.url || '#';
        var linkTarget = link.is_external ? ' target="_blank"' : '';
        var linkNofollow = link.nofollow ? ' rel="nofollow"' : '';
        #>
        
        <div class="{{{ wrapperClasses.join(' ') }}}" 
             data-animation="{{{ animation }}}"
             <# if (animation !== 'none' && animationDuration) { #>
             style="animation-duration: {{{ animationDuration }}}ms;"
             <# } #>>
            <div class="home-v1-fullbanner-ad fullbanner-ad">
                <a href="{{{ linkUrl }}}"{{{ linkTarget }}}{{{ linkNofollow }}}>
                    <img src="{{{ image.url }}}" 
                         class="{{{ imageClasses.join(' ') }}}"
                         alt="{{{ altText }}}">
                </a>
            </div>
        </div>
        <?php
    }
}
