<?php
require_once 'conexion.php';

echo "<h2>🚨 Problema Facebook - No Muestra Imagen</h2>";

$codigo_buscar = 'TecNM-ITSM-20251-116';
$base_url = 'http://127.0.0.1/Insignias_TecNM_Funcional';

echo "<p><strong>URL actual:</strong> $base_url</p>";

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
        $share_url = $base_url . '/facebook_127.php?codigo=' . urlencode($codigo_buscar);
        $validation_url = $base_url . '/validacion.php?insignia=' . urlencode($codigo_buscar);
        $image_url = $base_url . '/imagen/insignia_Responsabilidad Social.png';
        
        echo "<h3>🔗 Enlaces del sistema:</h3>";
        echo "<ul>";
        echo "<li><a href='$share_url' target='_blank' style='color: #1877f2; font-weight: bold; font-size: 18px;'>facebook_127.php (127.0.0.1)</a></li>";
        echo "<li><a href='$validation_url' target='_blank'>validacion.php (Certificado)</a></li>";
        echo "<li><a href='$image_url' target='_blank'>Imagen directa</a></li>";
        echo "</ul>";
        
        echo "<h3>🚨 PROBLEMA IDENTIFICADO:</h3>";
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
        echo "<h4>Facebook NO puede acceder a URLs de localhost:</h4>";
        echo "<ul>";
        echo "<li>❌ <code>http://localhost/</code> - No funciona</li>";
        echo "<li>❌ <code>http://127.0.0.1/</code> - No funciona</li>";
        echo "<li>❌ <code>http://::1/</code> - No funciona</li>";
        echo "<li>❌ Cualquier IP local - No funciona</li>";
        echo "</ul>";
        echo "<p><strong>Razón:</strong> Facebook solo puede acceder a URLs públicas, no a servidores locales.</p>";
        echo "</div>";
        
        echo "<h3>✅ SOLUCIONES:</h3>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
        echo "<h4>Opción 1: Usar ngrok (Recomendado)</h4>";
        echo "<ol>";
        echo "<li>Descargar ngrok desde <a href='https://ngrok.com/' target='_blank'>https://ngrok.com/</a></li>";
        echo "<li>Ejecutar: <code>ngrok http 80</code></li>";
        echo "<li>Usar la URL pública que genera ngrok</li>";
        echo "<li>Ejemplo: <code>https://abc123.ngrok.io/Insignias_TecNM_Funcional/facebook_127.php?codigo=TecNM-ITSM-20251-116</code></li>";
        echo "</ol>";
        echo "<br>";
        echo "<h4>Opción 2: Subir a hosting público</h4>";
        echo "<ol>";
        echo "<li>Subir el sistema a un hosting público (000webhost, InfinityFree, etc.)</li>";
        echo "<li>Usar la URL pública del hosting</li>";
        echo "<li>Ejemplo: <code>https://tusitio.com/Insignias_TecNM_Funcional/facebook_127.php?codigo=TecNM-ITSM-20251-116</code></li>";
        echo "</ol>";
        echo "<br>";
        echo "<h4>Opción 3: Usar servidor local con IP pública</h4>";
        echo "<ol>";
        echo "<li>Configurar el servidor para que sea accesible desde internet</li>";
        echo "<li>Usar la IP pública de tu router</li>";
        echo "<li>Configurar port forwarding en el router</li>";
        echo "</ol>";
        echo "</div>";
        
        echo "<h3>🧪 Pruebas locales:</h3>";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h4>Prueba 1: Acceso directo a la imagen</h4>";
        echo "<p><a href='$image_url' target='_blank'>$image_url</a></p>";
        echo "<p>Esta URL funciona localmente, pero Facebook no puede acceder a ella.</p>";
        echo "<br>";
        echo "<h4>Prueba 2: Meta tags de la página</h4>";
        echo "<p><a href='$share_url' target='_blank'>$share_url</a></p>";
        echo "<p>Esta página contiene los meta tags correctos, pero Facebook no puede acceder a ella.</p>";
        echo "<br>";
        echo "<h4>Prueba 3: Facebook Debugger</h4>";
        echo "<p><a href='https://developers.facebook.com/tools/debug/' target='_blank'>https://developers.facebook.com/tools/debug/</a></p>";
        echo "<p>Si pruebas la URL local en el debugger, te dará error de acceso.</p>";
        echo "</div>";
        
        echo "<h3>📋 Meta tags generados (correctos pero inaccesibles):</h3>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;'>";
        echo "&lt;meta property=\"og:title\" content=\"Insignia TecNM - Responsabilidad Social\"&gt;<br>";
        echo "&lt;meta property=\"og:description\" content=\"He recibido una insignia de Responsabilidad Social del TecNM!!!\"&gt;<br>";
        echo "&lt;meta property=\"og:image\" content=\"$image_url\"&gt;<br>";
        echo "&lt;meta property=\"og:image:secure_url\" content=\"$image_url\"&gt;<br>";
        echo "&lt;meta property=\"og:image:type\" content=\"image/png\"&gt;<br>";
        echo "&lt;meta property=\"og:image:width\" content=\"1200\"&gt;<br>";
        echo "&lt;meta property=\"og:image:height\" content=\"630\"&gt;<br>";
        echo "&lt;meta property=\"og:url\" content=\"$share_url\"&gt;<br>";
        echo "&lt;meta property=\"og:type\" content=\"website\"&gt;<br>";
        echo "&lt;meta property=\"og:site_name\" content=\"TecNM Insignias\"&gt;<br>";
        echo "</div>";
        
        echo "<h3>✅ Estado del sistema:</h3>";
        echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
        echo "<h4 style='color: #856404; margin: 0;'>⚠️ SISTEMA FUNCIONAL PERO INACCESIBLE PARA FACEBOOK</h4>";
        echo "<p style='color: #856404; margin: 10px 0 0 0;'>El sistema funciona correctamente localmente, pero Facebook no puede acceder a URLs de localhost.</p>";
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
echo "<li><strong>IMPORTANTE:</strong> Usa ngrok o sube el sistema a un hosting público</li>";
echo "<li>Una vez que tengas una URL pública, usa esa URL para compartir en Facebook</li>";
echo "<li>Facebook podrá acceder a la URL pública y mostrar la imagen</li>";
echo "</ol>";
?>
