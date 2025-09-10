<?php
/**
 * Widget Home Banner and Product Kintaelectric para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Home Banner and Product Kintaelectric
 */
class KEE_Home_Banner_Product_Kintaelectric_Widget extends \Elementor\Widget_Base {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'home_banner_product_kintaelectric';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('Home Banner & Product Kintaelectric', 'kinta-electric-elementor');
    }

    /**
     * Obtener icono del widget
     */
    public function get_icon() {
        return 'eicon-banner';
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
        return ['banner', 'product', 'kintaelectric', 'home', 'tabs'];
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
        // Sección de Banner
        $this->start_controls_section(
            'section_banner',
            [
                'label' => esc_html__('Banner', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'banner_image',
            [
                'label' => esc_html__('Imagen del Banner', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'banner_title',
            [
                'label' => esc_html__('Título del Banner', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('CATCH BIG DEALS ON THE CAMERAS', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'banners',
            [
                'label' => esc_html__('Banners', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'banner_image' => [
                            'url' => \Elementor\Utils::get_placeholder_image_src(),
                        ],
                        'banner_title' => esc_html__('CATCH BIG DEALS ON THE CAMERAS', 'kinta-electric-elementor'),
                    ],
                    [
                        'banner_image' => [
                            'url' => \Elementor\Utils::get_placeholder_image_src(),
                        ],
                        'banner_title' => esc_html__('CATCH BIG DEALS ON THE CAMERAS', 'kinta-electric-elementor'),
                    ],
                    [
                        'banner_image' => [
                            'url' => \Elementor\Utils::get_placeholder_image_src(),
                        ],
                        'banner_title' => esc_html__('CATCH BIG DEALS ON THE CAMERAS', 'kinta-electric-elementor'),
                    ],
                    [
                        'banner_image' => [
                            'url' => \Elementor\Utils::get_placeholder_image_src(),
                        ],
                        'banner_title' => esc_html__('CATCH BIG DEALS ON THE CAMERAS', 'kinta-electric-elementor'),
                    ],
                ],
                'title_field' => '{{{ banner_title }}}',
            ]
        );

        $this->end_controls_section();

        // Sección de Oferta Especial
        $this->start_controls_section(
            'section_special_offer',
            [
                'label' => esc_html__('Oferta Especial', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'offer_title',
            [
                'label' => esc_html__('Título de la Oferta', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Oferta Especial', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'save_amount',
            [
                'label' => esc_html__('Cantidad a Ahorrar', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('120', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'product_image',
            [
                'label' => esc_html__('Imagen del Producto', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'product_title',
            [
                'label' => esc_html__('Título del Producto', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Game Console Controller + USB 3.0 Cable', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'old_price',
            [
                'label' => esc_html__('Precio Anterior', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('99,00', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'new_price',
            [
                'label' => esc_html__('Precio Nuevo', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('79,00', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'available_quantity',
            [
                'label' => esc_html__('Cantidad Disponible', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
            ]
        );

        $this->add_control(
            'sold_quantity',
            [
                'label' => esc_html__('Cantidad Vendida', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 28,
            ]
        );

        $this->add_control(
            'countdown_date',
            [
                'label' => esc_html__('Fecha de Finalización', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::DATE_TIME,
                'default' => '2025-12-31 23:59',
            ]
        );

        $this->end_controls_section();

        // Sección de Productos
        $this->start_controls_section(
            'section_products',
            [
                'label' => esc_html__('Productos', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'featured_tab_title',
            [
                'label' => esc_html__('Título Pestaña Featured', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Featured', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'onsale_tab_title',
            [
                'label' => esc_html__('Título Pestaña On Sale', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('On Sale', 'kinta-electric-elementor'),
            ]
        );

        $this->add_control(
            'toprated_tab_title',
            [
                'label' => esc_html__('Título Pestaña Top Rated', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Top Rated', 'kinta-electric-elementor'),
            ]
        );

        // Control para número de productos por pestaña
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

        // Control para categoría de productos Featured
        $this->add_control(
            'featured_category',
            [
                'label' => esc_html__('Categoría Featured', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_product_categories(),
                'default' => '',
                'multiple' => false,
            ]
        );

        // Control para categoría de productos On Sale
        $this->add_control(
            'onsale_category',
            [
                'label' => esc_html__('Categoría On Sale', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_product_categories(),
                'default' => '',
                'multiple' => false,
            ]
        );

        // Control para categoría de productos Top Rated
        $this->add_control(
            'toprated_category',
            [
                'label' => esc_html__('Categoría Top Rated', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_product_categories(),
                'default' => '',
                'multiple' => false,
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
     * Obtener productos por categoría
     */
    private function get_products_by_category($category_id, $limit = 8) {
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
        
        if (!empty($category_id)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_id,
                ]
            ];
        }
        
        return wc_get_products($args);
    }

    /**
     * Obtener productos en oferta
     */
    private function get_onsale_products($category_id, $limit = 8) {
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
        
        if (!empty($category_id)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_id,
                ]
            ];
        }
        
        // Solo productos en oferta
        $args['meta_query'][] = [
            'relation' => 'OR',
            [
                'key' => '_sale_price',
                'value' => '',
                'compare' => '!='
            ],
            [
                'key' => '_sale_price',
                'value' => 0,
                'compare' => '>'
            ]
        ];
        
        return wc_get_products($args);
    }

    /**
     * Obtener productos mejor valorados
     */
    private function get_top_rated_products($category_id, $limit = 8) {
        if (!class_exists('WooCommerce')) {
            return [];
        }
        
        $args = [
            'post_type' => 'product',
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'meta_key' => '_wc_average_rating',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
            'meta_query' => [
                [
                    'key' => '_visibility',
                    'value' => ['catalog', 'visible'],
                    'compare' => 'IN'
                ]
            ]
        ];
        
        if (!empty($category_id)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_id,
                ]
            ];
        }
        
        return wc_get_products($args);
    }

    /**
     * Renderizar widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="container">
            <!-- Banner -->
            <div class="mb-5">
                <div class="row">
                    <?php foreach ($settings['banners'] as $banner) : ?>
                        <div class="col-md-6 mb-4 mb-xl-0 col-xl-3">
                            <a href="#" class="d-black text-gray-90">
                                <div class="min-height-132 py-1 d-flex bg-gray-1 align-items-center">
                                    <div class="col-6 col-xl-5 col-wd-6 pr-0">
                                        <img class="img-fluid" src="<?php echo esc_url($banner['banner_image']['url']); ?>" alt="Image Description">
                                    </div>
                                    <div class="col-6 col-xl-7 col-wd-6">
                                        <div class="mb-2 pb-1 font-size-18 font-weight-light text-ls-n1 text-lh-23">
                                            <?php echo wp_kses_post($banner['banner_title']); ?>
                                        </div>
                                        <div class="link text-gray-90 font-weight-bold font-size-15" href="#">
                                            Shop now
                                            <span class="link__icon ml-1">
                                                <span class="link__icon-inner"><i class="ec ec-arrow-right-categproes"></i></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- End Banner -->

            <!-- Deals-and-tabs -->
            <div class="mb-5">
                <div class="row">
                    <!-- Deal -->
                    <div class="col-md-auto mb-6 mb-md-0">
                        <div class="p-3 border border-width-2 border-primary borders-radius-20 bg-white min-width-370">
                            <div class="d-flex justify-content-between align-items-center m-1 ml-2">
                                <h3 class="font-size-22 mb-0 font-weight-normal text-lh-28 max-width-120"><?php echo esc_html($settings['offer_title']); ?></h3>
                                <div class="d-flex align-items-center flex-column justify-content-center bg-primary rounded-pill height-75 width-75 text-lh-1">
                                    <span class="font-size-12">Save</span>
                                    <div class="font-size-20 font-weight-bold">$<?php echo esc_html($settings['save_amount']); ?></div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <a href="#" class="d-block text-center"><img class="img-fluid" src="<?php echo esc_url($settings['product_image']['url']); ?>" alt="Image Description"></a>
                            </div>
                            <h5 class="mb-2 font-size-14 text-center mx-auto max-width-180 text-lh-18"><a href="#" class="text-blue font-weight-bold"><?php echo esc_html($settings['product_title']); ?></a></h5>
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <del class="font-size-18 mr-2 text-gray-2">$<?php echo esc_html($settings['old_price']); ?></del>
                                <ins class="font-size-30 text-red text-decoration-none">$<?php echo esc_html($settings['new_price']); ?></ins>
                            </div>
                            <div class="mb-3 mx-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="">Disponibles: <strong><?php echo esc_html($settings['available_quantity']); ?></strong></span>
                                    <span class="">Ya vendido: <strong><?php echo esc_html($settings['sold_quantity']); ?></strong></span>
                                </div>
                                <div class="rounded-pill bg-gray-3 height-20 position-relative">
                                    <span class="position-absolute left-0 top-0 bottom-0 rounded-pill w-30 bg-primary"></span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <h6 class="font-size-15 text-gray-2 text-center mb-3">¡Date prisa! La oferta termina en:</h6>
                                <div class="js-countdown d-flex justify-content-center" data-end-date="<?php echo esc_attr($settings['countdown_date']); ?>" data-hours-format="%H" data-minutes-format="%M" data-seconds-format="%S">
                                    <div class="text-lh-1">
                                        <div class="text-gray-2 font-size-30 bg-gray-4 py-2 px-2 rounded-sm mb-2">
                                            <span class="js-cd-hours"></span>
                                        </div>
                                        <div class="text-gray-2 font-size-12 text-center">HRS</div>
                                    </div>
                                    <div class="mx-1 pt-1 text-gray-2 font-size-24">:</div>
                                    <div class="text-lh-1">
                                        <div class="text-gray-2 font-size-30 bg-gray-4 py-2 px-2 rounded-sm mb-2">
                                            <span class="js-cd-minutes"></span>
                                        </div>
                                        <div class="text-gray-2 font-size-12 text-center">MIN</div>
                                    </div>
                                    <div class="mx-1 pt-1 text-gray-2 font-size-24">:</div>
                                    <div class="text-lh-1">
                                        <div class="text-gray-2 font-size-30 bg-gray-4 py-2 px-2 rounded-sm mb-2">
                                            <span class="js-cd-seconds"></span>
                                        </div>
                                        <div class="text-gray-2 font-size-12 text-center">SEG</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Deal -->

                    <!-- Tab Product -->
                    <div class="col">
                        <!-- Features Section -->
                        <div class="">
                            <!-- Nav Classic -->
                            <div class="position-relative bg-white text-center z-index-2">
                                <ul class="nav nav-classic nav-tab justify-content-center" id="pills-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="pills-one-example1-tab" data-toggle="pill" href="#pills-one-example1" role="tab" aria-controls="pills-one-example1" aria-selected="true">
                                            <div class="d-md-flex justify-content-md-center align-items-md-center">
                                                <?php echo esc_html($settings['featured_tab_title']); ?>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-two-example1-tab" data-toggle="pill" href="#pills-two-example1" role="tab" aria-controls="pills-two-example1" aria-selected="false">
                                            <div class="d-md-flex justify-content-md-center align-items-md-center">
                                                <?php echo esc_html($settings['onsale_tab_title']); ?>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-three-example1-tab" data-toggle="pill" href="#pills-three-example1" role="tab" aria-controls="pills-three-example1" aria-selected="false">
                                            <div class="d-md-flex justify-content-md-center align-items-md-center">
                                                <?php echo esc_html($settings['toprated_tab_title']); ?>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <!-- End Nav Classic -->

                            <!-- Tab Content -->
                            <div class="tab-content" id="pills-tabContent">
                                <!-- Featured Tab -->
                                <div class="tab-pane fade pt-2 show active" id="pills-one-example1" role="tabpanel" aria-labelledby="pills-one-example1-tab">
                                    <ul class="row list-unstyled products-group no-gutters">
                                        <?php 
                                        $featured_products = $this->get_products_by_category($settings['featured_category'], $settings['products_per_tab']);
                                        if (!empty($featured_products)) :
                                            foreach ($featured_products as $index => $product) :
                                                $product_id = $product->get_id();
                                                $product_url = get_permalink($product_id);
                                                $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
                                                $product_image_url = $product_image ? $product_image[0] : \Elementor\Utils::get_placeholder_image_src();
                                                $product_categories = get_the_terms($product_id, 'product_cat');
                                                $category_name = $product_categories ? $product_categories[0]->name : 'Product';
                                                $price_html = $product->get_price_html();
                                                $rating = $product->get_average_rating();
                                        ?>
                                            <li class="col-6 col-wd-3 col-md-4 product-item <?php echo ($index == 2 || $index == 3) ? 'remove-divider-xl' : ''; ?> <?php echo ($index == 3) ? 'remove-divider-wd' : ''; ?> <?php echo ($index >= 6) ? 'd-xl-none d-wd-block' : ''; ?>">
                                                <div class="product-item__outer h-100">
                                                    <div class="product-item__inner px-xl-4 p-3">
                                                        <div class="product-item__body pb-xl-2">
                                                            <div class="mb-2"><a href="<?php echo esc_url($product_url); ?>" class="font-size-12 text-gray-5"><?php echo esc_html($category_name); ?></a></div>
                                                            <h5 class="mb-1 product-item__title"><a href="<?php echo esc_url($product_url); ?>" class="text-blue font-weight-bold"><?php echo esc_html($product->get_name()); ?></a></h5>
                                                            <div class="mb-2">
                                                                <a href="<?php echo esc_url($product_url); ?>" class="d-block text-center">
                                                                    <div class="ratio ratio-1x1">
                                                                        <img class="img-fluid" src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" width="300" height="300">
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="flex-center-between mb-1">
                                                                <div class="prodcut-price">
                                                                    <div class="text-gray-100"><?php echo wp_kses_post($price_html); ?></div>
                                                                </div>
                                                                <div class="d-none d-xl-block prodcut-add-cart">
                                                                    <a href="<?php echo esc_url($product_url); ?>" class="btn-add-cart btn-primary transition-3d-hover"><i class="ec ec-add-to-cart"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="product-item__footer">
                                                            <div class="border-top pt-2 flex-center-between flex-wrap">
                                                                <a href="#" class="text-gray-6 font-size-13"><i class="ec ec-compare mr-1 font-size-15"></i> Compare</a>
                                                                <a href="#" class="text-gray-6 font-size-13"><i class="ec ec-favorites mr-1 font-size-15"></i> Add to Wishlist</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php 
                                            endforeach;
                                        else :
                                        ?>
                                            <li class="col-12">
                                                <div class="text-center py-4">
                                                    <p class="text-gray-5">No se encontraron productos en esta categoría.</p>
                                                </div>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <!-- On Sale Tab -->
                                <div class="tab-pane fade pt-2" id="pills-two-example1" role="tabpanel" aria-labelledby="pills-two-example1-tab">
                                    <ul class="row list-unstyled products-group no-gutters">
                                        <?php 
                                        $onsale_products = $this->get_onsale_products($settings['onsale_category'], $settings['products_per_tab']);
                                        if (!empty($onsale_products)) :
                                            foreach ($onsale_products as $index => $product) :
                                                $product_id = $product->get_id();
                                                $product_url = get_permalink($product_id);
                                                $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
                                                $product_image_url = $product_image ? $product_image[0] : \Elementor\Utils::get_placeholder_image_src();
                                                $product_categories = get_the_terms($product_id, 'product_cat');
                                                $category_name = $product_categories ? $product_categories[0]->name : 'Product';
                                                $price_html = $product->get_price_html();
                                        ?>
                                            <li class="col-6 col-wd-3 col-md-4 product-item <?php echo ($index == 2 || $index == 3) ? 'remove-divider-xl' : ''; ?> <?php echo ($index == 3) ? 'remove-divider-wd' : ''; ?> <?php echo ($index >= 6) ? 'd-xl-none d-wd-block' : ''; ?>">
                                                <div class="product-item__outer h-100">
                                                    <div class="product-item__inner px-xl-4 p-3">
                                                        <div class="product-item__body pb-xl-2">
                                                            <div class="mb-2"><a href="<?php echo esc_url($product_url); ?>" class="font-size-12 text-gray-5"><?php echo esc_html($category_name); ?></a></div>
                                                            <h5 class="mb-1 product-item__title"><a href="<?php echo esc_url($product_url); ?>" class="text-blue font-weight-bold"><?php echo esc_html($product->get_name()); ?></a></h5>
                                                            <div class="mb-2">
                                                                <a href="<?php echo esc_url($product_url); ?>" class="d-block text-center">
                                                                    <div class="ratio ratio-1x1">
                                                                        <img class="img-fluid" src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" width="300" height="300">
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="flex-center-between mb-1">
                                                                <div class="prodcut-price d-flex align-items-center flex-wrap position-relative">
                                                                    <?php echo wp_kses_post($price_html); ?>
                                                                </div>
                                                                <div class="d-none d-xl-block prodcut-add-cart">
                                                                    <a href="<?php echo esc_url($product_url); ?>" class="btn-add-cart btn-primary transition-3d-hover"><i class="ec ec-add-to-cart"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="product-item__footer">
                                                            <div class="border-top pt-2 flex-center-between flex-wrap">
                                                                <a href="#" class="text-gray-6 font-size-13"><i class="ec ec-compare mr-1 font-size-15"></i> Compare</a>
                                                                <a href="#" class="text-gray-6 font-size-13"><i class="ec ec-favorites mr-1 font-size-15"></i> Add to Wishlist</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php 
                                            endforeach;
                                        else :
                                        ?>
                                            <li class="col-12">
                                                <div class="text-center py-4">
                                                    <p class="text-gray-5">No se encontraron productos en oferta en esta categoría.</p>
                                                </div>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <!-- Top Rated Tab -->
                                <div class="tab-pane fade pt-2" id="pills-three-example1" role="tabpanel" aria-labelledby="pills-three-example1-tab">
                                    <ul class="row list-unstyled products-group no-gutters">
                                        <?php 
                                        $toprated_products = $this->get_top_rated_products($settings['toprated_category'], $settings['products_per_tab']);
                                        if (!empty($toprated_products)) :
                                            foreach ($toprated_products as $index => $product) :
                                                $product_id = $product->get_id();
                                                $product_url = get_permalink($product_id);
                                                $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
                                                $product_image_url = $product_image ? $product_image[0] : \Elementor\Utils::get_placeholder_image_src();
                                                $product_categories = get_the_terms($product_id, 'product_cat');
                                                $category_name = $product_categories ? $product_categories[0]->name : 'Product';
                                                $price_html = $product->get_price_html();
                                                $rating = $product->get_average_rating();
                                        ?>
                                            <li class="col-6 col-wd-3 col-md-4 product-item <?php echo ($index == 2 || $index == 3) ? 'remove-divider-xl' : ''; ?> <?php echo ($index == 3) ? 'remove-divider-wd' : ''; ?> <?php echo ($index >= 6) ? 'd-xl-none d-wd-block' : ''; ?>">
                                                <div class="product-item__outer h-100">
                                                    <div class="product-item__inner px-xl-4 p-3">
                                                        <div class="product-item__body pb-xl-2">
                                                            <div class="mb-2"><a href="<?php echo esc_url($product_url); ?>" class="font-size-12 text-gray-5"><?php echo esc_html($category_name); ?></a></div>
                                                            <h5 class="mb-1 product-item__title"><a href="<?php echo esc_url($product_url); ?>" class="text-blue font-weight-bold"><?php echo esc_html($product->get_name()); ?></a></h5>
                                                            <div class="mb-2">
                                                                <a href="<?php echo esc_url($product_url); ?>" class="d-block text-center">
                                                                    <div class="ratio ratio-1x1">
                                                                        <img class="img-fluid" src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" width="300" height="300">
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="flex-center-between mb-1">
                                                                <div class="prodcut-price">
                                                                    <div class="text-gray-100"><?php echo wp_kses_post($price_html); ?></div>
                                                                </div>
                                                                <div class="d-none d-xl-block prodcut-add-cart">
                                                                    <a href="<?php echo esc_url($product_url); ?>" class="btn-add-cart btn-primary transition-3d-hover"><i class="ec ec-add-to-cart"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="product-item__footer">
                                                            <div class="border-top pt-2 flex-center-between flex-wrap">
                                                                <a href="#" class="text-gray-6 font-size-13"><i class="ec ec-compare mr-1 font-size-15"></i> Compare</a>
                                                                <a href="#" class="text-gray-6 font-size-13"><i class="ec ec-favorites mr-1 font-size-15"></i> Add to Wishlist</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php 
                                            endforeach;
                                        else :
                                        ?>
                                            <li class="col-12">
                                                <div class="text-center py-4">
                                                    <p class="text-gray-5">No se encontraron productos mejor valorados en esta categoría.</p>
                                                </div>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>


                            </div>
                            <!-- End Tab Content -->
                        </div>
                        <!-- End Features Section -->
                    </div>
                    <!-- End Tab Product -->
                </div>
            </div>
            <!-- End Deals-and-tabs -->
        </div>
        <?php
    }

    /**
     * Renderizar widget en modo de edición
     */
    protected function content_template() {
        ?>
        <div class="container">
            <!-- Banner -->
            <div class="mb-5">
                <div class="row">
                    <# if (settings.banners && settings.banners.length > 0) { #>
                        <# _.each(settings.banners, function(banner) { #>
                            <div class="col-md-6 mb-4 mb-xl-0 col-xl-3">
                                <a href="#" class="d-black text-gray-90">
                                    <div class="min-height-132 py-1 d-flex bg-gray-1 align-items-center">
                                        <div class="col-6 col-xl-5 col-wd-6 pr-0">
                                            <img class="img-fluid" src="{{{ banner.banner_image.url }}}" alt="Image Description">
                                        </div>
                                        <div class="col-6 col-xl-7 col-wd-6">
                                            <div class="mb-2 pb-1 font-size-18 font-weight-light text-ls-n1 text-lh-23">
                                                {{{ banner.banner_title }}}
                                            </div>
                                            <div class="link text-gray-90 font-weight-bold font-size-15" href="#">
                                                Shop now
                                                <span class="link__icon ml-1">
                                                    <span class="link__icon-inner"><i class="ec ec-arrow-right-categproes"></i></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <# }); #>
                    <# } #>
                </div>
            </div>
            <!-- End Banner -->

            <!-- Deals-and-tabs -->
            <div class="mb-5">
                <div class="row">
                    <!-- Deal -->
                    <div class="col-md-auto mb-6 mb-md-0">
                        <div class="p-3 border border-width-2 border-primary borders-radius-20 bg-white min-width-370">
                            <div class="d-flex justify-content-between align-items-center m-1 ml-2">
                                <h3 class="font-size-22 mb-0 font-weight-normal text-lh-28 max-width-120">{{{ settings.offer_title }}}</h3>
                                <div class="d-flex align-items-center flex-column justify-content-center bg-primary rounded-pill height-75 width-75 text-lh-1">
                                    <span class="font-size-12">Save</span>
                                    <div class="font-size-20 font-weight-bold">${{{ settings.save_amount }}}</div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <a href="#" class="d-block text-center"><img class="img-fluid" src="{{{ settings.product_image.url }}}" alt="Image Description"></a>
                            </div>
                            <h5 class="mb-2 font-size-14 text-center mx-auto max-width-180 text-lh-18"><a href="#" class="text-blue font-weight-bold">{{{ settings.product_title }}}</a></h5>
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <del class="font-size-18 mr-2 text-gray-2">${{{ settings.old_price }}}</del>
                                <ins class="font-size-30 text-red text-decoration-none">${{{ settings.new_price }}}</ins>
                            </div>
                            <div class="mb-3 mx-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="">Disponibles: <strong>{{{ settings.available_quantity }}}</strong></span>
                                    <span class="">Ya vendido: <strong>{{{ settings.sold_quantity }}}</strong></span>
                                </div>
                                <div class="rounded-pill bg-gray-3 height-20 position-relative">
                                    <span class="position-absolute left-0 top-0 bottom-0 rounded-pill w-30 bg-primary"></span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <h6 class="font-size-15 text-gray-2 text-center mb-3">¡Date prisa! La oferta termina en:</h6>
                                <div class="js-countdown d-flex justify-content-center" data-end-date="{{{ settings.countdown_date }}}" data-hours-format="%H" data-minutes-format="%M" data-seconds-format="%S">
                                    <div class="text-lh-1">
                                        <div class="text-gray-2 font-size-30 bg-gray-4 py-2 px-2 rounded-sm mb-2">
                                            <span class="js-cd-hours">00</span>
                                        </div>
                                        <div class="text-gray-2 font-size-12 text-center">HOURS</div>
                                    </div>
                                    <div class="mx-1 pt-1 text-gray-2 font-size-24">:</div>
                                    <div class="text-lh-1">
                                        <div class="text-gray-2 font-size-30 bg-gray-4 py-2 px-2 rounded-sm mb-2">
                                            <span class="js-cd-minutes">00</span>
                                        </div>
                                        <div class="text-gray-2 font-size-12 text-center">MINS</div>
                                    </div>
                                    <div class="mx-1 pt-1 text-gray-2 font-size-24">:</div>
                                    <div class="text-lh-1">
                                        <div class="text-gray-2 font-size-30 bg-gray-4 py-2 px-2 rounded-sm mb-2">
                                            <span class="js-cd-seconds">00</span>
                                        </div>
                                        <div class="text-gray-2 font-size-12 text-center">SECS</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Deal -->

                    <!-- Tab Product -->
                    <div class="col">
                        <!-- Features Section -->
                        <div class="">
                            <!-- Nav Classic -->
                            <div class="position-relative bg-white text-center z-index-2">
                                <ul class="nav nav-classic nav-tab justify-content-center" id="pills-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="pills-one-example1-tab" data-toggle="pill" href="#pills-one-example1" role="tab" aria-controls="pills-one-example1" aria-selected="true">
                                            <div class="d-md-flex justify-content-md-center align-items-md-center">
                                                {{{ settings.featured_tab_title }}}
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-two-example1-tab" data-toggle="pill" href="#pills-two-example1" role="tab" aria-controls="pills-two-example1" aria-selected="false">
                                            <div class="d-md-flex justify-content-md-center align-items-md-center">
                                                {{{ settings.onsale_tab_title }}}
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-three-example1-tab" data-toggle="pill" href="#pills-three-example1" role="tab" aria-controls="pills-three-example1" aria-selected="false">
                                            <div class="d-md-flex justify-content-md-center align-items-md-center">
                                                {{{ settings.toprated_tab_title }}}
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <!-- End Nav Classic -->

                            <!-- Tab Content -->
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade pt-2 show active" id="pills-one-example1" role="tabpanel" aria-labelledby="pills-one-example1-tab">
                                    <ul class="row list-unstyled products-group no-gutters">
                                        <li class="col-6 col-wd-3 col-md-4 product-item">
                                            <div class="product-item__outer h-100">
                                                <div class="product-item__inner px-xl-4 p-3">
                                                    <div class="product-item__body pb-xl-2">
                                                        <div class="mb-2"><a href="#" class="font-size-12 text-gray-5">Categoría</a></div>
                                                        <h5 class="mb-1 product-item__title"><a href="#" class="text-blue font-weight-bold">Producto de Ejemplo</a></h5>
                                                        <div class="mb-2">
                                                            <a href="#" class="d-block text-center">
                                                                <div class="ratio ratio-1x1">
                                                                    <img class="img-fluid" src="{{{ settings.product_image.url }}}" alt="Producto de Ejemplo" width="300" height="300">
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="flex-center-between mb-1">
                                                            <div class="prodcut-price">
                                                                <div class="text-gray-100">$99,00</div>
                                                            </div>
                                                            <div class="d-none d-xl-block prodcut-add-cart">
                                                                <a href="#" class="btn-add-cart btn-primary transition-3d-hover"><i class="ec ec-add-to-cart"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="product-item__footer">
                                                        <div class="border-top pt-2 flex-center-between flex-wrap">
                                                            <a href="#" class="text-gray-6 font-size-13"><i class="ec ec-compare mr-1 font-size-15"></i> Compare</a>
                                                            <a href="#" class="text-gray-6 font-size-13"><i class="ec ec-favorites mr-1 font-size-15"></i> Add to Wishlist</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="col-12">
                                            <div class="text-center py-4">
                                                <p class="text-gray-5">En el modo de edición se muestra un producto de ejemplo. Los productos reales aparecerán en el frontend.</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-pane fade pt-2" id="pills-two-example1" role="tabpanel" aria-labelledby="pills-two-example1-tab">
                                    <!-- Contenido de On Sale -->
                                </div>
                                <div class="tab-pane fade pt-2" id="pills-three-example1" role="tabpanel" aria-labelledby="pills-three-example1-tab">
                                    <!-- Contenido de Top Rated -->
                                </div>

                            </div>
                            <!-- End Tab Content -->
                        </div>
                        <!-- End Features Section -->
                    </div>
                    <!-- End Tab Product -->
                </div>
            </div>
            <!-- End Deals-and-tabs -->
        </div>
        <?php
    }
}
