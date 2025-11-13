<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=sesion_invalida');
    exit();
}

require_once 'conexion.php';

// Obtener datos del usuario logueado
$correo_usuario = $_SESSION['correo'] ?? '';
$nombre_usuario = $_SESSION['nombre'] ?? '';
$apellido_usuario = $_SESSION['apellido_paterno'] ?? '';

// Obtener rol del usuario
$rol_usuario = $_SESSION['rol'] ?? 'Estudiante';

// Verificar si hay búsqueda específica
$busqueda = $_GET['buscar'] ?? '';

// Verificar qué tablas existen (pueden existir ambas)
// Las nuevas insignias se guardan en insigniasotorgadas
$usar_tabla_t = false;
$usar_tabla_i = false;

try {
    $tabla_existe_t = $conexion->query("SHOW TABLES LIKE 'T_insignias_otorgadas'");
    if ($tabla_existe_t && $tabla_existe_t->num_rows > 0) {
        $usar_tabla_t = true;
    }
} catch (Exception $e) {
    // Si hay error, no usar T_insignias_otorgadas
}

try {
    $tabla_existe_i = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
    if ($tabla_existe_i && $tabla_existe_i->num_rows > 0) {
        $usar_tabla_i = true;
    }
} catch (Exception $e) {
    // Si hay error, no usar insigniasotorgadas
}

// Detectar estructura dinámica de las tablas para JOINs correctos
$check_destinatario_id = $conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
$tiene_id_destinatario = ($check_destinatario_id && $check_destinatario_id->num_rows > 0);
$campo_id_destinatario = $tiene_id_destinatario ? 'id' : 'ID_destinatario';

$check_responsable_id = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'id'");
$tiene_id_responsable = ($check_responsable_id && $check_responsable_id->num_rows > 0);
$campo_id_responsable = $tiene_id_responsable ? 'id' : 'ID_responsable';

// Consulta básica para obtener las insignias otorgadas usando la estructura actual
// IMPORTANTE: Consultar AMBAS tablas si existen y combinar resultados
$insignias = [];

