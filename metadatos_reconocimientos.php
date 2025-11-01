<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=sesion_invalida');
    exit();
}

// Verificar permisos (solo Admin y SuperUsuario pueden ver metadatos)
if ($_SESSION['rol'] !== 'Admin' && $_SESSION['rol'] !== 'SuperUsuario') {
    header('Location: login.php?error=acceso_denegado');
    exit();
}

require_once 'conexion.php';
$conexion->select_db("insignia");

// Función para obtener reconocimientos de forma segura
function obtenerReconocimientos($conexion) {
    $reconocimientos = [];
    
    // Primero intentar con la estructura nueva
    $sql_nueva = "SELECT 
        tio.id,
        tio.Fecha_Emision,
        tio.Evidencia,
        ti.Programa,
        ti.Descripcion,
        ti.Criterio,
        ti.Fecha_Creacion,
        ti.Fecha_Autorizacion,
        ti.Nombre_gen_ins,
        ti.Archivo_Visual,
        tin.Nombre_insignia,
        ci.Nombre_cat,
        d.Nombre_Completo,
        d.Nombre,
        d.Apellido_Paterno,
        d.Apellido_Materno,
        d.Correo,
        d.Rol,
        itc.Nombre_itc,
        itc.Acron,
        e.Estatus,
        pe.periodo
    FROM T_insignias_otorgadas tio
    JOIN T_insignias ti ON tio.Id_Insignia = ti.id
    JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
    JOIN cat_insignias ci ON tin.id = ci.id
    JOIN destinatario d ON tio.Id_Destinatario = d.id
    JOIN it_centros itc ON ti.Propone_Insignia = itc.id
    JOIN estatus e ON ti.Estatus = e.id
    JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
    ORDER BY tio.Fecha_Emision DESC";
    
    $resultado = $conexion->query($sql_nueva);
    
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $reconocimientos[] = $fila;
        }
        return $reconocimientos;
    }
    
    // Si no funciona, intentar con la estructura antigua
    $sql_antigua = "SELECT 
        io.id,
        io.fecha_otorgamiento as Fecha_Emision,
        io.evidencia as Evidencia,
        i.Programa,
        i.Descripcion,
        i.Criterio,
        i.Fecha_Creacion,
        i.Fecha_Autorizacion,
        i.Nombre_gen_ins,
        i.archivo_visual as Archivo_Visual,
        ti.Nombre_insignia,
        ci.Nombre_cat,
        d.Nombre_Completo,
        d.Nombre,
        d.Apellido_Paterno,
        d.Apellido_Materno,
        d.Correo,
        d.Rol,
        itc.Nombre_itc,
        itc.Acron,
        e.Estatus,
        pe.periodo
    FROM insigniasotorgadas io
    JOIN insignias i ON io.insignia_id = i.id
    JOIN tipo_insignia ti ON i.Tipo_Insignia = ti.id
    JOIN cat_insignias ci ON ti.id = ci.id
    JOIN destinatario d ON io.destinatario_id = d.id
    JOIN it_centros itc ON i.Propone_Insignia = itc.id
    JOIN estatus e ON i.Estatus = e.id
    JOIN periodo_emision pe ON io.periodo_id = pe.id
    ORDER BY io.fecha_otorgamiento DESC";
    
    $resultado = $conexion->query($sql_antigua);
    
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $reconocimientos[] = $fila;
        }
    }
    
    return $reconocimientos;
}

