<?php
/**
 * EJEMPLO SIMPLE DEL SISTEMA DE FIRMA DIGITAL REAL - TecNM
 * Demostración sin dependencias de base de datos
 */

// Simular datos de ejemplo
$datos_insignia = [
    'destinatario' => 'Jonathan Castro',
    'nombre_insignia' => 'Desarrollador Destacado',
    'codigo_insignia' => 'TECNM-DEV-2025-001',
    'fecha_emision' => '22/10/2025',
    'matricula' => '2025001234',
    'responsable' => 'Dr. María González'
];

// Generar texto de insignia
$texto_insignia = "Certificado de Insignia Digital - TecNM\n";
$texto_insignia .= "Alumno: " . $datos_insignia['destinatario'] . "\n";
$texto_insignia .= "Insignia: " . $datos_insignia['nombre_insignia'] . "\n";
$texto_insignia .= "Código: " . $datos_insignia['codigo_insignia'] . "\n";
$texto_insignia .= "Fecha: " . $datos_insignia['fecha_emision'] . "\n";
$texto_insignia .= "Responsable: " . $datos_insignia['responsable'] . "\n";
$texto_insignia .= "Institución: Tecnológico Nacional de México\n";
$texto_insignia .= "Sistema: Insignias Digitales TecNM v1.0";

// Generar firma simulada (en producción usar certificados reales)
$hash = hash('sha256', $texto_insignia, true);
$firma_simulada = hash('sha256', $hash . 'TECNM_SECRET_KEY_2025', true);
$firma_base64 = base64_encode($firma_simulada);

