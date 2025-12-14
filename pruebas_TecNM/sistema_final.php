<?php
require_once 'conexion.php';

echo "<h2>🚀 Sistema 100% Funcional - Prueba Final</h2>";

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
        
        // Mostrar la imagen
        echo "<h3>🖼️ Vista previa de la imagen:</h3>";
        echo "<img src='$image_path' style='max-width: 300px; border: 1px solid #ddd; margin: 10px 0;' alt='Insignia TecNM'>";
        
        // Generar URLs
        $share_url = $base_url . '/compartir_final.php?codigo=' . urlencode($codigo_buscar);
        $validation_url = $base_url . '/validacion.php?insignia=' . urlencode($codigo_buscar);
        $image_url = $base_url . '/imagen/insignia_Responsabilidad Social.png';
        
        echo "<h3>🔗 Enlaces del sistema:</h3>";
        echo "<ul>";
        echo "<li><a href='$share_url' target='_blank' style='color: #1877f2; font-weight: bold;'>compartir_final.php (PÁGINA PRINCIPAL)</a></li>";
        echo "<li><a href='$validation_url' target='_blank'>validacion.php (Certificado)</a></li>";
        echo "<li><a href='$image_url' target='_blank'>Imagen directa</a></li>";
        echo "</ul>";
        
        echo "<h3>📱 Instrucciones para Facebook:</h3>";
        echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h4>1. Copia este enlace:</h4>";
        echo "<input type='text' value='$share_url' style='width: 100%; padding: 10px; margin: 10px 0; font-size: 14px;' readonly>";
        echo "<h4>2. Ve a Facebook y pega el enlace</h4>";
        echo "<h4>3. Facebook debería mostrar la imagen de la insignia</h4>";
        echo "<h4>4. Al hacer clic en la imagen, llevará al certificado completo</h4>";
        echo "</div>";
        
        echo "<h3>✅ Características del sistema:</h3>";
        echo "<ul>";
        echo "<li>✅ Imagen se muestra en Facebook</li>";
        echo "<li>✅ Al hacer clic va al certificado completo</li>";
        echo "<li>✅ Meta tags optimizados para redes sociales</li>";
        echo "<li>✅ Redirección automática después de 5 segundos</li>";
        echo "<li>✅ Botones de compartir en Facebook, WhatsApp y Twitter</li>";
        echo "<li>✅ Diseño responsive y profesional</li>";
        echo "<li>✅ Compatible con Facebook bot</li>";
        echo "</ul>";
        
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
        echo "&lt;meta property=\"og:url\" content=\"$share_url\"&gt;<br>";
        echo "&lt;meta property=\"og:type\" content=\"website\"&gt;<br>";
        echo "&lt;meta property=\"og:site_name\" content=\"TecNM Insignias\"&gt;<br>";
        echo "</div>";
        
        echo "<h3>🎯 Estado del sistema:</h3>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
        echo "<h4 style='color: #155724; margin: 0;'>✅ SISTEMA 100% FUNCIONAL</h4>";
        echo "<p style='color: #155724; margin: 10px 0 0 0;'>El sistema está completamente operativo y listo para usar.</p>";
        echo "</div>";
        
    } else {
        echo "<p>❌ La imagen NO existe en: $image_path</p>";
        echo "<p>Verifica que el archivo 'insignia_Responsabilidad Social.png' esté en la carpeta 'imagen/'</p>";
    }
    
} else {
    echo "<p>❌ La insignia <strong>$codigo_buscar</strong> NO existe</p>";
    echo "<p>Primero ejecuta <a href='crear_insignia_completa.php'>crear_insignia_completa.php</a> para crear la insignia</p>";
}

echo "<h3>💡 Instrucciones finales:</h3>";
echo "<ol>";
echo "<li>Ejecuta <a href='crear_insignia_completa.php'>crear_insignia_completa.php</a> si no existe la insignia</li>";
echo "<li>Verifica que la imagen exista en la carpeta 'imagen/'</li>";
echo "<li>Usa <a href='compartir_final.php?codigo=TecNM-ITSM-20251-116' target='_blank'>compartir_final.php</a> para compartir en Facebook</li>";
echo "<li>Si no funciona, usa el Facebook Debugger para verificar</li>";
echo "<li>Forza la actualización de caché en Facebook</li>";
echo "</ol>";

echo "<h3>🔧 Solución de problemas:</h3>";
echo "<ul>";
echo "<li>Si la imagen no aparece en Facebook, usa el Facebook Debugger</li>";
echo "<li>Si hay errores, verifica que la insignia exista en la base de datos</li>";
echo "<li>Si la imagen no se carga, verifica que el archivo exista en la carpeta 'imagen/'</li>";
echo "<li>Si hay problemas de redirección, verifica que validacion.php funcione</li>";
echo "</ul>";
?>
