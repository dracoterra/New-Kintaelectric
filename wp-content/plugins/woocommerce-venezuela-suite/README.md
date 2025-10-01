# WooCommerce Venezuela Suite

## 📋 Descripción General

**WooCommerce Venezuela Suite** es un plugin modular completo para WooCommerce diseñado específicamente para el mercado venezolano. Proporciona todas las funcionalidades necesarias para operar un e-commerce exitoso en Venezuela, incluyendo gestión de monedas, métodos de pago locales, envíos nacionales, facturación fiscal y mucho más.

## 🎯 Características Principales

- **🏗️ Arquitectura Modular**: Activa/desactiva solo las funcionalidades que necesitas
- **💰 Gestión de Monedas**: Conversión automática USD/VES con integración BCV
- **💳 Métodos de Pago Locales**: Zelle, Pago Móvil, Efectivo, Criptomonedas
- **🚚 Envíos Nacionales**: Cobertura completa de Venezuela
- **📄 Facturación Fiscal**: Cumplimiento con regulaciones venezolanas
- **📊 Reportes Avanzados**: Analytics y reportes fiscales
- **🔒 Seguridad Empresarial**: Validación, logs y protección avanzada
- **⚡ Alto Rendimiento**: Cache inteligente y optimización automática

## 📦 Módulos Disponibles

### 🔧 Core Module (Obligatorio)
- Gestión principal del plugin
- Sistema de seguridad base
- Gestión de base de datos
- Optimización de rendimiento

### 💰 Currency Module
- Integración con BCV Dólar Tracker
- Conversión USD/VES automática
- Gestión de IGTF
- Múltiples formatos de visualización

### 💳 Payments Module
- Zelle
- Pago Móvil
- Efectivo (USD/VES)
- Cashea
- Criptomonedas (Bitcoin, USDT)

### 🚚 Shipping Module
- Envío local (Caracas)
- Envío nacional (todos los estados)
- Envío express
- Calculadora de costos automática

### 📄 Invoicing Module
- Facturación híbrida USD/VES
- Facturación electrónica SENIAT
- Reportes fiscales automáticos
- Gestión de IVA

### 📱 Communication Module
- Notificaciones WhatsApp
- Templates de email personalizados
- SMS integration
- Chat en vivo

### 📊 Reports Module
- Reportes de ventas
- Reportes fiscales SENIAT
- Analytics avanzado
- Dashboard ejecutivo

### 🎨 Widgets Module
- Widget de conversión de moneda
- Widget de productos
- Widget de estado de pedidos
- Widget de comparación de precios

## 🚀 Instalación

1. **Requisitos Previos**:
   - WordPress 5.0+
   - WooCommerce 5.0+
   - PHP 7.4+
   - BCV Dólar Tracker (recomendado)

2. **Instalación**:
   ```bash
   # Subir el plugin a wp-content/plugins/
   # Activar desde el panel de administración
   ```

3. **Configuración Inicial**:
   - Ir a WooCommerce → Venezuela Suite
   - Configurar módulos básicos
   - Configurar integración BCV
   - Activar módulos necesarios

## ⚙️ Configuración

### Configuración Básica
1. **Moneda Base**: Configurar USD como moneda base
2. **BCV Integration**: Conectar con BCV Dólar Tracker
3. **IGTF**: Configurar tasa de IGTF (por defecto 3%)
4. **Módulos**: Activar módulos necesarios

### Configuración Avanzada
- Ver [CONFIGURATION.md](docs/CONFIGURATION.md) para detalles completos

## 📚 Documentación

- **[INSTALLATION.md](docs/INSTALLATION.md)** - Guía de instalación detallada
- **[CONFIGURATION.md](docs/CONFIGURATION.md)** - Configuración completa
- **[MODULES.md](docs/MODULES.md)** - Documentación de módulos
- **[API.md](docs/API.md)** - Documentación de API
- **[DEVELOPMENT.md](docs/DEVELOPMENT.md)** - Guía para desarrolladores
- **[CHANGELOG.md](CHANGELOG.md)** - Historial de cambios

## 🔧 Desarrollo

### Estructura del Plugin
```
woocommerce-venezuela-suite/
├── core/                    # Módulo core (obligatorio)
├── modules/                 # Módulos opcionales
│   ├── currency/
│   ├── payments/
│   ├── shipping/
│   ├── invoicing/
│   ├── communication/
│   ├── reports/
│   └── widgets/
├── assets/                  # CSS, JS, imágenes
├── languages/               # Archivos de traducción
├── docs/                    # Documentación
└── tests/                   # Tests unitarios
```

### Convenciones de Código
- **Prefijo de clases**: `WVS_`
- **Prefijo de funciones**: `wvs_`
- **Prefijo de constantes**: `WVS_`
- **Text domain**: `woocommerce-venezuela-suite`

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia GPL v2 o posterior - ver el archivo [LICENSE](LICENSE) para detalles.

## 🆘 Soporte

- **Documentación**: [docs/](docs/)
- **Issues**: [GitHub Issues](https://github.com/kinta-electric/woocommerce-venezuela-suite/issues)
- **Email**: soporte@kinta-electric.com
- **Website**: [kinta-electric.com](https://kinta-electric.com)

## 🏆 Créditos

Desarrollado por **Kinta Electric** para el mercado venezolano.

---

**Versión**: 1.0.0  
**Última actualización**: 2025-01-27  
**Compatibilidad**: WordPress 5.0+, WooCommerce 5.0+
