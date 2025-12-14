<?php
require_once 'conexion.php';

echo "<h2>🔧 Crear insignia en T_insignias_otorgadas</h2>";

// Verificar si ya existe la insignia con ID 115
echo "<h3>🔍 Verificando si ya existe la insignia con ID 115:</h3>";
$result = $conexion->query("SELECT * FROM T_insignias_otorgadas WHERE id = 115");
if ($result && $result->num_rows > 0) {
    echo "<p>✅ La insignia con ID 115 ya existe</p>";
    $row = $result->fetch_assoc();
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    foreach ($row as $key => $value) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
        echo "<td>" . htmlspecialchars($value) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $codigo_generado = 'TECNM-ITSM-2025-' . str_pad(115, 3, '0', STR_PAD_LEFT);
    echo "<p><strong>Código generado:</strong> $codigo_generado</p>";
    
} else {
    echo "<p>❌ La insignia con ID 115 NO existe</p>";
    
    // Crear la insignia de prueba
    echo "<h3>🔧 Creando insignia de prueba:</h3>";
    
    try {
        // Verificar que existen los datos necesarios
        $destinatario_id = 1;
        $insignia_id = 1;
        $periodo_id = 1;
        $estatus_id = 1;
        
        // Verificar destinatario
        $result = $conexion->query("SELECT id FROM destinatario WHERE id = $destinatario_id");
        if (!$result || $result->num_rows == 0) {
            // Crear destinatario
            $sql_dest = "INSERT INTO destinatario (Id_Centro, Nombre_Completo, Nombre, Apellido_Paterno, Apellido_Materno, Correo, Rol) VALUES (1, 'Victor Jonathan Castro Secundino', 'Victor Jonathan', 'Castro', 'Secundino', 'victor.castro@tecnm.mx', 'Estudiante')";
            if ($conexion->query($sql_dest)) {
                $destinatario_id = $conexion->insert_id;
                echo "<p>✅ Destinatario creado con ID: $destinatario_id</p>";
            }
        }
        
        // Verificar insignia
        $result = $conexion->query("SELECT id FROM T_insignias WHERE id = $insignia_id");
        if (!$result || $result->num_rows == 0) {
            // Crear insignia
            $sql_ins = "INSERT INTO T_insignias (Tipo_Insignia, Propone_Insignia, Programa, Descripcion, Criterio, Fecha_Creacion, Fecha_Autorizacion, Nombre_gen_ins, Estatus, Archivo_Visual) VALUES (1, 1, 'Ingeniería en Sistemas', 'Insignia de Responsabilidad Social', 'Completar actividades de responsabilidad social', '2025-01-15', '2025-01-15', 'TecNM-ITSM', 1, 'insignia_Responsabilidad Social.png')";
            if ($conexion->query($sql_ins)) {
                $insignia_id = $conexion->insert_id;
                echo "<p>✅ Insignia creada con ID: $insignia_id</p>";
            }
        }
        
        // Verificar periodo
        $result = $conexion->query("SELECT id FROM periodo_emision WHERE id = $periodo_id");
        if (!$result || $result->num_rows == 0) {
            // Crear periodo
            $sql_per = "INSERT INTO periodo_emision (Nombre_Periodo, Fecha_Inicio, Fecha_Fin) VALUES ('Enero-Junio 2025', '2025-01-01', '2025-06-30')";
            if ($conexion->query($sql_per)) {
                $periodo_id = $conexion->insert_id;
                echo "<p>✅ Periodo creado con ID: $periodo_id</p>";
            }
        }
        
        // Verificar estatus
        $result = $conexion->query("SELECT id FROM estatus WHERE id = $estatus_id");
        if (!$result || $result->num_rows == 0) {
            // Crear estatus
            $sql_est = "INSERT INTO estatus (Nombre_Estatus, Acron_Estatus) VALUES ('Activo', 'ACT')";
            if ($conexion->query($sql_est)) {
                $estatus_id = $conexion->insert_id;
                echo "<p>✅ Estatus creado con ID: $estatus_id</p>";
            }
        }
        
        // Crear la insignia otorgada con ID específico 115
        $sql_insignia = "INSERT INTO T_insignias_otorgadas (
            id,
            Id_Destinatario, 
            Id_Insignia, 
            Id_Periodo_Emision,
            Id_Estatus, 
            Fecha_Emision
        ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql_insignia);
        if ($stmt) {
            $fecha_emision = '2025-01-15';
            
            $stmt->bind_param("iiiiss", 
                115, // ID específico
                $destinatario_id, 
                $insignia_id, 
                $periodo_id,
                $estatus_id, 
                $fecha_emision
            );
            
            if ($stmt->execute()) {
                $codigo_generado = 'TECNM-ITSM-2025-' . str_pad(115, 3, '0', STR_PAD_LEFT);
                echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                echo "<h4>✅ Insignia creada exitosamente</h4>";
                echo "<p><strong>ID:</strong> 115</p>";
                echo "<p><strong>Código generado:</strong> $codigo_generado</p>";
                echo "</div>";
                
                echo "<h4>🔗 Enlaces de prueba:</h4>";
                echo "<ul>";
                echo "<li><a href='validacion.php?insignia=" . urlencode($codigo_generado) . "' target='_blank'>Validar insignia</a></li>";
                echo "<li><a href='imagen_compartible.php?codigo=" . urlencode($codigo_generado) . "' target='_blank'>Compartir insignia</a></li>";
                echo "</ul>";
                
            } else {
                echo "<p>❌ Error al ejecutar consulta: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p>❌ Error al preparar consulta: " . $conexion->error . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>🎯 Resumen:</h3>";
echo "<p>Si la insignia se crea correctamente, el sistema de validación debería funcionar con el código <strong>TECNM-ITSM-2025-115</strong>.</p>";
?>
