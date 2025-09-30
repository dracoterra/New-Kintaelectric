# 💳 **PLAN MÓDULO: Payment Gateways - MEJORADO**

## **🎯 OBJETIVO DEL MÓDULO**

Implementar métodos de pago específicos y avanzados para el mercado venezolano, incluyendo **Pago Móvil con API real**, **transferencias bancarias con verificación automática**, **Zelle con integración completa**, **efectivo con geolocalización**, y **criptomonedas con conversión automática**, proporcionando una experiencia de pago moderna, segura y familiar para los clientes venezolanos con **verificación automática**, **notificaciones inteligentes** y **análisis de riesgo**.

---

## **📋 FUNCIONALIDADES PRINCIPALES**

## **📋 FUNCIONALIDADES PRINCIPALES MEJORADAS**

### **📱 1. Pago Móvil Avanzado**
- **API Integration** con proveedores oficiales de Pago Móvil
- **Validación en tiempo real** de números telefónicos venezolanos
- **Generación automática** de códigos de pago únicos
- **Verificación automática** de pagos recibidos cada 30 segundos
- **Notificaciones inteligentes** por SMS/WhatsApp/Email
- **Sistema de reintentos** automáticos para verificación
- **Análisis de riesgo** para detectar pagos fraudulentos
- **Historial completo** de transacciones Pago Móvil
- **Soporte multi-banco** (Banesco, Mercantil, BBVA, etc.)
- **QR Code** para pagos rápidos

### **🏦 2. Transferencias Bancarias Inteligentes**
- **Integración con APIs bancarias** (cuando estén disponibles)
- **Verificación automática** de transferencias recibidas
- **Sistema de códigos de referencia** únicos por pedido
- **Validación de números de cuenta** en tiempo real
- **Comprobantes automáticos** con OCR para lectura
- **Soporte completo** para todos los bancos principales:
  - **Banesco** - Cuenta corriente y ahorro
  - **Mercantil** - Cuenta corriente y ahorro
  - **BBVA** - Cuenta corriente y ahorro
  - **Bancamiga** - Cuenta corriente y ahorro
  - **100% Banco** - Cuenta corriente y ahorro
  - **BFC** - Cuenta corriente y ahorro
  - **Venezuela** - Cuenta corriente y ahorro
- **Notificaciones push** cuando se recibe transferencia
- **Sistema de alertas** para transferencias pendientes
- **Reconciliación automática** con extractos bancarios

### **💵 3. Zelle Integration Completa**
- **Validación de emails** de Zelle en tiempo real
- **Verificación automática** de pagos Zelle recibidos
- **Sistema de códigos de referencia** únicos
- **Notificaciones instantáneas** por email/SMS
- **Integración con APIs** de bancos estadounidenses
- **Soporte para múltiples** cuentas Zelle
- **Análisis de riesgo** para transacciones Zelle
- **Historial detallado** de pagos Zelle
- **Sistema de alertas** para pagos pendientes
- **Reconciliación automática** con cuentas USD

### **💸 4. Efectivo con Geolocalización**
- **Geolocalización** para determinar zonas de entrega
- **Cálculo automático** de zonas de cobertura
- **Pago contra entrega** con confirmación GPS
- **Pago en tienda física** con código QR
- **Sistema de citas** para recogida en tienda
- **Validación de zonas** de entrega en tiempo real
- **Confirmación de pago** con foto del cliente
- **Sistema de alertas** para entregas pendientes
- **Tracking en tiempo real** de entregas
- **Integración con servicios** de delivery locales

### **₿ 5. Criptomonedas Avanzadas**
- **Soporte completo** para Bitcoin (BTC), Ethereum (ETH), Tether (USDT)
- **Conversión automática** a USD usando APIs de precios
- **Generación automática** de direcciones de wallet
- **Verificación automática** de pagos recibidos
- **Sistema de confirmaciones** (6 confirmaciones para BTC)
- **Notificaciones instantáneas** de pagos recibidos
- **Análisis de riesgo** para transacciones crypto
- **Historial completo** de transacciones crypto
- **Soporte para Lightning Network** (Bitcoin)
- **Integración con exchanges** para conversión automática

### **🔒 6. Seguridad y Análisis de Riesgo**
- **Análisis de riesgo** en tiempo real para cada transacción
- **Detección de fraude** usando machine learning
- **Sistema de blacklist** para usuarios fraudulentos
- **Verificación de identidad** con múltiples factores
- **Encriptación end-to-end** para datos sensibles
- **Auditoría completa** de todas las transacciones
- **Sistema de alertas** para transacciones sospechosas
- **Cumplimiento PCI-DSS** para datos de pago
- **Backup automático** de transacciones críticas
- **Monitoreo 24/7** del sistema de pagos

---

## **🏗️ ESTRUCTURA DEL MÓDULO**

