<?php
require_once 'conexion.php';
require_once 'firma_digital_real.php';
require_once 'verificar_sesion.php';

// Verificar sesión de administrador
verificarRoles(['Admin', 'SuperUsuario']);

$firma_digital = inicializarFirmaDigitalReal($conexion);
$mensaje = '';
$resultado_firma = null;

// Procesar acciones
if ($_POST) {
    if (isset($_POST['generar_firma_real'])) {
        $nombre_responsable = $_POST['nombre_responsable'] ?? '';
        $cargo_responsable = $_POST['cargo_responsable'] ?? 'RESPONSABLE DE EMISIÓN';
        $certificado_path = $_POST['certificado_path'] ?? '';
        $clave_privada_path = $_POST['clave_privada_path'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
        
        if ($nombre_responsable && $certificado_path && $clave_privada_path && $contrasena) {
            // Generar texto de ejemplo para firmar
            $texto_ejemplo = "Certificado de Insignia Digital - TecNM\n";
            $texto_ejemplo .= "Responsable: " . $nombre_responsable . "\n";
            $texto_ejemplo .= "Cargo: " . $cargo_responsable . "\n";
            $texto_ejemplo .= "Fecha: " . date('d/m/Y H:i:s') . "\n";
            $texto_ejemplo .= "Institución: Tecnológico Nacional de México";
            
            // Generar firma digital real
            $resultado_firma = $firma_digital->generarFirmaDigitalReal(
                $texto_ejemplo,
                $certificado_path,
                $clave_privada_path,
                $contrasena
            );
            
            if ($resultado_firma['success']) {
                // Guardar en base de datos
                $datos_guardar = [
                    'nombre_responsable' => $nombre_responsable,
                    'cargo_responsable' => $cargo_responsable,
                    'firma_base64' => $resultado_firma['firma_base64'],
                    'certificado_path' => $certificado_path,
                    'fecha_generacion' => date('Y-m-d H:i:s')
                ];
                
                $guardar_resultado = $firma_digital->guardarFirmaEnBD($datos_guardar);
                
                if ($guardar_resultado['success']) {
                    $mensaje = "✅ Firma digital generada exitosamente para: " . $nombre_responsable;
                } else {
                    $mensaje = "⚠️ Firma generada pero error al guardar: " . $guardar_resultado['error'];
                }
            } else {
                $mensaje = "❌ Error al generar firma: " . $resultado_firma['error'];
            }
        } else {
            $mensaje = "❌ Por favor completa todos los campos obligatorios";
        }
    }
    
    if (isset($_POST['verificar_firma_real'])) {
        $texto_original = $_POST['texto_original'] ?? '';
        $firma_base64 = $_POST['firma_base64'] ?? '';
        $certificado_path = $_POST['certificado_path_verificar'] ?? '';
        
        if ($texto_original && $firma_base64 && $certificado_path) {
            $resultado_verificacion = $firma_digital->verificarFirmaDigital(
                $texto_original,
                $firma_base64,
                $certificado_path
            );
            
            if ($resultado_verificacion['success']) {
                if ($resultado_verificacion['valida']) {
                    $mensaje = "✅ FIRMA VÁLIDA: " . $resultado_verificacion['mensaje'];
                } else {
                    $mensaje = "❌ FIRMA INVÁLIDA: " . $resultado_verificacion['mensaje'];
                }
            } else {
                $mensaje = "❌ Error en verificación: " . $resultado_verificacion['error'];
            }
        } else {
            $mensaje = "❌ Por favor completa todos los campos de verificación";
        }
    }
}

// Obtener responsables existentes
$query = "SELECT * FROM responsable_emision ORDER BY fecha_generacion DESC";
$result = $conexion->query($query);
$responsables_existentes = [];
if ($result) {
    $responsables_existentes = $result->fetch_all(MYSQLI_ASSOC);
}

// Generar ejemplo de firma
$ejemplo_firma = $firma_digital->generarEjemploFirma();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Digital Real - TecNM</title>
    <link rel="stylesheet" href="css_profesional.css">
    <style>
        .firma-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .firma-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-left: 5px solid #1b396a;
        }
        
        .firma-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #1b396a;
            font-size: 14px;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #1b396a;
        }
        
        .btn-generar {
            background: linear-gradient(135deg, #1b396a, #2c5aa0);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-generar:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(27, 57, 106, 0.3);
        }
        
        .mensaje {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: bold;
            font-size: 16px;
        }
        
        .mensaje.success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 2px solid #28a745;
        }
        
        .mensaje.error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 2px solid #dc3545;
        }
        
        .mensaje.warning {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
            border: 2px solid #ffc107;
        }
        
        .firma-preview {
            background: #f8f9fa;
            border: 2px dashed #1b396a;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .hash-display {
            font-family: monospace;
            font-size: 12px;
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 8px;
            word-break: break-all;
            margin: 10px 0;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .responsable-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .responsable-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .estado-activa {
            color: #28a745;
            font-weight: bold;
        }
        
        .estado-inactiva {
            color: #dc3545;
            font-weight: bold;
        }
        
        .ejemplo-section {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border: 2px solid #2196f3;
            border-radius: 15px;
            padding: 25px;
            margin: 25px 0;
        }
        
        .ejemplo-texto {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            font-family: monospace;
            font-size: 13px;
            white-space: pre-line;
            margin: 15px 0;
        }
        
        .tab-container {
            display: flex;
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 12px 24px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-bottom: none;
            cursor: pointer;
            font-weight: bold;
            color: #495057;
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            background: #1b396a;
            color: white;
            border-color: #1b396a;
        }
        
        .tab-content {
            display: none;
            padding: 20px;
            border: 2px solid #e9ecef;
            border-top: none;
            background: white;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="firma-container">
        <h1>🔐 Sistema de Firma Digital Real - TecNM</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Implementación de firma digital usando certificados .cer, .key y contraseña
        </p>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?php 
                if (strpos($mensaje, '✅') !== false) echo 'success';
                elseif (strpos($mensaje, '❌') !== false) echo 'error';
                else echo 'warning';
            ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <!-- Tabs de navegación -->
        <div class="tab-container">
            <button class="tab-button active" onclick="mostrarTab('generar')">📝 Generar Firma</button>
            <button class="tab-button" onclick="mostrarTab('verificar')">🔍 Verificar Firma</button>
            <button class="tab-button" onclick="mostrarTab('ejemplo')">📋 Ejemplo</button>
            <button class="tab-button" onclick="mostrarTab('responsables')">👥 Responsables</button>
        </div>
        
        <!-- Tab: Generar Firma -->
        <div id="tab-generar" class="tab-content active">
            <div class="firma-card">
                <div class="firma-header">
                    <h2>📝 Generar Firma Digital Real</h2>
                    <span style="color: #28a745; font-weight: bold;">🔐 Certificado + Clave Privada</span>
                </div>
                
                <form method="POST">
                    <div class="form-section">
                        <div>
                            <div class="form-group">
                                <label for="nombre_responsable">Nombre del Responsable <span style="color: #ff6b6b;">*</span></label>
                                <input type="text" id="nombre_responsable" name="nombre_responsable" 
                                       placeholder="Ej: Dr. Juan Pérez García" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="cargo_responsable">Cargo</label>
                                <input type="text" id="cargo_responsable" name="cargo_responsable" 
                                       value="RESPONSABLE DE EMISIÓN">
                            </div>
                            
                            <div class="form-group">
                                <label for="certificado_path">Ruta del Certificado (.cer) <span style="color: #ff6b6b;">*</span></label>
                                <input type="text" id="certificado_path" name="certificado_path" 
                                       placeholder="certificados/responsable.cer" required>
                            </div>
                        </div>
                        
                        <div>
                            <div class="form-group">
                                <label for="clave_privada_path">Ruta de la Clave Privada (.key) <span style="color: #ff6b6b;">*</span></label>
                                <input type="text" id="clave_privada_path" name="clave_privada_path" 
                                       placeholder="certificados/responsable.key" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="contrasena">Contraseña del Certificado <span style="color: #ff6b6b;">*</span></label>
                                <input type="password" id="contrasena" name="contrasena" 
                                       placeholder="Contraseña del certificado" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Información de Seguridad:</label>
                                <div style="background: #fff3cd; padding: 15px; border-radius: 8px; font-size: 13px;">
                                    <strong>⚠️ Importante:</strong><br>
                                    • Los archivos .cer y .key deben estar en el directorio 'certificados/'<br>
                                    • La contraseña debe ser la misma que se usó para generar el certificado<br>
                                    • La firma se generará en formato Base64 como en el ejemplo
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="generar_firma_real" class="btn-generar">
                        🔐 Generar Firma Digital Real
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Tab: Verificar Firma -->
        <div id="tab-verificar" class="tab-content">
            <div class="firma-card">
                <div class="firma-header">
                    <h2>🔍 Verificar Firma Digital</h2>
                    <span style="color: #17a2b8; font-weight: bold;">✅ Validación de Autenticidad</span>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="texto_original">Texto Original Firmado <span style="color: #ff6b6b;">*</span></label>
                        <textarea id="texto_original" name="texto_original" rows="6" 
                                  placeholder="Pega aquí el texto original que se firmó" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="firma_base64">Firma Digital (Base64) <span style="color: #ff6b6b;">*</span></label>
                        <textarea id="firma_base64" name="firma_base64" rows="4" 
                                  placeholder="Pega aquí la firma en formato Base64" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="certificado_path_verificar">Ruta del Certificado (.cer) <span style="color: #ff6b6b;">*</span></label>
                        <input type="text" id="certificado_path_verificar" name="certificado_path_verificar" 
                               placeholder="certificados/responsable.cer" required>
                    </div>
                    
                    <button type="submit" name="verificar_firma_real" class="btn-generar">
                        🔍 Verificar Firma Digital
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Tab: Ejemplo -->
        <div id="tab-ejemplo" class="tab-content">
            <div class="ejemplo-section">
                <h2>📋 Ejemplo de Firma Digital Real</h2>
                <p>Este es un ejemplo de cómo se ve una firma digital real generada con certificados:</p>
                
                <h3>📜 Texto a Firmar:</h3>
                <div class="ejemplo-texto"><?php echo htmlspecialchars($ejemplo_firma['texto_ejemplo']); ?></div>
                
                <h3>🔐 Firma Digital (Base64):</h3>
                <div class="hash-display"><?php echo $ejemplo_firma['firma_base64_ejemplo']; ?></div>
                
                <h3>🔍 Hash del Texto:</h3>
                <div class="hash-display"><?php echo $ejemplo_firma['hash_texto']; ?></div>
                
                <div style="background: white; padding: 20px; border-radius: 10px; margin-top: 20px;">
                    <h4>💡 Características de la Firma Digital Real:</h4>
                    <ul>
                        <li>✅ <strong>Única:</strong> Cada texto genera una firma diferente</li>
                        <li>✅ <strong>Irreversible:</strong> No se puede obtener el texto original desde la firma</li>
                        <li>✅ <strong>Verificable:</strong> Se puede validar con el certificado público</li>
                        <li>✅ <strong>Integra:</strong> Cualquier cambio en el texto invalida la firma</li>
                        <li>✅ <strong>Auténtica:</strong> Solo quien tiene la clave privada puede firmar</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Tab: Responsables -->
        <div id="tab-responsables" class="tab-content">
            <div class="firma-card">
                <div class="firma-header">
                    <h2>👥 Responsables con Firma Digital</h2>
                    <span style="color: #6c757d;">Total: <?php echo count($responsables_existentes); ?></span>
                </div>
                
                <?php if (empty($responsables_existentes)): ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <h3>📝 No hay responsables registrados</h3>
                        <p>Genera tu primera firma digital usando el tab "Generar Firma"</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($responsables_existentes as $responsable): ?>
                        <div class="responsable-item">
                            <div class="responsable-header">
                                <h3><?php echo htmlspecialchars($responsable['nombre_responsable']); ?></h3>
                                <span class="<?php echo $responsable['activa'] ? 'estado-activa' : 'estado-inactiva'; ?>">
                                    <?php echo $responsable['activa'] ? '✅ ACTIVA' : '❌ INACTIVA'; ?>
                                </span>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                <div>
                                    <strong>Cargo:</strong><br>
                                    <?php echo htmlspecialchars($responsable['cargo_responsable']); ?>
                                </div>
                                
                                <div>
                                    <strong>Fecha de Generación:</strong><br>
                                    <?php echo $responsable['fecha_generacion']; ?>
                                </div>
                                
                                <div>
                                    <strong>Certificado:</strong><br>
                                    <?php echo htmlspecialchars($responsable['certificado_path']); ?>
                                </div>
                                
                                <div>
                                    <strong>ID Responsable:</strong><br>
                                    <?php echo $responsable['id']; ?>
                                </div>
                            </div>
                            
                            <?php if ($responsable['firma_digital_base64']): ?>
                                <div style="margin-top: 15px;">
                                    <strong>Firma Digital (Base64):</strong>
                                    <div class="hash-display" style="max-height: 100px;">
                                        <?php echo substr($responsable['firma_digital_base64'], 0, 200) . '...'; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Resultado de firma generada -->
        <?php if ($resultado_firma && $resultado_firma['success']): ?>
            <div class="firma-card">
                <div class="firma-header">
                    <h2>✅ Firma Generada Exitosamente</h2>
                    <span style="color: #28a745; font-weight: bold;">🔐 Base64</span>
                </div>
                
                <div class="firma-preview">
                    <h3>📜 Texto Firmado:</h3>
                    <div class="ejemplo-texto"><?php echo htmlspecialchars($resultado_firma['texto_firmado']); ?></div>
                    
                    <h3>🔐 Firma Digital (Base64):</h3>
                    <div class="hash-display"><?php echo $resultado_firma['firma_base64']; ?></div>
                    
                    <h3>📊 Metadatos:</h3>
                    <div style="background: white; padding: 15px; border-radius: 8px; text-align: left;">
                        <strong>Hash del Texto:</strong> <?php echo $resultado_firma['metadatos']['hash_texto']; ?><br>
                        <strong>Fecha de Firma:</strong> <?php echo $resultado_firma['metadatos']['fecha_firma']; ?><br>
                        <strong>Algoritmo:</strong> <?php echo $resultado_firma['metadatos']['algoritmo']; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="modulo_de_administracion.php" class="btn-generar" style="text-decoration: none; display: inline-block; width: auto; padding: 12px 24px;">
                ← Volver al Módulo de Administración
            </a>
        </div>
    </div>
    
    <script>
        function mostrarTab(tabName) {
            // Ocultar todos los tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Desactivar todos los botones
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(button => button.classList.remove('active'));
            
            // Mostrar el tab seleccionado
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        // Auto-formatear el hash mientras se escribe
        document.getElementById('firma_base64')?.addEventListener('input', function() {
            let value = this.value.replace(/[^a-zA-Z0-9+/=]/g, ''); // Solo caracteres Base64 válidos
            this.value = value;
        });
        
        // Validación del formulario de generación
        document.querySelector('form[method="POST"]')?.addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre_responsable')?.value.trim();
            const certificado = document.getElementById('certificado_path')?.value.trim();
            const clave = document.getElementById('clave_privada_path')?.value.trim();
            const contrasena = document.getElementById('contrasena')?.value.trim();
            
            if (!nombre || !certificado || !clave || !contrasena) {
                e.preventDefault();
                alert('Por favor, completa todos los campos obligatorios');
                return false;
            }
        });
    </script>
</body>
</html>
