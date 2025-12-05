<?php
// ========================================
// HISTORIAL DE CARGAS MASIVAS
// Sistema de Insignias TecNM
// ========================================

session_start();
require_once 'conexion.php';

// Verificar sesión de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

// Obtener historial de cargas
$historial = [];
try {
    // Verificar si la tabla existe
    $check_table = $conexion->query("SHOW TABLES LIKE 'historial_cargas_masivas'");
    if ($check_table->num_rows > 0) {
        $sql = "SELECT h.*, u.nombre as usuario_nombre_completo 
                FROM historial_cargas_masivas h
                LEFT JOIN usuarios u ON h.usuario_id = u.id
                ORDER BY h.fecha_carga DESC
                LIMIT 100";
        $resultado = $conexion->query($sql);
        
        if ($resultado) {
            $historial = $resultado->fetch_all(MYSQLI_ASSOC);
        }
    }
} catch (Exception $e) {
    $error_historial = "Error al cargar historial: " . $e->getMessage();
}

// Función para formatear tamaño de archivo
function formatearTamanio($bytes) {
    if ($bytes == 0) return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

// Función para obtener badge de estado
function getBadgeEstado($estado) {
    $badges = [
        'completado' => '<span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">✅ Completado</span>',
        'con_errores' => '<span style="background: #ffc107; color: #000; padding: 4px 8px; border-radius: 4px; font-size: 12px;">⚠️ Con Errores</span>',
        'fallido' => '<span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">❌ Fallido</span>'
    ];
    return $badges[$estado] ?? '<span>' . htmlspecialchars($estado) . '</span>';
}

// Función para obtener nombre del tipo de carga
function getNombreTipoCarga($tipo) {
    $tipos = [
        'centros_it' => 'Centros IT',
        'destinatarios' => 'Destinatarios',
        'insignias_otorgadas' => 'Insignias Otorgadas',
        'tipos_insignia' => 'Tipos de Insignia',
        'categorias_insignia' => 'Categorías de Insignia',
        'periodos_emision' => 'Periodos de Emisión',
        'estatus' => 'Estatus',
        'responsables_emision' => 'Responsables de Emisión',
        'insignias_maestras' => 'Insignias Maestras',
        'usuarios' => 'Usuarios',
        'todas_las_tablas' => 'Todas las Tablas'
    ];
    return $tipos[$tipo] ?? $tipo;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Cargas Masivas - Insignias TecNM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: 
                radial-gradient(circle at 15% 25%, rgba(0, 102, 204, 0.18) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(0, 51, 102, 0.15) 0%, transparent 45%),
                linear-gradient(135deg, #e8f0f8 0%, #d5e3f0 20%, #c5d8ec 40%, #d5e3f0 60%, #e8f0f8 80%, #f0f5fa 100%);
            background-attachment: fixed;
            min-height: 100vh;
            padding-top: 100px;
            padding-bottom: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%);
            color: white;
            text-align: center;
            padding: 30px 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
        }
        
        header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
        }
        
        .back-btn {
            position: fixed;
            top: 100px;
            left: 20px;
            background: linear-gradient(135deg, #003366 0%, #0066CC 50%, #003366 100%);
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0,51,102,0.3);
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,51,102,0.4);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .header-section {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header-section h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 5px solid;
        }
        
        .alert-info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .btn-action {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>📊 Historial de Cargas Masivas</h1>
        </div>
    </header>
    
    <a href="modulo_de_administracion.php" class="back-btn">← Volver al Panel</a>
    <a href="carga_masiva_excel.php" class="back-btn" style="left: 200px;">📤 Nueva Carga</a>
    
    <div class="container">
        <div class="header-section">
            <h1>📋 Historial de Cargas</h1>
            <p>Registro de todas las cargas masivas realizadas</p>
        </div>
        
        <div class="content">
            <?php if (isset($error_historial)): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Información:</strong> <?php echo htmlspecialchars($error_historial); ?>
                    <br><br>
                    <strong>Para activar el historial, ejecuta el siguiente script SQL:</strong>
                    <br>
                    <code>crear_tabla_historial_cargas.sql</code>
                </div>
            <?php elseif (empty($historial)): ?>
                <div class="no-data">
                    <h2>📭 No hay registros de cargas</h2>
                    <p>Aún no se han realizado cargas masivas o la tabla de historial no existe.</p>
                    <br>
                    <a href="carga_masiva_excel.php" class="btn-action">Realizar Primera Carga</a>
                </div>
            <?php else: ?>
                <!-- Estadísticas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo count($historial); ?></h3>
                        <p>Total de Cargas</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                        <h3><?php echo array_sum(array_column($historial, 'registros_exitosos')); ?></h3>
                        <p>Registros Exitosos</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                        <h3><?php echo array_sum(array_column($historial, 'registros_con_error')); ?></h3>
                        <p>Registros con Error</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                        <h3><?php echo array_sum(array_column($historial, 'registros_actualizados')); ?></h3>
                        <p>Registros Actualizados</p>
                    </div>
                </div>
                
                <!-- Tabla de Historial -->
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Archivo</th>
                            <th>Tipo</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Exitosos</th>
                            <th>Actualizados</th>
                            <th>Errores</th>
                            <th>Tamaño</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $carga): ?>
                            <tr>
                                <td><?php echo $carga['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($carga['nombre_archivo']); ?></strong></td>
                                <td><?php echo getNombreTipoCarga($carga['tipo_carga']); ?></td>
                                <td><?php echo htmlspecialchars($carga['usuario_nombre'] ?? $carga['usuario_nombre'] ?? 'N/A'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($carga['fecha_carga'])); ?></td>
                                <td><?php echo $carga['total_registros']; ?></td>
                                <td style="color: #28a745; font-weight: bold;"><?php echo $carga['registros_exitosos']; ?></td>
                                <td style="color: #17a2b8; font-weight: bold;"><?php echo $carga['registros_actualizados']; ?></td>
                                <td style="color: #dc3545; font-weight: bold;"><?php echo $carga['registros_con_error']; ?></td>
                                <td><?php echo formatearTamanio($carga['tamanio_archivo'] ?? 0); ?></td>
                                <td><?php echo getBadgeEstado($carga['estado']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

