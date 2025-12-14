<?php
/**
 * Prueba de correo con simulación
 * Esta página probará el sistema de correos usando simulación
 */

require_once 'funciones_correo_simulacion.php';

echo "<h2>📧 Prueba de Correo con Simulación</h2>";

// Datos de prueba
$destinatario = "211230001@smarcos.tecnm.mx";
$datos_insignia = [
    'estudiante' => 'Juan Pérez García',
    'matricula' => '211230001',
    'curp' => 'PERJ800101HDFRGN01',
    'nombre_insignia' => 'Excelencia Académica',
    'categoria' => 'Formación Integral',
    'codigo_insignia' => 'INS-2024-001',
    'periodo' => 'Enero-Diciembre 2024',
    'fecha_otorgamiento' => date('Y-m-d'),
    'responsable' => 'Dr. María González',
    'descripcion' => 'Reconocimiento por obtener el mejor promedio de la generación.',
    'url_verificacion' => 'http://localhost/Insignias_TecNM_Funcional/verificar_insignia.php?clave=INS-2024-001'
];

echo "<h3>🔧 Configuración de Prueba:</h3>";
echo "<p><strong>Destinatario:</strong> " . htmlspecialchars($destinatario) . "</p>";
echo "<p><strong>Estudiante:</strong> " . htmlspecialchars($datos_insignia['estudiante']) . "</p>";
echo "<p><strong>Insignia:</strong> " . htmlspecialchars($datos_insignia['nombre_insignia']) . "</p>";

echo "<h3>📤 Enviando correo...</h3>";

// Enviar correo usando la función completa
$resultado = enviarNotificacionInsigniaCompleta($destinatario, $datos_insignia);

if ($resultado) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>✅ ¡Correo procesado exitosamente!</h4>";
    echo "<p>El correo se ha procesado correctamente.</p>";
    echo "<p>Si el correo real falló, se guardó en simulación.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Error al procesar correo</h4>";
    echo "<p>Hubo un error al procesar el correo.</p>";
    echo "</div>";
}

echo "<h3>📋 Correos Enviados:</h3>";
echo mostrarCorreosEnviados();

echo "<h3>💡 Información:</h3>";
echo "<p>Esta función:</p>";
echo "<ul>";
echo "<li>Intenta enviar el correo real primero</li>";
echo "<li>Si falla, guarda el correo en simulación</li>";
echo "<li>Los correos simulados se guardan en <code>correos_enviados.txt</code></li>";
echo "<li>Puedes ver todos los correos enviados arriba</li>";
echo "</ul>";

echo "<h3>🔧 Para usar en el formulario:</h3>";
echo "<p>Actualiza <code>metadatos_formulario.php</code> para usar:</p>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
echo "require_once 'funciones_correo_simulacion.php';\n";
echo "// Cambiar la línea de envío por:\n";
echo "\$correo_enviado = enviarNotificacionInsigniaCompleta(\$correo, \$datos_correo);";
echo "</pre>";

echo "<hr>";
echo "<p><a href='metadatos_formulario.php'>← Volver al formulario de insignias</a></p>";
echo "<p><a href='probar_correo_simple.php'>← Probar correo básico</a></p>";
echo "<p><a href='probar_correo_phpmailer.php'>← Probar con PHPMailer</a></p>";
?>
