<?php
// Script para configurar ngrok como alternativa a localtunnel
echo "<h2>🚀 Configurando ngrok para Compartir en Facebook</h2>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h3>✅ ngrok es la Mejor Solución</h3>";
echo "<p>ngrok es más estable que LocalTunnel y no requiere contraseña por defecto.</p>";
echo "<p>Es la solución recomendada para compartir en Facebook.</p>";
echo "</div>";

echo "<h3>📥 Paso 1: Descargar ngrok</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<p><strong>Descarga ngrok desde:</strong> <a href='https://ngrok.com/download' target='_blank' style='color: #007bff; font-weight: bold;'>https://ngrok.com/download</a></p>";
echo "<p>Selecciona la versión para Windows y descárgala.</p>";
echo "</div>";

echo "<h3>🔧 Paso 2: Configurar ngrok</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Instrucciones:</h4>";
echo "<ol>";
echo "<li>Extrae el archivo ngrok.exe en una carpeta (ej: C:\\ngrok\\)</li>";
echo "<li>Abre PowerShell como administrador</li>";
echo "<li>Navega a la carpeta donde está ngrok.exe</li>";
echo "<li>Ejecuta: <code>ngrok http 80</code></li>";
echo "</ol>";
echo "</div>";

echo "<h3>💻 Comandos para Ejecutar</h3>";
echo "<div style='background: #000; color: #0f0; padding: 15px; border-radius: 5px; font-family: monospace; margin: 20px 0;'>";
echo "cd C:\\ngrok<br>";
echo "ngrok http 80<br>";
echo "</div>";

echo "<h3>📋 Paso 3: Obtener la URL de ngrok</h3>";
echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196f3;'>";
echo "<h4>Después de ejecutar ngrok:</h4>";
echo "<ol>";
echo "<li>Verás una pantalla con URLs como: <code>https://abc123.ngrok.io</code></li>";
echo "<li>Copia la URL HTTPS (la que empieza con https://)</li>";
echo "<li>Esta será tu nueva URL pública</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🔄 Paso 4: Actualizar URLs en el Sistema</h3>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h4>Necesitas actualizar estas URLs:</h4>";
echo "<ol>";
echo "<li><strong>validacion.php</strong> - Línea 401: Cambiar la URL base</li>";
echo "<li><strong>facebook_imagen.php</strong> - Si existe, actualizar las URLs</li>";
echo "<li><strong>metadatos_formulario.php</strong> - Actualizar URLs de compartir</li>";
echo "</ol>";
echo "</div>";

echo "<h3>📱 Paso 5: Probar en Facebook</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Para probar:</h4>";
echo "<ol>";
echo "<li>Comparte la insignia en Facebook usando la nueva URL de ngrok</li>";
echo "<li>Haz clic en la imagen de la insignia</li>";
echo "<li>Debería ir directamente a la página de validación SIN pedir contraseña</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🔗 URLs que Necesitas Actualizar</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;'>";
echo "URL actual: <strong>https://cruel-needles-agree.loca.lt</strong><br>";
echo "URL nueva: <strong>https://[tu-codigo].ngrok.io</strong><br><br>";
echo "Archivos a actualizar:<br>";
echo "- validacion.php (línea ~401)<br>";
echo "- facebook_imagen.php<br>";
echo "- metadatos_formulario.php<br>";
echo "</div>";

echo "<h3>⚡ Solución Rápida</h3>";
echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>Si quieres una solución inmediata:</h4>";
echo "<ol>";
echo "<li>Descarga ngrok</li>";
echo "<li>Ejecuta: <code>ngrok http 80</code></li>";
echo "<li>Copia la URL HTTPS</li>";
echo "<li>Reemplaza <code>cruel-needles-agree.loca.lt</code> con tu URL de ngrok</li>";
echo "<li>¡Listo! Ya no pedirá contraseña</li>";
echo "</ol>";
echo "</div>";

echo "<h3>📊 Comparación: LocalTunnel vs ngrok</h3>";
echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
echo "<tr style='background: #f8f9fa;'>";
echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>Característica</th>";
echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>LocalTunnel</th>";
echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>ngrok</th>";
echo "</tr>";
echo "<tr>";
echo "<td style='border: 1px solid #ddd; padding: 12px;'>Contraseña</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: #dc3545;'>❌ Requiere contraseña</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: #28a745;'>✅ Sin contraseña</td>";
echo "</tr>";
echo "<tr>";
echo "<td style='border: 1px solid #ddd; padding: 12px;'>Estabilidad</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: #ffc107;'>⚠️ Intermitente</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: #28a745;'>✅ Muy estable</td>";
echo "</tr>";
echo "<tr>";
echo "<td style='border: 1px solid #ddd; padding: 12px;'>Velocidad</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: #ffc107;'>⚠️ Variable</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: #28a745;'>✅ Rápido</td>";
echo "</tr>";
echo "<tr>";
echo "<td style='border: 1px solid #ddd; padding: 12px;'>Facebook</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: #dc3545;'>❌ Problemas</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: #28a745;'>✅ Funciona perfecto</td>";
echo "</tr>";
echo "</table>";

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='https://ngrok.com/download' target='_blank' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; margin: 0 10px;'>⬇️ Descargar ngrok</a>";
echo "<a href='localtunnel_funcionando.php' style='background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; margin: 0 10px;'>🔄 Verificar Estado</a>";
echo "</div>";

echo "<h3>🎯 Resumen</h3>";
echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196f3;'>";
echo "<p><strong>El problema:</strong> LocalTunnel pide contraseña cuando alguien hace clic en la imagen de Facebook.</p>";
echo "<p><strong>La solución:</strong> Usar ngrok que no requiere contraseña y es más estable.</p>";
echo "<p><strong>El resultado:</strong> Los usuarios podrán acceder directamente a la página de validación sin problemas.</p>";
echo "</div>";
?>
