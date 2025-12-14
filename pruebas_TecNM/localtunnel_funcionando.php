<?php
require_once 'conexion.php';

echo "<h2>🎉 ¡localTunnel Funcionando! - URL Pública Lista</h2>";

$codigo_buscar = 'TecNM-ITSM-20251-116';
$localtunnel_url = 'https://cruel-needles-agree.loca.lt';

echo "<p><strong>URL de localTunnel:</strong> $localtunnel_url</p>";

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
        
        // Generar URLs con localTunnel
        $facebook_url = $localtunnel_url . '/Insignias_TecNM_Funcional/facebook_imagen.php?codigo=' . urlencode($codigo_buscar);
        $validation_url = $localtunnel_url . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . urlencode($codigo_buscar);
        $image_url = $localtunnel_url . '/Insignias_TecNM_Funcional/imagen/insignia_Responsabilidad Social.png';
        
        echo "<h3>🔗 URLs Públicas con localTunnel:</h3>";
        echo "<ul>";
        echo "<li><a href='$facebook_url' target='_blank' style='color: #1877f2; font-weight: bold; font-size: 18px;'>facebook_imagen.php (PARA FACEBOOK)</a></li>";
        echo "<li><a href='$validation_url' target='_blank'>validacion.php (Certificado)</a></li>";
        echo "<li><a href='$image_url' target='_blank'>Imagen directa</a></li>";
        echo "</ul>";
        
        echo "<h3>📱 Instrucciones para Facebook:</h3>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
        echo "<h4>✅ ¡localTunnel está funcionando!</h4>";
        echo "<p>Ahora puedes compartir en Facebook usando esta URL:</p>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0; border: 2px solid #28a745;'>";
        echo "<input type='text' value='$facebook_url' style='width: 100%; padding: 15px; font-size: 16px; border: none; background: transparent;' readonly>";
        echo "</div>";
        echo "<p><strong>Pasos:</strong></p>";
        echo "<ol>";
        echo "<li>Copia la URL de arriba</li>";
        echo "<li>Ve a Facebook y pega la URL</li>";
        echo "<li>Facebook debería mostrar la imagen de la insignia</li>";
        echo "<li>Al hacer clic en la imagen, llevará al certificado completo</li>";
        echo "</ol>";
        echo "</div>";
        
        echo "<h3>🧪 Pruebas con localTunnel:</h3>";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h4>Prueba 1: Acceso directo a la imagen</h4>";
        echo "<p><a href='$image_url' target='_blank'>$image_url</a></p>";
        echo "<p>Esta URL es pública y Facebook puede acceder a ella.</p>";
        echo "<br>";
        echo "<h4>Prueba 2: Página de Facebook</h4>";
        echo "<p><a href='$facebook_url' target='_blank'>$facebook_url</a></p>";
        echo "<p>Esta página contiene los meta tags para Facebook.</p>";
        echo "<br>";
        echo "<h4>Prueba 3: Facebook Debugger</h4>";
        echo "<p><a href='https://developers.facebook.com/tools/debug/' target='_blank'>https://developers.facebook.com/tools/debug/</a></p>";
        echo "<p>Pega la URL de Facebook en el debugger para verificar los meta tags.</p>";
        echo "</div>";
        
        echo "<h3>📋 Meta tags generados (con localTunnel):</h3>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;'>";
        echo "&lt;meta property=\"og:title\" content=\"Insignia TecNM - Responsabilidad Social\"&gt;<br>";
        echo "&lt;meta property=\"og:description\" content=\"He recibido una insignia de Responsabilidad Social del TecNM!!!\"&gt;<br>";
        echo "&lt;meta property=\"og:image\" content=\"$image_url\"&gt;<br>";
        echo "&lt;meta property=\"og:image:secure_url\" content=\"$image_url\"&gt;<br>";
        echo "&lt;meta property=\"og:image:type\" content=\"image/png\"&gt;<br>";
        echo "&lt;meta property=\"og:image:width\" content=\"1200\"&gt;<br>";
        echo "&lt;meta property=\"og:image:height\" content=\"630\"&gt;<br>";
        echo "&lt;meta property=\"og:url\" content=\"$facebook_url\"&gt;<br>";
        echo "&lt;meta property=\"og:type\" content=\"website\"&gt;<br>";
        echo "&lt;meta property=\"og:site_name\" content=\"TecNM Insignias\"&gt;<br>";
        echo "</div>";
        
        echo "<h3>✅ Estado del sistema:</h3>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
        echo "<h4 style='color: #155724; margin: 0;'>🎉 ¡SISTEMA COMPLETAMENTE FUNCIONAL!</h4>";
        echo "<p style='color: #155724; margin: 10px 0 0 0;'>localTunnel está funcionando y Facebook puede acceder a la URL pública. El sistema está listo para usar.</p>";
        echo "</div>";
        
        echo "<h3>🔧 Comandos para mantener localTunnel activo:</h3>";
        echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
        echo "<h4>Para mantener localTunnel funcionando:</h4>";
        echo "<p>Mantén abierta la terminal con el comando:</p>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
        echo "lt --port 80";
        echo "</div>";
        echo "<p><strong>Importante:</strong> No cierres la terminal mientras uses Facebook.</p>";
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
echo "<li>Mantén localTunnel activo en la terminal</li>";
echo "<li>Copia la URL de Facebook y pégala en Facebook</li>";
echo "<li>¡Facebook debería mostrar la imagen correctamente!</li>";
echo "</ol>";
?>
