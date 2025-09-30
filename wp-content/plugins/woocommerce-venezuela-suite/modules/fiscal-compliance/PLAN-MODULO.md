# 🧾 **PLAN MÓDULO: Fiscal Compliance - MEJORADO**

## **🎯 OBJETIVO DEL MÓDULO**

Implementar cumplimiento fiscal completo para Venezuela **trabajando en armonía con WooCommerce**, incluyendo integración con el sistema de impuestos nativo de WooCommerce, facturación electrónica SENIAT, validación de RIF venezolano, y generación de documentos fiscales imprimibles, respetando la configuración existente de WooCommerce y proporcionando visibilidad de los cálculos fiscales.

---

## **📋 FUNCIONALIDADES PRINCIPALES**

## **📋 FUNCIONALIDADES PRINCIPALES MEJORADAS**

### **🔗 1. Integración Armónica con WooCommerce**
- **Respeto** de la configuración de impuestos existente de WooCommerce
- **Visibilidad** de cálculos fiscales en configuración de WooCommerce
- **Extensión** del sistema de tax classes nativo
- **Integración** con WooCommerce Tax Settings
- **Compatibilidad** con plugins de impuestos existentes
- **Preservación** de funcionalidad nativa de WooCommerce

### **📄 2. Generación de Documentos Fiscales SENIAT**
- **Facturas electrónicas** según normativas SENIAT
- **Documentos imprimibles** en formato PDF
- **Numeración consecutiva** obligatoria
- **Formato** según normativas vigentes
- **Validación** automática de datos fiscales
- **Envío** automático por email
- **Almacenamiento** seguro para auditorías

### **💰 3. Sistema de Impuestos Flexible**
- **IVA configurable** (actualmente 16%, pero adaptable)
- **IGTF configurable** (actualmente 3%, pero adaptable)
- **Umbrales** configurables para IGTF
- **Exenciones** por producto/categoría
- **Tax classes** personalizadas para Venezuela
- **Cálculo diferenciado** según normativas

### **🆔 4. Validación de Datos Venezolanos**
- **RIF venezolano** (formato J-12345678-9) - **OBLIGATORIO**
- **Cédula de identidad** venezolana - **OPCIONAL** (recomendada para seguridad)
- **Direcciones** fiscales completas
- **Datos** de empresas venezolanas
- **Verificación** de contribuyentes SENIAT
- **Validación** en tiempo real

### **📊 5. Reportes Fiscales Automáticos**
- **Reportes SENIAT** en formato oficial
- **Libro de ventas** diario/mensual
- **Declaraciones** de IVA automáticas
- **Reportes** de IGTF detallados
- **Auditoría** fiscal completa
- **Exportación** en múltiples formatos

### **⚙️ 6. Configuración Transparente**
- **Panel** integrado en WooCommerce Tax Settings
- **Visibilidad** de cálculos fiscales venezolanos
- **Configuración** de tasas de impuestos
- **Gestión** de exenciones fiscales
- **Configuración** de documentos SENIAT
- **Monitoreo** de cumplimiento fiscal

---

## **🏗️ ESTRUCTURA DEL MÓDULO**

## **🏗️ ESTRUCTURA DEL MÓDULO MEJORADA**

