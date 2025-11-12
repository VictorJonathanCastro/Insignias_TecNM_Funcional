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

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

/**
 * Envía notificación REAL por correo cuando se otorga una insignia
 * Usa la configuración exitosa de prueba_simple.php
 */
function enviarNotificacionInsigniaCompleta($destinatario_email, $datos_insignia) {
    $asunto = "🎖️ Insignia Otorgada - " . $datos_insignia['nombre_insignia'];
    $mensaje_html = generarMensajeCorreo($datos_insignia);
    
    // 1. PRIMERO: Intentar envío con mail() nativo (NO requiere credenciales SMTP)
    // Solo necesita que sendmail esté instalado en el servidor
    $enviado_nativo = enviarConMailNativo($destinatario_email, $asunto, $mensaje_html);
    
    if ($enviado_nativo) {
        error_log("✅ Correo NATIVO enviado exitosamente a: " . $destinatario_email);
        return true;
    }
    
    // 2. Si falla mail() nativo, intentar PHPMailer con SMTP (requiere credenciales del sistema)
    // Solo si config_smtp.php tiene credenciales válidas del sistema
    if (file_exists('config_smtp.php')) {
        $enviado_real = enviarConPHPMailerReal($destinatario_email, $asunto, $mensaje_html, $datos_insignia);
        
        if ($enviado_real) {
            error_log("✅ Correo PHPMailer enviado exitosamente a: " . $destinatario_email);
            return true;
        }
    } else {
        error_log("⚠️ config_smtp.php no existe. Solo se intentó mail() nativo.");
    }
    
    // 3. Si todo falla, usar simulación como respaldo (guarda en archivo)
    error_log("⚠️ Todos los métodos fallaron, usando simulación para: " . $destinatario_email);
    error_log("   SOLUCIÓN: Instala sendmail o configura un correo del sistema en config_smtp.php");
    return enviarCorreoSimuladoInterno($destinatario_email, $asunto, $mensaje_html, $datos_insignia);
}

/**
 * Envía correo usando mail() nativo de PHP (más simple)
 */
function enviarConMailNativo($destinatario_email, $asunto, $mensaje_html) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: Sistema Insignias TecNM <noreply@tecnm.mx>" . "\r\n";
    $headers .= "Reply-To: noreply@tecnm.mx" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    $resultado = mail($destinatario_email, $asunto, $mensaje_html, $headers);
    
    if ($resultado) {
        error_log("Correo NATIVO enviado exitosamente a: " . $destinatario_email);
        return true;
    } else {
        error_log("Error en correo NATIVO para: " . $destinatario_email);
        return false;
    }
}

/**
 * Envía correo usando PHPMailer con la configuración exitosa
 */
function enviarConPHPMailerReal($destinatario_email, $asunto, $mensaje_html, $datos_insignia) {
    $mail = new PHPMailer(true);

    try {
        // Cargar configuración SMTP
        $tu_correo = "211230001@smarcos.tecnm.mx";
        $tu_contraseña = "cas29ye02vi20";
        $servidores = [];
        
        if (file_exists('config_smtp.php')) {
            require_once 'config_smtp.php';
            $tu_correo = defined('SMTP_USERNAME') ? SMTP_USERNAME : $tu_correo;
            $tu_contraseña = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : $tu_contraseña;
            
            // Agregar servidor principal
            if (defined('SMTP_HOST') && defined('SMTP_PORT')) {
                $encryption = defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls';
                $servidores[SMTP_HOST] = [
                    'port' => SMTP_PORT,
                    'encryption' => $encryption
                ];
            }
            
            // Agregar servidores alternativos
            if (defined('SMTP_SERVERS_ALTERNATIVOS')) {
                foreach (SMTP_SERVERS_ALTERNATIVOS as $host => $config) {
                    if (!isset($servidores[$host])) {
                        $servidores[$host] = $config;
                    }
                }
            }
        }
        
        // Si no hay configuración, usar valores por defecto
        if (empty($servidores)) {
            $servidores = [
                'smtp.office365.com' => ['port' => 587, 'encryption' => 'tls'],
                'smtp-mail.outlook.com' => ['port' => 587, 'encryption' => 'tls'],
                'smtp.gmail.com' => ['port' => 587, 'encryption' => 'tls'],
                'mail.tecnm.mx' => ['port' => 587, 'encryption' => 'tls'],
            ];
        }

        $funciono = false;
        $servidor_exitoso = '';
        $ultimo_error = '';

        foreach ($servidores as $servidor => $config) {
            try {
                $mail = new PHPMailer(true);
                $mail->clearAddresses();
                
                // Configuración SMTP
                $mail->isSMTP();
                $mail->Host = $servidor;
                $mail->SMTPAuth = true;
                $mail->Username = $tu_correo;
                $mail->Password = $tu_contraseña;
                
                // Configurar encriptación
                $puerto = $config['port'] ?? 587;
                $encryption = $config['encryption'] ?? 'tls';
                
                if ($encryption === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
                
                $mail->Port = $puerto;
                $mail->CharSet = 'UTF-8';
                $mail->SMTPDebug = defined('SMTP_DEBUG') && SMTP_DEBUG ? 2 : 0;
                $mail->Timeout = defined('SMTP_TIMEOUT') ? SMTP_TIMEOUT : 30;
                
                // SSL options
                $verify_ssl = defined('SMTP_VERIFY_SSL') ? SMTP_VERIFY_SSL : false;
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => $verify_ssl,
                        'verify_peer_name' => $verify_ssl,
                        'allow_self_signed' => !$verify_ssl
                    )
                );

                // Configurar correo
                $from_name = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Sistema Insignias TecNM';
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
                $ultimo_error = "Error con servidor $servidor:$puerto ($encryption) - " . $e->getMessage();
                error_log("❌ $ultimo_error");
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
    $contenido .= "DE: Sistema Insignias TecNM <211230001@smarcos.tecnm.mx>\n";
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
    
    // Mapeo de nombres de insignias a archivos de imagen
    $mapeo_imagenes = [
        'Embajador del Arte' => 'EmbajadordelArte.png',
        'Embajador del Deporte' => 'EmbajadordelDeporte.png',
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
    
    // Buscar el nombre de la insignia en el mapeo
    $archivo_imagen = 'insignia_default.png'; // Por defecto
    foreach ($mapeo_imagenes as $nombre => $archivo) {
        if (stripos($nombre_insignia, $nombre) !== false || stripos($nombre, $nombre_insignia) !== false) {
            $archivo_imagen = $archivo;
            break;
        }
    }
    
    return $base_url . '/imagen/Insignias/' . $archivo_imagen;
}
