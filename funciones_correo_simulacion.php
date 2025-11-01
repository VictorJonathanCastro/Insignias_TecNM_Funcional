<?php
/**
 * Función de simulación de correos para desarrollo
 * Esta función guarda los correos en un archivo para que puedas verlos
 */

function enviarCorreoSimulado($destinatario, $asunto, $mensaje_html, $datos_insignia = []) {
    $archivo = 'correos_enviados.txt';
    
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
    
    // Guardar en archivo
    $resultado = file_put_contents($archivo, $contenido, FILE_APPEND | LOCK_EX);
    
    if ($resultado !== false) {
        error_log("Correo simulado guardado exitosamente para: " . $destinatario);
        return true;
    } else {
        error_log("Error al guardar correo simulado para: " . $destinatario);
        return false;
    }
}

function mostrarCorreosEnviados() {
    $archivo = 'correos_enviados.txt';
    
    if (!file_exists($archivo)) {
        return "<p>No hay correos enviados aún.</p>";
    }
    
    $contenido = file_get_contents($archivo);
    if ($contenido === false) {
        return "<p>Error al leer el archivo de correos.</p>";
    }
    
    return "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;'>" . htmlspecialchars($contenido) . "</pre>";
}

// Función principal de envío que usa simulación si falla el correo real
function enviarNotificacionInsigniaCompleta($destinatario_email, $datos_insignia) {
    $asunto = "🎖️ Insignia Otorgada - " . $datos_insignia['nombre_insignia'];
    $mensaje_html = generarMensajeCorreo($datos_insignia);
    
    // Intentar envío real primero
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: Sistema Insignias TecNM <211230001@smarcos.tecnm.mx>" . "\r\n";
    $headers .= "Reply-To: 211230001@smarcos.tecnm.mx" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    $enviado_real = mail($destinatario_email, $asunto, $mensaje_html, $headers);
    
    if ($enviado_real) {
        error_log("Correo real enviado exitosamente a: " . $destinatario_email);
        return true;
    } else {
        // Si falla el correo real, usar simulación
        error_log("Correo real falló, usando simulación para: " . $destinatario_email);
        return enviarCorreoSimulado($destinatario_email, $asunto, $mensaje_html, $datos_insignia);
    }
}

// Función para generar mensaje HTML (reutilizada de funciones_correo.php)
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
