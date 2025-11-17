<?php
/**
 * Prueba directa de envío de correo
 * Este script prueba el envío de correo con el correo específico del usuario
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'conexion.php';
require_once 'funciones_correo_real.php';

$correo_destino = '211230001@smarcos.tecnm.mx';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Prueba de Envío de Correo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #1e3c72; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545; }
        .info { background: #e7f3ff; color: #004085; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #0056b3; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📧 Prueba de Envío de Correo</h1>";

echo "<div class='info'>";
echo "<p><strong>Enviando correo a:</strong> $correo_destino</p>";
echo "<p><strong>Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

// Datos de prueba
$datos_correo = [
    'estudiante' => 'Prueba de Sistema',
    'matricula' => '211230001',
    'curp' => 'PRUEBA123456HDFABC01',
    'nombre_insignia' => 'Talento Científico',
    'categoria' => 'Desarrollo Académico',
    'codigo_insignia' => 'TECNM-OFCM-2025-TAL-PRUEBA',
    'periodo' => '2025-1',
    'fecha_otorgamiento' => date('Y-m-d'),
    'responsable' => 'Sistema de Prueba',
    'descripcion' => 'Esta es una prueba del sistema de envío de correos. Si recibes este correo, significa que el sistema está funcionando correctamente.',
    'url_verificacion' => 'http://158.23.160.163/ver_insignia_publica.php?insignia=TECNM-OFCM-2025-TAL-PRUEBA'
];

echo "<div class='info'>";
echo "<h3>📋 Datos del correo:</h3>";
echo "<pre>";
print_r($datos_correo);
echo "</pre>";
echo "</div>";

// Intentar enviar
echo "<div class='info'>";
echo "<p><strong>⏳ Intentando enviar correo...</strong></p>";
echo "</div>";

// Habilitar logging detallado
error_log("=== INICIO PRUEBA DE CORREO ===");
error_log("Destinatario: $correo_destino");
error_log("Hora: " . date('Y-m-d H:i:s'));

$resultado = enviarNotificacionInsigniaCompleta($correo_destino, $datos_correo);

error_log("Resultado: " . ($resultado ? 'TRUE' : 'FALSE'));
error_log("=== FIN PRUEBA DE CORREO ===");

if ($resultado) {
    echo "<div class='success'>";
    echo "<h3>✅ ¡Correo enviado exitosamente!</h3>";
    echo "<p>El correo ha sido enviado a: <strong>$correo_destino</strong></p>";
    echo "<p><strong>Revisa tu bandeja de entrada</strong> (y también la carpeta de spam) en los próximos minutos.</p>";
    echo "<p>Si no recibes el correo en 5 minutos, revisa:</p>";
    echo "<ul>";
    echo "<li>Carpeta de spam/correo no deseado</li>";
    echo "<li>Filtros de correo</li>";
    echo "<li>Logs del servidor para ver errores específicos</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>❌ Error al enviar correo</h3>";
    echo "<p>No se pudo enviar el correo a: <strong>$correo_destino</strong></p>";
    echo "<p><strong>Posibles causas:</strong></p>";
    echo "<ul>";
    echo "<li>Credenciales SMTP incorrectas</li>";
    echo "<li>Servidor SMTP rechazó la conexión</li>";
    echo "<li>Problemas de autenticación con Office 365</li>";
    echo "<li>Firewall bloqueando el puerto 587</li>";
    echo "</ul>";
    echo "<p><strong>Para ver el error específico, ejecuta en PuTTY:</strong></p>";
    echo "<pre>sudo tail -n 50 /var/log/apache2/error.log | grep -i 'correo\|smtp\|mail\|phpmailer'</pre>";
    echo "</div>";
}

// Mostrar método usado
if (function_exists('obtenerMetodoCorreoUsado')) {
    $metodo = obtenerMetodoCorreoUsado();
    echo "<div class='info'>";
    echo "<p><strong>Método usado:</strong> $metodo</p>";
    if ($metodo === 'phpmailer') {
        echo "<p>✅ Se usó PHPMailer con SMTP (tiempo real)</p>";
    } elseif ($metodo === 'nativo') {
        echo "<p>⚠️ Se usó mail() nativo (puede tener retrasos)</p>";
    } elseif ($metodo === 'simulacion') {
        echo "<p>⚠️ Se usó simulación (correo guardado en archivo, no enviado realmente)</p>";
        echo "<p>Revisa el archivo <code>correos_enviados.txt</code> en el servidor</p>";
    }
    echo "</div>";
}

echo "<div class='info'>";
echo "<h3>📝 Próximos pasos:</h3>";
echo "<ol>";
echo "<li>Revisa tu correo: <strong>$correo_destino</strong></li>";
echo "<li>Si no recibes el correo, revisa los logs del servidor</li>";
echo "<li>Si el correo llega, el sistema está funcionando correctamente</li>";
echo "<li>Al registrar un reconocimiento, se enviará automáticamente el correo</li>";
echo "</ol>";
echo "</div>";

echo "</div></body></html>";
?>

