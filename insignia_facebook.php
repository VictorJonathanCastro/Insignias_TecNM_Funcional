<?php
require_once 'conexion.php';

// Obtener código de insignia desde GET
$codigo_insignia = $_GET['codigo'] ?? '';

if (empty($codigo_insignia)) {
    die('Código de insignia requerido');
}

try {
    // Usar la misma consulta que validacion.php
    $stmt = $conexion->prepare("
        SELECT 
            io.clave_insignia as codigo_insignia,
            d.Nombre_Completo as destinatario,
            io.fecha_otorgamiento as fecha_emision,
            io.evidencia,
            i.Descripcion as descripcion,
            ti.Nombre_ins as nombre_insignia,
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
            $insignia_data = [
                'codigo' => $row['codigo_insignia'],
                'nombre' => $row['nombre_insignia'],
                'categoria' => $row['nombre_categoria'],
                'destinatario' => $row['destinatario'],
                'descripcion' => $row['descripcion'],
                'fecha_emision' => $row['fecha_emision'],
                'emisor' => 'TecNM / ' . $row['nombre_instituto'],
                'evidencia' => $row['evidencia'],
                'responsable' => $row['responsable_nombre']
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

// Generar URLs
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional';
$validation_url = $base_url . '/validacion.php?insignia=' . urlencode($codigo_insignia);
$image_url = $base_url . '/imagen/insignia_Responsabilidad Social.png';
$current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?></title>
    
    <!-- Meta tags optimizados para Facebook -->
    <meta property="og:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta property="og:description" content="He recibido una insignia de <?php echo htmlspecialchars($insignia_data['nombre']); ?> del TecNM!!!">
    <meta property="og:image" content="<?php echo $image_url; ?>">
    <meta property="og:image:secure_url" content="<?php echo $image_url; ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="<?php echo $current_url; ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TecNM Insignias">
    
    <!-- Meta tags adicionales -->
    <meta name="description" content="He recibido una insignia de <?php echo htmlspecialchars($insignia_data['nombre']); ?> del TecNM!!!">
    <meta name="keywords" content="TecNM, insignia, certificado, <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta name="author" content="TecNM">
    
    <!-- Meta tags para Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta name="twitter:description" content="He recibido una insignia de <?php echo htmlspecialchars($insignia_data['nombre']); ?> del TecNM!!!">
    <meta name="twitter:image" content="<?php echo $image_url; ?>">
    <meta name="twitter:image:alt" content="Insignia TecNM de <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }
        
        .header {
            background: linear-gradient(135deg, #1b396a, #002855);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .badge-container {
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
        }
        
        .badge-image {
            width: 200px;
            height: 200px;
            margin: 0 auto 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .badge-image:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .badge-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 20px;
        }
        
        .badge-info {
            background: white;
            padding: 30px;
            border-top: 1px solid #e9ecef;
        }
        
        .badge-title {
            font-size: 20px;
            font-weight: bold;
            color: #1b396a;
            margin-bottom: 10px;
        }
        
        .badge-code {
            font-size: 14px;
            color: #666;
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 8px;
            margin: 15px 0;
            display: inline-block;
        }
        
        .recipient {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
        }
        
        .click-instruction {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
            text-align: center;
        }
        
        .click-instruction p {
            margin: 0;
            color: #1976d2;
            font-weight: bold;
            font-size: 16px;
        }
        
        .social-share {
            background: #f8f9fa;
            padding: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .social-share h3 {
            color: #1b396a;
            margin-bottom: 15px;
            text-align: center;
            font-size: 18px;
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .share-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .share-btn.facebook {
            background: linear-gradient(135deg, #1877f2, #0d47a1);
        }
        
        .share-btn.whatsapp {
            background: linear-gradient(135deg, #25d366, #128c7e);
        }
        
        .share-btn.twitter {
            background: linear-gradient(135deg, #1da1f2, #0d47a1);
        }
        
        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .redirect-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            font-size: 14px;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .badge-image {
                width: 150px;
                height: 150px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .badge-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="container" onclick="window.location.href='<?php echo $validation_url; ?>'">
        <div class="header">
            <h1>🏆 Insignia TecNM</h1>
            <p>¡Felicidades por tu reconocimiento!</p>
        </div>
        
        <div class="badge-container">
            <div class="badge-image">
                <img src="<?php echo $image_url; ?>" alt="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
            </div>
            
            <div class="badge-title"><?php echo htmlspecialchars($insignia_data['nombre']); ?></div>
            <div class="badge-code"><?php echo htmlspecialchars($insignia_data['codigo']); ?></div>
            <div class="recipient">Otorgada a: <strong><?php echo htmlspecialchars($insignia_data['destinatario']); ?></strong></div>
        </div>
        
        <div class="click-instruction">
            <p>🖱️ Haz clic en cualquier parte para ver el certificado completo</p>
        </div>
        
        <div class="redirect-notice">
            <p>⏰ Serás redirigido automáticamente al certificado en 5 segundos</p>
        </div>
        
        <div class="social-share">
            <h3>Compartir en redes sociales</h3>
            <div class="share-buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_url); ?>" class="share-btn facebook" target="_blank" onclick="event.stopPropagation();">
                    🔵 Facebook
                </a>
                <a href="https://wa.me/?text=<?php echo urlencode('He recibido una insignia de ' . $insignia_data['nombre'] . ' del TecNM!!! ' . $validation_url); ?>" class="share-btn whatsapp" target="_blank" onclick="event.stopPropagation();">
                    💬 WhatsApp
                </a>
                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('He recibido una insignia de ' . $insignia_data['nombre'] . ' del TecNM!!!'); ?>&url=<?php echo urlencode($validation_url); ?>" class="share-btn twitter" target="_blank" onclick="event.stopPropagation();">
                    🐤 Twitter
                </a>
            </div>
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