$reconocimientos = obtenerReconocimientos($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metadatos de Reconocimientos - Insignias TecNM</title>
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

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--bg-light);
            padding: 20px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--shadow-light);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 14px;
            font-weight: 600;
        }

        .reconocimientos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
        }

        .reconocimiento-card {
            background: var(--bg-white);
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow-light);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .reconocimiento-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
            border-color: var(--primary-color);
        }

        .reconocimiento-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .insignia-info {
            flex: 1;
        }

        .insignia-title {
            font-size: 18px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .insignia-category {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 600;
        }

        .insignia-code {
            background: var(--primary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            font-weight: bold;
        }

        .recipient-info {
            margin-bottom: 15px;
        }

        .recipient-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .recipient-details {
            font-size: 14px;
            color: var(--text-light);
        }

        .metadata-summary {
            margin-bottom: 20px;
        }

        .metadata-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .metadata-label {
            font-weight: 600;
            color: var(--text-dark);
        }

        .metadata-value {
            color: var(--text-light);
            text-align: right;
            max-width: 200px;
            word-wrap: break-word;
        }

        .card-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-small {
            padding: 8px 15px;
            font-size: 12px;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--border-color);
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .reconocimientos-grid {
                grid-template-columns: 1fr;
            }
            
            .content {
                padding: 20px;
            }
            
            .header {
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-database"></i> Metadatos de Reconocimientos</h1>
            <p>Sistema de Insignias Digitales TecNM</p>
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
                    <button onclick="window.print()" class="btn" style="background: linear-gradient(135deg, #6c757d, #495057);">
                        <i class="fas fa-print"></i>
                        Imprimir Lista
                    </button>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($reconocimientos); ?></div>
                    <div class="stat-label">Total Reconocimientos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_unique(array_column($reconocimientos, 'Nombre_itc'))); ?></div>
                    <div class="stat-label">Instituciones</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_unique(array_column($reconocimientos, 'Nombre_insignia'))); ?></div>
                    <div class="stat-label">Tipos de Insignias</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_unique(array_column($reconocimientos, 'Nombre_Completo'))); ?></div>
                    <div class="stat-label">Estudiantes Únicos</div>
                </div>
            </div>

            <!-- Lista de Reconocimientos -->
            <?php if (empty($reconocimientos)): ?>
                <div class="empty-state">
                    <i class="fas fa-medal"></i>
                    <h3>No hay reconocimientos registrados</h3>
                    <p>Comienza registrando tu primer reconocimiento para ver los metadatos aquí.</p>
                    <a href="registrar_reconocimiento.php" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        Registrar Primer Reconocimiento
                    </a>
                </div>
            <?php else: ?>
                <div class="reconocimientos-grid">
                    <?php foreach ($reconocimientos as $reconocimiento): ?>
                        <?php 
                        $codigo_identificacion = "TecNM-" . $reconocimiento['Acron'] . "-" . $reconocimiento['periodo'] . "-" . str_pad($reconocimiento['id'], 3, '0', STR_PAD_LEFT);
                        ?>
                        <div class="reconocimiento-card">
                            <div class="card-header">
                                <div class="insignia-info">
                                    <div class="insignia-title"><?php echo htmlspecialchars($reconocimiento['Nombre_insignia']); ?></div>
                                    <div class="insignia-category"><?php echo htmlspecialchars($reconocimiento['Nombre_cat']); ?></div>
                                </div>
                                <div class="insignia-code"><?php echo $codigo_identificacion; ?></div>
                            </div>

                            <div class="recipient-info">
                                <div class="recipient-name"><?php echo htmlspecialchars($reconocimiento['Nombre_Completo']); ?></div>
                                <div class="recipient-details">
                                    <?php echo htmlspecialchars($reconocimiento['Correo']); ?> • 
                                    <?php echo htmlspecialchars($reconocimiento['Nombre_itc']); ?>
                                </div>
                            </div>

                            <div class="metadata-summary">
                                <div class="metadata-item">
                                    <span class="metadata-label">Fecha de Emisión:</span>
                                    <span class="metadata-value"><?php echo date('d/m/Y', strtotime($reconocimiento['Fecha_Emision'])); ?></span>
                                </div>
                                <div class="metadata-item">
                                    <span class="metadata-label">Programa:</span>
                                    <span class="metadata-value"><?php echo htmlspecialchars($reconocimiento['Programa'] ?: 'No especificado'); ?></span>
                                </div>
                                <div class="metadata-item">
                                    <span class="metadata-label">Responsable:</span>
                                    <span class="metadata-value"><?php echo htmlspecialchars($reconocimiento['Nombre_gen_ins']); ?></span>
                                </div>
                                <div class="metadata-item">
                                    <span class="metadata-label">Estatus:</span>
                                    <span class="metadata-value"><?php echo htmlspecialchars($reconocimiento['Estatus']); ?></span>
                                </div>
                            </div>

                            <div class="card-actions">
                                <a href="imagen_clickeable.php?codigo=<?php echo urlencode($codigo_identificacion); ?>" class="btn btn-small btn-primary">
                                    <i class="fas fa-medal"></i>
                                    Ver Insignia
                                </a>
                                <a href="ver_metadatos_insignia.php?id=<?php echo $reconocimiento['id']; ?>" class="btn btn-small btn-outline">
                                    <i class="fas fa-eye"></i>
                                    Ver Metadatos
                                </a>
                                <button onclick="imprimirReconocimiento(<?php echo $reconocimiento['id']; ?>)" class="btn btn-small btn-outline">
                                    <i class="fas fa-print"></i>
                                    Imprimir
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function imprimirReconocimiento(id) {
            window.open('ver_metadatos_insignia.php?id=' + id, '_blank');
        }
    </script>
</body>
</html>