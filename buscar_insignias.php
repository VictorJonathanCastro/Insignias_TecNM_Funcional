<?php
// Incluir conexión a la base de datos
require_once 'conexion.php';

// Obtener término de búsqueda
$termino_busqueda = $_GET['buscar'] ?? '';
$termino_busqueda = trim($termino_busqueda);

// Si no hay término de búsqueda, mostrar mensaje
if (empty($termino_busqueda)) {
    $termino_busqueda = '';
    $total_resultados = 0;
    $insignias = [];
} else {

// Consulta que filtra por el término de búsqueda
$termino_like = '%' . $termino_busqueda . '%';

$sql = "
    SELECT 
        io.ID_otorgada as id,
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
            WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
            WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
            WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
            ELSE 'Formación Integral'
        END as categoria,
        'Tecnológico Nacional de México' as institucion,
        'Período Académico' as periodo,
        'Activo' as estatus,
        'Sistema TecNM' as responsable,
        'Administrador' as cargo,
        io.Codigo_Insignia as clave_insignia
    FROM insigniasotorgadas io
    LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario
    WHERE (
        d.Nombre_Completo LIKE ? 
        OR 
        io.Codigo_Insignia LIKE ?
        OR 
        CASE 
            WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
            WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
            WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
            WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
            WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
            WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
            WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
            ELSE 'Insignia TecNM'
        END LIKE ?
        OR 
        CASE 
            WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
            WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
            WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
            ELSE 'Formación Integral'
        END LIKE ?
    )
    ORDER BY io.Fecha_Emision DESC
    LIMIT 10
";

// Preparar y ejecutar la consulta
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error . "<br>Consulta SQL: " . htmlspecialchars($sql));
}
$stmt->bind_param("ssss", $termino_like, $termino_like, $termino_like, $termino_like);
$stmt->execute();
$resultado = $stmt->get_result();
$insignias = [];

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $insignias[] = $fila;
    }
} else {
    // Debug: mostrar información de la consulta
    if (isset($_GET['debug'])) {
        echo "<div style='background: #f8f9fa; padding: 15px; margin: 20px; border-radius: 5px;'>";
        echo "<h4>Debug Info:</h4>";
        echo "<p><strong>Término buscado:</strong> " . htmlspecialchars($termino_busqueda) . "</p>";
        echo "<p><strong>Término LIKE:</strong> " . htmlspecialchars($termino_like) . "</p>";
        echo "<p><strong>Número de filas:</strong> " . ($resultado ? $resultado->num_rows : 'Error en consulta') . "</p>";
        echo "<p><strong>Error SQL:</strong> " . ($conexion->error ?: 'Ninguno') . "</p>";
        echo "</div>";
    }
}
$stmt->close();

// Contar total de resultados
$total_resultados = count($insignias);
}

// Función para formatear fecha
function formatearFecha($fecha) {
    if (!$fecha) return 'N/A';
    $fecha_obj = new DateTime($fecha);
    return $fecha_obj->format('d/m/Y');
}

