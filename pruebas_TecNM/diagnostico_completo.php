<?php
// Página de diagnóstico completo del sistema de túneles
require_once 'conexion.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Diagnóstico del Sistema de Túneles</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }";
echo ".container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 1200px; margin: 0 auto; }";
echo ".success { background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745; margin: 10px 0; }";
echo ".error { background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545; margin: 10px 0; }";
echo ".warning { background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 10px 0; }";
echo ".info { background: #e3f2fd; padding: 15px; border-radius: 5px; border-left: 4px solid #2196f3; margin: 10px 0; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo ".btn-success { background: #28a745; }";
echo ".btn-danger { background: #dc3545; }";
echo ".code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔍 Diagnóstico Completo del Sistema de Túneles</h1>";

// 1. Verificar XAMPP
echo "<h2>1. Estado de XAMPP</h2>";
$xampp_test = @file_get_contents('http://localhost/Insignias_TecNM_Funcional/validacion.php?insignia=TECNM-ITSM-2025-ART-336', false, stream_context_create(['http' => ['timeout' => 5]]));

if ($xampp_test !== false) {
    echo "<div class='success'>";
    echo "<h3>✅ XAMPP Funcionando Correctamente</h3>";
    echo "<p>XAMPP está corriendo en el puerto 80 y el sistema responde correctamente.</p>";
    echo "<p><a href='http://localhost/Insignias_TecNM_Funcional/validacion.php?insignia=TECNM-ITSM-2025-ART-336' target='_blank' class='btn btn-success'>🔗 Probar localhost</a></p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>❌ XAMPP No Funcionando</h3>";
    echo "<p>XAMPP no está corriendo o no está configurado correctamente.</p>";
    echo "<p><strong>Solución:</strong> Inicia XAMPP y asegúrate de que Apache esté corriendo en el puerto 80.</p>";
    echo "</div>";
}

// 2. Verificar túneles disponibles
echo "<h2>2. Estado de los Túneles</h2>";
$tunnels = [
    'cruel-needles-agree.loca.lt' => 'https://cruel-needles-agree.loca.lt',
    'brave-cats-smile.loca.lt' => 'https://brave-cats-smile.loca.lt',
    'funny-dogs-run.loca.lt' => 'https://funny-dogs-run.loca.lt',
    'happy-birds-fly.loca.lt' => 'https://happy-birds-fly.loca.lt'
];

$tunnel_funcionando = null;
$test_url_base = '/Insignias_TecNM_Funcional/validacion.php?insignia=TECNM-ITSM-2025-ART-336';

foreach ($tunnels as $name => $url) {
    echo "<h3>Probando: $name</h3>";
    
    $test_url = $url . $test_url_base;
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
            echo "<div class='success'>";
            echo "<h4>✅ Túnel Funcionando</h4>";
            echo "<p><strong>URL:</strong> $url</p>";
            echo "<p><a href='$test_url' target='_blank' class='btn btn-success'>🔗 Probar túnel</a></p>";
            echo "</div>";
            $tunnel_funcionando = $url;
        } else {
            echo "<div class='error'>";
            echo "<h4>❌ Túnel No Disponible</h4>";
            echo "<p>El túnel $name no está funcionando.</p>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<h4>❌ Error al Probar Túnel</h4>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
}

// 3. Soluciones
echo "<h2>3. Soluciones Disponibles</h2>";

if ($tunnel_funcionando) {
    echo "<div class='success'>";
    echo "<h3>✅ Solución Encontrada</h3>";
    echo "<p>El túnel <strong>$tunnel_funcionando</strong> está funcionando correctamente.</p>";
    echo "<p>El sistema debería funcionar automáticamente con esta URL.</p>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Todos los Túneles Están Fuera de Servicio</h3>";
    echo "<p>Ninguno de los túneles probados está funcionando.</p>";
    echo "</div>";
    
    echo "<h3>🛠️ Soluciones Manuales:</h3>";
    
    echo "<div class='info'>";
    echo "<h4>Opción 1: Crear Nuevo LocalTunnel</h4>";
    echo "<ol>";
    echo "<li>Abre una terminal (cmd)</li>";
    echo "<li>Ejecuta: <div class='code'>lt --port 80</div></li>";
    echo "<li>Copia la nueva URL que genere</li>";
    echo "<li>Actualiza el código PHP</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h4>Opción 2: Usar ngrok</h4>";
    echo "<ol>";
    echo "<li>Descarga ngrok desde <a href='https://ngrok.com/' target='_blank'>https://ngrok.com/</a></li>";
    echo "<li>Ejecuta: <div class='code'>ngrok http 80</div></li>";
    echo "<li>Copia la URL HTTPS que genere</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h4>Opción 3: Usar serveo</h4>";
    echo "<ol>";
    echo "<li>Abre una terminal</li>";
    echo "<li>Ejecuta: <div class='code'>ssh -R 80:localhost:80 serveo.net</div></li>";
    echo "<li>Copia la URL que genere</li>";
    echo "</ol>";
    echo "</div>";
}

// 4. Información del sistema
echo "<h2>4. Información del Sistema</h2>";
echo "<div class='info'>";
echo "<h3>📊 Detalles del Servidor</h3>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p><strong>Puerto:</strong> " . ($_SERVER['SERVER_PORT'] ?? 'No especificado') . "</p>";
echo "<p><strong>Protocolo:</strong> " . ($_SERVER['HTTPS'] ? 'HTTPS' : 'HTTP') . "</p>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "</div>";

// 5. Botones de acción
echo "<h2>5. Acciones Disponibles</h2>";
echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='verificar_tunnel_status.php' class='btn'>🔍 Verificar Estado del Túnel</a>";
echo "<a href='sistema_tunnel_automatico.php' class='btn'>🚀 Sistema Automático</a>";
echo "<a href='actualizador_tunnel_automatico.php' class='btn'>🔧 Actualizador Automático</a>";
echo "<a href='validacion.php?insignia=TECNM-ITSM-2025-ART-336' target='_blank' class='btn btn-success'>🔗 Probar Validación</a>";
echo "<button onclick='window.location.reload()' class='btn'>🔄 Recargar Diagnóstico</button>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
