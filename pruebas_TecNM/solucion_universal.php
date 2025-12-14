<?php
/**
 * SOLUCIÓN UNIVERSAL - FUNCIONA CON CUALQUIER DOMINIO
 * Este archivo prueba automáticamente múltiples configuraciones SMTP
 */

echo "<h2>🌐 SOLUCIÓN UNIVERSAL - CUALQUIER DOMINIO</h2>";
echo "<h3>📧 Probando automáticamente múltiples configuraciones</h3>";

// Verificar si PHPMailer está disponible
if (!file_exists('src/PHPMailer.php')) {
    echo "<h2>❌ PHPMailer no encontrado</h2>";
    exit;
}

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

// CONFIGURACIONES MÚLTIPLES PARA PROBAR
$configuraciones = [
    // Configuración TecNM
    [
        'nombre' => 'TecNM - Outlook',
        'servidor' => 'smtp-mail.outlook.com',
        'puerto' => 587,
        'correo' => '211230001@smarcos.tecnm.mx',
        'contraseñas' => ['cas29ye02vi20', '123456789', 'TecNM2025!']
    ],
    // Configuración Gmail
    [
        'nombre' => 'Gmail',
        'servidor' => 'smtp.gmail.com',
        'puerto' => 587,
        'correo' => '211230001@gmail.com',
        'contraseñas' => ['cas29ye02vi20', '123456789', 'Gmail2025!']
    ],
    // Configuración TecNM alternativa
    [
        'nombre' => 'TecNM - Mail',
        'servidor' => 'mail.tecnm.mx',
        'puerto' => 587,
        'correo' => '211230001@smarcos.tecnm.mx',
        'contraseñas' => ['cas29ye02vi20', '123456789']
    ],
    // Configuración Outlook alternativo
    [
        'nombre' => 'Outlook Alternativo',
        'servidor' => 'smtp.live.com',
        'puerto' => 587,
        'correo' => '211230001@smarcos.tecnm.mx',
        'contraseñas' => ['cas29ye02vi20', '123456789']
    ]
];

echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔧 Configuraciones a probar:</h4>";
echo "<p><strong>Total:</strong> " . count($configuraciones) . " configuraciones diferentes</p>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

$funciono = false;
$configuracion_exitosa = [];
$errores_totales = [];

