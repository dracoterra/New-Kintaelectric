<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Kintaelectric04 Products Tabs Widget
 * 
 * Widget de productos con pestañas en layout 4-1-4
 *
 * @package kinta-electric-elementor
 */
class KEE_Kintaelectric04_Products_Tabs_Widget extends KEE_Base_Widget
{
    /**
     * Get widget name.
     */
    public function get_name()
    {
        return 'kintaelectric04-products-tabs';
    }

    /**
     * Get widget title.
     */
    public function get_title()
    {
        return esc_html__('Kintaelectric04 - Productos con Pestañas', 'kinta-electric-elementor');
    }

    /**
     * Get widget icon.
     */
    public function get_icon()
    {
        return 'eicon-products';
    }

    /**
     * Get widget categories.
     */
    public function get_categories()
    {
        return ['kinta-electric'];
    }

    /**
     * Get widget keywords.
     */
    public function get_keywords()
    {
        return ['productos', 'pestañas', 'woocommerce', 'grid', 'tabs', 'products', 'kintaelectric'];
    }

    /**
     * Obtener dependencias de scripts específicas del widget
     */
    protected function get_widget_script_depends() {
        return [];
    }

    /**
     * Obtener dependencias de estilos específicas del widget
     */
    protected function get_widget_style_depends() {
        return [];
    }

