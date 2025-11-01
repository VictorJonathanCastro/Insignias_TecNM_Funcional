<?php
/**
 * Configuración de correos para TecNM - SOLUCIÓN HÍBRIDA
 * Este archivo intenta diferentes configuraciones SMTP para TecNM
 */

// Incluir PHPMailer al principio del archivo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envía notificación por correo cuando se otorga una insignia
 */
function enviarNotificacionInsignia($destinatario_email, $datos_insignia) {
    // Verificar si PHPMailer está disponible
    if (file_exists('src/PHPMailer.php')) {
        return enviarConPHPMailerTecNM($destinatario_email, $datos_insignia);
    } else {
        return enviarConMailNativo($destinatario_email, $datos_insignia);
    }
}

function enviarConPHPMailerTecNM($destinatario_email, $datos_insignia) {
    // Incluir archivos de PHPMailer
    require 'src/Exception.php';
    require 'src/PHPMailer.php';
    require 'src/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        // CONFIGURACIÓN PARA TECNM
        $tu_correo = "211230001@smarcos.tecnm.mx";
        $tu_contraseña = "cas29ye02vi20"; // Tu contraseña real del TecNM
        
        // Lista de servidores SMTP posibles para TecNM
        $servidores_smtp = [
            'smtp.tecnm.mx',
            'mail.tecnm.mx', 
            'smtp.smarcos.tecnm.mx',
            'mail.smarcos.tecnm.mx',
            'smtp-mail.outlook.com', // Por si usa Office 365
            'smtp.gmail.com' // Por si usa Gmail
        ];
        
        $puertos = [587, 465, 25];
        
        foreach ($servidores_smtp as $servidor) {
            foreach ($puertos as $puerto) {
                try {
                    $mail->clearAddresses();
                    
                    // Configuración del servidor SMTP
                    $mail->isSMTP();
                    $mail->Host = $servidor;
                    $mail->SMTPAuth = true;
                    $mail->Username = $tu_correo;
                    $mail->Password = $tu_contraseña;
                    
                    if ($puerto == 465) {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    } else {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    }
                    
                    $mail->Port = $puerto;
                    $mail->CharSet = 'UTF-8';
                    
                    // Configuración SSL para XAMPP
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );

                    // Configurar remitente y destinatario
                    $mail->setFrom($tu_correo, 'Sistema Insignias TecNM');
                    $mail->addAddress($destinatario_email, $datos_insignia['estudiante']);

                    // Contenido del correo
                    $mail->isHTML(true);
                    $mail->Subject = '🎖️ Insignia Otorgada - ' . $datos_insignia['nombre_insignia'];
                    $mail->Body = generarMensajeCorreo($datos_insignia);
                    $mail->AltBody = 'Felicidades! Has recibido una nueva insignia del TecNM: ' . $datos_insignia['nombre_insignia'];

                    // Enviar el correo
                    $mail->send();
                    error_log("Correo enviado exitosamente con TecNM ($servidor:$puerto) a: " . $destinatario_email);
                    return true;
                    
                } catch (Exception $e) {
                    // Continuar con el siguiente servidor/puerto
                    continue;
                }
            }
        }
        
        // Si llegamos aquí, ningún servidor funcionó
        error_log("Ningún servidor SMTP de TecNM funcionó para: " . $destinatario_email);
        return false;
        
    } catch (Exception $e) {
        error_log("Error PHPMailer TecNM al enviar correo a " . $destinatario_email . ": " . $e->getMessage());
        return false;
    }
}

function enviarConMailNativo($destinatario_email, $datos_insignia) {
    $asunto = "🎖️ Insignia Otorgada - " . $datos_insignia['nombre_insignia'];
    $mensaje = generarMensajeCorreo($datos_insignia);
    $headers = generarHeadersCorreo();
    
    // Intentar envío con mail() nativo de PHP
    $enviado = mail($destinatario_email, $asunto, $mensaje, $headers);
    
    if (!$enviado) {
        error_log("Error al enviar correo con mail() nativo a: " . $destinatario_email);
        return false;
    }
    
    return true;
}

/**
 * Genera el mensaje HTML del correo
 */
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

/**
 * Genera los headers del correo
 */
function generarHeadersCorreo() {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Sistema Insignias TecNM <noreply@tecnm.mx>" . "\r\n";
    $headers .= "Reply-To: noreply@tecnm.mx" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    return $headers;
}

/**
 * Valida formato de correo electrónico
 */
function validarCorreo($correo) {
    return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Genera URL de verificación de insignia
 */
function generarUrlVerificacion($codigo_insignia, $base_url = '') {
    if (empty($base_url)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']);
    }
    
    return $base_url . '/verificar_insignia.php?clave=' . urlencode($codigo_insignia);
}
?>
