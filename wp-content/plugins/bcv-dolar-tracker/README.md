# BCV Dólar Tracker

Plugin de WordPress para rastrear y almacenar el precio del dólar desde el Banco Central de Venezuela (BCV) mediante tareas cron configurables.

## Descripción

Este plugin permite:
- Crear y mantener una tabla de base de datos para almacenar precios del dólar
- Configurar tareas cron personalizables para obtener precios automáticamente
- Scraping automático de la página del BCV
- Interfaz de administración para gestionar configuraciones y visualizar datos
- Sistema de migración para futuras expansiones

## Estructura del Plugin

```
bcv-dolar-tracker/
├── bcv-dolar-tracker.php          # Archivo principal del plugin
├── README.md                      # Documentación del plugin
├── includes/                      # Clases de funcionalidad principal
│   ├── class-bcv-database.php    # Gestión de base de datos
│   ├── class-bcv-cron.php        # Gestión de tareas cron
│   └── class-bcv-scraper.php     # Scraping de precios
├── admin/                        # Funcionalidades de administración
│   └── class-bcv-admin.php      # Clase principal de administración
└── assets/                       # Recursos estáticos
    ├── css/                      # Hojas de estilo
    └── js/                       # Scripts JavaScript
```

## Características

### Fase 1: Estructura Base ✅
- [x] Estructura de directorios organizada
- [x] Archivo principal con metadatos
- [x] Clases base con estructura preparada
- [x] Hooks de activación/desactivación
- [x] Prevención de acceso directo
- [x] Constantes del plugin definidas

### Fase 2: Base de Datos ✅
- [x] Creación de tabla `precio_dolar` con `wpdb`
- [x] Sistema de migración con versionado
- [x] Hook de activación para crear tabla
- [x] Campos: `datatime`, `precio` (DECIMAL(10,4))
- [x] Índices para optimización de consultas
- [x] Sistema de limpieza de registros antiguos
- [x] Funciones de consulta con paginación y filtros
- [x] Archivo de prueba para verificación

**Archivos implementados:**
- [class-bcv-database.php](mdc:wp-content/plugins/bcv-dolar-tracker/includes/class-bcv-database.php) - Clase completa de base de datos
- [test-database.php](mdc:wp-content/plugins/bcv-dolar-tracker/includes/test-database.php) - Archivo de prueba para verificación

**Características implementadas:**
- Tabla `wp_bcv_precio_dolar` con estructura optimizada
- Sistema de migración automático con versionado
- Funciones CRUD completas para gestión de precios
- Consultas optimizadas con índices y paginación
- Sistema de limpieza automática de registros antiguos
- Archivo de prueba para verificar funcionalidad

### Fase 3: Sistema Cron ✅
- [x] Tarea cron configurable con intervalos personalizables
- [x] Función de scraping del BCV implementada
- [x] Inserción automática de precios en la base de datos
- [x] Sistema de caché para evitar requests repetidos
- [x] Manejo de errores y reintentos automáticos
- [x] Estadísticas de ejecución y rendimiento
- [x] Archivo de prueba para verificación

**Archivos implementados:**
- [class-bcv-cron.php](mdc:wp-content/plugins/bcv-dolar-tracker/includes/class-bcv-cron.php) - Clase completa de gestión de cron
- [class-bcv-scraper.php](mdc:wp-content/plugins/bcv-dolar-tracker/includes/class-bcv-scraper.php) - Clase completa de scraping del BCV
- [test-cron.php](mdc:wp-content/plugins/bcv-dolar-tracker/includes/test-cron.php) - Archivo de prueba para verificación

**Características implementadas:**
- Cron configurable con intervalos en horas, minutos y segundos
- Scraping automático de la página del BCV con selectores robustos
- Sistema de caché de 12 horas para optimizar requests
- Manejo de errores con reintentos automáticos y backoff exponencial
- Inserción automática de precios en la base de datos
- Estadísticas completas de ejecución y rendimiento
- Funciones de prueba manual para debugging

### Fase 4: Panel de Administración ✅
- [x] Interfaz de configuración del cron
- [x] Tabla de datos con WP_List_Table
- [x] Filtros, búsqueda y ordenamiento
- [x] Seguridad con nonces y validación
- [x] Sistema de estadísticas completo
- [x] Interfaz responsive y moderna
- [x] Funcionalidades AJAX para mejor UX

**Archivos implementados:**
- [class-bcv-admin.php](mdc:wp-content/plugins/bcv-dolar-tracker/admin/class-bcv-admin.php) - Clase principal de administración
- [class-bcv-prices-table.php](mdc:wp-content/plugins/bcv-dolar-tracker/admin/class-bcv-prices-table.php) - Tabla de precios con WP_List_Table
- [admin.css](mdc:wp-content/plugins/bcv-dolar-tracker/assets/css/admin.css) - Estilos de la interfaz
- [admin.js](mdc:wp-content/plugins/bcv-dolar-tracker/assets/js/admin.js) - Funcionalidad JavaScript

**Características implementadas:**
- Menú principal con submenús para configuración, datos y estadísticas
- Formulario de configuración del cron con validación
- Tabla de precios con paginación, filtros y ordenamiento
- Sistema de estadísticas en tiempo real
- Interfaz moderna con CSS responsive
- Funcionalidades AJAX para mejor experiencia de usuario
- Seguridad completa con nonces y validación de permisos

## Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior
- Acceso a internet para scraping del BCV

## Instalación

1. Subir la carpeta `bcv-dolar-tracker` al directorio `/wp-content/plugins/`
2. Activar el plugin desde el panel de administración de WordPress
3. Configurar las opciones del cron en la nueva página de administración

## Uso

### Activación
El plugin se activa automáticamente y crea la estructura necesaria en la base de datos.

### Configuración
- Navegar a la nueva página de administración
- Configurar intervalos del cron
- Probar funcionalidad de scraping

### Monitoreo
- Ver datos recolectados en la tabla de administración
- Revisar logs para debugging

## Desarrollo

### Estructura de Clases

- **BCV_Dolar_Tracker**: Clase principal del plugin
- **BCV_Database**: Gestión de base de datos y migraciones
- **BCV_Cron**: Configuración y gestión de tareas cron
- **BCV_Scraper**: Obtención de precios desde BCV
- **BCV_Admin**: Interfaz de administración

### Hooks Principales

- `plugins_loaded`: Inicialización del plugin
- `admin_menu`: Menú de administración
- `admin_enqueue_scripts`: Assets de administración
- `wp_ajax_*`: Endpoints AJAX

## Seguridad

- Prevención de acceso directo a archivos
- Nonces para protección CSRF
- Validación y sanitización de datos
- Hooks de WordPress para integración segura

## Licencia

GPL v2 o posterior

## Autor

Kinta Electric - https://kinta-electric.com

## Versión

1.0.0

## Estado de Desarrollo

**Fase 1 Completada**: Estructura base del plugin implementada y lista para desarrollo de funcionalidades específicas.
