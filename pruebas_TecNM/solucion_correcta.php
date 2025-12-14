<?php
/**
 * SOLUCIÓN CORRECTA - SERVICIO DE CORREO GRATUITO
 * Este archivo usa servicios gratuitos para enviar correos sin necesidad de credenciales propias
 */

echo "<h2>📧 SOLUCIÓN CORRECTA - SERVICIO DE CORREO GRATUITO</h2>";
echo "<h3>🔧 Usando servicios que NO requieren credenciales del destinatario</h3>";

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

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>💡 Concepto Correcto:</h4>";
echo "<p><strong>Para NOTIFICAR:</strong> No necesitamos la contraseña del destinatario</p>";
echo "<p><strong>Necesitamos:</strong> Un servidor SMTP que funcione para enviar</p>";
echo "<p><strong>Solución:</strong> Usar servicios gratuitos o servidor local</p>";
echo "</div>";

// CONFIGURACIONES DE SERVICIOS GRATUITOS
$servicios_gratuitos = [
    [
        'nombre' => 'Mailtrap (Pruebas)',
        'servidor' => 'smtp.mailtrap.io',
        'puerto' => 2525,
        'usuario' => 'tu_usuario_mailtrap',
        'contraseña' => 'tu_contraseña_mailtrap',
        'descripcion' => 'Servicio gratuito para pruebas de correo'
    ],
    [
        'nombre' => 'SendGrid (Gratuito)',
        'servidor' => 'smtp.sendgrid.net',
        'puerto' => 587,
        'usuario' => 'apikey',
        'contraseña' => 'tu_api_key_sendgrid',
        'descripcion' => '100 correos gratis por día'
    ],
    [
        'nombre' => 'Mailgun (Gratuito)',
        'servidor' => 'smtp.mailgun.org',
        'puerto' => 587,
        'usuario' => 'tu_usuario_mailgun',
        'contraseña' => 'tu_contraseña_mailgun',
        'descripcion' => '10,000 correos gratis por mes'
    ],
    [
        'nombre' => 'SMTP Local XAMPP',
        'servidor' => 'localhost',
        'puerto' => 25,
        'usuario' => '',
        'contraseña' => '',
        'descripcion' => 'Servidor SMTP local de XAMPP'
    ]
];

echo "<h3>🔧 Configuraciones Disponibles:</h3>";

foreach ($servicios_gratuitos as $index => $servicio) {
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>" . ($index + 1) . ". " . $servicio['nombre'] . "</h4>";
    echo "<p><strong>Servidor:</strong> " . $servicio['servidor'] . "</p>";
    echo "<p><strong>Puerto:</strong> " . $servicio['puerto'] . "</p>";
    echo "<p><strong>Descripción:</strong> " . $servicio['descripcion'] . "</p>";
    echo "<p><strong>Estado:</strong> <span style='color: orange;'>Requiere configuración</span></p>";
    echo "</div>";
}

echo "<h3>🚀 SOLUCIÓN INMEDIATA - USAR FUNCIÓN MAIL() NATIVA:</h3>";

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>✅ Probando función mail() nativa de PHP:</h4>";
echo "<p>Esta función usa el servidor SMTP configurado en XAMPP</p>";
echo "</div>";

// Probar función mail() nativa
$destinatario = "211230001@smarcos.tecnm.mx";
$asunto = "🎖️ PRUEBA MAIL NATIVO - TecNM";
$mensaje = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Prueba Mail Nativo</title>
</head>
<body style='font-family: Arial, sans-serif; padding: 20px;'>
    <div style='background: #1b396a; color: white; padding: 20px; border-radius: 10px; text-align: center;'>
        <h1>🎖️ TECNM</h1>
        <p>PRUEBA MAIL NATIVO</p>
    </div>
    <div style='background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px;'>
        <h2 style='color: #1b396a;'>¡Mail Nativo Funciona!</h2>
        <p>Esta prueba usa la función <strong>mail()</strong> nativa de PHP.</p>
        <p>No requiere credenciales externas.</p>
        
        <div style='background: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
            <p><strong>Método:</strong> mail() nativo de PHP</p>
            <p><strong>Servidor:</strong> Configurado en XAMPP</p>
            <p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>
            <p><strong>Destinatario:</strong> " . $destinatario . "</p>
        </div>
        
        <p style='text-align: center; color: #666;'>
            <strong>Tecnológico Nacional de México</strong>
        </p>
    </div>
