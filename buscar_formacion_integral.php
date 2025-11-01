<?php
require_once 'conexion.php';

echo "<h2>Buscando registros de 'Formación Integral':</h2>";

$stmt = $conexion->prepare("
    SELECT 
        io.clave_insignia,
        ti.Nombre_ins,
        ti.Arch_ima,
        d.Nombre_Completo as destinatario
    FROM insigniasotorgadas io 
    LEFT JOIN insignias i ON io.insignia_id = i.id 
    LEFT JOIN tipo_insignia ti ON i.Tipo_Insignia = ti.id
    LEFT JOIN destinatario d ON io.destinatario_id = d.id
    WHERE ti.Nombre_ins = 'Formación Integral'
    ORDER BY io.clave_insignia
");

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Código</th><th>Nombre Insignia</th><th>Archivo Imagen</th><th>Destinatario</th><th>Enlace Prueba</th></tr>";
    
    $encontrados = 0;
    while($row = $result->fetch_assoc()) {
        $encontrados++;
        $codigo = $row['clave_insignia'];
        $nombre = $row['Nombre_ins'];
        $archivo = $row['Arch_ima'];
        $destinatario = $row['destinatario'];
        $enlace = "imagen_clickeable.php?codigo=" . urlencode($codigo);
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($codigo) . "</td>";
        echo "<td>" . htmlspecialchars($nombre) . "</td>";
        echo "<td>" . htmlspecialchars($archivo) . "</td>";
        echo "<td>" . htmlspecialchars($destinatario) . "</td>";
        echo "<td><a href='" . $enlace . "' target='_blank'>Probar</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($encontrados == 0) {
        echo "<p style='color: red;'>❌ No se encontraron registros para 'Formación Integral'</p>";
        echo "<p>Necesitas crear un registro que esté asociado con la insignia 'Formación Integral' (id: 6) en la tabla tipo_insignia.</p>";
    } else {
        echo "<p>✅ Se encontraron $encontrados registros para 'Formación Integral'</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Error en la consulta</p>";
}
?>
