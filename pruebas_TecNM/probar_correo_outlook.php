<?php
/**
 * Prueba de correo con Outlook - SOLUCIÓN DEFINITIVA
 * Este archivo está configurado específicamente para Outlook
 */

// Verificar si PHPMailer está disponible
if (!file_exists('src/PHPMailer.php')) {
    echo "<h2>❌ PHPMailer no encontrado</h2>";
    echo "<p>Necesitas instalar PHPMailer primero:</p>";
    echo "<ol>";
    echo "<li>Descarga PHPMailer desde: <a href='https://github.com/PHPMailer/PHPMailer' target='_blank'>https://github.com/PHPMailer/PHPMailer</a></li>";
    echo "<li>Extrae la carpeta 'src' en tu proyecto</li>";
    echo "<li>O ejecuta: <code>composer require phpmailer/phpmailer</code></li>";
    echo "</ol>";
    exit;
}

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

echo "<h2>📧 Prueba de Correo con Outlook</h2>";

// CONFIGURACIÓN PARA OUTLOOK
$tu_correo = "211230001@smarcos.tecnm.mx"; // Tu correo de Outlook
$tu_contraseña_app = "123456789"; // Tu contraseña de Outlook
$correo_destino = "211230001@smarcos.tecnm.mx"; // Correo de destino para la prueba

echo "<h3>🔧 Configuración para Outlook:</h3>";
echo "<p><strong>Tu correo:</strong> " . htmlspecialchars($tu_correo) . "</p>";
echo "<p><strong>Correo destino:</strong> " . htmlspecialchars($correo_destino) . "</p>";
echo "<p><strong>Servidor SMTP:</strong> smtp-mail.outlook.com</p>";
echo "<p><strong>Puerto:</strong> 587</p>";

if ($tu_contraseña_app === "123456789") {
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>⚠️ Configuración Pendiente</h4>";
    echo "<p>Necesitas configurar tu contraseña de Outlook:</p>";
    echo "<ol>";
    echo "<li>Ve a tu cuenta de Microsoft/Outlook</li>";
    echo "<li>Seguridad → Verificación en 2 pasos</li>";
    echo "<li>Contraseñas de aplicaciones</li>";
    echo "<li>Genera una contraseña para 'Mail'</li>";
    echo "<li>Copia esa contraseña y reemplaza '123456789' en este archivo</li>";
    echo "</ol>";
    echo "</div>";
    exit;
}

echo "<h3>📤 Enviando correo con Outlook...</h3>";

$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP para Outlook
    $mail->isSMTP();
    $mail->Host = 'smtp-mail.outlook.com'; // Servidor SMTP de Outlook
    $mail->SMTPAuth = true;
    $mail->Username = $tu_correo;
    $mail->Password = $tu_contraseña_app;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587; // Puerto para Outlook
    $mail->CharSet = 'UTF-8';
    
    // Configuración SSL para XAMPP
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Configurar remitente y destinatario
    $mail->setFrom($tu_correo, 'Sistema Insignias TecNM');
    $mail->addAddress($correo_destino, 'Usuario de Prueba');

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = '🎖️ Prueba de Sistema de Insignias TecNM - Outlook';
    
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Prueba Outlook</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
        <div style="max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;">
            <div style="background: linear-gradient(135deg, #1b396a, #002855); color: white; padding: 20px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">🎖️ SISTEMA DE INSIGNIAS TECNM</h1>
                <p style="font-size: 16px;">¡Outlook Funcionando!</p>
            </div>
            <div style="padding: 30px; background-color: #f9f9f9;">
                <h2 style="color: #002855; text-align: center;">¡Éxito Total!</h2>
                <p>Este correo fue enviado usando <strong>PHPMailer con Outlook</strong>.</p>
                <p>El sistema de correos está <strong>100% funcional</strong>.</p>
                
                <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="color: #1b396a;">📋 Información de Prueba:</h3>
                    <p><strong>Estudiante:</strong> Juan Pérez García</p>
                    <p><strong>Matrícula:</strong> 211230001</p>
                    <p><strong>CURP:</strong> PERJ800101HDFRGN01</p>
                    <p><strong>Insignia:</strong> Excelencia Académica</p>
                    <p><strong>Categoría:</strong> Formación Integral</p>
                    <p><strong>Código:</strong> INS-2024-001</p>
                    <p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>
                </div>
                
                <p style="text-align: center; margin-top: 30px;">
                    <a href="#" style="display: inline-block; background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                        Ver Insignia Completa
                    </a>
                </p>
            </div>
            <div style="background-color: #eee; padding: 15px; text-align: center; font-size: 12px; color: #666;">
                Sistema de Insignias TecNM - Outlook Test
                <p style="margin-top: 5px;">Tecnológico Nacional de México © ' . date('Y') . '</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    $mail->AltBody = 'Prueba exitosa de PHPMailer con Outlook - Sistema de Insignias TecNM funcionando correctamente.';

    // Enviar el correo
    $mail->send();
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎉 ¡ÉXITO TOTAL CON OUTLOOK!</h4>";
    echo "<p><strong>✅ Correo enviado exitosamente con PHPMailer + Outlook</strong></p>";
    echo "<p>El correo se envió a: <strong>" . htmlspecialchars($correo_destino) . "</strong></p>";
    echo "<p>Revisa tu bandeja de entrada y la carpeta de spam.</p>";
    echo "<p><strong>El sistema está 100% funcional con Outlook.</strong></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Error con PHPMailer + Outlook</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Posibles soluciones:</strong></p>";
    echo "<ul>";
    echo "<li>Verifica que tu contraseña de Outlook sea correcta</li>";
    echo "<li>Asegúrate de que la verificación en 2 pasos esté activada</li>";
    echo "<li>Revisa que no haya firewall bloqueando el puerto 587</li>";
    echo "<li>Verifica que tu correo tenga permisos para enviar correos</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h3>🔧 Información Técnica para Outlook:</h3>";
echo "<p><strong>SMTP Host:</strong> smtp-mail.outlook.com</p>";
echo "<p><strong>Puerto:</strong> 587</p>";
echo "<p><strong>Seguridad:</strong> STARTTLS</p>";
echo "<p><strong>Autenticación:</strong> Sí</p>";

echo "<h3>📋 Próximos pasos:</h3>";
echo "<p>1. Si funcionó, actualiza <code>metadatos_formulario.php</code> para usar <code>funciones_correo_outlook.php</code></p>";
echo "<p>2. Prueba el formulario completo</p>";
echo "<p>3. ¡El sistema estará 100% funcional con Outlook!</p>";

echo "<hr>";
echo "<p><a href='metadatos_formulario.php'>← Volver al formulario de insignias</a></p>";
echo "<p><a href='probar_correo_simple.php'>← Probar correo básico</a></p>";
?>
