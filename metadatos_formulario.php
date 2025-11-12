<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=sesion_invalida');
    exit();
}

// Verificar permisos (solo Admin y SuperUsuario pueden registrar reconocimientos)
// Comentar temporalmente para debug
/*
if ($_SESSION['rol'] !== 'Admin' && $_SESSION['rol'] !== 'SuperUsuario') {
    header('Location: login.php?error=acceso_denegado');
    exit();
}
*/

// Incluir conexión a la base de datos
require_once 'conexion.php';
require_once 'verificar_sesion.php';
require_once 'funciones_correo_real.php';

// Consultar categorías e insignias disponibles
$categorias_insignias = [];
$subcategorias_insignias = [];
$periodos_emision = [];
$estatus_disponibles = [];
$responsables_emision = [];

try {
    // Verificar estructura de cat_insignias (puede tener id o ID_cat)
    $check_cat = $conexion->query("SHOW COLUMNS FROM cat_insignias LIKE 'id'");
    $tiene_id_cat = ($check_cat && $check_cat->num_rows > 0);
    $campo_id_cat = $tiene_id_cat ? 'id' : 'ID_cat';
    
    // Consultar categorías de insignias (usar el campo correcto)
    $sql_categorias = "SELECT DISTINCT $campo_id_cat as id, Nombre_cat as nombre_categoria FROM cat_insignias ORDER BY Nombre_cat";
    $result_categorias = $conexion->query($sql_categorias);
    
    if ($result_categorias && $result_categorias->num_rows > 0) {
        while ($row = $result_categorias->fetch_assoc()) {
            $categorias_insignias[] = $row;
        }
    }
    
    // Verificar estructura de tipo_insignia (puede tener id o ID_tipo, y puede o no tener Cat_ins)
    $check_tipo = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'id'");
    $tiene_id_tipo = ($check_tipo && $check_tipo->num_rows > 0);
    $campo_id_tipo = $tiene_id_tipo ? 'id' : 'ID_tipo';
    
    $check_cat_ins = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Cat_ins'");
    $tiene_cat_ins = ($check_cat_ins && $check_cat_ins->num_rows > 0);
    
    // Verificar nombre de columna en tipo_insignia
    $check_nombre = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Nombre_Insignia'");
    $tiene_nombre_insignia = ($check_nombre && $check_nombre->num_rows > 0);
    $campo_nombre_tipo = $tiene_nombre_insignia ? 'Nombre_Insignia' : 'Nombre_ins';
    
    // Consultar tipos de insignias (subcategorías)
    // Si tiene Cat_ins, hacer JOIN con cat_insignias, si no, asignar categorías inteligentemente
    if ($tiene_cat_ins) {
        $sql_subcategorias = "SELECT ti.$campo_id_tipo as id, ti.$campo_nombre_tipo as nombre_insignia, ti.Cat_ins as categoria_id, ci.Nombre_cat as nombre_categoria 
                             FROM tipo_insignia ti 
                             LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.$campo_id_cat 
                             ORDER BY ci.Nombre_cat, ti.$campo_nombre_tipo";
    } else {
        // Si no tiene Cat_ins, asignar categorías basándose en el nombre de la insignia
        // Estructura correcta:
        // Formacion Integral (1): Embajador del Deporte, Embajador del Arte, Responsabilidad Social, Movilidad e Intercambio, Talento Innovador
        // Docencia (2): Formación y Actualización
        // Academia (3): Talento Científico
        $sql_subcategorias = "SELECT 
                                ti.$campo_id_tipo as id, 
                                ti.$campo_nombre_tipo as nombre_insignia,
                                CASE 
                                    WHEN ti.$campo_nombre_tipo LIKE '%Movilidad%' OR ti.$campo_nombre_tipo LIKE '%Intercambio%' OR 
                                         ti.$campo_nombre_tipo LIKE '%Arte%' OR ti.$campo_nombre_tipo LIKE '%Deporte%' OR
                                         ti.$campo_nombre_tipo LIKE '%Responsabilidad%' OR ti.$campo_nombre_tipo LIKE '%Social%' OR
                                         ti.$campo_nombre_tipo LIKE '%Innovacion%' OR ti.$campo_nombre_tipo LIKE '%Talento Innovador%' THEN 1
                                    WHEN ti.$campo_nombre_tipo LIKE '%Formacion%' OR ti.$campo_nombre_tipo LIKE '%Actualizacion%' THEN 2
                                    WHEN ti.$campo_nombre_tipo LIKE '%Talento Cientifico%' OR ti.$campo_nombre_tipo LIKE '%Cientifico%' THEN 3
                                    ELSE 1
                                END as categoria_id,
                                CASE 
                                    WHEN ti.$campo_nombre_tipo LIKE '%Movilidad%' OR ti.$campo_nombre_tipo LIKE '%Intercambio%' OR 
                                         ti.$campo_nombre_tipo LIKE '%Arte%' OR ti.$campo_nombre_tipo LIKE '%Deporte%' OR
                                         ti.$campo_nombre_tipo LIKE '%Responsabilidad%' OR ti.$campo_nombre_tipo LIKE '%Social%' OR
                                         ti.$campo_nombre_tipo LIKE '%Innovacion%' OR ti.$campo_nombre_tipo LIKE '%Talento Innovador%' THEN 'Formacion Integral'
                                    WHEN ti.$campo_nombre_tipo LIKE '%Formacion%' OR ti.$campo_nombre_tipo LIKE '%Actualizacion%' THEN 'Docencia'
                                    WHEN ti.$campo_nombre_tipo LIKE '%Talento Cientifico%' OR ti.$campo_nombre_tipo LIKE '%Cientifico%' THEN 'Academia'
                                    ELSE 'Formacion Integral'
                                END as nombre_categoria
                             FROM tipo_insignia ti 
                             ORDER BY nombre_categoria, ti.$campo_nombre_tipo";
    }
    
    $result_subcategorias = $conexion->query($sql_subcategorias);
    
    if ($result_subcategorias && $result_subcategorias->num_rows > 0) {
        while ($row = $result_subcategorias->fetch_assoc()) {
            $subcategorias_insignias[] = $row;
        }
    }
    
    // Consultar periodos de emisión (verificar estructura)
    $check_periodo = $conexion->query("SHOW COLUMNS FROM periodo_emision LIKE 'id'");
    $tiene_id_periodo = ($check_periodo && $check_periodo->num_rows > 0);
    $campo_id_periodo = $tiene_id_periodo ? 'id' : 'ID_periodo';
    
    $check_nombre_periodo = $conexion->query("SHOW COLUMNS FROM periodo_emision LIKE 'Nombre_Periodo'");
    $tiene_nombre_periodo = ($check_nombre_periodo && $check_nombre_periodo->num_rows > 0);
    $campo_periodo = $tiene_nombre_periodo ? 'Nombre_Periodo' : 'periodo';
    
    $sql_periodos = "SELECT DISTINCT $campo_id_periodo as id, $campo_periodo as periodo FROM periodo_emision ORDER BY $campo_periodo DESC";
    $result_periodos = $conexion->query($sql_periodos);
    
    if ($result_periodos && $result_periodos->num_rows > 0) {
        while ($row = $result_periodos->fetch_assoc()) {
            $periodos_emision[] = $row;
        }
    }
    
    // Consultar estatus disponibles (verificar estructura)
    $check_estatus = $conexion->query("SHOW COLUMNS FROM estatus LIKE 'id'");
    $tiene_id_estatus = ($check_estatus && $check_estatus->num_rows > 0);
    $campo_id_estatus = $tiene_id_estatus ? 'id' : 'ID_estatus';
    
    $check_nombre_estatus = $conexion->query("SHOW COLUMNS FROM estatus LIKE 'Nombre_Estatus'");
    $tiene_nombre_estatus = ($check_nombre_estatus && $check_nombre_estatus->num_rows > 0);
    $campo_nombre_estatus = $tiene_nombre_estatus ? 'Nombre_Estatus' : 'Estatus';
    
    $sql_estatus = "SELECT DISTINCT $campo_id_estatus as id, $campo_nombre_estatus as nombre_estatus FROM estatus ORDER BY $campo_nombre_estatus";
    $result_estatus = $conexion->query($sql_estatus);
    
    if ($result_estatus && $result_estatus->num_rows > 0) {
        while ($row = $result_estatus->fetch_assoc()) {
            $estatus_disponibles[] = $row;
        }
    }
    
    // Consultar responsables de emisión (verificar estructura)
    $check_responsable = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'id'");
    $tiene_id_responsable = ($check_responsable && $check_responsable->num_rows > 0);
    $campo_id_responsable = $tiene_id_responsable ? 'id' : 'ID_responsable';
    
    $sql_responsables = "SELECT DISTINCT $campo_id_responsable as id, Nombre_Completo as nombre_completo, Cargo as cargo FROM responsable_emision ORDER BY Nombre_Completo";
    $result_responsables = $conexion->query($sql_responsables);
    
    if ($result_responsables && $result_responsables->num_rows > 0) {
        while ($row = $result_responsables->fetch_assoc()) {
            $responsables_emision[] = $row;
        }
    }
    
} catch (Exception $e) {
    // Si hay error, usar arrays vacíos
    $categorias_insignias = [];
    $subcategorias_insignias = [];
    $periodos_emision = [
        ['id' => 1, 'periodo' => '2025-1'],
        ['id' => 2, 'periodo' => '2025-2'],
        ['id' => 3, 'periodo' => '2024-2']
    ];
    $estatus_disponibles = [
        ['id' => 1, 'nombre_estatus' => 'Pendiente'],
        ['id' => 2, 'nombre_estatus' => 'Autorizado'],
        ['id' => 3, 'nombre_estatus' => 'Rechazado']
    ];
    $responsables_emision = [
        ['id' => 1, 'nombre_completo' => 'Director Tecnológico de San Marcos', 'cargo' => 'Director'],
        ['id' => 2, 'nombre_completo' => 'Coordinador Académico', 'cargo' => 'Coordinador'],
        ['id' => 3, 'nombre_completo' => 'Jefe de Departamento', 'cargo' => 'Jefe de Departamento']
    ];
}

