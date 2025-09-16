<?php
/**
 * Kintaelectric03 Deals and Tabs Widget
 * 
 * Widget dinámico para mostrar ofertas especiales con countdown y tabs de productos de WooCommerce
 *
 * @package kinta-electric-elementor
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class KEE_Kintaelectric03_Deals_And_Tabs_Widget extends KEE_Base_Widget
{
    public function get_name()
    {
        return 'kintaelectric03-deals-and-tabs';
    }

    public function get_title()
    {
        return esc_html__('Kintaelectric03 - Ofertas y Pestañas', 'kinta-electric-elementor');
    }

    public function get_icon()
    {
        return 'eicon-tabs';
    }

    public function get_categories()
    {
        return ['kinta-electric'];
    }

    public function get_keywords()
    {
        return ['ofertas', 'pestañas', 'woocommerce', 'productos', 'countdown', 'descuentos', 'deals', 'tabs', 'products', 'offers'];
    }

    /**
     * Obtener dependencias de scripts específicas del widget
     */
    protected function get_widget_script_depends() {
        return ['kintaelectric03-countdown'];
    }

    /**
     * Obtener dependencias de estilos específicas del widget
     */
    protected function get_widget_style_depends() {
        return ['kintaelectric03-countdown'];
    }

    protected function register_controls()
    {
        // Sección: Oferta Especial
        $this->start_controls_section(
            'special_offer_section',
            [
                'label' => esc_html__('Oferta Especial', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'special_offer_title',
            [
                'label' => esc_html__('Título de la Oferta', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Oferta Especial', 'kinta-electric-elementor'),
                'placeholder' => esc_html__('Ingresa el título de la oferta', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'special_offer_product',
            [
                'label' => esc_html__('Producto en Oferta', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_products_on_sale(),
                'label_block' => true,
                'description' => esc_html__('Selecciona un producto que esté en oferta', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'countdown_days',
            [
                'label' => esc_html__('Días para Countdown', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 7,
                'min' => 1,
                'max' => 30,
                'description' => esc_html__('Número de días para el countdown', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'savings_text',
            [
                'label' => esc_html__('Texto de Ahorro', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Ahorra', 'kinta-electric-elementor'),
                'placeholder' => esc_html__('Ingresa el texto de ahorro (ej: Ahorra)', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'savings_amount',
            [
                'label' => esc_html__('Cantidad de Ahorro', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('$19.00', 'kinta-electric-elementor'),
                'placeholder' => esc_html__('Ingresa la cantidad (ej: $19.00)', 'kinta-electric-elementor'),
            ]
        );

        $this->end_controls_section();

        // Sección: Tabs de Productos
        $this->start_controls_section(
            'tabs_section',
            [
                'label' => esc_html__('Tabs de Productos', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $tabs_repeater = new \Elementor\Repeater();

        $tabs_repeater->add_control(
            'tab_name',
            [
                'label' => esc_html__('Nombre del Tab', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Featured', 'kinta-electric-elementor'),
                'placeholder' => esc_html__('Ingresa el nombre del tab', 'kinta-electric-elementor'),
            ]
        );

        $tabs_repeater->add_control(
            'tab_categories',
            [
                'label' => esc_html__('Categorías', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_product_categories(),
                'multiple' => true,
                'label_block' => true,
                'description' => esc_html__('Selecciona las categorías para este tab', 'kinta-electric-elementor'),
            ]
        );

        $tabs_repeater->add_control(
            'products_per_tab',
            [
                'label' => esc_html__('Productos por Tab', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 20,
            ]
        );

        $tabs_repeater->add_control(
            'tab_orderby',
            [
                'label' => esc_html__('Ordenar por', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => esc_html__('Fecha', 'kinta-electric-elementor'),
                    'popularity' => esc_html__('Popularidad', 'kinta-electric-elementor'),
                    'rating' => esc_html__('Valoración', 'kinta-electric-elementor'),
                    'price' => esc_html__('Precio', 'kinta-electric-elementor'),
                    'menu_order' => esc_html__('Orden del menú', 'kinta-electric-elementor'),
                ],
            ]
        );

        $this->add_control(
            'tabs_list',
            [
                'label' => esc_html__('Tabs', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $tabs_repeater->get_controls(),
                'default' => [
                    [
                        'tab_name' => esc_html__('Destacados', 'kinta-electric-elementor'),
                        'tab_categories' => [],
                        'products_per_tab' => 8,
                        'tab_orderby' => 'date',
                    ],
                    [
                        'tab_name' => esc_html__('En Oferta', 'kinta-electric-elementor'),
                        'tab_categories' => [],
                        'products_per_tab' => 8,
                        'tab_orderby' => 'popularity',
                    ],
                    [
                        'tab_name' => esc_html__('Mejor Valorados', 'kinta-electric-elementor'),
                        'tab_categories' => [],
                        'products_per_tab' => 8,
                        'tab_orderby' => 'rating',
                    ],
                ],
                'title_field' => '{{{ tab_name }}}',
            ]
        );

        $this->end_controls_section();

        // Sección: Configuración General
        $this->start_controls_section(
            'general_settings_section',
            [
                'label' => esc_html__('Configuración General', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'columns_mobile',
            [
                'label' => esc_html__('Columnas Móvil', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '2',
                'options' => [
                    '1' => '1',
                    '2' => '2',
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
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
            ]
        );

        $this->add_control(
            'columns_desktop',
            [
                'label' => esc_html__('Columnas Desktop', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        if (empty($settings['tabs_list'])) {
            return;
        }

        $column_classes = sprintf(
            'row-cols-%s row-cols-md-%s row-cols-lg-%s row-cols-xl-%s row-cols-xxl-%s',
            $settings['columns_mobile'],
            $settings['columns_tablet'],
            $settings['columns_desktop'],
            $settings['columns_desktop'],
            $settings['columns_desktop']
        );
        ?>

        <div class="home-v1-deals-and-tabs deals-and-tabs row">
            <!-- Oferta Especial -->
            <div class="deals-block col-12 col-md-6 col-lg-5 col-xl-4">
                <?php $this->render_special_offer($settings); ?>
            </div>
            
            <!-- Tabs de Productos -->
            <div class="tabs-block col-md-6 col-lg-7 col-xl-8">
                <?php $this->render_products_tabs($settings, $column_classes); ?>
            </div>
        </div>

        <?php
    }

    protected function render_special_offer($settings)
    {
        $special_product = null;
        if (!empty($settings['special_offer_product'])) {
            $special_product = wc_get_product($settings['special_offer_product']);
        }

        if (!$special_product || !$special_product->is_on_sale()) {
            // Si no hay producto especial, mostrar el primer producto en oferta
            $on_sale_products = wc_get_products([
                'status' => 'publish',
                'limit' => 1,
                'meta_query' => [
                    [
                        'key' => '_sale_price',
                        'value' => '',
                        'compare' => '!='
                    ]
                ]
            ]);
            
            if (!empty($on_sale_products)) {
                $special_product = $on_sale_products[0];
            }
        }

        if (!$special_product) {
            return;
        }

        $product_id = $special_product->get_id();
        $product_url = get_permalink($product_id);
        $product_title = $special_product->get_name();
        $product_image = wp_get_attachment_image(
            get_post_thumbnail_id($product_id),
            'woocommerce_thumbnail',
            false,
            ['width' => '300', 'height' => '300', 'loading' => 'lazy']
        );
        $product_price = $special_product->get_price_html();
        $countdown_days = $settings['countdown_days'] ?? 7;
        ?>

        <section class="section-onsale-product">
            <header>
                <h2 class="h1"><?php echo esc_html($settings['special_offer_title']); ?></h2>
                <div class="savings">
                    <span class="savings-text">
                        <?php echo esc_html($settings['savings_text']); ?> 
                        <span class="woocommerce-Price-amount amount">
                            <bdi>
                                <span class="woocommerce-Price-currencySymbol">$</span><?php echo esc_html(str_replace('$', '', $settings['savings_amount'])); ?>
                            </bdi>
                        </span>
                    </span>
                </div>
            </header>

            <div class="onsale-products">
                <div class="onsale-product">
                    <a href="<?php echo esc_url($product_url); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                        <div class="product-thumbnail product-item__thumbnail">
                            <?php echo $product_image; ?>
                        </div>
                        <h2 class="woocommerce-loop-product__title"><?php echo esc_html($product_title); ?></h2>
                    </a>
                    <span class="price">
                        <span class="electro-price"><?php echo $product_price; ?></span>
                    </span>
                    <div class="kintaelectric-countdown-timer">
                        <div class="kintaelectric-marketing-text text-xs-center">
                            <?php esc_html_e('¡Apúrate! La oferta termina en:', 'kinta-electric-elementor'); ?>
                        </div>
                        <div class="kintaelectric-countdown" data-days="<?php echo esc_attr($countdown_days); ?>"></div>
                    </div>
                </div>
            </div>
        </section>

        <?php
    }

    protected function render_products_tabs($settings, $column_classes)
    {
        ?>
        <div class="products-carousel-tabs">
            <ul class="nav nav-inline">
                <?php foreach ($settings['tabs_list'] as $index => $tab) : ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                           href="#tab-products-<?php echo esc_attr($index + 1); ?>" 
                           data-bs-toggle="tab">
                            <?php echo esc_html($tab['tab_name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content">
                <?php foreach ($settings['tabs_list'] as $index => $tab) : ?>
                    <div class="tab-pane <?php echo $index === 0 ? 'active' : ''; ?>" 
                         id="tab-products-<?php echo esc_attr($index + 1); ?>" 
                         role="tabpanel">
                        <?php $this->render_tab_products($tab, $column_classes); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    protected function render_tab_products($tab, $column_classes)
    {
        $args = [
            'post_type' => 'product',
            'posts_per_page' => $tab['products_per_tab'],
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_stock_status',
                    'value' => 'instock'
                ]
            ]
        ];

        // Añadir filtro por categorías si están seleccionadas
        if (!empty($tab['tab_categories'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $tab['tab_categories']
                ]
            ];
        }

        // Añadir ordenamiento
        switch ($tab['tab_orderby']) {
            case 'popularity':
                $args['meta_key'] = 'total_sales';
                $args['orderby'] = 'meta_value_num';
                break;
            case 'rating':
                $args['meta_key'] = '_wc_average_rating';
                $args['orderby'] = 'meta_value_num';
                break;
            case 'price':
                $args['meta_key'] = '_price';
                $args['orderby'] = 'meta_value_num';
                break;
            default:
                $args['orderby'] = $tab['tab_orderby'];
        }

        $products = get_posts($args);

        if (empty($products)) {
            echo '<p>' . esc_html__('No se encontraron productos.', 'kinta-electric-elementor') . '</p>';
            return;
        }
        ?>

        <div class="woocommerce columns-3">
            <ul data-view="grid" data-bs-toggle="regular-products" 
                class="products products list-unstyled row g-0 <?php echo esc_attr($column_classes); ?>">
                <?php foreach ($products as $product_post) : ?>
                    <?php
                    $product = wc_get_product($product_post->ID);
                    if (!$product) continue;

                    $product_id = $product->get_id();
                    $product_url = get_permalink($product_id);
                    $product_title = $product->get_name();
                    $product_image = wp_get_attachment_image(
                        get_post_thumbnail_id($product_id),
                        'woocommerce_thumbnail',
                        false,
                        ['width' => '300', 'height' => '300', 'loading' => 'lazy']
                    );
                    $product_price = $product->get_price_html();
                    $product_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']);
                    $product_rating = $product->get_average_rating();
                    $product_sku = $product->get_sku();
                    $product_short_description = $product->get_short_description();
                    ?>

                    <li class="product type-product post-<?php echo esc_attr($product_id); ?> status-publish <?php echo $product->is_on_sale() ? 'sale' : ''; ?> instock <?php echo implode(' ', array_map(function($cat) { return 'product_cat-' . sanitize_title($cat); }, $product_categories)); ?> has-post-thumbnail shipping-taxable purchasable product-type-<?php echo esc_attr($product->get_type()); ?>">
                        <div class="product-outer product-item__outer">
                            <div class="product-inner product-item__inner">
                                <div class="product-loop-header product-item__header">
                                    <span class="loop-product-categories">
                                        <?php if (!empty($product_categories)) : ?>
                                            <?php foreach ($product_categories as $index => $category) : ?>
                                                <a href="<?php echo esc_url(get_term_link(get_term_by('name', $category, 'product_cat'))); ?>" rel="tag"><?php echo esc_html($category); ?></a><?php echo $index < count($product_categories) - 1 ? ', ' : ''; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </span>
                                    <a href="<?php echo esc_url($product_url); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                        <h2 class="woocommerce-loop-product__title"><?php echo esc_html($product_title); ?></h2>
                                        <div class="product-thumbnail product-item__thumbnail">
                                            <?php echo $product_image; ?>
                                        </div>
                                    </a>
                                </div>

                                <div class="product-loop-body product-item__body">
                                    <span class="loop-product-categories">
                                        <?php if (!empty($product_categories)) : ?>
                                            <?php foreach ($product_categories as $index => $category) : ?>
                                                <a href="<?php echo esc_url(get_term_link(get_term_by('name', $category, 'product_cat'))); ?>" rel="tag"><?php echo esc_html($category); ?></a><?php echo $index < count($product_categories) - 1 ? ', ' : ''; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </span>
                                    <a href="<?php echo esc_url($product_url); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                        <h2 class="woocommerce-loop-product__title"><?php echo esc_html($product_title); ?></h2>
                                        <div class="product-rating">
                                            <div class="star-rating" title="Rated <?php echo esc_attr($product_rating); ?> out of 5">
                                                <span style="width:<?php echo esc_attr(($product_rating / 5) * 100); ?>%">
                                                    <strong class="rating"><?php echo esc_html($product_rating); ?></strong> out of 5
                                                </span>
                                            </div>
                                            (<?php echo esc_html($product->get_rating_count()); ?>)
                                        </div>
                                        <?php if (!empty($product_short_description)) : ?>
                                            <div class="product-short-description">
                                                <?php echo wp_kses_post($product_short_description); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($product_sku)) : ?>
                                            <div class="product-sku">SKU: <?php echo esc_html($product_sku); ?></div>
                                        <?php endif; ?>
                                    </a>
                                </div>

                                <div class="product-loop-footer product-item__footer">
                                    <div class="price-add-to-cart">
                                        <span class="price">
                                            <span class="electro-price"><?php echo $product_price; ?></span>
                                        </span>
                                        <div class="add-to-cart-wrap">
                                            <?php
                                            $add_to_cart_url = wc_get_cart_url() . '?add-to-cart=' . $product_id;
                                            ?>
                                            <a href="<?php echo esc_url($add_to_cart_url); ?>" 
                                               aria-describedby="woocommerce_loop_add_to_cart_link_describedby_<?php echo esc_attr($product_id); ?>"
                                               data-quantity="1"
                                               class="button product_type_<?php echo esc_attr($product->get_type()); ?> add_to_cart_button ajax_add_to_cart"
                                               data-product_id="<?php echo esc_attr($product_id); ?>"
                                               data-product_sku="<?php echo esc_attr($product_sku); ?>"
                                               aria-label="<?php echo esc_attr(sprintf(__('Add to cart: &ldquo;%s&rdquo;', 'kinta-electric-elementor'), $product_title)); ?>"
                                               rel="nofollow"
                                               data-success_message="<?php echo esc_attr(sprintf(__('&ldquo;%s&rdquo; has been added to your cart', 'kinta-electric-elementor'), $product_title)); ?>">
                                                <?php esc_html_e('Add to cart', 'kinta-electric-elementor'); ?>
                                            </a>
                                            <span id="woocommerce_loop_add_to_cart_link_describedby_<?php echo esc_attr($product_id); ?>" class="screen-reader-text"></span>
                                        </div>
                                    </div>

                                    <div class="hover-area">
                                        <div class="action-buttons">
                    <?php 
                    // YITH Wishlist - Múltiples métodos de integración
                    if (defined('YITH_WCWL')) : 
                        // Método 1: Función directa si está disponible
                        if (function_exists('yith_wcwl_add_to_wishlist_button')) {
                            yith_wcwl_add_to_wishlist_button($product_id);
                        }
                        // Método 2: Shortcode como fallback
                        elseif (shortcode_exists('yith_wcwl_add_to_wishlist')) {
                            echo do_shortcode('[yith_wcwl_add_to_wishlist product_id="' . $product_id . '"]');
                        }
                        // Método 3: HTML manual con nonce correcto
                        else {
                            $wishlist_nonce = wp_create_nonce('add_to_wishlist');
                            echo '<a href="#" class="add_to_wishlist" data-product-id="' . $product_id . '" data-nonce="' . $wishlist_nonce . '">' . __('Add to wishlist', 'kinta-electric-elementor') . '</a>';
                        }
                    endif; 
                    ?>

                    <?php 
                    // YITH Compare - Múltiples métodos de integración
                    if (defined('YITH_WOOCOMPARE')) : 
                        // Método 1: Función directa si está disponible
                        if (function_exists('yith_woocompare_add_compare_button')) {
                            yith_woocompare_add_compare_button($product_id);
                        }
                        // Método 2: Shortcode como fallback
                        elseif (shortcode_exists('yith_compare_button')) {
                            echo do_shortcode('[yith_compare_button product="' . $product_id . '"]');
                        }
                        // Método 3: HTML manual como último recurso
                        else {
                            echo '<a href="#" class="compare" data-product-id="' . $product_id . '">' . __('Compare', 'kinta-electric-elementor') . '</a>';
                        }
                    endif; 
                    ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php
    }

    protected function get_products_on_sale()
    {
        if (!class_exists('WooCommerce')) {
            return [];
        }

        $products = wc_get_products([
            'status' => 'publish',
            'limit' => 50,
            'meta_query' => [
                [
                    'key' => '_sale_price',
                    'value' => '',
                    'compare' => '!='
                ]
            ]
        ]);

        $options = [];
        foreach ($products as $product) {
            $options[$product->get_id()] = $product->get_name();
        }

        return $options;
    }

    protected function get_product_categories()
    {
        if (!class_exists('WooCommerce')) {
            return [];
        }

        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'parent' => 0,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);

        if (is_wp_error($categories) || empty($categories)) {
            return [];
        }

        $options = [];
        foreach ($categories as $category) {
            $options[$category->term_id] = $category->name;
        }

        return $options;
    }

    protected function content_template()
    {
        ?>
        <div class="home-v1-deals-and-tabs deals-and-tabs row">
            <div class="deals-block col-12 col-md-6 col-lg-5 col-xl-4">
                <section class="section-onsale-product">
                    <header>
                        <h2 class="h1">{{{ settings.special_offer_title }}}</h2>
                        <div class="savings">
                            <span class="savings-text">
                                {{{ settings.savings_text }}} 
                                <span class="woocommerce-Price-amount amount">
                                    <bdi>
                                        <span class="woocommerce-Price-currencySymbol">$</span>{{{ settings.savings_amount.replace('$', '') }}}
                                    </bdi>
                                </span>
                            </span>
                        </div>
                    </header>
                    <div class="onsale-products">
                        <div class="onsale-product">
                            <div class="product-thumbnail product-item__thumbnail">
                                <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="Product" width="300" height="300">
                            </div>
                            <h2 class="woocommerce-loop-product__title">Product Title</h2>
                            <span class="price">
                                <span class="electro-price">$99.00</span>
                            </span>
                            <div class="kintaelectric-countdown-timer">
                                <div class="kintaelectric-marketing-text text-xs-center">
                                    <?php esc_html_e('¡Apúrate! La oferta termina en:', 'kinta-electric-elementor'); ?>
                                </div>
                                <div class="kintaelectric-countdown" data-days="{{{ settings.countdown_days }}}"></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            
            <div class="tabs-block col-md-6 col-lg-7 col-xl-8">
                <div class="products-carousel-tabs">
                    <ul class="nav nav-inline">
                        <# _.each(settings.tabs_list, function(tab, index) { #>
                            <li class="nav-item">
                                <a class="nav-link <# if (index === 0) { #>active<# } #>" 
                                   href="#tab-products-{{ index + 1 }}" 
                                   data-bs-toggle="tab">
                                    {{{ tab.tab_name }}}
                                </a>
                            </li>
                        <# }); #>
                    </ul>
                    <div class="tab-content">
                        <# _.each(settings.tabs_list, function(tab, index) { #>
                            <div class="tab-pane <# if (index === 0) { #>active<# } #>" 
                                 id="tab-products-{{ index + 1 }}" 
                                 role="tabpanel">
                                <div class="woocommerce columns-3">
                                    <ul class="products products list-unstyled row g-0 row-cols-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-4">
                                        <li class="product">
                                            <div class="product-outer product-item__outer">
                                                <div class="product-inner product-item__inner">
                                                    <div class="product-loop-header product-item__header">
                                                        <div class="product-thumbnail product-item__thumbnail">
                                                            <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="Product" width="300" height="300">
                                                        </div>
                                                        <h2 class="woocommerce-loop-product__title">Product Title</h2>
                                                    </div>
                                                    <div class="product-loop-footer product-item__footer">
                                                        <div class="price-add-to-cart">
                                                            <span class="price">
                                                                <span class="electro-price">$99.00</span>
                                                            </span>
                                                            <div class="add-to-cart-wrap">
                                                                <a href="#" class="button">Add to cart</a>
                                                            </div>
                                                        </div>
                                                        <div class="hover-area">
                                                            <div class="action-buttons">
                                                                <div class="yith-wcwl-add-to-wishlist">
                                                                    <a href="#" class="add_to_wishlist">Add to wishlist</a>
                                                                </div>
                                                                <a href="#" class="compare">Compare</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <# }); #>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
