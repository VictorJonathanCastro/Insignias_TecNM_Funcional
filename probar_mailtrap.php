<?php
/**
 * PROBAR MAILTRAP - SERVICIO GRATUITO SIN CONFIGURACIÓN COMPLEJA
 * Solo necesitas registrarte y copiar las credenciales
 */

echo "<h2>📧 PROBAR MAILTRAP</h2>";
echo "<h3>🎯 Servicio gratuito para notificaciones</h3>";

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
if (file_exists('config_mailtrap.php')) {
    require_once 'config_mailtrap.php';
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>✅ Configuración cargada</h4>";
    echo "<p><strong>Servidor:</strong> " . (defined('SMTP_HOST') ? SMTP_HOST : 'No definido') . "</p>";
    echo "<p><strong>Puerto:</strong> " . (defined('SMTP_PORT') ? SMTP_PORT : 'No definido') . "</p>";
    echo "<p><strong>Usuario:</strong> " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'No definido') . "</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Configuración no encontrada</h4>";
    echo "<p>Primero ejecuta <a href='solucion_sin_credenciales.php'>solucion_sin_credenciales.php</a></p>";
    echo "</div>";
    exit;
}

// Verificar si la configuración está completa
if (!defined('SMTP_HOST') || !defined('SMTP_USERNAME') || !defined('SMTP_PASSWORD') || 
    SMTP_USERNAME === 'tu_usuario_mailtrap' || SMTP_PASSWORD === 'tu_password_mailtrap') {
    
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>⚠️ Configuración incompleta</h4>";
    echo "<p>Necesitas actualizar el archivo <strong>config_mailtrap.php</strong> con tus datos de Mailtrap:</p>";
    echo "<ul>";
    echo "<li>Cambia <strong>tu_usuario_mailtrap</strong> por tu usuario real de Mailtrap</li>";
    echo "<li>Cambia <strong>tu_password_mailtrap</strong> por tu password real de Mailtrap</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>📋 Cómo obtener credenciales de Mailtrap:</h4>";
    echo "<ol>";
    echo "<li>Ve a <a href='https://mailtrap.io' target='_blank'>mailtrap.io</a></li>";
    echo "<li>Regístrate gratis</li>";
    echo "<li>Ve a 'Inboxes' → 'Demo Inbox'</li>";
    echo "<li>Copia el 'Username' y 'Password'</li>";
    echo "<li>Pégalos en <strong>config_mailtrap.php</strong></li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p><a href='https://mailtrap.io' target='_blank' style='display: inline-block; background: #dc3545; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold;'>📧 Ir a Mailtrap</a></p>";
    exit;
}

echo "<h3>📤 Enviando notificación con Mailtrap...</h3>";

