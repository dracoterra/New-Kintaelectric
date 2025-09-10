<?php
/**
 * Método de envío local por zonas para Venezuela
 * 
 * @package WooCommerce_Venezuela_Pro
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined("ABSPATH")) {
    exit;
}

class WVP_Shipping_Local_Delivery extends WC_Shipping_Method {
    
    /**
     * Zonas de delivery configuradas
     * @var array
     */
    public $zones;
    
    /**
     * Constructor del método de envío
     */
    public function __construct($instance_id = 0) {
        $this->id = "wvp_local_delivery";
        $this->instance_id = absint($instance_id);
        $this->method_title = __("Delivery Local (Venezuela)", "wvp");
        $this->method_description = __("Envío local por zonas dentro de Caracas y Miranda", "wvp");
        $this->supports = array(
            "shipping-zones",
            "instance-settings",
            "instance-settings-modal"
        );
        
        $this->init();
    }
    
    /**
     * Inicializar el método de envío
     */
    public function init() {
        // Cargar configuraciones
        $this->init_form_fields();
        $this->init_settings();
        
        // Obtener configuraciones
        $this->title = $this->get_option("title");
        $this->enabled = $this->get_option("enabled");
        $this->zones = $this->get_option("zones", array());
        
        // Guardar configuraciones
        add_action("woocommerce_update_options_shipping_" . $this->id, array($this, "process_admin_options"));
    }
    
    /**
     * Definir campos de configuración
     */
    public function init_form_fields() {
        $this->form_fields = array(
            "enabled" => array(
                "title" => __("Activar/Desactivar", "wvp"),
                "type" => "checkbox",
                "label" => __("Activar delivery local", "wvp"),
                "default" => "no"
            ),
            "title" => array(
                "title" => __("Título", "wvp"),
                "type" => "text",
                "description" => __("Título que se muestra al cliente", "wvp"),
                "default" => __("Delivery Local", "wvp"),
                "desc_tip" => true
            ),
            "zones" => array(
                "title" => __("Zonas y Tarifas", "wvp"),
                "type" => "zones_table",
                "description" => __("Configurar zonas de delivery y sus tarifas", "wvp")
            )
        );
    }
    
    /**
     * Generar tabla de zonas
     */
    public function generate_zones_table_html($key, $data) {
        $field_key = $this->get_field_key($key);
        $zones = $this->get_option($key, array());
        
        ob_start();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?></label>
                <?php echo $this->get_tooltip_html($data); ?>
            </th>
            <td class="forminp">
                <div id="wvp-zones-container">
                    <table class="widefat wvp-zones-table" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="zone-name"><?php _e("Nombre de la Zona", "wvp"); ?></th>
                                <th class="zone-rate"><?php _e("Tarifa (USD)", "wvp"); ?></th>
                                <th class="zone-actions"><?php _e("Acciones", "wvp"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($zones)): ?>
                                <?php foreach ($zones as $index => $zone): ?>
                                    <tr class="wvp-zone-row">
                                        <td>
                                            <input type="text" 
                                                   name="<?php echo esc_attr($field_key); ?>[<?php echo $index; ?>][name]" 
                                                   value="<?php echo esc_attr($zone['name']); ?>" 
                                                   class="zone-name-input" 
                                                   placeholder="<?php _e("Ej: Chacao", "wvp"); ?>" />
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   name="<?php echo esc_attr($field_key); ?>[<?php echo $index; ?>][rate]" 
                                                   value="<?php echo esc_attr($zone['rate']); ?>" 
                                                   class="zone-rate-input" 
                                                   step="0.01" 
                                                   min="0" 
                                                   placeholder="0.00" />
                                        </td>
                                        <td>
                                            <button type="button" class="button wvp-remove-zone"><?php _e("Eliminar", "wvp"); ?></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <button type="button" class="button wvp-add-zone"><?php _e("Añadir Zona", "wvp"); ?></button>
                </div>
                <script type="text/javascript">
                jQuery(document).ready(function($) {
                    var zoneIndex = <?php echo count($zones); ?>;
                    
                    $('.wvp-add-zone').on('click', function() {
                        var row = '<tr class="wvp-zone-row">' +
                            '<td><input type="text" name="<?php echo esc_attr($field_key); ?>[' + zoneIndex + '][name]" class="zone-name-input" placeholder="<?php _e("Ej: Chacao", "wvp"); ?>" /></td>' +
                            '<td><input type="number" name="<?php echo esc_attr($field_key); ?>[' + zoneIndex + '][rate]" class="zone-rate-input" step="0.01" min="0" placeholder="0.00" /></td>' +
                            '<td><button type="button" class="button wvp-remove-zone"><?php _e("Eliminar", "wvp"); ?></button></td>' +
                            '</tr>';
                        $('.wvp-zones-table tbody').append(row);
                        zoneIndex++;
                    });
                    
                    $(document).on('click', '.wvp-remove-zone', function() {
                        $(this).closest('tr').remove();
                    });
                });
                </script>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Calcular costo de envío
     * 
     * @param array $package Paquete de envío
     */
    public function calculate_shipping($package = array()) {
        // Verificar si está habilitado
        if ($this->enabled !== "yes") {
            return;
        }
        
        // Verificar si hay zonas configuradas
        if (empty($this->zones)) {
            return;
        }
        
        // Obtener zona seleccionada por el cliente
        $selected_zone = WC()->session->get("wvp_selected_delivery_zone");
        
        if (empty($selected_zone)) {
            return;
        }
        
        // Buscar la zona en la configuración
        $zone_rate = null;
        foreach ($this->zones as $zone) {
            if ($zone['name'] === $selected_zone) {
                $zone_rate = floatval($zone['rate']);
                break;
            }
        }
        
        if ($zone_rate === null || $zone_rate <= 0) {
            return;
        }
        
        // Aplicar la tarifa
        $this->add_rate(array(
            "id" => $this->get_rate_id(),
            "label" => $this->title . " - " . $selected_zone,
            "cost" => $zone_rate,
            "package" => $package
        ));
    }
    
    /**
     * Validar campos de configuración
     */
    public function validate_zones_table_field($key, $value) {
        $zones = array();
        
        if (is_array($value)) {
            foreach ($value as $zone) {
                if (!empty($zone['name']) && !empty($zone['rate'])) {
                    $zones[] = array(
                        'name' => sanitize_text_field($zone['name']),
                        'rate' => floatval($zone['rate'])
                    );
                }
            }
        }
        
        return $zones;
    }
}
