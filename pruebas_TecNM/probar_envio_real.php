<?php
/**
 * PRUEBA DE ENVÍO REAL DE CORREOS CON INSIGNIAS
 * Este archivo prueba el envío real de correos usando la nueva función
 */

echo "<h2>📧 PRUEBA DE ENVÍO REAL DE CORREOS</h2>";
echo "<h3>🎯 Probando envío con insignia completa</h3>";

// Incluir la nueva función de envío real
require_once 'funciones_correo_real.php';

// Datos de prueba para una insignia
$datos_insignia_prueba = [
    'estudiante' => '211230001 (Usuario de Prueba)',
    'matricula' => '211230001',
    'curp' => 'PERJ800101HDFRGN01',
    'nombre_insignia' => 'Excelencia Académica - Prueba Real',
    'categoria' => 'Formación Integral',
    'codigo_insignia' => 'INS-PRUEBA-REAL-001',
    'periodo' => 'Enero-Diciembre 2024',
    'fecha_otorgamiento' => date('Y-m-d'),
    'responsable' => 'Sistema de Prueba TecNM',
    'descripcion' => 'Esta es una prueba del envío REAL de correos con insignias completas. Si recibiste este correo, significa que el sistema está funcionando correctamente y los correos llegarán a los destinatarios.',
    'url_verificacion' => generarUrlVerificacion('INS-PRUEBA-REAL-001')
];

$correo_destino = "211230001@smarcos.tecnm.mx";

echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔧 Datos de la Prueba:</h4>";
echo "<p><strong>Destinatario:</strong> " . htmlspecialchars($correo_destino) . "</p>";
echo "<p><strong>Insignia:</strong> " . htmlspecialchars($datos_insignia_prueba['nombre_insignia']) . "</p>";
echo "<p><strong>Código:</strong> " . htmlspecialchars($datos_insignia_prueba['codigo_insignia']) . "</p>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

echo "<h3>📤 Enviando correo real...</h3>";

// Intentar envío real
$resultado = enviarNotificacionInsigniaCompleta($correo_destino, $datos_insignia_prueba);

if ($resultado) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎉 ¡ÉXITO!</h4>";
    echo "<p><strong>✅ Correo REAL enviado correctamente</strong></p>";
    echo "<p><strong>Destinatario:</strong> " . htmlspecialchars($correo_destino) . "</p>";
    echo "<p><strong>Asunto:</strong> 🎖️ Insignia Otorgada - " . htmlspecialchars($datos_insignia_prueba['nombre_insignia']) . "</p>";
    echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>📧 ¿Dónde revisar?</h4>";
    echo "<p><strong>1. Bandeja de entrada:</strong> Revisa tu Outlook</p>";
    echo "<p><strong>2. Carpeta de spam:</strong> A veces va ahí</p>";
    echo "<p><strong>3. Busca:</strong> 🎖️ Insignia Otorgada - Excelencia Académica - Prueba Real</p>";
    echo "</div>";
    
    echo "<h3>🚀 PRÓXIMO PASO:</h3>";
    echo "<p>Ahora puedes usar el formulario completo y los correos llegarán realmente:</p>";
    echo "<p><a href='metadatos_formulario.php' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>📝 Ir al Formulario de Insignias</a></p>";
    
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Error en el envío</h4>";
    echo "<p>El correo real falló, pero se guardó en simulación como respaldo.</p>";
    echo "<p>Revisa el archivo <strong>correos_enviados.txt</strong> para ver el correo generado.</p>";
    echo "</div>";
}

echo "<h3>🔄 Probar Nuevamente:</h3>";
echo "<p><a href='probar_envio_real.php' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>🔄 Ejecutar Prueba Nuevamente</a></p>";

echo "<hr>";
echo "<p><a href='prueba_simple.php'>← Volver a prueba simple</a></p>";
echo "<p><a href='metadatos_formulario.php'>← Formulario de insignias</a></p>";
?>