### **📁 Archivos Principales**
```
modules/fiscal-compliance/
├── fiscal-compliance.php                        # 🚀 Bootstrap del módulo
├── includes/
│   ├── class-fiscal-core.php                    # ⚙️ Funcionalidad principal
│   ├── class-woocommerce-tax-integration.php     # 🔗 Integración con WooCommerce Tax
│   ├── class-tax-calculator.php                 # 🧮 Calculadora de impuestos
│   ├── class-seniat-document-generator.php      # 📄 Generador documentos SENIAT
│   ├── class-rif-validator.php                 # 🆔 Validador de RIF
│   ├── class-cedula-validator.php              # 🆔 Validador de Cédula (Opcional)
│   ├── class-seniat-reports.php                 # 📊 Reportes SENIAT
│   ├── class-fiscal-exemptions.php              # 🆓 Exenciones fiscales
│   ├── class-fiscal-audit.php                   # 🔍 Auditoría fiscal
│   ├── class-fiscal-settings.php                # ⚙️ Configuración fiscal
│   └── class-fiscal-admin.php                   # 👨‍💼 Panel administrativo
├── admin/
│   ├── css/fiscal-compliance-admin.css         # 🎨 Estilos admin
│   ├── js/fiscal-compliance-admin.js           # 📱 JavaScript admin
│   └── partials/
│       ├── fiscal-settings.php                 # ⚙️ Configuración fiscal
│       ├── woocommerce-tax-integration.php     # 🔗 Integración WooCommerce Tax
│       ├── tax-rates-configuration.php         # 💰 Configuración tasas impuestos
│       ├── seniat-document-settings.php        # 📄 Configuración documentos SENIAT
│       ├── exemptions-settings.php             # 🆓 Configuración de exenciones
│       ├── seniat-reports.php                  # 📊 Reportes SENIAT
│       ├── fiscal-audit.php                    # 🔍 Auditoría fiscal
│       ├── document-generator.php              # 📄 Generador de documentos
│       └── fiscal-dashboard.php                # 📊 Dashboard fiscal
├── public/
│   ├── css/fiscal-compliance-public.css        # 🎨 Estilos públicos
│   └── js/fiscal-compliance-public.js          # 📱 JavaScript público
├── templates/
│   ├── seniat-invoice-template.php             # 📄 Template factura SENIAT
│   ├── tax-breakdown.php                       # 💰 Desglose de impuestos
│   ├── rif-validation-form.php                 # 🆔 Formulario validación RIF
│   ├── fiscal-receipt.php                      # 🧾 Recibo fiscal
│   └── printable-documents/
│       ├── invoice-pdf.php                     # 📄 Template PDF factura
│       ├── receipt-pdf.php                     # 🧾 Template PDF recibo
│       └── fiscal-report-pdf.php               # 📊 Template PDF reporte
├── reports/
│   ├── seniat-daily.php                        # 📊 Reporte diario SENIAT
│   ├── seniat-monthly.php                      # 📊 Reporte mensual SENIAT
│   ├── iva-declaration.php                     # 📊 Declaración IVA
│   ├── igtf-report.php                         # 📊 Reporte IGTF
│   └── fiscal-audit-report.php                 # 🔍 Reporte auditoría fiscal
├── woocommerce-integration/
│   ├── class-tax-classes-manager.php           # 🏷️ Gestor de tax classes
│   ├── class-tax-rates-manager.php             # 💰 Gestor de tax rates
│   ├── class-checkout-fields.php               # 📝 Campos checkout fiscales
│   ├── class-order-fiscal-data.php             # 📋 Datos fiscales pedidos
│   └── hooks/
│       ├── tax-calculation-hooks.php           # 🪝 Hooks cálculo impuestos
│       ├── checkout-hooks.php                  # 🪝 Hooks checkout
│       └── order-hooks.php                     # 🪝 Hooks pedidos
├── data/
│   ├── tax-exemptions.json                     # 🆓 Exenciones de impuestos
│   ├── fiscal-categories.json                  # 📂 Categorías fiscales
│   ├── seniat-formats.json                     # 📄 Formatos SENIAT
│   └── venezuelan-tax-rates.json              # 💰 Tasas impuestos Venezuela
├── assets/
│   ├── images/
│   │   ├── seniat-logos/                       # 🏛️ Logos SENIAT
│   │   ├── fiscal-icons/                       # 🧾 Iconos fiscales
│   │   └── document-templates/                # 📄 Templates documentos
│   └── templates/
│       ├── invoice-templates/                  # 📄 Templates de factura
│       ├── receipt-templates/                  # 🧾 Templates de recibo
│       └── report-templates/                   # 📊 Templates de reportes
├── languages/
│   └── fiscal-compliance.pot                   # 🌍 Traducciones
├── tests/
│   ├── test-tax-calculator.php                 # 🧪 Tests calculadora impuestos
│   ├── test-rif-validator.php                  # 🧪 Tests validador RIF
│   ├── test-seniat-document-generator.php      # 🧪 Tests generador documentos
│   ├── test-woocommerce-integration.php        # 🧪 Tests integración WooCommerce
│   └── test-fiscal-integration.php             # 🧪 Tests integración fiscal
├── PLAN-MODULO.md                              # 📋 Este archivo
├── README.md                                    # 📖 Documentación
└── uninstall.php                                # 🗑️ Limpieza al eliminar
```

---

## **🔧 IMPLEMENTACIÓN TÉCNICA**

## **🔧 IMPLEMENTACIÓN TÉCNICA MEJORADA**

### **🔗 Integración Armónica con WooCommerce Tax**

