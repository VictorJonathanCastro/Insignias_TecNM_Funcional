<?php
// Script de diagnóstico para carga_masiva_excel.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Diagnóstico de carga_masiva_excel.php</h1>";
echo "<pre>";

// 1. Verificar PHP
echo "1. Versión de PHP: " . phpversion() . "\n\n";

// 2. Verificar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "2. Sesión iniciada: " . (session_status() === PHP_SESSION_ACTIVE ? "Sí" : "No") . "\n";
echo "   Usuario ID: " . (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : "No definido") . "\n";
echo "   Rol: " . (isset($_SESSION['rol']) ? $_SESSION['rol'] : "No definido") . "\n\n";

// 3. Verificar conexión
echo "3. Verificando conexión.php...\n";
try {
    require_once 'conexion.php';
    if (isset($conexion)) {
        echo "   ✓ Conexión definida\n";
        if ($conexion && !$conexion->connect_errno) {
            echo "   ✓ Conexión a BD exitosa\n";
        } else {
            echo "   ✗ Error de conexión: " . ($conexion->connect_error ?? "Desconocido") . "\n";
        }
    } else {
        echo "   ✗ Variable \$conexion no definida\n";
    }
} catch (Exception $e) {
    echo "   ✗ Excepción: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "   ✗ Error fatal: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Verificar vendor/autoload.php
echo "4. Verificando vendor/autoload.php...\n";
$vendor_path = __DIR__ . '/vendor/autoload.php';
if (file_exists($vendor_path)) {
    echo "   ✓ Archivo existe\n";
    try {
        require_once $vendor_path;
        echo "   ✓ Autoload cargado correctamente\n";
    } catch (Throwable $e) {
        echo "   ✗ Error al cargar: " . $e->getMessage() . "\n";
        echo "   Línea: " . $e->getLine() . "\n";
        echo "   Archivo: " . $e->getFile() . "\n";
    }
} else {
    echo "   ✗ Archivo no existe en: $vendor_path\n";
}
echo "\n";

// 5. Verificar permisos de archivos
echo "5. Verificando permisos...\n";
echo "   carga_masiva_excel.php: " . (is_readable('carga_masiva_excel.php') ? "Leyendo" : "No leyendo") . "\n";
echo "   conexion.php: " . (is_readable('conexion.php') ? "Leyendo" : "No leyendo") . "\n";
echo "   vendor/autoload.php: " . (is_readable($vendor_path) ? "Leyendo" : "No leyendo") . "\n";
echo "\n";

// 6. Verificar sintaxis de carga_masiva_excel.php
echo "6. Verificando sintaxis de carga_masiva_excel.php...\n";
$output = [];
$return_var = 0;
exec("php -l carga_masiva_excel.php 2>&1", $output, $return_var);
if ($return_var === 0) {
    echo "   ✓ Sintaxis correcta\n";
} else {
    echo "   ✗ Error de sintaxis:\n";
    foreach ($output as $line) {
        echo "   " . $line . "\n";
    }
}
echo "\n";

// 7. Intentar cargar carga_masiva_excel.php
echo "7. Intentando cargar carga_masiva_excel.php...\n";
ob_start();
try {
    // Solo incluir las primeras líneas para ver dónde falla
    $content = file_get_contents('carga_masiva_excel.php');
    $lines = explode("\n", $content);
    $first_lines = implode("\n", array_slice($lines, 0, 50));
    
    // Evaluar solo las primeras líneas
    eval('?>' . $first_lines);
    echo "   ✓ Primeras 50 líneas ejecutadas sin error\n";
} catch (Throwable $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}
$output = ob_get_clean();
if ($output) {
    echo "   Salida: " . htmlspecialchars($output) . "\n";
}

echo "\n";
echo "=== Fin del diagnóstico ===\n";
echo "</pre>";

