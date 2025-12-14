<?php
require_once 'conexion.php';

echo "<h2>🧪 Probar imagen_compartible.php</h2>";

$codigo_buscar = 'TecNM-ITSM-20251-116';
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional';

// Verificar si existe la insignia
$result = $conexion->query("SELECT * FROM insigniasotorgadas WHERE clave_insignia = '$codigo_buscar'");
if ($result && $result->num_rows > 0) {
    echo "<p>✅ La insignia <strong>$codigo_buscar</strong> existe</p>";
    
    // Verificar si la imagen existe
    $image_path = 'imagen/insignia_Responsabilidad Social.png';
    if (file_exists($image_path)) {
        echo "<p>✅ La imagen existe en: $image_path</p>";
        
        // Obtener información de la imagen
        $image_info = getimagesize($image_path);
        if ($image_info) {
            echo "<p><strong>Dimensiones:</strong> {$image_info[0]} x {$image_info[1]} píxeles</p>";
            echo "<p><strong>Tipo:</strong> {$image_info['mime']}</p>";
            echo "<p><strong>Tamaño del archivo:</strong> " . number_format(filesize($image_path) / 1024, 2) . " KB</p>";
        }
        
        // Mostrar la imagen
        echo "<h3>🖼️ Vista previa de la imagen:</h3>";
        echo "<img src='$image_path' style='max-width: 300px; border: 1px solid #ddd; margin: 10px 0;' alt='Insignia TecNM'>";
        
        // Generar URLs
        $share_url = $base_url . '/imagen_compartible.php?codigo=' . urlencode($codigo_buscar);
        $validation_url = $base_url . '/validacion.php?insignia=' . urlencode($codigo_buscar);
        $image_url = $base_url . '/imagen/insignia_Responsabilidad Social.png';
        
        echo "<h3>🔗 Enlaces de prueba:</h3>";
        echo "<ul>";
        echo "<li><a href='$share_url' target='_blank'>imagen_compartible.php</a></li>";
        echo "<li><a href='$validation_url' target='_blank'>Validación directa</a></li>";
        echo "</ul>";
        
        echo "<h3>📱 Compartir en Facebook:</h3>";
        echo "<p>1. Copia este enlace:</p>";
        echo "<input type='text' value='$share_url' style='width: 100%; padding: 10px; margin: 10px 0;' readonly>";
        
        echo "<p>2. Ve a Facebook y pega el enlace en tu publicación</p>";
        echo "<p>3. Facebook debería mostrar la imagen de la insignia</p>";
        echo "<p>4. Al hacer clic en la imagen, llevará al certificado completo</p>";
        
        echo "<h3>🧪 Herramientas de depuración:</h3>";
        echo "<ul>";
        echo "<li><a href='https://developers.facebook.com/tools/debug/' target='_blank'>Facebook Debugger</a> - Para verificar meta tags</li>";
        echo "<li><a href='https://www.opengraph.xyz/' target='_blank'>OpenGraph.xyz</a> - Verificador general de meta tags</li>";
        echo "</ul>";
        
        echo "<h3>📋 Meta tags generados:</h3>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;'>";
        echo "&lt;meta property=\"og:title\" content=\"Insignia TecNM - Responsabilidad Social\"&gt;<br>";
        echo "&lt;meta property=\"og:description\" content=\"Insignia de Responsabilidad Social otorgada a Victor Jonathan Castro Secundino\"&gt;<br>";
        echo "&lt;meta property=\"og:image\" content=\"$image_url\"&gt;<br>";
        echo "&lt;meta property=\"og:image:secure_url\" content=\"$image_url\"&gt;<br>";
        echo "&lt;meta property=\"og:image:type\" content=\"image/png\"&gt;<br>";
        echo "&lt;meta property=\"og:image:width\" content=\"1200\"&gt;<br>";
        echo "&lt;meta property=\"og:image:height\" content=\"630\"&gt;<br>";
        echo "&lt;meta property=\"og:url\" content=\"$validation_url\"&gt;<br>";
        echo "&lt;meta property=\"og:type\" content=\"website\"&gt;<br>";
        echo "&lt;meta property=\"og:site_name\" content=\"TecNM Insignias\"&gt;<br>";
        echo "</div>";
        
        echo "<h3>⚠️ Posibles problemas:</h3>";
        echo "<ul>";
        echo "<li>La imagen debe ser accesible públicamente (sin autenticación)</li>";
        echo "<li>Facebook puede tardar unos minutos en actualizar la caché</li>";
        echo "<li>Usa el Facebook Debugger para forzar la actualización</li>";
        echo "<li>Verifica que la URL de la imagen sea correcta</li>";
        echo "<li>La imagen debe tener al menos 600x315 píxeles</li>";
        echo "</ul>";
        
    } else {
        echo "<p>❌ La imagen NO existe en: $image_path</p>";
        echo "<p>Verifica que el archivo 'insignia_Responsabilidad Social.png' esté en la carpeta 'imagen/'</p>";
    }
    
} else {
    echo "<p>❌ La insignia <strong>$codigo_buscar</strong> NO existe</p>";
    echo "<p>Primero ejecuta <a href='crear_insignia_completa.php'>crear_insignia_completa.php</a> para crear la insignia</p>";
}

echo "<h3>💡 Instrucciones:</h3>";
echo "<ol>";
echo "<li>Ejecuta <a href='crear_insignia_completa.php'>crear_insignia_completa.php</a> si no existe la insignia</li>";
echo "<li>Verifica que la imagen exista en la carpeta 'imagen/'</li>";
echo "<li>Usa el enlace de 'imagen_compartible.php'</li>";
echo "<li>Si no funciona, usa el Facebook Debugger para verificar</li>";
echo "<li>Forza la actualización de caché en Facebook</li>";
echo "</ol>";
?>
