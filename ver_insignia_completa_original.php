<?php
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Obtener el código de la insignia desde la URL
$codigo_insignia = isset($_GET['insignia']) ? $_GET['insignia'] : '';

if (empty($codigo_insignia)) {
    echo "Error: No se proporcionó código de insignia";
    exit();
}

try {
    // Consulta para obtener los datos de la insignia
    $query = "SELECT 
        tio.Codigo_Insignia as codigo,
        tio.Nombre_Insignia as nombre,
        tio.Categoria as categoria,
        d.Nombre_Destinatario as destinatario,
        tio.Descripcion as descripcion,
        tio.Criterios as criterios,
        tio.Evidencias as evidencias,
        tio.Responsable_Emision as responsable,
        tio.Fecha_Emision as fecha_emision,
        tio.Emisor as emisor,
        tio.Evidencia as evidencia,
        tio.Archivo_Visual as archivo_visual,
        tio.Responsable_Captura as responsable_captura,
        tio.Codigo_Responsable as codigo_responsable,
        CONCAT('imagen/Insignias/', tio.Archivo_Visual) as imagen_path
    FROM T_insignias_otorgadas tio
    LEFT JOIN destinatario d ON tio.Id_Destinatario = d.id
    WHERE tio.Codigo_Insignia = ?";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $codigo_insignia);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "Error: No se encontró la insignia con el código proporcionado";
        exit();
    }
    
    $insignia_data = $result->fetch_assoc();
    $stmt->close();
    
} catch (Exception $e) {
    echo "Error al obtener los datos de la insignia: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background-color: #1b396a;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .header h1 {
            font-size: 24px;
            margin: 0;
        }
        
        .content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .insignia-section {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            border: 2px solid #1b396a;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        
        .insignia-hexagon {
            width: 100px;
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin-right: 20px;
            border: 2px solid #1b396a;
            border-radius: 8px;
        }
        
        .document-preview {
            position: relative;
            width: 100%;
            height: 400px;
            background: white;
            border: 2px solid #1b396a;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%23e0e0e0" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            background-size: 20px 20px;
        }
        
        .document-insignia {
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .metadata-section {
            margin-top: 30px;
        }
        
        .metadata-section h2 {
            color: #1b396a;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #1b396a;
            padding-bottom: 10px;
        }
        
        .metadata-item {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #1b396a;
            border-radius: 4px;
        }
        
        .metadata-item strong {
            color: #1b396a;
            display: block;
            margin-bottom: 5px;
        }
        
        .metadata-item span {
            color: #333;
            line-height: 1.5;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn {
            background-color: #1b396a;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 10px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #0f2a4a;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        footer {
            background-color: #1b396a;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 30px;
            border-radius: 8px;
        }
        
        footer p {
            margin: 5px 0;
        }
        
        @media (max-width: 768px) {
            .insignia-section {
                flex-direction: column;
                text-align: center;
            }
            
            .insignia-hexagon {
                margin-right: 0;
                margin-bottom: 20px;
            }
            
            .document-preview {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Insignias TecNM MetaDatos</h1>
        </div>
        
        <div class="content">
            <div class="insignia-section">
                <div class="insignia-hexagon" style="background-image: url(<?php echo $insignia_data['imagen_path']; ?>);">
                    <!-- La imagen se carga directamente desde PHP -->
                </div>
                
                <div class="document-preview">
                    <!-- Título institucional -->
                    <div style="font-size: 12px; font-weight: bold; color: #1b396a; margin-top: 100px; margin-bottom: 8px; text-align: center;">
                        EL TECNOLÓGICO NACIONAL DE MÉXICO
                    </div>
                    <div style="font-size: 10px; color: #1b396a; margin-bottom: 12px; text-align: center;">
                        OTORGA EL PRESENTE
                    </div>
                    
                    <!-- Título principal del reconocimiento -->
                    <div style="font-size: 13px; font-weight: bold; color: #d4af37; margin-bottom: 15px; text-align: center; text-transform: uppercase;">
                        RECONOCIMIENTO INSTITUCIONAL<br>
                        CON IMPACTO CURRICULAR
                    </div>
                    
                    <!-- Destinatario -->
                    <div style="font-size: 9px; margin-bottom: 6px; text-align: center;">A</div>
                    <div style="font-size: 13px; font-weight: bold; color: #1b396a; margin-bottom: 12px; text-align: center;">
                        <?php echo htmlspecialchars($insignia_data['destinatario']); ?>
                    </div>
                    
                    <!-- Texto descriptivo -->
                    <div style="font-size: 8px; text-align: left; line-height: 1.3; margin-bottom: 30px; padding: 0 12px;">
                        <?php echo nl2br(htmlspecialchars($insignia_data['descripcion'])); ?>
                    </div>
                    
                    <!-- Badge hexagonal en la esquina inferior izquierda -->
                    <div style="position: absolute; bottom: 20px; left: 20px; width: 50px; height: 50px;">
                        <div class="document-insignia" style="width: 100%; height: 100%; background-image: url(<?php echo $insignia_data['imagen_path']; ?>);"></div>
                    </div>
                    
                    <!-- Firma en la esquina inferior derecha -->
                    <div style="position: absolute; bottom: 25px; right: 60px; text-align: right; font-size: 8px; color: #333; background: rgba(255,255,255,0.9); padding: 5px; border-radius: 3px;">
                        <div style="border-bottom: 1px solid #333; width: 80px; margin-bottom: 4px;"></div>
                        <div style="font-weight: bold; color: #1b396a;"><?php echo htmlspecialchars($insignia_data['responsable']); ?></div>
                        <div style="font-size: 7px; color: #666;">RESPONSABLE DE EMISIÓN</div>
                    </div>
                    
                    <!-- Fecha y ubicación -->
                    <div style="position: absolute; bottom: 10px; right: 60px; font-size: 7px; color: #666; text-align: right; background: rgba(255,255,255,0.9); padding: 4px; border-radius: 2px; width: 80px;">
                        CIUDAD DE MÉXICO<br>
                        <?php echo date('F Y', strtotime($insignia_data['fecha_emision'])); ?>
                    </div>
                </div>
            </div>
            
            <div class="metadata-section">
                <h2>Metadatos de la Insignia</h2>
                
                <div class="metadata-item">
                    <strong>Código de identificación de la InsigniaTecNM:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['codigo']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Nombre de la InsigniaTecNM (Subcategoría):</strong>
                    <span><?php echo htmlspecialchars($insignia_data['nombre']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Categoría de la InsigniaTecNM:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['categoria']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Destinatario:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['destinatario']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Descripción:</strong>
                    <span><?php echo nl2br(htmlspecialchars($insignia_data['descripcion'])); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Criterios para su emisión:</strong>
                    <span><?php echo nl2br(htmlspecialchars($insignia_data['criterios'])); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Fecha de emisión:</strong>
                    <span><?php echo date('d-m-Y', strtotime($insignia_data['fecha_emision'])); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Emisor (TecNM o Instituto/Centro):</strong>
                    <span><?php echo htmlspecialchars($insignia_data['emisor']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Evidencia:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['evidencia'] ?: 'Sin evidencia registrada'); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Archivo Visual de la InsigniaTecNM:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['archivo_visual']); ?> (archivo)</span>
                </div>
                
                <div class="metadata-item">
                    <strong>Responsable de la captura de los Metadatos:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['responsable']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Código de identificación del Responsable de la captura de los Metadatos:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['codigo_responsable']); ?></span>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <button onclick="window.print()" class="btn">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button onclick="window.history.back()" class="btn">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 TecNM - Sistema de Insignias Digitales</p>
        <p>Última actualización - Octubre 2025</p>
    </footer>
</body>
</html>
