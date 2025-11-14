#!/bin/bash
# Script para configurar sendmail para envío en tiempo real
# Ejecuta este script en el servidor con: sudo bash configurar_sendmail_tiempo_real.sh

echo "=========================================="
echo "  CONFIGURAR SENDMAIL PARA TIEMPO REAL"
echo "=========================================="
echo ""

# Verificar si sendmail está instalado
if ! command -v sendmail &> /dev/null; then
    echo "❌ Sendmail no está instalado"
    echo ""
    echo "Instalando sendmail..."
    sudo apt-get update
    sudo apt-get install -y sendmail sendmail-bin
    echo "✅ Sendmail instalado"
    echo ""
else
    echo "✅ Sendmail ya está instalado"
    echo ""
fi

# Verificar configuración actual
echo "📋 Configuración actual de sendmail:"
sendmail -d0.1 -bv root 2>&1 | grep -i "version\|daemon" | head -5
echo ""

# Crear backup de configuración
echo "💾 Creando backup de configuración..."
sudo cp /etc/mail/sendmail.mc /etc/mail/sendmail.mc.backup 2>/dev/null || echo "⚠️ No se pudo hacer backup (puede que no exista)"
echo ""

# Configurar sendmail para envío inmediato (sin cola)
echo "🔧 Configurando sendmail para envío inmediato..."
echo ""

# Opción 1: Configurar sendmail para usar relay SMTP directo
if [ -f "/etc/mail/sendmail.mc" ]; then
    echo "📝 Configurando relay SMTP en sendmail.mc..."
    
    # Verificar si ya tiene configuración de relay
    if ! grep -q "SMART_HOST" /etc/mail/sendmail.mc; then
        echo "   Agregando configuración de relay SMTP..."
        
        # Agregar configuración para usar SMTP de TecNM o Office 365
        cat >> /etc/mail/sendmail.mc << 'EOF'

# Configuración para envío inmediato vía SMTP
define(`SMART_HOST', `smtp.tecnm.mx')dnl
define(`RELAY_MAILER', `esmtp')dnl
define(`RELAY_MAILER_ARGS', `TCP $h 587')dnl
FEATURE(`access_db')dnl
FEATURE(`relay_based_on_MX')dnl
EOF
        
        echo "   ✅ Configuración agregada"
    else
        echo "   ℹ️ Ya tiene configuración de relay"
    fi
    
    # Recompilar configuración
    echo ""
    echo "🔨 Recompilando configuración de sendmail..."
    cd /etc/mail
    sudo make -C /etc/mail 2>&1 | tail -5
    
    echo ""
    echo "🔄 Reiniciando sendmail..."
    sudo systemctl restart sendmail || sudo service sendmail restart
    echo "✅ Sendmail reiniciado"
else
    echo "⚠️ Archivo /etc/mail/sendmail.mc no encontrado"
    echo "   Sendmail puede estar usando configuración por defecto"
fi

echo ""
echo "=========================================="
echo "  CONFIGURACIÓN ADICIONAL"
echo "=========================================="
echo ""

# Configurar para procesar cola inmediatamente
echo "📝 Configurando procesamiento inmediato de cola..."
echo ""

# Crear script para procesar cola
sudo tee /usr/local/bin/procesar_cola_correo.sh > /dev/null << 'EOF'
#!/bin/bash
# Procesar cola de correo inmediatamente
sendmail -q
EOF

sudo chmod +x /usr/local/bin/procesar_cola_correo.sh
echo "✅ Script de procesamiento creado"

# Configurar cron para procesar cola cada minuto (opcional, como respaldo)
echo ""
echo "📅 Configurando cron para procesar cola cada minuto (opcional)..."
(crontab -l 2>/dev/null | grep -v "procesar_cola_correo"; echo "* * * * * /usr/local/bin/procesar_cola_correo.sh >/dev/null 2>&1") | crontab -
echo "✅ Cron configurado"

echo ""
echo "=========================================="
echo "  VERIFICACIÓN"
echo "=========================================="
echo ""

# Verificar estado de sendmail
echo "📊 Estado de sendmail:"
sudo systemctl status sendmail --no-pager | head -10
echo ""

# Verificar cola
echo "📬 Cola de correo:"
mailq 2>/dev/null | head -5 || echo "   Cola vacía o mailq no disponible"
echo ""

# Probar envío
echo "🧪 Probando envío de correo..."
echo "test" | mail -s "Prueba sendmail tiempo real" root 2>&1 | head -3
echo ""

echo "=========================================="
echo "  ✅ CONFIGURACIÓN COMPLETADA"
echo "=========================================="
echo ""
echo "📝 Próximos pasos:"
echo "1. Prueba el correo: http://158.23.160.163/probar_correo_tiempo_real.php"
echo "2. Verifica que mail() nativo ahora muestre 'TIEMPO REAL'"
echo "3. Si aún hay retrasos, verifica la configuración de relay SMTP"
echo ""

