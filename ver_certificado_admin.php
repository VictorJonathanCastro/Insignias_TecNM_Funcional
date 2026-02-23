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

// Detectar si la tabla tiene columnas de firma digital (no existen en esquema antiguo)
$tiene_firma = false;
$check_firma = @$conexion->query("SHOW COLUMNS FROM insigniasotorgadas LIKE 'firma_digital_base64'");
if ($check_firma && $check_firma->num_rows > 0) {
    $tiene_firma = true;
}
$campos_firma_sql = $tiene_firma
    ? "io.firma_digital_base64, io.hash_verificacion, io.certificado_info, io.fecha_firma"
    : "NULL as firma_digital_base64, NULL as hash_verificacion, NULL as certificado_info, NULL as fecha_firma";

// Detectar nombre del campo ID (ID_otorgada o id) y Destinatario o destinatario_id
$check_id_otorgada = @$conexion->query("SHOW COLUMNS FROM insigniasotorgadas LIKE 'ID_otorgada'");
$campo_id_io = ($check_id_otorgada && $check_id_otorgada->num_rows > 0) ? 'ID_otorgada' : 'id';
$check_dest = @$conexion->query("SHOW COLUMNS FROM insigniasotorgadas LIKE 'Destinatario'");
$campo_dest_io = ($check_dest && $check_dest->num_rows > 0) ? 'Destinatario' : 'destinatario_id';
$check_fecha = @$conexion->query("SHOW COLUMNS FROM insigniasotorgadas LIKE 'Fecha_Emision'");
$campo_fecha_io = ($check_fecha && $check_fecha->num_rows > 0) ? 'Fecha_Emision' : 'fecha_otorgamiento';
$check_codigo = @$conexion->query("SHOW COLUMNS FROM insigniasotorgadas LIKE 'Codigo_Insignia'");
$campo_codigo_io = ($check_codigo && $check_codigo->num_rows > 0) ? 'Codigo_Insignia' : 'clave_insignia';

$campo_id_dest = 'ID_destinatario';
$check_dest_id = @$conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
if ($check_dest_id && $check_dest_id->num_rows > 0) {
    $campo_id_dest = 'id';
}

$campo_resp_io = 'Responsable_Emision';
$campo_resp_re = 'ID_responsable';
$check_resp_io = @$conexion->query("SHOW COLUMNS FROM insigniasotorgadas LIKE 'responsable_id'");
if ($check_resp_io && $check_resp_io->num_rows > 0) {
    $campo_resp_io = 'responsable_id';
    $check_resp_re = @$conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'id'");
    if ($check_resp_re && $check_resp_re->num_rows > 0) {
        $campo_resp_re = 'id';
    }
}

// Consulta para obtener datos de la insignia (compatible con esquema viejo y nuevo)
$sql = "
    SELECT 
        io.{$campo_id_io} as id,
        io.{$campo_fecha_io} as fecha_otorgamiento,
        io.{$campo_codigo_io} as clave_insignia,
        'Certificación oficial' as evidencia,
        d.Nombre_Completo as destinatario,
        d.Matricula,
        'Programa Académico' as Programa,
        'Reconocimiento por méritos destacados' as Descripcion,
        'Cumplimiento de criterios establecidos' as Criterio,
        CASE 
            WHEN io.{$campo_codigo_io} LIKE '%ART%' THEN 'Embajador del Arte'
            WHEN io.{$campo_codigo_io} LIKE '%EMB%' THEN 'Embajador del Deporte'
            WHEN io.{$campo_codigo_io} LIKE '%TAL%' THEN 'Talento Científico'
            WHEN io.{$campo_codigo_io} LIKE '%INN%' THEN 'Talento Innovador'
            WHEN io.{$campo_codigo_io} LIKE '%SOC%' THEN 'Responsabilidad Social'
            WHEN io.{$campo_codigo_io} LIKE '%FOR%' THEN 'Formación y Actualización'
            WHEN io.{$campo_codigo_io} LIKE '%MOV%' THEN 'Movilidad e Intercambio'
            ELSE 'Insignia TecNM'
        END as nombre_insignia,
        CASE 
            WHEN io.{$campo_codigo_io} LIKE '%EMB%' THEN 'Desarrollo Personal'
            WHEN io.{$campo_codigo_io} LIKE '%TAL%' OR io.{$campo_codigo_io} LIKE '%INN%' OR io.{$campo_codigo_io} LIKE '%FOR%' THEN 'Desarrollo Académico'
            WHEN io.{$campo_codigo_io} LIKE '%ART%' OR io.{$campo_codigo_io} LIKE '%SOC%' OR io.{$campo_codigo_io} LIKE '%MOV%' THEN 'Formación Integral'
            ELSE 'Formación Integral'
        END as categoria,
        'Tecnológico Nacional de México' as institucion,
        '2025-1' as periodo,
        'Activo' as estatus,
        'insignia_default.png' as archivo_imagen,
        $campos_firma_sql,
        re.Nombre_Completo as responsable_nombre,
        NULL as responsable_firma,
        'RESPONSABLE DE EMISIÓN' as cargo_responsable
    FROM insigniasotorgadas io
    LEFT JOIN destinatario d ON io.{$campo_dest_io} = d.{$campo_id_dest}
    LEFT JOIN responsable_emision re ON io.{$campo_resp_io} = re.{$campo_resp_re}
    WHERE " . ($is_numeric ? "io.{$campo_id_io}" : "io.{$campo_codigo_io}") . " = ?
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

