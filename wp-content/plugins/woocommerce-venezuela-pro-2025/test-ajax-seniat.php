<?php
/**
 * Test AJAX para debugging SENIAT
 */

// Activar error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar WordPress
require_once('../../../wp-config.php');

echo "<!DOCTYPE html><html><head><title>Test AJAX SENIAT</title></head><body>";
echo "<h1>🔍 Test AJAX SENIAT</h1>";

// Verificar si WordPress está cargado
if (!function_exists('wp_verify_nonce')) {
    echo "❌ WordPress no está cargado correctamente";
    exit;
}

echo "✅ WordPress cargado correctamente<br>";

// Verificar si el plugin está activo
if (!class_exists('WVP_SENIAT_Exporter')) {
    echo "❌ Clase WVP_SENIAT_Exporter no encontrada<br>";
    exit;
}

echo "✅ Clase WVP_SENIAT_Exporter encontrada<br>";

// Verificar AJAX URL
$ajax_url = admin_url('admin-ajax.php');
echo "✅ AJAX URL: " . $ajax_url . "<br>";

// Verificar nonce
$nonce = wp_create_nonce('wvp_seniat_nonce');
echo "✅ Nonce generado: " . $nonce . "<br>";

// Verificar si el hook está registrado
if (!has_action('wp_ajax_wvp_generate_invoice')) {
    echo "❌ Hook wp_ajax_wvp_generate_invoice no registrado<br>";
} else {
    echo "✅ Hook wp_ajax_wvp_generate_invoice registrado<br>";
}

// Test de formulario
echo "<h2>🧪 Test de Formulario</h2>";
echo "<form id='test-form'>";
echo "<input type='hidden' name='action' value='wvp_generate_invoice'>";
echo "<input type='hidden' name='nonce' value='" . $nonce . "'>";
echo "<input type='text' name='start_date' value='2025-09-01'>";
echo "<input type='text' name='end_date' value='2025-09-30'>";
echo "<select name='format'>";
echo "<option value='HTML'>HTML</option>";
echo "<option value='PDF Individual'>PDF Individual</option>";
echo "<option value='PDF Lote'>PDF Lote</option>";
echo "</select>";
echo "<button type='button' onclick='testAjax()'>Test AJAX</button>";
echo "</form>";

echo "<div id='result'></div>";

echo "<script>";
echo "function testAjax() {";
echo "    const form = document.getElementById('test-form');";
echo "    const formData = new FormData(form);";
echo "    ";
echo "    console.log('Enviando AJAX...');";
echo "    ";
echo "    fetch('" . $ajax_url . "', {";
echo "        method: 'POST',";
echo "        body: formData";
echo "    })";
echo "    .then(response => {";
echo "        console.log('Response status:', response.status);";
echo "        console.log('Response headers:', response.headers);";
echo "        return response.text();";
echo "    })";
echo "    .then(text => {";
echo "        console.log('Response text:', text);";
echo "        try {";
echo "            const data = JSON.parse(text);";
echo "            document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';";
echo "        } catch(e) {";
echo "            document.getElementById('result').innerHTML = '<pre>Error parsing JSON: ' + e.message + '<br>Response: ' + text + '</pre>';";
echo "        }";
echo "    })";
echo "    .catch(error => {";
echo "        console.error('Error:', error);";
echo "        document.getElementById('result').innerHTML = '<pre>Error: ' + error.message + '</pre>';";
echo "    });";
echo "}";
echo "</script>";

echo "</body></html>";
?>
