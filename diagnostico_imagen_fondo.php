<?php
// Diagnóstico de la imagen de fondo
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Diagnóstico de Imagen de Fondo</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }";
echo ".container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 1200px; margin: 0 auto; }";
echo ".success { background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745; margin: 10px 0; }";
echo ".error { background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545; margin: 10px 0; }";
echo ".warning { background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 10px 0; }";
echo ".info { background: #e3f2fd; padding: 15px; border-radius: 5px; border-left: 4px solid #2196f3; margin: 10px 0; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo ".test-box { width: 400px; height: 300px; border: 2px solid #ddd; margin: 20px 0; position: relative; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔍 Diagnóstico de Imagen de Fondo</h1>";

// Verificar si el archivo existe
$imagen_path = 'imagen/Hoja_membrentada.png';
echo "<h2>1. Verificación del Archivo</h2>";

if (file_exists($imagen_path)) {
    echo "<div class='success'>";
    echo "<h3>✅ Archivo Encontrado</h3>";
    echo "<p><strong>Ruta:</strong> $imagen_path</p>";
    echo "<p><strong>Tamaño:</strong> " . filesize($imagen_path) . " bytes</p>";
    echo "<p><strong>Fecha de modificación:</strong> " . date('Y-m-d H:i:s', filemtime($imagen_path)) . "</p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>❌ Archivo No Encontrado</h3>";
    echo "<p><strong>Ruta buscada:</strong> $imagen_path</p>";
    echo "<p>El archivo de imagen no existe en la ruta especificada.</p>";
    echo "</div>";
}

// Verificar directorio de imágenes
echo "<h2>2. Contenido del Directorio de Imágenes</h2>";
$imagen_dir = 'imagen/';

if (is_dir($imagen_dir)) {
    echo "<div class='info'>";
    echo "<h3>📁 Archivos en el directorio 'imagen/':</h3>";
    $archivos = scandir($imagen_dir);
    echo "<ul>";
    foreach ($archivos as $archivo) {
        if ($archivo != '.' && $archivo != '..') {
            $ruta_completa = $imagen_dir . $archivo;
            if (is_file($ruta_completa)) {
                $extension = pathinfo($archivo, PATHINFO_EXTENSION);
                if (in_array(strtolower($extension), ['png', 'jpg', 'jpeg', 'gif'])) {
                    echo "<li>🖼️ <strong>$archivo</strong> (" . filesize($ruta_completa) . " bytes)</li>";
                } else {
                    echo "<li>📄 $archivo</li>";
                }
            } elseif (is_dir($ruta_completa)) {
                echo "<li>📁 <strong>$archivo/</strong> (directorio)</li>";
            }
        }
    }
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>❌ Directorio No Encontrado</h3>";
    echo "<p>El directorio 'imagen/' no existe.</p>";
    echo "</div>";
}

// Prueba de carga de imagen
echo "<h2>3. Prueba de Carga de Imagen</h2>";
if (file_exists($imagen_path)) {
    echo "<div class='success'>";
    echo "<h3>✅ Prueba de Imagen</h3>";
    echo "<p>La imagen debería aparecer a continuación:</p>";
    echo "<div class='test-box' style='background-image: url($imagen_path); background-size: contain; background-position: center; background-repeat: no-repeat;'>";
    echo "<div style='position: absolute; bottom: 10px; left: 10px; background: rgba(255,255,255,0.8); padding: 5px; border-radius: 3px; font-size: 12px;'>";
    echo "Si ves la imagen de fondo aquí, el problema está en el CSS";
    echo "</div>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h3>⚠️ No se puede probar la imagen</h3>";
    echo "<p>El archivo no existe, por lo que no se puede probar la carga.</p>";
    echo "</div>";
}

// Verificar permisos
echo "<h2>4. Verificación de Permisos</h2>";
if (file_exists($imagen_path)) {
    $permisos = fileperms($imagen_path);
    $permisos_octal = substr(sprintf('%o', $permisos), -4);
    
    echo "<div class='info'>";
    echo "<h3>🔐 Permisos del Archivo</h3>";
    echo "<p><strong>Permisos octales:</strong> $permisos_octal</p>";
    echo "<p><strong>Lectura:</strong> " . (is_readable($imagen_path) ? "✅ Sí" : "❌ No") . "</p>";
    echo "</div>";
}

// Soluciones
echo "<h2>5. Soluciones</h2>";
echo "<div class='info'>";
echo "<h3>🛠️ Posibles Soluciones:</h3>";
echo "<ol>";
echo "<li><strong>Si el archivo no existe:</strong> Sube la imagen 'Hoja_membrentada.png' al directorio 'imagen/'</li>";
echo "<li><strong>Si el archivo existe pero no se ve:</strong> Verifica los permisos del archivo</li>";
echo "<li><strong>Si el CSS está mal:</strong> Revisa la configuración de background-image</li>";
echo "<li><strong>Si es problema de caché:</strong> Refresca la página con Ctrl+F5</li>";
echo "</ol>";
echo "</div>";

// Botones de acción
echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='validacion.php?insignia=TECNM-ITSM-2025-ART-336' target='_blank' class='btn'>🔗 Ver Certificado</a>";
echo "<a href='prueba_imagen_fondo.php' class='btn'>🧪 Prueba de Imagen</a>";
echo "<button onclick='window.location.reload()' class='btn'>🔄 Recargar Diagnóstico</button>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