#### **🏷️ Tax Classes Manager**
```php
class Woocommerce_Venezuela_Suite_Tax_Classes_Manager {
    
    public function __construct() {
        add_action('init', array($this, 'register_venezuelan_tax_classes'));
        add_filter('woocommerce_tax_classes', array($this, 'add_venezuelan_tax_classes'));
        add_action('woocommerce_settings_tax_options_end', array($this, 'display_venezuelan_tax_info'));
    }
    
    public function register_venezuelan_tax_classes() {
        // Registrar tax classes específicas para Venezuela
        $tax_classes = array(
            'venezuela-iva-standard' => __('IVA Estándar Venezuela', 'woocommerce-venezuela-suite'),
            'venezuela-iva-exempt' => __('IVA Exento Venezuela', 'woocommerce-venezuela-suite'),
            'venezuela-igtf-applicable' => __('IGTF Aplicable', 'woocommerce-venezuela-suite'),
            'venezuela-igtf-exempt' => __('IGTF Exento', 'woocommerce-venezuela-suite')
        );
        
        foreach ($tax_classes as $class => $name) {
            $this->create_tax_class_if_not_exists($class, $name);
        }
    }
    
    public function add_venezuelan_tax_classes($classes) {
        $venezuelan_classes = array(
            'venezuela-iva-standard' => __('IVA Estándar Venezuela', 'woocommerce-venezuela-suite'),
            'venezuela-iva-exempt' => __('IVA Exento Venezuela', 'woocommerce-venezuela-suite'),
            'venezuela-igtf-applicable' => __('IGTF Aplicable', 'woocommerce-venezuela-suite'),
            'venezuela-igtf-exempt' => __('IGTF Exento', 'woocommerce-venezuela-suite')
        );
        
        return array_merge($classes, $venezuelan_classes);
    }
    
    public function display_venezuelan_tax_info() {
        // Mostrar información fiscal venezolana en WooCommerce Tax Settings
        echo '<div class="wvs-tax-info">';
        echo '<h3>' . __('Información Fiscal Venezuela', 'woocommerce-venezuela-suite') . '</h3>';
        echo '<p>' . __('Este módulo extiende el sistema de impuestos de WooCommerce para cumplir con las normativas fiscales venezolanas.', 'woocommerce-venezuela-suite') . '</p>';
        
        // Mostrar tasas actuales
        $iva_rate = get_option('wvs_iva_rate', 16);
        $igtf_rate = get_option('wvs_igtf_rate', 3);
        $igtf_threshold = get_option('wvs_igtf_threshold', 200);
        
        echo '<div class="wvs-current-rates">';
        echo '<h4>' . __('Tasas Actuales', 'woocommerce-venezuela-suite') . '</h4>';
        echo '<ul>';
        echo '<li><strong>IVA:</strong> ' . $iva_rate . '%</li>';
        echo '<li><strong>IGTF:</strong> ' . $igtf_rate . '% (sobre transacciones > $' . $igtf_threshold . ' USD)</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="wvs-tax-classes-info">';
        echo '<h4>' . __('Tax Classes Registradas', 'woocommerce-venezuela-suite') . '</h4>';
        echo '<ul>';
        echo '<li><strong>IVA Estándar Venezuela:</strong> Productos gravados con IVA</li>';
        echo '<li><strong>IVA Exento Venezuela:</strong> Productos exentos de IVA</li>';
        echo '<li><strong>IGTF Aplicable:</strong> Transacciones sujetas a IGTF</li>';
        echo '<li><strong>IGTF Exento:</strong> Transacciones exentas de IGTF</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '</div>';
    }
    
    private function create_tax_class_if_not_exists($class_slug, $class_name) {
        global $wpdb;
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT tax_class FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_class = %s",
            $class_slug
        ));
        
        if (!$existing) {
            // Crear tax class si no existe
            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_tax_rates',
                array(
                    'tax_rate_country' => 'VE',
                    'tax_rate_state' => '',
                    'tax_rate' => 0,
                    'tax_rate_name' => $class_name,
                    'tax_rate_priority' => 1,
                    'tax_rate_compound' => 0,
                    'tax_rate_shipping' => 1,
                    'tax_rate_order' => 0,
                    'tax_rate_class' => $class_slug
                )
            );
        }
    }
}
```

