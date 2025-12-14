<?php
require_once 'conexion.php';

echo "<h2>🔗 Probar compartir en Facebook</h2>";

$codigo_buscar = 'TecNM-ITSM-20251-116';

// Verificar si existe la insignia
$result = $conexion->query("SELECT * FROM insigniasotorgadas WHERE clave_insignia = '$codigo_buscar'");
if ($result && $result->num_rows > 0) {
    echo "<p>✅ La insignia <strong>$codigo_buscar</strong> existe</p>";
    
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional';
    $validation_url = $base_url . '/validacion.php?insignia=' . urlencode($codigo_buscar);
    $share_url = $base_url . '/imagen_compartible.php?codigo=' . urlencode($codigo_buscar);
    
    echo "<h3>🔗 Enlaces para compartir:</h3>";
    echo "<ul>";
    echo "<li><a href='$share_url' target='_blank'>Página para compartir</a></li>";
    echo "<li><a href='$validation_url' target='_blank'>Validación directa</a></li>";
    echo "</ul>";
    
    echo "<h3>📱 Compartir en Facebook:</h3>";
    echo "<p>1. Copia este enlace:</p>";
    echo "<input type='text' value='$share_url' style='width: 100%; padding: 10px; margin: 10px 0;' readonly>";
    
    echo "<p>2. Ve a Facebook y pega el enlace en tu publicación</p>";
    echo "<p>3. Facebook debería mostrar la imagen de la insignia</p>";
    echo "<p>4. Al hacer clic en la imagen, llevará al certificado completo</p>";
    
    echo "<h3>🧪 Probar meta tags:</h3>";
    echo "<p>Para verificar que los meta tags funcionan correctamente:</p>";
    echo "<ul>";
    echo "<li><a href='https://developers.facebook.com/tools/debug/' target='_blank'>Facebook Debugger</a></li>";
    echo "<li><a href='https://cards-dev.twitter.com/validator' target='_blank'>Twitter Card Validator</a></li>";
    echo "</ul>";
    
    echo "<h3>📋 Meta tags generados:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;'>";
    echo "<strong>&lt;meta property=\"og:title\" content=\"Insignia TecNM - Responsabilidad Social\"&gt;</strong><br>";
    echo "<strong>&lt;meta property=\"og:description\" content=\"Insignia de Responsabilidad Social otorgada a Victor Jonathan Castro Secundino\"&gt;</strong><br>";
    echo "<strong>&lt;meta property=\"og:image\" content=\"$base_url/imagen/insignia_Responsabilidad Social.png\"&gt;</strong><br>";
    echo "<strong>&lt;meta property=\"og:url\" content=\"$validation_url\"&gt;</strong><br>";
    echo "</div>";
    
} else {
    echo "<p>❌ La insignia <strong>$codigo_buscar</strong> NO existe</p>";
    echo "<p>Primero ejecuta <a href='crear_insignia_completa.php'>crear_insignia_completa.php</a> para crear la insignia</p>";
}

echo "<h3>💡 Instrucciones:</h3>";
echo "<ol>";
echo "<li>Ejecuta <a href='crear_insignia_completa.php'>crear_insignia_completa.php</a> si no existe la insignia</li>";
echo "<li>Usa el enlace de 'Página para compartir' en Facebook</li>";
echo "<li>Verifica que la imagen se muestre correctamente</li>";
echo "<li>Haz clic en la imagen para ir al certificado completo</li>";
echo "</ol>";
?>
