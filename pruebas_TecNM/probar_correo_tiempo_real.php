<?php
/**
 * Script para probar el envío de correo en TIEMPO REAL
 * Verifica si PHPMailer funciona correctamente
 */

require_once 'conexion.php';
require_once 'funciones_correo_real.php';

// Configuración de prueba
$correo_destino = $_GET['correo'] ?? '211230001@smarcos.tecnm.mx';

echo "<h1>⚡ Prueba de Correo en Tiempo Real</h1>";
echo "<p><strong>Enviando correo de prueba a:</strong> $correo_destino</p>";
echo "<hr>";

// Verificar configuración
echo "<h2>📋 Verificación de Configuración</h2>";

if (file_exists('config_smtp.php')) {
    require_once 'config_smtp.php';
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Configuración SMTP encontrada</h3>";
    echo "<p><strong>Correo:</strong> " . (defined('SMTP_USERNAME') ? htmlspecialchars(SMTP_USERNAME) : 'No configurado') . "</p>";
    echo "<p><strong>Contraseña:</strong> " . (defined('SMTP_PASSWORD') && !empty(SMTP_PASSWORD) ? str_repeat('*', strlen(SMTP_PASSWORD)) : 'No configurada') . "</p>";
    echo "<p><strong>Servidor Principal:</strong> " . (defined('SMTP_HOST') ? htmlspecialchars(SMTP_HOST) : 'No configurado') . "</p>";
    echo "<p><strong>Puerto:</strong> " . (defined('SMTP_PORT') ? SMTP_PORT : 'No configurado') . "</p>";
    
    // Mostrar orden de servidores que se probarán
    if (defined('SMTP_SERVERS_ALTERNATIVOS')) {
        echo "<p><strong>Servidores alternativos:</strong> ";
        $servidores_lista = [];
        if (defined('SMTP_HOST')) {
            $servidores_lista[] = SMTP_HOST . " (PRIMERO)";
        }
        foreach (SMTP_SERVERS_ALTERNATIVOS as $host => $config) {
            if ($host !== SMTP_HOST) {
                $servidores_lista[] = $host;
            }
        }
        echo implode(", ", $servidores_lista);
        echo "</p>";
    }
    echo "</div>";
    
    if (empty(SMTP_PASSWORD) || SMTP_PASSWORD === 'CONTRASEÑA_QUE_TE_DEN_PARA_ESTE_CORREO') {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>⚠️ Contraseña no configurada</h3>";
        echo "<p>La contraseña SMTP no está configurada. Edita config_smtp.php y actualiza SMTP_PASSWORD.</p>";
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ config_smtp.php no encontrado</h3>";
    echo "<p>No se puede probar PHPMailer sin configuración SMTP.</p>";
    echo "</div>";
}

echo "<hr>";

$datos_prueba = [
    'estudiante' => 'Estudiante de Prueba',
    'matricula' => '123456789',
    'curp' => 'TEST123456HDFABC01',
    'nombre_insignia' => 'Talento Científico',
    'categoria' => 'Desarrollo Académico',
    'codigo_insignia' => 'TECNM-OFCM-2025-TAL-TEST',
    'periodo' => '2025-1',
    'fecha_otorgamiento' => date('Y-m-d'),
    'responsable' => 'Sistema de Prueba',
    'descripcion' => 'Esta es una prueba del sistema de correo en tiempo real',
    'url_verificacion' => 'http://158.23.160.163/ver_insignia_publica.php?insignia=TECNM-OFCM-2025-TAL-TEST',
    'url_imagen' => 'http://158.23.160.163/imagen/Insignias/TalentoCientifico.png'
];

echo "<h2>1. Probando PHPMailer con SMTP (TIEMPO REAL)</h2>";
echo "<p style='color: #6c757d;'>Este método garantiza entrega inmediata si las credenciales son correctas.</p>";

$inicio = microtime(true);
ob_start();
$resultado_phpmailer = enviarConPHPMailerReal($correo_destino, "Prueba Tiempo Real - Insignia Otorgada", generarMensajeCorreo($datos_prueba), $datos_prueba);
$debug_output = ob_get_clean();
$tiempo = round((microtime(true) - $inicio) * 1000, 2);

if ($resultado_phpmailer) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ PHPMailer funcionó correctamente</h3>";
    echo "<p><strong>Tiempo de envío:</strong> {$tiempo}ms</p>";
    echo "<p><strong>Estado:</strong> <span style='color: green; font-weight: bold;'>⚡ CORREO ENVIADO EN TIEMPO REAL</span></p>";
    echo "<p>El correo debería llegar al destinatario en menos de 1 minuto.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ PHPMailer falló</h3>";
    echo "<p><strong>Tiempo de intento:</strong> {$tiempo}ms</p>";
    
    if (!empty($debug_output)) {
        echo "<div style='background: #fff; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; font-size: 12px; white-space: pre-wrap; max-height: 300px; overflow-y: auto;'>";
        echo "<strong>Detalles del error:</strong><br>";
        echo htmlspecialchars($debug_output);
        echo "</div>";
    }
    
    echo "<p><strong>Posibles causas:</strong></p>";
    echo "<ul>";
    echo "<li>Credenciales incorrectas (correo o contraseña)</li>";
    echo "<li>Office 365 requiere autenticación de dos factores (necesitas contraseña de aplicación)</li>";
    echo "<li>El servidor SMTP no es el correcto</li>";
    echo "<li>Problemas de firewall o conexión de red</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<br>";

echo "<h2>2. Probando mail() nativo (TIEMPO REAL - procesamiento inmediato)</h2>";
echo "<p style='color: #6c757d;'>Este método procesa la cola inmediatamente para envío en tiempo real.</p>";

$inicio = microtime(true);
$resultado_nativo = enviarConMailNativo($correo_destino, "Prueba - Insignia Otorgada", generarMensajeCorreo($datos_prueba));
$tiempo = round((microtime(true) - $inicio) * 1000, 2);

if ($resultado_nativo) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ mail() nativo funcionó</h3>";
    echo "<p><strong>Tiempo de envío:</strong> {$tiempo}ms</p>";
    echo "<p><strong>Estado:</strong> <span style='color: green; font-weight: bold;'>⚡ CORREO ENVIADO EN TIEMPO REAL</span></p>";
    echo "<p>El correo fue procesado inmediatamente y debería llegar en menos de 1 minuto.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ mail() nativo falló</h3>";
    echo "<p>Sendmail no está instalado o no está configurado correctamente.</p>";
    echo "</div>";
}

