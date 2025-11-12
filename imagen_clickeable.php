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
                        CASE 
                            WHEN io.Codigo_Insignia LIKE '%MOV%' OR io.Codigo_Insignia LIKE '%EMB%' OR io.Codigo_Insignia LIKE '%ART%' THEN 'Desarrollo Personal'
                            WHEN io.Codigo_Insignia LIKE '%FOR%' OR io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%CIE%' OR io.Codigo_Insignia LIKE '%INN%' THEN 'Desarrollo Académico'
                            WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Formación Integral'
                            ELSE 'Formación Integral'
                        END as categoria,
                        re.Nombre_Completo as responsable_nombre,
                        re.Cargo as responsable_cargo,
                        'Instituto Tecnológico de San Marcos' as institucion
                    FROM insigniasotorgadas io
                    LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
                    LEFT JOIN responsable_emision re ON io.Responsable_Emision = re." . $campo_id_responsable . "
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?></title>
    
    <!-- Meta tags para redes sociales -->
    <?php
    // Generar URLs públicas para Facebook
    $host = $_SERVER['HTTP_HOST'];
    
    // Si es localhost o IP local, usar ngrok para compartir
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, '192.168.') !== false) {
        // Verificar si hay una URL de ngrok configurada
        if (isset($_SESSION['ngrok_url']) && !empty($_SESSION['ngrok_url'])) {
            $base_url = rtrim($_SESSION['ngrok_url'], '/') . '/Insignias_TecNM_Funcional';
        } else {
            // Fallback a localtunnel si no hay ngrok configurado
            $base_url = 'https://bad-elephant-84.loca.lt/Insignias_TecNM_Funcional';
        }
    } else {
        $base_url = 'http://' . $host . '/Insignias_TecNM_Funcional';
    }
    
    $image_url = $base_url . '/' . (isset($insignia_data['imagen_path']) ? $insignia_data['imagen_path'] : 'imagen/insignia_Responsabilidad Social.png');
    $validation_url = $base_url . '/validacion.php?insignia=' . $codigo_insignia;
    ?>
    
    <meta property="og:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta property="og:description" content="Insignia de <?php echo htmlspecialchars($insignia_data['nombre']); ?> otorgada a <?php echo htmlspecialchars($insignia_data['destinatario']); ?>">
    <meta property="og:image" content="<?php echo $image_url; ?>">
    <meta property="og:url" content="<?php echo $validation_url; ?>">
    <meta property="og:type" content="website">
    
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
        <div class="insignia-image" style="background-image: url('<?php echo $insignia_data['imagen_path']; ?>');" onclick="window.open('validacion.php?insignia=<?php echo urlencode($codigo_insignia); ?>', '_blank')">
            <div class="click-indicator">👆 Haz clic para validar</div>
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
        </div>
        
        <div class="url-section">
            <div class="url-title">🔗 URL de Verificación:</div>
            <input type="text" class="url-input" id="verificationUrl" readonly>
            <button class="copy-btn" onclick="copyUrl()">Copiar URL</button>
        </div>
        
        <a href="ver_insignia_completa.php" class="back-link">← Volver a la insignia completa</a>
        
    </div>
    
    <!-- Librería QR Code -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <!-- Librería alternativa QR Code -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    
    <script>
        // La imagen ya se aplica directamente en el HTML
        
        // Función para obtener la URL base correcta
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
            
            const port = window.location.port || '80';
            return `${window.location.protocol}//${hostname}${port !== '80' ? ':' + port : ''}`;
        }
        
        // Generar código QR al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrl = getCorrectIP();
            const verificationUrl = `${baseUrl}/Insignias_TecNM_Funcional/validacion.php?insignia=<?php echo urlencode($codigo_insignia); ?>`;
            const canvas = document.getElementById('qrcode');
            
            console.log('URL de verificación:', verificationUrl);
            console.log('Canvas encontrado:', canvas);
            
            // Establecer la URL en el campo de entrada
            const urlInput = document.getElementById('verificationUrl');
            if (urlInput) {
                urlInput.value = verificationUrl;
            }
            
            // Generar QR dinámicamente
            generateQRCode(verificationUrl, canvas);
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
            const verificationUrl = document.getElementById('verificationUrl').value;
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(verificationUrl)}`;
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
            window.open('validacion.php?insignia=<?php echo urlencode($codigo_insignia); ?>', '_blank');
        });
    </script>
</body>
</html>