<?php
/**
 * PROBAR ENVÍO EN TIEMPO REAL
 * Este archivo prueba el envío real usando la configuración
 */

echo "<h2>⚡ PROBAR ENVÍO EN TIEMPO REAL</h2>";
echo "<h3>📧 Probando envío real a destinatario</h3>";

// Verificar si PHPMailer está disponible
if (!file_exists('src/PHPMailer.php')) {
    echo "<h2>❌ PHPMailer no encontrado</h2>";
    exit;
}

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

// Cargar configuración si existe
if (file_exists('config_tiempo_real.php')) {
    require_once 'config_tiempo_real.php';
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>✅ Configuración cargada</h4>";
    echo "<p><strong>Servidor:</strong> " . (defined('SMTP_HOST') ? SMTP_HOST : 'No definido') . "</p>";
    echo "<p><strong>Puerto:</strong> " . (defined('SMTP_PORT') ? SMTP_PORT : 'No definido') . "</p>";
    echo "<p><strong>Usuario:</strong> " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'No definido') . "</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Configuración no encontrada</h4>";
    echo "<p>Primero ejecuta <a href='configurar_tiempo_real.php'>configurar_tiempo_real.php</a></p>";
    echo "</div>";
    exit;
}

// Verificar si la configuración está completa
if (!defined('SMTP_HOST') || !defined('SMTP_USERNAME') || !defined('SMTP_PASSWORD') || 
    SMTP_USERNAME === 'tu_correo@gmail.com' || SMTP_PASSWORD === 'tu_contraseña_aplicacion') {
    
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>⚠️ Configuración incompleta</h4>";
    echo "<p>Necesitas actualizar el archivo <strong>config_tiempo_real.php</strong> con tus datos reales:</p>";
    echo "<ul>";
    echo "<li>Cambia <strong>tu_correo@gmail.com</strong> por tu correo real</li>";
    echo "<li>Cambia <strong>tu_contraseña_aplicacion</strong> por tu contraseña real</li>";
    echo "</ul>";
    echo "</div>";
    exit;
}

echo "<h3>📤 Enviando correo en tiempo real...</h3>";

$destinatario = "211230001@smarcos.tecnm.mx";
$asunto = "🎖️ PRUEBA TIEMPO REAL - TecNM";
$mensaje_html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Prueba Tiempo Real</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <div style="background: #1b396a; color: white; padding: 20px; border-radius: 10px; text-align: center;">
        <h1>🎖️ TECNM</h1>
        <p>ENVÍO EN TIEMPO REAL</p>
    </div>
    <div style="background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #1b396a;">¡Funciona en Tiempo Real!</h2>
        <p>Este correo fue enviado <strong>en tiempo real</strong> usando un servicio SMTP real.</p>
        <p>Si recibiste este correo, el sistema está funcionando correctamente.</p>
        
        <div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <p><strong>Método:</strong> PHPMailer con SMTP real</p>
            <p><strong>Servidor:</strong> ' . SMTP_HOST . '</p>
            <p><strong>Puerto:</strong> ' . SMTP_PORT . '</p>
            <p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>
            <p><strong>Destinatario:</strong> ' . $destinatario . '</p>
        </div>
        
        <p style="text-align: center; color: #666;">
            <strong>Tecnológico Nacional de México</strong>
        </p>
    </div>
</body>
</html>
';

try {
    $mail = new PHPMailer(true);
    
    // Configuración SMTP
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    
    // Configuración SSL para XAMPP
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Configurar correo
    $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
    $mail->addAddress($destinatario, 'Usuario TecNM');

    // Contenido
    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body = $mensaje_html;

    // Enviar
    $mail->send();
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎉 ¡ÉXITO!</h4>";
    echo "<p><strong>✅ Correo enviado en tiempo real</strong></p>";
    echo "<p><strong>✅ Usando servicio SMTP real</strong></p>";
    echo "<p><strong>Servidor:</strong> " . SMTP_HOST . "</p>";
    echo "<p><strong>Puerto:</strong> " . SMTP_PORT . "</p>";
    echo "<p><strong>Destinatario:</strong> " . $destinatario . "</p>";
    echo "<p><strong>Asunto:</strong> " . $asunto . "</p>";
    echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>📧 ¿Dónde revisar?</h4>";
    echo "<p><strong>1. Bandeja de entrada:</strong> Revisa tu correo</p>";
    echo "<p><strong>2. Carpeta de spam:</strong> A veces va ahí</p>";
    echo "<p><strong>3. Busca:</strong> 🎖️ PRUEBA TIEMPO REAL - TecNM</p>";
    echo "</div>";
    
    echo "<h3>🚀 PRÓXIMO PASO:</h3>";
    echo "<p>Ahora puedes usar el sistema completo con envío real:</p>";
    echo "<p><a href='probar_insignia_yeni_directo.php' style='display: inline-block; background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold;'>🎖️ Crear Insignia para Yeni Castro Sánchez</a></p>";
    
    // Actualizar funciones_correo_real.php para usar esta configuración
    $config_actualizada = "<?php\n";
    $config_actualizada .= "// CONFIGURACIÓN TIEMPO REAL EXITOSA\n";
    $config_actualizada .= "define('SMTP_HOST', '" . SMTP_HOST . "');\n";
    $config_actualizada .= "define('SMTP_PORT', " . SMTP_PORT . ");\n";
    $config_actualizada .= "define('SMTP_USERNAME', '" . SMTP_USERNAME . "');\n";
    $config_actualizada .= "define('SMTP_PASSWORD', '" . SMTP_PASSWORD . "');\n";
    $config_actualizada .= "define('SMTP_FROM_NAME', '" . SMTP_FROM_NAME . "');\n";
    $config_actualizada .= "define('SMTP_SECURE', 'tls');\n";
    $config_actualizada .= "?>";
    
    file_put_contents('config_smtp_exitosa.php', $config_actualizada);
    echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>✅ Configuración guardada en:</strong> config_smtp_exitosa.php</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Error en envío tiempo real</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Posibles causas:</strong></p>";
    echo "<ul>";
    echo "<li>Credenciales incorrectas</li>";
    echo "<li>Servidor SMTP no disponible</li>";
    echo "<li>Firewall bloqueando conexión</li>";
    echo "<li>Configuración incompleta</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h4>🔧 Soluciones:</h4>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<ol>";
    echo "<li>Verifica que las credenciales sean correctas</li>";
    echo "<li>Genera una nueva contraseña de aplicación</li>";
    echo "<li>Prueba con otro servicio (SendGrid, Mailgun)</li>";
    echo "<li>Verifica la conexión a internet</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<h3>🔄 Probar Nuevamente:</h3>";
echo "<p><a href='probar_tiempo_real.php' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>🔄 Ejecutar Prueba Nuevamente</a></p>";

echo "<hr>";
echo "<p><a href='configurar_tiempo_real.php'>← Configurar tiempo real</a></p>";
echo "<p><a href='probar_insignia_yeni_directo.php'>← Crear insignia para Yeni</a></p>";

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Estado:</strong> <span style='color: blue; font-weight: bold;'>PROBANDO TIEMPO REAL</span></p>";
?>
