<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=sesion_invalida');
    exit();
}

require_once 'conexion.php';

// Obtener datos del usuario logueado
$correo_usuario = $_SESSION['correo'] ?? '';
$nombre_usuario = $_SESSION['nombre'] ?? '';
$apellido_usuario = $_SESSION['apellido_paterno'] ?? '';

// Obtener rol del usuario
$rol_usuario = $_SESSION['rol'] ?? 'Estudiante';

// Verificar si hay búsqueda específica
$busqueda = $_GET['buscar'] ?? '';

// Consulta básica para obtener las insignias otorgadas usando la estructura actual
if (!empty($busqueda)) {
    // Modo búsqueda: mostrar solo lo que se busque
    $sql = "
        SELECT 
            io.ID_otorgada as id,
            io.Codigo_Insignia as clave_insignia,
            io.Fecha_Emision as fecha_otorgamiento,
            'Certificación oficial' as evidencia,
            d.Nombre_Completo as destinatario,
            'No especificada' as Matricula,
            'Programa no especificado' as Programa,
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
                WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                WHEN io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                ELSE 'Formación Integral'
            END as categoria,
            'TecNM' as institucion,
            '2025-1' as periodo,
            'Activo' as estatus,
            'Sistema' as responsable,
            'Administrador' as cargo
        FROM insigniasotorgadas io
        LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario
        WHERE d.Nombre_Completo LIKE ?
        ORDER BY io.Fecha_Emision DESC
    ";
    $filtro_por_busqueda = true;
} elseif ($rol_usuario === 'Admin' || $rol_usuario === 'SuperUsuario') {
    // Modo administrador: mostrar TODAS las insignias
    $sql = "
        SELECT 
            io.ID_otorgada as id,
            io.Codigo_Insignia as clave_insignia,
            io.Fecha_Emision as fecha_otorgamiento,
            'Certificación oficial' as evidencia,
            d.Nombre_Completo as destinatario,
            'No especificada' as Matricula,
            'Programa no especificado' as Programa,
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
                WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                WHEN io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                ELSE 'Formación Integral'
            END as categoria,
            'TecNM' as institucion,
            '2025-1' as periodo,
            'Activo' as estatus,
            'Sistema' as responsable,
            'Administrador' as cargo
        FROM insigniasotorgadas io
        LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario
        ORDER BY io.Fecha_Emision DESC
    ";
    $filtro_por_busqueda = false;
    $filtro_por_correo = false;
} else {
    // Filtrar por nombre del usuario
    $sql = "
        SELECT 
            io.ID_otorgada as id,
            io.Codigo_Insignia as clave_insignia,
            io.Fecha_Emision as fecha_otorgamiento,
            'Certificación oficial' as evidencia,
            d.Nombre_Completo as destinatario,
            'No especificada' as Matricula,
            'Programa no especificado' as Programa,
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
                WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                WHEN io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                ELSE 'Formación Integral'
            END as categoria,
            'TecNM' as institucion,
            '2025-1' as periodo,
            'Activo' as estatus,
            'Sistema' as responsable,
            'Administrador' as cargo
        FROM insigniasotorgadas io
        LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario
        WHERE d.Nombre_Completo LIKE ?
        ORDER BY io.Fecha_Emision DESC
    ";
    $filtro_por_correo = false;
    $filtro_por_busqueda = false;
}

// Preparar y ejecutar la consulta
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die('Error al preparar la consulta: ' . $conexion->error);
}

// Bind parameters según el tipo de filtro
if ($filtro_por_busqueda) {
    $busqueda_param = "%$busqueda%";
    $stmt->bind_param("s", $busqueda_param);
} elseif ($rol_usuario === 'Admin' || $rol_usuario === 'SuperUsuario') {
    // Para administradores, no hay parámetros que bindear
} else {
    $nombre_completo = "%$nombre_usuario $apellido_usuario%";
    $stmt->bind_param("s", $nombre_completo);
}

if (!$stmt->execute()) {
    die('Error al ejecutar la consulta: ' . $stmt->error);
}

