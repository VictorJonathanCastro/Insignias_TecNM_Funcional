<?php
/**
 * Script para actualizar el usuario admin@tecnm.mx 
 * para usar el correo real: sistema.insignias@smarcos.tecnm.mx
 * O crear un nuevo usuario si no existe
 */

require_once 'conexion.php';

// Seleccionar base de datos
$conexion->select_db("insignia");

// Datos del usuario
$correo_viejo = 'admin@tecnm.mx';
$correo_nuevo = 'sistema.insignias@smarcos.tecnm.mx';
$contrasena = 'Admin292'; // O la contraseña que quieras usar
$nombre = 'Victor Jonathan';
$apellido_paterno = 'Castro';
$apellido_materno = 'Secundino';
$rol = 'Admin';
$estado = 'Activo';

try {
    // Verificar si existe admin@tecnm.mx
    $stmt_check = $conexion->prepare("SELECT Id_Usuario, Correo, Nombre, Apellido_Paterno, Apellido_Materno FROM Usuario WHERE Correo = ?");
    $stmt_check->bind_param("s", $correo_viejo);
    $stmt_check->execute();
    $resultado_check = $stmt_check->get_result();
    
    if ($resultado_check->num_rows > 0) {
        // Usuario existe, actualizar correo
        $usuario_existente = $resultado_check->fetch_assoc();
        $id_usuario = $usuario_existente['Id_Usuario'];
        
        // Verificar si el correo nuevo ya existe
        $stmt_check_nuevo = $conexion->prepare("SELECT Id_Usuario FROM Usuario WHERE Correo = ?");
        $stmt_check_nuevo->bind_param("s", $correo_nuevo);
        $stmt_check_nuevo->execute();
        $resultado_check_nuevo = $stmt_check_nuevo->get_result();
        
        if ($resultado_check_nuevo->num_rows > 0) {
            // El correo nuevo ya existe, solo actualizar contraseña
            $usuario_nuevo = $resultado_check_nuevo->fetch_assoc();
            $stmt_update = $conexion->prepare("UPDATE Usuario SET Contrasena = ?, Nombre = ?, Apellido_Paterno = ?, Apellido_Materno = ? WHERE Correo = ?");
            $stmt_update->bind_param("sssss", $contrasena, $nombre, $apellido_paterno, $apellido_materno, $correo_nuevo);
            
            if ($stmt_update->execute()) {
                echo "<h2 style='color: #28a745;'>✅ Usuario actualizado exitosamente</h2>";
                echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
                echo "<h3>El correo <strong>$correo_nuevo</strong> ya existía. Se actualizaron los datos:</h3>";
                echo "<ul style='list-style: none; padding: 0;'>";
                echo "<li><strong>ID:</strong> {$usuario_nuevo['Id_Usuario']}</li>";
                echo "<li><strong>Nombre completo:</strong> $nombre $apellido_paterno $apellido_materno</li>";
                echo "<li><strong>Correo:</strong> $correo_nuevo</li>";
                echo "<li><strong>Contraseña:</strong> Actualizada</li>";
                echo "</ul>";
                echo "</div>";
            } else {
                throw new Exception("Error al actualizar: " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            // Actualizar el correo del usuario existente
            $stmt_update = $conexion->prepare("UPDATE Usuario SET Correo = ?, Contrasena = ?, Nombre = ?, Apellido_Paterno = ?, Apellido_Materno = ? WHERE Id_Usuario = ?");
            $stmt_update->bind_param("sssssi", $correo_nuevo, $contrasena, $nombre, $apellido_paterno, $apellido_materno, $id_usuario);
            
            if ($stmt_update->execute()) {
                echo "<h2 style='color: #28a745;'>✅ Usuario actualizado exitosamente</h2>";
                echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
                echo "<h3>Usuario actualizado:</h3>";
                echo "<ul style='list-style: none; padding: 0;'>";
                echo "<li><strong>ID:</strong> $id_usuario</li>";
                echo "<li><strong>Correo anterior:</strong> $correo_viejo</li>";
                echo "<li><strong>Correo nuevo:</strong> $correo_nuevo</li>";
                echo "<li><strong>Nombre completo:</strong> $nombre $apellido_paterno $apellido_materno</li>";
                echo "<li><strong>Contraseña:</strong> Actualizada</li>";
                echo "</ul>";
                echo "</div>";
            } else {
                throw new Exception("Error al actualizar: " . $stmt_update->error);
            }
            $stmt_update->close();
        }
        $stmt_check_nuevo->close();
    } else {
        // Usuario no existe, crear nuevo
        $stmt_insert = $conexion->prepare("INSERT INTO Usuario (Nombre, Apellido_Paterno, Apellido_Materno, Correo, Contrasena, Rol, Estado, It_Centro_Id) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt_insert->bind_param("sssssss", $nombre, $apellido_paterno, $apellido_materno, $correo_nuevo, $contrasena, $rol, $estado);
        
        if ($stmt_insert->execute()) {
            $id_usuario = $conexion->insert_id;
            echo "<h2 style='color: #28a745;'>✅ Usuario creado exitosamente</h2>";
            echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3>Nuevo usuario creado:</h3>";
            echo "<ul style='list-style: none; padding: 0;'>";
            echo "<li><strong>ID:</strong> $id_usuario</li>";
            echo "<li><strong>Nombre completo:</strong> $nombre $apellido_paterno $apellido_materno</li>";
            echo "<li><strong>Correo:</strong> $correo_nuevo</li>";
            echo "<li><strong>Rol:</strong> $rol</li>";
            echo "<li><strong>Estado:</strong> $estado</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            throw new Exception("Error al crear: " . $stmt_insert->error);
        }
        $stmt_insert->close();
    }
    
    $stmt_check->close();
    
    // Mostrar credenciales de acceso
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>📝 Credenciales de acceso:</h3>";
    echo "<p><strong>Correo:</strong> <code>$correo_nuevo</code></p>";
    echo "<p><strong>Contraseña:</strong> <code>$contrasena</code></p>";
    echo "<p><a href='login.php' style='display: inline-block; background: #0066CC; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Ir al Login</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2 style='color: #dc3545;'>❌ Error</h2>";
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

$conexion->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h2 {
        border-bottom: 3px solid #0066CC;
        padding-bottom: 10px;
    }
    code {
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
</style>

