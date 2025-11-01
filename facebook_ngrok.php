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

// Detectar si es Facebook bot
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_facebook_bot = strpos($user_agent, 'facebookexternalhit') !== false;

// Generar URLs - usar ngrok o URL pública
$ngrok_url = $_GET['ngrok'] ?? '';
if (!empty($ngrok_url)) {
    $base_url = $ngrok_url . '/Insignias_TecNM_Funcional';
} else {
    $base_url = 'http://127.0.0.1/Insignias_TecNM_Funcional';
}

$validation_url = $base_url . '/validacion.php?insignia=' . urlencode($codigo_insignia);
$image_url = $base_url . '/imagen/insignia_Responsabilidad Social.png';
$current_url = $base_url . '/facebook_ngrok.php?codigo=' . urlencode($codigo_insignia) . (!empty($ngrok_url) ? '&ngrok=' . urlencode($ngrok_url) : '');
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
        
        .url-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #ddd;
        }
        
        .url-display input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .ngrok-success {
            background: #d4edda;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
            font-size: 14px;
        }
        
        .ngrok-warning {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="badge-image" onclick="redirectToCertificate()">
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
        
        <div class="url-display">
            <h3>📋 URL para compartir en Facebook:</h3>
            <input type="text" value="<?php echo $current_url; ?>" readonly>
            <p style="margin-top: 10px; font-size: 14px; color: #666;">
                Copia esta URL y pégala en Facebook. La imagen debería aparecer automáticamente.
            </p>
        </div>
        
        <?php if (!empty($ngrok_url)): ?>
        <div class="ngrok-success">
            <h4>✅ ngrok configurado correctamente</h4>
            <p>Facebook puede acceder a esta URL pública: <strong><?php echo $ngrok_url; ?></strong></p>
        </div>
        <?php else: ?>
        <div class="ngrok-warning">
            <h4>⚠️ Configuración requerida</h4>
            <p>Para que Facebook funcione, necesitas configurar ngrok:</p>
            <ol>
                <li>Descargar ngrok desde <a href="https://ngrok.com/" target="_blank">https://ngrok.com/</a></li>
                <li>Ejecutar: <code>ngrok http 80</code></li>
                <li>Usar la URL que genera ngrok en el parámetro: <code>?ngrok=https://abc123.ngrok.io</code></li>
            </ol>
        </div>
        <?php endif; ?>
        
        <div class="social-share">
            <h3>Compartir en redes sociales</h3>
            <div class="share-buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_url); ?>" class="share-btn facebook" target="_blank">
                    🔵 Facebook
                </a>
                <a href="https://wa.me/?text=<?php echo urlencode('He recibido una insignia de ' . $insignia_data['nombre'] . ' del TecNM!!! ' . $validation_url); ?>" class="share-btn whatsapp" target="_blank">
                    💬 WhatsApp
                </a>
                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('He recibido una insignia de ' . $insignia_data['nombre'] . ' del TecNM!!!'); ?>&url=<?php echo urlencode($validation_url); ?>" class="share-btn twitter" target="_blank">
                    🐤 Twitter
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($validation_url); ?>" class="share-btn linkedin" target="_blank">
                    💼 LinkedIn
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Función para redirigir al certificado
        function redirectToCertificate() {
            // Usar window.open en lugar de window.location para evitar bloqueos
            window.open('<?php echo $validation_url; ?>', '_blank');
        }
        
        // Solo redirigir si no es Facebook bot
        if (!<?php echo $is_facebook_bot ? 'true' : 'false'; ?>) {
            // Redirigir automáticamente al certificado después de 5 segundos
            setTimeout(function() {
                // Usar window.open en lugar de window.location para evitar bloqueos
                window.open('<?php echo $validation_url; ?>', '_blank');
            }, 5000);
        }
        
        // Hacer que toda la página sea clickeable
        document.addEventListener('click', function(e) {
            if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'INPUT') {
                redirectToCertificate();
            }
        });
    </script>
</body>
</html>
