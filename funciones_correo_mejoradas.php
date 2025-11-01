<?php
/**
 * Funciones mejoradas para envío de correos con PHPMailer
 */

// Función para enviar correo usando PHPMailer (si está disponible)
function enviarNotificacionInsigniaMejorada($destinatario_email, $datos_insignia) {
    // Intentar usar PHPMailer si está disponible
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return enviarConPHPMailer($destinatario_email, $datos_insignia);
    }
    
    // Fallback a mail() nativo
    return enviarConMailNativo($destinatario_email, $datos_insignia);
}

// Función usando PHPMailer
function enviarConPHPMailer($destinatario_email, $datos_insignia) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tu-correo@gmail.com'; // Cambiar por tu correo
        $mail->Password = 'tu-contraseña-app'; // Cambiar por tu contraseña de aplicación
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Configuración del correo
        $mail->setFrom('noreply@tecnm.mx', 'Sistema Insignias TecNM');
        $mail->addAddress($destinatario_email);
        $mail->isHTML(true);
        
        $mail->Subject = "🎖️ Insignia Otorgada - " . $datos_insignia['nombre_insignia'];
        $mail->Body = generarMensajeCorreo($datos_insignia);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error PHPMailer: " . $e->getMessage());
        return false;
    }
}

// Función usando mail() nativo con mejor configuración
function enviarConMailNativo($destinatario_email, $datos_insignia) {
    $asunto = "🎖️ Insignia Otorgada - " . $datos_insignia['nombre_insignia'];
    $mensaje = generarMensajeCorreo($datos_insignia);
    
    // Headers mejorados
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Sistema Insignias TecNM <noreply@tecnm.mx>" . "\r\n";
    $headers .= "Reply-To: noreply@tecnm.mx" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "X-Priority: 3" . "\r\n";
    
    // Intentar envío
    $enviado = mail($destinatario_email, $asunto, $mensaje, $headers);
    
    if (!$enviado) {
        error_log("Error al enviar correo a: " . $destinatario_email);
        return false;
    }
    
    return true;
}

// Función para probar configuración de correo
function probarConfiguracionCorreo() {
    echo "<h2>🧪 Prueba de Configuración de Correo</h2>";
    
    // Verificar configuración PHP
    echo "<h3>📋 Configuración PHP:</h3>";
    echo "<p><strong>SMTP:</strong> " . ini_get('SMTP') . "</p>";
    echo "<p><strong>smtp_port:</strong> " . ini_get('smtp_port') . "</p>";
    echo "<p><strong>sendmail_from:</strong> " . ini_get('sendmail_from') . "</p>";
    
    // Verificar si mail() está habilitado
    echo "<h3>📧 Función mail():</h3>";
    if (function_exists('mail')) {
        echo "<p>✅ La función mail() está disponible</p>";
    } else {
        echo "<p>❌ La función mail() NO está disponible</p>";
    }
    
    // Probar envío de correo de prueba
    echo "<h3>🧪 Prueba de Envío:</h3>";
    $correo_prueba = "test@example.com";
    $asunto_prueba = "Prueba de Sistema Insignias";
    $mensaje_prueba = "<h1>Prueba</h1><p>Este es un correo de prueba.</p>";
    
    $headers_prueba = "MIME-Version: 1.0" . "\r\n";
    $headers_prueba .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers_prueba .= "From: Sistema Insignias TecNM <noreply@tecnm.mx>" . "\r\n";
    
    echo "<p>Intentando enviar correo de prueba...</p>";
    
    if (mail($correo_prueba, $asunto_prueba, $mensaje_prueba, $headers_prueba)) {
        echo "<p>✅ Correo enviado exitosamente</p>";
    } else {
        echo "<p>❌ Error al enviar correo</p>";
        echo "<p><strong>Posibles soluciones:</strong></p>";
        echo "<ul>";
        echo "<li>Configurar SMTP en php.ini</li>";
        echo "<li>Instalar PHPMailer</li>";
        echo "<li>Usar servicio de correo externo</li>";
        echo "</ul>";
    }
}

// Función para generar mensaje de correo (reutilizada)
function generarMensajeCorreo($datos) {
    $html = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Insignia Otorgada</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f4f4f4;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .header {
                background: linear-gradient(135deg, #1b396a, #002855);
                color: white;
                padding: 20px;
                border-radius: 10px 10px 0 0;
                text-align: center;
                margin: -30px -30px 30px -30px;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .badge {
                display: inline-block;
                background: linear-gradient(135deg, #28a745, #20c997);
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-weight: bold;
                margin: 10px 0;
            }
            .info-section {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
            .info-section h3 {
                color: #1b396a;
                margin-top: 0;
            }
            .info-row {
                display: flex;
                justify-content: space-between;
                margin: 10px 0;
                padding: 8px 0;
                border-bottom: 1px solid #e9ecef;
            }
            .info-label {
                font-weight: bold;
                color: #495057;
            }
            .info-value {
                color: #212529;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 2px solid #e9ecef;
                color: #6c757d;
                font-size: 14px;
            }
            .btn {
                display: inline-block;
                background: linear-gradient(135deg, #007bff, #0056b3);
                color: white;
                padding: 12px 24px;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                margin: 20px 0;
            }
            .btn:hover {
                background: linear-gradient(135deg, #0056b3, #004085);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🎖️ Sistema de Insignias TecNM</h1>
                <p>¡Felicitaciones! Has recibido una nueva insignia</p>
            </div>
            
            <div class="badge">' . htmlspecialchars($datos['nombre_insignia']) . '</div>
            
            <div class="info-section">
                <h3>📋 Información de la Insignia</h3>
                <div class="info-row">
                    <span class="info-label">Estudiante:</span>
                    <span class="info-value">' . htmlspecialchars($datos['estudiante']) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Matrícula:</span>
                    <span class="info-value">' . htmlspecialchars($datos['matricula']) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">CURP:</span>
                    <span class="info-value">' . htmlspecialchars($datos['curp']) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Código de Insignia:</span>
                    <span class="info-value"><strong>' . htmlspecialchars($datos['codigo_insignia']) . '</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Categoría:</span>
                    <span class="info-value">' . htmlspecialchars($datos['categoria']) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Período:</span>
                    <span class="info-value">' . htmlspecialchars($datos['periodo']) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de Otorgamiento:</span>
                    <span class="info-value">' . htmlspecialchars($datos['fecha_otorgamiento']) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Responsable:</span>
                    <span class="info-value">' . htmlspecialchars($datos['responsable']) . '</span>
                </div>
            </div>
            
            <div class="info-section">
                <h3>📝 Descripción</h3>
                <p>' . htmlspecialchars($datos['descripcion']) . '</p>
            </div>
            
            <div style="text-align: center;">
                <a href="' . $datos['url_verificacion'] . '" class="btn">🔍 Verificar Insignia</a>
            </div>
            
            <div class="footer">
                <p><strong>Tecnológico Nacional de México</strong></p>
                <p>Este correo fue enviado automáticamente por el Sistema de Insignias TecNM</p>
                <p>Para más información, contacta a tu institución educativa</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

// Función para validar correo
function validarCorreo($correo) {
    return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para generar URL de verificación
function generarUrlVerificacion($codigo_insignia, $base_url = '') {
    if (empty($base_url)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']);
    }
    
    return $base_url . '/verificar_insignia.php?clave=' . urlencode($codigo_insignia);
}
?>
