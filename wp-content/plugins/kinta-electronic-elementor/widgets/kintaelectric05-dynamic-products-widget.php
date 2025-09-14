<?php
/**
 * Kinta Electric 05 - Dynamic Products Widget
 * 
 * Widget dinámico que replica exactamente la estructura HTML del carousel original
 * con 24 productos divididos en 3 grupos de 8 productos cada uno.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class KEE_Kintaelectric05_Dynamic_Products_Widget extends KEE_Base_Widget
{
    public function get_name()
    {
        return 'kintaelectric05-dynamic-products';
    }

    public function get_title()
    {
        return esc_html__('Kinta Electric 05 - Dynamic Products', 'kinta-electronic-elementor');
    }

    public function get_icon()
    {
        return 'eicon-products';
    }

    public function get_categories()
    {
        return ['kinta-electronic'];
    }

    public function get_keywords()
    {
        return ['products', 'carousel', 'woocommerce', 'dynamic', 'kinta'];
    }

    protected function register_controls()
    {
        // Sección de configuración general
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Configuración General', 'kinta-electronic-elementor'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'section_title',
            [
                'label' => esc_html__('Título de la Sección', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Best Sellers',
                'placeholder' => 'Best Sellers',
            ]
        );

        $this->add_control(
            'nav_item_1',
            [
                'label' => esc_html__('Nav Item 1', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Top 20',
                'placeholder' => 'Top 20',
            ]
        );

        $this->add_control(
            'nav_item_2',
            [
                'label' => esc_html__('Nav Item 2', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Smart Phones & Tablets',
                'placeholder' => 'Smart Phones & Tablets',
            ]
        );

        $this->add_control(
            'nav_item_2_url',
            [
                'label' => esc_html__('Nav Item 2 URL', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::URL,
                'placeholder' => 'https://example.com',
            ]
        );

        $this->add_control(
            'nav_item_3',
            [
                'label' => esc_html__('Nav Item 3', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Laptops & Computers',
                'placeholder' => 'Laptops & Computers',
            ]
        );

        $this->add_control(
            'nav_item_3_url',
            [
                'label' => esc_html__('Nav Item 3 URL', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::URL,
                'placeholder' => 'https://example.com',
            ]
        );

        $this->add_control(
            'nav_item_4',
            [
                'label' => esc_html__('Nav Item 4', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Video Cameras',
                'placeholder' => 'Video Cameras',
            ]
        );

        $this->add_control(
            'nav_item_4_url',
            [
                'label' => esc_html__('Nav Item 4 URL', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::URL,
                'placeholder' => 'https://example.com',
            ]
        );

        $this->end_controls_section();

        // Sección de productos
        $this->start_controls_section(
            'products_section',
            [
                'label' => esc_html__('Configuración de Productos', 'kinta-electronic-elementor'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'product_source',
            [
                'label' => esc_html__('Fuente de Productos', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::SELECT,
                'default' => 'recent',
                'options' => [
                    'recent' => esc_html__('Recientes', 'kinta-electronic-elementor'),
                    'featured' => esc_html__('Destacados', 'kinta-electronic-elementor'),
                    'sale' => esc_html__('En Oferta', 'kinta-electronic-elementor'),
                    'best_selling' => esc_html__('Más Vendidos', 'kinta-electronic-elementor'),
                    'category' => esc_html__('Por Categoría', 'kinta-electronic-elementor'),
                ],
            ]
        );

        $this->add_control(
            'product_category',
            [
                'label' => esc_html__('Categoría de Productos', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_product_categories(),
                'condition' => [
                    'product_source' => 'category',
                ],
            ]
        );

        $this->add_control(
            'products_per_page',
            [
                'label' => esc_html__('Número de Productos', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::NUMBER,
                'default' => 24,
                'min' => 1,
                'max' => 100,
                'description' => 'Se mostrarán 24 productos en 3 grupos de 8',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Obtener categorías de productos
     */
    protected function get_product_categories()
    {
        $categories = [];
        
        if (function_exists('get_terms')) {
            $terms = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
            ]);
            
            if (!is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $categories[$term->slug] = $term->name;
                }
            }
        }
        
        return $categories;
    }

    /**
     * Obtener productos según la configuración
     */
    protected function get_products($settings)
    {
        $args = [
            'limit' => $settings['products_per_page'],
            'status' => 'publish',
            'visibility' => 'visible',
        ];

        switch ($settings['product_source']) {
            case 'featured':
                $args['featured'] = true;
                break;
            case 'sale':
                $args['on_sale'] = true;
                break;
            case 'best_selling':
                $args['orderby'] = 'popularity';
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

        if (function_exists('wc_get_products')) {
            return wc_get_products($args);
        }

        return [];
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $wc_products = $this->get_products($settings);
        
        if (empty($wc_products)) {
            echo '<p>No se encontraron productos.</p>';
            return;
        }

        // Convertir productos de WooCommerce al formato necesario
        $products = [];
        foreach ($wc_products as $wc_product) {
            $product_id = $wc_product->get_id();
            $product_name = $wc_product->get_name();
            $product_url = get_permalink($product_id);
            $product_sku = $wc_product->get_sku();
            
            // Imagen del producto
            $image_id = $wc_product->get_image_id();
            if ($image_id) {
                $image_src = wp_get_attachment_image_src($image_id, 'woocommerce_thumbnail');
                $image_url = $image_src ? $image_src[0] : wc_placeholder_img_src();
                
                // Generar srcset
                $image_srcset = wp_get_attachment_image_srcset($image_id, 'woocommerce_thumbnail');
                $srcset = $image_srcset ? $image_srcset : $image_url . ' 300w';
            } else {
                $image_url = wc_placeholder_img_src();
                $srcset = $image_url . ' 300w';
            }
            
            // Categorías del producto
            $product_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'all']);
            $category_links = [];
            $product_classes = ['product_cat-' . sanitize_title($product_name)];
            
            if (!empty($product_categories) && !is_wp_error($product_categories)) {
                foreach ($product_categories as $category) {
                    $term_link = get_term_link($category);
                    if (!is_wp_error($term_link)) {
                        $category_links[] = '<a href="' . esc_url($term_link) . '" rel="tag">' . esc_html($category->name) . '</a>';
                        $product_classes[] = 'product_cat-' . sanitize_title($category->name);
                    }
                }
            }
            
            // Clases adicionales
            $product_classes[] = 'product-card';
            $product_classes[] = 'post-' . $product_id;
            $product_classes[] = 'type-product';
            $product_classes[] = 'status-publish';
            $product_classes[] = 'has-post-thumbnail';
            
            if ($wc_product->is_in_stock()) {
                $product_classes[] = 'instock';
            } else {
                $product_classes[] = 'outofstock';
            }
            
            if ($wc_product->is_on_sale()) {
                $product_classes[] = 'sale';
            }
            
            if ($wc_product->is_featured()) {
                $product_classes[] = 'featured';
            }
            
            $product_classes[] = 'shipping-taxable';
            $product_classes[] = 'purchasable';
            $product_classes[] = 'product-type-' . $wc_product->get_type();
            
            // URL de añadir al carrito
            $add_to_cart_url = $wc_product->add_to_cart_url();
            
            // URL de comparar
            $compare_url = '';
            if (function_exists('yith_woocompare_add_product_url')) {
                $compare_url = yith_woocompare_add_product_url($product_id);
            }
            
            $products[] = [
                'id' => $product_id,
                'name' => $product_name,
                'url' => $product_url,
                'image' => $image_url,
                'srcset' => $srcset,
                'categories' => $category_links,
                'classes' => $product_classes,
                'sku' => $product_sku,
                'price_html' => $wc_product->get_price_html(),
                'add_to_cart_url' => $add_to_cart_url,
                'compare_url' => $compare_url,
            ];
        }

        // Dividir productos en grupos de 8: 8, 8, 8 (24 productos total)
        $product_groups = [];
        $total_products = count($products);
        
        if ($total_products > 0) {
            // Primer grupo: 8 productos
            $product_groups[] = array_slice($products, 0, 8);
            
            if ($total_products > 8) {
                // Segundo grupo: 8 productos
                $product_groups[] = array_slice($products, 8, 8);
            }
            
            if ($total_products > 16) {
                // Tercer grupo: 8 productos
                $product_groups[] = array_slice($products, 16, 8);
            }
        }
        
        ?>
        <section class="section-product-cards-carousel home-v1-product-cards-carousel animate-in-view" data-animation="fadeIn">
            <header class="show-nav">
                <h2 class="h1"><?php echo esc_html($settings['section_title']); ?></h2>
                <ul class="nav nav-inline">
                    <li class="nav-item active">
                        <span class="nav-link"><?php echo esc_html($settings['nav_item_1']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo esc_url($settings['nav_item_2_url']['url']); ?>"><?php echo esc_html($settings['nav_item_2']); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo esc_url($settings['nav_item_3_url']['url']); ?>"><?php echo esc_html($settings['nav_item_3']); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo esc_url($settings['nav_item_4_url']['url']); ?>"><?php echo esc_html($settings['nav_item_4']); ?></a>
                    </li>
                </ul>
            </header>

            <div id="<?php echo esc_attr(uniqid()); ?>" data-ride="owl-carousel" data-carousel-selector=".product-cards-carousel" data-carousel-options='{"items":1,"nav":false,"slideSpeed":300,"dots":true,"rtl":false,"paginationSpeed":400,"navText":["",""],"margin":0,"touchDrag":true,"autoplay":false}'>
                <div class="woocommerce columns-3 product-cards-carousel owl-carousel">
                    <?php foreach ($product_groups as $group_index => $group_products): ?>
                    <ul data-view="grid" data-bs-toggle="regular-products" class="products products list-unstyled row g-0 row-cols-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-4">
                        <?php foreach ($group_products as $product_index => $product): ?>
                        <li class="product-card post-<?php echo $product['id']; ?> product type-product status-publish has-post-thumbnail <?php echo implode(' ', $product['classes']); ?>">
                            <div class="product-outer product-item__outer">
                                <div class="product-inner">
                                    <a class="card-media-left" href="<?php echo esc_url($product['url']); ?>" title="<?php echo esc_attr($product['name']); ?>">
                                        <img loading="lazy" width="300" height="300" src="<?php echo esc_url($product['image']); ?>" class="media-object attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="<?php echo esc_attr($product['name']); ?>" decoding="async" srcset="<?php echo esc_attr($product['srcset']); ?>" sizes="(max-width: 300px) 100vw, 300px">
                                    </a>

                                    <div class="card-body">
                                        <div class="card-body-inner">
                                            <span class="loop-product-categories">
                                                <?php if (!empty($product['categories'])): ?>
                                                    <?php echo implode(', ', $product['categories']); ?>
                                                <?php endif; ?>
                                            </span>
                                            <a href="<?php echo esc_url($product['url']); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                                <h2 class="woocommerce-loop-product__title"><?php echo esc_html($product['name']); ?></h2>
                                            </a>
                                            <div class="price-add-to-cart">
                                                <span class="price"><?php echo $product['price_html']; ?></span>
                                                <div class="add-to-cart-wrap" data-bs-toggle="tooltip" data-bs-title="Add to cart">
                                                    <a href="<?php echo esc_url($product['add_to_cart_url']); ?>" aria-describedby="woocommerce_loop_add_to_cart_link_describedby_<?php echo $product['id']; ?>" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="<?php echo $product['id']; ?>" data-product_sku="<?php echo esc_attr($product['sku']); ?>" aria-label="Add to cart: &ldquo;<?php echo esc_attr($product['name']); ?>&rdquo;" rel="nofollow" data-success_message="&ldquo;<?php echo esc_attr($product['name']); ?>&rdquo; has been added to your cart">Add to cart</a>
                                                </div>
                                                <span id="woocommerce_loop_add_to_cart_link_describedby_<?php echo $product['id']; ?>" class="screen-reader-text"></span>
                                            </div><!-- /.price-add-to-cart -->
                                        </div>
                                    </div>
                                    <div class="hover-area">
                                        <div class="action-buttons">
                                            <?php 
                                            // YITH Wishlist - Múltiples métodos de integración
                                            if (defined('YITH_WCWL')) : 
                                                // Método 1: Función directa si está disponible
                                                if (function_exists('yith_wcwl_add_to_wishlist_button')) {
                                                    yith_wcwl_add_to_wishlist_button($product['id']);
                                                }
                                                // Método 2: Shortcode como fallback
                                                elseif (shortcode_exists('yith_wcwl_add_to_wishlist')) {
                                                    echo do_shortcode('[yith_wcwl_add_to_wishlist product_id="' . $product['id'] . '"]');
                                                }
                                                // Método 3: HTML manual con nonce correcto
                                                else {
                                                    $wishlist_nonce = wp_create_nonce('add_to_wishlist');
                                                    echo '<a href="#" class="add_to_wishlist" data-product-id="' . $product['id'] . '" data-nonce="' . $wishlist_nonce . '">' . __('Add to wishlist', 'kinta-electric-elementor') . '</a>';
                                                }
                                            endif; 
                                            ?>

                                            <?php 
                                            // YITH Compare - Múltiples métodos de integración
                                            if (defined('YITH_WOOCOMPARE')) : 
                                                // Método 1: Función directa si está disponible
                                                if (function_exists('yith_woocompare_add_compare_button')) {
                                                    yith_woocompare_add_compare_button($product['id']);
                                                }
                                                // Método 2: Shortcode como fallback
                                                elseif (shortcode_exists('yith_compare_button')) {
                                                    echo do_shortcode('[yith_compare_button product="' . $product['id'] . '"]');
                                                }
                                                // Método 3: HTML manual como último recurso
                                                else {
                                                    echo '<a href="' . esc_url($product['compare_url']) . '" class="compare link add-to-compare-link" data-product_id="' . $product['id'] . '" target="_self" rel="nofollow"><span class="label">Compare</span></a>';
                                                }
                                            endif; 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /.product-outer -->
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}