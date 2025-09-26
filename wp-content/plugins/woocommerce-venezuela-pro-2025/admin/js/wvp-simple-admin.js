jQuery(document).ready(function($) {
    // WVP Simple Admin JavaScript
    
    // Confirmación para exportaciones SENIAT
    $('button[name="export_iva"], button[name="export_igtf"], button[name="export_invoices"]').on('click', function(e) {
        var exportType = $(this).attr('name').replace('export_', '');
        var exportName = exportType.toUpperCase();
        
        if (!confirm('¿Está seguro de que desea exportar ' + exportName + '?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Validación de fechas
    $('input[type="date"]').on('change', function() {
        var startDate = $('input[name="start_date"]').val();
        var endDate = $('input[name="end_date"]').val();
        
        if (startDate && endDate && startDate > endDate) {
            alert('La fecha de inicio no puede ser mayor que la fecha de fin.');
            $(this).val('');
        }
    });
    
    // Auto-actualizar tasa USD si BCV está disponible
    if (typeof BCV_Dolar_Tracker !== 'undefined') {
        $('#wvp_usd_rate').on('blur', function() {
            var currentRate = BCV_Dolar_Tracker.get_current_rate();
            if (currentRate > 0) {
                $(this).val(currentRate.toFixed(2));
            }
        });
    }
});