## **🏗️ ESTRUCTURA DEL MÓDULO MEJORADA**

### **📁 Archivos Principales**
```
modules/payment-gateways/
├── payment-gateways.php                           # 🚀 Bootstrap del módulo
├── includes/
│   ├── class-gateways-core.php                    # ⚙️ Funcionalidad principal
│   ├── class-payment-risk-analyzer.php            # 🔒 Analizador de riesgo
│   ├── class-payment-fraud-detector.php            # 🛡️ Detector de fraude
│   ├── class-payment-notification-manager.php      # 📢 Gestor de notificaciones
│   ├── class-payment-verification-engine.php       # ✅ Motor de verificación
│   ├── class-payment-reconciliation.php           # 🔄 Reconciliación automática
│   ├── class-payment-audit-logger.php             # 📋 Logger de auditoría
│   └── class-gateways-admin.php                   # 👨‍💼 Panel administrativo
├── gateways/
│   ├── class-gateway-pago-movil.php               # 📱 Gateway Pago Móvil
│   ├── class-gateway-transferencia.php             # 🏦 Gateway Transferencias
│   ├── class-gateway-zelle.php                    # 💵 Gateway Zelle
│   ├── class-gateway-efectivo.php                 # 💸 Gateway Efectivo
│   ├── class-gateway-crypto.php                   # ₿ Gateway Criptomonedas
│   └── base/
│       ├── class-base-gateway.php                  # 🏗️ Clase base gateway
│       ├── class-gateway-interface.php             # 🔌 Interfaz gateway
│       └── class-gateway-validator.php             # ✅ Validador base
├── integrations/
│   ├── pago-movil/
│   │   ├── class-pago-movil-api.php               # 📱 API Pago Móvil
│   │   ├── class-pago-movil-verifier.php          # ✅ Verificador Pago Móvil
│   │   ├── class-pago-movil-notifier.php          # 📢 Notificador Pago Móvil
│   │   └── class-pago-movil-qr-generator.php      # 📱 Generador QR
│   ├── banks/
│   │   ├── class-banesco-api.php                  # 🏦 API Banesco
│   │   ├── class-mercantil-api.php                # 🏦 API Mercantil
│   │   ├── class-bbva-api.php                     # 🏦 API BBVA
│   │   ├── class-bancamiga-api.php                # 🏦 API Bancamiga
│   │   ├── class-transfer-verifier.php            # ✅ Verificador transferencias
│   │   └── class-bank-reconciliation.php          # 🔄 Reconciliación bancaria
│   ├── zelle/
│   │   ├── class-zelle-api.php                    # 💵 API Zelle
│   │   ├── class-zelle-verifier.php               # ✅ Verificador Zelle
│   │   ├── class-zelle-notifier.php               # 📢 Notificador Zelle
│   │   └── class-zelle-reconciliation.php         # 🔄 Reconciliación Zelle
│   ├── crypto/
│   │   ├── class-bitcoin-api.php                  # ₿ API Bitcoin
│   │   ├── class-ethereum-api.php                 # ₿ API Ethereum
│   │   ├── class-usdt-api.php                     # ₿ API USDT
│   │   ├── class-crypto-price-tracker.php         # 📈 Rastreador precios crypto
│   │   ├── class-crypto-wallet-generator.php      # 💳 Generador wallets
│   │   └── class-crypto-verifier.php              # ✅ Verificador crypto
│   └── geolocation/
│       ├── class-delivery-zones.php               # 📍 Zonas de entrega
│       ├── class-geolocation-service.php          # 📍 Servicio geolocalización
│       └── class-delivery-tracker.php             # 🚚 Rastreador entregas
├── admin/
│   ├── css/payment-gateways-admin.css            # 🎨 Estilos admin
│   ├── js/payment-gateways-admin.js              # 📱 JavaScript admin
│   └── partials/
│       ├── gateways-settings.php                 # ⚙️ Configuración general
│       ├── pago-movil-settings.php               # 📱 Configuración Pago Móvil
│       ├── transferencia-settings.php             # 🏦 Configuración Transferencias
│       ├── zelle-settings.php                     # 💵 Configuración Zelle
│       ├── efectivo-settings.php                  # 💸 Configuración Efectivo
│       ├── crypto-settings.php                    # ₿ Configuración Crypto
│       ├── risk-analysis-settings.php             # 🔒 Configuración análisis riesgo
│       ├── fraud-detection-settings.php           # 🛡️ Configuración detección fraude
│       ├── notification-settings.php              # 📢 Configuración notificaciones
│       ├── reconciliation-settings.php            # 🔄 Configuración reconciliación
│       ├── payment-logs.php                       # 📋 Logs de pagos
│       ├── transaction-monitor.php               # 📊 Monitor transacciones
│       ├── fraud-alerts.php                       # 🚨 Alertas de fraude
│       └── payment-dashboard.php                  # 📊 Dashboard pagos
├── public/
│   ├── css/payment-gateways-public.css           # 🎨 Estilos públicos
│   ├── js/payment-gateways-public.js             # 📱 JavaScript público
│   └── js/
│       ├── pago-movil-checkout.js                 # 📱 JS Pago Móvil checkout
│       ├── transfer-checkout.js                    # 🏦 JS Transferencia checkout
│       ├── zelle-checkout.js                      # 💵 JS Zelle checkout
│       ├── efectivo-checkout.js                    # 💸 JS Efectivo checkout
│       ├── crypto-checkout.js                     # ₿ JS Crypto checkout
│       ├── payment-verification.js                # ✅ JS Verificación pagos
│       └── geolocation.js                         # 📍 JS Geolocalización
├── templates/
│   ├── checkout/
│   │   ├── pago-movil-form.php                    # 📱 Formulario Pago Móvil
│   │   ├── transferencia-form.php                  # 🏦 Formulario Transferencia
│   │   ├── zelle-form.php                         # 💵 Formulario Zelle
│   │   ├── efectivo-form.php                      # 💸 Formulario Efectivo
│   │   ├── crypto-form.php                        # ₿ Formulario Crypto
│   │   └── payment-instructions.php               # 📋 Instrucciones de pago
│   ├── emails/
│   │   ├── payment-confirmation.php               # 📧 Confirmación pago
│   │   ├── payment-instructions.php               # 📧 Instrucciones pago
│   │   ├── payment-reminder.php                   # 📧 Recordatorio pago
│   │   └── payment-received.php                   # 📧 Pago recibido
│   └── admin/
│       ├── transaction-details.php               # 📊 Detalles transacción
│       ├── payment-status.php                     # 📊 Estado pago
│       └── fraud-alert.php                        # 🚨 Alerta fraude
├── assets/
│   ├── images/
│   │   ├── bank-logos/                            # 🏦 Logos de bancos
│   │   ├── payment-icons/                          # 💳 Iconos de pagos
│   │   ├── crypto-logos/                          # ₿ Logos criptomonedas
│   │   └── qr-codes/                              # 📱 Códigos QR
│   ├── js/
│   │   ├── crypto-price-tracker.js               # 📈 Rastreador precios crypto
│   │   ├── payment-verification.js                # ✅ Verificación pagos
│   │   └── fraud-detection.js                    # 🛡️ Detección fraude
│   └── css/
│       ├── payment-forms.css                      # 🎨 Estilos formularios
│       ├── crypto-widgets.css                     # 🎨 Estilos widgets crypto
│       └── payment-status.css                     # 🎨 Estilos estado pago
├── languages/
│   └── payment-gateways.pot                      # 🌍 Traducciones
├── tests/
│   ├── test-pago-movil.php                       # 🧪 Tests Pago Móvil
│   ├── test-transferencia.php                    # 🧪 Tests Transferencias
│   ├── test-zelle.php                            # 🧪 Tests Zelle
│   ├── test-crypto.php                           # 🧪 Tests Crypto
│   ├── test-fraud-detection.php                  # 🧪 Tests detección fraude
│   ├── test-risk-analysis.php                    # 🧪 Tests análisis riesgo
│   └── test-gateways-integration.php             # 🧪 Tests de integración
├── PLAN-MODULO.md                                 # 📋 Este archivo
├── README.md                                       # 📖 Documentación
└── uninstall.php                                   # 🗑️ Limpieza al eliminar
```

