<?php
/**
 * Funciones para envío REAL de correos con PHPMailer
 * Usa la configuración exitosa de prueba_simple.php
 */

// Verificar si PHPMailer está disponible
if (!file_exists('src/PHPMailer.php')) {
    error_log("PHPMailer no encontrado - usando simulación");
    require_once 'funciones_correo_simulacion.php';
    return;
}

// Incluir PHPMailer (usar require_once para evitar doble declaración)
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    require_once 'src/Exception.php';
    require_once 'src/PHPMailer.php';
    require_once 'src/SMTP.php';
}

// Declarar uso de clases PHPMailer (debe estar al nivel superior)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Variables globales para rastrear el método de correo usado
$metodo_correo_usado = 'simulacion'; // Por defecto
$mail_nativo_usó_phpmailer = false; // Indica si mail() nativo usó PHPMailer internamente

/**
 * Envía notificación REAL por correo cuando se otorga una insignia
 * Usa la configuración exitosa de prueba_simple.php
 */
function enviarNotificacionInsigniaCompleta($destinatario_email, $datos_insignia) {
    $asunto = "🎖️ Insignia Otorgada - " . ($datos_insignia['nombre_insignia'] ?? 'Nueva Insignia');
    $mensaje_html = generarMensajeCorreo($datos_insignia);
    
    // Guardar método usado en variable global para que pueda ser consultado
    global $metodo_correo_usado, $mail_nativo_usó_phpmailer;
    $metodo_correo_usado = 'simulacion'; // Por defecto
    $mail_nativo_usó_phpmailer = false; // Resetear
    
    // 1. INTENTAR PRIMERO PHPMailer (SMTP real)
    if (file_exists('config_smtp.php')) {
        $enviadorSMTP = enviarConPHPMailerReal($destinatario_email, $asunto, $mensaje_html, $datos_insignia);
        
        if ($enviadorSMTP === true) {
            $metodo_correo_usado = 'phpmailer';
            error_log("✅ Correo PHPMailer enviado exitosamente (TIEMPO REAL) a: " . $destinatario_email);
            return true;
        }
    }
    
    // 2. RESPALDO: función mail() nativa
    $enviadorNativo = enviarConMailNativo($destinatario_email, $asunto, $mensaje_html);
    
    if ($enviadorNativo === true) {
        // Si mail() nativo usó PHPMailer internamente, marcar como phpmailer (tiempo real)
        if ($mail_nativo_usó_phpmailer) {
            $metodo_correo_usado = 'phpmailer';
            error_log("✅ Correo NATIVO (usando PHPMailer internamente) enviado exitosamente (TIEMPO REAL) a: " . $destinatario_email);
        } else {
            $metodo_correo_usado = 'nativo';
            error_log("✅ Correo NATIVO enviado exitosamente (puede tener retrasos) a: " . $destinatario_email);
        }
        return true;
    }
    
    // 3. ÚLTIMO RECURSO: simulación interna
    $metodo_correo_usado = 'simulacion';
    error_log("⚠️⚠️⚠️ ADVERTENCIA: Todos los métodos de envío real fallaron");
    error_log("   Se está usando SIMULACIÓN para: " . $destinatario_email);
    error_log("   ⚠️ EL CORREO NO SE ENVIÓ REALMENTE - Solo se guardó en archivo");
    error_log("   POSIBLES SOLUCIONES:");
    error_log("   1. Si el servidor de TecNM permite envío sin credenciales, verifica la conexión de red");
    error_log("   2. Si el servidor requiere autenticación, configura SMTP_PASSWORD en config_smtp.php");
    error_log("   3. Si usas Office 365/Gmail, necesitas credenciales y contraseña de aplicación");
    error_log("   4. Revisa el archivo correos_enviados.txt para ver el correo simulado");
    error_log("   5. Revisa los logs anteriores para ver el error específico que causó el fallo");
    return enviarCorreoSimuladoInterno($destinatario_email, $asunto, $mensaje_html, $datos_insignia);
}

/**
 * Obtiene el método de correo que se usó en el último envío
 */
function obtenerMetodoCorreoUsado() {
    global $metodo_correo_usado;
    return $metodo_correo_usado ?? 'desconocido';
}

/**
 * Envía correo usando mail() nativo de PHP
 * SOLUCIÓN: Si PHPMailer está disponible, lo usa internamente para tiempo real
 * Si no, usa mail() nativo normal
 */
