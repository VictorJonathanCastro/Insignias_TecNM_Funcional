#!/bin/bash
# Script para configurar el dominio insigniasdigitales.tecnm.mx en Apache

echo "=== Configuración de Dominio insigniasdigitales.tecnm.mx ==="

# Crear archivo de configuración del VirtualHost
sudo tee /etc/apache2/sites-available/insigniasdigitales.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName insigniasdigitales.tecnm.mx
    ServerAlias www.insigniasdigitales.tecnm.mx
    ServerAdmin webmaster@tecnm.mx
    
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Configurar index.php como archivo por defecto
    DirectoryIndex index.php index.html
    
    # Logs
    ErrorLog \${APACHE_LOG_DIR}/insigniasdigitales_error.log
    CustomLog \${APACHE_LOG_DIR}/insigniasdigitales_access.log combined
    
    # Configuración adicional
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteBase /
    </IfModule>
</VirtualHost>
EOF

echo "✅ Archivo de configuración creado"

# Habilitar el sitio
sudo a2ensite insigniasdigitales.conf

# Habilitar mod_rewrite si no está habilitado
sudo a2enmod rewrite

# Verificar configuración
echo ""
echo "Verificando configuración de Apache..."
sudo apache2ctl configtest

echo ""
echo "=== Configuración completada ==="
echo ""
echo "Para aplicar los cambios, ejecuta:"
echo "sudo systemctl reload apache2"
echo ""
echo "NOTA: Asegúrate de que el DNS apunte a esta IP:"
echo "insigniasdigitales.tecnm.mx -> $(curl -s ifconfig.me 2>/dev/null || echo 'TU_IP_PUBLICA')"

