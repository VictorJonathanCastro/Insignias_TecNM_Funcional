<?php
/**
 * Prueba simple de envío de correos
 * Este archivo te permitirá probar si el correo funciona
 */

echo "<h2>📧 Prueba de Envío de Correos</h2>";

// Configuración del correo
$destinatario = "211230001@smarcos.tecnm.mx"; // Cambia por el correo que quieras probar
$asunto = "Prueba de Sistema de Insignias TecNM";
$mensaje = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Prueba de Correo</title>
</head>
<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;'>
        <div style='background: linear-gradient(135deg, #1b396a, #002855); color: white; padding: 20px; text-align: center;'>
            <h1 style='margin: 0; font-size: 24px;'>🎖️ SISTEMA DE INSIGNIAS TECNM</h1>
            <p style='font-size: 16px;'>¡Prueba de Correo Exitoso!</p>
        </div>
        <div style='padding: 30px; background-color: #f9f9f9;'>
            <h2 style='color: #002855; text-align: center;'>¡Hola!</h2>
            <p>Este es un correo de prueba del Sistema de Insignias TecNM.</p>
            <p>Si recibiste este correo, significa que la configuración está funcionando correctamente.</p>
            
            <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #1b396a;'>📋 Información de Prueba:</h3>
                <p><strong>Estudiante:</strong> Juan Pérez García</p>
                <p><strong>Matrícula:</strong> 211230001</p>
                <p><strong>CURP:</strong> PERJ800101HDFRGN01</p>
                <p><strong>Insignia:</strong> Excelencia Académica</p>
                <p><strong>Categoría:</strong> Formación Integral</p>
                <p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>
            </div>
            
            <p style='text-align: center; margin-top: 30px;'>
                <a href='#' style='display: inline-block; background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-weight: bold;'>
                    Ver Insignia
                </a>
            </p>
        </div>
        <div style='background-color: #eee; padding: 15px; text-align: center; font-size: 12px; color: #666;'>
            Este es un mensaje de prueba del Sistema de Insignias TecNM
            <p style='margin-top: 5px;'>Tecnológico Nacional de México © " . date('Y') . "</p>
        </div>
    </div>
</body>
</html>
";

// Headers del correo
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
$headers .= "From: Sistema Insignias TecNM <211230001@smarcos.tecnm.mx>" . "\r\n";
$headers .= "Reply-To: 211230001@smarcos.tecnm.mx" . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

echo "<h3>🔧 Configuración:</h3>";
echo "<p><strong>Destinatario:</strong> " . htmlspecialchars($destinatario) . "</p>";
echo "<p><strong>Remitente:</strong> 211230001@smarcos.tecnm.mx</p>";
echo "<p><strong>Asunto:</strong> " . htmlspecialchars($asunto) . "</p>";

echo "<h3>📤 Enviando correo...</h3>";

// Intentar enviar el correo
$enviado = mail($destinatario, $asunto, $mensaje, $headers);

if ($enviado) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>✅ ¡Correo enviado exitosamente!</h4>";
    echo "<p>El correo se ha enviado a: <strong>" . htmlspecialchars($destinatario) . "</strong></p>";
    echo "<p>Revisa tu bandeja de entrada y la carpeta de spam.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Error al enviar correo</h4>";
    echo "<p>El correo no se pudo enviar. Posibles causas:</p>";
    echo "<ul>";
    echo "<li>Configuración SMTP incorrecta en php.ini</li>";
    echo "<li>Servidor de correo no disponible</li>";
    echo "<li>Firewall bloqueando el puerto 25/587</li>";
    echo "</ul>";
    echo "</div>";
    
    // Mostrar información de configuración PHP
    echo "<h3>🔍 Información de Configuración PHP:</h3>";
    echo "<p><strong>sendmail_path:</strong> " . (ini_get('sendmail_path') ?: 'No configurado') . "</p>";
    echo "<p><strong>SMTP:</strong> " . (ini_get('SMTP') ?: 'No configurado') . "</p>";
    echo "<p><strong>smtp_port:</strong> " . (ini_get('smtp_port') ?: 'No configurado') . "</p>";
    echo "<p><strong>sendmail_from:</strong> " . (ini_get('sendmail_from') ?: 'No configurado') . "</p>";
}

echo "<h3>💡 Próximos pasos:</h3>";
echo "<p>1. Si el correo se envió exitosamente, el sistema está funcionando</p>";
echo "<p>2. Si falló, necesitas configurar SMTP en php.ini</p>";
echo "<p>3. Para producción, considera usar PHPMailer con Gmail</p>";

echo "<hr>";
echo "<p><a href='metadatos_formulario.php'>← Volver al formulario de insignias</a></p>";
?>