function enviarConMailNativo($destinatario_email, $asunto, $mensaje_html) {
    // SOLUCIÓN DEFINITIVA: Si tenemos configuración SMTP, usar PHPMailer internamente
    // Esto garantiza tiempo real sin depender de sendmail
    if (file_exists('config_smtp.php')) {
        require_once 'config_smtp.php';
        
        // Si tenemos credenciales, intentar usar PHPMailer internamente
        if (defined('SMTP_USERNAME') && defined('SMTP_PASSWORD') && 
            !empty(SMTP_USERNAME) && !empty(SMTP_PASSWORD) && 
            SMTP_PASSWORD !== 'CONTRASEÑA_QUE_TE_DEN_PARA_ESTE_CORREO') {
            
            // Intentar usar PHPMailer con servidores de TecNM (sin autenticación primero)
            if (file_exists('src/PHPMailer.php') && !class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                try {
                    require_once 'src/Exception.php';
                    require_once 'src/PHPMailer.php';
                    require_once 'src/SMTP.php';
                    
                    // PHPMailer ya está incluido al inicio del archivo
                    
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    
                    // Probar servidores de TecNM primero (sin autenticación)
                    $servidores_tecnm = ['smtp.tecnm.mx', 'mail.tecnm.mx', 'smtp.smarcos.tecnm.mx'];
                    
                    foreach ($servidores_tecnm as $servidor) {
                        try {
                            $mail->clearAddresses();
                            $mail->Host = $servidor;
                            $mail->SMTPAuth = false; // Sin autenticación primero
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = 587;
                            $mail->CharSet = 'UTF-8';
                            $mail->SMTPDebug = 0;
                            $mail->Timeout = 10;
                            $mail->SMTPOptions = array(
                                'ssl' => array(
                                    'verify_peer' => false,
                                    'verify_peer_name' => false,
                                    'allow_self_signed' => true
                                )
                            );
                            
                            $from_email = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'sistema.insignias@smarcos.tecnm.mx';
                            $from_name = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'sistema insignias';
                            $mail->setFrom($from_email, $from_name);
                            $mail->addAddress($destinatario_email);
                            $mail->isHTML(true);
                            $mail->Subject = $asunto;
                            $mail->Body = $mensaje_html;
                            $mail->AltBody = strip_tags($mensaje_html);
                            
                            $mail->send();
                            
                            // Marcar que mail() nativo usó PHPMailer internamente (tiempo real)
                            global $mail_nativo_usó_phpmailer;
                            $mail_nativo_usó_phpmailer = true;
                            
                            error_log("✅ Correo NATIVO (usando PHPMailer internamente) enviado en TIEMPO REAL a: " . $destinatario_email . " via $servidor");
                            return true;
                        } catch (Exception $e) {
                            // Si falla sin auth, intentar con auth
                            if (!empty(SMTP_USERNAME) && !empty(SMTP_PASSWORD)) {
                                try {
                                    $mail2 = new PHPMailer(true);
                                    $mail2->isSMTP();
                                    $mail2->Host = $servidor;
                                    $mail2->SMTPAuth = true;
                                    $mail2->Username = SMTP_USERNAME;
                                    $mail2->Password = SMTP_PASSWORD;
                                    $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                    $mail2->Port = 587;
                                    $mail2->CharSet = 'UTF-8';
                                    $mail2->SMTPDebug = 0;
                                    $mail2->Timeout = 10;
                                    $mail2->SMTPOptions = array(
                                        'ssl' => array(
                                            'verify_peer' => false,
                                            'verify_peer_name' => false,
                                            'allow_self_signed' => true
                                        )
                                    );
                                    $mail2->setFrom($from_email, $from_name);
                                    $mail2->addAddress($destinatario_email);
                                    $mail2->isHTML(true);
                                    $mail2->Subject = $asunto;
                                    $mail2->Body = $mensaje_html;
                                    $mail2->AltBody = strip_tags($mensaje_html);
                                    $mail2->send();
                                    
                                    // Marcar que mail() nativo usó PHPMailer internamente (tiempo real)
                                    global $mail_nativo_usó_phpmailer;
                                    $mail_nativo_usó_phpmailer = true;
                                    
                                    error_log("✅ Correo NATIVO (usando PHPMailer con auth) enviado en TIEMPO REAL a: " . $destinatario_email . " via $servidor");
                                    return true;
                                } catch (Exception $e2) {
                                    continue; // Probar siguiente servidor
                                }
                            }
                            continue; // Probar siguiente servidor
                        }
                    }
                } catch (Exception $e) {
                    // Si PHPMailer falla, continuar con mail() nativo normal
                    error_log("⚠️ PHPMailer no disponible en mail() nativo, usando sendmail");
                }
            }
        }
    }
    
    // Fallback: mail() nativo normal (puede tener retrasos)
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $from_email = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'sistema.insignias@smarcos.tecnm.mx';
    $from_name = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'sistema insignias';
    $headers .= "From: $from_name <$from_email>" . "\r\n";
    $headers .= "Reply-To: $from_email" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    $resultado = @mail($destinatario_email, $asunto, $mensaje_html, $headers);
    
    if ($resultado) {
        // Procesar cola inmediatamente
        @exec('sendmail -q 2>/dev/null &');
        error_log("✅ Correo NATIVO enviado (puede tener retrasos) a: " . $destinatario_email);
        return true;
    } else {
        error_log("❌ Error en correo NATIVO para: " . $destinatario_email);
        return false;
    }
}

