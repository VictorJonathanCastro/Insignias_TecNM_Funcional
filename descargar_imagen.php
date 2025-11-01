<?php
// Página que genera una imagen descargable con enlace embebido
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
    <title>Descargar Imagen de Insignia</title>
    
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
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #002855;
            margin-bottom: 20px;
        }
        
        .insignia-preview {
            width: 300px;
            height: 300px;
            background-image: url('imagen/insignia_Responsabilidad Social.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin: 0 auto 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .insignia-preview:hover {
            transform: scale(1.05);
        }
        
        .instructions {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .instructions h3 {
            margin: 0 0 10px 0;
            color: #155724;
            font-size: 18px;
        }
        
        .instructions p {
            margin: 0;
            color: #155724;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        
        .btn {
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
        
        .btn.download {
            background: #28a745;
        }
        
        .btn.whatsapp {
            background: #25D366;
        }
        
        .btn.facebook {
            background: #1877F2;
        }
        
        .btn.twitter {
            background: #1DA1F2;
        }
        
        .btn:hover {
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
            .container {
                padding: 20px;
            }
            
            .insignia-preview {
                width: 250px;
                height: 250px;
            }
            
            .buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 200px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">🎖️ Descargar Imagen de Insignia</div>
        
        <div class="insignia-preview" onclick="window.open('validacion.php?insignia=<?php echo urlencode($codigo_insignia); ?>', '_blank')"></div>
        
        <div class="instructions">
            <h3>📱 Instrucciones para Compartir</h3>
            <p>1. Descarga la imagen haciendo clic en "Descargar Imagen"<br>
            2. Comparte la imagen en WhatsApp, Facebook o Twitter<br>
            3. Cuando alguien haga clic en la imagen, será redirigido a la validación</p>
        </div>
        
        <div class="buttons">
            <button onclick="downloadImage()" class="btn download">
                📥 Descargar Imagen
            </button>
            <a href="javascript:void(0)" onclick="shareWhatsApp()" class="btn whatsapp">
                💬 WhatsApp
            </a>
            <a href="javascript:void(0)" onclick="shareFacebook()" class="btn facebook">
                🔵 Facebook
            </a>
            <a href="javascript:void(0)" onclick="shareTwitter()" class="btn twitter">
                🐤 Twitter
            </a>
        </div>
        
        <a href="ver_insignia_completa.php" class="back-link">← Volver a la insignia completa</a>
    </div>
    
    <script>
        // Función para descargar la imagen
        function downloadImage() {
            const imageUrl = 'imagen/insignia_Responsabilidad Social.png';
            const link = document.createElement('a');
            link.href = imageUrl;
            link.download = 'insignia_tecnm_<?php echo $codigo_insignia; ?>.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Función para compartir en WhatsApp
        function shareWhatsApp() {
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
