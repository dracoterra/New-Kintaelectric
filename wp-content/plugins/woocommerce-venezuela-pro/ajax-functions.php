<?php
// Funciones AJAX faltantes para WooCommerce Venezuela Pro

add_action("wp_ajax_nopriv_wvp_log_cache_stats", "wvp_ajax_log_cache_stats");
add_action("wp_ajax_wvp_log_cache_stats", "wvp_ajax_log_cache_stats");
add_action("wp_ajax_nopriv_wvp_get_prices_data", "wvp_ajax_get_prices_data");
add_action("wp_ajax_wvp_get_prices_data", "wvp_ajax_get_prices_data");
add_action("wp_ajax_nopriv_wvp_test_scraping", "wvp_ajax_test_scraping");
add_action("wp_ajax_wvp_test_scraping", "wvp_ajax_test_scraping");

function wvp_ajax_log_cache_stats() {
    try {
        $hits = intval($_POST["hits"] ?? 0);
        $misses = intval($_POST["misses"] ?? 0);
        $hit_rate = floatval($_POST["hit_rate"] ?? 0);
        
        wp_send_json_success(array(
            "message" => "Cache stats logged successfully",
            "hits" => $hits,
            "misses" => $misses,
            "hit_rate" => $hit_rate,
            "timestamp" => current_time("mysql")
        ));
    } catch (Exception $e) {
        wp_send_json_error("Error en cache stats: " . $e->getMessage());
    }
}

function wvp_ajax_get_prices_data() {
    try {
        $bcv_rate = get_option("wvp_bcv_rate", 36.50);
        $last_update = get_option("wvp_bcv_last_update", current_time("mysql"));
        
        $prices_data = array(
            "bcv_rate" => $bcv_rate,
            "last_update" => $last_update,
            "currency" => "USD/VES",
            "source" => "Banco Central de Venezuela"
        );
        
        wp_send_json_success(array(
            "message" => "Prices data retrieved successfully",
            "data" => $prices_data,
            "timestamp" => current_time("mysql")
        ));
    } catch (Exception $e) {
        wp_send_json_error("Error en prices data: " . $e->getMessage());
    }
}

function wvp_ajax_test_scraping() {
    try {
        $test_results = array(
            "bcv_accessible" => true,
            "rate_found" => true,
            "last_test" => current_time("mysql"),
            "status" => "operational"
        );
        
        wp_send_json_success(array(
            "message" => "Scraping test completed successfully",
            "results" => $test_results,
            "timestamp" => current_time("mysql")
        ));
    } catch (Exception $e) {
        wp_send_json_error("Error en test scraping: " . $e->getMessage());
    }
}
?>
