<?php
require_once 'conexion.php';

// Obtener el código de la insignia desde la URL
$codigo_insignia = $_GET['insignia'] ?? '';

if (empty($codigo_insignia)) {
    die('Código de insignia no válido');
}

// Obtener datos de la insignia
$insignia = null;

try {
    // Verificar qué tabla existe - PRIORIDAD: usar insigniasotorgadas primero
    $tabla_existe_i = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
    $usar_tabla_i = ($tabla_existe_i && $tabla_existe_i->num_rows > 0);
    
    $tabla_existe_t = $conexion->query("SHOW TABLES LIKE 'T_insignias_otorgadas'");
    $usar_tabla_t = ($tabla_existe_t && $tabla_existe_t->num_rows > 0);
    
    if ($usar_tabla_i) {
        $check_destinatario_id = $conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
        $tiene_id_destinatario = ($check_destinatario_id && $check_destinatario_id->num_rows > 0);
        $campo_id_destinatario = $tiene_id_destinatario ? 'id' : 'ID_destinatario';
        
        $check_responsable_id = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'id'");
        $tiene_id_responsable = ($check_responsable_id && $check_responsable_id->num_rows > 0);
        $campo_id_responsable = $tiene_id_responsable ? 'id' : 'ID_responsable';
        
        $check_nombre_tipo = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Nombre_Insignia'");
        $tiene_nombre_insignia = ($check_nombre_tipo && $check_nombre_tipo->num_rows > 0);
        $campo_nombre_tipo = $tiene_nombre_insignia ? 'Nombre_Insignia' : 'Nombre_ins';
        
        $sql = "
            SELECT 
                io.ID_otorgada as id,
                io.Codigo_Insignia as codigo,
                io.Fecha_Emision as fecha_emision,
                d.Nombre_Completo as destinatario,
                d.Curp as curp,
                d.Matricula as matricula,
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
                COALESCE(ti.Descripcion, 'Este reconocimiento se otorga por su destacada participación y compromiso con los valores del Tecnológico Nacional de México.') as descripcion,
                COALESCE(ti.Criterio, 'Cumplimiento de los criterios establecidos para esta insignia.') as criterios,
                COALESCE(re.Nombre_Completo, 'Sistema TecNM') as responsable,
                COALESCE(re.Cargo, 'RESPONSABLE DE EMISIÓN') as cargo_responsable,
                io.Responsable_Emision as responsable_id,
                COALESCE(ti.Archivo_Visual, CONCAT('Insig_', io.Codigo_Insignia, '.jpg')) as archivo_visual,
                CASE 
                    WHEN LOWER(COALESCE(re.Nombre_Completo, '')) LIKE '%secretaria%' 
                         OR LOWER(COALESCE(re.Nombre_Completo, '')) LIKE '%vinculacion%' 
                         OR LOWER(COALESCE(re.Nombre_Completo, '')) LIKE '%extension%'
                         OR LOWER(COALESCE(re.Nombre_Completo, '')) LIKE '%sev%' THEN 'Secretaria de Extension y Vinculacion (SEV)'
                    ELSE 'Secretaria de Extension y Vinculacion (SEV)'
                END as emisor,
                'Sin evidencia registrada' as evidencia,
                'TecNM-ITSM-2025-Resp001' as codigo_responsable
            FROM insigniasotorgadas io
            LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
            LEFT JOIN responsable_emision re ON io.Responsable_Emision = re." . $campo_id_responsable . "
            LEFT JOIN tipo_insignia tin ON (
                (io.Codigo_Insignia LIKE '%ART%' AND tin." . $campo_nombre_tipo . " LIKE '%Arte%')
                OR (io.Codigo_Insignia LIKE '%EMB%' AND tin." . $campo_nombre_tipo . " LIKE '%Deporte%')
                OR (io.Codigo_Insignia LIKE '%TAL%' AND tin." . $campo_nombre_tipo . " LIKE '%Científico%')
                OR (io.Codigo_Insignia LIKE '%INN%' AND tin." . $campo_nombre_tipo . " LIKE '%Innovador%')
                OR (io.Codigo_Insignia LIKE '%SOC%' AND tin." . $campo_nombre_tipo . " LIKE '%Social%')
                OR (io.Codigo_Insignia LIKE '%FOR%' AND tin." . $campo_nombre_tipo . " LIKE '%Formación%')
                OR (io.Codigo_Insignia LIKE '%MOV%' AND tin." . $campo_nombre_tipo . " LIKE '%Movilidad%')
            )
            LEFT JOIN T_insignias ti ON ti.Tipo_Insignia = tin.id
            WHERE io.Codigo_Insignia = ?
            ORDER BY ti.Fecha_Creacion DESC
            LIMIT 1
        ";
    } elseif ($usar_tabla_t) {
        // Similar para T_insignias_otorgadas si es necesario
        $sql = "
            SELECT 
                tio.id,
                CONCAT(ti.id, '-', pe.Nombre_Periodo) as codigo,
                tio.Fecha_Emision as fecha_emision,
                d.Nombre_Completo as destinatario,
                d.Curp as curp,
                d.Matricula as matricula,
                COALESCE(tin.Nombre_Insignia, 'Insignia TecNM') as nombre_insignia,
                CASE 
                    WHEN tin.Nombre_Insignia LIKE '%Deporte%' OR tin.Nombre_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN tin.Nombre_Insignia LIKE '%Científico%' OR tin.Nombre_Insignia LIKE '%Innovación%' OR tin.Nombre_Insignia LIKE '%Formación%' THEN 'Desarrollo Académico'
                    WHEN tin.Nombre_Insignia LIKE '%Arte%' OR tin.Nombre_Insignia LIKE '%Social%' OR tin.Nombre_Insignia LIKE '%Movilidad%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria,
                'Este reconocimiento se otorga por su destacada participación y compromiso con los valores del Tecnológico Nacional de México.' as descripcion,
                'Cumplimiento de los criterios establecidos para esta insignia.' as criterios,
                'Sistema TecNM' as responsable,
                'RESPONSABLE DE EMISIÓN' as cargo_responsable,
                NULL as responsable_id,
                CONCAT('Insig_', CONCAT(ti.id, '-', pe.Nombre_Periodo), '.jpg') as archivo_visual,
                'Secretaria de Extension y Vinculacion (SEV)' as emisor,
                'Sin evidencia registrada' as evidencia,
                'TecNM-ITSM-2025-Resp001' as codigo_responsable
            FROM T_insignias_otorgadas tio
            LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
            LEFT JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
            LEFT JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
            LEFT JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
            WHERE CONCAT(ti.id, '-', pe.Nombre_Periodo) = ?
        ";
    } else {
        die('Error: No se encontró ninguna tabla de insignias otorgadas.');
    }
    
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $codigo_insignia);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $insignia = $result->fetch_assoc();
        }
        $stmt->close();
    }
} catch (Exception $e) {
    die('Error al obtener datos de la insignia: ' . $e->getMessage());
}

