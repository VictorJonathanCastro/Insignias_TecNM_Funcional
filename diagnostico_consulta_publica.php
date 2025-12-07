<?php
require_once 'conexion.php';

echo "<h1>Diagnóstico: Consulta Pública vs Carga Masiva</h1>";

// Verificar estructura de tablas
echo "<h2>1. Estructura de Tablas</h2>";

// Verificar tabla insigniasotorgadas
$result = $conexion->query("SHOW COLUMNS FROM insigniasotorgadas");
echo "<h3>Tabla insigniasotorgadas:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table>";

// Verificar tabla destinatario
$result = $conexion->query("SHOW COLUMNS FROM destinatario");
echo "<h3>Tabla destinatario:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table>";

// Detectar campo ID de destinatario
$check_destinatario_id = $conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
$tiene_id_destinatario = ($check_destinatario_id && $check_destinatario_id->num_rows > 0);
$campo_id_destinatario = $tiene_id_destinatario ? 'id' : 'ID_destinatario';
echo "<p><strong>Campo ID destinatario detectado:</strong> $campo_id_destinatario</p>";

// Verificar últimas insignias insertadas
echo "<h2>2. Últimas 10 Insignias Insertadas</h2>";
$sql = "SELECT io.*, d.Nombre_Completo, d.Curp, d.Matricula 
        FROM insigniasotorgadas io 
        LEFT JOIN destinatario d ON io.Destinatario = d.$campo_id_destinatario 
        ORDER BY io.ID_otorgada DESC 
        LIMIT 10";
$result = $conexion->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID_otorgada</th><th>Codigo_Insignia</th><th>Destinatario (ID)</th><th>Nombre Completo</th><th>CURP</th><th>Matrícula</th><th>Fecha_Emision</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $nombre = $row['Nombre_Completo'] ?? 'NULL';
        $curp = $row['Curp'] ?? 'NULL';
        $matricula = $row['Matricula'] ?? 'NULL';
        echo "<tr>";
        echo "<td>{$row['ID_otorgada']}</td>";
        echo "<td>{$row['Codigo_Insignia']}</td>";
        echo "<td>{$row['Destinatario']}</td>";
        echo "<td>$nombre</td>";
        echo "<td>$curp</td>";
        echo "<td>$matricula</td>";
        echo "<td>{$row['Fecha_Emision']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>No se encontraron insignias en la tabla insigniasotorgadas</p>";
}

// Verificar si hay destinatarios sin JOIN
echo "<h2>3. Verificar Destinatarios sin JOIN</h2>";
$sql = "SELECT io.ID_otorgada, io.Codigo_Insignia, io.Destinatario as Destinatario_ID,
        (SELECT COUNT(*) FROM destinatario d WHERE d.$campo_id_destinatario = io.Destinatario) as existe_destinatario
        FROM insigniasotorgadas io 
        ORDER BY io.ID_otorgada DESC 
        LIMIT 10";
$result = $conexion->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID_otorgada</th><th>Codigo_Insignia</th><th>Destinatario_ID</th><th>¿Existe Destinatario?</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $existe = $row['existe_destinatario'] > 0 ? 'SÍ' : 'NO';
        $color = $row['existe_destinatario'] > 0 ? 'green' : 'red';
        echo "<tr>";
        echo "<td>{$row['ID_otorgada']}</td>";
        echo "<td>{$row['Codigo_Insignia']}</td>";
        echo "<td>{$row['Destinatario_ID']}</td>";
        echo "<td style='color:$color; font-weight:bold;'>$existe</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Probar la consulta exacta que usa consulta_publica.php
echo "<h2>4. Probar Consulta de consulta_publica.php</h2>";
$busqueda_test = ''; // Cambiar aquí para probar con un valor específico

