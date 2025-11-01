<?php
session_start();

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla
ini_set('log_errors', 1);

// Incluir conexión a base de datos
require_once 'conexion.php';

// Verificar si la petición es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php?error=metodo_no_valido');
    exit();
}

// Obtener datos del formulario
$correo = trim($_POST['usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

// Validar que no estén vacíos
if (empty($correo) || empty($contrasena)) {
    header('Location: login.php?error=campos_vacios');
    exit();
}

// Validar formato de email
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    header('Location: login.php?error=email_invalido');
    exit();
}

try {
    // Verificar conexión
    if ($conexion->connect_errno) {
        throw new Exception("Error de conexión a la base de datos: " . $conexion->connect_error);
    }
    
    // Seleccionar base de datos
    if (!$conexion->select_db("insignia")) {
        throw new Exception("Error al seleccionar base de datos: " . $conexion->error);
    }
    
    // Verificar estructura de la tabla Usuario
    $resultado_tabla = $conexion->query("SHOW COLUMNS FROM Usuario");
    $campos_tabla = [];
    while ($row = $resultado_tabla->fetch_assoc()) {
        $campos_tabla[] = $row['Field'];
    }
    
    // Determinar qué campos usar según la estructura
    $tiene_nombre_completo = in_array('Nombre_Completo', $campos_tabla);
    $campo_id = in_array('ID_usuario', $campos_tabla) ? 'ID_usuario' : 'Id_Usuario';
    $campo_correo = in_array('Correo_Electronico', $campos_tabla) ? 'Correo_Electronico' : 'Correo';
    $campo_password = in_array('Contraseña', $campos_tabla) ? 'Contraseña' : 'Contrasena';
    $campo_rol = in_array('Tipo_Usuario', $campos_tabla) ? 'Tipo_Usuario' : 'Rol';
    
    // Construir consulta según estructura
    if ($tiene_nombre_completo) {
        $sql = "SELECT $campo_id as Id_Usuario, Nombre_Completo, $campo_correo as Correo, $campo_password as Contrasena, $campo_rol as Rol 
                FROM Usuario 
                WHERE $campo_correo = ?";
    } else {
        $sql = "SELECT Id_Usuario, Nombre, Apellido_Paterno, Apellido_Materno, 
                       Correo, Contrasena, Rol, Fecha_Creacion
                FROM Usuario 
                WHERE Correo = ?";
    }
    
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error al preparar consulta: " . $conexion->error);
    }
    
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        // Usuario no encontrado
        header('Location: login.php?error=credenciales_incorrectas');
        exit();
    }
    
    $usuario = $resultado->fetch_assoc();
    $stmt->close();
    
    // Verificar contraseña (comparación directa sin hash)
    if ($contrasena !== $usuario['Contrasena']) {
        // Contraseña incorrecta
        header('Location: login.php?error=credenciales_incorrectas');
        exit();
    }
    
    // Login exitoso - preparar datos de sesión según estructura
    if ($tiene_nombre_completo) {
        $_SESSION['usuario_id'] = $usuario['Id_Usuario'];
        $_SESSION['nombre'] = $usuario['Nombre_Completo'];
        $_SESSION['correo'] = $usuario['Correo'];
        $_SESSION['rol'] = $usuario['Rol'];
    } else {
        $_SESSION['usuario_id'] = $usuario['Id_Usuario'];
        $_SESSION['nombre'] = $usuario['Nombre'] . ' ' . $usuario['Apellido_Paterno'] . ' ' . $usuario['Apellido_Materno'];
        $_SESSION['correo'] = $usuario['Correo'];
        $_SESSION['rol'] = $usuario['Rol'];
    }
    $_SESSION['login_time'] = time();
    
    // Debug temporal - mostrar información del usuario
    if (isset($_GET['debug'])) {
        echo "<pre>";
        echo "Usuario encontrado:\n";
        print_r($usuario);
        echo "Rol: " . $usuario['Rol'] . "\n";
        echo "Redirigiendo a: modulo_de_administracion.php\n";
        echo "</pre>";
        exit();
    }
    
    // Redirigir según el rol
    switch ($usuario['Rol']) {
        case 'Administrador':
        case 'Admin':
            header('Location: modulo_de_administracion.php');
            break;
        case 'Estudiante':
            header('Location: estudiante_dashboard.php');
            break;
        default:
            header('Location: index.php');
            break;
    }
    exit();
    
} catch (Exception $e) {
    // Log del error detallado
    $error_message = "Error en procesar_login.php: " . $e->getMessage();
    $error_message .= " | Usuario: " . ($correo ?? 'N/A');
    $error_message .= " | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A');
    $error_message .= " | Timestamp: " . date('Y-m-d H:i:s');
    
    error_log($error_message);
    
    // Determinar el tipo de error específico
    $error_type = 'error_sistema';
    
    if (strpos($e->getMessage(), 'conexión') !== false) {
        $error_type = 'bd_no_configurada';
    } elseif (strpos($e->getMessage(), 'preparar consulta') !== false) {
        $error_type = 'bd_no_configurada';
    } elseif (strpos($e->getMessage(), 'seleccionar base de datos') !== false) {
        $error_type = 'bd_no_configurada';
    }
    
    // Redirigir con error específico
    header('Location: login.php?error=' . $error_type);
    exit();
}
?>
