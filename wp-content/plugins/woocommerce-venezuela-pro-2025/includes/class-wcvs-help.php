<?php
/**
 * Sistema de ayuda del plugin WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar el sistema de ayuda
 */
class WCVS_Help {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_help_menu'));
        add_action('contextual_help', array($this, 'add_contextual_help'));
    }

    /**
     * Añadir menú de ayuda
     */
    public function add_help_menu() {
        add_submenu_page(
            'wcvs-dashboard',
            'Ayuda',
            'Ayuda',
            'manage_options',
            'wcvs-help',
            array($this, 'render_help_page')
        );
    }

    /**
     * Añadir ayuda contextual
     */
    public function add_contextual_help() {
        $screen = get_current_screen();
        
        if (strpos($screen->id, 'wcvs-') !== false) {
            $screen->add_help_tab(array(
                'id' => 'wcvs-general-help',
                'title' => 'Ayuda General',
                'content' => $this->get_general_help_content()
            ));

            $screen->add_help_tab(array(
                'id' => 'wcvs-contact-help',
                'title' => 'Contacto',
                'content' => $this->get_contact_help_content()
            ));
        }
    }

    /**
     * Renderizar página de ayuda
     */
    public function render_help_page() {
        ?>
        <div class="wrap wcvs-help">
            <h1>📚 Ayuda - WooCommerce Venezuela Suite</h1>
            
            <div class="wcvs-help-content">
                <h2>Guías Rápidas</h2>
                <div class="wcvs-help-grid">
                    <div class="wcvs-help-card">
                        <h3>🚀 Configuración Inicial</h3>
                        <p>Configura tu tienda para Venezuela en minutos</p>
                        <a href="#" class="button">Ver Guía</a>
                    </div>
                    
                    <div class="wcvs-help-card">
                        <h3>💰 Configuración de Moneda</h3>
                        <p>Aprende a configurar la conversión automática USD a VES</p>
                        <a href="#" class="button">Ver Guía</a>
                    </div>
                    
                    <div class="wcvs-help-card">
                        <h3>💳 Pasarelas de Pago</h3>
                        <p>Configura las pasarelas de pago locales</p>
                        <a href="#" class="button">Ver Guía</a>
                    </div>
                    
                    <div class="wcvs-help-card">
                        <h3>🚚 Métodos de Envío</h3>
                        <p>Configura los métodos de envío venezolanos</p>
                        <a href="#" class="button">Ver Guía</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Obtener contenido de ayuda general
     *
     * @return string
     */
    private function get_general_help_content() {
        return '<p>WooCommerce Venezuela Suite es un plugin completo para localizar tu tienda WooCommerce al mercado venezolano.</p>';
    }

    /**
     * Obtener contenido de ayuda de contacto
     *
     * @return string
     */
    private function get_contact_help_content() {
        return '<p>Para soporte técnico, contacta a: soporte@kinta-electric.com</p>';
    }
}