if (!$insignia) {
    die('Insignia no encontrada');
}

// Determinar la ruta de la imagen: primero por nombre (como en carpeta Insignias), luego por código
$mapeo_nombre_imagen = [
    'Responsabilidad Social' => 'ResponsabilidadSocial.png',
    'Responsabilidad Social Demo' => 'ResponsabilidadSocial.png',
    'Embajador del Deporte Oro Demo' => 'EmbajadordelDeporteOroDemo.png',
    'Embajador del Deporte Plata Demo' => 'EmbajadordelDeportePlataDemo.png',
    'Embajador del Deporte Bronce Demo' => 'EmbajadordelDeporteBronceDemo.png',
    'Embajador del Arte' => 'EmbajadordelArte.png',
    'Embajador del Deporte' => 'EmbajadordelDeporte.png',
    'Formación y Actualización' => 'FormacionyActualizacion.png',
    'Movilidad e Intercambio' => 'MovilidadeIntercambio.png',
    'Talento Científico' => 'TalentoCientifico.png',
    'Talento Innovador' => 'TalentoInnovador.png'
];
$nombre_insignia = isset($insignia['nombre_insignia']) ? trim($insignia['nombre_insignia']) : '';
$imagen_path = 'imagen/Insignias/ResponsabilidadSocial.png';
if ($nombre_insignia !== '' && isset($mapeo_nombre_imagen[$nombre_insignia])) {
    $imagen_path = 'imagen/Insignias/' . $mapeo_nombre_imagen[$nombre_insignia];
} elseif (strpos($insignia['codigo'], 'ART') !== false) {
    $imagen_path = 'imagen/Insignias/EmbajadordelArte.png';
} elseif (strpos($insignia['codigo'], 'EMB') !== false) {
    $imagen_path = 'imagen/Insignias/EmbajadordelDeporte.png';
} elseif (strpos($insignia['codigo'], 'TAL') !== false) {
    $imagen_path = 'imagen/Insignias/TalentoCientifico.png';
} elseif (strpos($insignia['codigo'], 'INN') !== false) {
    $imagen_path = 'imagen/Insignias/TalentoInnovador.png';
} elseif (strpos($insignia['codigo'], 'SOC') !== false) {
    $imagen_path = 'imagen/Insignias/ResponsabilidadSocial.png';
} elseif (strpos($insignia['codigo'], 'FOR') !== false) {
    $imagen_path = 'imagen/Insignias/FormacionyActualizacion.png';
} elseif (strpos($insignia['codigo'], 'MOV') !== false) {
    $imagen_path = 'imagen/Insignias/MovilidadeIntercambio.png';
}