---

## **🔧 IMPLEMENTACIÓN TÉCNICA**

## **🔧 IMPLEMENTACIÓN TÉCNICA MEJORADA**

### **📊 Base de Datos Extendida**
```sql
-- Tabla principal de métodos de pago
CREATE TABLE wp_wvs_payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    method_name VARCHAR(100) NOT NULL,
    method_type VARCHAR(50) NOT NULL,
    method_class VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    settings TEXT,
    risk_score DECIMAL(3,2) DEFAULT 0.00,
    fraud_detection_enabled BOOLEAN DEFAULT TRUE,
    verification_required BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_method_type (method_type),
    INDEX idx_is_active (is_active)
);

-- Tabla de transacciones de pago
CREATE TABLE wp_wvs_payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    external_transaction_id VARCHAR(200),
    amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    exchange_rate DECIMAL(10,4),
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_data TEXT,
    verification_data TEXT,
    risk_score DECIMAL(3,2) DEFAULT 0.00,
    fraud_flags TEXT,
    verification_attempts INT DEFAULT 0,
    last_verification_attempt TIMESTAMP NULL,
    verification_status ENUM('pending', 'verified', 'failed', 'timeout') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_status (status),
    INDEX idx_payment_method (payment_method),
    INDEX idx_verification_status (verification_status),
    FOREIGN KEY (order_id) REFERENCES wp_posts(ID) ON DELETE CASCADE
);

-- Tabla de cuentas bancarias
CREATE TABLE wp_wvs_bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_name VARCHAR(100) NOT NULL,
    bank_code VARCHAR(10) NOT NULL,
    account_type ENUM('corriente', 'ahorro') NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    account_holder VARCHAR(200) NOT NULL,
    rif VARCHAR(20) NOT NULL,
    currency VARCHAR(3) DEFAULT 'VES',
    is_active BOOLEAN DEFAULT TRUE,
    api_enabled BOOLEAN DEFAULT FALSE,
    api_credentials TEXT,
    verification_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bank_name (bank_name),
    INDEX idx_is_active (is_active),
    INDEX idx_api_enabled (api_enabled)
);

-- Tabla de verificación de pagos
CREATE TABLE wp_wvs_payment_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    verification_type ENUM('pago_movil', 'transferencia', 'zelle', 'crypto', 'efectivo') NOT NULL,
    verification_method VARCHAR(50) NOT NULL,
    verification_data TEXT,
    verification_result TEXT,
    verification_status ENUM('pending', 'success', 'failed', 'timeout') DEFAULT 'pending',
    verification_score DECIMAL(3,2) DEFAULT 0.00,
    verification_timestamp TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_verification_type (verification_type),
    INDEX idx_verification_status (verification_status),
    FOREIGN KEY (transaction_id) REFERENCES wp_wvs_payment_transactions(id) ON DELETE CASCADE
);

-- Tabla de análisis de riesgo
CREATE TABLE wp_wvs_payment_risk_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    risk_factors TEXT,
    risk_score DECIMAL(3,2) NOT NULL,
    risk_level ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    fraud_indicators TEXT,
    ml_prediction DECIMAL(3,2),
    manual_review_required BOOLEAN DEFAULT FALSE,
    review_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    review_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_risk_level (risk_level),
    INDEX idx_manual_review_required (manual_review_required),
    FOREIGN KEY (transaction_id) REFERENCES wp_wvs_payment_transactions(id) ON DELETE CASCADE
);

-- Tabla de notificaciones de pago
CREATE TABLE wp_wvs_payment_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    notification_type ENUM('sms', 'email', 'whatsapp', 'push') NOT NULL,
    recipient VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
    delivery_timestamp TIMESTAMP NULL,
    error_message TEXT,
    retry_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_notification_type (notification_type),
    INDEX idx_status (status),
    FOREIGN KEY (transaction_id) REFERENCES wp_wvs_payment_transactions(id) ON DELETE CASCADE
);

-- Tabla de wallets de criptomonedas
CREATE TABLE wp_wvs_crypto_wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_address VARCHAR(200) NOT NULL UNIQUE,
    wallet_type ENUM('bitcoin', 'ethereum', 'usdt') NOT NULL,
    wallet_network VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    balance DECIMAL(20,8) DEFAULT 0.00000000,
    last_balance_check TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_wallet_type (wallet_type),
    INDEX idx_is_active (is_active)
);

-- Tabla de zonas de entrega
CREATE TABLE wp_wvs_delivery_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_name VARCHAR(100) NOT NULL,
    zone_type ENUM('city', 'state', 'region', 'custom') NOT NULL,
    zone_coordinates TEXT,
    delivery_fee DECIMAL(8,2) DEFAULT 0.00,
    delivery_time_min INT DEFAULT 30,
    delivery_time_max INT DEFAULT 120,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_zone_type (zone_type),
    INDEX idx_is_active (is_active)
);
```

