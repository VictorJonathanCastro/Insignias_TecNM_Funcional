#!/bin/bash
# Script para encontrar la ruta del proyecto en el servidor

echo "🔍 Buscando la ruta de tu proyecto..."
echo ""

# Buscar el archivo metadatos_formulario.php
RUTA=$(find /var/www /home -name "metadatos_formulario.php" 2>/dev/null | head -1)

if [ -z "$RUTA" ]; then
    echo "❌ No se encontró el archivo en /var/www o /home"
    echo "🔍 Buscando en todo el sistema (puede tardar)..."
    RUTA=$(find / -name "metadatos_formulario.php" 2>/dev/null | head -1)
fi

if [ -n "$RUTA" ]; then
    # Obtener el directorio del archivo
    DIRECTORIO=$(dirname "$RUTA")
    echo "✅ Proyecto encontrado en:"
    echo "📁 $DIRECTORIO"
    echo ""
    echo "📋 Para ir a esa carpeta, ejecuta:"
    echo "cd $DIRECTORIO"
    echo ""
    echo "📋 Para actualizar desde GitHub, ejecuta:"
    echo "cd $DIRECTORIO && git pull origin main"
else
    echo "❌ No se encontró el proyecto"
    echo ""
    echo "💡 Intenta buscar manualmente:"
    echo "find /var/www -type d -name '*Insignias*' 2>/dev/null"
    echo "find /home -type d -name '*Insignias*' 2>/dev/null"
fi

