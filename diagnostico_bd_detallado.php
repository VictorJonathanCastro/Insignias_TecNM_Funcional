<?php
require_once 'conexion.php';

echo "<h2>🔍 Diagnóstico de Base de Datos</h2>";

try {
    // Verificar conexión
    if ($conexion->connect_error) {
        echo "<p style='color: red;'>❌ Error de conexión: " . $conexion->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>";
        
        // Verificar estructura de la tabla insigniasotorgadas
        echo "<h3>📋 Estructura de la tabla 'insigniasotorgadas':</h3>";
        $query = "DESCRIBE insigniasotorgadas";
        $result = $conexion->query($query);
        
        if ($result) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . $row['Default'] . "</td>";
                echo "<td>" . $row['Extra'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Verificar estructura de la tabla destinatario
        echo "<h3>📋 Estructura de la tabla 'destinatario':</h3>";
        $query = "DESCRIBE destinatario";
        $result = $conexion->query($query);
        
        if ($result) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . $row['Default'] . "</td>";
                echo "<td>" . $row['Extra'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Mostrar datos de ejemplo de insigniasotorgadas
        echo "<h3>📊 Datos de ejemplo de 'insigniasotorgadas':</h3>";
        $query = "SELECT * FROM insigniasotorgadas LIMIT 3";
        $result = $conexion->query($query);
        
        if ($result && $result->num_rows > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            $first = true;
            while ($row = $result->fetch_assoc()) {
                if ($first) {
                    echo "<tr>";
                    foreach (array_keys($row) as $key) {
                        echo "<th>" . $key . "</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No hay datos en la tabla insigniasotorgadas</p>";
        }
        
        // Mostrar datos de ejemplo de destinatario
        echo "<h3>👥 Datos de ejemplo de 'destinatario':</h3>";
        $query = "SELECT * FROM destinatario LIMIT 3";
        $result = $conexion->query($query);
        
        if ($result && $result->num_rows > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            $first = true;
            while ($row = $result->fetch_assoc()) {
                if ($first) {
                    echo "<tr>";
                    foreach (array_keys($row) as $key) {
                        echo "<th>" . $key . "</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No hay datos en la tabla destinatario</p>";
        }
        
        // Probar la consulta específica
        echo "<h3>🔍 Prueba de consulta específica:</h3>";
        $busqueda = "MAVR030711HDFSRM01";
        $query = "
            SELECT 
                io.ID_otorgada as id,
                io.Codigo_Insignia as clave_insignia,
                io.Fecha_Emision as fecha_otorgamiento,
                d.Nombre_Completo as destinatario
            FROM insigniasotorgadas io
            LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario
            WHERE d.Nombre_Completo LIKE ? OR io.Codigo_Insignia LIKE ?
        ";
        
        $stmt = $conexion->prepare($query);
        if ($stmt) {
            $searchTerm = "%$busqueda%";
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            echo "<p>Búsqueda por: <strong>" . htmlspecialchars($busqueda) . "</strong></p>";
            
            if ($result->num_rows > 0) {
                echo "<p style='color: green;'>✅ Se encontraron " . $result->num_rows . " resultados:</p>";
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr><th>ID</th><th>Código</th><th>Fecha</th><th>Destinatario</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['clave_insignia']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['fecha_otorgamiento']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['destinatario']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: red;'>❌ No se encontraron resultados</p>";
            }
            $stmt->close();
        } else {
            echo "<p style='color: red;'>❌ Error al preparar la consulta: " . $conexion->error . "</p>";
        }
        
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Excepción: " . $e->getMessage() . "</p>";
}

$conexion->close();
?>
