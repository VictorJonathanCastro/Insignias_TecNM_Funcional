<?php
require_once 'conexion.php';

// Obtener código de insignia desde GET
$codigo_insignia = $_GET['codigo'] ?? '';

if (empty($codigo_insignia)) {
    die('Código de insignia requerido');
}

try {
    // Consulta simplificada que funciona
    $stmt = $conexion->prepare("
        SELECT 
            io.clave_insignia as codigo_insignia,
            d.Nombre_Completo as destinatario,
            io.fecha_otorgamiento as fecha_emision,
            io.evidencia,
            i.Descripcion as descripcion,
            ti.Nombre_ins as nombre_insignia,
            ti.Arch_ima as archivo_imagen_generico,
            ci.Nombre_cat as nombre_categoria,
            re.Nombre_Completo as responsable_nombre,
            re.Cargo as responsable_cargo,
            ic.Nombre_itc as nombre_instituto
        FROM insigniasotorgadas io
        LEFT JOIN destinatario d ON io.destinatario_id = d.id
        LEFT JOIN insignias i ON io.insignia_id = i.id
        LEFT JOIN tipo_insignia ti ON i.Tipo_Insignia = ti.id
        LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.id
        LEFT JOIN responsable_emision re ON io.responsable_id = re.id
        LEFT JOIN it_centros ic ON d.ITCentro = ic.id
        WHERE io.clave_insignia = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("s", $codigo_insignia);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Determinar la imagen a usar según el tipo de insignia
            $codigo_insignia = $row['codigo_insignia'];
            $nombre_insignia = $row['nombre_insignia'];
            $archivo_imagen_generico = $row['archivo_imagen_generico'];
            
            $imagen_path = '';
            
            // 1. Buscar imagen específica primero (Insig_TecNM-ITSM-20251-120.jpg)
            $archivo_especifico = "Insig_" . $codigo_insignia . ".jpg";
            $imagen_path = 'imagen/' . $archivo_especifico;
            if (!file_exists($imagen_path)) {
                $imagen_path = 'imagen/Insignias/' . $archivo_especifico;
            }
            
            // 2. Si no existe imagen específica, usar imagen genérica según el tipo
            if (!file_exists($imagen_path)) {
                if (!empty($archivo_imagen_generico)) {
                    $imagen_path = 'imagen/Insignias/' . $archivo_imagen_generico;
                    if (!file_exists($imagen_path)) {
                        $imagen_path = 'imagen/' . $archivo_imagen_generico;
                    }
                }
            }
            
            // 3. Si no existe imagen genérica, mapear según categoría
            if (!file_exists($imagen_path)) {
                // Mapear categorías a imágenes específicas
                $imagenes_por_categoria = [
                    'Formación Integral' => 'FormacionIntegral.png',
                    'Desarrollo Tecnológico' => 'TalentoInnovador.png',
                    'Responsabilidad Social' => 'RespSocial.png',
                    'Liderazgo' => 'InnovacionLiderazgo.png',
                    'Innovación Social' => 'InnovacionSocial.png',
                    'Conciencia Intercultural' => 'ConcienciaIntercultural.png',
                    'Movilidad e Intercambio' => 'MovilidadIntercambio.png',
                    'Excelencia Académica' => 'ExcelenciaAcademica.png'
                ];
                
                // Buscar por categoría primero
                $categoria = $row['nombre_categoria'];
                if (isset($imagenes_por_categoria[$categoria])) {
                    $imagen_path = 'imagen/Insignias/' . $imagenes_por_categoria[$categoria];
                    if (!file_exists($imagen_path)) {
                        $imagen_path = 'imagen/' . $imagenes_por_categoria[$categoria];
                    }
                }
                
                // Si no existe por categoría, usar archivo genérico de la BD
                if (!file_exists($imagen_path) && !empty($archivo_imagen_generico)) {
                    $imagen_path = 'imagen/Insignias/' . $archivo_imagen_generico;
                    if (!file_exists($imagen_path)) {
                        $imagen_path = 'imagen/' . $archivo_imagen_generico;
                    }
                }
                
                // Si aún no existe, usar imagen por defecto
                if (!file_exists($imagen_path)) {
                    $imagen_path = 'imagen/insignia_Responsabilidad Social.png';
                }
            }
            
            $insignia_data = [
                'codigo' => $row['codigo_insignia'],
                'nombre' => $row['nombre_insignia'],
                'categoria' => $row['nombre_categoria'],
                'destinatario' => $row['destinatario'],
                'descripcion' => $row['descripcion'],
                'fecha_emision' => $row['fecha_emision'],
                'emisor' => 'TecNM / ' . $row['nombre_instituto'],
                'evidencia' => $row['evidencia'],
                'responsable' => $row['responsable_nombre'],
                'imagen_path' => $imagen_path,
                'archivo_especifico' => $archivo_especifico,
                'archivo_generico' => $archivo_imagen_generico
            ];
        } else {
            die('Insignia no encontrada');
        }
    } else {
        die('Error en la consulta');
    }
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

