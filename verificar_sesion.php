<?php
// Verificar si la sesión ya está iniciada antes de llamar session_start() 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexión a base de datos
require_once 'conexion.php';

// Verificar si existe la sesión del usuario
$archivo_actual = basename($_SERVER['PHP_SELF']);

// Lista de archivos que NO requieren autenticación (páginas públicas)
$archivos_publicos = ['login.php', 'index.php', 'consulta_publica.php', 'verificar_insignia.php', 'validacion_publica.php'];

// Solo hacer verificación automática si NO es una página pública
// Las páginas protegidas deben hacer su propia verificación usando las funciones específicas
if (
    !isset($_SESSION['usuario_id']) ||
    !isset($_SESSION['rol']) ||
    empty($_SESSION['usuario_id']) ||
    empty($_SESSION['rol'])
) {
    // Si es una página pública, no redirigir
    if (!in_array($archivo_actual, $archivos_publicos)) {
        // Solo redirigir si no hay sesión y no es una página pública
        header('Location: login.php?error=sesion_invalida');
        exit();
    }
}

// Función para obtener información del usuario actual desde la base de datos
function obtenerUsuarioActual() {
    global $conexion;
    
    if (!isset($_SESSION['usuario_id'])) {
        return null;
    }
    
    // Manejar el caso del admin hardcodeado
    if ($_SESSION['usuario_id'] == 999999) {
        $nombre_completo = $_SESSION['nombre'] ?? 'Administrador Sistema';
        return [
            'Id_Usuario' => 999999,
            'Nombre' => $nombre_completo,
            'Apellido_Paterno' => '',
            'Apellido_Materno' => '',
            'Correo' => $_SESSION['correo'] ?? 'admin@tecnm.mx',
            'Rol' => $_SESSION['rol'] ?? 'Admin',
            'Estado' => 'Activo',
            'Fecha_Creacion' => date('Y-m-d H:i:s')
        ];
    }
    
    try {
        $conexion->select_db("insignia");
        
        // Verificar estructura de la tabla
        $resultado_tabla = $conexion->query("SHOW COLUMNS FROM Usuario");
        $campos_tabla = [];
        while ($row = $resultado_tabla->fetch_assoc()) {
            $campos_tabla[] = $row['Field'];
        }
        
        $tiene_nombre_completo = in_array('Nombre_Completo', $campos_tabla);
        $campo_id = in_array('ID_usuario', $campos_tabla) ? 'ID_usuario' : 'Id_Usuario';
        $campo_rol = in_array('Tipo_Usuario', $campos_tabla) ? 'Tipo_Usuario' : 'Rol';
        
        // Construir consulta según estructura
        if ($tiene_nombre_completo) {
            $sql = "SELECT $campo_id as Id_Usuario, Nombre_Completo, 
                           Correo_Electronico as Correo, $campo_rol as Rol, Fecha_Creacion 
                    FROM Usuario 
                    WHERE $campo_id = ?";
        } else {
            $sql = "SELECT Id_Usuario, Nombre, Apellido_Paterno, Apellido_Materno, 
                           Correo, Rol, Estado, Fecha_Creacion 
                    FROM Usuario 
                    WHERE Id_Usuario = ?";
        }
        
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error al preparar consulta: " . $conexion->error);
        }
        
        $stmt->bind_param("i", $_SESSION['usuario_id']);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            $stmt->close();
            
            // Si tiene Nombre_Completo, ajustar estructura para compatibilidad
            if ($tiene_nombre_completo && isset($usuario['Nombre_Completo'])) {
                $usuario['Nombre'] = $usuario['Nombre_Completo'];
                $usuario['Apellido_Paterno'] = '';
                $usuario['Apellido_Materno'] = '';
                $usuario['Estado'] = 'Activo';
            }
            
            return $usuario;
        } else {
            // Usuario no existe, cerrar sesión
            session_destroy();
            header('Location: login.php?error=usuario_inactivo');
            exit();
        }
        
    } catch (Exception $e) {
        error_log("Error al obtener usuario: " . $e->getMessage());
        return null;
    }
}

// Función para verificar si el usuario tiene un rol específico
function verificarRol($rol_requerido) {
    $usuario = obtenerUsuarioActual();
    
    if (!$usuario || $usuario['Rol'] !== $rol_requerido) {
        header('Location: login.php?error=acceso_denegado');
        exit();
    }
}

