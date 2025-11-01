<?php
session_start();
require_once 'conexion.php';

// Consultar instituciones de la base de datos
$instituciones = [];
$total = 0;

try {
    $sql = "SELECT Nombre_itc, Acron, Estado, Clave_ct, Tipo_itc 
            FROM it_centros 
            ORDER BY Estado, Nombre_itc";
    $result = $conexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $instituciones[] = $row;
        }
    }
    $total = count($instituciones);
} catch (Exception $e) {
    $error = "Error al consultar instituciones: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instituciones TecNM - Lista Completa</title>
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
            --azul-sky: #E3F2FD;
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
            max-width: 1200px;
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
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .stats-bar {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .stats-bar h2 {
            color: var(--azul-oscuro);
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stats-bar p {
            color: var(--texto-gris);
            font-size: 1.1rem;
        }
        
        .filtros {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filtros label {
            font-weight: 600;
            color: var(--azul-oscuro);
        }
        
        .filtros input, .filtros select {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .filtros input:focus, .filtros select:focus {
            outline: none;
            border-color: var(--azul-claro);
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }
        
        .instituciones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .institucion-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--azul-claro);
        }
        
        .institucion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .institucion-nombre {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--azul-oscuro);
            margin-bottom: 10px;
        }
        
        .institucion-detalle {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            color: var(--texto-gris);
            font-size: 0.95rem;
        }
        
        .institucion-detalle i {
            color: var(--azul-claro);
            width: 20px;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .badge-estado {
            background: var(--azul-sky);
            color: var(--azul-oscuro);
        }
        
        .badge-tipo {
            background: var(--azul-claro);
            color: white;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: var(--texto-gris);
        }
        
        .no-results i {
            font-size: 4rem;
            color: var(--azul-claro);
            margin-bottom: 20px;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: var(--texto-gris);
        }
        
        @media (max-width: 768px) {
            .instituciones-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .filtros {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filtros input, .filtros select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>
                <i class="fas fa-building"></i>
                Instituciones del TecNM
            </h1>
            <a href="index.php" class="btn-volver">
                <i class="fas fa-arrow-left"></i>
                Volver al Inicio
            </a>
        </div>
    </div>
    
    <div class="container">
        <div class="stats-bar">
            <h2><?php echo $total; ?> Instituciones</h2>
            <p>Tecnológico Nacional de México</p>
        </div>
        
        <div class="filtros">
            <label for="buscar"><i class="fas fa-search"></i> Buscar:</label>
            <input type="text" id="buscar" placeholder="Nombre de la institución..." style="flex: 1; min-width: 200px;">
            
            <label for="estado">Estado:</label>
            <select id="estado">
                <option value="">Todos los estados</option>
                <?php
                $estados = array_unique(array_column($instituciones, 'Estado'));
                sort($estados);
                foreach ($estados as $estado) {
                    if (!empty($estado)) {
                        echo "<option value=\"$estado\">$estado</option>";
                    }
                }
                ?>
            </select>
            
            <label for="tipo">Tipo:</label>
            <select id="tipo">
                <option value="">Todos los tipos</option>
                <?php
                $tipos = array_unique(array_column($instituciones, 'Tipo_itc'));
                sort($tipos);
                foreach ($tipos as $tipo) {
                    if (!empty($tipo)) {
                        echo "<option value=\"$tipo\">$tipo</option>";
                    }
                }
                ?>
            </select>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="no-results">
                <i class="fas fa-exclamation-triangle"></i>
                <h3><?php echo $error; ?></h3>
            </div>
        <?php elseif ($total > 0): ?>
            <div class="instituciones-grid" id="institucionesGrid">
                <?php foreach ($instituciones as $inst): ?>
                    <div class="institucion-card" 
                         data-nombre="<?php echo htmlspecialchars(strtolower($inst['Nombre_itc'])); ?>"
                         data-estado="<?php echo htmlspecialchars($inst['Estado']); ?>"
                         data-tipo="<?php echo htmlspecialchars($inst['Tipo_itc']); ?>">
                        <div class="institucion-nombre">
                            <?php echo htmlspecialchars($inst['Nombre_itc']); ?>
                        </div>
                        
                        <?php if (!empty($inst['Acron'])): ?>
                            <div class="institucion-detalle">
                                <i class="fas fa-tag"></i>
                                <strong>Siglas:</strong> <?php echo htmlspecialchars($inst['Acron']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($inst['Estado'])): ?>
                            <div class="institucion-detalle">
                                <i class="fas fa-map-marker-alt"></i>
                                <strong>Estado:</strong> <?php echo htmlspecialchars($inst['Estado']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($inst['Clave_ct'])): ?>
                            <div class="institucion-detalle">
                                <i class="fas fa-key"></i>
                                <strong>Clave:</strong> <?php echo htmlspecialchars($inst['Clave_ct']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($inst['Tipo_itc'])): ?>
                            <span class="badge badge-tipo"><?php echo htmlspecialchars($inst['Tipo_itc']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-building"></i>
                <h3>No hay instituciones registradas</h3>
                <p>Las instituciones se cargarán desde la base de datos.</p>
                <p style="margin-top: 20px; color: var(--azul-claro);">
                    <a href="index.php" style="color: var(--azul-claro); text-decoration: underline;">
                        Volver al inicio
                    </a>
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Filtrado en tiempo real
        const buscarInput = document.getElementById('buscar');
        const estadoSelect = document.getElementById('estado');
        const tipoSelect = document.getElementById('tipo');
        const institucionesGrid = document.getElementById('institucionesGrid');
        
        function filtrarInstituciones() {
            const buscar = buscarInput.value.toLowerCase().trim();
            const estado = estadoSelect.value;
            const tipo = tipoSelect.value;
            
            const cards = institucionesGrid.querySelectorAll('.institucion-card');
            let visible = 0;
            
            cards.forEach(card => {
                const nombre = card.dataset.nombre || '';
                const cardEstado = card.dataset.estado || '';
                const cardTipo = card.dataset.tipo || '';
                
                const coincideNombre = nombre.includes(buscar);
                const coincideEstado = !estado || cardEstado === estado;
                const coincideTipo = !tipo || cardTipo === tipo;
                
                if (coincideNombre && coincideEstado && coincideTipo) {
                    card.style.display = 'block';
                    visible++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Mostrar mensaje si no hay resultados
            let noResultsMsg = document.querySelector('.no-results-search');
            if (visible === 0 && cards.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results no-results-search';
                    noResultsMsg.innerHTML = `
                        <i class="fas fa-search"></i>
                        <h3>No se encontraron instituciones</h3>
                        <p>Intenta con otros criterios de búsqueda</p>
                    `;
                    institucionesGrid.parentNode.insertBefore(noResultsMsg, institucionesGrid.nextSibling);
                }
                institucionesGrid.style.display = 'none';
            } else {
                if (noResultsMsg) {
                    noResultsMsg.remove();
                }
                institucionesGrid.style.display = 'grid';
            }
        }
        
        buscarInput.addEventListener('input', filtrarInstituciones);
        estadoSelect.addEventListener('change', filtrarInstituciones);
        tipoSelect.addEventListener('change', filtrarInstituciones);
    </script>
</body>
</html>

