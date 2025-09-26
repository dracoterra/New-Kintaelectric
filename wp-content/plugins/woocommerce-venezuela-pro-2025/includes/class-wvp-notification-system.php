<?php
/**
 * Advanced Notification System
 * Multi-channel notification system for WooCommerce Venezuela Pro 2025
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WVP_Notification_System {
    
    private static $instance = null;
    private $channels = array();
    private $templates = array();
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_channels();
        $this->init_templates();
        $this->init_hooks();
    }
    
    /**
     * Initialize notification channels
     */
    private function init_channels() {
        $this->channels = array(
            'email' => array(
                'name' => 'Email',
                'enabled' => get_option( 'wvp_notifications_email', true ),
                'class' => 'WVP_Email_Notification'
            ),
            'whatsapp' => array(
                'name' => 'WhatsApp',
                'enabled' => get_option( 'wvp_notifications_whatsapp', false ),
                'class' => 'WVP_WhatsApp_Notification'
            ),
            'sms' => array(
                'name' => 'SMS',
                'enabled' => get_option( 'wvp_notifications_sms', false ),
                'class' => 'WVP_SMS_Notification'
            ),
            'push' => array(
                'name' => 'Push Notification',
                'enabled' => get_option( 'wvp_notifications_push', false ),
                'class' => 'WVP_Push_Notification'
            )
        );
    }
    
    /**
     * Initialize notification templates
     */
    private function init_templates() {
        $this->templates = array(
            'order_placed' => array(
                'subject' => 'Nuevo Pedido #{order_number}',
                'message' => 'Se ha realizado un nuevo pedido por {order_total}',
                'channels' => array( 'email', 'whatsapp' )
            ),
            'order_processing' => array(
                'subject' => 'Pedido #{order_number} en Proceso',
                'message' => 'Su pedido #{order_number} está siendo procesado',
                'channels' => array( 'email', 'whatsapp', 'sms' )
            ),
            'order_shipped' => array(
                'subject' => 'Pedido #{order_number} Enviado',
                'message' => 'Su pedido #{order_number} ha sido enviado. Código de seguimiento: {tracking_number}',
                'channels' => array( 'email', 'whatsapp', 'sms' )
            ),
            'order_delivered' => array(
                'subject' => 'Pedido #{order_number} Entregado',
                'message' => 'Su pedido #{order_number} ha sido entregado exitosamente',
                'channels' => array( 'email', 'whatsapp' )
            ),
            'payment_received' => array(
                'subject' => 'Pago Recibido - Pedido #{order_number}',
                'message' => 'Hemos recibido su pago por {payment_amount} para el pedido #{order_number}',
                'channels' => array( 'email', 'whatsapp' )
            ),
            'payment_failed' => array(
                'subject' => 'Pago Fallido - Pedido #{order_number}',
                'message' => 'El pago para el pedido #{order_number} ha fallado. Por favor, intente nuevamente',
                'channels' => array( 'email', 'whatsapp', 'sms' )
            ),
            'low_stock' => array(
                'subject' => 'Stock Bajo - {product_name}',
                'message' => 'El producto {product_name} tiene stock bajo ({stock_quantity} unidades restantes)',
                'channels' => array( 'email', 'push' )
            ),
            'price_change' => array(
                'subject' => 'Cambio de Precio - {product_name}',
                'message' => 'El precio del producto {product_name} ha cambiado de {old_price} a {new_price}',
                'channels' => array( 'email', 'push' )
            ),
            'bcv_rate_update' => array(
                'subject' => 'Actualización Tasa BCV',
                'message' => 'La tasa BCV se ha actualizado a {new_rate} VES por USD',
                'channels' => array( 'email', 'push' )
            ),
            'seniat_report' => array(
                'subject' => 'Reporte SENIAT Generado',
                'message' => 'Se ha generado el reporte SENIAT para el período {report_period}',
                'channels' => array( 'email' )
            )
        );
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // WooCommerce order hooks
        add_action( 'woocommerce_new_order', array( $this, 'send_order_placed_notification' ) );
        add_action( 'woocommerce_order_status_processing', array( $this, 'send_order_processing_notification' ) );
        add_action( 'woocommerce_order_status_shipped', array( $this, 'send_order_shipped_notification' ) );
        add_action( 'woocommerce_order_status_delivered', array( $this, 'send_order_delivered_notification' ) );
        
        // Payment hooks
        add_action( 'woocommerce_payment_complete', array( $this, 'send_payment_received_notification' ) );
        add_action( 'woocommerce_payment_failed', array( $this, 'send_payment_failed_notification' ) );
        
        // Product hooks
        add_action( 'woocommerce_low_stock_notification', array( $this, 'send_low_stock_notification' ) );
        add_action( 'woocommerce_product_object_updated_props', array( $this, 'check_price_change' ), 10, 2 );
        
        // BCV rate update hook
        add_action( 'wvp_bcv_rate_updated', array( $this, 'send_bcv_rate_update_notification' ) );
        
        // SENIAT report hook
        add_action( 'wvp_seniat_report_generated', array( $this, 'send_seniat_report_notification' ) );
        
        // Admin hooks
        add_action( 'wp_ajax_wvp_send_test_notification', array( $this, 'ajax_send_test_notification' ) );
        add_action( 'wp_ajax_wvp_save_notification_settings', array( $this, 'ajax_save_notification_settings' ) );
        add_action( 'admin_menu', array( $this, 'add_notification_admin_menu' ), 55 );
    }
    
    /**
     * Send notification
     */
    public function send_notification( $template_key, $recipients, $data = array(), $channels = null ) {
        if ( ! isset( $this->templates[ $template_key ] ) ) {
            return false;
        }
        
        $template = $this->templates[ $template_key ];
        $channels_to_use = $channels ? $channels : $template['channels'];
        
        $results = array();
        
        foreach ( $channels_to_use as $channel_key ) {
            if ( ! isset( $this->channels[ $channel_key ] ) || ! $this->channels[ $channel_key ]['enabled'] ) {
                continue;
            }
            
            $channel_class = $this->channels[ $channel_key ]['class'];
            if ( class_exists( $channel_class ) ) {
                $channel = new $channel_class();
                $result = $channel->send( $recipients, $template, $data );
                $results[ $channel_key ] = $result;
            }
        }
        
        // Log notification
        $this->log_notification( $template_key, $recipients, $data, $results );
        
        return $results;
    }
    
    /**
     * Send order placed notification
     */
    public function send_order_placed_notification( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        $data = array(
            'order_number' => $order->get_order_number(),
            'order_total' => $order->get_formatted_order_total(),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'customer_email' => $order->get_billing_email(),
            'customer_phone' => $order->get_billing_phone(),
            'order_date' => $order->get_date_created()->format( 'd/m/Y H:i' ),
            'payment_method' => $order->get_payment_method_title(),
            'shipping_address' => $order->get_formatted_shipping_address()
        );
        
        $recipients = array(
            'customer' => array(
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
                'name' => $data['customer_name']
            ),
            'admin' => array(
                'email' => get_option( 'admin_email' ),
                'name' => get_option( 'blogname' )
            )
        );
        
        $this->send_notification( 'order_placed', $recipients, $data );
    }
    
    /**
     * Send order processing notification
     */
    public function send_order_processing_notification( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        $data = array(
            'order_number' => $order->get_order_number(),
            'order_total' => $order->get_formatted_order_total(),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
        );
        
        $recipients = array(
            'customer' => array(
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
                'name' => $data['customer_name']
            )
        );
        
        $this->send_notification( 'order_processing', $recipients, $data );
    }
    
    /**
     * Send order shipped notification
     */
    public function send_order_shipped_notification( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        $tracking_number = $order->get_meta( '_tracking_number' );
        
        $data = array(
            'order_number' => $order->get_order_number(),
            'tracking_number' => $tracking_number ? $tracking_number : 'Pendiente',
            'shipping_method' => $order->get_shipping_method(),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
        );
        
        $recipients = array(
            'customer' => array(
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
                'name' => $data['customer_name']
            )
        );
        
        $this->send_notification( 'order_shipped', $recipients, $data );
    }
    
    /**
     * Send order delivered notification
     */
    public function send_order_delivered_notification( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        $data = array(
            'order_number' => $order->get_order_number(),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'delivery_date' => current_time( 'd/m/Y H:i' )
        );
        
        $recipients = array(
            'customer' => array(
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
                'name' => $data['customer_name']
            )
        );
        
        $this->send_notification( 'order_delivered', $recipients, $data );
    }
    
    /**
     * Send payment received notification
     */
    public function send_payment_received_notification( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        $data = array(
            'order_number' => $order->get_order_number(),
            'payment_amount' => $order->get_formatted_order_total(),
            'payment_method' => $order->get_payment_method_title(),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
        );
        
        $recipients = array(
            'customer' => array(
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
                'name' => $data['customer_name']
            )
        );
        
        $this->send_notification( 'payment_received', $recipients, $data );
    }
    
    /**
     * Send payment failed notification
     */
    public function send_payment_failed_notification( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        $data = array(
            'order_number' => $order->get_order_number(),
            'payment_amount' => $order->get_formatted_order_total(),
            'payment_method' => $order->get_payment_method_title(),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
        );
        
        $recipients = array(
            'customer' => array(
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
                'name' => $data['customer_name']
            )
        );
        
        $this->send_notification( 'payment_failed', $recipients, $data );
    }
    
    /**
     * Send low stock notification
     */
    public function send_low_stock_notification( $product ) {
        $data = array(
            'product_name' => $product->get_name(),
            'product_id' => $product->get_id(),
            'stock_quantity' => $product->get_stock_quantity(),
            'product_url' => $product->get_permalink()
        );
        
        $recipients = array(
            'admin' => array(
                'email' => get_option( 'admin_email' ),
                'name' => get_option( 'blogname' )
            )
        );
        
        $this->send_notification( 'low_stock', $recipients, $data );
    }
    
    /**
     * Check price change
     */
    public function check_price_change( $product, $updated_props ) {
        if ( in_array( 'price', $updated_props ) ) {
            $old_price = $product->get_meta( '_old_price' );
            $new_price = $product->get_price();
            
            if ( $old_price && $old_price != $new_price ) {
                $data = array(
                    'product_name' => $product->get_name(),
                    'product_id' => $product->get_id(),
                    'old_price' => wc_price( $old_price ),
                    'new_price' => wc_price( $new_price ),
                    'product_url' => $product->get_permalink()
                );
                
                $recipients = array(
                    'admin' => array(
                        'email' => get_option( 'admin_email' ),
                        'name' => get_option( 'blogname' )
                    )
                );
                
                $this->send_notification( 'price_change', $recipients, $data );
            }
            
            // Store current price as old price for next comparison
            $product->update_meta_data( '_old_price', $new_price );
            $product->save();
        }
    }
    
    /**
     * Send BCV rate update notification
     */
    public function send_bcv_rate_update_notification( $new_rate ) {
        $data = array(
            'new_rate' => number_format( $new_rate, 2, ',', '.' ),
            'update_time' => current_time( 'd/m/Y H:i' )
        );
        
        $recipients = array(
            'admin' => array(
                'email' => get_option( 'admin_email' ),
                'name' => get_option( 'blogname' )
            )
        );
        
        $this->send_notification( 'bcv_rate_update', $recipients, $data );
    }
    
    /**
     * Send SENIAT report notification
     */
    public function send_seniat_report_notification( $report_data ) {
        $data = array(
            'report_period' => $report_data['period'] ?? 'Período actual',
            'report_type' => $report_data['type'] ?? 'Reporte',
            'report_url' => $report_data['url'] ?? ''
        );
        
        $recipients = array(
            'admin' => array(
                'email' => get_option( 'admin_email' ),
                'name' => get_option( 'blogname' )
            )
        );
        
        $this->send_notification( 'seniat_report', $recipients, $data );
    }
    
    /**
     * Log notification
     */
    private function log_notification( $template_key, $recipients, $data, $results ) {
        $log_entry = array(
            'template' => $template_key,
            'recipients' => $recipients,
            'data' => $data,
            'results' => $results,
            'timestamp' => current_time( 'mysql' )
        );
        
        $logs = get_option( 'wvp_notification_logs', array() );
        $logs[] = $log_entry;
        
        // Keep only last 1000 entries
        if ( count( $logs ) > 1000 ) {
            $logs = array_slice( $logs, -1000 );
        }
        
        update_option( 'wvp_notification_logs', $logs );
    }
    
    /**
     * Get notification statistics
     */
    public function get_notification_stats() {
        $logs = get_option( 'wvp_notification_logs', array() );
        
        $stats = array(
            'total_notifications' => count( $logs ),
            'successful_notifications' => 0,
            'failed_notifications' => 0,
            'channel_stats' => array(),
            'template_stats' => array(),
            'recent_notifications' => array_slice( $logs, -10 )
        );
        
        foreach ( $logs as $log ) {
            foreach ( $log['results'] as $channel => $result ) {
                if ( ! isset( $stats['channel_stats'][ $channel ] ) ) {
                    $stats['channel_stats'][ $channel ] = array(
                        'total' => 0,
                        'successful' => 0,
                        'failed' => 0
                    );
                }
                
                $stats['channel_stats'][ $channel ]['total']++;
                if ( $result ) {
                    $stats['channel_stats'][ $channel ]['successful']++;
                    $stats['successful_notifications']++;
                } else {
                    $stats['channel_stats'][ $channel ]['failed']++;
                    $stats['failed_notifications']++;
                }
            }
            
            $template = $log['template'];
            if ( ! isset( $stats['template_stats'][ $template ] ) ) {
                $stats['template_stats'][ $template ] = 0;
            }
            $stats['template_stats'][ $template ]++;
        }
        
        return $stats;
    }
    
    /**
     * Add notification admin menu
     */
    public function add_notification_admin_menu() {
        add_submenu_page(
            'wvp-dashboard',
            'Sistema de Notificaciones',
            'Notificaciones',
            'manage_options',
            'wvp-notifications',
            array( $this, 'notification_admin_page' )
        );
    }
    
    /**
     * Notification admin page
     */
    public function notification_admin_page() {
        $stats = $this->get_notification_stats();
        ?>
        <div class="wrap">
            <h1>📱 Sistema de Notificaciones - WooCommerce Venezuela Pro 2025</h1>
            
            <div class="wvp-notification-overview">
                <h2>Resumen de Notificaciones</h2>
                <div class="wvp-stats-grid">
                    <div class="wvp-stat-card">
                        <h3>Total Enviadas</h3>
                        <p class="wvp-stat-number"><?php echo esc_html( $stats['total_notifications'] ); ?></p>
                    </div>
                    <div class="wvp-stat-card">
                        <h3>Exitosas</h3>
                        <p class="wvp-stat-number" style="color: var(--wvp-success-color);"><?php echo esc_html( $stats['successful_notifications'] ); ?></p>
                    </div>
                    <div class="wvp-stat-card">
                        <h3>Fallidas</h3>
                        <p class="wvp-stat-number" style="color: var(--wvp-error-color);"><?php echo esc_html( $stats['failed_notifications'] ); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="wvp-notification-channels">
                <h2>Configuración de Canales</h2>
                <form method="post" action="options.php">
                    <?php settings_fields( 'wvp_notification_settings' ); ?>
                    <table class="form-table">
                        <?php foreach ( $this->channels as $channel_key => $channel_data ) : ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( $channel_data['name'] ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wvp_notifications_<?php echo esc_attr( $channel_key ); ?>" value="1" <?php checked( $channel_data['enabled'] ); ?> />
                                    Habilitar <?php echo esc_html( $channel_data['name'] ); ?>
                                </label>
                                <?php if ( isset( $stats['channel_stats'][ $channel_key ] ) ) : ?>
                                    <p class="description">
                                        Enviadas: <?php echo esc_html( $stats['channel_stats'][ $channel_key ]['total'] ); ?> | 
                                        Exitosas: <?php echo esc_html( $stats['channel_stats'][ $channel_key ]['successful'] ); ?> | 
                                        Fallidas: <?php echo esc_html( $stats['channel_stats'][ $channel_key ]['failed'] ); ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php submit_button( 'Guardar Configuración' ); ?>
                </form>
            </div>
            
            <div class="wvp-notification-templates">
                <h2>Plantillas de Notificación</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Plantilla</th>
                            <th>Asunto</th>
                            <th>Canales</th>
                            <th>Usos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $this->templates as $template_key => $template_data ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( ucfirst( str_replace( '_', ' ', $template_key ) ) ); ?></strong></td>
                            <td><?php echo esc_html( $template_data['subject'] ); ?></td>
                            <td><?php echo esc_html( implode( ', ', $template_data['channels'] ) ); ?></td>
                            <td><?php echo esc_html( $stats['template_stats'][ $template_key ] ?? 0 ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="wvp-notification-actions">
                <h2>Acciones de Notificación</h2>
                <p>
                    <button class="button button-primary" id="wvp-send-test-notification">
                        📧 Enviar Notificación de Prueba
                    </button>
                    <button class="button button-secondary" id="wvp-clear-notification-logs">
                        🗑️ Limpiar Logs
                    </button>
                </p>
            </div>
            
            <?php if ( ! empty( $stats['recent_notifications'] ) ) : ?>
            <div class="wvp-notification-recent">
                <h2>Notificaciones Recientes</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Plantilla</th>
                            <th>Destinatario</th>
                            <th>Canales</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( array_reverse( $stats['recent_notifications'] ) as $log ) : ?>
                        <tr>
                            <td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $log['template'] ) ) ); ?></td>
                            <td>
                                <?php
                                $recipients = array();
                                foreach ( $log['recipients'] as $recipient ) {
                                    if ( isset( $recipient['email'] ) ) {
                                        $recipients[] = $recipient['email'];
                                    }
                                }
                                echo esc_html( implode( ', ', $recipients ) );
                                ?>
                            </td>
                            <td><?php echo esc_html( implode( ', ', array_keys( $log['results'] ) ) ); ?></td>
                            <td>
                                <?php
                                $all_success = true;
                                foreach ( $log['results'] as $result ) {
                                    if ( ! $result ) {
                                        $all_success = false;
                                        break;
                                    }
                                }
                                echo $all_success ? '<span style="color: green;">✅ Exitoso</span>' : '<span style="color: red;">❌ Fallido</span>';
                                ?>
                            </td>
                            <td><?php echo esc_html( $log['timestamp'] ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .wvp-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .wvp-stat-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .wvp-stat-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .wvp-stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
            margin: 0;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#wvp-send-test-notification').on('click', function() {
                $(this).prop('disabled', true).text('Enviando...');
                
                $.post(ajaxurl, {
                    action: 'wvp_send_test_notification',
                    nonce: '<?php echo wp_create_nonce( 'wvp_notification_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Notificación de prueba enviada exitosamente');
                        location.reload();
                    } else {
                        alert('Error al enviar notificación de prueba');
                    }
                }).always(function() {
                    $('#wvp-send-test-notification').prop('disabled', false).text('📧 Enviar Notificación de Prueba');
                });
            });
            
            $('#wvp-clear-notification-logs').on('click', function() {
                if (confirm('¿Limpiar todos los logs de notificaciones?')) {
                    $(this).prop('disabled', true).text('Limpiando...');
                    
                    $.post(ajaxurl, {
                        action: 'wvp_clear_notification_logs',
                        nonce: '<?php echo wp_create_nonce( 'wvp_notification_nonce' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('Logs de notificaciones limpiados');
                            location.reload();
                        } else {
                            alert('Error al limpiar logs');
                        }
                    }).always(function() {
                        $('#wvp-clear-notification-logs').prop('disabled', false).text('🗑️ Limpiar Logs');
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX send test notification
     */
    public function ajax_send_test_notification() {
        check_ajax_referer( 'wvp_notification_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $data = array(
            'test_message' => 'Esta es una notificación de prueba del sistema',
            'test_time' => current_time( 'd/m/Y H:i' )
        );
        
        $recipients = array(
            'admin' => array(
                'email' => get_option( 'admin_email' ),
                'name' => get_option( 'blogname' )
            )
        );
        
        $results = $this->send_notification( 'order_placed', $recipients, $data );
        
        wp_send_json_success( 'Test notification sent successfully' );
    }
    
    /**
     * AJAX save notification settings
     */
    public function ajax_save_notification_settings() {
        check_ajax_referer( 'wvp_notification_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        foreach ( $this->channels as $channel_key => $channel_data ) {
            $enabled = isset( $_POST[ 'wvp_notifications_' . $channel_key ] );
            update_option( 'wvp_notifications_' . $channel_key, $enabled );
        }
        
        wp_send_json_success( 'Notification settings saved successfully' );
    }
}