// Si no hay datos, usar datos por defecto
// Las categorías se cargan desde la base de datos

// Las subcategorías se cargan desde la base de datos

if (empty($periodos_emision)) {
    $periodos_emision = [
        ['id' => 1, 'periodo' => '2025-1'],
        ['id' => 2, 'periodo' => '2025-2'],
        ['id' => 3, 'periodo' => '2024-2']
    ];
}

if (empty($estatus_disponibles)) {
    $estatus_disponibles = [
        ['id' => 1, 'nombre_estatus' => 'Pendiente'],
        ['id' => 2, 'nombre_estatus' => 'Autorizado'],
        ['id' => 3, 'nombre_estatus' => 'Rechazado']
    ];
}

if (empty($responsables_emision)) {
    $responsables_emision = [
        ['id' => 1, 'nombre_completo' => 'Director Tecnológico de San Marcos', 'cargo' => 'Director'],
        ['id' => 2, 'nombre_completo' => 'Coordinador Académico', 'cargo' => 'Coordinador'],
        ['id' => 3, 'nombre_completo' => 'Jefe de Departamento', 'cargo' => 'Jefe de Departamento']
    ];
}

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<!-- DEBUG: Formulario enviado -->";
    echo "<!-- DEBUG: POST data: " . print_r($_POST, true) . " -->";
    
    $categoria_id = $_POST['categoria'] ?? '';
    $subcategoria_id = $_POST['subcategoria'] ?? '';
    $insignia = $_POST['insignia'] ?? '';
    $estudiante = $_POST['estudiante'] ?? '';
    $curp = $_POST['curp'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $matricula = $_POST['matricula'] ?? '';
    $periodo = $_POST['periodo'] ?? '';
    $responsable = $_POST['responsable'] ?? '';
    $estatus = $_POST['estatus'] ?? '';
    $clave = $_POST['clave'] ?? '';
    $fecha_otorgamiento = $_POST['fecha_otorgamiento'] ?? '';
    $fecha_autorizacion = $_POST['fecha_autorizacion'] ?? '';
    $evidencia = $_POST['evidencia'] ?? '';
    $descripcion_formulario = $_POST['descripcion'] ?? '';
    
    echo "<!-- DEBUG: Valores extraídos -->";
    echo "<!-- DEBUG: categoria_id = '$categoria_id' -->";
    echo "<!-- DEBUG: subcategoria_id = '$subcategoria_id' -->";
    echo "<!-- DEBUG: insignia = '$insignia' -->";
    echo "<!-- DEBUG: estudiante = '$estudiante' -->";
    echo "<!-- DEBUG: periodo = '$periodo' -->";
    echo "<!-- DEBUG: responsable = '$responsable' -->";
    echo "<!-- DEBUG: estatus = '$estatus' -->";
    echo "<!-- DEBUG: fecha_otorgamiento = '$fecha_otorgamiento' -->";
    echo "<!-- DEBUG: fecha_autorizacion = '$fecha_autorizacion' -->";
    echo "<!-- DEBUG: clave recibida del POST = '" . htmlspecialchars($clave) . "' -->";
    
    // Obtener información de las tablas para nombres dinámicos
    $categoria_nombre = '';
    $tipo_insignia_nombre = '';
    
    if (!empty($subcategoria_id)) {
        // Verificar estructura de las tablas dinámicamente
        $check_tipo = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'id'");
        $tiene_id_tipo = ($check_tipo && $check_tipo->num_rows > 0);
        $campo_id_tipo = $tiene_id_tipo ? 'id' : 'ID_tipo';
        
        $check_nombre = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Nombre_Insignia'");
        $tiene_nombre_insignia = ($check_nombre && $check_nombre->num_rows > 0);
        $campo_nombre_tipo = $tiene_nombre_insignia ? 'Nombre_Insignia' : 'Nombre_ins';
        
        $check_cat_ins = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Cat_ins'");
        $tiene_cat_ins = ($check_cat_ins && $check_cat_ins->num_rows > 0);
        
        $check_cat = $conexion->query("SHOW COLUMNS FROM cat_insignias LIKE 'id'");
        $tiene_id_cat = ($check_cat && $check_cat->num_rows > 0);
        $campo_id_cat = $tiene_id_cat ? 'id' : 'ID_cat';
        
        if ($tiene_cat_ins) {
            // Si tiene Cat_ins, hacer JOIN con cat_insignias
            $sql_info_completa = "SELECT 
                                    ci.Nombre_cat as nombre_categoria,
                                    ti.$campo_nombre_tipo as nombre_insignia
                                 FROM tipo_insignia ti 
                                 LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.$campo_id_cat
                                 WHERE ti.$campo_id_tipo = ? 
                                 LIMIT 1";
        } else {
            // Si no tiene Cat_ins, determinar categoría basándose en el nombre de la insignia
            // Estructura correcta:
            // Formacion Integral (1): Embajador del Deporte, Embajador del Arte, Responsabilidad Social, Movilidad e Intercambio, Talento Innovador
            // Docencia (2): Formación y Actualización
            // Academia (3): Talento Científico
            $sql_info_completa = "SELECT 
                                    CASE 
                                        WHEN ti.$campo_nombre_tipo LIKE '%Movilidad%' OR ti.$campo_nombre_tipo LIKE '%Intercambio%' OR 
                                             ti.$campo_nombre_tipo LIKE '%Arte%' OR ti.$campo_nombre_tipo LIKE '%Deporte%' OR
                                             ti.$campo_nombre_tipo LIKE '%Responsabilidad%' OR ti.$campo_nombre_tipo LIKE '%Social%' OR
                                             ti.$campo_nombre_tipo LIKE '%Innovacion%' OR ti.$campo_nombre_tipo LIKE '%Talento Innovador%' THEN 'Formacion Integral'
                                        WHEN ti.$campo_nombre_tipo LIKE '%Formacion%' OR ti.$campo_nombre_tipo LIKE '%Actualizacion%' THEN 'Docencia'
                                        WHEN ti.$campo_nombre_tipo LIKE '%Talento Cientifico%' OR ti.$campo_nombre_tipo LIKE '%Cientifico%' THEN 'Academia'
                                        ELSE 'Formacion Integral'
                                    END as nombre_categoria,
                                    ti.$campo_nombre_tipo as nombre_insignia
                                 FROM tipo_insignia ti 
                                 WHERE ti.$campo_id_tipo = ? 
                                 LIMIT 1";
        }
        
        $stmt_info = $conexion->prepare($sql_info_completa);
        
        if ($stmt_info) {
            $stmt_info->bind_param("i", $subcategoria_id);
            $stmt_info->execute();
            $result_info = $stmt_info->get_result();
            
            if ($result_info && $result_info->num_rows > 0) {
                $row_info = $result_info->fetch_assoc();
                $categoria_nombre = $row_info['nombre_categoria'];
                $tipo_insignia_nombre = $row_info['nombre_insignia'];
            }
            $stmt_info->close();
        }
    }
    
    // Usar la descripción del formulario si está disponible, sino usar una genérica
    if (!empty($descripcion_formulario)) {
        $descripcion_real = $descripcion_formulario;
    } else {
        $descripcion_real = "Esta insignia reconoce la participación destacada en actividades de " . ($tipo_insignia_nombre ?: $insignia) . " desarrollando competencias de " . ($categoria_nombre ?: 'Formación Integral') . ", por parte de " . $estudiante . ".";
    }
    
    // Validar que el estatus existe en la base de datos (usar campo dinámico detectado)
    $estatus_valido = false;
    if (!empty($estatus)) {
        // Usar el campo dinámico detectado al inicio
        $check_estatus_val = $conexion->query("SHOW COLUMNS FROM estatus LIKE 'id'");
        $tiene_id_estatus_val = ($check_estatus_val && $check_estatus_val->num_rows > 0);
        $campo_id_estatus_val = $tiene_id_estatus_val ? 'id' : 'ID_estatus';
        
        $sql_verificar_estatus = "SELECT $campo_id_estatus_val as id FROM estatus WHERE $campo_id_estatus_val = ?";
        $stmt_estatus = $conexion->prepare($sql_verificar_estatus);
        if ($stmt_estatus) {
            $stmt_estatus->bind_param("i", $estatus);
            $stmt_estatus->execute();
            $result_estatus = $stmt_estatus->get_result();
            $estatus_valido = ($result_estatus && $result_estatus->num_rows > 0);
            $stmt_estatus->close();
        }
    }
    
    // Validar campos obligatorios (insignia puede estar vacío si tenemos subcategoria_id)
    $campos_validos = !empty($categoria_id) && !empty($subcategoria_id) && !empty($estudiante) && 
                      !empty($curp) && !empty($correo) && !empty($matricula) && 
                      !empty($periodo) && !empty($responsable) && !empty($estatus) && 
                      !empty($fecha_otorgamiento) && !empty($fecha_autorizacion) && $estatus_valido;
    
    if ($campos_validos) {
        echo "<!-- DEBUG: Validación pasada, procesando insignia -->";
        echo "<!-- DEBUG: Estatus seleccionado: " . $estatus . " -->";
        echo "<!-- DEBUG: Estatus válido: " . ($estatus_valido ? 'SÍ' : 'NO') . " -->";
        
        // VALIDACIÓN: Verificar si la persona ya tiene esta insignia específica
        // Determinar el código de tipo basado en el nombre de la insignia
        $codigo_tipo = '';
        if (strpos($tipo_insignia_nombre, 'Arte') !== false) {
            $codigo_tipo = 'ART';
        } elseif (strpos($tipo_insignia_nombre, 'Deporte') !== false) {
            $codigo_tipo = 'EMB';
        } elseif (strpos($tipo_insignia_nombre, 'Científico') !== false) {
            $codigo_tipo = 'TAL';
        } elseif (strpos($tipo_insignia_nombre, 'Innovador') !== false) {
            $codigo_tipo = 'INN';
        } elseif (strpos($tipo_insignia_nombre, 'Social') !== false) {
            $codigo_tipo = 'SOC';
        } elseif (strpos($tipo_insignia_nombre, 'Formación') !== false) {
            $codigo_tipo = 'FOR';
        } elseif (strpos($tipo_insignia_nombre, 'Movilidad') !== false) {
            $codigo_tipo = 'MOV';
        }
        
        // Verificar si la tabla existe antes de verificar duplicados
        $tabla_existe_io_check = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
        $tabla_io_existe = ($tabla_existe_io_check && $tabla_existe_io_check->num_rows > 0);
        
        if ($tabla_io_existe) {
            $sql_verificar_duplicado = "
                SELECT COUNT(*) as total, io.Codigo_Insignia, io.Fecha_Emision
                FROM insigniasotorgadas io
                LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario
                WHERE d.Nombre_Completo = ? 
                AND io.Codigo_Insignia LIKE ?
            ";
            
            $patron_codigo = '%' . $codigo_tipo . '%';
            $stmt_verificar = $conexion->prepare($sql_verificar_duplicado);
            if ($stmt_verificar) {
                $stmt_verificar->bind_param("ss", $estudiante, $patron_codigo);
                $stmt_verificar->execute();
                $resultado_verificar = $stmt_verificar->get_result();
                if ($resultado_verificar) {
                    $data_verificar = $resultado_verificar->fetch_assoc();
                    $ya_tiene_insignia = $data_verificar['total'] > 0;
                    $stmt_verificar->close();
                    
                    if ($ya_tiene_insignia) {
                        $mensaje_error_modal = [
                            'estudiante' => $estudiante,
                            'tipo_insignia' => $tipo_insignia_nombre,
                            'codigo_existente' => $data_verificar['Codigo_Insignia'],
                            'fecha_existente' => $data_verificar['Fecha_Emision'],
                            'mensaje' => "El estudiante '$estudiante' ya tiene una insignia de '$tipo_insignia_nombre' (Código: " . $data_verificar['Codigo_Insignia'] . "). No se puede otorgar la misma insignia dos veces."
                        ];
                    }
                }
            }
        }
        
        // Solo continuar si no hay duplicado
        if (!isset($mensaje_error_modal)) {
            // Generar clave única si no se proporcionó
            if (empty($clave)) {
                // Crear clave más específica: TECNM-OFCM-[PERIODO]-[TIPO]-[NUMERO]
                $tipo_codigo = strtoupper(substr($tipo_insignia_nombre, 0, 3)); // Primeras 3 letras del tipo
                $tipo_codigo = preg_replace('/[^A-Z]/', '', $tipo_codigo); // Solo letras
                if (strlen($tipo_codigo) < 3) $tipo_codigo = 'INS'; // Fallback
                
                $clave = "TECNM-OFCM-" . $periodo . "-" . $tipo_codigo . "-" . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
                
                // Verificar que la clave no exista (usar Codigo_Insignia, no clave_insignia)
                // Primero verificar si la tabla existe
                $tabla_existe_io_check = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
                $tabla_io_existe = ($tabla_existe_io_check && $tabla_existe_io_check->num_rows > 0);
                
                $clave_existe = false;
                if ($tabla_io_existe) {
                    $stmt_verificar_clave = $conexion->prepare("SELECT COUNT(*) as total FROM insigniasotorgadas WHERE Codigo_Insignia = ?");
                    if ($stmt_verificar_clave) {
                        $stmt_verificar_clave->bind_param("s", $clave);
                        $stmt_verificar_clave->execute();
                        $resultado_clave = $stmt_verificar_clave->get_result();
                        if ($resultado_clave) {
                            $clave_existe = $resultado_clave->fetch_assoc()['total'] > 0;
                        }
                        $stmt_verificar_clave->close();
                    }
                }
                
                // Si la clave existe, generar una nueva
                $intentos = 0;
                while ($clave_existe && $intentos < 10) {
                    $clave = "TECNM-OFCM-" . $periodo . "-" . $tipo_codigo . "-" . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
                    if ($tabla_io_existe) {
                        $stmt_verificar_clave = $conexion->prepare("SELECT COUNT(*) as total FROM insigniasotorgadas WHERE Codigo_Insignia = ?");
                        if ($stmt_verificar_clave) {
                            $stmt_verificar_clave->bind_param("s", $clave);
                            $stmt_verificar_clave->execute();
                            $resultado_clave = $stmt_verificar_clave->get_result();
                            if ($resultado_clave) {
                                $clave_existe = $resultado_clave->fetch_assoc()['total'] > 0;
                            } else {
                                $clave_existe = false;
                            }
                            $stmt_verificar_clave->close();
                        } else {
                            $clave_existe = false;
                        }
                    } else {
                        $clave_existe = false;
                    }
                    $intentos++;
                }
            }
        
        try {
            // Verificar conexión antes de proceder
            if (!$conexion) {
                throw new Exception("No hay conexión a la base de datos");
            }
            
            // Verificar qué tabla existe para guardar
            $tabla_existe_io = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
            $tabla_existe_t = $conexion->query("SHOW TABLES LIKE 'T_insignias_otorgadas'");
            $usar_tabla_io = ($tabla_existe_io && $tabla_existe_io->num_rows > 0);
            $usar_tabla_t = ($tabla_existe_t && $tabla_existe_t->num_rows > 0);
            
            // Si no existe ninguna tabla, crear insigniasotorgadas
            if (!$usar_tabla_io && !$usar_tabla_t) {
                // Crear tabla insigniasotorgadas si no existe (sin FOREIGN KEY para evitar problemas de permisos)
                $sql_crear_tabla = "CREATE TABLE IF NOT EXISTS insigniasotorgadas (
                    ID_otorgada INT AUTO_INCREMENT PRIMARY KEY,
                    Codigo_Insignia VARCHAR(255) NOT NULL UNIQUE,
                    Destinatario INT NOT NULL,
                    Periodo_Emision INT,
                    Responsable_Emision INT,
                    Estatus INT,
                    Fecha_Emision DATE,
                    Fecha_Vencimiento DATE,
                    Fecha_Creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_codigo (Codigo_Insignia),
                    INDEX idx_destinatario (Destinatario)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                $resultado_crear = $conexion->query($sql_crear_tabla);
                if ($resultado_crear) {
                    // Verificar que la tabla se creó
                    $verificar = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
                    if ($verificar && $verificar->num_rows > 0) {
                        $usar_tabla_io = true;
                    } else {
                        throw new Exception("Error: La tabla 'insigniasotorgadas' no se pudo crear. Error MySQL: " . $conexion->error . " (Código: " . $conexion->errno . ")");
                    }
                } else {
                    throw new Exception("Error al crear tabla insigniasotorgadas. Error MySQL: " . $conexion->error . " (Código: " . $conexion->errno . ")<br><br>Posibles soluciones:<br>1. Verificar permisos del usuario MySQL (necesita CREATE TABLE)<br>2. Crear la tabla manualmente con el usuario root");
                }
            }
            
            // Insertar datos en la base de datos
            if ($usar_tabla_io) {
                // Usar insigniasotorgadas (tabla con Codigo_Insignia)
                $sql = "INSERT INTO insigniasotorgadas (
                    Codigo_Insignia, 
                    Destinatario, 
                    Periodo_Emision, 
                    Responsable_Emision,
                    Estatus, 
                    Fecha_Emision, 
                    Fecha_Vencimiento
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            } else {
                // Si solo existe T_insignias_otorgadas, crear insigniasotorgadas de todas formas
                $sql_crear_tabla = "CREATE TABLE IF NOT EXISTS insigniasotorgadas (
                    ID_otorgada INT AUTO_INCREMENT PRIMARY KEY,
                    Codigo_Insignia VARCHAR(255) NOT NULL UNIQUE,
                    Destinatario INT NOT NULL,
                    Periodo_Emision INT,
                    Responsable_Emision INT,
                    Estatus INT,
                    Fecha_Emision DATE,
                    Fecha_Vencimiento DATE,
                    Fecha_Creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_codigo (Codigo_Insignia),
                    INDEX idx_destinatario (Destinatario)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                $resultado_crear = $conexion->query($sql_crear_tabla);
                if ($resultado_crear) {
                    $verificar = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
                    if ($verificar && $verificar->num_rows > 0) {
                        $usar_tabla_io = true;
                        $sql = "INSERT INTO insigniasotorgadas (
                            Codigo_Insignia, 
                            Destinatario, 
                            Periodo_Emision, 
                            Responsable_Emision,
                            Estatus, 
                            Fecha_Emision, 
                            Fecha_Vencimiento
                        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    } else {
                        throw new Exception("Error: La tabla 'insigniasotorgadas' no se pudo crear. Error MySQL: " . $conexion->error . " (Código: " . $conexion->errno . ")");
                    }
                } else {
                    throw new Exception("Error al crear tabla insigniasotorgadas. Error MySQL: " . $conexion->error . " (Código: " . $conexion->errno . ")<br><br>Posibles soluciones:<br>1. Verificar permisos del usuario MySQL (necesita CREATE TABLE)<br>2. Crear la tabla manualmente con el usuario root");
                }
            }
            
            $stmt = $conexion->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Error en prepare: " . $conexion->error . " | SQL: " . $sql);
            }
            
            // Obtener IDs de las tablas relacionadas
            
            // 1. Manejar destinatario con la estructura real de la base de datos
            $destinatario_id = null;
            
            // Buscar por nombre completo (único campo disponible)
            if (!empty($estudiante)) {
                $sql_buscar_nombre = "SELECT ID_destinatario FROM destinatario WHERE Nombre_Completo = ? LIMIT 1";
                $stmt_nombre = $conexion->prepare($sql_buscar_nombre);
                if ($stmt_nombre) {
                    $stmt_nombre->bind_param("s", $estudiante);
                    $stmt_nombre->execute();
                    $result_nombre = $stmt_nombre->get_result();
                    if ($result_nombre && $result_nombre->num_rows > 0) {
                        $row_nombre = $result_nombre->fetch_assoc();
                        $destinatario_id = $row_nombre['ID_destinatario'];
                    }
                    $stmt_nombre->close();
                }
            }
            
            // Si existe el destinatario, actualizar todos los datos
            if ($destinatario_id) {
                $sql_update = "UPDATE destinatario SET Nombre_Completo = ?, Curp = ?, Correo = ?, Matricula = ? WHERE ID_destinatario = ?";
                $stmt_update = $conexion->prepare($sql_update);
                if ($stmt_update) {
                    $stmt_update->bind_param("ssssi", $estudiante, $curp, $correo, $matricula, $destinatario_id);
                    $stmt_update->execute();
                    $stmt_update->close();
                }
            } else {
                // Crear nuevo destinatario con todos los datos
                $sql_insert = "INSERT INTO destinatario (Nombre_Completo, Curp, Correo, Matricula, ITCentro) VALUES (?, ?, ?, ?, 1)";
                $stmt_insert = $conexion->prepare($sql_insert);
                if ($stmt_insert) {
                    $stmt_insert->bind_param("ssss", $estudiante, $curp, $correo, $matricula);
                    if ($stmt_insert->execute()) {
                        $destinatario_id = $conexion->insert_id;
                    } else {
                        throw new Exception("No se pudo crear destinatario: " . $stmt_insert->error);
                    }
                    $stmt_insert->close();
                } else {
                    throw new Exception("Error al preparar inserción de destinatario: " . $conexion->error);
                }
            }
            
            // 2. Obtener periodo_id (usar campos dinámicos detectados al inicio)
            $check_periodo_insert = $conexion->query("SHOW COLUMNS FROM periodo_emision LIKE 'id'");
            $tiene_id_periodo_insert = ($check_periodo_insert && $check_periodo_insert->num_rows > 0);
            $campo_id_periodo_insert = $tiene_id_periodo_insert ? 'id' : 'ID_periodo';
            
            $check_nombre_periodo_insert = $conexion->query("SHOW COLUMNS FROM periodo_emision LIKE 'Nombre_Periodo'");
            $tiene_nombre_periodo_insert = ($check_nombre_periodo_insert && $check_nombre_periodo_insert->num_rows > 0);
            $campo_periodo_insert = $tiene_nombre_periodo_insert ? 'Nombre_Periodo' : 'periodo';
            
            $sql_periodo_id = "SELECT $campo_id_periodo_insert as id FROM periodo_emision WHERE $campo_periodo_insert = ? LIMIT 1";
            $stmt_periodo = $conexion->prepare($sql_periodo_id);
            $periodo_id = null;
            
            if ($stmt_periodo) {
                $stmt_periodo->bind_param("s", $periodo);
                $stmt_periodo->execute();
                $result_periodo = $stmt_periodo->get_result();
                
                if ($result_periodo && $result_periodo->num_rows > 0) {
                    $row_periodo = $result_periodo->fetch_assoc();
                    $periodo_id = $row_periodo['id'];
                } else {
                    // Crear periodo si no existe
                    $sql_insert_periodo = "INSERT INTO periodo_emision ($campo_periodo_insert) VALUES (?)";
                    $stmt_insert_periodo = $conexion->prepare($sql_insert_periodo);
                    if ($stmt_insert_periodo) {
                        $stmt_insert_periodo->bind_param("s", $periodo);
                        if ($stmt_insert_periodo->execute()) {
                            $periodo_id = $conexion->insert_id;
                        } else {
                            throw new Exception("No se pudo crear periodo: " . $stmt_insert_periodo->error);
                        }
                        $stmt_insert_periodo->close();
                    }
                }
                $stmt_periodo->close();
            }
            
            // 3. Obtener responsable_id
            // Verificar estructura de responsable_emision
            $check_resp = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'id'");
            $tiene_id_resp = ($check_resp && $check_resp->num_rows > 0);
            $campo_id_resp = $tiene_id_resp ? 'id' : 'ID_responsable';
            
            $sql_resp_emision = "SELECT $campo_id_resp as id FROM responsable_emision WHERE Nombre_Completo = ? LIMIT 1";
            $stmt_resp = $conexion->prepare($sql_resp_emision);
            $responsable_id = null;
            
            if ($stmt_resp) {
                $stmt_resp->bind_param("s", $responsable);
                $stmt_resp->execute();
                $result_resp = $stmt_resp->get_result();
                
                if ($result_resp && $result_resp->num_rows > 0) {
                    $row_resp = $result_resp->fetch_assoc();
                    $responsable_id = $row_resp['id'];
                } else {
                    // Crear responsable si no existe
                    $check_adscripcion = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'Adscripcion'");
                    $tiene_adscripcion = ($check_adscripcion && $check_adscripcion->num_rows > 0);
                    
                    if ($tiene_adscripcion) {
                        $sql_insert_resp = "INSERT INTO responsable_emision (Nombre_Completo, Cargo, Adscripcion) VALUES (?, 'Responsable', 1)";
                    } else {
                        $sql_insert_resp = "INSERT INTO responsable_emision (Nombre_Completo, Cargo) VALUES (?, 'Responsable')";
                    }
                    $stmt_insert_resp = $conexion->prepare($sql_insert_resp);
                    if ($stmt_insert_resp) {
                        $stmt_insert_resp->bind_param("s", $responsable);
                        if ($stmt_insert_resp->execute()) {
                            $responsable_id = $conexion->insert_id;
                        } else {
                            throw new Exception("No se pudo crear responsable: " . $stmt_insert_resp->error);
                        }
                        $stmt_insert_resp->close();
                    }
                }
                $stmt_resp->close();
            }
            
            // Usar el estatus seleccionado
            $estatus_id = $estatus;
            
            // Validar que todos los valores necesarios estén presentes
            if (empty($clave)) {
                throw new Exception("Error: El código de la insignia está vacío");
            }
            if (empty($destinatario_id)) {
                throw new Exception("Error: No se pudo obtener o crear el destinatario. Estudiante: " . htmlspecialchars($estudiante));
            }
            if (empty($periodo_id)) {
                throw new Exception("Error: No se pudo obtener o crear el periodo. Periodo: " . htmlspecialchars($periodo));
            }
            if (empty($responsable_id)) {
                throw new Exception("Error: No se pudo obtener o crear el responsable. Responsable: " . htmlspecialchars($responsable));
            }
            if (empty($estatus_id)) {
                throw new Exception("Error: El estatus está vacío");
            }
            if (empty($fecha_otorgamiento)) {
                throw new Exception("Error: La fecha de otorgamiento está vacía");
            }
            
            // Debug: Mostrar valores que se van a insertar
            error_log("DEBUG INSERT insigniasotorgadas: clave=$clave, destinatario_id=$destinatario_id, periodo_id=$periodo_id, responsable_id=$responsable_id, estatus_id=$estatus_id, fecha_otorgamiento=$fecha_otorgamiento, fecha_autorizacion=$fecha_autorizacion");
            
            // Verificar que el statement se preparó correctamente
            if (!$stmt) {
                throw new Exception("Error crítico: No se pudo preparar el statement. Error MySQL: " . $conexion->error . " (Código: " . $conexion->errno . ")");
            }
            
            $stmt->bind_param("siiiiss", 
                $clave,
                $destinatario_id, 
                $periodo_id, 
                $responsable_id,
                $estatus_id, 
                $fecha_otorgamiento, 
                $fecha_autorizacion
            );
            
            // Asegurar que autocommit esté activado
            $conexion->autocommit(true);
            
            // Intentar ejecutar el INSERT
            $resultado_insert = $stmt->execute();
            
            if ($resultado_insert) {
                $insignia_insertada_id = $conexion->insert_id;
                
                // Debug: Verificar que se insertó correctamente
                error_log("DEBUG: Insignia insertada correctamente con ID: " . $insignia_insertada_id);
                error_log("DEBUG: Código guardado en BD: " . $clave);
                
                // Verificar que el código se guardó correctamente (verificación simple)
                if ($insignia_insertada_id > 0) {
                    // Verificar que el código existe en la BD
                    $verificar_codigo = $conexion->prepare("SELECT Codigo_Insignia FROM insigniasotorgadas WHERE ID_otorgada = ? LIMIT 1");
                    $verificar_codigo->bind_param("i", $insignia_insertada_id);
                    $verificar_codigo->execute();
                    $resultado_verificar = $verificar_codigo->get_result();
                    
                    if ($resultado_verificar && $resultado_verificar->num_rows > 0) {
                        $row_verificar = $resultado_verificar->fetch_assoc();
                        $clave_guardada = $row_verificar['Codigo_Insignia'];
                        $clave = $clave_guardada; // Usar el código que realmente se guardó
                    }
                    $verificar_codigo->close();
                }
                
                // Guardar datos en sesión para mostrar la vista completa
                $_SESSION['insignia_data'] = [
                    'codigo' => $clave,
                    'nombre' => $tipo_insignia_nombre ?: $insignia,
                    'categoria' => $categoria_nombre ?: 'Formación Integral',
                    'destinatario' => $estudiante,
                    'descripcion' => $descripcion_real,
                    'criterios' => "Para obtener esta insignia de " . ($tipo_insignia_nombre ?: $insignia) . ", el estudiante debe haber demostrado competencias específicas.",
                    'fecha_emision' => $fecha_otorgamiento,
                    'emisor' => 'TecNM / Instituto Tecnológico de San Marcos',
                    'evidencia' => $evidencia,
                    'archivo_visual' => "Insig_" . $clave . ".jpg",
                    'responsable' => $responsable,
                    'codigo_responsable' => 'TecNM-OFCM-2025-Resp001',
                    'estatus' => $estatus,
                    'periodo' => $periodo
                ];
                
                // ENVIAR NOTIFICACIÓN POR CORREO
                if (validarCorreo($correo)) {
                    // Generar URL de la imagen de la insignia
                    $nombre_insignia_para_imagen = $tipo_insignia_nombre ?: $insignia;
                    // Verificar si la función existe, si no, usar función local
                    if (function_exists('generarUrlImagenInsignia')) {
                        $url_imagen_insignia = generarUrlImagenInsignia($nombre_insignia_para_imagen);
                    } else {
                        // Función local si no está disponible
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                        $host = $_SERVER['HTTP_HOST'];
                        $base_url = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']);
                        $mapeo_imagenes = [
                            'Embajador del Arte' => 'EmbajadordelArte.png',
                            'Embajador del Deporte' => 'EmbajadordelDeporte.png',
                            'Talento Científico' => 'TalentoCientifico.png',
                            'Talento Innovador' => 'TalentoInnovador.png',
                            'Innovacion' => 'TalentoInnovador.png',
                            'Responsabilidad Social' => 'ResponsabilidadSocial.png',
                            'Formación y Actualización' => 'FormacionyActualizacion.png',
                            'Formacion y Actualizacion' => 'FormacionyActualizacion.png',
                            'Movilidad e Intercambio' => 'MovilidadeIntercambio.png'
                        ];
                        $archivo_imagen = 'insignia_default.png';
                        foreach ($mapeo_imagenes as $nombre => $archivo) {
                            if (stripos($nombre_insignia_para_imagen, $nombre) !== false || stripos($nombre, $nombre_insignia_para_imagen) !== false) {
                                $archivo_imagen = $archivo;
                                break;
                            }
                        }
                        $url_imagen_insignia = $base_url . '/imagen/Insignias/' . $archivo_imagen;
                    }
                    
                    $datos_correo = [
                        'estudiante' => $estudiante,
                        'matricula' => $matricula,
                        'curp' => $curp,
                        'nombre_insignia' => $nombre_insignia_para_imagen,
                        'categoria' => $categoria_nombre ?: 'Formación Integral',
                        'codigo_insignia' => $clave,
                        'periodo' => $periodo,
                        'fecha_otorgamiento' => $fecha_otorgamiento,
                        'responsable' => $responsable,
                        'descripcion' => $descripcion_real,
                        'url_verificacion' => generarUrlVerificacion($clave),
                        'url_imagen' => $url_imagen_insignia
                    ];
                    
                    $correo_enviado = enviarNotificacionInsigniaCompleta($correo, $datos_correo);
                    
                    // Guardar resultado del correo en sesión para mostrar en la siguiente página
                    $_SESSION['correo_enviado'] = $correo_enviado;
                    $_SESSION['correo_destinatario'] = $correo;
                }
                
                // SOLO redirigir si TODO salió bien y el código está confirmado en la BD
                header('Location: ver_insignia_completa.php?insignia=' . urlencode($clave) . '&registrado=1');
                exit();
            } else {
                // El INSERT falló - obtener el error detallado
                $error_mysql = $stmt->error;
                $codigo_error = $stmt->errno;
                $error_detalle = "Error al ejecutar INSERT en insigniasotorgadas: " . $error_mysql . " (Código MySQL: " . $codigo_error . ")";
                
                error_log("ERROR INSERT insigniasotorgadas: " . $error_detalle);
                error_log("SQL: " . $sql);
                error_log("Valores: clave=$clave, destinatario_id=$destinatario_id, periodo_id=$periodo_id, responsable_id=$responsable_id, estatus_id=$estatus_id, fecha_otorgamiento=$fecha_otorgamiento, fecha_autorizacion=$fecha_autorizacion");
                
                // Construir mensaje de error detallado
                $mensaje_error = "<strong>ERROR AL GUARDAR EN LA BASE DE DATOS</strong><br><br>";
                $mensaje_error .= "Error MySQL: " . htmlspecialchars($error_mysql) . " (Código: $codigo_error)<br><br>";
                $mensaje_error .= "<strong>SQL ejecutado:</strong><br><code>" . htmlspecialchars($sql) . "</code><br><br>";
                $mensaje_error .= "<strong>Valores intentados:</strong><br>";
                $mensaje_error .= "Código: " . htmlspecialchars($clave) . "<br>";
                $mensaje_error .= "Destinatario ID: $destinatario_id<br>";
                $mensaje_error .= "Periodo ID: $periodo_id<br>";
                $mensaje_error .= "Responsable ID: $responsable_id<br>";
                $mensaje_error .= "Estatus ID: $estatus_id<br>";
                $mensaje_error .= "Fecha Otorgamiento: " . htmlspecialchars($fecha_otorgamiento) . "<br>";
                $mensaje_error .= "Fecha Autorización: " . htmlspecialchars($fecha_autorizacion) . "<br><br>";
                $mensaje_error .= "<small>Por favor, verifica estos valores y contacta al administrador si el problema persiste.</small>";
                
                // NO hacer redirect - el error se mostrará en la página
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $mensaje_error = "Error: " . $e->getMessage();
            error_log("EXCEPCIÓN en metadatos_formulario: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            // NO hacer redirect si hay error - el error se mostrará en la página
            // Asegurarse de que no se haya enviado ningún header de redirect
            if (!headers_sent()) {
                // No hacer nada, dejar que la página muestre el error
            }
        }
        } // Cerrar el bloque de validación de duplicados
    } else {
        echo "<!-- DEBUG: Validación falló -->";
        echo "<!-- DEBUG: categoria_id: " . ($categoria_id ?: 'VACÍO') . " -->";
        echo "<!-- DEBUG: subcategoria_id: " . ($subcategoria_id ?: 'VACÍO') . " -->";
        echo "<!-- DEBUG: insignia: " . ($insignia ?: 'VACÍO') . " -->";
        echo "<!-- DEBUG: estudiante: " . ($estudiante ?: 'VACÍO') . " -->";
        echo "<!-- DEBUG: periodo: " . ($periodo ?: 'VACÍO') . " -->";
        echo "<!-- DEBUG: responsable: " . ($responsable ?: 'VACÍO') . " -->";
        echo "<!-- DEBUG: estatus: " . ($estatus ?: 'VACÍO') . " -->";
        echo "<!-- DEBUG: fecha_otorgamiento: " . ($fecha_otorgamiento ?: 'VACÍO') . " -->";
        echo "<!-- DEBUG: fecha_autorizacion: " . ($fecha_autorizacion ?: 'VACÍO') . " -->";
        echo "<!-- DEBUG: estatus_valido: " . ($estatus_valido ? 'SÍ' : 'NO') . " -->";
        
        // Construir mensaje de error detallado indicando qué campos faltan
        $campos_faltantes = [];
        if (empty($categoria_id)) $campos_faltantes[] = "Categoría";
        if (empty($subcategoria_id)) $campos_faltantes[] = "Subcategoría";
        if (empty($estudiante)) $campos_faltantes[] = "Estudiante";
        if (empty($curp)) $campos_faltantes[] = "CURP";
        if (empty($correo)) $campos_faltantes[] = "Correo";
        if (empty($matricula)) $campos_faltantes[] = "Matrícula";
        if (empty($periodo)) $campos_faltantes[] = "Periodo";
        if (empty($responsable)) $campos_faltantes[] = "Responsable";
        if (empty($estatus)) $campos_faltantes[] = "Estatus";
        if (!$estatus_valido) $campos_faltantes[] = "Estatus válido (no existe en BD)";
        if (empty($fecha_otorgamiento)) $campos_faltantes[] = "Fecha de Otorgamiento";
        if (empty($fecha_autorizacion)) $campos_faltantes[] = "Fecha de Autorización";
        
        $mensaje_error = "Por favor, completa todos los campos obligatorios.<br><br>";
        if (!empty($campos_faltantes)) {
            $mensaje_error .= "<strong>Campos faltantes o inválidos:</strong><br>";
            $mensaje_error .= "• " . implode("<br>• ", $campos_faltantes);
        } else {
            $mensaje_error .= "Verifica que todos los campos estén completos, incluyendo la selección de categoría, subcategoría y estatus válido.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metadatos - Insignias TecNM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css_profesional.css">
    <style>
        /* Estilos específicos para metadatos */
        
        /* Formulario de metadatos */
        .metadatos-form {
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.08) 0%, 
            rgba(255, 255, 255, 0.03) 100%);
          backdrop-filter: blur(30px);
          border-radius: 20px;
          padding: 40px;
          box-shadow: 
            0 15px 30px rgba(0,0,0,0.15),
            inset 0 1px 0 rgba(255,255,255,0.1);
          border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .metadatos-form h2 {
          font-size: 32px;
          font-weight: 900;
          background: linear-gradient(135deg, #ffffff 0%, #e8f2fa 25%, #4A90E2 50%, #0066CC 75%, #003366 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
          margin-bottom: 30px;
          text-align: center;
          text-shadow: 0 4px 8px rgba(0,102,204,0.3);
          border-bottom: 3px solid rgba(0, 102, 204, 0.3);
          padding-bottom: 20px;
          letter-spacing: -0.5px;
        }
        
        .form-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 25px;
        }
        
        .form-group.full-width {
          grid-column: 1 / -1;
        }
        
        .required {
          color: #ff6b6b;
        }
        
        /* Botones de acción */
        .action-buttons {
          display: flex;
          gap: 20px;
          margin-top: 40px;
          justify-content: center;
          flex-wrap: wrap;
        }
        
        .btn-metadatos {
          background: linear-gradient(135deg, 
            #1b396a 0%, 
            #3b82f6 25%, 
            #8b5cf6 50%, 
            #3b82f6 75%, 
            #1b396a 100%);
          color: white;
          border: none;
          padding: 18px 36px;
          border-radius: 16px;
          font-size: 18px;
          font-weight: 700;
          cursor: pointer;
          transition: var(--transition);
          text-transform: uppercase;
          letter-spacing: 1px;
          box-shadow: 
            0 20px 40px rgba(27, 57, 106, 0.4),
            inset 0 1px 0 rgba(255,255,255,0.2);
          border: 1px solid rgba(255,255,255,0.2);
          position: relative;
          overflow: hidden;
        }
        
        .btn-metadatos::before {
          content: '';
          position: absolute;
          top: 0;
          left: -100%;
          width: 100%;
          height: 100%;
          background: linear-gradient(90deg, 
            transparent, 
            rgba(255,255,255,0.3), 
            transparent);
          transition: left 0.6s;
        }
        
        .btn-metadatos:hover {
          transform: translateY(-3px) scale(1.02);
          box-shadow: 
            0 25px 50px rgba(27, 57, 106, 0.5),
            inset 0 1px 0 rgba(255,255,255,0.3);
        }
        
        .btn-metadatos:hover::before {
          left: 100%;
        }
        
        .btn-secondary {
          background: linear-gradient(135deg, #6c757d, #495057);
        }
        
        .btn-secondary:hover {
          box-shadow: 
            0 25px 50px rgba(108, 117, 125, 0.5),
            inset 0 1px 0 rgba(255,255,255,0.3);
        }
        
        /* Información adicional */
        .info-section {
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.06) 0%, 
            rgba(255, 255, 255, 0.02) 100%);
          backdrop-filter: blur(30px);
          border-radius: 16px;
          padding: 25px;
          margin-top: 30px;
          box-shadow: 
            0 10px 25px rgba(0,0,0,0.1),
            inset 0 1px 0 rgba(255,255,255,0.1);
          border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .info-section h3 {
          font-size: 20px;
          font-weight: 700;
          color: rgba(255, 255, 255, 0.95);
          margin-bottom: 15px;
          text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .info-section p {
          font-size: 16px;
          color: rgba(255, 255, 255, 0.8);
          line-height: 1.6;
          margin-bottom: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
          .form-grid {
            grid-template-columns: 1fr;
            gap: 20px;
          }
          
          .metadatos-form {
            padding: 30px 20px;
          }
          
          .action-buttons {
            flex-direction: column;
            align-items: center;
          }
          
          .btn-metadatos {
            width: 100%;
            max-width: 300px;
          }
        }
        
        /* ==== HEADER PROFESIONAL ==== */
        header {
          background: linear-gradient(135deg, 
            #1e3c72 0%, 
            #2a5298 50%, 
            #1e3c72 100%);
          backdrop-filter: blur(40px) saturate(180%);
          color: white;
          text-align: center;
          padding: 30px 0;
          position: relative;
          box-shadow: 
            0 8px 32px rgba(0,0,0,0.3),
            inset 0 1px 0 rgba(255,255,255,0.2);
          border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        header h1 {
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
        
        .header-content {
          display: flex;
          align-items: center;
          justify-content: center;
          max-width: 1200px;
          margin: 0 auto;
          padding: 0 20px;
          position: relative;
        }
        
        .header-logo {
          position: absolute;
          left: -260px;
          top: 50%;
          transform: translateY(-50%);
          height: 60px;
          width: auto;
          filter: brightness(0) invert(1);
          transition: all 0.3s ease;
        }
        
        .header-logo:hover {
          transform: translateY(-50%) scale(1.1);
          filter: brightness(0) invert(1) drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 50px;
            min-height: calc(100vh - 200px);
        }
        
        .main-container {
            flex: 1;
            width: 100%;
        }
        
        h1 {
            color: #002855;
            text-align: center;
            margin-bottom: 30px;
        }
        
        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding-bottom: 50px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        select, input[type="text"], input[type="date"], input[type="email"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        button {
            background: #002855;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            width: auto;
            max-width: 300px;
            margin: 20px auto;
            display: block;
        }
        
        button:hover {
            background: #1b396a;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
            color: white !important;
            padding: 15px 30px !important;
            border: none !important;
            border-radius: 8px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px !important;
            margin: 30px auto !important;
            max-width: 300px !important;
            width: 100% !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3) !important;
            position: relative !important;
            z-index: 10 !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4) !important;
            background: linear-gradient(135deg, #218838, #1e7e34) !important;
        }
        
        .btn-primary i {
            font-size: 18px !important;
            display: inline-block !important;
        }
        
        /* Modal de Error */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.7);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.show .modal-content {
            transform: scale(1);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 20px 25px;
            border-radius: 15px 15px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s ease;
        }
        
        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .modal-body {
            padding: 25px;
            text-align: center;
        }
        
        .modal-icon {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 15px;
        }
        
        .modal-message {
            font-size: 16px;
            color: #333;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        
        .modal-student {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #dc3545;
        }
        
        .modal-student strong {
            color: #dc3545;
            font-size: 18px;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
        }
        
        .modal-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
        }
        
        .modal-btn-primary {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .modal-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }
        
        .modal-btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .modal-btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .nav-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #002855;
            text-decoration: none;
            font-weight: bold;
        }
        
        .nav-link:hover {
            text-decoration: underline;
        }
        
        /* ==== PIE DE PÁGINA ==== */
        /* Footer style removed - using professional footer below */
        
        /* ==== ESTILOS PARA SELECTS ==== */
        .select-group {
            display: flex;
            gap: 10px;
            align-items: end;
        }
        
        .select-group .form-group {
            flex: 1;
        }
        
        .select-group label {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }
        
        select:focus {
            outline: none;
            border-color: #002855;
            box-shadow: 0 0 0 2px rgba(0, 40, 85, 0.1);
        }
        
        .insignia-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
            display: none;
        }
        
        /* FOOTER PROFESIONAL AZUL */
        footer {
          background: #1e3c72;
          color: white;
          padding: 40px 0;
          margin-top: auto;
          text-align: center;
          width: 100%;
          position: relative;
        }
        
        html, body {
          display: flex;
          flex-direction: column;
          min-height: 100vh;
        }
        
        body {
          flex: 1;
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
    </style>
    <script>
        // Datos de insignias disponibles
        const insigniasData = <?php echo json_encode($subcategorias_insignias); ?>;
        
        function updateSubcategorias() {
            const categoriaSelect = document.getElementById('categoria');
            const subcategoriaSelect = document.getElementById('subcategoria');
            const insigniaInfo = document.getElementById('insignia-info');
            
            // Limpiar subcategorías
            subcategoriaSelect.innerHTML = '<option value="">Selecciona una subcategoría...</option>';
            insigniaInfo.style.display = 'none';
            
            if (categoriaSelect.value) {
                const categoriaId = parseInt(categoriaSelect.value);
                
                // Filtrar subcategorías por categoría seleccionada
                const subcategoriasFiltradas = insigniasData.filter(insignia => 
                    insignia.categoria_id == categoriaId
                );
                
                // Agregar opciones de subcategorías
                subcategoriasFiltradas.forEach(insignia => {
                    const option = document.createElement('option');
                    option.value = insignia.id;
                    option.textContent = insignia.nombre_insignia;
                    option.dataset.descripcion = insignia.descripcion || 'Descripción no disponible';
                    subcategoriaSelect.appendChild(option);
                });
            }
        }
        
        function updateInsigniaInfo() {
            const subcategoriaSelect = document.getElementById('subcategoria');
            const insigniaInfo = document.getElementById('insignia-info');
            const hiddenInput = document.getElementById('insignia-hidden');
            const claveInput = document.querySelector('input[name="clave"]');
            
            if (subcategoriaSelect.value) {
                const selectedOption = subcategoriaSelect.options[subcategoriaSelect.selectedIndex];
                const descripcion = selectedOption.dataset.descripcion;
                const nombreInsignia = selectedOption.textContent;
                
                // Solo mostrar descripción si no es "Descripción no disponible"
                if (descripcion && descripcion !== 'Descripción no disponible') {
                    insigniaInfo.innerHTML = '<strong>Descripción:</strong> ' + descripcion;
                    insigniaInfo.style.display = 'block';
                } else {
                    insigniaInfo.style.display = 'none';
                }
                
                // Actualizar campo oculto con el nombre de la insignia
                hiddenInput.value = nombreInsignia;
                
                // Generar clave única automáticamente
                generarClaveUnica(nombreInsignia);
            } else {
                insigniaInfo.style.display = 'none';
                hiddenInput.value = '';
                claveInput.value = ''; // Limpiar clave si no hay subcategoría
            }
        }
        
        function generarClaveUnica(nombreInsignia) {
            const claveInput = document.querySelector('input[name="clave"]');
            const periodoSelect = document.querySelector('select[name="periodo"]');
            
            // Solo generar si el campo está vacío (no sobrescribir si el usuario ya escribió algo)
            if (!claveInput.value.trim()) {
                // Obtener período seleccionado o año actual
                let periodo = '';
                if (periodoSelect && periodoSelect.value) {
                    // Usar el value directamente y limpiar espacios
                    periodo = periodoSelect.value.trim();
                } else {
                    // Si no hay período seleccionado, usar año actual
                    periodo = new Date().getFullYear().toString();
                }
                
                // Crear código de tipo basado en el nombre de la insignia
                let tipoCodigo = '';
                const nombreLower = nombreInsignia.toLowerCase().trim();
                
                // Mapeo completo de nombres de insignias a códigos
                if (nombreLower.includes('movilidad') || nombreLower.includes('intercambio')) {
                    tipoCodigo = 'MOV';
                } else if (nombreLower.includes('deporte') || nombreLower.includes('embajador del deporte')) {
                    tipoCodigo = 'EMB';
                } else if (nombreLower.includes('arte') || nombreLower.includes('embajador del arte')) {
                    tipoCodigo = 'ART';
                } else if (nombreLower.includes('formación') || nombreLower.includes('actualización')) {
                    tipoCodigo = 'FOR';
                } else if (nombreLower.includes('científico') || nombreLower.includes('talento científico')) {
                    tipoCodigo = 'TAL';
                } else if (nombreLower.includes('innovador') || nombreLower.includes('talento innovador')) {
                    tipoCodigo = 'INN';
                } else if (nombreLower.includes('responsabilidad') || nombreLower.includes('social')) {
                    tipoCodigo = 'SOC';
                } else if (nombreLower.includes('liderazgo') || nombreLower.includes('estudiantil')) {
                    tipoCodigo = 'LE';
                } else if (nombreLower.includes('emprendimiento')) {
                    tipoCodigo = 'EMP';
                } else if (nombreLower.includes('sustentabilidad')) {
                    tipoCodigo = 'SU';
                } else {
                    // Tomar primeras 3 letras del nombre (sin espacios ni caracteres especiales)
                    tipoCodigo = nombreInsignia.replace(/[^A-Za-z]/g, '').substring(0, 3).toUpperCase();
                    if (tipoCodigo.length < 3) tipoCodigo = 'INS';
                }
                
                // Generar número aleatorio de 3 dígitos
                const numero = Math.floor(Math.random() * 900) + 100; // 100-999
                
                // Crear clave única: TECNM-OFCM-[PERIODO]-[TIPO]-[NUMERO]
                const claveUnica = `TECNM-OFCM-${periodo}-${tipoCodigo}-${numero}`;
                
                // Asignar al campo
                claveInput.value = claveUnica;
            }
        }
        
        // Validar formulario antes de enviar
        function validarFormulario() {
            const categoria = document.getElementById('categoria').value;
            const subcategoria = document.getElementById('subcategoria').value;
            const insignia = document.getElementById('insignia-hidden').value;
            const estudiante = document.querySelector('input[name="estudiante"]').value;
            const curp = document.querySelector('input[name="curp"]').value;
            const correo = document.querySelector('input[name="correo"]').value;
            const matricula = document.querySelector('input[name="matricula"]').value;
            const periodo = document.querySelector('select[name="periodo"]').value;
            const responsable = document.querySelector('select[name="responsable"]').value;
            const estatus = document.querySelector('select[name="estatus"]').value;
            
            // Validar campos obligatorios
            if (!categoria) {
                alert('⚠️ Por favor selecciona una Categoría');
                document.getElementById('categoria').focus();
                return false;
            }
            
            if (!subcategoria) {
                alert('⚠️ Por favor selecciona una Subcategoría');
                document.getElementById('subcategoria').focus();
                return false;
            }
            
            if (!insignia) {
                alert('⚠️ Por favor selecciona una Subcategoría para que se asigne la insignia');
                document.getElementById('subcategoria').focus();
                return false;
            }
            
            if (!estudiante.trim()) {
                alert('⚠️ Por favor ingresa el nombre del Estudiante Destinatario');
                document.querySelector('input[name="estudiante"]').focus();
                return false;
            }
            
            if (!curp.trim()) {
                alert('⚠️ Por favor ingresa la CURP del estudiante');
                document.querySelector('input[name="curp"]').focus();
                return false;
            }
            
            if (!correo.trim()) {
                alert('⚠️ Por favor ingresa el Correo Electrónico del estudiante');
                document.querySelector('input[name="correo"]').focus();
                return false;
            }
            
            if (!matricula.trim()) {
                alert('⚠️ Por favor ingresa la Matrícula del estudiante');
                document.querySelector('input[name="matricula"]').focus();
                return false;
            }
            
            if (!periodo) {
                alert('⚠️ Por favor selecciona un Periodo de Emisión');
                document.querySelector('select[name="periodo"]').focus();
                return false;
            }
            
            if (!responsable) {
                alert('⚠️ Por favor selecciona un Responsable de Emisión');
                document.querySelector('select[name="responsable"]').focus();
                return false;
            }
            
            if (!estatus) {
                alert('⚠️ Por favor selecciona un Estatus del Reconocimiento');
                document.querySelector('select[name="estatus"]').focus();
                return false;
            }
            
            // Si todo está bien, permitir el envío
            return true;
        }
        
        // Inicializar cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            updateSubcategorias();
            
            // Si hay una subcategoría seleccionada, generar la clave
            const subcategoriaSelect = document.getElementById('subcategoria');
            if (subcategoriaSelect && subcategoriaSelect.value) {
                updateInsigniaInfo();
            }
            
            // Escuchar cambios en el período para regenerar la clave si es necesario
            const periodoSelect = document.querySelector('select[name="periodo"]');
            if (periodoSelect) {
                periodoSelect.addEventListener('change', function() {
                    const subcategoriaSelect = document.getElementById('subcategoria');
                    if (subcategoriaSelect && subcategoriaSelect.value) {
                        const selectedOption = subcategoriaSelect.options[subcategoriaSelect.selectedIndex];
                        const nombreInsignia = selectedOption.textContent;
                        generarClaveUnica(nombreInsignia);
                    }
                });
            }
        });
    </script>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="imagen/logo.png" alt="TecNM Logo" class="header-logo">
            <h1>Insignias TecNM</h1>
        </div>
    </header>
    
    <div class="main-container">
        <div class="card">
            <div class="card-title">🎖️ Metadatos</div>
            
            <?php
            // Obtener información del usuario actual
            $usuario_actual = obtenerUsuarioActual();
            $nombre_completo_usuario = '';
            if ($usuario_actual) {
                $nombre_completo_usuario = trim($usuario_actual['Nombre'] . ' ' . $usuario_actual['Apellido_Paterno'] . ' ' . $usuario_actual['Apellido_Materno']);
            }
            ?>
            
            <?php if (!empty($nombre_completo_usuario)): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                    <strong>Bienvenido, <?php echo htmlspecialchars($nombre_completo_usuario); ?></strong>
                </div>
            <?php endif; ?>
            
            <a href="modulo_de_administracion.php" class="btn btn-secondary" style="margin-bottom: 30px;">
                <i class="fas fa-arrow-left"></i>
                Volver al Módulo
            </a>
            
            <?php if (isset($mensaje_exito)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $mensaje_exito; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($mensaje_error)): ?>
                <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 20px; border: 2px solid #f5c6cb; border-radius: 8px; margin: 20px 0; font-size: 16px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>ERROR AL REGISTRAR LA INSIGNIA:</strong><br><br>
                    <?php echo nl2br(htmlspecialchars($mensaje_error)); ?>
                    <br><br>
                    <small style="color: #856404;">Por favor, verifica los datos e intenta nuevamente. Si el problema persiste, contacta al administrador del sistema.</small>
                </div>
            <?php endif; ?>
            
            <div class="metadatos-form">
                <h2><i class="fas fa-medal"></i> Registro de Metadatos de Insignia</h2>
                
                <form method="POST" action="" class="form-grid" onsubmit="return validarFormulario()">
            <div class="form-group full-width">
                <label>Insignia Disponible:</label>
                <div class="select-group">
                    <div class="form-group">
                        <label>Categoría:</label>
                        <select id="categoria" name="categoria" onchange="updateSubcategorias()" required>
                            <option value="">Selecciona una categoría...</option>
                            <?php foreach ($categorias_insignias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>">
                                    <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
            <div class="form-group">
                        <label>Subcategoría:</label>
                        <select id="subcategoria" name="subcategoria" onchange="updateInsigniaInfo()" required>
                            <option value="">Selecciona una subcategoría...</option>
                        </select>
                    </div>
                </div>
                
                <div id="insignia-info" class="insignia-info"></div>
                
                <!-- Campo oculto para enviar el nombre de la insignia -->
                <input type="hidden" id="insignia-hidden" name="insignia" value="">
            </div>

            <div class="form-group">
                <label>Estudiante Destinatario:</label>
                <input type="text" name="estudiante" placeholder="Ej: Juan Pérez, Ana López" required>
            </div>

            <div class="form-group">
                <label>CURP:</label>
                <input type="text" name="curp" placeholder="Ej: PERJ800101HDFRGN01" maxlength="18" required>
            </div>

            <div class="form-group">
                <label>Correo Electrónico:</label>
                <input type="email" name="correo" placeholder="Ej: estudiante@tecnm.mx" required>
            </div>

            <div class="form-group">
                <label>Matrícula:</label>
                <input type="text" name="matricula" placeholder="Ej: 2024001" required>
            </div>

            <div class="form-group">
                <label>Periodo de Emisión:</label>
                <select name="periodo" required>
                    <option value="">Selecciona un periodo...</option>
                    <?php foreach ($periodos_emision as $periodo): ?>
                        <option value="<?php echo htmlspecialchars($periodo['periodo']); ?>">
                            <?php echo htmlspecialchars($periodo['periodo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Responsable de Emisión:</label>
                <select name="responsable" required>
                    <option value="">Selecciona un responsable...</option>
                    <?php foreach ($responsables_emision as $responsable): ?>
                        <option value="<?php echo htmlspecialchars($responsable['nombre_completo']); ?>">
                            <?php echo htmlspecialchars($responsable['nombre_completo']); ?> - <?php echo htmlspecialchars($responsable['cargo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Estatus del Reconocimiento:</label>
                <select name="estatus" required>
                    <option value="">Selecciona un estatus...</option>
                    <?php foreach ($estatus_disponibles as $estatus): ?>
                        <option value="<?php echo htmlspecialchars($estatus['id']); ?>">
                            <?php echo htmlspecialchars($estatus['nombre_estatus']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Clave Única de Insignia:</label>
                <input type="text" name="clave" placeholder="Ej: TECNM-OFCM-2025-001">
            </div>

            <div class="form-group">
                <label>Fecha de Otorgamiento:</label>
                <input type="date" name="fecha_otorgamiento" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label>Fecha de Autorización:</label>
                <input type="date" name="fecha_autorizacion" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group full-width">
                <label>Descripción del Reconocimiento:</label>
                <textarea name="descripcion" rows="4" placeholder="  "></textarea>
            </div>

            <div class="form-group full-width">
                <label>Evidencia/Referencia:</label>
                <input type="text" name="evidencia" placeholder="/evidencias/certificado.pdf">
            </div>

            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-medal"></i>
                Registrar Reconocimiento
            </button>
            <button type="button" class="btn-primary" style="background:#0d6efd; border:none;" onclick="abrirModalFirmaSAT()">
                <i class="fas fa-file-signature"></i>
                Firma electrónica (SAT)
            </button>
            </div>
        </form>
    </div>
    
    <!-- Modal de Error para Duplicados -->
    <?php if (isset($mensaje_error_modal)): ?>
    <div class="modal-overlay show" id="errorModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-exclamation-triangle"></i>
                    Insignia Duplicada
                </h3>
                <button class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="modal-message">
                    No se puede otorgar la misma insignia dos veces al mismo estudiante.
                </div>
                <div class="modal-student">
                    <strong><?php echo htmlspecialchars($mensaje_error_modal['estudiante']); ?></strong><br>
                    <span style="color: #666; font-size: 14px;">
                        Ya tiene una insignia de: <strong><?php echo htmlspecialchars($mensaje_error_modal['tipo_insignia']); ?></strong>
                    </span>
                </div>
                <div class="modal-actions">
                    <button class="modal-btn modal-btn-primary" onclick="closeModal()">
                        <i class="fas fa-check"></i>
                        Entendido
                    </button>
                    <button class="modal-btn modal-btn-secondary" onclick="window.location.reload()">
                        <i class="fas fa-redo"></i>
                        Nuevo Intento
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        // Función para cerrar el modal
        function closeModal() {
            const modal = document.getElementById('errorModal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        }
        
        // Cerrar modal al hacer clic fuera de él
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('errorModal');
            if (modal && e.target === modal) {
                closeModal();
            }
        });
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Auto-cerrar después de 10 segundos (opcional)
        <?php if (isset($mensaje_error_modal)): ?>
        setTimeout(() => {
            closeModal();
        }, 10000);
        <?php endif; ?>
    </script>

    <!-- Modal Firma Electrónica (SAT) para firmar tras el registro -->
    <div id="modalFirmaSAT" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div style="max-width: 620px; margin: 60px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.4);">
            <div style="background: linear-gradient(135deg, #1b396a, #002855); color: white; padding: 16px 20px; display:flex; align-items:center; justify-content:space-between;">
                <div style="font-weight: 800;">🔏 Firma electrónica (e.firma SAT)</div>
                <button onclick="cerrarModalFirmaSAT()" style="background: transparent; border: none; color: white; font-size: 20px; cursor: pointer;">✕</button>
            </div>
            <form id="formEFirmaSat" method="POST" action="firmar_certificado.php" enctype="multipart/form-data" style="padding: 20px;">
                <!-- Campos de contexto (se llenan con JS usando los valores del formulario o sesión) -->
                <input type="hidden" name="codigo_insignia" id="ef_codigo" value="<?php echo isset($_SESSION['insignia_data']['codigo']) ? htmlspecialchars($_SESSION['insignia_data']['codigo']) : ''; ?>">
                <input type="hidden" name="destinatario" id="ef_destinatario">
                <input type="hidden" name="nombre_insignia" id="ef_nombre_insignia">
                <input type="hidden" name="fecha_emision" id="ef_fecha_emision">
                <input type="hidden" name="responsable" id="ef_responsable">
                <input type="hidden" name="cargo" value="RESPONSABLE DE EMISIÓN">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom: 14px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#1b396a;">Certificado (.cer)</label>
                        <input type="file" name="certificado" accept=".cer" required style="width:100%; padding:10px; border:1px solid #e0e0e0; border-radius:8px;">
                    </div>
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#1b396a;">Clave privada (.key)</label>
                        <input type="file" name="clave" accept=".key" required style="width:100%; padding:10px; border:1px solid #e0e0e0; border-radius:8px;">
                    </div>
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#1b396a;">Contraseña de la e.firma</label>
                    <input type="password" name="contrasena" required style="width:100%; padding:10px; border:1px solid #e0e0e0; border-radius:8px;">
                </div>
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:10px;">
                    <button type="button" onclick="cerrarModalFirmaSAT()" class="btn btn-secondary" style="background:#6c757d; color:white; border:none; padding:12px 20px; border-radius:8px; min-width:160px; font-weight:600; display:inline-flex; align-items:center; justify-content:center;">Cancelar</button>
                    <button type="submit" class="btn-primary" style="background:#0d6efd; border:none; padding:12px 20px; min-width:180px;">Firmar y guardar</button>
                </div>
                <p style="margin-top:10px; color:#6B7280; font-size:13px;">Se firmará el certificado con los datos capturados. Los archivos se usan únicamente para generar la firma.</p>
            </form>
        </div>
    </div>

    <script>
      // Abre/cierra modal
      function abrirModalFirmaSAT(){
        // Tomar valores del formulario principal sin alterar su envío
        const form = document.querySelector('.metadatos-form form');
        const getVal = (name) => (form.querySelector(`[name="${name}"]`)||{}).value || '';
        
        // Obtener código de insignia desde el campo hidden (ya tiene valor de sesión) o del formulario
        const codigoHidden = document.getElementById('ef_codigo').value;
        const codigoForm = getVal('clave');
        if (!codigoHidden && codigoForm) {
            document.getElementById('ef_codigo').value = codigoForm;
        }
        
        // Llenar campos ocultos
        document.getElementById('ef_destinatario').value = getVal('estudiante');
        // Nombre de la insignia desde el hidden que se actualiza al elegir subcategoría
        document.getElementById('ef_nombre_insignia').value = document.getElementById('insignia-hidden').value || 'Insignia TecNM';
        document.getElementById('ef_fecha_emision').value = getVal('fecha_otorgamiento');
        document.getElementById('ef_responsable').value = getVal('responsable');
        document.getElementById('modalFirmaSAT').style.display = 'block';
      }
      function cerrarModalFirmaSAT(){
        document.getElementById('modalFirmaSAT').style.display = 'none';
      }

      // Si se registró correctamente (mensaje_exito existe), sugerir firmar
      <?php if (isset($mensaje_exito)): ?>
      setTimeout(() => {
        // Ofrecer abrir el modal automáticamente
        if (confirm('Registro exitoso. ¿Deseas firmar electrónicamente este certificado ahora?')) {
            abrirModalFirmaSAT();
        }
      }, 300);
      <?php endif; ?>
    </script>

  <!-- FOOTER AZUL PROFESIONAL -->
  <footer>
    <div class="footer-content">
      <div class="copyright">
        <p>Copyright 2025 - TecNM</p>
        <p>Ultima actualización - Octubre 2025</p>
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