<?php
/**
 * Widget Kintaelectric05 Dynamic Products para Elementor
 * 
 * Widget dinámico basado en la estructura del tema Kinta Electric
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Kintaelectric05 Dynamic Products
 */
class KEE_Kintaelectric05_Dynamic_Products_Widget extends KEE_Base_Widget {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'kintaelectric05_dynamic_products';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('Kintaelectric05 Dynamic Products', 'kinta-electric-elementor');
    }

    /**
     * Obtener icono del widget
     */
    public function get_icon() {
        return 'eicon-products';
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
        return ['products', 'dynamic', 'carousel', 'kintaelectric', 'woocommerce'];
    }

    /**
     * Obtener dependencias de scripts específicas del widget
     */
    protected function get_widget_script_depends() {
        return ['owl-carousel-js'];
    }

    /**
     * Obtener dependencias de estilos específicas del widget
     */
    protected function get_widget_style_depends() {
        return ['owl-carousel-css', 'owl-carousel-theme-css'];
    }

    /**
     * Registrar controles del widget
     */
    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Configuración de Contenido', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'section_title',
            [
                'label' => esc_html__('Título de la Sección', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Best Sellers', 'kinta-electric-elementor'),
                'placeholder' => esc_html__('Ingresa el título', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'product_source',
            [
                'label' => esc_html__('Fuente de Productos', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'featured',
                'options' => [
                    'featured' => esc_html__('Productos Destacados', 'kinta-electric-elementor'),
                    'onsale' => esc_html__('Productos en Oferta', 'kinta-electric-elementor'),
                    'top_rated' => esc_html__('Mejor Valorados', 'kinta-electric-elementor'),
                    'recent' => esc_html__('Más Recientes', 'kinta-electric-elementor'),
                    'category' => esc_html__('Por Categoría', 'kinta-electric-elementor'),
                ],
            ]
        );

        $this->add_control(
            'product_category',
            [
                'label' => esc_html__('Categoría de Productos', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_product_categories(),
                'condition' => [
                    'product_source' => 'category',
                ],
            ]
        );

        $this->add_control(
            'products_per_page',
            [
                'label' => esc_html__('Número de Productos', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 50,
            ]
        );

        $this->add_control(
            'show_navigation_tabs',
            [
                'label' => esc_html__('Mostrar Pestañas de Navegación', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electric-elementor'),
                'label_off' => esc_html__('No', 'kinta-electric-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'navigation_tabs',
            [
                'label' => esc_html__('Pestañas de Navegación', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'tab_title',
                        'label' => esc_html__('Título de la Pestaña', 'kinta-electric-elementor'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'default' => esc_html__('Top 20', 'kinta-electric-elementor'),
                    ],
                    [
                        'name' => 'tab_link',
                        'label' => esc_html__('Enlace de la Pestaña', 'kinta-electric-elementor'),
                        'type' => \Elementor\Controls_Manager::URL,
                        'placeholder' => esc_html__('https://tu-sitio.com', 'kinta-electric-elementor'),
                    ],
                    [
                        'name' => 'is_active',
                        'label' => esc_html__('Pestaña Activa', 'kinta-electric-elementor'),
                        'type' => \Elementor\Controls_Manager::SWITCHER,
                        'label_on' => esc_html__('Sí', 'kinta-electric-elementor'),
                        'label_off' => esc_html__('No', 'kinta-electric-elementor'),
                        'return_value' => 'yes',
                        'default' => 'no',
                    ],
                ],
                'default' => [
                    [
                        'tab_title' => esc_html__('Top 20', 'kinta-electric-elementor'),
                        'is_active' => 'yes',
                    ],
                ],
                'condition' => [
                    'show_navigation_tabs' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Sección de configuración del carrusel
        $this->start_controls_section(
            'carousel_section',
            [
                'label' => esc_html__('Configuración del Carrusel', 'kinta-electric-elementor'),
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
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
            ]
        );

        $this->add_control(
            'columns_tablet',
            [
                'label' => esc_html__('Columnas Tablet', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
            ]
        );

        $this->add_control(
            'columns_mobile',
            [
                'label' => esc_html__('Columnas Mobile', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '2',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                ],
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => esc_html__('Autoplay', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electric-elementor'),
                'label_off' => esc_html__('No', 'kinta-electric-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'autoplay_timeout',
            [
                'label' => esc_html__('Tiempo de Autoplay (ms)', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5000,
                'min' => 1000,
                'max' => 10000,
                'step' => 500,
                'condition' => [
                    'autoplay' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_dots',
            [
                'label' => esc_html__('Mostrar Puntos de Navegación', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electric-elementor'),
                'label_off' => esc_html__('No', 'kinta-electric-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Sección de estilos
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Estilos', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'section_background_color',
            [
                'label' => esc_html__('Color de Fondo de la Sección', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-product-cards-carousel' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Color del Título', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-product-cards-carousel h2' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => esc_html__('Tipografía del Título', 'kinta-electric-elementor'),
                'selector' => '{{WRAPPER}} .section-product-cards-carousel h2',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Renderizar el widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if (!$this->is_woocommerce_active()) {
            echo '<div class="elementor-alert elementor-alert-warning">' . 
                 esc_html__('WooCommerce no está activo. Este widget requiere WooCommerce.', 'kinta-electric-elementor') . 
                 '</div>';
            return;
        }

        $products = $this->get_products($settings);
        
        if (empty($products)) {
            echo '<div class="elementor-alert elementor-alert-info">' . 
                 esc_html__('No se encontraron productos.', 'kinta-electric-elementor') . 
                 '</div>';
            return;
        }

        $this->render_products_section($settings, $products);
    }

    /**
     * Obtener productos según la configuración
     */
    private function get_products($settings) {
        $args = [
            'status' => 'publish',
            'limit' => intval($settings['products_per_page']),
            'return' => 'ids',
        ];

        switch ($settings['product_source']) {
            case 'featured':
                $args['featured'] = true;
                break;
            case 'onsale':
                $args['on_sale'] = true;
                break;
            case 'top_rated':
                $args['orderby'] = 'rating';
                $args['order'] = 'DESC';
                break;
            case 'recent':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
            case 'category':
                if (!empty($settings['product_category'])) {
                    $args['category'] = [$settings['product_category']];
                }
                break;
        }

        return wc_get_products($args);
    }

    /**
     * Renderizar la sección de productos
     */
    private function render_products_section($settings, $products) {
        $carousel_id = 'kintaelectric05-carousel-' . $this->get_id();
        $column_classes = sprintf(
            'row-cols-%s row-cols-md-%s row-cols-xl-%s',
            $settings['columns_mobile'],
            $settings['columns_tablet'],
            $settings['columns_desktop']
        );

        $carousel_options = [
            'items' => intval($settings['columns_desktop']),
            'nav' => false,
            'slideSpeed' => 300,
            'dots' => $settings['show_dots'] === 'yes',
            'rtl' => is_rtl(),
            'paginationSpeed' => 400,
            'navText' => ['', ''],
            'margin' => 0,
            'touchDrag' => true,
            'autoplay' => $settings['autoplay'] === 'yes',
            'autoplayTimeout' => intval($settings['autoplay_timeout']),
            'responsive' => [
                '0' => ['items' => intval($settings['columns_mobile'])],
                '768' => ['items' => intval($settings['columns_tablet'])],
                '1200' => ['items' => intval($settings['columns_desktop'])],
            ],
        ];
        ?>
        <section class="section-product-cards-carousel home-v1-product-cards-carousel animate-in-view" data-animation="fadeIn">
            <header class="show-nav">
                <h2 class="h1"><?php echo esc_html($settings['section_title']); ?></h2>
                
                <?php if ($settings['show_navigation_tabs'] === 'yes' && !empty($settings['navigation_tabs'])): ?>
                    <ul class="nav nav-inline">
                        <?php foreach ($settings['navigation_tabs'] as $index => $tab): ?>
                            <li class="nav-item <?php echo $tab['is_active'] === 'yes' ? 'active' : ''; ?>">
                                <?php if (!empty($tab['tab_link']['url'])): ?>
                                    <a class="nav-link" href="<?php echo esc_url($tab['tab_link']['url']); ?>" 
                                       <?php echo $tab['tab_link']['is_external'] ? 'target="_blank"' : ''; ?>
                                       <?php echo $tab['tab_link']['nofollow'] ? 'rel="nofollow"' : ''; ?>>
                                        <?php echo esc_html($tab['tab_title']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="nav-link"><?php echo esc_html($tab['tab_title']); ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </header>

            <div id="<?php echo esc_attr($carousel_id); ?>" 
                 data-ride="owl-carousel"
                 data-carousel-selector=".product-cards-carousel"
                 data-carousel-options="<?php echo esc_attr(json_encode($carousel_options)); ?>">
                <div class="woocommerce columns-3 product-cards-carousel owl-carousel">
                    <ul data-view="grid" 
                        data-bs-toggle="regular-products"
                        class="products products list-unstyled row g-0 <?php echo esc_attr($column_classes); ?>">
                        <?php foreach ($products as $product_id): ?>
                            <?php $this->render_product_card($product_id); ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * Renderizar tarjeta de producto individual
     */
    private function render_product_card($product_id) {
        $product = wc_get_product($product_id);
        if (!$product) return;

        $product_classes = [
            'product-card',
            'post-' . $product_id,
            'product',
            'type-product',
            'status-publish',
            'has-post-thumbnail',
        ];

        // Añadir clases de categorías
        $categories = wp_get_post_terms($product_id, 'product_cat');
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $product_classes[] = 'product_cat-' . $category->slug;
            }
        }

        // Añadir clases de estado
        if ($product->is_on_sale()) {
            $product_classes[] = 'sale';
        }
        if ($product->is_featured()) {
            $product_classes[] = 'featured';
        }
        if ($product->is_in_stock()) {
            $product_classes[] = 'instock';
        } else {
            $product_classes[] = 'outofstock';
        }

        $product_classes[] = 'shipping-taxable';
        $product_classes[] = 'purchasable';
        $product_classes[] = 'product-type-' . $product->get_type();
        ?>
        <li class="<?php echo esc_attr(implode(' ', $product_classes)); ?>">
            <div class="product-outer product-item__outer">
                <div class="product-inner">
                    <a class="card-media-left" 
                       href="<?php echo esc_url($product->get_permalink()); ?>" 
                       title="<?php echo esc_attr($product->get_name()); ?>">
                        <?php echo $product->get_image('woocommerce_thumbnail', ['loading' => 'lazy']); ?>
                    </a>

                    <div class="card-body">
                        <div class="card-body-inner">
                            <?php if ($categories): ?>
                                <span class="loop-product-categories">
                                    <?php 
                                    $category_links = array_map(function($cat) {
                                        return '<a href="' . esc_url(get_term_link($cat)) . '" rel="tag">' . esc_html($cat->name) . '</a>';
                                    }, $categories);
                                    echo implode(', ', $category_links);
                                    ?>
                                </span>
                            <?php endif; ?>
                            
                            <a href="<?php echo esc_url($product->get_permalink()); ?>" 
                               class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                <h2 class="woocommerce-loop-product__title"><?php echo esc_html($product->get_name()); ?></h2>
                            </a>
                            
                            <div class="price-add-to-cart">
                                <span class="price">
                                    <span class="electro-price"><?php echo $product->get_price_html(); ?></span>
                                </span>
                                
                                <div class="add-to-cart-wrap" 
                                     data-bs-toggle="tooltip" 
                                     data-bs-title="<?php esc_attr_e('Add to cart', 'kinta-electric-elementor'); ?>">
                                    <?php
                                    if ($product->is_in_stock()) {
                                        woocommerce_template_loop_add_to_cart();
                                    } else {
                                        echo '<a href="' . esc_url($product->get_permalink()) . '" class="button product_type_simple">' . 
                                             esc_html__('Read more', 'kinta-electric-elementor') . '</a>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hover-area">
                        <div class="action-buttons">
                            <?php echo $this->render_yith_wishlist_button($product_id); ?>
                            <?php echo $this->render_yith_compare_button($product_id); ?>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <?php
    }
}
