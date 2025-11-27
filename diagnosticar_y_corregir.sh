#!/bin/bash
# Script para diagnosticar y corregir permisos de archivos de correo

echo "=========================================="
echo "  DIAGNÓSTICO Y CORRECCIÓN DE ARCHIVOS"
echo "=========================================="
echo ""

# Buscar archivos
echo "🔍 Buscando archivos..."
ARCHIVO_ENCONTRADO=$(find /var/www -name "funciones_correo_real.php" 2>/dev/null | head -1)

if [ -z "$ARCHIVO_ENCONTRADO" ]; then
    echo "❌ No se encontró funciones_correo_real.php"
    echo ""
    echo "Verificando ubicaciones comunes..."
    echo ""
    
    # Verificar ubicaciones comunes
    if [ -d "/var/www/html/Insignias_TecNM_Funcional" ]; then
        echo "📁 Directorio encontrado: /var/www/html/Insignias_TecNM_Funcional"
        ls -la /var/www/html/Insignias_TecNM_Funcional/*.php 2>/dev/null | grep -E "(funciones_correo_real|config_smtp|metadatos_formulario|ver_insignia_completa|probar_correo_tiempo_real|verificar_correos_enviados)" || echo "  No se encontraron los archivos aquí"
    fi
    
    if [ -d "/var/www/Insignias_TecNM_Funcional" ]; then
        echo "📁 Directorio encontrado: /var/www/Insignias_TecNM_Funcional"
        ls -la /var/www/Insignias_TecNM_Funcional/*.php 2>/dev/null | grep -E "(funciones_correo_real|config_smtp|metadatos_formulario|ver_insignia_completa|probar_correo_tiempo_real|verificar_correos_enviados)" || echo "  No se encontraron los archivos aquí"
    fi
    
    echo ""
    echo "⚠️  Los archivos no se encontraron. Necesitas subirlos primero."
    exit 1
fi

# Obtener directorio
DIRECTORIO=$(dirname "$ARCHIVO_ENCONTRADO")
echo "✅ Archivos encontrados en: $DIRECTORIO"
echo ""

# Cambiar al directorio
cd "$DIRECTORIO" || exit 1

# Listar archivos que vamos a modificar
echo "📋 Archivos a modificar:"
ARCHIVOS=(
    "funciones_correo_real.php"
    "config_smtp.php"
    "metadatos_formulario.php"
    "ver_insignia_completa.php"
    "probar_correo_tiempo_real.php"
    "verificar_correos_enviados.php"
)

for archivo in "${ARCHIVOS[@]}"; do
    if [ -f "$archivo" ]; then
        echo "  ✅ $archivo"
    else
        echo "  ❌ $archivo (NO ENCONTRADO)"
    fi
done

echo ""
read -p "¿Deseas continuar y ajustar permisos? (S/N): " respuesta

if [ "$respuesta" != "S" ] && [ "$respuesta" != "s" ]; then
    echo "Operación cancelada."
    exit 0
fi

echo ""
echo "🔧 Ajustando permisos..."

# Ajustar permisos solo de archivos que existen
for archivo in "${ARCHIVOS[@]}"; do
    if [ -f "$archivo" ]; then
        echo "  📝 Ajustando $archivo..."
        sudo chown www-data:www-data "$archivo" 2>/dev/null && echo "    ✅ Propietario cambiado" || echo "    ⚠️  Error al cambiar propietario"
        sudo chmod 644 "$archivo" 2>/dev/null && echo "    ✅ Permisos cambiados" || echo "    ⚠️  Error al cambiar permisos"
    fi
done

echo ""
echo "✅ Verificando permisos finales:"
for archivo in "${ARCHIVOS[@]}"; do
    if [ -f "$archivo" ]; then
        ls -la "$archivo"
    fi
done

echo ""
echo "=========================================="
echo "  PROCESO COMPLETADO"
echo "=========================================="
echo ""
echo "📝 Próximos pasos:"
echo "1. Prueba el correo: http://158.23.160.163/probar_correo_tiempo_real.php"
echo "2. Verifica que funcione correctamente"
echo ""

