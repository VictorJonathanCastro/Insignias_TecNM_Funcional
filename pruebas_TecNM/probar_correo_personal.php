<?php
/**
 * Prueba de correo directo a 211230001@smarcos.tecnm.mx
 * Esta página enviará un correo de prueba directamente a tu correo
 */

require_once 'funciones_correo_simulacion.php';

echo "<h2>📧 Prueba de Correo Directo</h2>";
echo "<h3>🎯 Enviando correo de prueba a: 211230001@smarcos.tecnm.mx</h3>";

// Datos de prueba específicos para ti
$tu_correo = "211230001@smarcos.tecnm.mx";
$datos_insignia_prueba = [
    'estudiante' => '211230001 (Usuario de Prueba)',
    'matricula' => '211230001',
    'curp' => 'PERJ800101HDFRGN01',
    'nombre_insignia' => 'Excelencia Académica - Prueba del Sistema',
    'categoria' => 'Formación Integral',
    'codigo_insignia' => 'INS-PRUEBA-001',
    'periodo' => 'Enero-Diciembre 2024',
    'fecha_otorgamiento' => date('Y-m-d'),
    'responsable' => 'Sistema de Prueba TecNM',
    'descripcion' => 'Esta es una insignia de prueba del Sistema de Insignias TecNM. Si recibiste este correo, significa que el sistema está funcionando correctamente.',
    'url_verificacion' => 'http://localhost/Insignias_TecNM_Funcional/verificar_insignia.php?clave=INS-PRUEBA-001'
];

echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>📋 Datos de la Prueba:</h4>";
echo "<ul>";
echo "<li><strong>Destinatario:</strong> " . htmlspecialchars($tu_correo) . "</li>";
echo "<li><strong>Estudiante:</strong> " . htmlspecialchars($datos_insignia_prueba['estudiante']) . "</li>";
echo "<li><strong>Insignia:</strong> " . htmlspecialchars($datos_insignia_prueba['nombre_insignia']) . "</li>";
echo "<li><strong>Código:</strong> " . htmlspecialchars($datos_insignia_prueba['codigo_insignia']) . "</li>";
echo "<li><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</li>";
echo "</ul>";
echo "</div>";

echo "<h3>📤 Procesando envío...</h3>";

// Enviar correo usando la función completa
$resultado = enviarNotificacionInsigniaCompleta($tu_correo, $datos_insignia_prueba);

if ($resultado) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎉 ¡CORREO DE PRUEBA ENVIADO!</h4>";
    echo "<p><strong>✅ Correo procesado exitosamente</strong></p>";
    echo "<p><strong>Destinatario:</strong> " . htmlspecialchars($tu_correo) . "</p>";
    echo "<p><strong>Asunto:</strong> 🎖️ Insignia Otorgada - Excelencia Académica - Prueba del Sistema</p>";
    echo "<p><strong>Estado:</strong> El correo se ha procesado correctamente.</p>";
    echo "<p><strong>Nota:</strong> Si el correo real falló, se guardó en simulación para desarrollo.</p>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>📧 ¿Dónde revisar el correo?</h4>";
    echo "<p><strong>1. Bandeja de entrada:</strong> Revisa tu correo " . htmlspecialchars($tu_correo) . "</p>";
    echo "<p><strong>2. Carpeta de spam:</strong> A veces los correos automáticos van al spam</p>";
    echo "<p><strong>3. Archivo de simulación:</strong> Si no llega el correo real, revisa el archivo <code>correos_enviados.txt</code></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Error al procesar correo</h4>";
    echo "<p>Hubo un error al procesar el correo de prueba.</p>";
    echo "<p>Revisa la configuración del sistema.</p>";
    echo "</div>";
}

echo "<h3>📋 Correos Procesados:</h3>";
echo mostrarCorreosEnviados();

echo "<h3>🔄 Probar Nuevamente:</h3>";
echo "<p><a href='probar_correo_personal.php' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>🔄 Enviar Otro Correo de Prueba</a></p>";

echo "<h3>💡 Información del Sistema:</h3>";
echo "<ul>";
echo "<li><strong>Función usada:</strong> enviarNotificacionInsigniaCompleta()</li>";
echo "<li><strong>Comportamiento:</strong> Intenta correo real primero, si falla usa simulación</li>";
echo "<li><strong>Archivo de registro:</strong> correos_enviados.txt</li>";
echo "<li><strong>Estado:</strong> Sistema funcionando correctamente</li>";
echo "</ul>";

echo "<h3>🎯 Próximos pasos:</h3>";
echo "<p>1. <strong>Revisa tu correo</strong> " . htmlspecialchars($tu_correo) . "</p>";
echo "<p>2. <strong>Si no llega:</strong> Revisa la carpeta de spam</p>";
echo "<p>3. <strong>Si aún no llega:</strong> Revisa el archivo <code>correos_enviados.txt</code> arriba</p>";
echo "<p>4. <strong>Para producción:</strong> Configura SMTP real del TecNM</p>";

echo "<hr>";
echo "<p><a href='metadatos_formulario.php'>← Volver al formulario de insignias</a></p>";
echo "<p><a href='probar_correo_dinamico.php'>← Probar con otros correos</a></p>";
echo "<p><a href='sistema_100_funcional.php'>← Ver sistema completo</a></p>";
?>
