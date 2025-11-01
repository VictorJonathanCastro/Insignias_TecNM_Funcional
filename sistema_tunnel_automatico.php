<?php
// Sistema de detección automática de túnel funcionando
require_once 'conexion.php';

echo "<h2>🚀 Sistema de Túnel Automático</h2>";

// Lista de posibles URLs de túnel (puedes agregar más)
$posibles_tunnels = [
    'https://cruel-needles-agree.loca.lt',
    'https://brave-cats-smile.loca.lt',
    'https://funny-dogs-run.loca.lt',
    'https://happy-birds-fly.loca.lt'
];

$tunnel_funcionando = null;
$test_url_base = '/Insignias_TecNM_Funcional/validacion.php?insignia=TECNM-ITSM-2025-ART-336';

echo "<h3>🔍 Probando túneles disponibles:</h3>";

foreach ($posibles_tunnels as $tunnel_url) {
    echo "<p>Probando: <strong>$tunnel_url</strong> ... ";
    
    $test_url = $tunnel_url . $test_url_base;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'GET',
            'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    try {
        $response = @file_get_contents($test_url, false, $context);
        
        if ($response !== false) {
            echo "<span style='color: green; font-weight: bold;'>✅ FUNCIONANDO</span></p>";
            $tunnel_funcionando = $tunnel_url;
            break;
        } else {
            echo "<span style='color: red;'>❌ No disponible</span></p>";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>❌ Error</span></p>";
    }
}

if ($tunnel_funcionando) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
    echo "<h3>✅ Túnel Funcionando Encontrado</h3>";
    echo "<p><strong>URL del túnel:</strong> $tunnel_funcionando</p>";
    echo "<p><a href='$tunnel_funcionando$test_url_base' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Probar Validación</a></p>";
    echo "</div>";
    
    // Generar código PHP para actualizar la URL
    echo "<h3>📝 Código para Actualizar:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace;'>";
    echo "// Actualizar en validacion.php línea 332:<br>";
    echo "// Cambiar:<br>";
    echo "// \$base_url = 'https://cruel-needles-agree.loca.lt/Insignias_TecNM_Funcional';<br>";
    echo "// Por:<br>";
    echo "// \$base_url = '$tunnel_funcionando/Insignias_TecNM_Funcional';<br>";
    echo "</div>";
    
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ Ningún Túnel Disponible</h3>";
    echo "<p>Todos los túneles probados están fuera de servicio.</p>";
    echo "</div>";
    
    echo "<h3>🛠️ Soluciones Manuales:</h3>";
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196f3;'>";
    
    echo "<h4>Opción 1: Crear Nuevo LocalTunnel</h4>";
    echo "<ol>";
    echo "<li>Abre una terminal (cmd)</li>";
    echo "<li>Ejecuta: <code>lt --port 80</code></li>";
    echo "<li>Copia la nueva URL que genere</li>";
    echo "<li>Actualiza el código PHP</li>";
    echo "</ol>";
    
    echo "<h4>Opción 2: Usar ngrok</h4>";
    echo "<ol>";
    echo "<li>Descarga ngrok desde <a href='https://ngrok.com/' target='_blank'>https://ngrok.com/</a></li>";
    echo "<li>Ejecuta: <code>ngrok http 80</code></li>";
    echo "<li>Copia la URL HTTPS que genere</li>";
    echo "</ol>";
    
    echo "<h4>Opción 3: Usar serveo</h4>";
    echo "<ol>";
    echo "<li>Abre una terminal</li>";
    echo "<li>Ejecuta: <code>ssh -R 80:localhost:80 serveo.net</code></li>";
    echo "<li>Copia la URL que genere</li>";
    echo "</ol>";
    
    echo "</div>";
}

// Verificar XAMPP
echo "<h3>🔍 Estado de XAMPP:</h3>";
$xampp_test = @file_get_contents('http://localhost/Insignias_TecNM_Funcional/validacion.php?insignia=TECNM-ITSM-2025-ART-336', false, $context);

if ($xampp_test !== false) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
    echo "<h4>✅ XAMPP Funcionando</h4>";
    echo "<p>XAMPP está corriendo correctamente en localhost.</p>";
    echo "<p><a href='http://localhost/Insignias_TecNM_Funcional/validacion.php?insignia=TECNM-ITSM-2025-ART-336' target='_blank'>🔗 Probar localhost</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;'>";
    echo "<h4>❌ XAMPP No Funcionando</h4>";
    echo "<p>XAMPP no está corriendo. Por favor, inicia XAMPP y asegúrate de que Apache esté corriendo en el puerto 80.</p>";
    echo "</div>";
}

// Botón para actualizar automáticamente
echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<button onclick='actualizarTunnels()' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 10px;'>";
echo "🔄 Actualizar Lista de Túneles";
echo "</button>";
echo "<button onclick='window.location.reload()' style='background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 10px;'>";
echo "🔄 Recargar Página";
echo "</button>";
echo "</div>";

echo "<script>";
echo "function actualizarTunnels() {";
echo "    alert('Para agregar nuevos túneles, edita el archivo y agrega URLs en el array \$posibles_tunnels');";
echo "}";
echo "</script>";
?>
