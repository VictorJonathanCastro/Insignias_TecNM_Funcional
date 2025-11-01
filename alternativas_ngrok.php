<?php
require_once 'conexion.php';

echo "<h2>🚀 Solución Alternativa - Servicios de Túnel Gratuitos</h2>";

$codigo_buscar = 'TecNM-ITSM-20251-116';

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
        
        echo "<h3>🔧 Alternativas a ngrok (Gratuitas):</h3>";
        echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196f3;'>";
        echo "<h4>Opción 1: localtunnel (Recomendado)</h4>";
        echo "<p>Servicio gratuito y fácil de usar:</p>";
        echo "<ol>";
        echo "<li>Instalar Node.js desde <a href='https://nodejs.org/' target='_blank'>https://nodejs.org/</a></li>";
        echo "<li>Abrir terminal y ejecutar: <code>npm install -g localtunnel</code></li>";
        echo "<li>Ejecutar: <code>lt --port 80</code></li>";
        echo "<li>Copiar la URL que genera (ej: https://abc123.loca.lt)</li>";
        echo "</ol>";
        echo "<br>";
        echo "<h4>Opción 2: serveo (Sin instalación)</h4>";
        echo "<p>Servicio que no requiere instalación:</p>";
        echo "<ol>";
        echo "<li>Abrir terminal</li>";
        echo "<li>Ejecutar: <code>ssh -R 80:localhost:80 serveo.net</code></li>";
        echo "<li>Copiar la URL que genera (ej: https://abc123.serveo.net)</li>";
        echo "</ol>";
        echo "<br>";
        echo "<h4>Opción 3: Cloudflare Tunnel (Gratuito)</h4>";
        echo "<p>Servicio de Cloudflare:</p>";
        echo "<ol>";
        echo "<li>Descargar cloudflared desde <a href='https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/' target='_blank'>Cloudflare</a></li>";
        echo "<li>Ejecutar: <code>cloudflared tunnel --url http://localhost:80</code></li>";
        echo "<li>Copiar la URL que genera</li>";
        echo "</ol>";
        echo "</div>";
        
        echo "<h3>🧪 Pruebas con túneles:</h3>";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h4>Prueba 1: Acceso local</h4>";
        echo "<p><a href='facebook_ngrok.php?codigo=$codigo_buscar' target='_blank'>facebook_ngrok.php (local)</a></p>";
        echo "<p>Esta URL funciona localmente pero Facebook no puede acceder a ella.</p>";
        echo "<br>";
        echo "<h4>Prueba 2: Con túnel (cuando esté configurado)</h4>";
        echo "<p>https://abc123.loca.lt/Insignias_TecNM_Funcional/facebook_ngrok.php?codigo=$codigo_buscar&ngrok=https://abc123.loca.lt</p>";
        echo "<p>Esta URL funcionará con Facebook una vez que configures el túnel.</p>";
        echo "<br>";
        echo "<h4>Prueba 3: Facebook Debugger</h4>";
        echo "<p><a href='https://developers.facebook.com/tools/debug/' target='_blank'>https://developers.facebook.com/tools/debug/</a></p>";
        echo "<p>Pega la URL del túnel en el debugger para verificar que Facebook pueda acceder a ella.</p>";
        echo "</div>";
        
        echo "<h3>🔧 Instrucciones detalladas para localtunnel:</h3>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
        echo "<h4>Paso 1: Instalar Node.js</h4>";
        echo "<p>Ve a <a href='https://nodejs.org/' target='_blank'>https://nodejs.org/</a> y descarga la versión LTS para Windows.</p>";
        echo "<p>Instala Node.js con la configuración por defecto.</p>";
        echo "<br>";
        echo "<h4>Paso 2: Instalar localtunnel</h4>";
        echo "<p>Abre una terminal (cmd) y ejecuta:</p>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
        echo "npm install -g localtunnel";
        echo "</div>";
        echo "<br>";
        echo "<h4>Paso 3: Ejecutar localtunnel</h4>";
        echo "<p>En la misma terminal, ejecuta:</p>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
        echo "lt --port 80";
        echo "</div>";
        echo "<br>";
        echo "<h4>Paso 4: Copiar la URL</h4>";
        echo "<p>localtunnel mostrará algo como:</p>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
        echo "your url is: https://abc123.loca.lt";
        echo "</div>";
        echo "<p>Copia la URL: <strong>https://abc123.loca.lt</strong></p>";
        echo "<br>";
        echo "<h4>Paso 5: Usar la URL con túnel</h4>";
        echo "<p>Usa esta URL en tu navegador:</p>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
        echo "https://abc123.loca.lt/Insignias_TecNM_Funcional/facebook_ngrok.php?codigo=TecNM-ITSM-20251-116&ngrok=https://abc123.loca.lt";
        echo "</div>";
        echo "</div>";
        
        echo "<h3>✅ Estado del sistema:</h3>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
        echo "<h4 style='color: #155724; margin: 0;'>✅ SISTEMA LISTO PARA TÚNELES GRATUITOS</h4>";
        echo "<p style='color: #155724; margin: 10px 0 0 0;'>El sistema está preparado para funcionar con cualquier servicio de túnel gratuito. Una vez configurado, Facebook podrá acceder a la imagen.</p>";
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
        echo "&lt;meta property=\"og:url\" content=\"https://abc123.loca.lt/Insignias_TecNM_Funcional/facebook_ngrok.php?codigo=TecNM-ITSM-20251-116&ngrok=https://abc123.loca.lt\"&gt;<br>";
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
echo "<li>Instala Node.js y configura localtunnel (más fácil)</li>";
echo "<li>Usa la URL del túnel para compartir en Facebook</li>";
echo "<li>Facebook podrá acceder a la URL pública y mostrar la imagen</li>";
echo "</ol>";
?>
