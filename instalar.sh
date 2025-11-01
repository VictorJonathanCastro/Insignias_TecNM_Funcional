#!/bin/bash
# ========================================
# SCRIPT DE INSTALACIÓN - SISTEMA INSIGNIAS TECNM
# ========================================

echo "🚀 Instalando Sistema de Insignias TecNM..."
echo "=============================================="

# Verificar si PHP está instalado
if ! command -v php &> /dev/null; then
    echo "❌ PHP no está instalado. Por favor instale PHP 7.4 o superior."
    exit 1
fi

echo "✅ PHP encontrado: $(php -v | head -n 1)"

# Verificar si Composer está instalado
if ! command -v composer &> /dev/null; then
    echo "📦 Instalando Composer..."
    
    # Instalar Composer
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
    
    if [ $? -eq 0 ]; then
        echo "✅ Composer instalado correctamente"
    else
        echo "❌ Error al instalar Composer"
        exit 1
    fi
else
    echo "✅ Composer encontrado: $(composer --version)"
fi

# Instalar dependencias de PHP
echo "📚 Instalando dependencias de PHP..."
composer install --no-dev --optimize-autoloader

if [ $? -eq 0 ]; then
    echo "✅ Dependencias instaladas correctamente"
else
    echo "❌ Error al instalar dependencias"
    exit 1
fi

# Crear directorio de uploads si no existe
if [ ! -d "uploads" ]; then
    mkdir uploads
    chmod 755 uploads
    echo "✅ Directorio de uploads creado"
fi

# Crear directorio de logs si no existe
if [ ! -d "logs" ]; then
    mkdir logs
    chmod 755 logs
    echo "✅ Directorio de logs creado"
fi

# Verificar permisos
echo "🔐 Configurando permisos..."
chmod 755 .
chmod 644 *.php
chmod 755 uploads/
chmod 755 logs/

echo ""
echo "🎉 ¡Instalación completada exitosamente!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Configure la base de datos en conexion.php"
echo "2. Ejecute el script SQL: BD/estructura_completa_con_metadatos.sql"
echo "3. Acceda al sistema desde su navegador"
echo "4. Use el módulo de carga masiva para importar datos"
echo ""
echo "🌐 URL del sistema: http://localhost/Insignias_TecNM_Funcional/"
echo "📊 Carga masiva: http://localhost/Insignias_TecNM_Funcional/carga_masiva_excel.php"
echo ""
echo "¡Excelente tarde equipo! 🎓"
