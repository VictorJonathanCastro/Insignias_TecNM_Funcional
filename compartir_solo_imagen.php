<?php
// Página ultra-simple que muestra solo la imagen clickeable
session_start();

// Verificar que hay datos de insignia
if (!isset($_SESSION['insignia_data'])) {
    header('Location: metadatos_formulario.php');
    exit();
}

$insignia_data = $_SESSION['insignia_data'];
$codigo_insignia = $insignia_data['codigo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM</title>
    
    <!-- Meta tags para redes sociales -->
    <meta property="og:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?>">
    <meta property="og:description" content="Insignia de <?php echo htmlspecialchars($insignia_data['nombre']); ?> otorgada a <?php echo htmlspecialchars($insignia_data['destinatario']); ?>">
    <meta property="og:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/imagen/insignia_Responsabilidad Social.png'; ?>">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia; ?>">
    <meta property="og:type" content="website">
    
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .main-image {
            width: 350px;
            height: 350px;
            background-image: url('imagen/insignia_Responsabilidad Social.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        
        .main-image:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
        }
        
        .share-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        
        .share-title {
            font-size: 20px;
            font-weight: bold;
            color: #002855;
            margin-bottom: 15px;
        }
        
        .share-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .share-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .share-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
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
        
        .share-btn.linkedin {
            background: #0077B5;
        }
        
        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
            .main-image {
                width: 280px;
                height: 280px;
            }
            
            .share-section {
                margin: 0 10px;
                padding: 20px;
            }
            
            .share-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .share-btn {
                width: 100%;
                max-width: 200px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="main-image" onclick="window.open('validacion.php?insignia=<?php echo urlencode($codigo_insignia); ?>', '_blank')"></div>
    
    <div class="share-section">
        <div class="share-title">📱 Compartir Imagen</div>
        <div class="share-subtitle">Haz clic en los botones para compartir esta imagen</div>
        
        <div class="share-buttons">
            <a href="javascript:void(0)" onclick="shareImageOnly()" class="share-btn whatsapp">
                💬 WhatsApp
            </a>
            <a href="javascript:void(0)" onclick="shareFacebook()" class="share-btn facebook">
                🔵 Facebook
            </a>
            <a href="javascript:void(0)" onclick="shareTwitter()" class="share-btn twitter">
                🐤 Twitter
            </a>
            <a href="javascript:void(0)" onclick="shareLinkedIn()" class="share-btn linkedin">
                💼 LinkedIn
            </a>
        </div>
        
        <a href="ver_insignia_completa.php" class="back-link">← Volver</a>
    </div>
    
    <script>
        // Función para compartir solo la imagen (sin texto)
        function shareImageOnly() {
            const imageUrl = '<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/imagen/insignia_Responsabilidad Social.png'; ?>';
            const validationUrl = '<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia; ?>';
            
            // Intentar usar la API de compartir nativa
            if (navigator.share) {
                navigator.share({
                    title: 'Insignia TecNM',
                    text: 'Mira mi insignia del TecNM',
                    url: validationUrl
                }).catch(err => {
                    console.log('Error sharing:', err);
                    // Fallback a WhatsApp
                    fallbackShare();
                });
            } else {
                // Fallback para navegadores que no soportan navigator.share
                fallbackShare();
            }
        }
        
        function fallbackShare() {
            const validationUrl = '<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia; ?>';
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent('🎖️ Insignia TecNM - Haz clic para validar: ' + validationUrl)}`;
            window.open(whatsappUrl, '_blank');
        }
        
        // Función para compartir en Facebook
        function shareFacebook() {
            const validationUrl = '<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia; ?>';
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(validationUrl)}`;
            window.open(facebookUrl, '_blank');
        }
        
        // Función para compartir en Twitter
        function shareTwitter() {
            const validationUrl = '<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia; ?>';
            const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent('🎖️ Insignia TecNM')}&url=${encodeURIComponent(validationUrl)}`;
            window.open(twitterUrl, '_blank');
        }
        
        // Función para compartir en LinkedIn
        function shareLinkedIn() {
            const validationUrl = '<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia; ?>';
            const linkedinUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(validationUrl)}`;
            window.open(linkedinUrl, '_blank');
        }
        
        // Función para detectar dispositivo móvil
        function isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }
        
        // Optimizar experiencia móvil
        if (isMobile()) {
            document.body.style.padding = '10px';
        }
    </script>
</body>
</html>