// Función para resaltar el término de búsqueda
function resaltarBusqueda($texto, $termino) {
    if (empty($termino)) return htmlspecialchars($texto);
    return preg_replace(
        '/(' . preg_quote($termino, '/') . ')/i',
        '<mark style="background: #ffeb3b; padding: 2px 4px; border-radius: 3px;">$1</mark>',
        htmlspecialchars($texto)
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Búsqueda - Insignias TecNM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* ==== ENCABEZADO ==== */
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .header h1 {
            font-size: 26px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 10;
            letter-spacing: -0.01em;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            position: relative;
            z-index: 10;
        }

        .btn-header {
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 10px 18px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 25px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .btn-header:hover {
            background: rgba(255,255,255,0.25);
            border-color: rgba(255,255,255,0.3);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* ==== CONTENIDO PRINCIPAL ==== */
        .main-container {
            padding: 40px 30px;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }

        .nav-link {
            color: #1e3c72;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            display: inline-block;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #2a5298;
            text-decoration: underline;
        }

        /* ==== SECCIÓN DE BÚSQUEDA ==== */
        .search-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .search-title {
            font-size: 32px;
            font-weight: 800;
            color: #1e3c72;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            letter-spacing: -0.02em;
        }

        .search-subtitle {
            font-size: 18px;
            color: #64748b;
            margin-bottom: 35px;
            font-weight: 400;
        }

        .search-form {
            display: flex;
            gap: 20px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            max-width: 700px;
            margin: 0 auto;
        }

        .search-input {
            flex: 1;
            min-width: 350px;
            padding: 18px 25px;
            border: 2px solid rgba(226, 232, 240, 0.8);
            border-radius: 30px;
            font-size: 16px;
            font-weight: 400;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(248, 250, 252, 0.8);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .search-input:focus {
            outline: none;
            border-color: #1e3c72;
            background: white;
            box-shadow: 0 0 0 4px rgba(30, 60, 114, 0.1), 0 4px 12px rgba(0,0,0,0.05);
            transform: translateY(-1px);
        }

        .search-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .search-btn {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            padding: 18px 32px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 25px rgba(30, 60, 114, 0.3);
        }

        .search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(30, 60, 114, 0.4);
        }

        .back-btn {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
            text-decoration: none;
            padding: 14px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: absolute;
            top: 20px;
            left: 20px;
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(100, 116, 139, 0.4);
        }

        /* ==== RESULTADOS HEADER ==== */
        .results-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 25px;
            border-radius: 20px;
            margin-bottom: 25px;
            text-align: center;
            box-shadow: 0 12px 30px rgba(30, 60, 114, 0.3);
            position: relative;
            overflow: hidden;
        }

        .results-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }

        .results-title {
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
            z-index: 10;
        }

        /* ==== LISTA DE INSIGNIAS ==== */
        .insignias-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .insignia-card {
            padding: 35px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .insignia-card:last-child {
            border-bottom: none;
        }

        .insignia-card:hover {
            background: rgba(248, 250, 252, 0.8);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .insignia-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        .insignia-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .insignia-icon {
            font-size: 28px;
            margin-right: 18px;
            color: #f59e0b;
            filter: drop-shadow(0 2px 4px rgba(245, 158, 11, 0.3));
        }

        .insignia-name {
            font-size: 26px;
            font-weight: 800;
            color: #1e3c72;
            letter-spacing: -0.01em;
        }

        .insignia-details {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin-bottom: 25px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .detail-icon {
            font-size: 18px;
            color: #64748b;
            width: 24px;
            text-align: center;
        }

        .detail-label {
            font-size: 15px;
            color: #64748b;
            font-weight: 700;
            min-width: 120px;
            letter-spacing: 0.01em;
        }

        .detail-value {
            font-size: 16px;
            color: #334155;
            font-weight: 600;
        }

        .insignia-tags {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .tag {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #475569;
            padding: 10px 18px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .tag-icon {
            font-size: 14px;
        }

        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(30, 60, 114, 0.4);
        }

        /* ==== ESTADO VACÍO ==== */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 25px;
            opacity: 0.6;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
        }

        .empty-title {
            font-size: 24px;
            margin-bottom: 15px;
            color: #334155;
            font-weight: 700;
        }

        .empty-description {
            font-size: 16px;
            opacity: 0.8;
            margin-bottom: 25px;
            font-weight: 400;
        }

        .suggestions {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 25px;
            border-radius: 15px;
            margin-top: 25px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .suggestions-title {
            font-weight: 700;
            margin-bottom: 15px;
            color: #1e3c72;
            font-size: 16px;
        }

        .suggestions-list {
            list-style: none;
            padding: 0;
        }

        .suggestions-list li {
            padding: 8px 0;
            color: #64748b;
            font-weight: 500;
        }

        /* ==== RESPONSIVE ==== */
        @media (max-width: 768px) {
            .header {
                padding: 20px 20px;
                flex-direction: column;
                gap: 20px;
            }

            .main-container {
                padding: 25px 20px;
            }

            .search-section {
                padding: 30px 20px;
            }

            .search-title {
                font-size: 26px;
            }

            .search-input {
                min-width: auto;
                width: 100%;
            }

            .insignia-details {
                grid-template-columns: 1fr;
            }

            .insignia-actions {
                flex-direction: column;
            }

            .btn-action {
                justify-content: center;
            }

            .back-btn {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <!-- ==== ENCABEZADO ==== -->
    <header class="header">
        <h1>
            🔍 Resultados de Búsqueda
        </h1>
        <div class="header-actions">
            <a href="login.php" class="btn-header">← Volver al Login</a>
            <a href="index.php" class="btn-header">🏠 Inicio</a>
        </div>
    </header>

    <!-- ==== CONTENIDO PRINCIPAL ==== -->
    <div class="main-container">
        <!-- ==== BOTÓN VOLVER ==== -->
        <a href="login.php" class="back-btn">
            ← Volver al Login
        </a>

        <!-- ==== SECCIÓN DE BÚSQUEDA ==== -->
        <div class="search-section">
            <div class="search-title">
                🔍 Buscar Insignias
            </div>
            <div class="search-subtitle">
                Busca insignias por nombre o receptor
            </div>

            <form method="GET" action="" class="search-form">
                <input type="text" name="buscar" class="search-input" 
                       placeholder="Buscar por nombre de insignia, receptor o clave..." 
                       value="<?php echo htmlspecialchars($termino_busqueda); ?>" required>
                <button type="submit" class="search-btn">
                    🔍 Buscar
                </button>
            </form>
        </div>

        <!-- ==== HEADER DE RESULTADOS ==== -->
        <?php if ($total_resultados > 0): ?>
        <div class="results-header">
            <div class="results-title">
                🔍 Resultados para "<?php echo htmlspecialchars($termino_busqueda); ?>" (<?php echo $total_resultados; ?> encontrados)
            </div>
        </div>
        <?php endif; ?>

        <!-- ==== RESULTADOS ==== -->
        <div class="insignias-container">
            <?php if (empty($insignias)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🔍</div>
                    <div class="empty-title">No se encontraron resultados</div>
                    <div class="empty-description">
                        No se encontraron insignias que coincidan con el término "<?php echo htmlspecialchars($termino_busqueda); ?>"
                    </div>
                    
                    <div class="suggestions">
                        <div class="suggestions-title">💡 Sugerencias:</div>
                        <ul class="suggestions-list">
                            <li>• Verifica la ortografía del término de búsqueda</li>
                            <li>• Intenta con términos más generales</li>
                            <li>• Busca por nombre de la insignia o nombre del receptor</li>
                            <li>• Usa palabras clave como "Alfabetizatec", "Liderazgo", etc.</li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($insignias as $insignia): ?>
                    <div class="insignia-card">
                        <div class="insignia-header">
                            <div class="insignia-icon">🏆</div>
                            <div class="insignia-name">
                                <?php echo resaltarBusqueda($insignia['nombre_insignia'] ?? 'Insignia', $termino_busqueda); ?>
                            </div>
                        </div>

                        <div class="insignia-details">
                            <div class="detail-item">
                                <div class="detail-icon">👤</div>
                                <div class="detail-label">Receptor:</div>
                                <div class="detail-value">
                                    <?php echo resaltarBusqueda($insignia['destinatario'] ?? 'N/A', $termino_busqueda); ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">ℹ️</div>
                                <div class="detail-label">Descripción:</div>
                                <div class="detail-value">Estudiante</div>
                            </div>
                        </div>

                        <div class="insignia-tags">
                            <div class="tag">
                                <span class="tag-icon">📅</span>
                                Fecha: <?php echo formatearFecha($insignia['fecha_otorgamiento']); ?>
                            </div>
                            <div class="tag">
                                <span class="tag-icon">🏢</span>
                                Institución: <?php echo htmlspecialchars($insignia['institucion'] ?? 'Tecnológico Nacional de México'); ?>
                            </div>
                            <div class="tag">
                                <span class="tag-icon">👤</span>
                                Creador: <?php echo htmlspecialchars($insignia['responsable'] ?? 'Sistema'); ?>
                            </div>
                        </div>

                        <div class="insignia-actions" style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                            <a href="imagen_clickeable.php?codigo=<?php echo urlencode($insignia['clave_insignia']); ?>" 
                               class="btn-action" 
                               style="background: linear-gradient(135deg, #1b396a, #002855); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease;">
                                <span>🏆</span>
                                Ver Insignia
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
