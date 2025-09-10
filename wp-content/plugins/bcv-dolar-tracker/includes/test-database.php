<?php
/**
 * Archivo de prueba para verificar la funcionalidad de base de datos
 * Este archivo se puede incluir temporalmente para testing
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Función de prueba para verificar la base de datos
function bcv_test_database_functionality() {
    if (!current_user_can('manage_options')) {
        wp_die('Acceso denegado');
    }
    
    echo '<div class="wrap">';
    echo '<h1>Prueba de Base de Datos - BCV Dólar Tracker</h1>';
    
    // Verificar si la tabla existe
    global $wpdb;
    $table_name = $wpdb->prefix . 'bcv_precio_dolar';
    
    echo '<h2>Estado de la Base de Datos</h2>';
    
    // Verificar existencia de la tabla
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    echo '<p><strong>Tabla existe:</strong> ' . ($table_exists ? '✅ Sí' : '❌ No') . '</p>';
    
    if ($table_exists) {
        // Verificar estructura de la tabla
        $columns = $wpdb->get_results("DESCRIBE {$table_name}");
        echo '<h3>Estructura de la Tabla</h3>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>';
        echo '<tbody>';
        foreach ($columns as $column) {
            echo '<tr>';
            echo '<td>' . esc_html($column->Field) . '</td>';
            echo '<td>' . esc_html($column->Type) . '</td>';
            echo '<td>' . esc_html($column->Null) . '</td>';
            echo '<td>' . esc_html($column->Key) . '</td>';
            echo '<td>' . esc_html($column->Default) . '</td>';
            echo '<td>' . esc_html($column->Extra) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        
        // Verificar versión de la base de datos
        $db_version = get_option('bcv_db_version', 'No configurada');
        echo '<p><strong>Versión de BD:</strong> ' . esc_html($db_version) . '</p>';
        
        // Contar registros
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        echo '<p><strong>Total de registros:</strong> ' . esc_html($count) . '</p>';
        
        // Mostrar últimos 5 registros
        if ($count > 0) {
            $recent_records = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY datatime DESC LIMIT 5");
            echo '<h3>Últimos 5 Registros</h3>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>ID</th><th>Fecha/Hora</th><th>Precio</th><th>Creado</th><th>Actualizado</th></tr></thead>';
            echo '<tbody>';
            foreach ($recent_records as $record) {
                echo '<tr>';
                echo '<td>' . esc_html($record->id) . '</td>';
                echo '<td>' . esc_html($record->datatime) . '</td>';
                echo '<td>' . esc_html($record->precio) . '</td>';
                echo '<td>' . esc_html($record->created_at) . '</td>';
                echo '<td>' . esc_html($record->updated_at) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        
        // Botón para insertar registro de prueba
        if (isset($_POST['insert_test_record'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'insert_test_record')) {
                $test_price = 35.1234;
                $test_datetime = current_time('mysql');
                
                $result = $wpdb->insert(
                    $table_name,
                    array(
                        'datatime' => $test_datetime,
                        'precio' => $test_price
                    ),
                    array('%s', '%f')
                );
                
                if ($result !== false) {
                    echo '<div class="notice notice-success"><p>✅ Registro de prueba insertado correctamente</p></div>';
                    echo '<script>location.reload();</script>';
                } else {
                    echo '<div class="notice notice-error"><p>❌ Error al insertar registro de prueba</p></div>';
                }
            }
        }
        
        echo '<form method="post" style="margin-top: 20px;">';
        wp_nonce_field('insert_test_record');
        echo '<input type="submit" name="insert_test_record" class="button button-primary" value="Insertar Registro de Prueba">';
        echo '</form>';
        
    } else {
        echo '<div class="notice notice-error"><p>❌ La tabla no existe. Activa el plugin para crearla.</p></div>';
    }
    
    echo '</div>';
}

// Hook para añadir página de prueba (solo en desarrollo)
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_menu', function() {
        add_submenu_page(
            'tools.php',
            'Prueba BD - BCV',
            'Prueba BD - BCV',
            'manage_options',
            'bcv-test-db',
            'bcv_test_database_functionality'
        );
    });
}
