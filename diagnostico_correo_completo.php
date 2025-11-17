<?php
/**
 * DIAGNÓSTICO COMPLETO DEL SISTEMA DE CORREO
 * Este script verifica TODOS los componentes necesarios para enviar correos
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Diagnóstico Completo de Correo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #1e3c72; border-bottom: 3px solid #1e3c72; padding-bottom: 10px; }
        .check { margin: 15px 0; padding: 15px; border-left: 4px solid #ddd; background: #f9f9f9; }
        .check.success { border-left-color: #28a745; background: #d4edda; }
        .check.error { border-left-color: #dc3545; background: #f8d7da; }
        .check.warning { border-left-color: #ffc107; background: #fff3cd; }
        .check h3 { margin-top: 0; }
        pre { background: #fff; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Diagnóstico Completo del Sistema de Correo</h1>";

// 1. Verificar archivos necesarios
echo "<div class='check'>";
echo "<h3>1. Verificación de Archivos Necesarios</h3>";

$archivos_necesarios = [
    'conexion.php',
    'funciones_correo_real.php',
    'config_smtp.php',
    'registrar_reconocimiento.php',
    'probar_correo.php'
];

$archivos_ok = true;
foreach ($archivos_necesarios as $archivo) {
    if (file_exists($archivo)) {
        echo "<p>✅ <strong>$archivo</strong> existe</p>";
    } else {
        echo "<p>❌ <strong>$archivo</strong> NO existe</p>";
        $archivos_ok = false;
    }
}
echo "</div>";

// 2. Verificar PHPMailer
echo "<div class='check " . (file_exists('src/PHPMailer.php') ? 'success' : 'error') . "'>";
echo "<h3>2. Verificación de PHPMailer</h3>";
if (file_exists('src/PHPMailer.php')) {
    echo "<p>✅ PHPMailer está instalado</p>";
    echo "<p>Ubicación: <code>" . realpath('src/PHPMailer.php') . "</code></p>";
} else {
    echo "<p>❌ PHPMailer NO está instalado</p>";
    echo "<p>Necesitas instalar PHPMailer con Composer o descargarlo manualmente</p>";
}
echo "</div>";

// 3. Verificar config_smtp.php
echo "<div class='check'>";
echo "<h3>3. Verificación de config_smtp.php</h3>";
if (file_exists('config_smtp.php')) {
    require_once 'config_smtp.php';
    
    echo "<p><strong>SMTP_HOST:</strong> " . (defined('SMTP_HOST') ? SMTP_HOST : '❌ No definido') . "</p>";
    echo "<p><strong>SMTP_PORT:</strong> " . (defined('SMTP_PORT') ? SMTP_PORT : '❌ No definido') . "</p>";
    echo "<p><strong>SMTP_USERNAME:</strong> " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : '❌ No definido') . "</p>";
    echo "<p><strong>SMTP_PASSWORD:</strong> " . (defined('SMTP_PASSWORD') ? (strlen(SMTP_PASSWORD) > 0 ? '✅ Configurada (' . strlen(SMTP_PASSWORD) . ' caracteres)' : '❌ Vacía') : '❌ No definida') . "</p>";
    echo "<p><strong>SMTP_FROM_EMAIL:</strong> " . (defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : '❌ No definido') . "</p>";
    
    if (defined('SMTP_HOST') && defined('SMTP_USERNAME') && defined('SMTP_PASSWORD') && !empty(SMTP_PASSWORD)) {
        echo "<p>✅ Configuración SMTP completa</p>";
    } else {
        echo "<p>❌ Configuración SMTP incompleta</p>";
    }
} else {
    echo "<p>❌ config_smtp.php NO existe</p>";
}
echo "</div>";

// 4. Verificar función mail()
echo "<div class='check " . (function_exists('mail') ? 'success' : 'error') . "'>";
echo "<h3>4. Verificación de función mail() de PHP</h3>";
if (function_exists('mail')) {
    echo "<p>✅ La función mail() está disponible</p>";
    $sendmail_path = ini_get('sendmail_path');
    echo "<p><strong>sendmail_path:</strong> " . ($sendmail_path ? $sendmail_path : 'No configurado') . "</p>";
} else {
    echo "<p>❌ La función mail() NO está disponible</p>";
}
echo "</div>";

// 5. Verificar funciones de correo
echo "<div class='check'>";
echo "<h3>5. Verificación de Funciones de Correo</h3>";
if (file_exists('funciones_correo_real.php')) {
    require_once 'funciones_correo_real.php';
    
    if (function_exists('enviarNotificacionInsigniaCompleta')) {
        echo "<p>✅ Función enviarNotificacionInsigniaCompleta() disponible</p>";
    } else {
        echo "<p>❌ Función enviarNotificacionInsigniaCompleta() NO disponible</p>";
    }
    
    if (function_exists('enviarConPHPMailerReal')) {
        echo "<p>✅ Función enviarConPHPMailerReal() disponible</p>";
    } else {
        echo "<p>❌ Función enviarConPHPMailerReal() NO disponible</p>";
    }
    
    if (function_exists('enviarConMailNativo')) {
        echo "<p>✅ Función enviarConMailNativo() disponible</p>";
    } else {
        echo "<p>❌ Función enviarConMailNativo() NO disponible</p>";
    }
} else {
    echo "<p>❌ funciones_correo_real.php NO existe</p>";
}
echo "</div>";

// 6. Probar conexión SMTP
echo "<div class='check'>";
echo "<h3>6. Prueba de Conexión SMTP</h3>";

if (file_exists('config_smtp.php') && defined('SMTP_HOST') && defined('SMTP_USERNAME') && defined('SMTP_PASSWORD')) {
    $host = SMTP_HOST;
    $port = defined('SMTP_PORT') ? SMTP_PORT : 587;
    
    echo "<p>Intentando conectar a: <strong>$host:$port</strong></p>";
    
    $connection = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($connection) {
        echo "<p>✅ Conexión exitosa a $host:$port</p>";
        fclose($connection);
    } else {
        echo "<p>❌ No se pudo conectar a $host:$port</p>";
        echo "<p>Error: $errstr ($errno)</p>";
        echo "<p>Posibles causas:</p>";
        echo "<ul>";
        echo "<li>El servidor SMTP no es accesible desde este servidor</li>";
        echo "<li>El puerto está bloqueado por firewall</li>";
        echo "<li>El servidor SMTP no existe o está caído</li>";
        echo "</ul>";
    }
} else {
    echo "<p>⚠️ No se puede probar conexión: configuración SMTP incompleta</p>";
}
echo "</div>";

// 7. Probar envío real (si se proporciona correo)
echo "<div class='check'>";
echo "<h3>7. Prueba de Envío Real</h3>";

$correo_prueba = $_GET['correo'] ?? '';
if (!empty($correo_prueba) && filter_var($correo_prueba, FILTER_VALIDATE_EMAIL)) {
    echo "<p>Intentando enviar correo de prueba a: <strong>$correo_prueba</strong></p>";
    
    if (file_exists('funciones_correo_real.php')) {
        require_once 'funciones_correo_real.php';
        
        $datos_prueba = [
            'estudiante' => 'Prueba de Diagnóstico',
            'matricula' => 'TEST123',
            'curp' => 'TEST123456HDFABC01',
            'nombre_insignia' => 'Prueba de Correo',
            'categoria' => 'Prueba',
            'codigo_insignia' => 'TEST-2025-PRUEBA',
            'periodo' => '2025-1',
            'fecha_otorgamiento' => date('Y-m-d'),
            'responsable' => 'Sistema de Diagnóstico',
            'descripcion' => 'Esta es una prueba del sistema de correo',
            'url_verificacion' => 'http://158.23.160.163/ver_insignia_publica.php?insignia=TEST-2025-PRUEBA'
        ];
        
        $resultado = @enviarNotificacionInsigniaCompleta($correo_prueba, $datos_prueba);
        
        if ($resultado) {
            echo "<p>✅ <strong>Correo enviado exitosamente</strong></p>";
            echo "<p>Revisa tu bandeja de entrada (y spam) en: <strong>$correo_prueba</strong></p>";
        } else {
            echo "<p>❌ <strong>Error al enviar correo</strong></p>";
            echo "<p>Revisa los logs del servidor para más detalles:</p>";
            echo "<pre>tail -n 50 /var/log/apache2/error.log | grep -i correo</pre>";
        }
    } else {
        echo "<p>❌ No se puede probar: funciones_correo_real.php no existe</p>";
    }
} else {
    echo "<p>Para probar el envío, agrega <code>?correo=TU_CORREO@ejemplo.com</code> a la URL</p>";
    echo "<p>Ejemplo: <code>diagnostico_correo_completo.php?correo=tu_correo@ejemplo.com</code></p>";
}
echo "</div>";

// 8. Resumen y recomendaciones
echo "<div class='info'>";
echo "<h3>📋 Resumen y Recomendaciones</h3>";

$problemas = [];

if (!file_exists('src/PHPMailer.php')) {
    $problemas[] = "PHPMailer no está instalado";
}

if (!file_exists('config_smtp.php')) {
    $problemas[] = "config_smtp.php no existe";
} elseif (!defined('SMTP_PASSWORD') || empty(SMTP_PASSWORD)) {
    $problemas[] = "SMTP_PASSWORD no está configurada o está vacía";
}

if (!function_exists('mail') && !file_exists('src/PHPMailer.php')) {
    $problemas[] = "Ni mail() ni PHPMailer están disponibles";
}

if (empty($problemas)) {
    echo "<p>✅ <strong>Todo parece estar configurado correctamente</strong></p>";
    echo "<p>Si aún no funciona, el problema puede ser:</p>";
    echo "<ul>";
    echo "<li>Credenciales SMTP incorrectas</li>";
    echo "<li>Servidor SMTP bloqueado por firewall</li>";
    echo "<li>Problemas de red/DNS</li>";
    echo "</ul>";
} else {
    echo "<p>❌ <strong>Problemas detectados:</strong></p>";
    echo "<ul>";
    foreach ($problemas as $problema) {
        echo "<li>$problema</li>";
    }
    echo "</ul>";
}

echo "</div>";

echo "</div></body></html>";
?>

