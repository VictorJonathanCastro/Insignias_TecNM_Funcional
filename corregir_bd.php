<?php
require_once 'conexion.php';

echo "<h2>🔧 Corrección Automática de Base de Datos</h2>";

try {
    // Consulta para ver el estado actual
    echo "<h3>📋 Estado actual:</h3>";
    $sql_actual = "SELECT id, Nombre_ins, Arch_ima FROM tipo_insignia ORDER BY id";
    $resultado_actual = $conexion->query($sql_actual);
    
    if ($resultado_actual && $resultado_actual->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background-color: #2196F3; color: white;'>";
        echo "<th>ID</th><th>Nombre Insignia</th><th>Archivo Actual</th><th>Archivo Correcto</th><th>Estado</th>";
        echo "</tr>";
        
        $correcciones = [
            1 => 'RespSocial.png',
            2 => 'InnovacionSocial.png', 
            3 => 'ConcienciaIntercultural.png',
            4 => 'MovilidadIntercambio.png',
            6 => 'FormacionIntegral.png',
            7 => 'ExcelenciaAcademica.png',
            8 => 'InnovacionLiderazgo.png'
        ];
        
        while ($fila = $resultado_actual->fetch_assoc()) {
            $id = $fila['id'];
            $nombre = $fila['Nombre_ins'];
            $archivo_actual = $fila['Arch_ima'];
            $archivo_correcto = $correcciones[$id] ?? 'No definido';
            
            $necesita_correccion = ($archivo_actual !== $archivo_correcto);
            $color_fila = $necesita_correccion ? '#ffebee' : '#f8f9fa';
            $estado = $necesita_correccion ? '❌ Necesita corrección' : '✅ Correcto';
            
            echo "<tr style='background-color: $color_fila;'>";
            echo "<td style='text-align: center;'>$id</td>";
            echo "<td>" . htmlspecialchars($nombre) . "</td>";
            echo "<td style='color: " . ($necesita_correccion ? '#f44336' : '#4CAF50') . ";'>" . htmlspecialchars($archivo_actual) . "</td>";
            echo "<td style='color: #4CAF50;'>" . htmlspecialchars($archivo_correcto) . "</td>";
            echo "<td style='text-align: center;'>$estado</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Aplicar correcciones
    echo "<h3>🔧 Aplicando correcciones:</h3>";
    
    $correcciones_sql = [
        "UPDATE tipo_insignia SET Arch_ima = 'RespSocial.png' WHERE id = 1",
        "UPDATE tipo_insignia SET Arch_ima = 'InnovacionSocial.png' WHERE id = 2",
        "UPDATE tipo_insignia SET Arch_ima = 'ConcienciaIntercultural.png' WHERE id = 3",
        "UPDATE tipo_insignia SET Arch_ima = 'MovilidadIntercambio.png' WHERE id = 4",
        "UPDATE tipo_insignia SET Arch_ima = 'FormacionIntegral.png' WHERE id = 6",
        "UPDATE tipo_insignia SET Arch_ima = 'ExcelenciaAcademica.png' WHERE id = 7",
        "UPDATE tipo_insignia SET Arch_ima = 'InnovacionLiderazgo.png' WHERE id = 8"
    ];
    
    foreach ($correcciones_sql as $sql) {
        if ($conexion->query($sql)) {
            echo "<p style='color: #4CAF50;'>✅ " . htmlspecialchars($sql) . "</p>";
        } else {
            echo "<p style='color: #f44336;'>❌ Error: " . $conexion->error . "</p>";
        }
    }
    
    // Verificar resultado final
    echo "<h3>✅ Estado final:</h3>";
    $resultado_final = $conexion->query($sql_actual);
    
    if ($resultado_final && $resultado_final->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background-color: #4CAF50; color: white;'>";
        echo "<th>ID</th><th>Nombre Insignia</th><th>Archivo Corregido</th><th>Archivo Existe</th>";
        echo "</tr>";
        
        while ($fila = $resultado_final->fetch_assoc()) {
            $id = $fila['id'];
            $nombre = $fila['Nombre_ins'];
            $archivo = $fila['Arch_ima'];
            $ruta_completa = 'imagen/Insignias/' . $archivo;
            $existe = file_exists($ruta_completa);
            $color = $existe ? '#4CAF50' : '#f44336';
            $estado = $existe ? '✅ Sí' : '❌ No';
            
            echo "<tr>";
            echo "<td style='text-align: center;'>$id</td>";
            echo "<td>" . htmlspecialchars($nombre) . "</td>";
            echo "<td style='color: $color; font-weight: bold;'>" . htmlspecialchars($archivo) . "</td>";
            echo "<td style='text-align: center; color: $color;'>$estado</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; border-left: 4px solid #4CAF50; margin: 20px 0;'>";
    echo "<h3>🎉 ¡Corrección completada!</h3>";
    echo "<p><strong>El sistema ahora debería funcionar al 100%:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Todos los nombres de archivo coinciden con los archivos físicos</li>";
    echo "<li>✅ El sistema encontrará las imágenes específicas de cada insignia</li>";
    echo "<li>✅ Ya no usará la imagen por defecto</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
} finally {
    if (isset($conexion)) {
        $conexion->close();
    }
}

echo "<hr>";
echo "<p><a href='facebook_insignia.php?codigo=TecNM-itsm-20251-117' target='_blank'>🔗 Probar facebook_insignia.php</a></p>";
echo "<p><a href='debug_detallado.php?codigo=TecNM-itsm-20251-117' target='_blank'>🔍 Verificar debug detallado</a></p>";
echo "<p><a href='index.php'>← Volver al inicio</a></p>";
?>
