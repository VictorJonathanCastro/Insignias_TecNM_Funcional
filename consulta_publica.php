<?php
require_once 'conexion.php';

// Verificar qué tabla existe - PRIORIDAD: usar insigniasotorgadas primero (donde se guardan las nuevas insignias)
$usar_tabla_t = false;
$usar_tabla_i = false;

// Primero verificar insigniasotorgadas (donde se guardan las nuevas insignias)
try {
    $tabla_existe_i = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
    if ($tabla_existe_i && $tabla_existe_i->num_rows > 0) {
        $usar_tabla_i = true;
    }
} catch (Exception $e) {
    // Si hay error, no usar insigniasotorgadas
}

// Solo verificar T_insignias_otorgadas si insigniasotorgadas no existe
if (!$usar_tabla_i) {
    try {
        $tabla_existe_t = $conexion->query("SHOW TABLES LIKE 'T_insignias_otorgadas'");
        if ($tabla_existe_t && $tabla_existe_t->num_rows > 0) {
            $usar_tabla_t = true;
        }
    } catch (Exception $e) {
        // Si hay error, no usar T_insignias_otorgadas
    }
}

// Detectar estructura dinámica de las tablas para JOINs correctos
$check_destinatario_id = $conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
$tiene_id_destinatario = ($check_destinatario_id && $check_destinatario_id->num_rows > 0);
$campo_id_destinatario = $tiene_id_destinatario ? 'id' : 'ID_destinatario';

// Obtener parámetros de búsqueda
$busqueda = $_GET['busqueda'] ?? '';
$codigo = $_GET['codigo'] ?? '';
$categoria_id = $_GET['categoria'] ?? '';
$subcategoria_id = $_GET['subcategoria'] ?? '';

$resultados = [];
$mensaje = '';

// Consultar categorías e insignias disponibles
$categorias_insignias = [];
$subcategorias_insignias = [];

try {
    // Consultar categorías de insignias
    $sql_categorias = "SELECT DISTINCT ID_cat as id, Nombre_cat as nombre_categoria FROM cat_insignias ORDER BY Nombre_cat";
    $result_categorias = $conexion->query($sql_categorias);
    
    if ($result_categorias && $result_categorias->num_rows > 0) {
        while ($row = $result_categorias->fetch_assoc()) {
            $categorias_insignias[] = $row;
        }
    }
    
    // Consultar tipos de insignias (subcategorías)
    $sql_subcategorias = "SELECT ti.ID_tipo as id, ti.Nombre_ins as nombre_insignia, ti.Cat_ins as categoria_id, ci.Nombre_cat as nombre_categoria 
                         FROM tipo_insignia ti 
                         JOIN cat_insignias ci ON ti.Cat_ins = ci.ID_cat 
                         ORDER BY ci.Nombre_cat, ti.Nombre_ins";
    $result_subcategorias = $conexion->query($sql_subcategorias);
    
    if ($result_subcategorias && $result_subcategorias->num_rows > 0) {
        while ($row = $result_subcategorias->fetch_assoc()) {
            $subcategorias_insignias[] = $row;
        }
    }
} catch (Exception $e) {
    // Si hay error, usar arrays vacíos
    $categorias_insignias = [];
    $subcategorias_insignias = [];
}

// Mapeo de nombres de insignias a códigos en Codigo_Insignia
$mapa_insignias_codigos = [
    'Embajador del Arte' => 'ART',
    'Embajador del Deporte' => 'EMB',
    'Talento Científico' => 'TAL',
    'Talento Innovador' => 'INN',
    'Responsabilidad Social' => 'SOC',
    'Formación y Actualización' => 'FOR',
    'Movilidad e Intercambio' => 'MOV'
];