#### **💰 Tax Rates Manager**
```php
class Woocommerce_Venezuela_Suite_Tax_Rates_Manager {
    
    public function __construct() {
        add_action('init', array($this, 'setup_venezuelan_tax_rates'));
        add_filter('woocommerce_tax_rate', array($this, 'adjust_tax_rate'), 10, 2);
        add_action('woocommerce_cart_calculate_fees', array($this, 'calculate_igtf'));
    }
    
    public function setup_venezuelan_tax_rates() {
        // Configurar tax rates para Venezuela
        $this->setup_iva_rates();
        $this->setup_igtf_rates();
    }
    
    private function setup_iva_rates() {
        $iva_rate = get_option('wvs_iva_rate', 16);
        
        // Tax rate para IVA estándar
        $this->create_or_update_tax_rate(
            'venezuela-iva-standard',
            'IVA Venezuela',
            $iva_rate,
            'VE',
            '',
            1, // priority
            0, // compound
            1  // shipping
        );
    }
    
    private function setup_igtf_rates() {
        $igtf_rate = get_option('wvs_igtf_rate', 3);
        
        // Tax rate para IGTF (se aplicará como fee, no como tax rate tradicional)
        $this->create_or_update_tax_rate(
            'venezuela-igtf-applicable',
            'IGTF Venezuela',
            $igtf_rate,
            'VE',
            '',
            2, // priority
            0, // compound
            0  // shipping (IGTF no aplica a envíos)
        );
    }
    
    private function create_or_update_tax_rate($class, $name, $rate, $country, $state, $priority, $compound, $shipping) {
        global $wpdb;
        
        $existing_rate = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates 
             WHERE tax_rate_class = %s AND tax_rate_country = %s",
            $class, $country
        ));
        
        if ($existing_rate) {
            // Actualizar rate existente
            $wpdb->update(
                $wpdb->prefix . 'woocommerce_tax_rates',
                array(
                    'tax_rate' => $rate,
                    'tax_rate_name' => $name,
                    'tax_rate_priority' => $priority,
                    'tax_rate_compound' => $compound,
                    'tax_rate_shipping' => $shipping
                ),
                array('tax_rate_id' => $existing_rate->tax_rate_id)
            );
        } else {
            // Crear nuevo rate
            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_tax_rates',
                array(
                    'tax_rate_country' => $country,
                    'tax_rate_state' => $state,
                    'tax_rate' => $rate,
                    'tax_rate_name' => $name,
                    'tax_rate_priority' => $priority,
                    'tax_rate_compound' => $compound,
                    'tax_rate_shipping' => $shipping,
                    'tax_rate_order' => 0,
                    'tax_rate_class' => $class
                )
            );
        }
    }
    
    public function calculate_igtf($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        $igtf_threshold = get_option('wvs_igtf_threshold', 200);
        $igtf_rate = get_option('wvs_igtf_rate', 3);
        
        // Calcular total en USD
        $total_usd = $this->get_cart_total_usd($cart);
        
        if ($total_usd > $igtf_threshold) {
            $igtf_amount = $total_usd * ($igtf_rate / 100);
            
            $cart->add_fee(
                __('IGTF Venezuela', 'woocommerce-venezuela-suite'),
                $igtf_amount
            );
        }
    }
    
    private function get_cart_total_usd($cart) {
        // Obtener total del carrito en USD
        // Esto requerirá integración con el módulo Currency Converter
        $currency_converter = Woocommerce_Venezuela_Suite_Converter_Core::get_instance();
        return $currency_converter->convert_to_usd($cart->get_subtotal());
    }
}
```

