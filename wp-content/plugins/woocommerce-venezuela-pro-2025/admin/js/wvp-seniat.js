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
    
    // Manejar botón de vista previa
    $(document).on('click', 'button[onclick="wvpPreviewSalesBook()"]', function(e) {
        e.preventDefault();
        wvpPreviewSalesBook();
    });
    
    // Manejar cierre de modal
    $(document).on('click', '.wvp-modal-close', function(e) {
        e.preventDefault();
        wvpClosePreview();
    });
    
    // Manejar botón de exportar desde vista previa
    $(document).on('click', 'button[onclick="wvpExportFromPreview()"]', function(e) {
        e.preventDefault();
        wvpExportFromPreview();
    });
    
    // Cerrar modal al hacer clic fuera
    $(document).on('click', '.wvp-modal', function(e) {
        if (e.target === this) {
            wvpClosePreview();
        }
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

function wvpPreviewSalesBook() {
    const form = document.getElementById('wvp-sales-book-form');
    if (!form) {
        alert('Formulario no encontrado');
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'wvp_export_seniat');
    formData.append('export_type', 'sales_book');
    formData.append('preview', 'true');
    formData.append('nonce', wvp_seniat_ajax.nonce);
    
    wvpShowLoading('Generando vista previa...');
    
    fetch(wvp_seniat_ajax.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        wvpHideLoading();
        if (data.success) {
            const previewContent = document.getElementById('wvp-preview-content');
            const previewModal = document.getElementById('wvp-preview-modal');
            
            if (previewContent && previewModal) {
                previewContent.innerHTML = data.data.preview;
                previewModal.style.display = 'block';
            }
        } else {
            alert('Error: ' + (data.data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        wvpHideLoading();
        console.error('Error:', error);
        alert('Error al generar vista previa: ' + error.message);
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
    const form = document.getElementById('wvp-invoices-form');
    if (!form) {
        alert('Formulario de facturas no encontrado');
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'wvp_export_seniat');
    formData.append('export_type', 'invoices');
    formData.append('nonce', wvp_seniat_ajax.nonce);
    
    wvpShowLoading('Generando Facturas...');
    
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
        alert('Error al generar facturas: ' + error.message);
    });
}

function wvpShowResults(data) {
    const resultsContent = document.getElementById('wvp-results-content');
    const resultsDiv = document.getElementById('wvp-export-results');
    
    if (resultsContent && resultsDiv) {
        resultsContent.innerHTML = data.html;
        resultsDiv.style.display = 'block';
        
        // Scroll to results
        resultsDiv.scrollIntoView({ behavior: 'smooth' });
    }
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

function wvpClosePreview() {
    const previewModal = document.getElementById('wvp-preview-modal');
    if (previewModal) {
        previewModal.style.display = 'none';
    }
}

function wvpExportFromPreview() {
    wvpClosePreview();
    wvpExportSalesBook();
}
