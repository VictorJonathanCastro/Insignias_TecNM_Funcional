<?php
/**
 * Script para probar el envío de correo
 * Ejecuta este archivo desde el navegador para probar la configuración SMTP
 */

require_once 'conexion.php';
require_once 'funciones_correo_real.php';

// Configuración de prueba
$correo_destino = $_GET['correo'] ?? '211230001@smarcos.tecnm.mx';
$correo_origen = '211230001@smarcos.tecnm.mx';

echo "<h1>🧪 Prueba de Envío de Correo</h1>";
echo "<p><strong>Enviando correo de prueba a:</strong> $correo_destino</p>";
echo "<hr>";

$datos_prueba = [
    'estudiante' => 'Estudiante de Prueba',
    'matricula' => '123456789',
    'curp' => 'TEST123456HDFABC01',
    'nombre_insignia' => 'Talento Científico',
    'categoria' => 'Desarrollo Académico',
    'codigo_insignia' => 'TECNM-OFCM-2025-TAL-TEST',
    'periodo' => '2025-1',
    'fecha_otorgamiento' => date('Y-m-d'),
    'responsable' => 'Sistema de Prueba',
    'descripcion' => 'Esta es una prueba del sistema de correo',
    'url_verificacion' => 'http://158.23.160.163/ver_insignia_publica.php?insignia=TECNM-OFCM-2025-TAL-TEST',
    'url_imagen' => 'http://158.23.160.163/imagen/Insignias/TalentoCientifico.png'
];

echo "<h2>1. Probando mail() nativo...</h2>";
$resultado_nativo = enviarConMailNativo($correo_destino, "Prueba - Insignia Otorgada", generarMensajeCorreo($datos_prueba));
echo $resultado_nativo ? "✅ mail() nativo funcionó" : "❌ mail() nativo falló";
echo "<br><br>";

echo "<h2>2. Probando PHPMailer con SMTP...</h2>";
$resultado_phpmailer = enviarConPHPMailerReal($correo_destino, "Prueba - Insignia Otorgada", generarMensajeCorreo($datos_prueba), $datos_prueba);
echo $resultado_phpmailer ? "✅ PHPMailer funcionó" : "❌ PHPMailer falló";
echo "<br><br>";

echo "<h2>3. Probando función completa...</h2>";
$resultado_completo = enviarNotificacionInsigniaCompleta($correo_destino, $datos_prueba);
echo $resultado_completo ? "✅ Función completa funcionó" : "❌ Función completa falló";
echo "<br><br>";

echo "<hr>";
echo "<h2>📋 Resumen</h2>";
echo "<ul>";
echo "<li>mail() nativo: " . ($resultado_nativo ? "✅ OK" : "❌ FALLÓ") . "</li>";
echo "<li>PHPMailer SMTP: " . ($resultado_phpmailer ? "✅ OK" : "❌ FALLÓ") . "</li>";
echo "<li>Función completa: " . ($resultado_completo ? "✅ OK" : "❌ FALLÓ") . "</li>";
echo "</ul>";

if ($resultado_completo) {
    echo "<p style='color: green; font-weight: bold;'>✅ ¡El correo se envió exitosamente! Revisa tu bandeja de entrada.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ El correo no se pudo enviar. Revisa los logs de error.</p>";
    echo "<p><strong>Comandos para ver logs:</strong></p>";
    echo "<code>tail -n 50 /var/log/apache2/error.log | grep -i correo</code>";
}

echo "<hr>";
echo "<p><a href='metadatos_formulario.php'>← Volver al formulario</a></p>";
?>

