<?php
/**
 * Clase para scraping del precio del dólar del plugin BCV Dólar Tracker
 * 
 * @package BCV_Dolar_Tracker
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class BCV_Scraper {
    
    /**
     * URL del BCV
     * 
     * @var string
     */
    private $bcv_url = 'https://www.bcv.org.ve/';
    
    /**
     * User agent para simular navegador
     * 
     * @var string
     */
    private $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    
    /**
     * Tiempo de caché en segundos (12 horas)
     * 
     * @var int
     */
    private $cache_time = 43200;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Hook para limpiar caché cuando sea necesario
        add_action('bcv_clear_cache', array($this, 'clear_cache'));
    }
    
    /**
     * Obtener precio del dólar desde BCV
     * 
     * @return float|false Precio del dólar o false si falla
     */
    public function scrape_bcv_rate() {
        // 1. Revisa si ya tenemos la tasa guardada en el caché
        $cached_rate = $this->get_cached_price();
        if (false !== $cached_rate) {
            error_log('BCV Dólar Tracker: Usando precio en caché: ' . $cached_rate);
            return $cached_rate;
        }
        
        // 2. Obtiene el HTML de la página del BCV
        $response = $this->make_request();
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            error_log('BCV Dólar Tracker: Error en request HTTP: ' . (is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_response_code($response)));
            return false;
        }
        
        $html = wp_remote_retrieve_body($response);
        
        // 3. Analiza el HTML para encontrar el dato
        $rate = $this->parse_html($html);
        
        if ($rate !== false && $rate > 0) {
            // Guardamos en caché y devolvemos el valor
            $this->set_cached_price($rate);
            error_log('BCV Dólar Tracker: Precio obtenido del BCV: ' . $rate);
            return $rate;
        }
        
        error_log('BCV Dólar Tracker: No se pudo obtener precio válido del BCV');
        return false;
    }
    
    /**
     * Realizar request HTTP al BCV
     * 
     * @return array|WP_Error Response o error
     */
    private function make_request() {
        // Validar URL antes de hacer la petición
        if (!BCV_Security::sanitize_url($this->bcv_url)) {
            BCV_Logger::error('URL del BCV inválida', array('url' => $this->bcv_url));
            BCV_Security::log_security_event('Invalid BCV URL', 'URL: ' . $this->bcv_url);
            return new WP_Error('invalid_url', 'URL del BCV inválida');
        }
        
        $args = array(
            'timeout' => 30,
            'user-agent' => $this->user_agent,
            'headers' => array(
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ),
            'sslverify' => true, // Habilitar verificación SSL para seguridad
            'redirection' => 5, // Límite de redirecciones
            'httpversion' => '1.1',
        );
        
        return wp_remote_get($this->bcv_url, $args);
    }
    
    /**
     * Parsear HTML para extraer el precio del dólar
     * 
     * @param string $html HTML de la página del BCV
     * @return float|false Precio del dólar o false si falla
     */
    private function parse_html($html) {
        // Validar que el HTML no esté vacío
        if (empty($html) || strlen($html) > 1000000) { // Límite de 1MB
            BCV_Logger::error('HTML inválido o demasiado grande', array('size' => strlen($html)));
            BCV_Security::log_security_event('Invalid HTML size', 'Size: ' . strlen($html));
            return false;
        }
        
        // Crear DOM document
        $dom = new DOMDocument();
        
        // Suprimir warnings de HTML mal formado
        libxml_use_internal_errors(true);
        
        // Cargar HTML con encoding UTF-8
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        
        // Limpiar errores de libxml
        libxml_clear_errors();
        
        // Crear XPath
        $xpath = new DOMXPath($dom);
        
        // Buscar el precio del dólar usando el selector correcto
        $query = "//div[@id='dolar']//strong";
        $elements = $xpath->query($query);
        
        if ($elements && $elements->length > 0) {
            // Obtenemos el texto del primer elemento encontrado
            $rate_text = $elements[0]->nodeValue;
            
            // Limpiamos el texto para convertirlo en un número utilizable
            $rate = $this->clean_rate_text($rate_text);
            
            if ($rate > 0) {
                return $rate;
            }
        }
        
        // Si no se encuentra con el selector principal, intentar alternativos
        $alternative_selectors = array(
            "//div[contains(@class, 'dolar')]//strong",
            "//div[contains(@class, 'tasa')]//strong",
            "//span[contains(@class, 'dolar')]",
            "//td[contains(text(), 'USD')]/following-sibling::td"
        );
        
        foreach ($alternative_selectors as $selector) {
            $elements = $xpath->query($selector);
            if ($elements && $elements->length > 0) {
                $rate_text = $elements[0]->nodeValue;
                $rate = $this->clean_rate_text($rate_text);
                
                if ($rate > 0) {
                    error_log('BCV Dólar Tracker: Precio encontrado con selector alternativo: ' . $selector);
                    return $rate;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Limpiar texto del precio para convertirlo en número
     * 
     * @param string $text Texto del precio
     * @return float Precio limpio
     */
    private function clean_rate_text($text) {
        // Sanitizar entrada
        $text = BCV_Security::sanitize_text($text);
        
        // Quita espacios en blanco al inicio y al final
        $text = trim($text);
        
        // Validar longitud del texto
        if (strlen($text) > 50) {
            BCV_Logger::warning('Texto de precio demasiado largo', array('length' => strlen($text)));
            return false;
        }
        
        // Quita caracteres no numéricos excepto punto y coma
        $text = preg_replace('/[^0-9.,]/', '', $text);
        
        // Quita los puntos de miles (si los hubiera)
        $text = str_replace('.', '', $text);
        
        // Cambia la coma decimal por un punto
        $text = str_replace(',', '.', $text);
        
        // Convierte a float
        $rate = (float) $text;
        
        // Verificar que sea un número válido y esté en rango seguro
        if (is_numeric($rate) && $rate > 0 && $rate < 1000000) {
            return $rate;
        }
        
        // Log de intento de precio inválido
        BCV_Security::log_security_event('Invalid rate text', 'Text: ' . $text . ', Rate: ' . $rate);
        
        return false;
    }
    
    /**
     * Verificar si el precio está en caché
     * 
     * @return float|false Precio en caché o false si no existe
     */
    public function get_cached_price() {
        return get_transient('bcv_scraped_rate');
    }
    
    /**
     * Guardar precio en caché
     * 
     * @param float $price Precio del dólar
     * @return bool True si se guardó correctamente
     */
    public function set_cached_price($price) {
        return set_transient('bcv_scraped_rate', $price, $this->cache_time);
    }
    
    /**
     * Limpiar caché
     * 
     * @return bool True si se limpió correctamente
     */
    public function clear_cache() {
        $deleted = delete_transient('bcv_scraped_rate');
        
        if ($deleted) {
            error_log('BCV Dólar Tracker: Caché limpiado correctamente');
        } else {
            error_log('BCV Dólar Tracker: No se encontró caché para limpiar');
        }
        
        return $deleted;
    }
    
    /**
     * Guardar precio en base de datos
     * 
     * @param float $price Precio del dólar
     * @param string $datatime Fecha y hora del precio (opcional)
     * @return int|false ID del registro insertado o false si falla
     */
    public function save_price($price, $datatime = null) {
        if ($datatime === null) {
            $datatime = current_time('mysql');
        }
        
        $database = new BCV_Database();
        return $database->insert_price($price, $datatime);
    }
    
    /**
     * Obtener información del scraping
     * 
     * @return array Información del scraping
     */
    public function get_scraping_info() {
        $cached_price = $this->get_cached_price();
        $cache_expiry = get_option('_transient_timeout_bcv_scraped_rate', 0);
        
        return array(
            'cached_price' => $cached_price,
            'cache_expiry' => $cache_expiry ? date('Y-m-d H:i:s', $cache_expiry) : 'No disponible',
            'cache_valid' => $cached_price !== false,
            'last_scraping' => get_option('bcv_last_scraping_time', 'Nunca'),
            'scraping_attempts' => get_option('bcv_scraping_attempts', 0),
            'successful_scrapings' => get_option('bcv_successful_scrapings', 0),
            'failed_scrapings' => get_option('bcv_failed_scrapings', 0)
        );
    }
    
    /**
     * Actualizar estadísticas de scraping
     * 
     * @param bool $success Si el scraping fue exitoso
     */
    public function update_scraping_stats($success) {
        $attempts = get_option('bcv_scraping_attempts', 0) + 1;
        update_option('bcv_scraping_attempts', $attempts);
        
        if ($success) {
            $successful = get_option('bcv_successful_scrapings', 0) + 1;
            update_option('bcv_successful_scrapings', $successful);
        } else {
            $failed = get_option('bcv_failed_scrapings', 0) + 1;
            update_option('bcv_failed_scrapings', $failed);
        }
        
        // Actualizar tiempo del último scraping
        update_option('bcv_last_scraping_time', current_time('mysql'));
    }
    
    /**
     * Resetear estadísticas de scraping
     */
    public function reset_scraping_stats() {
        delete_option('bcv_scraping_attempts');
        delete_option('bcv_successful_scrapings');
        delete_option('bcv_failed_scrapings');
        delete_option('bcv_last_scraping_time');
        
        error_log('BCV Dólar Tracker: Estadísticas de scraping reseteadas');
    }
    
    /**
     * Probar conectividad con el BCV
     * 
     * @return array Resultado de la prueba
     */
    public function test_connectivity() {
        $response = $this->make_request();
        
        $result = array(
            'success' => false,
            'message' => '',
            'response_code' => 0,
            'response_time' => 0,
            'error' => null
        );
        
        if (is_wp_error($response)) {
            $result['error'] = $response->get_error_message();
            $result['message'] = 'Error de conexión: ' . $result['error'];
        } else {
            $result['response_code'] = wp_remote_retrieve_response_code($response);
            $result['response_time'] = wp_remote_retrieve_header($response, 'response_time');
            
            if ($result['response_code'] === 200) {
                $result['success'] = true;
                $result['message'] = 'Conexión exitosa con el BCV';
            } else {
                $result['message'] = 'Error HTTP: ' . $result['response_code'];
            }
        }
        
        return $result;
    }
    
    /**
     * Obtener precio del dólar con reintentos
     * 
     * @param int $max_retries Número máximo de reintentos
     * @param bool $update_stats Si debe actualizar estadísticas (por defecto true)
     * @return float|false Precio del dólar o false si falla
     */
    public function scrape_with_retries($max_retries = 3, $update_stats = true) {
        for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
            error_log("BCV Dólar Tracker: Intento de scraping {$attempt} de {$max_retries}");
            
            $rate = $this->scrape_bcv_rate();
            
            if ($rate !== false) {
                if ($update_stats) {
                    $this->update_scraping_stats(true);
                }
                return $rate;
            }
            
            // Solo actualizar estadísticas en el último intento fallido
            if ($attempt === $max_retries && $update_stats) {
                $this->update_scraping_stats(false);
            }
            
            // Esperar antes del siguiente intento (exponencial backoff)
            if ($attempt < $max_retries) {
                $wait_time = pow(2, $attempt) * 5; // 10, 20, 40 segundos
                error_log("BCV Dólar Tracker: Esperando {$wait_time} segundos antes del siguiente intento");
                sleep($wait_time);
            }
        }
        
        error_log("BCV Dólar Tracker: Todos los intentos de scraping fallaron después de {$max_retries} reintentos");
        return false;
    }
}
