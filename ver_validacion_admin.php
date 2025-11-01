<?php
session_start();

// Verificar sesión y rol de administrador
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'Admin' && $_SESSION['rol'] !== 'SuperUsuario')) {
    header('Location: login.php?error=acceso_denegado');
    exit();
}

// Incluir conexión a la base de datos
require_once 'conexion.php';

// Obtener ID o código de la insignia
$insignia_param = $_GET['id'] ?? '';

if (!$insignia_param) {
    die("ID o código de insignia no válido");
}

// Determinar si es un ID numérico o un código de insignia
$is_numeric = is_numeric($insignia_param);

// Consulta para obtener datos de la insignia con información real - CORREGIDA
$sql = "
    SELECT 
        io.ID_otorgada as id,
        io.Fecha_Emision as fecha_otorgamiento,
        io.Codigo_Insignia as clave_insignia,
        'Certificación oficial' as evidencia,
        d.Nombre_Completo as destinatario,
        d.Matricula,
        'Programa Académico' as Programa,
        'Reconocimiento por méritos destacados' as Descripcion,
        'Cumplimiento de criterios establecidos' as Criterio,
        CASE 
            WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
            WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
            WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
            WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
            WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
            WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
            WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
            ELSE 'Insignia TecNM'
        END as nombre_insignia,
        CASE 
            WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
            WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
            WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
            ELSE 'Formación Integral'
        END as categoria,
        'Tecnológico Nacional de México' as institucion,
        '2025-1' as periodo,
        'Activo' as estatus,
        'insignia_default.png' as archivo_imagen,
        io.Responsable_Emision as responsable_id,
        re.Nombre_Completo as responsable_nombre,
        re.Cargo as responsable_cargo
    FROM insigniasotorgadas io
    LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario
    LEFT JOIN responsable_emision re ON io.Responsable_Emision = re.id
    WHERE " . ($is_numeric ? "io.ID_otorgada" : "io.Codigo_Insignia") . " = ?
";

$stmt = $conexion->prepare($sql);

// Verificar si la preparación fue exitosa
if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error . "<br>Consulta SQL: " . htmlspecialchars($sql));
}

$stmt->bind_param($is_numeric ? "i" : "s", $insignia_param);

// Verificar si la ejecución fue exitosa
if (!$stmt->execute()) {
    die("Error al ejecutar la consulta: " . $stmt->error);
}

$resultado = $stmt->get_result();

if (!$resultado || $resultado->num_rows === 0) {
    die("Insignia no encontrada");
}

$insignia = $resultado->fetch_assoc();
$stmt->close();

// Obtener firma digital del responsable desde la tabla firmas_digitales
$firma_digital = null;
$hash_verificacion = null;

if (!empty($insignia['responsable_id'])) {
    $sql_firma = "SELECT hash_verificacion, nombre_responsable, fecha_generacion, archivo_firma 
                  FROM firmas_digitales 
                  WHERE responsable_id = ? AND activa = 1 
                  ORDER BY fecha_generacion DESC 
                  LIMIT 1";
    
    $stmt_firma = $conexion->prepare($sql_firma);
    if ($stmt_firma) {
        $stmt_firma->bind_param("i", $insignia['responsable_id']);
        $stmt_firma->execute();
        $resultado_firma = $stmt_firma->get_result();
        
        if ($resultado_firma && $resultado_firma->num_rows > 0) {
            $firma_digital = $resultado_firma->fetch_assoc();
            $hash_verificacion = $firma_digital['hash_verificacion'];
        }
        $stmt_firma->close();
    }
}

// Determinar la imagen de la insignia dinámicamente
$nombre_insignia = $insignia['nombre_insignia'];
$codigo_insignia = $insignia['clave_insignia'];

