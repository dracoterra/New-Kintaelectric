<?php
/**
 * Prueba Simple de CSS - WooCommerce Venezuela Pro
 * Página simple para verificar que el CSS funcione
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class WVP_Simple_CSS_Test {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_simple_css_test_page'));
    }
    
    /**
     * Agregar página de prueba simple
     */
    public function add_simple_css_test_page() {
        add_submenu_page(
            'wvp-dashboard',
            'Prueba CSS Simple',
            'Prueba CSS Simple',
            'manage_woocommerce',
            'wvp-simple-css-test',
            array($this, 'display_simple_css_test_page')
        );
    }
    
    /**
     * Mostrar página de prueba simple
     */
    public function display_simple_css_test_page() {
        ?>
        <div class="wrap">
            <h1>Prueba CSS Simple - WooCommerce Venezuela Pro</h1>
            
            <div class="notice notice-info">
                <p><strong>Información:</strong> Esta página muestra cómo se ven los productos con el CSS aplicado.</p>
            </div>
            
            <h2>Estado del Sistema</h2>
            <div class="wvp-system-status">
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Componente</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Generador CSS Dinámico</td>
                            <td><?php echo class_exists('WVP_Dynamic_CSS_Generator') ? '<span style="color: green;">✅ Activo</span>' : '<span style="color: red;">❌ Inactivo</span>'; ?></td>
                        </tr>
                        <tr>
                            <td>Sistema CSS Básico</td>
                            <td><?php echo class_exists('WVP_Basic_CSS') ? '<span style="color: green;">✅ Activo</span>' : '<span style="color: red;">❌ Inactivo</span>'; ?></td>
                        </tr>
                        <tr>
                            <td>Gestor de Productos</td>
                            <td><?php echo class_exists('WVP_Product_Display_Manager') ? '<span style="color: green;">✅ Activo</span>' : '<span style="color: red;">❌ Inactivo</span>'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <h2>Configuración Actual</h2>
            <div class="wvp-current-config">
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Configuración</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Estilo de Visualización</td>
                            <td><?php echo esc_html(get_option('wvp_display_style', 'minimal')); ?></td>
                        </tr>
                        <tr>
                            <td>Color Primario</td>
                            <td><?php echo esc_html(get_option('wvp_primary_color', '#007cba')); ?></td>
                        </tr>
                        <tr>
                            <td>Color Secundario</td>
                            <td><?php echo esc_html(get_option('wvp_secondary_color', '#005a87')); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <h2>Vista Previa de Productos</h2>
            <div class="wvp-product-preview">
                <div class="wvp-preview-grid">
                    <div class="wvp-preview-item">
                        <h4>Producto 1</h4>
                        <div class="wvp-product-container">
                            <div class="wvp-product-price-container">
                                <div class="wvp-price-usd">$18,00 USD</div>
                                <div class="wvp-price-ves">Bs. 450,00 VES</div>
                                <div class="wvp-price-conversion">
                                    <div class="wvp-rate-info">Tasa BCV: 25.00 Bs/USD</div>
                                </div>
                                <div class="wvp-currency-switcher">
                                    <button class="wvp-currency-option active" data-currency="usd">USD</button>
                                    <button class="wvp-currency-option" data-currency="ves">VES</button>
                                </div>
                            </div>
                            <div class="wvp-product-title">Producto de Prueba</div>
                            <div class="wvp-product-category">Categoría</div>
                            <div class="wvp-product-actions">
                                <a href="#" class="wvp-wishlist">❤️ Wishlist</a>
                                <a href="#" class="wvp-compare">Compare</a>
                            </div>
                            <button class="wvp-add-to-cart">🛒</button>
                        </div>
                    </div>
                    
                    <div class="wvp-preview-item">
                        <h4>Producto 2</h4>
                        <div class="wvp-product-container">
                            <div class="wvp-product-price-container">
                                <div class="wvp-price-usd">$25,00 USD</div>
                                <div class="wvp-price-ves">Bs. 625,00 VES</div>
                                <div class="wvp-price-conversion">
                                    <div class="wvp-rate-info">Tasa BCV: 25.00 Bs/USD</div>
                                </div>
                                <div class="wvp-currency-switcher">
                                    <button class="wvp-currency-option" data-currency="usd">USD</button>
                                    <button class="wvp-currency-option active" data-currency="ves">VES</button>
                                </div>
                            </div>
                            <div class="wvp-product-title">Otro Producto</div>
                            <div class="wvp-product-category">Otra Categoría</div>
                            <div class="wvp-product-actions">
                                <a href="#" class="wvp-wishlist">❤️ Wishlist</a>
                                <a href="#" class="wvp-compare">Compare</a>
                            </div>
                            <button class="wvp-add-to-cart">🛒</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <h2>Enlaces de Prueba</h2>
            <div class="wvp-test-links">
                <p>
                    <a href="<?php echo home_url('/shop/'); ?>" target="_blank" class="button button-primary">
                        Ver Tienda en Nueva Pestaña
                    </a>
                    <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-secondary">
                        Ver Sitio Principal
                    </a>
                </p>
            </div>
            
            <style>
            .wvp-preview-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            
            .wvp-preview-item {
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 15px;
                background: #f9f9f9;
            }
            
            .wvp-preview-item h4 {
                margin: 0 0 15px 0;
                color: #333;
            }
            
            .wvp-product-container {
                background: #fff;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                padding: 15px;
                margin: 10px 0;
            }
            
            .wvp-price-usd,
            .wvp-price-ves {
                font-size: 18px;
                font-weight: bold;
                margin: 5px 0;
            }
            
            .wvp-price-conversion {
                background: #f0f0f0;
                padding: 8px;
                border-radius: 3px;
                margin: 10px 0;
                font-size: 12px;
            }
            
            .wvp-currency-switcher {
                display: flex;
                gap: 8px;
                margin: 10px 0;
            }
            
            .wvp-currency-switcher button {
                padding: 5px 10px;
                border: 1px solid #ddd;
                background: #fff;
                border-radius: 3px;
                cursor: pointer;
            }
            
            .wvp-currency-switcher button.active {
                background: #007cba;
                color: #fff;
                border-color: #007cba;
            }
            
            .wvp-add-to-cart {
                background: #007cba;
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                color: #fff;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .wvp-product-title {
                font-size: 14px;
                font-weight: 500;
                margin: 8px 0;
            }
            
            .wvp-product-category {
                color: #999;
                font-size: 12px;
                text-transform: uppercase;
                margin: 5px 0;
            }
            
            .wvp-product-actions {
                display: flex;
                gap: 10px;
                margin: 10px 0;
                color: #999;
                font-size: 12px;
            }
            
            .wvp-product-actions a {
                color: #999;
                text-decoration: none;
            }
            
            .wvp-product-actions a:hover {
                color: #007cba;
            }
            </style>
        </div>
        <?php
    }
}

// Inicializar solo en admin
if (is_admin()) {
    new WVP_Simple_CSS_Test();
}
?>