### **📱 Gateway Pago Móvil Avanzado**
```php
class Woocommerce_Venezuela_Suite_Gateway_Pago_Movil extends WC_Payment_Gateway {
    
    private $api_client;
    private $verifier;
    private $notifier;
    private $risk_analyzer;
    
    public function __construct() {
        $this->id = 'wvs_pago_movil';
        $this->title = __('Pago Móvil Venezuela', 'woocommerce-venezuela-suite');
        $this->description = __('Paga usando Pago Móvil venezolano con verificación automática', 'woocommerce-venezuela-suite');
        $this->icon = plugin_dir_url(__FILE__) . 'assets/images/payment-icons/pago-movil.png';
        $this->has_fields = true;
        $this->supports = array('products');
        
        $this->init_form_fields();
        $this->init_settings();
        
        $this->api_client = new Woocommerce_Venezuela_Suite_Pago_Movil_API();
        $this->verifier = new Woocommerce_Venezuela_Suite_Pago_Movil_Verifier();
        $this->notifier = new Woocommerce_Venezuela_Suite_Pago_Movil_Notifier();
        $this->risk_analyzer = new Woocommerce_Venezuela_Suite_Payment_Risk_Analyzer();
        
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('wp_ajax_wvs_verify_pago_movil', array($this, 'verify_payment_ajax'));
        add_action('wp_ajax_nopriv_wvs_verify_pago_movil', array($this, 'verify_payment_ajax'));
    }
    
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Habilitar/Deshabilitar', 'woocommerce-venezuela-suite'),
                'type' => 'checkbox',
                'label' => __('Habilitar Pago Móvil', 'woocommerce-venezuela-suite'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Título', 'woocommerce-venezuela-suite'),
                'type' => 'text',
                'description' => __('Título que el usuario ve durante el checkout.', 'woocommerce-venezuela-suite'),
                'default' => __('Pago Móvil', 'woocommerce-venezuela-suite'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Descripción', 'woocommerce-venezuela-suite'),
                'type' => 'textarea',
                'description' => __('Descripción que el usuario ve durante el checkout.', 'woocommerce-venezuela-suite'),
                'default' => __('Paga usando Pago Móvil venezolano. Recibirás un SMS con las instrucciones.', 'woocommerce-venezuela-suite'),
            ),
            'api_settings' => array(
                'title' => __('Configuración API', 'woocommerce-venezuela-suite'),
                'type' => 'title',
                'description' => __('Configuración para integración con API de Pago Móvil', 'woocommerce-venezuela-suite'),
            ),
            'api_url' => array(
                'title' => __('URL API', 'woocommerce-venezuela-suite'),
                'type' => 'text',
                'description' => __('URL del servicio API de Pago Móvil', 'woocommerce-venezuela-suite'),
                'default' => '',
            ),
            'api_key' => array(
                'title' => __('API Key', 'woocommerce-venezuela-suite'),
                'type' => 'password',
                'description' => __('Clave API para autenticación', 'woocommerce-venezuela-suite'),
                'default' => '',
            ),
            'api_secret' => array(
                'title' => __('API Secret', 'woocommerce-venezuela-suite'),
                'type' => 'password',
                'description' => __('Secreto API para autenticación', 'woocommerce-venezuela-suite'),
                'default' => '',
            ),
            'verification_settings' => array(
                'title' => __('Configuración de Verificación', 'woocommerce-venezuela-suite'),
                'type' => 'title',
                'description' => __('Configuración para verificación automática de pagos', 'woocommerce-venezuela-suite'),
            ),
            'verification_interval' => array(
                'title' => __('Intervalo de Verificación (segundos)', 'woocommerce-venezuela-suite'),
                'type' => 'number',
                'description' => __('Intervalo entre verificaciones automáticas', 'woocommerce-venezuela-suite'),
                'default' => '30',
                'custom_attributes' => array(
                    'min' => '10',
                    'max' => '300'
                )
            ),
            'verification_timeout' => array(
                'title' => __('Timeout de Verificación (minutos)', 'woocommerce-venezuela-suite'),
                'type' => 'number',
                'description' => __('Tiempo máximo para verificar un pago', 'woocommerce-venezuela-suite'),
                'default' => '60',
                'custom_attributes' => array(
                    'min' => '5',
                    'max' => '180'
                )
            ),
            'notification_settings' => array(
                'title' => __('Configuración de Notificaciones', 'woocommerce-venezuela-suite'),
                'type' => 'title',
                'description' => __('Configuración para notificaciones automáticas', 'woocommerce-venezuela-suite'),
            ),
            'sms_enabled' => array(
                'title' => __('Habilitar SMS', 'woocommerce-venezuela-suite'),
                'type' => 'checkbox',
                'label' => __('Enviar notificaciones por SMS', 'woocommerce-venezuela-suite'),
                'default' => 'yes'
            ),
            'whatsapp_enabled' => array(
                'title' => __('Habilitar WhatsApp', 'woocommerce-venezuela-suite'),
                'type' => 'checkbox',
                'label' => __('Enviar notificaciones por WhatsApp', 'woocommerce-venezuela-suite'),
                'default' => 'yes'
            ),
            'email_enabled' => array(
                'title' => __('Habilitar Email', 'woocommerce-venezuela-suite'),
                'type' => 'checkbox',
                'label' => __('Enviar notificaciones por Email', 'woocommerce-venezuela-suite'),
                'default' => 'yes'
            ),
            'risk_settings' => array(
                'title' => __('Configuración de Riesgo', 'woocommerce-venezuela-suite'),
                'type' => 'title',
                'description' => __('Configuración para análisis de riesgo', 'woocommerce-venezuela-suite'),
            ),
            'fraud_detection' => array(
                'title' => __('Detección de Fraude', 'woocommerce-venezuela-suite'),
                'type' => 'checkbox',
                'label' => __('Habilitar detección automática de fraude', 'woocommerce-venezuela-suite'),
                'default' => 'yes'
            ),
            'risk_threshold' => array(
                'title' => __('Umbral de Riesgo', 'woocommerce-venezuela-suite'),
                'type' => 'number',
                'description' => __('Puntuación de riesgo máxima permitida (0-100)', 'woocommerce-venezuela-suite'),
                'default' => '70',
                'custom_attributes' => array(
                    'min' => '0',
                    'max' => '100'
                )
            )
        );
    }
    
    public function payment_fields() {
        echo '<div id="wvs-pago-movil-payment-fields">';
        
        woocommerce_form_field('pago_movil_phone', array(
            'type' => 'tel',
            'class' => array('form-row-wide'),
            'label' => __('Número de Teléfono', 'woocommerce-venezuela-suite'),
            'required' => true,
            'placeholder' => '+58-412-1234567',
            'custom_attributes' => array(
                'pattern' => '\\+58-[0-9]{3}-[0-9]{7}',
                'title' => __('Formato: +58-XXX-XXXXXXX', 'woocommerce-venezuela-suite')
            )
        ), WC()->checkout->get_value('pago_movil_phone'));
        
        woocommerce_form_field('pago_movil_bank', array(
            'type' => 'select',
            'class' => array('form-row-wide'),
            'label' => __('Banco', 'woocommerce-venezuela-suite'),
            'required' => true,
            'options' => array(
                '' => __('Selecciona tu banco', 'woocommerce-venezuela-suite'),
                'banesco' => __('Banesco', 'woocommerce-venezuela-suite'),
                'mercantil' => __('Mercantil', 'woocommerce-venezuela-suite'),
                'bbva' => __('BBVA', 'woocommerce-venezuela-suite'),
                'bancamiga' => __('Bancamiga', 'woocommerce-venezuela-suite'),
                '100banco' => __('100% Banco', 'woocommerce-venezuela-suite'),
                'bfc' => __('BFC', 'woocommerce-venezuela-suite'),
                'venezuela' => __('Banco de Venezuela', 'woocommerce-venezuela-suite')
            )
        ), WC()->checkout->get_value('pago_movil_bank'));
        
        echo '<div id="pago-movil-instructions" style="display: none;">';
        echo '<h4>' . __('Instrucciones de Pago', 'woocommerce-venezuela-suite') . '</h4>';
        echo '<ol>';
        echo '<li>' . __('Recibirás un SMS con el código de pago', 'woocommerce-venezuela-suite') . '</li>';
        echo '<li>' . __('Abre tu aplicación bancaria', 'woocommerce-venezuela-suite') . '</li>';
        echo '<li>' . __('Selecciona Pago Móvil', 'woocommerce-venezuela-suite') . '</li>';
        echo '<li>' . __('Ingresa el código recibido', 'woocommerce-venezuela-suite') . '</li>';
        echo '<li>' . __('Confirma el pago', 'woocommerce-venezuela-suite') . '</li>';
        echo '</ol>';
        echo '</div>';
        
        echo '</div>';
        
        // Encolar JavaScript específico
        wp_enqueue_script('wvs-pago-movil-checkout', plugin_dir_url(__FILE__) . 'public/js/pago-movil-checkout.js', array('jquery'), WOOCOMMERCE_VENEZUELA_SUITE_VERSION, true);
        
        wp_localize_script('wvs-pago-movil-checkout', 'wvs_pago_movil', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wvs_pago_movil_nonce'),
            'verification_interval' => $this->get_option('verification_interval', 30),
            'messages' => array(
                'phone_required' => __('El número de teléfono es requerido', 'woocommerce-venezuela-suite'),
                'bank_required' => __('Debes seleccionar un banco', 'woocommerce-venezuela-suite'),
                'phone_invalid' => __('Formato de teléfono inválido', 'woocommerce-venezuela-suite'),
                'payment_processing' => __('Procesando pago...', 'woocommerce-venezuela-suite'),
                'payment_verified' => __('Pago verificado exitosamente', 'woocommerce-venezuela-suite'),
                'payment_failed' => __('Error al verificar el pago', 'woocommerce-venezuela-suite')
            )
        ));
    }
    
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return array(
                'result' => 'fail',
                'redirect' => ''
            );
        }
        
        // Validar datos del formulario
        $phone = sanitize_text_field($_POST['pago_movil_phone']);
        $bank = sanitize_text_field($_POST['pago_movil_bank']);
        
        if (!$this->validate_phone_number($phone)) {
            wc_add_notice(__('Número de teléfono inválido', 'woocommerce-venezuela-suite'), 'error');
            return array(
                'result' => 'fail',
                'redirect' => ''
            );
        }
        
        if (!$bank) {
            wc_add_notice(__('Debes seleccionar un banco', 'woocommerce-venezuela-suite'), 'error');
            return array(
                'result' => 'fail',
                'redirect' => ''
            );
        }
        
        // Análisis de riesgo
        $risk_score = $this->risk_analyzer->analyze_transaction($order, array(
            'payment_method' => 'pago_movil',
            'phone' => $phone,
            'bank' => $bank
        ));
        
        if ($risk_score > $this->get_option('risk_threshold', 70)) {
            wc_add_notice(__('Transacción rechazada por alto riesgo', 'woocommerce-venezuela-suite'), 'error');
            return array(
                'result' => 'fail',
                'redirect' => ''
            );
        }
        
        // Generar código de pago
        $payment_code = $this->generate_payment_code($order_id);
        
        // Crear transacción en base de datos
        $transaction_id = $this->create_transaction($order_id, array(
            'payment_method' => 'pago_movil',
            'phone' => $phone,
            'bank' => $bank,
            'payment_code' => $payment_code,
            'risk_score' => $risk_score
        ));
        
        // Enviar notificaciones
        $this->notifier->send_payment_instructions($order, array(
            'phone' => $phone,
            'bank' => $bank,
            'payment_code' => $payment_code,
            'amount' => $order->get_total(),
            'currency' => $order->get_currency()
        ));
        
        // Iniciar verificación automática
        $this->start_verification_process($transaction_id);
        
        // Redirigir a página de confirmación
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }
    
    private function validate_phone_number($phone) {
        // Validar formato venezolano: +58-XXX-XXXXXXX
        $pattern = '/^\+58-[0-9]{3}-[0-9]{7}$/';
        return preg_match($pattern, $phone);
    }
    
    private function generate_payment_code($order_id) {
        // Generar código único de 6 dígitos
        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Verificar que no exista
        global $wpdb;
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wvs_payment_transactions 
             WHERE payment_data LIKE %s AND status = 'pending'",
            '%"payment_code":"' . $code . '"%'
        ));
        
        if ($existing > 0) {
            return $this->generate_payment_code($order_id); // Recursivo
        }
        
        return $code;
    }
    
    private function create_transaction($order_id, $data) {
        global $wpdb;
        
        $transaction_id = 'PM' . time() . $order_id;
        
        $wpdb->insert(
            $wpdb->prefix . 'wvs_payment_transactions',
            array(
                'order_id' => $order_id,
                'payment_method' => 'pago_movil',
                'transaction_id' => $transaction_id,
                'amount' => wc_get_order($order_id)->get_total(),
                'currency' => wc_get_order($order_id)->get_currency(),
                'status' => 'pending',
                'payment_data' => json_encode($data),
                'risk_score' => $data['risk_score'],
                'verification_status' => 'pending'
            )
        );
        
        return $transaction_id;
    }
    
    private function start_verification_process($transaction_id) {
        // Programar verificación automática
        wp_schedule_single_event(
            time() + $this->get_option('verification_interval', 30),
            'wvs_verify_pago_movil_payment',
            array($transaction_id)
        );
    }
    
    public function verify_payment_ajax() {
        check_ajax_referer('wvs_pago_movil_nonce', 'nonce');
        
        $transaction_id = sanitize_text_field($_POST['transaction_id']);
        $result = $this->verifier->verify_payment($transaction_id);
        
        wp_send_json($result);
    }
}
```

