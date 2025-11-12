<?php
// Página pública de validación de insignias
// Obtener código de insignia de la URL
$codigo_insignia = $_GET['insignia'] ?? '';

// Mantener el formato original del código de insignia
// No convertir a mayúsculas para preservar el formato exacto

// Si no hay código, mostrar página de búsqueda
if (empty($codigo_insignia)) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Validación de Insignias TecNM</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background: #fff;
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
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 500px;
                width: 100%;
                margin: 40px auto;
            }
            
            .logo {
                width: 100px;
                height: 100px;
                background: #002855;
                border-radius: 50%;
                margin: 0 auto 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 24px;
                font-weight: bold;
            }
            
            h1 {
                color: #002855;
                margin-bottom: 20px;
            }
            
            .search-form {
                margin: 30px 0;
            }
            
            input[type="text"] {
                width: 100%;
                padding: 15px;
                border: 2px solid #ddd;
                border-radius: 8px;
                font-size: 16px;
                margin-bottom: 15px;
            }
            
            .btn {
                background: #002855;
                color: white;
                padding: 15px 30px;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                cursor: pointer;
                width: 100%;
            }
            
            .btn:hover {
                background: #1b396a;
            }
            
            .example {
                margin-top: 20px;
                color: #666;
                font-size: 14px;
            }
            
            /* ==== PIE DE PÁGINA ==== */
            footer {
                font-size: 14px;
                margin-top: 40px;
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                background: #1b396a;
                color: white;
                text-align: center;
                padding: 10px 0;
                z-index: 10;
            }
        </style>
    </head>
    <body>
        <header>
            <h1>Insignias TecNM</h1>
        </header>
        
        <div class="container">
            <div class="logo">TecNM</div>
            <h1>Sistema de Validación de Insignias</h1>
            <p>Ingresa el código de la insignia para validar su autenticidad</p>
            
            <form class="search-form" method="GET">
                <input type="text" name="insignia" placeholder="Ej: TecNM-ITSM-20251-115" required>
                <button type="submit" class="btn">Validar Insignia</button>
            </form>
            
            <div class="example">
                <strong>Ejemplo:</strong> TecNM-ITSM-20251-115
            </div>
        </div>
        
        <footer>
            <p>Copyright 2025 - TecNM<br>
            Última actualización - Septiembre 2025</p>
        </footer>
    </body>
    </html>
    <?php
    exit();
}

// Obtener datos reales de la base de datos
require_once 'conexion.php';

$insignia_data = null;