/**
 * Envía correo usando PHPMailer con la configuración exitosa
 */
function enviarConPHPMailerReal($destinatario_email, $asunto, $mensaje_html, $datos_insignia) {
    $mail = new PHPMailer(true);

    try {
        // Cargar configuración SMTP desde config_smtp.php
        $tu_correo = '';
        $tu_contraseña = '';
        $servidores = [];
        
        if (file_exists('config_smtp.php')) {
            require_once 'config_smtp.php';
            $tu_correo = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
            $tu_contraseña = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
            
            // Agregar servidor principal PRIMERO (tiene prioridad)
            if (defined('SMTP_HOST') && defined('SMTP_PORT')) {
                $encryption = defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls';
                $servidores[SMTP_HOST] = [
                    'port' => SMTP_PORT,
                    'encryption' => $encryption,
                    'auth' => true
                ];
            }
            
            // Agregar servidores alternativos DESPUÉS (orden se mantiene)
            if (defined('SMTP_SERVERS_ALTERNATIVOS')) {
                foreach (SMTP_SERVERS_ALTERNATIVOS as $host => $config) {
                    // No sobrescribir el servidor principal si ya está configurado
                    if (!isset($servidores[$host])) {
                        $servidores[$host] = $config;
                    }
                }
            }
            
            // Reordenar para que el servidor principal esté primero
            if (defined('SMTP_HOST') && isset($servidores[SMTP_HOST])) {
                $servidor_principal = [SMTP_HOST => $servidores[SMTP_HOST]];
                unset($servidores[SMTP_HOST]);
                $servidores = $servidor_principal + $servidores;
            }
        }
        
        // Si no hay configuración, usar valores por defecto
        // PRIORIDAD: Probar primero servidores de TecNM (pueden no requerir autenticación moderna)
        if (empty($servidores)) {
            $servidores = [
                'smtp.tecnm.mx' => ['port' => 587, 'encryption' => 'tls', 'auth' => true],
                'mail.tecnm.mx' => ['port' => 587, 'encryption' => 'tls', 'auth' => true],
                'smtp.smarcos.tecnm.mx' => ['port' => 587, 'encryption' => 'tls', 'auth' => true],
                'smtp-mail.outlook.com' => ['port' => 587, 'encryption' => 'tls', 'auth' => true], // Office 365 alternativo
                'smtp.office365.com' => ['port' => 587, 'encryption' => 'tls', 'auth' => true], // Office 365 principal
            ];
        }
        
        // Validar que tenemos credenciales
        if (empty($tu_correo) || empty($tu_contraseña)) {
            error_log("❌ PHPMailer: No hay credenciales SMTP configuradas en config_smtp.php");
            return false;
        }

        $funciono = false;
        $servidor_exitoso = '';
        $ultimo_error = '';
        
        // Debug: Log de servidores que se probarán
        $lista_servidores = array_keys($servidores);
        error_log("🔍 PHPMailer: Probando servidores en orden: " . implode(", ", $lista_servidores));

        foreach ($servidores as $servidor => $config) {
            error_log("🔍 PHPMailer: Intentando con servidor: $servidor");
            try {
                $mail = new PHPMailer(true);
                $mail->clearAddresses();
                
                // Configuración SMTP
                $mail->isSMTP();
                $mail->Host = $servidor;
                
                // Configurar encriptación
                $puerto = $config['port'] ?? 587;
                $encryption = $config['encryption'] ?? 'tls';
                $requiere_auth = $config['auth'] ?? true;
                
                if ($encryption === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
                
                $mail->Port = $puerto;
                $mail->CharSet = 'UTF-8';
                $mail->SMTPDebug = defined('SMTP_DEBUG') && SMTP_DEBUG ? 2 : 0;
                $mail->Timeout = defined('SMTP_TIMEOUT') ? SMTP_TIMEOUT : 30;
                
                // Configurar autenticación
                // Para servidores de TecNM, intentar primero sin autenticación
                $es_servidor_tecnm = (strpos($servidor, 'tecnm.mx') !== false || strpos($servidor, 'smarcos.tecnm.mx') !== false);
                
                if ($es_servidor_tecnm) {
                    // Servidores de TecNM: Intentar primero sin autenticación
                    $mail->SMTPAuth = false;
                    error_log("🔍 Probando servidor TecNM sin autenticación: $servidor");
                } elseif ($requiere_auth && !empty($tu_correo) && !empty($tu_contraseña)) {
                    // Office 365 y otros: Requieren autenticación
                    $mail->SMTPAuth = true;
                    $mail->Username = $tu_correo;
                    $mail->Password = $tu_contraseña;
                } else {
                    $mail->SMTPAuth = false;
                }
                
                // SSL options - Configuración mejorada para diferentes servidores
                $verify_ssl = defined('SMTP_VERIFY_SSL') ? SMTP_VERIFY_SSL : false;
                
                // Para Office 365, necesitamos configuración especial
                if (strpos($servidor, 'office365') !== false || strpos($servidor, 'outlook') !== false) {
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $mail->SMTPKeepAlive = true;
                    // Office 365 puede requerir autenticación moderna
                    // Intentamos primero con contraseña normal, si falla necesitará contraseña de aplicación
                } else {
                    // Para otros servidores (TecNM), configuración más flexible
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => $verify_ssl,
                            'verify_peer_name' => $verify_ssl,
                            'allow_self_signed' => !$verify_ssl
                        )
                    );
                }

                // Configurar correo
                $from_name = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'sistema insignias';
                $mail->setFrom($tu_correo, $from_name);
                $mail->addAddress($destinatario_email, $datos_insignia['estudiante'] ?? '');

                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = $asunto;
                $mail->Body = $mensaje_html;
                $mail->AltBody = strip_tags($mensaje_html);

                // Enviar
                $mail->send();
                
                error_log("✅ Correo enviado exitosamente usando servidor: $servidor:$puerto ($encryption)");
                $funciono = true;
                $servidor_exitoso = $servidor;
                break;
                
            } catch (Exception $e) {
                $mensaje_error = $e->getMessage();
                $es_servidor_tecnm = (strpos($servidor, 'tecnm.mx') !== false || strpos($servidor, 'smarcos.tecnm.mx') !== false);
                
                // Si es servidor TecNM y falló sin autenticación, intentar CON autenticación
                if ($es_servidor_tecnm && !$mail->SMTPAuth && !empty($tu_correo) && !empty($tu_contraseña)) {
                    error_log("⚠️ Servidor TecNM falló sin autenticación, intentando CON autenticación...");
                    try {
                        $mail2 = new PHPMailer(true);
                        $mail2->isSMTP();
                        $mail2->Host = $servidor;
                        $mail2->SMTPAuth = true;
                        $mail2->Username = $tu_correo;
                        $mail2->Password = $tu_contraseña;
                        $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail2->Port = $puerto;
                        $mail2->CharSet = 'UTF-8';
                        $mail2->SMTPDebug = 0;
                        $mail2->Timeout = 30;
                        $mail2->SMTPOptions = array(
                            'ssl' => array(
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            )
                        );
                        $from_name = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'sistema insignias';
                        $mail2->setFrom($tu_correo, $from_name);
                        $mail2->addAddress($destinatario_email, $datos_insignia['estudiante'] ?? '');
                        $mail2->isHTML(true);
                        $mail2->Subject = $asunto;
                        $mail2->Body = $mensaje_html;
                        $mail2->AltBody = strip_tags($mensaje_html);
                        $mail2->send();
                        
                        error_log("✅ Correo enviado exitosamente usando servidor TecNM CON autenticación: $servidor:$puerto");
                        $funciono = true;
                        $servidor_exitoso = $servidor;
                        break;
                    } catch (Exception $e2) {
                        $ultimo_error = "Error con servidor $servidor:$puerto ($encryption) - " . $e2->getMessage();
                        error_log("❌ $ultimo_error (también falló con autenticación)");
                    }
                } else {
                    $ultimo_error = "Error con servidor $servidor:$puerto ($encryption) - " . $mensaje_error;
                    error_log("❌ $ultimo_error");
                    
                    // Mensaje más específico para errores de autenticación
                    if (stripos($mensaje_error, 'authenticate') !== false || stripos($mensaje_error, 'authentication') !== false) {
                        if (strpos($servidor, 'office365') !== false || strpos($servidor, 'outlook') !== false) {
                            error_log("⚠️ Office 365 requiere contraseña de aplicación. Ve a: https://account.microsoft.com/security/app-passwords");
                        } else {
                            error_log("⚠️ Error de autenticación. Verifica credenciales en config_smtp.php");
                        }
                    }
                }
                
                continue;
            }
        }

        if (!$funciono) {
            error_log("❌ Todos los servidores SMTP fallaron. Último error: $ultimo_error");
        }

        return $funciono;
        
    } catch (Exception $e) {
        error_log("❌ Error general PHPMailer: " . $e->getMessage());
        return false;
    }
}

