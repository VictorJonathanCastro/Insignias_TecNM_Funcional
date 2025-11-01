<?php
// Configurador de ngrok para Facebook
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar ngrok para Facebook - TecNM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .step {
            background: #e8f5e8;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        .step h3 {
            color: #155724;
            margin-top: 0;
        }
        .code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            border: 1px solid #dee2e6;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
        }
        .success {
            background: #d4edda;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
            margin: 15px 0;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .url-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px 0;
        }
        .test-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Configurar ngrok para Facebook</h1>
        
        <div class="warning">
            <h3>⚠️ Problema Actual</h3>
            <p>Facebook no puede acceder a URLs locales (localhost). Necesitas crear un túnel público para que Facebook pueda mostrar las imágenes cuando compartas.</p>
        </div>

        <div class="step">
            <h3>📥 Paso 1: Descargar ngrok</h3>
            <p>Descarga ngrok desde: <a href="https://ngrok.com/download" target="_blank">https://ngrok.com/download</a></p>
            <p>O instala con chocolatey: <code>choco install ngrok</code></p>
        </div>

        <div class="step">
            <h3>🔑 Paso 2: Crear cuenta y obtener token</h3>
            <p>1. Ve a <a href="https://dashboard.ngrok.com/signup" target="_blank">https://dashboard.ngrok.com/signup</a></p>
            <p>2. Crea una cuenta gratuita</p>
            <p>3. Copia tu token de autenticación</p>
            <p>4. Ejecuta: <code>ngrok config add-authtoken TU_TOKEN</code></p>
        </div>

        <div class="step">
            <h3>🚀 Paso 3: Ejecutar ngrok</h3>
            <p>Ejecuta este comando en tu terminal:</p>
            <div class="code">
                ngrok http 80
            </div>
            <p>Esto creará un túnel público hacia tu servidor local.</p>
        </div>

        <div class="step">
            <h3>📋 Paso 4: Configurar URL pública</h3>
            <p>Una vez que ngrok esté ejecutándose, copia la URL HTTPS que aparece (algo como: <code>https://abc123.ngrok.io</code>)</p>
            
            <form method="post" action="">
                <label for="ngrok_url">URL de ngrok:</label>
                <input type="text" id="ngrok_url" name="ngrok_url" class="url-input" 
                       placeholder="https://abc123.ngrok.io" required>
                <button type="submit" class="btn btn-success">💾 Guardar Configuración</button>
            </form>
        </div>

        <?php
        if ($_POST['ngrok_url']) {
            $ngrok_url = rtrim($_POST['ngrok_url'], '/');
            $_SESSION['ngrok_url'] = $ngrok_url;
            echo '<div class="success">';
            echo '<h3>✅ Configuración guardada</h3>';
            echo '<p>URL de ngrok configurada: <strong>' . htmlspecialchars($ngrok_url) . '</strong></p>';
            echo '</div>';
        }

        if (isset($_SESSION['ngrok_url'])) {
            $ngrok_url = $_SESSION['ngrok_url'];
            ?>
            <div class="test-section">
                <h3>🧪 Probar Configuración</h3>
                <p>URL configurada: <strong><?php echo htmlspecialchars($ngrok_url); ?></strong></p>
                
                <h4>🔗 Enlaces de prueba:</h4>
                <ul>
                    <li><a href="<?php echo $ngrok_url; ?>/Insignias_TecNM_Funcional/imagen_clickeable.php?codigo=TECNM-ITSM-2025-ART-308" target="_blank">
                        Probar página de insignia
                    </a></li>
                    <li><a href="<?php echo $ngrok_url; ?>/Insignias_TecNM_Funcional/validacion.php?insignia=TECNM-ITSM-2025-ART-308" target="_blank">
                        Probar validación
                    </a></li>
                </ul>

                <h4>📱 Probar Facebook:</h4>
                <p>1. Copia este enlace:</p>
                <input type="text" class="url-input" value="<?php echo $ngrok_url; ?>/Insignias_TecNM_Funcional/imagen_clickeable.php?codigo=TECNM-ITSM-2025-ART-308" readonly>
                <button onclick="copyToClipboard(this.previousElementSibling.value)" class="btn">📋 Copiar</button>
                
                <p>2. Ve a <a href="https://www.facebook.com" target="_blank">Facebook</a> y pega el enlace</p>
                <p>3. Facebook debería mostrar la imagen de la insignia automáticamente</p>
                
                <h4>🔍 Verificar Meta Tags:</h4>
                <p>Usa estas herramientas para verificar que los meta tags funcionan:</p>
                <ul>
                    <li><a href="https://developers.facebook.com/tools/debug/" target="_blank">Facebook Debugger</a></li>
                    <li><a href="https://cards-dev.twitter.com/validator" target="_blank">Twitter Card Validator</a></li>
                </ul>
            </div>
            <?php
        }
        ?>

        <div class="step">
            <h3>🔄 Alternativas a ngrok</h3>
            <p>Si ngrok no funciona, puedes usar:</p>
            <ul>
                <li><strong>localtunnel:</strong> <code>npx localtunnel --port 80</code></li>
                <li><strong>serveo:</strong> <code>ssh -R 80:localhost:80 serveo.net</code></li>
                <li><strong>cloudflare tunnel:</strong> Para uso más avanzado</li>
            </ul>
        </div>

        <div class="step">
            <h3>📝 Notas Importantes</h3>
            <ul>
                <li>La URL de ngrok cambia cada vez que reinicias ngrok (en la versión gratuita)</li>
                <li>Para URLs permanentes, necesitas la versión de pago de ngrok</li>
                <li>Asegúrate de que tu servidor web esté ejecutándose en el puerto 80</li>
                <li>Facebook necesita acceso HTTPS para funcionar correctamente</li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="imagen_clickeable.php?codigo=TECNM-ITSM-2025-ART-308" class="btn">🔙 Volver a la Insignia</a>
            <a href="index.php" class="btn">🏠 Ir al Inicio</a>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('URL copiada al portapapeles');
            });
        }
    </script>
</body>
</html>