// Función para formatear fecha
function formatearFecha($fecha) {
    if (!$fecha) return 'N/A';
    $fecha_obj = new DateTime($fecha);
    return $fecha_obj->format('d/m/Y');
}

// Generar URL de validación
$url_validacion = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/verificar_insignia.php?clave=" . urlencode($insignia['clave_insignia']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM - <?php echo htmlspecialchars($insignia['nombre_insignia']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* ==== ENCABEZADO ==== */
        header {
            background: #1b396a;
            color: white;
            text-align: center;
            padding: 20px 0;
        }
        header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: visible;
            display: block;
        }
        
        .header {
            background: linear-gradient(135deg, #002855, #1b396a);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px;
        }
        
        .insignia-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .insignia-hexagon {
            width: 200px;
            height: 200px;
            background-image: url('<?php echo $imagen_path; ?>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            margin-bottom: 15px;
            position: relative;
        }
        
        .insignia-logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            color: #002855;
            font-size: 24px;
            font-weight: bold;
        }
        
        .insignia-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .insignia-category {
            font-size: 16px;
            color: #4CAF50;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .insignia-code {
            font-size: 14px;
            font-weight: bold;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        .document-preview {
            background-image: url('imagen/Hoja_membrentada.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            width: 8.5in;
            height: 11in;
            position: relative;
            color: #333;
            overflow: visible;
            margin: 0 auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .document-title {
            font-size: 12px;
            font-weight: bold;
            color: #002855;
            margin-bottom: 10px;
        }
        
        .document-insignia {
            width: 80px;
            height: 80px;
            background-image: url('imagen/insignia_Responsabilidad Social.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin: 0 auto 10px;
        }
        
        .metadata-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        
        .metadata-title {
            font-size: 20px;
            font-weight: bold;
            color: #002855;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .metadata-item {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .metadata-label {
            font-weight: bold;
            color: #002855;
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .metadata-value {
            color: #333;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .metadata-value.long-text {
            font-size: 13px;
            line-height: 1.6;
        }
        
        .actions {
            text-align: center;
            padding: 20px;
            background: transparent;
            margin-top: 20px;
            display: block;
            width: 100%;
            position: relative;
        }
        
        .btn {
            background: linear-gradient(135deg, #1b396a, #002855);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 8px;
            transition: all 0.3s ease;
            min-width: 120px;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, #002855, #1b396a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            border: none;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #495057, #6c757d);
            color: white;
        }
        
        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 20px;
            }
            
            .insignia-hexagon {
                width: 250px;
                height: 250px;
            }
        }
        
        /* ==== PIE DE PÁGINA ==== */
        footer {
            font-size: 14px;
            margin-top: 40px;
            position: relative;
            width: 100%;
            background: #1b396a;
            color: white;
            text-align: center;
            padding: 10px 0;
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
    <header>
        <h1>SISTEMA DE INSIGNIAS TECNM</h1>
    </header>
    
    <div class="container">
        <div class="header">
            <h1>Insignias TecNM MetaDatos</h1>
        </div>
        
        <div class="content" style="display: flex; justify-content: center; align-items: center; padding: 20px;">
            <div class="document-preview">
                    <!-- Título institucional -->
                    <div style="font-size: 12px; font-weight: bold; color: #1b396a; margin-top: 100px; margin-bottom: 8px; text-align: center;">
                        EL TECNOLÓGICO NACIONAL DE MÉXICO
                    </div>
                    <div style="font-size: 10px; color: #1b396a; margin-bottom: 12px; text-align: center;">
                        OTORGA EL PRESENTE
                    </div>
                    
                    <!-- Título principal del reconocimiento -->
                    <div style="font-size: 13px; font-weight: bold; color: #d4af37; margin-bottom: 15px; text-align: center; text-transform: uppercase;">
                        RECONOCIMIENTO INSTITUCIONAL<br>
                        CON IMPACTO CURRICULAR
                    </div>
                    
                    <!-- Destinatario -->
                    <div style="font-size: 9px; margin-bottom: 6px; text-align: center;">A</div>
                    <div style="font-size: 13px; font-weight: bold; color: #1b396a; margin-bottom: 12px; text-align: center;">
                        <?php echo htmlspecialchars($insignia['destinatario']); ?>
                    </div>
                    
                    <!-- Texto descriptivo -->
                    <div style="font-size: 8px; text-align: left; line-height: 1.3; margin-bottom: 30px; padding: 0 12px;">
                        <?php echo nl2br(htmlspecialchars($insignia['Descripcion'])); ?>
                    </div>
                    
                    <!-- Badge hexagonal en la esquina inferior izquierda -->
                    <div style="position: absolute; bottom: 20px; left: 20px; width: 50px; height: 50px;">
                        <div style="width: 100%; height: 100%; background-image: url('imagen/insignia_Responsabilidad Social.png'); background-size: contain; background-repeat: no-repeat; background-position: center;"></div>
                    </div>
                    
                    <!-- Firma Digital en la esquina inferior derecha -->
                    <div style="position: absolute; bottom: 20px; right: 40px; text-align: center; font-size: 8px; color: #333; background: white; padding: 10px 12px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-width: 130px; border: 1px solid #e3f2fd;">
                        <div style="font-weight: bold; color: #003366; font-size: 8px; margin-bottom: 6px; text-align: center;">
                            Emitido por el Tecnologico Nacional de Mexico
                        </div>
                        
                        <div style="border-bottom: 2px solid #003366; width: 100%; margin-bottom: 6px; position: relative;"></div>
                        <div style="font-weight: bold; color: #003366; font-size: 7px; margin-bottom: 3px; text-align: center;">
                            <?php echo htmlspecialchars($insignia['responsable_nombre'] ?? 'Victor Hugo Agaton Catalan'); ?>
                        </div>
                        <div style="font-size: 6px; color: #0066CC; margin-bottom: 0; text-align: center; font-weight: 600;">
                            <?php echo htmlspecialchars($insignia['cargo_responsable'] ?? 'RESPONSABLE DE EMISIÓN'); ?>
                        </div>
                        
                        <!-- Fecha y ubicación dentro del mismo bloque -->
                        <div style="font-size: 5px; color: #546e7a; margin-top: 6px; padding-top: 6px; border-top: 1px dashed #90caf9; text-align: center; line-height: 1.4;">
                            CIUDAD DE MÉXICO<br>
                            <?php echo date('F Y', strtotime($insignia['fecha_otorgamiento'])); ?>
                        </div>
                        
                        
                    </div>
                </div>
            </div>
            
            <div class="metadata-section" style="display: none !important;">
                <div class="metadata-title">Metadatos de la Insignia</div>
                
                <div class="metadata-item">
                    <div class="metadata-label">Código de identificación de la InsigniaTecNM:</div>
                    <div class="metadata-value"><?php echo htmlspecialchars($insignia['clave_insignia']); ?></div>
                </div>
                
                <div class="metadata-item">
                    <div class="metadata-label">Nombre de la InsigniaTecNM (Subcategoría):</div>
                    <div class="metadata-value"><?php echo htmlspecialchars($insignia['nombre_insignia']); ?></div>
                </div>
                
                <div class="metadata-item">
                    <div class="metadata-label">Categoría de la InsigniaTecNM:</div>
                    <div class="metadata-value"><?php echo htmlspecialchars($insignia['categoria']); ?></div>
                </div>
                
                <div class="metadata-item">
                    <div class="metadata-label">Destinatario:</div>
                    <div class="metadata-value"><?php echo htmlspecialchars($insignia['destinatario']); ?></div>
                </div>
                
                <div class="metadata-item">
                    <div class="metadata-label">Descripción:</div>
                    <div class="metadata-value long-text"><?php echo nl2br(htmlspecialchars($insignia['Descripcion'])); ?></div>
                </div>
                
                <div class="metadata-item">
                    <div class="metadata-label">Criterios para su emisión:</div>
                    <div class="metadata-value long-text"><?php echo nl2br(htmlspecialchars($insignia['Criterio'])); ?></div>
                </div>
                
                <div class="metadata-item">
                    <div class="metadata-label">Fecha de emisión:</div>
                    <div class="metadata-value"><?php echo formatearFecha($insignia['fecha_otorgamiento']); ?></div>
                </div>
                
                <div class="metadata-item">
                    <div class="metadata-label">Emisor (TecNM o Instituto/Centro):</div>
                    <div class="metadata-value"><?php echo htmlspecialchars($insignia['institucion']); ?></div>
                </div>
                
                <div class="metadata-item">
                    <div class="metadata-label">Programa:</div>
                    <div class="metadata-value"><?php echo htmlspecialchars($insignia['Programa']); ?></div>
                </div>
                
                <div class="metadata-item">
                    <div class="metadata-label">Período:</div>
                    <div class="metadata-value"><?php echo htmlspecialchars($insignia['periodo']); ?></div>
                </div>
                
                <div class="metadata-item">
                    <div class="metadata-label">Estado:</div>
                    <div class="metadata-value"><?php echo htmlspecialchars($insignia['estatus']); ?></div>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <button onclick="window.print()" class="btn">
                <i class="fas fa-print"></i> Imprimir Insignia
            </button>
            <a href="ver_validacion_admin.php?id=<?php echo $insignia['id']; ?>" class="btn" style="background: linear-gradient(135deg, #25D366, #128C7E);">
                <i class="fas fa-share"></i> Ver Validación
            </a>
            <a href="historial_insignias.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Volver al Historial
            </a>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

  <!-- FOOTER AZUL PROFESIONAL -->
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
