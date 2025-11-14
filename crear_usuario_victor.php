<?php
/**
 * Script para crear usuario: Victor Jonathan Castro Secundino
 * Correo: sistema.insignias@smarcos.tecnm.mx
 * Contraseña: Admin292
 * Rol: Admin
 */

require_once 'conexion.php';

// Seleccionar base de datos
$conexion->select_db("insignia");

// Datos del nuevo usuario
$nombre = 'Victor Jonathan';
$apellido_paterno = 'Castro';
$apellido_materno = 'Secundino';
$correo = 'sistema.insignias@smarcos.tecnm.mx';
$contrasena = 'Admin292';
$rol = 'Admin';
$estado = 'Activo';
$it_centro_id = 1; // Puede ser NULL si no aplica

try {
    // Verificar si el usuario ya existe
    $stmt_check = $conexion->prepare("SELECT Id_Usuario, Correo FROM Usuario WHERE Correo = ?");
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    $resultado_check = $stmt_check->get_result();
    
    if ($resultado_check->num_rows > 0) {
        $usuario_existente = $resultado_check->fetch_assoc();
        echo "<h2>⚠️ Usuario ya existe</h2>";
        echo "<p>El correo <strong>$correo</strong> ya está registrado con ID: {$usuario_existente['Id_Usuario']}</p>";
        echo "<p>Si quieres actualizar la contraseña, usa este comando SQL:</p>";
        echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>";
        echo "UPDATE Usuario SET Contrasena = 'Admin292' WHERE Correo = 'sistema.insignias@smarcos.tecnm.mx';\n";
        echo "</pre>";
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();
    
    // Insertar nuevo usuario
    $stmt = $conexion->prepare("INSERT INTO Usuario (Nombre, Apellido_Paterno, Apellido_Materno, Correo, Contrasena, Rol, Estado, It_Centro_Id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Error al preparar consulta: " . $conexion->error);
    }
    
    $stmt->bind_param("sssssssi", $nombre, $apellido_paterno, $apellido_materno, $correo, $contrasena, $rol, $estado, $it_centro_id);
    
    if ($stmt->execute()) {
        $id_usuario = $conexion->insert_id;
        echo "<h2 style='color: #28a745;'>✅ Usuario creado exitosamente</h2>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>Datos del usuario:</h3>";
        echo "<ul style='list-style: none; padding: 0;'>";
        echo "<li><strong>ID:</strong> $id_usuario</li>";
        echo "<li><strong>Nombre completo:</strong> $nombre $apellido_paterno $apellido_materno</li>";
        echo "<li><strong>Correo:</strong> $correo</li>";
        echo "<li><strong>Rol:</strong> $rol</li>";
        echo "<li><strong>Estado:</strong> $estado</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>📝 Credenciales de acceso:</h3>";
        echo "<p><strong>Correo:</strong> <code>$correo</code></p>";
        echo "<p><strong>Contraseña:</strong> <code>$contrasena</code></p>";
        echo "<p><a href='login.php' style='display: inline-block; background: #0066CC; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Ir al Login</a></p>";
        echo "</div>";
        
    } else {
        throw new Exception("Error al ejecutar consulta: " . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo "<h2 style='color: #dc3545;'>❌ Error al crear usuario</h2>";
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

