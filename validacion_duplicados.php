<?php
// Sistema de validación de duplicados para insignias
require_once 'conexion.php';

/**
 * Función para verificar si un estudiante ya tiene una insignia específica
 * @param string $nombre_estudiante Nombre completo del estudiante
 * @param string $codigo_insignia Código de la insignia a verificar
 * @return array Resultado de la validación
 */
function verificarDuplicadoInsignia($nombre_estudiante, $codigo_insignia) {
    global $conexion;
    
    try {
        // Primero, obtener el tipo de insignia basado en el código
        $tipo_insignia = '';
        if (strpos($codigo_insignia, 'ART') !== false) {
            $tipo_insignia = 'Embajador del Arte';
        } elseif (strpos($codigo_insignia, 'EMB') !== false) {
            $tipo_insignia = 'Embajador del Deporte';
        } elseif (strpos($codigo_insignia, 'TAL') !== false) {
            $tipo_insignia = 'Talento Científico';
        } elseif (strpos($codigo_insignia, 'INN') !== false) {
            $tipo_insignia = 'Talento Innovador';
        } elseif (strpos($codigo_insignia, 'SOC') !== false) {
            $tipo_insignia = 'Responsabilidad Social';
        } elseif (strpos($codigo_insignia, 'FOR') !== false) {
            $tipo_insignia = 'Formación y Actualización';
        } elseif (strpos($codigo_insignia, 'MOV') !== false) {
            $tipo_insignia = 'Movilidad e Intercambio';
        }
        
        // Buscar si el estudiante ya tiene una insignia del mismo tipo
        $sql = "
            SELECT COUNT(*) as total, io.Codigo_Insignia, io.Fecha_Emision
            FROM insigniasotorgadas io
            LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario
            WHERE d.Nombre_Completo = ? 
            AND (
                (io.Codigo_Insignia LIKE '%ART%' AND ? = 'Embajador del Arte') OR
                (io.Codigo_Insignia LIKE '%EMB%' AND ? = 'Embajador del Deporte') OR
                (io.Codigo_Insignia LIKE '%TAL%' AND ? = 'Talento Científico') OR
                (io.Codigo_Insignia LIKE '%INN%' AND ? = 'Talento Innovador') OR
                (io.Codigo_Insignia LIKE '%SOC%' AND ? = 'Responsabilidad Social') OR
                (io.Codigo_Insignia LIKE '%FOR%' AND ? = 'Formación y Actualización') OR
                (io.Codigo_Insignia LIKE '%MOV%' AND ? = 'Movilidad e Intercambio')
            )
        ";
        
        $stmt = $conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssssss", $nombre_estudiante, $tipo_insignia, $tipo_insignia, $tipo_insignia, $tipo_insignia, $tipo_insignia, $tipo_insignia);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            
            return [
                'es_duplicado' => $data['total'] > 0,
                'total_encontrado' => $data['total'],
                'codigo_existente' => $data['Codigo_Insignia'] ?? null,
                'fecha_existente' => $data['Fecha_Emision'] ?? null,
                'tipo_insignia' => $tipo_insignia,
                'estudiante' => $nombre_estudiante
            ];
        }
        
        return [
            'es_duplicado' => false,
            'error' => 'Error en la consulta SQL'
        ];
        
    } catch (Exception $e) {
        return [
            'es_duplicado' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Función para mostrar el resultado de la validación
 */
function mostrarResultadoValidacion($resultado) {
    if (isset($resultado['error'])) {
        echo "<div class='error'>";
        echo "<h3>❌ Error en la validación</h3>";
        echo "<p>" . htmlspecialchars($resultado['error']) . "</p>";
        echo "</div>";
        return;
    }
    
    if ($resultado['es_duplicado']) {
        echo "<div class='error'>";
        echo "<h3>❌ DUPLICADO DETECTADO</h3>";
        echo "<p><strong>Estudiante:</strong> " . htmlspecialchars($resultado['estudiante']) . "</p>";
        echo "<p><strong>Tipo de Insignia:</strong> " . htmlspecialchars($resultado['tipo_insignia']) . "</p>";
        echo "<p><strong>Código Existente:</strong> " . htmlspecialchars($resultado['codigo_existente']) . "</p>";
        echo "<p><strong>Fecha de Emisión:</strong> " . htmlspecialchars($resultado['fecha_existente']) . "</p>";
        echo "<p><strong>Resultado:</strong> El estudiante YA TIENE esta insignia. No se puede otorgar la misma insignia dos veces.</p>";
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "<h3>✅ NO HAY DUPLICADO</h3>";
        echo "<p><strong>Estudiante:</strong> " . htmlspecialchars($resultado['estudiante']) . "</p>";
        echo "<p><strong>Tipo de Insignia:</strong> " . htmlspecialchars($resultado['tipo_insignia']) . "</p>";
        echo "<p><strong>Resultado:</strong> El estudiante NO TIENE esta insignia. Se puede otorgar.</p>";
        echo "</div>";
    }
}

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_estudiante = $_POST['nombre_estudiante'] ?? '';
    $codigo_insignia = $_POST['codigo_insignia'] ?? '';
    
    if (!empty($nombre_estudiante) && !empty($codigo_insignia)) {
        $resultado = verificarDuplicadoInsignia($nombre_estudiante, $codigo_insignia);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Duplicados - Insignias TecNM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 1200px;
            margin: 0 auto;
        }
        .success {
            background: #d4edda;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #dc3545;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            margin: 10px 0;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #2196f3;
            margin: 10px 0;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-success {
            background: #28a745;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Validación de Duplicados - Insignias TecNM</h1>
        
        <?php if (isset($resultado)): ?>
            <h2>🧪 Resultado de la Validación</h2>
            <?php mostrarResultadoValidacion($resultado); ?>
        <?php endif; ?>
        
        <!-- Formulario de validación -->
        <h2>🧪 Probar Validación de Duplicados</h2>
        <div class="info">
            <h3>Instrucciones:</h3>
            <ol>
                <li>Ingresa el nombre completo del estudiante</li>
                <li>Ingresa el código de la insignia a verificar</li>
                <li>Haz clic en 'Verificar Duplicado'</li>
                <li>El sistema te dirá si ya tiene esa insignia o no</li>
            </ol>
        </div>
        
        <form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <div class="form-group">
                <label for="nombre_estudiante">Nombre del Estudiante:</label>
                <input type="text" id="nombre_estudiante" name="nombre_estudiante" 
                       placeholder="Ej: Juan Perez Molina" required>
            </div>
            
            <div class="form-group">
                <label for="codigo_insignia">Código de Insignia:</label>
                <input type="text" id="codigo_insignia" name="codigo_insignia" 
                       placeholder="Ej: TECNM-ITSM-2025-ART-628" required>
            </div>
            
            <button type="submit" class="btn">🔍 Verificar Duplicado</button>
        </form>
        
        <!-- Mostrar insignias existentes -->
        <h2>📊 Insignias Actuales en el Sistema</h2>
        <?php
        try {
            $sql_insignias = "
                SELECT 
                    io.Codigo_Insignia,
                    d.Nombre_Completo as estudiante,
                    io.Fecha_Emision,
                    CASE 
                        WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                        WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                        WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                        WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                        WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                        WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                        WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                        ELSE 'Tipo Desconocido'
                    END as tipo_insignia
                FROM insigniasotorgadas io
                LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario
                ORDER BY d.Nombre_Completo, io.Fecha_Emision DESC
            ";
            
            $result = $conexion->query($sql_insignias);
            
            if ($result && $result->num_rows > 0) {
                echo "<table>";
                echo "<tr>";
                echo "<th>Estudiante</th>";
                echo "<th>Tipo de Insignia</th>";
                echo "<th>Código</th>";
                echo "<th>Fecha</th>";
                echo "<th>Acciones</th>";
                echo "</tr>";
                
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['estudiante']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tipo_insignia']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Codigo_Insignia']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Fecha_Emision']) . "</td>";
                    echo "<td>";
                    echo "<a href='validacion.php?insignia=" . urlencode($row['Codigo_Insignia']) . "' target='_blank' class='btn btn-success'>Ver</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<div class='warning'>";
                echo "<h3>⚠️ No hay insignias registradas</h3>";
                echo "<p>No se encontraron insignias en la base de datos.</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Error al consultar insignias</h3>";
            echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        ?>
        
        <!-- Información del sistema -->
        <h2>📋 Información del Sistema</h2>
        <div class="info">
            <h3>🔒 Cómo Funciona la Validación de Duplicados:</h3>
            <ol>
                <li><strong>Consulta:</strong> El sistema busca en la tabla 'insigniasotorgadas'</li>
                <li><strong>Verificación:</strong> Compara el tipo de insignia (basado en el código) y el nombre del estudiante</li>
                <li><strong>Resultado:</strong> Si encuentra una coincidencia, bloquea la creación</li>
                <li><strong>Mensaje:</strong> Muestra error: 'El estudiante ya tiene una insignia de [TIPO]'</li>
            </ol>
            
            <h3>✅ Ejemplo Práctico:</h3>
            <ul>
                <li>Si <strong>Juan Perez</strong> ya tiene <strong>Embajador del Arte</strong> (código con ART)</li>
                <li>No podrá recibir otra insignia de <strong>Embajador del Arte</strong></li>
                <li>SÍ podrá recibir otras insignias como <strong>Responsabilidad Social</strong> (código con SOC)</li>
                <li>Cada estudiante puede tener máximo 1 insignia de cada tipo</li>
            </ul>
            
            <h3>🏷️ Códigos de Insignias:</h3>
            <ul>
                <li><strong>ART</strong> = Embajador del Arte</li>
                <li><strong>EMB</strong> = Embajador del Deporte</li>
                <li><strong>TAL</strong> = Talento Científico</li>
                <li><strong>INN</strong> = Talento Innovador</li>
                <li><strong>SOC</strong> = Responsabilidad Social</li>
                <li><strong>FOR</strong> = Formación y Actualización</li>
                <li><strong>MOV</strong> = Movilidad e Intercambio</li>
            </ul>
        </div>
        
        <!-- Botones de acción -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="metadatos_formulario.php" class="btn">📝 Crear Nueva Insignia</a>
            <a href="validacion.php" class="btn btn-success">🔍 Validar Insignia</a>
            <a href="index.php" class="btn">🏠 Inicio</a>
        </div>
    </div>
</body>
</html>
