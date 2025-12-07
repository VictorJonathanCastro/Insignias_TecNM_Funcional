<?php
/**
 * Script para corregir el problema de consulta pública
 * Verifica y corrige las relaciones entre insigniasotorgadas y destinatario
 */

require_once 'conexion.php';

echo "<h1>Corrección de Consulta Pública</h1>";

// Detectar campo ID de destinatario
$check_destinatario_id = $conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
$tiene_id_destinatario = ($check_destinatario_id && $check_destinatario_id->num_rows > 0);
$campo_id_destinatario = $tiene_id_destinatario ? 'id' : 'ID_destinatario';

echo "<p><strong>Campo ID destinatario detectado:</strong> $campo_id_destinatario</p>";

// 1. Verificar insignias sin destinatario válido
echo "<h2>1. Verificando insignias sin destinatario válido</h2>";
$sql = "SELECT io.ID_otorgada, io.Codigo_Insignia, io.Destinatario as Destinatario_ID,
        (SELECT COUNT(*) FROM destinatario d WHERE d.$campo_id_destinatario = io.Destinatario) as existe_destinatario
        FROM insigniasotorgadas io 
        ORDER BY io.ID_otorgada DESC";
$result = $conexion->query($sql);

$insignias_problematicas = [];
$total_insignias = 0;
$insignias_con_destinatario = 0;
$insignias_sin_destinatario = 0;

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID_otorgada</th><th>Codigo_Insignia</th><th>Destinatario_ID</th><th>¿Existe Destinatario?</th><th>Acción</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $total_insignias++;
        $existe = $row['existe_destinatario'] > 0;
        
        if ($existe) {
            $insignias_con_destinatario++;
            $color = 'green';
            $texto = 'SÍ';
            $accion = 'OK';
        } else {
            $insignias_sin_destinatario++;
            $color = 'red';
            $texto = 'NO';
            $insignias_problematicas[] = $row;
            $accion = 'REQUIERE CORRECCIÓN';
        }
        
        echo "<tr>";
        echo "<td>{$row['ID_otorgada']}</td>";
        echo "<td>{$row['Codigo_Insignia']}</td>";
        echo "<td>{$row['Destinatario_ID']}</td>";
        echo "<td style='color:$color; font-weight:bold;'>$texto</td>";
        echo "<td>$accion</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>Resumen:</strong></p>";
    echo "<ul>";
    echo "<li>Total insignias: $total_insignias</li>";
    echo "<li>Con destinatario válido: $insignias_con_destinatario</li>";
    echo "<li>Sin destinatario válido: $insignias_sin_destinatario</li>";
    echo "</ul>";
}

