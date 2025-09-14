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
        return ['kinta-electric'];
    }

    public function get_keywords()
    {
        return ['products', 'carousel', 'woocommerce', 'dynamic', 'kinta'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'tabs_section',
            [
                'label' => esc_html__('Pestañas de Productos', 'kinta-electronic-elementor'),
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
            'tabs_list',
            [
                'label' => esc_html__('Lista de Pestañas', 'kinta-electronic-elementor'),
                'type' => Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'tab_name',
                        'label' => esc_html__('Nombre de Pestaña', 'kinta-electronic-elementor'),
                        'type' => Controls_Manager::TEXT,
                        'default' => 'Nueva Pestaña',
                        'placeholder' => 'Nombre de la pestaña',
                    ],
                    [
                        'name' => 'tab_categories',
                        'label' => esc_html__('Categorías', 'kinta-electronic-elementor'),
                        'type' => Controls_Manager::SELECT2,
                        'options' => $this->get_product_categories(),
                        'multiple' => true,
                        'label_block' => true,
                        'description' => 'Selecciona las categorías de productos para esta pestaña. Deja vacío para mostrar todos los productos.',
                    ],
                    [
                        'name' => 'products_per_tab',
                        'label' => esc_html__('Productos por Pestaña', 'kinta-electronic-elementor'),
                        'type' => Controls_Manager::NUMBER,
                        'default' => 8,
                        'min' => 1,
                        'max' => 20,
                    ],
                    [
                        'name' => 'order_by',
                        'label' => esc_html__('Ordenar por', 'kinta-electronic-elementor'),
                        'type' => Controls_Manager::SELECT,
                        'default' => 'date',
                        'options' => [
                            'date' => esc_html__('Fecha', 'kinta-electronic-elementor'),
                            'popularity' => esc_html__('Popularidad', 'kinta-electronic-elementor'),
                            'rating' => esc_html__('Valoración', 'kinta-electronic-elementor'),
                            'price' => esc_html__('Precio', 'kinta-electronic-elementor'),
                            'title' => esc_html__('Título', 'kinta-electronic-elementor'),
                        ],
                    ],
                ],
                'default' => [
                    [
                        'tab_name' => 'Top 20',
                        'tab_categories' => [],
                        'products_per_tab' => 8,
                        'order_by' => 'popularity',
                    ],
                ],
                'title_field' => '{{{ tab_name }}}',
                'description' => 'La primera pestaña será "Top 20" (más vendidos). Agrega más pestañas según necesites.',
            ]
        );

        $this->end_controls_section();
    }

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
     * Obtener categorías de productos para sliders (incluye opciones especiales)
     */
    protected function get_product_categories_for_sliders()
    {
        $categories = [
            'recent' => esc_html__('Recientes', 'kinta-electronic-elementor'),
            'featured' => esc_html__('Destacados', 'kinta-electronic-elementor'),
            'sale' => esc_html__('En Oferta', 'kinta-electronic-elementor'),
            'best_selling' => esc_html__('Más Vendidos', 'kinta-electronic-elementor'),
        ];
        
        if (function_exists('get_terms')) {
            $terms = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
            ]);
            
            if (!is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $categories['cat_' . $term->slug] = $term->name;
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

    protected function get_products_for_tab($tab_settings)
    {
        $args = [
            'limit' => $tab_settings['products_per_tab'] ?? 8,
            'status' => 'publish',
            'visibility' => 'visible',
        ];

        // Configurar categorías
        if (!empty($tab_settings['tab_categories']) && is_array($tab_settings['tab_categories'])) {
            $args['category'] = $tab_settings['tab_categories'];
        }

        // Configurar ordenamiento
        $order_by = $tab_settings['order_by'] ?? 'date';
        switch ($order_by) {
            case 'popularity':
                $args['orderby'] = 'popularity';
                break;
            case 'rating':
                $args['orderby'] = 'rating';
                break;
            case 'price':
                $args['orderby'] = 'price';
                break;
            case 'title':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'date':
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }

        if (function_exists('wc_get_products')) {
            $products = wc_get_products($args);
            return is_array($products) ? $products : [];
        }

        return [];
    }

    protected function get_category_name($category_slug)
    {
        if (empty($category_slug)) {
            return 'Categoría';
        }
        
        if (function_exists('get_term_by')) {
            $term = get_term_by('slug', $category_slug, 'product_cat');
            if ($term && !is_wp_error($term)) {
                return $term->name;
            }
        }
        
        return ucfirst(str_replace('-', ' ', $category_slug));
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        
        $tabs_list = $settings['tabs_list'] ?? [];
        if (empty($tabs_list)) {
            $tabs_list = [
                [
                    'tab_name' => 'Top 20',
                    'tab_categories' => [],
                    'products_per_tab' => 8,
                    'order_by' => 'popularity',
                ]
            ];
        }
        
        $product_groups = [];
        $tab_names = [];
        
        foreach ($tabs_list as $tab_index => $tab_settings) {
            $wc_products = $this->get_products_for_tab($tab_settings);
            
            if (empty($wc_products) || !is_array($wc_products)) {
                $product_groups[] = [];
                $tab_names[] = $tab_settings['tab_name'] ?? 'Pestaña ' . ($tab_index + 1);
                continue;
            }

            $products = [];
            foreach ($wc_products as $wc_product) {
                if (!is_object($wc_product) || !method_exists($wc_product, 'get_id')) {
                    continue;
                }
                $product_id = $wc_product->get_id();
                $product_name = $wc_product->get_name();
                $product_url = get_permalink($product_id);
                $product_sku = $wc_product->get_sku();
                
                $image_id = $wc_product->get_image_id();
                if ($image_id) {
                    $image_src = wp_get_attachment_image_src($image_id, 'woocommerce_thumbnail');
                    $image_url = $image_src ? $image_src[0] : wc_placeholder_img_src();
                    
                    $image_srcset = wp_get_attachment_image_srcset($image_id, 'woocommerce_thumbnail');
                    $srcset = $image_srcset ? $image_srcset : $image_url . ' 300w';
                } else {
                    $image_url = wc_placeholder_img_src();
                    $srcset = $image_url . ' 300w';
                }
                
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
                
                $add_to_cart_url = $wc_product->add_to_cart_url();
                
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
            
            $product_groups[] = $products;
            $tab_names[] = $tab_settings['tab_name'] ?? 'Pestaña ' . ($tab_index + 1);
        }
        
        if (empty($product_groups)) {
            echo '<p>No se encontraron productos.</p>';
            return;
        }

        ?>
        <style>
        .section-product-cards-carousel .products-carousel .owl-dots {
            display: block !important;
            text-align: center;
            margin-top: 20px;
        }
        .section-product-cards-carousel .products-carousel .owl-dots .owl-dot {
            width: 8px;
            height: 8px;
            background-color: #bcbcbc;
            display: inline-block;
            border-radius: 50%;
            padding: 0;
            border-width: 0;
            margin: 0 5px;
        }
        .section-product-cards-carousel .products-carousel .owl-dots .owl-dot.active {
            width: 30px;
            height: 8px;
            border-radius: 3px;
            background-color: var(--bs-ec-primary, #fed700);
        }
        .section-product-cards-carousel .products-carousel .owl-nav {
            display: block !important;
        }
        .section-product-cards-carousel .products-carousel .owl-nav .owl-prev,
        .section-product-cards-carousel .products-carousel .owl-nav .owl-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #333;
            text-decoration: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-product-cards-carousel .products-carousel .owl-nav .owl-prev {
            left: -20px;
        }
        .section-product-cards-carousel .products-carousel .owl-nav .owl-next {
            right: -20px;
        }
        .section-product-cards-carousel .products-carousel .owl-nav .owl-prev:hover,
        .section-product-cards-carousel .products-carousel .owl-nav .owl-next:hover {
            background: var(--bs-ec-primary, #fed700);
            color: #fff;
        }
        </style>
        <script>
        jQuery(document).ready(function($) {
            // Navegación del carousel
            $('.section-product-cards-carousel .nav-link[data-slider]').on('click', function(e) {
                e.preventDefault();
                var sliderIndex = parseInt($(this).data('slider'));
                
                // Actualizar navegación activa
                $('.section-product-cards-carousel .nav-item').removeClass('active');
                $('.section-product-cards-carousel .nav-link').removeClass('active');
                $(this).parent().addClass('active');
                $(this).addClass('active');
                
                // Ir al slide correspondiente
                var carousel = $('.section-product-cards-carousel .owl-carousel');
                if (carousel.length) {
                    carousel.trigger('to.owl.carousel', sliderIndex);
                }
            });
            
            // Actualizar navegación cuando cambia el carousel
            $('.section-product-cards-carousel .owl-carousel').on('changed.owl.carousel', function(event) {
                var currentIndex = event.item.index;
                $('.section-product-cards-carousel .nav-item').removeClass('active');
                $('.section-product-cards-carousel .nav-link').removeClass('active');
                $('.section-product-cards-carousel .nav-item').eq(currentIndex).addClass('active');
                $('.section-product-cards-carousel .nav-link').eq(currentIndex).addClass('active');
            });
        });
        </script>
        <section class="section-product-cards-carousel home-v1-product-cards-carousel animate-in-view" data-animation="fadeIn">
            <header class="show-nav">
                <h2 class="h1"><?php echo esc_html($settings['section_title']); ?></h2>
                <ul class="nav nav-inline">
                    <?php foreach ($tab_names as $index => $name): ?>
                    <li class="nav-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" href="#" data-slider="<?php echo $index; ?>">
                            <?php echo esc_html($name); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </header>
            
            <div id="<?php echo esc_attr(uniqid()); ?>" data-ride="owl-carousel" data-carousel-selector=".products-carousel" data-carousel-options='{"items":1,"nav":true,"slideSpeed":300,"dots":true,"rtl":false,"paginationSpeed":400,"navText":["<i class=\"ec ec-chevron-left\"></i>","<i class=\"ec ec-chevron-right\"></i>"],"margin":0,"touchDrag":true,"autoplay":false}'>
                <div class="woocommerce columns-3 products-carousel owl-carousel">
                    <?php foreach ($product_groups as $group_index => $group_products): ?>
                    <ul data-view="grid" data-bs-toggle="regular-products" class="products products list-unstyled row g-0 row-cols-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-4" data-slider="<?php echo $group_index; ?>">
                        <?php if (!empty($group_products)): ?>
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
                        <?php else: ?>
                            <li class="col-12">
                                <p class="text-center">No se encontraron productos para esta categoría.</p>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}