if (!empty($busqueda)) {
    // Modo búsqueda: mostrar solo lo que se busque
    if ($usar_tabla_t) {
        // Usar T_insignias_otorgadas con JOIN a T_insignias y tipo_insignia
        $sql = "
            SELECT 
                tio.id,
                CONCAT(ti.id, '-', pe.Nombre_Periodo) as clave_insignia,
                tio.Fecha_Emision as fecha_otorgamiento,
                'Certificación oficial' as evidencia,
                d.Nombre_Completo as destinatario,
                COALESCE(d.Matricula, 'No especificada') as Matricula,
                COALESCE(ti.Programa, 'Programa no especificado') as Programa,
                COALESCE(tin.Nombre_Insignia, 'Insignia TecNM') as nombre_insignia,
                CASE 
                    WHEN tin.Nombre_Insignia LIKE '%Deporte%' OR tin.Nombre_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN tin.Nombre_Insignia LIKE '%Científico%' OR tin.Nombre_Insignia LIKE '%Innovación%' OR tin.Nombre_Insignia LIKE '%Formación%' THEN 'Desarrollo Académico'
                    WHEN tin.Nombre_Insignia LIKE '%Arte%' OR tin.Nombre_Insignia LIKE '%Social%' OR tin.Nombre_Insignia LIKE '%Movilidad%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria,
                COALESCE(itc.Nombre_itc, 'TecNM') as institucion,
                pe.Nombre_Periodo as periodo,
                COALESCE(e.Nombre_Estatus, 'Activo') as estatus,
                'Sistema' as responsable,
                'Administrador' as cargo,
                NULL as firma_digital_base64,
                NULL as hash_verificacion,
                NULL as certificado_info,
                NULL as fecha_firma
            FROM T_insignias_otorgadas tio
            LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
            LEFT JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
            LEFT JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
            LEFT JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
            LEFT JOIN estatus e ON tio.Id_Estatus = e.id
            LEFT JOIN it_centros itc ON ti.Propone_Insignia = itc.id
            WHERE d.Nombre_Completo LIKE ?
            ORDER BY tio.Fecha_Emision DESC
        ";
    } else {
        // Usar insigniasotorgadas (estructura antigua)
        $sql = "
            SELECT 
                io.ID_otorgada as id,
                io.Codigo_Insignia as clave_insignia,
                io.Fecha_Emision as fecha_otorgamiento,
                'Certificación oficial' as evidencia,
                d.Nombre_Completo as destinatario,
                'No especificada' as Matricula,
                'Programa no especificado' as Programa,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                    WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                    WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                    WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                    WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                    ELSE 'Insignia TecNM'
                END as nombre_insignia,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                    WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria,
                'TecNM' as institucion,
                '2025-1' as periodo,
                'Activo' as estatus,
                COALESCE(re.Nombre_Completo, 'Sistema') as responsable,
                COALESCE(re.Cargo, 'Administrador') as cargo,
                io.firma_digital_base64,
                io.hash_verificacion,
                io.certificado_info,
                io.fecha_firma
            FROM insigniasotorgadas io
            LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
            LEFT JOIN responsable_emision re ON io.Responsable_Emision = re." . $campo_id_responsable . "
            WHERE d.Nombre_Completo LIKE ?
            ORDER BY io.Fecha_Emision DESC
        ";
    }
    $filtro_por_busqueda = true;
} elseif ($rol_usuario === 'Admin' || $rol_usuario === 'Administrador' || $rol_usuario === 'SuperUsuario') {
    // Modo administrador: mostrar TODAS las insignias
    if ($usar_tabla_t) {
        // Usar T_insignias_otorgadas
        $sql = "
            SELECT 
                tio.id,
                CONCAT(ti.id, '-', pe.Nombre_Periodo) as clave_insignia,
                tio.Fecha_Emision as fecha_otorgamiento,
                'Certificación oficial' as evidencia,
                COALESCE(d.Nombre_Completo, 'Destinatario no especificado') as destinatario,
                COALESCE(d.Matricula, 'No especificada') as Matricula,
                COALESCE(ti.Programa, 'Programa no especificado') as Programa,
                COALESCE(tin.Nombre_Insignia, 'Insignia TecNM') as nombre_insignia,
                CASE 
                    WHEN tin.Nombre_Insignia LIKE '%Deporte%' OR tin.Nombre_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN tin.Nombre_Insignia LIKE '%Científico%' OR tin.Nombre_Insignia LIKE '%Innovación%' OR tin.Nombre_Insignia LIKE '%Formación%' THEN 'Desarrollo Académico'
                    WHEN tin.Nombre_Insignia LIKE '%Arte%' OR tin.Nombre_Insignia LIKE '%Social%' OR tin.Nombre_Insignia LIKE '%Movilidad%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria,
                COALESCE(itc.Nombre_itc, 'TecNM') as institucion,
                pe.Nombre_Periodo as periodo,
                COALESCE(e.Nombre_Estatus, 'Activo') as estatus,
                'Sistema' as responsable,
                'Administrador' as cargo,
                NULL as firma_digital_base64,
                NULL as hash_verificacion,
                NULL as certificado_info,
                NULL as fecha_firma
            FROM T_insignias_otorgadas tio
            LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
            LEFT JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
            LEFT JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
            LEFT JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
            LEFT JOIN estatus e ON tio.Id_Estatus = e.id
            LEFT JOIN it_centros itc ON ti.Propone_Insignia = itc.id
            ORDER BY tio.Fecha_Emision DESC
        ";
    } else {
        // Usar insigniasotorgadas
        $sql = "
            SELECT 
                io.ID_otorgada as id,
                io.Codigo_Insignia as clave_insignia,
                io.Fecha_Emision as fecha_otorgamiento,
                'Certificación oficial' as evidencia,
                COALESCE(d.Nombre_Completo, 'Destinatario no especificado') as destinatario,
                COALESCE(d.Matricula, 'No especificada') as Matricula,
                'Programa no especificado' as Programa,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                    WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                    WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                    WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                    WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                    ELSE 'Insignia TecNM'
                END as nombre_insignia,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                    WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria,
                'TecNM' as institucion,
                '2025-1' as periodo,
                'Activo' as estatus,
                COALESCE(re.Nombre_Completo, 'Sistema') as responsable,
                COALESCE(re.Cargo, 'Administrador') as cargo,
                io.firma_digital_base64,
                io.hash_verificacion,
                io.certificado_info,
                io.fecha_firma
            FROM insigniasotorgadas io
            LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
            LEFT JOIN responsable_emision re ON io.Responsable_Emision = re." . $campo_id_responsable . "
            ORDER BY io.Fecha_Emision DESC
        ";
    }
    $filtro_por_busqueda = false;
    $filtro_por_correo = false;
} else {
    // Filtrar por nombre del usuario
    if ($usar_tabla_t) {
        // Usar T_insignias_otorgadas
        $sql = "
            SELECT 
                tio.id,
                CONCAT(ti.id, '-', pe.Nombre_Periodo) as clave_insignia,
                tio.Fecha_Emision as fecha_otorgamiento,
                'Certificación oficial' as evidencia,
                d.Nombre_Completo as destinatario,
                COALESCE(d.Matricula, 'No especificada') as Matricula,
                COALESCE(ti.Programa, 'Programa no especificado') as Programa,
                COALESCE(tin.Nombre_Insignia, 'Insignia TecNM') as nombre_insignia,
                CASE 
                    WHEN tin.Nombre_Insignia LIKE '%Deporte%' OR tin.Nombre_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN tin.Nombre_Insignia LIKE '%Científico%' OR tin.Nombre_Insignia LIKE '%Innovación%' OR tin.Nombre_Insignia LIKE '%Formación%' THEN 'Desarrollo Académico'
                    WHEN tin.Nombre_Insignia LIKE '%Arte%' OR tin.Nombre_Insignia LIKE '%Social%' OR tin.Nombre_Insignia LIKE '%Movilidad%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria,
                COALESCE(itc.Nombre_itc, 'TecNM') as institucion,
                pe.Nombre_Periodo as periodo,
                COALESCE(e.Nombre_Estatus, 'Activo') as estatus,
                'Sistema' as responsable,
                'Administrador' as cargo,
                NULL as firma_digital_base64,
                NULL as hash_verificacion,
                NULL as certificado_info,
                NULL as fecha_firma
            FROM T_insignias_otorgadas tio
            LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
            LEFT JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
            LEFT JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
            LEFT JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
            LEFT JOIN estatus e ON tio.Id_Estatus = e.id
            LEFT JOIN it_centros itc ON ti.Propone_Insignia = itc.id
            WHERE d.Nombre_Completo LIKE ?
            ORDER BY tio.Fecha_Emision DESC
        ";
    } else {
        // Usar insigniasotorgadas
        $sql = "
            SELECT 
                io.ID_otorgada as id,
                io.Codigo_Insignia as clave_insignia,
                io.Fecha_Emision as fecha_otorgamiento,
                'Certificación oficial' as evidencia,
                d.Nombre_Completo as destinatario,
                'No especificada' as Matricula,
                'Programa no especificado' as Programa,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                    WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                    WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                    WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                    WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                    ELSE 'Insignia TecNM'
                END as nombre_insignia,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                    WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria,
                'TecNM' as institucion,
                '2025-1' as periodo,
                'Activo' as estatus,
                COALESCE(re.Nombre_Completo, 'Sistema') as responsable,
                COALESCE(re.Cargo, 'Administrador') as cargo,
                io.firma_digital_base64,
                io.hash_verificacion,
                io.certificado_info,
                io.fecha_firma
            FROM insigniasotorgadas io
            LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
            LEFT JOIN responsable_emision re ON io.Responsable_Emision = re." . $campo_id_responsable . "
            WHERE d.Nombre_Completo LIKE ?
            ORDER BY io.Fecha_Emision DESC
        ";
    }
    $filtro_por_correo = false;
    $filtro_por_busqueda = false;
}