</body>
</html>
";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
$headers .= "From: Sistema Insignias TecNM <noreply@tecnm.mx>" . "\r\n";
$headers .= "Reply-To: noreply@tecnm.mx" . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

echo "<h4>📤 Enviando correo con mail() nativo...</h4>";

$resultado_mail = mail($destinatario, $asunto, $mensaje, $headers);

if ($resultado_mail) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎉 ¡ÉXITO!</h4>";
    echo "<p><strong>✅ Correo enviado con mail() nativo</strong></p>";
    echo "<p><strong>✅ No requiere credenciales externas</strong></p>";
    echo "<p><strong>✅ Usa configuración de XAMPP</strong></p>";
    echo "<p><strong>Destinatario:</strong> " . $destinatario . "</p>";
    echo "<p><strong>Asunto:</strong> " . $asunto . "</p>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>📧 ¿Dónde revisar?</h4>";
    echo "<p><strong>1. Bandeja de entrada:</strong> Revisa tu correo</p>";
    echo "<p><strong>2. Carpeta de spam:</strong> A veces va ahí</p>";
    echo "<p><strong>3. Busca:</strong> 🎖️ PRUEBA MAIL NATIVO - TecNM</p>";
    echo "</div>";
    
    echo "<h3>🚀 PRÓXIMO PASO:</h3>";
    echo "<p>Ahora puedes usar el sistema completo con mail() nativo:</p>";
    echo "<p><a href='probar_insignia_yeni_directo.php' style='display: inline-block; background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold;'>🎖️ Crear Insignia para Yeni Castro Sánchez</a></p>";
    
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ mail() nativo no funcionó</h4>";
    echo "<p>La función mail() nativa no está configurada en XAMPP.</p>";
    echo "<p><strong>Soluciones:</strong></p>";
    echo "<ul>";
    echo "<li>Configurar servidor SMTP en XAMPP</li>";
    echo "<li>Usar servicio gratuito como SendGrid</li>";
    echo "<li>Usar el sistema con simulación</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h3>🔧 CONFIGURAR XAMPP PARA CORREOS:</h3>";
echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>📋 Pasos para configurar XAMPP:</h4>";
echo "<ol>";
echo "<li>Abre el archivo <strong>php.ini</strong> en XAMPP</li>";
echo "<li>Busca la línea <strong>sendmail_path</strong></li>";
echo "<li>Configúrala con un servidor SMTP gratuito</li>";
echo "<li>Reinicia Apache en XAMPP</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🌐 SERVICIOS GRATUITOS RECOMENDADOS:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>1. SendGrid (Recomendado)</h4>";
echo "<ul>";
echo "<li><strong>Gratis:</strong> 100 correos por día</li>";
echo "<li><strong>Registro:</strong> sendgrid.com</li>";
echo "<li><strong>Configuración:</strong> smtp.sendgrid.net:587</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>2. Mailgun</h4>";
echo "<ul>";
echo "<li><strong>Gratis:</strong> 10,000 correos por mes</li>";
echo "<li><strong>Registro:</strong> mailgun.com</li>";
echo "<li><strong>Configuración:</strong> smtp.mailgun.org:587</li>";
echo "</ul>";
echo "</div>";

echo "<h3>🔄 Probar Nuevamente:</h3>";
echo "<p><a href='solucion_correcta.php' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>🔄 Ejecutar Prueba Nuevamente</a></p>";

echo "<hr>";
echo "<p><a href='probar_insignia_yeni_directo.php'>← Crear insignia para Yeni</a></p>";
echo "<p><a href='solucion_universal.php'>← Prueba universal</a></p>";

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Estado:</strong> <span style='color: blue; font-weight: bold;'>USANDO MÉTODO CORRECTO</span></p>";
?>
