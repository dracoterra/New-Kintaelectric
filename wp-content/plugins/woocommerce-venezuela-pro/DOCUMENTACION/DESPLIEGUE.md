# Guía de Despliegue - WooCommerce Venezuela Pro

## Estrategia de Despliegue

### Entornos de Despliegue

#### 1. Desarrollo Local
- **Propósito**: Desarrollo y testing inicial
- **Características**: Debug habilitado, logs detallados
- **Base de datos**: Local o de desarrollo

#### 2. Staging
- **Propósito**: Testing de integración y QA
- **Características**: Configuración similar a producción
- **Base de datos**: Copia de producción

#### 3. Producción
- **Propósito**: Ambiente final para usuarios
- **Características**: Optimizado, seguro, monitoreado
- **Base de datos**: Producción real

## Preparación para Despliegue

### Verificación Previa

#### Checklist de Pre-Despliegue
```bash
# Verificar versiones
php -v                    # PHP 7.4+
wp --version              # WP-CLI instalado
mysql --version           # MySQL 5.7+

# Verificar dependencias
wp plugin list            # WooCommerce activo
wp plugin list            # BCV Dólar Tracker activo

# Verificar configuraciones
wp option get wvp_version # Versión del plugin
wp option get wvp_settings # Configuraciones
```

#### Script de Verificación
```bash
#!/bin/bash
# scripts/pre-deployment-check.sh

echo "=== Verificación Pre-Despliegue ==="

# Verificar PHP
PHP_VERSION=$(php -r "echo PHP_VERSION;")
if [[ $(echo "$PHP_VERSION 7.4" | awk '{print ($1 >= $2)}') == 1 ]]; then
    echo "✅ PHP $PHP_VERSION - OK"
else
    echo "❌ PHP $PHP_VERSION - Requiere 7.4+"
    exit 1
fi

# Verificar WordPress
WP_VERSION=$(wp core version)
echo "✅ WordPress $WP_VERSION - OK"

# Verificar WooCommerce
if wp plugin is-active woocommerce; then
    WC_VERSION=$(wp plugin get woocommerce --field=version)
    echo "✅ WooCommerce $WC_VERSION - OK"
else
    echo "❌ WooCommerce no está activo"
    exit 1
fi

# Verificar BCV Dólar Tracker
if wp plugin is-active bcv-dolar-tracker; then
    echo "✅ BCV Dólar Tracker - OK"
else
    echo "❌ BCV Dólar Tracker no está activo"
    exit 1
fi

# Verificar permisos
if [ -w "wp-content/plugins/woocommerce-venezuela-pro" ]; then
    echo "✅ Permisos de escritura - OK"
else
    echo "❌ Sin permisos de escritura"
    exit 1
fi

echo "=== Verificación completada ==="
```

### Optimización para Producción

#### Configuración de Producción
```php
<?php
// wp-config.php - Configuraciones para producción

// Deshabilitar debug
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', false);

// Optimizaciones de rendimiento
define('WP_CACHE', true);
define('COMPRESS_CSS', true);
define('COMPRESS_SCRIPTS', true);
define('ENFORCE_GZIP', true);

// Configuraciones de seguridad
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);
define('FORCE_SSL_ADMIN', true);

// Configuraciones de memoria
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);
ini_set('max_input_vars', 3000);
```

#### Configuración del Plugin para Producción
```php
<?php
// wvp-production-config.php

// Configuraciones de rendimiento
define('WVP_CACHE_ENABLED', true);
define('WVP_CACHE_DURATION', 3600);
define('WVP_MINIFY_ASSETS', true);
define('WVP_LAZY_LOADING', true);

// Configuraciones de seguridad
define('WVP_RATE_LIMITING', true);
define('WVP_MAX_ATTEMPTS', 5);
define('WVP_LOCKOUT_DURATION', 300);
define('WVP_LOG_SECURITY_EVENTS', true);

// Configuraciones de logging
define('WVP_LOG_LEVEL', 'error');
define('WVP_LOG_RETENTION_DAYS', 7);
define('WVP_LOG_PERFORMANCE', false);

// Configuraciones de BCV
define('WVP_BCV_CACHE_DURATION', 1800);
define('WVP_BCV_FALLBACK_RATE', 36.50);
define('WVP_BCV_AUTO_UPDATE', true);
```

