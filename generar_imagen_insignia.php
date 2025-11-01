<?php
// Generar imagen de insignia clickeable para compartir
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
    <title>Compartir Insignia - <?php echo htmlspecialchars($insignia_data['nombre']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        
        .header {
            background: linear-gradient(135deg, #002855, #1b396a);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .insignia-container {
            position: relative;
            display: inline-block;
            margin: 20px 0;
        }
        
        .insignia-image {
            width: 300px;
            height: 300px;
            background-image: url('imagen/insignia_Responsabilidad Social.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .insignia-image:hover {
            transform: scale(1.05);
        }
        
        .click-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .insignia-container:hover .click-overlay {
            opacity: 1;
        }
        
        .click-text {
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .share-section {
            margin: 30px 0;
        }
        
        .share-title {
            font-size: 18px;
            font-weight: bold;
            color: #002855;
            margin-bottom: 20px;
        }
        
        .share-buttons {
            display: flex;
            gap: 15px;
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
        
        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .info-text {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
            color: #1565c0;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎖️ Compartir Insignia</h1>
        </div>
        
        <div class="insignia-container">
            <a href="validacion.php?insignia=<?php echo urlencode($codigo_insignia); ?>" target="_blank">
                <div class="insignia-image"></div>
                <div class="click-overlay">
                    <div class="click-text">👆 Haz clic para validar</div>
                </div>
            </a>
        </div>
        
        <div class="info-text">
            <strong>💡 Instrucciones:</strong><br>
            Haz clic en los botones de abajo para compartir solo la imagen de la insignia. 
            Cuando alguien haga clic en la imagen compartida, será redirigido a la página de validación.
        </div>
        
        <div class="share-section">
            <div class="share-title">Compartir en Redes Sociales</div>
            <div class="share-buttons">
                <a href="https://wa.me/?text=¡Mira mi insignia de <?php echo urlencode($insignia_data['nombre']); ?> del TecNM! Haz clic en la imagen para validarla: <?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia); ?>" 
                   class="share-btn whatsapp" target="_blank">
                    💬 WhatsApp
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia); ?>" 
                   class="share-btn facebook" target="_blank">
                    🔵 Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?text=¡Mira mi insignia de <?php echo urlencode($insignia_data['nombre']); ?> del TecNM! Haz clic para validarla&url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia); ?>" 
                   class="share-btn twitter" target="_blank">
                    🐤 Twitter
                </a>
            </div>
        </div>
        
        <div style="margin-top: 30px;">
            <div style="font-size: 14px; color: #666; margin-bottom: 15px;">
                <strong>URL de Validación:</strong>
            </div>
            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px; word-break: break-all;">
                <?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia; ?>
            </div>
        </div>
        
        <a href="ver_insignia_completa.php" class="back-btn">← Volver a la Insignia Completa</a>
    </div>
    
    <script>
        // Función para copiar URL al portapapeles
        function copyUrl() {
            const url = '<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional/validacion.php?insignia=' . $codigo_insignia; ?>';
            navigator.clipboard.writeText(url).then(() => {
                alert('URL copiada al portapapeles');
            });
        }
        
        // Agregar evento de clic para copiar URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlDiv = document.querySelector('div[style*="background: #f8f9fa"]');
            if (urlDiv) {
                urlDiv.style.cursor = 'pointer';
                urlDiv.title = 'Haz clic para copiar';
                urlDiv.addEventListener('click', copyUrl);
            }
        });
    </script>
</body>
</html>
