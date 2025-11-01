<?php
session_start();
require_once 'conexion.php';

// Obtener el código de insignia desde la URL
$codigo_insignia = $_GET['insignia'] ?? '';

if (empty($codigo_insignia)) {
    die('Código de insignia no válido');
}

// Intentar obtener datos reales de la base de datos
$insignia_data = null;

try {
    // Consulta corregida para obtener datos de la insignia
    $stmt = $conexion->prepare("
        SELECT 
            io.clave_insignia as codigo_insignia,
            d.Nombre_Completo as destinatario,
            io.fecha_otorgamiento as fecha_emision,
            io.evidencia,
            i.Descripcion as descripcion,
            ti.Nombre_ins as nombre_insignia,
            ci.Nombre_cat as nombre_categoria,
            'Andrea Yadira Zárate Fuentes' as responsable_nombre,
            'RESPONSABLE DE EMISIÓN' as responsable_cargo,
            ic.Nombre_itc as nombre_instituto
        FROM insigniasotorgadas io
        LEFT JOIN destinatario d ON io.destinatario_id = d.id
        LEFT JOIN insignias i ON io.insignia_id = i.id
        LEFT JOIN tipo_insignia ti ON i.Tipo_Insignia = ti.id
        LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.id
        LEFT JOIN it_centros ic ON d.ITCentro = ic.id
        WHERE io.clave_insignia = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("s", $codigo_insignia);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $insignia_data = $row;
            echo "<!-- Datos obtenidos de BD: " . json_encode($row) . " -->";
        } else {
            echo "<!-- No se encontraron datos para: " . $codigo_insignia . " -->";
        }
    }
} catch (Exception $e) {
    // Si hay error, usar datos por defecto
    $insignia_data = null;
    echo "<!-- Error en consulta: " . $e->getMessage() . " -->";
}

// Si no se obtuvieron datos de la BD, usar datos por defecto
if (!$insignia_data) {
    $insignia_data = [
        'codigo_insignia' => $codigo_insignia,
        'destinatario' => 'Ingrid Roxana Pioquinto Castro',
        'fecha_emision' => '2025-10-08',
        'evidencia' => 'Certificado_Alfabetizacion.pdf',
        'descripcion' => 'Por su destacada participación en actividades de Responsabilidad Social desarrollando competencias de Formación Integral, mediante el compromiso y dedicación en procesos de formación integral, contribuyendo de manera significativa al desarrollo de competencias profesionales y valores institucionales.

Su compromiso, empatía y vocación de servicio fortalecen los valores institucionales y promueven el desarrollo de una sociedad más justa e incluyente.

Esta actividad ha sido reconocida como parte de su formación integral, con impacto curricular por el Tecnológico Nacional de México.',
        'nombre_insignia' => 'Responsabilidad Social',
        'nombre_categoria' => 'Formación Integral',
        'responsable_nombre' => 'Andrea Yadira Zárate Fuentes',
        'responsable_cargo' => 'RESPONSABLE DE EMISIÓN',
        'nombre_instituto' => 'Instituto Tecnológico de San Marcos'
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Insignia TecNM - <?php echo htmlspecialchars($insignia_data['codigo_insignia']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1b396a, #002855);
            min-height: 100vh;
            padding: 20px;
        }
        
        .verification-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1b396a, #002855);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        
        .certificate-container {
            padding: 40px;
            text-align: center;
        }
        
        .certificate {
            background-image: url('imagen/Hoja_membrentada.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            width: 8.5in;
            height: 11in;
            margin: 0 auto;
            position: relative;
            border: 1px solid #ddd;
            color: #333;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .certificate-content {
            position: relative;
            z-index: 2;
            padding: 40px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .institutional-title {
            font-size: 22px;
            font-weight: bold;
            color: #1b396a;
            margin-top: 60px;
            margin-bottom: 8px;
            text-align: center;
        }
        
        .present-text {
            font-size: 18px;
            color: #1b396a;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .recognition-title {
            font-size: 26px;
            font-weight: bold;
            color: #d4af37;
            margin-bottom: 15px;
            text-align: center;
            text-transform: uppercase;
            line-height: 1.2;
        }
        
        .recipient-prefix {
            font-size: 18px;
            margin-bottom: 5px;
            text-align: center;
            color: #666;
        }
        
        .recipient-name {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .description-text {
            font-size: 18px;
            text-align: justify;
            line-height: 1.8;
            margin-bottom: 60px;
            padding: 0 50px;
            color: #333;
        }
        
        .bottom-section {
            position: absolute;
            bottom: 40px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 0 60px;
        }
        
        .badge-section {
            width: 90px;
            height: 90px;
        }
        
        .badge-section img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .signature-section {
            text-align: right;
            background: transparent;
            padding: 0;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            width: 160px;
            margin-bottom: 15px;
        }
        
        .signature-name {
            font-weight: bold;
            color: #333;
            font-size: 16px;
            text-transform: uppercase;
        }
        
        .signature-title {
            font-size: 14px;
            color: #666;
            margin-top: 8px;
            text-transform: uppercase;
        }
        
        .date-location {
            font-size: 14px;
            color: #333;
            margin-top: 20px;
            line-height: 1.4;
            text-align: center;
        }
        
        .actions {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
        }
        
        .btn {
            background: linear-gradient(135deg, #1b396a, #002855);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .verification-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .header, .actions {
                display: none;
            }
            
            .certificate-container {
                padding: 0;
            }
            
            .certificate {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="header">
            <h1>🔍 Verificación de Insignia TecNM</h1>
            <p>Sistema de Validación Oficial</p>
        </div>
        
        
        <div class="certificate-container">
            <div class="certificate">
                <div class="certificate-content">
                    <!-- Título institucional -->
                    <div class="institutional-title">
                        EL TECNOLÓGICO NACIONAL DE MÉXICO
                    </div>
                    <div class="present-text">
                        OTORGA EL PRESENTE
                    </div>
                    
                    <!-- Título principal del reconocimiento -->
                    <div class="recognition-title">
                        RECONOCIMIENTO INSTITUCIONAL<br>
                        CON IMPACTO CURRICULAR
                    </div>
                    
                    <!-- Destinatario -->
                    <div class="recipient-prefix">A</div>
                    <div class="recipient-name">
                        <?php echo htmlspecialchars($insignia_data['destinatario']); ?>
                    </div>
                    
                    <!-- Texto descriptivo -->
                    <div class="description-text">
                        <?php echo nl2br(htmlspecialchars($insignia_data['descripcion'])); ?>
                    </div>
                    
                    <!-- Sección inferior -->
                    <div class="bottom-section">
                        <div class="badge-section">
                            <img src="imagen/insignia_Responsabilidad Social.png" alt="Insignia TecNM">
                        </div>
                        
                        <div class="signature-section">
                            <div class="signature-line"></div>
                            <div class="signature-name">
                                <?php echo htmlspecialchars($insignia_data['responsable_nombre'] ?? 'Andrea Yadira Zárate Fuentes'); ?>
                            </div>
                            <div class="signature-title">RESPONSABLE DE EMISIÓN</div>
                        </div>
                    </div>
                    
                    <!-- Fecha y ubicación centradas -->
                    <div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); text-align: center; font-size: 14px; color: #333;">
                        CIUDAD DE MÉXICO<br>
                        <?php echo date('F Y', strtotime($insignia_data['fecha_emision'])); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <button onclick="window.print()" class="btn">
                🖨️ Imprimir Certificado
            </button>
            <a href="javascript:history.back()" class="btn btn-secondary">
                ← Volver
            </a>
        </div>
    </div>
</body>
</html>
