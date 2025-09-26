/**
 * JavaScript para Sistema SENIAT
 * WooCommerce Venezuela Pro 2025
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Configurar fechas por defecto
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    // Configurar fechas en todos los formularios
    $('input[name="start_date"]').val(firstDay.toISOString().split('T')[0]);
    $('input[name="end_date"]').val(lastDay.toISOString().split('T')[0]);
    
    // Configurar mes actual para IVA
    $('#iva_month').val(String(today.getMonth() + 1).padStart(2, '0'));
    $('#iva_year').val(today.getFullYear());
    
    // Manejar formularios
    $('#wvp-sales-book-form').on('submit', function(e) {
        e.preventDefault();
        wvpExportSalesBook();
    });
    
    $('#wvp-igtf-form').on('submit', function(e) {
        e.preventDefault();
        wvpExportIGTF();
    });
    
    $('#wvp-iva-form').on('submit', function(e) {
        e.preventDefault();
        wvpExportIVA();
    });
    
    $('#wvp-invoices-form').on('submit', function(e) {
        e.preventDefault();
        wvpExportInvoices();
    });
});

function wvpExportSalesBook() {
    const form = document.getElementById('wvp-sales-book-form');
    if (!form) {
        alert('Formulario no encontrado');
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'wvp_export_seniat');
    formData.append('export_type', 'sales_book');
    formData.append('nonce', wvp_seniat_ajax.nonce);
    
    wvpShowLoading('Generando Libro de Ventas...');
    
    fetch(wvp_seniat_ajax.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        wvpHideLoading();
        if (data.success) {
            wvpShowResults(data.data);
        } else {
            alert('Error: ' + (data.data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        wvpHideLoading();
        console.error('Error:', error);
        alert('Error al generar el reporte: ' + error.message);
    });
}

function wvpExportIGTF() {
    const form = document.getElementById('wvp-igtf-form');
    if (!form) {
        alert('Formulario IGTF no encontrado');
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'wvp_export_seniat');
    formData.append('export_type', 'igtf');
    formData.append('nonce', wvp_seniat_ajax.nonce);
    
    wvpShowLoading('Generando Reporte IGTF...');
    
    fetch(wvp_seniat_ajax.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        wvpHideLoading();
        if (data.success) {
            wvpShowResults(data.data);
        } else {
            alert('Error: ' + (data.data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        wvpHideLoading();
        console.error('Error:', error);
        alert('Error al generar reporte IGTF: ' + error.message);
    });
}

function wvpExportIVA() {
    const form = document.getElementById('wvp-iva-form');
    if (!form) {
        alert('Formulario IVA no encontrado');
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'wvp_export_seniat');
    formData.append('export_type', 'iva');
    formData.append('nonce', wvp_seniat_ajax.nonce);
    
    wvpShowLoading('Generando Declaración IVA...');
    
    fetch(wvp_seniat_ajax.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        wvpHideLoading();
        if (data.success) {
            wvpShowResults(data.data);
        } else {
            alert('Error: ' + (data.data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        wvpHideLoading();
        console.error('Error:', error);
        alert('Error al generar declaración IVA: ' + error.message);
    });
}

function wvpExportInvoices() {
    console.log('🔍 DEBUG: Iniciando wvpExportInvoices...');
    
    // Verificar que el objeto AJAX esté disponible
    if (typeof wvp_seniat_ajax === 'undefined') {
        console.error('❌ ERROR: wvp_seniat_ajax no está definido');
        alert('Error: Configuración AJAX no disponible');
        return;
    }
    
    console.log('✅ DEBUG: wvp_seniat_ajax disponible:', wvp_seniat_ajax);
    
    const form = document.getElementById('wvp-invoices-form');
    if (!form) {
        console.error('❌ ERROR: Formulario wvp-invoices-form no encontrado');
        alert('Formulario de facturas no encontrado');
        return;
    }
    
    console.log('✅ DEBUG: Formulario encontrado');
    
    // Obtener datos del formulario de forma más simple
    const startDate = form.querySelector('input[name="start_date"]')?.value || '2025-01-01';
    const endDate = form.querySelector('input[name="end_date"]')?.value || '2025-12-31';
    const format = 'PDF Individual'; // Simplificado - solo PDF
    
    console.log('📋 DEBUG: Datos del formulario:', {
        startDate: startDate,
        endDate: endDate,
        format: format
    });
    
    // Crear FormData de forma más simple
    const formData = new FormData();
    formData.append('action', 'wvp_generate_invoice');
    formData.append('nonce', wvp_seniat_ajax.nonce);
    formData.append('start_date', startDate);
    formData.append('end_date', endDate);
    formData.append('format', format);
    
    console.log('📤 DEBUG: Enviando datos AJAX...');
    console.log('🔗 DEBUG: URL:', wvp_seniat_ajax.ajax_url);
    console.log('🔑 DEBUG: Nonce:', wvp_seniat_ajax.nonce);
    
    wvpShowLoading('Generando PDF...');
    
    // Usar fetch con más información de debug
    fetch(wvp_seniat_ajax.ajax_url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('📥 DEBUG: Respuesta recibida:', response);
        console.log('📊 DEBUG: Status:', response.status);
        console.log('📋 DEBUG: Headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return response.text(); // Primero obtener como texto para debug
    })
    .then(text => {
        console.log('📄 DEBUG: Respuesta en texto:', text);
        
        try {
            const data = JSON.parse(text);
            console.log('✅ DEBUG: JSON parseado correctamente:', data);
            
            wvpHideLoading();
            
            if (data.success) {
                console.log('🎉 DEBUG: Éxito! Mostrando resultados...');
                wvpShowResults(data.data);
            } else {
                console.error('❌ DEBUG: Error en respuesta:', data);
                alert('Error: ' + (data.data?.message || 'Error desconocido'));
            }
        } catch (parseError) {
            console.error('❌ DEBUG: Error parseando JSON:', parseError);
            console.error('📄 DEBUG: Texto que causó el error:', text);
            wvpHideLoading();
            alert('Error parseando respuesta del servidor: ' + parseError.message);
        }
    })
    .catch(error => {
        console.error('❌ DEBUG: Error en fetch:', error);
        wvpHideLoading();
        alert('Error al generar facturas: ' + error.message);
    });
}

function wvpShowResults(data) {
    console.log('🔍 DEBUG: Mostrando resultados...', data);
    
    const resultsContent = document.getElementById('wvp-results-content');
    const resultsDiv = document.getElementById('wvp-export-results');
    
    if (!resultsContent || !resultsDiv) {
        console.error('❌ DEBUG: Elementos de resultados no encontrados');
        return;
    }
    
    // Generar HTML para mostrar los resultados
    let html = '<div class="wvp-results-success">';
    html += '<h4>✅ ' + data.message + '</h4>';
    
    // Mostrar resumen
    if (data.summary) {
        html += '<div class="wvp-results-summary">';
        html += '<p><strong>Período:</strong> ' + data.summary.period + '</p>';
        html += '<p><strong>Formato:</strong> ' + data.summary.format + '</p>';
        html += '<p><strong>Total Órdenes:</strong> ' + data.summary.total_orders + '</p>';
        html += '<p><strong>Total USD:</strong> $' + data.summary.total_usd + '</p>';
        html += '<p><strong>Total VES:</strong> ' + data.summary.total_ves + ' VES</p>';
        html += '<p><strong>Tipo de Cambio BCV:</strong> ' + data.summary.bcv_rate + '</p>';
        html += '</div>';
    }
    
    // Mostrar archivo único para descarga
    if (data.file) {
        html += '<div class="wvp-file-download">';
        html += '<h5>📄 Reporte SENIAT Generado:</h5>';
        html += '<div class="wvp-download-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 2px solid #28a745; margin: 15px 0;">';
        html += '<h6 style="margin: 0 0 10px 0; color: #28a745;">✅ ' + data.file.filename + '</h6>';
        html += '<p style="margin: 5px 0; color: #666;">Tamaño: ' + (data.file.size / 1024).toFixed(1) + ' KB</p>';
        html += '<a href="' + data.file.url + '" target="_blank" class="wvp-download-btn" style="display: inline-block; background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 10px;">';
        html += '📥 Descargar Reporte SENIAT';
        html += '</a>';
        html += '<p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">Este archivo contiene toda la información fiscal requerida por SENIAT</p>';
        html += '</div>';
        html += '</div>';
    }
    
    html += '</div>';
    
    console.log('📄 DEBUG: HTML generado:', html);
    
    resultsContent.innerHTML = html;
    resultsDiv.style.display = 'block';
    
    // Scroll to results
    resultsDiv.scrollIntoView({ behavior: 'smooth' });
    
    console.log('✅ DEBUG: Resultados mostrados correctamente');
}

function wvpShowLoading(message) {
    // Crear overlay de loading si no existe
    let loadingOverlay = document.getElementById('wvp-loading-overlay');
    if (!loadingOverlay) {
        loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'wvp-loading-overlay';
        loadingOverlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        `;
        document.body.appendChild(loadingOverlay);
    }
    
    loadingOverlay.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
            <div class="wvp-loading" style="margin: 0 auto 15px auto;"></div>
            <p style="margin: 0; font-size: 16px; color: #333;">${message}</p>
        </div>
    `;
    loadingOverlay.style.display = 'flex';
}

function wvpHideLoading() {
    const loadingOverlay = document.getElementById('wvp-loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
}