// Función para verificar si el usuario tiene alguno de los roles permitidos
function verificarRoles($roles_permitidos) {
    $usuario = obtenerUsuarioActual();
    
    if (!$usuario) {
        // Si no hay usuario, redirigir al login
        header('Location: login.php?error=sesion_invalida');
        exit();
    }
    
    // Obtener el rol del usuario
    $rol_usuario = $usuario['Rol'];
    
    // Mapear sinónimos de roles
    $roles_mapeados = [
        'Administrador' => 'Admin',
        'Admin' => 'Admin',
        'SuperUsuario' => 'SuperUsuario',
        'Estudiante' => 'Estudiante'
    ];
    
    // Verificar si el rol está en los permitidos (considerando sinónimos)
    $rol_valido = false;
    foreach ($roles_permitidos as $rol_permitido) {
        // Mapear el rol permitido
        $rol_permitido_mapeado = isset($roles_mapeados[$rol_permitido]) ? $roles_mapeados[$rol_permitido] : $rol_permitido;
        
        // Verificar si coincide
        if ($rol_usuario === $rol_permitido_mapeado || $rol_usuario === $rol_permitido) {
            $rol_valido = true;
            break;
        }
    }
    
    if (!$rol_valido) {
        header('Location: login.php?error=acceso_denegado');
        exit();
    }
}

// Función para obtener el nombre completo del usuario
function obtenerNombreCompleto() {
    $usuario = obtenerUsuarioActual();
    
    if ($usuario) {
        return $usuario['Nombre_Completo'];
    }
    
    return '';
}

// Función para verificar si el usuario es administrador (incluye SuperUsuario)
function esAdministrador() {
    $usuario = obtenerUsuarioActual();
    // Mapear sinónimos
    $rol = $usuario['Rol'] ?? '';
    if ($rol === 'Administrador') $rol = 'Admin';
    return $usuario && in_array($rol, ['Admin', 'SuperUsuario']);
}

// Función para verificar si el usuario es estudiante
function esEstudiante() {
    $usuario = obtenerUsuarioActual();
    return $usuario && $usuario['Rol'] === 'Estudiante';
}

// Función para obtener el rol del usuario
function obtenerRolUsuario() {
    $usuario = obtenerUsuarioActual();
    return $usuario ? $usuario['Rol'] : null;
}

// Función para verificar si la sesión es válida y actualizar datos si es necesario
function verificarSesionValida() {
    $usuario = obtenerUsuarioActual();
    
    if (!$usuario) {
        session_destroy();
        header('Location: login.php?error=sesion_invalida');
        exit();
    }
    
    // Actualizar datos de sesión con información actualizada de la BD
    $_SESSION['nombre'] = $usuario['Nombre'];
    $_SESSION['apellido_paterno'] = $usuario['Apellido_Paterno'];
    $_SESSION['apellido_materno'] = $usuario['Apellido_Materno'];
    $_SESSION['correo'] = $usuario['Correo'];
    $_SESSION['rol'] = $usuario['Rol'];
    $_SESSION['estado'] = $usuario['Estado'];
    
    return $usuario;
}

// ========================================
// FUNCIONES ESPECÍFICAS PARA SUPERUSUARIO
// ========================================

// Función para verificar si el usuario es SuperUsuario
function esSuperUsuario() {
    $usuario = obtenerUsuarioActual();
    return $usuario && $usuario['Rol'] === 'SuperUsuario';
}

// Función para verificar si el usuario es Admin (pero no SuperUsuario)
function esAdminRegular() {
    $usuario = obtenerUsuarioActual();
    return $usuario && $usuario['Rol'] === 'Admin';
}

// Función para verificar si el usuario puede crear otros administradores
function puedeCrearAdministradores() {
    return esSuperUsuario();
}

// Función para verificar si el usuario puede acceder a configuraciones críticas
function puedeAccederConfiguracionesCriticas() {
    return esSuperUsuario();
}

// Función para verificar si el usuario puede ver auditoría completa
function puedeVerAuditoriaCompleta() {
    return esSuperUsuario();
}

// Función para verificar si el usuario puede gestionar otros SuperUsuarios
function puedeGestionarSuperUsuarios() {
    return esSuperUsuario();
}

// Función para obtener permisos específicos del usuario
function obtenerPermisosUsuario() {
    $rol = obtenerRolUsuario();
    
    $permisos = [
        'Admin' => [
            'crear_categorias' => true,
            'crear_subcategorias' => true,
            'crear_insignias' => true,
            'registrar_usuarios' => true,
            'ver_historial' => true,
            'crear_administradores' => false,
            'configuraciones_criticas' => false,
            'auditoria_completa' => false,
            'gestionar_superusuarios' => false
        ],
        'SuperUsuario' => [
            'crear_categorias' => true,
            'crear_subcategorias' => true,
            'crear_insignias' => true,
            'registrar_usuarios' => true,
            'ver_historial' => true,
            'crear_administradores' => true,
            'configuraciones_criticas' => true,
            'auditoria_completa' => true,
            'gestionar_superusuarios' => true
        ],
        'Estudiante' => [
            'ver_insignias' => true,
            'compartir_insignias' => true,
            'imprimir_insignias' => true
        ]
    ];
    
    return $permisos[$rol] ?? [];
}

// Función para verificar un permiso específico
function tienePermiso($permiso) {
    $permisos = obtenerPermisosUsuario();
    return isset($permisos[$permiso]) && $permisos[$permiso] === true;
}
?>
