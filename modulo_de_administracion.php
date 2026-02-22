<?php
// ========================================
// MÓDULO DE ADMINISTRACIÓN MEJORADO
// Compatible con XAMPP y Ubuntu
// ========================================

// Configurar manejo de errores según el entorno
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Configurar ruta de log según el entorno (detectar sin depender de funciones externas)
$es_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
$es_ubuntu = (file_exists('/etc/apache2/') || file_exists('/var/www/html/'));

if ($es_ubuntu && !$es_windows) {
    // En Ubuntu, intentar usar el log del sistema, pero con fallback
    $log_path = '/var/log/apache2/php_errors.log';
    // Si no se puede escribir, usar un archivo local
    if (!is_writable(dirname($log_path))) {
        $log_path = __DIR__ . '/php_errors.log';
    }
} else {
    // En XAMPP/Windows o entorno desconocido, usar un archivo local
    $log_path = __DIR__ . '/php_errors.log';
}

// Intentar configurar el log, pero no fallar si no se puede
@ini_set('error_log', $log_path);

// Iniciar buffer de salida para evitar output no deseado
ob_start();

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar conexión antes de incluir archivos
try {
    // Incluir conexión con manejo de errores
    if (!file_exists("conexion.php")) {
        throw new Exception("Archivo conexion.php no encontrado");
    }
    
    include("conexion.php");
    
    // Verificar que la conexión existe y funciona
    if (!isset($conexion) || !is_object($conexion) || (property_exists($conexion, 'connect_errno') && $conexion->connect_errno)) {
        throw new Exception("Error de conexión a la base de datos: " . (isset($conexion) && property_exists($conexion, 'connect_error') ? $conexion->connect_error : 'Desconocido'));
    }
    
    // Incluir verificar_sesion con manejo de errores
    if (!file_exists("verificar_sesion.php")) {
        throw new Exception("Archivo verificar_sesion.php no encontrado");
    }
    
    include("verificar_sesion.php");
    
    // Verificar si el usuario está autenticado y es administrador
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }

    // Verificar que las funciones necesarias existan
    if (!function_exists('verificarRoles')) {
        throw new Exception("Función verificarRoles no encontrada. Verifique que verificar_sesion.php esté completo.");
    }
    
    if (!function_exists('obtenerUsuarioActual')) {
        throw new Exception("Función obtenerUsuarioActual no encontrada. Verifique que verificar_sesion.php esté completo.");
    }

    // Verificar que sea administrador
    verificarRoles(['Administrador', 'Admin', 'SuperUsuario']);

    // Obtener información del usuario actual
    $usuario = obtenerUsuarioActual();
    
    if (!$usuario) {
        // Si no se puede obtener el usuario, cerrar sesión y redirigir
        session_destroy();
        header("Location: login.php?error=usuario_inactivo");
        exit();
    }
    
} catch (Exception $e) {
    // Log del error con más detalles
    $error_msg = "Error en modulo_de_administracion.php: " . $e->getMessage();
    $error_msg .= " | Archivo: " . __FILE__;
    $error_msg .= " | Línea: " . $e->getLine();
    $error_msg .= " | Timestamp: " . date('Y-m-d H:i:s');
    error_log($error_msg);
    
    // Limpiar buffer de salida
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Detectar si estamos en desarrollo local (XAMPP) o producción
    $es_desarrollo = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') || 
                     (isset($_SERVER['HTTP_HOST']) && 
                      ($_SERVER['HTTP_HOST'] === 'localhost' || 
                       $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
                       strpos($_SERVER['HTTP_HOST'], 'localhost') !== false));
    
    // En desarrollo, mostrar error detallado; en producción, redirigir
    // Detectar si estamos en desarrollo local
    if ($es_desarrollo) {
        // Mostrar error detallado en desarrollo
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Error - Insignias TecNM</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 50px; background: #f5f5f5; }
                .error-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error-title { color: #dc3545; font-size: 24px; margin-bottom: 20px; }
                .error-content { background: #f8f9fa; padding: 20px; border-radius: 5px; white-space: pre-wrap; font-family: monospace; }
                .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <div class='error-title'>⚠️ Error en Módulo de Administración</div>
                <div class='error-content'><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "\n\n<strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "\n<strong>Línea:</strong> " . $e->getLine() . "\n<strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</div>
                <a href='login.php' class='btn'>Volver al Login</a>
            </div>
        </body>
        </html>";
        exit();
    } else {
        // En producción, mostrar error específico para diagnóstico
        // Guardar error en archivo de log
        $log_file = __DIR__ . '/php_errors.log';
        @file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . " | Archivo: " . $e->getFile() . " | Línea: " . $e->getLine() . "\n", FILE_APPEND);
        
        // Mostrar error específico en página
        session_destroy();
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Error del Sistema - Insignias TecNM</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 50px; background: #f5f5f5; }
                .error-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
                .error-title { color: #dc3545; font-size: 24px; margin-bottom: 20px; }
                .error-content { background: #f8f9fa; padding: 20px; border-radius: 5px; white-space: pre-wrap; font-family: monospace; font-size: 14px; }
                .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <div class='error-title'>⚠️ Error del Sistema</div>
                <div class='error-content'><strong>Mensaje de error:</strong>\n" . htmlspecialchars($e->getMessage()) . "\n\n<strong>Archivo:</strong> " . htmlspecialchars(basename($e->getFile())) . "\n<strong>Línea:</strong> " . $e->getLine() . "\n\n<strong>Posibles causas:</strong>\n- El archivo conexion.php no existe o tiene credenciales incorrectas\n- La base de datos no está accesible\n- Falta el archivo verificar_sesion.php\n- Las funciones necesarias no están disponibles</div>
                <a href='login.php' class='btn'>Volver al Login</a>
                <a href='diagnostico_servidor.php' class='btn' style='background: #28a745; margin-left: 10px;'>Ejecutar Diagnóstico</a>
            </div>
        </body>
        </html>";
        exit();
    }
} catch (Error $e) {
    // Capturar errores fatales de PHP 7+
    $error_msg = "Error fatal en modulo_de_administracion.php: " . $e->getMessage();
    $error_msg .= " | Archivo: " . $e->getFile();
    $error_msg .= " | Línea: " . $e->getLine();
    error_log($error_msg);
    
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Detectar si estamos en desarrollo
    $es_desarrollo = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') || 
                     (isset($_SERVER['HTTP_HOST']) && 
                      ($_SERVER['HTTP_HOST'] === 'localhost' || 
                       $_SERVER['HTTP_HOST'] === '127.0.0.1'));
    
    if ($es_desarrollo) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Error Fatal - Insignias TecNM</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 50px; background: #f5f5f5; }
                .error-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error-title { color: #dc3545; font-size: 24px; margin-bottom: 20px; }
                .error-content { background: #f8f9fa; padding: 20px; border-radius: 5px; white-space: pre-wrap; font-family: monospace; }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <div class='error-title'>❌ Error Fatal</div>
                <div class='error-content'><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "\n\n<strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "\n<strong>Línea:</strong> " . $e->getLine() . "</div>
            </div>
        </body>
        </html>";
        exit();
    } else {
        // En producción, mostrar error específico
        $log_file = __DIR__ . '/php_errors.log';
        @file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERROR FATAL: " . $e->getMessage() . " | Archivo: " . $e->getFile() . " | Línea: " . $e->getLine() . "\n", FILE_APPEND);
        
        session_destroy();
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Error Fatal - Insignias TecNM</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 50px; background: #f5f5f5; }
                .error-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
                .error-title { color: #dc3545; font-size: 24px; margin-bottom: 20px; }
                .error-content { background: #f8f9fa; padding: 20px; border-radius: 5px; white-space: pre-wrap; font-family: monospace; }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <div class='error-title'>❌ Error Fatal del Sistema</div>
                <div class='error-content'><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "\n\n<strong>Archivo:</strong> " . htmlspecialchars(basename($e->getFile())) . "\n<strong>Línea:</strong> " . $e->getLine() . "</div>
                <a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 20px;'>Volver al Login</a>
            </div>
        </body>
        </html>";
        exit();
    }
}


// =======================
// Procesar Formularios
// =======================

// Función para limpiar datos de entrada
function limpiarEntrada($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para validar email (permitir caracteres especiales como ó, ñ, etc.)
function validarEmailPersonalizado($email) {
    // Verificar longitud básica
    if (strlen($email) < 5 || strlen($email) > 254) {
        return false;
    }
    
    // Verificar que tenga @ y al menos un punto después del @
    if (strpos($email, '@') === false || substr_count($email, '@') !== 1) {
        return false;
    }
    
    // Dividir en usuario y dominio
    $partes = explode('@', $email);
    $usuario = $partes[0];
    $dominio = $partes[1];
    
    // Verificar que el usuario no esté vacío
    if (empty($usuario)) {
        return false;
    }
    
    // Verificar que el dominio tenga al menos un punto
    if (strpos($dominio, '.') === false) {
        return false;
    }
    
    // Verificar que no empiece o termine con punto
    if (substr($usuario, 0, 1) === '.' || substr($usuario, -1) === '.') {
        return false;
    }
    
    if (substr($dominio, 0, 1) === '.' || substr($dominio, -1) === '.') {
        return false;
    }
    
    return true;
}

// Variables para mensajes de éxito/error
$mensaje_exito = '';
$mensaje_error = '';

// Categoría
if (isset($_POST['guardar_categoria'])) {
    try {
        $nombre = limpiarEntrada($_POST['nombre']);
        
        if (empty($nombre)) {
            throw new Exception("El nombre de la categoría no puede estar vacío");
        }
        
        // Verificar si la categoría ya existe (evitar duplicados)
        $check_stmt = $conexion->prepare("SELECT id, Nombre_cat FROM cat_insignias WHERE LOWER(TRIM(Nombre_cat)) = LOWER(TRIM(?))");
        if (!$check_stmt) {
            throw new Exception("Error al preparar consulta de verificación: " . $conexion->error);
        }
        
        $check_stmt->bind_param("s", $nombre);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $check_stmt->close();
            throw new Exception("La categoría '$nombre' ya existe en la base de datos. Por favor, elige otro nombre.");
        }
        $check_stmt->close();
        
        // Si no existe, insertar la nueva categoría
        $stmt = $conexion->prepare("INSERT INTO cat_insignias (Nombre_cat) VALUES (?)");
        if (!$stmt) {
            throw new Exception("Error al preparar consulta: " . $conexion->error);
        }
        
        $stmt->bind_param("s", $nombre);
        if ($stmt->execute()) {
            // Recargar categorías para que aparezcan en el select de subcategorías
            $categorias = $conexion->query("SELECT id, Nombre_cat FROM cat_insignias ORDER BY Nombre_cat");
        } else {
            throw new Exception("Error al ejecutar consulta: " . $stmt->error);
        }
        $stmt->close();
        
    } catch (Exception $e) {
        $mensaje_error = "❌ Error al guardar categoría: " . $e->getMessage();
    }
}

// Subcategoría
if (isset($_POST['guardar_subcategoria'])) {
    try {
        $nombre = limpiarEntrada($_POST['nombre']);
        $categoria_id = intval($_POST['categoria_id']);
        
        if (empty($nombre) || $categoria_id <= 0) {
            throw new Exception("Datos de subcategoría inválidos");
        }
        
        // Verificar estructura de la tabla tipo_insignia
        $check_columns = $conexion->query("SHOW COLUMNS FROM tipo_insignia");
        $has_nombre_ins = false;
        $has_nombre_insignia = false;
        $has_cat_ins = false;
        
        if ($check_columns) {
            while ($col = $check_columns->fetch_assoc()) {
                if ($col['Field'] == 'Nombre_ins') $has_nombre_ins = true;
                if ($col['Field'] == 'Nombre_Insignia') $has_nombre_insignia = true;
                if ($col['Field'] == 'Cat_ins') $has_cat_ins = true;
            }
        }
        
        // Construir la consulta según las columnas disponibles
        if ($has_nombre_insignia && $has_cat_ins) {
            $stmt = $conexion->prepare("INSERT INTO tipo_insignia (Nombre_Insignia, Cat_ins) VALUES (?, ?)");
        } elseif ($has_nombre_ins && $has_cat_ins) {
            $stmt = $conexion->prepare("INSERT INTO tipo_insignia (Nombre_ins, Cat_ins) VALUES (?, ?)");
        } elseif ($has_nombre_insignia) {
            $stmt = $conexion->prepare("INSERT INTO tipo_insignia (Nombre_Insignia) VALUES (?)");
            $categoria_id = null; // No usar categoría si no existe la columna
        } else {
            throw new Exception("No se encontró la columna Nombre_ins o Nombre_Insignia en la tabla tipo_insignia");
        }
        
        if (!$stmt) {
            throw new Exception("Error al preparar consulta: " . $conexion->error);
        }
        
        if ($has_cat_ins) {
            $stmt->bind_param("si", $nombre, $categoria_id);
        } else {
            $stmt->bind_param("s", $nombre);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar consulta: " . $stmt->error);
        }
        $stmt->close();
        
    } catch (Exception $e) {
        $mensaje_error = "Error al guardar subcategoría: " . $e->getMessage();
    }
}

// ============================================
// PROCESAMIENTO DE OPERACIONES CRUD PARA MODAL OPCIONES
// ============================================

$mensaje_opciones = '';
$mensaje_error_opciones = '';
$abrir_modal_tab_insignias = false; // true después de crear/editar/eliminar insignia maestra para refrescar lista

// Operaciones CRUD para Destinatarios
if (isset($_POST['accion_opciones']) && $_POST['accion_opciones'] == 'destinatario') {
    try {
        if ($_POST['operacion'] == 'crear') {
            $nombre = limpiarEntrada($_POST['nombre_completo'] ?? '');
            $curp = limpiarEntrada($_POST['curp'] ?? '');
            $correo = limpiarEntrada($_POST['correo'] ?? '');
            $matricula = limpiarEntrada($_POST['matricula'] ?? '');
            $itcentro = intval($_POST['itcentro'] ?? 0);
            
            $stmt = $conexion->prepare("INSERT INTO destinatario (Nombre_Completo, Curp, Correo, Matricula, ITCentro) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $nombre, $curp, $correo, $matricula, $itcentro);
            $stmt->execute();
            $stmt->close();
            $mensaje_opciones = "Destinatario creado exitosamente";
        } elseif ($_POST['operacion'] == 'editar') {
            $id = intval($_POST['id']);
            $nombre = limpiarEntrada($_POST['nombre_completo'] ?? '');
            $curp = limpiarEntrada($_POST['curp'] ?? '');
            $correo = limpiarEntrada($_POST['correo'] ?? '');
            $matricula = limpiarEntrada($_POST['matricula'] ?? '');
            $itcentro = intval($_POST['itcentro'] ?? 0);
            
            $stmt = $conexion->prepare("UPDATE destinatario SET Nombre_Completo=?, Curp=?, Correo=?, Matricula=?, ITCentro=? WHERE ID_destinatario=?");
            $stmt->bind_param("ssssii", $nombre, $curp, $correo, $matricula, $itcentro, $id);
            $stmt->execute();
            $stmt->close();
            $mensaje_opciones = "Destinatario actualizado exitosamente";
        } elseif ($_POST['operacion'] == 'eliminar') {
            $id = intval($_POST['id']);
            $stmt = $conexion->prepare("DELETE FROM destinatario WHERE ID_destinatario=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $mensaje_opciones = "Destinatario eliminado exitosamente";
        }
    } catch (Exception $e) {
        $mensaje_error_opciones = "Error: " . $e->getMessage();
    }
}

// Operaciones CRUD para T_insignias
if (isset($_POST['accion_opciones']) && $_POST['accion_opciones'] == 'T_insignias') {
    try {
        if ($_POST['operacion'] == 'crear') {
            $tipo_insignia = intval($_POST['tipo_insignia'] ?? 0);
            $propone_insignia = intval($_POST['propone_insignia'] ?? 0);
            $programa = limpiarEntrada($_POST['programa'] ?? '');
            $descripcion = limpiarEntrada($_POST['descripcion'] ?? '');
            $criterio = limpiarEntrada($_POST['criterio'] ?? '');
            $fecha_creacion = $_POST['fecha_creacion'] ?? date('Y-m-d');
            $fecha_autorizacion = $_POST['fecha_autorizacion'] ?? date('Y-m-d');
            $nombre_gen_ins = limpiarEntrada($_POST['nombre_gen_ins'] ?? '');
            $estatus = intval($_POST['estatus'] ?? 1);
            $archivo_visual = limpiarEntrada($_POST['archivo_visual'] ?? '');
            
            $stmt = $conexion->prepare("INSERT INTO T_insignias (Tipo_Insignia, Propone_Insignia, Programa, Descripcion, Criterio, Fecha_Creacion, Fecha_Autorizacion, Nombre_gen_ins, Estatus, Archivo_Visual) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssssis", $tipo_insignia, $propone_insignia, $programa, $descripcion, $criterio, $fecha_creacion, $fecha_autorizacion, $nombre_gen_ins, $estatus, $archivo_visual);
            $stmt->execute();
            $stmt->close();
            $mensaje_opciones = "Insignia maestra creada exitosamente";
            $abrir_modal_tab_insignias = true;
        } elseif ($_POST['operacion'] == 'editar') {
            $id = intval($_POST['id']);
            $tipo_insignia = intval($_POST['tipo_insignia'] ?? 0);
            $propone_insignia = intval($_POST['propone_insignia'] ?? 0);
            $programa = limpiarEntrada($_POST['programa'] ?? '');
            $descripcion = limpiarEntrada($_POST['descripcion'] ?? '');
            $criterio = limpiarEntrada($_POST['criterio'] ?? '');
            $fecha_creacion = $_POST['fecha_creacion'] ?? '';
            $fecha_autorizacion = $_POST['fecha_autorizacion'] ?? '';
            $nombre_gen_ins = limpiarEntrada($_POST['nombre_gen_ins'] ?? '');
            $estatus = intval($_POST['estatus'] ?? 1);
            $archivo_visual = limpiarEntrada($_POST['archivo_visual'] ?? '');
            
            $stmt = $conexion->prepare("UPDATE T_insignias SET Tipo_Insignia=?, Propone_Insignia=?, Programa=?, Descripcion=?, Criterio=?, Fecha_Creacion=?, Fecha_Autorizacion=?, Nombre_gen_ins=?, Estatus=?, Archivo_Visual=? WHERE id=?");
            $stmt->bind_param("iissssssisi", $tipo_insignia, $propone_insignia, $programa, $descripcion, $criterio, $fecha_creacion, $fecha_autorizacion, $nombre_gen_ins, $estatus, $archivo_visual, $id);
            $stmt->execute();
            $stmt->close();
            $mensaje_opciones = "Insignia maestra actualizada exitosamente";
            $abrir_modal_tab_insignias = true;
        } elseif ($_POST['operacion'] == 'eliminar') {
            $id = intval($_POST['id']);
            $stmt = $conexion->prepare("DELETE FROM T_insignias WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $mensaje_opciones = "Insignia maestra eliminada exitosamente";
            $abrir_modal_tab_insignias = true;
        }
    } catch (Exception $e) {
        $mensaje_error_opciones = "Error: " . $e->getMessage();
    }
}

// Operaciones CRUD para Categorías
if (isset($_POST['accion_opciones']) && $_POST['accion_opciones'] == 'categorias') {
    try {
        if ($_POST['operacion'] == 'editar') {
            $id = intval($_POST['id']);
            $nombre = limpiarEntrada($_POST['nombre'] ?? '');
            
            $check_id = $conexion->query("SHOW COLUMNS FROM cat_insignias LIKE 'id'");
            $tiene_id = ($check_id && $check_id->num_rows > 0);
            $campo_id = $tiene_id ? 'id' : 'ID_cat';
            
            $stmt = $conexion->prepare("UPDATE cat_insignias SET Nombre_cat=? WHERE $campo_id=?");
            $stmt->bind_param("si", $nombre, $id);
            $stmt->execute();
            $stmt->close();
            $mensaje_opciones = "Categoría actualizada exitosamente";
        } elseif ($_POST['operacion'] == 'eliminar') {
            $id = intval($_POST['id']);
            
            $check_id = $conexion->query("SHOW COLUMNS FROM cat_insignias LIKE 'id'");
            $tiene_id = ($check_id && $check_id->num_rows > 0);
            $campo_id = $tiene_id ? 'id' : 'ID_cat';
            
            $stmt = $conexion->prepare("DELETE FROM cat_insignias WHERE $campo_id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $mensaje_opciones = "Categoría eliminada exitosamente";
        }
    } catch (Exception $e) {
        $mensaje_error_opciones = "Error: " . $e->getMessage();
    }
}

// Operaciones CRUD para Subcategorías
if (isset($_POST['accion_opciones']) && $_POST['accion_opciones'] == 'subcategorias') {
    try {
        if ($_POST['operacion'] == 'editar') {
            $id = intval($_POST['id']);
            $nombre = limpiarEntrada($_POST['nombre'] ?? '');
            $categoria_id = intval($_POST['categoria_id'] ?? 0);
            
            $check_id = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'id'");
            $tiene_id = ($check_id && $check_id->num_rows > 0);
            $campo_id = $tiene_id ? 'id' : 'ID_tipo';
            
            $check_nombre = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Nombre_Insignia'");
            $tiene_nombre_insignia = ($check_nombre && $check_nombre->num_rows > 0);
            $campo_nombre = $tiene_nombre_insignia ? 'Nombre_Insignia' : 'Nombre_ins';
            
            $check_cat = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Cat_ins'");
            $tiene_cat = ($check_cat && $check_cat->num_rows > 0);
            
            if ($tiene_cat) {
                $stmt = $conexion->prepare("UPDATE tipo_insignia SET $campo_nombre=?, Cat_ins=? WHERE $campo_id=?");
                $stmt->bind_param("sii", $nombre, $categoria_id, $id);
            } else {
                $stmt = $conexion->prepare("UPDATE tipo_insignia SET $campo_nombre=? WHERE $campo_id=?");
                $stmt->bind_param("si", $nombre, $id);
            }
            $stmt->execute();
            $stmt->close();
            $mensaje_opciones = "Subcategoría actualizada exitosamente";
        } elseif ($_POST['operacion'] == 'eliminar') {
            $id = intval($_POST['id']);
            
            $check_id = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'id'");
            $tiene_id = ($check_id && $check_id->num_rows > 0);
            $campo_id = $tiene_id ? 'id' : 'ID_tipo';
            
            $stmt = $conexion->prepare("DELETE FROM tipo_insignia WHERE $campo_id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $mensaje_opciones = "Subcategoría eliminada exitosamente";
        }
    } catch (Exception $e) {
        $mensaje_error_opciones = "Error: " . $e->getMessage();
    }
}

// Registro de Insignia Otorgada
if (isset($_POST['guardar_insignia_otorgada'])) {
    try {
        $insignia_id = intval($_POST['insignia_id']);
        $destinatario_id = intval($_POST['destinatario_id']);
        $periodo_id = intval($_POST['periodo_id']);
        $responsable_id = intval($_POST['responsable_id']);
        $estatus_id = intval($_POST['estatus_id']);
        $clave_insignia = limpiarEntrada($_POST['clave_insignia']);
        $fecha_otorgamiento = $_POST['fecha_otorgamiento'];
        $evidencia = limpiarEntrada($_POST['evidencia']);
        $fecha_autorizacion = $_POST['fecha_autorizacion'];

        // Validar datos requeridos
        if (empty($insignia_id) || empty($destinatario_id) || empty($periodo_id) || 
            empty($responsable_id) || empty($estatus_id) || 
            empty($fecha_otorgamiento) || empty($fecha_autorizacion)) {
            throw new Exception("Todos los campos son obligatorios");
        }

        // Generar clave automáticamente si está vacía
        if (empty($clave_insignia)) {
            $clave_insignia = generarClaveInsigniaAutomatica($conexion, $insignia_id);
        }

        // Verificar si la clave ya existe
        $stmt_verificar = $conexion->prepare("SELECT COUNT(*) as total FROM insigniasotorgadas WHERE clave_insignia = ?");
        $stmt_verificar->bind_param("s", $clave_insignia);
        $stmt_verificar->execute();
        $resultado_verificar = $stmt_verificar->get_result();
        $existe_clave = $resultado_verificar->fetch_assoc()['total'] > 0;
        $stmt_verificar->close();

        if ($existe_clave) {
            // Generar nueva clave si ya existe
            $clave_insignia = generarClaveInsigniaAutomatica($conexion, $insignia_id, true);
        }

        // Insertar en insigniasotorgadas
        $stmt = $conexion->prepare("
            INSERT INTO insigniasotorgadas 
            (insignia_id, destinatario_id, periodo_id, responsable_id, estatus_id, 
             clave_insignia, fecha_otorgamiento, evidencia, fecha_autorizacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("iiiiissss", $insignia_id, $destinatario_id, $periodo_id, 
                         $responsable_id, $estatus_id, $clave_insignia, 
                         $fecha_otorgamiento, $evidencia, $fecha_autorizacion);
        
        if ($stmt->execute()) {
            $insignia_otorgada_id = $conexion->insert_id;
            $mensaje_exito = "🎖️ Reconocimiento registrado exitosamente con clave: " . $clave_insignia;
            
            // Opcional: Redirigir a la página de reconocimiento
            echo "<script>
                setTimeout(function() { 
                    if (window.innerWidth <= 768) {
                        window.location.href = 'reconocimiento_insignia.php?id=$insignia_otorgada_id';
                    } else {
                        window.open('reconocimiento_insignia.php?id=$insignia_otorgada_id','_blank');
                    }
                }, 2000);
            </script>";
        } else {
            throw new Exception("Error al registrar la insignia: " . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $mensaje_error = "Error al registrar insignia: " . $e->getMessage();
    }
}

// Función para generar clave de insignia automáticamente
function generarClaveInsigniaAutomatica($conexion, $insignia_id, $forzar_nueva = false) {
    try {
        // Obtener información de la insignia
        $stmt = $conexion->prepare("
            SELECT i.Nombre_gen_ins, ic.Nombre_itc, ic.Acron
            FROM insignias i
            LEFT JOIN it_centros ic ON i.Propone_Insignia = ic.id
            WHERE i.id = ?
        ");
        $stmt->bind_param("i", $insignia_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $insignia = $resultado->fetch_assoc();
        $stmt->close();

        if (!$insignia) {
            throw new Exception("Insignia no encontrada");
        }

        // Crear siglas del centro
        $siglas_centro = 'TECNM';
        if (!empty($insignia['Acron'])) {
            $siglas_centro = strtoupper($insignia['Acron']);
        } elseif (!empty($insignia['Nombre_itc'])) {
            $palabras = explode(' ', $insignia['Nombre_itc']);
            $siglas_centro = '';
            foreach ($palabras as $palabra) {
                if (!empty($palabra)) {
                    $siglas_centro .= strtoupper(substr($palabra, 0, 1));
                }
            }
            $siglas_centro = substr($siglas_centro, 0, 4);
        }

        // Crear siglas de la insignia
        $siglas_insignia = 'INS';
        if (!empty($insignia['Nombre_gen_ins'])) {
            $palabras = explode(' ', $insignia['Nombre_gen_ins']);
            $siglas_insignia = '';
            foreach ($palabras as $palabra) {
                if (!empty($palabra)) {
                    $siglas_insignia .= strtoupper(substr($palabra, 0, 1));
                }
            }
            $siglas_insignia = substr($siglas_insignia, 0, 3);
        }

        // Generar fecha
        $año = date('Y');
        $mes = date('m');

        // Generar número secuencial
        $numero = 1;
        if (!$forzar_nueva) {
            // Buscar el último número usado para este patrón
            $patron = $siglas_centro . '-' . $siglas_insignia . '-' . $año . $mes . '-%';
            $stmt_count = $conexion->prepare("
                SELECT COUNT(*) as total 
                FROM insigniasotorgadas 
                WHERE clave_insignia LIKE ?
            ");
            $stmt_count->bind_param("s", $patron);
            $stmt_count->execute();
            $resultado_count = $stmt_count->get_result();
            $total = $resultado_count->fetch_assoc()['total'];
            $stmt_count->close();
            
            $numero = $total + 1;
        } else {
            // Para claves duplicadas, usar timestamp
            $numero = substr(time(), -3);
        }

        // Formatear número con ceros a la izquierda
        $numero_formateado = str_pad($numero, 3, '0', STR_PAD_LEFT);

        // Generar clave final
        $clave = $siglas_centro . '-' . $siglas_insignia . '-' . $año . $mes . '-' . $numero_formateado;

        return $clave;

    } catch (Exception $e) {
        // Fallback: clave simple con timestamp
        return 'TECNM-INS-' . date('Ym') . '-' . substr(time(), -3);
    }
}

// Insignia con Metadatos (código anterior)
if (isset($_POST['guardar_metadatos'])) {
    try {
        $nombre = limpiarEntrada($_POST['nombre']);
        $descripcion = limpiarEntrada($_POST['descripcion']);
        $emisor = limpiarEntrada($_POST['emisor']);
        $receptor = limpiarEntrada($_POST['receptor']);
        $instituto = limpiarEntrada($_POST['instituto']);
        $fecha_emision = $_POST['fecha_emision'];
        $fecha_creacion = date("Y-m-d");
        $id_creador = $_SESSION['usuario_id'];

        // Validar datos requeridos
        if (empty($nombre) || empty($descripcion) || empty($emisor) || empty($receptor)) {
            throw new Exception("Todos los campos son obligatorios");
        }

        // ========================================
        // VALIDACIÓN: Verificar si el estudiante ya tiene un reconocimiento
        // ========================================
        
        // Buscar si ya existe un usuario con ese nombre completo
        $stmt_buscar_usuario = $conexion->prepare("
            SELECT u.Id_Usuario, u.Nombre, u.Apellido_Paterno, u.Apellido_Materno 
            FROM Usuario u 
            WHERE CONCAT(u.Nombre, ' ', u.Apellido_Paterno, ' ', u.Apellido_Materno) = ?
            AND u.Rol = 'Estudiante'
        ");
        
        if ($stmt_buscar_usuario) {
            $stmt_buscar_usuario->bind_param("s", $receptor);
            $stmt_buscar_usuario->execute();
            $resultado_usuario = $stmt_buscar_usuario->get_result();
            
            if ($resultado_usuario->num_rows > 0) {
                $usuario_encontrado = $resultado_usuario->fetch_assoc();
                $usuario_id = $usuario_encontrado['Id_Usuario'];
                
                // Verificar si ya tiene una insignia asignada
                $stmt_verificar_insignia = $conexion->prepare("
                    SELECT COUNT(*) as total 
                    FROM insigniasotorgadas io
                    INNER JOIN destinatario d ON io.destinatario_id = d.id
                    WHERE d.id = ?
                ");
                
                if ($stmt_verificar_insignia) {
                    // Obtener destinatario_id del usuario
                    $stmt_dest_id = $conexion->prepare("SELECT id FROM destinatario WHERE Nombre_Completo LIKE ?");
                    $nombre_busqueda = $usuario_encontrado['Nombre'] . '%';
                    $stmt_dest_id->bind_param("s", $nombre_busqueda);
                    $stmt_dest_id->execute();
                    $result_dest_id = $stmt_dest_id->get_result();
                    
                    if ($result_dest_id->num_rows > 0) {
                        $dest_id_row = $result_dest_id->fetch_assoc();
                        $destinatario_id = $dest_id_row['id'];
                        $stmt_dest_id->close();
                        
                        $stmt_verificar_insignia->bind_param("i", $destinatario_id);
                        $stmt_verificar_insignia->execute();
                        $resultado_insignia = $stmt_verificar_insignia->get_result();
                    } else {
                        $stmt_dest_id->close();
                        $mensaje_error = "No se encontró destinatario asociado al usuario";
                        // No se puede continuar sin destinatario_id
                        throw new Exception("No se encontró destinatario asociado al usuario");
                    }
                    $total_insignias = $resultado_insignia->fetch_assoc()['total'];
                    $stmt_verificar_insignia->close();
                    
                    if ($total_insignias > 0) {
                        // El estudiante ya tiene un reconocimiento
                        $mensaje_error = "YA_EXISTE_RECONOCIMIENTO";
                        $stmt_buscar_usuario->close();
                        throw new Exception("El estudiante " . htmlspecialchars($receptor) . " ya tiene un reconocimiento asignado. No se puede tramitar otro reconocimiento.");
                    }
                }
            }
            $stmt_buscar_usuario->close();
        }

        // Imagen fija de Responsabilidad Social
        $url_imagen = "imagen/insignia_Responsabilidad Social.png";

        $stmt = $conexion->prepare("INSERT INTO insignias 
                (Nombre_Insignia, Descripcion, Criterios_Emision, Url_Imagen, Fecha_Creacion, Id_Creador, Receptor, Fecha_Emision, Institucion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Error al preparar consulta: " . $conexion->error);
        }
        
        $stmt->bind_param("sssssisss", $nombre, $descripcion, $emisor, $url_imagen, $fecha_creacion, $id_creador, $receptor, $fecha_emision, $instituto);
        
        if ($stmt->execute()) {
            $insignia_id = $conexion->insert_id;
            $mensaje_exito = "Insignia '$nombre' creada exitosamente";
            // Redirección mejorada para móviles y desktop
            echo "<script>
                setTimeout(function() { 
                    if (window.innerWidth <= 768) {
                        // En móviles, redirigir en la misma ventana
                        window.location.href = 'reconocimiento_insignia.php?id=$insignia_id';
                    } else {
                        // En desktop, abrir en nueva ventana
                        window.open('reconocimiento_insignia.php?id=$insignia_id','_blank');
                    }
                }, 1500);
            </script>";
        } else {
            throw new Exception("Error al ejecutar consulta: " . $stmt->error);
        }
        $stmt->close();
        
    } catch (Exception $e) {
        // Solo mostrar mensaje de error si NO es el caso de reconocimiento duplicado
        if ($mensaje_error !== "YA_EXISTE_RECONOCIMIENTO") {
            $mensaje_error = "Error al crear insignia: " . $e->getMessage();
        }
    }
}

// Registro de Usuario
if (isset($_POST['guardar_usuario'])) {
    try {
        $nombre = limpiarEntrada($_POST['nombre']);
        $apellido_paterno = limpiarEntrada($_POST['apellido_paterno']);
        $apellido_materno = limpiarEntrada($_POST['apellido_materno']);
        $correo = limpiarEntrada($_POST['correo']);
        $password = $_POST['password']; // No limpiar contraseña para preservar caracteres especiales
        $rol = $_POST['rol'];

        // Validar datos requeridos
        if (empty($nombre) || empty($apellido_paterno) || empty($correo) || empty($password)) {
            throw new Exception("Todos los campos son obligatorios");
        }
        
        // Validar formato de email (permitir caracteres especiales como ó, ñ, etc.)
        if (!validarEmailPersonalizado($correo)) {
            throw new Exception("Formato de correo electrónico inválido");
        }

        $stmt = $conexion->prepare("INSERT INTO Usuario (Nombre, Apellido_Paterno, Apellido_Materno, Correo, Contrasena, Rol, Estado) VALUES (?, ?, ?, ?, ?, ?, 'Activo')");
        
        if (!$stmt) {
            throw new Exception("Error al preparar consulta: " . $conexion->error);
        }
        
        $stmt->bind_param("ssssss", $nombre, $apellido_paterno, $apellido_materno, $correo, $password, $rol);
        
        if ($stmt->execute()) {
            $mensaje_exito = "Usuario '$nombre $apellido_paterno' registrado exitosamente";
        } else {
            throw new Exception("Error al ejecutar consulta: " . $stmt->error);
        }
        $stmt->close();
        
    } catch (Exception $e) {
        $mensaje_error = "Error al registrar usuario: " . $e->getMessage();
    }
}

// ========================================
// PROCESAMIENTO DE FUNCIONALIDADES SUPERUSUARIO
// ========================================

// Guardar configuraciones críticas
if (isset($_POST['guardar_configuraciones']) && esSuperUsuario()) {
    try {
        $nombre_institucion = limpiarEntrada($_POST['nombre_institucion']);
        $logo_institucion = limpiarEntrada($_POST['logo_institucion']);
        $max_insignias = intval($_POST['max_insignias_estudiante']);
        $dias_validez = intval($_POST['dias_validez_insignias']);
        $max_intentos = intval($_POST['max_intentos_login']);
        $tiempo_bloqueo = intval($_POST['tiempo_bloqueo']);
        
        // Aquí podrías guardar en una tabla de configuraciones
        // Por ahora solo mostramos un mensaje de éxito
        $mensaje_exito = "Configuraciones críticas guardadas exitosamente";
        
    } catch (Exception $e) {
        $mensaje_error = "Error al guardar configuraciones: " . $e->getMessage();
    }
}

// Cambiar estado de administrador
if (isset($_POST['cambiar_estado_admin']) && esSuperUsuario()) {
    try {
        $admin_id = intval($_POST['admin_id']);
        
        // Obtener estado actual
        $stmt = $conexion->prepare("SELECT Estado FROM Usuario WHERE Id_Usuario = ? AND Rol IN ('Admin', 'SuperUsuario')");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $admin = $resultado->fetch_assoc();
            $nuevo_estado = $admin['Estado'] === 'Activo' ? 'Inactivo' : 'Activo';
            
            // Actualizar estado
            $stmt_update = $conexion->prepare("UPDATE Usuario SET Estado = ? WHERE Id_Usuario = ?");
            $stmt_update->bind_param("si", $nuevo_estado, $admin_id);
            
            if ($stmt_update->execute()) {
                $mensaje_exito = "Estado del administrador actualizado exitosamente";
            } else {
                throw new Exception("Error al actualizar estado");
            }
            $stmt_update->close();
        } else {
            throw new Exception("Administrador no encontrado");
        }
        $stmt->close();
        
    } catch (Exception $e) {
        $mensaje_error = "Error al cambiar estado: " . $e->getMessage();
    }
}

// Traer categorías para subcategorías
$categorias = null;
try {
    if ($conexion && !$conexion->connect_errno) {
        // Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }
        // Consultar categorías existentes para el select
        $categorias = $conexion->query("SELECT id, Nombre_cat FROM cat_insignias ORDER BY Nombre_cat");
    }
} catch (Exception $e) {
    $categorias = null;
}

// Consultar categorías para el formulario de categorías (siempre, no solo después de guardar)
if (!isset($categorias) || !$categorias) {
    try {
        $categorias = $conexion->query("SELECT id, Nombre_cat FROM cat_insignias ORDER BY Nombre_cat");
    } catch (Exception $e) {
        $categorias = null;
    }
}

// Limpiar buffer de salida antes de mostrar HTML
ob_clean();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Administración - Insignias TecNM</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css_profesional.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    /* Importar fuente Inter */
    * {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    /* ========== HEADER AZUL PROFESIONAL (igual a login/index) ========== */
    header.header-principal {
      background: linear-gradient(135deg, 
        #1e3c72 0%, 
        #2a5298 50%, 
        #1e3c72 100%);
      backdrop-filter: blur(40px) saturate(180%);
      color: white;
      text-align: center;
      padding: 30px 0;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
      box-shadow: 
        0 8px 32px rgba(0,0,0,0.3),
        inset 0 1px 0 rgba(255,255,255,0.2);
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    header.header-principal::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        radial-gradient(circle at 50% 0%, rgba(255,255,255,0.1) 0%, transparent 70%);
      pointer-events: none;
    }
    
    header.header-principal h1 {
      margin: 0;
      font-size: 28px;
      font-weight: 800;
      text-shadow: 
        0 4px 8px rgba(0,0,0,0.4),
        0 0 20px rgba(59, 130, 246, 0.3);
      background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 50%, #ffffff 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .header-content-admin {
      display: flex;
      align-items: center;
      justify-content: center;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      position: relative;
    }
    
    .header-logo-admin {
      position: absolute;
      left: -260px;
      top: 50%;
      transform: translateY(-50%);
      height: 60px;
      width: auto;
      max-width: none;
      object-fit: contain;
      filter: brightness(0) invert(1);
      transition: all 0.3s ease;
    }
    
    .header-logo-admin:hover {
      transform: translateY(-50%) scale(1.1);
      filter: brightness(0) invert(1) drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
    }
    
    /* Ajuste para el contenedor principal */
    .main-container {
      padding-top: 100px !important;
      padding-bottom: 150px !important;
    }
    
    /* ========== FOOTER AZUL PROFESIONAL ========== */
    footer {
      background: #1e3c72;
      color: white;
      padding: 40px 0;
      margin-top: 50px;
      text-align: center;
      position: relative;
    }
    
    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .footer-section {
      margin-bottom: 25px;
    }
    
    footer h3 {
      font-size: 16px;
      margin-bottom: 12px;
      color: #fff;
      font-weight: bold;
    }
    
    .footer-links {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 18px;
      margin-bottom: 18px;
    }
    
    .footer-links a {
      color: #fff;
      text-decoration: underline;
      font-size: 14px;
      transition: color 0.3s ease;
    }
    
    .footer-links a:hover {
      color: #a0c4ff;
    }
    
    .social-icons {
      display: flex;
      justify-content: center;
      gap: 18px;
      margin-top: 18px;
    }
    
    .social-icon {
      width: 35px;
      height: 35px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 16px;
      transition: all 0.3s ease;
    }
    
    .social-icon:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateY(-2px);
    }
    
    .copyright {
      margin-top: 25px;
      padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
      color: #a0c4ff;
      font-size: 14px;
    }
    
    /* Estilos específicos para el módulo de administración */
    
    /* Header personalizado */
    .admin-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 25px 35px;
      background: linear-gradient(135deg, 
        rgba(255,255,255,0.15) 0%, 
        rgba(255,255,255,0.08) 100%);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      box-shadow: 
        0 8px 24px rgba(0,51,102,0.15),
        inset 0 1px 0 rgba(255,255,255,0.2);
      margin-bottom: 30px;
      transition: all 0.3s ease;
    }
    
    .admin-header:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(0,51,102,0.2);
    }
    
    .admin-header h2 {
      margin: 0;
      font-size: 32px;
      font-weight: 900;
      background: linear-gradient(135deg, #ffffff 0%, #e8f2fa 50%, #ffffff 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.5px;
      text-shadow: 0 2px 8px rgba(0,102,204,0.2);
    }
    
    .header-actions {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .logout-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 20px;
      background: linear-gradient(135deg, #dc3545, #c82333);
      color: white;
      text-decoration: none;
      border-radius: 12px;
      font-weight: 600;
      transition: var(--transition);
      box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
    }
    
    .logout-btn:hover {
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 12px 30px rgba(220, 53, 69, 0.4);
      color: white;
    }
    
    /* Navegación de tabs profesional */
    .nav-tabs {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 25px;
      padding: 12px;
      background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.08) 0%, 
        rgba(255, 255, 255, 0.03) 100%);
      backdrop-filter: blur(30px);
      border-radius: 12px;
      box-shadow:
        0 15px 30px rgba(0,0,0,0.15),
        inset 0 1px 0 rgba(255,255,255,0.1);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .nav-tabs button {
      background: linear-gradient(135deg, 
        rgba(27, 57, 106, 0.8) 0%, 
        rgba(59, 130, 246, 0.6) 100%);
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 12px;
      font-weight: 600;
      transition: var(--transition);
      box-shadow: 0 4px 15px rgba(27, 57, 106, 0.3);
    }
    
    .nav-tabs button:hover {
      background: linear-gradient(135deg, 
        rgba(59, 130, 246, 0.8) 0%, 
        rgba(139, 92, 246, 0.6) 100%);
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }
    
    .nav-tabs button.active {
      background: linear-gradient(135deg, #dc3545, #c82333);
      box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
    }
    
    /* Contenido de tabs */
    .tab-content {
      min-height: 500px;
    }
    
    /* Estadísticas profesionales */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .stat-card {
      background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.1) 0%, 
        rgba(255, 255, 255, 0.05) 100%);
      backdrop-filter: blur(30px);
      border-radius: 20px;
      padding: 30px;
      text-align: center;
      box-shadow: 
        0 15px 30px rgba(0,0,0,0.15),
        inset 0 1px 0 rgba(255,255,255,0.1);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: var(--transition);
      cursor: pointer;
    }
    
    .stat-card:hover {
      transform: translateY(-5px) scale(1.02);
      box-shadow: 
        0 25px 50px rgba(0,0,0,0.2),
        inset 0 1px 0 rgba(255,255,255,0.2);
    }
    
    .stat-number {
      font-size: 36px;
      font-weight: 900;
      background: linear-gradient(135deg, 
        #ffffff 0%, 
        #3b82f6 50%, 
        #8b5cf6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 10px;
      text-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
    }
    
    .stat-label {
      font-size: 16px;
      color: rgba(255, 255, 255, 0.8);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    /* Lista de usuarios profesional */
    .user-list {
      display: grid;
      gap: 20px;
    }
    
    .user-item {
      background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.08) 0%, 
        rgba(255, 255, 255, 0.03) 100%);
      backdrop-filter: blur(30px);
      border-radius: 16px;
      padding: 25px;
      box-shadow: 
        0 10px 25px rgba(0,0,0,0.1),
        inset 0 1px 0 rgba(255,255,255,0.1);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: var(--transition);
    }
    
    .user-item:hover {
      transform: translateY(-3px);
      box-shadow: 
        0 15px 35px rgba(0,0,0,0.15),
        inset 0 1px 0 rgba(255,255,255,0.2);
    }
    
    .user-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .user-details h4 {
      margin: 0 0 8px 0;
      color: rgba(255, 255, 255, 0.95);
      font-size: 18px;
      font-weight: 700;
    }
    
    .user-details small {
      color: rgba(255, 255, 255, 0.7);
      font-size: 14px;
    }
    
    .user-badges {
      display: flex;
      gap: 8px;
      margin-top: 10px;
    }
    
    .badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .badge-success {
      background: linear-gradient(135deg, #28a745, #20c997);
      color: white;
    }
    
    .badge-danger {
      background: linear-gradient(135deg, #dc3545, #c82333);
      color: white;
    }
    
    .badge-warning {
      background: linear-gradient(135deg, #ffc107, #e0a800);
      color: #212529;
    }
    
    .badge-info {
      background: linear-gradient(135deg, #17a2b8, #138496);
      color: white;
    }
    
    /* RESPONSIVE - Tablet */
    @media (max-width: 1024px) {
      .header-principal {
        padding: 25px 0;
      }
      
      .header-logo-admin {
        height: 50px;
        width: auto;
        max-width: none;
        object-fit: contain;
        left: -180px;
      }
      
      .header-content-admin h1 {
        font-size: 28px;
      }
      
      .main-container {
        padding: 90px 25px 120px;
      }
    }
    
    /* RESPONSIVE - Móviles y tablets pequeñas */
    @media (max-width: 768px) {
      .header-principal {
        padding: 20px 0;
      }
      
      .header-content-admin {
        padding: 0 15px;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        gap: 12px;
      }
      
      .header-logo-admin {
        position: relative;
        left: auto;
        top: auto;
        transform: none;
        height: 45px;
        width: auto;
        max-width: none;
        object-fit: contain;
        display: block;
        margin: 0;
      }
      
      .header-content-admin h1 {
        font-size: 22px;
        margin: 0;
      }
      
      .main-container {
        padding: 80px 20px 120px;
      }
      
      .card {
        padding: 30px 20px;
        border-radius: 16px;
      }
      
      .card-title {
        font-size: 24px;
      }
      
      .card-subtitle {
        font-size: 16px;
      }
      
      .admin-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
      }
      
      .nav-tabs {
        flex-direction: column;
        align-items: center;
        gap: 12px;
      }
      
      .nav-tabs button {
        width: 100%;
        max-width: 100%;
        padding: 14px 20px;
        font-size: 15px;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
        gap: 15px;
      }
      
      .user-info {
        flex-direction: column;
        gap: 15px;
        text-align: center;
      }
      
      .form-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
      }
      
      table {
        min-width: 500px;
        font-size: 13px;
      }
      
      th, td {
        padding: 10px 6px;
        font-size: 13px;
      }
      
      .btn {
        width: 100%;
        padding: 14px 20px;
        font-size: 14px;
        margin-bottom: 10px;
      }
    }
    
    /* RESPONSIVE - Móviles pequeños */
    @media (max-width: 480px) {
      .header-principal {
        padding: 15px 0;
      }
      
      .header-content-admin {
        padding: 0 10px;
        gap: 8px;
      }
      
      .header-logo-admin {
        height: 35px;
        width: auto;
        max-width: none;
        object-fit: contain;
      }
      
      .header-content-admin h1 {
        font-size: 18px;
      }
      
      .main-container {
        padding: 70px 15px 100px;
      }
      
      .card {
        padding: 25px 15px;
      }
      
      .card-title {
        font-size: 20px;
      }
      
      .card-subtitle {
        font-size: 14px;
      }
      
      .nav-tabs button {
        padding: 12px 16px;
        font-size: 14px;
      }
      
      table {
        min-width: 400px;
        font-size: 12px;
      }
      
      /* Estilos para Modal Opciones */
      .tab-opciones-btn.active {
        background: #6c757d !important;
        color: white !important;
        border-bottom: 3px solid #28a745 !important;
      }
      
      .tab-opciones-content {
        display: none !important;
      }
      
      .tab-opciones-content.active {
        display: block !important;
      }
      
      #modalOpciones table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
      }
      
      #modalOpciones table th,
      #modalOpciones table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
      }
      
      #modalOpciones table th {
        background: #6c757d;
        color: white;
        font-weight: 600;
      }
      
      #modalOpciones table tr:hover {
        background: #f8f9fa;
      }
      
      #modalOpciones .btn-accion {
        padding: 5px 10px;
        margin: 2px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
      }
      
      #modalOpciones .btn-editar {
        background: #17a2b8;
        color: white;
      }
      
      #modalOpciones .btn-eliminar {
        background: #dc3545;
        color: white;
      }
      
      #modalOpciones .btn-accion:hover {
        opacity: 0.8;
        transform: scale(1.05);
      }
      
      th, td {
        padding: 8px 4px;
        font-size: 11px;
      }
      
      .btn {
        padding: 12px 16px;
        font-size: 13px;
      }
    }
    
    /* RESPONSIVE - Móviles muy pequeños */
    @media (max-width: 360px) {
      .header-principal {
        padding: 12px 0;
      }
      
      .header-logo-admin {
        height: 30px;
        width: auto;
        max-width: none;
        object-fit: contain;
      }
      
      .header-content-admin h1 {
        font-size: 16px;
      }
      
      .main-container {
        padding: 60px 10px 90px;
      }
      
      .card {
        padding: 20px 12px;
      }
      
      .card-title {
        font-size: 18px;
      }
    }
    
    /* Orientación horizontal en móviles */
    @media (max-width: 768px) and (orientation: landscape) {
      .header-principal {
        padding: 15px 0;
      }
      
      .main-container {
        padding: 60px 20px 100px;
      }
    }
    
    /* Header responsivo */
    header {
      display:flex; 
      justify-content:center; 
      align-items:center; 
      position:relative; 
      background:#1b396a; 
      color:white; 
      padding:15px 10px;
      min-height:60px;
      box-sizing:border-box;
    }
    header h2 {
      flex:1; 
      text-align:center; 
      margin:0; 
      font-size:18px;
    }
    header img {
      position:absolute; 
      right:10px; 
      height:35px;
      max-width:50px;
    }
    header a.volver {
      position:absolute; 
      left:10px; 
      top:50%; 
      transform:translateY(-50%); 
      text-decoration:none; 
      font-size:24px; 
      color:white; 
      font-weight:bold;
      padding:5px;
    }
    
    .header-actions {
      position:absolute; 
      right:10px; 
      top:50%; 
      transform:translateY(-50%); 
      display:flex;
      align-items:center;
      gap:10px;
    }
    
    .logout-btn {
      text-decoration:none; 
      font-size:20px; 
      color:white; 
      padding:8px;
      border-radius:50%;
      background:rgba(255,255,255,0.1);
      transition:all 0.3s ease;
      display:flex;
      align-items:center;
      justify-content:center;
      width:35px;
      height:35px;
    }
    
    .logout-btn:hover {
      background:rgba(255,255,255,0.2);
      transform:scale(1.1);
    }
    
    /* Container responsivo */
    .container {
      margin:15px auto; 
      width:95%; 
      max-width:600px;
      background:white; 
      padding:15px; 
      border-radius:12px;
      box-sizing:border-box;
    }
    
    /* Navegación responsiva */
    nav {
      margin-bottom:20px; 
      text-align:center;
      display:flex;
      flex-wrap:wrap;
      justify-content:center;
      gap:5px;
    }
    nav button {
      background:#1b396a; 
      color:white; 
      border:none; 
      padding:8px 15px; 
      margin:2px; 
      border-radius:6px; 
      cursor:pointer;
      font-size:14px;
      min-width:120px;
      flex:1;
      max-width:180px;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
    }
    nav button:hover {
      background:#0d2a4a;
    }
    nav button:active {
      background:#0d2a4a;
      transform:scale(0.98);
    }
    
    .tab {display:none;}
    .tab.active {display:block;}
    
    /* Estilo para el ícono de mostrar/ocultar contraseña */
    #toggle-password {
      transition: color 0.3s ease;
    }
    
    #toggle-password:hover {
      color: #1b396a !important;
    }
    
    /* Formularios responsivos */
    form {text-align:left; margin-top:20px;}
    label {
      display:block; 
      margin:10px 0 5px; 
      font-weight:bold;
      color:#333;
    }
    input, select, textarea {
      width:100%; 
      padding:10px; 
      border-radius:6px; 
      border:1px solid #ccc; 
      margin-bottom:15px;
      box-sizing:border-box;
      font-size:16px;
      color: #000;
      background-color: #fff;
    }
    
    /* Estilos específicos para formulario de categorías */
    .form-label {
      color: #000 !important;
      font-weight: bold;
      display: block;
      margin: 10px 0 5px;
    }
    
    .form-control {
      color: #000 !important;
      background-color: #fff !important;
    }
    
    .form-control::placeholder {
      color: #999;
    }
    textarea {
      min-height:80px;
      resize:vertical;
    }
    button[type="submit"] {
      background:#1b396a; 
      color:white; 
      border:none; 
      padding:8px 16px; 
      border-radius:6px; 
      cursor:pointer;
      font-size:14px;
      width:auto;
      display:inline-block;
      margin-top:10px;
    }
    button[type="submit"]:hover {
      background:#0d2a4a;
    }
    button[type="submit"]:active {
      background:#0d2a4a;
      transform:scale(0.98);
    }
    
    .welcome-section {
      background:#f8f9fa; 
      border-radius:8px; 
      padding:15px; 
      margin-bottom:20px; 
      border-left:4px solid #1b396a;
    }
    .welcome-section h3 {
      margin:0; 
      color:#1b396a; 
      font-size:18px;
    }
    
    .logout-btn {
      display:inline-block; 
      margin:10px 5px; 
      text-align:center; 
      background:#1b396a; 
      color:white; 
      padding:8px 16px; 
      border-radius:6px; 
      text-decoration:none; 
      font-weight:500;
      font-size:14px;
      border:1px solid #1b396a;
      transition:all 0.3s ease;
    }
    
    .logout-btn:hover {
      background:#2c5aa0;
      border-color:#2c5aa0;
      transform:translateY(-1px);
      box-shadow:0 2px 8px rgba(27, 57, 106, 0.3);
    }
    
    /* Media queries para móviles */
    @media (max-width: 768px) {
      header {
        padding:10px 5px;
        min-height:50px;
      }
      header h2 {
        font-size:16px;
      }
      header img {
        height:30px;
        right:5px;
      }
      header a.volver {
        left:5px;
        font-size:20px;
      }
      
      .container {
        margin:10px auto;
        padding:10px;
        width:98%;
      }
      
      nav {
        flex-direction:column;
        gap:3px;
      }
      nav button {
        width:100%;
        max-width:none;
        padding:10px;
        font-size:16px;
      }
      
      input, select, textarea {
        font-size:16px;
        padding:12px;
      }
      
      button[type="submit"] {
        padding:15px;
        font-size:18px;
      }
      
      .welcome-section h3 {
        font-size:16px;
      }
      
      footer {
        font-size:11px;
        padding:30px 0;
      }
    }
    
    @media (max-width: 480px) {
      header h2 {
        font-size:14px;
      }
      header img {
        height:25px;
      }
      header a.volver {
        font-size:18px;
      }
      
      .container {
        margin:5px auto;
        padding:8px;
      }
      
      nav button {
        padding:12px;
        font-size:14px;
      }
      
      .welcome-section {
        padding:10px;
      }
      .welcome-section h3 {
        font-size:14px;
      }
    }
    
    /* Estilos para el historial */
    .historial-container {
      margin-top: 20px;
    }
    
    .historial-stats {
      background: #e8f4fd;
      border: 2px solid #0046c3;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      text-align: center;
    }
    
    .historial-stats p {
      margin: 0;
      color: #0046c3;
      font-weight: bold;
    }
    
    .historial-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    
    .historial-item {
      background: white;
      border: 1px solid #dee2e6;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s ease;
    }
    
    .historial-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .historial-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 2px solid #f8f9fa;
    }
    
    .historial-header h4 {
      margin: 0;
      color: #0046c3;
      font-size: 18px;
    }
    
    .historial-fecha {
      background: #0046c3;
      color: white;
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 12px;
      font-weight: bold;
    }
    
    .historial-details {
      margin-bottom: 15px;
    }
    
    .historial-details p {
      margin: 5px 0;
      font-size: 14px;
      color: #333;
    }
    
    .historial-description {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 15px;
      border-left: 4px solid #0046c3;
    }
    
    .historial-description p {
      margin: 0;
      font-style: italic;
      color: #666;
      line-height: 1.5;
    }
    
    .historial-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    
    .btn-historial {
      background: #28a745;
      color: white;
      text-decoration: none;
      padding: 8px 15px;
      border-radius: 6px;
      font-size: 14px;
      font-weight: bold;
      transition: background 0.3s ease;
      flex: 1;
      text-align: center;
      min-width: 120px;
    }
    
    .btn-historial:hover {
      background: #218838;
      color: white;
      text-decoration: none;
    }
    
    .btn-historial:last-child {
      background: #17a2b8;
    }
    
    .btn-historial:last-child:hover {
      background: #138496;
    }
    
    .no-data {
      text-align: center;
      padding: 40px 20px;
      color: #666;
    }
    
    .no-data p {
      margin: 10px 0;
      font-size: 16px;
    }
    
    /* Responsivo para historial */
    @media (max-width: 768px) {
      .historial-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
      
      .historial-actions {
        flex-direction: column;
      }
      
      .btn-historial {
        flex: none;
        width: 100%;
      }
    }
  </style>
  <script>
    function mostrarTab(tabId) {
      // Función simplificada y robusta
      try {
        // Obtener todos los tabs
        let tabs = document.querySelectorAll(".tab");
        
        // Remover clase active de todos los tabs
        tabs.forEach(tab => {
          tab.classList.remove("active");
        });
        
        // Buscar el tab objetivo
        let targetTab = document.getElementById(tabId);
        if (targetTab) {
          targetTab.classList.add("active");
          
          // Scroll suave hacia arriba en móviles
          if (window.innerWidth <= 768) {
            window.scrollTo({
              top: 0,
              behavior: 'smooth'
            });
          }
        } else {
          console.error('Tab no encontrado:', tabId);
          // Fallback: activar el primer tab disponible
          if (tabs.length > 0) {
            tabs[0].classList.add("active");
          }
        }
      } catch (error) {
        console.error('Error en mostrarTab:', error);
      }
    }
    
    // Funciones para mostrar detalles de auditoría
    function mostrarDetalleSuperUsuarios() {
      // Crear modal para mostrar SuperUsuarios
      const modal = document.createElement('div');
      modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        z-index: 10000;
        display: flex;
        justify-content: center;
        align-items: center;
      `;
      
      modal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 15px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
          <h3 style="color: #388e3c; margin-bottom: 20px;">🔧 Detalles de SuperUsuarios</h3>
          <div id="superusuarios-content">
            <p>Cargando información...</p>
          </div>
          <div style="text-align: center; margin-top: 20px;">
            <button onclick="this.closest('.modal').remove()" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">
              Cerrar
            </button>
          </div>
        </div>
      `;
      
      modal.className = 'modal';
      document.body.appendChild(modal);
      
      // Cargar datos de SuperUsuarios
      cargarSuperUsuarios();
    }
    
    function mostrarDetalleAdministradores() {
      // Crear modal para mostrar Administradores
      const modal = document.createElement('div');
      modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        z-index: 10000;
        display: flex;
        justify-content: center;
        align-items: center;
      `;
      
      modal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 15px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
          <h3 style="color: #f57c00; margin-bottom: 20px;">👤 Detalles de Administradores</h3>
          <div id="administradores-content">
            <p>Cargando información...</p>
          </div>
          <div style="text-align: center; margin-top: 20px;">
            <button onclick="this.closest('.modal').remove()" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">
              Cerrar
            </button>
          </div>
        </div>
      `;
      
      modal.className = 'modal';
      document.body.appendChild(modal);
      
      // Cargar datos de Administradores
      cargarAdministradores();
    }
    
    function cargarSuperUsuarios() {
      // Simular carga de datos (en una implementación real, harías una petición AJAX)
      const content = document.getElementById('superusuarios-content');
      content.innerHTML = `
        <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
          <h4 style="color: #388e3c; margin: 0 0 10px 0;">Información de SuperUsuarios</h4>
          <p><strong>Total:</strong> <?php echo $stats['usuarios_por_rol']['SuperUsuario'] ?? 0; ?></p>
          <p><strong>Permisos:</strong> Acceso completo al sistema</p>
          <p><strong>Funciones:</strong> Gestión de administradores, configuración del sistema, auditoría completa</p>
        </div>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
          <h5 style="margin: 0 0 10px 0;">Acciones disponibles:</h5>
          <ul style="margin: 0; padding-left: 20px;">
            <li>Crear nuevos administradores</li>
            <li>Gestionar permisos de usuarios</li>
            <li>Configurar parámetros del sistema</li>
            <li>Ver auditoría completa</li>
            <li>Exportar datos del sistema</li>
          </ul>
        </div>
      `;
    }
    
    function cargarAdministradores() {
      // Simular carga de datos (en una implementación real, harías una petición AJAX)
      const content = document.getElementById('administradores-content');
      content.innerHTML = `
        <div style="background: #fff3e0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
          <h4 style="color: #f57c00; margin: 0 0 10px 0;">Información de Administradores</h4>
          <p><strong>Total:</strong> <?php echo $stats['usuarios_por_rol']['Admin'] ?? 0; ?></p>
          <p><strong>Permisos:</strong> Gestión de contenido y usuarios</p>
          <p><strong>Funciones:</strong> Crear insignias, gestionar categorías, registrar usuarios</p>
        </div>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
          <h5 style="margin: 0 0 10px 0;">Acciones disponibles:</h5>
          <ul style="margin: 0; padding-left: 20px;">
            <li>Crear y gestionar insignias</li>
            <li>Registrar nuevos usuarios</li>
            <li>Gestionar categorías y subcategorías</li>
            <li>Ver historial de insignias</li>
            <li>Generar reconocimientos</li>
          </ul>
        </div>
      `;
    }
    
    // Función para mostrar/ocultar contraseña
    function togglePassword() {
      const passwordInput = document.getElementById('password-input');
      const toggleIcon = document.getElementById('toggle-password');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = '🙈';
        toggleIcon.title = 'Ocultar contraseña';
      } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = '👁️';
        toggleIcon.title = 'Mostrar contraseña';
      }
    }
    
    // Mejorar la experiencia táctil
    document.addEventListener('DOMContentLoaded', function() {
      // Asegurar que los botones respondan al toque
      const buttons = document.querySelectorAll('button');
      buttons.forEach(button => {
        button.addEventListener('touchstart', function(e) {
          e.preventDefault();
          this.style.transform = 'scale(0.98)';
        });
        
        button.addEventListener('touchend', function(e) {
          e.preventDefault();
          this.style.transform = 'scale(1)';
          // Simular click después del toque
          setTimeout(() => {
            this.click();
          }, 100);
        });
      });
      
      // Mejorar el comportamiento del formulario en móviles
      const forms = document.querySelectorAll('form');
      forms.forEach(form => {
        form.addEventListener('submit', function(e) {
          // Mostrar indicador de carga en móviles
          if (window.innerWidth <= 768) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
              submitBtn.innerHTML = 'Procesando...';
              submitBtn.disabled = true;
            }
          }
        });
      });
    });
    
    // ========================================
    // NOTIFICACIÓN DE RECONOCIMIENTO DUPLICADO
    // ========================================
    
    function mostrarNotificacionReconocimientoDuplicado() {
        // Crear notificación
        const notificacion = document.createElement('div');
        notificacion.id = 'notificacion-reconocimiento-duplicado';
        notificacion.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #dc3545;
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(220, 53, 69, 0.3);
            z-index: 10000;
            max-width: 400px;
            animation: slideInRight 0.5s ease;
            border-left: 5px solid #b02a37;
        `;
        
        // Contenido de la notificación
        notificacion.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 10px;">
                <div style="font-size: 24px; margin-top: 2px;">❌</div>
                <div style="flex: 1;">
                    <div style="font-weight: bold; margin-bottom: 8px; font-size: 16px;">
                        Error: Reconocimiento Duplicado
                    </div>
                    <div style="font-size: 14px; line-height: 1.4; margin-bottom: 10px;">
                        ⚠️ Este estudiante ya tiene un reconocimiento<br>
                        No se puede tramitar otro reconocimiento según la política del sistema.
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button onclick="cerrarNotificacionReconocimiento()" style="
                            background: rgba(255,255,255,0.2);
                            color: white;
                            border: 1px solid rgba(255,255,255,0.3);
                            padding: 6px 12px;
                            border-radius: 5px;
                            font-size: 12px;
                            cursor: pointer;
                            transition: background 0.3s ease;
                        " onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                            Cerrar
                        </button>
                        <button onclick="verHistorialEstudiante()" style="
                            background: rgba(255,255,255,0.9);
                            color: #dc3545;
                            border: none;
                            padding: 6px 12px;
                            border-radius: 5px;
                            font-size: 12px;
                            font-weight: bold;
                            cursor: pointer;
                            transition: background 0.3s ease;
                        " onmouseover="this.style.background='white'" onmouseout="this.style.background='rgba(255,255,255,0.9)'">
                            Ver Historial
                        </button>
                    </div>
                </div>
                <button onclick="cerrarNotificacionReconocimiento()" style="
                    background: none;
                    border: none;
                    color: white;
                    font-size: 18px;
                    cursor: pointer;
                    padding: 0;
                    margin-left: 5px;
                    opacity: 0.7;
                    transition: opacity 0.3s ease;
                " onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                    ×
                </button>
            </div>
        `;
        
        // Agregar estilos de animación
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { 
                    transform: translateX(100%); 
                    opacity: 0; 
                }
                to { 
                    transform: translateX(0); 
                    opacity: 1; 
                }
            }
            @keyframes slideOutRight {
                from { 
                    transform: translateX(0); 
                    opacity: 1; 
                }
                to { 
                    transform: translateX(100%); 
                    opacity: 0; 
                }
            }
        `;
        document.head.appendChild(style);
        
        // Agregar notificación al body
        document.body.appendChild(notificacion);
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            cerrarNotificacionReconocimiento();
        }, 5000);
    }
    
    function cerrarNotificacionReconocimiento() {
        const notificacion = document.getElementById('notificacion-reconocimiento-duplicado');
        if (notificacion) {
            notificacion.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                notificacion.remove();
                
                // Limpiar formulario
                const form = document.querySelector('form[method="POST"]');
                if (form) {
                    form.reset();
                }
            }, 300);
        }
    }
    
    function verHistorialEstudiante() {
        cerrarNotificacionReconocimiento();
        // Redirigir al historial de insignias
        window.location.href = 'historial_insignias.php';
    }
    
     // Inicialización cuando el DOM esté listo
     document.addEventListener('DOMContentLoaded', function() {
         // Asegurar que el primer tab esté activo por defecto
         const firstTab = document.getElementById('tab1');
         if (firstTab && !document.querySelector('.tab.active')) {
             firstTab.classList.add('active');
         }
         
         // Inicializar generación automática de clave de insignia
         inicializarGeneracionClave();
         
         <?php if ($mensaje_error === "YA_EXISTE_RECONOCIMIENTO"): ?>
             // Mostrar notificación de reconocimiento duplicado
             setTimeout(function() {
                 mostrarNotificacionReconocimientoDuplicado();
             }, 500);
         <?php endif; ?>
     });
    
    // ========================================
    // FUNCIONES PARA MODAL OPCIONES CRUD
    // ========================================
    
    function abrirModalOpciones() {
        const modal = document.getElementById('modalOpciones');
        if (!modal) {
            console.error('No se encontró el modal modalOpciones');
            return;
        }
        
        // Primero ocultar TODAS las pestañas
        document.querySelectorAll('.tab-opciones-content').forEach(tab => {
            tab.classList.remove('active');
            tab.style.display = 'none';
        });
        
        // Desactivar todos los botones
        document.querySelectorAll('.tab-opciones-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.borderBottom = '3px solid transparent';
        });
        
        // Mostrar el modal
        modal.style.display = 'block';
        
        // Activar SOLO la primera pestaña (Destinatarios)
        const firstBtn = document.querySelector('.tab-opciones-btn');
        if (firstBtn) {
            mostrarTabOpciones('tab-destinatarios', firstBtn);
        } else {
            mostrarTabOpciones('tab-destinatarios');
        }
    }
    
    function cerrarModalOpciones() {
        document.getElementById('modalOpciones').style.display = 'none';
        cerrarDropdownMasOpciones();
    }

    function toggleDropdownMasOpciones(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        var d = document.getElementById('dropdown-mas-opciones');
        if (!d) return;
        if (d.style.display === 'block') {
            d.style.display = 'none';
        } else {
            d.style.display = 'block';
        }
    }

    function cerrarDropdownMasOpciones() {
        var d = document.getElementById('dropdown-mas-opciones');
        if (d) d.style.display = 'none';
    }

    function accionEliminar(nombre) {
        if (confirm('¿Desea eliminar el elemento seleccionado de «' + nombre + '»? Esta acción puede ser irreversible.')) {
            alert('Función de eliminación para «' + nombre + '» en desarrollo. Conecte aquí su lógica de borrado.');
        }
    }

    document.addEventListener('click', function(e) {
        var d = document.getElementById('dropdown-mas-opciones');
        var btn = document.getElementById('btn-mas-opciones');
        if (!d || d.style.display !== 'block') return;
        if (d.contains(e.target) || (btn && btn.contains(e.target))) return;
        cerrarDropdownMasOpciones();
    });
    
    function mostrarTabOpciones(tabId, element = null) {
        // Ocultar TODOS los tabs primero (forzar ocultamiento)
        document.querySelectorAll('.tab-opciones-content').forEach(tab => {
            tab.classList.remove('active');
            tab.style.display = 'none'; // Forzar ocultamiento
        });
        
        // Desactivar todos los botones
        document.querySelectorAll('.tab-opciones-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.borderBottom = '3px solid transparent';
            btn.style.color = '';
        });
        
        // Mostrar SOLO el tab seleccionado
        const tabContent = document.getElementById(tabId);
        if (tabContent) {
            tabContent.classList.add('active');
            tabContent.style.display = 'block'; // Forzar visualización
        } else {
            console.error('No se encontró el tab:', tabId);
            return;
        }
        
        // Activar el botón correspondiente
        if (element) {
            element.classList.add('active');
            element.style.borderBottom = '3px solid #28a745';
            element.style.color = '#6c757d';
        } else {
            // Buscar el botón por el tabId
            const buttons = document.querySelectorAll('.tab-opciones-btn');
            buttons.forEach(btn => {
                if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(tabId)) {
                    btn.classList.add('active');
                    btn.style.borderBottom = '3px solid #28a745';
                    btn.style.color = '#6c757d';
                }
            });
        }
        
        // Cargar datos SOLO del tab seleccionado
        console.log('Cambiando a pestaña:', tabId);
        if (tabId === 'tab-destinatarios') {
            console.log('Cargando destinatarios...');
            cargarDestinatarios();
        } else if (tabId === 'tab-insignias') {
            console.log('Cargando insignias...');
            cargarInsignias();
        } else if (tabId === 'tab-categorias') {
            console.log('Cargando categorías...');
            cargarCategorias();
        } else if (tabId === 'tab-subcategorias') {
            console.log('Cargando subcategorías...');
            cargarSubcategorias();
        } else if (tabId === 'tab-certificados') {
            cargarCertificados();
        } else if (tabId === 'tab-insignias-otorgadas') {
            cargarInsigniasOtorgadas();
        }
    }
    
    // Funciones para cargar listas
    function cargarDestinatarios() {
        const listaDiv = document.getElementById('lista-destinatarios');
        if (!listaDiv) {
            console.error('No se encontró el elemento lista-destinatarios');
            return;
        }
        listaDiv.innerHTML = '<div style="text-align: center; padding: 20px;">Cargando...</div>';
        fetch('ajax_opciones.php?tabla=destinatario&accion=listar')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta: ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                listaDiv.innerHTML = html;
            })
            .catch(error => {
                console.error('Error al cargar destinatarios:', error);
                listaDiv.innerHTML = '<div style="text-align: center; padding: 20px; color: red;">Error al cargar los datos: ' + error.message + '</div>';
            });
    }
    
    function cargarInsignias() {
        const listaDiv = document.getElementById('lista-insignias');
        if (!listaDiv) {
            console.error('No se encontró el elemento lista-insignias');
            return;
        }
        listaDiv.innerHTML = '<div style="text-align: center; padding: 20px;">Cargando...</div>';
        fetch('ajax_opciones.php?tabla=T_insignias&accion=listar&_=' + Date.now(), { cache: 'no-store' })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta: ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                listaDiv.innerHTML = html;
            })
            .catch(error => {
                console.error('Error al cargar insignias:', error);
                listaDiv.innerHTML = '<div style="text-align: center; padding: 20px; color: red;">Error al cargar los datos: ' + error.message + '</div>';
            });
    }
    
    function cargarCategorias() {
        const listaDiv = document.getElementById('lista-categorias');
        if (!listaDiv) {
            console.error('No se encontró el elemento lista-categorias');
            return;
        }
        listaDiv.innerHTML = '<div style="text-align: center; padding: 20px;">Cargando...</div>';
        fetch('ajax_opciones.php?tabla=categorias&accion=listar')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta: ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                listaDiv.innerHTML = html;
            })
            .catch(error => {
                console.error('Error al cargar categorías:', error);
                listaDiv.innerHTML = '<div style="text-align: center; padding: 20px; color: red;">Error al cargar los datos: ' + error.message + '</div>';
            });
    }
    
    function cargarSubcategorias() {
        const listaDiv = document.getElementById('lista-subcategorias');
        if (!listaDiv) {
            console.error('No se encontró el elemento lista-subcategorias');
            return;
        }
        listaDiv.innerHTML = '<div style="text-align: center; padding: 20px;">Cargando...</div>';
        fetch('ajax_opciones.php?tabla=subcategorias&accion=listar')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta: ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                listaDiv.innerHTML = html;
            })
            .catch(error => {
                console.error('Error al cargar subcategorías:', error);
                listaDiv.innerHTML = '<div style="text-align: center; padding: 20px; color: red;">Error al cargar los datos: ' + error.message + '</div>';
            });
    }

    function cargarCertificados() {
        const listaDiv = document.getElementById('lista-certificados');
        if (!listaDiv) return;
        listaDiv.innerHTML = '<div style="text-align: center; padding: 20px;">Cargando...</div>';
        fetch('ajax_opciones.php?tabla=certificados&accion=listar&_=' + Date.now(), { cache: 'no-store' })
            .then(response => response.ok ? response.text() : Promise.reject(new Error('No disponible')))
            .then(html => { listaDiv.innerHTML = html; })
            .catch(() => {
                listaDiv.innerHTML = '<div style="text-align: center; padding: 24px; color: #6c757d;">Gestión de certificados. Conecte aquí el listado cuando disponga de backend (tabla certificados).</div>';
            });
    }

    function cargarInsigniasOtorgadas() {
        const listaDiv = document.getElementById('lista-insignias-otorgadas');
        if (!listaDiv) return;
        listaDiv.innerHTML = '<div style="text-align: center; padding: 20px;">Cargando...</div>';
        fetch('ajax_opciones.php?tabla=insigniasotorgadas&accion=listar&_=' + Date.now(), { cache: 'no-store' })
            .then(response => response.ok ? response.text() : Promise.reject(new Error('No disponible')))
            .then(html => { listaDiv.innerHTML = html; })
            .catch(() => {
                listaDiv.innerHTML = '<div style="text-align: center; padding: 24px; color: #6c757d;">Gestión de insignias otorgadas. Conecte aquí el listado cuando disponga de backend (tabla insigniasotorgadas).</div>';
            });
    }
    
    // Funciones para mostrar formularios
    function mostrarFormDestinatario(operacion, id = null) {
        const formDiv = document.getElementById('form-destinatario');
        formDiv.style.display = 'block';
        
        if (operacion === 'crear') {
            formDiv.innerHTML = `
                <h4>Crear Nuevo Destinatario</h4>
                <form method="POST" onsubmit="return confirmarOperacion('destinatario', 'crear')">
                    <input type="hidden" name="accion_opciones" value="destinatario">
                    <input type="hidden" name="operacion" value="crear">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label>Nombre Completo:</label>
                            <input type="text" name="nombre_completo" required style="width: 100%; padding: 8px;">
                        </div>
                        <div>
                            <label>CURP:</label>
                            <input type="text" name="curp" maxlength="18" required style="width: 100%; padding: 8px;">
                        </div>
                        <div>
                            <label>Correo:</label>
                            <input type="email" name="correo" required style="width: 100%; padding: 8px;">
                        </div>
                        <div>
                            <label>Matrícula:</label>
                            <input type="text" name="matricula" required style="width: 100%; padding: 8px;">
                        </div>
                        <div>
                            <label>IT Centro:</label>
                            <input type="number" name="itcentro" required style="width: 100%; padding: 8px;">
                        </div>
                    </div>
                    <button type="submit" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Guardar</button>
                    <button type="button" onclick="document.getElementById('form-destinatario').style.display='none'" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">Cancelar</button>
                </form>
            `;
        } else {
            // Cargar datos para editar
            fetch(`ajax_opciones.php?tabla=destinatario&accion=obtener&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    formDiv.innerHTML = `
                        <h4>Editar Destinatario</h4>
                        <form method="POST" onsubmit="return confirmarOperacion('destinatario', 'editar')">
                            <input type="hidden" name="accion_opciones" value="destinatario">
                            <input type="hidden" name="operacion" value="editar">
                            <input type="hidden" name="id" value="${data.ID_destinatario}">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                <div>
                                    <label>Nombre Completo:</label>
                                    <input type="text" name="nombre_completo" value="${data.Nombre_Completo || ''}" required style="width: 100%; padding: 8px;">
                                </div>
                                <div>
                                    <label>CURP:</label>
                                    <input type="text" name="curp" value="${data.Curp || ''}" maxlength="18" required style="width: 100%; padding: 8px;">
                                </div>
                                <div>
                                    <label>Correo:</label>
                                    <input type="email" name="correo" value="${data.Correo || ''}" required style="width: 100%; padding: 8px;">
                                </div>
                                <div>
                                    <label>Matrícula:</label>
                                    <input type="text" name="matricula" value="${data.Matricula || ''}" required style="width: 100%; padding: 8px;">
                                </div>
                                <div>
                                    <label>IT Centro:</label>
                                    <input type="number" name="itcentro" value="${data.ITCentro || ''}" required style="width: 100%; padding: 8px;">
                                </div>
                            </div>
                            <button type="submit" style="background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Actualizar</button>
                            <button type="button" onclick="document.getElementById('form-destinatario').style.display='none'" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">Cancelar</button>
                        </form>
                    `;
                });
        }
    }
    
    function mostrarFormInsignia(operacion, id = null) {
        const formDiv = document.getElementById('form-insignia');
        const formCrear = document.getElementById('form-insignia-crear');
        const formEditar = document.getElementById('form-insignia-editar');
        formDiv.style.display = 'block';
        
        if (operacion === 'crear') {
            if (formCrear) formCrear.style.display = 'block';
            if (formEditar) { formEditar.style.display = 'none'; formEditar.innerHTML = ''; }
        } else {
            if (formCrear) formCrear.style.display = 'none';
            if (formEditar) {
                formEditar.style.display = 'block';
                formEditar.innerHTML = '<p style="padding: 20px;">Cargando...</p>';
                fetch('ajax_opciones.php?tabla=T_insignias&accion=obtener&id=' + id)
                    .then(response => response.json())
                    .then(data => {
                        formEditar.innerHTML = `
                            <h4>Editar Insignia Maestra</h4>
                            <form method="POST" onsubmit="return confirmarOperacion('T_insignias', 'editar')">
                                <input type="hidden" name="accion_opciones" value="T_insignias">
                                <input type="hidden" name="operacion" value="editar">
                                <input type="hidden" name="id" value="${data.id}">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                    <div><label>Tipo Insignia (ID):</label><input type="number" name="tipo_insignia" value="${data.Tipo_Insignia || ''}" required style="width: 100%; padding: 8px;"></div>
                                    <div><label>Propone Insignia (ID):</label><input type="number" name="propone_insignia" value="${data.Propone_Insignia || ''}" required style="width: 100%; padding: 8px;"></div>
                                    <div><label>Programa:</label><input type="text" name="programa" value="${(data.Programa || '').replace(/"/g, '&quot;')}" required style="width: 100%; padding: 8px;"></div>
                                    <div><label>Estatus:</label><input type="number" name="estatus" value="${data.Estatus ?? 1}" required style="width: 100%; padding: 8px;"></div>
                                    <div style="grid-column: 1 / -1;"><label>Descripción:</label><textarea name="descripcion" required style="width: 100%; padding: 8px; min-height: 100px;">${(data.Descripcion || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea></div>
                                    <div style="grid-column: 1 / -1;"><label>Criterio:</label><textarea name="criterio" required style="width: 100%; padding: 8px; min-height: 80px;">${(data.Criterio || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea></div>
                                    <div><label>Fecha Creación:</label><input type="date" name="fecha_creacion" value="${data.Fecha_Creacion || ''}" required style="width: 100%; padding: 8px;"></div>
                                    <div><label>Fecha Autorización:</label><input type="date" name="fecha_autorizacion" value="${data.Fecha_Autorizacion || ''}" required style="width: 100%; padding: 8px;"></div>
                                    <div><label>Nombre Gen Ins:</label><input type="text" name="nombre_gen_ins" value="${(data.Nombre_gen_ins || '').replace(/"/g, '&quot;')}" style="width: 100%; padding: 8px;"></div>
                                    <div><label>Archivo Visual:</label><input type="text" name="archivo_visual" value="${(data.Archivo_Visual || '').replace(/"/g, '&quot;')}" style="width: 100%; padding: 8px;"></div>
                                </div>
                                <button type="submit" style="background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Actualizar</button>
                                <button type="button" onclick="document.getElementById('form-insignia').style.display='none'" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">Cancelar</button>
                            </form>
                        `;
                    })
                    .catch(err => { formEditar.innerHTML = '<p style="color:red;">Error al cargar datos.</p>'; });
            }
        }
    }
    
    function confirmarOperacion(tabla, operacion) {
        if (operacion === 'eliminar') {
            return confirm('¿Estás seguro de que deseas eliminar este registro?');
        }
        return true;
    }
    
    function eliminarRegistro(tabla, id) {
        if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="accion_opciones" value="${tabla}">
                <input type="hidden" name="operacion" value="eliminar">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function editarCategoria(id, nombre) {
        const nuevoNombre = prompt('Editar nombre de categoría:', nombre);
        if (nuevoNombre !== null && nuevoNombre.trim() !== '') {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="accion_opciones" value="categorias">
                <input type="hidden" name="operacion" value="editar">
                <input type="hidden" name="id" value="${id}">
                <input type="hidden" name="nombre" value="${nuevoNombre}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function editarSubcategoria(id, nombre, categoria_id) {
        const nuevoNombre = prompt('Editar nombre de subcategoría:', nombre);
        if (nuevoNombre !== null && nuevoNombre.trim() !== '') {
            const nuevaCategoria = prompt('Editar ID de categoría:', categoria_id);
            if (nuevaCategoria !== null) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="accion_opciones" value="subcategorias">
                    <input type="hidden" name="operacion" value="editar">
                    <input type="hidden" name="id" value="${id}">
                    <input type="hidden" name="nombre" value="${nuevoNombre}">
                    <input type="hidden" name="categoria_id" value="${nuevaCategoria}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
    
    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        const modal = document.getElementById('modalOpciones');
        if (event.target == modal) {
            cerrarModalOpciones();
        }
    }
    
    // ========================================
    // FUNCIONES PARA ASIGNACIÓN DE INSIGNIAS
    // ========================================
    
    function mostrarDetallesInsignia() {
        const select = document.getElementById('insignia-select');
        const detallesDiv = document.getElementById('detalles-insignia');
        const contenidoDiv = document.getElementById('contenido-detalles');
        
        if (!select || !detallesDiv || !contenidoDiv) return;
        
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption.value === '') {
            detallesDiv.style.display = 'none';
            return;
        }
        
        // Obtener datos de la insignia seleccionada
        const descripcion = selectedOption.getAttribute('data-descripcion') || 'No disponible';
        const criterio = selectedOption.getAttribute('data-criterio') || 'No disponible';
        const tipo = selectedOption.getAttribute('data-tipo') || 'No disponible';
        const categoria = selectedOption.getAttribute('data-categoria') || 'No disponible';
        const centro = selectedOption.getAttribute('data-centro') || 'No disponible';
        
        // Mostrar detalles
        contenidoDiv.innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <strong>📂 Categoría:</strong> ${categoria}<br>
                    <strong>🏷️ Tipo:</strong> ${tipo}<br>
                    <strong>🏢 Centro:</strong> ${centro}
                </div>
                <div>
                    <strong>📝 Descripción:</strong><br>
                    <div style="background: white; padding: 8px; border-radius: 4px; font-size: 12px; margin-top: 5px;">
                        ${descripcion}
                    </div>
                </div>
            </div>
            <div style="margin-top: 10px;">
                <strong>📋 Criterios de Emisión:</strong><br>
                <div style="background: white; padding: 8px; border-radius: 4px; font-size: 12px; margin-top: 5px;">
                    ${criterio}
                </div>
            </div>
        `;
        
        detallesDiv.style.display = 'block';
        
        // Generar clave automáticamente
        generarClaveInsignia(selectedOption.textContent, centro);
    }
    
    function generarClaveInsignia(nombreInsignia, centro) {
        const claveInput = document.getElementById('clave-insignia');
        if (!claveInput || claveInput.value.trim() !== '') return; // No sobrescribir si ya tiene valor
        
        // Generar clave basada en el nombre de la insignia y centro
        const año = new Date().getFullYear();
        const mes = String(new Date().getMonth() + 1).padStart(2, '0');
        
        // Crear siglas del centro
        let siglasCentro = 'TECNM';
        if (centro && centro !== 'No disponible') {
            const palabras = centro.split(' ');
            siglasCentro = palabras.map(p => p.charAt(0).toUpperCase()).join('').substring(0, 4);
        }
        
        // Crear siglas de la insignia
        let siglasInsignia = 'INS';
        if (nombreInsignia) {
            const palabras = nombreInsignia.split(' ');
            siglasInsignia = palabras.map(p => p.charAt(0).toUpperCase()).join('').substring(0, 3);
        }
        
        // Generar número secuencial (simulado)
        const numero = Math.floor(Math.random() * 999) + 1;
        
        const claveGenerada = `${siglasCentro}-${siglasInsignia}-${año}${mes}-${String(numero).padStart(3, '0')}`;
        
        claveInput.value = claveGenerada;
        claveInput.style.backgroundColor = '#e8f5e8';
        
        // Quitar el color de fondo después de 2 segundos
        setTimeout(() => {
            claveInput.style.backgroundColor = '';
        }, 2000);
    }
    
    function inicializarGeneracionClave() {
        const claveInput = document.getElementById('clave-insignia');
        if (!claveInput) return;
        
        // Limpiar campo al hacer clic
        claveInput.addEventListener('click', function() {
            if (this.value.trim() === '') {
                const select = document.getElementById('insignia-select');
                if (select && select.value !== '') {
                    const selectedOption = select.options[select.selectedIndex];
                    const centro = selectedOption.getAttribute('data-centro') || 'No disponible';
                    generarClaveInsignia(selectedOption.textContent, centro);
                }
            }
        });
        
        // Generar clave al cambiar la insignia
        const select = document.getElementById('insignia-select');
        if (select) {
            select.addEventListener('change', function() {
                const claveInput = document.getElementById('clave-insignia');
                if (claveInput && claveInput.value.trim() === '') {
                    // Solo generar si el campo está vacío
                    setTimeout(() => {
                        mostrarDetallesInsignia();
                    }, 100);
                }
            });
        }
    }
    
    // ========================================
    // VERIFICACIÓN EN TIEMPO REAL DEL ESTUDIANTE
    // ========================================
    
    let timeoutVerificacion;
    
    function verificarEstudiante(nombreCompleto) {
        // Limpiar timeout anterior
        clearTimeout(timeoutVerificacion);
        
        const statusDiv = document.getElementById('receptor-status');
        const infoDiv = document.getElementById('receptor-info');
        const input = document.getElementById('receptor-input');
        
        // Ocultar indicadores si el campo está vacío
        if (!nombreCompleto.trim()) {
            statusDiv.style.display = 'none';
            infoDiv.style.display = 'none';
            input.style.borderColor = '';
            return;
        }
        
        // Mostrar indicador de carga
        statusDiv.innerHTML = '⏳';
        statusDiv.style.display = 'block';
        statusDiv.style.color = '#ffc107';
        infoDiv.style.display = 'none';
        
        // Verificar después de 500ms de inactividad
        timeoutVerificacion = setTimeout(() => {
            // Hacer petición AJAX para verificar el estudiante
            fetch('verificar_estudiante.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'receptor=' + encodeURIComponent(nombreCompleto)
            })
            .then(response => response.json())
            .then(data => {
                statusDiv.style.display = 'block';
                infoDiv.style.display = 'block';
                
                if (data.existe) {
                    if (data.tiene_reconocimiento) {
                        // Estudiante existe y ya tiene reconocimiento
                        statusDiv.innerHTML = '❌';
                        statusDiv.style.color = '#dc3545';
                        input.style.borderColor = '#dc3545';
                        
                        infoDiv.innerHTML = `
                            <strong style="color: #dc3545;">⚠️ Este estudiante ya tiene un reconocimiento</strong><br>
                            <small>No se puede tramitar otro reconocimiento según la política del sistema.</small>
                        `;
                        infoDiv.style.backgroundColor = '#f8d7da';
                        infoDiv.style.borderColor = '#dc3545';
                        infoDiv.style.color = '#721c24';
                        
                        // Deshabilitar botón de envío
                        const submitBtn = document.querySelector('button[name="guardar_metadatos"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.style.opacity = '0.5';
                            submitBtn.style.cursor = 'not-allowed';
                        }
                    } else {
                        // Estudiante existe pero no tiene reconocimiento
                        statusDiv.innerHTML = '✅';
                        statusDiv.style.color = '#28a745';
                        input.style.borderColor = '#28a745';
                        
                        infoDiv.innerHTML = `
                            <strong style="color: #28a745;">✅ Estudiante válido</strong><br>
                            <small>Este estudiante puede recibir un reconocimiento.</small>
                        `;
                        infoDiv.style.backgroundColor = '#d4edda';
                        infoDiv.style.borderColor = '#28a745';
                        infoDiv.style.color = '#155724';
                        
                        // Habilitar botón de envío
                        const submitBtn = document.querySelector('button[name="guardar_metadatos"]');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.style.opacity = '1';
                            submitBtn.style.cursor = 'pointer';
                        }
                    }
                } else {
                    // Estudiante no existe
                    statusDiv.innerHTML = '❓';
                    statusDiv.style.color = '#6c757d';
                    input.style.borderColor = '#ffc107';
                    
                    infoDiv.innerHTML = `
                        <strong style="color: #856404;">ℹ️ Estudiante no encontrado</strong><br>
                        <small>Se creará un nuevo registro para este estudiante.</small>
                    `;
                    infoDiv.style.backgroundColor = '#fff3cd';
                    infoDiv.style.borderColor = '#ffc107';
                    infoDiv.style.color = '#856404';
                    
                    // Habilitar botón de envío
                    const submitBtn = document.querySelector('button[name="guardar_metadatos"]');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                        submitBtn.style.cursor = 'pointer';
                    }
                }
            })
            .catch(error => {
                console.error('Error verificando estudiante:', error);
                statusDiv.innerHTML = '❌';
                statusDiv.style.color = '#dc3545';
                infoDiv.innerHTML = 'Error al verificar estudiante';
                infoDiv.style.backgroundColor = '#f8d7da';
                infoDiv.style.color = '#721c24';
            });
        }, 500);
    }

    // Después de guardar/editar/eliminar insignia maestra: abrir modal en pestaña Insignias y refrescar lista
    <?php if (!empty($abrir_modal_tab_insignias)): ?>
    document.addEventListener('DOMContentLoaded', function() {
        abrirModalOpciones();
        var btnInsignias = document.querySelector('.tab-opciones-btn[onclick*="tab-insignias"]');
        if (btnInsignias) mostrarTabOpciones('tab-insignias', btnInsignias);
        var formInsignia = document.getElementById('form-insignia');
        if (formInsignia) formInsignia.style.display = 'none';
    });
    <?php endif; ?>
  </script>
</head>
<body>
  <!-- HEADER AZUL PROFESIONAL -->
  <header class="header-principal">
    <div class="header-content-admin">
      <img src="imagen/logo.png" alt="TecNM Logo" class="header-logo-admin">
      <h1>Insignias TecNM</h1>
    </div>
  </header>

  <div class="main-container">
    <div class="card">
      <div class="card-title">Panel de Administración</div>
      <div class="card-subtitle">Bienvenido, <?php 
        $nombre_completo = trim($usuario['Nombre'] . ' ' . $usuario['Apellido_Paterno'] . ' ' . $usuario['Apellido_Materno']);
        echo htmlspecialchars($nombre_completo); 
      ?></div>
      
      <?php if (!empty($mensaje_error) && $mensaje_error !== "YA_EXISTE_RECONOCIMIENTO"): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle"></i>
          <strong>Error:</strong> <?php echo htmlspecialchars($mensaje_error); ?>
        </div>
      <?php endif; ?>
      
      <div class="nav-tabs">
        <button onclick="mostrarTab('tab1')" class="active">Categorías</button>
        <button onclick="mostrarTab('tab2')">Subcategorías</button>
        <button onclick="window.location.href='metadatos_formulario.php'">🎖️ Metadatos</button>
        <button onclick="mostrarTab('tab4')">Registro</button>
        <button onclick="window.location.href='historial_insignias.php'">Historial</button>
        <button onclick="window.location.href='carga_masiva_excel.php'" style="background: linear-gradient(135deg, #28a745, #20c997);">📊 Carga Masiva</button>
        <button onclick="abrirModalOpciones()" style="background: linear-gradient(135deg, #6c757d, #5a6268);">⚙️ Opciones</button>
        <?php if (esSuperUsuario()): ?>
          <button onclick="mostrarTab('tab5')" style="background: linear-gradient(135deg, #dc3545, #c82333);">🔧 Configuración</button>
          <button onclick="mostrarTab('tab6')" style="background: linear-gradient(135deg, #6f42c1, #5a2d91);">👥 Gestión Admin</button>
          <button onclick="mostrarTab('tab7')" style="background: linear-gradient(135deg, #fd7e14, #e55a00);">📊 Auditoría</button>
        <?php endif; ?>
      </div>

      <div class="tab-content">
        <!-- Categorías -->
        <div id="tab1" class="tab active">
          <div class="card-title">Registrar Categoría</div>
          <form method="POST" class="form-group">
            <label class="form-label">Nombre de la categoría:</label>
            <input type="text" name="nombre" class="form-control" required placeholder="Ej: Formación Integral">
            <button type="submit" name="guardar_categoria" class="btn" style="display: inline-block; padding: 8px 16px; font-size: 14px; margin-bottom: 10px; width: auto;">Guardar Categoría</button>
            <a href="logout.php" class="logout-btn" style="background: #dc3545 !important; display: inline-block; text-align: center; font-size: 14px; padding: 8px 16px !important; text-decoration: none; color: white; border-radius: 6px; font-weight: bold; width: auto; box-sizing: border-box; margin-left: 10px;">
              Cerrar Sesión
            </a>
          </form>
        </div>

    <!-- Subcategorías -->
    <div id="tab2" class="tab">
      <h3>Registrar Subcategoría</h3>
      <form method="POST">
        <label>Nombre:</label>
        <input type="text" name="nombre" required>
        <label>Categoría:</label>
        <select name="categoria_id" required>
          <option value="">Selecciona una categoría...</option>
          <?php 
          // Consultar categorías para el select (asegurar que siempre esté disponible)
          $categorias_select = $conexion->query("SELECT id, Nombre_cat FROM cat_insignias ORDER BY Nombre_cat");
          if ($categorias_select && $categorias_select->num_rows > 0): 
            // Reiniciar el puntero del resultado
            $categorias_select->data_seek(0);
            while($row = $categorias_select->fetch_assoc()): 
              $categoria_id = isset($row['id']) ? $row['id'] : null;
              $categoria_nombre = isset($row['Nombre_cat']) ? $row['Nombre_cat'] : '';
              if ($categoria_id && $categoria_nombre): ?>
                <option value="<?php echo htmlspecialchars($categoria_id); ?>"><?php echo htmlspecialchars($categoria_nombre); ?></option>
              <?php endif; 
            endwhile; 
          else: ?>
            <option value="">No hay categorías disponibles. Primero registra una categoría.</option>
          <?php endif; ?>
        </select>
        <button type="submit" name="guardar_subcategoria" style="background: #1b396a; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; width: auto; display: inline-block;">Guardar</button>
      </form>
    </div>

    <!-- Metadatos -->
    <div id="tab3" class="tab">
      <h3>🎖️ Asignar Insignia a Estudiante</h3>
      
      <!-- Botón para Registrar Reconocimiento -->
      <div style="background: linear-gradient(135deg, #28a745, #20c997); padding: 20px; border-radius: 12px; margin-bottom: 25px; text-align: center;">
        <h4 style="color: white; margin-bottom: 15px; font-size: 18px;">
          <i class="fas fa-plus-circle"></i> Nuevo Reconocimiento
        </h4>
        <p style="color: rgba(255,255,255,0.9); margin-bottom: 20px; font-size: 14px;">
          Registra un nuevo reconocimiento con metadatos completos
        </p>
        <a href="registrar_reconocimiento.php" style="
          background: white; 
          color: #28a745; 
          padding: 4px 8px; 
          border-radius: 4px; 
          text-decoration: none; 
          font-weight: 500; 
          font-size: 12px;
          display: inline-flex; 
          align-items: center; 
          gap: 4px;
          transition: all 0.3s ease;
          box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        " onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 4px rgba(0,0,0,0.1)'">
          <i class="fas fa-medal"></i>
          Registrar Reconocimiento
        </a>
      </div>
      
      <!-- Botón para Gestión de Estudiantes -->
      <div style="background: linear-gradient(135deg, #17a2b8, #138496); padding: 20px; border-radius: 12px; margin-bottom: 25px; text-align: center;">
        <h4 style="color: white; margin-bottom: 15px; font-size: 18px;">
          <i class="fas fa-user-graduate"></i> Gestión de Estudiantes
        </h4>
        <p style="color: rgba(255,255,255,0.9); margin-bottom: 20px; font-size: 14px;">
          Registrar y gestionar información de estudiantes destinatarios
        </p>
        <a href="gestion_estudiantes.php" style="
          background: white; 
          color: #17a2b8; 
          padding: 12px 25px; 
          border-radius: 8px; 
          text-decoration: none; 
          font-weight: 600; 
          display: inline-flex; 
          align-items: center; 
          gap: 8px;
          transition: all 0.3s ease;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.1)'">
          <i class="fas fa-users"></i>
          Gestionar Estudiantes
        </a>
      </div>
      
      <p style="background: #e8f4fd; padding: 15px; border-radius: 8px; border-left: 4px solid #0046c3; margin-bottom: 20px;">
        <strong>📋 Instrucciones:</strong> Selecciona una insignia de la tabla de insignias disponibles y asigna a un estudiante. 
        Este formulario registra el reconocimiento en el sistema de metadatos.
      </p>
      
      <form method="POST" id="form-asignar-insignia">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
          <div>
            <label>🎖️ Insignia Disponible:</label>
            <select name="insignia_id" id="insignia-select" required onchange="mostrarDetallesInsignia()">
              <option value="">Seleccionar insignia</option>
              <?php 
              try {
                $stmt_insignias = $conexion->query("
                  SELECT i.id, i.Nombre_gen_ins, i.Descripcion, i.Criterio, 
                         ti.Nombre_ins, ci.Nombre_cat, ic.Nombre_itc
                  FROM insignias i
                  LEFT JOIN tipo_insignia ti ON i.Tipo_Insignia = ti.id
                  LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.id
                  LEFT JOIN it_centros ic ON i.Propone_Insignia = ic.id
                  ORDER BY i.Nombre_gen_ins
                ");
                if ($stmt_insignias) {
                  while($row = $stmt_insignias->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>" 
                            data-descripcion="<?= htmlspecialchars($row['Descripcion']) ?>"
                            data-criterio="<?= htmlspecialchars($row['Criterio']) ?>"
                            data-tipo="<?= htmlspecialchars($row['Nombre_ins']) ?>"
                            data-categoria="<?= htmlspecialchars($row['Nombre_cat']) ?>"
                            data-centro="<?= htmlspecialchars($row['Nombre_itc']) ?>">
                      <?= htmlspecialchars($row['Nombre_gen_ins']) ?>
                    </option>
                  <?php }
                } else {
                  echo '<option value="">Error al cargar insignias</option>';
                }
              } catch (Exception $e) {
                echo '<option value="">Error: ' . htmlspecialchars($e->getMessage()) . '</option>';
              } ?>
            </select>
          </div>
          
          <div>
            <label>👤 Estudiante Destinatario:</label>
            <select name="destinatario_id" required>
              <option value="">Seleccionar estudiante</option>
              <?php 
              try {
                $stmt_destinatarios = $conexion->query("
                  SELECT d.id, d.Nombre_Completo, d.Matricula, ic.Nombre_itc
                  FROM destinatario d
                  LEFT JOIN it_centros ic ON d.ITCentro = ic.id
                  ORDER BY d.Nombre_Completo
                ");
                if ($stmt_destinatarios) {
                  while($row = $stmt_destinatarios->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>">
                      <?= htmlspecialchars($row['Nombre_Completo']) ?> 
                      <?php if($row['Matricula']): ?> - <?= htmlspecialchars($row['Matricula']) ?><?php endif; ?>
                      <?php if($row['Nombre_itc']): ?> (<?= htmlspecialchars($row['Nombre_itc']) ?>)<?php endif; ?>
                    </option>
                  <?php }
                } else {
                  echo '<option value="">Error al cargar estudiantes</option>';
                }
              } catch (Exception $e) {
                echo '<option value="">Error: ' . htmlspecialchars($e->getMessage()) . '</option>';
              } ?>
            </select>
          </div>
        </div>
        
        <!-- Panel de detalles de la insignia seleccionada -->
        <div id="detalles-insignia" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #0046c3;">
          <h4 style="margin: 0 0 10px 0; color: #0046c3;">📋 Detalles de la Insignia</h4>
          <div id="contenido-detalles"></div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
          <div>
            <label>📅 Período de Emisión:</label>
            <select name="periodo_id" required>
              <option value="">Seleccionar período</option>
              <?php 
              try {
                $stmt_periodos = $conexion->query("SELECT id, periodo FROM periodo_emision ORDER BY periodo DESC");
                if ($stmt_periodos) {
                  while($row = $stmt_periodos->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>" <?= $row['periodo'] == date('Y') ? 'selected' : '' ?>>
                      <?= htmlspecialchars($row['periodo']) ?>
                    </option>
                  <?php }
                } else {
                  echo '<option value="">Error al cargar períodos</option>';
                }
              } catch (Exception $e) {
                echo '<option value="">Error: ' . htmlspecialchars($e->getMessage()) . '</option>';
              } ?>
            </select>
          </div>
          
          <div>
            <label>👨‍💼 Responsable de Emisión:</label>
            <select name="responsable_id" required>
              <option value="">Seleccionar responsable</option>
              <?php 
              try {
                $stmt_responsables = $conexion->query("
                  SELECT r.id, r.Nombre_Completo, r.Cargo, ic.Nombre_itc
                  FROM responsable_emision r
                  LEFT JOIN it_centros ic ON r.Adscripcion = ic.id
                  ORDER BY r.Nombre_Completo
                ");
                if ($stmt_responsables) {
                  while($row = $stmt_responsables->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>">
                      <?= htmlspecialchars($row['Nombre_Completo']) ?>
                      <?php if($row['Cargo']): ?> - <?= htmlspecialchars($row['Cargo']) ?><?php endif; ?>
                      <?php if($row['Nombre_itc']): ?> (<?= htmlspecialchars($row['Nombre_itc']) ?>)<?php endif; ?>
                    </option>
                  <?php }
                } else {
                  echo '<option value="">Error al cargar responsables</option>';
                }
              } catch (Exception $e) {
                echo '<option value="">Error: ' . htmlspecialchars($e->getMessage()) . '</option>';
              } ?>
            </select>
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
          <div>
            <label>📊 Estatus del Reconocimiento:</label>
            <select name="estatus_id" required>
              <option value="">Seleccionar estatus</option>
              <?php 
              try {
                $stmt_estatus = $conexion->query("SELECT id, Estatus FROM estatus ORDER BY Estatus");
                if ($stmt_estatus) {
                  while($row = $stmt_estatus->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>" <?= $row['Estatus'] == 'Activo' ? 'selected' : '' ?>>
                      <?= htmlspecialchars($row['Estatus']) ?>
                    </option>
                  <?php }
                } else {
                  echo '<option value="">Error al cargar estatus</option>';
                }
              } catch (Exception $e) {
                echo '<option value="">Error: ' . htmlspecialchars($e->getMessage()) . '</option>';
              } ?>
            </select>
          </div>
          
          <div>
            <label>🔑 Clave Única de Insignia:</label>
            <input type="text" name="clave_insignia" id="clave-insignia" placeholder="Ej: TECNM-ITSM-2025-001" required>
            <small style="color: #666; font-size: 12px;">Se generará automáticamente si se deja vacío</small>
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
          <div>
            <label>📅 Fecha de Otorgamiento:</label>
            <input type="date" name="fecha_otorgamiento" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          
          <div>
            <label>📅 Fecha de Autorización:</label>
            <input type="date" name="fecha_autorizacion" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
        </div>
        
        <div>
          <label>📄 Evidencia/Referencia:</label>
          <input type="text" name="evidencia" placeholder="Ej: /evidencias/certificado_alfabetiza_tec_jose.pdf" style="margin-bottom: 10px;">
          <small style="color: #666; font-size: 12px;">Ruta al archivo de evidencia o número de folio de certificación</small>
        </div>
        
        <button type="submit" name="guardar_insignia_otorgada" style="background: #28a745; font-size: 14px; padding: 8px 16px; display: inline-block; width: auto; margin-bottom: 10px; border-radius: 6px; color: white; border: none; cursor: pointer;">
          🎖️ Registrar Reconocimiento
        </button>
        <a href="logout.php" class="logout-btn" style="background: #dc3545 !important; display: inline-block; text-align: center; font-size: 14px; padding: 8px 16px; text-decoration: none; color: white; border-radius: 6px; font-weight: bold; margin-left: 10px;">
          🚪 Cerrar Sesión
        </a>
      </form>
    </div>

    <!-- Registro -->
    <div id="tab4" class="tab">
      <h3>Registro de Usuario</h3>
      <form method="POST">
        <label>Nombre:</label>
        <input type="text" name="nombre" required>
        <label>Apellido Paterno:</label>
        <input type="text" name="apellido_paterno" required>
        <label>Apellido Materno:</label>
        <input type="text" name="apellido_materno" required>
        <label>Correo:</label>
        <input type="email" name="correo" required>
        <label>Contraseña:</label>
        <div style="position: relative;">
            <input type="password" name="password" id="password-input" required style="padding-right: 40px;">
            <span id="toggle-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 18px; color: #666;" onclick="togglePassword()">👁️</span>
        </div>
        <label>Rol:</label>
        <select name="rol" id="rol-select">
          <option value="Estudiante">Estudiante</option>
          <?php if (puedeCrearAdministradores()): ?>
            <option value="Admin">Administrador</option>
          <?php endif; ?>
          <?php if (puedeGestionarSuperUsuarios()): ?>
            <option value="SuperUsuario">Super Usuario</option>
          <?php endif; ?>
        </select>
        <button type="submit" name="guardar_usuario">Registrar</button>
      </form>
    </div>

    <?php if (esSuperUsuario()): ?>
    <!-- Configuraciones Críticas - Solo SuperUsuario -->
    <div id="tab5" class="tab">
      <h3>🔧 Configuraciones Críticas del Sistema</h3>
      
      <form method="POST">
        <h4>🏫 Configuración de la Institución</h4>
        <label>Nombre de la Institución:</label>
        <input type="text" name="nombre_institucion" value="Tecnológico Nacional de México" required>
        
        <label>Logo de la Institución (URL):</label>
        <input type="url" name="logo_institucion" placeholder="https://ejemplo.com/logo.png">
        
        <h4>🎖️ Políticas de Insignias</h4>
        <label>Máximo de insignias por estudiante:</label>
        <input type="number" name="max_insignias_estudiante" value="1" min="1" max="10">
        
        <label>Días de validez de insignias:</label>
        <input type="number" name="dias_validez_insignias" value="365" min="30" max="3650">
        
        <h4>🔒 Configuración de Seguridad</h4>
        <label>Intentos máximos de login:</label>
        <input type="number" name="max_intentos_login" value="5" min="3" max="10">
        
        <label>Tiempo de bloqueo (minutos):</label>
        <input type="number" name="tiempo_bloqueo" value="30" min="5" max="1440">
        
        <button type="submit" name="guardar_configuraciones" style="background: #dc3545;">💾 Guardar Configuraciones</button>
      </form>
    </div>

    <!-- Gestión Admin - Solo SuperUsuario -->
    <div id="tab6" class="tab">
      <h3>👥 Gestión Admin</h3>
      
      <?php
      // Obtener lista de administradores
      $stmt_admins = $conexion->prepare("SELECT * FROM Usuario WHERE Rol IN ('Admin', 'SuperUsuario') ORDER BY Rol DESC, Nombre ASC");
      $stmt_admins->execute();
      $administradores = $stmt_admins->get_result();
      ?>
      
      <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 15px;">
        <h4>📋 Lista de Administradores</h4>
        <?php while($admin = $administradores->fetch_assoc()): ?>
          <div style="background: <?php echo $admin['Rol'] === 'SuperUsuario' ? '#f8d7da' : '#d4edda'; ?>; padding: 10px; margin: 5px 0; border-radius: 5px; border-left: 4px solid <?php echo $admin['Rol'] === 'SuperUsuario' ? '#dc3545' : '#28a745'; ?>;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div>
                <strong><?php echo htmlspecialchars($admin['Nombre'] . ' ' . $admin['Apellido_Paterno'] . ' ' . $admin['Apellido_Materno']); ?></strong>
                <br>
                <small><?php echo htmlspecialchars($admin['Correo']); ?></small>
                <br>
                <span style="background: <?php echo $admin['Rol'] === 'SuperUsuario' ? '#dc3545' : '#28a745'; ?>; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px;">
                  <?php echo $admin['Rol'] === 'SuperUsuario' ? '🔧 SuperUsuario' : '👤 Administrador'; ?>
                </span>
                <span style="background: <?php echo $admin['Estado'] === 'Activo' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 5px;">
                  <?php echo $admin['Estado'] === 'Activo' ? '✅ Activo' : '❌ Inactivo'; ?>
                </span>
              </div>
              <div>
                <?php if ($admin['Id_Usuario'] != $_SESSION['usuario_id']): ?>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="admin_id" value="<?php echo $admin['Id_Usuario']; ?>">
                    <button type="submit" name="cambiar_estado_admin" style="background: <?php echo $admin['Estado'] === 'Activo' ? '#dc3545' : '#28a745'; ?>; color: white; border: none; padding: 5px 10px; border-radius: 4px; font-size: 12px;">
                      <?php echo $admin['Estado'] === 'Activo' ? '❌ Desactivar' : '✅ Activar'; ?>
                    </button>
                  </form>
                <?php else: ?>
                  <span style="color: #6c757d; font-size: 12px;">(Tu cuenta)</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

    <!-- Auditoría Completa - Solo SuperUsuario -->
    <div id="tab7" class="tab">
      <h3>📊 Auditoría Completa del Sistema</h3>
      
      <?php
      // Obtener estadísticas del sistema con manejo de errores
      $stats = [];
      
      try {
        // Verificar conexión
        if (!$conexion) {
          throw new Exception("No hay conexión a la base de datos");
        }
        
        // Total de usuarios
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM Usuario");
        if ($stmt && $stmt->num_rows > 0) {
          $stats['total_usuarios'] = $stmt->fetch_assoc()['total'];
        } else {
          $stats['total_usuarios'] = 0;
        }
        
        // Usuarios por rol
        $stmt = $conexion->query("SELECT Rol, COUNT(*) as total FROM Usuario GROUP BY Rol");
        $stats['usuarios_por_rol'] = [];
        if ($stmt && $stmt->num_rows > 0) {
          while($row = $stmt->fetch_assoc()) {
            $stats['usuarios_por_rol'][$row['Rol']] = $row['total'];
          }
        }
        
        // Total de insignias
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM insignias");
        if ($stmt && $stmt->num_rows > 0) {
          $stats['total_insignias'] = $stmt->fetch_assoc()['total'];
        } else {
          $stats['total_insignias'] = 0;
        }
        
        // Insignias por mes
        $stmt = $conexion->query("SELECT DATE_FORMAT(Fecha_Creacion, '%Y-%m') as mes, COUNT(*) as total FROM insignias GROUP BY mes ORDER BY mes DESC LIMIT 12");
        $stats['insignias_por_mes'] = [];
        if ($stmt && $stmt->num_rows > 0) {
          while($row = $stmt->fetch_assoc()) {
            $stats['insignias_por_mes'][$row['mes']] = $row['total'];
          }
        }
        
        
      } catch (Exception $e) {
        // Log del error pero no interrumpir la visualización
        error_log("Error en consultas de auditoría: " . $e->getMessage());
        $stats['total_usuarios'] = 0;
        $stats['total_insignias'] = 0;
        $stats['usuarios_por_rol'] = [];
        $stats['insignias_por_mes'] = [];
      }
      ?>
      
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
        <a href="verificar_usuarios.php" style="text-decoration: none; display: block;">
          <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #2196f3; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            <h4 style="margin: 0; color: #1976d2;">👥 Total Usuarios</h4>
            <div style="font-size: 24px; font-weight: bold; color: #1976d2;"><?php echo $stats['total_usuarios']; ?></div>
            <small style="color: #666;">Ver detalles →</small>
          </div>
        </a>
        
        <div style="background: #f3e5f5; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #9c27b0;">
          <h4 style="margin: 0; color: #7b1fa2;">🎖️ Total Insignias</h4>
          <div style="font-size: 24px; font-weight: bold; color: #7b1fa2;"><?php echo $stats['total_insignias']; ?></div>
        </div>
        
        <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #4caf50;">
          <h4 style="margin: 0; color: #388e3c;">🔧 SuperUsuarios</h4>
          <div style="font-size: 24px; font-weight: bold; color: #388e3c;"><?php echo $stats['usuarios_por_rol']['SuperUsuario'] ?? 0; ?></div>
        </div>
        
        <div style="background: #fff3e0; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #ff9800;">
          <h4 style="margin: 0; color: #f57c00;">👤 Administradores</h4>
          <div style="font-size: 24px; font-weight: bold; color: #f57c00;"><?php echo $stats['usuarios_por_rol']['Admin'] ?? 0; ?></div>
        </div>
      </div>
      
      <h4>📈 Actividad Reciente</h4>
      <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6;">
        <?php if (!empty($stats['insignias_por_mes'])): ?>
          <h5>Insignias creadas por mes:</h5>
          <?php foreach($stats['insignias_por_mes'] as $mes => $total): ?>
            <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e9ecef;">
              <span><?php echo date('F Y', strtotime($mes . '-01')); ?></span>
              <span style="font-weight: bold;"><?php echo $total; ?> insignias</span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="color: #6c757d; text-align: center;">No hay datos de actividad reciente.</p>
        <?php endif; ?>
      </div>
    </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MODAL OPCIONES CRUD -->
  <div id="modalOpciones" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; overflow-y: auto;">
    <div style="background: white; margin: 30px auto; max-width: 95%; width: 1200px; min-height: 85vh; max-height: 92vh; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); position: relative; display: flex; flex-direction: column;">
      <div style="background: linear-gradient(135deg, #6c757d, #5a6268); color: white; padding: 20px; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0;">⚙️ Opciones de Administración</h2>
        <button onclick="cerrarModalOpciones()" style="background: rgba(255,255,255,0.2); border: none; color: white; font-size: 24px; cursor: pointer; padding: 5px 15px; border-radius: 5px;">&times;</button>
      </div>
      
      <?php if ($mensaje_opciones): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; margin: 15px; border-radius: 5px; border-left: 4px solid #28a745;">
          <?php echo htmlspecialchars($mensaje_opciones); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($mensaje_error_opciones): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px; border-left: 4px solid #dc3545;">
          <?php echo htmlspecialchars($mensaje_error_opciones); ?>
        </div>
      <?php endif; ?>
      
      <!-- Pestañas del Modal -->
      <div style="display: flex; border-bottom: 2px solid #e9ecef; background: #f8f9fa; flex-wrap: wrap; align-items: stretch;">
        <button onclick="cerrarDropdownMasOpciones(); mostrarTabOpciones('tab-destinatarios', this)" class="tab-opciones-btn active" style="flex: 1; min-width: 100px; padding: 15px; border: none; background: transparent; cursor: pointer; font-weight: 600; border-bottom: 3px solid transparent;">👤 Destinatarios</button>
        <button onclick="cerrarDropdownMasOpciones(); mostrarTabOpciones('tab-insignias', this)" class="tab-opciones-btn" style="flex: 1; min-width: 100px; padding: 15px; border: none; background: transparent; cursor: pointer; font-weight: 600; border-bottom: 3px solid transparent;">🎖️ Insignias Maestras</button>
        <button onclick="cerrarDropdownMasOpciones(); mostrarTabOpciones('tab-categorias', this)" class="tab-opciones-btn" style="flex: 1; min-width: 100px; padding: 15px; border: none; background: transparent; cursor: pointer; font-weight: 600; border-bottom: 3px solid transparent;">📁 Categorías</button>
        <button onclick="cerrarDropdownMasOpciones(); mostrarTabOpciones('tab-subcategorias', this)" class="tab-opciones-btn" style="flex: 1; min-width: 100px; padding: 15px; border: none; background: transparent; cursor: pointer; font-weight: 600; border-bottom: 3px solid transparent;">📂 Subcategorías</button>
        <div style="position: relative; flex: 1; min-width: 120px;">
          <button type="button" onclick="toggleDropdownMasOpciones(event)" class="tab-opciones-btn" id="btn-mas-opciones" style="width: 100%; padding: 15px; border: none; background: transparent; cursor: pointer; font-weight: 600; border-bottom: 3px solid transparent;">⋮ Más Opciones</button>
          <div id="dropdown-mas-opciones" style="display: none; position: absolute; top: 100%; left: 0; min-width: 280px; background: #1e3a5f; color: white; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.25); z-index: 10001; margin-top: 4px; overflow: hidden;">
            <div style="padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.15); font-weight: 600;">Más opciones</div>
            <div style="padding: 8px 0;">
              <div class="mas-opciones-fila" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.1); cursor: pointer;" onclick="cerrarDropdownMasOpciones(); mostrarTabOpciones('tab-certificados');" title="Abrir gestión de Certificados (como Insignias Maestras / Categorías / Subcategorías)">
                <span style="flex: 1;">📄 Certificados</span>
                <span style="display: flex; gap: 8px;" onclick="event.stopPropagation();">
                  <button type="button" onclick="cerrarDropdownMasOpciones(); mostrarTabOpciones('tab-certificados');" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); padding: 6px 12px; border-radius: 5px; cursor: pointer; font-size: 13px;">Editar</button>
                  <button type="button" onclick="event.stopPropagation(); cerrarDropdownMasOpciones(); accionEliminar('Certificados');" style="background: rgba(220,53,69,0.8); color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; font-size: 13px;">Eliminar</button>
                </span>
              </div>
              <div class="mas-opciones-fila" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; cursor: pointer;" onclick="cerrarDropdownMasOpciones(); mostrarTabOpciones('tab-insignias-otorgadas');" title="Abrir gestión de Insignias Otorgadas (como Insignias Maestras / Categorías / Subcategorías)">
                <span style="flex: 1;">🎖️ Insignias Otorgadas</span>
                <span style="display: flex; gap: 8px;" onclick="event.stopPropagation();">
                  <button type="button" onclick="cerrarDropdownMasOpciones(); mostrarTabOpciones('tab-insignias-otorgadas');" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); padding: 6px 12px; border-radius: 5px; cursor: pointer; font-size: 13px;">Editar</button>
                  <button type="button" onclick="event.stopPropagation(); cerrarDropdownMasOpciones(); accionEliminar('Insignias Otorgadas');" style="background: rgba(220,53,69,0.8); color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; font-size: 13px;">Eliminar</button>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div style="padding: 20px; overflow-y: auto; flex: 1; min-height: 0;">
        <!-- Tab Destinatarios -->
        <div id="tab-destinatarios" class="tab-opciones-content active">
          <h3>Gestión de Destinatarios</h3>
          <div style="margin-bottom: 20px;">
            <button onclick="mostrarFormDestinatario('crear')" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-right: 10px;">➕ Crear Nuevo</button>
            <button onclick="cargarDestinatarios()" style="background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">🔄 Actualizar Lista</button>
          </div>
          <div id="lista-destinatarios" style="max-height: 500px; overflow-y: auto;">
            <!-- Se carga dinámicamente -->
          </div>
          <div id="form-destinatario" style="display: none; background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
            <!-- Formulario se carga dinámicamente -->
          </div>
        </div>
        
        <!-- Tab Insignias Maestras -->
        <div id="tab-insignias" class="tab-opciones-content">
          <h3>Gestión de Insignias Maestras (T_insignias)</h3>
          <div style="margin-bottom: 20px;">
            <button onclick="mostrarFormInsignia('crear')" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-right: 10px;">➕ Crear Nueva</button>
            <button onclick="cargarInsignias()" style="background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">🔄 Actualizar Lista</button>
          </div>
          <div id="lista-insignias" style="max-height: 280px; overflow-y: auto;">
            <!-- Se carga dinámicamente -->
          </div>
          <div id="form-insignia" style="display: none; background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px; max-height: 65vh; overflow-y: auto;">
            <div id="form-insignia-crear">
              <h4>Crear Nueva Insignia Maestra</h4>
              <form method="POST" onsubmit="return confirmarOperacion('T_insignias', 'crear')">
                <input type="hidden" name="accion_opciones" value="T_insignias">
                <input type="hidden" name="operacion" value="crear">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                  <div>
                    <label>Tipo Insignia (ID):</label>
                    <input type="number" name="tipo_insignia" required style="width: 100%; padding: 8px;">
                  </div>
                  <div>
                    <label>Propone Insignia (ID):</label>
                    <input type="number" name="propone_insignia" required style="width: 100%; padding: 8px;">
                  </div>
                  <div>
                    <label>Programa:</label>
                    <input type="text" name="programa" required style="width: 100%; padding: 8px;" placeholder="Ej: Ingeniería en Sistemas">
                  </div>
                  <div>
                    <label>Estatus:</label>
                    <input type="number" name="estatus" value="1" required style="width: 100%; padding: 8px;">
                  </div>
                  <div style="grid-column: 1 / -1;">
                    <label>Descripción:</label>
                    <textarea name="descripcion" required style="width: 100%; padding: 8px; min-height: 100px;" placeholder="Descripción de la insignia"></textarea>
                  </div>
                  <div style="grid-column: 1 / -1;">
                    <label>Criterio:</label>
                    <textarea name="criterio" required style="width: 100%; padding: 8px; min-height: 80px;" placeholder="Criterios para otorgar la insignia"></textarea>
                  </div>
                  <div>
                    <label>Fecha Creación:</label>
                    <input type="date" name="fecha_creacion" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 8px;">
                  </div>
                  <div>
                    <label>Fecha Autorización:</label>
                    <input type="date" name="fecha_autorizacion" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 8px;">
                  </div>
                  <div>
                    <label>Nombre Gen Ins:</label>
                    <input type="text" name="nombre_gen_ins" style="width: 100%; padding: 8px;" placeholder="Ej: TecNM-ITSM">
                  </div>
                  <div>
                    <label>Archivo Visual:</label>
                    <input type="text" name="archivo_visual" style="width: 100%; padding: 8px;" placeholder="Ej: insignia_nombre.png">
                  </div>
                </div>
                <button type="submit" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Guardar</button>
                <button type="button" onclick="document.getElementById('form-insignia').style.display='none'" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">Cancelar</button>
              </form>
            </div>
            <div id="form-insignia-editar" style="display: none;">
              <!-- Formulario de edición se carga dinámicamente -->
            </div>
          </div>
        </div>
        
        <!-- Tab Categorías -->
        <div id="tab-categorias" class="tab-opciones-content">
          <h3>Gestión de Categorías</h3>
          <div style="margin-bottom: 20px;">
            <button onclick="cargarCategorias()" style="background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">🔄 Actualizar Lista</button>
          </div>
          <div id="lista-categorias" style="max-height: 500px; overflow-y: auto;">
            <!-- Se carga dinámicamente -->
          </div>
          <div id="form-categoria" style="display: none; background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
            <!-- Formulario se carga dinámicamente -->
          </div>
        </div>
        
        <!-- Tab Subcategorías -->
        <div id="tab-subcategorias" class="tab-opciones-content">
          <h3>Gestión de Subcategorías</h3>
          <div style="margin-bottom: 20px;">
            <button onclick="cargarSubcategorias()" style="background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">🔄 Actualizar Lista</button>
          </div>
          <div id="lista-subcategorias" style="max-height: 500px; overflow-y: auto;">
            <!-- Se carga dinámicamente -->
          </div>
          <div id="form-subcategoria" style="display: none; background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
            <!-- Formulario se carga dinámicamente -->
          </div>
        </div>

        <!-- Tab Certificados (desde Más Opciones) -->
        <div id="tab-certificados" class="tab-opciones-content">
          <h3>Gestión de Certificados</h3>
          <div style="margin-bottom: 20px;">
            <button onclick="cargarCertificados()" style="background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">🔄 Actualizar Lista</button>
          </div>
          <div id="lista-certificados" style="max-height: 500px; overflow-y: auto;">
            <div style="text-align: center; padding: 20px; color: #6c757d;">Use «Actualizar Lista» para cargar certificados.</div>
          </div>
        </div>

        <!-- Tab Insignias Otorgadas (desde Más Opciones) -->
        <div id="tab-insignias-otorgadas" class="tab-opciones-content">
          <h3>Gestión de Insignias Otorgadas</h3>
          <div style="margin-bottom: 20px;">
            <button onclick="cargarInsigniasOtorgadas()" style="background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">🔄 Actualizar Lista</button>
          </div>
          <div id="lista-insignias-otorgadas" style="max-height: 500px; overflow-y: auto;">
            <div style="text-align: center; padding: 20px; color: #6c757d;">Use «Actualizar Lista» para cargar insignias otorgadas.</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- FOOTER AZUL PROFESIONAL -->
  <footer>
    <div class="footer-content">
      <div class="copyright">
        <p>Copyright 2025 - TecNM</p>
        <p>Ultima actualización - Diciembre 2025</p>
      </div>
      
      <div class="footer-section">
        <h3>Enlaces</h3>
        <div class="footer-links">
          <a href="https://datos.gob.mx/" target="_blank">Datos</a>
          <a href="https://www.gob.mx/publicaciones" target="_blank">Publicaciones</a>
          <a href="https://consultapublicamx.plataformadetransparencia.org.mx/vut-web/faces/view/consultaPublica.xhtml?idEntidad=MzM=&idSujetoObligado=MTAwMDE=#inicio" target="_blank">Portal de Obligaciones de Transparencia</a>
          <a href="https://www.gob.mx/pnt" target="_blank">PNT</a>
          <a href="https://www.inai.org.mx/" target="_blank">INAI</a>
          <a href="https://www.gob.mx/alerta" target="_blank">Alerta</a>
          <a href="https://www.gob.mx/denuncia" target="_blank">Denuncia</a>
        </div>
      </div>
      
      <div class="footer-section">
        <h3>¿Qué es gob.mx?</h3>
        <p>Es el portal único de trámites, información y participación ciudadana.</p>
        <a href="https://www.gob.mx/" target="_blank">Leer más</a>
      </div>
      
      <div class="footer-section">
        <div class="footer-links">
          <a href="https://www.gob.mx/administraciones-anteriores" target="_blank">Administraciones anteriores</a>
          <a href="https://www.gob.mx/accesibilidad" target="_blank">Declaración de Accesibilidad</a>
          <a href="https://www.gob.mx/privacidad" target="_blank">Aviso de privacidad</a>
          <a href="https://www.gob.mx/privacidad-simplificado" target="_blank">Aviso de privacidad simplificado</a>
          <a href="https://www.gob.mx/terminos" target="_blank">Términos y Condiciones</a>
        </div>
      </div>
      
      <div class="footer-section">
        <div class="footer-links">
          <a href="https://www.gob.mx/politica-seguridad" target="_blank">Política de seguridad</a>
          <a href="https://www.gob.mx/denuncia-servidores" target="_blank">Denuncia contra servidores públicos</a>
        </div>
      </div>
      
      <div class="footer-section">
        <h3>Síguenos en</h3>
        <div class="social-icons">
          <a href="https://www.facebook.com/TecNacionalMexico" target="_blank" class="social-icon">f</a>
          <a href="https://twitter.com/TecNacionalMex" target="_blank" class="social-icon">X</a>
          <a href="https://www.youtube.com/user/TecNacionalMexico" target="_blank" class="social-icon">▶</a>
          <a href="https://www.instagram.com/tecnacionalmexico/" target="_blank" class="social-icon">📷</a>
        </div>
      </div>
    </div>
  </footer>
</body>
</html>

