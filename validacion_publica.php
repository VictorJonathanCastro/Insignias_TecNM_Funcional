<?php
require_once 'conexion.php';

// Obtener el código de insignia desde la URL
$codigo_insignia = $_GET['insignia'] ?? '';

if (empty($codigo_insignia)) {
    die('Código de insignia no válido');
}

// Obtener datos de la insignia
$insignia_data = null;

try {
    $sql = "
        SELECT 
            io.ID_otorgada as id,
            io.Codigo_Insignia as clave_insignia,
            io.Fecha_Emision as fecha_otorgamiento,
            d.Nombre_Completo as destinatario,
            d.Curp as curp,
            d.Matricula as matricula,
            CASE 
                WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                ELSE 'Insignia TecNM'
            END as nombre_insignia,
            CASE 
                WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                ELSE 'Formación Integral'
            END as categoria,
            'TecNM' as institucion,
            '2025-1' as periodo,
            'Activo' as estatus
        FROM insigniasotorgadas io
        LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario
        WHERE io.Codigo_Insignia = ?
    ";
    
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $codigo_insignia);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $insignia_data = $result->fetch_assoc();
        }
        $stmt->close();
    }
} catch (Exception $e) {
    die('Error al obtener datos de la insignia: ' . $e->getMessage());
}

if (!$insignia_data) {
    die('Insignia no encontrada');
}

// Función para formatear fechas
function formatearFecha($fecha) {
    if (empty($fecha)) return 'No especificada';
    return date('d/m/Y', strtotime($fecha));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Insignia - TecNM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.2em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .validation-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 5px solid #28a745;
        }

        .validation-status {
            text-align: center;
            margin-bottom: 30px;
        }

        .status-icon {
            font-size: 4em;
            color: #28a745;
            margin-bottom: 15px;
        }

        .status-text {
            font-size: 1.5em;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
        }

        .status-subtitle {
            color: #666;
            font-size: 1.1em;
        }

        .insignia-title {
            font-size: 1.8em;
            font-weight: bold;
            color: #1e3c72;
            margin-bottom: 15px;
            text-align: center;
        }

        .insignia-category {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 18px;
            border-radius: 25px;
            font-size: 0.9em;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
            text-align: center;
            width: 100%;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .detail-label {
            font-weight: bold;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #333;
            font-size: 1.1em;
        }

        .actions {
            text-align: center;
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 0 10px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 60, 114, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .footer {
            background: #1e3c72;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Validación de Insignia</h1>
            <p>Tecnológico Nacional de México</p>
        </div>

        <div class="content">
            <div class="validation-card">
                <div class="validation-status">
                    <div class="status-icon">✓</div>
                    <div class="status-text">INSIGNIA VÁLIDA</div>
                    <div class="status-subtitle">Esta insignia ha sido verificada y es auténtica</div>
                </div>

                <div class="insignia-title"><?php echo htmlspecialchars($insignia_data['nombre_insignia']); ?></div>
                <div class="insignia-category"><?php echo htmlspecialchars($insignia_data['categoria']); ?></div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Destinatario</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia_data['destinatario']); ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Código de Insignia</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia_data['clave_insignia']); ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Fecha de Emisión</div>
                        <div class="detail-value"><?php echo formatearFecha($insignia_data['fecha_otorgamiento']); ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Institución</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia_data['institucion']); ?></div>
                    </div>

                    <?php if (!empty($insignia_data['curp'])): ?>
                    <div class="detail-item">
                        <div class="detail-label">CURP</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia_data['curp']); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($insignia_data['matricula'])): ?>
                    <div class="detail-item">
                        <div class="detail-label">Matrícula</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia_data['matricula']); ?></div>
                    </div>
                    <?php endif; ?>

                    <div class="detail-item">
                        <div class="detail-label">Estado</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia_data['estatus']); ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Período</div>
                        <div class="detail-value"><?php echo htmlspecialchars($insignia_data['periodo']); ?></div>
                    </div>
                </div>

                <div class="actions">
                    <a href="ver_insignia_publica.php?id=<?php echo $insignia_data['id']; ?>" class="btn btn-primary">
                        🏆 Ver Reconocimiento
                    </a>
                    <a href="consulta_publica.php" class="btn btn-secondary">
                        ← Volver a Consulta
                    </a>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Copyright 2025 - TecNM | Última actualización - Octubre 2025</p>
        </div>
    </div>
</body>
</html>