// Validar que se haya determinado qué tabla usar
if (!$usar_tabla_t && !$usar_tabla_i) {
    die('Error: No se encontró ninguna tabla de insignias otorgadas. Verifica que exista T_insignias_otorgadas o insigniasotorgadas en la base de datos.');
}

// Función auxiliar para ejecutar consulta y obtener resultados
function ejecutarConsulta($conexion, $sql, $params = []) {
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        error_log("Error al preparar consulta: " . $conexion->error);
        return [];
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Error al ejecutar consulta: " . $stmt->error);
        $stmt->close();
        return [];
    }
    
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

// Consultar ambas tablas si existen y combinar resultados
$insignias_combinadas = [];

// 1. Consultar T_insignias_otorgadas si existe
if ($usar_tabla_t && isset($sql)) {
    $params = [];
    if ($filtro_por_busqueda) {
        $params[] = "%$busqueda%";
    } elseif ($rol_usuario !== 'Admin' && $rol_usuario !== 'Administrador' && $rol_usuario !== 'SuperUsuario') {
        $params[] = "%$nombre_usuario $apellido_usuario%";
    }
    $insignias_t = ejecutarConsulta($conexion, $sql, $params);
    $insignias_combinadas = array_merge($insignias_combinadas, $insignias_t);
}