$result = $stmt->get_result();
$insignias = [];
while ($row = $result->fetch_assoc()) {
    $insignias[] = $row;
}
$stmt->close();

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
    <title>Historial de Insignias - TecNM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.08);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .back-button {
            position: absolute;
            left: 30px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 14px 24px;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            z-index: 10;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-50%) translateX(-3px);
            text-decoration: none;
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .header h1 {
            font-size: 2.8rem;
            margin-bottom: 12px;
            font-weight: 700;
            letter-spacing: -0.02em;
            position: relative;
            z-index: 10;
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 400;
            position: relative;
            z-index: 10;
        }

        .search-section {
            padding: 40px 30px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .search-form {
            display: flex;
            gap: 20px;
            align-items: center;
            max-width: 700px;
            margin: 0 auto;
        }

        .search-input {
            flex: 1;
            padding: 16px 24px;
            border: 2px solid #e2e8f0;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 400;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .search-input:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 4px rgba(30, 60, 114, 0.1), 0 4px 12px rgba(0,0,0,0.05);
            transform: translateY(-1px);
        }

        .search-btn {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            padding: 16px 28px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 60, 114, 0.4);
        }

        .content {
            padding: 40px 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 12px;
            position: relative;
            z-index: 10;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.95;
            font-weight: 500;
            position: relative;
            z-index: 10;
        }

        .insignias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
            gap: 30px;
        }

        .insignia-card {
            background: white;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .insignia-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
            border-color: rgba(30, 60, 114, 0.2);
        }

        .insignia-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        .insignia-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            position: relative;
            z-index: 10;
        }

        .insignia-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .insignia-category {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 18px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .insignia-details {
            margin-bottom: 25px;
            position: relative;
            z-index: 10;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #64748b;
            font-size: 0.9rem;
            letter-spacing: 0.01em;
        }

        .detail-value {
            color: #334155;
            font-size: 0.9rem;
            text-align: right;
            max-width: 220px;
            word-wrap: break-word;
            font-weight: 500;
        }

        .insignia-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            position: relative;
            z-index: 10;
        }

        .btn-action {
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 0.01em;
        }

        .btn-ver {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-ver:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-validar {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-validar:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
        }

        .empty-state h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #334155;
            font-weight: 700;
        }

        .empty-state p {
            font-size: 1.1rem;
            line-height: 1.7;
            font-weight: 400;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .back-button {
                left: 20px;
                padding: 12px 20px;
                font-size: 14px;
            }
            
            .header h1 {
                font-size: 2.2rem;
                margin-left: 80px;
                margin-right: 80px;
            }
            
            .insignias-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .search-form {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-input {
                width: 100%;
            }
            
            .insignia-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .search-section {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <?php if ($rol_usuario === 'Admin' || $rol_usuario === 'SuperUsuario'): ?>
                <a href="modulo_de_administracion.php" class="back-button">
                    ← Regresar al Módulo
                </a>
            <?php endif; ?>
            <h1>📜 Historial de Insignias</h1>
            <p>Sistema de Gestión de Reconocimientos TecNM</p>
        </div>

        <div class="search-section">
            <form class="search-form" method="GET">
                <input type="text" name="buscar" class="search-input" 
                       placeholder="Buscar por nombre del destinatario..." 
                       value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="search-btn">🔍 Buscar</button>
                <?php if (!empty($busqueda)): ?>
                    <a href="historial_insignias.php" class="search-btn" style="text-decoration: none; background: #6c757d;">❌ Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($insignias); ?></div>
                    <div class="stat-label">Total de Insignias</div>
                </div>
            </div>

            <?php if (empty($insignias)): ?>
                <div class="empty-state">
                    <h3>📭 No se encontraron insignias</h3>
                    <?php if (!empty($busqueda)): ?>
                        <p>No se encontraron insignias que coincidan con la búsqueda "<?php echo htmlspecialchars($busqueda); ?>"</p>
                    <?php elseif ($rol_usuario === 'Admin' || $rol_usuario === 'SuperUsuario'): ?>
                        <p>No hay insignias registradas en el sistema.</p>
                    <?php else: ?>
                        <p>No tienes insignias asignadas o no se pudo encontrar tu información en la base de datos.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="insignias-grid">
                    <?php foreach ($insignias as $insignia): ?>
                        <div class="insignia-card">
                            <div class="insignia-header">
                                <div>
                                    <div class="insignia-title"><?php echo htmlspecialchars($insignia['nombre_insignia']); ?></div>
                                </div>
                                <div class="insignia-category"><?php echo htmlspecialchars($insignia['categoria']); ?></div>
                            </div>

                            <div class="insignia-details">
                                <div class="detail-row">
                                    <span class="detail-label">Destinatario:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($insignia['destinatario']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Matrícula:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($insignia['Matricula'] ?? 'No especificada'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Fecha de Emisión:</span>
                                    <span class="detail-value"><?php echo formatearFecha($insignia['fecha_otorgamiento']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Programa:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($insignia['Programa']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Institución:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($insignia['institucion']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Estado:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($insignia['estatus']); ?></span>
                                </div>
                            </div>

                            <div class="insignia-actions">
                                <?php if ($rol_usuario === 'Admin' || $rol_usuario === 'SuperUsuario'): ?>
                                    <!-- Enlaces para administradores -->
                                    <a href="ver_certificado_admin.php?id=<?php echo $insignia['id']; ?>" class="btn-action btn-ver" target="_blank">
                                        🏆 Ver Certificado
                                    </a>
                                    <a href="ver_validacion_admin.php?id=<?php echo $insignia['id']; ?>" class="btn-action btn-validar">
                                        🔍 Ver Validación
                                    </a>
                                <?php else: ?>
                                    <!-- Enlaces para usuarios normales -->
                                    <a href="ver_insignia_completa.php?id=<?php echo $insignia['id']; ?>" class="btn-action btn-ver">
                                        ⭐ Ver Reconocimiento
                                    </a>
                                    <a href="validacion.php?insignia=<?php echo urlencode($insignia['clave_insignia'] ?? $insignia['id']); ?>" class="btn-action btn-validar">
                                        ✓ Ver Validación
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>
