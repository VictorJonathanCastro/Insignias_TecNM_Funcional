<?php
// Página que genera una imagen clickeable para compartir
session_start();

// Obtener código de insignia desde GET o sesión
$codigo_insignia = $_GET['codigo'] ?? '';

if (!empty($codigo_insignia)) {
    // Modo público: obtener datos de la base de datos
    require_once 'conexion.php';
    
    try {
        // Detectar formato del código para saber qué tabla usar
        $codigo_tiene_formato_tecnm = (strpos($codigo_insignia, 'TECNM-') === 0);
        
        // Verificar qué tablas existen
        $tabla_existe_t = $conexion->query("SHOW TABLES LIKE 'T_insignias_otorgadas'");
        $tabla_existe_io = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
        $usar_tabla_t = ($tabla_existe_t && $tabla_existe_t->num_rows > 0);
        $usar_tabla_io = ($tabla_existe_io && $tabla_existe_io->num_rows > 0);
        
        // Detectar estructura dinámica de las tablas
        $check_destinatario_id = $conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
        $tiene_id_destinatario = ($check_destinatario_id && $check_destinatario_id->num_rows > 0);
        $campo_id_destinatario = $tiene_id_destinatario ? 'id' : 'ID_destinatario';
        
        $check_responsable_id = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'id'");
        $tiene_id_responsable = ($check_responsable_id && $check_responsable_id->num_rows > 0);
        $campo_id_responsable = $tiene_id_responsable ? 'id' : 'ID_responsable';
        
        // Detectar estructura dinámica de tipo_insignia y cat_insignias
        $check_tipo = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'id'");
        $tiene_id_tipo = ($check_tipo && $check_tipo->num_rows > 0);
        $campo_id_tipo = $tiene_id_tipo ? 'id' : 'ID_tipo';
        
        $check_nombre_tipo = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Nombre_Insignia'");
        $tiene_nombre_insignia = ($check_nombre_tipo && $check_nombre_tipo->num_rows > 0);
        $campo_nombre_tipo = $tiene_nombre_insignia ? 'Nombre_Insignia' : 'Nombre_ins';
        
        $check_cat_ins = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Cat_ins'");
        $tiene_cat_ins = ($check_cat_ins && $check_cat_ins->num_rows > 0);
        
        $check_cat = $conexion->query("SHOW COLUMNS FROM cat_insignias LIKE 'id'");
        $tiene_id_cat = ($check_cat && $check_cat->num_rows > 0);
        $campo_id_cat = $tiene_id_cat ? 'id' : 'ID_cat';
        
        $row = null;
        $stmt = null;
        
        // Intentar primero en insigniasotorgadas si el código tiene formato TECNM- o si no tiene formato ID-Periodo
        if ($codigo_tiene_formato_tecnm || (!$codigo_tiene_formato_tecnm && $usar_tabla_io && !$usar_tabla_t)) {
            if ($usar_tabla_io) {
                $sql = "
                    SELECT 
                        io.Codigo_Insignia as codigo_insignia,
                        d.Nombre_Completo as destinatario,
                        io.Fecha_Emision as fecha_emision,
                        CASE 
                            WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                            WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                            WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                            WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                            WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%CIE%' THEN 'Talento Científico'
                            WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                            WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                            ELSE 'Insignia TecNM'
                        END as nombre_insignia,
                        COALESCE(cat.Nombre_cat, 'Formación Integral') as categoria,
                        re.Nombre_Completo as responsable_nombre,
                        re.Cargo as responsable_cargo,
                        'Instituto Tecnológico de San Marcos' as institucion
                    FROM insigniasotorgadas io
                    LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
                    LEFT JOIN responsable_emision re ON io.Responsable_Emision = re." . $campo_id_responsable . "
                    LEFT JOIN tipo_insignia ti ON (
                        (io.Codigo_Insignia LIKE '%MOV%' AND (ti." . $campo_nombre_tipo . " LIKE '%Movilidad%' OR ti." . $campo_nombre_tipo . " LIKE '%Intercambio%'))
                        OR (io.Codigo_Insignia LIKE '%EMB%' AND (ti." . $campo_nombre_tipo . " LIKE '%Deporte%' OR ti." . $campo_nombre_tipo . " LIKE '%Embajador%'))
                        OR (io.Codigo_Insignia LIKE '%ART%' AND (ti." . $campo_nombre_tipo . " LIKE '%Arte%' OR ti." . $campo_nombre_tipo . " LIKE '%Embajador%'))
                        OR (io.Codigo_Insignia LIKE '%FOR%' AND (ti." . $campo_nombre_tipo . " LIKE '%Formación%' OR ti." . $campo_nombre_tipo . " LIKE '%Actualización%'))
                        OR (io.Codigo_Insignia LIKE '%TAL%' AND ti." . $campo_nombre_tipo . " LIKE '%Científico%')
                        OR (io.Codigo_Insignia LIKE '%CIE%' AND ti." . $campo_nombre_tipo . " LIKE '%Científico%')
                        OR (io.Codigo_Insignia LIKE '%INN%' AND (ti." . $campo_nombre_tipo . " LIKE '%Innovador%' OR ti." . $campo_nombre_tipo . " LIKE '%Innovación%'))
                        OR (io.Codigo_Insignia LIKE '%SOC%' AND (ti." . $campo_nombre_tipo . " LIKE '%Social%' OR ti." . $campo_nombre_tipo . " LIKE '%Responsabilidad%'))
                    )
                    " . ($tiene_cat_ins ? "LEFT JOIN cat_insignias cat ON ti.Cat_ins = cat." . $campo_id_cat : "") . "
                    WHERE io.Codigo_Insignia = ?
                ";
                
                $stmt = $conexion->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Error al preparar consulta insigniasotorgadas: " . $conexion->error);
                }
                
                $stmt->bind_param("s", $codigo_insignia);
                if (!$stmt->execute()) {
                    throw new Exception("Error al ejecutar consulta insigniasotorgadas: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();
            }
        }
        
        // Si no se encontró y el código tiene formato ID-Periodo, buscar en T_insignias_otorgadas
        if (!$row && $usar_tabla_t && !$codigo_tiene_formato_tecnm) {
            $sql = "
                SELECT 
                    CONCAT(ti.id, '-', pe.Nombre_Periodo) as codigo_insignia,
                    d.Nombre_Completo as destinatario,
                    tio.Fecha_Emision as fecha_emision,
                    COALESCE(tin.Nombre_Insignia, 'Insignia TecNM') as nombre_insignia,
                    CASE 
                        WHEN tin.Nombre_Insignia LIKE '%Deporte%' OR tin.Nombre_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                        WHEN tin.Nombre_Insignia LIKE '%Científico%' OR tin.Nombre_Insignia LIKE '%Innovación%' OR tin.Nombre_Insignia LIKE '%Formación%' THEN 'Desarrollo Académico'
                        WHEN tin.Nombre_Insignia LIKE '%Arte%' OR tin.Nombre_Insignia LIKE '%Social%' OR tin.Nombre_Insignia LIKE '%Movilidad%' THEN 'Formación Integral'
                        ELSE 'Formación Integral'
                    END as categoria,
                    COALESCE(re.Nombre_Completo, 'Sistema TecNM') as responsable_nombre,
                    COALESCE(re.Cargo, 'RESPONSABLE DE EMISIÓN') as responsable_cargo,
                    COALESCE(itc.Nombre_itc, 'Instituto Tecnológico de San Marcos') as institucion
                FROM T_insignias_otorgadas tio
                LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
                LEFT JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
                LEFT JOIN destinatario d ON tio.Id_Destinatario = d." . $campo_id_destinatario . "
                LEFT JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
                LEFT JOIN it_centros itc ON ti.Propone_Insignia = itc.id
                LEFT JOIN responsable_emision re ON itc.id = re.Adscripcion
                WHERE CONCAT(ti.id, '-', pe.Nombre_Periodo) = ?
            ";
            
            $stmt = $conexion->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Error al preparar consulta T_insignias_otorgadas: " . $conexion->error);
            }
            
            $stmt->bind_param("s", $codigo_insignia);
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar consulta T_insignias_otorgadas: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
        }
        
        if (!$row) {
            // Si no se encontró la insignia, mostrar error
            http_response_code(404);
            die('Insignia no encontrada con el código: ' . htmlspecialchars($codigo_insignia));
        }
        
        // Usar los datos dinámicos obtenidos de la consulta
        $codigo_insignia = $row['codigo_insignia'];
        $nombre_insignia = $row['nombre_insignia'];
        $categoria = $row['categoria'];
        
        // Mapeo directo de nombres de insignias a archivos PNG
        $mapeo_imagenes = [
            'Movilidad e Intercambio' => 'MovilidadeIntercambio.png',
            'Embajador del Deporte' => 'EmbajadordelDeporte.png',
            'Embajador del Arte' => 'EmbajadordelArte.png',
            'Formación y Actualización' => 'FormacionyActualizacion.png',
            'Talento Científico' => 'TalentoCientifico.png',
            'Talento Innovador' => 'TalentoInnovador.png',
            'Responsabilidad Social' => 'ResponsabilidadSocial.png'
        ];
        
        $imagen_path = 'imagen/Insignias/ResponsabilidadSocial.png'; // Por defecto
        if (isset($mapeo_imagenes[$nombre_insignia])) {
            $imagen_path = 'imagen/Insignias/' . $mapeo_imagenes[$nombre_insignia];
        }
        
        $insignia_data = [
            'codigo' => $row['codigo_insignia'],
            'nombre' => $row['nombre_insignia'],
            'categoria' => $row['categoria'],
            'destinatario' => $row['destinatario'] ?? 'Estudiante',
            'descripcion' => $row['descripcion'] ?? "Esta insignia reconoce la participación destacada en actividades de " . $row['nombre_insignia'] . " por parte del estudiante.",
            'criterios' => "Para obtener esta insignia de " . $row['nombre_insignia'] . ", el estudiante debe haber demostrado competencias específicas.",
            'fecha_emision' => $row['fecha_emision'] ?? date('Y-m-d'),
            'emisor' => 'TecNM / ' . ($row['institucion'] ?? 'Instituto Tecnológico'),
            'evidencia' => $row['evidencia'] ?? 'Sin evidencia registrada',
            'archivo_visual' => "Insig_" . $row['codigo_insignia'] . ".jpg",
            'imagen_path' => $imagen_path,
            'responsable' => $row['responsable_nombre'] ?? 'Sistema TecNM',
            'codigo_responsable' => 'TecNM-ITSM-2025-Resp001',
            'estatus' => 'Activo',
            'periodo' => '2025'
        ];
    } catch (Exception $e) {
        error_log("Error en imagen_clickeable.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        http_response_code(500);
        die('Error al procesar la solicitud. Por favor, intente más tarde.');
    }
} else {
    // Modo con sesión: verificar sesión y datos de insignia
    if (!isset($_SESSION['insignia_data'])) {
        header('Location: metadatos_formulario.php');
        exit();
    }
    
    $insignia_data = $_SESSION['insignia_data'];
    $codigo_insignia = $insignia_data['codigo'];
}

// Detectar si es un crawler de redes sociales (Facebook, Twitter, etc.)
// Los crawlers necesitan leer los meta tags, así que NO debemos redirigir
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
$is_crawler = strpos($user_agent, 'facebookexternalhit') !== false ||  // Facebook crawler (principal)
              strpos($user_agent, 'facebot') !== false ||                // Facebook bot
              strpos($user_agent, 'facebook') !== false ||               // Cualquier Facebook bot
              strpos($user_agent, 'twitterbot') !== false ||             // Twitter crawler
              strpos($user_agent, 'linkedinbot') !== false ||            // LinkedIn crawler
              strpos($user_agent, 'whatsapp') !== false ||                // WhatsApp crawler
              strpos($user_agent, 'slackbot') !== false ||                // Slack crawler
              strpos($user_agent, 'discordbot') !== false ||              // Discord crawler
              strpos($user_agent, 'redditbot') !== false ||               // Reddit crawler
              strpos($user_agent, 'googlebot') !== false ||               // Google crawler
              strpos($user_agent, 'bingbot') !== false ||                 // Bing crawler
              strpos($user_agent, 'crawler') !== false ||                 // Cualquier crawler genérico
              (strpos($user_agent, 'bot') !== false && strpos($user_agent, 'mozilla') === false); // Bot genérico (pero no navegadores)

// Redirigir automáticamente al certificado completo si viene de Facebook u otra red social
// PERO SOLO si NO es un crawler (los crawlers necesitan leer los meta tags)
if (isset($_GET['codigo']) && !isset($_GET['stay']) && !$is_crawler) {
    $referer = isset($_SERVER['HTTP_REFERER']) ? strtolower($_SERVER['HTTP_REFERER']) : '';
    $isFromSocialMedia = strpos($referer, 'facebook.com') !== false || 
                        strpos($referer, 'twitter.com') !== false || 
                        strpos($referer, 'whatsapp') !== false ||
                        strpos($referer, 'linkedin.com') !== false ||
                        strpos($referer, 'reddit.com') !== false ||
                        isset($_GET['from_share']);
    
    if ($isFromSocialMedia) {
        // Redirigir inmediatamente al certificado completo (solo para usuarios reales, no crawlers)
        $codigo_redirect = urlencode($_GET['codigo']);
        header("Location: ver_insignia_completa.php?codigo=$codigo_redirect");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?></title>
    
    <!-- Meta tags para redes sociales -->
    <?php
    // Generar URLs públicas para Facebook - forma simple que funciona como localmente
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Construir URL base de forma simple (como funcionaba localmente)
    // Usar REQUEST_URI para obtener la ruta completa y construir desde ahí
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    
    // Obtener el directorio base del script
    $script_dir = dirname($script_name);
    // Normalizar: si está en raíz, será '/' o '.'
    if ($script_dir === '/' || $script_dir === '.' || $script_dir === '\\') {
        $base_path = '';
    } else {
        $base_path = trim($script_dir, '/\\');
    }
    
    // Construir base_url
    if (!empty($base_path)) {
        $base_url = $protocol . '://' . $host . '/' . $base_path;
    } else {
        $base_url = $protocol . '://' . $host;
    }
    
    // Si es localhost o IP local, usar configuración especial (como funcionaba localmente)
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, '192.168.') !== false) {
        if (isset($_SESSION['ngrok_url']) && !empty($_SESSION['ngrok_url'])) {
            $base_url = rtrim($_SESSION['ngrok_url'], '/');
            if (!empty($base_path)) {
                $base_url .= '/' . $base_path;
            }
        } else {
            // Fallback a localtunnel (como funcionaba localmente)
            $base_url = 'https://bad-elephant-84.loca.lt';
            if (!empty($base_path)) {
                $base_url .= '/' . $base_path;
            }
        }
    }
    
    // URL de la imagen de la insignia para compartir en Facebook
    $image_path = isset($insignia_data['imagen_path']) ? $insignia_data['imagen_path'] : 'imagen/Insignias/ResponsabilidadSocial.png';
    $image_path = ltrim($image_path, '/');
    
    // Asegurar que la URL de la imagen sea absoluta y accesible
    // Construir URL completa de la imagen
    $image_url = $base_url . '/' . $image_path;
    
    // Asegurar que la URL de la imagen no tenga barras dobles
    $image_url = str_replace('//', '/', $image_url);
    $image_url = str_replace('http:/', 'http://', $image_url);
    $image_url = str_replace('https:/', 'https://', $image_url);
    
    // URL para compartir en Facebook - debe apuntar a imagen_clickeable.php (esta página)
    // para que Facebook lea los meta tags correctos
    $share_url = $base_url . '/imagen_clickeable.php?codigo=' . urlencode($codigo_insignia);
    
    // URL del certificado completo (para el QR code también)
    $certificado_url = $base_url . '/ver_insignia_completa.php?codigo=' . urlencode($codigo_insignia);
    
    // Título dinámico según la insignia
    $og_title = "Insignia TecNM - " . htmlspecialchars($insignia_data['nombre']);
    $og_description = "Insignia de " . htmlspecialchars($insignia_data['nombre']) . " otorgada a " . htmlspecialchars($insignia_data['destinatario']) . ". Haz clic para ver el certificado completo.";
    
    // Debug: Log de las URLs generadas
    error_log("DEBUG imagen_clickeable.php - host: $host");
    error_log("DEBUG imagen_clickeable.php - base_path: $base_path");
    error_log("DEBUG imagen_clickeable.php - base_url: $base_url");
    error_log("DEBUG imagen_clickeable.php - image_path: $image_path");
    error_log("DEBUG imagen_clickeable.php - image_url: $image_url");
    error_log("DEBUG imagen_clickeable.php - share_url: $share_url");
    error_log("DEBUG imagen_clickeable.php - og_title: $og_title");
    ?>
    
    <!-- DEBUG: URLs generadas para compartir -->
    <!-- base_url: <?php echo htmlspecialchars($base_url); ?> -->
    <!-- image_path: <?php echo htmlspecialchars($image_path); ?> -->
    <!-- image_url: <?php echo htmlspecialchars($image_url); ?> -->
    <!-- share_url: <?php echo htmlspecialchars($share_url); ?> -->
    
    <!-- Meta tags para Facebook - TÍTULO DINÁMICO según la insignia -->
    <!-- IMPORTANTE: Estos meta tags deben estar ANTES de cualquier contenido para que Facebook los lea correctamente -->
    <meta property="og:title" content="<?php echo $og_title; ?>">
    <meta property="og:description" content="<?php echo $og_description; ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($image_url); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($share_url); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Insignias TecNM">
    <meta property="og:locale" content="es_MX">
    <meta property="og:image:width" content="500">
    <meta property="og:image:height" content="500">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($image_url); ?>">
    <meta property="og:image:alt" content="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    
    <!-- Meta tags adicionales para mejor compatibilidad -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $og_title; ?>">
    <meta name="twitter:description" content="<?php echo $og_description; ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($image_url); ?>">
    
    <!-- Meta tags adicionales para asegurar que Facebook muestre el botón Publicar -->
    <meta name="description" content="<?php echo $og_description; ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($share_url); ?>">
    
    <!-- Evitar que Facebook detecte redirects o problemas -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="index, follow">
    
    <!-- Font Awesome para logos oficiales de redes sociales -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f0f0f0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .insignia-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            position: relative;
        }
        
        .insignia-image {
            width: 350px;
            height: 350px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin: 0 auto 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .insignia-image:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        }
        
        .insignia-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.1);
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 15px;
        }
        
        .insignia-image:hover::after {
            opacity: 1;
        }
        
        .click-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 16px;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            z-index: 10;
        }
        
        .insignia-image:hover .click-indicator {
            opacity: 1;
        }
        
        .share-section {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
        }
        
        .qr-section {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .qr-section h3 {
            color: #1b396a;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .qr-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #1b396a;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .qr-container p {
            margin-top: 10px;
            color: #666;
            font-size: 12px;
        }
        
        #qrcode {
            display: block;
            margin: 0 auto;
            width: 200px;
            height: 200px;
        }
        
        .share-title {
            font-size: 20px;
            font-weight: bold;
            color: #155724;
            margin-bottom: 15px;
        }
        
        .share-subtitle {
            font-size: 14px;
            color: #155724;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .share-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .share-btn {
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 150px;
            justify-content: center;
        }
        
        .share-btn.whatsapp {
            background: #25D366;
        }
        
        .share-btn.facebook {
            background: #1877F2;
        }
        
        .share-btn.twitter {
            background: #1DA1F2;
        }
        
        .share-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        
        .url-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .url-title {
            font-size: 16px;
            font-weight: bold;
            color: #002855;
            margin-bottom: 10px;
        }
        
        .url-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 12px;
            margin-bottom: 10px;
            background: white;
        }
        
        .copy-btn {
            background: #6c757d;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .copy-btn:hover {
            background: #5a6268;
        }
        
        .back-link {
            margin-top: 20px;
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            color: #002855;
            text-decoration: underline;
        }
        
        @media (max-width: 600px) {
            .insignia-container {
                padding: 20px;
            }
            
            .insignia-image {
                width: 280px;
                height: 280px;
            }
            
            .share-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .share-btn {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="insignia-container">
        <div class="insignia-image" style="background-image: url('<?php echo $insignia_data['imagen_path']; ?>');" onclick="window.open('ver_insignia_completa.php?codigo=<?php echo urlencode($codigo_insignia); ?>', '_blank')">
            <div class="click-indicator">👆 Haz clic para ver certificado completo</div>
        </div>
        
        <div class="share-section">
            <div class="qr-section">
                <h3>📱 Código QR para Compartir</h3>
                <div class="qr-container">
                    <canvas id="qrcode"></canvas>
                    <p>Escanea este código para verificar la insignia</p>
                </div>
            </div>
            
            <div class="share-buttons">
                <a href="javascript:void(0)" onclick="shareWhatsApp()" class="share-btn whatsapp">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="javascript:void(0)" onclick="shareFacebook()" class="share-btn facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
                <a href="javascript:void(0)" onclick="shareTwitter()" class="share-btn twitter">
                    <i class="fab fa-x-twitter"></i> Twitter
                </a>
                <a href="javascript:void(0)" onclick="copyLink()" class="share-btn copy">
                    📋 Copiar Enlace
                </a>
            </div>
            
            <!-- Botón Ver Validación -->
            <div style="margin-top: 20px; text-align: center;">
                <a href="ver_insignia_publica.php?insignia=<?php echo urlencode($codigo_insignia); ?>" 
                   class="share-btn" 
                   style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);"
                   target="_blank">
                    <i class="fas fa-check-circle"></i> Ver Validación
                </a>
            </div>
        </div>
        
        <div class="url-section">
            <div class="url-title">🔗 URL del Certificado Completo:</div>
            <input type="text" class="url-input" id="verificationUrl" readonly>
            <button class="copy-btn" onclick="copyUrl()">Copiar URL</button>
        </div>
        
        <a href="ver_insignia_completa.php?codigo=<?php echo urlencode($codigo_insignia); ?>" class="back-link">← Volver a la insignia completa</a>
        
    </div>
    
    <!-- Librería QR Code -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <!-- Librería alternativa QR Code -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    
    <script>
        // Redirigir automáticamente al certificado completo si se accede desde un enlace compartido
        // PERO solo si NO es un crawler (los crawlers ya fueron manejados por PHP)
        // Verificar si viene de Facebook, Twitter, WhatsApp u otro sitio de redes sociales
        const urlParams = new URLSearchParams(window.location.search);
        const codigo = urlParams.get('codigo');
        
        // Detectar si es un crawler (no redirigir si es crawler)
        const userAgent = navigator.userAgent.toLowerCase();
        const isCrawler = userAgent.includes('facebookexternalhit') ||
                         userAgent.includes('facebot') ||
                         userAgent.includes('twitterbot') ||
                         userAgent.includes('linkedinbot') ||
                         userAgent.includes('whatsapp') ||
                         userAgent.includes('slackbot') ||
                         userAgent.includes('discordbot') ||
                         userAgent.includes('redditbot') ||
                         userAgent.includes('googlebot') ||
                         userAgent.includes('bingbot') ||
                         userAgent.includes('crawler') ||
                         userAgent.includes('bot');
        
        // Detectar si viene de un enlace compartido (solo para usuarios reales, no crawlers)
        const referrer = document.referrer.toLowerCase();
        const isFromSocialMedia = referrer.includes('facebook.com') || 
                                  referrer.includes('twitter.com') || 
                                  referrer.includes('whatsapp') ||
                                  referrer.includes('linkedin.com') ||
                                  referrer.includes('reddit.com') ||
                                  urlParams.get('from_share') === '1';
        
        // Si viene de redes sociales, NO es un crawler, y no tiene el parámetro 'stay', redirigir
        if (isFromSocialMedia && !isCrawler && !urlParams.get('stay') && codigo) {
            // Usar replace en lugar de href para evitar que el usuario pueda volver atrás
            window.location.replace(`ver_insignia_completa.php?codigo=${encodeURIComponent(codigo)}`);
        }
        
        // La imagen ya se aplica directamente en el HTML
        
        // Función para obtener la URL base correcta (como funcionaba localmente)
        function getCorrectIP() {
            const hostname = window.location.hostname;
            
            // Si es localhost o IP local, usar ngrok para compartir
            if (hostname === 'localhost' || hostname === '127.0.0.1' || hostname.startsWith('192.168.')) {
                // Verificar si hay una URL de ngrok configurada en la sesión
                <?php if (isset($_SESSION['ngrok_url']) && !empty($_SESSION['ngrok_url'])): ?>
                    return '<?php echo rtrim($_SESSION['ngrok_url'], '/'); ?>';
                <?php else: ?>
                    return 'https://bad-elephant-84.loca.lt';
                <?php endif; ?>
            }
            
            // Para servidor, usar la URL actual
            const port = window.location.port || '80';
            const baseUrl = `${window.location.protocol}//${hostname}${port !== '80' ? ':' + port : ''}`;
            
            // Obtener el path base del script actual
            const currentPath = window.location.pathname;
            const scriptPath = currentPath.substring(0, currentPath.lastIndexOf('/'));
            
            return baseUrl + scriptPath;
        }
        
        // Generar código QR al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrl = getCorrectIP();
            // El QR debe apuntar a ver_insignia_completa.php (certificado completo) - misma funcionalidad que la imagen
            const certificadoUrl = `${baseUrl}/ver_insignia_completa.php?codigo=<?php echo urlencode($codigo_insignia); ?>`;
            const canvas = document.getElementById('qrcode');
            
            console.log('URL del certificado completo:', certificadoUrl);
            console.log('Canvas encontrado:', canvas);
            
            // Establecer la URL en el campo de entrada
            const urlInput = document.getElementById('verificationUrl');
            if (urlInput) {
                urlInput.value = certificadoUrl;
            }
            
            // Generar QR dinámicamente apuntando al certificado completo
            generateQRCode(certificadoUrl, canvas);
        });
        
        // Función para generar código QR
        function generateQRCode(url, canvas) {
            if (!canvas) {
                console.error('Canvas no encontrado');
                return;
            }
            
            console.log('Generando QR para URL:', url);
            
            // Limpiar canvas anterior
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Establecer dimensiones del canvas
            canvas.width = 200;
            canvas.height = 200;
            
            // Método 1: Usar QRCode.toCanvas
            if (typeof QRCode !== 'undefined') {
                console.log('Usando QRCode.toCanvas');
                QRCode.toCanvas(canvas, url, {
                    width: 200,
                    height: 200,
                    color: {
                        dark: '#1b396a',
                        light: '#ffffff'
                    },
                    margin: 2
                }, function (error) {
                    if (error) {
                        console.error('Error con QRCode.toCanvas:', error);
                        generateQRAlternative(url, canvas);
                    } else {
                        console.log('QR generado exitosamente con QRCode.toCanvas');
                        // Agregar imagen de insignia al centro del QR
                        addInsigniaToQR(canvas);
                    }
                });
            } else {
                console.log('QRCode no disponible, usando método alternativo');
                generateQRAlternative(url, canvas);
            }
            
            // Si todo falla, generar QR simple
            setTimeout(() => {
                if (canvas && canvas.width === 0) {
                    console.log('Generando QR simple como último recurso');
                    generateSimpleQR(url, canvas);
                }
            }, 1000);
        }
        
        // Función para agregar imagen de insignia al centro del QR
        function addInsigniaToQR(canvas) {
            const ctx = canvas.getContext('2d');
            const imagePath = '<?php echo $insignia_data['imagen_path']; ?>';
            
            // Crear imagen
            const img = new Image();
            img.onload = function() {
                // Calcular posición central
                const centerX = canvas.width / 2;
                const centerY = canvas.height / 2;
                const logoSize = 40; // Tamaño del logo en el centro
                
                // Dibujar fondo blanco para el logo
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(centerX - logoSize/2 - 2, centerY - logoSize/2 - 2, logoSize + 4, logoSize + 4);
                
                // Dibujar borde
                ctx.strokeStyle = '#1b396a';
                ctx.lineWidth = 1;
                ctx.strokeRect(centerX - logoSize/2 - 2, centerY - logoSize/2 - 2, logoSize + 4, logoSize + 4);
                
                // Dibujar la imagen de la insignia
                ctx.drawImage(img, centerX - logoSize/2, centerY - logoSize/2, logoSize, logoSize);
                
                console.log('Imagen de insignia agregada al QR:', imagePath);
            };
            img.onerror = function() {
                console.error('Error al cargar imagen de insignia:', imagePath);
            };
            img.src = imagePath;
        }
        
        // Método alternativo de generación QR
        function generateQRAlternative(url, canvas) {
            try {
                // Usar qrcode-generator como alternativa
                if (typeof qrcode !== 'undefined') {
                    const qr = qrcode(0, 'M');
                    qr.addData(url);
                    qr.make();
                    
                    const size = qr.getModuleCount();
                    const cellSize = Math.floor(200 / size);
                    const offset = (200 - size * cellSize) / 2;
                    
                    const ctx = canvas.getContext('2d');
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
                    console.log('QR generado exitosamente con método alternativo');
                    // Agregar imagen de insignia al centro del QR
                    addInsigniaToQR(canvas);
                } else {
                    throw new Error('Ninguna librería QR disponible');
                }
            } catch (error) {
                console.error('Error con método alternativo:', error);
                showFallback(url);
            }
        }
        
        // Mostrar fallback si no se puede generar QR
        function showFallback(url) {
            const canvas = document.getElementById('qrcode');
            if (canvas) {
                canvas.style.display = 'none';
                const fallbackDiv = document.createElement('div');
                fallbackDiv.style.cssText = 'text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;';
                fallbackDiv.innerHTML = `
                    <p style="color: #1b396a; font-weight: bold; margin-bottom: 10px;">📱 URL de Verificación:</p>
                    <p style="word-break: break-all; font-size: 12px; color: #666; margin-bottom: 15px;">${url}</p>
                    <button onclick="copyVerificationUrl()" style="background: #1b396a; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer;">Copiar URL</button>
                `;
                canvas.parentNode.appendChild(fallbackDiv);
            }
        }
        
        // Función simple para generar QR básico
        function generateSimpleQR(url, canvas) {
            const ctx = canvas.getContext('2d');
            canvas.width = 200;
            canvas.height = 200;
            
            // Fondo blanco
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, 200, 200);
            
            // Borde
            ctx.strokeStyle = '#1b396a';
            ctx.lineWidth = 2;
            ctx.strokeRect(10, 10, 180, 180);
            
            // Texto
            ctx.fillStyle = '#1b396a';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('QR Code', 100, 100);
            ctx.fillText('Escanea para verificar', 100, 120);
            
            console.log('QR simple generado');
        }
        
        // Función para copiar URL de verificación
        function copyVerificationUrl() {
            const verificationUrl = document.getElementById('verificationUrl').value;
            navigator.clipboard.writeText(verificationUrl).then(function() {
                alert('URL de verificación copiada al portapapeles');
            });
        }
        
        // Función para compartir en WhatsApp
        function shareWhatsApp() {
            const verificationUrl = document.getElementById('verificationUrl').value;
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent('🎖️ Insignia TecNM - Escanea el código QR para verificar: ' + verificationUrl)}`;
            window.open(whatsappUrl, '_blank');
        }
        
        // Función para compartir en Facebook
        function shareFacebook() {
            // Usar la URL de imagen_clickeable.php para que Facebook lea los meta tags correctos
            const baseUrl = getCorrectIP();
            const shareUrl = `${baseUrl}/imagen_clickeable.php?codigo=<?php echo urlencode($codigo_insignia); ?>`;
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
            window.open(facebookUrl, '_blank');
        }
        
        // Función para compartir en Twitter
        function shareTwitter() {
            const verificationUrl = document.getElementById('verificationUrl').value;
            const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent('🎖️ Insignia TecNM - Verifica con código QR')}&url=${encodeURIComponent(verificationUrl)}`;
            window.open(twitterUrl, '_blank');
        }
        
        // Función para copiar enlace de verificación
        function copyLink() {
            const verificationUrl = document.getElementById('verificationUrl').value;
            navigator.clipboard.writeText(verificationUrl).then(function() {
                alert('Enlace de verificación copiado al portapapeles');
            });
        }
        
        // Función para copiar URL
        function copyUrl() {
            const urlInput = document.querySelector('.url-input');
            urlInput.select();
            urlInput.setSelectionRange(0, 99999);
            document.execCommand('copy');
            
            const btn = document.querySelector('.copy-btn');
            const originalText = btn.textContent;
            btn.textContent = '¡Copiado!';
            btn.style.background = '#28a745';
            
            setTimeout(() => {
                btn.textContent = originalText;
                btn.style.background = '#6c757d';
            }, 2000);
        }
        
        // Función para detectar dispositivo móvil
        function isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }
        
        // Optimizar experiencia móvil
        if (isMobile()) {
            document.body.style.padding = '10px';
        }
        
        // Agregar evento de clic a la imagen
        document.querySelector('.insignia-image').addEventListener('click', function() {
            window.open('ver_insignia_completa.php?codigo=<?php echo urlencode($codigo_insignia); ?>', '_blank');
        });
    </script>
</body>
</html>