---

## **⚙️ CONFIGURACIÓN DEL MÓDULO**

### **🎛️ Configuración General**
```php
$gateways_settings = array(
    'default_currency' => 'VES',           // Moneda por defecto
    'auto_verify_payments' => true,       // Verificación automática
    'verification_interval' => 300,        // Intervalo de verificación (5 min)
    'payment_timeout' => 3600,            // Timeout de pago (1 hora)
    'send_notifications' => true,          // Enviar notificaciones
    'require_confirmation' => true,        // Requerir confirmación manual
    'debug_mode' => false                 // Modo debug
);
```

### **📱 Configuración Pago Móvil**
```php
$pago_movil_settings = array(
    'enabled' => true,
    'title' => 'Pago Móvil',
    'description' => 'Paga usando Pago Móvil venezolano',
    'phone_prefix' => '+58',
    'require_verification' => true,
    'verification_method' => 'sms',        // sms, whatsapp, email
    'instructions' => 'Instrucciones personalizadas...'
);
```

### **🏦 Configuración Transferencias**
```php
$transferencia_settings = array(
    'enabled' => true,
    'title' => 'Transferencia Bancaria',
    'description' => 'Transfiere a nuestras cuentas bancarias',
    'banks' => array(
        'banesco' => array(
            'name' => 'Banesco',
            'account_type' => 'Corriente',
            'account_number' => '0134-0000-0000-0000',
            'account_holder' => 'Empresa XYZ',
            'rif' => 'J-12345678-9'
        ),
        'mercantil' => array(
            'name' => 'Mercantil',
            'account_type' => 'Corriente',
            'account_number' => '0105-0000-0000-0000',
            'account_holder' => 'Empresa XYZ',
            'rif' => 'J-12345678-9'
        )
    ),
    'require_receipt' => true,
    'receipt_upload' => true
);
```

