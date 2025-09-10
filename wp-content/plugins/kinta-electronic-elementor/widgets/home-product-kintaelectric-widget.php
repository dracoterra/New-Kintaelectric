<?php
/**
 * Widget Home Product Kintaelectric para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Home Product Kintaelectric
 */
class KEE_Home_Product_Kintaelectric_Widget extends \Elementor\Widget_Base {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'home_product_kintaelectric';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('Home Product Kintaelectric', 'kinta-electric-elementor');
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
        return ['product', 'home', 'kintaelectric', 'grid', 'tabs', 'woocommerce'];
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
            'section_title',
            [
                'label' => esc_html__('Título de la Sección', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Products Grid', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'products_per_tab',
            [
                'label' => esc_html__('Productos por Pestaña', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 20,
            ]
        );

        $this->add_control(
            'gallery_products_count',
            [
                'label' => esc_html__('Productos en Galería del Producto Grande', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1,
                'max' => 10,
                'description' => esc_html__('Número máximo de productos que se mostrarán en la galería del producto grande.', 'kinta-electric-elementor'),
            ]
        );

        $this->end_controls_section();

        // Sección de Pestañas
        $this->start_controls_section(
            'section_tabs',
            [
                'label' => esc_html__('Pestañas de Productos', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'tab_title',
            [
                'label' => esc_html__('Título de la Pestaña', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Best Deals', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'tab_category',
            [
                'label' => esc_html__('Categoría de Productos', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_product_categories(),
                'default' => '',
                'multiple' => false,
                'description' => esc_html__('Selecciona una categoría para esta pestaña. Deja vacío para mostrar todos los productos.', 'kinta-electric-elementor'),
            ]
        );

        $repeater->add_control(
            'tab_orderby',
            [
                'label' => esc_html__('Ordenar por', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => esc_html__('Fecha', 'kinta-electric-elementor'),
                    'title' => esc_html__('Título', 'kinta-electric-elementor'),
                    'price' => esc_html__('Precio', 'kinta-electric-elementor'),
                    'popularity' => esc_html__('Popularidad', 'kinta-electric-elementor'),
                    'rating' => esc_html__('Valoración', 'kinta-electric-elementor'),
                    'rand' => esc_html__('Aleatorio', 'kinta-electric-elementor'),
                ],
            ]
        );

        $repeater->add_control(
            'tab_order',
            [
                'label' => esc_html__('Orden', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'ASC' => esc_html__('Ascendente', 'kinta-electric-elementor'),
                    'DESC' => esc_html__('Descendente', 'kinta-electric-elementor'),
                ],
            ]
        );

        $repeater->add_control(
            'featured_product_id',
            [
                'label' => esc_html__('Producto Destacado (Centro)', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_all_products(),
                'default' => '',
                'multiple' => false,
                'description' => esc_html__('Selecciona un producto específico para que aparezca en el centro como producto grande. Deja vacío para que se seleccione automáticamente.', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'tabs',
            [
                'label' => esc_html__('Pestañas', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'tab_title' => esc_html__('Best Deals', 'kinta-electric-elementor'),
                        'tab_category' => '',
                        'tab_orderby' => 'date',
                        'tab_order' => 'DESC',
                        'featured_product_id' => '',
                    ],
                    [
                        'tab_title' => esc_html__('TV & Video', 'kinta-electric-elementor'),
                        'tab_category' => '',
                        'tab_orderby' => 'date',
                        'tab_order' => 'DESC',
                        'featured_product_id' => '',
                    ],
                    [
                        'tab_title' => esc_html__('Cameras', 'kinta-electric-elementor'),
                        'tab_category' => '',
                        'tab_orderby' => 'date',
                        'tab_order' => 'DESC',
                        'featured_product_id' => '',
                    ],
                    [
                        'tab_title' => esc_html__('Audio', 'kinta-electric-elementor'),
                        'tab_category' => '',
                        'tab_orderby' => 'date',
                        'tab_order' => 'DESC',
                        'featured_product_id' => '',
                    ],
                    [
                        'tab_title' => esc_html__('Smartphones', 'kinta-electric-elementor'),
                        'tab_category' => '',
                        'tab_orderby' => 'date',
                        'tab_order' => 'DESC',
                        'featured_product_id' => '',
                    ],
                ],
                'title_field' => '{{{ tab_title }}}',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Obtener categorías de productos
     */
    private function get_product_categories() {
        $categories = [];
        
        if (class_exists('WooCommerce')) {
            $product_categories = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
            ]);
            
            if (!is_wp_error($product_categories)) {
                foreach ($product_categories as $category) {
                    $categories[$category->term_id] = $category->name;
                }
            }
        }
        
        return $categories;
    }

    /**
     * Obtener todos los productos para selección
     */
    private function get_all_products() {
        $products = [];
        
        if (class_exists('WooCommerce')) {
            $args = [
                'post_type' => 'product',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => [
                    [
                        'key' => '_visibility',
                        'value' => ['catalog', 'visible'],
                        'compare' => 'IN'
                    ]
                ]
            ];
            
            $products_query = new WP_Query($args);
            
            if ($products_query->have_posts()) {
                while ($products_query->have_posts()) {
                    $products_query->the_post();
                    $product = wc_get_product(get_the_ID());
                    if ($product) {
                        $products[get_the_ID()] = get_the_title() . ' (ID: ' . get_the_ID() . ')';
                    }
                }
                wp_reset_postdata();
            }
        }
        
        return $products;
    }

    /**
     * Obtener productos para una pestaña
     */
    private function get_products_for_tab($category_id, $orderby, $order, $limit = 8) {
        if (!class_exists('WooCommerce')) {
            return [];
        }
        
        $args = [
            'post_type' => 'product',
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_visibility',
                    'value' => ['catalog', 'visible'],
                    'compare' => 'IN'
                ]
            ]
        ];
        
        // Agregar categoría si está especificada
        if (!empty($category_id)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_id,
                ]
            ];
        }
        
        // Configurar ordenamiento
        switch ($orderby) {
            case 'price':
                $args['meta_key'] = '_price';
                $args['orderby'] = 'meta_value_num';
                break;
            case 'popularity':
                $args['meta_key'] = 'total_sales';
                $args['orderby'] = 'meta_value_num';
                break;
            case 'rating':
                $args['meta_key'] = '_wc_average_rating';
                $args['orderby'] = 'meta_value_num';
                break;
            case 'rand':
                $args['orderby'] = 'rand';
                break;
            default:
                $args['orderby'] = $orderby;
        }
        
        $args['order'] = $order;
        
        return wc_get_products($args);
    }

    /**
     * Renderizar widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if (empty($settings['tabs'])) {
            return;
        }
        ?>
        <!-- Products-4-1-4 -->
        <div class="products-group-4-1-4 space-1 bg-gray-7">
            <h2 class="sr-only"><?php echo esc_html($settings['section_title']); ?></h2>
            <div class="container">
                <!-- Nav Classic -->
                <div class="position-relative text-center z-index-2 mb-3">
                    <ul class="nav nav-classic nav-tab nav-tab-sm px-md-3 justify-content-start justify-content-lg-center flex-nowrap flex-lg-wrap overflow-auto overflow-lg-visble border-md-down-bottom-0 pb-1 pb-lg-0 mb-n1 mb-lg-0" id="pills-tab-1" role="tablist">
                        <?php foreach ($settings['tabs'] as $index => $tab) : ?>
                            <li class="nav-item flex-shrink-0 flex-lg-shrink-1">
                                <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                                   id="Tpills-<?php echo $index + 1; ?>-example1-tab" 
                                   data-toggle="pill" 
                                   href="#Tpills-<?php echo $index + 1; ?>-example1" 
                                   role="tab" 
                                   aria-controls="Tpills-<?php echo $index + 1; ?>-example1" 
                                   aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                                    <div class="d-md-flex justify-content-md-center align-items-md-center">
                                        <?php echo esc_html($tab['tab_title']); ?>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <!-- End Nav Classic -->

                <!-- Tab Content -->
                <div class="tab-content" id="Tpills-tabContent">
                    <?php foreach ($settings['tabs'] as $index => $tab) : ?>
                        <div class="tab-pane fade <?php echo $index === 0 ? 'pt-2 show active' : 'pt-2'; ?>" 
                             id="Tpills-<?php echo $index + 1; ?>-example1" 
                             role="tabpanel" 
                             aria-labelledby="Tpills-<?php echo $index + 1; ?>-example1-tab">
                            
                            <?php 
                            $products = $this->get_products_for_tab(
                                $tab['tab_category'], 
                                $tab['tab_orderby'], 
                                $tab['tab_order'], 
                                $settings['products_per_tab']
                            );
                            
                            if (!empty($products)) :
                                // Si hay un producto destacado seleccionado, usarlo como producto grande
                                $large_product = null;
                                if (!empty($tab['featured_product_id'])) {
                                    $large_product = wc_get_product($tab['featured_product_id']);
                                }
                                
                                // Si no hay producto destacado o no se pudo obtener, usar el quinto producto
                                if (!$large_product && isset($products[4])) {
                                    $large_product = $products[4];
                                }
                                
                                // Dividir productos en grupos: 4 productos pequeños, 1 grande, 4 productos pequeños
                                $small_products = array_slice($products, 0, 4);
                                $remaining_products = array_slice($products, 5, 4);
                            ?>
                                <div class="row no-gutters">
                                    <!-- Primera columna: 4 productos pequeños -->
                                    <div class="col-md-3 col-wd-4 d-md-flex d-wd-block">
                                        <ul class="row list-unstyled products-group no-gutters mb-0 flex-xl-column flex-wd-row">
                                            <?php foreach ($small_products as $product_index => $product) : ?>
                                                <li class="col-xl-6 product-item max-width-xl-100 <?php echo $product_index > 0 ? 'remove-divider' : ''; ?>">
                                                    <?php $this->render_product_item($product, 'small'); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <!-- Segunda columna: 1 producto grande -->
                                    <?php if ($large_product) : ?>
                                        <div class="col-md-6 col-wd-4 products-group-1">
                                            <ul class="row list-unstyled products-group no-gutters bg-white h-100 mb-0">
                                                <li class="col product-item remove-divider">
                                                    <?php $this->render_product_item($large_product, 'large'); ?>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Tercera columna: 4 productos pequeños -->
                                    <div class="col-md-3 col-wd-4 d-md-flex d-wd-block">
                                        <ul class="row list-unstyled products-group no-gutters mb-0 flex-xl-column flex-wd-row">
                                            <?php foreach ($remaining_products as $product_index => $product) : ?>
                                                <li class="col-xl-6 product-item max-width-xl-100 <?php echo $product_index > 0 ? 'remove-divider' : ''; ?> d-md-none d-wd-block">
                                                    <?php $this->render_product_item($product, 'small'); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="text-center py-5">
                                    <p class="text-gray-5">No se encontraron productos en esta categoría.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- End Tab Content -->
            </div>
        </div>
        <!-- End Products-4-1-4 -->
        <?php
    }

    /**
     * Renderizar item de producto
     */
    private function render_product_item($product, $size = 'small') {
        $settings = $this->get_settings_for_display();
        $product_id = $product->get_id();
        $product_url = get_permalink($product_id);
        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
        $product_image_url = $product_image ? $product_image[0] : \Elementor\Utils::get_placeholder_image_src();
        $product_categories = get_the_terms($product_id, 'product_cat');
        $category_name = $product_categories ? $product_categories[0]->name : 'Product';
        $price_html = $product->get_price_html();
        ?>
        <div class="product-item__outer h-100 w-100 prodcut-box-shadow">
            <div class="product-item__inner bg-white p-3">
                <div class="product-item__body <?php echo $size === 'large' ? 'd-flex flex-column' : 'pb-xl-2'; ?>">
                    <?php if ($size === 'large') : ?>
                        <!-- Estructura específica para producto grande -->
                        <div class="mb-1">
                            <div class="mb-2">
                                <a href="<?php echo esc_url(get_term_link($product_categories[0]->term_id, 'product_cat')); ?>" class="font-size-12 text-gray-5">
                                    <?php echo esc_html($category_name); ?>
                                </a>
                            </div>
                            <h5 class="mb-0 product-item__title">
                                <a href="<?php echo esc_url($product_url); ?>" class="text-blue font-weight-bold">
                                    <?php echo esc_html($product->get_name()); ?>
                                </a>
                            </h5>
                        </div>
                        <div class="mb-1 min-height-4-1-4">
                            <a href="<?php echo esc_url($product_url); ?>" class="d-block text-center my-4 mt-lg-6 mb-lg-5 mt-xl-0 mb-xl-0 mt-wd-6 mb-wd-5">
                                <img class="img-fluid" src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                            </a>
                            
                            <!-- Galería de miniaturas para producto grande -->
                            <?php 
                            $gallery_ids = $product->get_gallery_image_ids();
                            if (!empty($gallery_ids)) : 
                            ?>
                                <div class="row mx-gutters-2 mb-3">
                                    <?php 
                                    $gallery_limit = isset($settings['gallery_products_count']) ? intval($settings['gallery_products_count']) : 5;
                                    foreach (array_slice($gallery_ids, 0, $gallery_limit) as $gallery_id) : 
                                    ?>
                                        <?php 
                                        $gallery_image = wp_get_attachment_image_src($gallery_id, 'thumbnail');
                                        $gallery_full = wp_get_attachment_image_src($gallery_id, 'full');
                                        if ($gallery_image && $gallery_full) : 
                                        ?>
                                            <div class="col-auto">
                                                <!-- Gallery -->
                                                <a class="js-fancybox max-width-60 u-media-viewer" 
                                                   href="javascript:;" 
                                                   data-src="<?php echo esc_url($gallery_full[0]); ?>" 
                                                   data-fancybox="fancyboxGallery<?php echo esc_attr($product_id); ?>" 
                                                   data-caption="<?php echo esc_attr($product->get_name()); ?>" 
                                                   data-speed="700" 
                                                   data-is-infinite="true">
                                                    <img class="img-fluid border" 
                                                         src="<?php echo esc_url($gallery_image[0]); ?>" 
                                                         alt="<?php echo esc_attr($product->get_name()); ?>">
                                                    <span class="u-media-viewer__container">
                                                        <span class="u-media-viewer__icon">
                                                            <span class="fas fa-plus u-media-viewer__icon-inner"></span>
                                                        </span>
                                                    </span>
                                                </a>
                                                <!-- End Gallery -->
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <div class="col"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <!-- Estructura para productos pequeños -->
                        <div class="mb-2">
                            <a href="<?php echo esc_url(get_term_link($product_categories[0]->term_id, 'product_cat')); ?>" class="font-size-12 text-gray-5">
                                <?php echo esc_html($category_name); ?>
                            </a>
                        </div>
                        <h5 class="mb-1 product-item__title">
                            <a href="<?php echo esc_url($product_url); ?>" class="text-blue font-weight-bold">
                                <?php echo esc_html($product->get_name()); ?>
                            </a>
                        </h5>
                        <div class="mb-2">
                            <a href="<?php echo esc_url($product_url); ?>" class="d-block text-center">
                                <img class="img-fluid" src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex-center-between">
                        <div class="prodcut-price">
                            <div class="text-gray-100"><?php echo wp_kses_post($price_html); ?></div>
                        </div>
                        <div class="d-none d-xl-block prodcut-add-cart">
                            <?php if ($size === 'large') : ?>
                                <!-- Botón completo para producto grande -->
                                <a href="<?php echo esc_url($product_url); ?>" class="btn-add-cart btn-add-cart__wide btn-primary transition-3d-hover">
                                    <i class="ec ec-add-to-cart mr-2"></i> Add to Cart
                                </a>
                            <?php else : ?>
                                <!-- Botón simple para productos pequeños -->
                                <a href="<?php echo esc_url($product_url); ?>" class="btn-add-cart btn-primary transition-3d-hover">
                                    <i class="ec ec-add-to-cart"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="product-item__footer">
                    <div class="border-top pt-2 flex-center-between flex-wrap">
                        <a href="#" class="text-gray-6 font-size-13">
                            <i class="ec ec-compare mr-1 font-size-15"></i> Compare
                        </a>
                        <a href="#" class="text-gray-6 font-size-13">
                            <i class="ec ec-favorites mr-1 font-size-15"></i> Add to Wishlist
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Renderizar widget en modo de edición
     */
    protected function content_template() {
        ?>
        <!-- Products-4-1-4 -->
        <div class="products-group-4-1-4 space-1 bg-gray-7">
            <h2 class="sr-only">{{{ settings.section_title }}}</h2>
            <div class="container">
                <!-- Nav Classic -->
                <div class="position-relative text-center z-index-2 mb-3">
                    <ul class="nav nav-classic nav-tab nav-tab-sm px-md-3 justify-content-start justify-content-lg-center flex-nowrap flex-lg-wrap overflow-auto overflow-lg-visble border-md-down-bottom-0 pb-1 pb-lg-0 mb-n1 mb-lg-0" id="pills-tab-1" role="tablist">
                        <# if (settings.tabs && settings.tabs.length > 0) { #>
                            <# _.each(settings.tabs, function(tab, index) { #>
                                <li class="nav-item flex-shrink-0 flex-lg-shrink-1">
                                    <a class="nav-link <# if (index === 0) { #>active<# } #>" 
                                       id="Tpills-{{ index + 1 }}-example1-tab" 
                                       data-toggle="pill" 
                                       href="#Tpills-{{ index + 1 }}-example1" 
                                       role="tab" 
                                       aria-controls="Tpills-{{ index + 1 }}-example1" 
                                       aria-selected="<# if (index === 0) { #>true<# } else { #>false<# } #>">
                                        <div class="d-md-flex justify-content-md-center align-items-md-center">
                                            {{{ tab.tab_title }}}
                                        </div>
                                    </a>
                                </li>
                            <# }); #>
                        <# } #>
                    </ul>
                </div>
                <!-- End Nav Classic -->

                <!-- Tab Content -->
                <div class="tab-content" id="Tpills-tabContent">
                    <# if (settings.tabs && settings.tabs.length > 0) { #>
                        <# _.each(settings.tabs, function(tab, index) { #>
                            <div class="tab-pane fade <# if (index === 0) { #>pt-2 show active<# } else { #>pt-2<# } #>" 
                                 id="Tpills-{{ index + 1 }}-example1" 
                                 role="tabpanel" 
                                 aria-labelledby="Tpills-{{ index + 1 }}-example1-tab">
                                
                                <div class="text-center py-5">
                                    <p class="text-gray-5">En el modo de edición se muestra la estructura de las pestañas. Los productos reales aparecerán en el frontend.</p>
                                    <p class="text-gray-5">Pestaña: {{{ tab.tab_title }}}</p>
                                    <# if (tab.tab_category) { #>
                                        <p class="text-gray-5">Categoría: {{{ tab.tab_category }}}</p>
                                    <# } #>
                                    <# if (tab.featured_product_id) { #>
                                        <p class="text-gray-5">Producto Destacado: {{{ tab.featured_product_id }}}</p>
                                    <# } else { #>
                                        <p class="text-gray-5">Producto Destacado: Selección automática</p>
                                    <# } #>
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <h6 class="text-gray-6">Estructura del Grid 4-1-4:</h6>
                                        <ul class="text-left text-gray-5">
                                            <li>• 4 productos pequeños (izquierda)</li>
                                            <li>• 1 producto grande con galería (centro)</li>
                                            <li>• 4 productos pequeños (derecha)</li>
                                        </ul>
                                        <p class="text-gray-6 mt-2"><small>El producto grande mostrará una galería de miniaturas debajo de la imagen principal si tiene múltiples imágenes.</small></p>
                                        <p class="text-gray-6 mt-2"><small>Puedes seleccionar un producto específico para que aparezca en el centro como producto destacado.</small></p>
                                    </div>
                                </div>
                            </div>
                        <# }); #>
                    <# } #>
                </div>
                <!-- End Tab Content -->
            </div>
        </div>
        <!-- End Products-4-1-4 -->
        <?php
    }
}
