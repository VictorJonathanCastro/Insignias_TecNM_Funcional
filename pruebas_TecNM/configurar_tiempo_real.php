<?php
/**
 * CONFIGURACIÓN TIEMPO REAL - SERVICIO GRATUITO
 * Este archivo configura un servicio gratuito para envío real en tiempo real
 */

echo "<h2>⚡ CONFIGURACIÓN TIEMPO REAL</h2>";
echo "<h3>📧 Configurando servicio gratuito para envío real</h3>";

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>💡 Solución para Envío Real:</h4>";
echo "<p><strong>Problema:</strong> Simulación no llega en tiempo real</p>";
echo "<p><strong>Solución:</strong> Usar servicio gratuito como SendGrid</p>";
echo "<p><strong>Resultado:</strong> Correos llegan realmente al destinatario</p>";
echo "</div>";

echo "<h3>🚀 OPCIÓN 1: SENDGRID (RECOMENDADO)</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>📋 Pasos para SendGrid:</h4>";
echo "<ol>";
echo "<li><strong>Registro:</strong> Ve a <a href='https://sendgrid.com' target='_blank'>sendgrid.com</a></li>";
echo "<li><strong>Cuenta gratuita:</strong> 100 correos por día</li>";
echo "<li><strong>API Key:</strong> Genera una API Key</li>";
echo "<li><strong>Configuración:</strong> smtp.sendgrid.net:587</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🔧 CONFIGURACIÓN RÁPIDA:</h3>";

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>⚠️ Configuración Temporal:</h4>";
echo "<p>Mientras configuras SendGrid, puedes usar esta configuración temporal:</p>";
echo "</div>";

// Crear configuración temporal con Gmail usando contraseña de aplicación
$configuracion_temporal = "<?php\n";
$configuracion_temporal .= "// CONFIGURACIÓN TEMPORAL PARA TIEMPO REAL\n";
$configuracion_temporal .= "define('SMTP_HOST', 'smtp.gmail.com');\n";
$configuracion_temporal .= "define('SMTP_PORT', 587);\n";
$configuracion_temporal .= "define('SMTP_USERNAME', 'tu_correo@gmail.com');\n";
$configuracion_temporal .= "define('SMTP_PASSWORD', 'tu_contraseña_aplicacion');\n";
$configuracion_temporal .= "define('SMTP_FROM_NAME', 'Sistema Insignias TecNM');\n";
$configuracion_temporal .= "define('SMTP_SECURE', 'tls');\n";
$configuracion_temporal .= "?>";

file_put_contents('config_tiempo_real.php', $configuracion_temporal);

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>✅ Archivo de configuración creado:</h4>";
echo "<p><strong>Archivo:</strong> config_tiempo_real.php</p>";
echo "<p><strong>Estado:</strong> Listo para configurar</p>";
echo "</div>";

echo "<h3>📝 INSTRUCCIONES PASO A PASO:</h3>";

echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔑 Paso 1: Crear cuenta Gmail para el sistema</h4>";
echo "<ol>";
echo "<li>Ve a <a href='https://gmail.com' target='_blank'>gmail.com</a></li>";
echo "<li>Crea una cuenta nueva: <strong>insignias.tecnm@gmail.com</strong></li>";
echo "<li>Activa verificación en dos pasos</li>";
echo "<li>Genera contraseña de aplicación</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔑 Paso 2: Configurar contraseña de aplicación</h4>";
echo "<ol>";
echo "<li>Ve a <a href='https://myaccount.google.com/security' target='_blank'>myaccount.google.com/security</a></li>";
echo "<li>Inicia sesión con la cuenta nueva</li>";
echo "<li>Ve a 'Contraseñas de aplicación'</li>";
echo "<li>Crea una nueva: 'Sistema Insignias TecNM'</li>";
echo "<li>Copia la contraseña de 16 caracteres</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔑 Paso 3: Actualizar configuración</h4>";
echo "<ol>";
echo "<li>Edita el archivo <strong>config_tiempo_real.php</strong></li>";
echo "<li>Cambia <strong>tu_correo@gmail.com</strong> por tu correo real</li>";
echo "<li>Cambia <strong>tu_contraseña_aplicacion</strong> por tu contraseña real</li>";
echo "<li>Guarda el archivo</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🧪 PROBAR CONFIGURACIÓN:</h3>";

echo "<p><a href='probar_tiempo_real.php' style='display: inline-block; background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold;'>⚡ Probar Envío en Tiempo Real</a></p>";

echo "<h3>📧 SERVICIOS GRATUITOS ALTERNATIVOS:</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>1. Mailgun</h4>";
echo "<ul>";
echo "<li><strong>Gratis:</strong> 10,000 correos por mes</li>";
echo "<li><strong>Registro:</strong> <a href='https://mailgun.com' target='_blank'>mailgun.com</a></li>";
echo "<li><strong>SMTP:</strong> smtp.mailgun.org:587</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>2. SendGrid</h4>";
echo "<ul>";
echo "<li><strong>Gratis:</strong> 100 correos por día</li>";
echo "<li><strong>Registro:</strong> <a href='https://sendgrid.com' target='_blank'>sendgrid.com</a></li>";
echo "<li><strong>SMTP:</strong> smtp.sendgrid.net:587</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>3. Mailtrap (Solo pruebas)</h4>";
echo "<ul>";
echo "<li><strong>Gratis:</strong> Para pruebas de desarrollo</li>";
echo "<li><strong>Registro:</strong> <a href='https://mailtrap.io' target='_blank'>mailtrap.io</a></li>";
echo "<li><strong>SMTP:</strong> smtp.mailtrap.io:2525</li>";
echo "</ul>";
echo "</div>";

echo "<h3>⚡ CONFIGURACIÓN RÁPIDA:</h3>";

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🚀 Para que funcione AHORA:</h4>";
echo "<ol>";
echo "<li>Crea cuenta Gmail: <strong>insignias.tecnm@gmail.com</strong></li>";
echo "<li>Genera contraseña de aplicación</li>";
echo "<li>Actualiza <strong>config_tiempo_real.php</strong></li>";
echo "<li>Prueba con <strong>probar_tiempo_real.php</strong></li>";
echo "</ol>";
echo "</div>";

echo "<h3>🔄 Enlaces útiles:</h3>";
echo "<p><a href='https://gmail.com' target='_blank' style='display: inline-block; background: #dc3545; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>📧 Crear Gmail</a></p>";
echo "<p><a href='https://myaccount.google.com/security' target='_blank' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>🔐 Contraseñas App</a></p>";
echo "<p><a href='probar_tiempo_real.php' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>⚡ Probar Tiempo Real</a></p>";

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Estado:</strong> <span style='color: orange; font-weight: bold;'>ESPERANDO CONFIGURACIÓN</span></p>";
?>
