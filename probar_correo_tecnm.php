<?php
/**
 * Configuración de correos para TecNM - SOLUCIÓN DEFINITIVA
 * Este archivo está configurado específicamente para smarcos.tecnm.mx
 */

// Verificar si PHPMailer está disponible
if (!file_exists('src/PHPMailer.php')) {
    echo "<h2>❌ PHPMailer no encontrado</h2>";
    echo "<p>Necesitas instalar PHPMailer primero:</p>";
    echo "<ol>";
    echo "<li>Descarga PHPMailer desde: <a href='https://github.com/PHPMailer/PHPMailer' target='_blank'>https://github.com/PHPMailer/PHPMailer</a></li>";
    echo "<li>Extrae la carpeta 'src' en tu proyecto</li>";
    echo "</ol>";
    exit;
}

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

echo "<h2>📧 Prueba de Correo para TecNM</h2>";

// CONFIGURACIÓN PARA TECNM
$tu_correo = "211230001@smarcos.tecnm.mx"; // Tu correo del TecNM (remitente)
$tu_contraseña = "tu-contraseña-tecnm"; // Tu contraseña del TecNM

// Correo de destino - CAMBIA ESTE POR EL CORREO QUE QUIERAS PROBAR
$correo_destino = "211230002@smarcos.tecnm.mx"; // Ejemplo: otro estudiante del TecNM

echo "<h3>🔧 Configuración para TecNM:</h3>";
echo "<p><strong>Tu correo:</strong> " . htmlspecialchars($tu_correo) . "</p>";
echo "<p><strong>Correo destino:</strong> " . htmlspecialchars($correo_destino) . "</p>";
echo "<p><strong>Dominio:</strong> smarcos.tecnm.mx</p>";

if ($tu_contraseña === "tu-contraseña-tecnm") {
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>⚠️ Configuración Pendiente</h4>";
    echo "<p>Necesitas configurar tu contraseña del TecNM:</p>";
    echo "<ol>";
    echo "<li>Usa tu contraseña normal del TecNM (la misma que usas para Teams)</li>";
    echo "<li>O genera una contraseña de aplicación si está disponible</li>";
    echo "<li>Reemplaza 'tu-contraseña-tecnm' en este archivo</li>";
    echo "</ol>";
    echo "</div>";
    exit;
}

echo "<h3>📤 Probando diferentes servidores SMTP del TecNM...</h3>";

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
            $mail->clearAttachments();
            
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
            $mail->addAddress($correo_destino, 'Usuario de Prueba');

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = '🎖️ Prueba TecNM - ' . $servidor . ':' . $puerto;
            
            $mail->Body = '
            <div style="font-family: Arial, sans-serif; padding: 20px;">
                <h2 style="color: #1b396a;">🎖️ Sistema de Insignias TecNM</h2>
                <p><strong>¡Éxito!</strong> Este correo fue enviado usando:</p>
                <ul>
                    <li><strong>Servidor:</strong> ' . $servidor . '</li>
                    <li><strong>Puerto:</strong> ' . $puerto . '</li>
                    <li><strong>Correo:</strong> ' . $tu_correo . '</li>
                    <li><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</li>
                </ul>
                <p>El sistema está funcionando correctamente con esta configuración.</p>
            </div>
            ';

            // Enviar el correo
            $mail->send();
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>🎉 ¡ÉXITO!</h4>";
            echo "<p><strong>✅ Correo enviado exitosamente</strong></p>";
            echo "<p><strong>Servidor:</strong> $servidor</p>";
            echo "<p><strong>Puerto:</strong> $puerto</p>";
            echo "<p><strong>Correo enviado a:</strong> " . htmlspecialchars($correo_destino) . "</p>";
            echo "<p>Revisa tu bandeja de entrada y la carpeta de spam.</p>";
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

echo "<h3>💡 Información para el administrador de TI:</h3>";
echo "<p>Si necesitas la configuración SMTP correcta, contacta al administrador de TI del TecNM y pregunta por:</p>";
echo "<ul>";
echo "<li>Servidor SMTP para el dominio smarcos.tecnm.mx</li>";
echo "<li>Puerto SMTP (587, 465, o 25)</li>";
echo "<li>Tipo de seguridad (STARTTLS, SMTPS, o ninguna)</li>";
echo "<li>Si requiere autenticación</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='metadatos_formulario.php'>← Volver al formulario de insignias</a></p>";
echo "<p><a href='probar_correo_simulacion.php'>← Usar simulación para desarrollo</a></p>";
?>