// Función para determinar la insignia dinámicamente
function determinarInsigniaDinamica($codigo_insignia, $nombre_insignia) {
    // Mapeo de códigos a tipos de insignia
    $mapeo_codigos = [
        'ART' => 'Embajador del Arte',
        'EMB' => 'Embajador del Deporte', 
        'TAL' => 'Talento Científico',
        'INN' => 'Talento Innovador',
        'SOC' => 'Responsabilidad Social',
        'FOR' => 'Formación y Actualización',
        'MOV' => 'Movilidad e Intercambio'
    ];
    
    // Mapeo de nombres de insignias a archivos PNG
    $mapeo_imagenes = [
        'Movilidad e Intercambio' => 'MovilidadeIntercambio.png',
        'Embajador del Deporte' => 'EmbajadordelDeporte.png',
        'Embajador del Arte' => 'EmbajadordelArte.png',
        'Formación y Actualización' => 'FormacionyActualizacion.png',
        'Talento Científico' => 'TalentoCientifico.png',
        'Talento Innovador' => 'TalentoInnovador.png',
        'Responsabilidad Social' => 'ResponsabilidadSocial.png'
    ];
    
    // 1. Intentar determinar por código
    foreach ($mapeo_codigos as $codigo => $tipo) {
        if (strpos($codigo_insignia, $codigo) !== false) {
            return $mapeo_imagenes[$tipo] ?? 'EmbajadordelArte.png';
        }
    }
    
    // 2. Intentar determinar por nombre de insignia
    if (isset($mapeo_imagenes[$nombre_insignia])) {
        return $mapeo_imagenes[$nombre_insignia];
    }
    
    // 3. Buscar coincidencias parciales en el nombre
    foreach ($mapeo_imagenes as $tipo => $archivo) {
        if (strpos($nombre_insignia, $tipo) !== false || strpos($tipo, $nombre_insignia) !== false) {
            return $archivo;
        }
    }
    
    // 4. Fallback
    return 'EmbajadordelArte.png';
}

// Determinar la imagen de la insignia
$archivo_imagen = determinarInsigniaDinamica($codigo_insignia, $nombre_insignia);
$imagen_path = 'imagen/Insignias/' . $archivo_imagen;

// Verificar que el archivo existe, si no, usar la primera disponible
if (!file_exists($imagen_path)) {
    $mapeo_imagenes = [
        'MovilidadeIntercambio.png',
        'EmbajadordelDeporte.png', 
        'EmbajadordelArte.png',
        'FormacionyActualizacion.png',
        'TalentoCientifico.png',
        'TalentoInnovador.png',
        'ResponsabilidadSocial.png'
    ];
    
    foreach ($mapeo_imagenes as $archivo) {
        $ruta_alternativa = 'imagen/Insignias/' . $archivo;
        if (file_exists($ruta_alternativa)) {
            $imagen_path = $ruta_alternativa;
            break;
        }
    }
}

// Generar URL de validación accesible desde móviles
function getServerIP() {
    // Obtener la IP del servidor
    $ip = $_SERVER['SERVER_ADDR'] ?? 'localhost';
    
    // Si es localhost, intentar obtener la IP real
    if ($ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') {
        // Intentar obtener la IP de la interfaz de red
        $ip = gethostbyname(gethostname());
        if ($ip === gethostname()) {
            // Si no funciona, usar una IP común de desarrollo
            $ip = '192.168.1.100'; // Cambiar por tu IP real
        }
    }
    
    return $ip;
}

$server_ip = getServerIP();
$port = $_SERVER['SERVER_PORT'] ?? '80';
$base_url = "http://" . $server_ip . ($port != '80' ? ':' . $port : '');
$url_validacion = $base_url . "/Insignias_TecNM_Funcional/verificar_insignia.php?clave=" . urlencode($insignia['clave_insignia']);

// Generar código QR usando Google Charts API
$qr_url = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($url_validacion);

