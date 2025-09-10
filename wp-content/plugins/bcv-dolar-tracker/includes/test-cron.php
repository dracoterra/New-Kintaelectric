<?php
/**
 * Archivo de prueba para verificar la funcionalidad del sistema cron
 * Este archivo se puede incluir temporalmente para testing
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Función de prueba para verificar el sistema cron
function bcv_test_cron_functionality() {
    if (!current_user_can('manage_options')) {
        wp_die('Acceso denegado');
    }
    
    echo '<div class="wrap">';
    echo '<h1>Prueba del Sistema Cron - BCV Dólar Tracker</h1>';
    
    // Verificar estado del cron
    global $wpdb;
    $cron_hook = 'bcv_scrape_dollar_rate';
    
    echo '<h2>Estado del Sistema Cron</h2>';
    
    // Verificar si el cron está programado
    $next_scheduled = wp_next_scheduled($cron_hook);
    $is_scheduled = $next_scheduled !== false;
    
    echo '<p><strong>Cron programado:</strong> ' . ($is_scheduled ? '✅ Sí' : '❌ No') . '</p>';
    
    if ($is_scheduled) {
        echo '<p><strong>Próxima ejecución:</strong> ' . date('Y-m-d H:i:s', $next_scheduled) . '</p>';
        echo '<p><strong>Tiempo restante:</strong> ' . human_time_diff(time(), $next_scheduled) . '</p>';
    }
    
    // Verificar configuración del cron
    $cron_settings = get_option('bcv_cron_settings', array());
    echo '<h3>Configuración del Cron</h3>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Configuración</th><th>Valor</th></tr></thead>';
    echo '<tbody>';
    echo '<tr><td>Habilitado</td><td>' . ($cron_settings['enabled'] ? '✅ Sí' : '❌ No') . '</td></tr>';
    echo '<tr><td>Horas</td><td>' . esc_html($cron_settings['hours']) . '</td></tr>';
    echo '<tr><td>Minutos</td><td>' . esc_html($cron_settings['minutes']) . '</td></tr>';
    echo '<tr><td>Segundos</td><td>' . esc_html($cron_settings['seconds']) . '</td></tr>';
    echo '</tbody></table>';
    
    // Verificar estadísticas del cron
    echo '<h3>Estadísticas del Cron</h3>';
    $total_executions = get_option('bcv_cron_total_executions', 0);
    $successful_executions = get_option('bcv_cron_successful_executions', 0);
    $failed_executions = get_option('bcv_cron_failed_executions', 0);
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Métrica</th><th>Valor</th></tr></thead>';
    echo '<tbody>';
    echo '<tr><td>Total de ejecuciones</td><td>' . esc_html($total_executions) . '</td></tr>';
    echo '<tr><td>Ejecuciones exitosas</td><td>' . esc_html($successful_executions) . '</td></tr>';
    echo '<tr><td>Ejecuciones fallidas</td><td>' . esc_html($failed_executions) . '</td></tr>';
    echo '<tr><td>Última ejecución</td><td>' . esc_html(get_transient('bcv_cron_last_execution') ? date('Y-m-d H:i:s', get_transient('bcv_cron_last_execution')) : 'Nunca') . '</td></tr>';
    echo '</tbody></table>';
    
    // Verificar estado del scraping
    echo '<h3>Estado del Scraping</h3>';
    $cached_price = get_transient('bcv_scraped_rate');
    $cache_expiry = get_option('_transient_timeout_bcv_scraped_rate', 0);
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Métrica</th><th>Valor</th></tr></thead>';
    echo '<tbody>';
    echo '<tr><td>Precio en caché</td><td>' . ($cached_price ? esc_html($cached_price) : 'No disponible') . '</td></tr>';
    echo '<tr><td>Caché válido</td><td>' . ($cached_price !== false ? '✅ Sí' : '❌ No') . '</td></tr>';
    echo '<tr><td>Expiración del caché</td><td>' . ($cache_expiry ? date('Y-m-d H:i:s', $cache_expiry) : 'No disponible') . '</td></tr>';
    echo '<tr><td>Último scraping</td><td>' . esc_html(get_option('bcv_last_scraping_time', 'Nunca')) . '</td></tr>';
    echo '<tr><td>Intentos de scraping</td><td>' . esc_html(get_option('bcv_scraping_attempts', 0)) . '</td></tr>';
    echo '<tr><td>Scrapings exitosos</td><td>' . esc_html(get_option('bcv_successful_scrapings', 0)) . '</td></tr>';
    echo '<tr><td>Scrapings fallidos</td><td>' . esc_html(get_option('bcv_failed_scrapings', 0)) . '</td></tr>';
    echo '</tbody></table>';
    
    // Botones de acción
    echo '<h3>Acciones de Prueba</h3>';
    
    // Probar scraping manual
    if (isset($_POST['test_scraping'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'test_scraping')) {
            echo '<div class="notice notice-info"><p>🔄 Ejecutando scraping manual...</p></div>';
            
            // Ejecutar scraping
            $scraper = new BCV_Scraper();
            $result = $scraper->scrape_bcv_rate();
            
            if ($result !== false) {
                echo '<div class="notice notice-success"><p>✅ Scraping exitoso! Precio obtenido: ' . esc_html($result) . '</p></div>';
                
                // Guardar en BD
                $database = new BCV_Database();
                $inserted = $database->insert_price($result);
                
                if ($inserted) {
                    echo '<div class="notice notice-success"><p>✅ Precio guardado en BD con ID: ' . esc_html($inserted) . '</p></div>';
                    
                    // Verificar que se guardó correctamente
                    $saved_record = $database->get_price_by_id($inserted);
                    if ($saved_record) {
                        echo '<div class="notice notice-info"><p>📊 Verificación de BD: ID=' . esc_html($saved_record->id) . 
                             ', Precio=' . esc_html($saved_record->precio) . 
                             ', Fecha=' . esc_html($saved_record->datatime) . '</p></div>';
                    }
                } else {
                    echo '<div class="notice notice-error"><p>❌ Error al guardar en BD</p></div>';
                }
            } else {
                echo '<div class="notice notice-error"><p>❌ Scraping falló</p></div>';
            }
            
            echo '<script>location.reload();</script>';
        }
    }
    
    // Probar inserción directa en BD
    if (isset($_POST['test_database_insert'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'test_database_insert')) {
            echo '<div class="notice notice-info"><p>🔄 Probando inserción directa en BD...</p></div>';
            
            $database = new BCV_Database();
            
            // Insertar precio de prueba
            $test_price = 35.1234;
            $test_datetime = current_time('mysql');
            
            $inserted = $database->insert_price($test_price, $test_datetime);
            
            if ($inserted) {
                echo '<div class="notice notice-success"><p>✅ Inserción directa exitosa! ID: ' . esc_html($inserted) . '</p></div>';
                
                // Verificar inserción
                $saved_record = $database->get_price_by_id($inserted);
                if ($saved_record) {
                    echo '<div class="notice notice-info"><p>📊 Registro verificado: ID=' . esc_html($saved_record->id) . 
                         ', Precio=' . esc_html($saved_record->precio) . 
                         ', Fecha=' . esc_html($saved_record->datatime) . 
                         ', Creado=' . esc_html($saved_record->created_at) . 
                         ', Actualizado=' . esc_html($saved_record->updated_at) . '</p></div>';
                }
            } else {
                echo '<div class="notice notice-error"><p>❌ Error en inserción directa</p></div>';
            }
            
            echo '<script>location.reload();</script>';
        }
    }
    
    // Verificar registros en BD
    if (isset($_POST['check_database_records'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'check_database_records')) {
            echo '<div class="notice notice-info"><p>🔄 Verificando registros en BD...</p></div>';
            
            $database = new BCV_Database();
            
            // Obtener últimos 5 registros
            $recent_prices = $database->get_prices(array('per_page' => 5));
            
            if ($recent_prices['items']) {
                echo '<div class="notice notice-success"><p>✅ Se encontraron ' . count($recent_prices['items']) . ' registros recientes:</p></div>';
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>ID</th><th>Precio</th><th>Fecha</th><th>Creado</th><th>Actualizado</th></tr></thead>';
                echo '<tbody>';
                
                foreach ($recent_prices['items'] as $record) {
                    echo '<tr>';
                    echo '<td>' . esc_html($record->id) . '</td>';
                    echo '<td>' . esc_html($record->precio) . '</td>';
                    echo '<td>' . esc_html($record->datatime) . '</td>';
                    echo '<td>' . esc_html($record->created_at) . '</td>';
                    echo '<td>' . esc_html($record->updated_at) . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<div class="notice notice-warning"><p>⚠️ No se encontraron registros en la BD</p></div>';
            }
            
            echo '<script>location.reload();</script>';
        }
    }
    
    // Probar ejecución del cron
    if (isset($_POST['test_cron_execution'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'test_cron_execution')) {
            echo '<div class="notice notice-info"><p>🔄 Probando ejecución del cron...</p></div>';
            
            // Ejecutar la tarea del cron directamente
            $cron = new BCV_Cron();
            
            // Obtener número de registros antes de la ejecución
            $database = new BCV_Database();
            $before_count = $database->get_price_stats()['total_records'];
            
            echo '<div class="notice notice-info"><p>📊 Registros en BD antes de ejecución: ' . $before_count . '</p></div>';
            
            // Ejecutar tarea del cron
            $cron->execute_scraping_task();
            
            // Esperar un momento para que se procese
            sleep(2);
            
            // Obtener número de registros después de la ejecución
            $after_count = $database->get_price_stats()['total_records'];
            
            echo '<div class="notice notice-info"><p>📊 Registros en BD después de ejecución: ' . $after_count . '</p></div>';
            
            if ($after_count > $before_count) {
                echo '<div class="notice notice-success"><p>✅ Cron ejecutado exitosamente! Se añadieron ' . ($after_count - $before_count) . ' nuevo(s) registro(s)</p></div>';
                
                // Mostrar el último registro añadido
                $latest_price = $database->get_latest_price();
                if ($latest_price) {
                    echo '<div class="notice notice-info"><p>📊 Último registro añadido: ID=' . esc_html($latest_price->id) . 
                         ', Precio=' . esc_html($latest_price->precio) . 
                         ', Fecha=' . esc_html($latest_price->datatime) . '</p></div>';
                }
            } else {
                echo '<div class="notice notice-warning"><p>⚠️ Cron ejecutado pero no se añadieron nuevos registros</p></div>';
            }
            
            echo '<script>location.reload();</script>';
        }
    }
    
    // Probar conectividad
    if (isset($_POST['test_connectivity'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'test_connectivity')) {
            echo '<div class="notice notice-info"><p>🔄 Probando conectividad con BCV...</p></div>';
            
            $scraper = new BCV_Scraper();
            $result = $scraper->test_connectivity();
            
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>✅ ' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>❌ ' . esc_html($result['message']) . '</p></div>';
            }
            
            echo '<script>location.reload();</script>';
        }
    }
    
    // Limpiar caché
    if (isset($_POST['clear_cache'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'clear_cache')) {
            $scraper = new BCV_Scraper();
            $result = $scraper->clear_cache();
            
            if ($result) {
                echo '<div class="notice notice-success"><p>✅ Caché limpiado correctamente</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>❌ Error al limpiar caché</p></div>';
            }
            
            echo '<script>location.reload();</script>';
        }
    }
    
    // Resetear estadísticas
    if (isset($_POST['reset_stats'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'reset_stats')) {
            $cron = new BCV_Cron();
            $cron->reset_cron_stats();
            
            $scraper = new BCV_Scraper();
            $scraper->reset_scraping_stats();
            
            echo '<div class="notice notice-success"><p>✅ Estadísticas reseteadas correctamente</p></div>';
            echo '<script>location.reload();</script>';
        }
    }
    
    // Formularios de acción
    echo '<div style="margin-top: 20px;">';
    
    // Probar scraping
    echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
    wp_nonce_field('test_scraping');
    echo '<input type="submit" name="test_scraping" class="button button-primary" value="Probar Scraping Manual">';
    echo '</form>';
    
    // Probar inserción directa en BD
    echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
    wp_nonce_field('test_database_insert');
    echo '<input type="submit" name="test_database_insert" class="button button-secondary" value="Probar Inserción BD">';
    echo '</form>';

    // Verificar registros en BD
    echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
    wp_nonce_field('check_database_records');
    echo '<input type="submit" name="check_database_records" class="button button-secondary" value="Verificar Registros BD">';
    echo '</form>';
    
    // Probar ejecución del cron
    echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
    wp_nonce_field('test_cron_execution');
    echo '<input type="submit" name="test_cron_execution" class="button button-primary" value="Probar Ejecución Cron">';
    echo '</form>';
    
    // Probar conectividad
    echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
    wp_nonce_field('test_connectivity');
    echo '<input type="submit" name="test_connectivity" class="button button-secondary" value="Probar Conectividad">';
    echo '</form>';
    
    // Limpiar caché
    echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
    wp_nonce_field('clear_cache');
    echo '<input type="submit" name="clear_cache" class="button button-secondary" value="Limpiar Caché">';
    echo '</form>';
    
    // Resetear estadísticas
    echo '<form method="post" style="display: inline-block;">';
    wp_nonce_field('reset_stats');
    echo '<input type="submit" name="reset_stats" class="button button-secondary" value="Resetear Estadísticas">';
    echo '</form>';
    
    echo '</div>';
    
    echo '</div>';
}

// Hook para añadir página de prueba (solo en desarrollo)
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_menu', function() {
        add_submenu_page(
            'tools.php',
            'Prueba Cron - BCV',
            'Prueba Cron - BCV',
            'manage_options',
            'bcv-test-cron',
            'bcv_test_cron_functionality'
        );
    });
}
