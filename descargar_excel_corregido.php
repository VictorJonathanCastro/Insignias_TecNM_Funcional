<?php
/**
 * Script para descargar el archivo Excel corregido
 */

if (!isset($_POST['archivo']) || !isset($_POST['nombre'])) {
    die('Error: Parámetros faltantes');
}

$archivo_temp = base64_decode($_POST['archivo']);
$nombre_archivo = $_POST['nombre'];

if (!file_exists($archivo_temp)) {
    die('Error: El archivo temporal no existe');
}

// Configurar headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
header('Content-Length: ' . filesize($archivo_temp));
header('Cache-Control: max-age=0');

// Leer y enviar el archivo
readfile($archivo_temp);

// Eliminar el archivo temporal después de enviarlo
@unlink($archivo_temp);
exit;
?>

