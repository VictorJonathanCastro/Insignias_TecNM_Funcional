#!/bin/bash
# Script para verificar la instalación de dependencias en el servidor

echo "=== Verificación de Instalación de Dependencias ==="
echo ""

# 1. Verificar vendor/autoload.php
echo "1. Verificando vendor/autoload.php..."
if [ -f "vendor/autoload.php" ]; then
    echo "   ✅ vendor/autoload.php existe"
else
    echo "   ❌ vendor/autoload.php NO existe"
fi
echo ""

# 2. Verificar PhpSpreadsheet
echo "2. Verificando PhpSpreadsheet..."
if [ -f "vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/IOFactory.php" ]; then
    echo "   ✅ PhpSpreadsheet está instalado"
else
    echo "   ❌ PhpSpreadsheet NO está instalado"
fi
echo ""

# 3. Probar carga de clases
echo "3. Probando carga de clases PHP..."
php -r "
require 'vendor/autoload.php';
if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
    echo '   ✅ PhpSpreadsheet se carga correctamente\n';
} else {
    echo '   ❌ Error: No se puede cargar PhpSpreadsheet\n';
}
" 2>&1

# 4. Verificar composer.lock
echo ""
echo "4. Verificando composer.lock..."
if [ -f "composer.lock" ]; then
    echo "   ✅ composer.lock existe"
    echo "   📋 Contenido del lock file:"
    grep -A 5 "phpoffice/phpspreadsheet" composer.lock | head -10
else
    echo "   ⚠️  composer.lock NO existe (esto es normal si es primera instalación)"
fi
echo ""

# 5. Verificar estructura de vendor
echo "5. Estructura del directorio vendor:"
if [ -d "vendor" ]; then
    echo "   Directorios en vendor/:"
    ls -la vendor/ | head -10
else
    echo "   ❌ El directorio vendor/ no existe"
fi

echo ""
echo "=== Fin de la verificación ==="

