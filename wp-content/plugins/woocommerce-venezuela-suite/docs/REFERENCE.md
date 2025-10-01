# WooCommerce Venezuela Suite - Guía de Referencia Rápida

## 📋 Archivo Principal de Referencia

Este archivo sirve como índice central para encontrar toda la documentación y recursos del plugin WooCommerce Venezuela Suite.

## 📚 Documentación Principal

### Archivos de Documentación
- **[README.md](README.md)** - Documentación principal del plugin
- **[DEVELOPMENT-PLAN.md](DEVELOPMENT-PLAN.md)** - Plan completo de desarrollo
- **[MODULES.md](MODULES.md)** - Documentación detallada de cada módulo
- **[INSTALLATION.md](INSTALLATION.md)** - Guía de instalación paso a paso
- **[CONFIGURATION.md](CONFIGURATION.md)** - Guía completa de configuración
- **[DEVELOPMENT.md](DEVELOPMENT.md)** - Guía para desarrolladores
- **[API.md](API.md)** - Documentación completa de la API
- **[CHANGELOG.md](CHANGELOG.md)** - Historial de cambios

## 🏗️ Estructura del Plugin

### Directorios Principales
```
woocommerce-venezuela-suite/
├── 📄 woocommerce-venezuela-suite.php    # Archivo principal
├── 📁 core/                              # Módulo core (obligatorio)
├── 📁 modules/                           # Módulos opcionales
│   ├── 📁 currency/                      # Gestión de monedas
│   ├── 📁 payments/                      # Métodos de pago
│   ├── 📁 shipping/                      # Métodos de envío
│   ├── 📁 invoicing/                    # Facturación
│   ├── 📁 communication/                # Comunicación
│   ├── 📁 reports/                      # Reportes
│   └── 📁 widgets/                      # Widgets
├── 📁 assets/                           # Recursos estáticos
├── 📁 languages/                        # Traducciones
├── 📁 admin/                            # Panel administrativo
├── 📁 public/                           # Frontend
├── 📁 includes/                         # Clases compartidas
└── 📁 tests/                            # Tests unitarios
```

## 🔧 Módulos Disponibles

