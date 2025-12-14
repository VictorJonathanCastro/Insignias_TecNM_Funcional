<?php
// Demostración de cómo se ve la insignia compartida en Facebook
// Esta página simula el comportamiento de Facebook cuando se comparte la insignia

$codigo_insignia = $_GET['codigo'] ?? 'TECNM-ITSM-2025-115';
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Insignias_TecNM_Funcional';
$validation_url = $base_url . '/validacion.php?insignia=' . urlencode($codigo_insignia);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo: Insignia en Facebook - TecNM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding: 20px;
            min-height: 100vh;
        }
        
        .demo-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .demo-header {
            background: #1877F2;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .demo-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .demo-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .facebook-post {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            margin: 20px;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .post-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e1e5e9;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .profile-pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1b396a, #002855);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        .profile-info h3 {
            font-size: 16px;
            color: #050505;
            margin-bottom: 2px;
        }
        
        .profile-info p {
            font-size: 12px;
            color: #65676b;
        }
        
        .post-content {
            padding: 15px 20px;
        }
        
        .post-text {
            font-size: 16px;
            color: #050505;
            line-height: 1.4;
            margin-bottom: 15px;
        }
        
        .shared-link {
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .shared-link:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .link-preview {
            display: flex;
            background: white;
        }
        
        .link-image {
            width: 200px;
            height: 200px;
            background-image: url('imagen/insignia_Responsabilidad Social.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            flex-shrink: 0;
        }
        
        .link-image::after {
            content: '👆 Haz clic para validar';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(27, 57, 106, 0.9);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .shared-link:hover .link-image::after {
            opacity: 1;
        }
        
        .link-content {
            padding: 20px;
            flex: 1;
        }
        
        .link-title {
            font-size: 18px;
            font-weight: bold;
            color: #050505;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .link-description {
            font-size: 14px;
            color: #65676b;
            line-height: 1.4;
            margin-bottom: 10px;
        }
        
        .link-domain {
            font-size: 12px;
            color: #65676b;
            text-transform: uppercase;
            font-weight: 500;
        }
        
        .post-actions {
            padding: 10px 20px;
            border-top: 1px solid #e1e5e9;
            display: flex;
            gap: 20px;
        }
        
        .action-btn {
            background: none;
            border: none;
            color: #65676b;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-btn:hover {
            background: #f0f2f5;
        }
        
        .action-btn.liked {
            color: #1877F2;
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
            color: #1877F2;
            text-decoration: none;
            font-weight: 500;
        }
        
        .validation-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .link-preview {
                flex-direction: column;
            }
            
            .link-image {
                width: 100%;
                height: 250px;
            }
            
            .post-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1>🔵 Demostración: Insignia en Facebook</h1>
            <p>Así se ve cuando compartes una insignia TecNM en Facebook</p>
        </div>
        
        <div class="facebook-post">
            <div class="post-header">
                <div class="profile-pic">CE</div>
                <div class="profile-info">
                    <h3>Carlos Espinosa Kattz</h3>
                    <p>21 de agosto a las 6:28 a. m.</p>
                </div>
            </div>
            
            <div class="post-content">
                <div class="post-text">
                    ¡He recibido una insignia de Responsabilidad Social del TecNM!!! 🎖️
                </div>
                
                <a href="<?php echo $validation_url; ?>" class="shared-link" target="_blank">
                    <div class="link-preview">
                        <div class="link-image"></div>
                        <div class="link-content">
                            <div class="link-title">Insignia TecNM - Responsabilidad Social</div>
                            <div class="link-description">
                                Victor Jonathan Castro Secundino ha recibido una insignia de Responsabilidad Social del Tecnológico Nacional de México. 
                                Haz clic en la imagen para validar su autenticidad y ver todos los detalles del reconocimiento.
                            </div>
                            <div class="link-domain">insignias.tecnm.mx</div>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="post-actions">
                <button class="action-btn liked">
                    👍 Me gusta
                </button>
                <button class="action-btn">
                    💬 Comentar
                </button>
                <button class="action-btn">
                    🔄 Compartir
                </button>
            </div>
        </div>
        
        <div class="demo-instructions">
            <h3>💡 Cómo Funciona</h3>
            <p><strong>Cuando compartes la insignia en Facebook:</strong></p>
            <ul>
                <li>La imagen de la insignia aparece como un enlace clickeable</li>
                <li>Al hacer clic en la imagen, se abre la página de validación oficial</li>
                <li>La validación muestra todos los detalles de la insignia</li>
                <li>Se confirma la autenticidad del reconocimiento</li>
                <li>Se puede imprimir el certificado oficial</li>
            </ul>
        </div>
        
        <div class="validation-link">
            <h4>🔗 Enlace de Validación:</h4>
            <a href="<?php echo $validation_url; ?>" target="_blank"><?php echo $validation_url; ?></a>
        </div>
    </div>
    
    <script>
        // Simular interacciones de Facebook
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.textContent.includes('Me gusta')) {
                    this.classList.toggle('liked');
                    if (this.classList.contains('liked')) {
                        this.innerHTML = '👍 Me gusta';
                    } else {
                        this.innerHTML = '👍 Me gusta';
                    }
                }
            });
        });
        
        // Efecto hover en el enlace compartido
        document.querySelector('.shared-link').addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        document.querySelector('.shared-link').addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    </script>
</body>
</html>