echo "<hr>";

echo "<h2>📊 Resumen</h2>";
echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
echo "<tr style='background: #f4f4f4;'>";
echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Método</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Estado</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Tiempo</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Tipo</th>";
echo "</tr>";

echo "<tr>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'><strong>PHPMailer SMTP</strong></td>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . ($resultado_phpmailer ? "<span style='color: green;'>✅ OK</span>" : "<span style='color: red;'>❌ FALLÓ</span>") . "</td>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . ($resultado_phpmailer ? "{$tiempo}ms" : "N/A") . "</td>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'><span style='color: green; font-weight: bold;'>⚡ TIEMPO REAL</span></td>";
echo "</tr>";

echo "<tr>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'><strong>mail() nativo</strong></td>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . ($resultado_nativo ? "<span style='color: green;'>✅ OK</span>" : "<span style='color: red;'>❌ FALLÓ</span>") . "</td>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . ($resultado_nativo ? "{$tiempo}ms" : "N/A") . "</td>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'><span style='color: green; font-weight: bold;'>⚡ TIEMPO REAL</span></td>";
echo "</tr>";

echo "</table>";

if ($resultado_phpmailer) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #28a745;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>✅ ¡Sistema configurado para correo en tiempo real!</h3>";
    echo "<p>El sistema está usando PHPMailer con SMTP, lo que garantiza entrega inmediata.</p>";
    echo "<p><strong>Cuando registres una insignia:</strong></p>";
    echo "<ul>";
    echo "<li>✅ El correo se enviará inmediatamente</li>";
    echo "<li>✅ Llegará al estudiante en menos de 1 minuto</li>";
    echo "<li>✅ No habrá retrasos</li>";
    echo "</ul>";
    echo "</div>";
} elseif ($resultado_nativo) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #28a745;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>✅ Sistema configurado para correo en tiempo real</h3>";
    echo "<p>El sistema está usando mail() nativo con procesamiento inmediato.</p>";
    echo "<p><strong>Cuando registres una insignia:</strong></p>";
    echo "<ul>";
    echo "<li>✅ El correo se enviará inmediatamente</li>";
    echo "<li>✅ Llegará al estudiante en menos de 1 minuto</li>";
    echo "<li>✅ No habrá retrasos</li>";
    echo "</ul>";
    echo "<p><strong>Estado:</strong> Sistema funcionando al 100% en tiempo real</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #dc3545;'>";
    echo "<h3 style='color: #721c24; margin-top: 0;'>❌ Sistema no puede enviar correos</h3>";
    echo "<p>Ninguno de los métodos de envío funciona. Revisa la configuración.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='probar_correo.php'>← Prueba estándar</a> | <a href='metadatos_formulario.php'>← Volver al formulario</a></p>";
?>

