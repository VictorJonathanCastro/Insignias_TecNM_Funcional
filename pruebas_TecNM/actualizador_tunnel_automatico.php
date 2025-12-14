<?php
// Actualizador automático de URL de túnel
require_once 'conexion.php';

echo "<h2>🔧 Actualizador Automático de Túnel</h2>";

// Lista de posibles URLs de túnel
$posibles_tunnels = [
    'https://cruel-needles-agree.loca.lt',
    'https://brave-cats-smile.loca.lt',
    'https://funny-dogs-run.loca.lt',
    'https://happy-birds-fly.loca.lt'
];

$tunnel_funcionando = null;
$test_url_base = '/Insignias_TecNM_Funcional/validacion.php?insignia=TECNM-ITSM-2025-ART-336';

echo "<h3>🔍 Buscando túnel funcionando...</h3>";

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
    echo "</div>";
    
    // Leer el archivo validacion.php
    $archivo_validacion = 'validacion.php';
    
    if (file_exists($archivo_validacion)) {
        $contenido = file_get_contents($archivo_validacion);
        
        // Buscar la línea que contiene la URL del túnel
        $patron = '/\$base_url = \'https:\/\/[^\']+\.loca\.lt\/Insignias_TecNM_Funcional\';/';
        $nueva_url = '$base_url = \'' . $tunnel_funcionando . '/Insignias_TecNM_Funcional\';';
        
        if (preg_match($patron, $contenido)) {
            $contenido_actualizado = preg_replace($patron, $nueva_url, $contenido);
            
            // Crear backup del archivo original
            $backup_file = 'validacion_backup_' . date('Y-m-d_H-i-s') . '.php';
            file_put_contents($backup_file, $contenido);
            
            // Actualizar el archivo
            if (file_put_contents($archivo_validacion, $contenido_actualizado)) {
                echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
                echo "<h3>✅ Archivo Actualizado Exitosamente</h3>";
                echo "<p>El archivo <strong>validacion.php</strong> ha sido actualizado con la nueva URL del túnel.</p>";
                echo "<p><strong>Backup creado:</strong> $backup_file</p>";
                echo "<p><a href='$tunnel_funcionando$test_url_base' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Probar Validación Actualizada</a></p>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
                echo "<h3>❌ Error al Actualizar Archivo</h3>";
                echo "<p>No se pudo escribir en el archivo validacion.php. Verifica los permisos.</p>";
                echo "</div>";
            }
        } else {
            echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
            echo "<h3>⚠️ Patrón No Encontrado</h3>";
            echo "<p>No se encontró el patrón de URL del túnel en validacion.php.</p>";
            echo "<p>Actualización manual requerida.</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
        echo "<h3>❌ Archivo No Encontrado</h3>";
        echo "<p>El archivo validacion.php no existe.</p>";
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ Ningún Túnel Disponible</h3>";
    echo "<p>Todos los túneles probados están fuera de servicio.</p>";
    echo "<p>Por favor, crea un nuevo túnel manualmente.</p>";
    echo "</div>";
    
    echo "<h3>🛠️ Instrucciones para Crear Nuevo Túnel:</h3>";
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196f3;'>";
    
    echo "<h4>Paso 1: Abrir Terminal</h4>";
    echo "<p>Abre una terminal (cmd) en Windows.</p>";
    
    echo "<h4>Paso 2: Ejecutar LocalTunnel</h4>";
    echo "<p>Ejecuta el siguiente comando:</p>";
    echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
    echo "lt --port 80";
    echo "</div>";
    
    echo "<h4>Paso 3: Copiar URL</h4>";
    echo "<p>LocalTunnel mostrará algo como:</p>";
    echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
    echo "your url is: https://abc123.loca.lt";
    echo "</div>";
    
    echo "<h4>Paso 4: Actualizar Código</h4>";
    echo "<p>Agrega la nueva URL al array \$posibles_tunnels en este archivo y ejecuta nuevamente.</p>";
    
    echo "</div>";
}

// Mostrar información adicional
echo "<h3>📊 Información del Sistema:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Archivo actual:</strong> " . __FILE__ . "</p>";
echo "</div>";

// Botones de acción
echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<button onclick='window.location.reload()' style='background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 10px;'>";
echo "🔄 Ejecutar Nuevamente";
echo "</button>";
echo "<a href='validacion.php?insignia=TECNM-ITSM-2025-ART-336' target='_blank' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; margin: 10px; display: inline-block;'>";
echo "🔗 Probar Validación";
echo "</a>";
echo "</div>";
?>
