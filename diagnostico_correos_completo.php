<?php
/**
 * DIAGNÓSTICO COMPLETO DEL SISTEMA DE CORREOS
 * Este archivo diagnostica por qué no llegan los correos reales
 */

echo "<h2>🔍 DIAGNÓSTICO COMPLETO DEL SISTEMA DE CORREOS</h2>";
echo "<h3>📧 Verificando configuración SMTP y envío real</h3>";

// Verificar si PHPMailer está disponible
if (!file_exists('src/PHPMailer.php')) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ PHPMailer no encontrado</h4>";
    echo "<p>PHPMailer no está instalado. El sistema solo puede usar simulación.</p>";
    echo "</div>";
    exit;
}

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>✅ PHPMailer encontrado</h4>";
echo "<p>PHPMailer está disponible. Procediendo con diagnóstico...</p>";
echo "</div>";

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

echo "<h3>🔧 Probando configuración SMTP...</h3>";

// CONFIGURACIÓN
$tu_correo = "211230001@smarcos.tecnm.mx";
$tu_contraseña = "cas29ye02vi20";

echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔧 Configuración:</h4>";
echo "<p><strong>Correo:</strong> " . htmlspecialchars($tu_correo) . "</p>";
echo "<p><strong>Contraseña:</strong> " . str_repeat('*', strlen($tu_contraseña)) . "</p>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

// Servidores SMTP para TecNM
$servidores = [
    'smtp-mail.outlook.com' => 587,  // Office 365
    'smtp.tecnm.mx' => 587,         // TecNM directo
    'mail.tecnm.mx' => 587,         // TecNM mail
    'smtp.smarcos.tecnm.mx' => 587, // TecNM específico
];

$funciono = false;
$servidor_exitoso = '';
$errores_detallados = [];

foreach ($servidores as $servidor => $puerto) {
    echo "<h4>🔍 Probando: $servidor:$puerto</h4>";
    
    try {
        $mail = new PHPMailer(true);
        $mail->clearAddresses();
        
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = $servidor;
        $mail->SMTPAuth = true;
        $mail->Username = $tu_correo;
        $mail->Password = $tu_contraseña;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $puerto;
        $mail->CharSet = 'UTF-8';
        
        // SSL para XAMPP
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Configurar correo de prueba
        $mail->setFrom($tu_correo, 'Sistema Insignias TecNM');
        $mail->addAddress($tu_correo, 'Usuario TecNM');

        // Contenido de prueba
        $mail->isHTML(true);
        $mail->Subject = '🎖️ PRUEBA DIAGNÓSTICO - TecNM';
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; padding: 20px; max-width: 500px; margin: 0 auto;">
            <div style="background: #1b396a; color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center;">
                <h1 style="margin: 0;">🎖️ TECNM</h1>
                <p style="margin: 10px 0 0 0;">PRUEBA DE DIAGNÓSTICO</p>
            </div>
            <div style="background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px;">
                <h2 style="color: #1b396a;">¡Diagnóstico Exitoso!</h2>
                <p>Este es un correo de <strong>PRUEBA DE DIAGNÓSTICO</strong>.</p>
                <p>Si recibiste este correo, el sistema SMTP está funcionando correctamente.</p>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
                    <p><strong>Servidor:</strong> ' . $servidor . '</p>
                    <p><strong>Puerto:</strong> ' . $puerto . '</p>
                    <p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>
                    <p><strong>Correo:</strong> ' . $tu_correo . '</p>
                </div>
                
                <p style="text-align: center; color: #666;">
                    <strong>Tecnológico Nacional de México</strong>
                </p>
            </div>
        </div>
        ';

        // Intentar conexión SMTP
        $mail->smtpConnect();
        
        echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p><strong>✅ Conexión SMTP exitosa</strong></p>";
        echo "<p><strong>Servidor:</strong> $servidor</p>";
        echo "<p><strong>Puerto:</strong> $puerto</p>";
        echo "</div>";
        
        // Enviar correo
        $mail->send();
        
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>🎉 ¡ÉXITO!</h4>";
        echo "<p><strong>✅ Correo enviado correctamente</strong></p>";
        echo "<p><strong>Servidor:</strong> $servidor</p>";
        echo "<p><strong>Puerto:</strong> $puerto</p>";
        echo "<p><strong>Destinatario:</strong> " . htmlspecialchars($tu_correo) . "</p>";
        echo "<p><strong>Asunto:</strong> 🎖️ PRUEBA DIAGNÓSTICO - TecNM</p>";
        echo "</div>";
        
        echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>📧 ¿Dónde revisar?</h4>";
        echo "<p><strong>1. Bandeja de entrada:</strong> Revisa tu Outlook</p>";
        echo "<p><strong>2. Carpeta de spam:</strong> A veces va ahí</p>";
        echo "<p><strong>3. Busca:</strong> 🎖️ PRUEBA DIAGNÓSTICO - TecNM</p>";
        echo "</div>";
        
        $funciono = true;
        $servidor_exitoso = $servidor;
        break;
        
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
        $errores_detallados[$servidor] = $error_msg;
        
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p><strong>❌ Error con $servidor:$puerto</strong></p>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($error_msg) . "</p>";
        echo "</div>";
    }
}

if ($funciono) {
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎯 CONFIGURACIÓN EXITOSA:</h4>";
    echo "<p><strong>Servidor SMTP:</strong> $servidor_exitoso</p>";
    echo "<p><strong>Puerto:</strong> 587</p>";
    echo "<p><strong>Correo:</strong> " . htmlspecialchars($tu_correo) . "</p>";
    echo "<p>Esta configuración funcionará para el sistema completo.</p>";
    echo "</div>";
    
    echo "<h3>🚀 PRÓXIMO PASO:</h3>";
    echo "<p>Ahora puedes usar el formulario completo y los correos llegarán realmente:</p>";
    echo "<p><a href='probar_insignia_yeni_directo.php' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>🎖️ Crear Insignia para Yeni Castro Sánchez</a></p>";
    
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ No funcionó ningún servidor</h4>";
    echo "<p>Posibles causas:</p>";
    echo "<ul>";
    echo "<li>Contraseña incorrecta</li>";
    echo "<li>Servidores SMTP bloqueados por firewall</li>";
    echo "<li>Configuración del TecNM diferente</li>";
    echo "<li>Necesitas contraseña de aplicación</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h4>🔍 Errores detallados:</h4>";
    foreach ($errores_detallados as $servidor => $error) {
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p><strong>$servidor:</strong> " . htmlspecialchars($error) . "</p>";
        echo "</div>";
    }
}

echo "<h3>🔄 Probar Nuevamente:</h3>";
echo "<p><a href='diagnostico_correos_completo.php' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>🔄 Ejecutar Diagnóstico Nuevamente</a></p>";

echo "<hr>";
echo "<p><a href='prueba_simple.php'>← Volver a prueba simple</a></p>";
echo "<p><a href='probar_insignia_yeni_directo.php'>← Crear insignia para Yeni</a></p>";
?>
