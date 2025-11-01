<?php
require_once 'conexion.php';

$codigo_insignia = $_GET['insignia'] ?? '';

if (empty($codigo_insignia)) {
    echo "<h2>❌ No se proporcionó código de insignia</h2>";
    exit();
}

echo "<h2>🔍 Depuración para código: " . htmlspecialchars($codigo_insignia) . "</h2>";

// Verificar si existe en la tabla insigniasotorgadas
$stmt_check = $conexion->prepare("SELECT * FROM insigniasotorgadas WHERE clave_insignia = ?");
$stmt_check->bind_param("s", $codigo_insignia);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($row_check = $result_check->fetch_assoc()) {
    echo "<h3>✅ Encontrado en insigniasotorgadas:</h3>";
    echo "<pre>" . print_r($row_check, true) . "</pre>";
    
    // Verificar datos relacionados
    $destinatario_id = $row_check['destinatario_id'];
    $insignia_id = $row_check['insignia_id'];
    $responsable_id = $row_check['responsable_id'];
    
    // Verificar destinatario
    $stmt_d = $conexion->prepare("SELECT * FROM destinatario WHERE id = ?");
    $stmt_d->bind_param("i", $destinatario_id);
    $stmt_d->execute();
    $result_d = $stmt_d->get_result();
    if ($row_d = $result_d->fetch_assoc()) {
        echo "<h3>👤 Datos del destinatario:</h3>";
        echo "<pre>" . print_r($row_d, true) . "</pre>";
    } else {
        echo "<h3>❌ Destinatario no encontrado</h3>";
    }
    
    // Verificar insignia
    $stmt_i = $conexion->prepare("SELECT * FROM insignias WHERE id = ?");
    $stmt_i->bind_param("i", $insignia_id);
    $stmt_i->execute();
    $result_i = $stmt_i->get_result();
    if ($row_i = $result_i->fetch_assoc()) {
        echo "<h3>🏆 Datos de la insignia:</h3>";
        echo "<pre>" . print_r($row_i, true) . "</pre>";
        
        // Verificar tipo_insignia
        $tipo_insignia_id = $row_i['Tipo_Insignia'];
        $stmt_ti = $conexion->prepare("SELECT * FROM tipo_insignia WHERE id = ?");
        $stmt_ti->bind_param("i", $tipo_insignia_id);
        $stmt_ti->execute();
        $result_ti = $stmt_ti->get_result();
        if ($row_ti = $result_ti->fetch_assoc()) {
            echo "<h3>📋 Tipo de insignia:</h3>";
            echo "<pre>" . print_r($row_ti, true) . "</pre>";
        }
    } else {
        echo "<h3>❌ Insignia no encontrada</h3>";
    }
    
    // Verificar responsable
    $stmt_re = $conexion->prepare("SELECT * FROM responsable_emision WHERE id = ?");
    $stmt_re->bind_param("i", $responsable_id);
    $stmt_re->execute();
    $result_re = $stmt_re->get_result();
    if ($row_re = $result_re->fetch_assoc()) {
        echo "<h3>👨‍💼 Datos del responsable:</h3>";
        echo "<pre>" . print_r($row_re, true) . "</pre>";
    } else {
        echo "<h3>❌ Responsable no encontrado</h3>";
    }
    
} else {
    echo "<h3>❌ Código de insignia no encontrado en la base de datos</h3>";
    
    // Mostrar todos los códigos disponibles
    echo "<h3>📋 Códigos disponibles:</h3>";
    $stmt_all = $conexion->query("SELECT clave_insignia FROM insigniasotorgadas ORDER BY fecha_otorgamiento DESC");
    if ($stmt_all && $stmt_all->num_rows > 0) {
        echo "<ul>";
        while ($row_all = $stmt_all->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row_all['clave_insignia']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No hay códigos en la base de datos.</p>";
    }
}

$conexion->close();
?>
