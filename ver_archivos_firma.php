<?php
session_start();
require_once 'conexion.php';

// Verificar sesión de administrador
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'Admin' && $_SESSION['rol'] !== 'SuperUsuario')) {
    header('Location: login.php');
    exit();
}

$directorio_firmas = 'firmas_digitales/';
$archivo_seleccionado = $_GET['archivo'] ?? '';

// Obtener todas las firmas de la base de datos
$firmas = [];
try {
    $sql = "SELECT id, responsable_id, nombre_responsable, archivo_firma, hash_verificacion, 
                   fecha_generacion, activa, fecha_creacion 
            FROM firmas_digitales 
            ORDER BY fecha_creacion DESC";
    $result = $conexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $firmas[] = $row;
        }
    }
} catch (Exception $e) {
    $error = "Error al consultar firmas: " . $e->getMessage();
}

// Si se solicita ver un archivo específico
$contenido_archivo = '';
$nombre_archivo_actual = '';
if ($archivo_seleccionado && file_exists($directorio_firmas . $archivo_seleccionado)) {
    $nombre_archivo_actual = $archivo_seleccionado;
    $contenido_archivo = file_get_contents($directorio_firmas . $archivo_seleccionado);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archivos de Firma Digital - TecNM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --azul-oscuro: #003366;
            --azul-medio: #0066CC;
            --azul-claro: #1976d2;
            --blanco: #FFFFFF;
            --gris-claro: #F5F7FA;
            --texto-oscuro: #1a1a1a;
            --texto-gris: #6B7280;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #e8f0f8 0%, #ddebf5 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, 
                rgba(30, 60, 114, 0.95) 0%, 
                rgba(42, 82, 152, 0.98) 50%,
                rgba(30, 60, 114, 0.95) 100%);
            color: white;
            padding: 25px 0;
            margin: -20px -20px 30px -20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .header h1 {
            font-size: 1.8rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn-volver {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-volver:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 30px;
        }
        
        .sidebar {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .sidebar h2 {
            color: var(--azul-oscuro);
            font-size: 1.4rem;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--azul-claro);
        }
        
        .firmas-lista {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .firma-item {
            padding: 15px;
            margin-bottom: 12px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--gris-claro);
        }
        
        .firma-item:hover {
            border-color: var(--azul-claro);
            background: #E3F2FD;
            transform: translateX(5px);
        }
        
        .firma-item.activa {
            border-color: var(--azul-claro);
            background: #E3F2FD;
        }
        
        .firma-item h3 {
            color: var(--azul-oscuro);
            font-size: 1rem;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .firma-item .firma-info {
            font-size: 0.85rem;
            color: var(--texto-gris);
            margin-bottom: 5px;
        }
        
        .firma-item .firma-info i {
            width: 18px;
            color: var(--azul-claro);
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 8px;
        }
        
        .badge-activa {
            background: #10b981;
            color: white;
        }
        
        .badge-inactiva {
            background: #ef4444;
            color: white;
        }
        
        .contenido-area {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            min-height: 500px;
        }
        
        .contenido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--gris-claro);
        }
        
        .contenido-header h2 {
            color: var(--azul-oscuro);
            font-size: 1.5rem;
        }
        
        .btn-descargar {
            background: var(--azul-claro);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-descargar:hover {
            background: var(--azul-medio);
            transform: translateY(-2px);
        }
        
        .contenido-archivo {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 600px;
            overflow-y: auto;
        }
        
        .vista-previa {
            border: 1px solid #e0e0e0;
            padding: 20px;
            border-radius: 10px;
            background: white;
        }
        
        .no-seleccionado {
            text-align: center;
            padding: 80px 20px;
            color: var(--texto-gris);
        }
        
        .no-seleccionado i {
            font-size: 4rem;
            color: var(--azul-claro);
            margin-bottom: 20px;
        }
        
        .stats-bar {
            background: #E3F2FD;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .stats-bar strong {
            color: var(--azul-oscuro);
            font-size: 1.2rem;
        }
        
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                position: relative;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>
                <i class="fas fa-file-signature"></i>
                Archivos de Firma Digital
            </h1>
            <a href="modulo_de_administracion.php" class="btn-volver">
                <i class="fas fa-arrow-left"></i>
                Volver al Módulo
            </a>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <h2><i class="fas fa-list"></i> Lista de Firmas</h2>
            
            <div class="stats-bar">
                <strong><?php echo count($firmas); ?></strong> firmas registradas
            </div>
            
            <div class="firmas-lista">
                <?php if (!empty($firmas)): ?>
                    <?php foreach ($firmas as $firma): ?>
                        <div class="firma-item <?php echo ($archivo_seleccionado === $firma['archivo_firma']) ? 'activa' : ''; ?>"
                             onclick="window.location.href='?archivo=<?php echo urlencode($firma['archivo_firma']); ?>'">
                            <h3><?php echo htmlspecialchars($firma['nombre_responsable']); ?></h3>
                            <div class="firma-info">
                                <i class="fas fa-file"></i>
                                <strong>Archivo:</strong> <?php echo htmlspecialchars($firma['archivo_firma']); ?>
                            </div>
                            <div class="firma-info">
                                <i class="fas fa-calendar"></i>
                                <strong>Creado:</strong> <?php echo date('d/m/Y H:i', strtotime($firma['fecha_creacion'])); ?>
                            </div>
                            <?php if (!empty($firma['hash_verificacion'])): ?>
                                <div class="firma-info">
                                    <i class="fas fa-fingerprint"></i>
                                    <strong>Hash:</strong> <?php echo substr(htmlspecialchars($firma['hash_verificacion']), 0, 20); ?>...
                                </div>
                            <?php endif; ?>
                            <span class="badge <?php echo $firma['activa'] ? 'badge-activa' : 'badge-inactiva'; ?>">
                                <?php echo $firma['activa'] ? 'Activa' : 'Inactiva'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-seleccionado">
                        <i class="fas fa-inbox"></i>
                        <p>No hay firmas registradas</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="contenido-area">
            <?php if ($contenido_archivo): ?>
                <div class="contenido-header">
                    <h2>
                        <i class="fas fa-file-code"></i>
                        <?php echo htmlspecialchars($nombre_archivo_actual); ?>
                    </h2>
                    <a href="<?php echo $directorio_firmas . $archivo_seleccionado; ?>" 
                       class="btn-descargar" 
                       download>
                        <i class="fas fa-download"></i>
                        Descargar
                    </a>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h3 style="color: var(--azul-oscuro); margin-bottom: 10px;">Vista Previa HTML:</h3>
                    <div class="vista-previa">
                        <?php echo $contenido_archivo; ?>
                    </div>
                </div>
                
                <div>
                    <h3 style="color: var(--azul-oscuro); margin-bottom: 10px;">Código Fuente:</h3>
                    <div class="contenido-archivo">
                        <?php echo htmlspecialchars($contenido_archivo); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-seleccionado">
                    <i class="fas fa-hand-pointer"></i>
                    <h3>Selecciona un archivo de firma</h3>
                    <p>Haz clic en una firma de la lista para ver su contenido</p>
                    <p style="margin-top: 20px; font-size: 0.9rem; color: var(--texto-gris);">
                        Los archivos se encuentran en: <code style="background: #f0f0f0; padding: 5px 10px; border-radius: 5px;"><?php echo $directorio_firmas; ?></code>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