/**
 * Genera mensaje HTML para el correo (reutilizada de funciones_correo_simulacion.php)
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
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . htmlspecialchars($datos['url_verificacion']) . '" 
                   target="_blank"
                   style="display: inline-block; cursor: pointer; text-decoration: none;">
                    <img src="' . htmlspecialchars($datos['url_imagen'] ?? '') . '" 
                         alt="' . htmlspecialchars($datos['nombre_insignia']) . '" 
                         style="max-width: 300px; height: auto; border: 3px solid #1b396a; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); transition: transform 0.3s ease; cursor: pointer;"
                         onmouseover="this.style.transform=\'scale(1.05)\'; this.style.boxShadow=\'0 6px 12px rgba(0,0,0,0.3)\';" 
                         onmouseout="this.style.transform=\'scale(1)\'; this.style.boxShadow=\'0 4px 8px rgba(0,0,0,0.2)\';"
                         onclick="window.open(\'' . htmlspecialchars($datos['url_verificacion']) . '\', \'_blank\'); return false;"
                         ondblclick="window.open(\'' . htmlspecialchars($datos['url_verificacion']) . '\', \'_blank\'); return false;">
                </a>
                <p style="margin-top: 10px; color: #6c757d; font-size: 14px; font-style: italic;">🖱️ Haz clic o doble clic en la imagen para ver tu certificado completo</p>
            </div>
            
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
 * Función de simulación interna (sin conflicto con funciones_correo_simulacion.php)
 */