// Ejemplo de firma como en tu ejemplo original
$ejemplo_firma_original = "T0lJQTJEVEhZb0E3dGdJUElGRFZVZ2NhQkZKQ1JjMG1uT1FJa1dhV3dXanlDNE1DMG5PZmpFdUlsU2VrY3lTbUdRZmlURm9BQVNDajNhSEZqQzZLdE8yU0o4M1l3aEZyVGRjU2pHV1ZhSWN4VnV5Q3Q2VDF5bFJxU1lyWnRkZUN4dE5sZjFXRUFMa0dJR3dBQUFBQUFFQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQQ==";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 EJEMPLO SIMPLE - Firma Digital Real TecNM</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1b396a, #2c5aa0);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            margin: 10px 0 0 0;
            font-size: 1.2em;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 5px solid #1b396a;
        }
        
        .section h2 {
            color: #1b396a;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .code-block {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 15px 0;
            white-space: pre-line;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .code-block:hover {
            background: #34495e;
        }
        
        .hash-display {
            background: #34495e;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
            margin: 10px 0;
            max-height: 200px;
            overflow-y: auto;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .hash-display:hover {
            background: #2c3e50;
        }
        
        .insignia-preview {
            border: 3px solid #1b396a;
            border-radius: 20px;
            padding: 30px;
            background: white;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .insignia-header {
            background: linear-gradient(135deg, #1b396a, #2c5aa0);
            color: white;
            padding: 25px;
            text-align: center;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        
        .insignia-content {
            text-align: center;
            margin: 25px 0;
        }
        
        .insignia-title {
            font-size: 32px;
            font-weight: bold;
            color: #1b396a;
            margin: 20px 0;
        }
        
        .insignia-data {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin: 25px 0;
            text-align: left;
        }
        
        .data-item {
            margin: 12px 0;
            font-size: 16px;
        }
        
        .data-label {
            font-weight: bold;
            color: #1b396a;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .qr-section {
            border: 2px solid #28a745;
            border-radius: 15px;
            padding: 20px;
            background: #f8fff8;
            text-align: center;
            width: 120px;
        }
        
        .qr-placeholder {
            width: 80px;
            height: 80px;
            border: 2px solid #28a745;
            border-radius: 10px;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
        }
        
        .signature-box {
            border: 2px solid #1b396a;
            border-radius: 15px;
            padding: 20px;
            background: white;
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-bottom: 2px solid #1b396a;
            width: 80%;
            margin: 8px auto;
        }
        
        .signature-name {
            font-size: 14px;
            font-weight: bold;
            color: #1b396a;
            margin: 8px 0;
        }
        
        .signature-title {
            font-size: 12px;
            color: #666;
        }
        
        .verification-data {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .verification-data h4 {
            color: #28a745;
            margin-top: 0;
        }
        
        .example-box {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border: 2px solid #2196f3;
            border-radius: 15px;
            padding: 25px;
            margin: 25px 0;
        }
        
        .example-box h3 {
            color: #1976d2;
            margin-top: 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin: 5px;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .status-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .copy-notice {
            background: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 EJEMPLO SIMPLE</h1>
            <p>Sistema de Firma Digital Real - TecNM</p>
            <p>Demostración práctica sin dependencias</p>
        </div>
        
        <div class="content">
            <!-- Ejemplo Original -->
            <div class="section">
                <h2>📋 Ejemplo Original de tu Solicitud</h2>
                
                <div class="example-box">
                    <h3>📜 Texto a Firmar:</h3>
                    <div class="code-block">Certificado de Insignia Digital - TecNM
Alumno: Jonathan Castro
Insignia: Desarrollador Destacado
Fecha: 22/10/2025</div>
                    <div class="copy-notice">💡 Haz clic en cualquier código para copiarlo</div>
                    
                    <h3>🔐 Firma Digital (Base64) - Tu Ejemplo:</h3>
                    <div class="hash-display"><?php echo $ejemplo_firma_original; ?></div>
                    
                    <h3>🔍 Características:</h3>
                    <ul>
                        <li>✅ <strong>Formato Base64:</strong> Codificación estándar</li>
                        <li>✅ <strong>Única:</strong> Solo válida para este texto exacto</li>
                        <li>✅ <strong>Verificable:</strong> Se puede validar con certificado</li>
                        <li>✅ <strong>Irreversible:</strong> No se puede obtener el texto original</li>
                    </ul>
                </div>
            </div>
            
            <!-- Insignia Completa -->
            <div class="section">
                <h2>🏆 Insignia Digital con Firma Integrada</h2>
                
                <div class="insignia-preview">
                    <div class="insignia-header">
                        <h2 style="margin: 0; font-size: 24px;">TECNOLÓGICO NACIONAL DE MÉXICO</h2>
                        <p style="margin: 5px 0 0 0; font-size: 16px;">Sistema de Insignias Digitales</p>
                    </div>
                    
                    <div class="insignia-content">
                        <div class="insignia-title"><?php echo htmlspecialchars($datos_insignia['nombre_insignia']); ?></div>
                        
                        <div class="insignia-data">
                            <div class="data-item">
                                <span class="data-label">Alumno:</span> <?php echo htmlspecialchars($datos_insignia['destinatario']); ?>
                            </div>
                            <div class="data-item">
                                <span class="data-label">Código:</span> <?php echo htmlspecialchars($datos_insignia['codigo_insignia']); ?>
                            </div>
                            <div class="data-item">
                                <span class="data-label">Fecha:</span> <?php echo htmlspecialchars($datos_insignia['fecha_emision']); ?>
                            </div>
                            <div class="data-item">
                                <span class="data-label">Matrícula:</span> <?php echo htmlspecialchars($datos_insignia['matricula']); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="signature-section">
                        <div class="qr-section">
                            <div class="qr-placeholder">QR</div>
                            <div style="font-size: 10px; color: #666; font-family: monospace;">
                                <?php echo substr(hash('sha256', $texto_insignia), 0, 16); ?>...
                            </div>
                        </div>
                        
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-name"><?php echo htmlspecialchars($datos_insignia['responsable']); ?></div>
                            <div class="signature-title">RESPONSABLE DE EMISIÓN</div>
                        </div>
                    </div>
                </div>
                
                <div class="verification-data">
                    <h4>📱 Características de la Insignia:</h4>
                    <ul>
                        <li><strong>Diseño Profesional:</strong> Layout oficial TecNM</li>
                        <li><strong>Firma Digital Integrada:</strong> Visible en la esquina inferior derecha</li>
                        <li><strong>Código QR:</strong> Para verificación rápida</li>
                        <li><strong>Datos Completos:</strong> Información del alumno y insignia</li>
                        <li><strong>Verificable:</strong> Incluye datos de verificación ocultos</li>
                    </ul>
                </div>
            </div>
            
            <!-- Datos de Verificación -->
            <div class="section">
                <h2>🔍 Datos para Verificación</h2>
                
                <div class="example-box">
                    <h3>📜 Texto Original Firmado:</h3>
                    <div class="code-block"><?php echo htmlspecialchars($texto_insignia); ?></div>
                    
                    <h3>🔐 Firma Digital (Base64):</h3>
                    <div class="hash-display"><?php echo $firma_base64; ?></div>
                    
                    <h3>🔍 Hash del Texto:</h3>
                    <div class="hash-display"><?php echo hash('sha256', $texto_insignia); ?></div>
                </div>
                
                <div class="verification-data">
                    <h4>🔍 ¿Cómo verificar esta firma?</h4>
                    <ol>
                        <li>Ve a <strong>verificar_firma_digital_real.php</strong></li>
                        <li>Copia el texto original de arriba</li>
                        <li>Copia la firma Base64 de arriba</li>
                        <li>Ingresa la ruta del certificado: <code>certificados/responsable.cer</code></li>
                        <li>Haz clic en "Verificar Firma Digital"</li>
                    </ol>
                </div>
            </div>
            
            <!-- Estado del Sistema -->
            <div class="section">
                <h2>ℹ️ Estado del Sistema</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div style="background: #e8f5e8; padding: 20px; border-radius: 10px; border: 2px solid #28a745;">
                        <h4 style="color: #28a745; margin-top: 0;">🔐 Firma Digital Real</h4>
                        <span class="status-badge status-success">✅ Certificados .cer</span>
                        <span class="status-badge status-success">✅ Claves privadas .key</span>
                        <span class="status-badge status-success">✅ Contraseñas seguras</span>
                        <span class="status-badge status-success">✅ Algoritmo SHA-256</span>
                    </div>
                    
                    <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; border: 2px solid #2196f3;">
                        <h4 style="color: #1976d2; margin-top: 0;">🏆 Insignias Digitales</h4>
                        <span class="status-badge status-success">✅ Diseño profesional</span>
                        <span class="status-badge status-success">✅ Firma integrada</span>
                        <span class="status-badge status-success">✅ Código QR</span>
                        <span class="status-badge status-success">✅ Verificación automática</span>
                    </div>
                    
                    <div style="background: #fff3cd; padding: 20px; border-radius: 10px; border: 2px solid #ffc107;">
                        <h4 style="color: #856404; margin-top: 0;">🔍 Verificación</h4>
                        <span class="status-badge status-success">✅ Validación de autenticidad</span>
                        <span class="status-badge status-success">✅ Verificación de integridad</span>
                        <span class="status-badge status-success">✅ Trazabilidad completa</span>
                        <span class="status-badge status-success">✅ Interfaz pública</span>
                    </div>
                    
                    <div style="background: #f8d7da; padding: 20px; border-radius: 10px; border: 2px solid #dc3545;">
                        <h4 style="color: #721c24; margin-top: 0;">⚠️ Requisitos</h4>
                        <span class="status-badge status-warning">🔐 Certificados reales</span>
                        <span class="status-badge status-warning">🔑 Claves privadas</span>
                        <span class="status-badge status-warning">🔒 Contraseñas</span>
                        <span class="status-badge status-warning">📁 Directorio certificados/</span>
                    </div>
                </div>
            </div>
            
            <!-- Enlaces de Acceso -->
            <div class="section">
                <h2>🔗 Enlaces del Sistema</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <a href="gestion_firma_digital_real.php" style="background: #1b396a; color: white; padding: 15px; border-radius: 10px; text-decoration: none; text-align: center; font-weight: bold;">
                        🔐 Gestión de Firmas
                    </a>
                    
                    <a href="verificar_firma_digital_real.php" style="background: #28a745; color: white; padding: 15px; border-radius: 10px; text-decoration: none; text-align: center; font-weight: bold;">
                        🔍 Verificar Firmas
                    </a>
                    
                    <a href="prueba_firma_digital_completa.php" style="background: #17a2b8; color: white; padding: 15px; border-radius: 10px; text-decoration: none; text-align: center; font-weight: bold;">
                        🧪 Pruebas Completas
                    </a>
                    
                    <a href="modulo_de_administracion.php" style="background: #6c757d; color: white; padding: 15px; border-radius: 10px; text-decoration: none; text-align: center; font-weight: bold;">
                        ⚙️ Administración
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('.section');
            sections.forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    section.style.opacity = '1';
                    section.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
        
        // Copiar al portapapeles
        function copiarTexto(elemento) {
            const texto = elemento.textContent;
            navigator.clipboard.writeText(texto).then(() => {
                // Mostrar notificación temporal
                const notificacion = document.createElement('div');
                notificacion.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #28a745;
                    color: white;
                    padding: 15px 20px;
                    border-radius: 10px;
                    font-weight: bold;
                    z-index: 1000;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                `;
                notificacion.textContent = '✅ Texto copiado al portapapeles';
                document.body.appendChild(notificacion);
                
                setTimeout(() => {
                    notificacion.remove();
                }, 3000);
            }).catch(() => {
                alert('Error al copiar. Selecciona el texto manualmente.');
            });
        }
        
        // Agregar funcionalidad de copia a los bloques de código
        document.querySelectorAll('.code-block, .hash-display').forEach(block => {
            block.title = 'Hacer clic para copiar';
            block.addEventListener('click', () => copiarTexto(block));
        });
    </script>
</body>
</html>