---

## **🔄 FLUJO DE FUNCIONAMIENTO**

### **💳 Proceso de Pago General**
1. **Cliente selecciona** método de pago
2. **Sistema valida** método disponible
3. **Muestra formulario** específico del método
4. **Cliente completa** información requerida
5. **Sistema procesa** información
6. **Genera instrucciones** de pago
7. **Envía notificaciones** al cliente
8. **Inicia verificación** automática
9. **Confirma pago** recibido
10. **Actualiza estado** del pedido

### **📱 Flujo Pago Móvil Específico**
1. **Cliente ingresa** número telefónico
2. **Sistema valida** formato venezolano
3. **Genera código** de pago único
4. **Envía SMS** con instrucciones
5. **Cliente realiza** pago móvil
6. **Sistema verifica** pago automáticamente
7. **Confirma** y actualiza pedido

---

## **🎨 INTEGRACIÓN CON WOOCOMMERCE**

### **🔌 Registro de Gateways**
```php
add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateways'));

public function add_payment_gateways($gateways) {
    $gateways[] = 'Woocommerce_Venezuela_Suite_Gateway_Pago_Movil';
    $gateways[] = 'Woocommerce_Venezuela_Suite_Gateway_Transferencia';
    $gateways[] = 'Woocommerce_Venezuela_Suite_Gateway_Zelle';
    $gateways[] = 'Woocommerce_Venezuela_Suite_Gateway_Efectivo';
    $gateways[] = 'Woocommerce_Venezuela_Suite_Gateway_Crypto';
    return $gateways;
}
```

