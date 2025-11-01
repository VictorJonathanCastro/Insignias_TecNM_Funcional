<?php
/**
 * Diagnóstico de la estructura de la base de datos
 */

require_once 'conexion.php';

echo "<h2>🔍 Diagnóstico de Base de Datos</h2>";

// Verificar conexión
if (!$conexion) {
    die("❌ Error de conexión: " . mysqli_connect_error());
}

echo "<p>✅ Conexión exitosa</p>";

// Verificar si existe la base de datos
$result = $conexion->query("SELECT DATABASE() as db_name");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>📊 Base de datos actual: <strong>" . $row['db_name'] . "</strong></p>";
}

// Verificar si existe la tabla destinatario
echo "<h3>📋 Verificando tabla 'destinatario':</h3>";
$result = $conexion->query("SHOW TABLES LIKE 'destinatario'");
if ($result && $result->num_rows > 0) {
    echo "<p>✅ La tabla 'destinatario' existe</p>";
    
    // Mostrar estructura de la tabla
    echo "<h4>🏗️ Estructura de la tabla:</h4>";
    $result = $conexion->query("DESCRIBE destinatario");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Contar registros
    $result = $conexion->query("SELECT COUNT(*) as total FROM destinatario");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>📊 Total de registros: <strong>" . $row['total'] . "</strong></p>";
    }
    
} else {
    echo "<p>❌ La tabla 'destinatario' NO existe</p>";
}

// Verificar otras tablas importantes
$tablas_importantes = ['insigniasotorgadas', 'periodo_emision', 'responsable_emision', 'estatus'];
echo "<h3>📋 Verificando otras tablas importantes:</h3>";
foreach ($tablas_importantes as $tabla) {
    $result = $conexion->query("SHOW TABLES LIKE '$tabla'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ Tabla '$tabla' existe</p>";
    } else {
        echo "<p>❌ Tabla '$tabla' NO existe</p>";
    }
}

// Probar consulta específica que está fallando
echo "<h3>🧪 Probando consulta específica:</h3>";
$sql_test = "SELECT id FROM destinatario WHERE Curp = ? OR Nombre_Completo = ? LIMIT 1";
$stmt_test = $conexion->prepare($sql_test);

if (!$stmt_test) {
    echo "<p>❌ Error al preparar consulta: " . $conexion->error . "</p>";
    echo "<p>🔍 Consulta SQL: " . htmlspecialchars($sql_test) . "</p>";
} else {
    echo "<p>✅ Consulta se prepara correctamente</p>";
    
    // Probar con datos de ejemplo
    $curp_test = "PERJ800101HDFRGN01";
    $nombre_test = "Juan Pérez García";
    
    $stmt_test->bind_param("ss", $curp_test, $nombre_test);
    if ($stmt_test->execute()) {
        echo "<p>✅ Consulta se ejecuta correctamente</p>";
        $result_test = $stmt_test->get_result();
        echo "<p>📊 Resultados encontrados: " . $result_test->num_rows . "</p>";
    } else {
        echo "<p>❌ Error al ejecutar consulta: " . $stmt_test->error . "</p>";
    }
    $stmt_test->close();
}

// Mostrar todas las tablas disponibles
echo "<h3>📋 Todas las tablas disponibles:</h3>";
$result = $conexion->query("SHOW TABLES");
if ($result && $result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li><strong>" . htmlspecialchars($row[0]) . "</strong></li>";
    }
    echo "</ul>";
} else {
    echo "<p>No se encontraron tablas.</p>";
}

$conexion->close();
?>
