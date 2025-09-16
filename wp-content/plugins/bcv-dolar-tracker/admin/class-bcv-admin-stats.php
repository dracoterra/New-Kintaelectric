<?php
/**
 * Clase para manejar la página de estadísticas con diseño moderno
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCV_Admin_Stats {
    
    /**
     * Renderizar página de estadísticas con diseño moderno
     */
    public static function render_statistics_page() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Acceso denegado');
        }
        
        // Procesar reset de estadísticas del cron
        if (isset($_POST['bcv_reset_cron_stats'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'bcv_reset_cron_stats')) {
                self::handle_reset_cron_stats();
            }
        }
        
        // Obtener estadísticas
        $database = new BCV_Database();
        $price_stats = $database->get_price_stats();
        
        $cron = new BCV_Cron();
        $cron_stats = $cron->get_cron_stats();
        
        $scraper = new BCV_Scraper();
        $scraping_info = $scraper->get_scraping_info();
        
        // Cargar CSS específico para estadísticas
        wp_enqueue_style('bcv-admin-stats', BCV_DOLAR_TRACKER_PLUGIN_URL . 'assets/css/admin-stats.css', array(), BCV_DOLAR_TRACKER_VERSION);
        
        echo '<div class="wrap">';
        echo '<div class="bcv-stats-container">';
        
        // Header principal
        echo '<div class="bcv-stats-header">';
        echo '<h1 class="bcv-stats-title">📊 Estadísticas del Sistema</h1>';
        echo '<p class="bcv-stats-subtitle">Monitoreo en tiempo real del estado del plugin</p>';
        echo '</div>';
        
        // Dashboard principal
        echo '<div class="bcv-dashboard-grid">';
        
        // Tarjeta de precio actual
        $current_price = $price_stats['last_price'] ?: 0;
        $price_trend = self::get_price_trend($price_stats);
        echo '<div class="bcv-card bcv-card-primary">';
        echo '<div class="bcv-card-header">';
        echo '<div class="bcv-card-icon">💰</div>';
        echo '<div class="bcv-card-title">Precio Actual</div>';
        echo '</div>';
        echo '<div class="bcv-card-content">';
        echo '<div class="bcv-price-display">1 USD = ' . esc_html(number_format($current_price, 4, ',', '.')) . ' Bs.</div>';
        echo '<div class="bcv-price-trend">' . $price_trend . '</div>';
        echo '</div>';
        echo '</div>';
        
        // Tarjeta de estado del sistema
        $system_health = self::get_system_health($cron_stats, $scraping_info);
        $health_class = $system_health['status'] === 'excellent' ? 'bcv-card-success' : ($system_health['status'] === 'good' ? 'bcv-card-info' : 'bcv-card-warning');
        echo '<div class="bcv-card ' . $health_class . '">';
        echo '<div class="bcv-card-header">';
        echo '<div class="bcv-card-icon">' . $system_health['icon'] . '</div>';
        echo '<div class="bcv-card-title">Estado del Sistema</div>';
        echo '</div>';
        echo '<div class="bcv-card-content">';
        echo '<div class="bcv-status-text">' . $system_health['text'] . '</div>';
        echo '</div>';
        echo '</div>';
        
        // Tarjeta de tareas automáticas
        $cron_status = wp_next_scheduled('bcv_scrape_dollar_rate') !== false;
        $cron_class = $cron_status ? 'bcv-card-success' : 'bcv-card-warning';
        echo '<div class="bcv-card ' . $cron_class . '">';
        echo '<div class="bcv-card-header">';
        echo '<div class="bcv-card-icon">⏰</div>';
        echo '<div class="bcv-card-title">Actualización Automática</div>';
        echo '</div>';
        echo '<div class="bcv-card-content">';
        echo '<div class="bcv-status-text">' . ($cron_status ? 'Activo' : 'Inactivo') . '</div>';
        echo '</div>';
        echo '</div>';
        
        // Tarjeta de conexión BCV
        $connection_health = self::get_connection_health($scraping_info);
        $connection_class = $connection_health === 'excellent' ? 'bcv-card-success' : ($connection_health === 'good' ? 'bcv-card-info' : 'bcv-card-warning');
        echo '<div class="bcv-card ' . $connection_class . '">';
        echo '<div class="bcv-card-header">';
        echo '<div class="bcv-card-icon">🏦</div>';
        echo '<div class="bcv-card-title">Conexión BCV</div>';
        echo '</div>';
        echo '<div class="bcv-card-content">';
        echo '<div class="bcv-status-text">' . self::get_connection_status_text($scraping_info) . '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>'; // Fin dashboard-grid
        
        // Sección de estadísticas detalladas
        echo '<div class="bcv-stats-section">';
        echo '<h2 class="bcv-section-title">📈 Estadísticas de Precios</h2>';
        
        echo '<div class="bcv-stats-grid">';
        
        // Total de registros
        echo '<div class="bcv-stat-card">';
        echo '<div class="bcv-stat-icon">📊</div>';
        echo '<div class="bcv-stat-value">' . esc_html($price_stats['total_records']) . '</div>';
        echo '<div class="bcv-stat-label">Registros Guardados</div>';
        echo '</div>';
        
        // Precio más alto
        echo '<div class="bcv-stat-card">';
        echo '<div class="bcv-stat-icon">📈</div>';
        echo '<div class="bcv-stat-value">1 USD = ' . esc_html(number_format($price_stats['max_price'], 4, ',', '.')) . ' Bs.</div>';
        echo '<div class="bcv-stat-label">Precio Más Alto</div>';
        echo '</div>';
        
        // Precio más bajo
        echo '<div class="bcv-stat-card">';
        echo '<div class="bcv-stat-icon">📉</div>';
        echo '<div class="bcv-stat-value">1 USD = ' . esc_html(number_format($price_stats['min_price'], 4, ',', '.')) . ' Bs.</div>';
        echo '<div class="bcv-stat-label">Precio Más Bajo</div>';
        echo '</div>';
        
        // Precio promedio
        echo '<div class="bcv-stat-card">';
        echo '<div class="bcv-stat-icon">📊</div>';
        echo '<div class="bcv-stat-value">1 USD = ' . esc_html(number_format($price_stats['avg_price'], 4, ',', '.')) . ' Bs.</div>';
        echo '<div class="bcv-stat-label">Precio Promedio</div>';
        echo '</div>';
        
        echo '</div>'; // Fin stats-grid
        echo '</div>'; // Fin stats-section
        
        // Sección de rendimiento
        echo '<div class="bcv-stats-section">';
        echo '<h2 class="bcv-section-title">⚡ Rendimiento del Sistema</h2>';
        
        echo '<div class="bcv-stats-grid">';
        
        // Actualizaciones realizadas
        echo '<div class="bcv-stat-card">';
        echo '<div class="bcv-stat-icon">🔄</div>';
        echo '<div class="bcv-stat-value">' . esc_html($cron_stats['total_executions']) . '</div>';
        echo '<div class="bcv-stat-label">Actualizaciones Realizadas</div>';
        echo '</div>';
        
        // Actualizaciones exitosas
        echo '<div class="bcv-stat-card">';
        echo '<div class="bcv-stat-icon">✅</div>';
        echo '<div class="bcv-stat-value">' . esc_html($cron_stats['successful_executions']) . '</div>';
        echo '<div class="bcv-stat-label">Actualizaciones Exitosas</div>';
        echo '</div>';
        
        // Tasa de éxito
        $success_rate = $cron_stats['total_executions'] > 0 ? round(($cron_stats['successful_executions'] / $cron_stats['total_executions']) * 100, 1) : 0;
        echo '<div class="bcv-stat-card">';
        echo '<div class="bcv-stat-icon">📊</div>';
        echo '<div class="bcv-stat-value">' . esc_html($success_rate) . '%</div>';
        echo '<div class="bcv-stat-label">Tasa de Éxito</div>';
        echo '</div>';
        
        // Conexiones exitosas
        echo '<div class="bcv-stat-card">';
        echo '<div class="bcv-stat-icon">🌐</div>';
        echo '<div class="bcv-stat-value">' . esc_html($scraping_info['successful_scrapings']) . '</div>';
        echo '<div class="bcv-stat-label">Conexiones Exitosas</div>';
        echo '</div>';
        
        echo '</div>'; // Fin stats-grid
        echo '</div>'; // Fin stats-section
        
        // Información adicional
        echo '<div class="bcv-info-section">';
        echo '<div class="bcv-info-grid">';
        
        // Período de registro
        echo '<div class="bcv-info-card">';
        echo '<h3 class="bcv-info-title">📅 Período de Registro</h3>';
        echo '<div class="bcv-info-content">';
        echo '<p><strong>Primer precio:</strong> ' . esc_html($price_stats['first_date'] ?: 'N/A') . '</p>';
        echo '<p><strong>Último precio:</strong> ' . esc_html($price_stats['last_date'] ?: 'N/A') . '</p>';
        echo '</div>';
        echo '</div>';
        
        // Horarios de actualización
        echo '<div class="bcv-info-card">';
        echo '<h3 class="bcv-info-title">⏰ Horarios de Actualización</h3>';
        echo '<div class="bcv-info-content">';
        echo '<p><strong>Última actualización:</strong> ' . esc_html($cron_stats['last_execution'] ?: 'N/A') . '</p>';
        echo '<p><strong>Próxima actualización:</strong> ' . esc_html($cron_stats['next_execution'] ?: 'N/A') . '</p>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>'; // Fin info-grid
        echo '</div>'; // Fin info-section
        
        // Botones de acción
        echo '<div class="bcv-actions-section">';
        echo '<div class="bcv-actions-grid">';
        
        echo '<form method="post" class="bcv-action-form">';
        wp_nonce_field('bcv_reset_cron_stats');
        echo '<button type="submit" name="bcv_reset_cron_stats" class="bcv-btn bcv-btn-secondary">🔄 Reiniciar Contadores</button>';
        echo '</form>';
        
        echo '<form method="post" class="bcv-action-form">';
        wp_nonce_field('bcv_fix_database');
        echo '<button type="submit" name="bcv_fix_database" class="bcv-btn bcv-btn-danger" onclick="return confirm(\'¿Estás seguro? Esto recreará la tabla de logs.\');">🔧 Reparar Base de Datos</button>';
        echo '</form>';
        
        echo '</div>'; // Fin actions-grid
        echo '</div>'; // Fin actions-section
        
        echo '</div>'; // Fin stats-container
        echo '</div>'; // Fin wrap
    }
    
    /**
     * Obtener tendencia del precio
     */
    private static function get_price_trend($price_stats) {
        if ($price_stats['total_records'] < 2) {
            return '📊 Sin datos suficientes';
        }
        
        $current = $price_stats['last_price'];
        $previous = $price_stats['avg_price']; // Usar promedio como referencia
        
        if ($current > $previous) {
            return '📈 Subiendo';
        } elseif ($current < $previous) {
            return '📉 Bajando';
        } else {
            return '➡️ Estable';
        }
    }
    
    /**
     * Obtener estado de salud del sistema
     */
    private static function get_system_health($cron_stats, $scraping_info) {
        $success_rate = $cron_stats['total_executions'] > 0 ? 
            ($cron_stats['successful_executions'] / $cron_stats['total_executions']) * 100 : 0;
        
        if ($success_rate >= 90 && $scraping_info['successful_scrapings'] > 0) {
            return array(
                'status' => 'excellent',
                'icon' => '✅',
                'text' => 'Excelente'
            );
        } elseif ($success_rate >= 70) {
            return array(
                'status' => 'good',
                'icon' => '⚠️',
                'text' => 'Bueno'
            );
        } else {
            return array(
                'status' => 'warning',
                'icon' => '❌',
                'text' => 'Necesita atención'
            );
        }
    }
    
    /**
     * Obtener estado de salud de la conexión
     */
    private static function get_connection_health($scraping_info) {
        if ($scraping_info['total_scrapings'] == 0) {
            return 'warning';
        }
        
        $success_rate = ($scraping_info['successful_scrapings'] / $scraping_info['total_scrapings']) * 100;
        
        if ($success_rate >= 80) {
            return 'excellent';
        } elseif ($success_rate >= 50) {
            return 'good';
        } else {
            return 'warning';
        }
    }
    
    /**
     * Obtener texto del estado de conexión
     */
    private static function get_connection_status_text($scraping_info) {
        if ($scraping_info['total_scrapings'] == 0) {
            return 'Sin intentos de conexión';
        }
        
        $success_rate = ($scraping_info['successful_scrapings'] / $scraping_info['total_scrapings']) * 100;
        
        if ($success_rate >= 80) {
            return 'Conexión estable';
        } elseif ($success_rate >= 50) {
            return 'Conexión intermitente';
        } else {
            return 'Hay problemas con la conexión';
        }
    }
    
    /**
     * Manejar reset de estadísticas del cron
     */
    private static function handle_reset_cron_stats() {
        // Resetear contadores del cron
        update_option('bcv_cron_total_executions', 0);
        update_option('bcv_cron_successful_executions', 0);
        update_option('bcv_cron_failed_executions', 0);
        
        echo '<div class="notice notice-success"><p>Contadores reiniciados correctamente.</p></div>';
    }
}
