<?php
session_start();

// Incluir archivo de conexión (sin verificar autenticación para acceso público)
require_once 'conexion.php';

// Obtener el código de la insignia desde la URL
$codigo_insignia = isset($_GET['insignia']) ? $_GET['insignia'] : '';
$solo_certificado = isset($_GET['solo']) && $_GET['solo'] == '1';

if (empty($codigo_insignia)) {
    echo "Error: No se proporcionó código de insignia";
    exit();
}

try {
    // Verificar estructura dinámica de las tablas para JOINs correctos
    $check_destinatario_id = $conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
    $tiene_id_destinatario = ($check_destinatario_id && $check_destinatario_id->num_rows > 0);
    $campo_id_destinatario = $tiene_id_destinatario ? 'id' : 'ID_destinatario';
    
    $check_responsable_id = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'id'");
    $tiene_id_responsable = ($check_responsable_id && $check_responsable_id->num_rows > 0);
    $campo_id_responsable = $tiene_id_responsable ? 'id' : 'ID_responsable';
    
    // Verificar nombre de columna en tipo_insignia (puede ser Nombre_Insignia o Nombre_ins)
    $check_nombre_tipo = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Nombre_Insignia'");
    $tiene_nombre_insignia = ($check_nombre_tipo && $check_nombre_tipo->num_rows > 0);
    $campo_nombre_tipo = $tiene_nombre_insignia ? 'Nombre_Insignia' : 'Nombre_ins';
    
    // Consulta para obtener los datos de la insignia - CORREGIDA para obtener descripción de T_insignias
    // Primero obtener el tipo de insignia del código para hacer JOIN con T_insignias
    $query = "SELECT 
            io.ID_otorgada as id,
        io.Codigo_Insignia as codigo,
            CASE 
                WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                ELSE 'Insignia TecNM'
        END as nombre,
            CASE 
                WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                ELSE 'Formación Integral'
            END as categoria,
        d.Nombre_Completo as destinatario,
        COALESCE(ti.Descripcion, NULL) as descripcion,
        COALESCE(ti.Criterio, NULL) as criterios,
        'Certificación oficial' as evidencias,
        COALESCE(re.Nombre_Completo, 'Sistema TecNM') as responsable,
        COALESCE(re.Cargo, 'RESPONSABLE DE EMISIÓN') as cargo_responsable,
        io.Fecha_Emision as fecha_emision,
        'Tecnológico Nacional de México' as emisor,
            'Certificación oficial' as evidencia,
        COALESCE(ti.Archivo_Visual, 'insignia_default.png') as archivo_visual,
        COALESCE(re.Nombre_Completo, 'Administrador') as responsable_captura,
        'ADMIN001' as codigo_responsable,
        CONCAT('imagen/Insignias/', COALESCE(ti.Archivo_Visual, 'insignia_default.png')) as imagen_path,
            io.Responsable_Emision as responsable_id
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
    LIMIT 1";
    
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception("Error al preparar consulta: " . $conexion->error);
    }
    
        $stmt->bind_param("s", $codigo_insignia);
        $stmt->execute();
        $result = $stmt->get_result();
        
    if ($result->num_rows === 0) {
        echo "Error: No se encontró la insignia con el código proporcionado";
        exit();
    }
    
    $insignia_data = $result->fetch_assoc();
    $stmt->close();
    
    // Función para determinar la imagen de la insignia dinámicamente
    function determinarInsigniaDinamica($codigo_insignia, $nombre_insignia) {
        $mapeo_codigos = [
            'ART' => 'Embajador del Arte',
            'EMB' => 'Embajador del Deporte', 
            'TAL' => 'Talento Científico',
            'INN' => 'Talento Innovador',
            'SOC' => 'Responsabilidad Social',
            'FOR' => 'Formación y Actualización',
            'MOV' => 'Movilidad e Intercambio'
        ];
        
        $mapeo_imagenes = [
            'Movilidad e Intercambio' => 'MovilidadeIntercambio.png',
            'Embajador del Deporte' => 'EmbajadordelDeporte.png',
            'Embajador del Arte' => 'EmbajadordelArte.png',
            'Formación y Actualización' => 'FormacionyActualizacion.png',
            'Talento Científico' => 'TalentoCientifico.png',
            'Talento Innovador' => 'TalentoInnovador.png',
            'Responsabilidad Social' => 'ResponsabilidadSocial.png'
        ];
        
        foreach ($mapeo_codigos as $codigo => $tipo) {
            if (strpos($codigo_insignia, $codigo) !== false) {
                return $mapeo_imagenes[$tipo] ?? 'EmbajadordelArte.png';
            }
        }
        
        if (isset($mapeo_imagenes[$nombre_insignia])) {
            return $mapeo_imagenes[$nombre_insignia];
        }
        
        return 'EmbajadordelArte.png';
    }
    
    // Obtener firma digital del responsable si existe (separado para evitar errores si el campo no existe)
    $insignia_data['firma_digital_base64'] = null;
