<?php
/**
 * Script temporal para forzar la ejecución del cron
 * Acceder desde: /wp-content/plugins/bcv-dolar-tracker/force-cron.php
 */

// Cargar WordPress
require_once('../../../wp-config.php');

echo "<h1>=== FORZAR EJECUCIÓN DEL CRON ===</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Limpiar el transient que previene ejecuciones frecuentes
delete_transient('bcv_cron_last_execution');
echo "<p>✅ Transient 'bcv_cron_last_execution' eliminado</p>";

// Cargar la clase del cron
require_once('includes/class-bcv-cron.php');

// Crear instancia y ejecutar manualmente
$cron = new BCV_Cron();
echo "<p>✅ Instancia de BCV_Cron creada</p>";

// Ejecutar scraping manual
echo "<h2>Ejecutando scraping manual...</h2>";
$result = $cron->execute_manual_scraping();

echo "<h3>Resultado:</h3>";
echo "<ul>";
echo "<li><strong>Éxito:</strong> " . ($result['success'] ? 'Sí' : 'No') . "</li>";
echo "<li><strong>Mensaje:</strong> " . $result['message'] . "</li>";
echo "<li><strong>Precio:</strong> " . ($result['price'] ?? 'N/A') . "</li>";
echo "<li><strong>Timestamp:</strong> " . $result['timestamp'] . "</li>";
echo "</ul>";

// Mostrar información del cron
echo "<h2>Información del Cron:</h2>";
$cron_info = $cron->get_cron_info();
echo "<ul>";
echo "<li><strong>Próxima ejecución:</strong> " . $cron_info['next_run'] . "</li>";
echo "<li><strong>Intervalo:</strong> " . $cron_info['interval_formatted'] . "</li>";
echo "<li><strong>Programado:</strong> " . ($cron_info['is_scheduled'] ? 'Sí' : 'No') . "</li>";
echo "</ul>";

echo "<h2>=== FIN ===</h2>";
echo "<p><em>Este archivo se puede eliminar después de la prueba.</em></p>";
?>