try {
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
    
    // Obtener datos de la insignia con categoría dinámica desde la base de datos
    // Primero obtener los datos básicos, luego buscar la categoría en una consulta separada si es necesario
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
            re.Nombre_Completo as responsable_nombre,
            re.Cargo as responsable_cargo,
            'Instituto Tecnológico de San Marcos' as nombre_instituto
        FROM insigniasotorgadas io
        LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
        LEFT JOIN responsable_emision re ON io.Responsable_Emision = re." . $campo_id_responsable . "
        WHERE io.Codigo_Insignia = ?
        LIMIT 1
    ";
    
    $stmt = $conexion->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Error al preparar consulta: " . $conexion->error);
    }
    
    $stmt->bind_param("s", $codigo_insignia);
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
            // Obtener la categoría dinámicamente desde la base de datos
            $nombre_categoria = 'Formación Integral'; // Valor por defecto
            
            if ($tiene_cat_ins) {
                // Buscar el tipo_insignia basándose en el nombre de la insignia
                $nombre_insignia_buscar = $row['nombre_insignia'];
                
                $sql_categoria = "
                    SELECT cat.Nombre_cat
                    FROM tipo_insignia ti
                    LEFT JOIN cat_insignias cat ON ti.Cat_ins = cat." . $campo_id_cat . "
                    WHERE ti." . $campo_nombre_tipo . " LIKE ?
                    LIMIT 1
                ";
                
                $stmt_cat = $conexion->prepare($sql_categoria);
                if ($stmt_cat) {
                    $buscar_like = '%' . $nombre_insignia_buscar . '%';
                    $stmt_cat->bind_param("s", $buscar_like);
                    if ($stmt_cat->execute()) {
                        $result_cat = $stmt_cat->get_result();
                        if ($row_cat = $result_cat->fetch_assoc()) {
                            $nombre_categoria = $row_cat['Nombre_cat'] ?? 'Formación Integral';
                        }
                    }
                    $stmt_cat->close();
                }
            }
            
            // Agregar la categoría al array de resultados
            $row['nombre_categoria'] = $nombre_categoria;
            
            // Mapear nombre de insignia a imagen PNG correspondiente
            $nombre_insignia = $row['nombre_insignia'];
            
            // Debug: Mostrar el nombre de insignia obtenido
            echo "<!-- DEBUG: nombre_insignia = '$nombre_insignia' -->";
            echo "<!-- DEBUG: codigo_insignia = '" . $row['codigo_insignia'] . "' -->";
            echo "<!-- DEBUG: nombre_categoria = '$nombre_categoria' -->";
            
            // Función para determinar la insignia dinámicamente basándose en el código
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
                        echo "<!-- DEBUG: Encontrado código '$codigo' en '$codigo_insignia' -> '$tipo' -->";
                        return $mapeo_imagenes[$tipo] ?? null;
                    }
                }
                
                // 2. Intentar determinar por nombre de insignia
                if (isset($mapeo_imagenes[$nombre_insignia])) {
                    echo "<!-- DEBUG: Encontrado por nombre '$nombre_insignia' -->";
                    return $mapeo_imagenes[$nombre_insignia];
                }
                
                // 3. Buscar coincidencias parciales en el nombre
                foreach ($mapeo_imagenes as $tipo => $archivo) {
                    if (strpos($nombre_insignia, $tipo) !== false || strpos($tipo, $nombre_insignia) !== false) {
                        echo "<!-- DEBUG: Coincidencia parcial '$nombre_insignia' -> '$tipo' -->";
                        return $archivo;
                    }
                }
                
                echo "<!-- DEBUG: No se encontró coincidencia, usando primera disponible -->";
                return reset($mapeo_imagenes);
            }
            
            // Determinar la imagen dinámicamente
            $archivo_imagen = determinarInsigniaDinamica($row['codigo_insignia'], $nombre_insignia);
            $imagen_path = 'imagen/Insignias/' . $archivo_imagen;
            
            // Verificar que el archivo existe, si no, usar la primera disponible
            if (!file_exists($imagen_path)) {
                echo "<!-- DEBUG: Archivo '$imagen_path' no existe, buscando alternativa -->";
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
                        echo "<!-- DEBUG: Usando imagen alternativa: $imagen_path -->";
                        break;
                    }
                }
            }
            
            echo "<!-- DEBUG: imagen_path final = '$imagen_path' -->";
            
            // Convertir datos de BD al formato de sesión (igual que ver_insignia_completa.php)
            $insignia_data = [
                'codigo' => $row['codigo_insignia'],
                'nombre' => $row['nombre_insignia'],
                'categoria' => $row['nombre_categoria'],
                'destinatario' => $row['destinatario'],
                'descripcion' => "Insignia de " . $row['nombre_insignia'] . " otorgada por el Tecnológico Nacional de México",
                'criterios' => "Para obtener esta insignia de " . $row['nombre_insignia'] . ", el estudiante debe haber demostrado competencias específicas.",
                'fecha_emision' => $row['fecha_emision'],
                'emisor' => 'TecNM / ' . $row['nombre_instituto'],
                'evidencia' => "Certificación oficial emitida por el Tecnológico Nacional de México",
                'archivo_visual' => "Insig_" . $row['codigo_insignia'] . ".jpg",
                'imagen_path' => $imagen_path,
                'responsable' => $row['responsable_nombre'],
                'codigo_responsable' => 'TecNM-ITSM-2025-Resp001',
                'estatus' => 'Autorizado',
                'periodo' => '2025-1'
            ];

            // Buscar firma digital activa del responsable para mostrar sello automáticamente
            $firma_data = null;
            try {
                $sql_buscar_firma = "SELECT hash_verificacion, archivo_firma, fecha_creacion 
                                     FROM firmas_digitales 
                                     WHERE nombre_responsable = ? AND activa = 1 
                                     ORDER BY fecha_creacion DESC LIMIT 1";
                $stmt_firma = $conexion->prepare($sql_buscar_firma);
                if ($stmt_firma) {
                    $stmt_firma->bind_param("s", $insignia_data['responsable']);
                    $stmt_firma->execute();
                    $res_firma = $stmt_firma->get_result();
                    if ($res_firma && $res_firma->num_rows > 0) {
                        $firma_data = $res_firma->fetch_assoc();
                    }
                    $stmt_firma->close();
                }
            } catch (Exception $e) {
                // No bloquear la vista si hay error al consultar firma
            }
        } else {
            // Si no se encuentra en BD, mostrar mensaje de error
            $insignia_data = null;
        }
    }
} catch (Exception $e) {
    // En caso de error, no mostrar datos por defecto
    $insignia_data = null;
}

