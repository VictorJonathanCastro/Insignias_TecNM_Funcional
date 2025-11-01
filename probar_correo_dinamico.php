<?php
/**
 * Prueba de correo dinámico para TecNM
 * Permite probar con cualquier correo de destino
 */

// Verificar si PHPMailer está disponible
if (!file_exists('src/PHPMailer.php')) {
    echo "<h2>❌ PHPMailer no encontrado</h2>";
    echo "<p>Necesitas instalar PHPMailer primero.</p>";
    exit;
}

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

echo "<h2>📧 Prueba de Correo Dinámico para TecNM</h2>";

// CONFIGURACIÓN PARA TECNM
$tu_correo = "211230001@smarcos.tecnm.mx"; // Tu correo del TecNM (remitente)
$tu_contraseña = "tu-contraseña-tecnm"; // Tu contraseña del TecNM

// Obtener correo de destino desde URL o usar por defecto
$correo_destino = isset($_GET['correo']) ? $_GET['correo'] : "211230002@smarcos.tecnm.mx";

echo "<h3>🔧 Configuración:</h3>";
echo "<p><strong>Remitente:</strong> " . htmlspecialchars($tu_correo) . "</p>";
echo "<p><strong>Destinatario:</strong> " . htmlspecialchars($correo_destino) . "</p>";
echo "<p><strong>Dominio:</strong> smarcos.tecnm.mx</p>";

// Formulario para cambiar correo de destino
echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>📝 Cambiar Correo de Destino:</h4>";
echo "<form method='GET' style='display: inline-block;'>";
echo "<input type='email' name='correo' value='" . htmlspecialchars($correo_destino) . "' placeholder='ejemplo@smarcos.tecnm.mx' style='padding: 8px; margin-right: 10px; width: 250px;'>";
echo "<button type='submit' style='padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 3px;'>Probar con este correo</button>";
echo "</form>";
echo "</div>";

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

echo "<h3>📤 Probando envío a: " . htmlspecialchars($correo_destino) . "</h3>";

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
$servidor_exitoso = '';
$puerto_exitoso = '';

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
            $mail->addAddress($correo_destino, 'Estudiante TecNM');

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
                    <li><strong>Remitente:</strong> ' . $tu_correo . '</li>
                    <li><strong>Destinatario:</strong> ' . $correo_destino . '</li>
                    <li><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</li>
                </ul>
                <p>El sistema está funcionando correctamente con esta configuración.</p>
                <p><strong>Prueba:</strong> Insignia de Excelencia Académica</p>
                <p><strong>Código:</strong> INS-2024-001</p>
            </div>
            ';

            // Enviar el correo
            $mail->send();
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>🎉 ¡ÉXITO!</h4>";
            echo "<p><strong>✅ Correo enviado exitosamente</strong></p>";
            echo "<p><strong>Servidor:</strong> $servidor</p>";
            echo "<p><strong>Puerto:</strong> $puerto</p>";
            echo "<p><strong>Remitente:</strong> " . htmlspecialchars($tu_correo) . "</p>";
            echo "<p><strong>Destinatario:</strong> " . htmlspecialchars($correo_destino) . "</p>";
            echo "<p>Revisa la bandeja de entrada y la carpeta de spam del destinatario.</p>";
            echo "</div>";
            
            $funciono = true;
            $servidor_exitoso = $servidor;
            $puerto_exitoso = $puerto;
            break 2; // Salir de ambos bucles
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<p><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
}

if ($funciono) {
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎯 Configuración Exitosa Encontrada:</h4>";
    echo "<p><strong>Servidor SMTP:</strong> $servidor_exitoso</p>";
    echo "<p><strong>Puerto:</strong> $puerto_exitoso</p>";
    echo "<p><strong>Remitente:</strong> " . htmlspecialchars($tu_correo) . "</p>";
    echo "<p>Esta configuración funcionará para enviar correos a cualquier estudiante del TecNM.</p>";
    echo "</div>";
    
    echo "<h3>📋 Para usar en el formulario:</h3>";
    echo "<p>Actualiza <code>metadatos_formulario.php</code> para usar <code>funciones_correo_tecnm.php</code></p>";
    echo "<p>El sistema enviará correos automáticamente a cualquier correo que se ingrese en el formulario.</p>";
} else {
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

echo "<h3>💡 Ejemplos de correos para probar:</h3>";
echo "<ul>";
echo "<li><a href='?correo=211230001@smarcos.tecnm.mx'>211230001@smarcos.tecnm.mx</a></li>";
echo "<li><a href='?correo=211230002@smarcos.tecnm.mx'>211230002@smarcos.tecnm.mx</a></li>";
echo "<li><a href='?correo=211230003@smarcos.tecnm.mx'>211230003@smarcos.tecnm.mx</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='metadatos_formulario.php'>← Volver al formulario de insignias</a></p>";
echo "<p><a href='probar_correo_simulacion.php'>← Usar simulación para desarrollo</a></p>";
?>
