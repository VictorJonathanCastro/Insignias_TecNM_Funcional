<?php
require_once 'conexion.php';

echo "<h2>🔄 Actualizar Insignias Existentes con Imágenes Correctas</h2>";

// Obtener todas las insignias existentes
echo "<h3>1. Insignias existentes en la base de datos</h3>";
$sql_insignias = "SELECT 
                    io.clave_insignia,
                    io.insignia_id,
                    ti.Nombre_ins as nombre_subcategoria,
                    ti.Arch_ima as archivo_imagen_actual,
                    ci.Nombre_cat as nombre_categoria
                  FROM insigniasotorgadas io
                  LEFT JOIN insignias i ON io.insignia_id = i.id
                  LEFT JOIN tipo_insignia ti ON i.Tipo_Insignia = ti.id
                  LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.id
                  ORDER BY io.clave_insignia";

$resultado = $conexion->query($sql_insignias);

if ($resultado && $resultado->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Código</th><th>Subcategoría</th><th>Categoría</th><th>Imagen Actual</th><th>Acción</th></tr>";
    
    while ($row = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['clave_insignia']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre_subcategoria']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre_categoria']) . "</td>";
        echo "<td>" . htmlspecialchars($row['archivo_imagen_actual'] ?? 'NULL') . "</td>";
        echo "<td><a href='?actualizar=" . urlencode($row['clave_insignia']) . "'>🔄 Actualizar</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No se encontraron insignias";
}

// Procesar actualización si se solicita
if (isset($_GET['actualizar'])) {
    $codigo_actualizar = $_GET['actualizar'];
    
    echo "<h3>2. Actualizando insignia: $codigo_actualizar</h3>";
    
    // Obtener datos de la insignia
    $sql_obtener = "SELECT 
                      io.insignia_id,
                      ti.Nombre_ins as nombre_subcategoria,
                      ti.Arch_ima as archivo_imagen_actual
                    FROM insigniasotorgadas io
                    LEFT JOIN insignias i ON io.insignia_id = i.id
                    LEFT JOIN tipo_insignia ti ON i.Tipo_Insignia = ti.id
                    WHERE io.clave_insignia = ?";
    
    $stmt = $conexion->prepare($sql_obtener);
    if ($stmt) {
        $stmt->bind_param("s", $codigo_actualizar);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $nombre_subcategoria = $row['nombre_subcategoria'];
            $archivo_actual = $row['archivo_imagen_actual'];
            
            echo "<p><strong>Subcategoría:</strong> $nombre_subcategoria</p>";
            echo "<p><strong>Archivo actual:</strong> " . ($archivo_actual ?? 'NULL') . "</p>";
            
            // Verificar si ya tiene la imagen correcta
            $imagen_correcta = '';
            switch ($nombre_subcategoria) {
                case 'Embajador del Deporte':
                    $imagen_correcta = 'EmbajadordelDeporte.png';
                    break;
                case 'Movilidad e Intercambio':
                    $imagen_correcta = 'MovilidadeIntercambio.png';
                    break;
                case 'Responsabilidad Social':
                    $imagen_correcta = 'ResponsabilidadSocial.png';
                    break;
                case 'Talento Innovador':
                    $imagen_correcta = 'TalentoInnovador.png';
                    break;
                case 'Talento Cientifico':
                    $imagen_correcta = 'TalentoCientifico.png';
                    break;
                case 'Formación y Actualización':
                    $imagen_correcta = 'FormacionyActualizacion.png';
                    break;
                case 'Embajador del Arte':
                    $imagen_correcta = 'EmbajadordelArte.png';
                    break;
                default:
                    $imagen_correcta = 'ResponsabilidadSocial.png'; // Por defecto
            }
            
            echo "<p><strong>Imagen correcta:</strong> $imagen_correcta</p>";
            
            if ($archivo_actual !== $imagen_correcta) {
                // Actualizar la imagen en tipo_insignia
                $sql_actualizar = "UPDATE tipo_insignia ti 
                                   JOIN insignias i ON ti.id = i.Tipo_Insignia 
                                   JOIN insigniasotorgadas io ON i.id = io.insignia_id 
                                   SET ti.Arch_ima = ? 
                                   WHERE io.clave_insignia = ?";
                
                $stmt2 = $conexion->prepare($sql_actualizar);
                if ($stmt2) {
                    $stmt2->bind_param("ss", $imagen_correcta, $codigo_actualizar);
                    
                    if ($stmt2->execute()) {
                        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                        echo "<strong>✅ ¡Imagen actualizada exitosamente!</strong><br>";
                        echo "La insignia ahora usará: $imagen_correcta";
                        echo "</div>";
                        
                        echo "<p><a href='ver_insignia_completa.php?codigo=$codigo_actualizar' target='_blank'>🔗 Ver insignia actualizada</a></p>";
                        
                    } else {
                        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                        echo "<strong>❌ Error al actualizar:</strong> " . $stmt2->error;
                        echo "</div>";
                    }
                    $stmt2->close();
                } else {
                    echo "<p>❌ Error al preparar consulta de actualización</p>";
                }
            } else {
                echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<strong>ℹ️ La insignia ya tiene la imagen correcta.</strong>";
                echo "</div>";
            }
        } else {
            echo "<p>❌ No se encontró la insignia</p>";
        }
        $stmt->close();
    } else {
        echo "<p>❌ Error al preparar consulta</p>";
    }
}

echo "<br><h3>3. Opciones</h3>";
echo "<ul>";
echo "<li><strong>Actualizar insignias existentes:</strong> Haz clic en '🔄 Actualizar' para cada insignia</li>";
echo "<li><strong>Crear nueva insignia:</strong> Ve al <a href='metadatos_formulario.php'>formulario de metadatos</a> para crear una nueva</li>";
echo "</ul>";

echo "<p><a href='metadatos_formulario.php'>← Crear nueva insignia</a></p>";
echo "<p><a href='modulo_de_administracion.php'>← Ir al módulo de administración</a></p>";
?>