// 2. Consultar insigniasotorgadas si existe (donde se guardan las nuevas insignias)
if ($usar_tabla_i) {
    // Construir SQL para insigniasotorgadas
    if (!empty($busqueda)) {
        $sql_i = "
            SELECT 
                io.ID_otorgada as id,
                io.Codigo_Insignia as clave_insignia,
                io.Fecha_Emision as fecha_otorgamiento,
                'Certificación oficial' as evidencia,
                d.Nombre_Completo as destinatario,
                COALESCE(d.Matricula, 'No especificada') as Matricula,
                'Programa no especificado' as Programa,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                    WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                    WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                    WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                    WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                    ELSE 'Insignia TecNM'
                END as nombre_insignia,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                    WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria,
                'TecNM' as institucion,
                '2025-1' as periodo,
                'Activo' as estatus,
                COALESCE(re.Nombre_Completo, 'Sistema') as responsable,
                COALESCE(re.Cargo, 'Administrador') as cargo,
                io.firma_digital_base64,
                io.hash_verificacion,
                io.certificado_info,
                io.fecha_firma
            FROM insigniasotorgadas io
            LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
            LEFT JOIN responsable_emision re ON io.Responsable_Emision = re." . $campo_id_responsable . "
            WHERE d.Nombre_Completo LIKE ?
            ORDER BY io.Fecha_Emision DESC
        ";
        $params_i = ["%$busqueda%"];
    } elseif ($rol_usuario === 'Admin' || $rol_usuario === 'Administrador' || $rol_usuario === 'SuperUsuario') {
        $sql_i = "
            SELECT 
                io.ID_otorgada as id,
                io.Codigo_Insignia as clave_insignia,
                io.Fecha_Emision as fecha_otorgamiento,
                'Certificación oficial' as evidencia,
                COALESCE(d.Nombre_Completo, 'Destinatario no especificado') as destinatario,
                COALESCE(d.Matricula, 'No especificada') as Matricula,
                'Programa no especificado' as Programa,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                    WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                    WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                    WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                    WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                    ELSE 'Insignia TecNM'
                END as nombre_insignia,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                    WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria,
                'TecNM' as institucion,
                '2025-1' as periodo,
                'Activo' as estatus,
                COALESCE(re.Nombre_Completo, 'Sistema') as responsable,
                COALESCE(re.Cargo, 'Administrador') as cargo,
                io.firma_digital_base64,
                io.hash_verificacion,
                io.certificado_info,
                io.fecha_firma
            FROM insigniasotorgadas io
            LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
            LEFT JOIN responsable_emision re ON io.Responsable_Emision = re." . $campo_id_responsable . "
            ORDER BY io.Fecha_Emision DESC
        ";
        $params_i = [];
    } else {
        $sql_i = "
            SELECT 
                io.ID_otorgada as id,
                io.Codigo_Insignia as clave_insignia,
                io.Fecha_Emision as fecha_otorgamiento,
                'Certificación oficial' as evidencia,
                d.Nombre_Completo as destinatario,
                'No especificada' as Matricula,
                'Programa no especificado' as Programa,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                    WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                    WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                    WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                    WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                    ELSE 'Insignia TecNM'
                END as nombre_insignia,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                    WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria,
                'TecNM' as institucion,
                '2025-1' as periodo,
                'Activo' as estatus,
                COALESCE(re.Nombre_Completo, 'Sistema') as responsable,
                COALESCE(re.Cargo, 'Administrador') as cargo,
                io.firma_digital_base64,
                io.hash_verificacion,
                io.certificado_info,
                io.fecha_firma
            FROM insigniasotorgadas io
            LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
            LEFT JOIN responsable_emision re ON io.Responsable_Emision = re." . $campo_id_responsable . "
            WHERE d.Nombre_Completo LIKE ?
            ORDER BY io.Fecha_Emision DESC
        ";
        $params_i = ["%$nombre_usuario $apellido_usuario%"];
    }
    
    $insignias_i = ejecutarConsulta($conexion, $sql_i, $params_i);
    $insignias_combinadas = array_merge($insignias_combinadas, $insignias_i);
}

