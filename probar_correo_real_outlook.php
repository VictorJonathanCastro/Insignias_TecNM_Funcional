<?php
/**
 * Prueba REAL de correo a Outlook - TecNM
 * Este archivo intentará enviar correo REAL a tu Outlook
 */

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

echo "<h2>📧 Prueba REAL de Correo a tu Outlook</h2>";
echo "<h3>🎯 Enviando correo REAL a: 211230001@smarcos.tecnm.mx</h3>";

// CONFIGURACIÓN PARA TECNM
$tu_correo = "211230001@smarcos.tecnm.mx"; // Tu correo del TecNM
$tu_contraseña = "cas29ye02vi20"; // Tu contraseña real del TecNM

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>⚠️ IMPORTANTE - CONFIGURAR CONTRASEÑA</h4>";
echo "<p>Para que llegue el correo REAL a tu Outlook, necesitas:</p>";
echo "<ol>";
echo "<li><strong>Usar tu contraseña real</strong> del TecNM (la misma que usas para Teams)</li>";
echo "<li><strong>Cambiar</strong> 'tu-contraseña-tecnm' por tu contraseña real</li>";
echo "<li><strong>O generar</strong> una contraseña de aplicación si está disponible</li>";
echo "</ol>";
echo "</div>";

if ($tu_contraseña === "tu-contraseña-tecnm") {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Configuración Pendiente</h4>";
    echo "<p><strong>Debes configurar tu contraseña real del TecNM</strong></p>";
    echo "<p>Edita este archivo y cambia 'tu-contraseña-tecnm' por tu contraseña real.</p>";
    echo "</div>";
    exit;
}

// Lista de servidores SMTP posibles para TecNM
$servidores_smtp = [
    'smtp.tecnm.mx',
    'mail.tecnm.mx', 
    'smtp.smarcos.tecnm.mx',
    'mail.smarcos.tecnm.mx',
    'smtp-mail.outlook.com', // Por si usa Office 365
    'smtp.gmail.com' // Por si usa Gmail
];

$puertos = [587, 465, 25];

$mail = new PHPMailer(true);
$funciono = false;

foreach ($servidores_smtp as $servidor) {
    foreach ($puertos as $puerto) {
        echo "<h4>🔍 Probando: $servidor:$puerto</h4>";
        
        try {
            $mail->clearAddresses();
            
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $servidor;
            $mail->SMTPAuth = true;
            $mail->Username = $tu_correo;
            $mail->Password = $tu_contraseña;
            
            if ($puerto == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port = $puerto;
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
            $mail->addAddress($tu_correo, 'Usuario TecNM'); // Enviar a ti mismo

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = '🎖️ PRUEBA REAL - Sistema Insignias TecNM';
            
            $mail->Body = '
            <div style="font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto;">
                <div style="background: linear-gradient(135deg, #1b396a, #002855); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center;">
                    <h1 style="margin: 0;">🎖️ SISTEMA DE INSIGNIAS TECNM</h1>
                    <p style="margin: 10px 0 0 0;">¡PRUEBA REAL EXITOSA!</p>
                </div>
                <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
                    <h2 style="color: #1b396a;">¡Felicidades!</h2>
                    <p>Este es un correo de <strong>PRUEBA REAL</strong> del Sistema de Insignias TecNM.</p>
                    <p>Si recibiste este correo, significa que el sistema está funcionando correctamente.</p>
                    
                    <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="color: #1b396a;">📋 Información de la Prueba:</h3>
                        <ul>
                            <li><strong>Estudiante:</strong> 211230001 (Usuario de Prueba)</li>
                            <li><strong>Matrícula:</strong> 211230001</li>
                            <li><strong>CURP:</strong> PERJ800101HDFRGN01</li>
                            <li><strong>Insignia:</strong> Excelencia Académica</li>
                            <li><strong>Categoría:</strong> Formación Integral</li>
                            <li><strong>Código:</strong> INS-PRUEBA-REAL-001</li>
                            <li><strong>Período:</strong> Enero-Diciembre 2024</li>
                            <li><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</li>
                            <li><strong>Servidor usado:</strong> ' . $servidor . ':' . $puerto . '</li>
                        </ul>
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="#" style="display: inline-block; background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                            🔍 Verificar Insignia
                        </a>
                    </div>
                    
                    <p style="text-align: center; color: #666; font-size: 14px;">
                        <strong>Tecnológico Nacional de México</strong><br>
                        Sistema de Insignias TecNM - Prueba Real
                    </p>
                </div>
            </div>
            ';

            // Enviar el correo
            $mail->send();
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>🎉 ¡CORREO REAL ENVIADO!</h4>";
            echo "<p><strong>✅ Correo enviado exitosamente</strong></p>";
            echo "<p><strong>Servidor:</strong> $servidor</p>";
            echo "<p><strong>Puerto:</strong> $puerto</p>";
            echo "<p><strong>Destinatario:</strong> " . htmlspecialchars($tu_correo) . "</p>";
            echo "<p><strong>Asunto:</strong> 🎖️ PRUEBA REAL - Sistema Insignias TecNM</p>";
            echo "<p><strong>Estado:</strong> El correo REAL se envió a tu Outlook</p>";
            echo "</div>";
            
            echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4>📧 ¿Dónde revisar?</h4>";
            echo "<p><strong>1. Bandeja de entrada:</strong> Revisa tu Outlook " . htmlspecialchars($tu_correo) . "</p>";
            echo "<p><strong>2. Carpeta de spam:</strong> A veces los correos automáticos van al spam</p>";
            echo "<p><strong>3. Busca el asunto:</strong> 🎖️ PRUEBA REAL - Sistema Insignias TecNM</p>";
            echo "</div>";
            
            $funciono = true;
            break 2; // Salir de ambos bucles
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<p><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
}

if (!$funciono) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Ningún servidor funcionó</h4>";
    echo "<p>Posibles soluciones:</p>";
    echo "<ul>";
    echo "<li>Verifica que tu contraseña del TecNM sea correcta</li>";
    echo "<li>Contacta al administrador de TI del TecNM para obtener la configuración SMTP correcta</li>";
    echo "<li>Usa la función de simulación para desarrollo</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h3>💡 Información:</h3>";
echo "<p>Esta prueba intenta enviar un correo REAL a tu Outlook usando diferentes servidores SMTP del TecNM.</p>";
echo "<p>Si funciona, recibirás el correo en tu bandeja de entrada.</p>";

echo "<hr>";
echo "<p><a href='probar_correo_personal.php'>← Volver a simulación</a></p>";
echo "<p><a href='metadatos_formulario.php'>← Formulario de insignias</a></p>";
?>
