<?php
/**
 * Suite de pruebas automatizadas para BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class BCV_Test_Suite {
    
    /**
     * Resultados de las pruebas
     * 
     * @var array
     */
    private $test_results = array();
    
    /**
     * Número de pruebas ejecutadas
     * 
     * @var int
     */
    private $tests_run = 0;
    
    /**
     * Número de pruebas exitosas
     * 
     * @var int
     */
    private $tests_passed = 0;
    
    /**
     * Ejecutar todas las pruebas
     * 
     * @return array Resultados de las pruebas
     */
    public function run_all_tests() {
        BCV_Logger::info('TEST', 'Iniciando suite de pruebas automatizadas');
        BCV_Performance_Monitor::start_timer('automated_tests');
        
        // Limpiar resultados anteriores
        $this->test_results = array();
        $this->tests_run = 0;
        $this->tests_passed = 0;
        
        // Ejecutar pruebas por categoría
        $this->run_database_tests();
        $this->run_scraper_tests();
        $this->run_cron_tests();
        $this->run_cache_tests();
        $this->run_performance_tests();
        
        // Compilar resultados finales
        $summary = array(
            'total_tests' => $this->tests_run,
            'passed' => $this->tests_passed,
            'failed' => $this->tests_run - $this->tests_passed,
            'success_rate' => $this->tests_run > 0 ? round(($this->tests_passed / $this->tests_run) * 100, 2) : 0,
            'results' => $this->test_results,
            'timestamp' => current_time('mysql')
        );
        
        BCV_Performance_Monitor::end_timer('automated_tests', array(
            'tests_run' => $this->tests_run,
            'success_rate' => $summary['success_rate'] . '%'
        ));
        
        BCV_Logger::info('TEST', 'Suite de pruebas completada', $summary);
        
        return $summary;
    }
    
    /**
     * Ejecutar pruebas de base de datos
     */
    private function run_database_tests() {
        $this->add_test_category('Database Tests');
        
        // Test 1: Verificar existencia de tabla
        $database = BCV_Database::get_instance();
        $this->assert_test(
            'table_exists',
            $database->table_exists(),
            'La tabla de base de datos debe existir'
        );
        
        // Test 2: Insertar precio de prueba
        $test_price = 35.1234;
        $inserted_id = $database->insert_price($test_price);
        $this->assert_test(
            'insert_price',
            $inserted_id !== false,
            'Debe poder insertar precios válidos'
        );
        
        // Test 3: Recuperar precio insertado
        if ($inserted_id) {
            $retrieved_price = $database->get_price_by_id($inserted_id);
            $this->assert_test(
                'retrieve_price',
                $retrieved_price && floatval($retrieved_price->precio) === $test_price,
                'Debe poder recuperar precios insertados'
            );
            
            // Limpiar datos de prueba
            $database->delete_price($inserted_id);
        }
        
        // Test 4: Validación de precios inválidos
        $invalid_insert = $database->insert_price(-10);
        $this->assert_test(
            'invalid_price_rejection',
            $invalid_insert === false,
            'Debe rechazar precios inválidos'
        );
        
        // Test 5: Estadísticas de base de datos
        $stats = $database->get_price_stats();
        $this->assert_test(
            'price_stats',
            is_array($stats) && isset($stats['total_records']),
            'Debe generar estadísticas válidas'
        );
    }
    
    /**
     * Ejecutar pruebas del scraper
     */
    private function run_scraper_tests() {
        $this->add_test_category('Scraper Tests');
        
        $scraper = new BCV_Scraper();
        
        // Test 1: Conectividad con BCV
        $connectivity = $scraper->test_connectivity();
        $this->assert_test(
            'bcv_connectivity',
            $connectivity['success'],
            'Debe poder conectarse al sitio del BCV'
        );
        
        // Test 2: Limpieza de texto de precios
        $reflection = new ReflectionClass($scraper);
        $clean_method = $reflection->getMethod('clean_rate_text');
        $clean_method->setAccessible(true);
        
        $test_cases = array(
            '36,5000' => 36.5,
            '36.5000' => 365000, // Puntos como miles
            'Bs. 36,50' => 36.5,
            'invalid' => false
        );
        
        foreach ($test_cases as $input => $expected) {
            $result = $clean_method->invoke($scraper, $input);
            $this->assert_test(
                "clean_text_{$input}",
                $result === $expected,
                "Debe limpiar correctamente el texto: {$input}"
            );
        }
        
        // Test 3: Caché de precios
        $cache_test_price = 37.25;
        $scraper->set_cached_price($cache_test_price);
        $cached_price = $scraper->get_cached_price();
        
        $this->assert_test(
            'price_caching',
            floatval($cached_price) === $cache_test_price,
            'Debe cachear precios correctamente'
        );
        
        // Limpiar caché de prueba
        $scraper->clear_cache();
    }
    
    /**
     * Ejecutar pruebas del cron
     */
    private function run_cron_tests() {
        $this->add_test_category('Cron Tests');
        
        $cron = new BCV_Cron();
        
        // Test 1: Configuración del cron
        $test_settings = array(
            'hours' => 2,
            'minutes' => 30,
            'seconds' => 0,
            'enabled' => true
        );
        
        $setup_result = $cron->setup_cron($test_settings);
        $this->assert_test(
            'cron_setup',
            $setup_result,
            'Debe poder configurar el cron'
        );
        
        // Test 2: Información del cron
        $cron_info = $cron->get_cron_info();
        $this->assert_test(
            'cron_info',
            is_array($cron_info) && isset($cron_info['is_scheduled']),
            'Debe proporcionar información del cron'
        );
        
        // Test 3: Cálculo de intervalos
        $reflection = new ReflectionClass($cron);
        $interval_method = $reflection->getMethod('calculate_interval');
        $interval_method->setAccessible(true);
        
        // Configurar settings temporalmente
        $settings_property = $reflection->getProperty('settings');
        $settings_property->setAccessible(true);
        $settings_property->setValue($cron, array('hours' => 1, 'minutes' => 30, 'seconds' => 0));
        
        $interval = $interval_method->invoke($cron);
        $expected_interval = (1 * 3600) + (30 * 60); // 5400 segundos
        
        $this->assert_test(
            'interval_calculation',
            $interval === $expected_interval,
            'Debe calcular intervalos correctamente'
        );
        
        // Test 4: Estadísticas del cron
        $stats = $cron->get_cron_stats();
        $this->assert_test(
            'cron_stats',
            is_array($stats) && isset($stats['total_executions']),
            'Debe generar estadísticas del cron'
        );
    }
    
    /**
     * Ejecutar pruebas de caché
     */
    private function run_cache_tests() {
        $this->add_test_category('Cache Tests');
        
        // Test 1: Transients básicos
        $test_key = 'bcv_test_cache';
        $test_value = array('test' => 'data', 'timestamp' => time());
        
        set_transient($test_key, $test_value, 300);
        $retrieved_value = get_transient($test_key);
        
        $this->assert_test(
            'transient_storage',
            $retrieved_value === $test_value,
            'Debe almacenar y recuperar transients'
        );
        
        // Test 2: Expiración de caché
        set_transient($test_key . '_expire', 'test', 1);
        sleep(2);
        $expired_value = get_transient($test_key . '_expire');
        
        $this->assert_test(
            'cache_expiration',
            $expired_value === false,
            'Debe expirar caché correctamente'
        );
        
        // Test 3: Limpieza de caché
        delete_transient($test_key);
        $deleted_value = get_transient($test_key);
        
        $this->assert_test(
            'cache_cleanup',
            $deleted_value === false,
            'Debe limpiar caché correctamente'
        );
    }
    
    /**
     * Ejecutar pruebas de rendimiento
     */
    private function run_performance_tests() {
        $this->add_test_category('Performance Tests');
        
        // Test 1: Tiempo de consulta de estadísticas
        $start_time = microtime(true);
        $database = BCV_Database::get_instance();
        $stats = $database->get_price_stats();
        $query_time = microtime(true) - $start_time;
        
        $this->assert_test(
            'stats_query_performance',
            $query_time < 1.0,
            'Las consultas de estadísticas deben ser rápidas (<1s)'
        );
        
        // Test 2: Uso de memoria
        $memory_start = memory_get_usage();
        
        // Simular operaciones que consumen memoria
        for ($i = 0; $i < 100; $i++) {
            $temp_data[] = array(
                'id' => $i,
                'precio' => rand(30, 50) + (rand(0, 9999) / 10000),
                'timestamp' => current_time('mysql')
            );
        }
        
        $memory_used = memory_get_usage() - $memory_start;
        unset($temp_data); // Limpiar memoria
        
        $this->assert_test(
            'memory_usage',
            $memory_used < (1024 * 1024), // Menos de 1MB
            'Debe usar memoria eficientemente'
        );
        
        // Test 3: Performance del logger
        $start_time = microtime(true);
        for ($i = 0; $i < 50; $i++) {
            BCV_Logger::debug('TEST', "Test log message {$i}");
        }
        $logger_time = microtime(true) - $start_time;
        
        $this->assert_test(
            'logger_performance',
            $logger_time < 0.1,
            'El logger debe ser eficiente (<0.1s para 50 mensajes)'
        );
    }
    
    /**
     * Añadir categoría de prueba
     * 
     * @param string $category Nombre de la categoría
     */
    private function add_test_category($category) {
        $this->test_results[$category] = array();
    }
    
    /**
     * Ejecutar una prueba y registrar resultado
     * 
     * @param string $test_name Nombre de la prueba
     * @param bool $condition Condición a evaluar
     * @param string $description Descripción de la prueba
     */
    private function assert_test($test_name, $condition, $description) {
        $this->tests_run++;
        
        $result = array(
            'name' => $test_name,
            'description' => $description,
            'passed' => (bool) $condition,
            'timestamp' => microtime(true)
        );
        
        if ($condition) {
            $this->tests_passed++;
            $result['status'] = 'PASS';
        } else {
            $result['status'] = 'FAIL';
            BCV_Logger::warning("Test failed: {$test_name}", array(
                'description' => $description
            ));
        }
        
        // Añadir a la última categoría
        $categories = array_keys($this->test_results);
        $last_category = end($categories);
        $this->test_results[$last_category][] = $result;
    }
    
    /**
     * Ejecutar pruebas de carga básicas
     * 
     * @param int $iterations Número de iteraciones
     * @return array Resultados de las pruebas de carga
     */
    public function run_load_tests($iterations = 10) {
        BCV_Logger::info('TEST', "Iniciando pruebas de carga con {$iterations} iteraciones");
        BCV_Performance_Monitor::start_timer('load_tests');
        
        $results = array(
            'iterations' => $iterations,
            'database_operations' => array(),
            'scraper_operations' => array(),
            'memory_usage' => array()
        );
        
        $database = BCV_Database::get_instance();
        
        for ($i = 0; $i < $iterations; $i++) {
            $iteration_start = microtime(true);
            $memory_start = memory_get_usage();
            
            // Test de base de datos
            $test_price = 30 + rand(0, 20) + (rand(0, 9999) / 10000);
            $insert_start = microtime(true);
            $inserted_id = $database->insert_price($test_price);
            $insert_time = microtime(true) - $insert_start;
            
            if ($inserted_id) {
                $stats_start = microtime(true);
                $stats = $database->get_price_stats();
                $stats_time = microtime(true) - $stats_start;
                
                $database->delete_price($inserted_id);
                
                $results['database_operations'][] = array(
                    'insert_time' => $insert_time,
                    'stats_time' => $stats_time
                );
            }
            
            $memory_used = memory_get_usage() - $memory_start;
            $results['memory_usage'][] = $memory_used;
            
            // Pequeña pausa entre iteraciones
            usleep(100000); // 0.1 segundos
        }
        
        // Calcular promedios
        $avg_insert_time = array_sum(array_column($results['database_operations'], 'insert_time')) / $iterations;
        $avg_stats_time = array_sum(array_column($results['database_operations'], 'stats_time')) / $iterations;
        $avg_memory = array_sum($results['memory_usage']) / $iterations;
        
        $summary = array(
            'avg_insert_time' => round($avg_insert_time, 4),
            'avg_stats_time' => round($avg_stats_time, 4),
            'avg_memory_usage' => $avg_memory,
            'peak_memory' => max($results['memory_usage']),
            'total_time' => microtime(true) - BCV_Performance_Monitor::get_instance()->timers['load_tests']
        );
        
        BCV_Performance_Monitor::end_timer('load_tests', $summary);
        BCV_Logger::info('TEST', 'Pruebas de carga completadas', $summary);
        
        return array_merge($results, array('summary' => $summary));
    }
}