// Ordenar todas las insignias por fecha de emisión (más recientes primero)
usort($insignias_combinadas, function($a, $b) {
    $fecha_a = strtotime($a['fecha_otorgamiento'] ?? '1970-01-01');
    $fecha_b = strtotime($b['fecha_otorgamiento'] ?? '1970-01-01');
    return $fecha_b - $fecha_a; // Orden descendente
});

$insignias = $insignias_combinadas;

// Función para formatear fechas
function formatearFecha($fecha) {
    if (empty($fecha)) return 'No especificada';
    return date('d/m/Y', strtotime($fecha));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Insignias - TecNM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css_profesional.css">
    <style>
        /* Estilos específicos para historial de insignias */
        
        /* Header personalizado */
        .historial-header {
          background: linear-gradient(135deg, 
            rgba(27, 57, 106, 0.95) 0%, 
            rgba(44, 82, 130, 0.9) 25%, 
            rgba(59, 130, 246, 0.85) 50%, 
            rgba(44, 82, 130, 0.9) 75%, 
            rgba(27, 57, 106, 0.95) 100%);
          backdrop-filter: blur(40px) saturate(180%);
          color: white;
          padding: 40px 30px;
          text-align: center;
          position: relative;
          box-shadow: 
            0 8px 32px rgba(0,0,0,0.3),
            inset 0 1px 0 rgba(255,255,255,0.2);
          border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .historial-header h1 {
          font-size: 2.8rem;
          margin-bottom: 12px;
          font-weight: 800;
          letter-spacing: -0.02em;
          text-shadow: 0 4px 8px rgba(0,0,0,0.4);
          background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 50%, #ffffff 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
        }
        
        .historial-header p {
          font-size: 1.2rem;
          opacity: 0.9;
          font-weight: 400;
          text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .back-button {
          position: absolute;
          left: 30px;
          top: 50%;
          transform: translateY(-50%);
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
          color: white;
          border: 1px solid rgba(255, 255, 255, 0.2);
          padding: 14px 24px;
          border-radius: 30px;
          font-size: 15px;
          font-weight: 600;
          cursor: pointer;
          text-decoration: none;
          display: flex;
          align-items: center;
          gap: 10px;
          transition: var(--transition);
          backdrop-filter: blur(10px);
          box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .back-button:hover {
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.1));
          transform: translateY(-50%) translateX(-3px);
          text-decoration: none;
          color: white;
          box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }
        
        /* Sección de búsqueda */
        .search-section {
          padding: 40px 30px;
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.08) 0%, 
            rgba(255, 255, 255, 0.03) 100%);
          backdrop-filter: blur(30px);
          border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .search-form {
          display: flex;
          gap: 20px;
          align-items: center;
          max-width: 700px;
          margin: 0 auto;
        }
        
        .search-input {
          flex: 1;
          padding: 16px 20px;
          border: 2px solid rgba(255, 255, 255, 0.1);
          border-radius: 16px;
          font-size: 16px;
          outline: none;
          transition: var(--transition);
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.08) 0%, 
            rgba(255, 255, 255, 0.03) 100%);
          backdrop-filter: blur(20px);
          color: rgba(255, 255, 255, 0.9);
          box-shadow: 
            0 8px 32px rgba(0,0,0,0.1),
            inset 0 1px 0 rgba(255,255,255,0.1);
        }
        
        .search-input::placeholder {
          color: rgba(255, 255, 255, 0.5);
        }
        
        .search-input:focus {
          border-color: rgba(59, 130, 246, 0.5);
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.12) 0%, 
            rgba(255, 255, 255, 0.06) 100%);
          box-shadow: 
            0 0 0 4px rgba(59, 130, 246, 0.2),
            0 12px 40px rgba(0,0,0,0.15),
            inset 0 1px 0 rgba(255,255,255,0.2);
        }
        
        .search-btn {
          background: linear-gradient(135deg, 
            #1b396a 0%, 
            #3b82f6 25%, 
            #1b396a 50%, 
            #3b82f6 75%, 
            #1b396a 100%);
          color: white;
          border: none;
          padding: 16px 24px;
          border-radius: 16px;
          font-size: 16px;
          font-weight: 700;
          cursor: pointer;
          transition: var(--transition);
          box-shadow: 
            0 15px 30px rgba(27, 57, 106, 0.4),
            inset 0 1px 0 rgba(255,255,255,0.2);
          border: 1px solid rgba(255,255,255,0.2);
        }
        
        .search-btn:hover {
          transform: translateY(-2px) scale(1.05);
          box-shadow: 
            0 25px 50px rgba(27, 57, 106, 0.5),
            inset 0 1px 0 rgba(255,255,255,0.3);
        }
        
        /* Tabla de insignias */
        .insignias-table {
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.06) 0%, 
            rgba(255, 255, 255, 0.02) 100%);
          backdrop-filter: blur(30px);
          border-radius: 20px;
          padding: 30px;
          margin: 30px;
          box-shadow: 
            0 20px 40px rgba(0,0,0,0.15),
            inset 0 1px 0 rgba(255,255,255,0.1);
          border: 1px solid rgba(255, 255, 255, 0.08);
          overflow-x: auto;
        }
        
        .insignias-table h2 {
          font-size: 24px;
          font-weight: 800;
          color: rgba(255, 255, 255, 0.95);
          margin-bottom: 25px;
          text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        /* RESPONSIVE - Tablet */
        @media (max-width: 1024px) {
          .historial-header {
            padding: 35px 25px;
          }
          
          .historial-header h1 {
            font-size: 2.5rem;
          }
          
          .insignias-table {
            margin: 25px;
            padding: 25px;
          }
          
          .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
          }
        }
        
        /* RESPONSIVE - Móviles y tablets pequeñas */
        @media (max-width: 768px) {
          .historial-header {
            padding: 30px 20px;
          }
          
          .historial-header h1 {
            font-size: 2rem;
          }
          
          .historial-header p {
            font-size: 1rem;
          }
          
          .back-button {
            position: static;
            transform: none;
            margin-bottom: 20px;
            align-self: flex-start;
            width: auto;
            padding: 12px 20px;
            font-size: 14px;
          }
          
          .search-section {
            padding: 30px 20px;
          }
          
          .search-form {
            flex-direction: column;
            gap: 15px;
          }
          
          .search-input {
            width: 100%;
            padding: 14px 18px;
            font-size: 16px;
          }
          
          .search-btn {
            width: 100%;
            padding: 14px 20px;
            font-size: 16px;
          }
          
          .insignias-table {
            margin: 20px;
            padding: 20px;
          }
          
          .stats-grid {
            grid-template-columns: 1fr;
            gap: 15px;
          }
          
          .insignias-grid {
            grid-template-columns: 1fr;
            gap: 20px;
          }
          
          .insignia-card {
            padding: 25px 20px;
          }
          
          .insignia-title {
            font-size: 1.2rem;
          }
          
          .insignia-category {
            font-size: 0.8rem;
            padding: 6px 14px;
          }
          
          .btn-action {
            width: 100%;
            padding: 12px 20px;
            font-size: 14px;
            margin-bottom: 8px;
          }
        }
        
        /* RESPONSIVE - Móviles pequeños */
        @media (max-width: 480px) {
          .historial-header {
            padding: 25px 15px;
          }
          
          .historial-header h1 {
            font-size: 1.8rem;
          }
          
          .historial-header p {
            font-size: 0.9rem;
          }
          
          .back-button {
            padding: 10px 16px;
            font-size: 13px;
          }
          
          .search-section {
            padding: 25px 15px;
          }
          
          .search-input {
            padding: 12px 16px;
            font-size: 16px;
          }
          
          .search-btn {
            padding: 12px 18px;
            font-size: 15px;
          }
          
          .insignias-table {
            margin: 15px;
            padding: 15px;
          }
          
          .stats-grid {
            gap: 12px;
          }
          
          .stat-card {
            padding: 20px 15px;
          }
          
          .stat-number {
            font-size: 2.5rem;
          }
          
          .stat-label {
            font-size: 0.9rem;
          }
          
          .insignias-grid {
            gap: 15px;
          }
          
          .insignia-card {
            padding: 20px 15px;
          }
          
          .insignia-title {
            font-size: 1.1rem;
          }
          
          .detail-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
          }
          
          .detail-value {
            text-align: left;
            max-width: 100%;
          }
          
          .btn-action {
            padding: 10px 16px;
            font-size: 13px;
          }
        }
        
        /* RESPONSIVE - Móviles muy pequeños */
        @media (max-width: 360px) {
          .historial-header {
            padding: 20px 12px;
          }
          
          .historial-header h1 {
            font-size: 1.5rem;
          }
          
          .historial-header p {
            font-size: 0.85rem;
          }
          
          .back-button {
            padding: 8px 14px;
            font-size: 12px;
          }
          
          .search-section {
            padding: 20px 12px;
          }
          
          .insignias-table {
            margin: 12px;
            padding: 12px;
          }
          
          .stat-card {
            padding: 18px 12px;
          }
          
          .stat-number {
            font-size: 2rem;
          }
          
          .stat-label {
            font-size: 0.85rem;
          }
          
          .insignia-card {
            padding: 18px 12px;
          }
          
          .insignia-title {
            font-size: 1rem;
          }
        }
        
        /* Orientación horizontal en móviles */
        @media (max-width: 768px) and (orientation: landscape) {
          .historial-header {
            padding: 20px 20px;
          }
          
          .historial-header h1 {
            font-size: 1.8rem;
          }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.08);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .back-button {
            position: absolute;
            left: 30px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 14px 24px;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            z-index: 10;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-50%) translateX(-3px);
            text-decoration: none;
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .header h1 {
            font-size: 2.8rem;
            margin-bottom: 12px;
            font-weight: 700;
            letter-spacing: -0.02em;
            position: relative;
            z-index: 10;
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 400;
            position: relative;
            z-index: 10;
        }

        .search-section {
            padding: 40px 30px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .search-form {
            display: flex;
            gap: 20px;
            align-items: center;
            max-width: 700px;
            margin: 0 auto;
        }

        .search-input {
            flex: 1;
            padding: 16px 24px;
            border: 2px solid #e2e8f0;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 400;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .search-input:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 4px rgba(30, 60, 114, 0.1), 0 4px 12px rgba(0,0,0,0.05);
            transform: translateY(-1px);
        }

        .search-btn {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            padding: 16px 28px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 60, 114, 0.4);
        }

        .content {
            padding: 40px 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #1b396a 0%, #3b82f6 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(27, 57, 106, 0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(27, 57, 106, 0.3);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 12px;
            position: relative;
            z-index: 10;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.95;
            font-weight: 500;
            position: relative;
            z-index: 10;
        }

        .insignias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
            gap: 30px;
        }

        .insignia-card {
            background: white;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .insignia-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
            border-color: rgba(30, 60, 114, 0.2);
        }

        .insignia-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        .insignia-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            position: relative;
            z-index: 10;
        }

        .insignia-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .insignia-category {
            background: linear-gradient(135deg, #1b396a 0%, #3b82f6 100%);
            color: white;
            padding: 8px 18px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 12px rgba(27, 57, 106, 0.3);
        }

        .insignia-details {
            margin-bottom: 25px;
            position: relative;
            z-index: 10;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #64748b;
            font-size: 0.9rem;
            letter-spacing: 0.01em;
        }

        .detail-value {
            color: #334155;
            font-size: 0.9rem;
            text-align: right;
            max-width: 220px;
            word-wrap: break-word;
            font-weight: 500;
        }

        .insignia-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            position: relative;
            z-index: 10;
        }

        .btn-action {
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 0.01em;
        }

        .btn-ver {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-ver:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-validar {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-validar:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
        }

        .empty-state h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #334155;
            font-weight: 700;
        }

        .empty-state p {
            font-size: 1.1rem;
            line-height: 1.7;
            font-weight: 400;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .back-button {
                left: 20px;
                padding: 12px 20px;
                font-size: 14px;
            }
            
            .header h1 {
                font-size: 2.2rem;
                margin-left: 80px;
                margin-right: 80px;
            }
            
            .insignias-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .search-form {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-input {
                width: 100%;
            }
            
            .insignia-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .search-section {
                padding: 30px 20px;
            }
        }
        
        /* Ajuste para el contenedor principal */
        .main-container {
          padding-bottom: 150px !important;
        }
        
        /* FOOTER AZUL PROFESIONAL */
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
    </style>
</head>
<body>
    <div class="main-container">
        <div class="historial-header">
            <?php if ($rol_usuario === 'Admin' || $rol_usuario === 'Administrador' || $rol_usuario === 'SuperUsuario'): ?>
                <a href="modulo_de_administracion.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Regresar al Módulo
                </a>
            <?php endif; ?>
            <h1>📜 Historial de Insignias</h1>
            <p>Sistema de Gestión de Reconocimientos TecNM</p>
        </div>

        <div class="search-section">
            <form class="search-form" method="GET">
                <input type="text" name="buscar" class="search-input" 
                       placeholder="Buscar por nombre del destinatario..." 
                       value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    Buscar
                </button>
                <?php if (!empty($busqueda)): ?>
                    <a href="historial_insignias.php" class="search-btn" style="background: linear-gradient(135deg, #6c757d, #495057); text-decoration: none;">
                        <i class="fas fa-times"></i>
                        Limpiar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="insignias-table">
            <h2>📊 Estadísticas</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($insignias); ?></div>
                    <div class="stat-label">Total de Insignias</div>
                </div>
            </div>

            <?php if (empty($insignias)): ?>
                <div class="empty-state">
                    <h3>📭 No se encontraron insignias</h3>
                    <?php if (!empty($busqueda)): ?>
                        <p>No se encontraron insignias que coincidan con la búsqueda "<?php echo htmlspecialchars($busqueda); ?>"</p>
                     <?php elseif ($rol_usuario === 'Admin' || $rol_usuario === 'Administrador' || $rol_usuario === 'SuperUsuario'): ?>
                         <p>No hay insignias registradas en el sistema.</p>
                     <?php else: ?>
                        <p>No tienes insignias asignadas o no se pudo encontrar tu información en la base de datos.</p>
                     <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="insignias-grid">
                    <?php foreach ($insignias as $insignia): ?>
                        <div class="insignia-card">
                            <div class="insignia-header">
                                <div>
                                    <div class="insignia-title"><?php echo htmlspecialchars($insignia['nombre_insignia']); ?></div>
                                </div>
                                <div class="insignia-category"><?php echo htmlspecialchars($insignia['categoria']); ?></div>
                            </div>

                            <div class="insignia-details">
                                <div class="detail-row">
                                    <span class="detail-label">Destinatario:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($insignia['destinatario']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Matrícula:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($insignia['Matricula'] ?? 'No especificada'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Fecha de Emisión:</span>
                                    <span class="detail-value"><?php echo formatearFecha($insignia['fecha_otorgamiento']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Programa:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($insignia['Programa']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Institución:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($insignia['institucion']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Estado:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($insignia['estatus']); ?></span>
                                </div>
                            </div>

                            <div class="insignia-actions">
                                <?php if ($rol_usuario === 'Admin' || $rol_usuario === 'Administrador' || $rol_usuario === 'SuperUsuario'): ?>
                                    <!-- Enlaces para administradores -->
                                    <a href="ver_insignia_completa.php?insignia=<?php echo urlencode($insignia['clave_insignia']); ?>" class="btn-action btn-ver" target="_blank">
                                        🏆 Ver Certificado
                                    </a>
                                    <a href="ver_insignia_publica.php?insignia=<?php echo urlencode($insignia['clave_insignia']); ?>" class="btn-action btn-validar" target="_blank">
                                        🔍 Ver Validación
                                    </a>
                                <?php else: ?>
                                    <!-- Enlaces para usuarios normales -->
                                    <a href="ver_insignia_completa.php?insignia=<?php echo urlencode($insignia['clave_insignia']); ?>" class="btn-action btn-ver">
                                        ⭐ Ver Reconocimiento
                                    </a>
                                    <a href="ver_insignia_publica.php?insignia=<?php echo urlencode($insignia['clave_insignia']); ?>" class="btn-action btn-validar">
                                        ✓ Ver Validación
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
    
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