## Proceso de Despliegue

### Despliegue Manual

#### Paso 1: Backup
```bash
#!/bin/bash
# scripts/backup.sh

echo "=== Creando Backup ==="

# Backup de base de datos
wp db export backup-$(date +%Y%m%d-%H%M%S).sql

# Backup de archivos
tar -czf backup-files-$(date +%Y%m%d-%H%M%S).tar.gz wp-content/

# Backup de configuraciones
wp option get wvp_settings > wvp-settings-backup.json

echo "✅ Backup completado"
```

#### Paso 2: Despliegue
```bash
#!/bin/bash
# scripts/deploy.sh

echo "=== Iniciando Despliegue ==="

# Mantener modo de mantenimiento
wp maintenance-mode activate

# Actualizar plugin
wp plugin deactivate woocommerce-venezuela-pro
wp plugin install woocommerce-venezuela-pro.zip --force
wp plugin activate woocommerce-venezuela-pro

# Ejecutar migraciones
wp wvp migrate

# Limpiar caché
wp cache flush
wp wvp clear-cache

# Desactivar modo de mantenimiento
wp maintenance-mode deactivate

echo "✅ Despliegue completado"
```

#### Paso 3: Verificación Post-Despliegue
```bash
#!/bin/bash
# scripts/post-deployment-check.sh

echo "=== Verificación Post-Despliegue ==="

# Verificar que el plugin está activo
if wp plugin is-active woocommerce-venezuela-pro; then
    echo "✅ Plugin activo"
else
    echo "❌ Plugin no está activo"
    exit 1
fi

# Verificar versión
PLUGIN_VERSION=$(wp plugin get woocommerce-venezuela-pro --field=version)
echo "✅ Versión del plugin: $PLUGIN_VERSION"

# Verificar configuraciones
if wp option get wvp_settings > /dev/null 2>&1; then
    echo "✅ Configuraciones cargadas"
else
    echo "❌ Configuraciones no encontradas"
    exit 1
fi

# Verificar BCV
BCV_RATE=$(wp wvp get-bcv-rate)
if [ ! -z "$BCV_RATE" ]; then
    echo "✅ Tasa BCV: $BCV_RATE"
else
    echo "⚠️ Tasa BCV no disponible"
fi

# Verificar pasarelas de pago
PAYMENT_GATEWAYS=$(wp wvp get-payment-gateways)
echo "✅ Pasarelas de pago: $PAYMENT_GATEWAYS"

echo "=== Verificación completada ==="
```

### Despliegue Automatizado

#### GitHub Actions
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [ main ]
    tags: [ 'v*' ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '7.4'
        
    - name: Install WP-CLI
      run: |
        curl -O https://raw.githubusercontent.com/wp-cli/wp-cli/gh-pages/phar/wp-cli.phar
        chmod +x wp-cli.phar
        sudo mv wp-cli.phar /usr/local/bin/wp
        
    - name: Deploy to Staging
      run: |
        wp plugin install woocommerce-venezuela-pro.zip --force
        wp plugin activate woocommerce-venezuela-pro
        wp wvp migrate
        wp cache flush
      env:
        WP_CLI_CONFIG_PATH: staging-config.yml
        
    - name: Run Tests
      run: |
        wp wvp test
        wp wvp test-integration
        
    - name: Deploy to Production
      if: github.ref == 'refs/heads/main'
      run: |
        wp plugin install woocommerce-venezuela-pro.zip --force
        wp plugin activate woocommerce-venezuela-pro
        wp wvp migrate
        wp cache flush
      env:
        WP_CLI_CONFIG_PATH: production-config.yml
        
    - name: Notify Deployment
      if: always()
      run: |
        curl -X POST -H 'Content-type: application/json' \
        --data '{"text":"Deployment completed"}' \
        $SLACK_WEBHOOK_URL
```

#### Script de Despliegue Automatizado
```bash
#!/bin/bash
# scripts/auto-deploy.sh

set -e

ENVIRONMENT=${1:-staging}
VERSION=${2:-latest}

echo "=== Despliegue Automatizado a $ENVIRONMENT ==="

# Configurar entorno
case $ENVIRONMENT in
    staging)
        WP_CONFIG="staging-config.yml"
        SITE_URL="https://staging.example.com"
        ;;
    production)
        WP_CONFIG="production-config.yml"
        SITE_URL="https://example.com"
        ;;
    *)
        echo "❌ Entorno no válido: $ENVIRONMENT"
        exit 1
        ;;