### **📄 Generador de Documentos SENIAT**
```php
class Woocommerce_Venezuela_Suite_SENIAT_Document_Generator {
    
    public function __construct() {
        add_action('woocommerce_order_status_completed', array($this, 'generate_seniat_invoice'));
        add_action('wp_ajax_wvs_print_invoice', array($this, 'print_invoice'));
        add_action('wp_ajax_nopriv_wvs_print_invoice', array($this, 'print_invoice'));
    }
    
    public function generate_seniat_invoice($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return false;
        }
        
        // Generar número de factura consecutivo
        $invoice_number = $this->generate_invoice_number();
        
        // Crear datos de la factura
        $invoice_data = array(
            'invoice_number' => $invoice_number,
            'order_id' => $order_id,
            'invoice_date' => current_time('Y-m-d'),
            'customer_data' => $this->get_customer_fiscal_data($order),
            'company_data' => $this->get_company_fiscal_data(),
            'items' => $this->get_order_items_data($order),
            'taxes' => $this->get_order_taxes_data($order),
            'totals' => $this->get_order_totals_data($order)
        );
        
        // Guardar en base de datos
        $this->save_invoice_to_database($invoice_data);
        
        // Generar PDF
        $pdf_path = $this->generate_invoice_pdf($invoice_data);
        
        // Enviar por email
        $this->send_invoice_email($order, $pdf_path);
        
        return $invoice_data;
    }
    
    public function print_invoice() {
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        
        if (!$order || !current_user_can('manage_woocommerce')) {
            wp_die('Acceso denegado');
        }
        
        $invoice_data = $this->get_invoice_data($order_id);
        
        if (!$invoice_data) {
            wp_die('Factura no encontrada');
        }
        
        // Generar PDF para impresión
        $this->generate_printable_pdf($invoice_data);
    }
    
    private function generate_invoice_number() {
        global $wpdb;
        
        $prefix = get_option('wvs_invoice_prefix', 'FAC');
        $year = date('Y');
        
        // Obtener último número del año
        $last_number = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(CAST(SUBSTRING(invoice_number, %d) AS UNSIGNED)) 
             FROM {$wpdb->prefix}wvs_fiscal_invoices 
             WHERE invoice_number LIKE %s",
            strlen($prefix . $year) + 1,
            $prefix . $year . '%'
        ));
        
        $next_number = ($last_number ? $last_number : 0) + 1;
        
        return $prefix . $year . str_pad($next_number, 6, '0', STR_PAD_LEFT);
    }
    
    private function generate_invoice_pdf($invoice_data) {
        // Usar TCPDF o similar para generar PDF
        require_once plugin_dir_path(__FILE__) . '../vendor/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Configurar documento
        $pdf->SetCreator('WooCommerce Venezuela Suite');
        $pdf->SetTitle('Factura Fiscal ' . $invoice_data['invoice_number']);
        $pdf->SetSubject('Factura Electrónica SENIAT');
        
        // Agregar página
        $pdf->AddPage();
        
        // Generar contenido de la factura
        $this->generate_invoice_content($pdf, $invoice_data);
        
        // Guardar PDF
        $upload_dir = wp_upload_dir();
        $pdf_path = $upload_dir['basedir'] . '/wvs-invoices/' . $invoice_data['invoice_number'] . '.pdf';
        
        // Crear directorio si no existe
        wp_mkdir_p(dirname($pdf_path));
        
        $pdf->Output($pdf_path, 'F');
        
        return $pdf_path;
    }
    
    private function generate_invoice_content($pdf, $invoice_data) {
        // Generar contenido de la factura según formato SENIAT
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'FACTURA FISCAL', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Número: ' . $invoice_data['invoice_number'], 0, 1);
        $pdf->Cell(0, 5, 'Fecha: ' . $invoice_data['invoice_date'], 0, 1);
        
        // Datos de la empresa
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'DATOS DEL EMISOR', 0, 1);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'RIF: ' . $invoice_data['company_data']['rif'], 0, 1);
        $pdf->Cell(0, 5, 'Razón Social: ' . $invoice_data['company_data']['name'], 0, 1);
        $pdf->Cell(0, 5, 'Dirección: ' . $invoice_data['company_data']['address'], 0, 1);
        
        // Datos del cliente
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'DATOS DEL CLIENTE', 0, 1);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'RIF: ' . $invoice_data['customer_data']['rif'], 0, 1);
        $pdf->Cell(0, 5, 'Nombre: ' . $invoice_data['customer_data']['name'], 0, 1);
        $pdf->Cell(0, 5, 'Dirección: ' . $invoice_data['customer_data']['address'], 0, 1);
        
        // Tabla de productos
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 10);
        
        // Encabezados de tabla
        $pdf->Cell(60, 8, 'Descripción', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Cantidad', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Precio Unit.', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Subtotal', 1, 0, 'C');
        $pdf->Cell(30, 8, 'IVA', 1, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 9);
        
        foreach ($invoice_data['items'] as $item) {
            $pdf->Cell(60, 6, $item['name'], 1, 0);
            $pdf->Cell(30, 6, $item['quantity'], 1, 0, 'C');
            $pdf->Cell(30, 6, number_format($item['price'], 2, ',', '.'), 1, 0, 'R');
            $pdf->Cell(30, 6, number_format($item['subtotal'], 2, ',', '.'), 1, 0, 'R');
            $pdf->Cell(30, 6, number_format($item['iva'], 2, ',', '.'), 1, 1, 'R');
        }
        
        // Totales
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(120, 8, 'SUBTOTAL:', 0, 0, 'R');
        $pdf->Cell(30, 8, number_format($invoice_data['totals']['subtotal'], 2, ',', '.'), 1, 1, 'R');
        
        $pdf->Cell(120, 8, 'IVA:', 0, 0, 'R');
        $pdf->Cell(30, 8, number_format($invoice_data['totals']['iva'], 2, ',', '.'), 1, 1, 'R');
        
        if ($invoice_data['totals']['igtf'] > 0) {
            $pdf->Cell(120, 8, 'IGTF:', 0, 0, 'R');
            $pdf->Cell(30, 8, number_format($invoice_data['totals']['igtf'], 2, ',', '.'), 1, 1, 'R');
        }
        
        $pdf->Cell(120, 8, 'TOTAL:', 0, 0, 'R');
        $pdf->Cell(30, 8, number_format($invoice_data['totals']['total'], 2, ',', '.'), 1, 1, 'R');
        
        // Pie de página con información SENIAT
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, 'Este documento cumple con las normativas fiscales del SENIAT', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Factura electrónica generada automáticamente', 0, 1, 'C');
    }
}
```