### **📋 Campos de Checkout**
- **Validación** de campos específicos por país
- **Campos adicionales** según método de pago
- **Instrucciones** dinámicas de pago
- **Confirmación** de datos ingresados

---

## **🧪 TESTING**

### **🔍 Casos de Prueba**
- **Registro** de gateways en WooCommerce
- **Validación** de formularios de pago
- **Procesamiento** de pagos
- **Verificación** automática
- **Notificaciones** a clientes
- **Manejo** de errores

### **📊 Datos de Prueba**
- **Números telefónicos** venezolanos válidos/inválidos
- **Números de cuenta** bancarios
- **Emails** de Zelle
- **Direcciones** de wallets crypto
- **Comprobantes** de transferencia

---

## **🚨 MANEJO DE ERRORES**

### **⚠️ Errores Comunes**
- **Número telefónico** inválido → Validación mejorada
- **Pago no recibido** → Reintento de verificación
- **Timeout** de pago → Notificación al cliente
- **Error de validación** → Log y notificación

### **📋 Logging**
- **Transacciones** de pago
- **Errores** de validación
- **Verificaciones** automáticas
- **Notificaciones** enviadas

---

## **📈 MÉTRICAS DE ÉXITO**

### **🎯 KPIs del Módulo**
- **Tasa de conversión** de checkout > 20%
- **Tiempo promedio** de verificación < 5 minutos
- **Tasa de éxito** de pagos > 95%
- **Satisfacción** del cliente > 4.5/5

