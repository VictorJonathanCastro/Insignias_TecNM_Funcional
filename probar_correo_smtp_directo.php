<?php
/**
 * SOLUCIÓN SIN PHPMailer - Configuración SMTP directa
 * Esta solución funciona sin necesidad de instalar PHPMailer
 */

echo "<h2>📧 Prueba de Correo SIN PHPMailer</h2>";

// Configuración del correo
$destinatario = "211230001@smarcos.tecnm.mx"; // Cambia por el correo que quieras probar
$asunto = "Prueba de Sistema de Insignias TecNM - SMTP Directo";
$mensaje = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Prueba SMTP Directo</title>
</head>
<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;'>
        <div style='background: linear-gradient(135deg, #1b396a, #002855); color: white; padding: 20px; text-align: center;'>
            <h1 style='margin: 0; font-size: 24px;'>🎖️ SISTEMA DE INSIGNIAS TECNM</h1>
            <p style='font-size: 16px;'>¡SMTP Directo Funcionando!</p>
        </div>
        <div style='padding: 30px; background-color: #f9f9f9;'>
            <h2 style='color: #002855; text-align: center;'>¡Éxito!</h2>
            <p>Este correo fue enviado usando configuración SMTP directa.</p>
            <p>El sistema de correos está <strong>funcionando correctamente</strong>.</p>
            
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
            Sistema de Insignias TecNM - SMTP Directo
            <p style='margin-top: 5px;'>Tecnológico Nacional de México © " . date('Y') . "</p>
        </div>
    </div>
</body>
</html>
";

// Configurar headers mejorados
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
$headers .= "From: Sistema Insignias TecNM <211230001@smarcos.tecnm.mx>" . "\r\n";
$headers .= "Reply-To: 211230001@smarcos.tecnm.mx" . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "X-Priority: 3" . "\r\n";

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
    echo "<p><strong>El sistema está funcionando correctamente.</strong></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Error al enviar correo</h4>";
    echo "<p>El correo no se pudo enviar. Vamos a configurar una solución alternativa:</p>";
    echo "</div>";
    
    // Mostrar información de configuración PHP
    echo "<h3>🔍 Información de Configuración PHP:</h3>";
    echo "<p><strong>sendmail_path:</strong> " . (ini_get('sendmail_path') ?: 'No configurado') . "</p>";
    echo "<p><strong>SMTP:</strong> " . (ini_get('SMTP') ?: 'No configurado') . "</p>";
    echo "<p><strong>smtp_port:</strong> " . (ini_get('smtp_port') ?: 'No configurado') . "</p>";
    echo "<p><strong>sendmail_from:</strong> " . (ini_get('sendmail_from') ?: 'No configurado') . "</p>";
    
    echo "<h3>🛠️ Solución Alternativa:</h3>";
    echo "<p>Vamos a crear una función que simule el envío de correos para desarrollo:</p>";
    
    // Crear función de simulación
    echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>📝 Función de Simulación de Correo:</h4>";
    echo "<p>Esta función guardará los correos en un archivo para desarrollo:</p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
    echo "function enviarCorreoSimulado(\$destinatario, \$asunto, \$mensaje) {\n";
    echo "    \$archivo = 'correos_enviados.txt';\n";
    echo "    \$contenido = \"\\n=== CORREO SIMULADO ===\\n\";\n";
    echo "    \$contenido .= \"Fecha: \" . date('Y-m-d H:i:s') . \"\\n\";\n";
    echo "    \$contenido .= \"Para: \" . \$destinatario . \"\\n\";\n";
    echo "    \$contenido .= \"Asunto: \" . \$asunto . \"\\n\";\n";
    echo "    \$contenido .= \"Mensaje: \" . \$mensaje . \"\\n\";\n";
    echo "    \$contenido .= \"========================\\n\";\n";
    echo "    file_put_contents(\$archivo, \$contenido, FILE_APPEND);\n";
    echo "    return true;\n";
    echo "}";
    echo "</pre>";
    echo "</div>";
}

echo "<h3>💡 Opciones disponibles:</h3>";
echo "<p>1. <strong>Configurar SMTP en php.ini</strong> (recomendado para producción)</p>";
echo "<p>2. <strong>Usar función de simulación</strong> (para desarrollo)</p>";
echo "<p>3. <strong>Instalar PHPMailer</strong> (más robusto)</p>";

echo "<hr>";
echo "<p><a href='metadatos_formulario.php'>← Volver al formulario de insignias</a></p>";
echo "<p><a href='probar_correo_simple.php'>← Probar correo básico</a></p>";
echo "<p><a href='probar_correo_phpmailer.php'>← Probar con PHPMailer</a></p>";
?>
