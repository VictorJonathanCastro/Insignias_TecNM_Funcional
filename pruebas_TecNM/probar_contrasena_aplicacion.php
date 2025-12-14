<?php
/**
 * PROBAR CON CONTRASEÑA DE APLICACIÓN
 * Este archivo te permite probar con tu nueva contraseña de aplicación
 */

echo "<h2>🔐 PROBAR CON CONTRASEÑA DE APLICACIÓN</h2>";
echo "<h3>📧 Ingresa tu contraseña de aplicación generada</h3>";

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

// CONFIGURACIÓN
$tu_correo = "211230001@smarcos.tecnm.mx";

echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔧 Configuración:</h4>";
echo "<p><strong>Correo:</strong> " . htmlspecialchars($tu_correo) . "</p>";
echo "<p><strong>Servidor:</strong> smtp-mail.outlook.com</p>";
echo "<p><strong>Puerto:</strong> 587</p>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

// Verificar si se envió el formulario
if ($_POST && isset($_POST['contrasena_aplicacion'])) {
    $contrasena_aplicacion = $_POST['contrasena_aplicacion'];
    
    echo "<h3>🔍 Probando con contraseña de aplicación...</h3>";
    
    try {
        $mail = new PHPMailer(true);
        
        // Configuración específica para Outlook con contraseña de aplicación
        $mail->isSMTP();
        $mail->Host = 'smtp-mail.outlook.com';
        $mail->SMTPAuth = true;
        $mail->Username = $tu_correo;
        $mail->Password = $contrasena_aplicacion;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
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
        $mail->setFrom($tu_correo, 'Sistema Insignias TecNM');
        $mail->addAddress($tu_correo, 'Usuario TecNM');

        // Contenido específico
        $mail->isHTML(true);
        $mail->Subject = '🎖️ PRUEBA CONTRASEÑA APLICACIÓN - TecNM';
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; padding: 20px; max-width: 500px; margin: 0 auto;">
            <div style="background: #1b396a; color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center;">
                <h1 style="margin: 0;">🎖️ TECNM</h1>
                <p style="margin: 10px 0 0 0;">CONTRASEÑA APLICACIÓN FUNCIONA</p>
            </div>
            <div style="background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px;">
                <h2 style="color: #1b396a;">¡Éxito Total!</h2>
                <p>Este correo confirma que la <strong>contraseña de aplicación</strong> funciona correctamente.</p>
                <p>Ahora el sistema de insignias puede enviar correos reales.</p>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
                    <p><strong>Servidor:</strong> smtp-mail.outlook.com</p>
                    <p><strong>Puerto:</strong> 587</p>
                    <p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>
                    <p><strong>Correo:</strong> ' . $tu_correo . '</p>
                    <p><strong>Estado:</strong> ✅ Funcionando</p>
                </div>
                
                <p style="text-align: center; color: #666;">
                    <strong>Tecnológico Nacional de México</strong>
                </p>
            </div>
        </div>
        ';

        // Enviar
        $mail->send();
        
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>🎉 ¡ÉXITO TOTAL!</h4>";
        echo "<p><strong>✅ Correo enviado correctamente</strong></p>";
        echo "<p><strong>✅ Contraseña de aplicación funciona</strong></p>";
        echo "<p><strong>✅ Sistema listo para producción</strong></p>";
        echo "<p><strong>Servidor:</strong> smtp-mail.outlook.com</p>";
        echo "<p><strong>Puerto:</strong> 587</p>";
        echo "<p><strong>Destinatario:</strong> " . htmlspecialchars($tu_correo) . "</p>";
        echo "<p><strong>Asunto:</strong> 🎖️ PRUEBA CONTRASEÑA APLICACIÓN - TecNM</p>";
        echo "</div>";
        
        echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>📧 ¿Dónde revisar?</h4>";
        echo "<p><strong>1. Bandeja de entrada:</strong> Revisa tu Outlook</p>";
        echo "<p><strong>2. Carpeta de spam:</strong> A veces va ahí</p>";
        echo "<p><strong>3. Busca:</strong> 🎖️ PRUEBA CONTRASEÑA APLICACIÓN - TecNM</p>";
        echo "</div>";
        
        echo "<h3>🚀 PRÓXIMO PASO:</h3>";
        echo "<p>Ahora puedes usar el sistema completo y los correos llegarán realmente:</p>";
        echo "<p><a href='probar_insignia_yeni_directo.php' style='display: inline-block; background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold;'>🎖️ Crear Insignia para Yeni Castro Sánchez</a></p>";
        
        echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>🔧 Configuración Exitosa:</h4>";
        echo "<p><strong>Servidor SMTP:</strong> smtp-mail.outlook.com</p>";
        echo "<p><strong>Puerto:</strong> 587</p>";
        echo "<p><strong>Correo:</strong> " . htmlspecialchars($tu_correo) . "</p>";
        echo "<p><strong>Contraseña:</strong> [Tu contraseña de aplicación]</p>";
        echo "<p>Esta configuración funcionará para el sistema completo.</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>❌ Error con contraseña de aplicación</h4>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Posibles causas:</strong></p>";
        echo "<ul>";
        echo "<li>Contraseña de aplicación incorrecta</li>";
        echo "<li>Contraseña copiada con espacios extra</li>";
        echo "<li>Verificación en dos pasos no activada</li>";
        echo "<li>Cuenta bloqueada temporalmente</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<h4>🔧 Soluciones:</h4>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<ol>";
        echo "<li>Verifica que copiaste la contraseña correctamente (sin espacios extra)</li>";
        echo "<li>Genera una nueva contraseña de aplicación</li>";
        echo "<li>Espera 5 minutos y vuelve a intentar</li>";
        echo "<li>Verifica que tienes verificación en dos pasos activada</li>";
        echo "</ol>";
        echo "</div>";
    }
    
} else {
    // Mostrar formulario para ingresar contraseña
    echo "<h3>🔐 Ingresa tu contraseña de aplicación:</h3>";
    
    echo "<form method='POST' style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label for='contrasena_aplicacion' style='display: block; margin-bottom: 5px; font-weight: bold;'>Contraseña de aplicación (16 caracteres):</label>";
    echo "<input type='password' id='contrasena_aplicacion' name='contrasena_aplicacion' required style='width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 3px; font-size: 16px;' placeholder='Ejemplo: abcd efgh ijkl mnop'>";
    echo "</div>";
    echo "<button type='submit' style='background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer;'>🔐 Probar Contraseña de Aplicación</button>";
    echo "</form>";
    
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>⚠️ Instrucciones:</h4>";
    echo "<ul>";
    echo "<li>La contraseña de aplicación tiene 16 caracteres</li>";
    echo "<li>Puede tener espacios o no tenerlos</li>";
    echo "<li>Ejemplo: <strong>abcd efgh ijkl mnop</strong> o <strong>abcdefghijklmnop</strong></li>";
    echo "<li>Si no tienes una, ve a <a href='solucion_contrasena_aplicacion.php'>Generar Contraseña de Aplicación</a></li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔄 Enlaces útiles:</h3>";
echo "<p><a href='solucion_contrasena_aplicacion.php' style='display: inline-block; background: #17a2b8; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>🔐 Generar Contraseña de Aplicación</a></p>";
echo "<p><a href='prueba_simple.php' style='display: inline-block; background: #6c757d; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>📧 Prueba Simple Original</a></p>";
echo "<p><a href='probar_insignia_yeni_directo.php' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>🎖️ Crear Insignia para Yeni</a></p>";

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Estado:</strong> <span style='color: blue; font-weight: bold;'>ESPERANDO CONTRASEÑA DE APLICACIÓN</span></p>";
?>
