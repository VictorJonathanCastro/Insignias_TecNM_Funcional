<?php
// Script de diagnóstico para carga_masiva_excel.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico de Carga Masiva</h1>";

// 1. Verificar sesión
echo "<h2>1. Verificación de Sesión</h2>";
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo "<p style='color: red;'>❌ No hay sesión activa</p>";
} else {
    echo "<p style='color: green;'>✅ Sesión activa - Usuario ID: " . $_SESSION['usuario_id'] . "</p>";
    echo "<p>Rol: " . ($_SESSION['rol'] ?? 'No definido') . "</p>";
}

// 2. Verificar conexión
echo "<h2>2. Verificación de Conexión</h2>";
require_once 'conexion.php';
if ($conexion->connect_errno) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $conexion->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
}

// 3. Verificar vendor/autoload.php
echo "<h2>3. Verificación de Dependencias</h2>";
if (!file_exists('vendor/autoload.php')) {
    echo "<p style='color: red;'>❌ vendor/autoload.php no existe</p>";
    echo "<p>Ejecuta: composer install</p>";
} else {
    echo "<p style='color: green;'>✅ vendor/autoload.php existe</p>";
    try {
        require_once 'vendor/autoload.php';
        echo "<p style='color: green;'>✅ PhpSpreadsheet cargado correctamente</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al cargar PhpSpreadsheet: " . $e->getMessage() . "</p>";
    }
}

// 4. Verificar firma_digital_real.php
echo "<h2>4. Verificación de Firma Digital</h2>";
if (!file_exists('firma_digital_real.php')) {
    echo "<p style='color: orange;'>⚠️ firma_digital_real.php no existe (puede ser opcional)</p>";
} else {
    echo "<p style='color: green;'>✅ firma_digital_real.php existe</p>";
}

// 5. Verificar permisos de escritura
echo "<h2>5. Verificación de Permisos</h2>";
$tempDir = sys_get_temp_dir();
if (is_writable($tempDir)) {
    echo "<p style='color: green;'>✅ Directorio temporal escribible: $tempDir</p>";
} else {
    echo "<p style='color: red;'>❌ Directorio temporal NO escribible: $tempDir</p>";
}

// 6. Verificar tamaño de archivo PHP
echo "<h2>6. Información del Archivo</h2>";
$archivo = 'carga_masiva_excel.php';
if (file_exists($archivo)) {
    $tamanio = filesize($archivo);
    echo "<p>Tamaño del archivo: " . number_format($tamanio / 1024, 2) . " KB</p>";
    echo "<p>Última modificación: " . date('Y-m-d H:i:s', filemtime($archivo)) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Archivo no encontrado</p>";
}

// 7. Verificar límites de PHP
echo "<h2>7. Límites de PHP</h2>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>max_execution_time: " . ini_get('max_execution_time') . " segundos</p>";
echo "<p>memory_limit: " . ini_get('memory_limit') . "</p>";

// 8. Probar crear instancia de la clase
echo "<h2>8. Prueba de Clase</h2>";
try {
    require_once 'vendor/autoload.php';
    require_once 'carga_masiva_excel.php';
    // No podemos instanciar sin procesar, pero podemos verificar que la clase existe
    if (class_exists('CargaMasivaExcel')) {
        echo "<p style='color: green;'>✅ Clase CargaMasivaExcel existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Clase CargaMasivaExcel NO existe</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='carga_masiva_excel.php'>Intentar acceder a carga_masiva_excel.php</a></p>";
?>

