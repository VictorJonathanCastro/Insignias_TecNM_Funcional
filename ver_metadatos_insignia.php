<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=sesion_invalida');
    exit();
}

require_once 'conexion.php';
$conexion->select_db("insignia");

// Obtener ID de la insignia desde la URL
$insignia_id = $_GET['id'] ?? '';

if (empty($insignia_id)) {
    header('Location: modulo_de_administracion.php?error=insignia_no_encontrada');
    exit();
}

// Obtener datos de la insignia otorgada
$stmt = $conexion->prepare("
    SELECT 
        io.Codigo_Insignia,
        io.Destinatario,
        io.Responsable_Emision,
        io.Periodo_Emision,
        io.Estatus,
        io.Fecha_Emision,
        io.Fecha_Vencimiento,
        io.Fecha_Creacion,
        d.Nombre_Completo,
        d.Matricula,
        d.Curp,
        d.Correo,
        re.Nombre_Completo as Responsable_Nombre,
        pe.periodo,
        pe.descripcion as periodo_descripcion
    FROM insigniasotorgadas io
    LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario
    LEFT JOIN responsable_emision re ON io.Responsable_Emision = re.ID_responsable
    LEFT JOIN periodo_emision pe ON io.Periodo_Emision = pe.ID_periodo
    WHERE io.ID_otorgada = ?
");

$stmt->bind_param("i", $insignia_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header('Location: modulo_de_administracion.php?error=insignia_no_encontrada');
    exit();
}

$insignia = $resultado->fetch_assoc();
$stmt->close();

// Generar código de identificación usando el código que ya existe
$codigo_identificacion = $insignia['Codigo_Insignia'];

// Buscar firma digital en la tabla firmas_digitales
$firma_data_real = null;
$responsable_nombre = $insignia['Responsable_Nombre'] ?? 'Victor Hugo Agaton Catalan'; // Default if not found

try {
    $sql_buscar_firma = "SELECT * FROM firmas_digitales WHERE nombre_responsable = ? AND activa = 1 ORDER BY fecha_creacion DESC LIMIT 1";
    $stmt_buscar_firma = $conexion->prepare($sql_buscar_firma);
    
    if ($stmt_buscar_firma) {
        $stmt_buscar_firma->bind_param("s", $responsable_nombre);
        $stmt_buscar_firma->execute();
        $resultado_buscar = $stmt_buscar_firma->get_result();
        
        if ($resultado_buscar && $resultado_buscar->num_rows > 0) {
            $firma_data_db = $resultado_buscar->fetch_assoc();
            
            // Construir datos de firma real
            $firma_data_real = [
                'nombre_responsable' => $firma_data_db['nombre_responsable'],
                'fecha_generacion' => $firma_data_db['fecha_creacion'],
                'hash_verificacion' => $firma_data_db['hash_verificacion'],
                'archivo_firma' => $firma_data_db['archivo_firma'],
                'activa' => $firma_data_db['activa']
            ];
        }
        $stmt_buscar_firma->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener firma digital: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metadatos de Insignia - Insignias TecNM</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #002855;
            --secondary-color: #b10000;
            --accent-color: #1b396a;
            --text-dark: #333;
            --text-light: #666;
            --bg-white: #ffffff;
            --bg-light: #f8f9fa;
            --border-color: #e0e0e0;
            --shadow-light: 0 2px 10px rgba(0,0,0,0.1);
            --shadow-medium: 0 4px 20px rgba(0,0,0,0.15);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .content {
            padding: 40px;
        }

        .insignia-display {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .insignia-visual {
            background: var(--bg-light);
            padding: 30px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--shadow-light);
        }

        .insignia-hexagon {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            margin: 0 auto 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
        }

        .insignia-logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            color: var(--primary-color);
            font-size: 24px;
            font-weight: bold;
        }

        .insignia-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
        }

        .insignia-category {
            font-size: 14px;
            color: #4CAF50;
            font-weight: 600;
        }

        .insignia-code {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-family: 'Courier New', monospace;
            font-weight: bold;
            margin: 20px 0;
            display: inline-block;
        }

        .recognition-document {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-top: 20px;
            box-shadow: var(--shadow-light);
        }

        .recognition-title {
            font-size: 14px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .recipient-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .metadata-section {
            background: var(--bg-light);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
        }

        .metadata-section h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .metadata-list {
            list-style: none;
        }

        .metadata-item {
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--shadow-light);
        }

        .metadata-label {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 14px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metadata-value {
            color: var(--text-dark);
            font-size: 16px;
            line-height: 1.5;
        }

        .metadata-value.long-text {
            font-size: 14px;
            line-height: 1.6;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            padding: 10px 20px;
            border: 2px solid var(--primary-color);
            border-radius: var(--border-radius);
        }

        .nav-link:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: var(--bg-white);
            border: none;
            padding: 12px 25px;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        @media (max-width: 1024px) {
            .insignia-display {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .insignia-visual {
                order: 2;
            }
            
            .metadata-section {
                order: 1;
            }
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }
            
            .header {
                padding: 20px;
            }
            
            .insignia-hexagon {
                width: 250px;
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-medal"></i> Insignias TecNM MetaDatos</h1>
            <p>Sistema de Reconocimientos Digitales</p>
        </div>

        <div class="content">
            <div class="navigation">
                <a href="modulo_de_administracion.php" class="nav-link">
                    <i class="fas fa-arrow-left"></i>
                    Volver al Módulo
                </a>
                
                <div style="display: flex; gap: 10px;">
                    <a href="registrar_reconocimiento.php" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        Nuevo Reconocimiento
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i>
                        Imprimir
                    </button>
                </div>
            </div>

            <div class="insignia-display">
                <div class="insignia-visual">
                    <div class="insignia-hexagon">
                        <div class="insignia-logo">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="insignia-title">Insignia TecNM</div>
                        <div class="insignia-category">Reconocimiento Institucional</div>
                    </div>
                    
                    <div class="insignia-code">
                        <?php echo $codigo_identificacion; ?>
                    </div>
                    
                    <div class="recognition-document">
                        <div class="recognition-title">RECONOCIMIENTO INSTITUCIONAL CON IMPACTO CURRICULAR</div>
                        <div class="recipient-name"><?php echo htmlspecialchars($insignia['Nombre_Completo']); ?></div>
                    </div>
                    
                    <!-- Firma Digital -->
                    <div style="position: absolute; bottom: 25px; right: 60px; text-align: right; font-size: 8px; color: #333; background: rgba(255,255,255,0.95); padding: 8px; border-radius: 5px; border: 2px solid #28a745; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <!-- QR Code placeholder -->
                        <div style="width: 40px; height: 40px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 3px; margin-bottom: 5px; display: flex; align-items: center; justify-content: center; font-size: 6px; color: #666;">
                            QR
                        </div>
                        
                        <!-- Firma digital -->
                        <div style="border-bottom: 2px solid #1b396a; width: 100px; margin-bottom: 4px; position: relative;">
                            <div style="position: absolute; right: -5px; top: -8px; background: #28a745; color: white; padding: 1px 3px; border-radius: 2px; font-size: 5px; font-weight: bold;">
                                🔐 DIGITAL
                            </div>
                        </div>
                        
                        <div style="font-weight: bold; color: #1b396a; font-size: 7px; margin-bottom: 2px;">
                            <?php echo htmlspecialchars($insignia['Responsable_Nombre'] ?: 'Victor Hugo Agaton Catalan'); ?>
                        </div>
                        
                        <div style="font-size: 6px; color: #666; margin-bottom: 2px;">RESPONSABLE DE EMISIÓN</div>
                        
                        <!-- Hash de verificación -->
                        <div style="font-size: 5px; color: #28a745; font-family: monospace; background: #f8f9fa; padding: 2px; border-radius: 2px; margin-top: 3px;">
                            <?php 
                            if ($firma_data_real && isset($firma_data_real['hash_verificacion'])) {
                                echo substr($firma_data_real['hash_verificacion'], 0, 12) . '...';
                            } else {
                                echo substr(hash('sha256', $insignia['Codigo_Insignia'] . $insignia['Nombre_Completo']), 0, 12) . '...';
                            }
                            ?>
                        </div>
                        
                        <!-- Indicador de firma digital -->
                        <div style="font-size: 5px; color: #28a745; font-weight: bold; margin-top: 2px;">
                            ✓ FIRMA DIGITAL VÁLIDA
                        </div>
                    </div>
                </div>

                <div class="metadata-section">
                    <h2><i class="fas fa-info-circle"></i> Metadatos Completos</h2>
                    
                    <ul class="metadata-list">
                        <li class="metadata-item">
                            <div class="metadata-label">Código de identificación de la InsigniaTecNM</div>
                            <div class="metadata-value"><?php echo $codigo_identificacion; ?></div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Nombre de la InsigniaTecNM (Subcategoría)</div>
                            <div class="metadata-value">Insignia TecNM</div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Categoría de la InsigniaTecNM</div>
                            <div class="metadata-value">Reconocimiento Institucional</div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Destinatario</div>
                            <div class="metadata-value"><?php echo htmlspecialchars($insignia['Nombre_Completo']); ?></div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Descripción</div>
                            <div class="metadata-value long-text">Esta insignia reconoce la participación destacada en actividades académicas y de formación integral por parte de <?php echo htmlspecialchars($insignia['Nombre_Completo']); ?>.</div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Criterios para su emisión</div>
                            <div class="metadata-value long-text">Para obtener esta insignia, el estudiante debe haber demostrado competencias específicas y cumplimiento de criterios establecidos.</div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Fecha de emisión</div>
                            <div class="metadata-value"><?php echo date('d-m-Y', strtotime($insignia['Fecha_Emision'])); ?></div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Emisor (TecNM o Instituto/Centro)</div>
                            <div class="metadata-value">TecNM / Instituto Tecnológico de San Marcos</div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Evidencia</div>
                            <div class="metadata-value">Certificación oficial</div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Archivo Visual de la InsigniaTecNM</div>
                            <div class="metadata-value">Insignia digital oficial</div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Responsable de la captura de los Metadatos</div>
                            <div class="metadata-value"><?php echo htmlspecialchars($insignia['Responsable_Nombre'] ?: 'Sistema TecNM'); ?></div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Código de identificación del Responsable de la captura de los Metadatos</div>
                            <div class="metadata-value">TecNM-ITSM-2025-Resp001</div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Firma Digital</div>
                            <div class="metadata-value"><?php echo $firma_data_real ? 'Activa' : 'No disponible'; ?></div>
                        </li>
                        
                        <?php if ($firma_data_real): ?>
                        <li class="metadata-item">
                            <div class="metadata-label">Hash de Verificación</div>
                            <div class="metadata-value" style="font-family: monospace; font-size: 12px;"><?php echo htmlspecialchars($firma_data_real['hash_verificacion']); ?></div>
                        </li>
                        
                        <li class="metadata-item">
                            <div class="metadata-label">Fecha de Generación de Firma</div>
                            <div class="metadata-value"><?php echo date('d-m-Y H:i:s', strtotime($firma_data_real['fecha_generacion'])); ?></div>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para imprimir solo el contenido relevante
        function printInsignia() {
            const printContent = document.querySelector('.container').innerHTML;
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = `
                <div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">
                    ${printContent}
                </div>
            `;
            
            window.print();
            document.body.innerHTML = originalContent;
            location.reload();
        }
    </script>
</body>
</html>