### Core Module (Obligatorio)
- **Archivo**: `core/class-wvs-core.php`
- **Documentación**: [MODULES.md#core-module](MODULES.md#core-module)
- **Funcionalidades**: Gestión principal, seguridad, base de datos, performance
- **Configuración**: [CONFIGURATION.md#core-settings](CONFIGURATION.md#core-settings)

### Currency Module
- **Archivo**: `modules/currency/class-wvs-currency-manager.php`
- **Documentación**: [MODULES.md#currency-module](MODULES.md#currency-module)
- **Funcionalidades**: Conversión USD/VES, IGTF, BCV integration
- **Configuración**: [CONFIGURATION.md#currency-settings](CONFIGURATION.md#currency-settings)
- **API**: [API.md#currency-api](API.md#currency-api)

### Payments Module
- **Archivo**: `modules/payments/class-wvs-payment-manager.php`
- **Documentación**: [MODULES.md#payments-module](MODULES.md#payments-module)
- **Funcionalidades**: Zelle, Pago Móvil, Efectivo, Cashea, Crypto
- **Configuración**: [CONFIGURATION.md#payment-settings](CONFIGURATION.md#payment-settings)
- **API**: [API.md#payments-api](API.md#payments-api)

### Shipping Module
- **Archivo**: `modules/shipping/class-wvs-shipping-manager.php`
- **Documentación**: [MODULES.md#shipping-module](MODULES.md#shipping-module)
- **Funcionalidades**: Envío local, nacional, express
- **Configuración**: [CONFIGURATION.md#shipping-settings](CONFIGURATION.md#shipping-settings)
- **API**: [API.md#shipping-api](API.md#shipping-api)

### Invoicing Module
- **Archivo**: `modules/invoicing/class-wvs-invoice-manager.php`
- **Documentación**: [MODULES.md#invoicing-module](MODULES.md#invoicing-module)
- **Funcionalidades**: Facturación híbrida, electrónica, SENIAT
- **Configuración**: [CONFIGURATION.md#invoicing-settings](CONFIGURATION.md#invoicing-settings)
- **API**: [API.md#invoicing-api](API.md#invoicing-api)

### Communication Module
- **Archivo**: `modules/communication/class-wvs-notification-manager.php`
- **Documentación**: [MODULES.md#communication-module](MODULES.md#communication-module)
- **Funcionalidades**: WhatsApp, Email, SMS, Chat
- **Configuración**: [CONFIGURATION.md#communication-settings](CONFIGURATION.md#communication-settings)
- **API**: [API.md#communication-api](API.md#communication-api)

### Reports Module
- **Archivo**: `modules/reports/class-wvs-reports-manager.php`
- **Documentación**: [MODULES.md#reports-module](MODULES.md#reports-module)
- **Funcionalidades**: Reportes de ventas, fiscales, analytics
- **Configuración**: [CONFIGURATION.md#reports-settings](CONFIGURATION.md#reports-settings)
- **API**: [API.md#reports-api](API.md#reports-api)

### Widgets Module
- **Archivo**: `modules/widgets/class-wvs-widget-manager.php`
- **Documentación**: [MODULES.md#widgets-module](MODULES.md#widgets-module)
- **Funcionalidades**: Widgets de conversión, productos, estado
- **Configuración**: [CONFIGURATION.md#widgets-settings](CONFIGURATION.md#widgets-settings)
- **API**: [API.md#widgets-api](API.md#widgets-api)

## 🚀 Guías de Inicio Rápido

### Instalación
1. **Requisitos**: [INSTALLATION.md#requisitos-del-sistema](INSTALLATION.md#requisitos-del-sistema)
2. **Instalación**: [INSTALLATION.md#instalación-paso-a-paso](INSTALLATION.md#instalación-paso-a-paso)
3. **Configuración inicial**: [INSTALLATION.md#configuración-post-instalación](INSTALLATION.md#configuración-post-instalación)

### Configuración Básica
1. **Core Settings**: [CONFIGURATION.md#core-settings](CONFIGURATION.md#core-settings)
2. **Currency Settings**: [CONFIGURATION.md#currency-settings](CONFIGURATION.md#currency-settings)
3. **Payment Settings**: [CONFIGURATION.md#payment-settings](CONFIGURATION.md#payment-settings)

### Desarrollo
1. **Arquitectura**: [DEVELOPMENT.md#arquitectura-del-plugin](DEVELOPMENT.md#arquitectura-del-plugin)
2. **Convenciones**: [DEVELOPMENT.md#convenciones-de-código](DEVELOPMENT.md#convenciones-de-código)
3. **Testing**: [DEVELOPMENT.md#testing](DEVELOPMENT.md#testing)

## 🔌 API y Hooks

### Funciones Principales
- **Gestión de opciones**: `wvs_get_option()`, `wvs_set_option()`
- **Gestión de módulos**: `wvs_is_module_active()`, `wvs_set_module_active()`
- **Conversión de moneda**: `wvs_convert_price()`, `wvs_convert_usd_to_ves()`
- **Procesamiento de pagos**: `wvs_process_payment()`, `wvs_verify_payment()`

### Hooks Principales
- **Inicialización**: `wvs_plugin_initialized`, `wvs_module_activated`
- **Moneda**: `wvs_convert_price`, `wvs_bcv_rate_updated`
- **Pagos**: `wvs_process_payment`, `wvs_payment_verified`
- **Envíos**: `wvs_calculate_shipping`, `wvs_shipping_tracking_updated`

### Documentación Completa
- **API Reference**: [API.md](API.md)
- **Hooks y Filtros**: [API.md#hooks-y-filtros](API.md#hooks-y-filtros)
- **Ejemplos de Uso**: [API.md#ejemplos-de-uso](API.md#ejemplos-de-uso)

## 🛠️ Herramientas de Desarrollo

### Comandos WP-CLI
```bash
# Activar módulo
wp wvs activate_module currency

# Desactivar módulo
wp wvs deactivate_module payments

# Listar módulos
wp wvs list_modules

# Verificar configuración
wp wvs verify_config
```

### Testing
```bash
# Ejecutar todos los tests
phpunit tests/

# Tests específicos
phpunit tests/test-wvs-currency.php

# Con cobertura
phpunit --coverage-html coverage/ tests/
```

### Debugging
```php
// Habilitar debug
define('WVS_DEBUG', true);
define('WVS_LOG_LEVEL', 'debug');

// Usar logger
WVS_Logger::debug('Mensaje de debug', $context);
WVS_Logger::error('Error encontrado', $context);
```

## 📊 Métricas y Performance

### Métricas de Rendimiento
- **Tiempo de carga**: <2 segundos
- **Consultas de BD**: <10 por página
- **Uso de memoria**: <50MB por módulo
- **Cobertura de tests**: >80%

### Optimizaciones Implementadas
- Cache inteligente con invalidación automática
- Optimización de consultas de base de datos
- Minificación de assets
- Lazy loading de módulos

## 🔒 Seguridad

### Características de Seguridad
- Validación estricta de inputs
- Sanitización completa de outputs
- Rate limiting por IP
- Logs de seguridad detallados
- Protección contra SQL injection y XSS

### Configuración de Seguridad
```php
// Configuración básica
wvs_set_option('security_enabled', true);
wvs_set_option('rate_limiting', true);
wvs_set_option('log_security_events', true);
```

## 🌐 Internacionalización

### Configuración
- **Text domain**: `woocommerce-venezuela-suite`
- **Idioma por defecto**: Español (Venezuela)
- **Archivos de traducción**: `languages/`

### Funciones de Traducción
```php
// Strings simples
__('Texto a traducir', 'woocommerce-venezuela-suite')

// Strings con contexto
_x('Texto', 'contexto', 'woocommerce-venezuela-suite')

// Strings plurales
_n('Singular', 'Plural', $count, 'woocommerce-venezuela-suite')
```

## 📞 Soporte y Comunidad

### Canales de Soporte
- **Email**: soporte@kinta-electric.com
- **WhatsApp**: +58-412-123-4567
- **Website**: [kinta-electric.com](https://kinta-electric.com)
- **Discord**: [Servidor de la comunidad](https://discord.gg/kinta-electric)

### Recursos de Desarrollo
- **Documentación**: [docs.kinta-electric.com](https://docs.kinta-electric.com)
- **GitHub**: [Repositorio del plugin](https://github.com/kinta-electric/woocommerce-venezuela-suite)
- **Issues**: [GitHub Issues](https://github.com/kinta-electric/woocommerce-venezuela-suite/issues)

## 📋 Checklist de Implementación

### Instalación
- [ ] Verificar requisitos del sistema
- [ ] Instalar plugin
- [ ] Activar plugin
- [ ] Configurar Core Module
- [ ] Verificar funcionamiento básico

### Configuración por Módulo
- [ ] **Currency**: Configurar BCV integration
- [ ] **Payments**: Configurar métodos de pago
- [ ] **Shipping**: Configurar métodos de envío
- [ ] **Invoicing**: Configurar facturación
- [ ] **Communication**: Configurar notificaciones
- [ ] **Reports**: Configurar reportes
- [ ] **Widgets**: Configurar widgets

### Testing
- [ ] Tests unitarios
- [ ] Tests de integración
- [ ] Tests de performance
- [ ] Tests de seguridad
- [ ] Tests de usuario

### Producción
- [ ] Backup completo
- [ ] Configuración de seguridad
- [ ] Monitoreo de logs
- [ ] Configuración de cache
- [ ] Documentación del proyecto

## 🔄 Migración desde Plugin Anterior

### Proceso de Migración
1. **Backup**: Crear backup completo del sitio
2. **Instalación**: Instalar nuevo plugin
3. **Migración**: Ejecutar proceso de migración automático
4. **Verificación**: Verificar funcionamiento
5. **Limpieza**: Desinstalar plugin anterior

### Datos Migrados
- Configuraciones de módulos
- Datos de base de datos
- Configuraciones de pagos
- Configuraciones de envíos
- Configuraciones de facturación

## 📈 Roadmap Futuro

### Versión 1.1.0 (Q2 2025)
- Integración con más bancos venezolanos
- Gateway de pagos móviles adicionales
- Sistema de cupones y descuentos
- Analytics avanzado

### Versión 1.2.0 (Q3 2025)
- Integración con sistemas de contabilidad
- API REST completa
- App móvil nativa
- Integración con marketplaces

### Versión 2.0.0 (Q4 2025)
- Arquitectura microservicios
- Integración con blockchain
- IA para predicción de precios
- Sistema de recomendaciones

---

**Última actualización**: 2025-01-27  
**Versión**: 1.0.0  
**Mantenido por**: Kinta Electric Team