---

## **⚙️ CONFIGURACIÓN DEL MÓDULO MEJORADA**

### **📊 Base de Datos Extendida**
```sql
-- Tabla principal de facturas fiscales
CREATE TABLE wp_wvs_fiscal_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(20) NOT NULL UNIQUE,
    order_id INT NOT NULL,
    customer_rif VARCHAR(20) NOT NULL,
    customer_name VARCHAR(200) NOT NULL,
    customer_address TEXT NOT NULL,
    customer_phone VARCHAR(20),
    customer_email VARCHAR(100),
    subtotal DECIMAL(12,2) NOT NULL,
    iva_amount DECIMAL(12,2) NOT NULL,
    igtf_amount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    invoice_date DATE NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    fiscal_status ENUM('pending', 'sent', 'confirmed', 'printed') DEFAULT 'pending',
    pdf_path VARCHAR(500),
    seniat_reference VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_order_id (order_id),
    INDEX idx_invoice_date (invoice_date),
    INDEX idx_fiscal_status (fiscal_status),
    FOREIGN KEY (order_id) REFERENCES wp_posts(ID) ON DELETE CASCADE
);

-- Tabla de impuestos detallados
CREATE TABLE wp_wvs_fiscal_taxes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    tax_type ENUM('iva', 'igtf') NOT NULL,
    tax_rate DECIMAL(5,2) NOT NULL,
    taxable_amount DECIMAL(12,2) NOT NULL,
    tax_amount DECIMAL(12,2) NOT NULL,
    is_exempt BOOLEAN DEFAULT FALSE,
    exemption_reason VARCHAR(200),
    tax_class VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_tax_type (tax_type),
    INDEX idx_tax_class (tax_class),
    FOREIGN KEY (order_id) REFERENCES wp_posts(ID) ON DELETE CASCADE
);

-- Tabla de exenciones fiscales
CREATE TABLE wp_wvs_fiscal_exemptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    category_id INT,
    exemption_type ENUM('iva', 'igtf', 'both') NOT NULL,
    exemption_reason VARCHAR(200) NOT NULL,
    exemption_code VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    valid_from DATE,
    valid_until DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id),
    INDEX idx_category_id (category_id),
    INDEX idx_exemption_type (exemption_type),
    INDEX idx_is_active (is_active)
);

-- Tabla de configuración fiscal
CREATE TABLE wp_wvs_fiscal_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT NOT NULL,
    config_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_editable BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de reportes SENIAT
CREATE TABLE wp_wvs_seniat_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_type ENUM('daily', 'monthly', 'yearly', 'iva_declaration', 'igtf_report') NOT NULL,
    report_period VARCHAR(20) NOT NULL,
    report_year INT NOT NULL,
    report_month INT,
    report_day INT,
    total_sales DECIMAL(15,2) NOT NULL,
    total_iva DECIMAL(15,2) NOT NULL,
    total_igtf DECIMAL(15,2) DEFAULT 0,
    invoice_count INT NOT NULL,
    report_status ENUM('generated', 'sent', 'confirmed') DEFAULT 'generated',
    file_path VARCHAR(500),
    seniat_reference VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_type (report_type),
    INDEX idx_report_period (report_period),
    INDEX idx_report_year (report_year),
    INDEX idx_report_status (report_status)
);
```