    /**
     * Register widget controls.
     */
    protected function register_controls()
    {
        // Sección: Pestañas de Productos
        $this->start_controls_section(
            'tabs_section',
            [
                'label' => esc_html__('Pestañas de Productos', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'tabs_list',
            [
                'label' => esc_html__('Lista de Pestañas', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'tab_name',
                        'label' => esc_html__('Nombre de Pestaña', 'kinta-electric-elementor'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'default' => esc_html__('Mejores Ofertas', 'kinta-electric-elementor'),
                        'label_block' => true,
                    ],
                    [
                        'name' => 'tab_categories',
                        'label' => esc_html__('Categorías', 'kinta-electric-elementor'),
                        'type' => \Elementor\Controls_Manager::SELECT2,
                        'options' => $this->get_product_categories(),
                        'multiple' => true,
                        'label_block' => true,
                        'description' => esc_html__('Selecciona las categorías de productos para esta pestaña. Deja vacío para mostrar todos los productos.', 'kinta-electric-elementor'),
                    ],
                    [
                        'name' => 'products_per_tab',
                        'label' => esc_html__('Productos por Pestaña', 'kinta-electric-elementor'),
                        'type' => \Elementor\Controls_Manager::NUMBER,
                        'default' => 8,
                        'min' => 1,
                        'max' => 20,
                    ],
                    [
                        'name' => 'tab_orderby',
                        'label' => esc_html__('Ordenar por', 'kinta-electric-elementor'),
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'default' => 'date',
                        'options' => [
                            'date' => esc_html__('Fecha', 'kinta-electric-elementor'),
                            'title' => esc_html__('Título', 'kinta-electric-elementor'),
                            'price' => esc_html__('Precio', 'kinta-electric-elementor'),
                            'popularity' => esc_html__('Popularidad', 'kinta-electric-elementor'),
                            'rating' => esc_html__('Valoración', 'kinta-electric-elementor'),
                            'menu_order' => esc_html__('Orden del menú', 'kinta-electric-elementor'),
                        ],
                    ],
                ],
                'default' => [
                    [
                        'tab_name' => esc_html__('Mejores Ofertas', 'kinta-electric-elementor'),
                        'tab_categories' => [],
                        'products_per_tab' => 8,
                        'tab_orderby' => 'date',
                    ],
                    [
                        'tab_name' => esc_html__('TV & Audio', 'kinta-electric-elementor'),
                        'tab_categories' => [],
                        'products_per_tab' => 8,
                        'tab_orderby' => 'date',
                    ],
                    [
                        'tab_name' => esc_html__('Cámaras', 'kinta-electric-elementor'),
                        'tab_categories' => [],
                        'products_per_tab' => 8,
                        'tab_orderby' => 'date',
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
                'default' => '4',
                'options' => [
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
            ]
        );

        $this->end_controls_section();

        // Sección de Debug (solo para administradores)
        if (current_user_can('manage_options')) {
            $this->start_controls_section(
                'debug_section',
                [
                    'label' => esc_html__('Información de Depuración', 'kinta-electric-elementor'),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );

            $this->add_control(
                'debug_info',
                [
                    'type' => \Elementor\Controls_Manager::RAW_HTML,
                    'raw' => '<div style="background: #f0f0f0; padding: 15px; border-left: 4px solid #007cba;">
                        <h4>Categorías de Productos Disponibles:</h4>
                        <ul>' . $this->get_categories_debug_list() . '</ul>
                    </div>',
                ]
            );

            $this->end_controls_section();
        }
    }

    /**
     * Render widget output on the frontend.
     */
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

        <section class="products-4-1-4 stretch-full-width animate-in-view" data-animation="fadeIn">
            <h2 class="sr-only visually-hidden">Products Grid</h2>
            <div class="container">
                <?php $this->render_products_tabs($settings, $column_classes); ?>
            </div>
        </section>

        <?php
    }

    /**
     * Render products tabs
     */
    protected function render_products_tabs($settings, $column_classes)
    {
        ?>
        <ul class="nav nav-inline products-4-1-4__nav">
            <?php foreach ($settings['tabs_list'] as $index => $tab) : ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                       href="#kintaelectric-tab-products-<?php echo esc_attr($index + 1); ?>" 
                       data-bs-toggle="tab">
                        <?php echo esc_html($tab['tab_name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content">
            <?php foreach ($settings['tabs_list'] as $index => $tab) : ?>
                <div class="tab-pane <?php echo $index === 0 ? 'active' : ''; ?>" 
                     id="kintaelectric-tab-products-<?php echo esc_attr($index + 1); ?>" 
                     role="tabpanel">
                    <?php $this->render_tab_products($tab, $column_classes); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render products for a specific tab
     */
    protected function render_tab_products($tab, $column_classes)
    {
        $args = [
            'post_type' => 'product',
            'posts_per_page' => $tab['products_per_tab'],
            'post_status' => 'publish',
        ];

        // Añadir filtro por categorías si están seleccionadas
        if (!empty($tab['tab_categories']) && is_array($tab['tab_categories'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => array_map('intval', $tab['tab_categories']),
                    'operator' => 'IN'
                ]
            ];
        }

        // Configurar ordenamiento
        switch ($tab['tab_orderby']) {
            case 'price':
                $args['meta_key'] = '_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'popularity':
                $args['meta_key'] = 'total_sales';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'rating':
                $args['meta_key'] = '_wc_average_rating';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = $tab['tab_orderby'];
        }

        $products = get_posts($args);

        if (empty($products)) {
            echo '<p>' . esc_html__('No se encontraron productos.', 'kinta-electric-elementor') . '</p>';
            // Debug: Mostrar información de la consulta
            if (current_user_can('manage_options')) {
                echo '<div style="background: #f0f0f0; padding: 15px; margin: 15px 0; border-left: 4px solid #007cba; font-family: monospace; font-size: 12px;">';
                echo '<h4 style="margin-top: 0;">🔍 Debug Info - Pestaña: ' . esc_html($tab['tab_name']) . '</h4>';
                echo '<strong>Categorías seleccionadas:</strong> ' . (empty($tab['tab_categories']) ? 'Ninguna (mostrará todos los productos)' : implode(', ', $tab['tab_categories'])) . '<br>';
                echo '<strong>Productos solicitados:</strong> ' . $tab['products_per_tab'] . '<br>';
                echo '<strong>Ordenar por:</strong> ' . $tab['tab_orderby'] . '<br>';
                echo '<strong>Query args:</strong><br><pre style="background: white; padding: 10px; border: 1px solid #ddd; overflow-x: auto;">' . print_r($args, true) . '</pre>';
                
                // Verificar si hay productos en la base de datos
                $total_products = wp_count_posts('product');
                echo '<strong>Total productos en BD:</strong> ' . $total_products->publish . '<br>';
                
                // Verificar categorías disponibles
                $all_categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
                echo '<strong>Categorías disponibles:</strong> ' . (is_wp_error($all_categories) ? 'Error al cargar' : count($all_categories)) . '<br>';
                
                echo '</div>';
            }
            return;
        }
        ?>

        <div class="columns-4-1-4 row g-0">
            <!-- Productos Izquierda (4 productos) -->
            <div class="products-4 products-4-left col-md-3 col-xxl-4 d-xl-flex d-xxl-block column">
                <ul class="products exclude-auto-height list-unstyled flex-md-column flex-xxl-row mb-0">
                    <?php 
                    $left_products = array_slice($products, 0, 4);
                    foreach ($left_products as $product_post) : 
                        $this->render_single_product($product_post);
                    endforeach; 
                    ?>
                </ul>
            </div>

            <!-- Producto Principal (1 producto) -->
            <div class="products-1 col-md-6 col-xxl-4 column">
                <ul class="products list-unstyled product-main-2-1-2 show-btn">
                    <?php 
                    if (isset($products[4])) {
                        $this->render_featured_product($products[4]);
                    }
                    ?>
                </ul>
            </div>

            <!-- Productos Derecha (4 productos) -->
            <div class="products-4 products-4-right col-md-3 col-xxl-4 d-xl-flex d-xxl-block column">
                <ul class="products exclude-auto-height list-unstyled flex-md-column flex-xxl-row mb-0">
                    <?php 
                    $right_products = array_slice($products, 5, 4);
                    foreach ($right_products as $product_post) : 
                        $this->render_single_product($product_post);
                    endforeach; 
                    ?>
                </ul>
            </div>
        </div>

        <?php
    }

    /**
     * Render single product (left and right columns)
     */
    protected function render_single_product($product_post)
    {
        $product = wc_get_product($product_post->ID);
        if (!$product) return;

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
        $product_short_description = $product->get_short_description();
        $product_sku = $product->get_sku();
        $is_on_sale = $product->is_on_sale();
        $is_featured = $product->is_featured();
        $is_in_stock = $product->is_in_stock();
        $stock_status = $product->get_stock_status();

        $product_classes = [
            'product',
            'type-product',
            'post-' . $product_id,
            'status-publish',
            $is_in_stock ? 'instock' : 'outofstock',
            $is_featured ? 'featured' : '',
            $is_on_sale ? 'sale' : '',
            'shipping-taxable',
            'purchasable',
            'product-type-' . $product->get_type()
        ];

        // Añadir clases de categorías
        foreach ($product_categories as $category) {
            $product_classes[] = 'product_cat-' . sanitize_title($category);
        }

        $product_classes = array_filter($product_classes);
        ?>

        <li class="<?php echo esc_attr(implode(' ', $product_classes)); ?>">
            <div class="product-outer product-item__outer">
                <div class="product-inner product-item__inner">
                    <div class="product-loop-header product-item__header">
                        <span class="loop-product-categories">
                            <?php echo implode(', ', array_map(function($cat) {
                                $term_link = get_term_link($cat);
                                if (is_wp_error($term_link)) {
                                    return '<span>' . esc_html($cat) . '</span>';
                                }
                                return '<a href="' . esc_url($term_link) . '" rel="tag">' . esc_html($cat) . '</a>';
                            }, $product_categories)); ?>
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
                            <?php echo implode(', ', array_map(function($cat) {
                                $term_link = get_term_link($cat);
                                if (is_wp_error($term_link)) {
                                    return '<span>' . esc_html($cat) . '</span>';
                                }
                                return '<a href="' . esc_url($term_link) . '" rel="tag">' . esc_html($cat) . '</a>';
                            }, $product_categories)); ?>
                        </span>
                        <a href="<?php echo esc_url($product_url); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                            <h2 class="woocommerce-loop-product__title"><?php echo esc_html($product_title); ?></h2>
                            
                            <?php if ($product_rating > 0) : ?>
                                <div class="product-rating">
                                    <div class="star-rating" title="Rated <?php echo esc_attr($product_rating); ?> out of 5">
                                        <span style="width:<?php echo esc_attr(($product_rating / 5) * 100); ?>%">
                                            <strong class="rating"><?php echo esc_html($product_rating); ?></strong> out of 5
                                        </span>
                                    </div>
                                    (<?php echo esc_html($product->get_rating_count()); ?>)
                                </div>
                            <?php endif; ?>

                            <?php if ($product_short_description) : ?>
                                <div class="product-short-description">
                                    <?php echo wp_kses_post($product_short_description); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($product_sku) : ?>
                                <div class="product-sku">SKU: <?php echo esc_html($product_sku); ?></div>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="product-loop-footer product-item__footer">
                        <div class="price-add-to-cart">
                            <span class="price">
                                <span class="electro-price"><?php echo $product_price; ?></span>
                            </span>
                            <div class="add-to-cart-wrap" data-bs-toggle="tooltip" data-bs-title="<?php esc_attr_e('Add to cart', 'kinta-electric-elementor'); ?>">
                                <?php
                                if ($is_in_stock) {
                                    $add_to_cart_url = wc_get_cart_url() . '?add-to-cart=' . $product_id;
                                    ?>
                                    <a href="<?php echo esc_url($add_to_cart_url); ?>" 
                                       aria-describedby="woocommerce_loop_add_to_cart_link_describedby_<?php echo esc_attr($product_id); ?>"
                                       data-quantity="1"
                                       class="button product_type_<?php echo esc_attr($product->get_type()); ?> add_to_cart_button ajax_add_to_cart"
                                       data-product_id="<?php echo esc_attr($product_id); ?>"
                                       data-product_sku="<?php echo esc_attr($product_sku); ?>"
                                       aria-label="<?php echo esc_attr(sprintf(__('Añadir al carrito: &ldquo;%s&rdquo;', 'kinta-electric-elementor'), $product_title)); ?>"
                                       rel="nofollow"
                                       data-success_message="<?php echo esc_attr(sprintf(__('&ldquo;%s&rdquo; ha sido añadido a tu carrito', 'kinta-electric-elementor'), $product_title)); ?>">
                                        <?php esc_html_e('Añadir al carrito', 'kinta-electric-elementor'); ?>
                                    </a>
                                    <span id="woocommerce_loop_add_to_cart_link_describedby_<?php echo esc_attr($product_id); ?>" class="screen-reader-text"></span>
                                    <?php
                                } else {
                                    echo '<a href="' . esc_url($product_url) . '" class="button product_type_simple" rel="nofollow">' . esc_html__('Leer más', 'kinta-electric-elementor') . '</a>';
                                }
                                ?>
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
                                        echo '<a href="#" class="add_to_wishlist" data-product-id="' . $product_id . '" data-nonce="' . $wishlist_nonce . '">' . __('Añadir a favoritos', 'kinta-electric-elementor') . '</a>';
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
                                        echo '<a href="#" class="compare" data-product-id="' . $product_id . '">' . __('Comparar', 'kinta-electric-elementor') . '</a>';
                                    }
                                endif; 
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>

        <?php
    }

    /**
     * Render featured product (center column)
     */
    protected function render_featured_product($product_post)
    {
        $product = wc_get_product($product_post->ID);
        if (!$product) return;

        $product_id = $product->get_id();
        $product_url = get_permalink($product_id);
        $product_title = $product->get_name();
        $product_image = wp_get_attachment_image(
            get_post_thumbnail_id($product_id),
            'shop_single',
            false,
            ['width' => '600', 'height' => '550', 'loading' => 'lazy']
        );
        $product_price = $product->get_price_html();
        $product_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']);
        $product_rating = $product->get_average_rating();
        $product_short_description = $product->get_short_description();
        $product_sku = $product->get_sku();
        $is_on_sale = $product->is_on_sale();
        $is_featured = $product->is_featured();
        $is_in_stock = $product->is_in_stock();
        $stock_status = $product->get_stock_status();

        $product_classes = [
            'product',
            'type-product',
            'post-' . $product_id,
            'status-publish',
            $is_in_stock ? 'instock' : 'outofstock',
            $is_featured ? 'featured' : '',
            $is_on_sale ? 'sale' : '',
            'shipping-taxable',
            'purchasable',
            'product-type-' . $product->get_type()
        ];

        // Añadir clases de categorías
        foreach ($product_categories as $category) {
            $product_classes[] = 'product_cat-' . sanitize_title($category);
        }

        $product_classes = array_filter($product_classes);
        ?>

        <li class="<?php echo esc_attr(implode(' ', $product_classes)); ?>">
            <div class="product-outer product-item__outer">
                <div class="product-inner product-item__inner">
                    <div class="flex-div">
                        <div class="product-loop-header product-item__header">
                            <span class="loop-product-categories">
                                <?php echo implode(', ', array_map(function($cat) {
                                    $term_link = get_term_link($cat);
                                if (is_wp_error($term_link)) {
                                    return '<span>' . esc_html($cat) . '</span>';
                                }
                                return '<a href="' . esc_url($term_link) . '" rel="tag">' . esc_html($cat) . '</a>';
                                }, $product_categories)); ?>
                            </span>
                            <a href="<?php echo esc_url($product_url); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                <h2 class="woocommerce-loop-product__title"><?php echo esc_html($product_title); ?></h2>
                            </a>
                        </div>

                        <div class="images">
                            <a href="<?php echo esc_url($product_url); ?>" class="woocommerce-main-image" title="">
                                <?php echo $product_image; ?>
                            </a>
                            <div class="thumbnails columns-3">
                                <?php
                                $gallery_images = $product->get_gallery_image_ids();
                                $gallery_images = array_slice($gallery_images, 0, 3);
                                foreach ($gallery_images as $gallery_image_id) :
                                    $gallery_image = wp_get_attachment_image(
                                        $gallery_image_id,
                                        'shop_thumbnail',
                                        false,
                                        ['width' => '100', 'height' => '100', 'loading' => 'lazy']
                                    );
                                    ?>
                                    <a href="<?php echo esc_url($product_url); ?>" class="<?php echo $gallery_image_id === $gallery_images[0] ? 'first' : ''; ?><?php echo $gallery_image_id === end($gallery_images) ? ' last' : ''; ?>" title="">
                                        <?php echo $gallery_image; ?>
                                    </a>
                                    <?php
                                endforeach;
                                ?>
                            </div>
                        </div>

                        <div class="product-loop-body product-item__body">
                            <span class="loop-product-categories">
                                <?php echo implode(', ', array_map(function($cat) {
                                    $term_link = get_term_link($cat);
                                if (is_wp_error($term_link)) {
                                    return '<span>' . esc_html($cat) . '</span>';
                                }
                                return '<a href="' . esc_url($term_link) . '" rel="tag">' . esc_html($cat) . '</a>';
                                }, $product_categories)); ?>
                            </span>
                            <a href="<?php echo esc_url($product_url); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                <h2 class="woocommerce-loop-product__title"><?php echo esc_html($product_title); ?></h2>
                                
                                <?php if ($product_rating > 0) : ?>
                                    <div class="product-rating">
                                        <div class="star-rating" title="Rated <?php echo esc_attr($product_rating); ?> out of 5">
                                            <span style="width:<?php echo esc_attr(($product_rating / 5) * 100); ?>%">
                                                <strong class="rating"><?php echo esc_html($product_rating); ?></strong> out of 5
                                            </span>
                                        </div>
                                        (<?php echo esc_html($product->get_rating_count()); ?>)
                                    </div>
                                <?php endif; ?>

                                <?php if ($product_short_description) : ?>
                                    <div class="product-short-description">
                                        <?php echo wp_kses_post($product_short_description); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($product_sku) : ?>
                                    <div class="product-sku">SKU: <?php echo esc_html($product_sku); ?></div>
                                <?php endif; ?>
                            </a>
                        </div>

                        <div class="product-loop-footer product-item__footer">
                            <div class="price-add-to-cart">
                                <span class="price">
                                    <span class="electro-price"><?php echo $product_price; ?></span>
                                </span>
                                <div class="add-to-cart-wrap" data-bs-toggle="tooltip" data-bs-title="<?php esc_attr_e('Añadir al carrito', 'kinta-electric-elementor'); ?>">
                                    <?php
                                    if ($is_in_stock) {
                                        $add_to_cart_url = wc_get_cart_url() . '?add-to-cart=' . $product_id;
                                        ?>
                                        <a href="<?php echo esc_url($add_to_cart_url); ?>" 
                                           aria-describedby="woocommerce_loop_add_to_cart_link_describedby_featured_<?php echo esc_attr($product_id); ?>"
                                           data-quantity="1"
                                           class="button product_type_<?php echo esc_attr($product->get_type()); ?> add_to_cart_button ajax_add_to_cart"
                                           data-product_id="<?php echo esc_attr($product_id); ?>"
                                           data-product_sku="<?php echo esc_attr($product_sku); ?>"
                                           aria-label="<?php echo esc_attr(sprintf(__('Añadir al carrito: &ldquo;%s&rdquo;', 'kinta-electric-elementor'), $product_title)); ?>"
                                           rel="nofollow"
                                           data-success_message="<?php echo esc_attr(sprintf(__('&ldquo;%s&rdquo; ha sido añadido a tu carrito', 'kinta-electric-elementor'), $product_title)); ?>">
                                            <?php esc_html_e('Añadir al carrito', 'kinta-electric-elementor'); ?>
                                        </a>
                                        <span id="woocommerce_loop_add_to_cart_link_describedby_featured_<?php echo esc_attr($product_id); ?>" class="screen-reader-text"></span>
                                        <?php
                                    } else {
                                        echo '<a href="' . esc_url($product_url) . '" class="button product_type_simple" rel="nofollow">' . esc_html__('Leer más', 'kinta-electric-elementor') . '</a>';
                                    }
                                    ?>
                                </div>
                            </div>
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
                                    echo '<a href="#" class="compare" data-product-id="' . $product_id . '">' . __('Comparar', 'kinta-electric-elementor') . '</a>';
                                }
                            endif; 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </li>

        <?php
    }

    /**
     * Get product categories for select control
     */
    protected function get_product_categories()
    {
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $options = [];
        if (!is_wp_error($categories) && !empty($categories)) {
            foreach ($categories as $category) {
                $options[$category->term_id] = $category->name;
            }
        }

        return $options;
    }

    /**
     * Get categories debug list for admin
     */
    protected function get_categories_debug_list()
    {
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $list = '';
        if (!is_wp_error($categories) && !empty($categories)) {
            foreach ($categories as $category) {
                $list .= '<li><strong>ID:</strong> ' . $category->term_id . ' - <strong>Nombre:</strong> ' . $category->name . ' - <strong>Slug:</strong> ' . $category->slug . '</li>';
            }
        } else {
            $list = '<li>No se encontraron categorías de productos.</li>';
        }

        return $list;
    }

    /**
     * Render widget output in the editor.
     */
    protected function content_template()
    {
        ?>
        <section class="products-4-1-4 stretch-full-width animate-in-view" data-animation="fadeIn">
            <h2 class="sr-only visually-hidden">Products Grid</h2>
            <div class="container">
                <ul class="nav nav-inline products-4-1-4__nav">
                    <# _.each(settings.tabs_list, function(tab, index) { #>
                        <li class="nav-item">
                            <a class="nav-link <# if (index === 0) { #>active<# } #>" 
                               href="#kintaelectric-tab-products-{{ index + 1 }}" 
                               data-bs-toggle="tab">
                                {{{ tab.tab_name }}}
                            </a>
                        </li>
                    <# }); #>
                </ul>

                <div class="tab-content">
                    <# _.each(settings.tabs_list, function(tab, index) { #>
                        <div class="tab-pane <# if (index === 0) { #>active<# } #>" 
                             id="kintaelectric-tab-products-{{ index + 1 }}" 
                             role="tabpanel">
                            <div class="columns-4-1-4 row g-0">
                                <!-- Productos Izquierda -->
                                <div class="products-4 products-4-left col-md-3 col-xxl-4 d-xl-flex d-xxl-block column">
                                    <ul class="products exclude-auto-height list-unstyled flex-md-column flex-xxl-row mb-0">
                                        <li class="product">
                                            <div class="product-outer product-item__outer">
                                                <div class="product-inner product-item__inner">
                                                    <div class="product-loop-header product-item__header">
                                                        <span class="loop-product-categories">
                                                            <a href="#" rel="tag">Category</a>
                                                        </span>
                                                        <a href="#" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                                            <h2 class="woocommerce-loop-product__title">Product Title</h2>
                                                            <div class="product-thumbnail product-item__thumbnail">
                                                                <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="Product" width="300" height="300">
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="product-loop-body product-item__body">
                                                        <span class="loop-product-categories">
                                                            <a href="#" rel="tag">Category</a>
                                                        </span>
                                                        <a href="#" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                                            <h2 class="woocommerce-loop-product__title">Product Title</h2>
                                                            <div class="product-rating">
                                                                <div class="star-rating" title="Rated 4 out of 5">
                                                                    <span style="width:80%">Rated <strong class="rating">4</strong> out of 5</span>
                                                                </div> (3)
                                                            </div>
                                                            <div class="product-short-description">
                                                                <ul>
                                                                    <li>Feature 1</li>
                                                                    <li>Feature 2</li>
                                                                    <li>Feature 3</li>
                                                                </ul>
                                                            </div>
                                                            <div class="product-sku">SKU: 12345</div>
                                                        </a>
                                                    </div>
                                                    <div class="product-loop-footer product-item__footer">
                                                        <div class="price-add-to-cart">
                                                            <span class="price">
                                                                <span class="electro-price">$99.00</span>
                                                            </span>
                                                            <div class="add-to-cart-wrap" data-bs-toggle="tooltip" data-bs-title="Add to cart">
                                                                <a href="#" class="button product_type_simple add_to_cart_button ajax_add_to_cart">Add to cart</a>
                                                            </div>
                                                        </div>
                                                        <div class="hover-area">
                                                            <div class="action-buttons">
                                                                <div class="yith-wcwl-add-to-wishlist">
                                                                    <a href="#" class="add_to_wishlist">Wishlist</a>
                                                                </div>
                                                                <a href="#" class="compare link add-to-compare-link">Compare</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Producto Principal -->
                                <div class="products-1 col-md-6 col-xxl-4 column">
                                    <ul class="products list-unstyled product-main-2-1-2 show-btn">
                                        <li class="product">
                                            <div class="product-outer product-item__outer">
                                                <div class="product-inner product-item__inner">
                                                    <div class="flex-div">
                                                        <div class="product-loop-header product-item__header">
                                                            <span class="loop-product-categories">
                                                                <a href="#" rel="tag">Category</a>
                                                            </span>
                                                            <a href="#" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                                                <h2 class="woocommerce-loop-product__title">Featured Product</h2>
                                                            </a>
                                                        </div>
                                                        <div class="images">
                                                            <a href="#" class="woocommerce-main-image" title="">
                                                                <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="Featured Product" width="600" height="550">
                                                            </a>
                                                            <div class="thumbnails columns-3">
                                                                <a href="#" class="first" title="">
                                                                    <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="Thumb 1" width="100" height="100">
                                                                </a>
                                                                <a href="#" class="" title="">
                                                                    <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="Thumb 2" width="100" height="100">
                                                                </a>
                                                                <a href="#" class="last" title="">
                                                                    <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="Thumb 3" width="100" height="100">
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="product-loop-body product-item__body">
                                                            <span class="loop-product-categories">
                                                                <a href="#" rel="tag">Category</a>
                                                            </span>
                                                            <a href="#" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                                                <h2 class="woocommerce-loop-product__title">Featured Product</h2>
                                                                <div class="product-rating">
                                                                    <div class="star-rating" title="Rated 5 out of 5">
                                                                        <span style="width:100%">Rated <strong class="rating">5</strong> out of 5</span>
                                                                    </div> (1)
                                                                </div>
                                                                <div class="product-short-description">
                                                                    <ul>
                                                                        <li>Premium Feature 1</li>
                                                                        <li>Premium Feature 2</li>
                                                                        <li>Premium Feature 3</li>
                                                                    </ul>
                                                                </div>
                                                                <div class="product-sku">SKU: 67890</div>
                                                            </a>
                                                        </div>
                                                        <div class="product-loop-footer product-item__footer">
                                                            <div class="price-add-to-cart">
                                                                <span class="price">
                                                                    <span class="electro-price">$299.00</span>
                                                                </span>
                                                                <div class="add-to-cart-wrap" data-bs-toggle="tooltip" data-bs-title="Add to cart">
                                                                    <a href="#" class="button product_type_simple add_to_cart_button ajax_add_to_cart">Add to cart</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="hover-area">
                                                        <div class="action-buttons">
                                                            <div class="yith-wcwl-add-to-wishlist">
                                                                <a href="#" class="add_to_wishlist">Wishlist</a>
                                                            </div>
                                                            <a href="#" class="compare link add-to-compare-link">Compare</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Productos Derecha -->
                                <div class="products-4 products-4-right col-md-3 col-xxl-4 d-xl-flex d-xxl-block column">
                                    <ul class="products exclude-auto-height list-unstyled flex-md-column flex-xxl-row mb-0">
                                        <li class="product">
                                            <div class="product-outer product-item__outer">
                                                <div class="product-inner product-item__inner">
                                                    <div class="product-loop-header product-item__header">
                                                        <span class="loop-product-categories">
                                                            <a href="#" rel="tag">Category</a>
                                                        </span>
                                                        <a href="#" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                                            <h2 class="woocommerce-loop-product__title">Product Title</h2>
                                                            <div class="product-thumbnail product-item__thumbnail">
                                                                <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="Product" width="300" height="300">
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="product-loop-body product-item__body">
                                                        <span class="loop-product-categories">
                                                            <a href="#" rel="tag">Category</a>
                                                        </span>
                                                        <a href="#" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                                            <h2 class="woocommerce-loop-product__title">Product Title</h2>
                                                            <div class="product-rating">
                                                                <div class="star-rating" title="Rated 3 out of 5">
                                                                    <span style="width:60%">Rated <strong class="rating">3</strong> out of 5</span>
                                                                </div> (2)
                                                            </div>
                                                            <div class="product-short-description">
                                                                <ul>
                                                                    <li>Feature 1</li>
                                                                    <li>Feature 2</li>
                                                                    <li>Feature 3</li>
                                                                </ul>
                                                            </div>
                                                            <div class="product-sku">SKU: 54321</div>
                                                        </a>
                                                    </div>
                                                    <div class="product-loop-footer product-item__footer">
                                                        <div class="price-add-to-cart">
                                                            <span class="price">
                                                                <span class="electro-price">$149.00</span>
                                                            </span>
                                                            <div class="add-to-cart-wrap" data-bs-toggle="tooltip" data-bs-title="Add to cart">
                                                                <a href="#" class="button product_type_simple add_to_cart_button ajax_add_to_cart">Add to cart</a>
                                                            </div>
                                                        </div>
                                                        <div class="hover-area">
                                                            <div class="action-buttons">
                                                                <div class="yith-wcwl-add-to-wishlist">
                                                                    <a href="#" class="add_to_wishlist">Wishlist</a>
                                                                </div>
                                                                <a href="#" class="compare link add-to-compare-link">Compare</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <# }); #>
                </div>
            </div>
        </section>
        <?php
    }
}
