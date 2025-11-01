<?php
require_once 'conexion.php';

echo "<h2>🚀 Configurar localtunnel - Node.js Instalado</h2>";

$codigo_buscar = 'TecNM-ITSM-20251-116';

echo "<p>✅ Node.js ya está instalado</p>";

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
        
        echo "<h3>🔧 Instrucciones para localtunnel:</h3>";
        echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196f3;'>";
        echo "<h4>Paso 1: Instalar localtunnel</h4>";
        echo "<p>Abre una terminal (cmd) y ejecuta:</p>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
        echo "npm install -g localtunnel";
        echo "</div>";
        echo "<br>";
        echo "<h4>Paso 2: Ejecutar localtunnel</h4>";
        echo "<p>En la misma terminal, ejecuta:</p>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
        echo "lt --port 80";
        echo "</div>";
        echo "<br>";
        echo "<h4>Paso 3: Copiar la URL</h4>";
        echo "<p>localtunnel mostrará algo como:</p>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
        echo "your url is: https://abc123.loca.lt";
        echo "</div>";
        echo "<p>Copia la URL: <strong>https://abc123.loca.lt</strong></p>";
        echo "<br>";
        echo "<h4>Paso 4: Usar la URL con localtunnel</h4>";
        echo "<p>Usa esta URL en tu navegador:</p>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
        echo "https://abc123.loca.lt/Insignias_TecNM_Funcional/facebook_imagen.php?codigo=TecNM-ITSM-20251-116";
        echo "</div>";
        echo "</div>";
        
        echo "<h3>🧪 Pruebas con localtunnel:</h3>";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h4>Prueba 1: Acceso local</h4>";
        echo "<p><a href='facebook_imagen.php?codigo=$codigo_buscar' target='_blank'>facebook_imagen.php (local)</a></p>";
        echo "<p>Esta URL funciona localmente pero Facebook no puede acceder a ella.</p>";
        echo "<br>";
        echo "<h4>Prueba 2: Con localtunnel (cuando esté configurado)</h4>";
        echo "<p>https://abc123.loca.lt/Insignias_TecNM_Funcional/facebook_imagen.php?codigo=$codigo_buscar</p>";
        echo "<p>Esta URL funcionará con Facebook una vez que configures localtunnel.</p>";
        echo "<br>";
        echo "<h4>Prueba 3: Facebook Debugger</h4>";
        echo "<p><a href='https://developers.facebook.com/tools/debug/' target='_blank'>https://developers.facebook.com/tools/debug/</a></p>";
        echo "<p>Pega la URL de localtunnel en el debugger para verificar los meta tags.</p>";
        echo "</div>";
        
        echo "<h3>✅ Estado del sistema:</h3>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
        echo "<h4 style='color: #155724; margin: 0;'>✅ LISTO PARA CONFIGURAR LOCALTUNNEL</h4>";
        echo "<p style='color: #155724; margin: 10px 0 0 0;'>Node.js está instalado. Solo necesitas instalar y ejecutar localtunnel para que Facebook funcione.</p>";
        echo "</div>";
        
        echo "<h3>📋 Meta tags que se generarán:</h3>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;'>";
        echo "&lt;meta property=\"og:title\" content=\"Insignia TecNM - Responsabilidad Social\"&gt;<br>";
        echo "&lt;meta property=\"og:description\" content=\"He recibido una insignia de Responsabilidad Social del TecNM!!!\"&gt;<br>";
        echo "&lt;meta property=\"og:image\" content=\"https://abc123.loca.lt/Insignias_TecNM_Funcional/imagen/insignia_Responsabilidad Social.png\"&gt;<br>";
        echo "&lt;meta property=\"og:image:secure_url\" content=\"https://abc123.loca.lt/Insignias_TecNM_Funcional/imagen/insignia_Responsabilidad Social.png\"&gt;<br>";
        echo "&lt;meta property=\"og:image:type\" content=\"image/png\"&gt;<br>";
        echo "&lt;meta property=\"og:image:width\" content=\"1200\"&gt;<br>";
        echo "&lt;meta property=\"og:image:height\" content=\"630\"&gt;<br>";
        echo "&lt;meta property=\"og:url\" content=\"https://abc123.loca.lt/Insignias_TecNM_Funcional/facebook_imagen.php?codigo=TecNM-ITSM-20251-116\"&gt;<br>";
        echo "&lt;meta property=\"og:type\" content=\"website\"&gt;<br>";
        echo "&lt;meta property=\"og:site_name\" content=\"TecNM Insignias\"&gt;<br>";
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
echo "<li>Instala localtunnel: <code>npm install -g localtunnel</code></li>";
echo "<li>Ejecuta localtunnel: <code>lt --port 80</code></li>";
echo "<li>Copia la URL que genera localtunnel</li>";
echo "<li>Usa esa URL para compartir en Facebook</li>";
echo "<li>¡Facebook podrá acceder a la imagen sin errores SSL!</li>";
echo "</ol>";
?>
