<?php
/**
 * Script simple para agregar campos faltantes a la tabla destinatario
 */

require_once 'conexion.php';

echo "<h2>🔧 Agregando campos a la tabla destinatario</h2>";

// Verificar conexión
if (!$conexion) {
    die("❌ Error de conexión: " . mysqli_connect_error());
}

echo "<p>✅ Conexión exitosa</p>";

// Agregar los campos faltantes
$campos_agregar = [
    "ALTER TABLE destinatario ADD COLUMN Curp VARCHAR(20) AFTER Nombre_Completo",
    "ALTER TABLE destinatario ADD COLUMN Matricula VARCHAR(100) AFTER Curp", 
    "ALTER TABLE destinatario ADD COLUMN Correo VARCHAR(255) AFTER Matricula"
];

foreach ($campos_agregar as $sql) {
    echo "<p>Ejecutando: " . htmlspecialchars($sql) . "</p>";
    
    if ($conexion->query($sql)) {
        echo "<p>✅ Campo agregado exitosamente</p>";
    } else {
        echo "<p>⚠️ Error: " . $conexion->error . "</p>";
        // Si el campo ya existe, continuar
        if (strpos($conexion->error, 'Duplicate column name') !== false) {
            echo "<p>ℹ️ El campo ya existe, continuando...</p>";
        }
    }
}

// Verificar la nueva estructura
echo "<h3>📊 Nueva estructura de la tabla destinatario:</h3>";
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

echo "<h3>✅ Proceso completado</h3>";
echo "<p>Ahora la tabla destinatario tiene los campos: Curp, Matricula y Correo</p>";
echo "<p>El formulario de metadatos funcionará correctamente.</p>";

$conexion->close();
?>
