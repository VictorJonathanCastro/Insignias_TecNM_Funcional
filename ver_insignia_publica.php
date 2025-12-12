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
                'TecNM / Instituto Tecnológico de San Marcos' as emisor,
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
                'TecNM / Instituto Tecnológico de San Marcos' as emisor,
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

// Determinar la ruta de la imagen basada en el código de insignia
$imagen_path = 'imagen/Insignias/ResponsabilidadSocial.png';
if (strpos($insignia['codigo'], 'ART') !== false) {
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        .title {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #1b396a;
            margin-bottom: 40px;
            text-transform: uppercase;
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
            }
        }
        
        /* RESPONSIVE - Móviles y tablets pequeñas */
        @media (max-width: 768px) {
            body {
                padding: 0;
            }
            
            .container {
                padding: 10px;
                margin: 0;
            }
            
            .document-container {
                padding: 30px 20px;
                min-height: auto;
            }
            
            .title {
                font-size: 24px;
                margin-top: 30px;
                margin-bottom: 30px;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            
            .insignia-hexagon {
                width: 150px;
                height: 150px;
            }
            
            .detail-label {
                min-width: auto;
                display: block;
                margin-bottom: 5px;
            }
            
            .detail-row {
                margin-bottom: 15px;
            }
            
            .section-title {
                font-size: 14px;
            }
            
            .section-content {
                font-size: 12px;
            }
            
            .actions {
                padding: 15px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 14px;
                margin: 5px;
                display: block;
                width: 100%;
                text-align: center;
            }
        }
        
        /* RESPONSIVE - Móviles pequeños (iPhone SE, etc.) */
        @media (max-width: 480px) {
            .title {
                font-size: 20px;
                margin-top: 20px;
                margin-bottom: 20px;
            }
            
            .document-container {
                padding: 20px 15px;
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
                padding: 8px 16px;
                font-size: 12px;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="document-container">
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
            
            <!-- Archivo Visual -->
            <div class="section-title">Archivo Visual de la InsigniaTecNM</div>
            <div class="section-content">
                <?php echo htmlspecialchars($insignia['archivo_visual']); ?> (archivo)
            </div>
            
            <!-- Footer con responsable -->
            <div class="footer-section">
                <div class="footer-label">Responsable de la captura de los Metadatos</div>
                <div>Nombre completo del usuario con permisos de generador de insignia: <?php echo htmlspecialchars($insignia['responsable']); ?></div>
                <div style="margin-top: 10px;">
                    <span class="footer-label">Código de identificación del Responsable de la captura de los Metadatos:</span>
                    <?php echo htmlspecialchars($insignia['codigo_responsable']); ?>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <button onclick="window.print()" class="btn">🖨️ Imprimir</button>
            <a href="consulta_publica.php" class="btn">← Volver</a>
        </div>
    </div>
</body>
</html>