esac

# Verificación pre-despliegue
./scripts/pre-deployment-check.sh

# Backup
./scripts/backup.sh

# Despliegue
wp maintenance-mode activate --config=$WP_CONFIG
wp plugin deactivate woocommerce-venezuela-pro --config=$WP_CONFIG
wp plugin install woocommerce-venezuela-pro-$VERSION.zip --force --config=$WP_CONFIG
wp plugin activate woocommerce-venezuela-pro --config=$WP_CONFIG

# Migraciones
wp wvp migrate --config=$WP_CONFIG

# Limpieza
wp cache flush --config=$WP_CONFIG
wp wvp clear-cache --config=$WP_CONFIG

# Verificación post-despliegue
./scripts/post-deployment-check.sh

# Desactivar mantenimiento
wp maintenance-mode deactivate --config=$WP_CONFIG

echo "✅ Despliegue a $ENVIRONMENT completado"
```

## Rollback y Recuperación

### Estrategia de Rollback

#### Rollback Automático
```bash
#!/bin/bash
# scripts/rollback.sh

VERSION=${1:-previous}

echo "=== Iniciando Rollback a versión $VERSION ==="

# Activar modo de mantenimiento
wp maintenance-mode activate

# Desactivar plugin actual
wp plugin deactivate woocommerce-venezuela-pro

# Restaurar versión anterior
if [ -f "woocommerce-venezuela-pro-$VERSION.zip" ]; then
    wp plugin install woocommerce-venezuela-pro-$VERSION.zip --force
    wp plugin activate woocommerce-venezuela-pro
else
    echo "❌ Archivo de versión $VERSION no encontrado"
    exit 1
fi

# Restaurar configuraciones si es necesario
if [ -f "wvp-settings-backup.json" ]; then
    wp option update wvp_settings "$(cat wvp-settings-backup.json)"
fi

# Limpiar caché
wp cache flush
wp wvp clear-cache

# Desactivar mantenimiento
wp maintenance-mode deactivate

echo "✅ Rollback completado"
```

#### Recuperación de Base de Datos
```bash
#!/bin/bash
# scripts/restore-database.sh

BACKUP_FILE=${1:-latest}

echo "=== Restaurando Base de Datos ==="

# Encontrar archivo de backup más reciente
if [ "$BACKUP_FILE" = "latest" ]; then
    BACKUP_FILE=$(ls -t backup-*.sql | head -n1)
fi

if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ Archivo de backup no encontrado: $BACKUP_FILE"
    exit 1
fi

# Activar modo de mantenimiento
wp maintenance-mode activate

# Restaurar base de datos
wp db import $BACKUP_FILE

# Verificar restauración
if wp db check; then
    echo "✅ Base de datos restaurada correctamente"
else
    echo "❌ Error en la restauración de la base de datos"
    exit 1
fi

# Desactivar mantenimiento
wp maintenance-mode deactivate

echo "✅ Restauración completada"
```

## Monitoreo Post-Despliegue

### Verificaciones Automáticas

#### Script de Monitoreo
```bash
#!/bin/bash
# scripts/monitor.sh

echo "=== Monitoreo Post-Despliegue ==="

# Verificar estado del sitio
SITE_STATUS=$(wp core check-update --format=count)
if [ "$SITE_STATUS" -eq 0 ]; then
    echo "✅ Sitio funcionando correctamente"
else
    echo "⚠️ Actualizaciones disponibles"
fi

