<?php
/**
 * Script para agregar campos adicionales a la tabla destinatario
 * o crear una tabla complementaria para datos adicionales
 */

require_once 'conexion.php';

echo "<h2>🔧 Actualizando Estructura de Base de Datos</h2>";

// Opción 1: Agregar campos a la tabla destinatario existente
echo "<h3>📋 Opción 1: Agregar campos a la tabla destinatario</h3>";

$campos_adicionales = [
    "ALTER TABLE destinatario ADD COLUMN Curp VARCHAR(20) AFTER Nombre_Completo",
    "ALTER TABLE destinatario ADD COLUMN Matricula VARCHAR(100) AFTER Curp", 
    "ALTER TABLE destinatario ADD COLUMN Correo VARCHAR(255) AFTER Matricula",
    "ALTER TABLE destinatario ADD COLUMN Telefono VARCHAR(20) AFTER Correo",
    "ALTER TABLE destinatario ADD COLUMN Genero VARCHAR(50) AFTER Telefono"
];

foreach ($campos_adicionales as $sql) {
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

// Actualizar algunos registros existentes con datos de ejemplo
echo "<h3>🔄 Actualizando registros existentes con datos de ejemplo:</h3>";

$datos_ejemplo = [
    ['ID_destinatario' => 1, 'Curp' => 'MAVR800101HDFRGN01', 'Matricula' => '2024001', 'Correo' => 'rigoberto.martinez@tecnm.mx'],
    ['ID_destinatario' => 2, 'Curp' => 'PERJ800101HDFRGN02', 'Matricula' => '2024002', 'Correo' => 'juan.perez@tecnm.mx'],
    ['ID_destinatario' => 3, 'Curp' => 'LOPM800101MDFRGN03', 'Matricula' => '2024003', 'Correo' => 'maria.lopez@tecnm.mx'],
    ['ID_destinatario' => 4, 'Curp' => 'PERJ800101HDFRGN04', 'Matricula' => '2024004', 'Correo' => 'juan.perez.molina@tecnm.mx'],
    ['ID_destinatario' => 5, 'Curp' => 'BENG800101MDFRGN05', 'Matricula' => '2024005', 'Correo' => 'alma.benitez@tecnm.mx'],
    ['ID_destinatario' => 6, 'Curp' => 'CAGT800101HDFRGN06', 'Matricula' => '2024006', 'Correo' => 'tomas.castro@tecnm.mx']
];

foreach ($datos_ejemplo as $datos) {
    $sql_update = "UPDATE destinatario SET Curp = ?, Matricula = ?, Correo = ? WHERE ID_destinatario = ?";
    $stmt = $conexion->prepare($sql_update);
    
    if ($stmt) {
        $stmt->bind_param("sssi", $datos['Curp'], $datos['Matricula'], $datos['Correo'], $datos['ID_destinatario']);
        if ($stmt->execute()) {
            echo "<p>✅ Actualizado destinatario ID " . $datos['ID_destinatario'] . "</p>";
        } else {
            echo "<p>❌ Error actualizando destinatario ID " . $datos['ID_destinatario'] . ": " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

echo "<h3>✅ Proceso completado</h3>";
echo "<p>Ahora puedes usar el formulario de metadatos con los campos CURP, correo y matrícula.</p>";

$conexion->close();
?>
