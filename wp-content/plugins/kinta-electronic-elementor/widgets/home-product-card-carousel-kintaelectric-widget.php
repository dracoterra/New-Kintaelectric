<?php
/**
 * Widget Home Product Card Carousel Kintaelectric para Elementor
 * 
 * @package KintaElectricElementor
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Home Product Card Carousel Kintaelectric
 */
class KEE_Home_Product_Card_Carousel_Kintaelectric_Widget extends \Elementor\Widget_Base {

    /**
     * Obtener nombre del widget
     */
    public function get_name() {
        return 'home_product_card_carousel_kintaelectric';
    }

    /**
     * Obtener título del widget
     */
    public function get_title() {
        return esc_html__('Home Product Card Carousel Kintaelectric', 'kinta-electric-elementor');
    }

    /**
     * Obtener icono del widget
     */
    public function get_icon() {
        return 'eicon-carousel';
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
        return ['product', 'carousel', 'card', 'kintaelectric', 'slider', 'woocommerce'];
    }

    /**
     * Obtener dependencias de scripts
     */
    public function get_script_depends() {
        return ['kinta-electric-elementor-script', 'slick-carousel'];
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
                'default' => esc_html__('Bestsellers', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'products_per_slide',
            [
                'label' => esc_html__('Productos por Slide', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 12,
            ]
        );

        $this->end_controls_section();

        // Sección de Banner
        $this->start_controls_section(
            'section_banner',
            [
                'label' => esc_html__('Banner Completo', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'banner_title',
            [
                'label' => esc_html__('Título del Banner', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('COMPRA Y AHORRA A LO GRANDE EN LAS TABLETAS MÁS POPULARES', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'banner_subtitle',
            [
                'label' => esc_html__('Subtítulo del Banner', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('A PARTIR DE', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'banner_price',
            [
                'label' => esc_html__('Precio del Banner', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('79.99', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'banner_image',
            [
                'label' => esc_html__('Imagen de Fondo', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'banner_link',
            [
                'label' => esc_html__('Enlace del Banner', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'default' => [
                    'url' => '#',
                ],
            ]
        );

        $this->end_controls_section();

        // Sección de Productos Vistos Recientemente
        $this->start_controls_section(
            'section_recently_viewed',
            [
                'label' => esc_html__('Productos Vistos Recientemente', 'kinta-electric-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'recently_viewed_title',
            [
                'label' => esc_html__('Título de la Sección', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Vistos recientemente', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'recently_viewed_products',
            [
                'label' => esc_html__('Número de Productos', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 7,
                'min' => 1,
                'max' => 12,
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
                'default' => esc_html__('Top 20', 'kinta-electric-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'tab_type',
            [
                'label' => esc_html__('Tipo de Pestaña', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'button',
                'options' => [
                    'button' => esc_html__('Botón (Top 20)', 'kinta-electric-elementor'),
                    'link' => esc_html__('Enlace (Categoría)', 'kinta-electric-elementor'),
                ],
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
                'condition' => [
                    'tab_type' => 'link',
                ],
                'description' => esc_html__('Selecciona una categoría para esta pestaña.', 'kinta-electric-elementor'),
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

        $this->add_control(
            'tabs',
            [
                'label' => esc_html__('Pestañas', 'kinta-electric-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'tab_title' => esc_html__('Top 20', 'kinta-electric-elementor'),
                        'tab_type' => 'button',
                        'tab_category' => '',
                        'tab_orderby' => 'date',
                        'tab_order' => 'DESC',
                    ],
                    [
                        'tab_title' => esc_html__('Phones & Tablets', 'kinta-electric-elementor'),
                        'tab_type' => 'link',
                        'tab_category' => '',
                        'tab_orderby' => 'date',
                        'tab_order' => 'DESC',
                    ],
                    [
                        'tab_title' => esc_html__('Laptops & Computers', 'kinta-electric-elementor'),
                        'tab_type' => 'link',
                        'tab_category' => '',
                        'tab_orderby' => 'date',
                        'tab_order' => 'DESC',
                    ],
                    [
                        'tab_title' => esc_html__('Video Cameras', 'kinta-electric-elementor'),
                        'tab_type' => 'link',
                        'tab_category' => '',
                        'tab_orderby' => 'date',
                        'tab_order' => 'DESC',
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
     * Obtener productos vistos recientemente
     */
    private function get_recently_viewed_products($limit = 7) {
        if (!class_exists('WooCommerce')) {
            return [];
        }
        
        // Obtener productos recientes como fallback
        $args = [
            'post_type' => 'product',
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => [
                [
                    'key' => '_visibility',
                    'value' => ['catalog', 'visible'],
                    'compare' => 'IN'
                ]
            ]
        ];
        
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
        <div class="container">
            <!-- Prodcut-cards-carousel -->
            <div class="space-top-2">
                <div class="d-flex justify-content-between border-bottom border-color-1 flex-md-nowrap flex-wrap border-sm-bottom-0">
                    <h3 class="section-title mb-0 pb-2 font-size-22"><?php echo esc_html($settings['section_title']); ?></h3>
                    <ul class="nav nav-pills mb-2 pt-3 pt-md-0 mb-0 border-top border-color-1 border-md-top-0 align-items-center font-size-15 font-size-15-md flex-nowrap flex-md-wrap overflow-auto overflow-md-visble" id="product-carousel-tabs-<?php echo esc_attr($this->get_id()); ?>">
                        <?php foreach ($settings['tabs'] as $index => $tab) : ?>
                            <li class="nav-item flex-shrink-0 flex-md-shrink-1">
                                <?php if ($tab['tab_type'] === 'button') : ?>
                                    <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?> text-gray-90 btn btn-outline-primary border-width-2 rounded-pill py-1 px-4 font-size-15 text-lh-19 font-size-15-md" 
                                       href="javascript:void(0);" 
                                       data-slide="<?php echo esc_attr($index); ?>"
                                       data-target="#product-carousel-<?php echo esc_attr($this->get_id()); ?>">
                                        <?php echo esc_html($tab['tab_title']); ?>
                                    </a>
                                <?php else : ?>
                                    <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?> text-gray-8" 
                                       href="javascript:void(0);" 
                                       data-slide="<?php echo esc_attr($index); ?>"
                                       data-target="#product-carousel-<?php echo esc_attr($this->get_id()); ?>">
                                        <?php echo esc_html($tab['tab_title']); ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="js-slick-carousel u-slick u-slick--gutters-2 overflow-hidden u-slick-overflow-visble pt-3 pb-6" 
                     id="product-carousel-<?php echo esc_attr($this->get_id()); ?>"
                     data-pagi-classes="text-center right-0 bottom-1 left-0 u-slick__pagination u-slick__pagination--long mb-0 z-index-n1 mt-4">
                    
                    <?php foreach ($settings['tabs'] as $index => $tab) : ?>
                        <div class="js-slide">
                            <ul class="row list-unstyled products-group no-gutters mb-0 overflow-visible">
                                <?php 
                                $products = $this->get_products_for_tab(
                                    $tab['tab_category'], 
                                    $tab['tab_orderby'], 
                                    $tab['tab_order'], 
                                    $settings['products_per_slide']
                                );
                                
                                if (!empty($products)) :
                                    foreach ($products as $product_index => $product) :
                                        $this->render_product_card($product, $product_index, count($products));
                                    endforeach;
                                else :
                                ?>
                                    <li class="col-12">
                                        <div class="text-center py-5">
                                            <p class="text-gray-5">No se encontraron productos en esta categoría.</p>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- End Prodcut-cards-carousel -->

            <!-- Full banner -->
            <?php if (!empty($settings['banner_title']) || !empty($settings['banner_image']['url'])) : ?>
            <div class="mb-6">
                <a href="<?php echo esc_url($settings['banner_link']['url']); ?>" class="d-block text-gray-90">
                    <div class="" style="background-image: url(<?php echo esc_url($settings['banner_image']['url']); ?>);">
                        <div class="space-top-2-md p-4 pt-6 pt-md-8 pt-lg-6 pt-xl-8 pb-lg-4 px-xl-8 px-lg-6">
                            <div class="flex-horizontal-center mt-lg-3 mt-xl-0 overflow-auto overflow-md-visble">
                                <h1 class="text-lh-38 font-size-32 font-weight-light mb-0 flex-shrink-0 flex-md-shrink-1"><?php echo esc_html($settings['banner_title']); ?></h1>
                                <div class="ml-5 flex-content-center flex-shrink-0">
                                    <div class="bg-primary rounded-lg px-6 py-2">
                                        <em class="font-size-14 font-weight-light"><?php echo esc_html($settings['banner_subtitle']); ?></em>
                                        <div class="font-size-30 font-weight-bold text-lh-1">
                                            <sup class="">$</sup><?php echo esc_html($settings['banner_price']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
            <!-- End Full banner -->

            <!-- Recently viewed -->
            <?php if (!empty($settings['recently_viewed_title'])) : ?>
            <div class="mb-6">
                <div class="position-relative">
                    <div class="border-bottom border-color-1 mb-2">
                        <h3 class="section-title mb-0 pb-2 font-size-22"><?php echo esc_html($settings['recently_viewed_title']); ?></h3>
                    </div>
                    <div class="js-slick-carousel u-slick position-static overflow-hidden u-slick-overflow-visble pb-7 pt-2 px-1" 
                         data-pagi-classes="text-center right-0 bottom-1 left-0 u-slick__pagination u-slick__pagination--long mb-0 z-index-n1 mt-3 mt-md-0" 
                         data-slides-show="7" 
                         data-slides-scroll="1" 
                         data-arrows-classes="position-absolute top-0 font-size-17 u-slick__arrow-normal top-10" 
                         data-arrow-left-classes="fa fa-angle-left right-1" 
                         data-arrow-right-classes="fa fa-angle-right right-0" 
                         data-responsive='[{
                             "breakpoint": 1400,
                             "settings": {
                                 "slidesToShow": 6
                             }
                         }, {
                             "breakpoint": 1200,
                             "settings": {
                                 "slidesToShow": 4
                             }
                         }, {
                             "breakpoint": 992,
                             "settings": {
                                 "slidesToShow": 3
                             }
                         }, {
                             "breakpoint": 768,
                             "settings": {
                                 "slidesToShow": 2
                             }
                         }, {
                             "breakpoint": 554,
                             "settings": {
                                 "slidesToShow": 2
                             }
                         }]'>
                        
                        <?php 
                        $recently_viewed_products = $this->get_recently_viewed_products($settings['recently_viewed_products']);
                        if (!empty($recently_viewed_products)) :
                            foreach ($recently_viewed_products as $product) :
                        ?>
                        <div class="js-slide products-group">
                            <div class="product-item">
                                <div class="product-item__outer h-100">
                                    <div class="product-item__inner px-wd-4 p-2 p-md-3">
                                        <div class="product-item__body pb-xl-2">
                                            <div class="mb-2">
                                                <a href="<?php echo esc_url(get_term_link(get_the_terms($product->get_id(), 'product_cat')[0]->term_id, 'product_cat')); ?>" class="font-size-12 text-gray-5">
                                                    <?php echo esc_html(get_the_terms($product->get_id(), 'product_cat')[0]->name ?? 'Product'); ?>
                                                </a>
                                            </div>
                                            <h5 class="mb-1 product-item__title">
                                                <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>" class="text-blue font-weight-bold">
                                                    <?php echo esc_html($product->get_name()); ?>
                                                </a>
                                            </h5>
                                            <div class="mb-2">
                                                <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>" class="d-block text-center">
                                                    <img class="img-fluid" src="<?php echo esc_url(wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'medium')[0] ?? \Elementor\Utils::get_placeholder_image_src()); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                                                </a>
                                            </div>
                                            <div class="flex-center-between mb-1">
                                                <div class="prodcut-price">
                                                    <div class="text-gray-100"><?php echo wp_kses_post($product->get_price_html()); ?></div>
                                                </div>
                                                <div class="d-none d-xl-block prodcut-add-cart">
                                                    <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>" class="btn-add-cart btn-primary transition-3d-hover">
                                                        <i class="ec ec-add-to-cart"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="product-item__footer">
                                            <div class="border-top pt-2 flex-center-between flex-wrap">
                                                <a href="#" class="text-gray-6 font-size-13">
                                                    <i class="ec ec-compare mr-1 font-size-15"></i> Compare
                                                </a>
                                                <a href="#" class="text-gray-6 font-size-13">
                                                    <i class="ec ec-favorites mr-1 font-size-15"></i> Wishlist
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <!-- End Recently viewed -->
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var carouselId = '<?php echo esc_js($this->get_id()); ?>';
            var $carousel = $('#product-carousel-' + carouselId);
            var $tabs = $('#product-carousel-tabs-' + carouselId + ' .nav-link');
            
            // Esperar a que Slick se inicialice automáticamente por el tema
            setTimeout(function() {
                // Manejar clicks en las pestañas
                $tabs.on('click', function(e) {
                    e.preventDefault();
                    
                    var slideIndex = $(this).data('slide');
                    
                    // Remover clase active de todas las pestañas
                    $tabs.removeClass('active');
                    
                    // Agregar clase active a la pestaña clickeada
                    $(this).addClass('active');
                    
                    // Cambiar al slide correspondiente si Slick está inicializado
                    if ($carousel.hasClass('slick-initialized')) {
                        $carousel.slick('slickGoTo', slideIndex);
                    }
                });
            }, 1000);
        });
        </script>
        <?php
    }

    /**
     * Renderizar tarjeta de producto
     */
    private function render_product_card($product, $index, $total_products) {
        $product_id = $product->get_id();
        $product_url = get_permalink($product_id);
        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
        $product_image_url = $product_image ? $product_image[0] : \Elementor\Utils::get_placeholder_image_src();
        $product_categories = get_the_terms($product_id, 'product_cat');
        $category_name = $product_categories ? $product_categories[0]->name : 'Product';
        $price_html = $product->get_price_html();
        
        // Determinar clases CSS para divisores
        $divider_classes = '';
        if ($index === $total_products - 1) {
            $divider_classes = 'remove-divider-xl';
        } elseif ($index === $total_products - 2) {
            $divider_classes = 'remove-divider-wd';
        }
        
        // Clases de visibilidad responsiva
        $visibility_classes = '';
        if ($index >= 4) {
            $visibility_classes = 'd-none d-md-block';
        }
        if ($index >= 6) {
            $visibility_classes = 'd-none d-wd-block';
        }
        ?>
        <li class="col-wd-3 col-md-4 product-item product-item__card pb-2 mb-2 pb-md-0 mb-md-0 border-bottom border-md-bottom-0 <?php echo esc_attr($divider_classes . ' ' . $visibility_classes); ?>">
            <div class="product-item__outer h-100">
                <div class="product-item__inner p-md-3 row no-gutters">
                    <div class="col col-lg-auto product-media-left">
                        <a href="<?php echo esc_url($product_url); ?>" class="max-width-150 d-block">
                            <img class="img-fluid" src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                        </a>
                    </div>
                    <div class="col product-item__body pl-2 pl-lg-3 mr-xl-2 mr-wd-1">
                        <div class="mb-4">
                            <div class="mb-2">
                                <a href="<?php echo esc_url(get_term_link($product_categories[0]->term_id, 'product_cat')); ?>" class="font-size-12 text-gray-5">
                                    <?php echo esc_html($category_name); ?>
                                </a>
                            </div>
                            <h5 class="product-item__title">
                                <a href="<?php echo esc_url($product_url); ?>" class="text-blue font-weight-bold">
                                    <?php echo esc_html($product->get_name()); ?>
                                </a>
                            </h5>
                        </div>
                        <div class="flex-center-between mb-3">
                            <div class="prodcut-price">
                                <div class="text-gray-100"><?php echo wp_kses_post($price_html); ?></div>
                            </div>
                            <div class="d-none d-xl-block prodcut-add-cart">
                                <a href="<?php echo esc_url($product_url); ?>" class="btn-add-cart btn-primary transition-3d-hover">
                                    <i class="ec ec-add-to-cart"></i>
                                </a>
                            </div>
                        </div>
                        <div class="product-item__footer">
                            <div class="border-top pt-2 flex-center-between flex-wrap">
                                <a href="#" class="text-gray-6 font-size-13">
                                    <i class="ec ec-compare mr-1 font-size-15"></i> Compare
                                </a>
                                <a href="#" class="text-gray-6 font-size-13">
                                    <i class="ec ec-favorites mr-1 font-size-15"></i> Wishlist
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <?php
    }

    /**
     * Renderizar widget en modo de edición
     */
    protected function content_template() {
        ?>
        <div class="container">
            <!-- Prodcut-cards-carousel -->
            <div class="space-top-2">
                <div class="d-flex justify-content-between border-bottom border-color-1 flex-md-nowrap flex-wrap border-sm-bottom-0">
                    <h3 class="section-title mb-0 pb-2 font-size-22">{{{ settings.section_title }}}</h3>
                    <ul class="nav nav-pills mb-2 pt-3 pt-md-0 mb-0 border-top border-color-1 border-md-top-0 align-items-center font-size-15 font-size-15-md flex-nowrap flex-md-wrap overflow-auto overflow-md-visble">
                        <# if (settings.tabs && settings.tabs.length > 0) { #>
                            <# _.each(settings.tabs, function(tab, index) { #>
                                <li class="nav-item flex-shrink-0 flex-md-shrink-1">
                                    <# if (tab.tab_type === 'button') { #>
                                        <a class="text-gray-90 btn btn-outline-primary border-width-2 rounded-pill py-1 px-4 font-size-15 text-lh-19 font-size-15-md" href="javascript:void(0);">
                                            {{{ tab.tab_title }}}
                                        </a>
                                    <# } else { #>
                                        <a class="nav-link text-gray-8" href="javascript:void(0);">
                                            {{{ tab.tab_title }}}
                                        </a>
                                    <# } #>
                                </li>
                            <# }); #>
                        <# } #>
                    </ul>
                </div>
                
                <div class="js-slick-carousel u-slick u-slick--gutters-2 overflow-hidden u-slick-overflow-visble pt-3 pb-6" 
                     data-pagi-classes="text-center right-0 bottom-1 left-0 u-slick__pagination u-slick__pagination--long mb-0 z-index-n1 mt-4">
                    
                    <# if (settings.tabs && settings.tabs.length > 0) { #>
                        <# _.each(settings.tabs, function(tab, index) { #>
                            <div class="js-slide">
                                <div class="text-center py-5">
                                    <p class="text-gray-5">En el modo de edición se muestra la estructura del carrusel. Los productos reales aparecerán en el frontend.</p>
                                    <p class="text-gray-5">Pestaña: {{{ tab.tab_title }}}</p>
                                    <p class="text-gray-5">Tipo: <# if (tab.tab_type === 'button') { #>Botón<# } else { #>Enlace<# } #></p>
                                    <# if (tab.tab_category) { #>
                                        <p class="text-gray-5">Categoría: {{{ tab.tab_category }}}</p>
                                    <# } #>
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <h6 class="text-gray-6">Carrusel de Tarjetas de Productos:</h6>
                                        <ul class="text-left text-gray-5">
                                            <li>• Cada pestaña muestra un slide del carrusel</li>
                                            <li>• Productos en formato de tarjeta horizontal</li>
                                            <li>• Navegación por pestañas en la parte superior</li>
                                            <li>• Responsive con diferentes breakpoints</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <# }); #>
                    <# } #>
                </div>
            </div>
            <!-- End Prodcut-cards-carousel -->

            <!-- Full banner -->
            <# if (settings.banner_title || settings.banner_image.url) { #>
            <div class="mb-6">
                <div class="text-center py-5">
                    <h6 class="text-gray-6">Banner Completo:</h6>
                    <p class="text-gray-5">Título: {{{ settings.banner_title }}}</p>
                    <p class="text-gray-5">Subtítulo: {{{ settings.banner_subtitle }}}</p>
                    <p class="text-gray-5">Precio: ${{{ settings.banner_price }}}</p>
                    <# if (settings.banner_image.url) { #>
                        <p class="text-gray-5">Imagen: Configurada</p>
                    <# } #>
                    <# if (settings.banner_link.url) { #>
                        <p class="text-gray-5">Enlace: {{{ settings.banner_link.url }}}</p>
                    <# } #>
                </div>
            </div>
            <# } #>
            <!-- End Full banner -->

            <!-- Recently viewed -->
            <# if (settings.recently_viewed_title) { #>
            <div class="mb-6">
                <div class="text-center py-5">
                    <h6 class="text-gray-6">Productos Vistos Recientemente:</h6>
                    <p class="text-gray-5">Título: {{{ settings.recently_viewed_title }}}</p>
                    <p class="text-gray-5">Número de productos: {{{ settings.recently_viewed_products }}}</p>
                    <div class="mt-3 p-3 bg-light rounded">
                        <h6 class="text-gray-6">Carrusel de Productos Recientes:</h6>
                        <ul class="text-left text-gray-5">
                            <li>• Muestra productos vistos recientemente</li>
                            <li>• Carrusel horizontal con navegación</li>
                            <li>• Responsive con diferentes breakpoints</li>
                            <li>• Enlaces a productos y categorías</li>
                        </ul>
                    </div>
                </div>
            </div>
            <# } #>
            <!-- End Recently viewed -->
        </div>
        <?php
    }
}
