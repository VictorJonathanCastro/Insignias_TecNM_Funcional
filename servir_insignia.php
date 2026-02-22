<?php
/**
 * Sirve imágenes de la carpeta imagen/Insignias por nombre de archivo.
 * Así el carrusel siempre carga desde la ruta física correcta del proyecto.
 */
$archivo = isset($_GET['f']) ? basename($_GET['f']) : '';
if ($archivo === '' || preg_match('/[^a-zA-Z0-9_.-]/', $archivo)) {
    header('HTTP/1.0 400 Bad Request');
    exit;
}
// Asegurar que termina en .png
if (strtolower(substr($archivo, -4)) !== '.png') {
    $archivo .= '.png';
}
$ruta = __DIR__ . DIRECTORY_SEPARATOR . 'imagen' . DIRECTORY_SEPARATOR . 'Insignias' . DIRECTORY_SEPARATOR . $archivo;
if (!is_file($ruta) || !is_readable($ruta)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');
readfile($ruta);
exit;
