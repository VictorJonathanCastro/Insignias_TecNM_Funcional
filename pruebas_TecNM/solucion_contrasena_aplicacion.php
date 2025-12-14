<?php
/**
 * SOLUCIÓN DEFINITIVA - CONTRASEÑA DE APLICACIÓN MICROSOFT
 * Este archivo te guía paso a paso para generar la contraseña de aplicación
 */

echo "<h2>🔐 SOLUCIÓN DEFINITIVA - CONTRASEÑA DE APLICACIÓN</h2>";
echo "<h3>📧 Configuración correcta para Outlook/TecNM</h3>";

echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>❌ Problema Identificado:</h4>";
echo "<p><strong>Error:</strong> SMTP Error: Could not authenticate</p>";
echo "<p><strong>Causa:</strong> Microsoft requiere contraseña de aplicación para aplicaciones externas</p>";
echo "<p><strong>Solución:</strong> Generar contraseña de aplicación específica</p>";
echo "</div>";

echo "<h3>📋 PASOS PARA GENERAR CONTRASEÑA DE APLICACIÓN:</h3>";

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔑 Paso 1: Acceder a la configuración de seguridad</h4>";
echo "<ol>";
echo "<li>Abre tu navegador y ve a: <a href='https://account.microsoft.com/security' target='_blank' style='color: #007bff;'>https://account.microsoft.com/security</a></li>";
echo "<li>Inicia sesión con tu cuenta TecNM: <strong>211230001@smarcos.tecnm.mx</strong></li>";
echo "<li>Si te pide verificación en dos pasos, complétala</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔑 Paso 2: Crear contraseña de aplicación</h4>";
echo "<ol>";
echo "<li>En la página de seguridad, busca la sección <strong>'Contraseñas de aplicación'</strong></li>";
echo "<li>Si no la ves, busca <strong>'Opciones de seguridad avanzadas'</strong> o <strong>'Verificación en dos pasos'</strong></li>";
echo "<li>Haz clic en <strong>'Crear una nueva contraseña de aplicación'</strong></li>";
echo "<li>Dale un nombre como: <strong>'Sistema Insignias TecNM'</strong></li>";
echo "<li>Haz clic en <strong>'Crear'</strong></li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔑 Paso 3: Copiar la contraseña generada</h4>";
echo "<ol>";
echo "<li>Microsoft te mostrará una contraseña de 16 caracteres</li>";
echo "<li>Ejemplo: <strong>abcd efgh ijkl mnop</strong></li>";
echo "<li><strong>¡IMPORTANTE!</strong> Copia esta contraseña inmediatamente</li>";
echo "<li>No podrás verla de nuevo después de cerrar la ventana</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🧪 PROBAR CON LA NUEVA CONTRASEÑA:</h3>";

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>⚠️ Instrucciones:</h4>";
echo "<ol>";
echo "<li>Genera tu contraseña de aplicación siguiendo los pasos anteriores</li>";
echo "<li>Regresa a esta página</li>";
echo "<li>Haz clic en el botón de abajo para probar</li>";
echo "<li>Ingresa tu nueva contraseña de aplicación cuando se te solicite</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🔧 CONFIGURACIÓN ACTUAL:</h3>";
echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Correo:</strong> 211230001@smarcos.tecnm.mx</p>";
echo "<p><strong>Servidor SMTP:</strong> smtp-mail.outlook.com</p>";
echo "<p><strong>Puerto:</strong> 587</p>";
echo "<p><strong>Seguridad:</strong> STARTTLS</p>";
echo "<p><strong>Contraseña:</strong> [Tu nueva contraseña de aplicación]</p>";
echo "</div>";

echo "<h3>🚀 PROBAR CONFIGURACIÓN:</h3>";
echo "<p><a href='probar_contrasena_aplicacion.php' style='display: inline-block; background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold;'>🔐 Probar con Contraseña de Aplicación</a></p>";

echo "<h3>📚 INFORMACIÓN ADICIONAL:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>¿Por qué necesito una contraseña de aplicación?</h4>";
echo "<ul>";
echo "<li>Microsoft bloquea el acceso de aplicaciones externas por seguridad</li>";
echo "<li>Las contraseñas de aplicación son específicas para cada aplicación</li>";
echo "<li>Son más seguras que usar tu contraseña normal</li>";
echo "<li>Puedes revocarlas en cualquier momento</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>¿Qué pasa si no puedo generar la contraseña?</h4>";
echo "<ul>";
echo "<li>Verifica que tengas verificación en dos pasos activada</li>";
echo "<li>Contacta al administrador de TI de TecNM</li>";
echo "<li>Usa temporalmente Gmail para pruebas</li>";
echo "<li>El sistema seguirá funcionando con simulación</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h3>🔄 Enlaces útiles:</h3>";
echo "<p><a href='https://account.microsoft.com/security' target='_blank' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>🔐 Configuración de Seguridad Microsoft</a></p>";
echo "<p><a href='prueba_simple.php' style='display: inline-block; background: #6c757d; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>📧 Prueba Simple Original</a></p>";
echo "<p><a href='probar_insignia_yeni_directo.php' style='display: inline-block; background: #17a2b8; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>🎖️ Crear Insignia para Yeni</a></p>";

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Estado:</strong> <span style='color: orange; font-weight: bold;'>ESPERANDO CONTRASEÑA DE APLICACIÓN</span></p>";
?>
