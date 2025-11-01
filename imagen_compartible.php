<?php
// Página que genera una imagen de insignia clickeable para compartir en redes sociales
session_start();

// Obtener código de insignia desde GET o sesión
$codigo_insignia = $_GET['codigo'] ?? '';

if (!empty($codigo_insignia)) {
    // Modo público: obtener datos de la base de datos
    require_once 'conexion.php';
    
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
                // Convertir datos de BD al formato de sesión (igual que validacion.php)
                $insignia_data = [
                    'codigo' => $row['codigo_insignia'],
                    'nombre' => $row['nombre_insignia'],
                    'categoria' => $row['nombre_categoria'],
                    'destinatario' => $row['destinatario'],
                    'descripcion' => $row['descripcion'],
                    'criterios' => "Para obtener esta insignia de " . $row['nombre_insignia'] . ", el estudiante debe haber demostrado competencias específicas.",
                    'fecha_emision' => $row['fecha_emision'],
                    'emisor' => 'TecNM / ' . $row['nombre_instituto'],
                    'evidencia' => $row['evidencia'],
                    'archivo_visual' => "Insig_" . $row['codigo_insignia'] . ".jpg",
                    'responsable' => $row['responsable_nombre'],
                    'codigo_responsable' => 'TecNM-ITSM-2025-Resp001',
                    'estatus' => 'Autorizado',
                    'periodo' => '2025-1'
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
} else {
    // Modo con sesión: verificar sesión y datos de insignia
    if (!isset($_SESSION['insignia_data'])) {
        header('Location: metadatos_formulario.php');
        exit();
    }
    
    $insignia_data = $_SESSION['insignia_data'];
    $codigo_insignia = $insignia_data['codigo'];
}

// Generar URL de validación
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional';
$validation_url = $base_url . '/validacion.php?insignia=' . urlencode($codigo_insignia);
$image_url = $base_url . '/imagen/insignia_Responsabilidad Social.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?></title>
    
    <!-- Meta tags para redes sociales -->
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
    
    <!-- Meta tags adicionales para mejor compatibilidad -->
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .share-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            padding: 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .share-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #1b396a, #002855, #d4af37);
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #1b396a;
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .header p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }
        
        .insignia-display {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            position: relative;
            border: 3px solid #1b396a;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .insignia-image {
            width: 300px;
            height: 300px;
            background-image: url('imagen/insignia_Responsabilidad Social.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin: 0 auto 20px;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .insignia-image:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }
        
        .click-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(27, 57, 106, 0.9);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .insignia-image:hover .click-overlay {
            opacity: 1;
        }
        
        .click-text {
            color: white;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            border: 2px solid white;
            border-radius: 10px;
            background: rgba(0,0,0,0.3);
        }
        
        .insignia-info {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            border-left: 5px solid #1b396a;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .insignia-info h3 {
            color: #1b396a;
            font-size: 20px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            text-align: left;
        }
        
        .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #1b396a;
            font-size: 14px;
            font-weight: 500;
        }
        
        .share-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }
        
        .share-btn {
            padding: 15px 20px;
            border: none;
            border-radius: 12px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .share-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .share-btn:hover::before {
            left: 100%;
        }
        
        .share-btn.whatsapp {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }
        
        .share-btn.facebook {
            background: linear-gradient(135deg, #1877F2, #0A5BC4);
        }
        
        .share-btn.twitter {
            background: linear-gradient(135deg, #1DA1F2, #0D8BD9);
        }
        
        .share-btn.copy {
            background: linear-gradient(135deg, #6c757d, #495057);
        }
        
        .share-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .validation-url {
            background: #f8f9fa;
            border: 2px solid #1b396a;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .validation-url h4 {
            color: #1b396a;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .url-display {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            font-size: 12px;
            word-break: break-all;
            color: #666;
            margin-bottom: 10px;
        }
        
        .copy-url-btn {
            background: #1b396a;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.3s ease;
        }
        
        .copy-url-btn:hover {
            background: #002855;
        }
        
        .instructions {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .instructions h4 {
            color: #155724;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .instructions p {
            color: #155724;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 10px;
        }
        
        .instructions ul {
            color: #155724;
            font-size: 14px;
            padding-left: 20px;
            line-height: 1.5;
        }
        
        .instructions li {
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .share-container {
                padding: 20px;
                margin: 10px;
            }
            
            .insignia-image {
                width: 250px;
                height: 250px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .share-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="share-container">
        <div class="header">
            <h1>🎖️ Compartir Insignia TecNM</h1>
            <p>Haz clic en la imagen para validar su autenticidad</p>
        </div>
        
        <div class="insignia-display">
            <div class="insignia-image" onclick="window.open('<?php echo $validation_url; ?>', '_blank')">
                <div class="click-overlay">
                    <div class="click-text">
                        👆 Haz clic para validar<br>
                        <small>Verificar autenticidad</small>
                    </div>
                </div>
            </div>
            
            <div class="insignia-info">
                <h3>📋 Información de la Insignia</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Código</div>
                        <div class="info-value"><?php echo htmlspecialchars($insignia_data['codigo']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nombre</div>
                        <div class="info-value"><?php echo htmlspecialchars($insignia_data['nombre']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Categoría</div>
                        <div class="info-value"><?php echo htmlspecialchars($insignia_data['categoria']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Destinatario</div>
                        <div class="info-value"><?php echo htmlspecialchars($insignia_data['destinatario']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="instructions">
            <h4>💡 Instrucciones para Compartir</h4>
            <p><strong>Esta imagen es clickeable y lleva a la validación oficial:</strong></p>
            <ul>
                <li>Haz clic en cualquier botón de compartir de abajo</li>
                <li>La imagen se compartirá con un enlace de validación</li>
                <li>Cuando alguien haga clic en la imagen compartida, será redirigido a la página de validación oficial</li>
                <li>La validación mostrará todos los detalles de la insignia y confirmará su autenticidad</li>
            </ul>
        </div>
        
        <div class="share-buttons">
            <a href="javascript:void(0)" onclick="shareWhatsApp()" class="share-btn whatsapp">
                💬 WhatsApp
            </a>
            <a href="javascript:void(0)" onclick="shareFacebook()" class="share-btn facebook">
                🔵 Facebook
            </a>
            <a href="javascript:void(0)" onclick="shareTwitter()" class="share-btn twitter">
                🐤 Twitter
            </a>
            <a href="javascript:void(0)" onclick="copyLink()" class="share-btn copy">
                📋 Copiar Enlace
            </a>
        </div>
        
        <div class="validation-url">
            <h4>🔗 URL de Validación:</h4>
            <div class="url-display" id="validationUrl"><?php echo $validation_url; ?></div>
            <button class="copy-url-btn" onclick="copyValidationUrl()">Copiar URL</button>
        </div>
    </div>
    
    <script>
        // Función para compartir en WhatsApp
        function shareWhatsApp() {
            const validationUrl = document.getElementById('validationUrl').textContent;
            const message = `🎖️ Insignia TecNM - ${<?php echo json_encode($insignia_data['nombre']); ?>}

${<?php echo json_encode($insignia_data['destinatario']); ?>} ha recibido una insignia de ${<?php echo json_encode($insignia_data['nombre']); ?>} del TecNM.

Haz clic en la imagen para validar su autenticidad:
${validationUrl}`;
            
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
        
        // Función para compartir en Facebook
        function shareFacebook() {
            const validationUrl = document.getElementById('validationUrl').textContent;
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(validationUrl)}`;
            window.open(facebookUrl, '_blank');
        }
        
        // Función para compartir en Twitter
        function shareTwitter() {
            const validationUrl = document.getElementById('validationUrl').textContent;
            const message = `🎖️ Insignia TecNM - ${<?php echo json_encode($insignia_data['nombre']); ?>}

${<?php echo json_encode($insignia_data['destinatario']); ?>} ha recibido una insignia del TecNM. Haz clic para validar:`;
            
            const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(validationUrl)}`;
            window.open(twitterUrl, '_blank');
        }
        
        // Función para copiar enlace
        function copyLink() {
            const validationUrl = document.getElementById('validationUrl').textContent;
            navigator.clipboard.writeText(validationUrl).then(function() {
                alert('Enlace de validación copiado al portapapeles');
            }).catch(function() {
                // Fallback para navegadores que no soportan clipboard API
                const textArea = document.createElement('textarea');
                textArea.value = validationUrl;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Enlace de validación copiado al portapapeles');
            });
        }
        
        // Función para copiar URL de validación
        function copyValidationUrl() {
            const validationUrl = document.getElementById('validationUrl').textContent;
            navigator.clipboard.writeText(validationUrl).then(function() {
                const btn = document.querySelector('.copy-url-btn');
                const originalText = btn.textContent;
                btn.textContent = '¡Copiado!';
                btn.style.background = '#28a745';
                
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = '#1b396a';
                }, 2000);
            }).catch(function() {
                // Fallback
                const textArea = document.createElement('textarea');
                textArea.value = validationUrl;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('URL copiada al portapapeles');
            });
        }
        
        // Agregar evento de clic a la imagen
        document.querySelector('.insignia-image').addEventListener('click', function() {
            window.open('<?php echo $validation_url; ?>', '_blank');
        });
        
        // Efecto de hover en la imagen
        document.querySelector('.insignia-image').addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        document.querySelector('.insignia-image').addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    </script>
</body>
</html>
