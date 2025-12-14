<?php
/**
 * Script V2 para corregir conexion.php en el servidor
 * Este script corrige el error de sintaxis
 */

$archivo = __DIR__ . '/conexion.php';

// Verificar que el archivo existe
if (!file_exists($archivo)) {
    die("❌ Error: El archivo conexion.php no existe");
}

// Crear backup
$backup = $archivo . '.backup.' . date('Ymd_His');
copy($archivo, $backup);
echo "✅ Backup creado: " . basename($backup) . "<br><br>";

// Leer el contenido
$contenido = file_get_contents($archivo);

// Buscar y corregir el problema específico
// El problema es que hay llaves de más después de la función

// Método 1: Buscar el patrón problemático y reemplazarlo
$patron_problema = '/\/\/ Detectar el entorno.*?function detectarEntorno\(\) \{.*?return \'desconocido\';.*?\n\s*\}\s*\}\s*\n/s';

// Reemplazo correcto
$reemplazo_correcto = "// Detectar el entorno (XAMPP vs Ubuntu)
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

";

// Intentar reemplazo con regex
$contenido_corregido = preg_replace($patron_problema, $reemplazo_correcto, $contenido);

// Si no funcionó, usar método manual línea por línea
if ($contenido_corregido === $contenido || $contenido_corregido === null) {
    $lineas = explode("\n", $contenido);
    $nuevas_lineas = [];
    $en_funcion = false;
    $funcion_encontrada = false;
    $llaves_cerradas = 0;
    
    foreach ($lineas as $i => $linea) {
        $num_linea = $i + 1;
        
        // Detectar inicio de función
        if (preg_match('/^\s*function detectarEntorno\(\) \{/', $linea) && !$funcion_encontrada) {
            $funcion_encontrada = true;
            $en_funcion = true;
            $nuevas_lineas[] = "// Detectar el entorno (XAMPP vs Ubuntu)";
            $nuevas_lineas[] = "// Evitar redeclaración si la función ya existe";
            $nuevas_lineas[] = "if (!function_exists('detectarEntorno')) {";
            $nuevas_lineas[] = "    function detectarEntorno() {";
            continue;
        }
        
        // Si estamos en la función y encontramos return 'desconocido';
        if ($en_funcion && preg_match('/^\s+return \'desconocido\';/', $linea)) {
            $nuevas_lineas[] = $linea;
            $nuevas_lineas[] = "    }";
            $nuevas_lineas[] = "}";
            $en_funcion = false;
            continue;
        }
        
        // Si estamos en la función y encontramos una llave de cierre extra
        if ($en_funcion && preg_match('/^\s*\}\s*$/', $linea)) {
            // Contar cuántas llaves de cierre hay
            $llaves_cerradas++;
            // Si es la primera llave después de return, es la de la función
            if ($llaves_cerradas == 1) {
                $nuevas_lineas[] = "    }";
                $nuevas_lineas[] = "}";
                $en_funcion = false;
            }
            // Si hay más llaves, las ignoramos (son extras)
            continue;
        }
        
        // Agregar línea normal
        $nuevas_lineas[] = $linea;
    }
    
    $contenido_corregido = implode("\n", $nuevas_lineas);
}

// Verificar sintaxis antes de guardar
$temp_file = tempnam(sys_get_temp_dir(), 'conexion_check');
file_put_contents($temp_file, $contenido_corregido);

// Verificar sintaxis PHP
exec("php -l $temp_file 2>&1", $output, $return_code);
unlink($temp_file);

if ($return_code === 0) {
    // Sintaxis correcta, guardar
    if (file_put_contents($archivo, $contenido_corregido)) {
        echo "✅ <strong>Archivo conexion.php corregido exitosamente</strong><br>";
        echo "✅ Sintaxis PHP verificada correctamente<br><br>";
        
        // Mostrar las primeras líneas para verificar
        echo "<strong>Verificación (primeras 25 líneas):</strong><br>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
        $lineas_verificacion = explode("\n", $contenido_corregido);
        for ($i = 0; $i < min(25, count($lineas_verificacion)); $i++) {
            echo htmlspecialchars(($i + 1) . ": " . $lineas_verificacion[$i]) . "\n";
        }
        echo "</pre>";
        
        echo "<br>📝 <strong>Próximos pasos:</strong><br>";
        echo "1. Abre: <a href='diagnostico_servidor.php' target='_blank'>diagnostico_servidor.php</a> para verificar<br>";
        echo "2. Intenta iniciar sesión de nuevo<br>";
        echo "3. Si todo está bien, puedes eliminar este archivo: corregir_conexion_v2.php<br>";
    } else {
        echo "❌ Error: No se pudo escribir el archivo. Verifica permisos.<br>";
        echo "Ejecuta en PuTTY: sudo chmod 666 $archivo<br>";
    }
} else {
    echo "❌ Error de sintaxis detectado:<br>";
    echo "<pre style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo implode("\n", $output);
    echo "</pre>";
    echo "<br>⚠️ El archivo NO se modificó. El backup está en: " . basename($backup) . "<br>";
    echo "Puedes restaurar el backup si es necesario.<br>";
}

?>

