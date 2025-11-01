<?php
// Demostración de cómo se ve la insignia compartida en WhatsApp
// Esta página simula el comportamiento de WhatsApp cuando se comparte la insignia

$codigo_insignia = $_GET['codigo'] ?? 'TECNM-ITSM-2025-115';
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional';
$validation_url = $base_url . '/validacion.php?insignia=' . urlencode($codigo_insignia);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo: Insignia en WhatsApp - TecNM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #075e54;
            padding: 20px;
            min-height: 100vh;
        }
        
        .demo-container {
            max-width: 400px;
            margin: 0 auto;
            background: #ece5dd;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .demo-header {
            background: #25D366;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .demo-header h1 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        .demo-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .chat-container {
            background: #ece5dd;
            padding: 20px;
            min-height: 500px;
        }
        
        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }
        
        .message.sent {
            justify-content: flex-end;
        }
        
        .message.received {
            justify-content: flex-start;
        }
        
        .message-bubble {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
        }
        
        .message.sent .message-bubble {
            background: #dcf8c6;
            color: #303030;
            border-bottom-right-radius: 4px;
        }
        
        .message.received .message-bubble {
            background: white;
            color: #303030;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .message-text {
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 8px;
        }
        
        .message-time {
            font-size: 11px;
            color: #667781;
            text-align: right;
            margin-top: 4px;
        }
        
        .message.received .message-time {
            text-align: left;
        }
        
        .shared-link {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .shared-link:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .link-preview {
            display: flex;
            background: white;
        }
        
        .link-image {
            width: 120px;
            height: 120px;
            background-image: url('imagen/insignia_Responsabilidad Social.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            flex-shrink: 0;
        }
        
        .link-image::after {
            content: '👆 Toca para validar';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(37, 211, 102, 0.9);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .shared-link:hover .link-image::after {
            opacity: 1;
        }
        
        .link-content {
            padding: 12px;
            flex: 1;
        }
        
        .link-title {
            font-size: 14px;
            font-weight: bold;
            color: #303030;
            margin-bottom: 4px;
            line-height: 1.3;
        }
        
        .link-description {
            font-size: 12px;
            color: #667781;
            line-height: 1.3;
            margin-bottom: 6px;
        }
        
        .link-domain {
            font-size: 11px;
            color: #25D366;
            text-transform: uppercase;
            font-weight: 500;
        }
        
        .demo-instructions {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin: 20px;
        }
        
        .demo-instructions h3 {
            color: #155724;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .demo-instructions p {
            color: #155724;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .demo-instructions ul {
            color: #155724;
            padding-left: 20px;
            line-height: 1.6;
        }
        
        .demo-instructions li {
            margin-bottom: 5px;
        }
        
        .validation-link {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px;
            text-align: center;
        }
        
        .validation-link h4 {
            color: #1b396a;
            margin-bottom: 10px;
        }
        
        .validation-link a {
            color: #25D366;
            text-decoration: none;
            font-weight: 500;
        }
        
        .validation-link a:hover {
            text-decoration: underline;
        }
        
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 12px 16px;
            background: white;
            border-radius: 18px;
            border-bottom-left-radius: 4px;
            max-width: 80px;
            margin-bottom: 15px;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            background: #667781;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }
        
        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
                opacity: 0.4;
            }
            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }
        
        @media (max-width: 480px) {
            .demo-container {
                margin: 10px;
                max-width: none;
            }
            
            .link-preview {
                flex-direction: column;
            }
            
            .link-image {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1>💬 Demostración: Insignia en WhatsApp</h1>
            <p>Así se ve cuando compartes una insignia TecNM en WhatsApp</p>
        </div>
        
        <div class="chat-container">
            <div class="message received">
                <div class="message-bubble">
                    <div class="message-text">¡Hola! ¿Cómo estás?</div>
                    <div class="message-time">10:30</div>
                </div>
            </div>
            
            <div class="message sent">
                <div class="message-bubble">
                    <div class="message-text">¡Hola! Muy bien, gracias. Te comparto algo emocionante 🎖️</div>
                    <div class="message-time">10:31</div>
                </div>
            </div>
            
            <div class="message sent">
                <div class="message-bubble">
                    <div class="message-text">¡He recibido una insignia de Responsabilidad Social del TecNM!</div>
                    <div class="message-time">10:32</div>
                    
                    <a href="<?php echo $validation_url; ?>" class="shared-link" target="_blank">
                        <div class="link-preview">
                            <div class="link-image"></div>
                            <div class="link-content">
                                <div class="link-title">Insignia TecNM - Responsabilidad Social</div>
                                <div class="link-description">
                                    Victor Jonathan Castro Secundino ha recibido una insignia de Responsabilidad Social del Tecnológico Nacional de México. Toca la imagen para validar su autenticidad.
                                </div>
                                <div class="link-domain">insignias.tecnm.mx</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
            
            <div class="message received">
                <div class="message-bubble">
                    <div class="message-text">¡Wow! ¡Felicitaciones! 🎉</div>
                    <div class="message-time">10:33</div>
                </div>
            </div>
            
            <div class="message received">
                <div class="message-bubble">
                    <div class="message-text">Es increíble, voy a validar la insignia para ver todos los detalles</div>
                    <div class="message-time">10:33</div>
                </div>
            </div>
        </div>
        
        <div class="demo-instructions">
            <h3>💡 Cómo Funciona en WhatsApp</h3>
            <p><strong>Cuando compartes la insignia en WhatsApp:</strong></p>
            <ul>
                <li>La imagen aparece como un enlace clickeable en el chat</li>
                <li>Al tocar la imagen, se abre la página de validación oficial</li>
                <li>La validación se ve perfectamente en dispositivos móviles</li>
                <li>Se pueden ver todos los detalles de la insignia</li>
                <li>Se puede compartir fácilmente con otros contactos</li>
            </ul>
        </div>
        
        <div class="validation-link">
            <h4>🔗 Enlace de Validación:</h4>
            <a href="<?php echo $validation_url; ?>" target="_blank"><?php echo $validation_url; ?></a>
        </div>
    </div>
    
    <script>
        // Simular indicador de escritura
        setTimeout(() => {
            const typingIndicator = document.querySelector('.typing-indicator');
            if (typingIndicator) {
                typingIndicator.style.display = 'none';
            }
        }, 2000);
        
        // Efecto hover en el enlace compartido
        document.querySelector('.shared-link').addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
        });
        
        document.querySelector('.shared-link').addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
        
        // Simular clic en la imagen
        document.querySelector('.link-image').addEventListener('click', function(e) {
            e.preventDefault();
            window.open('<?php echo $validation_url; ?>', '_blank');
        });
    </script>
</body>
</html>