// 2. Intentar corregir las insignias problemáticas
if (count($insignias_problematicas) > 0) {
    echo "<h2>2. Intentando corregir insignias problemáticas</h2>";
    
    $corregidas = 0;
    $no_corregidas = 0;
    
    foreach ($insignias_problematicas as $insignia) {
        $destinatario_id = $insignia['Destinatario_ID'];
        $codigo_insignia = $insignia['Codigo_Insignia'];
        
        echo "<p><strong>Procesando insignia ID {$insignia['ID_otorgada']}:</strong></p>";
        echo "<ul>";
        echo "<li>Código: $codigo_insignia</li>";
        echo "<li>Destinatario_ID actual: $destinatario_id</li>";
        
        // Intentar extraer información del código de insignia o buscar por otros medios
        // Por ahora, intentar buscar si hay algún destinatario con un ID similar o crear uno genérico
        
        // Opción 1: Verificar si el ID existe pero con otro tipo de dato
        $sql_check = "SELECT $campo_id_destinatario FROM destinatario WHERE $campo_id_destinatario = ? OR CAST($campo_id_destinatario AS CHAR) = ?";
        $stmt_check = $conexion->prepare($sql_check);
        if ($stmt_check) {
            $dest_id_str = (string)$destinatario_id;
            $stmt_check->bind_param("ss", $dest_id_str, $dest_id_str);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) {
                $row_check = $result_check->fetch_assoc();
                $nuevo_id = $row_check[$campo_id_destinatario];
                
                // Actualizar la insignia con el ID correcto
                $sql_update = "UPDATE insigniasotorgadas SET Destinatario = ? WHERE ID_otorgada = ?";
                $stmt_update = $conexion->prepare($sql_update);
                if ($stmt_update) {
                    $stmt_update->bind_param("ii", $nuevo_id, $insignia['ID_otorgada']);
                    if ($stmt_update->execute()) {
                        echo "<li style='color:green;'>✓ Corregida: Actualizado Destinatario de $destinatario_id a $nuevo_id</li>";
                        $corregidas++;
                    } else {
                        echo "<li style='color:red;'>✗ Error al actualizar: " . $stmt_update->error . "</li>";
                        $no_corregidas++;
                    }
                    $stmt_update->close();
                }
            } else {
                // No se encontró el destinatario, crear uno genérico
                echo "<li style='color:orange;'>⚠ Destinatario no encontrado. Creando destinatario genérico...</li>";
                
                // Obtener un ITCentro por defecto
                $itcentro_default = 1;
                $sql_itc = "SELECT id FROM it_centros ORDER BY id LIMIT 1";
                $result_itc = $conexion->query($sql_itc);
                if ($result_itc && $result_itc->num_rows > 0) {
                    $row_itc = $result_itc->fetch_assoc();
                    $itcentro_default = $row_itc['id'];
                }
                
                // Verificar si ITCentro existe en la tabla
                $check_itcentro = $conexion->query("SHOW COLUMNS FROM destinatario LIKE 'ITCentro'");
                $tiene_itcentro = ($check_itcentro && $check_itcentro->num_rows > 0);
                
                // Crear destinatario genérico
                $nombre_generico = "Destinatario de " . $codigo_insignia;
                if ($tiene_itcentro) {
                    $sql_insert = "INSERT INTO destinatario (Nombre_Completo, ITCentro) VALUES (?, ?)";
                    $stmt_insert = $conexion->prepare($sql_insert);
                    if ($stmt_insert) {
                        $stmt_insert->bind_param("si", $nombre_generico, $itcentro_default);
                        if ($stmt_insert->execute()) {
                            $nuevo_destinatario_id = $conexion->insert_id;
                            
                            // Actualizar la insignia
                            $sql_update = "UPDATE insigniasotorgadas SET Destinatario = ? WHERE ID_otorgada = ?";
                            $stmt_update = $conexion->prepare($sql_update);
                            if ($stmt_update) {
                                $stmt_update->bind_param("ii", $nuevo_destinatario_id, $insignia['ID_otorgada']);
                                if ($stmt_update->execute()) {
                                    echo "<li style='color:green;'>✓ Corregida: Creado destinatario genérico (ID: $nuevo_destinatario_id) y actualizada insignia</li>";
                                    $corregidas++;
                                } else {
                                    echo "<li style='color:red;'>✗ Error al actualizar insignia: " . $stmt_update->error . "</li>";
                                    $no_corregidas++;
                                }
                                $stmt_update->close();
                            }
                        } else {
                            echo "<li style='color:red;'>✗ Error al crear destinatario: " . $stmt_insert->error . "</li>";
                            $no_corregidas++;
                        }
                        $stmt_insert->close();
                    }
                } else {
                    $sql_insert = "INSERT INTO destinatario (Nombre_Completo) VALUES (?)";
                    $stmt_insert = $conexion->prepare($sql_insert);
                    if ($stmt_insert) {
                        $stmt_insert->bind_param("s", $nombre_generico);
                        if ($stmt_insert->execute()) {
                            $nuevo_destinatario_id = $conexion->insert_id;
                            
                            // Actualizar la insignia
                            $sql_update = "UPDATE insigniasotorgadas SET Destinatario = ? WHERE ID_otorgada = ?";
                            $stmt_update = $conexion->prepare($sql_update);
                            if ($stmt_update) {
                                $stmt_update->bind_param("ii", $nuevo_destinatario_id, $insignia['ID_otorgada']);
                                if ($stmt_update->execute()) {
                                    echo "<li style='color:green;'>✓ Corregida: Creado destinatario genérico (ID: $nuevo_destinatario_id) y actualizada insignia</li>";
                                    $corregidas++;
                                } else {
                                    echo "<li style='color:red;'>✗ Error al actualizar insignia: " . $stmt_update->error . "</li>";
                                    $no_corregidas++;
                                }
                                $stmt_update->close();
                            }
                        } else {
                            echo "<li style='color:red;'>✗ Error al crear destinatario: " . $stmt_insert->error . "</li>";
                            $no_corregidas++;
                        }
                        $stmt_insert->close();
                    }
                }
            }
            $stmt_check->close();
        }
        
        echo "</ul>";
    }
    
    echo "<h3>Resumen de correcciones:</h3>";
    echo "<ul>";
    echo "<li>Insignias corregidas: $corregidas</li>";
    echo "<li>Insignias no corregidas: $no_corregidas</li>";
    echo "</ul>";
}

// 3. Verificar que ahora todas las insignias tengan destinatario válido
echo "<h2>3. Verificación final</h2>";
$sql_final = "SELECT COUNT(*) as total FROM insigniasotorgadas io 
              LEFT JOIN destinatario d ON io.Destinatario = d.$campo_id_destinatario 
              WHERE d.$campo_id_destinatario IS NOT NULL";
$result_final = $conexion->query($sql_final);
if ($result_final) {
    $row_final = $result_final->fetch_assoc();
    $total_con_destinatario = $row_final['total'];
    $total_insignias_final = $conexion->query("SELECT COUNT(*) as total FROM insigniasotorgadas")->fetch_assoc()['total'];
    
    echo "<p><strong>Total insignias:</strong> $total_insignias_final</p>";
    echo "<p><strong>Insignias con destinatario válido:</strong> $total_con_destinatario</p>";
    
    if ($total_con_destinatario == $total_insignias_final) {
        echo "<p style='color:green; font-weight:bold;'>✓ ¡Perfecto! Todas las insignias tienen destinatario válido.</p>";
    } else {
        $faltantes = $total_insignias_final - $total_con_destinatario;
        echo "<p style='color:orange; font-weight:bold;'>⚠ Aún faltan $faltantes insignias sin destinatario válido.</p>";
    }
}

echo "<hr>";
echo "<p><a href='consulta_publica.php'>← Volver a Consulta Pública</a></p>";
echo "<p><a href='diagnostico_consulta_publica.php'>Ver Diagnóstico Detallado</a></p>";

?>