// Obtener el código de la insignia seleccionada
$codigo_filtro = '';
if (!empty($subcategoria_id)) {
    // Buscar el nombre de la insignia por su ID
    foreach ($subcategorias_insignias as $subcat) {
        if ($subcat['id'] == $subcategoria_id) {
            $nombre_insignia = $subcat['nombre_insignia'];
            // Buscar el código correspondiente
            foreach ($mapa_insignias_codigos as $nombre => $cod) {
                if (stripos($nombre_insignia, $nombre) !== false || stripos($nombre, $nombre_insignia) !== false) {
                    $codigo_filtro = $cod;
                    break 2;
                }
            }
            // Si no hay coincidencia exacta, intentar buscar por palabras clave
            if (empty($codigo_filtro)) {
                if (stripos($nombre_insignia, 'Arte') !== false || stripos($nombre_insignia, 'Art') !== false) {
                    $codigo_filtro = 'ART';
                } elseif (stripos($nombre_insignia, 'Deporte') !== false || stripos($nombre_insignia, 'Embajador') !== false) {
                    $codigo_filtro = 'EMB';
                } elseif (stripos($nombre_insignia, 'Científico') !== false || stripos($nombre_insignia, 'Talento') !== false) {
                    $codigo_filtro = 'TAL';
                } elseif (stripos($nombre_insignia, 'Innovador') !== false) {
                    $codigo_filtro = 'INN';
                } elseif (stripos($nombre_insignia, 'Responsabilidad') !== false || stripos($nombre_insignia, 'Social') !== false) {
                    $codigo_filtro = 'SOC';
                } elseif (stripos($nombre_insignia, 'Formación') !== false || stripos($nombre_insignia, 'Actualización') !== false) {
                    $codigo_filtro = 'FOR';
                } elseif (stripos($nombre_insignia, 'Movilidad') !== false || stripos($nombre_insignia, 'Intercambio') !== false) {
                    $codigo_filtro = 'MOV';
                }
            }
            break;
        }
    }
}

