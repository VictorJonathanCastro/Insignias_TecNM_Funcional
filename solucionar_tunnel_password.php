<?php
// Script para configurar localtunnel sin contraseña
echo "<h2>🔧 Configurando LocalTunnel sin Contraseña</h2>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h3>⚠️ Problema Identificado</h3>";
echo "<p>El túnel local está pidiendo contraseña cuando alguien hace clic en la imagen compartida en Facebook.</p>";
echo "<p>Esto impide que los usuarios accedan directamente a la página de validación.</p>";
echo "</div>";

echo "<h3>🛠️ Solución: Configurar LocalTunnel sin Contraseña</h3>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Opción 1: Usar ngrok (Recomendado)</h4>";
echo "<p>ngrok es más estable y no requiere contraseña por defecto:</p>";
echo "<ol>";
echo "<li>Descarga ngrok desde: <a href='https://ngrok.com/download' target='_blank'>https://ngrok.com/download</a></li>";
echo "<li>Ejecuta: <code>ngrok http 80</code></li>";
echo "<li>Usa la URL HTTPS que te proporcione</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Opción 2: Configurar LocalTunnel sin contraseña</h4>";
echo "<p>Si quieres seguir usando LocalTunnel:</p>";
echo "<ol>";
echo "<li>Cierra el túnel actual</li>";
echo "<li>Ejecuta: <code>lt --port 80 --subdomain tu-subdominio-personalizado</code></li>";
echo "<li>O usa: <code>lt --port 80 --local-host 127.0.0.1</code></li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>✅ Solución Rápida</h4>";
echo "<p>Para solucionar inmediatamente el problema:</p>";
echo "<ol>";
echo "<li>Ve a <a href='https://ngrok.com/download' target='_blank'>ngrok.com</a></li>";
echo "<li>Descarga ngrok</li>";
echo "<li>Ejecuta: <code>ngrok http 80</code></li>";
echo "<li>Usa la URL HTTPS que aparezca</li>";
echo "<li>Actualiza las URLs en el sistema</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🔗 URLs Actuales del Sistema</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;'>";
echo "URL actual del túnel: <strong>https://cruel-needles-agree.loca.lt</strong><br>";
echo "URL de validación: <strong>https://cruel-needles-agree.loca.lt/Insignias_TecNM_Funcional/validacion.php</strong><br>";
echo "URL de Facebook: <strong>https://cruel-needles-agree.loca.lt/Insignias_TecNM_Funcional/facebook_imagen.php</strong><br>";
echo "</div>";

echo "<h3>📱 Prueba con Facebook</h3>";
echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196f3;'>";
echo "<h4>Para probar que funciona:</h4>";
echo "<ol>";
echo "<li>Configura ngrok o LocalTunnel sin contraseña</li>";
echo "<li>Actualiza las URLs en el sistema</li>";
echo "<li>Comparte en Facebook</li>";
echo "<li>Haz clic en la imagen</li>";
echo "<li>Debería ir directamente a la página de validación</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🚀 Comandos para Ejecutar</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Terminal/PowerShell:</h4>";
echo "<div style='background: #000; color: #0f0; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0;'>";
echo "# Opción 1: ngrok (Recomendado)<br>";
echo "ngrok http 80<br><br>";
echo "# Opción 2: LocalTunnel sin contraseña<br>";
echo "lt --port 80 --subdomain mi-insignia-tecnm<br>";
echo "</div>";
echo "</div>";

echo "<h3>📋 Checklist para Solucionar</h3>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<input type='checkbox'> Descargar e instalar ngrok<br>";
echo "<input type='checkbox'> Ejecutar <code>ngrok http 80</code><br>";
echo "<input type='checkbox'> Copiar la URL HTTPS de ngrok<br>";
echo "<input type='checkbox'> Actualizar las URLs en el sistema<br>";
echo "<input type='checkbox'> Probar compartir en Facebook<br>";
echo "<input type='checkbox'> Verificar que no pida contraseña<br>";
echo "</div>";

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='localtunnel_funcionando.php' style='background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; margin: 0 10px;'>🔄 Verificar Túnel</a>";
echo "<a href='https://ngrok.com/download' target='_blank' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; margin: 0 10px;'>⬇️ Descargar ngrok</a>";
echo "</div>";
?>
