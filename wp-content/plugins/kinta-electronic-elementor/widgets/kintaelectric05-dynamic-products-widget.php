<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class KEE_Kintaelectric05_Dynamic_Products_Widget extends KEE_Base_Widget
{
    public function get_name()
    {
        return 'kintaelectric05-dynamic-products';
    }

    public function get_title()
    {
        return esc_html__('Kinta Electric 05 - Dynamic Products Carousel', 'kinta-electronic-elementor');
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
        return ['products', 'carousel', 'woocommerce', 'kinta', 'slider', 'best sellers'];
    }

    protected function register_controls()
    {
        // Sección de configuración general
        $this->start_controls_section(
            'section_general',
            [
                'label' => esc_html__('Configuración General', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'section_title',
            [
                'label' => esc_html__('Título de la Sección', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Best Sellers', 'kinta-electronic-elementor'),
                'placeholder' => esc_html__('Ingresa el título', 'kinta-electronic-elementor'),
            ]
        );

        $this->add_control(
            'products_per_page',
            [
                'label' => esc_html__('Número de Productos', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => -1, // -1 = mostrar todos los productos
                'min' => -1,
                'max' => 100,
                'description' => esc_html__('Usar -1 para mostrar todos los productos disponibles', 'kinta-electronic-elementor'),
            ]
        );

        $this->add_control(
            'product_source',
            [
                'label' => esc_html__('Fuente de Productos', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'featured',
                'options' => [
                    'featured' => esc_html__('Productos Destacados', 'kinta-electronic-elementor'),
                    'sale' => esc_html__('Productos en Oferta', 'kinta-electronic-elementor'),
                    'recent' => esc_html__('Productos Recientes', 'kinta-electronic-elementor'),
                    'best_selling' => esc_html__('Más Vendidos', 'kinta-electronic-elementor'),
                    'category' => esc_html__('Por Categoría', 'kinta-electronic-elementor'),
                ],
            ]
        );

        $this->add_control(
            'product_category',
            [
                'label' => esc_html__('Categoría de Productos', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_product_categories(),
                'condition' => [
                    'product_source' => 'category',
                ],
            ]
        );

        $this->end_controls_section();

        // Sección de configuración del carrusel
        $this->start_controls_section(
            'section_carousel',
            [
                'label' => esc_html__('Configuración del Carrusel', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'items_desktop',
            [
                'label' => esc_html__('Items en Desktop', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 12,
            ]
        );

        $this->add_control(
            'items_tablet',
            [
                'label' => esc_html__('Items en Tablet', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3,
                'min' => 1,
                'max' => 4,
            ]
        );

        $this->add_control(
            'items_mobile',
            [
                'label' => esc_html__('Items en Móvil', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'max' => 2,
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => esc_html__('Autoplay', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electronic-elementor'),
                'label_off' => esc_html__('No', 'kinta-electronic-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'autoplay_timeout',
            [
                'label' => esc_html__('Tiempo de Autoplay (ms)', 'kinta-electronic-elementor'),
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
                'label' => esc_html__('Mostrar Dots', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electronic-elementor'),
                'label_off' => esc_html__('No', 'kinta-electronic-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_nav',
            [
                'label' => esc_html__('Mostrar Navegación', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electronic-elementor'),
                'label_off' => esc_html__('No', 'kinta-electronic-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->end_controls_section();

        // Sección de navegación por pestañas
        $this->start_controls_section(
            'section_tabs',
            [
                'label' => esc_html__('Navegación por Pestañas', 'kinta-electronic-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_tabs',
            [
                'label' => esc_html__('Mostrar Pestañas', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Sí', 'kinta-electronic-elementor'),
                'label_off' => esc_html__('No', 'kinta-electronic-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'tab_categories',
            [
                'label' => esc_html__('Categorías para Pestañas', 'kinta-electronic-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_product_categories(),
                'condition' => [
                    'show_tabs' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function get_widget_script_depends()
    {
        return ['owl-carousel'];
    }

    protected function get_widget_style_depends()
    {
        return []; // La clase base ya incluye electro-style
    }

    private function get_products_query($settings)
    {
        if (!$this->is_woocommerce_active()) {
            return new WP_Query();
        }

        // Debug: Verificar productos básicos primero
        $all_products = wc_get_products([
            'limit' => 5,
            'status' => 'publish',
        ]);

        // Si no hay productos básicos, usar WP_Query como fallback
        if (empty($all_products)) {
            $args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => $settings['products_per_page'],
            ];

            switch ($settings['product_source']) {
                case 'featured':
                    $args['meta_query'] = [
                        [
                            'key' => '_featured',
                            'value' => 'yes'
                        ]
                    ];
                    break;
                case 'sale':
                    $args['meta_query'] = [
                        [
                            'key' => '_sale_price',
                            'value' => '',
                            'compare' => '!='
                        ]
                    ];
                    break;
                case 'best_selling':
                    $args['meta_key'] = 'total_sales';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                case 'recent':
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                    break;
                case 'category':
                    if (!empty($settings['product_category'])) {
                        $args['tax_query'] = [
                            [
                                'taxonomy' => 'product_cat',
                                'field' => 'term_id',
                                'terms' => $settings['product_category']
                            ]
                        ];
                    }
                    break;
            }

            return new WP_Query($args);
        }

        // Usar wc_get_products() que es la función recomendada de WooCommerce
        // Si products_per_page es -1, mostrar todos los productos
        if ($settings['products_per_page'] == -1) {
            $max_products = -1; // Sin límite
        } else {
            // Calcular el límite total: productos por página x 3 páginas
            $max_products = $settings['products_per_page'] * 3;
        }
        
        $args = [
            'limit' => $max_products, // Obtener productos según configuración
            'status' => 'publish',
        ];

        switch ($settings['product_source']) {
            case 'featured':
                $args['featured'] = true;
                // Si queremos mostrar todos los productos, no limitar por featured
                if ($settings['products_per_page'] == -1) {
                    // Obtener todos los productos destacados disponibles
                    $args['limit'] = -1;
                }
                break;
            case 'sale':
                $args['on_sale'] = true;
                break;
            case 'best_selling':
                $args['orderby'] = 'popularity';
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

        // Obtener productos usando la API de WooCommerce
        $products = wc_get_products($args);
        
        // Si no hay suficientes productos con el filtro específico, usar fallback
        // Solo aplicar fallback si no estamos mostrando todos los productos (-1)
        if ($settings['products_per_page'] != -1 && count($products) < $settings['products_per_page']) {
            // Fallback: obtener productos recientes para completar
            $fallback_args = [
                'limit' => $max_products,
                'status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
            ];
            
            // Si es categoría específica, mantener el filtro
            if ($settings['product_source'] === 'category' && !empty($settings['product_category'])) {
                $fallback_args['category'] = [$settings['product_category']];
            }
            
            $fallback_products = wc_get_products($fallback_args);
            
            // Combinar productos originales con fallback, evitando duplicados
            $existing_ids = array_map(function($product) {
                return $product->get_id();
            }, $products);
            
            foreach ($fallback_products as $fallback_product) {
                if (!in_array($fallback_product->get_id(), $existing_ids) && count($products) < $max_products) {
                    $products[] = $fallback_product;
                }
            }
        }
        
        // Si estamos en modo "todos los productos" pero la fuente es "featured" y no hay suficientes,
        // cambiar a "todos los productos" sin filtro
        if ($settings['products_per_page'] == -1 && $settings['product_source'] === 'featured' && count($products) < 10) {
            // Obtener todos los productos sin filtro de featured
            $all_products_args = [
                'limit' => -1,
                'status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
            ];
            
            // Si hay categoría específica, aplicarla
            if (!empty($settings['product_category'])) {
                $all_products_args['category'] = [$settings['product_category']];
            }
            
            $products = wc_get_products($all_products_args);
            
            // Log para debug
            error_log('Modo "todos los productos" activado: Mostrando ' . count($products) . ' productos sin filtro de featured');
        }
        
        // Convertir a WP_Query para mantener compatibilidad
        if (empty($products)) {
            return new WP_Query(['post__in' => [0]]); // Query vacío
        }

        $product_ids = array_map(function($product) {
            return $product->get_id();
        }, $products);

        $query_args = [
            'post_type' => 'product',
            'post__in' => $product_ids,
            'orderby' => 'post__in',
        ];
        
        // Si products_per_page es -1, no establecer límite
        if ($settings['products_per_page'] != -1) {
            $query_args['posts_per_page'] = $settings['products_per_page'];
        }
        
        return new WP_Query($query_args);
    }

    private function get_carousel_options($settings)
    {
        $options = [
            'items' => 1, // Cada <ul> es un slide
            'nav' => false, // Usar flechas personalizadas del theme
            'slideSpeed' => 300,
            'dots' => $settings['show_dots'] === 'yes',
            'rtl' => is_rtl(),
            'paginationSpeed' => 400,
            'navText' => ['', ''],
            'margin' => 0,
            'touchDrag' => true,
            'autoplay' => $settings['autoplay'] === 'yes',
        ];

        return json_encode($options);
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        
        if (!$this->is_woocommerce_active()) {
            echo '<div class="elementor-alert elementor-alert-warning">' . 
                 esc_html__('WooCommerce no está activo. Este widget requiere WooCommerce.', 'kinta-electronic-elementor') . 
                 '</div>';
            return;
        }

        $products_query = $this->get_products_query($settings);
        
        // Debug temporal - remover en producción
        if (current_user_can('manage_options')) {
            // Verificar productos básicos
            $basic_products = wc_get_products(['limit' => 5, 'status' => 'publish']);
            $total_products = wp_count_posts('product');
            $max_products = $settings['products_per_page'] * 3;
            
            // Verificar productos destacados específicamente
            $featured_products = wc_get_products(['featured' => true, 'limit' => 10, 'status' => 'publish']);
            
            echo '<div class="elementor-alert elementor-alert-info" style="margin-bottom: 20px;">';
            echo '<strong>Debug Info:</strong><br>';
            echo 'WooCommerce activo: ' . ($this->is_woocommerce_active() ? 'Sí' : 'No') . '<br>';
            echo 'Total productos en BD: ' . $total_products->publish . '<br>';
            echo 'Productos básicos encontrados: ' . count($basic_products) . '<br>';
            echo 'Productos destacados disponibles: ' . count($featured_products) . '<br>';
            
            if ($settings['products_per_page'] == -1) {
                echo 'Modo: <strong style="color: green;">Mostrando TODOS los productos</strong><br>';
                echo 'Máximo productos solicitados: Sin límite<br>';
            } else {
                echo 'Máximo productos solicitados: ' . $max_products . ' (' . $settings['products_per_page'] . ' x 3 páginas)<br>';
            }
            
            echo 'Productos en query: ' . $products_query->found_posts . '<br>';
            echo 'Productos por página: ' . ($settings['products_per_page'] == -1 ? 'Todos' : $settings['products_per_page']) . '<br>';
            echo 'Fuente seleccionada: ' . $settings['product_source'] . '<br>';
            if (!empty($settings['product_category'])) {
                echo 'Categoría seleccionada: ' . $settings['product_category'] . '<br>';
            }
            if ($settings['products_per_page'] != -1 && count($featured_products) < $settings['products_per_page'] && $settings['product_source'] === 'featured') {
                echo '<strong style="color: orange;">⚠️ Fallback activado: Solo ' . count($featured_products) . ' productos destacados, completando con productos recientes</strong><br>';
            }
            echo '</div>';
        }
        
        if (!$products_query->have_posts()) {
            echo '<div class="elementor-alert elementor-alert-info">' . 
                 esc_html__('No se encontraron productos.', 'kinta-electronic-elementor') . 
                 '</div>';
            return;
        }

        $carousel_id = 'kinta-carousel-' . $this->get_id();
        $carousel_options = $this->get_carousel_options($settings);
        ?>
        
        <section class="section-product-cards-carousel home-v1-product-cards-carousel animate-in-view" data-animation="fadeIn">
            <header class="show-nav">
                <h2 class="h1"><?php echo esc_html($settings['section_title']); ?></h2>
                
                <?php if ($settings['show_tabs'] === 'yes' && !empty($settings['tab_categories'])): ?>
                <ul class="nav nav-inline">
                    <li class="nav-item active">
                        <span class="nav-link"><?php echo esc_html($settings['section_title']); ?></span>
                    </li>
                    <?php foreach ($settings['tab_categories'] as $category_id): 
                        $category = get_term($category_id, 'product_cat');
                        if ($category && !is_wp_error($category)):
                    ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo esc_url(get_term_link($category)); ?>">
                            <?php echo esc_html($category->name); ?>
                        </a>
                    </li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
                <?php endif; ?>
            </header>
            
            <div id="<?php echo esc_attr($carousel_id); ?>" 
                 data-ride="owl-carousel"
                 data-carousel-selector=".product-cards-carousel"
                 data-carousel-options="<?php echo esc_attr($carousel_options); ?>">
                <div class="woocommerce columns-3 product-cards-carousel owl-carousel">
                    <?php
                    // Dividir productos en grupos para el carrusel
                    $products_per_slide = $settings['items_desktop']; // 8 productos por slide
                    $all_products = [];
                    
                    // Resetear el query para poder iterar de nuevo
                    $products_query->rewind_posts();
                    
                    // Recopilar productos limitados según configuración
                    $max_products = ($settings['products_per_page'] == -1) ? -1 : $settings['products_per_page'];
                    $count = 0;
                    
                    while ($products_query->have_posts() && ($max_products == -1 || $count < $max_products)): 
                        $products_query->the_post();
                        global $product;
                        if (!$product) continue;
                        $all_products[] = $product;
                        $count++;
                    endwhile;
                    
                    // Dividir en grupos según productos por slide
                    $product_groups = array_chunk($all_products, $products_per_slide);
                    
                    // Crear un slide para cada grupo
                    foreach ($product_groups as $group_index => $product_group):
                    ?>
                    <ul data-view="grid" 
                        data-bs-toggle="regular-products"
                        class="products products list-unstyled row g-0 row-cols-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-4">
                        <?php 
                        foreach ($product_group as $product): 
                        ?>
                        <li class="product-card post-<?php echo $product->get_id(); ?> product type-product status-publish has-post-thumbnail <?php echo implode(' ', wc_get_product_class('', $product)); ?>">
                            <div class="product-outer product-item__outer">
                                <div class="product-inner">
                                    <a class="card-media-left" 
                                       href="<?php echo esc_url($product->get_permalink()); ?>" 
                                       title="<?php echo esc_attr($product->get_name()); ?>">
                                        <?php echo $product->get_image('woocommerce_thumbnail', ['loading' => 'lazy']); ?>
                                    </a>

                                    <div class="card-body">
                                        <div class="card-body-inner">
                                            <span class="loop-product-categories">
                                                <?php
                                                $categories = $product->get_category_ids();
                                                if (!empty($categories)) {
                                                    $category_links = [];
                                                    foreach (array_slice($categories, 0, 2) as $cat_id) {
                                                        $cat = get_term($cat_id, 'product_cat');
                                                        if ($cat && !is_wp_error($cat)) {
                                                            $category_links[] = '<a href="' . get_term_link($cat) . '" rel="tag">' . $cat->name . '</a>';
                                                        }
                                                    }
                                                    echo implode(', ', $category_links);
                                                }
                                                ?>
                                            </span>
                                            
                                            <a href="<?php echo esc_url($product->get_permalink()); ?>" 
                                               class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                                <h2 class="woocommerce-loop-product__title"><?php echo esc_html($product->get_name()); ?></h2>
                                            </a>
                                            
                                            <div class="price-add-to-cart">
                                                <span class="price">
                                                    <span class="electro-price">
                                                        <?php echo wp_kses_post($product->get_price_html()); ?>
                                                    </span>
                                                </span>
                                                
                                                <div class="add-to-cart-wrap" 
                                                     data-bs-toggle="tooltip" 
                                                     data-bs-title="<?php esc_attr_e('Add to cart', 'woocommerce'); ?>">
                                                    <?php
                                                    if ($product->is_in_stock()) {
                                                        woocommerce_template_loop_add_to_cart();
                                                    } else {
                                                        echo '<a href="' . esc_url($product->get_permalink()) . '" class="button product_type_simple">' . 
                                                             esc_html__('Read more', 'woocommerce') . '</a>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="hover-area">
                                        <div class="action-buttons">
                                            <?php if ($this->is_yith_wishlist_active()): ?>
                                            <div class="yith-wcwl-add-to-wishlist add-to-wishlist-<?php echo $product->get_id(); ?> yith-wcwl-add-to-wishlist--link-style">
                                                <?php echo $this->render_yith_wishlist_button($product->get_id()); ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($this->is_yith_compare_active()): ?>
                                            <a href="<?php echo esc_url(add_query_arg('action', 'yith-woocompare-add-product&id=' . $product->get_id(), home_url())); ?>" 
                                               class="compare link add-to-compare-link" 
                                               data-product_id="<?php echo $product->get_id(); ?>" 
                                               target="_self" rel="nofollow">
                                                <span class="label"><?php esc_html_e('Compare', 'yith-woocommerce-compare'); ?></span>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>


        <?php
        wp_reset_postdata();
    }
}