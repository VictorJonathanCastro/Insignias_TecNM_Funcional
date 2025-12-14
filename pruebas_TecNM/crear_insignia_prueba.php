<?php
require_once 'conexion.php';

echo "<h2>🔧 Crear insignia de prueba</h2>";

// Verificar que las tablas necesarias existen
echo "<h3>📋 Verificando tablas necesarias:</h3>";
$tablas_necesarias = ['insigniasotorgadas', 'destinatario', 'T_insignias', 'periodo_emision', 'estatus', 'responsable_emision'];

foreach ($tablas_necesarias as $tabla) {
    $result = $conexion->query("SHOW TABLES LIKE '$tabla'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ Tabla <strong>$tabla</strong> existe</p>";
    } else {
        echo "<p>❌ Tabla <strong>$tabla</strong> NO existe</p>";
    }
}

// Verificar si ya existe la insignia
echo "<h3>🔍 Verificando si ya existe la insignia:</h3>";
$codigo_buscar = 'TecNM-ITSM-20251-115';
$result = $conexion->query("SELECT * FROM insigniasotorgadas WHERE clave_insignia = '$codigo_buscar'");
if ($result && $result->num_rows > 0) {
    echo "<p>✅ La insignia <strong>$codigo_buscar</strong> ya existe</p>";
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
} else {
    echo "<p>❌ La insignia <strong>$codigo_buscar</strong> NO existe</p>";
    
    // Crear la insignia de prueba
    echo "<h3>🔧 Creando insignia de prueba:</h3>";
    
    try {
        // Verificar que existen los datos necesarios
        $destinatario_id = 1;
        $insignia_id = 1;
        $periodo_id = 1;
        $responsable_id = 1;
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
        
        // Verificar responsable
        $result = $conexion->query("SELECT id FROM responsable_emision WHERE id = $responsable_id");
        if (!$result || $result->num_rows == 0) {
            // Crear responsable
            $sql_resp = "INSERT INTO responsable_emision (Nombre_Completo, Adscripcion, Cargo, Codigo_Identificacion) VALUES ('Andrea Yadira Zárate Fuentes', 1, 'Directora', 'TecNM-ITSM-2025-Dir001')";
            if ($conexion->query($sql_resp)) {
                $responsable_id = $conexion->insert_id;
                echo "<p>✅ Responsable creado con ID: $responsable_id</p>";
            }
        }
        
        // Crear la insignia otorgada
        $sql_insignia = "INSERT INTO insigniasotorgadas (
            insignia_id, 
            destinatario_id, 
            periodo_id, 
            responsable_id,
            estatus_id, 
            clave_insignia,
            fecha_otorgamiento, 
            evidencia, 
            fecha_autorizacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql_insignia);
        if ($stmt) {
            $fecha_otorgamiento = '2025-01-15';
            $evidencia = '9 educandos con certificación por parte del INEA';
            $fecha_autorizacion = '2025-01-15';
            
            $stmt->bind_param("iiiisssss", 
                $insignia_id, 
                $destinatario_id, 
                $periodo_id, 
                $responsable_id,
                $estatus_id, 
                $codigo_buscar,
                $fecha_otorgamiento, 
                $evidencia, 
                $fecha_autorizacion
            );
            
            if ($stmt->execute()) {
                echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                echo "<h4>✅ Insignia creada exitosamente</h4>";
                echo "<p><strong>Código:</strong> $codigo_buscar</p>";
                echo "<p><strong>ID:</strong> " . $conexion->insert_id . "</p>";
                echo "</div>";
                
                echo "<h4>🔗 Enlaces de prueba:</h4>";
                echo "<ul>";
                echo "<li><a href='validacion.php?insignia=" . urlencode($codigo_buscar) . "' target='_blank'>Validar insignia</a></li>";
                echo "<li><a href='imagen_compartible.php?codigo=" . urlencode($codigo_buscar) . "' target='_blank'>Compartir insignia</a></li>";
                echo "<li><a href='test_validacion_corregida.php' target='_blank'>Probar validación</a></li>";
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
echo "<p>Si la insignia se crea correctamente, el sistema de validación debería funcionar.</p>";
?>