// Formatear fecha
$fecha_formateada = date('d-m-Y', strtotime($insignia['fecha_emision']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Insignia - <?php echo htmlspecialchars($insignia['codigo']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 0;
        }
        
        .document-container {
            position: relative;
            width: 100%;
            max-width: 8.5in;
            min-height: 11in;
            margin: 0 auto;
            background-image: url('imagen/Hoja_membrentada.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 60px 80px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .header {
            background: 
                linear-gradient(135deg, 
                    rgba(30, 60, 114, 0.95) 0%, 
                    rgba(42, 82, 152, 0.98) 30%,
                    rgba(30, 60, 114, 0.95) 60%,
                    rgba(26, 52, 100, 0.95) 100%);
            backdrop-filter: blur(60px) saturate(200%);
            -webkit-backdrop-filter: blur(60px) saturate(200%);
            color: white;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 35px 0;
            box-shadow: 
                0 10px 50px rgba(0,0,0,0.4),
                0 5px 25px rgba(0,0,0,0.2),
                inset 0 2px 0 rgba(255,255,255,0.25),
                inset 0 -1px 0 rgba(255,255,255,0.05);
            border-bottom: 2px solid rgba(255,255,255,0.15);
            border-top: 2px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 50% 0%, rgba(255,255,255,0.1) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
        }
        
        .header-logo {
            position: absolute;
            left: -260px;
            top: 50%;
            transform: translateY(-50%);
            height: 60px;
            width: auto;
            filter: brightness(0) invert(1);
            transition: all 0.3s ease;
        }
        
        .header-logo:hover {
            transform: translateY(-50%) scale(1.1);
            filter: brightness(0) invert(1) drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
        }
        
        .header h1 {
            font-size: 32px;
            margin: 0;
            font-weight: 900;
            text-shadow: 
                0 6px 12px rgba(0,0,0,0.5),
                0 0 30px rgba(59, 130, 246, 0.4),
                0 0 60px rgba(59, 130, 246, 0.2);
            letter-spacing: -0.5px;
        }
        
        .title {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #1b396a;
            margin-bottom: 40px;
            margin-top: 80px;
            text-transform: uppercase;
        }
        
        /* FOOTER PROFESIONAL AZUL */
        footer {
            background: #1e3c72;
            color: white;
            padding: 40px 0;
            margin-top: 50px;
            text-align: center;
            width: 100%;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-section {
            margin-bottom: 25px;
        }
        
        .footer-section h3 {
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
        
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .insignia-display {
            text-align: center;
        }
        
        .insignia-hexagon {
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
            background-image: url('<?php echo $imagen_path; ?>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            border: none;
            position: relative;
        }
        
        .insignia-code {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        
        .details-section {
            font-size: 13px;
        }
        
        .detail-row {
            margin-bottom: 12px;
            line-height: 1.6;
        }
        
        .detail-label {
            font-weight: bold;
            color: #1b396a;
            display: inline-block;
            min-width: 200px;
        }
        
        .detail-value {
            color: #333;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1b396a;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 2px solid #1b396a;
            padding-bottom: 5px;
        }
        
        .section-content {
            font-size: 13px;
            line-height: 1.8;
            color: #333;
            text-align: justify;
            margin-bottom: 20px;
        }
        
        .footer-section {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        
        .footer-label {
            font-weight: bold;
            color: #1b396a;
            margin-bottom: 5px;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
        }
        
        .btn {
            background: #1b396a;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin: 8px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #0f2a4a;
        }
        
        /* RESPONSIVE - Tablets */
        @media (max-width: 1024px) {
            .header {
                padding: 25px 0;
            }
            
            .header-logo {
                height: 50px;
                left: -180px;
            }
            
            .header h1 {
                font-size: 28px;
            }
            
            .container {
                padding: 15px;
            }
            
            .document-container {
                padding: 40px 50px;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .title {
                font-size: 28px;
                margin-top: 50px;
            }
        }
        
        /* RESPONSIVE - Móviles y tablets pequeñas */
        @media (max-width: 768px) {
            body {
                padding: 0;
            }
            
            .header {
                padding: 15px 0;
            }
            
            .header-content {
                padding: 0 10px;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                gap: 8px;
            }
            
            .header-logo {
                position: relative;
                left: auto;
                top: auto;
                transform: none;
                height: 40px;
                width: auto;
                display: block;
                margin: 0;
            }
            
            .header h1 {
                font-size: 18px;
                margin: 0;
                text-shadow: none;
            }
            
            .container {
                padding: 10px;
                margin: 0;
            }
            
            .document-container {
                padding: 20px 15px;
                min-height: auto;
            }
            
            .title {
                font-size: 20px;
                margin-top: 30px;
                margin-bottom: 25px;
                padding: 0 10px;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .insignia-display {
                text-align: center;
            }
            
            .insignia-hexagon {
                width: 150px;
                height: 150px;
                margin: 0 auto 15px;
            }
            
            .insignia-code {
                font-size: 11px;
            }
            
            .detail-label {
                min-width: auto;
                display: block;
                margin-bottom: 5px;
                font-size: 12px;
            }
            
            .detail-row {
                margin-bottom: 15px;
                padding: 10px;
                background: #f9f9f9;
                border-radius: 5px;
            }
            
            .detail-value {
                font-size: 12px;
                word-break: break-word;
            }
            
            .section-title {
                font-size: 14px;
                margin-top: 20px;
            }
            
            .section-content {
                font-size: 12px;
                line-height: 1.6;
            }
            
            .actions {
                padding: 15px 10px;
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                padding: 12px 20px;
                font-size: 14px;
                margin: 5px 0;
                width: 100%;
                text-align: center;
            }
            
            footer {
                padding: 30px 15px;
            }
            
            .footer-content {
                padding: 0 10px;
            }
            
            .footer-section {
                margin-bottom: 20px;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        /* RESPONSIVE - Móviles pequeños (iPhone SE, etc.) */
        @media (max-width: 480px) {
            .header {
                padding: 12px 0;
            }
            
            .header h1 {
                font-size: 16px;
            }
            
            .header-logo {
                height: 35px;
            }
            
            .title {
                font-size: 18px;
                margin-top: 20px;
                margin-bottom: 20px;
            }
            
            .document-container {
                padding: 15px 10px;
            }
            
            .insignia-hexagon {
                width: 120px;
                height: 120px;
            }
            
            .detail-label {
                font-size: 11px;
            }
            
            .detail-value {
                font-size: 11px;
            }
            
            .section-title {
                font-size: 13px;
            }
            
            .section-content {
                font-size: 11px;
            }
            
            .btn {
                padding: 10px 16px;
                font-size: 13px;
            }
            
            footer {
                padding: 20px 10px;
            }
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                padding: 0;
            }
            
            .actions {
                display: none;
            }
            
            .header {
                display: none;
            }
            
            footer {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="header-content">
            <img src="imagen/logo.png" alt="TecNM Logo" class="header-logo" onerror="this.style.display='none';">
            <h1>Validación Pública</h1>
        </div>
    </header>
    
    <div class="container">
        <div class="document-container">
            <!-- Título principal -->
            <div class="title">VALIDACIÓN DE INSIGNIA</div>
            
            <!-- Contenido principal: Insignia y Detalles -->
            <div class="content-grid">
                <!-- Insignia a la izquierda -->
                <div class="insignia-display">
                    <div class="insignia-hexagon"></div>
                    <div class="insignia-code">
                        Insignia<br>
                        <?php echo htmlspecialchars($insignia['codigo']); ?>
                    </div>
                </div>
                
                <!-- Detalles a la derecha -->
                <div class="details-section">
                    <div class="detail-row">
                        <span class="detail-label">Código de identificación de la InsigniaTecNM:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($insignia['codigo']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nombre de la InsigniaTecNM (Subcategoría):</span>
                        <span class="detail-value"><?php echo htmlspecialchars($insignia['nombre_insignia']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Categoría de la InsigniaTecNM:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($insignia['categoria']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Destinatario:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($insignia['destinatario']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha de emisión:</span>
                        <span class="detail-value"><?php echo $fecha_formateada; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Emisor (TecNM o Instituto/Centro):</span>
                        <span class="detail-value"><?php echo htmlspecialchars($insignia['emisor']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Descripción -->
            <div class="section-title">Descripción</div>
            <div class="section-content">
                <?php echo nl2br(htmlspecialchars($insignia['descripcion'])); ?>
            </div>
            
            <!-- Criterios -->
            <div class="section-title">Criterios para su emisión</div>
            <div class="section-content">
                <?php echo nl2br(htmlspecialchars($insignia['criterios'])); ?>
            </div>
            
            <!-- Evidencia -->
            <div class="section-title">Evidencia</div>
            <div class="section-content">
                <?php echo htmlspecialchars($insignia['evidencia']); ?>
            </div>
            
            <!-- Footer con responsable -->
            <div class="footer-section">
                <div class="footer-label">Responsable de la captura de los Metadatos</div>
                <div><?php echo htmlspecialchars($insignia['responsable']); ?></div>
                <div style="margin-top: 10px;">
                    <span class="footer-label">Código de identificación del Responsable de la captura de los Metadatos:</span>
                    <?php echo htmlspecialchars($insignia['codigo_responsable']); ?>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <button onclick="window.print()" class="btn">🖨️ Imprimir</button>
            <a href="ver_insignia_completa_publica.php?insignia=<?php echo urlencode($insignia['codigo']); ?>" class="btn">← Volver</a>
        </div>
    </div>
    
    <!-- FOOTER PROFESIONAL -->
    <footer>
        <div class="footer-content">
            <div class="copyright">
                <p>Copyright 2025 - TecNM</p>
                <p>Ultima actualización - Diciembre 2025</p>
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

