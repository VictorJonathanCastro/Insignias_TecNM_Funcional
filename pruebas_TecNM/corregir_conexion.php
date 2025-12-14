<?php
/**
 * Script para corregir conexion.php en el servidor
 * Ejecutar una vez: http://158.23.160.163/corregir_conexion.php
 */

$archivo = __DIR__ . '/conexion.php';
$backup = $archivo . '.backup.' . date('Ymd_His');

// Verificar que el archivo existe
if (!file_exists($archivo)) {
    die("❌ Error: El archivo conexion.php no existe en: $archivo");
}

// Crear backup
copy($archivo, $backup);
echo "✅ Backup creado: $backup<br>";

// Leer el contenido del archivo
$contenido = file_get_contents($archivo);

// Verificar si ya está corregido
if (strpos($contenido, "if (!function_exists('detectarEntorno'))") !== false) {
    echo "✅ El archivo ya está corregido. No se necesita modificar.<br>";
    exit;
}

// Patrón a buscar: función detectarEntorno sin protección
$patron = '/\/\/ Detectar el entorno \(XAMPP vs Ubuntu\)\s*function detectarEntorno\(\) \{/';

// Reemplazo: agregar verificación de función existente
$reemplazo = "// Detectar el entorno (XAMPP vs Ubuntu)\n// Evitar redeclaración si la función ya existe\nif (!function_exists('detectarEntorno')) {\n    function detectarEntorno() {";

// Reemplazar
$contenido_nuevo = preg_replace($patron, $reemplazo, $contenido, 1);

// Agregar el cierre de la verificación if después del return 'desconocido';
$patron_cierre = "/(\s+return 'desconocido';\s*\})/";
$reemplazo_cierre = "$1\n    }\n}";

$contenido_nuevo = preg_replace($patron_cierre, $reemplazo_cierre, $contenido_nuevo, 1);

// Si el reemplazo no funcionó, intentar método alternativo
if ($contenido_nuevo === $contenido) {
    // Método alternativo: buscar y reemplazar línea por línea
    $lineas = explode("\n", $contenido);
    $nuevas_lineas = [];
    $en_funcion = false;
    $funcion_encontrada = false;
    
    foreach ($lineas as $i => $linea) {
        // Buscar la declaración de la función
        if (preg_match('/^\s*function detectarEntorno\(\) \{/', $linea) && !$funcion_encontrada) {
            $funcion_encontrada = true;
            $nuevas_lineas[] = "// Detectar el entorno (XAMPP vs Ubuntu)";
            $nuevas_lineas[] = "// Evitar redeclaración si la función ya existe";
            $nuevas_lineas[] = "if (!function_exists('detectarEntorno')) {";
            $nuevas_lineas[] = "    function detectarEntorno() {";
            $en_funcion = true;
        } elseif ($en_funcion && preg_match('/^\s+return \'desconocido\';/', $linea)) {
            $nuevas_lineas[] = $linea;
            $nuevas_lineas[] = "    }";
            $nuevas_lineas[] = "}";
            $en_funcion = false;
        } else {
            $nuevas_lineas[] = $linea;
        }
    }
    
    $contenido_nuevo = implode("\n", $nuevas_lineas);
}

// Guardar el archivo corregido
if (file_put_contents($archivo, $contenido_nuevo)) {
    echo "✅ Archivo conexion.php corregido exitosamente<br>";
    echo "✅ Verificación: ";
    
    // Verificar que la corrección se aplicó
    $verificar = file_get_contents($archivo);
    if (strpos($verificar, "if (!function_exists('detectarEntorno'))") !== false) {
        echo "La corrección se aplicó correctamente<br>";
    } else {
        echo "⚠️ Advertencia: La corrección puede no haberse aplicado completamente<br>";
    }
    
    echo "<br>📝 <strong>Próximos pasos:</strong><br>";
    echo "1. Abre: <a href='diagnostico_servidor.php'>diagnostico_servidor.php</a> para verificar<br>";
    echo "2. Intenta iniciar sesión de nuevo<br>";
    echo "3. Si todo está bien, puedes eliminar este archivo: corregir_conexion.php<br>";
} else {
    echo "❌ Error: No se pudo escribir el archivo. Verifica permisos.<br>";
    echo "Ejecuta: sudo chmod 666 $archivo<br>";
}

?>