### **📊 Métricas Específicas**
- **Uso** por método de pago
- **Tiempo** de procesamiento por gateway
- **Errores** por método de pago
- **Abandono** en checkout por método

---

## **🔗 DEPENDENCIAS**

### **📦 Módulos Requeridos**
- **Currency Converter** (para conversiones de precio)

### **📦 Módulos que Dependen de Este**
- **Fiscal Compliance** (para reportes de pagos)
- **Reports Analytics** (para métricas de pagos)
- **Notifications** (para notificaciones de pago)

### **🔌 Integraciones Externas**
- **SMS Gateway** (para Pago Móvil)
- **WhatsApp API** (para notificaciones)
- **Email Service** (para confirmaciones)
- **Crypto APIs** (para wallets)

---

## **📅 CRONOGRAMA DE DESARROLLO**

### **📅 Semana 1: Gateways Básicos**
- **Día 1-2**: Estructura del módulo y clase base
- **Día 3-4**: Gateway Pago Móvil
- **Día 5**: Gateway Transferencias

### **📅 Semana 2: Gateways Avanzados**
- **Día 1-2**: Gateway Zelle
- **Día 3-4**: Gateway Efectivo
- **Día 5**: Gateway Criptomonedas

### **📅 Semana 3: Integración y Testing**
- **Día 1-2**: Sistema de verificación
- **Día 3-4**: Notificaciones y admin
- **Día 5**: Testing completo

---

## **🚀 PRÓXIMOS PASOS**

1. **Crear estructura** de carpetas del módulo
2. **Implementar** clase base de gateway
3. **Desarrollar** gateway Pago Móvil
4. **Implementar** gateways bancarios
5. **Crear** sistema de verificación
6. **Desarrollar** panel de administración
7. **Testing** completo del módulo
8. **Documentación** y deployment

---

*Este módulo es crucial para la experiencia de pago y debe ser confiable, fácil de usar y compatible con los métodos de pago más populares en Venezuela.*