// Validar que se haya determinado qué tabla usar
if (!$usar_tabla_t && !$usar_tabla_i) {
    $mensaje = "Error: No se encontró ninguna tabla de insignias otorgadas. Verifica que exista T_insignias_otorgadas o insigniasotorgadas en la base de datos.";
} else {
    // Solo ejecutar búsquedas cuando haya parámetros de búsqueda
    if (!empty($busqueda)) {
        // Búsqueda por nombre completo (igual que historial)
        // PRIORIDAD: usar insigniasotorgadas primero
        if ($usar_tabla_i) {
            // Usar insigniasotorgadas (donde se guardan las nuevas insignias)
            // Búsqueda flexible: también buscar por código de insignia
            $busqueda_like = '%' . $busqueda . '%';
            $sql = "
                SELECT 
                    io.ID_otorgada as id,
                    io.Codigo_Insignia as clave_insignia,
                    io.Fecha_Emision as fecha_otorgamiento,
                    'Certificación oficial' as evidencia,
                    COALESCE(d.Nombre_Completo, 'Destinatario no especificado') as destinatario,
                    COALESCE(d.Matricula, 'No especificada') as Matricula,
                    'Programa no especificado' as Programa,
                    COALESCE(d.Curp, '') as curp,
                    CASE 
                        WHEN io.Codigo_Insignia LIKE '%EMB-BRONCE%' THEN 'EmbajadordelDeporteBronce'
                        WHEN io.Codigo_Insignia LIKE '%EMB-ORO%' THEN 'EmbajadordelDeporteOro'
                        WHEN io.Codigo_Insignia LIKE '%EMB-PLATA%' THEN 'EmbajadordelDeportePlata'
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
                    COALESCE(itc.Nombre_itc, 'TecNM') as institucion,
                    '2025-1' as periodo,
                    'Activo' as estatus,
                    'Sistema' as responsable,
                    'Administrador' as cargo
                FROM insigniasotorgadas io
                LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
                LEFT JOIN it_centros itc ON d.ITCentro = itc.id
                WHERE (
                    UPPER(COALESCE(d.Nombre_Completo, '')) LIKE UPPER(?) 
                    OR UPPER(COALESCE(d.Curp, '')) LIKE UPPER(?) 
                    OR UPPER(COALESCE(d.Matricula, '')) LIKE UPPER(?)
                    OR UPPER(io.Codigo_Insignia) LIKE UPPER(?)
                )
                " . (!empty($codigo_filtro) ? "AND io.Codigo_Insignia LIKE '%$codigo_filtro%'" : "") . "
                ORDER BY io.Fecha_Emision DESC
            ";
        } elseif ($usar_tabla_t) {
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
                    COALESCE(d.Curp, '') as curp,
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
                    'Administrador' as cargo
                FROM T_insignias_otorgadas tio
                LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
                LEFT JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
                LEFT JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
                LEFT JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
                LEFT JOIN estatus e ON tio.Id_Estatus = e.id
                LEFT JOIN it_centros itc ON ti.Propone_Insignia = itc.id
                WHERE (d.Nombre_Completo LIKE ? OR COALESCE(d.Curp, '') LIKE ? OR COALESCE(d.Matricula, '') LIKE ?)
                " . (!empty($codigo_filtro) ? "AND tin.Nombre_Insignia LIKE '%$codigo_filtro%'" : "") . "
                ORDER BY tio.Fecha_Emision DESC
            ";
        } else {
            // Si no existe ninguna tabla, mostrar error
            die('Error: No se encontró ninguna tabla de insignias otorgadas. Verifica que exista T_insignias_otorgadas o insigniasotorgadas en la base de datos.');
        }
    
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            die('Error al preparar la consulta: ' . $conexion->error);
        }
        
        $busqueda_param = "%$busqueda%";
        // Para insigniasotorgadas: buscar en nombre, CURP, matrícula y código de insignia (4 parámetros)
        // Para T_insignias_otorgadas: buscar en nombre, CURP y matrícula (3 parámetros)
        if ($usar_tabla_i) {
            $stmt->bind_param("ssss", $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param);
        } else {
            $stmt->bind_param("sss", $busqueda_param, $busqueda_param, $busqueda_param);
        }
    
    if (!$stmt->execute()) {
        die('Error al ejecutar la consulta: ' . $stmt->error);
    }
    
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }
        $stmt->close();
        
        if (empty($resultados)) {
            $mensaje = "🔍 No se encontraron insignias con los criterios de búsqueda '" . htmlspecialchars($busqueda) . "'.";
        } else {
            $mensaje = "";
        }
    } elseif (!empty($codigo)) {
        // Búsqueda por código específico
        // PRIORIDAD: usar insigniasotorgadas primero
        if ($usar_tabla_i) {
            // Usar insigniasotorgadas (donde se guardan las nuevas insignias)
            $sql = "
                SELECT 
                    io.ID_otorgada as id,
                    io.Codigo_Insignia as clave_insignia,
                    io.Fecha_Emision as fecha_otorgamiento,
                    'Certificación oficial' as evidencia,
                    COALESCE(d.Nombre_Completo, 'Destinatario no especificado') as destinatario,
                    COALESCE(d.Matricula, 'No especificada') as Matricula,
                    'Programa no especificado' as Programa,
                    COALESCE(d.Curp, '') as curp,
                    CASE 
                        WHEN io.Codigo_Insignia LIKE '%EMB-BRONCE%' THEN 'EmbajadordelDeporteBronce'
                        WHEN io.Codigo_Insignia LIKE '%EMB-ORO%' THEN 'EmbajadordelDeporteOro'
                        WHEN io.Codigo_Insignia LIKE '%EMB-PLATA%' THEN 'EmbajadordelDeportePlata'
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
                    COALESCE(itc.Nombre_itc, 'TecNM') as institucion,
                    '2025-1' as periodo,
                    'Activo' as estatus,
                    'Sistema' as responsable,
                    'Administrador' as cargo
                FROM insigniasotorgadas io
                LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
                LEFT JOIN it_centros itc ON d.ITCentro = itc.id
                WHERE io.Codigo_Insignia = ?
                ORDER BY io.Fecha_Emision DESC
            ";
        } elseif ($usar_tabla_t) {
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
                    COALESCE(d.Curp, '') as curp,
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
                    'Administrador' as cargo
                FROM T_insignias_otorgadas tio
                LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
                LEFT JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
                LEFT JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
                LEFT JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
                LEFT JOIN estatus e ON tio.Id_Estatus = e.id
                LEFT JOIN it_centros itc ON ti.Propone_Insignia = itc.id
                WHERE CONCAT(ti.id, '-', pe.Nombre_Periodo) = ?
                ORDER BY tio.Fecha_Emision DESC
            ";
        } else {
            // Si no existe ninguna tabla, mostrar error
            die('Error: No se encontró ninguna tabla de insignias otorgadas. Verifica que exista T_insignias_otorgadas o insigniasotorgadas en la base de datos.');
        }
        
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            die('Error al preparar la consulta: ' . $conexion->error);
        }
        
        $stmt->bind_param("s", $codigo);
        
        if (!$stmt->execute()) {
            die('Error al ejecutar la consulta: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }
        $stmt->close();
        
        if (empty($resultados)) {
            $mensaje = "🔍 No se encontraron insignias con el código '" . htmlspecialchars($codigo) . "'.";
        } else {
            $mensaje = "";
        }
    } elseif (!empty($subcategoria_id) || !empty($categoria_id)) {
        // Búsqueda por categoría/subcategoría
        // PRIORIDAD: usar insigniasotorgadas primero
        if ($usar_tabla_i) {
            // Usar insigniasotorgadas (donde se guardan las nuevas insignias)
            $sql = "
                SELECT 
                    io.ID_otorgada as id,
                    io.Codigo_Insignia as clave_insignia,
                    io.Fecha_Emision as fecha_otorgamiento,
                    'Certificación oficial' as evidencia,
                    COALESCE(d.Nombre_Completo, 'Destinatario no especificado') as destinatario,
                    COALESCE(d.Matricula, 'No especificada') as Matricula,
                    'Programa no especificado' as Programa,
                    COALESCE(d.Curp, '') as curp,
                    CASE 
                        WHEN io.Codigo_Insignia LIKE '%EMB-BRONCE%' THEN 'EmbajadordelDeporteBronce'
                        WHEN io.Codigo_Insignia LIKE '%EMB-ORO%' THEN 'EmbajadordelDeporteOro'
                        WHEN io.Codigo_Insignia LIKE '%EMB-PLATA%' THEN 'EmbajadordelDeportePlata'
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
                    COALESCE(itc.Nombre_itc, 'TecNM') as institucion,
                    '2025-1' as periodo,
                    'Activo' as estatus,
                    'Sistema' as responsable,
                    'Administrador' as cargo
                FROM insigniasotorgadas io
                LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
                LEFT JOIN it_centros itc ON d.ITCentro = itc.id
                WHERE 1=1
                " . (!empty($codigo_filtro) ? "AND io.Codigo_Insignia LIKE '%$codigo_filtro%'" : "") . "
                ORDER BY io.Fecha_Emision DESC
            ";
        } elseif ($usar_tabla_t) {
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
                    COALESCE(d.Curp, '') as curp,
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
                    'Administrador' as cargo
                FROM T_insignias_otorgadas tio
                LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
                LEFT JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
                LEFT JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
                LEFT JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
                LEFT JOIN estatus e ON tio.Id_Estatus = e.id
                LEFT JOIN it_centros itc ON ti.Propone_Insignia = itc.id
                WHERE 1=1
                " . (!empty($subcategoria_id) ? "AND tin.id = $subcategoria_id" : "") . "
                ORDER BY tio.Fecha_Emision DESC
            ";
        } else {
            // Si no existe ninguna tabla, mostrar error
            die('Error: No se encontró ninguna tabla de insignias otorgadas. Verifica que exista T_insignias_otorgadas o insigniasotorgadas en la base de datos.');
        }
        
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            die('Error al preparar la consulta: ' . $conexion->error);
        }
        
        if (!$stmt->execute()) {
            die('Error al ejecutar la consulta: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }
        $stmt->close();
        
        if (empty($resultados)) {
            $mensaje = "🔍 No se encontraron insignias para la categoría seleccionada.";
        } else {
            $mensaje = "";
        }
    } else {
        // No hay búsqueda: no mostrar nada, solo el formulario
        $mensaje = "";
    }
}

// Función para formatear fechas
function formatearFecha($fecha) {
    if (empty($fecha)) return 'No especificada';
    return date('d/m/Y', strtotime($fecha));
}

// Función para determinar la imagen de la insignia dinámicamente
function determinarImagenInsignia($codigo_insignia, $nombre_insignia) {
    // Variantes Embajador del Deporte: imagen por código antes del genérico EMB
    if (stripos($codigo_insignia, 'EMB-BRONCE') !== false) return 'imagen/Insignias/EmbajadordelDeporteBronce.png';
    if (stripos($codigo_insignia, 'EMB-ORO') !== false) return 'imagen/Insignias/EmbajadordelDeporteOro.png';
    if (stripos($codigo_insignia, 'EMB-PLATA') !== false) return 'imagen/Insignias/EmbajadordelDeportePlata.png';
    if (stripos($nombre_insignia, 'EmbajadordelDeporteBronce') !== false) return 'imagen/Insignias/EmbajadordelDeporteBronce.png';
    if (stripos($nombre_insignia, 'EmbajadordelDeporteOro') !== false) return 'imagen/Insignias/EmbajadordelDeporteOro.png';
    if (stripos($nombre_insignia, 'EmbajadordelDeportePlata') !== false) return 'imagen/Insignias/EmbajadordelDeportePlata.png';
    $mapeo_codigos = [
        'ART' => 'Embajador del Arte',
        'EMB' => 'Embajador del Deporte',
        'TAL' => 'Talento Científico',
        'INN' => 'Talento Innovador',
        'SOC' => 'Responsabilidad Social',
        'FOR' => 'Formación y Actualización',
        'MOV' => 'Movilidad e Intercambio'
    ];
    
    $mapeo_imagenes = [
        'Movilidad e Intercambio' => 'MovilidadeIntercambio.png',
        'Embajador del Deporte' => 'EmbajadordelDeporte.png',
        'Embajador del Arte' => 'EmbajadordelArte.png',
        'Formación y Actualización' => 'FormacionyActualizacion.png',
        'Talento Científico' => 'TalentoCientifico.png',
        'Talento Innovador' => 'TalentoInnovador.png',
        'Responsabilidad Social' => 'ResponsabilidadSocial.png',
        'Responsabilidad Social Demo' => 'ResponsabilidadSocial.png',
        'Embajador del Deporte Oro Demo' => 'EmbajadordelDeporteOroDemo.png',
        'Embajador del Deporte Plata Demo' => 'EmbajadordelDeportePlataDemo.png',
        'Embajador del Deporte Bronce Demo' => 'EmbajadordelDeporteBronceDemo.png',
        'EmbajadordelDeporteOroDemo' => 'EmbajadordelDeporteOroDemo.png',
        'EmbajadordelDeportePlataDemo' => 'EmbajadordelDeportePlataDemo.png',
        'EmbajadordelDeporteBronceDemo' => 'EmbajadordelDeporteBronceDemo.png',
        'EmbajadordelDeporteOro' => 'EmbajadordelDeporteOro.png',
        'EmbajadordelDeportePlata' => 'EmbajadordelDeportePlata.png',
        'EmbajadordelDeporteBronce' => 'EmbajadordelDeporteBronce.png'
    ];
    
    foreach ($mapeo_codigos as $codigo => $tipo) {
        if (strpos($codigo_insignia, $codigo) !== false) {
            return 'imagen/Insignias/' . ($mapeo_imagenes[$tipo] ?? 'EmbajadordelArte.png');
        }
    }
    
    if (isset($mapeo_imagenes[$nombre_insignia])) {
        return 'imagen/Insignias/' . $mapeo_imagenes[$nombre_insignia];
    }
    
    return 'imagen/Insignias/EmbajadordelArte.png';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Pública de Insignias - TecNM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: 
                linear-gradient(135deg, 
                    rgba(30, 60, 114, 0.95) 0%, 
                    rgba(42, 82, 152, 0.98) 30%,
                    rgba(30, 60, 114, 0.95) 60%,
                    rgba(26, 52, 100, 0.95) 100%);
            backdrop-filter: blur(60px) saturate(200%);
            -webkit-backdrop-filter: blur(60px) saturate(200%);
            color: white;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 35px 0;
            box-shadow: 
                0 10px 50px rgba(0,0,0,0.4),
                0 5px 25px rgba(0,0,0,0.2),
                inset 0 2px 0 rgba(255,255,255,0.25),
                inset 0 -1px 0 rgba(255,255,255,0.05);
            border-bottom: 2px solid rgba(255,255,255,0.15);
            border-top: 2px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            width: 100%;
            left: 0;
            right: 0;
        }

        .header-content {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .header-logo {
            position: absolute;
            left: 40px;
            top: 50%;
            transform: translateY(-50%);
            height: 48px;
            width: auto;
            filter: brightness(0) invert(1);
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .back-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .search-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #1e3c72;
        }

        .search-btn {
            padding: 15px 25px;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .search-btn:hover {
            background: #2a5298;
            transform: translateY(-2px);
        }

        .results-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(30, 60, 114, 0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(30, 60, 114, 0.3);
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 8px 18px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
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

        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1em;
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

        /* Footer */
        footer {
            background: #1e3c72;
            color: white;
            padding: 40px 0;
            margin-top: 50px;
            text-align: center;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-section {
            margin-bottom: 25px;
        }
        
        .footer h3 {
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
        
        .footer-social {
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
            color: #fff;
            text-decoration: none;
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
        
        /* RESPONSIVE - Tablets */
        @media (max-width: 1024px) {
            .container {
                padding: 15px;
            }
            
            .header {
                padding: 25px 0;
            }
            
            .header-logo {
                height: 50px;
                left: -180px;
            }
            
            .insignias-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }
        
        /* RESPONSIVE - Móviles y tablets pequeñas */
        @media (max-width: 768px) {
            .header {
                padding: 20px 0;
            }
            
            .header-content {
                padding: 0 15px;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                gap: 12px;
            }
            
            .header-logo {
                position: relative;
                left: auto;
                top: auto;
                transform: none;
                height: 45px;
                width: auto;
                display: block;
                margin: 0;
            }
            
            .header h1 {
                font-size: 18px;
                margin: 0;
            }
            
            .header p {
                font-size: 12px;
            }
            
            .container {
                padding: 10px;
            }
            
            .footer-links {
                flex-direction: column;
                align-items: center;
                gap: 12px;
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
            
            .btn {
                width: 100%;
                padding: 12px 20px;
                font-size: 14px;
            }
        }
        
        /* RESPONSIVE - Móviles pequeños (iPhone SE, etc.) */
        @media (max-width: 480px) {
            .header {
                padding: 15px 0;
            }
            
            .header h1 {
                font-size: 16px;
            }
            
            .header-logo {
                height: 35px;
            }
            
            .container {
                padding: 5px;
            }
            
            .insignias-grid {
                gap: 15px;
            }
            
            .btn {
                padding: 10px 16px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <img src="imagen/logo.png" alt="TecNM Logo" class="header-logo" onerror="this.style.display='none';">
            <div>
                <h1>🔍 Consulta Pública de Insignias</h1>
                <p>TecNM - Tecnológico Nacional de México</p>
            </div>
        </div>
    </header>
    <div class="container">

        <a href="index.php" class="back-link">
            ← Volver al Inicio
        </a>

        <div class="search-section">
            <h2 style="margin-bottom: 20px; color: #1e3c72;">Buscar Insignias</h2>
            
            <!-- Búsqueda general -->
            <form method="GET" class="search-form">
                <input type="text" 
                       name="busqueda" 
                       class="search-input" 
                       placeholder="Buscar por CURP, nombre completo, matrícula, responsabilidad social..."
                       value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="search-btn">
                    🔍 Buscar
                </button>
                <?php if (!empty($busqueda) || !empty($codigo)): ?>
                <a href="consulta_publica.php" class="search-btn" style="background: #dc3545; margin-left: 10px;">
                    🗑️ Limpiar
                </a>
                <?php endif; ?>
            </form>
            
            <!-- Búsqueda específica por código -->
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                <h3 style="color: #1e3c72; margin-bottom: 15px; font-size: 1.1em;">🔍 Búsqueda por Código de Insignia</h3>
                <form method="GET" class="search-form">
                    <input type="text" 
                           name="codigo" 
                           class="search-input" 
                           placeholder="Ejemplo: TECNM-OFCM-2025-ART-001"
                           value="<?php echo htmlspecialchars($codigo); ?>">
                    <button type="submit" class="search-btn">
                        🎯 Buscar por Código
                    </button>
                    <?php if (!empty($busqueda) || !empty($codigo) || !empty($categoria_id) || !empty($subcategoria_id)): ?>
                    <a href="consulta_publica.php" class="search-btn" style="background: #dc3545; margin-left: 10px;">
                        🗑️ Limpiar
                    </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div style="text-align: center; margin-top: 15px;">
                <p style="color: #666; font-size: 0.9em;">
                    💡 <strong>Tipos de búsqueda:</strong> CURP, nombre completo, matrícula, código de insignia, responsabilidad social, formación integral
                </p>
            </div>
        </div>

        <?php if (!empty($mensaje) && (strpos($mensaje, 'No se encontraron') !== false || strpos($mensaje, 'No hay insignias') !== false || strpos($mensaje, 'Error') !== false)): ?>
        <div class="results-section">
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
                <p style="margin: 0; color: #856404;"><?php echo $mensaje; ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($resultados)): ?>
        <div class="results-section">
            <h2 style="margin-bottom: 20px; color: #1e3c72;">📊 Estadísticas</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($resultados); ?></div>
                    <div class="stat-label">Insignias Encontradas</div>
                </div>
            </div>

            <div class="insignias-grid">
                <?php foreach ($resultados as $insignia): 
                    $imagen_insignia = determinarImagenInsignia($insignia['clave_insignia'], $insignia['nombre_insignia']);
                ?>
                <div class="insignia-card">
                    <!-- Imagen de la insignia -->
                    <div style="text-align: center; margin-bottom: 25px;">
                        <div style="width: 200px; height: 200px; margin: 0 auto; background-image: url('<?php echo $imagen_insignia; ?>'); background-size: contain; background-repeat: no-repeat; background-position: center;">
                        </div>
                    </div>
                    
                    <!-- Título y categoría -->
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="font-size: 1.4rem; font-weight: 700; color: #1e3c72; margin-bottom: 8px;">
                            <?php echo htmlspecialchars($insignia['nombre_insignia']); ?>
                        </div>
                        <div style="display: inline-block; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 8px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                            <?php echo htmlspecialchars($insignia['categoria']); ?>
                        </div>
                    </div>

                    <!-- Información del destinatario en dos columnas -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                        <div>
                            <div style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px;">Destinatario:</div>
                            <div style="color: #666; font-size: 15px;"><?php echo htmlspecialchars($insignia['destinatario']); ?></div>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px;">Matrícula:</div>
                            <div style="color: #666; font-size: 15px;"><?php echo htmlspecialchars($insignia['Matricula'] ?? $insignia['matricula'] ?? 'No especificada'); ?></div>
                        </div>
                        <?php if (!empty($insignia['curp'])): ?>
                        <div>
                            <div style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px;">CURP:</div>
                            <div style="color: #666; font-size: 15px;"><?php echo htmlspecialchars($insignia['curp']); ?></div>
                        </div>
                        <?php endif; ?>
                        <div>
                            <div style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px;">Fecha de Emisión:</div>
                            <div style="color: #666; font-size: 15px;"><?php echo formatearFecha($insignia['fecha_otorgamiento']); ?></div>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <div style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px;">Institución:</div>
                            <div style="color: #666; font-size: 15px;"><?php echo htmlspecialchars($insignia['institucion']); ?></div>
                        </div>
                    </div>

                    <div class="insignia-actions">
                        <a href="ver_insignia_completa_publica.php?insignia=<?php echo urlencode($insignia['clave_insignia']); ?>&solo=1" class="btn-action btn-ver" target="_blank">
                            🏆 Ver Certificado
                        </a>
                        <a href="ver_validacion_publica.php?insignia=<?php echo urlencode($insignia['clave_insignia']); ?>" class="btn-action btn-validar" target="_blank">
                            🔍 Ver Validación
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php elseif (!empty($busqueda) || !empty($codigo)): ?>
        <div class="results-section">
            <div class="empty-state">
                <h3>📭 No se encontraron insignias</h3>
                <p>No se encontraron insignias que coincidan con tu búsqueda.</p>
                <p><strong>Sugerencias:</strong></p>
                <ul style="text-align: left; max-width: 400px; margin: 0 auto;">
                    <li>Verifica que el CURP esté escrito correctamente</li>
                    <li>Intenta buscar solo por nombre o apellido</li>
                    <li>Verifica que el código de insignia sea exacto</li>
                    <li>Prueba con términos más generales</li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
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
                <div class="footer-social">
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