// USAR LOCALTUNNEL PARA URL PÚBLICA
$localtunnel_url = 'https://quick-pens-win.loca.lt'; // Tu URL de localtunnel
$base_url = $localtunnel_url . '/Insignias_TecNM_Funcional';
$validation_url = $base_url . '/validacion.php?insignia=' . urlencode($codigo_insignia);
$image_url = $base_url . '/' . $insignia_data['imagen_path'];
$current_url = $base_url . '/facebook_insignia_publico.php?codigo=' . urlencode($codigo_insignia);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?></title>
    
    <!-- Meta tags optimizados para Facebook -->
    <meta property="og:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta property="og:description" content="Insignia de <?php echo htmlspecialchars($insignia_data['nombre']); ?> otorgada a <?php echo htmlspecialchars($insignia_data['destinatario']); ?>">
    <meta property="og:image" content="<?php echo $image_url; ?>">
    <meta property="og:image:secure_url" content="<?php echo $image_url; ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="<?php echo $current_url; ?>">
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
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #1b396a, #002855);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        
        .badge-image {
            width: 200px;
            height: 200px;
            margin: 0 auto 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .badge-image:hover {
            transform: scale(1.05);
        }
        
        .badge-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        h1 {
            color: #1b396a;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #1b396a;
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
            margin: 10px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .social-share {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .social-share h3 {
            color: #1b396a;
            margin-bottom: 15px;
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .share-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .share-btn.facebook {
            background: #1877f2;
        }
        
        .share-btn.whatsapp {
            background: #25d366;
        }
        
        .share-btn.twitter {
            background: #1da1f2;
        }
        
        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .click-instruction {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        
        .click-instruction p {
            margin: 0;
            color: #1976d2;
            font-weight: bold;
        }
        
        .debug-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
            text-align: left;
        }
        
        .debug-info h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        
        .debug-info p {
            margin: 5px 0;
            font-size: 12px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="badge-image" onclick="window.location.href='<?php echo $validation_url; ?>'">
            <img src="<?php echo $image_url; ?>" alt="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
        </div>
        
        <h1>Insignia TecNM</h1>
        <h2 style="color: #666; margin-bottom: 30px;"><?php echo htmlspecialchars($insignia_data['nombre']); ?></h2>
        
        <div class="click-instruction">
            <p>🖱️ Haz clic en la imagen para ver el certificado completo</p>
        </div>
        
        <div class="info">
            <div class="info-item">
                <span class="info-label">Destinatario:</span>
                <?php echo htmlspecialchars($insignia_data['destinatario']); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Código:</span>
                <?php echo htmlspecialchars($insignia_data['codigo']); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Fecha de emisión:</span>
                <?php echo date('d-m-Y', strtotime($insignia_data['fecha_emision'])); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Emisor:</span>
                <?php echo htmlspecialchars($insignia_data['emisor']); ?>
            </div>
        </div>
        
        <a href="<?php echo $validation_url; ?>" class="btn" target="_blank">
            🔍 Ver Certificado Completo
        </a>
        
        <div class="social-share">
            <h3>Compartir en redes sociales</h3>
            <div class="share-buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_url); ?>" class="share-btn facebook" target="_blank">
                    🔵 Facebook
                </a>
                <a href="https://wa.me/?text=<?php echo urlencode('Insignia TecNM - ' . $insignia_data['nombre'] . ' - ' . $current_url); ?>" class="share-btn whatsapp" target="_blank">
                    💬 WhatsApp
                </a>
                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('Insignia TecNM - ' . $insignia_data['nombre']); ?>&url=<?php echo urlencode($current_url); ?>" class="share-btn twitter" target="_blank">
                    🐤 Twitter
                </a>
            </div>
        </div>
        
        <div class="debug-info">
            <h4>🔧 Información de Debug:</h4>
            <p><strong>URL Pública:</strong> <?php echo $current_url; ?></p>
            <p><strong>Imagen:</strong> <?php echo $image_url; ?></p>
            <p><strong>Archivo físico:</strong> <?php echo $insignia_data['imagen_path']; ?></p>
            <p><strong>Existe:</strong> <?php echo file_exists($insignia_data['imagen_path']) ? '✅ Sí' : '❌ No'; ?></p>
            <p><strong>Archivo específico:</strong> <?php echo htmlspecialchars($insignia_data['archivo_especifico'] ?? 'No disponible'); ?></p>
            <p><strong>Archivo genérico:</strong> <?php echo htmlspecialchars($insignia_data['archivo_generico'] ?? 'No disponible'); ?></p>
            <p><strong>Categoría:</strong> <?php echo htmlspecialchars($insignia_data['categoria']); ?></p>
            <p><strong>Tipo de imagen:</strong> 
                <?php 
                if (!empty($insignia_data['archivo_especifico']) && strpos($insignia_data['imagen_path'], $insignia_data['archivo_especifico']) !== false) {
                    echo '🎯 <strong>ESPECÍFICA</strong> (Generada por metadatos)';
                } elseif (!empty($insignia_data['archivo_generico']) && strpos($insignia_data['imagen_path'], $insignia_data['archivo_generico']) !== false) {
                    echo '📋 <strong>GENÉRICA</strong> (De tabla tipo_insignia)';
                } elseif (strpos($insignia_data['imagen_path'], 'FormacionIntegral.png') !== false) {
                    echo '📚 <strong>FORMACIÓN INTEGRAL</strong>';
                } elseif (strpos($insignia_data['imagen_path'], 'TalentoInnovador.png') !== false) {
                    echo '💻 <strong>DESARROLLO TECNOLÓGICO</strong>';
                } elseif (strpos($insignia_data['imagen_path'], 'RespSocial.png') !== false) {
                    echo '🤝 <strong>RESPONSABILIDAD SOCIAL</strong>';
                } elseif (strpos($insignia_data['imagen_path'], 'InnovacionLiderazgo.png') !== false) {
                    echo '👑 <strong>LIDERAZGO</strong>';
                } elseif (strpos($insignia_data['imagen_path'], 'ExcelenciaAcademica.png') !== false) {
                    echo '🎓 <strong>EXCELENCIA ACADÉMICA</strong>';
                } else {
                    echo '🔄 <strong>POR DEFECTO</strong> (Fallback)';
                }
                ?>
            </p>
        </div>
    </div>
    
    <script>
        // Solo redirigir si no es Facebook bot
        var user_agent = navigator.userAgent;
        if (user_agent.indexOf('facebookexternalhit') === -1) {
            // Redirigir automáticamente al certificado después de 5 segundos
            setTimeout(function() {
                window.location.href = '<?php echo $validation_url; ?>';
            }, 5000);
        }
        
        // Hacer que toda la página sea clickeable
        document.addEventListener('click', function(e) {
            if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
                window.location.href = '<?php echo $validation_url; ?>';
            }
        });
    </script>
</body>
</html>
