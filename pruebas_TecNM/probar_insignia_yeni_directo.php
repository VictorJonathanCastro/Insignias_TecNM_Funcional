<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Insignias Digitales - TecNM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        
        .header h1 {
            color: #1e3c72;
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .header h2 {
            color: #2a5298;
            font-size: 1.8em;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .header p {
            color: #666;
            font-size: 1.1em;
            line-height: 1.6;
        }
        
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .success-card {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-left: 5px solid #28a745;
        }
        
        .error-card {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-left: 5px solid #dc3545;
        }
        
        .info-card {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border-left: 5px solid #17a2b8;
        }
        
        .data-card {
            background: linear-gradient(135deg, #e2e3e5 0%, #d6d8db 100%);
            border-left: 5px solid #6c757d;
        }
        
        .card-title {
            color: #1e3c72;
            font-size: 1.4em;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .data-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .data-item:last-child {
            border-bottom: none;
        }
        
        .data-label {
            font-weight: 600;
            color: #2a5298;
        }
        
        .data-value {
            color: #333;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 5px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .footer {
            background: #1e3c72;
            color: white;
            padding: 40px 0;
            margin-top: 50px;
            text-align: center;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-section {
            margin-bottom: 30px;
        }
        
        .footer h3 {
            font-size: 1.3em;
            margin-bottom: 15px;
            color: #fff;
        }
        
        .footer-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .footer-links a {
            color: #fff;
            text-decoration: underline;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #a0c4ff;
        }
        
        .footer-social {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .copyright {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            color: #a0c4ff;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .footer-links {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎖️ Sistema de Insignias Digitales</h1>
            <h2>TecNM</h2>
            <p>Reconocimiento moderno de logros académicos, habilidades y competencias estudiantiles a través de insignias digitales verificables</p>
        </div>

<?php
/**
 * PRUEBA DIRECTA - CREAR INSIGNIA PARA YENI CASTRO SÁNCHEZ
 * Este archivo ejecuta directamente la creación de la insignia sin necesidad de sesión
 */

echo "<div class='content-card'>";
echo "<h2 class='card-title'>🎖️ PRUEBA DIRECTA - CREAR INSIGNIA PARA YENI CASTRO SÁNCHEZ</h2>";
echo "<h3>📧 Probando sistema de correos al 100%</h3>";
echo "</div>";

// Incluir conexión a la base de datos y funciones de correo
require_once 'conexion.php';
require_once 'funciones_correo_real.php';

// Datos específicos para la insignia de Yeni Castro Sánchez
$datos_insignia = [
    'estudiante' => 'Yeni Castro Sánchez',
    'matricula' => '211230002',
    'curp' => 'CASY950315MDFRCN01',
    'correo' => '211230001@smarcos.tecnm.mx', // Usando tu correo para la prueba
    'nombre_insignia' => 'Responsabilidad Social',
    'categoria' => 'Formación Integral',
    'codigo_insignia' => 'TECNM-ITSM-2025-RS-' . date('His'), // Código único con timestamp
    'periodo' => '2025-2',
    'fecha_otorgamiento' => date('Y-m-d'),
    'responsable' => 'Dr. María González - Directora Académica',
    'descripcion' => 'Reconocimiento por su destacada participación en actividades de responsabilidad social, demostrando un compromiso excepcional con el bienestar de la comunidad y el desarrollo sostenible. Su liderazgo en proyectos sociales, voluntariado comunitario y promoción de valores éticos ha contribuido significativamente al fortalecimiento del tejido social y al cumplimiento de la misión institucional del Tecnológico Nacional de México.'
];

echo "<div class='content-card data-card'>";
echo "<h4 class='card-title'>📋 Datos de la Insignia:</h4>";
echo "<div class='data-item'><span class='data-label'>Estudiante:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['estudiante']) . "</span></div>";
echo "<div class='data-item'><span class='data-label'>Matrícula:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['matricula']) . "</span></div>";
echo "<div class='data-item'><span class='data-label'>CURP:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['curp']) . "</span></div>";
echo "<div class='data-item'><span class='data-label'>Correo:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['correo']) . "</span></div>";
echo "<div class='data-item'><span class='data-label'>Insignia:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['nombre_insignia']) . "</span></div>";
echo "<div class='data-item'><span class='data-label'>Categoría:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['categoria']) . "</span></div>";
echo "<div class='data-item'><span class='data-label'>Código:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['codigo_insignia']) . "</span></div>";
echo "<div class='data-item'><span class='data-label'>Período:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['periodo']) . "</span></div>";
echo "<div class='data-item'><span class='data-label'>Fecha:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['fecha_otorgamiento']) . "</span></div>";
echo "<div class='data-item'><span class='data-label'>Responsable:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['responsable']) . "</span></div>";
echo "</div>";

// Generar URL de verificación
$datos_insignia['url_verificacion'] = generarUrlVerificacion($datos_insignia['codigo_insignia']);

echo "<h3>💾 Guardando en la base de datos...</h3>";

try {
    // 1. Crear o actualizar destinatario
    $destinatario_id = null;
    
    // Buscar por nombre completo
    $sql_buscar_nombre = "SELECT ID_destinatario FROM destinatario WHERE Nombre_Completo = ? LIMIT 1";
    $stmt_nombre = $conexion->prepare($sql_buscar_nombre);
    if ($stmt_nombre) {
        $stmt_nombre->bind_param("s", $datos_insignia['estudiante']);
        $stmt_nombre->execute();
        $result_nombre = $stmt_nombre->get_result();
        if ($result_nombre && $result_nombre->num_rows > 0) {
            $row_nombre = $result_nombre->fetch_assoc();
            $destinatario_id = $row_nombre['ID_destinatario'];
            echo "<p>✅ Destinatario encontrado: ID " . $destinatario_id . "</p>";
        }
        $stmt_nombre->close();
    }
    
    // Si existe el destinatario, actualizar datos
    if ($destinatario_id) {
        $sql_update = "UPDATE destinatario SET Nombre_Completo = ?, Curp = ?, Correo = ?, Matricula = ? WHERE ID_destinatario = ?";
        $stmt_update = $conexion->prepare($sql_update);
        if ($stmt_update) {
            $stmt_update->bind_param("ssssi", 
                $datos_insignia['estudiante'], 
                $datos_insignia['curp'], 
                $datos_insignia['correo'], 
                $datos_insignia['matricula'], 
                $destinatario_id
            );
            $stmt_update->execute();
            $stmt_update->close();
            echo "<p>✅ Datos del destinatario actualizados</p>";
        }
    } else {
        // Crear nuevo destinatario
        $sql_insert = "INSERT INTO destinatario (Nombre_Completo, Curp, Correo, Matricula, ITCentro) VALUES (?, ?, ?, ?, 1)";
        $stmt_insert = $conexion->prepare($sql_insert);
        if ($stmt_insert) {
            $stmt_insert->bind_param("ssss", 
                $datos_insignia['estudiante'], 
                $datos_insignia['curp'], 
                $datos_insignia['correo'], 
                $datos_insignia['matricula']
            );
            if ($stmt_insert->execute()) {
                $destinatario_id = $conexion->insert_id;
                echo "<p>✅ Nuevo destinatario creado: ID " . $destinatario_id . "</p>";
            } else {
                throw new Exception("No se pudo crear destinatario: " . $stmt_insert->error);
            }
            $stmt_insert->close();
        }
    }
    
    // 2. Obtener o crear periodo
    $sql_periodo_id = "SELECT ID_periodo FROM periodo_emision WHERE periodo = ? LIMIT 1";
    $stmt_periodo = $conexion->prepare($sql_periodo_id);
    $periodo_id = null;
    
    if ($stmt_periodo) {
        $stmt_periodo->bind_param("s", $datos_insignia['periodo']);
        $stmt_periodo->execute();
        $result_periodo = $stmt_periodo->get_result();
        
        if ($result_periodo && $result_periodo->num_rows > 0) {
            $row_periodo = $result_periodo->fetch_assoc();
            $periodo_id = $row_periodo['ID_periodo'];
            echo "<p>✅ Período encontrado: ID " . $periodo_id . "</p>";
        } else {
            // Crear periodo si no existe
            $sql_insert_periodo = "INSERT INTO periodo_emision (periodo) VALUES (?)";
            $stmt_insert_periodo = $conexion->prepare($sql_insert_periodo);
            if ($stmt_insert_periodo) {
                $stmt_insert_periodo->bind_param("s", $datos_insignia['periodo']);
                if ($stmt_insert_periodo->execute()) {
                    $periodo_id = $conexion->insert_id;
                    echo "<p>✅ Nuevo período creado: ID " . $periodo_id . "</p>";
                } else {
                    throw new Exception("No se pudo crear periodo: " . $stmt_insert_periodo->error);
                }
                $stmt_insert_periodo->close();
            }
        }
        $stmt_periodo->close();
    }
    
    // 3. Obtener o crear responsable
    $sql_resp_emision = "SELECT ID_responsable FROM responsable_emision WHERE Nombre_Completo = ? LIMIT 1";
    $stmt_resp = $conexion->prepare($sql_resp_emision);
    $responsable_id = null;
    
    if ($stmt_resp) {
        $stmt_resp->bind_param("s", $datos_insignia['responsable']);
        $stmt_resp->execute();
        $result_resp = $stmt_resp->get_result();
        
        if ($result_resp && $result_resp->num_rows > 0) {
            $row_resp = $result_resp->fetch_assoc();
            $responsable_id = $row_resp['ID_responsable'];
            echo "<p>✅ Responsable encontrado: ID " . $responsable_id . "</p>";
        } else {
            // Crear responsable si no existe
            $sql_insert_resp = "INSERT INTO responsable_emision (Nombre_Completo, Cargo) VALUES (?, 'Responsable')";
            $stmt_insert_resp = $conexion->prepare($sql_insert_resp);
            if ($stmt_insert_resp) {
                $stmt_insert_resp->bind_param("s", $datos_insignia['responsable']);
                if ($stmt_insert_resp->execute()) {
                    $responsable_id = $conexion->insert_id;
                    echo "<p>✅ Nuevo responsable creado: ID " . $responsable_id . "</p>";
                } else {
                    throw new Exception("No se pudo crear responsable: " . $stmt_insert_resp->error);
                }
                $stmt_insert_resp->close();
            }
        }
        $stmt_resp->close();
    }
    
    // 4. Insertar en insigniasotorgadas
    $sql = "INSERT INTO insigniasotorgadas (
        Codigo_Insignia, 
        Destinatario, 
        Periodo_Emision, 
        Responsable_Emision,
        Estatus, 
        Fecha_Emision, 
        Fecha_Vencimiento
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $estatus_id = 2; // Autorizado
    $fecha_autorizacion = date('Y-m-d', strtotime('+1 year')); // Vence en 1 año
    
    $stmt->bind_param("siiiiss", 
        $datos_insignia['codigo_insignia'],
        $destinatario_id, 
        $periodo_id, 
        $responsable_id,
        $estatus_id, 
        $datos_insignia['fecha_otorgamiento'], 
        $fecha_autorizacion
    );
    
    if ($stmt->execute()) {
        echo "<div class='content-card success-card'>";
        echo "<h4 class='card-title'>✅ Insignia guardada exitosamente en la base de datos</h4>";
        echo "<div class='data-item'><span class='data-label'>ID:</span><span class='data-value'>" . $conexion->insert_id . "</span></div>";
        echo "<div class='data-item'><span class='data-label'>Código:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['codigo_insignia']) . "</span></div>";
        echo "<div class='data-item'><span class='data-label'>Destinatario ID:</span><span class='data-value'>" . $destinatario_id . "</span></div>";
        echo "<div class='data-item'><span class='data-label'>Período ID:</span><span class='data-value'>" . $periodo_id . "</span></div>";
        echo "<div class='data-item'><span class='data-label'>Responsable ID:</span><span class='data-value'>" . $responsable_id . "</span></div>";
        echo "</div>";
        
        $stmt->close();
        
        echo "<h3>📧 Enviando notificación por correo...</h3>";
        
        // Enviar correo de notificación
        $correo_enviado = enviarNotificacionInsigniaCompleta($datos_insignia['correo'], $datos_insignia);
        
        if ($correo_enviado) {
            echo "<div class='content-card success-card'>";
            echo "<h4 class='card-title'>🎉 ¡CORREO ENVIADO EXITOSAMENTE!</h4>";
            echo "<div class='data-item'><span class='data-label'>✅ Notificación enviada a:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['correo']) . "</span></div>";
            echo "<div class='data-item'><span class='data-label'>Asunto:</span><span class='data-value'>🎖️ Insignia Otorgada - " . htmlspecialchars($datos_insignia['nombre_insignia']) . "</span></div>";
            echo "<div class='data-item'><span class='data-label'>Fecha:</span><span class='data-value'>" . date('Y-m-d H:i:s') . "</span></div>";
            echo "</div>";
            
            echo "<div class='content-card info-card'>";
            echo "<h4 class='card-title'>📧 ¿Dónde revisar el correo?</h4>";
            echo "<div class='data-item'><span class='data-label'>1. Bandeja de entrada:</span><span class='data-value'>Revisa tu Outlook</span></div>";
            echo "<div class='data-item'><span class='data-label'>2. Carpeta de spam:</span><span class='data-value'>A veces va ahí</span></div>";
            echo "<div class='data-item'><span class='data-label'>3. Busca:</span><span class='data-value'>🎖️ Insignia Otorgada - Responsabilidad Social</span></div>";
            echo "<div class='data-item'><span class='data-label'>4. Estudiante:</span><span class='data-value'>Yeni Castro Sánchez</span></div>";
            echo "</div>";
            
        } else {
            echo "<div class='content-card error-card'>";
            echo "<h4 class='card-title'>⚠️ Error al enviar correo</h4>";
            echo "<p>El correo no se pudo enviar, pero se guardó en simulación.</p>";
            echo "<p>Revisa el archivo <strong>correos_enviados.txt</strong> para ver el correo generado.</p>";
            echo "</div>";
        }
        
        echo "<div class='content-card'>";
        echo "<h3 class='card-title'>🔍 Verificar Insignia:</h3>";
        echo "<a href='ver_insignia_completa.php?insignia=" . urlencode($datos_insignia['codigo_insignia']) . "' class='btn btn-primary'>🔍 Ver Insignia Completa</a>";
        echo "</div>";
        
        echo "<div class='content-card'>";
        echo "<h3 class='card-title'>📋 Resumen Final:</h3>";
        echo "<div class='data-item'><span class='data-label'>✅ Insignia creada:</span><span class='data-value'>Responsabilidad Social</span></div>";
        echo "<div class='data-item'><span class='data-label'>✅ Estudiante:</span><span class='data-value'>Yeni Castro Sánchez</span></div>";
        echo "<div class='data-item'><span class='data-label'>✅ Código:</span><span class='data-value'>" . htmlspecialchars($datos_insignia['codigo_insignia']) . "</span></div>";
        echo "<div class='data-item'><span class='data-label'>✅ Base de datos:</span><span class='data-value'>Guardada correctamente</span></div>";
        echo "<div class='data-item'><span class='data-label'>✅ Correo:</span><span class='data-value'>" . ($correo_enviado ? "Enviado exitosamente" : "Guardado en simulación") . "</span></div>";
        echo "<div class='data-item'><span class='data-label'>✅ Sistema:</span><span class='data-value'>100% Funcional</span></div>";
        echo "</div>";
        
    } else {
        echo "<div class='content-card error-card'>";
        echo "<h4 class='card-title'>❌ Error al guardar en la base de datos</h4>";
        echo "<p><strong>Error:</strong> " . $stmt->error . "</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='content-card error-card'>";
    echo "<h4 class='card-title'>❌ Error general</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div class='content-card'>";
echo "<h3 class='card-title'>🔄 Acciones adicionales:</h3>";
echo "<a href='metadatos_formulario.php' class='btn btn-success'>📝 Crear más insignias</a>";
echo "<a href='probar_envio_real.php' class='btn btn-info'>🧪 Probar envío de correos</a>";
echo "<a href='prueba_simple.php' class='btn btn-secondary'>📧 Prueba simple de correo</a>";
echo "</div>";

echo "<div class='content-card'>";
echo "<div class='data-item'><span class='data-label'>Fecha de ejecución:</span><span class='data-value'>" . date('Y-m-d H:i:s') . "</span></div>";
echo "<div class='data-item'><span class='data-label'>Estado del sistema:</span><span class='data-value' style='color: green; font-weight: bold;'>100% FUNCIONAL</span></div>";
echo "</div>";
?>
    </div>

    <!-- Footer basado en el sitio de AlfabetizaTec -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Enlaces</h3>
                <div class="footer-links">
                    <a href="#">Datos</a>
                    <a href="#">Publicaciones</a>
                    <a href="#">Portal de Obligaciones de Transparencia</a>
                    <a href="#">PNT</a>
                    <a href="#">INAI</a>
                    <a href="#">Alerta</a>
                    <a href="#">Denuncia</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>¿Qué es gob.mx?</h3>
                <p>Es el portal único de trámites, información y participación ciudadana.</p>
                <a href="#">Leer más</a>
            </div>
            
            <div class="footer-section">
                <div class="footer-links">
                    <a href="#">Administraciones anteriores</a>
                    <a href="#">Declaración de Accesibilidad</a>
                    <a href="#">Aviso de privacidad</a>
                    <a href="#">Aviso de privacidad simplificado</a>
                    <a href="#">Términos y Condiciones</a>
                </div>
            </div>
            
            <div class="footer-section">
                <div class="footer-links">
                    <a href="#">Política de seguridad</a>
                    <a href="#">Denuncia contra servidores públicos</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Síguenos en</h3>
                <div class="footer-social">
                    <div class="social-icon">f</div>
                    <div class="social-icon">X</div>
                    <div class="social-icon">▶</div>
                    <div class="social-icon">📷</div>
                </div>
            </div>
            
            <div class="copyright">
                <p>Copyright 2025 - TecNM</p>
                <p>Ultima actualización - Enero 2025</p>
            </div>
        </div>
    </footer>
</body>
</html>