function enviarCorreoSimuladoInterno($destinatario, $asunto, $mensaje_html, $datos_insignia = []) {
    // Intentar varios directorios con permisos de escritura
    $directorios_posibles = [
        __DIR__ . '/correos_enviados.txt',
        '/tmp/correos_enviados.txt',
        sys_get_temp_dir() . '/correos_enviados.txt',
        __DIR__ . '/logs/correos_enviados.txt'
    ];
    
    $archivo = null;
    foreach ($directorios_posibles as $ruta) {
        $directorio = dirname($ruta);
        if (!is_dir($directorio)) {
            @mkdir($directorio, 0755, true);
        }
        if (is_writable($directorio) || @file_put_contents($ruta, '', FILE_APPEND) !== false) {
            $archivo = $ruta;
            break;
        }
    }
    
    // Si no se puede escribir en ningún lado, solo loguear
    if (!$archivo) {
        error_log("CORREO SIMULADO - " . date('Y-m-d H:i:s') . " - PARA: " . $destinatario . " - ASUNTO: " . $asunto);
        return true; // Retornar true para que no se considere un error total
    }
    
    $contenido = "\n" . str_repeat("=", 80) . "\n";
    $contenido .= "CORREO SIMULADO - " . date('Y-m-d H:i:s') . "\n";
    $contenido .= str_repeat("=", 80) . "\n";
    $contenido .= "PARA: " . $destinatario . "\n";
    $contenido .= "ASUNTO: " . $asunto . "\n";
    $from_email = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'sistema.insignias@smarcos.tecnm.mx';
    $from_name = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'sistema insignias';
    $contenido .= "DE: $from_name <$from_email>\n";
    $contenido .= str_repeat("-", 80) . "\n";
    
    if (!empty($datos_insignia)) {
        $contenido .= "DATOS DE LA INSIGNIA:\n";
        $contenido .= "Estudiante: " . ($datos_insignia['estudiante'] ?? 'N/A') . "\n";
        $contenido .= "Matrícula: " . ($datos_insignia['matricula'] ?? 'N/A') . "\n";
        $contenido .= "CURP: " . ($datos_insignia['curp'] ?? 'N/A') . "\n";
        $contenido .= "Insignia: " . ($datos_insignia['nombre_insignia'] ?? 'N/A') . "\n";
        $contenido .= "Categoría: " . ($datos_insignia['categoria'] ?? 'N/A') . "\n";
        $contenido .= "Código: " . ($datos_insignia['codigo_insignia'] ?? 'N/A') . "\n";
        $contenido .= "Período: " . ($datos_insignia['periodo'] ?? 'N/A') . "\n";
        $contenido .= "Fecha: " . ($datos_insignia['fecha_otorgamiento'] ?? 'N/A') . "\n";
        $contenido .= "Responsable: " . ($datos_insignia['responsable'] ?? 'N/A') . "\n";
        $contenido .= "URL Verificación: " . ($datos_insignia['url_verificacion'] ?? 'N/A') . "\n";
        $contenido .= str_repeat("-", 80) . "\n";
    }
    
    $contenido .= "MENSAJE HTML:\n";
    $contenido .= $mensaje_html . "\n";
    $contenido .= str_repeat("=", 80) . "\n";
    
    // Guardar en archivo con manejo de errores
    $resultado = @file_put_contents($archivo, $contenido, FILE_APPEND | LOCK_EX);
    
    if ($resultado !== false) {
        error_log("Correo simulado guardado exitosamente en: " . $archivo . " para: " . $destinatario);
        return true;
    } else {
        // Si falla, al menos loguear en error_log
        error_log("CORREO SIMULADO (no se pudo guardar en archivo) - " . date('Y-m-d H:i:s') . " - PARA: " . $destinatario . " - ASUNTO: " . $asunto);
        return true; // Retornar true para que no se considere un error total
    }
}

