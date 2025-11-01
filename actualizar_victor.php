<?php
require_once 'conexion.php';

echo "<h2>🔄 Actualizando datos para código TecNM-ITSM-20251-115</h2>";

$codigo_insignia = "TecNM-ITSM-20251-115";

// Datos nuevos para Victor Jonathan Castro Secundino
$nuevo_destinatario = "Victor Jonathan Castro Secundino";
$nueva_descripcion = "Por su destacada participación en el Programa Nacional 'AlfabetizaTEC' desarrollando competencias de Formación Integral, mediante el compromiso y dedicación en procesos de formación integral, contribuyendo de manera significativa al desarrollo de competencias profesionales y valores institucionales.";
$nueva_evidencia = "Participación activa en programa de alfabetización";
$nuevo_responsable = "Coordinador General TecNM Morelia";

try {
    // Buscar el registro existente
    $stmt_find = $conexion->prepare("SELECT * FROM insigniasotorgadas WHERE clave_insignia = ?");
    $stmt_find->bind_param("s", $codigo_insignia);
    $stmt_find->execute();
    $result_find = $stmt_find->get_result();
    
    if ($row_find = $result_find->fetch_assoc()) {
        echo "<p>✅ Encontrado registro con ID: " . $row_find['id'] . "</p>";
        
        $destinatario_id = $row_find['destinatario_id'];
        $insignia_id = $row_find['insignia_id'];
        $responsable_id = $row_find['responsable_id'];
        
        // Actualizar destinatario
        $stmt_update_dest = $conexion->prepare("UPDATE destinatario SET Nombre_Completo = ? WHERE id = ?");
        $stmt_update_dest->bind_param("si", $nuevo_destinatario, $destinatario_id);
        if ($stmt_update_dest->execute()) {
            echo "<p>✅ Destinatario actualizado a: " . htmlspecialchars($nuevo_destinatario) . "</p>";
        } else {
            echo "<p>❌ Error actualizando destinatario: " . $stmt_update_dest->error . "</p>";
        }
        
        // Actualizar insignia (descripción)
        $stmt_update_ins = $conexion->prepare("UPDATE insignias SET Descripcion = ? WHERE id = ?");
        $stmt_update_ins->bind_param("si", $nueva_descripcion, $insignia_id);
        if ($stmt_update_ins->execute()) {
            echo "<p>✅ Descripción de insignia actualizada</p>";
        } else {
            echo "<p>❌ Error actualizando insignia: " . $stmt_update_ins->error . "</p>";
        }
        
        // Actualizar responsable
        $stmt_update_resp = $conexion->prepare("UPDATE responsable_emision SET Nombre_Completo = ? WHERE id = ?");
        $stmt_update_resp->bind_param("si", $nuevo_responsable, $responsable_id);
        if ($stmt_update_resp->execute()) {
            echo "<p>✅ Responsable actualizado a: " . htmlspecialchars($nuevo_responsable) . "</p>";
        } else {
            echo "<p>❌ Error actualizando responsable: " . $stmt_update_resp->error . "</p>";
        }
        
        // Actualizar evidencia en insigniasotorgadas
        $stmt_update_evid = $conexion->prepare("UPDATE insigniasotorgadas SET evidencia = ? WHERE clave_insignia = ?");
        $stmt_update_evid->bind_param("ss", $nueva_evidencia, $codigo_insignia);
        if ($stmt_update_evid->execute()) {
            echo "<p>✅ Evidencia actualizada</p>";
        } else {
            echo "<p>❌ Error actualizando evidencia: " . $stmt_update_evid->error . "</p>";
        }
        
        echo "<h3>🎯 Verificar resultado:</h3>";
        echo "<p><a href='validacion.php?insignia=" . urlencode($codigo_insignia) . "' target='_blank'>Ver certificado actualizado</a></p>";
        
    } else {
        echo "<p>❌ No se encontró el código de insignia: " . htmlspecialchars($codigo_insignia) . "</p>";
        
        // Mostrar códigos disponibles
        echo "<h3>📋 Códigos disponibles:</h3>";
        $stmt_all = $conexion->query("SELECT clave_insignia FROM insigniasotorgadas ORDER BY fecha_otorgamiento DESC LIMIT 10");
        if ($stmt_all && $stmt_all->num_rows > 0) {
            echo "<ul>";
            while ($row_all = $stmt_all->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row_all['clave_insignia']) . "</li>";
            }
            echo "</ul>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

$conexion->close();
?>
