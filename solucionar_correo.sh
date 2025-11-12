#!/bin/bash
# Script para solucionar problemas de correo

echo "=== SOLUCIONANDO PROBLEMAS DE CORREO ==="
echo ""

# 1. Crear directorio logs con permisos correctos
echo "1. Creando directorio logs con permisos correctos..."
mkdir -p /var/www/html/logs
chmod 755 /var/www/html/logs
chown www-data:www-data /var/www/html/logs 2>/dev/null || chown apache:apache /var/www/html/logs 2>/dev/null || echo "No se pudo cambiar propietario, pero el directorio existe"
echo "✓ Directorio logs creado"
echo ""

# 2. Crear archivo correos_enviados.txt con permisos correctos
echo "2. Creando archivo correos_enviados.txt con permisos correctos..."
touch /var/www/html/logs/correos_enviados.txt
chmod 666 /var/www/html/logs/correos_enviados.txt
chown www-data:www-data /var/www/html/logs/correos_enviados.txt 2>/dev/null || chown apache:apache /var/www/html/logs/correos_enviados.txt 2>/dev/null || echo "No se pudo cambiar propietario, pero el archivo existe"
echo "✓ Archivo correos_enviados.txt creado"
echo ""

# 3. Verificar si sendmail está instalado
echo "3. Verificando sendmail..."
if command -v sendmail &> /dev/null; then
    echo "✓ sendmail está instalado"
else
    echo "⚠ sendmail NO está instalado"
    echo "  Para instalar sendmail, ejecuta:"
    echo "  sudo apt-get update && sudo apt-get install -y sendmail"
    echo "  O configura SMTP en funciones_correo_real.php"
fi
echo ""

# 4. Verificar permisos del directorio /tmp
echo "4. Verificando permisos de /tmp..."
if [ -w /tmp ]; then
    echo "✓ /tmp tiene permisos de escritura"
else
    echo "⚠ /tmp NO tiene permisos de escritura"
fi
echo ""

# 5. Verificar configuración PHP mail
echo "5. Verificando configuración PHP mail..."
php -r "echo 'sendmail_path: ' . ini_get('sendmail_path') . PHP_EOL;"
echo ""

echo "=== FIN DE VERIFICACIÓN ==="
echo ""
echo "NOTA: Si sendmail no está instalado, el sistema usará:"
echo "  1. PHPMailer con SMTP (requiere credenciales correctas)"
echo "  2. Simulación guardando en /var/www/html/logs/correos_enviados.txt"

