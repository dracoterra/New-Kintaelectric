<?php
/**
 * Widget Kintaelectric07 Recently Added para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Kintaelectric07 Recently Added
 */
class KEE_Kintaelectric07_Recently_Added_Widget extends KEE_Base_Widget {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'kintaelectric07_recently_added';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('Kintaelectric07 Recently Added', 'kinta-electric-elementor');
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
        return ['recently', 'added', 'products', 'carousel', 'kintaelectric', 'woocommerce'];
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
        // Sección de Configuración
        $this->start_controls_section(
            'section_config',
            [
                'label' => esc_html__('Configuración', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Control para título de la sección
        $this->add_control(
            'section_title',
            [
                'label' => esc_html__('Título de la Sección', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Recently Added',
                'placeholder' => 'Recently Added',
            ]
        );

        // Control para número de productos
        $this->add_control(
            'products_per_page',
            [
                'label' => esc_html__('Número de Productos', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 50,
            ]
        );

        // Control para mostrar navegación
        $this->add_control(
            'show_navigation',
            [
                'label' => esc_html__('Mostrar Navegación', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electric-elementor'),
                'label_off' => esc_html__('No', 'kinta-electric-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Control para mostrar dots
        $this->add_control(
            'show_dots',
            [
                'label' => esc_html__('Mostrar Dots', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electric-elementor'),
                'label_off' => esc_html__('No', 'kinta-electric-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Control para autoplay
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

        $this->end_controls_section();

        // Sección de Configuración del Carrusel
        $this->start_controls_section(
            'section_carousel_config',
            [
                'label' => esc_html__('Configuración del Carrusel', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Control para items en desktop
        $this->add_control(
            'items_desktop',
            [
                'label' => esc_html__('Items en Desktop', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 10,
            ]
        );

        // Control para items en tablet
        $this->add_control(
            'items_tablet',
            [
                'label' => esc_html__('Items en Tablet', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
                'max' => 8,
            ]
        );

        // Control para items en mobile
        $this->add_control(
            'items_mobile',
            [
                'label' => esc_html__('Items en Mobile', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 2,
                'min' => 1,
                'max' => 4,
            ]
        );

        // Control para margen entre items
        $this->add_control(
            'margin_between_items',
            [
                'label' => esc_html__('Margen entre Items (px)', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'max' => 50,
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Obtener productos recientes
     */
    protected function get_recent_products($limit = 12) {
        if (!$this->is_woocommerce_active()) {
            return [];
        }

        $args = [
            'limit' => $limit,
            'status' => 'publish',
            'visibility' => 'visible',
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if (function_exists('wc_get_products')) {
            $products = wc_get_products($args);
            return is_array($products) ? $products : [];
        }

        return [];
    }

    /**
     * Renderizar widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $products = $this->get_recent_products($settings['products_per_page']);
        
        if (empty($products)) {
            echo '<p>' . esc_html__('No se encontraron productos recientes.', 'kinta-electric-elementor') . '</p>';
            return;
        }

        $carousel_id = 'products-carousel-' . uniqid();
        $show_nav = $settings['show_navigation'] === 'yes' ? 'true' : 'false';
        $show_dots = $settings['show_dots'] === 'yes' ? 'true' : 'false';
        $autoplay = $settings['autoplay'] === 'yes' ? 'true' : 'false';
        
        $carousel_options = [
            'items' => $settings['items_desktop'],
            //'nav' => $show_nav,
            'slideSpeed' => 300,
            'dots' => $show_dots,
            'rtl' => is_rtl(),
            'paginationSpeed' => 400,
            //'navText' => ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
            'margin' => $settings['margin_between_items'],
            'touchDrag' => true,
            'autoplay' => $autoplay,
            'responsive' => [
                '0' => ['items' => $settings['items_mobile']],
                '480' => ['items' => $settings['items_mobile']],
                '768' => ['items' => $settings['items_tablet']],
                '992' => ['items' => $settings['items_tablet']],
                '1200' => ['items' => $settings['items_desktop']],
                '1480' => ['items' => $settings['items_desktop']],
            ],
        ];

        ?>
        <section class="home-v1-recently-viewed-products-carousel section-products-carousel animate-in-view animation" data-animation="fadeIn">
            <header>
                <h2 class="h1"><?php echo esc_html($settings['section_title']); ?></h2>
                
                <?php if ($settings['show_navigation'] === 'yes'): ?>
                <div class="owl-nav">
                    <a href="#products-carousel-prev" data-target="#<?php echo esc_attr($carousel_id); ?>" class="slider-prev">
                        <i class="fa fa-angle-left"></i>
                    </a>
                    <a href="#products-carousel-next" data-target="#<?php echo esc_attr($carousel_id); ?>" class="slider-next">
                        <i class="fa fa-angle-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </header>

            <div id="<?php echo esc_attr($carousel_id); ?>" 
                 data-ride="owl-carousel" 
                 data-replace-active-class="true" 
                 data-carousel-selector=".products-carousel"
                 data-carousel-options='<?php echo esc_attr(json_encode($carousel_options)); ?>'>
                <div class="woocommerce columns-<?php echo esc_attr($settings['items_desktop']); ?>">
                    <div data-view="grid" 
                         data-bs-toggle="regular-products"
                         class="products owl-carousel products-carousel products list-unstyled row g-0 row-cols-2 row-cols-md-3 row-cols-lg-<?php echo esc_attr($settings['items_tablet']); ?> row-cols-xl-<?php echo esc_attr($settings['items_desktop']); ?> row-cols-xxl-<?php echo esc_attr($settings['items_desktop']); ?>">
                        
                        <?php foreach ($products as $index => $product): ?>
                            <?php
                            $product_id = $product->get_id();
                            $product_name = $product->get_name();
                            $product_url = get_permalink($product_id);
                            $product_sku = $product->get_sku();
                            
                            // Obtener imagen del producto
                            $image_id = $product->get_image_id();
                            if ($image_id) {
                                $image_src = wp_get_attachment_image_src($image_id, 'woocommerce_thumbnail');
                                $image_url = $image_src ? $image_src[0] : wc_placeholder_img_src();
                            } else {
                                $image_url = wc_placeholder_img_src();
                            }
                            
                            // Obtener categorías del producto
                            $product_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'all']);
                            $category_links = [];
                            
                            if (!empty($product_categories) && !is_wp_error($product_categories)) {
                                foreach ($product_categories as $category) {
                                    $term_link = get_term_link($category);
                                    if (!is_wp_error($term_link)) {
                                        $category_links[] = '<a href="' . esc_url($term_link) . '" rel="tag">' . esc_html($category->name) . '</a>';
                                    }
                                }
                            }
                            
                            // Clases del producto
                            $product_classes = [
                                'product',
                                'type-product',
                                'post-' . $product_id,
                                'status-publish',
                            ];
                            
                            if ($index === 0) {
                                $product_classes[] = 'first';
                            }
                            
                            if ($index === count($products) - 1) {
                                $product_classes[] = 'last';
                            }
                            
                            if ($product->is_in_stock()) {
                                $product_classes[] = 'instock';
                            } else {
                                $product_classes[] = 'outofstock';
                            }
                            
                            if ($product->is_on_sale()) {
                                $product_classes[] = 'sale';
                            }
                            
                            if ($product->is_featured()) {
                                $product_classes[] = 'featured';
                            }
                            
                            $product_classes[] = 'has-post-thumbnail';
                            $product_classes[] = 'shipping-taxable';
                            $product_classes[] = 'purchasable';
                            $product_classes[] = 'product-type-' . $product->get_type();
                            
                            // Obtener rating
                            $rating = $product->get_average_rating();
                            $rating_count = $product->get_rating_count();
                            
                            // Obtener descripción corta
                            $short_description = $product->get_short_description();
                            
                            // URLs para acciones
                            $add_to_cart_url = $product->add_to_cart_url();
                            $compare_url = '';
                            if (function_exists('yith_woocompare_add_product_url')) {
                                $compare_url = yith_woocompare_add_product_url($product_id);
                            }
                            ?>
                            
                            <div class="<?php echo esc_attr(implode(' ', $product_classes)); ?>">
                                <div class="product-outer product-item__outer">
                                    <div class="product-inner product-item__inner">
                                        <div class="product-loop-header product-item__header">
                                            <?php if (!empty($category_links)): ?>
                                            <span class="loop-product-categories">
                                                <?php echo implode(', ', $category_links); ?>
                                            </span>
                                            <?php endif; ?>
                                            
                                            <a href="<?php echo esc_url($product_url); ?>" 
                                               class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                                <h2 class="woocommerce-loop-product__title">
                                                    <?php echo esc_html($product_name); ?>
                                                </h2>
                                                <div class="product-thumbnail product-item__thumbnail">
                                                    <img loading="lazy" 
                                                         width="300" 
                                                         height="300"
                                                         src="<?php echo esc_url($image_url); ?>"
                                                         class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail"
                                                         alt="<?php echo esc_attr($product_name); ?>">
                                                </div>
                                            </a>
                                        </div><!-- /.product-loop-header -->
                                        
                                        <div class="product-loop-body product-item__body">
                                            <?php if (!empty($category_links)): ?>
                                            <span class="loop-product-categories">
                                                <?php echo implode(', ', $category_links); ?>
                                            </span>
                                            <?php endif; ?>
                                            
                                            <a href="<?php echo esc_url($product_url); ?>" 
                                               class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                                <h2 class="woocommerce-loop-product__title">
                                                    <?php echo esc_html($product_name); ?>
                                                </h2>
                                                
                                                <?php if ($rating > 0): ?>
                                                <div class="product-rating">
                                                    <div class="star-rating" 
                                                         role="img" 
                                                         aria-label="Rated <?php echo esc_attr($rating); ?> out of 5">
                                                        <span style="width:<?php echo esc_attr(($rating / 5) * 100); ?>%">
                                                            Rated <strong class="rating"><?php echo esc_html($rating); ?></strong> out of 5
                                                        </span>
                                                    </div> (<?php echo esc_html($rating_count); ?>)
                                                </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($short_description)): ?>
                                                <div class="product-short-description">
                                                    <div>
                                                        <?php echo wp_kses_post($short_description); ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <div class="product-sku">SKU: <?php echo esc_html($product_sku); ?></div>
                                            </a>
                                        </div><!-- /.product-loop-body -->
                                        
                                        <div class="product-loop-footer product-item__footer">
                                            <div class="price-add-to-cart">
                                                <span class="price">
                                                    <span class="electro-price">
                                                        <?php echo $product->get_price_html(); ?>
                                                    </span>
                                                </span>
                                                <div class="add-to-cart-wrap" 
                                                    <?php if ($product->is_in_stock()): ?>
                                                        <a href="<?php echo esc_url($add_to_cart_url); ?>"
                                                           aria-describedby="woocommerce_loop_add_to_cart_link_describedby_<?php echo $product_id; ?>"
                                                           data-quantity="1"
                                                           class="button product_type_simple add_to_cart_button ajax_add_to_cart"
                                                           data-product_id="<?php echo $product_id; ?>"
                                                           data-product_sku="<?php echo esc_attr($product_sku); ?>"
                                                           aria-label="Add to cart: &ldquo;<?php echo esc_attr($product_name); ?>&rdquo;"
                                                           rel="nofollow"
                                                           data-success_message="&ldquo;<?php echo esc_attr($product_name); ?>&rdquo; has been added to your cart">
                                                            Add to cart
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo esc_url($product_url); ?>"
                                                           aria-describedby="woocommerce_loop_add_to_cart_link_describedby_<?php echo $product_id; ?>"
                                                           data-quantity="1"
                                                           class="button product_type_simple"
                                                           data-product_id="<?php echo $product_id; ?>"
                                                           data-product_sku="<?php echo esc_attr($product_sku); ?>"
                                                           aria-label="Read more about &ldquo;<?php echo esc_attr($product_name); ?>&rdquo;"
                                                           rel="nofollow"
                                                           data-success_message="">
                                                            Read more
                                                        </a>
                                                    <?php endif; ?>
                                                    <span id="woocommerce_loop_add_to_cart_link_describedby_<?php echo $product_id; ?>" 
                                                          class="screen-reader-text"></span>
                                                </div>
                                            </div><!-- /.price-add-to-cart -->
                                            
                                            <div class="hover-area">
                                                <div class="action-buttons">
                                                    <?php 
                                                    // YITH Wishlist
                                                    if (defined('YITH_WCWL')): 
                                                        if (function_exists('yith_wcwl_add_to_wishlist_button')) {
                                                            yith_wcwl_add_to_wishlist_button($product_id);
                                                        } elseif (shortcode_exists('yith_wcwl_add_to_wishlist')) {
                                                            echo do_shortcode('[yith_wcwl_add_to_wishlist product_id="' . $product_id . '"]');
                                                        } else {
                                                            $wishlist_nonce = wp_create_nonce('add_to_wishlist');
                                                            echo '<a href="#" class="add_to_wishlist" data-product-id="' . $product_id . '" data-nonce="' . $wishlist_nonce . '">' . __('Add to wishlist', 'kinta-electric-elementor') . '</a>';
                                                        }
                                                    endif; 
                                                    ?>
                                                    
                                                    <?php 
                                                    // YITH Compare
                                                    if (defined('YITH_WOOCOMPARE')): 
                                                        if (function_exists('yith_woocompare_add_compare_button')) {
                                                            yith_woocompare_add_compare_button($product_id);
                                                        } elseif (shortcode_exists('yith_compare_button')) {
                                                            echo do_shortcode('[yith_compare_button product="' . $product_id . '"]');
                                                        } else {
                                                            echo '<a href="' . esc_url($compare_url) . '" class="compare link add-to-compare-link" data-product_id="' . $product_id . '" target="_self" rel="nofollow"><span class="label">Compare</span></a>';
                                                        }
                                                    endif; 
                                                    ?>
                                                </div>
                                            </div>
                                        </div><!-- /.product-loop-footer -->
                                    </div><!-- /.product-inner -->
                                </div><!-- /.product-outer -->
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * Renderizar widget en modo de edición
     */
    protected function content_template() {
        ?>
        <#
        var carouselId = 'products-carousel-' + Math.random().toString(36).substr(2, 9);
        var showNav = settings.show_navigation === 'yes';
        var showDots = settings.show_dots === 'yes';
        var autoplay = settings.autoplay === 'yes';
        
        var carouselOptions = {
            items: settings.items_desktop,
            nav: showNav,
            slideSpeed: 300,
            dots: showDots,
            rtl: false,
            paginationSpeed: 400,
            navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
            margin: settings.margin_between_items,
            touchDrag: true,
            autoplay: autoplay,
            responsive: {
                '0': { items: settings.items_mobile },
                '480': { items: settings.items_mobile },
                '768': { items: settings.items_tablet },
                '992': { items: settings.items_tablet },
                '1200': { items: settings.items_desktop },
                '1480': { items: settings.items_desktop }
            }
        };
        #>
        
        <section class="home-v1-recently-viewed-products-carousel section-products-carousel animate-in-view animation" data-animation="fadeIn">
            <header>
                <h2 class="h1">{{{ settings.section_title }}}</h2>
                
                <# if (showNav) { #>
                <div class="owl-nav">
                    <a href="#products-carousel-prev" data-target="#{{{ carouselId }}}" class="slider-prev">
                        <i class="fa fa-angle-left"></i>
                    </a>
                    <a href="#products-carousel-next" data-target="#{{{ carouselId }}}" class="slider-next">
                        <i class="fa fa-angle-right"></i>
                    </a>
                </div>
                <# } #>
            </header>

            <div id="{{{ carouselId }}}" 
                 data-ride="owl-carousel" 
                 data-replace-active-class="true" 
                 data-carousel-selector=".products-carousel"
                 data-carousel-options="{{{ JSON.stringify(carouselOptions) }}}">
                <div class="woocommerce columns-{{{ settings.items_desktop }}}">
                    <div data-view="grid" 
                         data-bs-toggle="regular-products"
                         class="products owl-carousel products-carousel products list-unstyled row g-0 row-cols-2 row-cols-md-3 row-cols-lg-{{{ settings.items_tablet }}} row-cols-xl-{{{ settings.items_desktop }}} row-cols-xxl-{{{ settings.items_desktop }}}">
                        <div class="product-placeholder">
                            <p><?php echo esc_html__('Los productos se cargarán dinámicamente', 'kinta-electric-elementor'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}
