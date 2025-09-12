/**
 * Scripts para Reportes SENIAT
 * WooCommerce Venezuela Pro
 */

function wvpPrintReport() {
    // Crear una nueva ventana para imprimir solo el reporte
    var reportContainer = document.querySelector('.wvp-fiscal-report-container');
    if (!reportContainer) {
        alert('No se encontró el reporte para imprimir');
        return;
    }
    
    var printContent = reportContainer.innerHTML;
    var printWindow = window.open('', '_blank', 'width=1000,height=800,scrollbars=yes');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Reporte SENIAT</title>
            <meta charset="UTF-8">
            <style>
                @media print {
                    @page {
                        margin: 1cm;
                        size: A4;
                    }
                }
                
                body { 
                    margin: 0; 
                    padding: 20px; 
                    font-family: Arial, sans-serif; 
                    font-size: 12px;
                    line-height: 1.4;
                }
                
                .wvp-fiscal-table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-top: 20px; 
                }
                
                .wvp-fiscal-table th, 
                .wvp-fiscal-table td { 
                    border: 1px solid #333; 
                    padding: 6px; 
                    text-align: left; 
                    font-size: 10px;
                }
                
                .wvp-fiscal-table th { 
                    background-color: #f0f0f0; 
                    color: #000; 
                    font-weight: bold; 
                }
                
                .wvp-fiscal-table tbody tr:nth-child(even) { 
                    background-color: #f9f9f9; 
                }
                
                .wvp-report-header { 
                    border-bottom: 2px solid #333; 
                    padding-bottom: 15px; 
                    margin-bottom: 20px; 
                    text-align: center; 
                }
                
                .wvp-report-title h2 { 
                    color: #000; 
                    font-size: 20px; 
                    margin: 0; 
                }
                
                .wvp-report-title h3 { 
                    color: #333; 
                    font-size: 14px; 
                    margin: 5px 0 0 0; 
                }
                
                .wvp-company-info { 
                    margin-top: 15px; 
                    text-align: left; 
                    background: #f5f5f5; 
                    padding: 10px; 
                    border: 1px solid #ddd;
                }
                
                .wvp-report-actions,
                .wvp-report-filters { 
                    display: none !important; 
                }
                
                .wvp-executive-summary,
                .wvp-financial-analysis {
                    page-break-inside: avoid;
                    margin-bottom: 20px;
                }
                
                .wvp-summary-grid,
                .wvp-analysis-grid {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 10px;
                    margin-bottom: 15px;
                }
                
                .wvp-summary-item,
                .wvp-analysis-item {
                    border: 1px solid #ddd;
                    padding: 8px;
                    background: #f9f9f9;
                }
                
                .wvp-summary-label,
                .wvp-analysis-item h4 {
                    font-weight: bold;
                    margin-bottom: 5px;
                    font-size: 11px;
                }
                
                .wvp-summary-value {
                    font-size: 14px;
                    font-weight: bold;
                    color: #0073aa;
                }
            </style>
        </head>
        <body>
            <div class="wvp-fiscal-report-container">
                ${printContent}
            </div>
            <script>
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                        window.close();
                    }, 1000);
                };
            </script>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
}

// Función para generar número de control automático
function generateControlNumber() {
    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var seconds = String(now.getSeconds()).padStart(2, '0');
    
    return year + month + day + hours + minutes + seconds;
}

// Inicialización cuando el DOM esté listo
jQuery(document).ready(function($) {
    // Agregar funcionalidad adicional si es necesario
    console.log('WVP Seniat Reports: Scripts cargados correctamente');
});