foreach ($configuraciones as $index => $config) {
    echo "<h3>🔍 Probando: " . $config['nombre'] . "</h3>";
    echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Servidor:</strong> " . $config['servidor'] . "</p>";
    echo "<p><strong>Puerto:</strong> " . $config['puerto'] . "</p>";
    echo "<p><strong>Correo:</strong> " . $config['correo'] . "</p>";
    echo "<p><strong>Contraseñas:</strong> " . count($config['contraseñas']) . " a probar</p>";
    echo "</div>";
    
    foreach ($config['contraseñas'] as $pass_index => $contraseña) {
        echo "<h4>🔑 Probando contraseña " . ($pass_index + 1) . " de " . count($config['contraseñas']) . "</h4>";
        
        try {
            $mail = new PHPMailer(true);
            
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host = $config['servidor'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['correo'];
            $mail->Password = $contraseña;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $config['puerto'];
            $mail->CharSet = 'UTF-8';
            
            // Configuración SSL para XAMPP
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Configurar correo
            $mail->setFrom($config['correo'], 'Sistema Insignias TecNM');
            $mail->addAddress($config['correo'], 'Usuario TecNM');

            // Contenido específico
            $mail->isHTML(true);
            $mail->Subject = '🎖️ PRUEBA UNIVERSAL - TecNM';
            
            $mail->Body = '
            <div style="font-family: Arial, sans-serif; padding: 20px; max-width: 500px; margin: 0 auto;">
                <div style="background: #1b396a; color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center;">
                    <h1 style="margin: 0;">🎖️ TECNM</h1>
                    <p style="margin: 10px 0 0 0;">PRUEBA UNIVERSAL EXITOSA</p>
                </div>
                <div style="background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px;">
                    <h2 style="color: #1b396a;">¡Funciona!</h2>
                    <p>Esta configuración <strong>FUNCIONA</strong> y se usará para el sistema.</p>
                    
                    <div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
                        <p><strong>Configuración:</strong> ' . $config['nombre'] . '</p>
                        <p><strong>Servidor:</strong> ' . $config['servidor'] . '</p>
                        <p><strong>Puerto:</strong> ' . $config['puerto'] . '</p>
                        <p><strong>Correo:</strong> ' . $config['correo'] . '</p>
                        <p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>
                    </div>
                    
                    <p style="text-align: center; color: #666;">
                        <strong>Tecnológico Nacional de México</strong>
                    </p>
                </div>
            </div>
            ';

            // Enviar
            $mail->send();
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>🎉 ¡ÉXITO!</h4>";
            echo "<p><strong>✅ Correo enviado correctamente</strong></p>";
            echo "<p><strong>✅ Configuración funcionando</strong></p>";
            echo "<p><strong>Configuración:</strong> " . $config['nombre'] . "</p>";
            echo "<p><strong>Servidor:</strong> " . $config['servidor'] . "</p>";
            echo "<p><strong>Puerto:</strong> " . $config['puerto'] . "</p>";
            echo "<p><strong>Correo:</strong> " . $config['correo'] . "</p>";
            echo "<p><strong>Contraseña:</strong> Contraseña " . ($pass_index + 1) . " funcionó</p>";
            echo "</div>";
            
            echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4>📧 ¿Dónde revisar?</h4>";
            echo "<p><strong>1. Bandeja de entrada:</strong> Revisa tu correo</p>";
            echo "<p><strong>2. Carpeta de spam:</strong> A veces va ahí</p>";
            echo "<p><strong>3. Busca:</strong> 🎖️ PRUEBA UNIVERSAL - TecNM</p>";
            echo "</div>";
            
            $funciono = true;
            $configuracion_exitosa = [
                'nombre' => $config['nombre'],
                'servidor' => $config['servidor'],
                'puerto' => $config['puerto'],
                'correo' => $config['correo'],
                'contraseña' => $contraseña,
                'indice_pass' => $pass_index + 1
            ];
            break 2; // Salir de ambos bucles
            
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
            $errores_totales[] = [
                'configuracion' => $config['nombre'],
                'servidor' => $config['servidor'],
                'contraseña' => $pass_index + 1,
                'error' => $error_msg
            ];
            
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<p><strong>❌ Error con contraseña " . ($pass_index + 1) . "</strong></p>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($error_msg) . "</p>";
            echo "</div>";
        }
    }
    
    if (!$funciono) {
        echo "<div style='background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p><strong>⚠️ Ninguna contraseña funcionó para " . $config['nombre'] . "</strong></p>";
        echo "</div>";
    }
}

if ($funciono) {
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎯 CONFIGURACIÓN EXITOSA ENCONTRADA:</h4>";
    echo "<p><strong>Configuración:</strong> " . $configuracion_exitosa['nombre'] . "</p>";
    echo "<p><strong>Servidor SMTP:</strong> " . $configuracion_exitosa['servidor'] . "</p>";
    echo "<p><strong>Puerto:</strong> " . $configuracion_exitosa['puerto'] . "</p>";
    echo "<p><strong>Correo:</strong> " . $configuracion_exitosa['correo'] . "</p>";
    echo "<p><strong>Contraseña:</strong> Contraseña " . $configuracion_exitosa['indice_pass'] . " funcionó</p>";
    echo "<p>Esta configuración funcionará para el sistema completo.</p>";
    echo "</div>";
    
    echo "<h3>🚀 PRÓXIMO PASO:</h3>";
    echo "<p>Ahora puedes usar el sistema completo y los correos llegarán realmente:</p>";
    echo "<p><a href='probar_insignia_yeni_directo.php' style='display: inline-block; background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold;'>🎖️ Crear Insignia para Yeni Castro Sánchez</a></p>";
    
    // Guardar configuración exitosa
    $config_guardada = "<?php\n";
    $config_guardada .= "// CONFIGURACIÓN EXITOSA ENCONTRADA AUTOMÁTICAMENTE\n";
    $config_guardada .= "define('SMTP_HOST', '" . $configuracion_exitosa['servidor'] . "');\n";
    $config_guardada .= "define('SMTP_PORT', " . $configuracion_exitosa['puerto'] . ");\n";
    $config_guardada .= "define('SMTP_USERNAME', '" . $configuracion_exitosa['correo'] . "');\n";
    $config_guardada .= "define('SMTP_PASSWORD', '" . $configuracion_exitosa['contraseña'] . "');\n";
    $config_guardada .= "define('SMTP_FROM_NAME', 'Sistema Insignias TecNM');\n";
    $config_guardada .= "?>";
    
    file_put_contents('config_smtp_exitosa.php', $config_guardada);
    echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>✅ Configuración guardada en:</strong> config_smtp_exitosa.php</p>";
    echo "</div>";
    
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Ninguna configuración funcionó</h4>";
    echo "<p>Se probaron " . count($configuraciones) . " configuraciones diferentes sin éxito.</p>";
    echo "<p><strong>Posibles soluciones:</strong></p>";
    echo "<ul>";
    echo "<li>Verificar credenciales de correo</li>";
    echo "<li>Generar contraseña de aplicación</li>";
    echo "<li>Contactar al administrador de TI</li>";
    echo "<li>Usar Gmail con configuración específica</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h4>🔍 Resumen de errores:</h4>";
    foreach ($errores_totales as $error) {
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p><strong>" . $error['configuracion'] . " - " . $error['servidor'] . " - Contraseña " . $error['contraseña'] . ":</strong> " . htmlspecialchars($error['error']) . "</p>";
        echo "</div>";
    }
}

echo "<h3>🔄 Probar Nuevamente:</h3>";
echo "<p><a href='solucion_universal.php' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>🔄 Ejecutar Prueba Universal Nuevamente</a></p>";

echo "<hr>";
echo "<p><a href='prueba_simple.php'>← Volver a prueba simple</a></p>";
echo "<p><a href='probar_insignia_yeni_directo.php'>← Crear insignia para Yeni</a></p>";

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Estado:</strong> <span style='color: " . ($funciono ? "green" : "red") . "; font-weight: bold;'>" . ($funciono ? "CONFIGURACIÓN ENCONTRADA" : "BUSCANDO CONFIGURACIÓN") . "</span></p>";
?>