$destinatario = "211230001@smarcos.tecnm.mx";
$asunto = "🎖️ NOTIFICACIÓN REAL - Insignia TecNM";
$mensaje_html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Notificación Real</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <div style="background: #1b396a; color: white; padding: 20px; border-radius: 10px; text-align: center;">
        <h1>🎖️ TECNM</h1>
        <p>NOTIFICACIÓN REAL ENVIADA</p>
    </div>
    <div style="background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #1b396a;">¡Notificación Enviada!</h2>
        <p>Esta es una <strong>notificación real</strong> enviada usando Mailtrap.</p>
        <p>El sistema está funcionando correctamente para enviar notificaciones.</p>
        
        <div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <h3 style="color: #1b396a;">📋 Detalles de la Notificación:</h3>
            <p><strong>Método:</strong> PHPMailer con Mailtrap</p>
            <p><strong>Servidor:</strong> ' . SMTP_HOST . '</p>
            <p><strong>Puerto:</strong> ' . SMTP_PORT . '</p>
            <p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>
            <p><strong>Destinatario:</strong> ' . $destinatario . '</p>
            <p><strong>Tipo:</strong> Notificación de insignia</p>
        </div>
        
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <h4>✅ Sistema Funcionando:</h4>
            <p>• Notificaciones en tiempo real</p>
            <p>• Sin necesidad de credenciales del destinatario</p>
            <p>• Servicio gratuito y confiable</p>
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
    
    // Configuración SMTP Mailtrap
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = ''; // Mailtrap no requiere SSL
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
    $mail->setFrom('noreply@tecnm.mx', SMTP_FROM_NAME);
    $mail->addAddress($destinatario, 'Usuario TecNM');

    // Contenido
    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body = $mensaje_html;

    // Enviar
    $mail->send();
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎉 ¡ÉXITO!</h4>";
    echo "<p><strong>✅ Notificación enviada en tiempo real</strong></p>";
    echo "<p><strong>✅ Usando Mailtrap (servicio gratuito)</strong></p>";
    echo "<p><strong>✅ Sin necesidad de credenciales del destinatario</strong></p>";
    echo "<p><strong>Servidor:</strong> " . SMTP_HOST . "</p>";
    echo "<p><strong>Puerto:</strong> " . SMTP_PORT . "</p>";
    echo "<p><strong>Destinatario:</strong> " . $destinatario . "</p>";
    echo "<p><strong>Asunto:</strong> " . $asunto . "</p>";
    echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>📧 ¿Dónde revisar?</h4>";
    echo "<p><strong>1. Mailtrap:</strong> Ve a tu cuenta de Mailtrap para ver el correo</p>";
    echo "<p><strong>2. Demo Inbox:</strong> Allí aparecerá el correo enviado</p>";
    echo "<p><strong>3. Busca:</strong> 🎖️ NOTIFICACIÓN REAL - Insignia TecNM</p>";
    echo "</div>";
    
    echo "<h3>🚀 PRÓXIMO PASO:</h3>";
    echo "<p>Ahora puedes usar el sistema completo con notificaciones reales:</p>";
    echo "<p><a href='probar_insignia_yeni_directo.php' style='display: inline-block; background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold;'>🎖️ Crear Insignia para Yeni Castro Sánchez</a></p>";
    
    // Actualizar funciones_correo_real.php para usar esta configuración
    $config_actualizada = "<?php\n";
    $config_actualizada .= "// CONFIGURACIÓN MAILTRAP EXITOSA\n";
    $config_actualizada .= "define('SMTP_HOST', '" . SMTP_HOST . "');\n";
    $config_actualizada .= "define('SMTP_PORT', " . SMTP_PORT . ");\n";
    $config_actualizada .= "define('SMTP_USERNAME', '" . SMTP_USERNAME . "');\n";
    $config_actualizada .= "define('SMTP_PASSWORD', '" . SMTP_PASSWORD . "');\n";
    $config_actualizada .= "define('SMTP_FROM_NAME', '" . SMTP_FROM_NAME . "');\n";
    $config_actualizada .= "define('SMTP_SECURE', '');\n";
    $config_actualizada .= "?>";
    
    file_put_contents('config_smtp_exitosa.php', $config_actualizada);
    echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>✅ Configuración guardada en:</strong> config_smtp_exitosa.php</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Error en envío con Mailtrap</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Posibles causas:</strong></p>";
    echo "<ul>";
    echo "<li>Credenciales de Mailtrap incorrectas</li>";
    echo "<li>No te has registrado en Mailtrap</li>";
    echo "<li>No has copiado las credenciales correctas</li>";
    echo "<li>Configuración incompleta</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h4>🔧 Soluciones:</h4>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<ol>";
    echo "<li>Regístrate en <a href='https://mailtrap.io' target='_blank'>mailtrap.io</a></li>";
    echo "<li>Ve a 'Inboxes' → 'Demo Inbox'</li>";
    echo "<li>Copia el 'Username' y 'Password'</li>";
    echo "<li>Actualiza <strong>config_mailtrap.php</strong></li>";
    echo "</ol>";
    echo "</div>";
}

echo "<h3>🔄 Probar Nuevamente:</h3>";
echo "<p><a href='probar_mailtrap.php' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>🔄 Ejecutar Prueba Nuevamente</a></p>";

echo "<hr>";
echo "<p><a href='solucion_sin_credenciales.php'>← Solución sin credenciales</a></p>";
echo "<p><a href='probar_insignia_yeni_directo.php'>← Crear insignia para Yeni</a></p>";

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Estado:</strong> <span style='color: blue; font-weight: bold;'>PROBANDO MAILTRAP</span></p>";
?>