if (!empty($insignia_data['responsable_id'])) {
    try {
            // Verificar si el campo existe primero
            $check_field = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'firma_digital_base64'");
            if ($check_field && $check_field->num_rows > 0) {
                $sql_firma = "SELECT firma_digital_base64 FROM responsable_emision WHERE " . $campo_id_responsable . " = ? LIMIT 1";
        $stmt_firma = $conexion->prepare($sql_firma);
        if ($stmt_firma) {
            $stmt_firma->bind_param("i", $insignia_data['responsable_id']);
            $stmt_firma->execute();
            $resultado_firma = $stmt_firma->get_result();
            if ($resultado_firma && $resultado_firma->num_rows > 0) {
                        $fila_firma = $resultado_firma->fetch_assoc();
                        $insignia_data['firma_digital_base64'] = $fila_firma['firma_digital_base64'] ?? null;
            }
            $stmt_firma->close();
                }
        }
    } catch (Exception $e) {
        // Si hay error, simplemente no se mostrará la firma digital
        error_log("Error al obtener firma digital: " . $e->getMessage());
    }
    }
    
    // Obtener descripción y criterios: primero de la consulta SQL, luego de sesión, luego valores por defecto
    if (empty($insignia_data['descripcion']) || $insignia_data['descripcion'] === null) {
        // Si no hay descripción en la consulta SQL, intentar obtenerla de la sesión
        if (isset($_SESSION['insignia_data']) && is_array($_SESSION['insignia_data'])) {
            $sid = $_SESSION['insignia_data'];
            if (!empty($sid['codigo']) && $sid['codigo'] === $codigo_insignia && !empty($sid['descripcion'])) {
                $insignia_data['descripcion'] = $sid['descripcion'];
            }
        }
        // Si aún no hay descripción, usar valor por defecto
        if (empty($insignia_data['descripcion']) || $insignia_data['descripcion'] === null) {
            $insignia_data['descripcion'] = 'Este reconocimiento se otorga por su destacada participación y compromiso con los valores del Tecnológico Nacional de México.';
        }
    }
    
    if (empty($insignia_data['criterios']) || $insignia_data['criterios'] === null) {
        // Si no hay criterios en la consulta SQL, intentar obtenerlos de la sesión
        if (isset($_SESSION['insignia_data']) && is_array($_SESSION['insignia_data'])) {
            $sid = $_SESSION['insignia_data'];
            if (!empty($sid['codigo']) && $sid['codigo'] === $codigo_insignia && !empty($sid['criterios'])) {
                $insignia_data['criterios'] = $sid['criterios'];
            }
        }
        // Si aún no hay criterios, usar valor por defecto
        if (empty($insignia_data['criterios']) || $insignia_data['criterios'] === null) {
            $insignia_data['criterios'] = 'Cumplimiento de los criterios establecidos para esta insignia.';
        }
    }

    // Determinar la imagen dinámicamente
    $archivo_imagen = determinarInsigniaDinamica($codigo_insignia, $insignia_data['nombre']);
    $insignia_data['imagen_path'] = 'imagen/Insignias/' . $archivo_imagen;
    
    // Inicializar hash de verificación (puede usarse de firmas_digitales si existe esa tabla)
    $hash_verificacion = null;
    
    // Generar URL de validación y código QR
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (empty($host) || $host === '::1') {
        $host = 'localhost';
    }
    
    // Obtener el directorio base del script para construir URLs correctas
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    $base_path = '';
    if ($script_dir !== '/' && $script_dir !== '.' && $script_dir !== '\\') {
        $base_path = trim($script_dir, '/\\');
    }
    
    // Construir base_url con el path del proyecto si existe
    if (!empty($base_path)) {
        $base_url = $protocol . '://' . $host . '/' . $base_path;
    } else {
        $base_url = $protocol . '://' . $host;
    }
    
    // Construir URL completa de la página actual para Open Graph
    $url_pagina_actual = $protocol . '://' . $host . $_SERVER['REQUEST_URI'];
    
    // Construir URL absoluta de la imagen de la insignia para Open Graph
    // Asegurarse de que la ruta de la imagen sea relativa desde la raíz del proyecto
    $imagen_path_clean = ltrim($insignia_data['imagen_path'], '/');
    if (!empty($base_path)) {
        $url_imagen_insignia = $protocol . '://' . $host . '/' . $base_path . '/' . $imagen_path_clean;
    } else {
        $url_imagen_insignia = $protocol . '://' . $host . '/' . $imagen_path_clean;
    }
    
    // Limpiar barras dobles en la URL
    $url_imagen_insignia = str_replace('//', '/', $url_imagen_insignia);
    $url_imagen_insignia = str_replace('http:/', 'http://', $url_imagen_insignia);
    $url_imagen_insignia = str_replace('https:/', 'https://', $url_imagen_insignia);
    
    // Verificar que la imagen existe, si no, usar una por defecto
    $ruta_imagen_fisica = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($insignia_data['imagen_path'], '/');
    if (!empty($base_path)) {
        $ruta_imagen_fisica = $_SERVER['DOCUMENT_ROOT'] . '/' . $base_path . '/' . ltrim($insignia_data['imagen_path'], '/');
    }
    
    // Si la imagen no existe físicamente, usar una imagen por defecto
    if (!file_exists($ruta_imagen_fisica)) {
        // Intentar con imagen por defecto
        $imagen_default = 'imagen/Insignias/EmbajadordelArte.png';
        $imagen_path_clean = ltrim($imagen_default, '/');
        if (!empty($base_path)) {
            $url_imagen_insignia = $protocol . '://' . $host . '/' . $base_path . '/' . $imagen_path_clean;
        } else {
            $url_imagen_insignia = $protocol . '://' . $host . '/' . $imagen_path_clean;
        }
        $url_imagen_insignia = str_replace('//', '/', $url_imagen_insignia);
        $url_imagen_insignia = str_replace('http:/', 'http://', $url_imagen_insignia);
        $url_imagen_insignia = str_replace('https:/', 'https://', $url_imagen_insignia);
    }
    
    $url_validacion = $base_url . "/validacion.php?insignia=" . urlencode($codigo_insignia);
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($url_validacion);
    
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
    
} catch (Exception $e) {
    echo "Error al obtener los datos de la insignia: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?> - Público</title>
    
    <!-- DEBUG: URLs generadas para compartir en Facebook -->
    <!-- base_url: <?php echo htmlspecialchars($base_url); ?> -->
    <!-- imagen_path: <?php echo htmlspecialchars($insignia_data['imagen_path']); ?> -->
    <!-- url_imagen_insignia: <?php echo htmlspecialchars($url_imagen_insignia); ?> -->
    <!-- url_pagina_actual: <?php echo htmlspecialchars($url_pagina_actual); ?> -->
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($url_pagina_actual); ?>">
    <meta property="og:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta property="og:description" content="Insignia otorgada a <?php echo htmlspecialchars($insignia_data['destinatario'] ?? 'estudiante'); ?> - <?php echo htmlspecialchars($insignia_data['descripcion'] ?? 'Reconocimiento del Tecnológico Nacional de México'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($url_imagen_insignia); ?>">
    <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($url_imagen_insignia); ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta property="og:site_name" content="Sistema de Insignias TecNM">
    <meta property="og:locale" content="es_MX">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo htmlspecialchars($url_pagina_actual); ?>">
    <meta name="twitter:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta name="twitter:description" content="Insignia otorgada a <?php echo htmlspecialchars($insignia_data['destinatario'] ?? 'estudiante'); ?> - <?php echo htmlspecialchars($insignia_data['descripcion'] ?? 'Reconocimiento del Tecnológico Nacional de México'); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($url_imagen_insignia); ?>">
    
    <!-- Meta tags adicionales -->
    <meta name="description" content="Insignia otorgada a <?php echo htmlspecialchars($insignia_data['destinatario'] ?? 'estudiante'); ?> - <?php echo htmlspecialchars($insignia_data['descripcion'] ?? 'Reconocimiento del Tecnológico Nacional de México'); ?>">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Librerías para descargar certificado como imagen y PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        <?php if ($solo_certificado): ?>
        /* Ocultar metadatos e imagen de insignia cuando solo se muestra el certificado */
        /* PERO mantener los botones de acciones visibles para poder descargar */
        .metadata-section,
        .insignia-hexagon {
            display: none !important;
        }
        .insignia-section {
            grid-template-columns: 1fr !important;
        }
        .insignia-preview {
            width: 100%;
        }
        /* Asegurar que los botones de acciones se muestren siempre */
        .actions {
            display: block !important;
        }
        <?php endif; ?>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        body {
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
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
            width: 100%;
            left: 0;
            right: 0;
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
            left: -240px;
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
        
        .content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .insignia-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            border: 2px solid #1b396a;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        
        .insignia-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .insignia-hexagon {
            width: 200px;
            height: 200px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin: 0 auto 30px;
            border: 2px solid #1b396a;
            border-radius: 8px;
        }
        
        .document-preview {
            position: relative;
            width: 100%;
            max-width: 6in;
            height: auto;
            min-height: 750px;
            aspect-ratio: 8.5 / 11;
            background: white;
            border: 2px solid #1b396a;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            background-image: url('imagen/Hoja_membrentada.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0 auto;
        }
        
        .document-insignia {
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .metadata-section {
            margin-top: 30px;
        }
        
        .metadata-section h2 {
            color: #1b396a;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #1b396a;
            padding-bottom: 10px;
        }
        
        .metadata-item {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #1b396a;
            border-radius: 4px;
        }
        
        .metadata-item strong {
            color: #1b396a;
            display: block;
            margin-bottom: 5px;
        }
        
        .metadata-item span {
            color: #333;
            line-height: 1.5;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .btn {
            background-color: #1b396a;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin: 8px;
            transition: all 0.3s;
            display: inline-block;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .btn:hover {
            background-color: #0f2a4a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        footer {
            background: #1e3c72;
            color: white;
            padding: 40px 0;
            margin-top: 50px;
            text-align: center;
            width: 100%;
            left: 0;
            right: 0;
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
        
        footer p {
            margin: 5px 0;
        }
        
        @media (max-width: 768px) {
            .insignia-section {
                grid-template-columns: 1fr;
            }
            
            .insignia-preview {
                flex-direction: column;
                text-align: center;
            }
            
            .insignia-hexagon {
                margin-right: 0;
                margin-bottom: 20px;
            }
            
            .document-preview {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER PROFESIONAL -->
    <header class="header">
        <div class="header-content">
            <img src="imagen/logo.png" alt="TecNM Logo" class="header-logo" onerror="this.style.display='none';">
            <div style="text-align: center;">
                <h1>🔍 Verificación Pública de Insignia TecNM</h1>
                <p style="font-size: 14px; opacity: 0.9; margin-top: 8px;">Sistema de Validación Oficial</p>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="content">
            <div class="insignia-section">
                <div class="insignia-preview">
                    <div class="insignia-hexagon" style="background-image: url(<?php echo $insignia_data['imagen_path']; ?>);">
                        <!-- La imagen se carga directamente desde PHP -->
        </div>
        
                    <div class="document-preview">
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
                        <?php echo htmlspecialchars($insignia_data['destinatario']); ?>
                    </div>
                    
                    <!-- Texto descriptivo (ajustable automáticamente según longitud) -->
                    <?php 
                    $descripcion = htmlspecialchars($insignia_data['descripcion']);
                    $descripcion_length = strlen($descripcion);
                    // Ajustar tamaño de fuente automáticamente según longitud del texto (más agresivo para textos largos)
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
                            <img src="<?php echo htmlspecialchars($insignia_data['imagen_path']); ?>" alt="<?php echo htmlspecialchars($insignia_data['nombre']); ?>" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 25px; height: 25px; background: white; border-radius: 4px; padding: 2px; border: 1px solid #1b396a; object-fit: contain;">
                        </div>
                        </div>
                        
                    <!-- Firma en la esquina inferior derecha -->
                    <div style="position: absolute; bottom: 25px; right: 60px; text-align: left; font-size: 9px; color: #333; max-width: 300px;">
                        <?php if (!empty($insignia_data['firma_digital_base64'])): ?>
                        <!-- Mostrar solo el SELLO DIGITAL REAL del SAT completo (tamaño más grande) -->
                        <div style="font-size: 6px; font-family: 'Courier New', monospace; color: #333; word-break: break-all; line-height: 1.2; margin-bottom: 6px; letter-spacing: -0.1px;">
                            &lt;sello&gt;<?php echo htmlspecialchars($insignia_data['firma_digital_base64']); ?>&lt;/sello&gt;
                            </div>
                            <?php endif; ?>
                        <div style="font-weight: bold; color: #1b396a; margin-top: 4px; font-size: 11px;"><?php echo htmlspecialchars($insignia_data['responsable']); ?></div>
                        <div style="font-size: 8px; color: #666; margin-top: 2px;"><?php echo htmlspecialchars($insignia_data['cargo_responsable'] ?? 'RESPONSABLE DE EMISIÓN'); ?></div>
                    </div>
                    
                    <!-- Fecha y ubicación -->
                    <div style="position: absolute; bottom: 10px; right: 60px; font-size: 7px; color: #666; text-align: right; background: rgba(255,255,255,0.9); padding: 4px; border-radius: 2px; width: 80px;">
                        CIUDAD DE MÉXICO<br>
                        <?php echo formatearFechaEspanol($insignia_data['fecha_emision']); ?>
                    </div>
                        </div>
                    </div>
                    
                <div class="metadata-section">
                    <h2>Metadatos de la Insignia</h2>
                
                <div class="metadata-item">
                    <strong>Código de identificación de la InsigniaTecNM:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['codigo']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Nombre de la InsigniaTecNM (Subcategoría):</strong>
                    <span><?php echo htmlspecialchars($insignia_data['nombre']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Categoría de la InsigniaTecNM:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['categoria']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Destinatario:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['destinatario']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Fecha de emisión:</strong>
                    <span><?php echo date('d-m-Y', strtotime($insignia_data['fecha_emision'])); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Emisor (TecNM o Instituto/Centro):</strong>
                    <span><?php echo htmlspecialchars($insignia_data['emisor']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Descripción:</strong>
                    <span><?php echo nl2br(htmlspecialchars($insignia_data['descripcion'])); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Criterios para su emisión:</strong>
                    <span><?php echo nl2br(htmlspecialchars($insignia_data['criterios'])); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Evidencia:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['evidencia'] ?: 'Sin evidencia registrada'); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Archivo Visual de la InsigniaTecNM:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['archivo_visual']); ?> (archivo)</span>
                </div>
                
                <div class="metadata-item">
                    <strong>Responsable de la captura de los Metadatos:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['responsable']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Código de identificación del Responsable de la captura de los Metadatos:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['codigo_responsable']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <button onclick="descargarCertificado('imagen')" class="btn" id="btn-descargar-imagen" style="background-color: #28a745;">
                <i class="fas fa-download"></i> Descargar como Imagen
            </button>
            <button onclick="descargarCertificado('pdf')" class="btn" id="btn-descargar-pdf" style="background-color: #dc3545;">
                <i class="fas fa-file-pdf"></i> Descargar como PDF
            </button>
            <button onclick="window.print()" class="btn">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="ver_validacion_publica.php?insignia=<?php echo isset($codigo_insignia) ? urlencode($codigo_insignia) : ''; ?>" class="btn" style="text-decoration: none; display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);" target="_blank">
                <i class="fas fa-check-circle"></i> Ver Validación
            </a>
            <a href="consulta_publica.php" class="btn" style="text-decoration: none; display: inline-block;">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    
    <!-- FOOTER PROFESIONAL -->
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
    
    <script>
        // Función para descargar el certificado como imagen o PDF
        async function descargarCertificado(formato) {
            const certificado = document.querySelector('.document-preview');
            const nombreArchivo = 'Certificado_<?php echo htmlspecialchars(str_replace(' ', '_', $insignia_data['destinatario'])); ?>_<?php echo htmlspecialchars($insignia_data['codigo']); ?>';
            
            // Deshabilitar botones mientras se procesa
            const btnImagen = document.getElementById('btn-descargar-imagen');
            const btnPdf = document.getElementById('btn-descargar-pdf');
            const botones = [btnImagen, btnPdf];
            
            botones.forEach(btn => {
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
                }
            });
            
            try {
                if (formato === 'imagen') {
                    // Descargar como imagen PNG
                    await descargarComoImagen(certificado, nombreArchivo);
                } else if (formato === 'pdf') {
                    // Descargar como PDF
                    await descargarComoPDF(certificado, nombreArchivo);
                }
            } catch (error) {
                console.error('Error al generar certificado:', error);
                alert('Error al generar el certificado. Por favor, intenta nuevamente.');
            } finally {
                // Rehabilitar botones
                botones.forEach(btn => {
                    if (btn) {
                        btn.disabled = false;
                        if (btn === btnImagen) {
                            btn.innerHTML = '<i class="fas fa-download"></i> Descargar como Imagen';
                        } else if (btn === btnPdf) {
                            btn.innerHTML = '<i class="fas fa-file-pdf"></i> Descargar como PDF';
                        }
                    }
                });
            }
        }
        
        // Función para descargar como imagen PNG
        async function descargarComoImagen(elemento, nombreArchivo) {
            // Esperar a que las imágenes se carguen
            await esperarImagenes();
            
            const canvas = await html2canvas(elemento, {
                scale: 2, // Mayor resolución
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff',
                width: elemento.offsetWidth,
                height: elemento.offsetHeight,
                windowWidth: elemento.scrollWidth,
                windowHeight: elemento.scrollHeight
            });
            
            // Convertir canvas a blob y descargar
            canvas.toBlob(function(blob) {
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = nombreArchivo + '.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            }, 'image/png');
        }
        
        // Función para descargar como PDF
        async function descargarComoPDF(elemento, nombreArchivo) {
            // Esperar a que las imágenes se carguen
            await esperarImagenes();
            
            const canvas = await html2canvas(elemento, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff',
                width: elemento.offsetWidth,
                height: elemento.offsetHeight
            });
            
            const imgData = canvas.toDataURL('image/png');
            
            // Calcular dimensiones del PDF (mantener proporción)
            const imgWidth = 210; // A4 width in mm
            const pageHeight = 297; // A4 height in mm
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            let heightLeft = imgHeight;
            
            const pdf = new window.jspdf.jsPDF('p', 'mm', 'a4');
            let position = 0;
            
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
            
            while (heightLeft >= 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }
            
            pdf.save(nombreArchivo + '.pdf');
        }
        
        // Función para esperar a que todas las imágenes se carguen
        function esperarImagenes() {
            return new Promise((resolve) => {
                const imagenes = document.querySelectorAll('.document-preview img');
                let cargadas = 0;
                const total = imagenes.length;
                
                if (total === 0) {
                    resolve();
                    return;
                }
                
                imagenes.forEach(img => {
                    if (img.complete) {
                        cargadas++;
                        if (cargadas === total) resolve();
                    } else {
                        img.onload = img.onerror = () => {
                            cargadas++;
                            if (cargadas === total) resolve();
                        };
                    }
                });
                
                // Timeout de seguridad (5 segundos)
                setTimeout(resolve, 5000);
            });
        }
    </script>
</body>
</html>