// Función para formatear fecha
function formatearFecha($fecha) {
    if (!$fecha) return 'N/A';
    $fecha_obj = new DateTime($fecha);
    return $fecha_obj->format('d/m/Y');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Insignia - <?php echo htmlspecialchars($insignia['destinatario']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .validation-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .validation-header {
            background: linear-gradient(135deg, #1b396a 0%, #2c5aa0 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .validation-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .validation-subtitle {
            font-size: 16px;
            opacity: 0.9;
        }

        .validation-content {
            padding: 40px;
        }

        .validation-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .validation-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border-left: 5px solid #1b396a;
        }

        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #1b396a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .qr-container {
            text-align: center;
            margin: 20px 0;
        }

        .qr-code {
            border: 2px solid #1b396a;
            border-radius: 10px;
            padding: 10px;
            background: white;
            display: inline-block;
        }

        .url-container {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .url-text {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #495057;
            word-break: break-all;
            margin-bottom: 10px;
        }

        .copy-button {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.3s;
        }

        .copy-button:hover {
            background: #218838;
        }

        .copy-button.copied {
            background: #17a2b8;
        }

        .insignia-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .detail-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 14px;
            color: #212529;
            font-weight: 500;
        }

        .recipient-name {
            font-size: 24px;
            font-weight: bold;
            color: #1b396a;
            margin: 15px 0;
        }

        .badge-name {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
            margin: 10px 0;
        }

        .actions-section {
            background: #e3f2fd;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        @media (max-width: 768px) {
            .validation-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
        
        /* Ocultar secciones innecesarias */
        .hide-section {
            display: none !important;
        }
        
        /* Hacer la imagen clickeable */
        .insignia-image {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .insignia-image:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
        }
        
        /* FOOTER PROFESIONAL AZUL */
        footer {
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
          margin-bottom: 25px;
        }
        
        footer h3 {
          font-size: 16px;
          margin-bottom: 12px;
          color: #fff;
          font-weight: bold;
        }
        
        .footer-links {
          display: flex;
          flex-wrap: wrap;
          justify-content: center;
          gap: 18px;
          margin-bottom: 18px;
        }
        
        .footer-links a {
          color: #fff;
          text-decoration: underline;
          font-size: 14px;
          transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
          color: #a0c4ff;
        }
        
        .social-icons {
          display: flex;
          justify-content: center;
          gap: 18px;
          margin-top: 18px;
        }
        
        .social-icon {
          width: 35px;
          height: 35px;
          background: rgba(255, 255, 255, 0.1);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-size: 16px;
          transition: all 0.3s ease;
        }
        
        .social-icon:hover {
          background: rgba(255, 255, 255, 0.2);
          transform: translateY(-2px);
        }
        
        .copyright {
          margin-top: 25px;
          padding-top: 20px;
          border-top: 1px solid rgba(255, 255, 255, 0.2);
          color: #a0c4ff;
          font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="validation-container">
        <!-- Encabezado -->
        <div class="validation-header">
            <div class="validation-title">🔍 Validación de Insignia</div>
            <div class="validation-subtitle">Sistema de Verificación TecNM</div>
        </div>

        <!-- Contenido Principal -->
        <div class="validation-content">
            <!-- Imagen de la Insignia -->
            <div class="validation-section" style="margin-bottom: 30px;">
                <div class="section-title">
                    🏆 Imagen de la Insignia
                </div>
                <div style="text-align: center; padding: 20px;">
                    <a href="ver_certificado_admin.php?id=<?php echo $insignia['id']; ?>" target="_blank" style="text-decoration: none; cursor: pointer;">
                        <div class="insignia-image" style="display: inline-block; width: 250px; height: 250px; background-image: url('<?php echo $imagen_path; ?>'); background-size: contain; background-repeat: no-repeat; background-position: center; border: 2px solid #e9ecef; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" title="Haz clic para ver el certificado completo"></div>
                    </a>
                    <p style="margin-top: 15px; font-size: 14px; color: #6c757d;">
                        Insignia: <?php echo htmlspecialchars($insignia['nombre_insignia'] ?? 'Insignia de Reconocimiento'); ?><br>
                        <small style="color: #999;">💡 Haz clic en la imagen para ver el certificado completo</small>
                    </p>
                </div>
            </div>
            
            <!-- Información del Destinatario -->
            <div class="validation-section hide-section">
                <div class="section-title">
                    👤 Información del Destinatario
                </div>
                
                <div class="recipient-name">
                    <?php echo htmlspecialchars($insignia['destinatario']); ?>
                </div>
                <div class="badge-name">
                    <?php echo htmlspecialchars($insignia['nombre_insignia'] ?? 'Insignia de Reconocimiento'); ?>
                </div>
                <div class="status-badge status-active">
                    ✅ <?php echo htmlspecialchars($insignia['estatus'] ?? 'Activo'); ?>
                </div>
            </div>

            <!-- Grid Principal -->
            <div class="validation-grid">
                <!-- Código QR -->
                <div class="validation-section">
                    <div class="section-title">
                        📱 Código QR de Validación
                    </div>
                    <div class="qr-container">
                        <div class="qr-code">
                            <canvas id="qr-canvas" width="200" height="200" style="display: none;"></canvas>
                            <img id="qr-image" src="<?php echo htmlspecialchars($qr_url); ?>" alt="Código QR" style="display: none; width: 200px; height: 200px;">
                            <div id="qr-fallback" style="display: block;">
                                <div style="width: 200px; height: 200px; border: 2px solid #1b396a; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                    <div style="text-align: center; color: #1b396a;">
                                        <div style="font-size: 24px; margin-bottom: 10px;">📱</div>
                                        <div style="font-size: 12px; font-weight: bold;">Código QR</div>
                                        <div style="font-size: 10px;">Cargando...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <p style="margin-top: 15px; font-size: 14px; color: #6c757d;">
                        Escanea este código QR para verificar la autenticidad de la insignia
                    </p>
                    <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-size: 12px; color: #666;">
                        <strong>Debug:</strong> IP del servidor: <?php echo htmlspecialchars($server_ip); ?><br>
                        <strong>URL:</strong> <?php echo htmlspecialchars($url_validacion); ?>
                    </div>
                    </div>
                </div>

                <!-- Enlace de Validación -->
                <div class="validation-section">
                    <div class="section-title">
                        🔗 Enlace de Validación
                    </div>
                    <div class="url-container">
                        <div class="url-text" id="validation-url">
                            <?php echo htmlspecialchars($url_validacion); ?>
                        </div>
                        <button class="copy-button" onclick="copyToClipboard()">
                            📋 Copiar Enlace
                        </button>
                    </div>
                    <p style="margin-top: 15px; font-size: 14px; color: #6c757d;">
                        Comparte este enlace para que otros puedan verificar la insignia
                    </p>
                </div>
            </div>

            <!-- Firma Digital del Responsable -->
            <?php if ($firma_digital): ?>
            <div class="validation-section" style="background: #e8f5e8; border-left: 5px solid #28a745; margin-bottom: 30px;">
                <div class="section-title" style="color: #28a745;">
                    🔐 Firma Digital del Responsable
                </div>
                <div style="background: white; padding: 20px; border-radius: 8px;">
                    <div class="detail-item" style="margin-bottom: 15px;">
                        <div class="detail-label">Responsable</div>
                        <div class="detail-value"><?php echo htmlspecialchars($firma_digital['nombre_responsable']); ?></div>
                    </div>
                    <?php if (!empty($insignia['responsable_cargo'])): ?>
                    <div class="detail-item" style="margin-bottom: 15px;">
                        <div class="detail-label">Cargo</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia['responsable_cargo']); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="detail-item" style="margin-bottom: 15px;">
                        <div class="detail-label">Hash de Verificación</div>
                        <div class="detail-value" style="font-family: 'Courier New', monospace; font-size: 11px; word-break: break-all; color: #155724;">
                            <?php echo htmlspecialchars($hash_verificacion); ?>
                        </div>
                    </div>
                    <?php if (!empty($firma_digital['fecha_generacion'])): ?>
                    <div class="detail-item">
                        <div class="detail-label">Fecha de Generación</div>
                        <div class="detail-value"><?php echo date('d/m/Y H:i:s', strtotime($firma_digital['fecha_generacion'])); ?></div>
                    </div>
                    <?php endif; ?>
                    <div style="margin-top: 15px; padding: 10px; background: #d4edda; border-radius: 5px; border-left: 3px solid #28a745;">
                        <small style="color: #155724;">
                            <strong>✅ Firma verificada:</strong> Esta insignia cuenta con firma digital válida emitida por el responsable correspondiente.
                        </small>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="validation-section" style="background: #fff3cd; border-left: 5px solid #ffc107; margin-bottom: 30px;">
                <div class="section-title" style="color: #856404;">
                    ⚠️ Firma Digital Pendiente
                </div>
                <div style="background: white; padding: 20px; border-radius: 8px;">
                    <p style="color: #856404; margin: 0;">
                        Esta insignia no cuenta con una firma digital asociada al responsable de emisión.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Detalles de la Insignia -->
            <div class="validation-section hide-section">
                <div class="section-title">
                    📋 Detalles de la Insignia
                </div>
                <div class="insignia-details">
                    <div class="detail-item">
                        <div class="detail-label">Fecha de Emisión</div>
                        <div class="detail-value"><?php echo formatearFecha($insignia['fecha_otorgamiento']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Institución</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia['institucion'] ?? 'Tecnológico Nacional de México'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Programa</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia['Programa'] ?? 'Programa Académico'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Período</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia['periodo'] ?? 'Período Académico'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Categoría</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia['categoria'] ?? 'Reconocimiento'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Clave de Insignia</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia['clave_insignia']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Compartir en Redes Sociales -->
            <div class="actions-section" style="background: #f8f9fa; margin-bottom: 20px;">
                <div class="section-title" style="color: #1b396a;">
                    📱 Compartir en Redes Sociales
                </div>
                <div class="action-buttons">
                    <a href="https://wa.me/?text=<?php echo urlencode('🎖️ ¡He recibido una insignia de ' . $insignia['nombre_insignia'] . ' del TecNM! 👨‍🎓 ' . htmlspecialchars($insignia['destinatario']) . ' 🏆 Valida mi insignia aquí: ' . $url_validacion); ?>" class="btn btn-success" target="_blank" style="background: #25D366; display: flex; align-items: center; gap: 10px;">
                        💬 WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($url_validacion); ?>" class="btn btn-primary" target="_blank" style="background: #1877F2; display: flex; align-items: center; gap: 10px;">
                        🔵 Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('🎖️ ¡He recibido una insignia de ' . $insignia['nombre_insignia'] . ' del TecNM! 👨‍🎓'); ?>&url=<?php echo urlencode($url_validacion); ?>" class="btn btn-info" target="_blank" style="background: #1DA1F2; display: flex; align-items: center; gap: 10px;">
                        🐤 Twitter
                    </a>
                </div>
            </div>

            <!-- Acciones -->
            <div class="actions-section hide-section">
                <div class="section-title">
                    ⚡ Acciones Disponibles
                </div>
                <div class="action-buttons">
                    <a href="ver_certificado_admin.php?id=<?php echo $insignia['id']; ?>" class="btn btn-primary" target="_blank">
                        🏆 Ver Certificado
                    </a>
                    <a href="<?php echo htmlspecialchars($url_validacion); ?>" class="btn btn-success" target="_blank">
                        ✅ Verificar Insignia
                    </a>
                    <button class="btn btn-info" onclick="downloadQR()">
                        📱 Descargar QR
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Librería QR Code más simple -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js"></script>
    
    <script>
        // Generar código QR al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const validationUrl = '<?php echo htmlspecialchars($url_validacion); ?>';
            const canvas = document.getElementById('qr-canvas');
            const qrImage = document.getElementById('qr-image');
            const fallback = document.getElementById('qr-fallback');
            
            console.log('URL de validación:', validationUrl);
            
            // Método más simple: usar QRCode.toCanvas directamente
            function generateQR() {
                if (typeof QRCode !== 'undefined') {
                    console.log('Generando QR con QRCode...');
                    QRCode.toCanvas(canvas, validationUrl, {
                        width: 200,
                        margin: 2,
                        color: {
                            dark: '#1b396a',
                            light: '#ffffff'
                        }
                    }, function (error) {
                        if (error) {
                            console.error('Error QRCode:', error);
                            showFallback();
                        } else {
                            console.log('QR generado exitosamente');
                            canvas.style.display = 'block';
                            qrImage.style.display = 'none';
                            fallback.style.display = 'none';
                        }
                    });
                } else {
                    console.log('QRCode no disponible, usando método alternativo...');
                    generateQRAlternative();
                }
            }
            
            function generateQRAlternative() {
                // Método alternativo usando qrcode-generator
                if (typeof qrcode !== 'undefined') {
                    console.log('Generando QR con qrcode-generator...');
                    try {
                        const qr = qrcode(0, 'M');
                        qr.addData(validationUrl);
                        qr.make();
                        
                        const size = qr.getModuleCount();
                        const cellSize = Math.floor(200 / size);
                        const offset = Math.floor((200 - size * cellSize) / 2);
                        
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, 200, 200);
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, 200, 200);
                        
                        ctx.fillStyle = '#1b396a';
                        for (let row = 0; row < size; row++) {
                            for (let col = 0; col < size; col++) {
                                if (qr.isDark(row, col)) {
                                    ctx.fillRect(
                                        offset + col * cellSize,
                                        offset + row * cellSize,
                                        cellSize,
                                        cellSize
                                    );
                                }
                            }
                        }
                        
                        console.log('QR generado con qrcode-generator');
                        canvas.style.display = 'block';
                        qrImage.style.display = 'none';
                        fallback.style.display = 'none';
                    } catch (error) {
                        console.error('Error con qrcode-generator:', error);
                        showFallback();
                    }
                } else {
                    console.log('Ninguna librería QR disponible');
                    showFallback();
                }
            }
            
            function showFallback() {
                // Último intento: usar una imagen QR simple
                console.log('Intentando método de imagen QR simple...');
                const simpleQRUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(validationUrl);
                
                qrImage.onload = function() {
                    console.log('QR simple cargado exitosamente');
                    qrImage.style.display = 'block';
                    canvas.style.display = 'none';
                    fallback.style.display = 'none';
                };
                
                qrImage.onerror = function() {
                    console.log('Todos los métodos QR fallaron');
                    canvas.style.display = 'none';
                    qrImage.style.display = 'none';
                    fallback.style.display = 'block';
                    fallback.querySelector('div div div:last-child').textContent = 'No disponible';
                };
                
                qrImage.src = simpleQRUrl;
            }
            
            // Verificar qué librerías están disponibles
            console.log('QRCode disponible:', typeof QRCode !== 'undefined');
            console.log('qrcode disponible:', typeof qrcode !== 'undefined');
            
            // Intentar generar QR después de un pequeño delay
            setTimeout(generateQR, 500);
        });

        function copyToClipboard() {
            const url = document.getElementById('validation-url').textContent;
            const button = document.querySelector('.copy-button');
            
            navigator.clipboard.writeText(url).then(function() {
                button.textContent = '✅ Copiado!';
                button.classList.add('copied');
                
                setTimeout(function() {
                    button.textContent = '📋 Copiar Enlace';
                    button.classList.remove('copied');
                }, 2000);
            }).catch(function(err) {
                // Fallback para navegadores más antiguos
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                button.textContent = '✅ Copiado!';
                button.classList.add('copied');
                
                setTimeout(function() {
                    button.textContent = '📋 Copiar Enlace';
                    button.classList.remove('copied');
                }, 2000);
            });
        }

        function downloadQR() {
            const canvas = document.getElementById('qr-canvas');
            const qrImage = document.getElementById('qr-image');
            
            if (canvas && canvas.width > 0 && canvas.style.display !== 'none') {
                // Descargar desde canvas
                const link = document.createElement('a');
                link.download = 'qr-insignia-<?php echo htmlspecialchars($insignia['clave_insignia']); ?>.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            } else if (qrImage && qrImage.style.display !== 'none') {
                // Descargar desde imagen
                const link = document.createElement('a');
                link.download = 'qr-insignia-<?php echo htmlspecialchars($insignia['clave_insignia']); ?>.png';
                link.href = qrImage.src;
                link.click();
            } else {
                alert('El código QR no está disponible para descargar');
            }
        }

        // Auto-focus en la página
        window.onload = function() {
            // Opcional: auto-seleccionar el enlace
            // document.getElementById('validation-url').select();
        };
    </script>

  <!-- FOOTER AZUL PROFESIONAL -->
  <footer>
    <div class="footer-content">
      <div class="copyright">
        <p>Copyright 2025 - TecNM</p>
        <p>Ultima actualización - Octubre 2025</p>
      </div>
      
      <div class="footer-section">
        <h3>Enlaces</h3>
        <div class="footer-links">
          <a href="https://datos.gob.mx/" target="_blank">Datos</a>
          <a href="https://www.gob.mx/publicaciones" target="_blank">Publicaciones</a>
          <a href="https://consultapublicamx.plataformadetransparencia.org.mx/vut-web/faces/view/consultaPublica.xhtml?idEntidad=MzM=&idSujetoObligado=MTAwMDE=#inicio" target="_blank">Portal de Obligaciones de Transparencia</a>
          <a href="https://www.gob.mx/pnt" target="_blank">PNT</a>
          <a href="https://www.inai.org.mx/" target="_blank">INAI</a>
          <a href="https://www.gob.mx/alerta" target="_blank">Alerta</a>
          <a href="https://www.gob.mx/denuncia" target="_blank">Denuncia</a>
        </div>
      </div>
      
      <div class="footer-section">
        <h3>¿Qué es gob.mx?</h3>
        <p>Es el portal único de trámites, información y participación ciudadana.</p>
        <a href="https://www.gob.mx/" target="_blank">Leer más</a>
      </div>
      
      <div class="footer-section">
        <div class="footer-links">
          <a href="https://www.gob.mx/administraciones-anteriores" target="_blank">Administraciones anteriores</a>
          <a href="https://www.gob.mx/accesibilidad" target="_blank">Declaración de Accesibilidad</a>
          <a href="https://www.gob.mx/privacidad" target="_blank">Aviso de privacidad</a>
          <a href="https://www.gob.mx/privacidad-simplificado" target="_blank">Aviso de privacidad simplificado</a>
          <a href="https://www.gob.mx/terminos" target="_blank">Términos y Condiciones</a>
        </div>
      </div>
      
      <div class="footer-section">
        <div class="footer-links">
          <a href="https://www.gob.mx/politica-seguridad" target="_blank">Política de seguridad</a>
          <a href="https://www.gob.mx/denuncia-servidores" target="_blank">Denuncia contra servidores públicos</a>
        </div>
      </div>
      
      <div class="footer-section">
        <h3>Síguenos en</h3>
        <div class="social-icons">
          <a href="https://www.facebook.com/TecNacionalMexico" target="_blank" class="social-icon">f</a>
          <a href="https://twitter.com/TecNacionalMex" target="_blank" class="social-icon">X</a>
          <a href="https://www.youtube.com/user/TecNacionalMexico" target="_blank" class="social-icon">▶</a>
          <a href="https://www.instagram.com/tecnacionalmexico/" target="_blank" class="social-icon">📷</a>
        </div>
      </div>
    </div>
  </footer>

</body>
</html>
