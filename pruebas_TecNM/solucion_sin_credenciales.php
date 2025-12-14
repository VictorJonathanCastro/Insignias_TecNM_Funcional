<?php
/**
 * SOLUCIÓN SIN CREDENCIALES DEL DESTINATARIO
 * Para notificar solo necesitamos una cuenta del sistema
 */

echo "<h2>📧 SOLUCIÓN SIN CREDENCIALES DEL DESTINATARIO</h2>";
echo "<h3>🎯 Solo necesitas una cuenta del SISTEMA para enviar notificaciones</h3>";

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>💡 Concepto Correcto:</h4>";
echo "<p><strong>❌ NO necesitas:</strong> Contraseña del destinatario</p>";
echo "<p><strong>✅ SÍ necesitas:</strong> Cuenta del sistema para enviar</p>";
echo "<p><strong>🎯 Objetivo:</strong> Notificar que recibió una insignia</p>";
echo "</div>";

echo "<h3>🚀 OPCIÓN 1: SERVICIO GRATUITO SIN CONFIGURACIÓN</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>📧 Mailtrap (Solo para pruebas):</h4>";
echo "<ul>";
echo "<li><strong>Gratis:</strong> Sin límites para pruebas</li>";
echo "<li><strong>Sin configuración:</strong> Solo copiar credenciales</li>";
echo "<li><strong>Registro:</strong> <a href='https://mailtrap.io' target='_blank'>mailtrap.io</a></li>";
echo "<li><strong>SMTP:</strong> smtp.mailtrap.io:2525</li>";
echo "</ul>";
echo "</div>";

echo "<h3>🔧 CONFIGURACIÓN AUTOMÁTICA:</h3>";

// Crear configuración con Mailtrap (servicio gratuito para pruebas)
$configuracion_mailtrap = "<?php\n";
$configuracion_mailtrap .= "// CONFIGURACIÓN MAILTRAP - GRATIS PARA PRUEBAS\n";
$configuracion_mailtrap .= "define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');\n";
$configuracion_mailtrap .= "define('SMTP_PORT', 2525);\n";
$configuracion_mailtrap .= "define('SMTP_USERNAME', 'tu_usuario_mailtrap');\n";
$configuracion_mailtrap .= "define('SMTP_PASSWORD', 'tu_password_mailtrap');\n";
$configuracion_mailtrap .= "define('SMTP_FROM_NAME', 'Sistema Insignias TecNM');\n";
$configuracion_mailtrap .= "define('SMTP_SECURE', '');\n";
$configuracion_mailtrap .= "?>";

file_put_contents('config_mailtrap.php', $configuracion_mailtrap);

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>✅ Archivo creado:</h4>";
echo "<p><strong>Archivo:</strong> config_mailtrap.php</p>";
echo "<p><strong>Servicio:</strong> Mailtrap (gratis para pruebas)</p>";
echo "</div>";

echo "<h3>📝 PASOS SIMPLES:</h3>";

echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔑 Paso 1: Registro en Mailtrap</h4>";
echo "<ol>";
echo "<li>Ve a <a href='https://mailtrap.io' target='_blank'>mailtrap.io</a></li>";
echo "<li>Regístrate gratis</li>";
echo "<li>Ve a 'Inboxes' → 'Demo Inbox'</li>";
echo "<li>Copia las credenciales SMTP</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔑 Paso 2: Actualizar configuración</h4>";
echo "<ol>";
echo "<li>Edita <strong>config_mailtrap.php</strong></li>";
echo "<li>Cambia <strong>tu_usuario_mailtrap</strong> por tu usuario real</li>";
echo "<li>Cambia <strong>tu_password_mailtrap</strong> por tu password real</li>";
echo "<li>Guarda el archivo</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🧪 PROBAR CONFIGURACIÓN:</h3>";

echo "<p><a href='probar_mailtrap.php' style='display: inline-block; background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold;'>📧 Probar Mailtrap</a></p>";

echo "<h3>🌐 OPCIÓN 2: SERVICIO PÚBLICO GRATUITO</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>📧 SendGrid (100 correos gratis por día):</h4>";
echo "<ul>";
echo "<li><strong>Registro:</strong> <a href='https://sendgrid.com' target='_blank'>sendgrid.com</a></li>";
echo "<li><strong>SMTP:</strong> smtp.sendgrid.net:587</li>";
echo "<li><strong>Usuario:</strong> apikey</li>";
echo "<li><strong>Password:</strong> Tu API Key</li>";
echo "</ul>";
echo "</div>";

echo "<h3>⚡ OPCIÓN 3: CONFIGURACIÓN LOCAL XAMPP</h3>";

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔧 Configurar sendmail en XAMPP:</h4>";
echo "<p>Edita <strong>C:\\xampp\\php\\php.ini</strong>:</p>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "[mail function]\n";
echo "SMTP = smtp.gmail.com\n";
echo "smtp_port = 587\n";
echo "sendmail_from = tu_correo@gmail.com\n";
echo "sendmail_path = \"C:\\xampp\\sendmail\\sendmail.exe -t\"\n";
echo "</pre>";
echo "</div>";

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔧 Configurar sendmail.ini:</h4>";
echo "<p>Edita <strong>C:\\xampp\\sendmail\\sendmail.ini</strong>:</p>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "smtp_server=smtp.gmail.com\n";
echo "smtp_port=587\n";
echo "auth_username=tu_correo@gmail.com\n";
echo "auth_password=tu_contraseña_aplicacion\n";
echo "force_sender=tu_correo@gmail.com\n";
echo "</pre>";
echo "</div>";

echo "<h3>🎯 RECOMENDACIÓN:</h3>";

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🚀 Para empezar AHORA:</h4>";
echo "<ol>";
echo "<li><strong>Mailtrap:</strong> Más fácil, gratis, sin configuración compleja</li>";
echo "<li><strong>SendGrid:</strong> Más profesional, 100 correos gratis</li>";
echo "<li><strong>XAMPP:</strong> Más técnico, requiere configuración</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🔄 Enlaces útiles:</h3>";
echo "<p><a href='https://mailtrap.io' target='_blank' style='display: inline-block; background: #dc3545; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>📧 Mailtrap (Recomendado)</a></p>";
echo "<p><a href='https://sendgrid.com' target='_blank' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>📧 SendGrid</a></p>";
echo "<p><a href='probar_mailtrap.php' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>🧪 Probar Mailtrap</a></p>";

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Estado:</strong> <span style='color: orange; font-weight: bold;'>ESPERANDO CONFIGURACIÓN SIMPLE</span></p>";
?>