# Verificar plugin
if wp plugin is-active woocommerce-venezuela-pro; then
    echo "✅ Plugin activo"
else
    echo "❌ Plugin no está activo"
    exit 1
fi

# Verificar BCV
BCV_RATE=$(wp wvp get-bcv-rate)
if [ ! -z "$BCV_RATE" ] && [ "$BCV_RATE" -gt 0 ]; then
    echo "✅ Tasa BCV: $BCV_RATE"
else
    echo "⚠️ Tasa BCV no disponible"
fi

# Verificar errores recientes
ERROR_COUNT=$(wp wvp get-error-count --hours=1)
if [ "$ERROR_COUNT" -eq 0 ]; then
    echo "✅ Sin errores recientes"
else
    echo "⚠️ $ERROR_COUNT errores en la última hora"
fi

# Verificar rendimiento
RESPONSE_TIME=$(wp wvp get-response-time)
if [ "$RESPONSE_TIME" -lt 2 ]; then
    echo "✅ Tiempo de respuesta: ${RESPONSE_TIME}s"
else
    echo "⚠️ Tiempo de respuesta lento: ${RESPONSE_TIME}s"
fi

echo "=== Monitoreo completado ==="
```

#### Alertas Automáticas
```bash
#!/bin/bash
# scripts/alert.sh

MESSAGE=$1
LEVEL=${2:-info}

case $LEVEL in
    error)
        COLOR="#ff0000"
        EMOJI="🚨"
        ;;
    warning)
        COLOR="#ffaa00"
        EMOJI="⚠️"
        ;;
    success)
        COLOR="#00ff00"
        EMOJI="✅"
        ;;
    *)
        COLOR="#0000ff"
        EMOJI="ℹ️"
        ;;
esac

# Enviar a Slack
curl -X POST -H 'Content-type: application/json' \
--data "{
    \"attachments\": [{
        \"color\": \"$COLOR\",
        \"text\": \"$EMOJI $MESSAGE\",
        \"timestamp\": $(date +%s)
    }]
}" \
$SLACK_WEBHOOK_URL

# Enviar email
echo "$MESSAGE" | mail -s "WVP Alert: $LEVEL" $ADMIN_EMAIL
```

## Configuración de Entornos

### Configuración de Staging
```yaml
# staging-config.yml
path: /var/www/staging
url: https://staging.example.com
user: staging_user
color: false
debug: false
```

### Configuración de Producción
```yaml
# production-config.yml
path: /var/www/production
url: https://example.com
user: production_user
color: false
debug: false
quiet: true
```

## Consideraciones de Seguridad

### Despliegue Seguro
```bash
#!/bin/bash
# scripts/secure-deploy.sh

echo "=== Despliegue Seguro ==="

# Verificar certificados SSL
if openssl s_client -connect example.com:443 -servername example.com < /dev/null 2>/dev/null | grep -q "Verify return code: 0"; then
    echo "✅ Certificado SSL válido"
else
    echo "❌ Certificado SSL inválido"
    exit 1
fi

# Verificar permisos de archivos
find wp-content/plugins/woocommerce-venezuela-pro -type f -exec chmod 644 {} \;
find wp-content/plugins/woocommerce-venezuela-pro -type d -exec chmod 755 {} \;

# Verificar que no hay archivos sensibles
if find wp-content/plugins/woocommerce-venezuela-pro -name "*.env" -o -name "*.key" -o -name "*.pem"; then
    echo "❌ Archivos sensibles encontrados"
    exit 1
fi

echo "✅ Despliegue seguro completado"
```

## Conclusión

La estrategia de despliegue del plugin WooCommerce Venezuela Pro incluye:

- ✅ **Proceso estructurado**: Desarrollo → Staging → Producción
- ✅ **Automatización**: Scripts y CI/CD
- ✅ **Verificaciones**: Pre y post despliegue
- ✅ **Rollback**: Recuperación rápida
- ✅ **Monitoreo**: Alertas y verificaciones
- ✅ **Seguridad**: Despliegue seguro y verificado

Esta estrategia asegura despliegues confiables y recuperación rápida en caso de problemas.