### **🎛️ Configuración Fiscal Flexible**
```php
$fiscal_settings = array(
    // Configuración de empresa
    'company_rif' => 'J-12345678-9',                    // RIF de la empresa
    'company_name' => 'Empresa XYZ',                     // Nombre de la empresa
    'company_address' => 'Dirección fiscal completa',    // Dirección fiscal
    'company_phone' => '+58-212-1234567',               // Teléfono de la empresa
    'company_email' => 'fiscal@empresa.com',            // Email fiscal
    
    // Configuración de impuestos (FLEXIBLES)
    'iva_rate' => 16.00,                                // Tasa de IVA (configurable)
    'iva_rate_effective_date' => '2024-01-01',         // Fecha de vigencia IVA
    'igtf_rate' => 3.00,                                // Tasa de IGTF (configurable)
    'igtf_rate_effective_date' => '2024-01-01',        // Fecha de vigencia IGTF
    'igtf_threshold' => 200.00,                         // Umbral IGTF en USD (configurable)
    'igtf_threshold_effective_date' => '2024-01-01',   // Fecha de vigencia umbral
    
    // Configuración de facturación
    'invoice_prefix' => 'FAC',                           // Prefijo de facturas
    'invoice_start_number' => 1,                        // Número inicial
    'invoice_number_format' => 'FAC{year}{number}',     // Formato número factura
    'auto_generate_invoices' => true,                    // Generar facturas automáticamente
    'send_invoices_email' => true,                      // Enviar facturas por email
    'invoice_email_template' => 'default',              // Template email factura
    
    // Configuración SENIAT
    'seniat_integration' => false,                       // Integración con SENIAT
    'seniat_api_url' => '',                             // URL API SENIAT
    'seniat_api_key' => '',                             // API Key SENIAT
    'seniat_test_mode' => true,                         // Modo prueba SENIAT
    
    // Configuración fiscal
    'fiscal_year_start' => '01-01',                     // Inicio del año fiscal
    'fiscal_currency' => 'VES',                         // Moneda fiscal principal
    'fiscal_currency_usd' => 'USD',                     // Moneda de referencia USD
    
    // Configuración de documentos
    'document_logo_path' => '',                         // Ruta logo documentos
    'document_footer_text' => 'Este documento cumple con las normativas fiscales del SENIAT',
    'document_signature_required' => true,              // Requerir firma digital
    
    // Configuración de reportes
    'auto_generate_reports' => true,                     // Generar reportes automáticamente
    'report_email_recipients' => array(),               // Destinatarios reportes
    'report_backup_enabled' => true,                     // Backup de reportes
    
    // Configuración de validación
    'rif_validation_enabled' => true,                   // Validación RIF habilitada
    'rif_validation_api' => '',                         // API validación RIF
    'cedula_validation_enabled' => true,               // Validación cédula habilitada
    'cedula_required' => false,                         // Cédula obligatoria (false = opcional)
    'cedula_field_enabled' => true,                    // Mostrar campo cédula en checkout
    'address_validation_enabled' => true,              // Validación direcciones
    
    // Configuración de notificaciones
    'notify_tax_changes' => true,                       // Notificar cambios de impuestos
    'notify_invoice_errors' => true,                    // Notificar errores facturación
    'notify_report_generation' => true,                 // Notificar generación reportes
);

// Configuración de exenciones fiscales
$exemptions_settings = array(
    'food_products' => array(
        'exempt_from' => 'iva',
        'reason' => 'Productos alimenticios básicos',
        'code' => 'EX-FOOD-001',
        'valid_from' => '2024-01-01',
        'valid_until' => null,
        'categories' => array('alimentos', 'comida')
    ),
    'books_education' => array(
        'exempt_from' => 'iva',
        'reason' => 'Libros y material educativo',
        'code' => 'EX-EDU-001',
        'valid_from' => '2024-01-01',
        'valid_until' => null,
        'categories' => array('libros', 'educacion')
    ),
    'medical_products' => array(
        'exempt_from' => 'iva',
        'reason' => 'Productos médicos y farmacéuticos',
        'code' => 'EX-MED-001',
        'valid_from' => '2024-01-01',
        'valid_until' => null,
        'categories' => array('medicina', 'farmacia')
    ),
    'small_transactions' => array(
        'exempt_from' => 'igtf',
        'reason' => 'Transacciones menores a $200 USD',
        'code' => 'EX-IGTF-001',
        'valid_from' => '2024-01-01',
        'valid_until' => null,
        'threshold' => 200.00
    ),
    'government_entities' => array(
        'exempt_from' => 'both',
        'reason' => 'Entidades gubernamentales',
        'code' => 'EX-GOV-001',
        'valid_from' => '2024-01-01',
        'valid_until' => null,
        'rif_patterns' => array('G-', 'P-')
    )
);
```

---

## **🔄 FLUJO DE FUNCIONAMIENTO**

### **🧾 Proceso de Facturación**
1. **Cliente completa** pedido
2. **Sistema calcula** impuestos automáticamente
3. **Valida datos** fiscales del cliente
4. **Genera factura** según formato SENIAT
5. **Asigna número** consecutivo
6. **Envía factura** por email
7. **Registra** en base de datos fiscal
8. **Actualiza** reportes automáticamente

