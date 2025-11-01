<?php
// Página ultra-simple para compartir solo la imagen de la insignia
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
            padding: 0;
            background: #f5f5f5;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        
        .insignia-display {
            width: 300px;
            height: 300px;
            background-image: url('imagen/insignia_Responsabilidad Social.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin: 0 auto 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .insignia-display:hover {
            transform: scale(1.05);
        }
        
        .share-title {
            font-size: 24px;
            font-weight: bold;
            color: #002855;
            margin-bottom: 20px;
        }
        
        .share-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .share-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
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
            min-width: 140px;
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
        
        .info-box {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #155724;
            font-size: 18px;
        }
        
        .info-box p {
            margin: 0;
            color: #155724;
            font-size: 14px;
        }
        
        .back-link {
            display: inline-block;
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
            
            .insignia-display {
                width: 250px;
                height: 250px;
            }
            
            .share-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .share-btn {
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="share-title">🎖️ Compartir Insignia TecNM</div>
        <div class="share-subtitle">Haz clic en los botones para compartir esta insignia</div>
        
        <div class="insignia-display" onclick="window.open('validacion.php?insignia=<?php echo urlencode($codigo_insignia); ?>', '_blank')"></div>
        
        <div class="info-box">
            <h3>✅ Insignia Válida</h3>
            <p>Esta insignia ha sido verificada y es auténtica. Al compartirla, incluirá un enlace para validar su autenticidad.</p>
        </div>
        
        <div class="share-buttons">
            <a href="https://wa.me/?text=🎖️ *Insignia TecNM*%0A%0A*<?php echo urlencode($insignia_data['nombre']); ?>*%0A%0AOtorgada a: <?php echo urlencode($insignia_data['destinatario']); ?>%0A%0A✅ *INSIGNIA VÁLIDA*%0AEsta insignia ha sido verificada y es auténtica%0A%0AHaz clic aquí para validar: <?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia); ?>" 
               class="share-btn whatsapp" target="_blank">
                💬 WhatsApp
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia); ?>" 
               class="share-btn facebook" target="_blank">
                🔵 Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?text=🎖️ Insignia TecNM - <?php echo urlencode($insignia_data['nombre']); ?> otorgada a <?php echo urlencode($insignia_data['destinatario']); ?> ✅ VÁLIDA&url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia); ?>" 
               class="share-btn twitter" target="_blank">
                🐤 Twitter
            </a>
        </div>
        
        <a href="ver_insignia_completa.php" class="back-link">← Volver a la insignia completa</a>
    </div>
    
    <script>
        // Función para detectar dispositivo móvil
        function isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }
        
        // Optimizar experiencia móvil
        if (isMobile()) {
            document.body.style.padding = '10px';
        }
        
        // Agregar efecto de clic a la imagen
        document.querySelector('.insignia-display').addEventListener('click', function() {
            window.open('validacion.php?insignia=<?php echo urlencode($codigo_insignia); ?>', '_blank');
        });
    </script>
</body>
</html>