/**
 * Valida formato de correo electrónico
 */
function validarCorreo($correo) {
    return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Genera URL de verificación pública de insignia
 * Esta URL lleva a ver_insignia_publica.php donde se puede ver el certificado completo
 */
function generarUrlVerificacion($codigo_insignia, $base_url = '') {
    if (empty($base_url)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']);
    }
    
    // Usar ver_insignia_publica.php para que el estudiante vea su certificado completo
    return $base_url . '/ver_insignia_publica.php?insignia=' . urlencode($codigo_insignia);
}

/**
 * Genera URL de la imagen de la insignia basándose en el nombre
 */
function generarUrlImagenInsignia($nombre_insignia, $base_url = '') {
    if (empty($base_url)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']);
    }
    
    // Mapeo de nombres de insignias a archivos de imagen (carpeta imagen/Insignias)
    $mapeo_imagenes = [
        'Embajador del Arte' => 'EmbajadordelArte.png',
        'EmbajadordelArte' => 'EmbajadordelArte.png',
        'Embajador del Deporte' => 'EmbajadordelDeporte.png',
        'EmbajadordelDeporte' => 'EmbajadordelDeporte.png',
        'EmbajadordelDeporteOro' => 'EmbajadordelDeporteOro.png',
        'EmbajadordelDeportePlata' => 'EmbajadordelDeportePlata.png',
        'EmbajadordelDeporteBronce' => 'EmbajadordelDeporteBronce.png',
        'Talento Científico' => 'TalentoCientifico.png',
        'Talento Innovador' => 'TalentoInnovador.png',
        'Innovacion' => 'TalentoInnovador.png',
        'Responsabilidad Social' => 'ResponsabilidadSocial.png',
        'Formación y Actualización' => 'FormacionyActualizacion.png',
        'Formacion y Actualizacion' => 'FormacionyActualizacion.png',
        'Movilidad e Intercambio' => 'MovilidadeIntercambio.png',
        'Liderazgo Estudiantil' => 'LiderazgoEstudiantil.png',
        'Emprendimiento' => 'Emprendimiento.png',
        'Sustentabilidad' => 'Sustentabilidad.png'
    ];
    
    $archivo_imagen = 'insignia_default.png';
    foreach ($mapeo_imagenes as $nombre => $archivo) {
        if (stripos($nombre_insignia, $nombre) !== false || stripos($nombre, $nombre_insignia) !== false) {
            $archivo_imagen = $archivo;
            break;
        }
    }
    if ($archivo_imagen === 'insignia_default.png') {
        $archivo_imagen = preg_replace('/\s+/', '', $nombre_insignia) . '.png';
    }
    
    return $base_url . '/imagen/Insignias/' . $archivo_imagen;
}
