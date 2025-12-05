<?php
/**
 * DIAGNÓSTICO: Verificar por qué no se envía el correo al rellenar el formulario
 * Este script simula exactamente lo que hace metadatos_formulario.php
 */

session_start();
require_once 'conexion.php';
require_once 'funciones_correo_real.php';

// Simular datos del formulario
$correo = $_GET['correo'] ?? 'test@example.com';
$clave = $_GET['clave'] ?? 'TECNM-TEST-2025-001';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Envío de Correo - Formulario</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #1b396a; border-bottom: 3px solid #1b396a; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #007bff; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        .info { border-left-color: #17a2b8; background: #d1ecf1; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .test-form { background: #e9ecef; padding: 20px; border-radius: 5px; margin: 20px 0; }
        input[type='email'], input[type='text'] { padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Diagnóstico: Envío de Correo desde Formulario</h1>
        
        <div class='test-form'>
            <h3>Probar con datos personalizados:</h3>
            <form method='GET' action=''>
                <p>
                    <label>Correo de destino:</label><br>
                    <input type='email' name='correo' value='$correo' required>
                </p>
                <p>
                    <label>Código de insignia:</label><br>
                    <input type='text' name='clave' value='$clave' required>
                </p>
                <button type='submit'>🔍 Ejecutar Diagnóstico</button>
            </form>
        </div>";

// ============================================
// PASO 1: Verificar que las funciones estén disponibles
// ============================================
echo "<div class='section info'>";
echo "<h2>1️⃣ Verificación de Funciones</h2>";

$funciones_requeridas = [
    'validarCorreo',
    'enviarNotificacionInsigniaCompleta',
    'obtenerMetodoCorreoUsado',
    'generarUrlVerificacion',
    'generarMensajeCorreo'
];

$todas_disponibles = true;
foreach ($funciones_requeridas as $func) {
    if (function_exists($func)) {
        echo "<p>✅ <strong>$func()</strong> está disponible</p>";
    } else {
        echo "<p>❌ <strong>$func()</strong> NO está disponible</p>";
        $todas_disponibles = false;
    }
}

if ($todas_disponibles) {
    echo "<p style='color: #28a745; font-weight: bold;'>✅ Todas las funciones están disponibles</p>";
} else {
    echo "<p style='color: #dc3545; font-weight: bold;'>❌ Faltan funciones. Verifica que funciones_correo_real.php esté incluido correctamente.</p>";
}
echo "</div>";

// ============================================
// PASO 2: Verificar configuración SMTP
// ============================================
echo "<div class='section info'>";
echo "<h2>2️⃣ Verificación de Configuración SMTP</h2>";

if (file_exists('config_smtp.php')) {
    echo "<p>✅ Archivo config_smtp.php existe</p>";
    require_once 'config_smtp.php';
    
    $config_items = [
        'SMTP_FROM_EMAIL' => defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'NO DEFINIDO',
        'SMTP_USERNAME' => defined('SMTP_USERNAME') ? SMTP_USERNAME : 'NO DEFINIDO',
        'SMTP_PASSWORD' => defined('SMTP_PASSWORD') ? (SMTP_PASSWORD === 'CAMBIAR_POR_CONTRASEÑA_REAL' ? '⚠️ NO CONFIGURADA (tiene valor por defecto)' : '✅ Configurada') : 'NO DEFINIDO',
        'SMTP_HOST' => defined('SMTP_HOST') ? SMTP_HOST : 'NO DEFINIDO',
        'SMTP_PORT' => defined('SMTP_PORT') ? SMTP_PORT : 'NO DEFINIDO',
    ];
    
    foreach ($config_items as $key => $value) {
        $icon = (strpos($value, 'NO') !== false || strpos($value, '⚠️') !== false) ? '❌' : '✅';
        echo "<p>$icon <strong>$key:</strong> $value</p>";
    }
    
    if (defined('SMTP_PASSWORD') && SMTP_PASSWORD === 'CAMBIAR_POR_CONTRASEÑA_REAL') {
        echo "<p style='color: #dc3545; font-weight: bold;'>⚠️ PROBLEMA: La contraseña SMTP no está configurada. Edita config_smtp.php</p>";
    }
} else {
    echo "<p>❌ Archivo config_smtp.php NO existe</p>";
}
echo "</div>";

// ============================================
// PASO 3: Validar correo
// ============================================
echo "<div class='section info'>";
echo "<h2>3️⃣ Validación de Correo</h2>";

if (function_exists('validarCorreo')) {
    $correo_valido = validarCorreo($correo);
    if ($correo_valido) {
        echo "<p>✅ Correo <strong>$correo</strong> es válido</p>";
    } else {
        echo "<p>❌ Correo <strong>$correo</strong> NO es válido</p>";
    }
} else {
    echo "<p>❌ Función validarCorreo() no está disponible</p>";
}
echo "</div>";

// ============================================
// PASO 4: Simular envío exacto del formulario
// ============================================
echo "<div class='section info'>";
echo "<h2>4️⃣ Simulación de Envío (igual que en metadatos_formulario.php)</h2>";

if (function_exists('validarCorreo') && validarCorreo($correo)) {
    try {
        // Datos simulados (igual que en el formulario)
        $datos_correo = [
            'estudiante' => 'Estudiante de Prueba',
            'matricula' => 'TEST123',
            'curp' => 'TEST123456TEST01',
            'nombre_insignia' => 'Insignia de Prueba',
            'categoria' => 'Formación Integral',
            'codigo_insignia' => $clave,
            'periodo' => '2025-1',
            'fecha_otorgamiento' => date('Y-m-d'),
            'responsable' => 'Sistema de Prueba',
            'descripcion' => 'Esta es una prueba del sistema de envío de correos',
            'url_verificacion' => generarUrlVerificacion($clave),
            'url_imagen' => 'http://localhost/imagen/Insignias/insignia_default.png'
        ];
        
        echo "<p>📧 Intentando enviar correo a: <strong>$correo</strong></p>";
        echo "<p>📋 Datos del correo:</p>";
        echo "<pre>" . print_r($datos_correo, true) . "</pre>";
        
        // Intentar enviar (igual que en metadatos_formulario.php línea 847)
        $correo_enviado = enviarNotificacionInsigniaCompleta($correo, $datos_correo);
        
        // Obtener método usado
        $metodo_usado = obtenerMetodoCorreoUsado();
        
        if ($correo_enviado) {
            echo "<div class='section success'>";
            echo "<h3>✅ Correo enviado exitosamente</h3>";
            echo "<p><strong>Método usado:</strong> $metodo_usado</p>";
            if ($metodo_usado === 'phpmailer') {
                echo "<p>⚡ <strong>ENVIADO EN TIEMPO REAL</strong> - Debería llegar en menos de 1 minuto</p>";
            } elseif ($metodo_usado === 'nativo') {
                echo "<p>⚠️ Enviado con mail() nativo - Puede tardar 1-5 minutos. Revisa también spam.</p>";
            } elseif ($metodo_usado === 'simulacion') {
                echo "<p>⚠️ <strong>SOLO SIMULACIÓN</strong> - El correo NO se envió realmente. Se guardó en correos_enviados.txt</p>";
                echo "<p>📝 Revisa el archivo correos_enviados.txt para ver el contenido del correo simulado.</p>";
            }
            echo "</div>";
        } else {
            echo "<div class='section error'>";
            echo "<h3>❌ Error: No se pudo enviar el correo</h3>";
            echo "<p><strong>Método intentado:</strong> $metodo_usado</p>";
            echo "<p><strong>Posibles causas:</strong></p>";
            echo "<ul>";
            echo "<li>Credenciales SMTP incorrectas o no configuradas</li>";
            echo "<li>Servidor SMTP rechazó la conexión</li>";
            echo "<li>Firewall bloqueando el puerto 587</li>";
            echo "<li>Office 365 requiere contraseña de aplicación (si tienes 2FA)</li>";
            echo "</ul>";
            echo "<p><strong>Revisa los logs de error de PHP para más detalles:</strong></p>";
            echo "<pre>tail -n 50 /var/log/apache2/error.log | grep -i 'correo\|smtp\|phpmailer'</pre>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='section error'>";
        echo "<h3>❌ Excepción al enviar correo</h3>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Stack trace:</strong></p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    }
} else {
    echo "<div class='section warning'>";
    echo "<p>⚠️ El correo no es válido, no se puede enviar</p>";
    echo "</div>";
}
echo "</div>";

// ============================================
// PASO 5: Verificar logs recientes
// ============================================
echo "<div class='section info'>";
echo "<h2>5️⃣ Logs Recientes de Correo</h2>";

// Intentar leer el archivo de correos simulados
$archivos_log = [
    __DIR__ . '/correos_enviados.txt',
    '/tmp/correos_enviados.txt',
    sys_get_temp_dir() . '/correos_enviados.txt'
];

$log_encontrado = false;
foreach ($archivos_log as $archivo) {
    if (file_exists($archivo) && is_readable($archivo)) {
        echo "<p>✅ Archivo de log encontrado: <strong>$archivo</strong></p>";
        $contenido = file_get_contents($archivo);
        $lineas = explode("\n", $contenido);
        $ultimas_lineas = array_slice($lineas, -20); // Últimas 20 líneas
        echo "<p>Últimas líneas del log:</p>";
        echo "<pre>" . htmlspecialchars(implode("\n", $ultimas_lineas)) . "</pre>";
        $log_encontrado = true;
        break;
    }
}

if (!$log_encontrado) {
    echo "<p>ℹ️ No se encontró archivo de log de correos simulados</p>";
}
echo "</div>";

// ============================================
// RESUMEN Y SOLUCIONES
// ============================================
echo "<div class='section warning'>";
echo "<h2>📋 Resumen y Soluciones</h2>";

if (defined('SMTP_PASSWORD') && SMTP_PASSWORD === 'CAMBIAR_POR_CONTRASEÑA_REAL') {
    echo "<h3>⚠️ PROBLEMA PRINCIPAL: Credenciales SMTP no configuradas</h3>";
    echo "<p><strong>Solución:</strong></p>";
    echo "<ol>";
    echo "<li>Edita el archivo <strong>config_smtp.php</strong></li>";
    echo "<li>Cambia <code>SMTP_PASSWORD</code> de <code>'CAMBIAR_POR_CONTRASEÑA_REAL'</code> a tu contraseña real</li>";
    echo "<li>Si usas Office 365 con 2FA, necesitas una <strong>contraseña de aplicación</strong></li>";
    echo "<li>Obtén la contraseña de aplicación en: <a href='https://account.microsoft.com/security/app-passwords' target='_blank'>https://account.microsoft.com/security/app-passwords</a></li>";
    echo "</ol>";
}

echo "<h3>🔧 Otras soluciones:</h3>";
echo "<ul>";
echo "<li>Si el método usado es 'simulacion', el correo NO se envía realmente. Configura SMTP correctamente.</li>";
echo "<li>Si el método usado es 'nativo', puede tardar 1-5 minutos. Revisa también la carpeta de spam.</li>";
echo "<li>Si el método usado es 'phpmailer', el correo debería llegar en menos de 1 minuto.</li>";
echo "<li>Revisa los logs de error de PHP para ver errores específicos de SMTP.</li>";
echo "</ul>";
echo "</div>";

echo "</div>
</body>
</html>";
?>

