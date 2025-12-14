# Comandos para Instalar Dependencias en el Servidor

## 📋 Opción 1: Si tienes Composer instalado globalmente

```bash
# Navegar al directorio del proyecto
cd /ruta/a/tu/proyecto/Insignias_TecNM_Funcional

# Instalar dependencias
composer install --no-interaction
```

## 📋 Opción 2: Si tienes composer.phar en el servidor

```bash
# Navegar al directorio del proyecto
cd /ruta/a/tu/proyecto/Insignias_TecNM_Funcional

# Instalar dependencias usando composer.phar
php composer.phar install --no-interaction
```

## 📋 Opción 3: Si NO tienes Composer instalado

### Paso 1: Descargar Composer
```bash
cd /ruta/a/tu/proyecto/Insignias_TecNM_Funcional

# Descargar Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

### Paso 2: Instalar dependencias
```bash
php composer.phar install --no-interaction
```

## 📋 Opción 4: Si tienes problemas con certificados SSL

```bash
# Configurar certificado CA (si tienes cacert.pem)
export SSL_CERT_FILE=/ruta/completa/a/cacert.pem
export CURL_CA_BUNDLE=/ruta/completa/a/cacert.pem

# Luego instalar
composer install --no-interaction
# O
php composer.phar install --no-interaction
```

## 📋 Verificación después de la instalación

```bash
# Verificar que vendor/autoload.php existe
ls -la vendor/autoload.php

# Verificar que PhpSpreadsheet está instalado
ls -la vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/IOFactory.php

# Probar que PHP puede cargar las clases
php -r "require 'vendor/autoload.php'; echo class_exists('PhpOffice\PhpSpreadsheet\IOFactory') ? 'OK' : 'ERROR';"
```

## ⚠️ Notas Importantes

1. **Permisos**: Asegúrate de tener permisos de escritura en el directorio del proyecto
2. **PHP Version**: Necesitas PHP >= 7.4
3. **Memoria**: Si tienes problemas de memoria, aumenta el límite:
   ```bash
   php -d memory_limit=512M composer.phar install
   ```

## 🔧 Solución de Problemas

### Error: "SSL certificate problem"
```bash
# Usar la configuración en composer.json (ya está configurado)
composer install --no-interaction
```

### Error: "Permission denied"
```bash
# Dar permisos al directorio
chmod -R 755 .
chown -R www-data:www-data vendor/
```

### Error: "Memory limit exceeded"
```bash
php -d memory_limit=512M composer.phar install --no-interaction
```

