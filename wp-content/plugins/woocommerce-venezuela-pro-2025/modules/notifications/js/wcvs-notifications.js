/**
 * JavaScript para el módulo de notificaciones - WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Objeto principal del módulo de notificaciones
    window.WCVS_Notifications = {
        
        // Configuración
        config: {
            ajaxUrl: wcvs_notifications_ajax.ajax_url,
            nonce: wcvs_notifications_ajax.nonce,
            refreshInterval: 30000, // 30 segundos
            maxRetries: 3,
            retryDelay: 1000
        },

        // Estado del módulo
        state: {
            currentPage: 1,
            totalPages: 1,
            isLoading: false,
            refreshTimer: null,
            unreadCount: 0
        },

        // Inicializar el módulo
        init: function() {
            this.bindEvents();
            this.loadNotifications();
            this.startAutoRefresh();
            this.updateUnreadCount();
        },

        // Vincular eventos
        bindEvents: function() {
            var self = this;

            // Filtros
            $(document).on('change', '.wcvs-notifications-filters select, .wcvs-notifications-filters input', function() {
                self.state.currentPage = 1;
                self.loadNotifications();
            });

            // Botones de acción
            $(document).on('click', '.wcvs-btn-mark-all-read', function(e) {
                e.preventDefault();
                self.markAllAsRead();
            });

            $(document).on('click', '.wcvs-btn-refresh', function(e) {
                e.preventDefault();
                self.loadNotifications();
            });

            $(document).on('click', '.wcvs-btn-clear-all', function(e) {
                e.preventDefault();
                self.clearAllNotifications();
            });

            // Notificaciones individuales
            $(document).on('click', '.wcvs-notification-item', function(e) {
                e.preventDefault();
                var notificationId = $(this).data('notification-id');
                self.showNotificationDetails(notificationId);
            });

            $(document).on('click', '.wcvs-notification-actions button', function(e) {
                e.stopPropagation();
                var action = $(this).data('action');
                var notificationId = $(this).closest('.wcvs-notification-item').data('notification-id');
                
                if (action === 'mark-read') {
                    self.markAsRead(notificationId);
                } else if (action === 'delete') {
                    self.deleteNotification(notificationId);
                }
            });

            // Paginación
            $(document).on('click', '.wcvs-pagination button', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                if (page && page !== self.state.currentPage) {
                    self.state.currentPage = page;
                    self.loadNotifications();
                }
            });

            // Modal
            $(document).on('click', '.wcvs-modal-close, .wcvs-notification-modal', function(e) {
                if (e.target === this) {
                    self.closeModal();
                }
            });

            // Teclado
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    self.closeModal();
                }
            });

            // Auto-refresh cuando la ventana gana foco
            $(window).on('focus', function() {
                self.loadNotifications();
            });
        },

        // Cargar notificaciones
        loadNotifications: function() {
            var self = this;
            
            if (self.state.isLoading) {
                return;
            }

            self.state.isLoading = true;
            self.showLoading();

            var filters = self.getFilters();
            filters.page = self.state.currentPage;

            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcvs_get_notifications',
                    nonce: self.config.nonce,
                    filters: filters
                },
                success: function(response) {
                    if (response.success) {
                        self.renderNotifications(response.data.notifications);
                        self.updatePagination(response.data.pagination);
                        self.state.totalPages = response.data.pagination.total_pages;
                        self.updateUnreadCount(response.data.stats.unread_notifications);
                    } else {
                        self.showError(response.data.message || 'Error al cargar notificaciones');
                    }
                },
                error: function(xhr, status, error) {
                    self.showError('Error de conexión: ' + error);
                },
                complete: function() {
                    self.state.isLoading = false;
                    self.hideLoading();
                }
            });
        },

        // Obtener filtros actuales
        getFilters: function() {
            var filters = {};
            
            $('.wcvs-notifications-filters select, .wcvs-notifications-filters input').each(function() {
                var $this = $(this);
                var name = $this.attr('name');
                var value = $this.val();
                
                if (name && value) {
                    filters[name] = value;
                }
            });

            return filters;
        },

        // Renderizar notificaciones
        renderNotifications: function(notifications) {
            var $container = $('.wcvs-notifications-list');
            
            if (notifications.length === 0) {
                $container.html(this.getEmptyStateHtml());
                return;
            }

            var html = '';
            notifications.forEach(function(notification) {
                html += this.getNotificationHtml(notification);
            }.bind(this));

            $container.html(html);
        },

        // Obtener HTML de notificación
        getNotificationHtml: function(notification) {
            var unreadClass = notification.is_read ? '' : ' unread';
            var timeAgo = this.getTimeAgo(notification.created_at);
            var typeClass = 'wcvs-notification-type ' + notification.notification_type;
            var iconClass = 'wcvs-icon wcvs-icon-' + notification.notification_type;

            return `
                <div class="wcvs-notification-item${unreadClass}" data-notification-id="${notification.id}">
                    <div class="wcvs-notification-header">
                        <h4 class="wcvs-notification-title">${notification.title}</h4>
                        <span class="wcvs-notification-time">${timeAgo}</span>
                    </div>
                    <p class="wcvs-notification-message">${notification.message}</p>
                    <div class="wcvs-notification-meta">
                        <span class="${typeClass}">
                            <span class="${iconClass}"></span>
                            ${notification.notification_type}
                        </span>
                        <div class="wcvs-notification-actions">
                            ${!notification.is_read ? '<button data-action="mark-read" title="Marcar como leída">✓</button>' : ''}
                            <button data-action="delete" title="Eliminar">×</button>
                        </div>
                    </div>
                </div>
            `;
        },

        // Obtener HTML de estado vacío
        getEmptyStateHtml: function() {
            return `
                <div class="wcvs-empty-state">
                    <h3>No hay notificaciones</h3>
                    <p>No se encontraron notificaciones con los filtros seleccionados.</p>
                    <button class="wcvs-btn wcvs-btn-primary wcvs-btn-refresh">
                        <span class="wcvs-icon wcvs-icon-refresh"></span>
                        Actualizar
                    </button>
                </div>
            `;
        },

        // Obtener tiempo transcurrido
        getTimeAgo: function(dateString) {
            var date = new Date(dateString);
            var now = new Date();
            var diff = now - date;
            var minutes = Math.floor(diff / 60000);
            var hours = Math.floor(diff / 3600000);
            var days = Math.floor(diff / 86400000);

            if (minutes < 1) {
                return 'Hace un momento';
            } else if (minutes < 60) {
                return `Hace ${minutes} minuto${minutes > 1 ? 's' : ''}`;
            } else if (hours < 24) {
                return `Hace ${hours} hora${hours > 1 ? 's' : ''}`;
            } else if (days < 7) {
                return `Hace ${days} día${days > 1 ? 's' : ''}`;
            } else {
                return date.toLocaleDateString();
            }
        },

        // Actualizar paginación
        updatePagination: function(pagination) {
            var $pagination = $('.wcvs-pagination');
            
            if (pagination.total_pages <= 1) {
                $pagination.hide();
                return;
            }

            $pagination.show();
            var html = '';

            // Botón anterior
            if (pagination.current_page > 1) {
                html += `<button data-page="${pagination.current_page - 1}">« Anterior</button>`;
            }

            // Páginas
            var startPage = Math.max(1, pagination.current_page - 2);
            var endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

            for (var i = startPage; i <= endPage; i++) {
                var currentClass = i === pagination.current_page ? ' current-page' : '';
                html += `<button data-page="${i}" class="${currentClass}">${i}</button>`;
            }

            // Botón siguiente
            if (pagination.current_page < pagination.total_pages) {
                html += `<button data-page="${pagination.current_page + 1}">Siguiente »</button>`;
            }

            $pagination.html(html);
        },

        // Marcar como leída
        markAsRead: function(notificationId) {
            var self = this;
            
            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcvs_mark_notification_read',
                    nonce: self.config.nonce,
                    notification_id: notificationId
                },
                success: function(response) {
                    if (response.success) {
                        $('.wcvs-notification-item[data-notification-id="' + notificationId + '"]')
                            .removeClass('unread')
                            .find('.wcvs-notification-actions button[data-action="mark-read"]')
                            .remove();
                        
                        self.updateUnreadCount();
                    } else {
                        self.showError(response.data.message || 'Error al marcar como leída');
                    }
                },
                error: function(xhr, status, error) {
                    self.showError('Error de conexión: ' + error);
                }
            });
        },

        // Marcar todas como leídas
        markAllAsRead: function() {
            var self = this;
            
            if (!confirm('¿Estás seguro de que quieres marcar todas las notificaciones como leídas?')) {
                return;
            }

            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcvs_mark_all_notifications_read',
                    nonce: self.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.wcvs-notification-item').removeClass('unread');
                        $('.wcvs-notification-actions button[data-action="mark-read"]').remove();
                        self.updateUnreadCount(0);
                        self.showSuccess('Todas las notificaciones han sido marcadas como leídas');
                    } else {
                        self.showError(response.data.message || 'Error al marcar como leídas');
                    }
                },
                error: function(xhr, status, error) {
                    self.showError('Error de conexión: ' + error);
                }
            });
        },

        // Eliminar notificación
        deleteNotification: function(notificationId) {
            var self = this;
            
            if (!confirm('¿Estás seguro de que quieres eliminar esta notificación?')) {
                return;
            }

            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcvs_delete_notification',
                    nonce: self.config.nonce,
                    notification_id: notificationId
                },
                success: function(response) {
                    if (response.success) {
                        $('.wcvs-notification-item[data-notification-id="' + notificationId + '"]')
                            .fadeOut(300, function() {
                                $(this).remove();
                                if ($('.wcvs-notification-item').length === 0) {
                                    $('.wcvs-notifications-list').html(self.getEmptyStateHtml());
                                }
                            });
                        
                        self.updateUnreadCount();
                    } else {
                        self.showError(response.data.message || 'Error al eliminar notificación');
                    }
                },
                error: function(xhr, status, error) {
                    self.showError('Error de conexión: ' + error);
                }
            });
        },

        // Limpiar todas las notificaciones
        clearAllNotifications: function() {
            var self = this;
            
            if (!confirm('¿Estás seguro de que quieres eliminar todas las notificaciones? Esta acción no se puede deshacer.')) {
                return;
            }

            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcvs_clear_all_notifications',
                    nonce: self.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.wcvs-notifications-list').html(self.getEmptyStateHtml());
                        self.updateUnreadCount(0);
                        self.showSuccess('Todas las notificaciones han sido eliminadas');
                    } else {
                        self.showError(response.data.message || 'Error al eliminar notificaciones');
                    }
                },
                error: function(xhr, status, error) {
                    self.showError('Error de conexión: ' + error);
                }
            });
        },

        // Mostrar detalles de notificación
        showNotificationDetails: function(notificationId) {
            var self = this;
            
            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wcvs_get_notification_details',
                    nonce: self.config.nonce,
                    notification_id: notificationId
                },
                success: function(response) {
                    if (response.success) {
                        self.showModal(response.data.notification);
                    } else {
                        self.showError(response.data.message || 'Error al cargar detalles');
                    }
                },
                error: function(xhr, status, error) {
                    self.showError('Error de conexión: ' + error);
                }
            });
        },

        // Mostrar modal
        showModal: function(notification) {
            var modalHtml = `
                <div class="wcvs-notification-modal">
                    <div class="wcvs-modal-content">
                        <div class="wcvs-modal-header">
                            <h3 class="wcvs-modal-title">${notification.title}</h3>
                            <button class="wcvs-modal-close">&times;</button>
                        </div>
                        <div class="wcvs-modal-body">
                            <p>${notification.message}</p>
                            <div class="wcvs-modal-meta">
                                <h4>Información adicional</h4>
                                <p><strong>Tipo:</strong> ${notification.notification_type}</p>
                                <p><strong>Evento:</strong> ${notification.event}</p>
                                <p><strong>Fecha:</strong> ${new Date(notification.created_at).toLocaleString()}</p>
                                <p><strong>Estado:</strong> ${notification.is_read ? 'Leída' : 'No leída'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHtml);
        },

        // Cerrar modal
        closeModal: function() {
            $('.wcvs-notification-modal').remove();
        },

        // Actualizar contador de no leídas
        updateUnreadCount: function(count) {
            if (count !== undefined) {
                this.state.unreadCount = count;
            }

            $('.wcvs-unread-count').text(this.state.unreadCount);
            
            if (this.state.unreadCount > 0) {
                $('.wcvs-unread-count').show();
            } else {
                $('.wcvs-unread-count').hide();
            }
        },

        // Iniciar auto-refresh
        startAutoRefresh: function() {
            var self = this;
            
            if (self.state.refreshTimer) {
                clearInterval(self.state.refreshTimer);
            }

            self.state.refreshTimer = setInterval(function() {
                self.loadNotifications();
            }, self.config.refreshInterval);
        },

        // Detener auto-refresh
        stopAutoRefresh: function() {
            if (this.state.refreshTimer) {
                clearInterval(this.state.refreshTimer);
                this.state.refreshTimer = null;
            }
        },

        // Mostrar loading
        showLoading: function() {
            $('.wcvs-notifications-list').html('<div class="wcvs-loading">Cargando notificaciones...</div>');
        },

        // Ocultar loading
        hideLoading: function() {
            $('.wcvs-loading').remove();
        },

        // Mostrar error
        showError: function(message) {
            this.showMessage(message, 'error');
        },

        // Mostrar éxito
        showSuccess: function(message) {
            this.showMessage(message, 'success');
        },

        // Mostrar mensaje
        showMessage: function(message, type) {
            var messageClass = 'wcvs-message wcvs-message-' + type;
            var messageHtml = `<div class="${messageClass}">${message}</div>`;
            
            $('.wcvs-notifications-admin').prepend(messageHtml);
            
            setTimeout(function() {
                $('.wcvs-message').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        },

        // Destruir el módulo
        destroy: function() {
            this.stopAutoRefresh();
            $(document).off('.wcvs-notifications');
        }
    };

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        if (typeof wcvs_notifications_ajax !== 'undefined') {
            WCVS_Notifications.init();
        }
    });

    // Limpiar cuando se abandona la página
    $(window).on('beforeunload', function() {
        WCVS_Notifications.destroy();
    });

})(jQuery);
