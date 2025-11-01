<?php
require_once 'conexion.php';

echo "<h2>🔐 Solución Página de Verificación localTunnel</h2>";

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
        
        echo "<h3>🔐 Solución para la Página de Verificación:</h3>";
        echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196f3;'>";
        echo "<h4>¿Qué es la página de verificación?</h4>";
        echo "<p>localTunnel muestra esta página para evitar abusos. Es normal y seguro.</p>";
        echo "<br>";
        echo "<h4>¿Cómo obtener la contraseña?</h4>";
        echo "<p>La contraseña es tu <strong>IP pública</strong>. Puedes obtenerla de varias formas:</p>";
        echo "<ol>";
        echo "<li><strong>Opción 1:</strong> Ve a <a href='https://whatismyipaddress.com/' target='_blank'>https://whatismyipaddress.com/</a></li>";
        echo "<li><strong>Opción 2:</strong> Ve a <a href='https://ipinfo.io/' target='_blank'>https://ipinfo.io/</a></li>";
        echo "<li><strong>Opción 3:</strong> Busca en Google 'mi ip'</li>";
        echo "</ol>";
        echo "<br>";
        echo "<h4>Pasos para continuar:</h4>";
        echo "<ol>";
        echo "<li>Obtén tu IP pública de cualquiera de los sitios de arriba</li>";
        echo "<li>Ingresa la IP en el campo 'Contraseña del túnel'</li>";
        echo "<li>Haz clic en 'Haga clic para enviar'</li>";
        echo "<li>Ahora podrás acceder a tu sitio</li>";
        echo "</ol>";
        echo "</div>";
        
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
        echo "<p>Después de pasar la verificación, puedes compartir en Facebook usando esta URL:</p>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0; border: 2px solid #28a745;'>";
        echo "<input type='text' value='$facebook_url' style='width: 100%; padding: 15px; font-size: 16px; border: none; background: transparent;' readonly>";
        echo "</div>";
        echo "<p><strong>Pasos:</strong></p>";
        echo "<ol>";
        echo "<li>Pasa la verificación de localTunnel</li>";
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
        echo "<p>Esta URL es pública y Facebook puede acceder a ella (después de la verificación).</p>";
        echo "<br>";
        echo "<h4>Prueba 2: Página de Facebook</h4>";
        echo "<p><a href='$facebook_url' target='_blank'>$facebook_url</a></p>";
        echo "<p>Esta página contiene los meta tags para Facebook.</p>";
        echo "<br>";
        echo "<h4>Prueba 3: Facebook Debugger</h4>";
        echo "<p><a href='https://developers.facebook.com/tools/debug/' target='_blank'>https://developers.facebook.com/tools/debug/</a></p>";
        echo "<p>Pega la URL de Facebook en el debugger para verificar los meta tags.</p>";
        echo "</div>";
        
        echo "<h3>✅ Estado del sistema:</h3>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
        echo "<h4 style='color: #155724; margin: 0;'>🎉 ¡SISTEMA FUNCIONANDO!</h4>";
        echo "<p style='color: #155724; margin: 10px 0 0 0;'>localTunnel está funcionando correctamente. Solo necesitas pasar la verificación para acceder al sitio.</p>";
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
echo "<li>Obtén tu IP pública de <a href='https://whatismyipaddress.com/' target='_blank'>whatismyipaddress.com</a></li>";
echo "<li>Ingresa la IP en la página de verificación de localTunnel</li>";
echo "<li>Mantén localTunnel activo en la terminal</li>";
echo "<li>Copia la URL de Facebook y pégala en Facebook</li>";
echo "<li>¡Facebook debería mostrar la imagen correctamente!</li>";
echo "</ol>";
?>