if (!empty($busqueda_test)) {
    $busqueda_like = '%' . $busqueda_test . '%';
    $sql = "
        SELECT 
            io.ID_otorgada as id,
            io.Codigo_Insignia as clave_insignia,
            io.Fecha_Emision as fecha_otorgamiento,
            COALESCE(d.Nombre_Completo, 'Destinatario no especificado') as destinatario,
            COALESCE(d.Matricula, 'No especificada') as Matricula,
            COALESCE(d.Curp, '') as curp
        FROM insigniasotorgadas io
        LEFT JOIN destinatario d ON io.Destinatario = d.$campo_id_destinatario
        WHERE (
            UPPER(COALESCE(d.Nombre_Completo, '')) LIKE UPPER(?) 
            OR UPPER(COALESCE(d.Curp, '')) LIKE UPPER(?) 
            OR UPPER(COALESCE(d.Matricula, '')) LIKE UPPER(?)
            OR UPPER(io.Codigo_Insignia) LIKE UPPER(?)
        )
        ORDER BY io.Fecha_Emision DESC
        LIMIT 10
    ";
    
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", $busqueda_like, $busqueda_like, $busqueda_like, $busqueda_like);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "<p><strong>Búsqueda:</strong> '$busqueda_test'</p>";
        if ($result->num_rows > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Código</th><th>Destinatario</th><th>Matrícula</th><th>CURP</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['clave_insignia']}</td>";
                echo "<td>{$row['destinatario']}</td>";
                echo "<td>{$row['Matricula']}</td>";
                echo "<td>{$row['curp']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:orange;'>No se encontraron resultados con la búsqueda '$busqueda_test'</p>";
        }
        $stmt->close();
    }
} else {
    echo "<p>Para probar la búsqueda, edita este archivo y cambia la variable \$busqueda_test</p>";
}

// Verificar todos los destinatarios
echo "<h2>5. Últimos 10 Destinatarios Creados</h2>";
$sql = "SELECT * FROM destinatario ORDER BY $campo_id_destinatario DESC LIMIT 10";
$result = $conexion->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>";
    while ($row = $result->fetch_assoc()) {
        foreach ($row as $key => $value) {
            echo "<th>$key</th>";
        }
        break;
    }
    echo "</tr>";
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>No se encontraron destinatarios</p>";
}

// Contar registros
echo "<h2>6. Estadísticas</h2>";
$count_insignias = $conexion->query("SELECT COUNT(*) as total FROM insigniasotorgadas")->fetch_assoc()['total'];
$count_destinatarios = $conexion->query("SELECT COUNT(*) as total FROM destinatario")->fetch_assoc()['total'];
$count_con_join = $conexion->query("SELECT COUNT(*) as total FROM insigniasotorgadas io LEFT JOIN destinatario d ON io.Destinatario = d.$campo_id_destinatario WHERE d.$campo_id_destinatario IS NOT NULL")->fetch_assoc()['total'];
$count_sin_join = $count_insignias - $count_con_join;

echo "<ul>";
echo "<li><strong>Total insignias:</strong> $count_insignias</li>";
echo "<li><strong>Total destinatarios:</strong> $count_destinatarios</li>";
echo "<li><strong>Insignias con destinatario válido:</strong> $count_con_join</li>";
echo "<li><strong>Insignias sin destinatario válido:</strong> $count_sin_join</li>";
echo "</ul>";

if ($count_sin_join > 0) {
    echo "<p style='color:red; font-weight:bold;'>⚠️ PROBLEMA DETECTADO: Hay $count_sin_join insignias sin destinatario válido. Estas no aparecerán en la consulta pública.</p>";
    
    // Mostrar las insignias problemáticas
    echo "<h3>Insignias sin destinatario válido:</h3>";
    $sql = "SELECT io.* FROM insigniasotorgadas io 
            LEFT JOIN destinatario d ON io.Destinatario = d.$campo_id_destinatario 
            WHERE d.$campo_id_destinatario IS NULL 
            ORDER BY io.ID_otorgada DESC 
            LIMIT 20";
    $result = $conexion->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID_otorgada</th><th>Codigo_Insignia</th><th>Destinatario (ID)</th><th>Fecha_Emision</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['ID_otorgada']}</td>";
            echo "<td>{$row['Codigo_Insignia']}</td>";
            echo "<td style='color:red;'>{$row['Destinatario']}</td>";
            echo "<td>{$row['Fecha_Emision']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

?>

