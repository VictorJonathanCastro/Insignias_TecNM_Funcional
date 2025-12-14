<?php
// Prueba del sistema dinámico de insignias
require_once 'conexion.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Sistema Dinámico de Insignias</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }";
echo ".container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 1200px; margin: 0 auto; }";
echo ".success { background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745; margin: 10px 0; }";
echo ".error { background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545; margin: 10px 0; }";
echo ".info { background: #e3f2fd; padding: 15px; border-radius: 5px; border-left: 4px solid #2196f3; margin: 10px 0; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo ".insignia-test { display: inline-block; margin: 20px; padding: 20px; border: 2px solid #ddd; border-radius: 10px; text-align: center; }";
echo ".insignia-image { width: 100px; height: 100px; background-size: contain; background-repeat: no-repeat; background-position: center; margin: 0 auto 10px; }";
echo "table { width: 100%; border-collapse: collapse; margin: 20px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }";
echo "th { background-color: #f8f9fa; font-weight: bold; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🎖️ Sistema Dinámico de Insignias</h1>";

// Función para determinar la insignia dinámicamente
function determinarInsigniaDinamica($codigo_insignia, $nombre_insignia) {
    // Mapeo de códigos a tipos de insignia
    $mapeo_codigos = [
        'ART' => 'Embajador del Arte',
        'EMB' => 'Embajador del Deporte', 
        'TAL' => 'Talento Científico',
        'INN' => 'Talento Innovador',
        'SOC' => 'Responsabilidad Social',
        'FOR' => 'Formación y Actualización',
        'MOV' => 'Movilidad e Intercambio'
    ];
    
    // Mapeo de nombres de insignias a archivos PNG
    $mapeo_imagenes = [
        'Movilidad e Intercambio' => 'MovilidadeIntercambio.png',
        'Embajador del Deporte' => 'EmbajadordelDeporte.png',
        'Embajador del Arte' => 'EmbajadordelArte.png',
        'Formación y Actualización' => 'FormacionyActualizacion.png',
        'Talento Científico' => 'TalentoCientifico.png',
        'Talento Innovador' => 'TalentoInnovador.png',
        'Responsabilidad Social' => 'ResponsabilidadSocial.png'
    ];
    
    $resultado = [];
    
    // 1. Intentar determinar por código
    foreach ($mapeo_codigos as $codigo => $tipo) {
        if (strpos($codigo_insignia, $codigo) !== false) {
            $resultado['metodo'] = 'código';
            $resultado['codigo_encontrado'] = $codigo;
            $resultado['tipo'] = $tipo;
            $resultado['archivo'] = $mapeo_imagenes[$tipo] ?? null;
            return $resultado;
        }
    }
    
    // 2. Intentar determinar por nombre de insignia
    if (isset($mapeo_imagenes[$nombre_insignia])) {
        $resultado['metodo'] = 'nombre_exacto';
        $resultado['tipo'] = $nombre_insignia;
        $resultado['archivo'] = $mapeo_imagenes[$nombre_insignia];
        return $resultado;
    }
    
    // 3. Buscar coincidencias parciales en el nombre
    foreach ($mapeo_imagenes as $tipo => $archivo) {
        if (strpos($nombre_insignia, $tipo) !== false || strpos($tipo, $nombre_insignia) !== false) {
            $resultado['metodo'] = 'coincidencia_parcial';
            $resultado['tipo'] = $tipo;
            $resultado['archivo'] = $archivo;
            return $resultado;
        }
    }
    
    // 4. Fallback
    $resultado['metodo'] = 'fallback';
    $resultado['tipo'] = 'Primera disponible';
    $resultado['archivo'] = reset($mapeo_imagenes);
    return $resultado;
}

// Probar con diferentes códigos de insignia
$codigos_prueba = [
    'TECNM-ITSM-2025-ART-336' => 'Embajador del Arte',
    'TECNM-ITSM-2025-EMB-001' => 'Embajador del Deporte',
    'TECNM-ITSM-2025-TAL-002' => 'Talento Científico',
    'TECNM-ITSM-2025-INN-003' => 'Talento Innovador',
    'TECNM-ITSM-2025-SOC-004' => 'Responsabilidad Social',
    'TECNM-ITSM-2025-FOR-005' => 'Formación y Actualización',
    'TECNM-ITSM-2025-MOV-006' => 'Movilidad e Intercambio'
];

echo "<h2>🧪 Pruebas del Sistema Dinámico</h2>";

echo "<table>";
echo "<tr>";
echo "<th>Código de Insignia</th>";
echo "<th>Nombre Esperado</th>";
echo "<th>Método de Detección</th>";
echo "<th>Archivo de Imagen</th>";
echo "<th>Archivo Existe</th>";
echo "<th>Vista Previa</th>";
echo "</tr>";

foreach ($codigos_prueba as $codigo => $nombre_esperado) {
    $resultado = determinarInsigniaDinamica($codigo, $nombre_esperado);
    $imagen_path = 'imagen/Insignias/' . $resultado['archivo'];
    $archivo_existe = file_exists($imagen_path);
    
    echo "<tr>";
    echo "<td><strong>$codigo</strong></td>";
    echo "<td>$nombre_esperado</td>";
    echo "<td>" . ucfirst(str_replace('_', ' ', $resultado['metodo'])) . "</td>";
    echo "<td>" . $resultado['archivo'] . "</td>";
    echo "<td>" . ($archivo_existe ? "✅ Sí" : "❌ No") . "</td>";
    echo "<td>";
    if ($archivo_existe) {
        echo "<div style='width: 50px; height: 50px; background-image: url($imagen_path); background-size: contain; background-repeat: no-repeat; background-position: center; margin: 0 auto;'></div>";
    } else {
        echo "<div style='width: 50px; height: 50px; background: #f0f0f0; margin: 0 auto;'></div>";
    }
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

// Probar con datos reales de la base de datos
echo "<h2>📊 Datos Reales de la Base de Datos</h2>";

try {
    $sql = "SELECT DISTINCT io.clave_insignia, ti.Nombre_ins as nombre_insignia 
            FROM insigniasotorgadas io
            LEFT JOIN insignias i ON io.insignia_id = i.id
            LEFT JOIN tipo_insignia ti ON i.Tipo_Insignia = ti.id
            ORDER BY io.clave_insignia
            LIMIT 10";
    
    $result = $conexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr>";
        echo "<th>Código Real</th>";
        echo "<th>Nombre en BD</th>";
        echo "<th>Método de Detección</th>";
        echo "<th>Archivo de Imagen</th>";
        echo "<th>Archivo Existe</th>";
        echo "<th>Acción</th>";
        echo "</tr>";
        
        while ($row = $result->fetch_assoc()) {
            $codigo = $row['clave_insignia'];
            $nombre = $row['nombre_insignia'];
            
            $resultado = determinarInsigniaDinamica($codigo, $nombre);
            $imagen_path = 'imagen/Insignias/' . $resultado['archivo'];
            $archivo_existe = file_exists($imagen_path);
            
            echo "<tr>";
            echo "<td><strong>$codigo</strong></td>";
            echo "<td>$nombre</td>";
            echo "<td>" . ucfirst(str_replace('_', ' ', $resultado['metodo'])) . "</td>";
            echo "<td>" . $resultado['archivo'] . "</td>";
            echo "<td>" . ($archivo_existe ? "✅ Sí" : "❌ No") . "</td>";
            echo "<td><a href='validacion.php?insignia=" . urlencode($codigo) . "' target='_blank' class='btn'>Ver</a></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<div class='warning'>";
        echo "<h3>⚠️ No hay insignias en la base de datos</h3>";
        echo "<p>No se encontraron insignias para probar.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error al consultar base de datos</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<h2>🔧 Cómo Funciona el Sistema Dinámico</h2>";
echo "<div class='info'>";
echo "<h3>📋 Proceso de Detección:</h3>";
echo "<ol>";
echo "<li><strong>Por Código:</strong> Busca códigos como 'ART', 'EMB', 'TAL', etc. en el código de insignia</li>";
echo "<li><strong>Por Nombre Exacto:</strong> Compara el nombre de insignia con el mapeo</li>";
echo "<li><strong>Por Coincidencia Parcial:</strong> Busca coincidencias parciales en el nombre</li>";
echo "<li><strong>Fallback:</strong> Usa la primera imagen disponible si no encuentra coincidencia</li>";
echo "</ol>";

echo "<h3>🎯 Ventajas del Sistema Dinámico:</h3>";
echo "<ul>";
echo "<li>✅ Se adapta automáticamente a nuevos tipos de insignia</li>";
echo "<li>✅ Funciona con diferentes formatos de código</li>";
echo "<li>✅ Tiene múltiples métodos de detección</li>";
echo "<li>✅ Siempre muestra una imagen válida</li>";
echo "<li>✅ Fácil de mantener y actualizar</li>";
echo "</ul>";
echo "</div>";

// Botones de acción
echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='validacion.php?insignia=TECNM-ITSM-2025-ART-336' target='_blank' class='btn'>🔗 Ver Certificado ART</a>";
echo "<a href='metadatos_formulario.php' class='btn'>📝 Crear Nueva Insignia</a>";
echo "<button onclick='window.location.reload()' class='btn'>🔄 Recargar Pruebas</button>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
