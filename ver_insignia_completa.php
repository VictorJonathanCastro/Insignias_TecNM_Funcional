<?php
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Obtener el código de la insignia desde la URL
$codigo_insignia = isset($_GET['insignia']) ? $_GET['insignia'] : '';
$solo_certificado = isset($_GET['solo']) && $_GET['solo'] == '1';

if (empty($codigo_insignia)) {
    echo "Error: No se proporcionó código de insignia";
    exit();
}

try {
    // Detectar formato del código para saber qué tabla y campo usar
    // Si el código tiene formato "TECNM-OFCM-..." buscar en insigniasotorgadas
    // Si el código tiene formato "ID-Periodo" buscar en T_insignias_otorgadas
    $codigo_tiene_formato_tecnm = (strpos($codigo_insignia, 'TECNM-') === 0);
    
    // Verificar qué tabla existe
    $tabla_existe_t = $conexion->query("SHOW TABLES LIKE 'T_insignias_otorgadas'");
    $tabla_existe_io = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
    $usar_tabla_t = ($tabla_existe_t && $tabla_existe_t->num_rows > 0);
    $usar_tabla_io = ($tabla_existe_io && $tabla_existe_io->num_rows > 0);
    
    // Decidir qué tabla usar basándose en el formato del código
    // PRIORIDAD: El formato del código determina la tabla, no solo su existencia
    if ($codigo_tiene_formato_tecnm) {
        // Si el código tiene formato TECNM-OFCM-..., buscar SIEMPRE en insigniasotorgadas
        if (!$usar_tabla_io) {
            // Si la tabla no existe, intentar crearla (sin FOREIGN KEY para evitar problemas de permisos)
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
                // Verificar que la tabla se creó correctamente
                $verificar = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
                if ($verificar && $verificar->num_rows > 0) {
                    $usar_tabla_io = true;
                } else {
                    throw new Exception("La tabla 'insigniasotorgadas' no se pudo crear. Error: " . $conexion->error . " (Código: " . $conexion->errno . ")");
                }
            } else {
                $error_detalle = "Error MySQL: " . $conexion->error . " (Código: " . $conexion->errno . ")";
                throw new Exception("El código tiene formato TECNM-OFCM-... pero la tabla 'insigniasotorgadas' no existe y no se pudo crear. " . $error_detalle . "<br><br>Posibles soluciones:<br>1. Verificar permisos del usuario MySQL<br>2. Crear la tabla manualmente con el usuario root<br>3. Contactar al administrador de la base de datos");
            }
        }
        // Usar insigniasotorgadas cuando el código tiene formato TECNM-OFCM-...
        // Verificar estructura dinámica de la tabla destinatario
        $check_destinatario_id = $conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
        $tiene_id_destinatario = ($check_destinatario_id && $check_destinatario_id->num_rows > 0);
        $campo_id_destinatario = $tiene_id_destinatario ? 'id' : 'ID_destinatario';
        
        $query = "SELECT 
            io.ID_otorgada as id,
            io.Codigo_Insignia as codigo,
            CASE 
                WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                ELSE 'Insignia TecNM'
            END as nombre,
            CASE 
                WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                ELSE 'Formación Integral'
            END as categoria,
            d.Nombre_Completo as destinatario,
            NULL as descripcion,
            NULL as criterios,
            'Certificación oficial' as evidencias,
            COALESCE(re.Nombre_Completo, 'Sistema TecNM') as responsable,
            COALESCE(re.Cargo, 'RESPONSABLE DE EMISIÓN') as cargo_responsable,
            io.Fecha_Emision as fecha_emision,
            'Tecnológico Nacional de México' as emisor,
            'Certificación oficial' as evidencia,
            'insignia_default.png' as archivo_visual,
            COALESCE(re.Nombre_Completo, 'Administrador') as responsable_captura,
            'ADMIN001' as codigo_responsable,
            'imagen/Insignias/insignia_default.png' as imagen_path,
            io.Responsable_Emision as responsable_id
        FROM insigniasotorgadas io
        LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
        LEFT JOIN responsable_emision re ON io.Responsable_Emision = re.id
        WHERE io.Codigo_Insignia = ?";
    } elseif ($usar_tabla_t) {
        // Usar T_insignias_otorgadas con JOIN a T_insignias (código formato ID-Periodo)
        $query = "SELECT 
            tio.id,
            CONCAT(ti.id, '-', pe.Nombre_Periodo) as codigo,
            COALESCE(tin.Nombre_Insignia, 'Insignia TecNM') as nombre,
            CASE 
                WHEN tin.Nombre_Insignia LIKE '%Deporte%' OR tin.Nombre_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                WHEN tin.Nombre_Insignia LIKE '%Científico%' OR tin.Nombre_Insignia LIKE '%Innovación%' OR tin.Nombre_Insignia LIKE '%Formación%' THEN 'Desarrollo Académico'
                WHEN tin.Nombre_Insignia LIKE '%Arte%' OR tin.Nombre_Insignia LIKE '%Social%' OR tin.Nombre_Insignia LIKE '%Movilidad%' THEN 'Formación Integral'
                ELSE 'Formación Integral'
            END as categoria,
            d.Nombre_Completo as destinatario,
            ti.Descripcion as descripcion,
            ti.Criterio as criterios,
            'Certificación oficial' as evidencias,
            COALESCE(re.Nombre_Completo, 'Sistema TecNM') as responsable,
            COALESCE(re.Cargo, 'RESPONSABLE DE EMISIÓN') as cargo_responsable,
            tio.Fecha_Emision as fecha_emision,
            COALESCE(itc.Nombre_itc, 'Tecnológico Nacional de México') as emisor,
            'Certificación oficial' as evidencia,
            COALESCE(ti.Archivo_Visual, 'insignia_default.png') as archivo_visual,
            COALESCE(re.Nombre_Completo, 'Administrador') as responsable_captura,
            'ADMIN001' as codigo_responsable,
            CONCAT('imagen/Insignias/', COALESCE(ti.Archivo_Visual, 'insignia_default.png')) as imagen_path,
            NULL as responsable_id
        FROM T_insignias_otorgadas tio
        LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
        LEFT JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
        LEFT JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
        LEFT JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
        LEFT JOIN it_centros itc ON ti.Propone_Insignia = itc.id
        LEFT JOIN responsable_emision re ON itc.id = re.Adscripcion
        WHERE CONCAT(ti.id, '-', pe.Nombre_Periodo) = ?";
    } else {
        // Fallback: usar insigniasotorgadas si existe
        // Verificar estructura dinámica de la tabla destinatario
        $check_destinatario_id_fallback = $conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
        $tiene_id_destinatario_fallback = ($check_destinatario_id_fallback && $check_destinatario_id_fallback->num_rows > 0);
        $campo_id_destinatario_fallback = $tiene_id_destinatario_fallback ? 'id' : 'ID_destinatario';
        
        $query = "SELECT 
            io.ID_otorgada as id,
            io.Codigo_Insignia as codigo,
            CASE 
                WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                ELSE 'Insignia TecNM'
            END as nombre,
            CASE 
                WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                ELSE 'Formación Integral'
            END as categoria,
            d.Nombre_Completo as destinatario,
            NULL as descripcion,
            NULL as criterios,
            'Certificación oficial' as evidencias,
            COALESCE(re.Nombre_Completo, 'Sistema TecNM') as responsable,
            COALESCE(re.Cargo, 'RESPONSABLE DE EMISIÓN') as cargo_responsable,
            io.Fecha_Emision as fecha_emision,
            'Tecnológico Nacional de México' as emisor,
            'Certificación oficial' as evidencia,
            'insignia_default.png' as archivo_visual,
            COALESCE(re.Nombre_Completo, 'Administrador') as responsable_captura,
            'ADMIN001' as codigo_responsable,
            'imagen/Insignias/insignia_default.png' as imagen_path,
            io.Responsable_Emision as responsable_id
        FROM insigniasotorgadas io
        LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario_fallback . "
        LEFT JOIN responsable_emision re ON io.Responsable_Emision = re.id
        WHERE io.Codigo_Insignia = ?";
    }
    
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception("Error al preparar consulta: " . $conexion->error);
    }
    
    // Debug: Log del código que se está buscando
    error_log("DEBUG ver_insignia_completa: Buscando código: " . $codigo_insignia);
    error_log("DEBUG ver_insignia_completa: Formato TECNM: " . ($codigo_tiene_formato_tecnm ? "SÍ" : "NO"));
    error_log("DEBUG ver_insignia_completa: Tabla IO existe: " . ($usar_tabla_io ? "SÍ" : "NO"));
    error_log("DEBUG ver_insignia_completa: Tabla T existe: " . ($usar_tabla_t ? "SÍ" : "NO"));
    
    $stmt->bind_param("s", $codigo_insignia);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Debug: Mostrar información útil para diagnosticar el problema
        $debug_info = "Error: No se encontró la insignia con el código proporcionado: " . htmlspecialchars($codigo_insignia);
        $debug_info .= "<br><br>Formato detectado: " . ($codigo_tiene_formato_tecnm ? "TECNM-OFCM-..." : "ID-Periodo");
        $tabla_usada = "desconocida";
        if ($codigo_tiene_formato_tecnm) {
            $tabla_usada = "insigniasotorgadas";
        } elseif ($usar_tabla_t) {
            $tabla_usada = "T_insignias_otorgadas";
        } else {
            $tabla_usada = "insigniasotorgadas (fallback)";
        }
        $debug_info .= "<br>Tabla usada: " . $tabla_usada;
        $debug_info .= "<br>Campo ID destinatario usado: " . (isset($campo_id_destinatario) ? $campo_id_destinatario : (isset($campo_id_destinatario_fallback) ? $campo_id_destinatario_fallback : "N/A"));
        
        // Intentar buscar en ambas tablas para debug
        if ($usar_tabla_io) {
            $debug_query1 = $conexion->query("SELECT COUNT(*) as total FROM insigniasotorgadas WHERE Codigo_Insignia = '" . $conexion->real_escape_string($codigo_insignia) . "'");
            if ($debug_query1) {
                $debug_row1 = $debug_query1->fetch_assoc();
                $debug_info .= "<br>Registros en insigniasotorgadas con este código: " . $debug_row1['total'];
            }
            // Mostrar algunos códigos similares para debug
            $debug_query2 = $conexion->query("SELECT Codigo_Insignia FROM insigniasotorgadas WHERE Codigo_Insignia LIKE 'TECNM-OFCM-%' ORDER BY ID_otorgada DESC LIMIT 5");
            if ($debug_query2 && $debug_query2->num_rows > 0) {
                $debug_info .= "<br><br>Últimos 5 códigos TECNM-OFCM encontrados en insigniasotorgadas:";
                while ($row_debug = $debug_query2->fetch_assoc()) {
                    $debug_info .= "<br>- " . htmlspecialchars($row_debug['Codigo_Insignia']);
                }
            }
        } else {
            $debug_info .= "<br>⚠️ Tabla insigniasotorgadas NO existe";
        }
        
        if ($usar_tabla_t) {
            $debug_info .= "<br>Tabla T_insignias_otorgadas existe";
        } else {
            $debug_info .= "<br>Tabla T_insignias_otorgadas NO existe";
        }
        
        $debug_info .= "<br><br>Código buscado (exacto): <code>" . htmlspecialchars($codigo_insignia) . "</code>";
        $debug_info .= "<br>Longitud del código: " . strlen($codigo_insignia) . " caracteres";
        
        echo $debug_info;
        exit();
    }
    
    $insignia_data = $result->fetch_assoc();
    $stmt->close();
    
    // Obtener firma digital del responsable si existe (separado para evitar errores si el campo no existe)
    $insignia_data['firma_digital_base64'] = null;
    if (!empty($insignia_data['responsable_id'])) {
        try {
            // Detectar estructura dinámica de responsable_emision
            $check_responsable_id = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'id'");
            $tiene_id_responsable = ($check_responsable_id && $check_responsable_id->num_rows > 0);
            $campo_id_responsable = $tiene_id_responsable ? 'id' : 'ID_responsable';
            
            // Verificar si el campo firma_digital_base64 existe primero
            $check_field = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'firma_digital_base64'");
            if ($check_field && $check_field->num_rows > 0) {
                $sql_firma = "SELECT firma_digital_base64 FROM responsable_emision WHERE " . $campo_id_responsable . " = ? LIMIT 1";
                $stmt_firma = $conexion->prepare($sql_firma);
                if ($stmt_firma) {
                    $stmt_firma->bind_param("i", $insignia_data['responsable_id']);
                    $stmt_firma->execute();
                    $resultado_firma = $stmt_firma->get_result();
                    if ($resultado_firma && $resultado_firma->num_rows > 0) {
                        $fila_firma = $resultado_firma->fetch_assoc();
                        $insignia_data['firma_digital_base64'] = $fila_firma['firma_digital_base64'] ?? null;
                        
                        if (!empty($insignia_data['firma_digital_base64'])) {
                            error_log("✅ Firma digital encontrada para responsable_id: " . $insignia_data['responsable_id']);
                        } else {
                            error_log("⚠️ Firma digital vacía para responsable_id: " . $insignia_data['responsable_id']);
                        }
                    } else {
                        error_log("⚠️ No se encontró firma digital para responsable_id: " . $insignia_data['responsable_id']);
                    }
                    $stmt_firma->close();
                } else {
                    error_log("❌ Error al preparar consulta de firma: " . $conexion->error);
                }
            } else {
                error_log("⚠️ Campo firma_digital_base64 no existe en responsable_emision");
            }
        } catch (Exception $e) {
            // Si hay error, simplemente no se mostrará la firma digital
            error_log("Error al obtener firma digital: " . $e->getMessage());
        }
    } else {
        error_log("⚠️ responsable_id está vacío, no se puede buscar la firma");
    }
    
    // Función para determinar la imagen de la insignia dinámicamente
    function determinarInsigniaDinamica($codigo_insignia, $nombre_insignia) {
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
            'Responsabilidad Social' => 'ResponsabilidadSocial.png'
        ];
        
        foreach ($mapeo_codigos as $codigo => $tipo) {
            if (strpos($codigo_insignia, $codigo) !== false) {
                return $mapeo_imagenes[$tipo] ?? 'EmbajadordelArte.png';
            }
        }
        
        if (isset($mapeo_imagenes[$nombre_insignia])) {
            return $mapeo_imagenes[$nombre_insignia];
        }
        
        return 'EmbajadordelArte.png';
    }
    
    // Obtener descripción y criterios dinámicamente desde la sesión o usar valores por defecto apropiados
    if (isset($_SESSION['insignia_data']) && is_array($_SESSION['insignia_data'])) {
        $sid = $_SESSION['insignia_data'];
        if (!empty($sid['codigo']) && $sid['codigo'] === $codigo_insignia) {
            if (!empty($sid['descripcion'])) {
                $insignia_data['descripcion'] = $sid['descripcion'];
            } else {
                // Valor por defecto dinámico si no hay descripción en sesión
                $insignia_data['descripcion'] = 'Este reconocimiento se otorga por su destacada participación y compromiso con los valores del Tecnológico Nacional de México.';
            }
            if (!empty($sid['criterios'])) {
                $insignia_data['criterios'] = $sid['criterios'];
            } else {
                // Valor por defecto dinámico si no hay criterios en sesión
                $insignia_data['criterios'] = 'Cumplimiento de los criterios establecidos para esta insignia.';
            }
        } else {
            // Si la sesión no coincide con el código actual, usar valores por defecto
            $insignia_data['descripcion'] = 'Este reconocimiento se otorga por su destacada participación y compromiso con los valores del Tecnológico Nacional de México.';
            $insignia_data['criterios'] = 'Cumplimiento de los criterios establecidos para esta insignia.';
        }
    } else {
        // Si no hay sesión, usar valores por defecto
        $insignia_data['descripcion'] = 'Este reconocimiento se otorga por su destacada participación y compromiso con los valores del Tecnológico Nacional de México.';
        $insignia_data['criterios'] = 'Cumplimiento de los criterios establecidos para esta insignia.';
    }
    
    // Determinar la imagen dinámicamente
    $archivo_imagen = determinarInsigniaDinamica($codigo_insignia, $insignia_data['nombre']);
    $insignia_data['imagen_path'] = 'imagen/Insignias/' . $archivo_imagen;
    
    // Inicializar hash de verificación (puede usarse de firmas_digitales si existe esa tabla)
    $hash_verificacion = null;
    
    // Generar URL de validación y código QR
    $server_ip = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (empty($server_ip) || $server_ip === '::1') {
        $server_ip = 'localhost';
    }
    $port = $_SERVER['SERVER_PORT'] ?? '80';
    $base_url = "http://" . $server_ip . ($port != '80' ? ':' . $port : '');
    $url_validacion = $base_url . "/Insignias_TecNM_Funcional/validacion.php?insignia=" . urlencode($codigo_insignia);
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($url_validacion);
    
    // Función para formatear fecha en español
    function formatearFechaEspanol($fecha) {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $timestamp = strtotime($fecha);
        $mes = (int)date('n', $timestamp);
        $anio = date('Y', $timestamp);
        return $meses[$mes] . ' ' . $anio;
    }
    
} catch (Exception $e) {
    echo "Error al obtener los datos de la insignia: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM - <?php echo htmlspecialchars($insignia_data['nombre']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        <?php if ($solo_certificado): ?>
        /* Ocultar metadatos cuando solo se muestra el certificado */
        .metadata-section,
        .actions {
            display: none !important;
        }
        .insignia-section {
            grid-template-columns: 1fr !important;
        }
        <?php endif; ?>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        body {
            margin: 0;
            padding: 0;
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
        
        .header::before {
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
            left: -240px;
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
        
        .header h1 {
            font-size: 32px;
            margin: 0;
            font-weight: 900;
            text-shadow: 
                0 6px 12px rgba(0,0,0,0.5),
                0 0 30px rgba(59, 130, 246, 0.4),
                0 0 60px rgba(59, 130, 246, 0.2);
            letter-spacing: -0.5px;
        }
        
        .content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .insignia-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            border: 2px solid #1b396a;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        
        .insignia-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .insignia-hexagon {
            width: 200px;
            height: 200px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin: 0 auto 30px;
            border: 2px solid #1b396a;
            border-radius: 8px;
        }
        
        .document-preview {
            position: relative;
            width: 100%;
            max-width: 6in;
            height: auto;
            min-height: 750px;
            aspect-ratio: 8.5 / 11;
            background: white;
            border: 2px solid #1b396a;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            background-image: url('imagen/Hoja_membrentada.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0 auto;
        }
        
        .document-insignia {
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .metadata-section {
            margin-top: 30px;
        }
        
        .metadata-section h2 {
            color: #1b396a;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #1b396a;
            padding-bottom: 10px;
        }
        
        .metadata-item {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #1b396a;
            border-radius: 4px;
        }
        
        .metadata-item strong {
            color: #1b396a;
            display: block;
            margin-bottom: 5px;
        }
        
        .metadata-item span {
            color: #333;
            line-height: 1.5;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn {
            background-color: #1b396a;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 10px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #0f2a4a;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        footer {
            background: #1e3c72;
            color: white;
            padding: 40px 0;
            margin-top: 50px;
            text-align: center;
            width: 100%;
            left: 0;
            right: 0;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-section {
            margin-bottom: 25px;
        }
        
        .footer-section h3 {
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
        
        footer p {
            margin: 5px 0;
        }
        
        /* Responsive - Tablet */
        @media (max-width: 1024px) {
            .container {
                padding: 15px;
            }
            
            .insignia-section {
                gap: 15px;
            }
            
            .document-preview {
                padding: 30px;
            }
        }
        
        /* Responsive - Móviles y tablets pequeñas */
        @media (max-width: 768px) {
            .header {
                padding: 20px 0;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .header-logo {
                height: 40px;
                left: -120px;
            }
            
            .container {
                padding: 10px;
            }
            
            .content {
                padding: 20px;
            }
            
            .insignia-section {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 15px;
            }
            
            .insignia-preview {
                flex-direction: column;
                text-align: center;
            }
            
            .insignia-hexagon {
                margin-right: 0;
                margin-bottom: 20px;
                width: 150px;
                height: 150px;
            }
            
            .document-preview {
                height: auto;
                min-height: 500px;
                padding: 20px;
                font-size: 12px;
            }
            
            .metadata-section {
                margin-top: 20px;
            }
            
            .metadata-item {
                padding: 8px;
                font-size: 14px;
            }
            
            .metadata-item strong {
                font-size: 13px;
            }
            
            .actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                padding: 14px 20px;
                font-size: 14px;
            }
            
            footer {
                padding: 30px 15px;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        /* Responsive - Móviles pequeños */
        @media (max-width: 480px) {
            .header {
                padding: 15px 0;
            }
            
            .header h1 {
                font-size: 18px;
            }
            
            .header-logo {
                height: 35px;
                left: -100px;
            }
            
            .container {
                padding: 5px;
            }
            
            .content {
                padding: 15px;
            }
            
            .insignia-section {
                padding: 10px;
            }
            
            .insignia-hexagon {
                width: 120px;
                height: 120px;
            }
            
            .document-preview {
                padding: 15px;
                font-size: 11px;
                min-height: 400px;
            }
            
            .metadata-item {
                padding: 6px;
                font-size: 12px;
            }
            
            .metadata-item strong {
                font-size: 12px;
            }
            
            .btn {
                padding: 12px 16px;
                font-size: 13px;
            }
            
            footer {
                padding: 20px 10px;
                font-size: 11px;
            }
        }
        
        /* Orientación horizontal en móviles */
        @media (max-width: 768px) and (orientation: landscape) {
            .header {
                padding: 12px 0;
            }
            
            .document-preview {
                min-height: 350px;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER PROFESIONAL -->
    <header class="header">
        <div class="header-content">
            <img src="imagen/logo.png" alt="TecNM Logo" class="header-logo" onerror="this.style.display='none';">
            <h1>Insignias TecNM</h1>
        </div>
    </header>
        
    <div class="container">
        <?php if (isset($_GET['registrado']) && $_GET['registrado'] == '1'): ?>
            <div style="background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3); display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 40px;">✅</div>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 10px 0; font-size: 20px;">¡Reconocimiento Registrado Exitosamente!</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.95;">
                        La insignia ha sido registrada en la base de datos.
                        <?php if (isset($_SESSION['correo_enviado']) && $_SESSION['correo_enviado']): ?>
                            <br><strong style="color: #28a745;">✅ Correo de notificación enviado exitosamente a: <?php echo htmlspecialchars($_SESSION['correo_destinatario'] ?? ''); ?></strong>
                        <?php elseif (isset($_SESSION['correo_enviado']) && !$_SESSION['correo_enviado']): ?>
                            <br><span style="color: #ffc107; font-weight: bold;">⚠️ El correo no pudo ser enviado, pero la insignia fue registrada correctamente.</span>
                            <?php if (isset($_SESSION['correo_error'])): ?>
                                <br><small style="color: #dc3545;">Error: <?php echo htmlspecialchars($_SESSION['correo_error']); ?></small>
                                <br><small style="color: #6c757d;">Solución: Edita config_smtp.php con tus credenciales correctas y prueba con probar_correo.php</small>
                            <?php endif; ?>
                        <?php else: ?>
                            <br><span style="color: #6c757d;">ℹ️ El correo no se intentó enviar (correo no válido o no configurado).</span>
                        <?php endif; ?>
                    </p>
                </div>
                <button onclick="this.parentElement.style.display='none'" style="background: rgba(255,255,255,0.2); border: 2px solid white; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">✕</button>
            </div>
            <?php 
            // Limpiar variables de sesión después de mostrarlas
            unset($_SESSION['correo_enviado']);
            unset($_SESSION['correo_destinatario']);
            unset($_SESSION['correo_error']);
            ?>
        <?php endif; ?>
        <div class="content">
            <div class="insignia-section">
                <div class="insignia-preview">
                    <div class="insignia-hexagon" style="background-image: url(<?php echo $insignia_data['imagen_path']; ?>);">
                        <!-- La imagen se carga directamente desde PHP -->
                    </div>
                    
                    <div class="document-preview">
                    <!-- Título institucional -->
                    <div style="font-size: 22px; font-weight: bold; color: #1b396a; margin-top: 40px; margin-bottom: 8px; text-align: center;">
                        EL TECNOLÓGICO NACIONAL DE MÉXICO
                    </div>
                    <div style="font-size: 18px; color: #1b396a; margin-bottom: 20px; text-align: center;">
                        OTORGA EL PRESENTE
                    </div>
                    
                    <!-- Título principal del reconocimiento -->
                    <div style="font-size: 26px; font-weight: bold; color: #d4af37; margin-bottom: 15px; text-align: center; text-transform: uppercase; line-height: 1.2;">
                        RECONOCIMIENTO INSTITUCIONAL<br>
                        CON IMPACTO CURRICULAR
                    </div>
                    
                    <!-- Destinatario -->
                    <div style="font-size: 18px; margin-bottom: 5px; text-align: center; color: #666;">A</div>
                    <div style="font-size: 28px; font-weight: bold; color: #333; margin-bottom: 20px; text-align: center;">
                        <?php echo htmlspecialchars($insignia_data['destinatario']); ?>
                    </div>
                    
                    <!-- Texto descriptivo (ajustable automáticamente según longitud) -->
                    <?php 
                    $descripcion = htmlspecialchars($insignia_data['descripcion']);
                    $descripcion_length = strlen($descripcion);
                    // Ajustar tamaño de fuente automáticamente según longitud del texto (más agresivo para textos largos)
                    if ($descripcion_length > 1000) {
                        $font_size = 12;
                        $line_height = 1.5;
                        $margin_bottom = 35;
                    } elseif ($descripcion_length > 800) {
                        $font_size = 13;
                        $line_height = 1.55;
                        $margin_bottom = 40;
                    } elseif ($descripcion_length > 600) {
                        $font_size = 14;
                        $line_height = 1.6;
                        $margin_bottom = 45;
                    } elseif ($descripcion_length > 400) {
                        $font_size = 15;
                        $line_height = 1.65;
                        $margin_bottom = 50;
                    } else {
                        $font_size = 18;
                        $line_height = 1.8;
                        $margin_bottom = 60;
                    }
                    ?>
                    <div style="font-size: <?php echo $font_size; ?>px; text-align: justify; line-height: <?php echo $line_height; ?>; margin-bottom: <?php echo $margin_bottom; ?>px; padding: 0 50px; color: #333; word-wrap: break-word; hyphens: auto;">
                        <?php echo nl2br($descripcion); ?>
                    </div>
                    
                    <!-- Código QR de Verificación con imagen de insignia en el centro -->
                    <div style="position: absolute; bottom: 40px; left: 40px; width: 90px; height: 90px; background: white; padding: 5px; border: 1px solid #e5e7eb; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="position: relative; width: 100%; height: 100%;">
                            <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="Código QR de Verificación" style="width: 100%; height: 100%; object-fit: contain; display: block;">
                            <img src="<?php echo htmlspecialchars($insignia_data['imagen_path']); ?>" alt="<?php echo htmlspecialchars($insignia_data['nombre']); ?>" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 25px; height: 25px; background: white; border-radius: 4px; padding: 2px; border: 1px solid #1b396a; object-fit: contain;">
                        </div>
                    </div>
                    
                    <!-- Firma en la esquina inferior derecha -->
                    <div style="position: absolute; bottom: 25px; right: 60px; text-align: left; font-size: 9px; color: #333; max-width: 300px;">
                        <?php if (!empty($insignia_data['firma_digital_base64'])): ?>
                        <!-- Mostrar el SELLO DIGITAL REAL del SAT (como en la imagen 2) -->
                        <div style="font-size: 5px; font-family: 'Courier New', monospace; color: #000; word-break: break-all; line-height: 1.1; margin-bottom: 8px; letter-spacing: 0px; background: rgba(255,255,255,0.95); padding: 4px; border: 1px solid #ddd; border-radius: 2px;">
                            <?php echo htmlspecialchars($insignia_data['firma_digital_base64']); ?>
                        </div>
                        <?php endif; ?>
                        <div style="font-weight: bold; color: #1b396a; margin-top: 4px; font-size: 11px;"><?php echo htmlspecialchars($insignia_data['responsable'] ?? $insignia_data['responsable_captura'] ?? 'Responsable'); ?></div>
                        <div style="font-size: 8px; color: #666; margin-top: 2px;"><?php echo htmlspecialchars($insignia_data['cargo_responsable'] ?? 'RESPONSABLE DE EMISIÓN'); ?></div>
                    </div>
                    
                    <!-- Fecha y ubicación -->
                    <div style="position: absolute; bottom: 10px; right: 60px; font-size: 7px; color: #666; text-align: right; background: rgba(255,255,255,0.9); padding: 4px; border-radius: 2px; width: 80px;">
                        CIUDAD DE MÉXICO<br>
                        <?php echo formatearFechaEspanol($insignia_data['fecha_emision']); ?>
                    </div>
                </div>
                </div>
                
                <div class="metadata-section">
                    <h2>Metadatos de la Insignia</h2>
                
                <div class="metadata-item">
                    <strong>Código de identificación de la InsigniaTecNM:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['codigo']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Nombre de la InsigniaTecNM (Subcategoría):</strong>
                    <span><?php echo htmlspecialchars($insignia_data['nombre']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Categoría de la InsigniaTecNM:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['categoria']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Destinatario:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['destinatario']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Descripción:</strong>
                    <span><?php echo nl2br(htmlspecialchars($insignia_data['descripcion'])); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Criterios para su emisión:</strong>
                    <span><?php echo nl2br(htmlspecialchars($insignia_data['criterios'])); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Fecha de emisión:</strong>
                    <span><?php echo date('d-m-Y', strtotime($insignia_data['fecha_emision'])); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Emisor (TecNM o Instituto/Centro):</strong>
                    <span><?php echo htmlspecialchars($insignia_data['emisor']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Evidencia:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['evidencia'] ?: 'Sin evidencia registrada'); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Archivo Visual de la InsigniaTecNM:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['archivo_visual']); ?> (archivo)</span>
                </div>
                
                <div class="metadata-item">
                    <strong>Responsable de la captura de los Metadatos:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['responsable']); ?></span>
                </div>
                
                <div class="metadata-item">
                    <strong>Código de identificación del Responsable de la captura de los Metadatos:</strong>
                    <span><?php echo htmlspecialchars($insignia_data['codigo_responsable']); ?></span>
                </div>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <button onclick="window.print()" class="btn">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="imagen_clickeable.php?codigo=<?php echo isset($codigo_insignia) ? urlencode($codigo_insignia) : ''; ?>" class="btn" style="text-decoration: none; display: inline-block;">
                <i class="fas fa-share-alt"></i> Compartir Públicamente
            </a>
            <a href="historial_insignias.php" class="btn" style="text-decoration: none; display: inline-block;">
                <i class="fas fa-arrow-left"></i> Volver al Historial
            </a>
        </div>
    </div>
    
    <!-- FOOTER PROFESIONAL -->
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