### **📊 Generación de Reportes**
1. **Sistema recopila** datos del período
2. **Calcula totales** de ventas e impuestos
3. **Genera reporte** en formato SENIAT
4. **Valida** datos del reporte
5. **Exporta** en formato requerido
6. **Envía** por email al contador
7. **Archiva** para auditoría

---

## **🎨 INTEGRACIÓN CON WOOCOMMERCE**

### **🔌 Hooks de WooCommerce**
```php
// Cálculo automático de impuestos
add_action('woocommerce_cart_calculate_fees', array($this, 'calculate_venezuelan_taxes'));
add_action('woocommerce_checkout_process', array($this, 'validate_fiscal_data'));

// Generación de facturas
add_action('woocommerce_order_status_completed', array($this, 'generate_fiscal_invoice'));
add_action('woocommerce_new_order', array($this, 'prepare_fiscal_data'));

// Campos adicionales en checkout
add_filter('woocommerce_checkout_fields', array($this, 'add_fiscal_fields'));
add_action('woocommerce_checkout_process', array($this, 'validate_fiscal_fields'));
```

### **📄 Campos Fiscales Adicionales**
- **RIF** del cliente (obligatorio)
- **Dirección fiscal** completa
- **Tipo de contribuyente**
- **Exenciones** aplicables

---

## **🧪 TESTING**

### **🔍 Casos de Prueba**
- **Cálculo** de IVA en diferentes productos
- **Cálculo** de IGTF según monto
- **Validación** de RIF venezolano
- **Generación** de facturas
- **Exenciones** fiscales
- **Reportes** SENIAT

### **📊 Datos de Prueba**
- **RIFs** válidos e inválidos
- **Productos** con y sin exenciones
- **Montos** para cálculo de IGTF
- **Direcciones** fiscales completas

---

## **🚨 MANEJO DE ERRORES**

### **⚠️ Errores Comunes**
- **RIF inválido** → Validación mejorada
- **Error de cálculo** → Recalculación automática
- **Factura duplicada** → Verificación de numeración
- **Datos faltantes** → Validación obligatoria

### **📋 Logging**
- **Cálculos** de impuestos
- **Generación** de facturas
- **Validaciones** de RIF
- **Errores** de reportes

---

## **📈 MÉTRICAS DE ÉXITO**

### **🎯 KPIs del Módulo**
- **Precisión** de cálculos > 99.9%
- **Tiempo** de generación de facturas < 5 segundos
- **Cumplimiento** fiscal 100%
- **Satisfacción** del contador > 4.5/5

### **📊 Métricas Específicas**
- **Facturas** generadas por día
- **Impuestos** calculados correctamente
- **Reportes** SENIAT generados
- **Errores** de validación por día

---

## **🔗 DEPENDENCIAS**

### **📦 Módulos Requeridos**
- **Currency Converter** (para cálculos en USD)

### **📦 Módulos que Dependen de Este**
- **Reports Analytics** (para métricas fiscales)
- **Payment Gateways** (para validación fiscal)

### **🔌 Integraciones Externas**
- **SENIAT API** (si está disponible)
- **Email Service** (para envío de facturas)
- **PDF Generator** (para facturas)

---

## **📅 CRONOGRAMA DE DESARROLLO**

### **📅 Semana 1: Cálculo de Impuestos**
- **Día 1-2**: Estructura del módulo y calculadora de impuestos
- **Día 3-4**: Sistema de exenciones fiscales
- **Día 5**: Validación de RIF

### **📅 Semana 2: Facturación**
- **Día 1-2**: Generador de facturas
- **Día 3-4**: Templates de factura SENIAT
- **Día 5**: Sistema de numeración

### **📅 Semana 3: Reportes y Admin**
- **Día 1-2**: Reportes SENIAT
- **Día 3-4**: Panel de administración
- **Día 5**: Testing y optimización

---

## **🚀 PRÓXIMOS PASOS**

1. **Crear estructura** de carpetas del módulo
2. **Implementar** calculadora de impuestos
3. **Desarrollar** validador de RIF
4. **Crear** generador de facturas
5. **Implementar** sistema de exenciones
6. **Desarrollar** reportes SENIAT
7. **Testing** completo del módulo
8. **Documentación** y deployment

---

*Este módulo es crítico para el cumplimiento legal y debe ser preciso, confiable y estar siempre actualizado con las normativas fiscales venezolanas.*
