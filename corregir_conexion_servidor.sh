#!/bin/bash
# Script para corregir conexion.php en el servidor
# Ejecutar en el servidor: sudo bash corregir_conexion_servidor.sh

echo "🔧 Corrigiendo conexion.php en el servidor..."

# Ruta del archivo
ARCHIVO="/var/www/html/conexion.php"

# Verificar que el archivo existe
if [ ! -f "$ARCHIVO" ]; then
    echo "❌ Error: El archivo $ARCHIVO no existe"
    exit 1
fi

# Crear backup
cp "$ARCHIVO" "$ARCHIVO.backup.$(date +%Y%m%d_%H%M%S)"
echo "✅ Backup creado"

# Corregir la función detectarEntorno para evitar redeclaración
sed -i 's/^function detectarEntorno() {/if (!function_exists('\''detectarEntorno'\'')) {\n    function detectarEntorno() {/' "$ARCHIVO"
sed -i '/^function detectarEntorno() {/,/^}$/ {
    /^}$/a\
}
}' "$ARCHIVO"

# Si sed no funcionó bien, usar un método más directo
# Buscar y reemplazar la función completa
cat > /tmp/conexion_fix.php << 'ENDFIX'
// Detectar el entorno (XAMPP vs Ubuntu)
// Evitar redeclaración si la función ya existe
if (!function_exists('detectarEntorno')) {
    function detectarEntorno() {
        // Verificar si estamos en XAMPP (Windows)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 'xampp';
        }
        // Verificar si estamos en Ubuntu/Linux
        if (file_exists('/etc/apache2/') || file_exists('/var/www/html/')) {
            return 'ubuntu';
        }
        return 'desconocido';
    }
}
ENDFIX

# Reemplazar la función en el archivo
php -r "
\$archivo = file_get_contents('$ARCHIVO');
\$patron = '/\/\/ Detectar el entorno.*?function detectarEntorno\(\) \{.*?\n    return \'desconocido\';\n    \}\n/s';
\$reemplazo = file_get_contents('/tmp/conexion_fix.php');
\$archivo = preg_replace(\$patron, \$reemplazo, \$archivo, 1);
file_put_contents('$ARCHIVO', \$archivo);
"

echo "✅ conexion.php corregido"
echo "📝 Verifica que el archivo esté correcto antes de continuar"

