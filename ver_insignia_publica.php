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
    // Verificar qué tabla existe - PRIORIDAD: usar insigniasotorgadas primero (donde se guardan las nuevas insignias)
    $tabla_existe_i = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
    $usar_tabla_i = ($tabla_existe_i && $tabla_existe_i->num_rows > 0);
    
    $tabla_existe_t = $conexion->query("SHOW TABLES LIKE 'T_insignias_otorgadas'");
    $usar_tabla_t = ($tabla_existe_t && $tabla_existe_t->num_rows > 0);
    
    if ($usar_tabla_i) {
        // Usar insigniasotorgadas (donde se guardan las nuevas insignias)
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
        // Usar T_insignias_otorgadas con JOIN a T_insignias
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
                END as categoria
            FROM T_insignias_otorgadas tio
            LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
            LEFT JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
            LEFT JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
            LEFT JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
            WHERE CONCAT(ti.id, '-', pe.Nombre_Periodo) = ?
        ";
    } else {
        // Si no existe ninguna tabla, mostrar error
        die('Error: No se encontró ninguna tabla de insignias otorgadas. Verifica que exista T_insignias_otorgadas o insigniasotorgadas en la base de datos.');
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
$imagen_path = 'imagen/Insignias/ResponsabilidadSocial.png'; // Por defecto
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

// Obtener IP del servidor
$server_ip = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (empty($server_ip) || $server_ip === '::1') {
    $server_ip = 'localhost';
}
$port = $_SERVER['SERVER_PORT'] ?? '80';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base_url = $protocol . "://" . $server_ip . ($port != '80' && $port != '443' ? ':' . $port : '');

// Obtener la ruta del proyecto dinámicamente
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
// Normalizar la ruta: eliminar barras iniciales y finales, pero mantener la estructura
$project_path = trim($script_dir, '/');
if (!empty($project_path)) {
    $project_path = '/' . $project_path;
}

// URL de validación - Redirigir directamente al certificado completo (igual que el clic en la imagen)
$url_validacion = $base_url . $project_path . "/ver_insignia_completa_publica.php?insignia=" . urlencode($insignia['codigo']) . "&solo=1";

// Generar QR usando servicio alternativo (más confiable)
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($url_validacion);

// Obtener firma digital del responsable si existe
$insignia['firma_digital_base64'] = null;
if (!empty($insignia['responsable_id'])) {
    try {
        $check_field = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'firma_digital_base64'");
        if ($check_field && $check_field->num_rows > 0) {
            $check_responsable_id = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'id'");
            $tiene_id_responsable = ($check_responsable_id && $check_responsable_id->num_rows > 0);
            $campo_id_responsable = $tiene_id_responsable ? 'id' : 'ID_responsable';
            $sql_firma = "SELECT firma_digital_base64 FROM responsable_emision WHERE " . $campo_id_responsable . " = ? LIMIT 1";
            $stmt_firma = $conexion->prepare($sql_firma);
            if ($stmt_firma) {
                $stmt_firma->bind_param("i", $insignia['responsable_id']);
                $stmt_firma->execute();
                $resultado_firma = $stmt_firma->get_result();
                if ($resultado_firma && $resultado_firma->num_rows > 0) {
                    $fila_firma = $resultado_firma->fetch_assoc();
                    $insignia['firma_digital_base64'] = $fila_firma['firma_digital_base64'] ?? null;
                }
                $stmt_firma->close();
            }
        }
    } catch (Exception $e) {
        error_log("Error al obtener firma digital: " . $e->getMessage());
    }
}

// Función para formatear fecha en español
function formatearFechaEspanol($fecha) {
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    $timestamp = strtotime($fecha);
    $mes = (int)date('n', $timestamp);
    $anio = date('Y', $timestamp);
    return $meses[$mes] . ' ' . $anio;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM - <?php echo htmlspecialchars($insignia['nombre_insignia'] ?? 'Validación'); ?></title>
    
    <!-- Meta tags para redes sociales (Open Graph) -->
    <meta property="og:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia['nombre_insignia'] ?? 'Insignia TecNM'); ?>">
    <meta property="og:description" content="Insignia otorgada a <?php echo htmlspecialchars($insignia['destinatario'] ?? ''); ?>">
    <meta property="og:image" content="<?php echo $base_url . $project_path . '/' . $imagen_path; ?>">
    <meta property="og:url" content="<?php echo $base_url . $project_path; ?>/ver_insignia_publica.php?insignia=<?php echo urlencode($insignia['codigo']); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TecNM Insignias">
    
    <!-- Meta tags para Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia['nombre_insignia'] ?? 'Insignia TecNM'); ?>">
    <meta name="twitter:description" content="Insignia otorgada a <?php echo htmlspecialchars($insignia['destinatario'] ?? ''); ?>">
    <meta name="twitter:image" content="<?php echo $base_url . $project_path . '/' . $imagen_path; ?>">
    
    <!-- Font Awesome para logos oficiales de redes sociales -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1b396a 0%, #002855 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: #1b396a;
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            background: white;
            padding: 40px;
            border-radius: 0 0 15px 15px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .validation-section {
            background: transparent;
            padding: 25px;
            border-radius: 0;
            border: none;
            box-shadow: none;
        }

        .section-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #1b396a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .insignia-image {
            width: 300px;
            height: 300px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin: 0 auto;
            border: none;
            border-radius: 0;
            box-shadow: none;
            transition: transform 0.3s;
            display: block;
        }

        a:hover .insignia-image {
            transform: scale(1.05);
            cursor: pointer;
        }

        .qr-code {
            text-align: center;
        }

        .qr-code img {
            width: 300px;
            height: 300px;
            border: none;
            border-radius: 0;
            padding: 0;
            background: transparent;
            display: block;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
        }
        
        .btn i {
            font-size: 1.2em;
        }

        .btn-success {
            background: #25D366;
        }

        .btn-primary {
            background: #1877F2;
        }

        .btn-info {
            background: #1DA1F2;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .validation-link-section {
            grid-column: 1 / -1;
        }

        .validation-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .actions-section {
            grid-column: 1 / -1;
            background: #f8f9fa;
        }

        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
            
            .validation-link-section, .actions-section {
                grid-column: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Validación de Insignia</h1>
            <p>Sistema de Verificación TecNM</p>
        </div>

        <div class="content">
            <!-- Certificado con hoja membretada -->
            <div class="validation-section" style="grid-column: 1 / -1; margin-bottom: 30px;">
                <div class="section-title">
                    📜 Certificado de Validación
                </div>
                <div style="position: relative; width: 100%; max-width: 6in; height: auto; min-height: 750px; aspect-ratio: 8.5 / 11; background: white; border: 2px solid #1b396a; border-radius: 8px; padding: 40px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); background-image: url('imagen/Hoja_membrentada.png'); background-size: cover; background-position: center; background-repeat: no-repeat; margin: 0 auto;">
                    <!-- Título institucional -->
                    <div style="font-size: 22px; font-weight: bold; color: #1b396a; margin-top: 40px; margin-bottom: 8px; text-align: center;">
                        EL TECNOLÓGICO NACIONAL DE MÉXICO
                    </div>
                    <div style="font-size: 18px; color: #1b396a; margin-bottom: 20px; text-align: center;">
                        OTORGA EL PRESENTE
                    </div>
                    
                    <!-- Título principal del reconocimiento -->
                    <div style="font-size: 26px; font-weight: bold; color: #d4af37; margin-bottom: 15px; text-align: center; text-transform: uppercase; line-height: 1.2;">
                        RECONOCIMIENTO INSTITUCIONAL<br>
                        CON IMPACTO CURRICULAR
                    </div>
                    
                    <!-- Destinatario -->
                    <div style="font-size: 18px; margin-bottom: 5px; text-align: center; color: #666;">A</div>
                    <div style="font-size: 28px; font-weight: bold; color: #333; margin-bottom: 20px; text-align: center;">
                        <?php echo htmlspecialchars($insignia['destinatario']); ?>
                    </div>
                    
                    <!-- Texto descriptivo (ajustable automáticamente según longitud) -->
                    <?php 
                    $descripcion = htmlspecialchars($insignia['descripcion'] ?? 'Este reconocimiento se otorga por su destacada participación y compromiso con los valores del Tecnológico Nacional de México.');
                    $descripcion_length = strlen($descripcion);
                    // Ajustar tamaño de fuente automáticamente según longitud del texto
                    if ($descripcion_length > 1000) {
                        $font_size = 12;
                        $line_height = 1.5;
                        $margin_bottom = 35;
                    } elseif ($descripcion_length > 800) {
                        $font_size = 13;
                        $line_height = 1.55;
                        $margin_bottom = 40;
                    } elseif ($descripcion_length > 600) {
                        $font_size = 14;
                        $line_height = 1.6;
                        $margin_bottom = 45;
                    } elseif ($descripcion_length > 400) {
                        $font_size = 15;
                        $line_height = 1.65;
                        $margin_bottom = 50;
                    } else {
                        $font_size = 18;
                        $line_height = 1.8;
                        $margin_bottom = 60;
                    }
                    ?>
                    <div style="font-size: <?php echo $font_size; ?>px; text-align: justify; line-height: <?php echo $line_height; ?>; margin-bottom: <?php echo $margin_bottom; ?>px; padding: 0 50px; color: #333; word-wrap: break-word; hyphens: auto;">
                        <?php echo nl2br($descripcion); ?>
                    </div>
                    
                    <!-- Código QR de Verificación con imagen de insignia en el centro -->
                    <div style="position: absolute; bottom: 40px; left: 40px; width: 90px; height: 90px; background: white; padding: 5px; border: 1px solid #e5e7eb; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="position: relative; width: 100%; height: 100%;">
                            <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="Código QR de Verificación" style="width: 100%; height: 100%; object-fit: contain; display: block;">
                            <img src="<?php echo htmlspecialchars($imagen_path); ?>" alt="<?php echo htmlspecialchars($insignia['nombre_insignia'] ?? 'Insignia'); ?>" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 25px; height: 25px; background: white; border-radius: 4px; padding: 2px; border: 1px solid #1b396a; object-fit: contain;">
                        </div>
                    </div>
                    
                    <!-- Firma en la esquina inferior derecha -->
                    <div style="position: absolute; bottom: 25px; right: 60px; text-align: left; font-size: 9px; color: #333; max-width: 300px;">
                        <?php if (!empty($insignia['firma_digital_base64'])): ?>
                        <!-- Mostrar solo el SELLO DIGITAL REAL del SAT completo (tamaño más grande) -->
                        <div style="font-size: 6px; font-family: 'Courier New', monospace; color: #333; word-break: break-all; line-height: 1.2; margin-bottom: 6px; letter-spacing: -0.1px;">
                            &lt;sello&gt;<?php echo htmlspecialchars($insignia['firma_digital_base64']); ?>&lt;/sello&gt;
                        </div>
                        <?php endif; ?>
                        <div style="font-weight: bold; color: #1b396a; margin-top: 4px; font-size: 11px;"><?php echo htmlspecialchars($insignia['responsable'] ?? 'Sistema TecNM'); ?></div>
                        <div style="font-size: 8px; color: #666; margin-top: 2px;"><?php echo htmlspecialchars($insignia['cargo_responsable'] ?? 'RESPONSABLE DE EMISIÓN'); ?></div>
                    </div>
                    
                    <!-- Fecha y ubicación -->
                    <div style="position: absolute; bottom: 10px; right: 60px; font-size: 7px; color: #666; text-align: right; background: rgba(255,255,255,0.9); padding: 4px; border-radius: 2px; width: 80px;">
                        CIUDAD DE MÉXICO<br>
                        <?php echo formatearFechaEspanol($insignia['fecha_emision']); ?>
                    </div>
                </div>
            </div>

            <!-- Enlace de Validación -->
            <div class="validation-section validation-link-section">
                <div class="section-title">
                    🔗 Enlace de Validación
                </div>
                <input type="text" value="<?php echo htmlspecialchars($url_validacion); ?>" class="validation-input" readonly id="validationLink">
                <button onclick="copiarEnlace()" class="btn btn-success" style="display: block; width: 100%;">
                    📋 Copiar Enlace
                </button>
                <p style="margin-top: 15px; font-size: 14px; color: #6c757d; text-align: center;">
                    Comparte este enlace para que otros puedan verificar la insignia
                </p>
            </div>

            <!-- Botón Ver Validación -->
            <div class="actions-section" style="background: #f8f9fa; margin-bottom: 20px; text-align: center;">
                <a href="ver_validacion_publica.php?insignia=<?php echo urlencode($insignia['codigo']); ?>" class="btn" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); text-decoration: none; display: inline-flex; align-items: center; gap: 10px;" target="_blank">
                    <i class="fas fa-check-circle"></i> Ver Validación
                </a>
            </div>

            <!-- Compartir en Redes Sociales -->
            <div class="actions-section" style="background: #f8f9fa; margin-bottom: 20px;">
                <div class="section-title" style="color: #1b396a;">
                    📱 Compartir en Redes Sociales
                </div>
                <div class="action-buttons">
                    <?php
                    // Construir URL completa de la insignia (sin project_path para que sea la URL directa)
                    $url_insignia = $base_url . '/ver_insignia_publica.php?insignia=' . urlencode($insignia['codigo']);
                    
                    // Crear mensaje sin emojis ni símbolos especiales
                    // La URL debe estar en una línea separada o con espacios para que WhatsApp la reconozca como enlace
                    // Usar salto de línea (\n) para separar la URL y que WhatsApp la detecte como enlace clickeable
                    $mensaje_whatsapp = 'He recibido una insignia de ' . $insignia['nombre_insignia'] . ' del TecNM. ' . htmlspecialchars($insignia['destinatario']) . '. Ver mi insignia:' . "\n" . $url_insignia;
                    
                    // Codificar todo el mensaje para WhatsApp (rawurlencode codifica correctamente la URL dentro del mensaje)
                    $whatsapp_url = 'https://wa.me/?text=' . rawurlencode($mensaje_whatsapp);
                    ?>
                    <a href="<?php echo $whatsapp_url; ?>" class="btn btn-success" target="_blank">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($base_url . $project_path . '/ver_insignia_publica.php?insignia=' . urlencode($insignia['codigo'])); ?>" class="btn btn-primary" target="_blank">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('🎖️ ¡He recibido una insignia de ' . $insignia['nombre_insignia'] . ' del TecNM! 👨‍🎓'); ?>&url=<?php echo urlencode($base_url . $project_path . '/ver_insignia_publica.php?insignia=' . urlencode($insignia['codigo'])); ?>" class="btn btn-info" target="_blank">
                        <i class="fab fa-x-twitter"></i> Twitter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copiarEnlace() {
            const linkInput = document.getElementById('validationLink');
            linkInput.select();
            linkInput.setSelectionRange(0, 99999);
            
            navigator.clipboard.writeText(linkInput.value).then(function() {
                alert('✅ Enlace copiado al portapapeles');
            }, function(err) {
                alert('❌ Error al copiar: ' + err);
            });
        }
    </script>
</body>
</html>