// Si no se encontraron datos, mostrar página de error
if ($insignia_data === null) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Insignia No Encontrada - TecNM</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #1b396a, #002855);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .error-container {
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                text-align: center;
                max-width: 500px;
                width: 100%;
            }
            
            .error-icon {
                font-size: 64px;
                color: #dc3545;
                margin-bottom: 20px;
            }
            
            h1 {
                color: #1b396a;
                margin-bottom: 20px;
            }
            
            p {
                color: #666;
                margin-bottom: 30px;
                line-height: 1.6;
            }
            
            .btn {
                background: linear-gradient(135deg, #1b396a, #002855);
                color: white;
                padding: 15px 30px;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                transition: all 0.3s ease;
            }
            
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">❌</div>
            <h1>Insignia No Encontrada</h1>
            <p>La insignia con código <strong><?php echo htmlspecialchars($codigo_insignia); ?></strong> no se encuentra registrada en nuestro sistema.</p>
            <p>Por favor, verifica que el código de la insignia sea correcto o contacta con el administrador del sistema.</p>
            <a href="validacion.php" class="btn">🔍 Buscar Otra Insignia</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Insignia TecNM - <?php echo htmlspecialchars($insignia_data['codigo']); ?></title>
    
    <!-- Meta tags para Facebook y redes sociales -->
    <?php
    // Generar URLs para las imágenes - usar URL del servidor remoto
    $host = $_SERVER['HTTP_HOST'];
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base_url = $protocol . '://' . $host . '/Insignias_TecNM_Funcional';
    
    $image_url = $base_url . '/' . $insignia_data['imagen_path'];
    $validation_url = $base_url . '/validacion.php?insignia=' . urlencode($insignia_data['codigo']);
    ?>
    
    <!-- Meta tags optimizados para Facebook -->
    <meta property="og:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta property="og:description" content="Insignia de <?php echo htmlspecialchars($insignia_data['nombre']); ?> otorgada a <?php echo htmlspecialchars($insignia_data['destinatario']); ?>">
    <meta property="og:image" content="<?php echo $image_url; ?>">
    <meta property="og:image:secure_url" content="<?php echo $image_url; ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="<?php echo $validation_url; ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TecNM Insignias">
    
    <!-- Meta tags adicionales -->
    <meta name="description" content="Insignia de <?php echo htmlspecialchars($insignia_data['nombre']); ?> otorgada a <?php echo htmlspecialchars($insignia_data['destinatario']); ?>">
    <meta name="keywords" content="TecNM, insignia, certificado, <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta name="author" content="TecNM">
    
    <!-- Meta tags para Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta name="twitter:description" content="Insignia de <?php echo htmlspecialchars($insignia_data['nombre']); ?> otorgada a <?php echo htmlspecialchars($insignia_data['destinatario']); ?>">
    <meta name="twitter:image" content="<?php echo $image_url; ?>">
    <meta name="twitter:image:alt" content="Insignia TecNM de <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1b396a, #002855);
            min-height: 100vh;
            padding: 20px;
        }
        
        .verification-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1b396a, #002855);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        
        .certificate-container {
            padding: 40px;
            text-align: center;
        }
        
        
        .actions {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
        }
        
        .btn {
            background: linear-gradient(135deg, #1b396a, #002855);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
        }
        
        .document-preview {
            background-image: url('imagen/Hoja_membrentada.png');
            background-size: contain;
            background-position: top center;
            background-repeat: no-repeat;
            border: 2px solid #002855;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            width: 8.5in; /* Formato carta - ancho */
            height: 11in; /* Formato carta - alto */
            position: relative;
            color: #333;
            overflow: visible;
            background-color: transparent; /* Cambiado para que se vea la imagen */
            margin: 0 auto;
            transform: scale(0.6); /* Reducir escala para que quepa en pantalla */
            transform-origin: top center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .document-insignia {
            width: 80px;
            height: 80px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin: 0 auto 10px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            .verification-container {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
                margin: 0;
                width: 100%;
            }
            
            .header, .actions {
                display: none;
            }
            
            .certificate-container {
                padding: 0;
                margin: 0;
                width: 100%;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .document-preview {
                transform: scale(1); /* Tamaño completo para impresión */
                width: 8.5in; /* Formato carta completo */
                height: 11in; /* Formato carta completo */
                margin: 0;
                padding: 0.75in 0.5in 0.5in 0.5in; /* Más espacio arriba para la imagen */
                box-shadow: none;
                border: none;
                page-break-inside: avoid;
                background-image: url('imagen/Hoja_membrentada.png') !important;
                background-size: cover !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
                position: relative;
                background-color: transparent !important;
            }
            
            /* Asegurar que el contenido quepa en una página */
            .document-preview * {
                page-break-inside: avoid;
            }
            
            /* Ajustar tamaños de fuente para impresión */
            .document-preview div {
                font-size: 12px !important;
            }
            
            /* Título institucional */
            .document-preview div:first-of-type {
                font-size: 14px !important;
                margin-top: 80px !important;
            }
            
            /* Título del reconocimiento */
            .document-preview div:nth-of-type(3) {
                font-size: 16px !important;
            }
            
            /* Nombre del destinatario */
            .document-preview div:nth-of-type(5) {
                font-size: 16px !important;
            }
            
            /* Texto descriptivo */
            .texto-descriptivo {
                font-size: 11px !important;
                line-height: 1.2 !important;
                max-height: none !important;
                overflow: visible !important;
                margin-bottom: 15px !important;
                padding: 0 10px !important;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="header">
            <h1>🔍 Verificación de Insignia TecNM</h1>
            <p>Sistema de Validación Oficial</p>
        </div>
        
        
        <div class="certificate-container">
            <!-- Imagen principal de la insignia para Facebook -->
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="<?php echo $insignia_data['imagen_path']; ?>" 
                     alt="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>" 
                     style="max-width: 200px; height: auto; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            </div>
            
            <div class="document-preview">
                <!-- Título institucional -->
                <div style="font-size: 14px; font-weight: bold; color: #1b396a; margin-top: 180px; margin-bottom: 10px; text-align: center;">
                    EL TECNOLÓGICO NACIONAL DE MÉXICO
                </div>
                <div style="font-size: 12px; color: #1b396a; margin-bottom: 15px; text-align: center;">
                    OTORGA EL PRESENTE
                </div>
                
                <!-- Título principal del reconocimiento -->
                <div style="font-size: 16px; font-weight: bold; color: #d4af37; margin-bottom: 20px; text-align: center; text-transform: uppercase;">
                    RECONOCIMIENTO INSTITUCIONAL<br>
                    CON IMPACTO CURRICULAR
                </div>
                
                <!-- Destinatario -->
                <div style="font-size: 11px; margin-bottom: 8px; text-align: center;">A</div>
                <div style="font-size: 16px; font-weight: bold; color: #1b396a; margin-bottom: 15px; text-align: center;">
                    <?php echo htmlspecialchars($insignia_data['destinatario']); ?>
                </div>
                
                <!-- Texto descriptivo dinámico -->
                <div style="font-size: 16px; text-align: left; line-height: 1.4; margin-bottom: 20px; padding: 0 15px; max-height: 200px; overflow: hidden;" class="texto-descriptivo">
                    <?php
                    // Texto dinámico según el tipo de insignia
                    $nombre_insignia = $insignia_data['nombre'];
                    
                    // Definir textos específicos para cada tipo de insignia
                    $textos_insignias = [
                        'Embajador del Arte' => 'Por su destacada participación como Embajador del Arte, demostrando talento, creatividad y sensibilidad artística en la promoción de la cultura, las artes y la expresión estética como medios de transformación social y fortalecimiento de la identidad institucional.',
                        'Embajador del Deporte' => 'Por su destacada participación como Embajador del Deporte, demostrando excelencia deportiva, liderazgo y compromiso con la promoción de valores como el trabajo en equipo, la disciplina y la superación personal.',
                        'Talento Científico' => 'Por su destacada participación como Talento Científico, demostrando habilidades analíticas, pensamiento crítico y contribución al desarrollo del conocimiento científico y tecnológico.',
                        'Talento Innovador' => 'Por su destacada participación como Talento Innovador, demostrando creatividad, iniciativa y capacidad para generar soluciones innovadoras que contribuyan al desarrollo tecnológico.',
                        'Responsabilidad Social' => 'Por su destacada participación en Responsabilidad Social, demostrando compromiso con la comunidad, valores éticos y contribución al bienestar social.',
                        'Formación y Actualización' => 'Por su destacada participación en Formación y Actualización, demostrando compromiso con el aprendizaje continuo y la actualización de conocimientos.',
                        'Movilidad e Intercambio' => 'Por su destacada participación en Movilidad e Intercambio, demostrando apertura cultural, adaptabilidad y contribución al intercambio académico.'
                    ];
                    
                    // Obtener el texto específico o usar uno genérico
                    $texto_especifico = isset($textos_insignias[$nombre_insignia]) ? $textos_insignias[$nombre_insignia] : "Por su destacada participación como $nombre_insignia, demostrando excelencia y compromiso con los valores institucionales.";
                    ?>
                    
                    <?php echo $texto_especifico; ?><br><br>
                    
                    Su dedicación, disciplina y compromiso reflejan una formación integral orientada a la excelencia, la apreciación cultural y el desarrollo humano, contribuyendo al enriquecimiento del entorno educativo y comunitario.<br><br>
                    
                    Este reconocimiento se otorga como testimonio de su esfuerzo, vocación y compromiso con los valores del Tecnológico Nacional de México.
                </div>
                
                <!-- Badge hexagonal en la esquina inferior izquierda -->
                <div style="position: absolute; bottom: 40px; left: 30px; width: 80px; height: 80px;">
                    <div class="document-insignia" style="width: 100%; height: 100%; background-image: url(<?php echo $insignia_data['imagen_path']; ?>);"></div>
                </div>
                
                <!-- Sello de firma digital (automático si existe en BD) -->
                <?php if (!empty($firma_data)): ?>
                <div style="position: absolute; bottom: 36px; left: 120px; background: rgba(16, 185, 129, 0.12); border: 2px solid #10b981; color:#065f46; padding: 8px 10px; border-radius: 10px; font-size: 10px; max-width: 280px; backdrop-filter: blur(2px);">
                    <div style="font-weight: 800; font-size: 10px; display:flex; align-items:center; gap:6px; color:#065f46;">
                        <span>🔏</span> Firmado digitalmente (SAT)
                    </div>
                    <div style="margin-top:6px; line-height:1.4;">
                        Hash: <span style="font-family: monospace; word-break: break-all;"><?php echo htmlspecialchars(substr($firma_data['hash_verificacion'], 0, 24)); ?>…</span>
                    </div>
                    <div style="margin-top:6px;">
                        <a href="<?php echo 'firmas_digitales/' . rawurlencode($firma_data['archivo_firma']); ?>" target="_blank" style="color:#0b5ed7; text-decoration: underline; font-weight:600;">Ver archivo de firma</a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Firma en la esquina inferior derecha -->
                <div style="position: absolute; bottom: 40px; right: 80px; text-align: right; font-size: 10px; color: #333; background: rgba(255,255,255,0.9); padding: 8px; border-radius: 5px;">
                    <div style="border-bottom: 1px solid #333; width: 100px; margin-bottom: 6px;"></div>
                    <div style="font-weight: bold; color: #1b396a; font-size: 11px;"><?php echo htmlspecialchars($insignia_data['responsable']); ?></div>
                    <div style="font-size: 9px; color: #666;">RESPONSABLE DE EMISIÓN</div>
                </div>
                
                <!-- Fecha y ubicación -->
                <div style="position: absolute; bottom: 25px; right: 80px; font-size: 9px; color: #666; text-align: right; background: rgba(255,255,255,0.9); padding: 6px; border-radius: 3px; width: 100px;">
                    CIUDAD DE MÉXICO<br>
                    <?php echo date('F Y', strtotime($insignia_data['fecha_emision'])); ?>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <a href="consulta_publica.php" class="btn btn-secondary" style="background: #6c757d; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; margin: 0 10px; transition: all 0.3s ease;">
                ← Regresar
            </a>
            <button onclick="window.print()" class="btn">
                🖨️ Imprimir Certificado
            </button>
            <button onclick="compartirInsignia()" class="btn" id="btnCompartir" style="display: none;">
                📤 Compartir Insignia
            </button>
            <a href="consulta_publica.php" class="btn btn-secondary">
                🔍 Buscar Otra Insignia
            </a>
        </div>
    </div>
    
    <script>
        // Función para mostrar el botón de compartir cuando la imagen se carga
        function mostrarBotonCompartir() {
            const btnCompartir = document.getElementById('btnCompartir');
            if (btnCompartir) {
                btnCompartir.style.display = 'inline-block';
                btnCompartir.style.animation = 'fadeIn 0.5s ease-in';
            }
        }
        
        // Función para compartir la insignia
        function compartirInsignia() {
            const url = window.location.href;
            const titulo = 'Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>';
            const texto = 'Mira mi insignia de <?php echo htmlspecialchars($insignia_data['nombre']); ?> otorgada por TecNM';
            
            if (navigator.share) {
                // Usar Web Share API si está disponible
                navigator.share({
                    title: titulo,
                    text: texto,
                    url: url
                });
            } else {
                // Fallback: copiar URL al portapapeles
                navigator.clipboard.writeText(url).then(function() {
                    alert('¡URL copiada al portapapeles! Puedes compartirla en redes sociales.');
                });
            }
        }
        
        // Detectar cuando la imagen se carga
        document.addEventListener('DOMContentLoaded', function() {
            const imagenInsignia = document.querySelector('img[src*="imagen/Insignias/"]');
            if (imagenInsignia) {
                if (imagenInsignia.complete) {
                    // La imagen ya está cargada
                    mostrarBotonCompartir();
                } else {
                    // Esperar a que la imagen se cargue
                    imagenInsignia.addEventListener('load', mostrarBotonCompartir);
                }
            }
        });
        
        // CSS para la animación
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>