/**
 * Sistema de Seguimiento de Envíos - JavaScript Admin
 * WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Clase principal para el seguimiento de envíos
     */
    class WCVSShippingTracking {
        constructor() {
            this.init();
        }

        /**
         * Inicializar
         */
        init() {
            this.bindEvents();
            this.initTooltips();
        }

        /**
         * Vincular eventos
         */
        bindEvents() {
            // Botón de rastrear envío
            $(document).on('click', '.wcvs-track-shipment', (e) => {
                e.preventDefault();
                this.trackShipment($(e.target));
            });

            // Botón de actualizar estado
            $(document).on('click', '.wcvs-update-status', (e) => {
                e.preventDefault();
                this.updateStatus($(e.target));
            });

            // Botón de enviar notificación
            $(document).on('click', '.wcvs-send-notification', (e) => {
                e.preventDefault();
                this.sendNotification($(e.target));
            });

            // Filtro por estado
            $(document).on('change', '#wcvs-status-filter', (e) => {
                this.filterByStatus($(e.target).val());
            });

            // Búsqueda por número de seguimiento
            $(document).on('keyup', '#wcvs-tracking-search', (e) => {
                this.searchByTrackingNumber($(e.target).val());
            });

            // Actualización automática cada 5 minutos
            setInterval(() => {
                this.autoUpdateStatuses();
            }, 300000); // 5 minutos
        }

        /**
         * Rastrear envío
         *
         * @param {jQuery} $button Botón
         */
        trackShipment($button) {
            const trackingNumber = $button.data('tracking-number');
            const $row = $button.closest('tr');
            
            if (!trackingNumber) {
                this.showAlert('Error: Número de seguimiento no encontrado', 'error');
                return;
            }

            // Mostrar loading
            $button.prop('disabled', true).text(wcvs_shipping_tracking.strings.loading);

            // Realizar petición AJAX
            $.ajax({
                url: wcvs_shipping_tracking.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_track_shipment',
                    tracking_number: trackingNumber,
                    nonce: wcvs_shipping_tracking.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.displayTrackingInfo($row, response.data);
                        this.showAlert('Información de seguimiento actualizada', 'success');
                    } else {
                        this.showAlert(response.data || 'Error al rastrear envío', 'error');
                    }
                },
                error: () => {
                    this.showAlert('Error de conexión', 'error');
                },
                complete: () => {
                    $button.prop('disabled', false).text(wcvs_shipping_tracking.strings.track);
                }
            });
        }

        /**
         * Mostrar información de seguimiento
         *
         * @param {jQuery} $row Fila
         * @param {Object} data Datos de seguimiento
         */
        displayTrackingInfo($row, data) {
            const $statusCell = $row.find('td:nth-child(5)');
            const $lastUpdateCell = $row.find('td:nth-child(6)');
            
            // Actualizar estado
            $statusCell.html(`<span class="wcvs-status wcvs-status-${this.getStatusClass(data.status)}">${data.status}</span>`);
            
            // Actualizar última actualización
            $lastUpdateCell.text(new Date().toLocaleString());
            
            // Mostrar modal con detalles
            this.showTrackingModal(data);
        }

        /**
         * Mostrar modal de seguimiento
         *
         * @param {Object} data Datos de seguimiento
         */
        showTrackingModal(data) {
            const modalHtml = `
                <div class="wcvs-tracking-modal" style="display: none;">
                    <div class="wcvs-modal-overlay"></div>
                    <div class="wcvs-modal-content">
                        <div class="wcvs-modal-header">
                            <h3>Información de Seguimiento</h3>
                            <button class="wcvs-modal-close">&times;</button>
                        </div>
                        <div class="wcvs-modal-body">
                            <div class="wcvs-tracking-info">
                                <div class="wcvs-tracking-item">
                                    <strong>Número de Seguimiento:</strong>
                                    <span>${data.tracking_number}</span>
                                </div>
                                <div class="wcvs-tracking-item">
                                    <strong>Estado Actual:</strong>
                                    <span class="wcvs-status wcvs-status-${this.getStatusClass(data.status)}">${data.status}</span>
                                </div>
                                <div class="wcvs-tracking-item">
                                    <strong>Ubicación:</strong>
                                    <span>${data.location}</span>
                                </div>
                                <div class="wcvs-tracking-item">
                                    <strong>Entrega Estimada:</strong>
                                    <span>${data.estimated_delivery}</span>
                                </div>
                            </div>
                            <div class="wcvs-tracking-history">
                                <h4>Historial de Seguimiento</h4>
                                <div class="wcvs-tracking-timeline">
                                    ${this.renderTrackingHistory(data.history)}
                                </div>
                            </div>
                        </div>
                        <div class="wcvs-modal-footer">
                            <button class="button wcvs-modal-close">Cerrar</button>
                        </div>
                    </div>
                </div>
            `;

            // Remover modal anterior si existe
            $('.wcvs-tracking-modal').remove();
            
            // Añadir nuevo modal
            $('body').append(modalHtml);
            
            // Mostrar modal
            $('.wcvs-tracking-modal').fadeIn(300);
            
            // Vincular eventos del modal
            this.bindModalEvents();
        }

        /**
         * Renderizar historial de seguimiento
         *
         * @param {Array} history Historial
         * @return {string}
         */
        renderTrackingHistory(history) {
            if (!history || history.length === 0) {
                return '<p>No hay historial disponible</p>';
            }

            return history.map(item => `
                <div class="wcvs-tracking-event">
                    <div class="wcvs-tracking-date">${item.date}</div>
                    <div class="wcvs-tracking-status">${item.status}</div>
                    <div class="wcvs-tracking-location">${item.location}</div>
                </div>
            `).join('');
        }

        /**
         * Vincular eventos del modal
         */
        bindModalEvents() {
            $('.wcvs-modal-close, .wcvs-modal-overlay').on('click', () => {
                $('.wcvs-tracking-modal').fadeOut(300, function() {
                    $(this).remove();
                });
            });

            // Cerrar con tecla ESC
            $(document).on('keyup', (e) => {
                if (e.keyCode === 27) { // ESC
                    $('.wcvs-modal-close').click();
                }
            });
        }

        /**
         * Obtener clase CSS para estado
         *
         * @param {string} status Estado
         * @return {string}
         */
        getStatusClass(status) {
            const statusMap = {
                'Recogido': 'picked-up',
                'En tránsito': 'in-transit',
                'En ruta': 'in-transit',
                'Entregado': 'delivered',
                'Listo para recoger': 'ready-pickup',
                'Preparado': 'prepared'
            };
            
            return statusMap[status] || 'unknown';
        }

        /**
         * Actualizar estado
         *
         * @param {jQuery} $button Botón
         */
        updateStatus($button) {
            const orderId = $button.data('order-id');
            const newStatus = prompt('Nuevo estado del envío:');
            
            if (!newStatus) {
                return;
            }

            $button.prop('disabled', true).text('Actualizando...');

            $.ajax({
                url: wcvs_shipping_tracking.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_update_shipment_status',
                    order_id: orderId,
                    status: newStatus,
                    nonce: wcvs_shipping_tracking.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showAlert('Estado actualizado correctamente', 'success');
                        location.reload(); // Recargar página para mostrar cambios
                    } else {
                        this.showAlert(response.data || 'Error al actualizar estado', 'error');
                    }
                },
                error: () => {
                    this.showAlert('Error de conexión', 'error');
                },
                complete: () => {
                    $button.prop('disabled', false).text('Actualizar Estado');
                }
            });
        }

        /**
         * Enviar notificación
         *
         * @param {jQuery} $button Botón
         */
        sendNotification($button) {
            const orderId = $button.data('order-id');
            
            if (!confirm('¿Enviar notificación de cambio de estado al cliente?')) {
                return;
            }

            $button.prop('disabled', true).text('Enviando...');

            $.ajax({
                url: wcvs_shipping_tracking.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_send_status_notification',
                    order_id: orderId,
                    nonce: wcvs_shipping_tracking.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showAlert('Notificación enviada correctamente', 'success');
                    } else {
                        this.showAlert(response.data || 'Error al enviar notificación', 'error');
                    }
                },
                error: () => {
                    this.showAlert('Error de conexión', 'error');
                },
                complete: () => {
                    $button.prop('disabled', false).text('Enviar Notificación');
                }
            });
        }

        /**
         * Filtrar por estado
         *
         * @param {string} status Estado
         */
        filterByStatus(status) {
            if (status === 'all') {
                $('tbody tr').show();
            } else {
                $('tbody tr').hide();
                $(`tbody tr .wcvs-status-${this.getStatusClass(status)}`).closest('tr').show();
            }
        }

        /**
         * Buscar por número de seguimiento
         *
         * @param {string} searchTerm Término de búsqueda
         */
        searchByTrackingNumber(searchTerm) {
            if (searchTerm.length < 3) {
                $('tbody tr').show();
                return;
            }

            $('tbody tr').hide();
            $(`tbody tr:contains("${searchTerm}")`).show();
        }

        /**
         * Actualización automática de estados
         */
        autoUpdateStatuses() {
            $('.wcvs-track-shipment').each((index, button) => {
                const $button = $(button);
                const trackingNumber = $button.data('tracking-number');
                
                if (trackingNumber) {
                    this.trackShipment($button);
                }
            });
        }

        /**
         * Mostrar alerta
         *
         * @param {string} message Mensaje
         * @param {string} type Tipo
         */
        showAlert(message, type = 'info') {
            const alertClass = `notice notice-${type === 'error' ? 'error' : 'success'}`;
            const alertHtml = `
                <div class="${alertClass} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Descartar este aviso.</span>
                    </button>
                </div>
            `;

            // Remover alertas anteriores
            $('.notice').remove();
            
            // Añadir nueva alerta
            $('.wrap h1').after(alertHtml);
            
            // Auto-dismiss después de 5 segundos
            setTimeout(() => {
                $('.notice').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }

        /**
         * Inicializar tooltips
         */
        initTooltips() {
            $('[data-tooltip]').each(function() {
                const $element = $(this);
                const tooltip = $element.data('tooltip');
                
                $element.attr('title', tooltip);
            });
        }

        /**
         * Exportar datos de seguimiento
         *
         * @param {string} format Formato (csv, excel)
         */
        exportTrackingData(format = 'csv') {
            $.ajax({
                url: wcvs_shipping_tracking.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_export_tracking_data',
                    format: format,
                    nonce: wcvs_shipping_tracking.nonce
                },
                success: (response) => {
                    if (response.success) {
                        // Descargar archivo
                        const link = document.createElement('a');
                        link.href = response.data.url;
                        link.download = response.data.filename;
                        link.click();
                        
                        this.showAlert('Datos exportados correctamente', 'success');
                    } else {
                        this.showAlert(response.data || 'Error al exportar datos', 'error');
                    }
                },
                error: () => {
                    this.showAlert('Error de conexión', 'error');
                }
            });
        }

        /**
         * Generar reporte de seguimiento
         */
        generateTrackingReport() {
            $.ajax({
                url: wcvs_shipping_tracking.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcvs_generate_tracking_report',
                    nonce: wcvs_shipping_tracking.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showAlert('Reporte generado correctamente', 'success');
                        // Mostrar reporte en modal o nueva ventana
                        this.showReportModal(response.data);
                    } else {
                        this.showAlert(response.data || 'Error al generar reporte', 'error');
                    }
                },
                error: () => {
                    this.showAlert('Error de conexión', 'error');
                }
            });
        }

        /**
         * Mostrar modal de reporte
         *
         * @param {Object} data Datos del reporte
         */
        showReportModal(data) {
            const modalHtml = `
                <div class="wcvs-report-modal" style="display: none;">
                    <div class="wcvs-modal-overlay"></div>
                    <div class="wcvs-modal-content wcvs-modal-large">
                        <div class="wcvs-modal-header">
                            <h3>Reporte de Seguimiento</h3>
                            <button class="wcvs-modal-close">&times;</button>
                        </div>
                        <div class="wcvs-modal-body">
                            <div class="wcvs-report-stats">
                                <div class="wcvs-stat-item">
                                    <strong>Total de Envíos:</strong>
                                    <span>${data.total_shipments}</span>
                                </div>
                                <div class="wcvs-stat-item">
                                    <strong>Entregados:</strong>
                                    <span>${data.delivered}</span>
                                </div>
                                <div class="wcvs-stat-item">
                                    <strong>En Tránsito:</strong>
                                    <span>${data.in_transit}</span>
                                </div>
                                <div class="wcvs-stat-item">
                                    <strong>Tasa de Entrega:</strong>
                                    <span>${data.delivery_rate}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="wcvs-modal-footer">
                            <button class="button wcvs-modal-close">Cerrar</button>
                        </div>
                    </div>
                </div>
            `;

            // Remover modal anterior si existe
            $('.wcvs-report-modal').remove();
            
            // Añadir nuevo modal
            $('body').append(modalHtml);
            
            // Mostrar modal
            $('.wcvs-report-modal').fadeIn(300);
            
            // Vincular eventos del modal
            this.bindModalEvents();
        }
    }

    // Inicializar cuando el documento esté listo
    $(document).ready(() => {
        new WCVSShippingTracking();
    });

})(jQuery);
