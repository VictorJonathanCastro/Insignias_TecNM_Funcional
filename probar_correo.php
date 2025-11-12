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

// Verificar si realmente se envió o solo se simuló
$usando_simulacion = !$resultado_nativo && !$resultado_phpmailer && $resultado_completo;

if ($usando_simulacion) {
    echo "⚠️ <strong style='color: orange;'>FUNCIÓN COMPLETA USÓ SIMULACIÓN</strong><br>";
    echo "<small style='color: #666;'>El correo NO se envió realmente. Se guardó en un archivo local.</small><br>";
    echo "<small style='color: #666;'>Para enviar correos reales, necesitas:</small><br>";
    echo "<ul style='color: #666; font-size: 14px;'>";
    echo "<li>Instalar sendmail en el servidor, O</li>";
    echo "<li>Configurar el correo sistema.insignias@smarcos.tecnm.mx en config_smtp.php</li>";
    echo "</ul>";
} else {
    echo $resultado_completo ? "✅ Función completa funcionó (ENVÍO REAL)" : "❌ Función completa falló";
}
echo "<br><br>";

echo "<hr>";
echo "<h2>📋 Resumen</h2>";
echo "<ul>";
echo "<li>mail() nativo: " . ($resultado_nativo ? "✅ OK (ENVÍO REAL)" : "❌ FALLÓ") . "</li>";
echo "<li>PHPMailer SMTP: " . ($resultado_phpmailer ? "✅ OK (ENVÍO REAL)" : "❌ FALLÓ") . "</li>";
if ($usando_simulacion) {
    echo "<li>Función completa: ⚠️ SIMULACIÓN (NO se envió realmente)</li>";
} else {
    echo "<li>Función completa: " . ($resultado_completo ? "✅ OK (ENVÍO REAL)" : "❌ FALLÓ") . "</li>";
}
echo "</ul>";

if ($resultado_completo && !$usando_simulacion) {
    echo "<p style='color: green; font-weight: bold;'>✅ ¡El correo se envió exitosamente! Revisa tu bandeja de entrada.</p>";
} elseif ($usando_simulacion) {
    echo "<p style='color: orange; font-weight: bold; padding: 15px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 5px;'>";
    echo "⚠️ <strong>ATENCIÓN: El correo NO se envió realmente</strong><br><br>";
    echo "El sistema usó simulación porque ambos métodos de envío real fallaron:<br>";
    echo "• mail() nativo no está disponible (sendmail no instalado)<br>";
    echo "• PHPMailer SMTP falló (falta configurar sistema.insignias@smarcos.tecnm.mx)<br><br>";
    echo "<strong>SOLUCIÓN:</strong> Cuando tengas el correo sistema.insignias@smarcos.tecnm.mx:<br>";
    echo "1. Edita config_smtp.php en el servidor<br>";
    echo "2. Actualiza SMTP_PASSWORD con la contraseña real<br>";
    echo "3. Vuelve a probar este script<br><br>";
    echo "O instala sendmail: <code>sudo apt-get install sendmail</code>";
    echo "</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ El correo no se pudo enviar. Revisa los logs de error.</p>";
    echo "<p><strong>Comandos para ver logs:</strong></p>";
    echo "<code>tail -n 50 /var/log/apache2/error.log | grep -i correo</code>";
}

echo "<hr>";
echo "<p><a href='metadatos_formulario.php'>← Volver al formulario</a></p>";
?>

