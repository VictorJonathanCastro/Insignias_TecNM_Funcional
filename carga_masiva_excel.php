<?php
// SISTEMA DE CARGA MASIVA VIA EXCEL - Insignias TecNM
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (!ob_get_level()) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

if (basename($_SERVER['PHP_SELF']) === 'carga_masiva_excel.php') {
    try {
        $conexion_loaded = @include_once __DIR__ . '/conexion.php';
        if ($conexion_loaded === false || !isset($conexion) || !$conexion) {
            throw new Exception('No se pudo conectar a la base de datos. Revise conexion.php');
        }
        if (isset($conexion->connect_errno) && $conexion->connect_errno) {
            throw new Exception('Error MySQL: ' . (isset($conexion->connect_error) ? $conexion->connect_error : 'Sin detalle'));
        }
        $rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
        if (!isset($_SESSION['usuario_id']) || !in_array($rol, array('Admin', 'Administrador'), true)) {
            if (ob_get_level()) { @ob_clean(); }
            header('Location: login.php');
            exit;
        }
    } catch (Exception $e) {
        if (ob_get_level()) { @ob_clean(); }
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title></head><body style="font-family:sans-serif;padding:20px">';
        echo '<h1>Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><a href="modulo_de_administracion.php">Volver al panel</a></p></body></html>';
        exit;
    }
} else {
    // Si se está incluyendo, solo cargar la conexión si no está definida
    if (!isset($conexion)) {
        require_once __DIR__ . '/conexion.php';
    }
}

// Incluir librería para leer Excel
$vendor_path = __DIR__ . '/vendor/autoload.php';
if (!file_exists($vendor_path)) {
    if (ob_get_level()) { @ob_clean(); }
    http_response_code(200);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Carga Masiva</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .error-container {
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                max-width: 600px;
                text-align: center;
            }
            .error-icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            h1 {
                color: #dc3545;
                margin-bottom: 20px;
            }
            p {
                color: #666;
                line-height: 1.6;
                margin-bottom: 20px;
            }
            .code {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                font-family: monospace;
                margin: 20px 0;
                text-align: left;
            }
            .btn {
                background: linear-gradient(135deg, #28a745, #20c997);
                color: white;
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
                text-decoration: none;
                display: inline-block;
                margin-top: 20px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">⚠️</div>
            <h1>Dependencias Faltantes</h1>
            <p>Para usar la carga masiva de Excel, necesitas instalar las dependencias de Composer.</p>
            <p><strong>Ejecuta en el servidor:</strong></p>
            <div class="code">
                composer install
            </div>
            <p>O si no tienes Composer instalado, ejecuta:</p>
            <div class="code">
                php composer.phar install
            </div>
            <a href="modulo_de_administracion.php" class="btn">← Volver al Panel</a>
        </div>
    </body>
    </html>';
    exit();
}

try {
    require_once $vendor_path;
} catch (Throwable $e) {
    // Limpiar cualquier salida previa
    if (ob_get_level()) {
        ob_clean();
    }
    // Usar 200 en lugar de 500 para mostrar el mensaje de error correctamente
    http_response_code(200);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Error - Carga Masiva</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
            .error-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
            h1 { color: #dc3545; }
            .error-details { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; font-family: monospace; white-space: pre-wrap; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>Error al cargar dependencias</h1>
            <p>No se pudo cargar la librería de Excel. Error:</p>
            <div class="error-details">' . htmlspecialchars($e->getMessage()) . '</div>
            <p><a href="modulo_de_administracion.php">← Volver al Panel</a></p>
        </div>
    </body>
    </html>';
    exit();
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class CargaMasivaExcel {
    private $conexion;
    private $errores = [];
    private $exitos = [];
    private $archivo_temporal;
    private $id_historial = null;
    private $estadisticas = [
        'insertados' => 0,
        'actualizados' => 0,
        'errores' => 0,
        'firmadas' => 0
    ];
    private $firmar_insignias = false;
    private $certificado_path = null;
    private $clave_privada_path = null;
    private $contrasena_firma = null;
    private $firma_digital = null;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Configurar firma digital para las insignias
     */
    public function configurarFirmaDigital($certificado_path, $clave_privada_path, $contrasena) {
        $this->firmar_insignias = true;
        $this->certificado_path = $certificado_path;
        $this->clave_privada_path = $clave_privada_path;
        $this->contrasena_firma = $contrasena;
        
        // Inicializar clase de firma digital
        require_once 'firma_digital_real.php';
        $this->firma_digital = new FirmaDigitalReal($this->conexion);
    }
    
    /**
     * Obtener estadísticas
     */
    public function getEstadisticas() {
        return $this->estadisticas;
    }
    
    /**
     * Registrar carga en el historial
     */
    public function registrarHistorial($nombre_archivo, $tipo_carga, $usuario_id, $usuario_nombre, $tamanio_archivo, $total_registros, $registros_exitosos, $registros_actualizados, $registros_con_error, $estado) {
        try {
            // Verificar si la tabla existe
            $check_table = $this->conexion->query("SHOW TABLES LIKE 'historial_cargas_masivas'");
            if ($check_table->num_rows == 0) {
                // La tabla no existe, no podemos registrar
                return false;
            }
            
            $sql = "INSERT INTO historial_cargas_masivas 
                    (nombre_archivo, tipo_carga, usuario_id, usuario_nombre, tamanio_archivo, 
                     total_registros, registros_exitosos, registros_actualizados, registros_con_error, estado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conexion->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error al preparar consulta de historial: " . $this->conexion->error);
            }
            $stmt->bind_param("ssisiiiiis", 
                $nombre_archivo,
                $tipo_carga,
                $usuario_id,
                $usuario_nombre,
                $tamanio_archivo,
                $total_registros,
                $registros_exitosos,
                $registros_actualizados,
                $registros_con_error,
                $estado
            );
            
            if ($stmt->execute()) {
                $this->id_historial = $this->conexion->insert_id;
                return $this->id_historial;
            }
            return false;
        } catch (Exception $e) {
            // Si hay error, no fallar la carga, solo loguear
            error_log("Error al registrar historial: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener ID del historial
     */
    public function getIdHistorial() {
        return $this->id_historial;
    }
    
    /**
     * Procesar archivo Excel para carga masiva
     */
    public function procesarArchivo($archivo, $tipo_carga) {
        try {
            // Validar archivo
            if (!$this->validarArchivo($archivo)) {
                return false;
            }
            
            // Leer archivo Excel
            $spreadsheet = IOFactory::load($archivo['tmp_name']);
            
            // Si es carga completa, procesar todas las hojas
            if ($tipo_carga === 'todas_las_tablas') {
                // Si hay firma digital configurada, se usará solo para insignias otorgadas
                return $this->procesarTodasLasHojas($spreadsheet);
            }
            
            // Procesar una sola hoja (comportamiento original)
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            $active_sheet_index = $spreadsheet->getActiveSheetIndex();
            
            // Procesar según el tipo de carga
            switch ($tipo_carga) {
                case 'insignias_otorgadas':
                    // Pasar spreadsheet y sheet_index para leer fechas correctamente
                    return $this->cargarInsigniasOtorgadas($data, null, $spreadsheet, $active_sheet_index);
                case 'destinatarios':
                    return $this->cargarDestinatarios($data);
                case 'centros_it':
                    return $this->cargarCentrosIT($data);
                case 'tipos_insignia':
                    return $this->cargarTiposInsignia($data);
                case 'categorias_insignia':
                    return $this->cargarCategoriasInsignia($data);
                case 'periodos_emision':
                    return $this->cargarPeriodosEmision($data);
                default:
                    $this->errores[] = "Tipo de carga no válido: $tipo_carga";
                    return false;
            }
            
        } catch (Exception $e) {
            $this->errores[] = "Error al procesar archivo: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Procesar todas las hojas del Excel automáticamente
     */
    private function procesarTodasLasHojas($spreadsheet) {
        $total_hojas = $spreadsheet->getSheetCount();
        $hojas_procesadas = 0;
        $hojas_con_error = 0;
        
        $this->exitos[] = "📊 Iniciando procesamiento de $total_hojas hoja(s) del Excel...";
        
        // Mapeo de nombres de hojas a tipos de carga (orden: más específico primero para no confundir con "insignias")
        $mapeo_hojas = [
            'categorias_insignia' => ['cat_insignias', 't_cat_insignias', 'categorias', 'categorías', 'categorias insignia'],
            'tipos_insignia' => ['tipo_insignia', 't_tipo_insignia', 't_insignias', 'insignias_maestras', 'tipos', 'tipos insignia', 'tipos de insignia'],
            'centros_it' => ['t_centros', 'centros', 'centros it', 'institutos'],
            'periodos_emision' => ['periodos', 'periodos emision', 'periodos de emision'],
            'estatus' => ['estatus', 'estados', 'status'],
            'responsables_emision' => ['t_responsable_emision', 'responsable_emision', 'responsables', 'responsables emision', 'responsables de emision'],
            'destinatarios' => ['t_desti', 'desti', 'destinatarios', 'estudiantes', 'personas'],
            'insignias_otorgadas' => ['t_insignias_otorgadas', 'insignias otorgadas', 'insigniasotorgadas', 'otorgadas']
        ];
        
        // Procesar cada hoja
        for ($i = 0; $i < $total_hojas; $i++) {
            $sheet = $spreadsheet->getSheet($i);
            $nombre_hoja = strtolower(trim($sheet->getTitle() ?? ''));
            $data = $sheet->toArray();
            
            if (empty($data) || count($data) < 2) {
                $this->errores[] = "Hoja '$nombre_hoja': Está vacía o no tiene datos suficientes";
                $hojas_con_error++;
                continue;
            }
            
            // Detectar tipo de tabla por headers
            $headers_originales = $data[0];
            $headers_normalizados = array_map('strtolower', array_map(function ($v) { return trim($v ?? ''); }, $headers_originales));
            $tipo_detectado = $this->detectarTipoTabla($headers_normalizados, $nombre_hoja, $mapeo_hojas);
            
            if (!$tipo_detectado) {
                $this->errores[] = "Hoja '$nombre_hoja': No se pudo detectar el tipo de tabla. Headers encontrados: " . implode(', ', $headers_originales);
                $hojas_con_error++;
                continue;
            }
            
            // Procesar la hoja según el tipo detectado
            $this->exitos[] = "📄 Procesando hoja '$nombre_hoja' como: $tipo_detectado";
            $data_sin_headers = array_slice($data, 1); // Quitar la fila de headers
            
            $resultado = false;
            switch ($tipo_detectado) {
                case 'insignias_otorgadas':
                    // Si hay firma digital configurada (viene de todas_las_tablas), se aplicará aquí
                    // Pasar spreadsheet y sheet_index para leer fechas correctamente
                    $resultado = $this->cargarInsigniasOtorgadas($data_sin_headers, $headers_originales, $spreadsheet, $i);
                    break;
                case 'destinatarios':
                    $resultado = $this->cargarDestinatarios($data_sin_headers, $headers_originales);
                    break;
                case 'centros_it':
                    $resultado = $this->cargarCentrosIT($data_sin_headers, $headers_originales);
                    break;
                case 'tipos_insignia':
                    $resultado = $this->cargarTiposInsignia($data_sin_headers, $headers_originales);
                    break;
                case 'categorias_insignia':
                    $resultado = $this->cargarCategoriasInsignia($data_sin_headers, $headers_originales);
                    break;
                case 'periodos_emision':
                    $resultado = $this->cargarPeriodosEmision($data_sin_headers, $headers_originales);
                    break;
                case 'estatus':
                    $resultado = $this->cargarEstatus($data_sin_headers, $headers_originales);
                    break;
                case 'responsables_emision':
                    $resultado = $this->cargarResponsablesEmision($data_sin_headers, $headers_originales);
                    break;
            }
            
            if ($resultado) {
                $hojas_procesadas++;
                $this->exitos[] = "✅ Hoja '$nombre_hoja' procesada correctamente";
            } else {
                $hojas_con_error++;
                $this->errores[] = "❌ Hoja '$nombre_hoja' tuvo errores al procesar";
            }
        }
        
        // Resumen final
        $this->exitos[] = "📊 RESUMEN: $hojas_procesadas hoja(s) procesada(s) exitosamente, $hojas_con_error hoja(s) con errores";
        
        return $hojas_procesadas > 0;
    }
    
    /**
     * Detectar tipo de tabla basándose en los headers
     */
    private function detectarTipoTabla($headers, $nombre_hoja, $mapeo_hojas) {
        // Primero intentar por nombre de hoja
        foreach ($mapeo_hojas as $tipo => $nombres) {
            foreach ($nombres as $nombre) {
                if (stripos($nombre_hoja, $nombre) !== false) {
                    return $tipo;
                }
            }
        }
        
        // Si no se detecta por nombre, intentar por headers
        $headers_str = implode(' ', $headers);
        
        // PRIORIDAD: Insignias Otorgadas (insigniasotorgadas) - verificar PRIMERO
        $tiene_codigo = in_array('codigo_insignia', $headers) || in_array('código_insignia', $headers);
        $tiene_dest = in_array('destinatario', $headers) || in_array('id_destinatario', $headers);
        if ($tiene_codigo && $tiene_dest) {
            return 'insignias_otorgadas';
        }
        if (in_array('id_insignia', $headers) && in_array('id_destinatario', $headers)) {
            return 'insignias_otorgadas';
        }
        
        // Destinatarios - solo si NO tiene Codigo_Insignia (para evitar confusión)
        if (!$tiene_codigo && !in_array('id_insignia', $headers)) {
            $tiene_nombre = in_array('nombre_completo', $headers) || in_array('nombre completo', $headers);
            $tiene_id = in_array('matricula', $headers) || in_array('correo', $headers) || in_array('curp', $headers) || in_array('correo_inst', $headers) || in_array('correo_per', $headers);
            if ($tiene_nombre && $tiene_id) {
                return 'destinatarios';
            }
        }
        
        // Centros IT
        if (in_array('nombre_itc', $headers) || in_array('acron', $headers)) {
            return 'centros_it';
        }
        
        // Tipos de Insignia
        if (in_array('nombre_insignia', $headers) || in_array('nombre_ins', $headers)) {
            return 'tipos_insignia';
        }
        
        // Categorías
        if (in_array('nombre_cat', $headers) || in_array('nombre_categoria', $headers)) {
            return 'categorias_insignia';
        }
        
        // Periodos
        if (in_array('periodo', $headers) || in_array('nombre_periodo', $headers)) {
            return 'periodos_emision';
        }
        
        // Estatus
        if (in_array('nombre_estatus', $headers) || in_array('estatus', $headers)) {
            return 'estatus';
        }
        
        // Responsables de Emisión
        if (in_array('nombre_completo', $headers) && (in_array('adscripcion', $headers) || in_array('cargo', $headers))) {
            // Verificar que no sea destinatario (destinatarios tienen matricula o curp)
            if (!in_array('matricula', $headers) && !in_array('curp', $headers)) {
                return 'responsables_emision';
            }
        }
        
        return false;
    }
    
    /**
     * Validar archivo Excel
     */
    private function validarArchivo($archivo) {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $this->errores[] = "Error al subir archivo";
            return false;
        }
        
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls'])) {
            $this->errores[] = "Formato de archivo no válido. Use Excel (.xlsx o .xls)";
            return false;
        }
        
        if ($archivo['size'] > 10 * 1024 * 1024) { // 10MB
            $this->errores[] = "Archivo demasiado grande. Máximo 10MB";
            return false;
        }
        
        return true;
    }
    
    /**
     * Cargar insignias otorgadas desde Excel
     */
    private function cargarInsigniasOtorgadas($data, $headers_provided = null, $spreadsheet = null, $sheet_index = null) {
        // Si se proporcionan headers, usarlos; si no, tomar la primera fila
        if ($headers_provided !== null) {
            $headers = $headers_provided;
        } else {
            $headers = array_shift($data); // Primera fila son headers
        }
        $procesados = 0;
        
        // Si tenemos acceso al spreadsheet, usarlo para leer fechas correctamente
        $sheet = null;
        if ($spreadsheet !== null && $sheet_index !== null) {
            $sheet = $spreadsheet->getSheet($sheet_index);
        }
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue; // Saltar filas vacías
            
            try {
                // Si tenemos acceso a la hoja, convertir fechas de Excel correctamente
                if ($sheet !== null) {
                    $fila_excel = $fila + 2; // +2 porque fila 1 es headers y empezamos desde 0
                    $col_fecha = null;
                    $alias_fecha_loop = ['fecha_emision', 'fecha emision', 'fecha', 'fecha_otorgamiento'];
                    foreach ($headers as $idx => $header) {
                        $h = strtolower(trim((string)($header ?? '')));
                        if (in_array($h, $alias_fecha_loop, true) || str_replace(' ', '_', $h) === 'fecha_emision') {
                            $col_fecha = $idx;
                            break;
                        }
                    }
                    
                    if ($col_fecha !== null) {
                        $col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_fecha + 1);
                        $cell = $sheet->getCell($col_letter . $fila_excel);
                        
                        // Si la celda contiene una fecha de Excel (número serial), convertirla
                        if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                            $excel_date = $cell->getValue();
                            $php_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excel_date);
                            $row[$col_fecha] = $php_date->format('Y-m-d');
                        } else {
                            // Intentar obtener el valor formateado
                            $formatted = $cell->getFormattedValue();
                            if (!empty($formatted)) {
                                // Corregir formato erróneo común: YYYY-MM-YYYY (ej: 2025-08-2025)
                                if (preg_match('/^(\d{4})-(\d{1,2})-(\d{4})$/', $formatted, $matches)) {
                                    if ($matches[1] === $matches[3]) {
                                        // Año duplicado, corregir a YYYY-MM-01
                                        $anio = $matches[1];
                                        $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                                        $formatted = "$anio-$mes-01";
                                    }
                                }
                                // Detectar formato YYYY-MM (sin día) y agregar día 01
                                elseif (preg_match('/^(\d{4})-(\d{1,2})$/', $formatted, $matches)) {
                                    $anio = $matches[1];
                                    $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                                    $formatted = "$anio-$mes-01";
                                }
                                $row[$col_fecha] = $formatted;
                            }
                        }
                    }
                }
                
                // Validar datos requeridos
                $datos = $this->validarDatosInsigniaOtorgada($row, $headers, $fila + 2);
                if (!$datos) continue;
                
                // Insertar en base de datos (tabla insigniasotorgadas)
                $sql = "INSERT INTO insigniasotorgadas 
                        (Codigo_Insignia, Destinatario, Periodo_Emision, Responsable_Emision, Estatus, Fecha_Emision) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->conexion->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error al preparar consulta de insignia otorgada: " . $this->conexion->error);
                }
                $stmt->bind_param("siiiss", 
                    $datos['Codigo_Insignia'],
                    $datos['Destinatario'],
                    $datos['Periodo_Emision'],
                    $datos['Responsable_Emision'],
                    $datos['Estatus'],
                    $datos['Fecha_Emision']
                );
                
                if ($stmt->execute()) {
                    $insignia_id = $this->conexion->insert_id;
                    $procesados++;
                    
                    // Si está habilitada la firma digital, firmar la insignia
                    if ($this->firmar_insignias && $this->firma_digital) {
                        $resultado_firma = $this->firmarInsignia($insignia_id, $datos);
                        if ($resultado_firma['success']) {
                            $this->estadisticas['firmadas']++;
                            $this->exitos[] = "Fila " . ($fila + 2) . ": Insignia otorgada registrada y firmada correctamente (Código: {$datos['Codigo_Insignia']})";
                        } else {
                            $this->exitos[] = "Fila " . ($fila + 2) . ": Insignia otorgada registrada correctamente (Código: {$datos['Codigo_Insignia']}, error al firmar: " . $resultado_firma['error'] . ")";
                        }
                    } else {
                        $this->exitos[] = "Fila " . ($fila + 2) . ": Insignia otorgada registrada correctamente (Código: {$datos['Codigo_Insignia']})";
                    }
                } else {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Error al insertar - " . $stmt->error;
                }
                
            } catch (Exception $e) {
                $this->errores[] = "Fila " . ($fila + 2) . ": " . $e->getMessage();
            }
        }
        
        return $procesados > 0;
    }
    
    /**
     * Firmar una insignia después de insertarla
     */
    private function firmarInsignia($insignia_id, $datos_insignia) {
        try {
            // Obtener datos completos de la insignia para la firma (desde insigniasotorgadas)
            // Verificar estructura de la tabla destinatario
            $check_destinatario = $this->conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
            $campo_id_destinatario = ($check_destinatario && $check_destinatario->num_rows > 0) ? 'id' : 'ID_destinatario';
            
            $sql = "SELECT 
                        io.Codigo_Insignia,
                        io.Destinatario,
                        io.Fecha_Emision,
                        d.Nombre_Completo as destinatario,
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
                        io.Estatus
                    FROM insigniasotorgadas io
                    LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
                    WHERE io.ID_otorgada = ?";
            
            $stmt = $this->conexion->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error al preparar consulta de firma: " . $this->conexion->error);
            }
            $stmt->bind_param("i", $insignia_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                return ['success' => false, 'error' => 'No se encontró la insignia'];
            }
            
            $insignia_data = $result->fetch_assoc();
            
            // Usar el código de insignia que ya existe
            $codigo_insignia = $insignia_data['Codigo_Insignia'] ?? 'TECNM-' . date('Y') . '-' . str_pad($insignia_id, 6, '0', STR_PAD_LEFT);
            
            // Obtener responsable (usar el de sesión o uno por defecto)
            $responsable = $_SESSION['nombre'] ?? 'Responsable de Emisión';
            
            // Preparar datos para la firma
            $datos_firma = [
                'destinatario' => $insignia_data['destinatario'] ?? 'N/A',
                'nombre_insignia' => $insignia_data['nombre_insignia'] ?? 'Insignia',
                'codigo_insignia' => $codigo_insignia,
                'fecha_emision' => date('d/m/Y', strtotime($insignia_data['Fecha_Emision'] ?? date('Y-m-d'))),
                'responsable' => $responsable
            ];
            
            // Generar texto para firmar
            $texto_firma = $this->firma_digital->generarTextoInsignia($datos_firma);
            
            // Generar firma digital
            $resultado = $this->firma_digital->generarFirmaDigitalReal(
                $texto_firma,
                $this->certificado_path,
                $this->clave_privada_path,
                $this->contrasena_firma
            );
            
            if (!$resultado['success']) {
                return $resultado;
            }
            
            // Guardar la firma en la base de datos (actualizar la tabla T_insignias_otorgadas si tiene campo de firma)
            // O guardar en responsable_emision si es necesario
            // Por ahora, solo retornamos éxito ya que la firma se puede usar después
            
            return [
                'success' => true,
                'firma_base64' => $resultado['firma_base64'],
                'codigo_insignia' => $codigo_insignia
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Cargar destinatarios desde Excel
     */
    private function cargarDestinatarios($data, $headers_provided = null) {
        // Si se proporcionan headers, usarlos; si no, tomar la primera fila
        if ($headers_provided !== null) {
            $headers = $headers_provided;
        } else {
            $headers = array_shift($data);
        }
        $procesados = 0;
        
        // Detectar qué columnas existen en la tabla destinatario
        $columnas_disponibles = [];
        $result = $this->conexion->query("SHOW COLUMNS FROM destinatario");
        if ($result) {
            while ($col = $result->fetch_assoc()) {
                $columnas_disponibles[] = $col['Field'];
            }
        }
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            
            try {
                $datos = $this->validarDatosDestinatario($row, $headers, $fila + 2);
                if (!$datos) continue;
                
                // Construir nombre completo si no viene directamente
                if (empty($datos['Nombre_Completo']) && !empty($datos['Nombre'])) {
                    $nombre_completo = trim($datos['Nombre'] . ' ' . 
                                         ($datos['Apellido_Paterno'] ?? '') . ' ' . 
                                         ($datos['Apellido_Materno'] ?? ''));
                    $datos['Nombre_Completo'] = $nombre_completo;
                }
                
                // Construir SQL adaptativo según columnas disponibles
                $campos = [];
                $valores = [];
                $tipos = '';
                $params = [];
                
                // Campos básicos que siempre deben existir
                if (in_array('Nombre_Completo', $columnas_disponibles) && !empty($datos['Nombre_Completo'])) {
                    $campos[] = 'Nombre_Completo';
                    $valores[] = '?';
                    $tipos .= 's';
                    $params[] = $datos['Nombre_Completo'];
                }
                
                // ITCentro (no Id_Centro)
                if (in_array('ITCentro', $columnas_disponibles) && !empty($datos['ITCentro'])) {
                    $campos[] = 'ITCentro';
                    $valores[] = '?';
                    $tipos .= 'i';
                    $params[] = (int)$datos['ITCentro'];
                }
                
                // Campos opcionales
                if (in_array('Curp', $columnas_disponibles) && !empty($datos['Curp'])) {
                    $campos[] = 'Curp';
                    $valores[] = '?';
                    $tipos .= 's';
                    $params[] = $datos['Curp'];
                }
                
                if (in_array('Matricula', $columnas_disponibles) && !empty($datos['Matricula'])) {
                    $campos[] = 'Matricula';
                    $valores[] = '?';
                    $tipos .= 's';
                    $params[] = $datos['Matricula'];
                }
                
                if (in_array('Correo', $columnas_disponibles) && !empty($datos['Correo'])) {
                    $campos[] = 'Correo';
                    $valores[] = '?';
                    $tipos .= 's';
                    $params[] = $datos['Correo'];
                }
                
                if (in_array('Fecha_Creación', $columnas_disponibles) && !empty($datos['Fecha_Creación'] ?? '')) {
                    $campos[] = 'Fecha_Creación';
                    $valores[] = '?';
                    $tipos .= 's';
                    $params[] = $datos['Fecha_Creación'];
                }
                
                if (empty($campos)) {
                    $this->errores[] = "Fila " . ($fila + 2) . ": No hay campos válidos para insertar";
                    continue;
                }
                
                $sql = "INSERT INTO destinatario (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ")";
                
                $stmt = $this->conexion->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error al preparar consulta de destinatario: " . $this->conexion->error);
                }
                
                if (!empty($params)) {
                    $stmt->bind_param($tipos, ...$params);
                }
                
                if ($stmt->execute()) {
                    $procesados++;
                    $this->exitos[] = "Fila " . ($fila + 2) . ": Destinatario registrado correctamente";
                } else {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Error al insertar - " . $stmt->error;
                }
                
                $stmt->close();
                
            } catch (Exception $e) {
                $this->errores[] = "Fila " . ($fila + 2) . ": " . $e->getMessage();
            }
        }
        
        return $procesados > 0;
    }
    
    /**
     * Cargar centros IT desde Excel
     */
    private function cargarCentrosIT($data, $headers_provided = null) {
        // Si se proporcionan headers, usarlos; si no, tomar la primera fila
        if ($headers_provided !== null) {
            $headers = $headers_provided;
        } else {
            $headers = array_shift($data);
        }
        
        // Limpiar headers (quitar espacios, nulls, etc.)
        $headers = array_map(function($h) {
            return is_string($h) ? trim($h) : '';
        }, $headers);
        $headers = array_filter($headers, function($h) {
            return !empty($h);
        });
        $headers = array_values($headers); // Reindexar
        
        // Mostrar información de debug
        $this->exitos[] = "📋 Columnas detectadas: " . implode(', ', $headers);
        
        $procesados = 0;
        $actualizados = 0;
        $errores_count = 0;
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            
            try {
                $datos = $this->validarDatosCentroIT($row, $headers, $fila + 2);
                if (!$datos) continue;
                
                // Verificar si ya existe un centro con la misma Clave_ct
                $sql_check = "SELECT id FROM it_centros WHERE Clave_ct = ? LIMIT 1";
                $stmt_check = $this->conexion->prepare($sql_check);
                if (!$stmt_check) {
                    throw new Exception("Error al preparar consulta de verificación de centro: " . $this->conexion->error);
                }
                $stmt_check->bind_param("s", $datos['Clave_ct']);
                $stmt_check->execute();
                $resultado_check = $stmt_check->get_result();
                $existe = $resultado_check->num_rows > 0;
                $id_existente = $existe ? $resultado_check->fetch_assoc()['id'] : null;
                
                // Construir SQL dinámicamente según los campos disponibles
                $campos_sql = ['Nombre_itc', 'Acron', 'Estado', 'Clave_ct', 'Tipo_itc'];
                $valores_sql = [];
                $tipos = '';
                
                // Campos básicos obligatorios
                $valores_sql[] = $datos['Nombre_itc'];
                $valores_sql[] = $datos['Acron'];
                $valores_sql[] = $datos['Estado'];
                $valores_sql[] = $datos['Clave_ct'];
                $valores_sql[] = $datos['Tipo_itc'];
                $tipos = 'sssss';
                
                // Campos de correo opcionales
                $campos_correo = ['CE_dir', 'CE_svin', 'CE_saca', 'CE_sadm', 'CE_dvin', 'CE_dcyd'];
                foreach ($campos_correo as $campo_correo) {
                    if (isset($datos[$campo_correo]) && !empty($datos[$campo_correo])) {
                        $campos_sql[] = $campo_correo;
                        $valores_sql[] = $datos[$campo_correo];
                        $tipos .= 's';
                    }
                }
                
                if ($existe) {
                    // Si existe, hacer UPDATE
                    // Construir campos para UPDATE (todos excepto Clave_ct que es el identificador)
                    $campos_update = [];
                    $valores_update = [];
                    $tipos_update = '';
                    
                    // Reconstruir arrays sin Clave_ct
                    $campos_sin_clave = [];
                    $valores_sin_clave = [];
                    foreach ($campos_sql as $index => $campo) {
                        if ($campo !== 'Clave_ct') {
                            $campos_sin_clave[] = $campo;
                            $valores_sin_clave[] = $valores_sql[$index];
                            $campos_update[] = "$campo = ?";
                        }
                    }
                    
                    // Construir string de tipos (todos son 's' para strings)
                    $tipos_update = str_repeat('s', count($campos_update));
                    
                    // Agregar valores para UPDATE
                    $valores_update = $valores_sin_clave;
                    
                    // Agregar Clave_ct al final para el WHERE
                    $valores_update[] = $datos['Clave_ct'];
                    $tipos_update .= 's';
                    
                    $campos_str = implode(', ', $campos_update);
                    $sql = "UPDATE it_centros SET $campos_str WHERE Clave_ct = ?";
                    $stmt = $this->conexion->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Error al preparar consulta de actualización de centro: " . $this->conexion->error);
                    }
                    $stmt->bind_param($tipos_update, ...$valores_update);
                    
                    if ($stmt->execute()) {
                        $actualizados++;
                        $this->exitos[] = "Fila " . ($fila + 2) . ": Centro IT actualizado correctamente (Clave_ct: " . $datos['Clave_ct'] . ")";
                    } else {
                        $errores_count++;
                        $this->errores[] = "Fila " . ($fila + 2) . ": Error al actualizar - " . $stmt->error;
                    }
                } else {
                    // Si no existe, hacer INSERT
                    $campos_str = implode(', ', $campos_sql);
                    $placeholders = implode(', ', array_fill(0, count($campos_sql), '?'));
                    
                    $sql = "INSERT INTO it_centros ($campos_str) VALUES ($placeholders)";
                    $stmt = $this->conexion->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Error al preparar consulta de inserción de centro: " . $this->conexion->error);
                    }
                    $stmt->bind_param($tipos, ...$valores_sql);
                    
                    if ($stmt->execute()) {
                        $procesados++;
                        $this->exitos[] = "Fila " . ($fila + 2) . ": Centro IT registrado correctamente";
                    } else {
                        $errores_count++;
                        $this->errores[] = "Fila " . ($fila + 2) . ": Error al insertar - " . $stmt->error;
                    }
                }
                
            } catch (Exception $e) {
                $errores_count++;
                $this->errores[] = "Fila " . ($fila + 2) . ": " . $e->getMessage();
            }
        }
        
        // Guardar estadísticas
        $this->estadisticas['insertados'] += $procesados;
        $this->estadisticas['actualizados'] += $actualizados;
        $this->estadisticas['errores'] += $errores_count;
        
        // Retornar booleano para compatibilidad
        return ($procesados + $actualizados) > 0;
    }
    
    /**
     * Cargar tipos de insignia desde Excel
     */
    private function cargarTiposInsignia($data, $headers_provided = null) {
        if ($headers_provided !== null) {
            $headers = $headers_provided;
        } else {
            $headers = array_shift($data);
        }
        $procesados = 0;
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            $this->exitos[] = "Fila " . ($fila + 2) . ": Tipo de insignia - Implementación pendiente";
            $procesados++;
        }
        
        return $procesados > 0;
    }
    
    /**
     * Cargar categorías de insignia desde Excel
     */
    private function cargarCategoriasInsignia($data, $headers_provided = null) {
        if ($headers_provided !== null) {
            $headers = $headers_provided;
        } else {
            $headers = array_shift($data);
        }
        $procesados = 0;
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            $this->exitos[] = "Fila " . ($fila + 2) . ": Categoría de insignia - Implementación pendiente";
            $procesados++;
        }
        
        return $procesados > 0;
    }
    
    /**
     * Cargar periodos de emisión desde Excel
     */
    private function cargarPeriodosEmision($data, $headers_provided = null) {
        if ($headers_provided !== null) {
            $headers = $headers_provided;
        } else {
            $headers = array_shift($data);
        }
        $procesados = 0;
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            $this->exitos[] = "Fila " . ($fila + 2) . ": Periodo de emisión - Implementación pendiente";
            $procesados++;
        }
        
        return $procesados > 0;
    }
    
    /**
     * Cargar estatus desde Excel
     */
    private function cargarEstatus($data, $headers_provided = null) {
        if ($headers_provided !== null) {
            $headers = $headers_provided;
        } else {
            $headers = array_shift($data);
        }
        $procesados = 0;
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            
            try {
                $columnas = $this->headersToColumnas($headers);
                $nombre_estatus = trim($row[$columnas['Nombre_Estatus']] ?? '');
                $acron = trim($row[$columnas['Acron_Estatus']] ?? '');
                
                if (empty($nombre_estatus)) {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Nombre_Estatus es requerido";
                    continue;
                }
                
                $sql = "INSERT INTO estatus (Nombre_Estatus, Acron_Estatus) VALUES (?, ?)";
                $stmt = $this->conexion->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error al preparar consulta de estatus: " . $this->conexion->error);
                }
                $stmt->bind_param("ss", $nombre_estatus, $acron);
                
                if ($stmt->execute()) {
                    $procesados++;
                    $this->exitos[] = "Fila " . ($fila + 2) . ": Estatus '$nombre_estatus' registrado correctamente";
                } else {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Error al insertar estatus - " . $stmt->error;
                }
            } catch (Exception $e) {
                $this->errores[] = "Fila " . ($fila + 2) . ": " . $e->getMessage();
            }
        }
        
        return $procesados > 0;
    }
    
    /**
     * Cargar responsables de emisión desde Excel
     */
    private function cargarResponsablesEmision($data, $headers_provided = null) {
        if ($headers_provided !== null) {
            $headers = $headers_provided;
        } else {
            $headers = array_shift($data);
        }
        $procesados = 0;
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            
            try {
                $columnas = $this->headersToColumnas($headers);
                $nombre_completo = trim($row[$columnas['Nombre_Completo']] ?? '');
                $adscripcion = trim($row[$columnas['Adscripcion']] ?? '');
                $cargo = trim($row[$columnas['Cargo']] ?? '');
                $codigo = trim($row[$columnas['Codigo_Identificacion']] ?? '');
                $correo = trim($row[$columnas['Correo']] ?? '');
                $telefono = trim($row[$columnas['Telefono']] ?? '');
                
                if (empty($nombre_completo) || empty($adscripcion)) {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Nombre_Completo y Adscripcion son requeridos";
                    continue;
                }
                
                if (!is_numeric($adscripcion)) {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Adscripcion debe ser un ID numérico";
                    continue;
                }
                
                // Verificar si existe el campo Adscripcion en la tabla
                $check_adscripcion = $this->conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'Adscripcion'");
                $tiene_adscripcion = ($check_adscripcion && $check_adscripcion->num_rows > 0);
                
                if ($tiene_adscripcion) {
                    $sql = "INSERT INTO responsable_emision (Nombre_Completo, Adscripcion, Cargo, Codigo_Identificacion, Correo, Telefono) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $this->conexion->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Error al preparar consulta de responsable (con adscripción): " . $this->conexion->error);
                    }
                    $stmt->bind_param("sissss", $nombre_completo, $adscripcion, $cargo, $codigo, $correo, $telefono);
                } else {
                    $sql = "INSERT INTO responsable_emision (Nombre_Completo, Cargo, Codigo_Identificacion, Correo, Telefono) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $this->conexion->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Error al preparar consulta de responsable (sin adscripción): " . $this->conexion->error);
                    }
                    $stmt->bind_param("sssss", $nombre_completo, $cargo, $codigo, $correo, $telefono);
                }
                
                if ($stmt->execute()) {
                    $procesados++;
                    $this->exitos[] = "Fila " . ($fila + 2) . ": Responsable '$nombre_completo' registrado correctamente";
                } else {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Error al insertar responsable - " . $stmt->error;
                }
            } catch (Exception $e) {
                $this->errores[] = "Fila " . ($fila + 2) . ": " . $e->getMessage();
            }
        }
        
        return $procesados > 0;
    }
    
    /**
     * Cargar insignias maestras (T_insignias) desde Excel
     */
    private function cargarInsigniasMaestras($data, $headers_provided = null) {
        if ($headers_provided !== null) {
            $headers = $headers_provided;
        } else {
            $headers = array_shift($data);
        }
        $procesados = 0;
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            
            try {
                $columnas = $this->headersToColumnas($headers);
                $tipo_insignia = trim($row[$columnas['Tipo_Insignia']] ?? '');
                $propone_insignia = trim($row[$columnas['Propone_Insignia']] ?? '');
                $programa = trim($row[$columnas['Programa']] ?? '');
                $descripcion = trim($row[$columnas['Descripcion']] ?? '');
                $criterio = trim($row[$columnas['Criterio']] ?? '');
                $fecha_creacion = trim($row[$columnas['Fecha_Creacion']] ?? date('Y-m-d'));
                $fecha_autorizacion = trim($row[$columnas['Fecha_Autorizacion']] ?? date('Y-m-d'));
                $nombre_gen_ins = trim($row[$columnas['Nombre_gen_ins']] ?? '');
                $estatus = trim($row[$columnas['Estatus']] ?? '1');
                $archivo_visual = trim($row[$columnas['Archivo_Visual']] ?? '');
                
                if (empty($tipo_insignia) || empty($propone_insignia) || empty($descripcion) || empty($criterio)) {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Tipo_Insignia, Propone_Insignia, Descripcion y Criterio son requeridos";
                    continue;
                }
                
                if (!is_numeric($tipo_insignia) || !is_numeric($propone_insignia) || !is_numeric($estatus)) {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Tipo_Insignia, Propone_Insignia y Estatus deben ser numéricos";
                    continue;
                }
                
                $sql = "INSERT INTO T_insignias (Tipo_Insignia, Propone_Insignia, Programa, Descripcion, Criterio, Fecha_Creacion, Fecha_Autorizacion, Nombre_gen_ins, Estatus, Archivo_Visual) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conexion->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error al preparar consulta de tipo de insignia: " . $this->conexion->error);
                }
                $estatus_int = (int)$estatus;
                $stmt->bind_param("iissssssis", $tipo_insignia, $propone_insignia, $programa, $descripcion, $criterio, $fecha_creacion, $fecha_autorizacion, $nombre_gen_ins, $estatus_int, $archivo_visual);
                
                if ($stmt->execute()) {
                    $procesados++;
                    $this->exitos[] = "Fila " . ($fila + 2) . ": Insignia maestra registrada correctamente";
                } else {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Error al insertar insignia maestra - " . $stmt->error;
                }
            } catch (Exception $e) {
                $this->errores[] = "Fila " . ($fila + 2) . ": " . $e->getMessage();
            }
        }
        
        return $procesados > 0;
    }
    
    /**
     * Cargar usuarios desde Excel
     */
    private function cargarUsuarios($data, $headers_provided = null) {
        if ($headers_provided !== null) {
            $headers = $headers_provided;
        } else {
            $headers = array_shift($data);
        }
        $procesados = 0;
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            
            try {
                $columnas = $this->headersToColumnas($headers);
                $nombre = trim($row[$columnas['Nombre']] ?? '');
                $apellido_paterno = trim($row[$columnas['Apellido_Paterno']] ?? '');
                $apellido_materno = trim($row[$columnas['Apellido_Materno']] ?? '');
                $correo = trim($row[$columnas['Correo']] ?? '');
                $contrasena = trim($row[$columnas['Contrasena']] ?? '');
                $rol = trim($row[$columnas['Rol']] ?? 'Estudiante');
                $estado = trim($row[$columnas['Estado']] ?? 'Activo');
                $it_centro_id = trim($row[$columnas['It_Centro_Id']] ?? '');
                
                if (empty($nombre) || empty($apellido_paterno) || empty($correo) || empty($contrasena)) {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Nombre, Apellido_Paterno, Correo y Contrasena son requeridos";
                    continue;
                }
                
                if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Correo inválido";
                    continue;
                }
                
                // Hash de contraseña
                $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO Usuario (Nombre, Apellido_Paterno, Apellido_Materno, Correo, Contrasena, Rol, Estado, It_Centro_Id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conexion->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error al preparar consulta de usuario: " . $this->conexion->error);
                }
                $it_centro_id_val = !empty($it_centro_id) && is_numeric($it_centro_id) ? $it_centro_id : null;
                $stmt->bind_param("sssssssi", $nombre, $apellido_paterno, $apellido_materno, $correo, $contrasena_hash, $rol, $estado, $it_centro_id_val);
                
                if ($stmt->execute()) {
                    $procesados++;
                    $this->exitos[] = "Fila " . ($fila + 2) . ": Usuario '$correo' registrado correctamente";
                } else {
                    $this->errores[] = "Fila " . ($fila + 2) . ": Error al insertar usuario - " . $stmt->error;
                }
            } catch (Exception $e) {
                $this->errores[] = "Fila " . ($fila + 2) . ": " . $e->getMessage();
            }
        }
        
        return $procesados > 0;
    }
    
    /**
     * Generar código de insignia único automáticamente
     */
    private function generarCodigoInsigniaUnico($fecha_emision, $codigo_original = null) {
        // Extraer año de la fecha
        $anio = date('Y', strtotime($fecha_emision));
        
        // Intentar extraer el tipo y siglas del código original si existe
        $tipo_codigo = 'INS'; // Por defecto
        $siglas_centro = 'SEV'; // Por defecto
        $prefijo = 'TECNM'; // Por defecto
        
        if (!empty($codigo_original)) {
            // Intentar extraer del formato: TECNM-OFCM-2025-ART-008 o TECNIM-OFCM-2025-EMB-009
            if (preg_match('/^(TECNM|TECNIM)-([A-Z]+)-(\d{4})-([A-Z]+)-(\d+)$/i', $codigo_original, $matches)) {
                $prefijo = strtoupper($matches[1]);
                $siglas_centro = strtoupper($matches[2]);
                $tipo_codigo = strtoupper($matches[4]);
            } elseif (preg_match('/-([A-Z]{3,4})-\d{4}-([A-Z]{3})-\d+/i', $codigo_original, $matches)) {
                // Buscar patrón: -SIGLAS-AÑO-TIPO-NUM
                $siglas_centro = strtoupper($matches[1]);
                $tipo_codigo = strtoupper($matches[2]);
            } elseif (preg_match('/(ART|EMB|TAL|INN|SOC|FOR|MOV)/i', $codigo_original, $matches)) {
                // Buscar códigos conocidos de tipo
                $tipo_codigo = strtoupper($matches[1]);
            }
        }
        
        // Generar código único
        $intentos = 0;
        $max_intentos = 50;
        
        while ($intentos < $max_intentos) {
            $numero = str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
            $codigo = "$prefijo-$siglas_centro-$anio-$tipo_codigo-$numero";
            
            // Verificar que no exista
            $sql = "SELECT COUNT(*) as total FROM insigniasotorgadas WHERE Codigo_Insignia = ?";
            $stmt = $this->conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $codigo);
                $stmt->execute();
                $result = $stmt->get_result();
                $existe = $result->fetch_assoc()['total'] > 0;
                $stmt->close();
                
                if (!$existe) {
                    return $codigo;
                }
            }
            
            $intentos++;
        }
        
        // Si después de muchos intentos no se encontró uno único, usar timestamp
        $timestamp = time();
        $numero_final = substr($timestamp, -6);
        return "$prefijo-$siglas_centro-$anio-$tipo_codigo-$numero_final";
    }
    
    /**
     * Buscar índice de columna probando varios nombres posibles (alias).
     * Usado en validarDatosInsigniaOtorgada. No confundir con buscarColumna($headers, $nombre_buscado, $variaciones).
     */
    private function buscarColumnaPorAlias($columnas, $alias) {
        foreach ($alias as $nombre) {
            $k = strtolower(trim($nombre ?? ''));
            if ($k !== '' && isset($columnas[$k])) {
                return $columnas[$k];
            }
            $k2 = str_replace(' ', '_', $k);
            if ($k2 !== '' && isset($columnas[$k2])) {
                return $columnas[$k2];
            }
        }
        return null;
    }

    /**
     * Validar datos de insignia otorgada
     */
    private function validarDatosInsigniaOtorgada($row, $headers, $fila) {
        $datos = [];
        if (!is_array($headers)) {
            $this->errores[] = "Fila $fila: Headers inválidos";
            return false;
        }
        if (!is_array($row)) {
            $row = [];
        }

        // Mapear columnas por nombre (case-insensitive), normalizar espacios (no reindexar: mantener claves como en $row)
        $columnas = [];
        foreach ($headers as $idx => $header) {
            $h = trim((string)($header ?? ''));
            $key = strtolower($h);
            if ($key !== '') {
                $columnas[$key] = $idx;
                $columnas[str_replace(' ', '_', $key)] = $idx;
                $columnas[str_replace('_', ' ', $key)] = $idx;
            }
        }
        
        // Destinatario: aceptar Destinatario (nombre) o Id_Destinatario (ID numérico)
        $alias_destinatario = ['id_destinatario', 'destinatario', 'destinatario_id', 'nombre_destinatario', 'estudiante', 'nombre_completo'];
        $idx_dest = $this->buscarColumnaPorAlias($columnas, $alias_destinatario);
        if ($idx_dest === null) {
            $this->errores[] = "Fila $fila: Columna 'Destinatario' o 'Id_Destinatario' no encontrada";
            return false;
        }
        $valor_dest = trim($row[$idx_dest] ?? '');
        if ($valor_dest === '') {
            $this->errores[] = "Fila $fila: Campo 'Destinatario' es requerido";
            return false;
        }
        $datos['Destinatario'] = $valor_dest;
        
        // Fecha_Emision: aceptar varios nombres
        $alias_fecha = ['fecha_emision', 'fecha emision', 'fecha', 'fecha_otorgamiento', 'fecha otorgamiento', 'fecha_emision'];
        $idx_fecha = $this->buscarColumnaPorAlias($columnas, $alias_fecha);
        if ($idx_fecha === null) {
            $this->errores[] = "Fila $fila: Columna 'Fecha_Emision' no encontrada (pruebe: Fecha_Emision, Fecha, Fecha_Otorgamiento)";
            return false;
        }
        $valor_fecha = trim($row[$idx_fecha] ?? '');
        if ($valor_fecha === '') {
            $this->errores[] = "Fila $fila: Campo 'Fecha_Emision' es requerido";
            return false;
        }
        $datos['Fecha_Emision'] = $valor_fecha;
        
        // Obtener Codigo_Insignia / Código_Insignia (puede estar vacío, se generará automáticamente)
        $codigo_insignia = '';
        $idx_cod = $columnas['codigo_insignia'] ?? $columnas['código_insignia'] ?? null;
        if ($idx_cod !== null) {
            $codigo_insignia = trim($row[$idx_cod] ?? '');
        }
        $datos['Codigo_Insignia'] = $codigo_insignia;
        
        // Validar fecha - aceptar múltiples formatos y corregir errores comunes
        $fecha_valida = false;
        $fecha_formateada = null;
        $fecha_original = trim($datos['Fecha_Emision'] ?? '');
        
        // Detectar y corregir formato erróneo común: YYYY-MM-YYYY (ej: 2025-08-2025)
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{4})$/', $fecha_original, $matches)) {
            // Si el tercer grupo es igual al primero (año duplicado), usar el mes como día
            if ($matches[1] === $matches[3]) {
                $anio = $matches[1];
                $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                // Usar día 1 por defecto si solo hay año-mes
                $fecha_original = "$anio-$mes-01";
                $this->exitos[] = "Fila $fila: Fecha corregida automáticamente de '{$datos['Fecha_Emision']}' a '$fecha_original' (formato YYYY-MM-YYYY detectado, usando día 01)";
            }
        }
        
        // Detectar formato YYYY-MM (sin día) y agregar día 01
        if (preg_match('/^(\d{4})-(\d{1,2})$/', $fecha_original, $matches)) {
            $anio = $matches[1];
            $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $fecha_original = "$anio-$mes-01";
            $this->exitos[] = "Fila $fila: Fecha corregida automáticamente de '{$datos['Fecha_Emision']}' a '$fecha_original' (formato YYYY-MM detectado, usando día 01)";
        }
        
        // Si es un número (fecha serial de Excel), convertirla
        if (is_numeric($fecha_original)) {
            // Excel usa fechas seriales desde 1900-01-01
            // PHP usa timestamps desde 1970-01-01
            // Necesitamos convertir: Excel serial = días desde 1900-01-01
            $excel_serial = (float)$fecha_original;
            // Excel cuenta desde 1900-01-01, pero tiene un bug: cuenta 1900 como año bisiesto
            // Entonces: timestamp = (serial - 25569) * 86400
            $timestamp = ($excel_serial - 25569) * 86400;
            $fecha_formateada = date('Y-m-d', $timestamp);
            if ($fecha_formateada && $fecha_formateada !== '1970-01-01') {
                $fecha_valida = true;
            }
        } else {
            // Intentar diferentes formatos de fecha
            $formatos_fecha = [
                'Y-m-d',           // 2025-01-15
                'd/m/Y',           // 15/01/2025
                'd-m-Y',           // 15-01-2025
                'Y/m/d',           // 2025/01/15
                'm/d/Y',           // 01/15/2025
                'd.m.Y',           // 15.01.2025
                'Y.m.d',           // 2025.01.15
            ];
            
            foreach ($formatos_fecha as $formato) {
                $fecha_obj = \DateTime::createFromFormat($formato, $fecha_original);
                if ($fecha_obj !== false) {
                    $fecha_formateada = $fecha_obj->format('Y-m-d');
                    $fecha_valida = true;
                    break;
                }
            }
            
            // Si no funcionó con formatos específicos, intentar strtotime
            if (!$fecha_valida) {
                $timestamp = strtotime($fecha_original);
                if ($timestamp !== false && $timestamp > 0) {
                    $fecha_formateada = date('Y-m-d', $timestamp);
                    if ($fecha_formateada && $fecha_formateada !== '1970-01-01') {
                        $fecha_valida = true;
                    }
                }
            }
        }
        
        if (!$fecha_valida || !$fecha_formateada) {
            $this->errores[] = "Fila $fila: Fecha_Emision formato inválido. Valor recibido: '{$datos['Fecha_Emision']}'. Use formato YYYY-MM-DD, DD/MM/YYYY o DD-MM-YYYY";
            return false;
        }
        
        $datos['Fecha_Emision'] = $fecha_formateada;
        
        // Generar o validar Codigo_Insignia
        $codigo_insignia = $datos['Codigo_Insignia'];
        $codigo_generado = false;
        
        // Si está vacío o ya existe, generar uno nuevo
        if (empty($codigo_insignia)) {
            $codigo_insignia = $this->generarCodigoInsigniaUnico($fecha_formateada);
            $codigo_generado = true;
            $this->exitos[] = "Fila $fila: Codigo_Insignia generado automáticamente: '$codigo_insignia'";
        } else {
            // Verificar si ya existe
            $sql = "SELECT Codigo_Insignia FROM insigniasotorgadas WHERE Codigo_Insignia = ?";
            $stmt = $this->conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $codigo_insignia);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    // Si existe, generar uno nuevo
                    $codigo_original = $codigo_insignia;
                    $codigo_insignia = $this->generarCodigoInsigniaUnico($fecha_formateada, $codigo_original);
                    $codigo_generado = true;
                    $this->exitos[] = "Fila $fila: Codigo_Insignia '$codigo_original' ya existía, se generó uno nuevo: '$codigo_insignia'";
                }
                $stmt->close();
            }
        }
        
        $datos['Codigo_Insignia'] = $codigo_insignia;
        
        // Validar que Destinatario existe en destinatario o crearlo si es necesario
        // PRIORIDAD: Buscar por nombre completo primero (recomendado para Excel)
        // Si es un número, buscar por ID; si es texto, buscar por nombre completo
        // Verificar estructura de la tabla primero
        $check_destinatario = $this->conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
        $campo_id_destinatario = ($check_destinatario && $check_destinatario->num_rows > 0) ? 'id' : 'ID_destinatario';
        
        $destinatario_id = null;
        $valor_destinatario = trim($datos['Destinatario'] ?? '');
        
        // PRIORIDAD 1: Si NO es numérico (es texto/nombre), buscar por nombre completo primero
        if (!is_numeric($valor_destinatario)) {
            // Buscar por nombre completo (case-insensitive, con trim)
            $sql_buscar = "SELECT $campo_id_destinatario as id FROM destinatario WHERE TRIM(Nombre_Completo) = ? LIMIT 1";
            $stmt_buscar = $this->conexion->prepare($sql_buscar);
            if ($stmt_buscar) {
                $stmt_buscar->bind_param("s", $valor_destinatario);
                $stmt_buscar->execute();
                $result_buscar = $stmt_buscar->get_result();
                if ($result_buscar && $result_buscar->num_rows > 0) {
                    $row_buscar = $result_buscar->fetch_assoc();
                    $destinatario_id = $row_buscar['id'];
                    $this->exitos[] = "Fila $fila: ✅ Destinatario encontrado por nombre: '{$valor_destinatario}' (ID: $destinatario_id)";
                }
                $stmt_buscar->close();
            }
        }
        
        // PRIORIDAD 2: Si es numérico, buscar por ID
        if ($destinatario_id === null && is_numeric($valor_destinatario)) {
            $destinatario_id = (int)$valor_destinatario;
            $sql = "SELECT $campo_id_destinatario as id FROM destinatario WHERE $campo_id_destinatario = ?";
            $stmt = $this->conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $destinatario_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $this->exitos[] = "Fila $fila: ✅ Destinatario encontrado por ID: $destinatario_id";
                } else {
                    $destinatario_id = null; // No existe, buscar por otros campos
                    $this->errores[] = "Fila $fila: ⚠️ ID de destinatario $destinatario_id no existe. Buscando por otros campos...";
                }
                $stmt->close();
            }
        }
        
        // PRIORIDAD 3: Si aún no se encontró, buscar por CURP o matrícula (solo si no es numérico)
        if ($destinatario_id === null && !is_numeric($valor_destinatario)) {
            // Buscar por CURP
            $sql_curp = "SELECT $campo_id_destinatario as id FROM destinatario WHERE TRIM(Curp) = ? LIMIT 1";
            $stmt_curp = $this->conexion->prepare($sql_curp);
            if ($stmt_curp) {
                $stmt_curp->bind_param("s", $valor_destinatario);
                $stmt_curp->execute();
                $result_curp = $stmt_curp->get_result();
                if ($result_curp && $result_curp->num_rows > 0) {
                    $row_curp = $result_curp->fetch_assoc();
                    $destinatario_id = $row_curp['id'];
                    $this->exitos[] = "Fila $fila: ✅ Destinatario encontrado por CURP: '{$valor_destinatario}' (ID: $destinatario_id)";
                }
                $stmt_curp->close();
            }
            
            // Si aún no se encontró, buscar por matrícula
            if ($destinatario_id === null) {
                $sql_mat = "SELECT $campo_id_destinatario as id FROM destinatario WHERE TRIM(Matricula) = ? LIMIT 1";
                $stmt_mat = $this->conexion->prepare($sql_mat);
                if ($stmt_mat) {
                    $stmt_mat->bind_param("s", $valor_destinatario);
                    $stmt_mat->execute();
                    $result_mat = $stmt_mat->get_result();
                    if ($result_mat && $result_mat->num_rows > 0) {
                        $row_mat = $result_mat->fetch_assoc();
                        $destinatario_id = $row_mat['id'];
                        $this->exitos[] = "Fila $fila: ✅ Destinatario encontrado por matrícula: '{$valor_destinatario}' (ID: $destinatario_id)";
                    }
                    $stmt_mat->close();
                }
            }
        }
        
        // PRIORIDAD 4: Si aún no se encontró, crear nuevo destinatario automáticamente
        // (Solo si es texto/nombre, no si es un ID numérico que no existe)
        if ($destinatario_id === null) {
            // Si es un ID numérico que no existe, no crear automáticamente (mostrar error)
            if (is_numeric($valor_destinatario)) {
                $this->errores[] = "Fila $fila: ❌ ID de destinatario '$valor_destinatario' no existe en la base de datos. Usa el nombre completo del destinatario en lugar del ID.";
                return false;
            }
            
            // Si es texto/nombre, crear nuevo destinatario automáticamente
            // Verificar si ITCentro existe en la tabla
            $check_itcentro = $this->conexion->query("SHOW COLUMNS FROM destinatario LIKE 'ITCentro'");
            $tiene_itcentro = ($check_itcentro && $check_itcentro->num_rows > 0);
            
            // Obtener un ITCentro por defecto (el primero disponible)
            $itcentro_default = 1;
            $sql_itc = "SELECT id FROM it_centros ORDER BY id LIMIT 1";
            $result_itc = $this->conexion->query($sql_itc);
            if ($result_itc && $result_itc->num_rows > 0) {
                $row_itc = $result_itc->fetch_assoc();
                $itcentro_default = $row_itc['id'];
            }
            
            // Crear nuevo destinatario
            if ($tiene_itcentro) {
                $sql_insert = "INSERT INTO destinatario (Nombre_Completo, ITCentro) VALUES (?, ?)";
                $stmt_insert = $this->conexion->prepare($sql_insert);
                if ($stmt_insert) {
                    $stmt_insert->bind_param("si", $valor_destinatario, $itcentro_default);
                    if ($stmt_insert->execute()) {
                        $destinatario_id = $this->conexion->insert_id;
                        $this->exitos[] = "Fila $fila: ✅ Destinatario creado automáticamente: '{$valor_destinatario}' (ID: $destinatario_id)";
                    } else {
                        $this->errores[] = "Fila $fila: ❌ Error al crear destinatario: " . $stmt_insert->error;
                        $stmt_insert->close();
                        return false;
                    }
                    $stmt_insert->close();
                }
            } else {
                $sql_insert = "INSERT INTO destinatario (Nombre_Completo) VALUES (?)";
                $stmt_insert = $this->conexion->prepare($sql_insert);
                if ($stmt_insert) {
                    $stmt_insert->bind_param("s", $valor_destinatario);
                    if ($stmt_insert->execute()) {
                        $destinatario_id = $this->conexion->insert_id;
                        $this->exitos[] = "Fila $fila: ✅ Destinatario creado automáticamente: '{$valor_destinatario}' (ID: $destinatario_id)";
                    } else {
                        $this->errores[] = "Fila $fila: ❌ Error al crear destinatario: " . $stmt_insert->error;
                        $stmt_insert->close();
                        return false;
                    }
                    $stmt_insert->close();
                }
            }
        }
        
        if ($destinatario_id === null) {
            $this->errores[] = "Fila $fila: No se pudo encontrar o crear el destinatario: '{$valor_destinatario}'";
            return false;
        }
        
        $datos['Destinatario'] = $destinatario_id;
        
        // Campos opcionales
        $datos['Periodo_Emision'] = null;
        $idx_per = $columnas['periodo_emision'] ?? $columnas['id_periodo_emision'] ?? null;
        if ($idx_per !== null && !empty(trim($row[$idx_per] ?? ''))) {
            $valor = trim($row[$idx_per]);
            if (is_numeric($valor)) {
                $datos['Periodo_Emision'] = (int)$valor;
            }
        }
        
        $datos['Responsable_Emision'] = null;
        if (isset($columnas['responsable_emision']) && !empty(trim($row[$columnas['responsable_emision']] ?? ''))) {
            $valor = trim($row[$columnas['responsable_emision']]);
            if (is_numeric($valor)) {
                $datos['Responsable_Emision'] = (int)$valor;
            }
        }
        
        $datos['Estatus'] = null;
        $idx_est = $columnas['estatus'] ?? $columnas['id_estatus'] ?? null;
        if ($idx_est !== null && !empty(trim($row[$idx_est] ?? ''))) {
            $valor = trim($row[$idx_est]);
            if (is_numeric($valor)) {
                $datos['Estatus'] = (int)$valor;
            } else {
                // Resolver por nombre (ej. "Aprobada" -> id en tabla estatus)
                $sql_est = "SELECT id FROM estatus WHERE Nombre_Estatus = ? OR Acron_Estatus = ? LIMIT 1";
                $stmt_est = $this->conexion->prepare($sql_est);
                if ($stmt_est) {
                    $stmt_est->bind_param("ss", $valor, $valor);
                    $stmt_est->execute();
                    $res_est = $stmt_est->get_result();
                    if ($res_est && $res_est->num_rows > 0) {
                        $datos['Estatus'] = (int)$res_est->fetch_assoc()['id'];
                    }
                    $stmt_est->close();
                }
            }
        }
        
        // Si no se proporcionó Estatus, usar 1 por defecto (Activo)
        if ($datos['Estatus'] === null) {
            $datos['Estatus'] = 1;
        }
        
        return $datos;
    }
    
    /**
     * Obtener valor de fila por primera columna que exista en el mapa (nombres alternativos).
     */
    private function getValDestinatario($row, $columnas, $nombres_posibles) {
        foreach ($nombres_posibles as $nombre) {
            if (isset($columnas[$nombre])) {
                $idx = $columnas[$nombre];
                $val = trim($row[$idx] ?? '');
                if ($val !== '') {
                    return $val;
                }
            }
        }
        return '';
    }
    
    /**
     * Validar datos de destinatario (acepta formato TecNM: Nombre Completo, Correo_Inst, Correo_Per, ITCentro por nombre).
     */
    private function validarDatosDestinatario($row, $headers, $fila) {
        $datos = [];
        $columnas = $this->headersToColumnas($headers);
        
        // Nombre completo: aceptar "Nombre_Completo" o "Nombre Completo" (formato TecNM)
        $nombre_completo = $this->getValDestinatario($row, $columnas, ['Nombre_Completo', 'Nombre Completo']);
        $tiene_nombre = isset($columnas['Nombre']) && !empty(trim($row[$columnas['Nombre']] ?? ''));
        
        if ($nombre_completo === '' && !$tiene_nombre) {
            $this->errores[] = "Fila $fila: Se requiere 'Nombre_Completo' o 'Nombre Completo' o 'Nombre'";
            return false;
        }
        
        if ($nombre_completo !== '') {
            $datos['Nombre_Completo'] = $nombre_completo;
        } else {
            $datos['Nombre'] = trim($row[$columnas['Nombre']] ?? '');
            $datos['Apellido_Paterno'] = trim($row[$columnas['Apellido_Paterno']] ?? '');
            $datos['Apellido_Materno'] = trim($row[$columnas['Apellido_Materno']] ?? '');
        }
        
        // ITCentro: ITCentro, Id_Centro o "Instituto Tecnológico de Adscripción" (exportación evento TecNM)
        $valor_itc = $this->getValDestinatario($row, $columnas, ['ITCentro', 'Id_Centro', 'Instituto Tecnológico de Adscripción']);
        if ($valor_itc === '') {
            $this->errores[] = "Fila $fila: Se requiere 'ITCentro' o 'Id_Centro'";
            return false;
        }
        
        if (is_numeric($valor_itc)) {
            $datos['ITCentro'] = (int)$valor_itc;
        } else {
            // Resolver nombre del instituto a ID
            $sql = "SELECT id FROM it_centros WHERE Nombre_itc = ? OR Nombre_itc LIKE ? ORDER BY id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            if ($stmt) {
                $like = '%' . $valor_itc . '%';
                $stmt->bind_param("ss", $valor_itc, $like);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $res->num_rows > 0) {
                    $datos['ITCentro'] = (int)$res->fetch_assoc()['id'];
                } else {
                    $this->errores[] = "Fila $fila: ITCentro '{$valor_itc}' no encontrado en it_centros";
                    $stmt->close();
                    return false;
                }
                $stmt->close();
            } else {
                $datos['ITCentro'] = (int)$valor_itc;
            }
        }
        
        // Validar que ITCentro existe en it_centros
        $sql = "SELECT id FROM it_centros WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $datos['ITCentro']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                $this->errores[] = "Fila $fila: ITCentro ({$datos['ITCentro']}) no existe en la tabla it_centros";
                $stmt->close();
                return false;
            }
            $stmt->close();
        }
        
        // Correo: Correo, Correo_Inst o Correo_Per (formato TecNM)
        $datos['Correo'] = $this->getValDestinatario($row, $columnas, ['Correo', 'Correo_Inst', 'Correo_Per', 'Correo Electrónico para Notificación']);
        
        // Campos opcionales
        if (isset($columnas['Curp'])) {
            $datos['Curp'] = trim($row[$columnas['Curp']] ?? '');
        }
        if (isset($columnas['Matricula'])) {
            $datos['Matricula'] = trim($row[$columnas['Matricula']] ?? '');
        }
        if (isset($columnas['Telefono'])) {
            $datos['Telefono'] = trim($row[$columnas['Telefono']] ?? '');
        }
        if (isset($columnas['Genero'])) {
            $datos['Genero'] = trim($row[$columnas['Genero']] ?? '');
        }
        if (isset($columnas['Rol'])) {
            $datos['Rol'] = trim($row[$columnas['Rol']] ?? 'Estudiante');
        }
        if (isset($columnas['Fecha_Creación'])) {
            $datos['Fecha_Creación'] = trim($row[$columnas['Fecha_Creación']] ?? '');
        }
        
        // Validar email si se proporciona
        if (!empty($datos['Correo']) && !filter_var($datos['Correo'], FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = "Fila $fila: Correo electrónico inválido";
            return false;
        }
        
        return $datos;
    }
    
    /**
     * Limpiar y validar correo electrónico
     */
    private function limpiarYValidarEmail($email) {
        if (empty($email)) {
            return ['valido' => false, 'email' => ''];
        }
        
        // Limpiar el email
        $email_limpio = trim($email ?? '');
        $email_limpio = preg_replace('/[\r\n\t]+/', '', $email_limpio); // Quitar saltos de línea y tabs
        $email_limpio = preg_replace('/\s+/', '', $email_limpio); // Quitar todos los espacios
        $email_limpio = trim($email_limpio ?? '', " \t\n\r\0\x0B\"'`"); // Quitar comillas y espacios
        
        // Validar formato básico
        if (empty($email_limpio)) {
            return ['valido' => false, 'email' => $email_limpio];
        }
        
        // Verificar que tenga @
        if (strpos($email_limpio, '@') === false) {
            return ['valido' => false, 'email' => $email_limpio];
        }
        
        // Validar con filter_var
        $email_validado = filter_var($email_limpio, FILTER_VALIDATE_EMAIL);
        
        if ($email_validado !== false) {
            return ['valido' => true, 'email' => $email_validado];
        }
        
        // Si filter_var falla, hacer validación manual más flexible
        // Formato básico: algo@dominio.extension
        if (preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email_limpio)) {
            return ['valido' => true, 'email' => $email_limpio];
        }
        
        return ['valido' => false, 'email' => $email_limpio];
    }
    
    /**
     * Normalizar nombre de columna para búsqueda flexible
     */
    private function normalizarNombreColumna($nombre) {
        // Convertir a minúsculas, quitar espacios, guiones y caracteres especiales
        $normalizado = strtolower(trim($nombre ?? ''));
        $normalizado = str_replace([' ', '-', '_', '.', 'á', 'é', 'í', 'ó', 'ú', 'ñ'], 
                                   ['', '', '', '', 'a', 'e', 'i', 'o', 'u', 'n'], 
                                   $normalizado);
        return $normalizado;
    }

    /**
     * Convierte array de headers a mapa nombre_columna => índice. array_flip() solo acepta string/int.
     */
    private function headersToColumnas($headers) {
        $safe = array_map(function ($h) { return is_string($h) || is_int($h) ? $h : (string)($h ?? ''); }, $headers);
        return array_flip($safe);
    }
    
    /**
     * Buscar columna por nombre flexible
     */
    private function buscarColumna($headers, $nombre_buscado, $variaciones = []) {
        $nombre_normalizado = $this->normalizarNombreColumna($nombre_buscado);
        
        // Agregar variaciones comunes
        $todas_variaciones = array_merge(
            [$nombre_buscado],
            [strtolower($nombre_buscado), strtoupper($nombre_buscado), ucfirst(strtolower($nombre_buscado))],
            $variaciones
        );
        
        // Normalizar todas las variaciones
        $variaciones_normalizadas = array_map([$this, 'normalizarNombreColumna'], $todas_variaciones);
        
        foreach ($headers as $index => $header) {
            if (empty($header)) continue;
            
            $header_normalizado = $this->normalizarNombreColumna($header);
            
            // Comparación exacta normalizada
            if ($header_normalizado === $nombre_normalizado) {
                return $index;
            }
            
            // Comparación con variaciones normalizadas
            foreach ($variaciones_normalizadas as $variacion_norm) {
                if ($header_normalizado === $variacion_norm) {
                    return $index;
                }
            }
            
            // Búsqueda parcial (contiene) - solo si el nombre buscado tiene al menos 3 caracteres
            if (strlen($nombre_normalizado) >= 3) {
                if (stripos($header_normalizado, $nombre_normalizado) !== false || 
                    stripos($nombre_normalizado, $header_normalizado) !== false) {
                    return $index;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Validar datos de centro IT
     */
    private function validarDatosCentroIT($row, $headers, $fila) {
        $datos = [];
        
        // Mapeo de campos con sus posibles variaciones
        $mapeo_campos = [
            'Nombre_itc' => ['nombre_itc', 'nombre', 'nombre del centro', 'nombre centro', 'centro', 'instituto', 'nombre instituto'],
            'Acron' => ['acron', 'acronimo', 'acrónimo', 'siglas', 'abreviatura'],
            'Estado' => ['estado', 'estado del plantel', 'ubicacion', 'ubicación', 'localidad', 'entidad', 'entidad federativa'],
            'Clave_ct' => ['clave_ct', 'clave', 'clave centro', 'clave del centro', 'cct', 'clave cct', 'clave de institución', 'clave de institucion', 'clave institucion', 'clave institucion'],
            'Tipo_itc' => ['tipo_itc', 'tipo', 'tipo de centro', 'tipo centro', 'categoria', 'categoría', 'tipo de plantel', 'tipo plantel', 'clasificación', 'clasificacion']
        ];
        
        // Buscar cada campo
        foreach ($mapeo_campos as $campo_db => $variaciones) {
            $indice_columna = $this->buscarColumna($headers, $campo_db, $variaciones);
            
            if ($indice_columna === false) {
                // Mostrar las columnas disponibles para ayudar al usuario
                $columnas_disponibles = implode(', ', array_filter($headers));
                $this->errores[] = "Fila $fila: Columna '$campo_db' no encontrada. Columnas disponibles: $columnas_disponibles";
                return false;
            }
            
            $valor = trim($row[$indice_columna] ?? '');
            if (empty($valor)) {
                $this->errores[] = "Fila $fila: Campo '$campo_db' es requerido pero está vacío";
                return false;
            }
            
            $datos[$campo_db] = $valor;
        }
        
        // Campos de correo opcionales
        $campos_correo = [
            'CE_dir' => ['ce_dir', 'correo direccion', 'correo dirección', 'email direccion', 'email dirección', 'correo dir'],
            'CE_svin' => ['ce_svin', 'correo subdireccion vinculacion', 'correo subdirección vinculación', 'email subdireccion vinculacion', 'correo svin'],
            'CE_saca' => ['ce_saca', 'correo subdireccion academica', 'correo subdirección académica', 'email subdireccion academica', 'correo saca'],
            'CE_sadm' => ['ce_sadm', 'correo subdireccion administrativa', 'correo subdirección administrativa', 'email subdireccion administrativa', 'correo sadm'],
            'CE_dvin' => ['ce_dvin', 'correo departamento vinculacion', 'correo departamento vinculación', 'email departamento vinculacion', 'correo dvin'],
            'CE_dcyd' => ['ce_dcyd', 'correo departamento comunicacion', 'correo departamento comunicación', 'correo departamento comunicacion y difusion', 'correo dcyd']
        ];
        
        foreach ($campos_correo as $campo_db => $variaciones) {
            $indice_columna = $this->buscarColumna($headers, $campo_db, $variaciones);
            
            if ($indice_columna !== false) {
                $valor_original = $row[$indice_columna] ?? '';
                
                // Validar y limpiar el email
                if (!empty($valor_original)) {
                    $resultado_email = $this->limpiarYValidarEmail($valor_original);
                    
                    if ($resultado_email['valido']) {
                        $datos[$campo_db] = $resultado_email['email'];
                    } else {
                        // Mostrar el valor original para debugging
                        $valor_mostrar = addslashes($valor_original);
                        $this->errores[] = "Fila $fila: El campo '$campo_db' contiene un correo electrónico inválido: '$valor_mostrar'";
                        // No retornamos false, solo registramos el error pero continuamos
                    }
                }
            }
        }
        
        return $datos;
    }
    
    /**
     * Generar plantilla Excel
     */
    /**
     * Generar plantilla con todas las tablas en un solo Excel
     */
    public function generarTodasLasPlantillas() {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            
            // Definir todas las plantillas con datos de ejemplo
            // ORDEN IMPORTANTE: Debe coincidir con el orden recomendado de carga
            $plantillas = [
                'Centros IT' => [
                    'headers' => ['Nombre_itc', 'Acron', 'Estado', 'Clave_ct', 'Tipo_itc'],
                    'datos' => [
                        ['Instituto Tecnológico de San Marcos', 'ITSM', 'Coahuila', '05DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Celaya', 'ITC', 'Guanajuato', '11DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Morelia', 'ITM', 'Michoacán', '16DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Durango', 'ITD', 'Durango', '10DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Tijuana', 'ITT', 'Baja California', '02DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Chihuahua', 'ITCH', 'Chihuahua', '08DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Puebla', 'ITP', 'Puebla', '21DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Veracruz', 'ITV', 'Veracruz', '30DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Mérida', 'ITM', 'Yucatán', '31DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Cancún', 'ITC', 'Quintana Roo', '23DIT0001A', 'Federal']
                    ]
                ],
                'Categorías de Insignia' => [
                    'headers' => ['Nombre_Cat', 'Descripcion'],
                    'datos' => [
                        ['Formación Integral', 'Categoría para insignias de formación integral del estudiante'],
                        ['Desarrollo Académico', 'Categoría para insignias de excelencia académica'],
                        ['Desarrollo Personal', 'Categoría para insignias de desarrollo personal'],
                        ['Responsabilidad Social', 'Categoría para insignias de responsabilidad social'],
                        ['Liderazgo', 'Categoría para insignias de liderazgo estudiantil'],
                        ['Emprendimiento', 'Categoría para insignias de emprendimiento'],
                        ['Investigación', 'Categoría para insignias de investigación'],
                        ['Innovación', 'Categoría para insignias de innovación tecnológica'],
                        ['Sustentabilidad', 'Categoría para insignias de sustentabilidad'],
                        ['Internacionalización', 'Categoría para insignias de movilidad internacional']
                    ]
                ],
                'Tipos de Insignia' => [
                    'headers' => ['Nombre_Insignia', 'Descripcion', 'Id_Categoria'],
                    'datos' => [
                        ['Embajador del Arte', 'Reconocimiento por destacar en actividades artísticas y culturales', 1],
                        ['Embajador del Deporte', 'Reconocimiento por excelencia en actividades deportivas', 3],
                        ['Embajador del Deporte Oro', 'Reconocimiento máximo por excelencia en actividades deportivas (nivel Oro)', 3],
                        ['Embajador del Deporte Plata', 'Reconocimiento por excelencia en actividades deportivas (nivel Plata)', 3],
                        ['Embajador del Deporte Bronce', 'Reconocimiento por excelencia en actividades deportivas (nivel Bronce)', 3],
                        ['Talento Científico', 'Reconocimiento por participación destacada en proyectos científicos', 2],
                        ['Talento Innovador', 'Reconocimiento por innovación tecnológica', 2],
                        ['Responsabilidad Social', 'Reconocimiento por participación en actividades de responsabilidad social', 1],
                        ['Formación y Actualización', 'Reconocimiento por formación continua y actualización', 2],
                        ['Movilidad e Intercambio', 'Reconocimiento por participación en programas de movilidad', 1],
                        ['Liderazgo Estudiantil', 'Reconocimiento por liderazgo en actividades estudiantiles', 1],
                        ['Emprendimiento', 'Reconocimiento por proyectos emprendedores', 1],
                        ['Sustentabilidad', 'Reconocimiento por proyectos de sustentabilidad ambiental', 1]
                    ]
                ],
                'Estatus' => [
                    'headers' => ['Nombre_Estatus', 'Acron_Estatus'],
                    'datos' => [
                        ['Activo', 'ACT'],
                        ['Pendiente', 'PEND'],
                        ['Autorizado', 'AUT'],
                        ['Rechazado', 'REC'],
                        ['Vencido', 'VEN'],
                        ['En Revisión', 'REV'],
                        ['Aprobado', 'APR'],
                        ['Cancelado', 'CAN'],
                        ['Suspendido', 'SUS'],
                        ['Finalizado', 'FIN']
                    ]
                ],
                'Periodos de Emisión' => [
                    'headers' => ['Periodo', 'Anio', 'Fecha_Inicio', 'Fecha_Fin'],
                    'datos' => [
                        ['Enero-Junio 2024', 2024, '2024-01-01', '2024-06-30'],
                        ['Agosto-Diciembre 2024', 2024, '2024-08-01', '2024-12-31'],
                        ['Enero-Junio 2025', 2025, '2025-01-01', '2025-06-30'],
                        ['Agosto-Diciembre 2025', 2025, '2025-08-01', '2025-12-31'],
                        ['Enero-Junio 2026', 2026, '2026-01-01', '2026-06-30'],
                        ['Agosto-Diciembre 2026', 2026, '2026-08-01', '2026-12-31'],
                        ['Enero-Junio 2027', 2027, '2027-01-01', '2027-06-30'],
                        ['Agosto-Diciembre 2027', 2027, '2027-08-01', '2027-12-31'],
                        ['Enero-Junio 2028', 2028, '2028-01-01', '2028-06-30'],
                        ['Agosto-Diciembre 2028', 2028, '2028-08-01', '2028-12-31']
                    ]
                ],
                'Responsables de Emisión' => [
                    'headers' => ['Nombre_Completo', 'Adscripcion', 'Cargo', 'Codigo_Identificacion', 'Correo', 'Telefono'],
                    'datos' => [
                        ['Dr. Juan Pérez García', 1, 'Director', 'TECNM-DIR-001', 'juan.perez@tecnm.mx', '5551234567'],
                        ['Mtra. María González López', 1, 'Subdirectora Académica', 'TECNM-SUB-001', 'maria.gonzalez@tecnm.mx', '5551234568'],
                        ['Lic. Carlos Ramírez Martínez', 1, 'Coordinador de Vinculación', 'TECNM-COORD-001', 'carlos.ramirez@tecnm.mx', '5551234569'],
                        ['Ing. Ana Sánchez Hernández', 1, 'Jefa de Departamento', 'TECNM-JEFE-001', 'ana.sanchez@tecnm.mx', '5551234570'],
                        ['Mtra. Laura Torres Díaz', 1, 'Coordinadora de Extensión', 'TECNM-COORD-002', 'laura.torres@tecnm.mx', '5551234571'],
                        ['Dr. Roberto Morales Silva', 1, 'Director de Investigación', 'TECNM-DIR-002', 'roberto.morales@tecnm.mx', '5551234572'],
                        ['Mtra. Patricia Jiménez Ruiz', 1, 'Coordinadora de Servicios', 'TECNM-COORD-003', 'patricia.jimenez@tecnm.mx', '5551234573'],
                        ['Ing. Fernando Castro Moreno', 1, 'Jefe de División', 'TECNM-JEFE-002', 'fernando.castro@tecnm.mx', '5551234574'],
                        ['Mtra. Gabriela Mendoza Vega', 1, 'Coordinadora Académica', 'TECNM-COORD-004', 'gabriela.mendoza@tecnm.mx', '5551234575'],
                        ['Dr. Luis Hernández Campos', 1, 'Director de Posgrado', 'TECNM-DIR-003', 'luis.hernandez@tecnm.mx', '5551234576']
                    ]
                ],
                'Destinatarios' => [
                    'headers' => [
                        'ID destinatario', 'Nombre Completo', 'Curp', 'Matricula', 'Correo_Inst', 'Correo_Per', 'Fecha_Creación', 'ITCentro',
                        'FolioRegistroEvento', 'Instituto Tecnológico de Adscripción', 'Género', 'Disciplina', 'Correo Electrónico para Notificación', 'Matricula', 'Evento', 'Lugar', 'Registro', 'Registro Participación'
                    ],
                    'datos' => [
                        [1, 'Emmanuel Sanchez Gomez', 'SAGE010809HMCNMMA01', 'IT719IF025', '', '', '', 'Centro Nacional de Investigación y Desarrollo Tecnológico', 'Registro de participación ENDTecNM2025: 81757', 'Instituto Tecnológico de Acapulco', 'M', 'Natación', '', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81757', 'Registro de participación ENDTecNM2025: 81757'],
                        [2, 'Adrián Mendoza Rendón', 'MERA040624HGRNNDA02', 'C21321039', '', '', '', 'Instituto Tecnológico de Acapulco', 'Registro de participación ENDTecNM2025: 81775', 'Instituto Tecnológico de Agua Prieta', 'M', 'Basquetbol', '', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81775', 'Registro de participación ENDTecNM2025: 81775'],
                        [3, 'Juan Pérez Gómez', 'PERJ800101HDFRGN01', '2024001', 'juan.perez@tecnm.mx', '', '', 'Instituto Tecnológico de Agua Prieta', 'Registro de participación ENDTecNM2025: 81776', 'Instituto Tecnológico de Aguascalientes', 'M', 'Taekwondo', 'juan.perez@tecnm.mx', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Primer lugar', 'Registro de participación ENDTecNM2025: 81776', 'Registro de participación ENDTecNM2025: 81776'],
                        [4, 'María González López', 'GOLM900215HDFRGN02', '2024002', 'maria.gonzalez@tecnm.mx', '', '', 'Instituto Tecnológico de Aguascalientes', 'Registro de participación ENDTecNM2025: 81777', 'Instituto Tecnológico de Celaya', 'F', 'Atletismo', 'maria.gonzalez@tecnm.mx', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81777', 'Registro de participación ENDTecNM2025: 81777'],
                        [5, 'Carlos Ramírez Martínez', 'RAMC850320HDFRGN03', '2024003', '', '', '', 'Instituto Tecnológico de Celaya', 'Registro de participación ENDTecNM2025: 81778', 'Instituto Tecnológico de Chihuahua', 'M', 'Sóftbol', '', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81778', 'Registro de participación ENDTecNM2025: 81778'],
                        [6, 'Ana Sánchez Hernández', 'SAHA920510HDFRGN04', '2024004', 'ana.sanchez@tecnm.mx', '', '', 'Instituto Tecnológico de Chihuahua', 'Registro de participación ENDTecNM2025: 81779', 'Instituto Tecnológico de Durango', 'F', 'Ajedrez', 'ana.sanchez@tecnm.mx', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Primer lugar', 'Registro de participación ENDTecNM2025: 81779', 'Registro de participación ENDTecNM2025: 81779'],
                        [7, 'Roberto Torres Díaz', 'TODR880725HDFRGN05', '2024005', '', '', '', 'Instituto Tecnológico de Durango', 'Registro de participación ENDTecNM2025: 81780', 'Instituto Tecnológico de Morelia', 'M', 'Tenis', '', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81780', 'Registro de participación ENDTecNM2025: 81780'],
                        [8, 'Laura Morales Silva', 'MOSL910330HDFRGN06', '2024006', 'laura.morales@tecnm.mx', '', '', 'Instituto Tecnológico de Morelia', 'Registro de participación ENDTecNM2025: 81781', 'Instituto Tecnológico de Puebla', 'F', 'Natación', 'laura.morales@tecnm.mx', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81781', 'Registro de participación ENDTecNM2025: 81781'],
                        [9, 'Fernando Jiménez Ruiz', 'JIRF870415HDFRGN07', '2024007', '', '', '', 'Instituto Tecnológico de Puebla', 'Registro de participación ENDTecNM2025: 81782', 'Instituto Tecnológico de Veracruz', 'M', 'Basquetbol', '', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81782', 'Registro de participación ENDTecNM2025: 81782'],
                        [10, 'Patricia Castro Moreno', 'CAMP920620HDFRGN08', '2024008', 'patricia.castro@tecnm.mx', '', '', 'Instituto Tecnológico de Veracruz', 'Registro de participación ENDTecNM2025: 81783', 'Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET)', 'F', 'Atletismo', 'patricia.castro@tecnm.mx', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Primer lugar', 'Registro de participación ENDTecNM2025: 81783', 'Registro de participación ENDTecNM2025: 81783']
                    ]
                ],
                'insigniasotorgadas' => [
                    'headers' => ['id', 'Id_Insignia', 'Id_Destinatario', 'Evidencia', 'Fecha_Emision', 'Id_Periodo_Emision', 'Id_Estatus', 'Código_Insignia', 'Fecha_Creacion_Registro'],
                    'datos' => [
                        [1, 3, 1, 'Registro de participación ENDTecNM2025: 84757', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-1', ''],
                        [2, 3, 2, 'Registro de participación ENDTecNM2025: 81775', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-2', ''],
                        [3, 3, 3, 'Registro de participación ENDTecNM2025: 81776', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-3', ''],
                        [4, 3, 4, 'Registro de participación ENDTecNM2025: 81777', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-4', ''],
                        [5, 3, 5, 'Registro de participación ENDTecNM2025: 81778', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-5', ''],
                        [6, 3, 6, 'Registro de participación ENDTecNM2025: 81779', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-6', ''],
                        [7, 3, 7, 'Registro de participación ENDTecNM2025: 81780', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-7', ''],
                        [8, 3, 8, 'Registro de participación ENDTecNM2025: 81781', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-8', ''],
                        [9, 3, 9, 'Registro de participación ENDTecNM2025: 81782', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-9', ''],
                        [10, 3, 10, 'Registro de participación ENDTecNM2025: 81783', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-10', ''],
                        [11, 3, 11, 'Registro de participación ENDTecNM2025: 84758', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-11', ''],
                        [12, 3, 12, 'Registro de participación ENDTecNM2025: 84759', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-12', ''],
                        [13, 3, 13, 'Registro de participación ENDTecNM2025: 84760', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-13', ''],
                        [14, 3, 14, 'Registro de participación ENDTecNM2025: 84761', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-14', ''],
                        [15, 3, 15, 'Registro de participación ENDTecNM2025: 84762', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-15', ''],
                        [16, 3, 16, 'Registro de participación ENDTecNM2025: 84763', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-16', ''],
                        [17, 3, 17, 'Registro de participación ENDTecNM2025: 84764', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-17', ''],
                        [18, 3, 18, 'Registro de participación ENDTecNM2025: 84765', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-18', ''],
                        [19, 3, 19, 'Registro de participación ENDTecNM2025: 84766', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-19', ''],
                        [20, 3, 20, 'Registro de participación ENDTecNM2025: 84767', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-20', '']
                    ]
                ]
            ];
            
            // Crear una hoja por cada plantilla
            $primera_vez = true;
            foreach ($plantillas as $nombre_hoja => $datos_plantilla) {
                if ($primera_vez) {
                    // Reutilizar la hoja por defecto para la primera plantilla
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setTitle($nombre_hoja);
                    $primera_vez = false;
                } else {
                    // Crear nuevas hojas para las demás
                    $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $nombre_hoja);
                    $spreadsheet->addSheet($sheet);
                }
                
                // Escribir headers con estilo profesional
                $col = 'A';
                $ultima_col = 'A';
                foreach ($datos_plantilla['headers'] as $header) {
                    $sheet->setCellValue($col . '1', $header);
                    // Estilo para headers: fondo azul, texto blanco, negrita
                    $sheet->getStyle($col . '1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'FFFFFF'],
                            'size' => 11
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '1e3c72']
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC']
                            ]
                        ]
                    ]);
                    $ultima_col = $col;
                    $col++;
                }
                $sheet->getRowDimension(1)->setRowHeight(25);
                
                // Escribir datos de ejemplo (10 registros)
                $fila = 2;
                foreach ($datos_plantilla['datos'] as $fila_datos) {
                    $col = 'A';
                    foreach ($fila_datos as $valor) {
                        // Si el valor está vacío, dejar la celda vacía (no escribir nada)
                        if ($valor !== '' && $valor !== null) {
                            $sheet->setCellValue($col . $fila, $valor);
                        }
                        $col++;
                    }
                    $fila++;
                }
                
                // Aplicar bordes a todas las celdas con datos
                $ultima_fila = $fila - 1;
                if ($ultima_fila >= 2) {
                    $sheet->getStyle('A1:' . $ultima_col . $ultima_fila)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC']
                            ]
                        ]
                    ]);
                }
                
                // Ajustar ancho de columnas automáticamente
                $col_letra = 'A';
                $col_num = 0;
                while ($col_num < count($datos_plantilla['headers'])) {
                    $sheet->getColumnDimension($col_letra)->setAutoSize(true);
                    $col_letra++;
                    $col_num++;
                }
                
                // Agregar nota importante para insigniasotorgadas
                if ($nombre_hoja === 'insigniasotorgadas') {
                    $nota_fila = $ultima_fila + 2;
                    $sheet->setCellValue('A' . $nota_fila, 'NOTAS IMPORTANTES:');
                    $nota_style = $sheet->getStyle('A' . $nota_fila);
                    $nota_font = $nota_style->getFont();
                    $nota_font->setBold(true);
                    $nota_font->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0000'));
                    $nota_font->setSize(12);
                    $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                    
                    $nota_fila++;
                    $sheet->setCellValue('A' . $nota_fila, '- Destinatario: Usa el NOMBRE COMPLETO del estudiante (ej: "Juan Perez Gomez"). NO uses IDs numericos.');
                    $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                    $sheet->getStyle('A' . $nota_fila)->getFont()->setSize(10);
                    
                    $nota_fila++;
                    $sheet->setCellValue('A' . $nota_fila, '- Codigo_Insignia: Puede estar vacio (se generara automaticamente) o usar un codigo unico. Si ya existe, se generara uno nuevo.');
                    $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                    $sheet->getStyle('A' . $nota_fila)->getFont()->setSize(10);
                    
                    $nota_fila++;
                    $sheet->setCellValue('A' . $nota_fila, '- Periodo_Emision, Responsable_Emision: Pueden estar vacios (se usaran NULL). Si llenas con un ID, debe existir en la base de datos.');
                    $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                    $sheet->getStyle('A' . $nota_fila)->getFont()->setSize(10);
                    
                    $nota_fila++;
                    $sheet->setCellValue('A' . $nota_fila, '- Estatus: Puede estar vacio (se usara 1 "Activo" por defecto) o usar un ID valido que exista en la tabla estatus.');
                    $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                    $sheet->getStyle('A' . $nota_fila)->getFont()->setSize(10);
                    
                    $nota_fila++;
                    $sheet->setCellValue('A' . $nota_fila, '- Fecha_Emision: Formato YYYY-MM-DD (ej: 2025-01-15)');
                    $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                    $sheet->getStyle('A' . $nota_fila)->getFont()->setSize(10);
                }
            }
            
            // Usar directorio temporal
            $temp_dir = sys_get_temp_dir();
            $filename = $temp_dir . DIRECTORY_SEPARATOR . "plantilla_todas_las_tablas_" . uniqid() . ".xlsx";
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filename);
            
            return $filename;
        } catch (Exception $e) {
            $this->errores[] = "Error al generar plantilla completa: " . $e->getMessage();
            error_log("Error al generar plantilla completa Excel: " . $e->getMessage());
            return false;
        }
    }
    
    public function generarPlantilla($tipo) {
        try {
            // Si es todas las plantillas, usar función especial
            if ($tipo === 'todas_las_plantillas') {
                return $this->generarTodasLasPlantillas();
            }
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Datos de ejemplo para 10 registros
            $datos_ejemplo = [];
            
            switch ($tipo) {
                case 'insignias_otorgadas':
                    $headers = ['id', 'Id_Insignia', 'Id_Destinatario', 'Evidencia', 'Fecha_Emision', 'Id_Periodo_Emision', 'Id_Estatus', 'Código_Insignia', 'Fecha_Creacion_Registro'];
                    $datos_ejemplo = [
                        [1, 3, 1, 'Registro de participación ENDTecNM2025: 84757', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-1', ''],
                        [2, 3, 2, 'Registro de participación ENDTecNM2025: 81775', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-2', ''],
                        [3, 3, 3, 'Registro de participación ENDTecNM2025: 81776', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-3', ''],
                        [4, 3, 4, 'Registro de participación ENDTecNM2025: 81777', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-4', ''],
                        [5, 3, 5, 'Registro de participación ENDTecNM2025: 81778', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-5', ''],
                        [6, 3, 6, 'Registro de participación ENDTecNM2025: 81779', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-6', ''],
                        [7, 3, 7, 'Registro de participación ENDTecNM2025: 81780', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-7', ''],
                        [8, 3, 8, 'Registro de participación ENDTecNM2025: 81781', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-8', ''],
                        [9, 3, 9, 'Registro de participación ENDTecNM2025: 81782', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-9', ''],
                        [10, 3, 10, 'Registro de participación ENDTecNM2025: 81783', '2025-11-28', 20252, 'Aprobada', 'TecNM-SExtVin-Fint-EDep-10', '']
                    ];
                    break;
                case 'destinatarios':
                    $headers = [
                        'ID destinatario', 'Nombre Completo', 'Curp', 'Matricula', 'Correo_Inst', 'Correo_Per', 'Fecha_Creación', 'ITCentro',
                        'FolioRegistroEvento', 'Instituto Tecnológico de Adscripción', 'Género', 'Disciplina', 'Correo Electrónico para Notificación', 'Matricula', 'Evento', 'Lugar', 'Registro', 'Registro Participación'
                    ];
                    $datos_ejemplo = [
                        [1, 'Emmanuel Sanchez Gomez', 'SAGE010809HMCNMMA01', 'IT719IF025', '', '', '', 'Centro Nacional de Investigación y Desarrollo Tecnológico', 'Registro de participación ENDTecNM2025: 81757', 'Instituto Tecnológico de Acapulco', 'M', 'Natación', '', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81757', 'Registro de participación ENDTecNM2025: 81757'],
                        [2, 'Adrián Mendoza Rendón', 'MERA040624HGRNNDA02', 'C21321039', '', '', '', 'Instituto Tecnológico de Acapulco', 'Registro de participación ENDTecNM2025: 81775', 'Instituto Tecnológico de Agua Prieta', 'M', 'Basquetbol', '', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81775', 'Registro de participación ENDTecNM2025: 81775'],
                        [3, 'Juan Pérez Gómez', 'PERJ800101HDFRGN01', '2024001', 'juan.perez@tecnm.mx', '', '', 'Instituto Tecnológico de Agua Prieta', 'Registro de participación ENDTecNM2025: 81776', 'Instituto Tecnológico de Aguascalientes', 'M', 'Taekwondo', 'juan.perez@tecnm.mx', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Primer lugar', 'Registro de participación ENDTecNM2025: 81776', 'Registro de participación ENDTecNM2025: 81776'],
                        [4, 'María González López', 'GOLM900215HDFRGN02', '2024002', 'maria.gonzalez@tecnm.mx', '', '', 'Instituto Tecnológico de Aguascalientes', 'Registro de participación ENDTecNM2025: 81777', 'Instituto Tecnológico de Celaya', 'F', 'Atletismo', 'maria.gonzalez@tecnm.mx', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81777', 'Registro de participación ENDTecNM2025: 81777'],
                        [5, 'Carlos Ramírez Martínez', 'RAMC850320HDFRGN03', '2024003', '', '', '', 'Instituto Tecnológico de Celaya', 'Registro de participación ENDTecNM2025: 81778', 'Instituto Tecnológico de Chihuahua', 'M', 'Sóftbol', '', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81778', 'Registro de participación ENDTecNM2025: 81778'],
                        [6, 'Ana Sánchez Hernández', 'SAHA920510HDFRGN04', '2024004', 'ana.sanchez@tecnm.mx', '', '', 'Instituto Tecnológico de Chihuahua', 'Registro de participación ENDTecNM2025: 81779', 'Instituto Tecnológico de Durango', 'F', 'Ajedrez', 'ana.sanchez@tecnm.mx', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Primer lugar', 'Registro de participación ENDTecNM2025: 81779', 'Registro de participación ENDTecNM2025: 81779'],
                        [7, 'Roberto Torres Díaz', 'TODR880725HDFRGN05', '2024005', '', '', '', 'Instituto Tecnológico de Durango', 'Registro de participación ENDTecNM2025: 81780', 'Instituto Tecnológico de Morelia', 'M', 'Tenis', '', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81780', 'Registro de participación ENDTecNM2025: 81780'],
                        [8, 'Laura Morales Silva', 'MOSL910330HDFRGN06', '2024006', 'laura.morales@tecnm.mx', '', '', 'Instituto Tecnológico de Morelia', 'Registro de participación ENDTecNM2025: 81781', 'Instituto Tecnológico de Puebla', 'F', 'Natación', 'laura.morales@tecnm.mx', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81781', 'Registro de participación ENDTecNM2025: 81781'],
                        [9, 'Fernando Jiménez Ruiz', 'JIRF870415HDFRGN07', '2024007', '', '', '', 'Instituto Tecnológico de Puebla', 'Registro de participación ENDTecNM2025: 81782', 'Instituto Tecnológico de Veracruz', 'M', 'Basquetbol', '', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Participante', 'Registro de participación ENDTecNM2025: 81782', 'Registro de participación ENDTecNM2025: 81782'],
                        [10, 'Patricia Castro Moreno', 'CAMP920620HDFRGN08', '2024008', 'patricia.castro@tecnm.mx', '', '', 'Instituto Tecnológico de Veracruz', 'Registro de participación ENDTecNM2025: 81783', 'Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET)', 'F', 'Atletismo', 'patricia.castro@tecnm.mx', 'LXVII Evento Nacional Deportivo del TecNM 2025', 'Primer lugar', 'Registro de participación ENDTecNM2025: 81783', 'Registro de participación ENDTecNM2025: 81783']
                    ];
                    break;
                case 'centros_it':
                    $headers = ['Nombre_itc', 'Acron', 'Estado', 'Clave_ct', 'Tipo_itc'];
                    $datos_ejemplo = [
                        ['Instituto Tecnológico de San Marcos', 'ITSM', 'Coahuila', '05DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Celaya', 'ITC', 'Guanajuato', '11DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Morelia', 'ITM', 'Michoacán', '16DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Durango', 'ITD', 'Durango', '10DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Tijuana', 'ITT', 'Baja California', '02DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Chihuahua', 'ITCH', 'Chihuahua', '08DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Puebla', 'ITP', 'Puebla', '21DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Veracruz', 'ITV', 'Veracruz', '30DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Mérida', 'ITM', 'Yucatán', '31DIT0001A', 'Federal'],
                        ['Instituto Tecnológico de Cancún', 'ITC', 'Quintana Roo', '23DIT0001A', 'Federal']
                    ];
                    break;
                case 'tipos_insignia':
                    $headers = ['Nombre_Insignia', 'Descripcion', 'Id_Categoria'];
                    $datos_ejemplo = [
                        ['Embajador del Arte', 'Reconocimiento por destacar en actividades artísticas y culturales', 1],
                        ['Embajador del Deporte', 'Reconocimiento por excelencia en actividades deportivas', 3],
                        ['Embajador del Deporte Oro', 'Reconocimiento máximo por excelencia en actividades deportivas (nivel Oro)', 3],
                        ['Embajador del Deporte Plata', 'Reconocimiento por excelencia en actividades deportivas (nivel Plata)', 3],
                        ['Embajador del Deporte Bronce', 'Reconocimiento por excelencia en actividades deportivas (nivel Bronce)', 3],
                        ['Talento Científico', 'Reconocimiento por participación destacada en proyectos científicos', 2],
                        ['Talento Innovador', 'Reconocimiento por innovación tecnológica', 2],
                        ['Responsabilidad Social', 'Reconocimiento por participación en actividades de responsabilidad social', 1],
                        ['Formación y Actualización', 'Reconocimiento por formación continua y actualización', 2],
                        ['Movilidad e Intercambio', 'Reconocimiento por participación en programas de movilidad', 1],
                        ['Liderazgo Estudiantil', 'Reconocimiento por liderazgo en actividades estudiantiles', 1],
                        ['Emprendimiento', 'Reconocimiento por proyectos emprendedores', 1],
                        ['Sustentabilidad', 'Reconocimiento por proyectos de sustentabilidad ambiental', 1]
                    ];
                    break;
                case 'categorias_insignia':
                    $headers = ['Nombre_Cat', 'Descripcion'];
                    $datos_ejemplo = [
                        ['Formación Integral', 'Categoría para insignias de formación integral del estudiante'],
                        ['Desarrollo Académico', 'Categoría para insignias de excelencia académica'],
                        ['Desarrollo Personal', 'Categoría para insignias de desarrollo personal'],
                        ['Responsabilidad Social', 'Categoría para insignias de responsabilidad social'],
                        ['Liderazgo', 'Categoría para insignias de liderazgo estudiantil'],
                        ['Emprendimiento', 'Categoría para insignias de emprendimiento'],
                        ['Investigación', 'Categoría para insignias de investigación'],
                        ['Innovación', 'Categoría para insignias de innovación tecnológica'],
                        ['Sustentabilidad', 'Categoría para insignias de sustentabilidad'],
                        ['Internacionalización', 'Categoría para insignias de movilidad internacional']
                    ];
                    break;
                case 'periodos_emision':
                    $headers = ['Periodo', 'Anio', 'Fecha_Inicio', 'Fecha_Fin'];
                    $datos_ejemplo = [
                        ['Enero-Junio 2024', 2024, '2024-01-01', '2024-06-30'],
                        ['Agosto-Diciembre 2024', 2024, '2024-08-01', '2024-12-31'],
                        ['Enero-Junio 2025', 2025, '2025-01-01', '2025-06-30'],
                        ['Agosto-Diciembre 2025', 2025, '2025-08-01', '2025-12-31'],
                        ['Enero-Junio 2026', 2026, '2026-01-01', '2026-06-30'],
                        ['Agosto-Diciembre 2026', 2026, '2026-08-01', '2026-12-31'],
                        ['Enero-Junio 2027', 2027, '2027-01-01', '2027-06-30'],
                        ['Agosto-Diciembre 2027', 2027, '2027-08-01', '2027-12-31'],
                        ['Enero-Junio 2028', 2028, '2028-01-01', '2028-06-30'],
                        ['Agosto-Diciembre 2028', 2028, '2028-08-01', '2028-12-31']
                    ];
                    break;
                case 'estatus':
                    $headers = ['Nombre_Estatus', 'Acron_Estatus'];
                    $datos_ejemplo = [
                        ['Activo', 'ACT'],
                        ['Pendiente', 'PEND'],
                        ['Autorizado', 'AUT'],
                        ['Rechazado', 'REC'],
                        ['Vencido', 'VEN'],
                        ['En Revisión', 'REV'],
                        ['Aprobado', 'APR'],
                        ['Cancelado', 'CAN'],
                        ['Suspendido', 'SUS'],
                        ['Finalizado', 'FIN']
                    ];
                    break;
                case 'responsables_emision':
                    $headers = ['Nombre_Completo', 'Adscripcion', 'Cargo', 'Codigo_Identificacion', 'Correo', 'Telefono'];
                    $datos_ejemplo = [
                        ['Dr. Juan Pérez García', 1, 'Director', 'TECNM-DIR-001', 'juan.perez@tecnm.mx', '5551234567'],
                        ['Mtra. María González López', 1, 'Subdirectora Académica', 'TECNM-SUB-001', 'maria.gonzalez@tecnm.mx', '5551234568'],
                        ['Lic. Carlos Ramírez Martínez', 1, 'Coordinador de Vinculación', 'TECNM-COORD-001', 'carlos.ramirez@tecnm.mx', '5551234569'],
                        ['Ing. Ana Sánchez Hernández', 1, 'Jefa de Departamento', 'TECNM-JEFE-001', 'ana.sanchez@tecnm.mx', '5551234570'],
                        ['Mtra. Laura Torres Díaz', 1, 'Coordinadora de Extensión', 'TECNM-COORD-002', 'laura.torres@tecnm.mx', '5551234571'],
                        ['Dr. Roberto Morales Silva', 1, 'Director de Investigación', 'TECNM-DIR-002', 'roberto.morales@tecnm.mx', '5551234572'],
                        ['Mtra. Patricia Jiménez Ruiz', 1, 'Coordinadora de Servicios', 'TECNM-COORD-003', 'patricia.jimenez@tecnm.mx', '5551234573'],
                        ['Ing. Fernando Castro Moreno', 1, 'Jefe de División', 'TECNM-JEFE-002', 'fernando.castro@tecnm.mx', '5551234574'],
                        ['Mtra. Gabriela Mendoza Vega', 1, 'Coordinadora Académica', 'TECNM-COORD-004', 'gabriela.mendoza@tecnm.mx', '5551234575'],
                        ['Dr. Luis Hernández Campos', 1, 'Director de Posgrado', 'TECNM-DIR-003', 'luis.hernandez@tecnm.mx', '5551234576']
                    ];
                    break;
                default:
                    $this->errores[] = "Tipo de plantilla no válido: $tipo";
                    return false;
            }
            
            // Escribir headers con estilo profesional
            $col = 'A';
            $ultima_col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                // Estilo para headers: fondo azul, texto blanco, negrita
                $sheet->getStyle($col . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1e3c72']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);
                $ultima_col = $col;
                $col++;
            }
            $sheet->getRowDimension(1)->setRowHeight(25);
            
            // Escribir 10 registros de ejemplo
            $fila = 2;
            foreach ($datos_ejemplo as $fila_datos) {
                $col = 'A';
                foreach ($fila_datos as $valor) {
                    // Si el valor está vacío, dejar la celda vacía (no escribir nada)
                    if ($valor !== '' && $valor !== null) {
                        $sheet->setCellValue($col . $fila, $valor);
                    }
                    $col++;
                }
                $fila++;
            }
            
            // Aplicar bordes a todas las celdas con datos
            $ultima_fila = $fila - 1;
            if ($ultima_fila >= 2) {
                $sheet->getStyle('A1:' . $ultima_col . $ultima_fila)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);
            }
            
            // Ajustar ancho de columnas automáticamente
            // Convertir última columna a número para usar range de forma segura
            $num_cols = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ultima_col);
            for ($i = 1; $i <= $num_cols; $i++) {
                $col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($col_letter)->setAutoSize(true);
            }
            
            // Agregar nota importante para insignias_otorgadas
            if ($tipo === 'insignias_otorgadas') {
                $nota_fila = $ultima_fila + 2;
                $sheet->setCellValue('A' . $nota_fila, 'NOTAS IMPORTANTES:');
                $nota_style = $sheet->getStyle('A' . $nota_fila);
                $nota_font = $nota_style->getFont();
                $nota_font->setBold(true);
                $nota_font->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0000'));
                $nota_font->setSize(12);
                $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                
                $nota_fila++;
                $sheet->setCellValue('A' . $nota_fila, '- Destinatario: Usa el NOMBRE COMPLETO del estudiante (ej: "Juan Perez Gomez"). NO uses IDs numericos.');
                $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                $sheet->getStyle('A' . $nota_fila)->getFont()->setSize(10);
                
                $nota_fila++;
                $sheet->setCellValue('A' . $nota_fila, '- Codigo_Insignia: Puede estar vacio (se generara automaticamente) o usar un codigo unico. Si ya existe, se generara uno nuevo.');
                $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                $sheet->getStyle('A' . $nota_fila)->getFont()->setSize(10);
                
                $nota_fila++;
                $sheet->setCellValue('A' . $nota_fila, '- Periodo_Emision, Responsable_Emision: Pueden estar vacios (se usaran NULL). Si llenas con un ID, debe existir en la base de datos.');
                $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                $sheet->getStyle('A' . $nota_fila)->getFont()->setSize(10);
                
                $nota_fila++;
                $sheet->setCellValue('A' . $nota_fila, '- Estatus: Puede estar vacio (se usara 1 "Activo" por defecto) o usar un ID valido que exista en la tabla estatus.');
                $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                $sheet->getStyle('A' . $nota_fila)->getFont()->setSize(10);
                
                $nota_fila++;
                $sheet->setCellValue('A' . $nota_fila, '- Fecha_Emision: Formato YYYY-MM-DD (ej: 2025-01-15)');
                $sheet->mergeCells('A' . $nota_fila . ':' . $ultima_col . $nota_fila);
                $sheet->getStyle('A' . $nota_fila)->getFont()->setSize(10);
            }
            
            // Usar directorio temporal
            $temp_dir = sys_get_temp_dir();
            $filename = $temp_dir . DIRECTORY_SEPARATOR . "plantilla_$tipo_" . uniqid() . ".xlsx";
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filename);
            
            return $filename;
        } catch (Exception $e) {
            $this->errores[] = "Error al generar plantilla: " . $e->getMessage();
            error_log("Error al generar plantilla Excel: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener errores
     */
    public function getErrores() {
        return $this->errores;
    }
    
    /**
     * Obtener éxitos
     */
    public function getExitos() {
        return $this->exitos;
    }
}

// Procesar formulario (solo si se accede directamente)
if (basename($_SERVER['PHP_SELF']) === 'carga_masiva_excel.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generar_plantilla'])) {
        try {
            $tipo = $_POST['tipo_plantilla'] ?? '';
            
            if (empty($tipo)) {
                throw new Exception("Tipo de plantilla no especificado");
            }
            
            $cargaMasiva = new CargaMasivaExcel($conexion);
            $archivo = $cargaMasiva->generarPlantilla($tipo);
            
            if ($archivo && file_exists($archivo)) {
                // Limpiar cualquier salida previa
                if (ob_get_level()) {
                    ob_end_clean();
                }
                
                // Nombres amigables para el archivo
                $nombres_amigables = [
                    'todas_las_plantillas' => 'Plantilla_Completa_Todas_Las_Tablas',
                    'insignias_otorgadas' => 'Plantilla_Insignias_Otorgadas',
                    'destinatarios' => 'Plantilla_Destinatarios',
                    'centros_it' => 'Plantilla_Centros_IT',
                    'tipos_insignia' => 'Plantilla_Tipos_Insignia',
                    'categorias_insignia' => 'Plantilla_Categorias_Insignia',
                    'periodos_emision' => 'Plantilla_Periodos_Emision',
                    'estatus' => 'Plantilla_Estatus',
                    'responsables_emision' => 'Plantilla_Responsables_Emision'
                ];
                
                $nombre_descarga = ($nombres_amigables[$tipo] ?? "plantilla_$tipo") . ".xlsx";
                
                // Enviar headers
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $nombre_descarga . '"');
                header('Content-Length: ' . filesize($archivo));
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                
                // Leer y enviar archivo
                readfile($archivo);
                
                // Eliminar archivo temporal
                @unlink($archivo);
                exit();
            } else {
                throw new Exception("No se pudo generar el archivo de plantilla");
            }
        } catch (Exception $e) {
            error_log("Error al descargar plantilla: " . $e->getMessage());
            // Redirigir con mensaje de error
            header('Location: carga_masiva_excel.php?error=' . urlencode("Error al generar plantilla: " . $e->getMessage()));
            exit();
        }
    }
    
    if (isset($_POST['cargar_datos'])) {
        try {
            // Inicializar variables
            $resultado = false;
            $errores = [];
            $exitos = [];
            $estadisticas = [];
            $mensaje = 'error';
            $usuario_id = $_SESSION['usuario_id'] ?? 0;
            $usuario_nombre = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Desconocido';
            
            // Verificar que se haya subido un archivo
            if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
                $errores[] = 'No se pudo subir el archivo. Error: ' . ($_FILES['archivo_excel']['error'] ?? 'Archivo no recibido');
                $mensaje = 'error';
            } else {
                $cargaMasiva = new CargaMasivaExcel($conexion);
                $tipo_carga = $_POST['tipo_carga'] ?? '';
                $nombre_archivo = $_FILES['archivo_excel']['name'] ?? '';
                $tamanio_archivo = $_FILES['archivo_excel']['size'] ?? 0;
                
                // Validar tipo de carga
                if (empty($tipo_carga)) {
                    $errores[] = 'No se especificó el tipo de carga';
                } else {
                    // Verificar si se desea firmar las insignias
                    $firmar_insignias = isset($_POST['firmar_insignias']) && $_POST['firmar_insignias'] === '1';
                    
                    // Si se desea firmar, procesar archivos de certificado
                    // Aplicar para "insignias_otorgadas" o "todas_las_tablas" 
                    // NOTA: Si es "todas_las_tablas", la firma solo se aplicará a las hojas de "Insignias Otorgadas"
                    if ($firmar_insignias && ($tipo_carga === 'insignias_otorgadas' || $tipo_carga === 'todas_las_tablas')) {
                        if (empty($_FILES['certificado']['tmp_name']) || empty($_FILES['clave_privada']['tmp_name'])) {
                            if ($tipo_carga === 'todas_las_tablas') {
                                $errores[] = 'Debes cargar el certificado .cer y la clave .key para firmar las Insignias Otorgadas (las demás tablas no se verán afectadas)';
                            } else {
                                $errores[] = 'Debes cargar el certificado .cer y la clave .key para firmar las insignias';
                            }
                            $exitos = [];
                        } else {
                            $contrasena_firma = $_POST['contrasena_firma'] ?? '';
                            if (empty($contrasena_firma)) {
                                $errores[] = 'Debes proporcionar la contraseña de la e.firma';
                                $exitos = [];
                            } else {
                                // Crear archivos temporales para el certificado y la clave
                                $tempDir = sys_get_temp_dir();
                                $cerPath = tempnam($tempDir, 'cert_masivo_') . '.cer';
                                $keyPath = tempnam($tempDir, 'key_masivo_') . '.key';
                                
                                // Copiar archivos subidos a ubicaciones temporales
                                if (!copy($_FILES['certificado']['tmp_name'], $cerPath)) {
                                    $errores[] = 'No se pudo procesar el archivo .cer';
                                    $exitos = [];
                                } elseif (!copy($_FILES['clave_privada']['tmp_name'], $keyPath)) {
                                    @unlink($cerPath);
                                    $errores[] = 'No se pudo procesar el archivo .key';
                                    $exitos = [];
                                } else {
                                    // Configurar firma digital en la clase
                                    $cargaMasiva->configurarFirmaDigital($cerPath, $keyPath, $contrasena_firma);
                                    
                                    // Obtener información del usuario
                                    $usuario_id = $_SESSION['usuario_id'] ?? 0;
                                    $usuario_nombre = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Desconocido';
                                    
                                    // Procesar archivo (la firma se aplicará solo a insignias otorgadas si es todas_las_tablas)
                                    $resultado = $cargaMasiva->procesarArchivo($_FILES['archivo_excel'], $tipo_carga);
                                    
                                    // Limpiar archivos temporales después de procesar
                                    @unlink($cerPath);
                                    @unlink($keyPath);
                                }
                            }
                        }
                    } else {
                        // Obtener información del usuario
                        $usuario_id = $_SESSION['usuario_id'] ?? 0;
                        $usuario_nombre = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Desconocido';
                        
                        $resultado = $cargaMasiva->procesarArchivo($_FILES['archivo_excel'], $tipo_carga);
                    }
                    
                    // Obtener errores y éxitos
                    if (empty($errores)) {
                        $errores = $cargaMasiva->getErrores();
                    }
                    if (empty($exitos)) {
                        $exitos = $cargaMasiva->getExitos();
                    }
                    
                    // Obtener estadísticas
                    $estadisticas = $cargaMasiva->getEstadisticas();
                    
                    // Contar registros
                    $total_registros = count($errores) + count($exitos);
                    $registros_exitosos = $estadisticas['insertados'] ?? 0;
                    $registros_actualizados = $estadisticas['actualizados'] ?? 0;
                    $registros_con_error = count($errores) + ($estadisticas['errores'] ?? 0);
                    $registros_firmados = $estadisticas['firmadas'] ?? 0;
                    
                    // Determinar estado
                    $estado = 'completado';
                    if ($registros_con_error > 0 && $registros_exitosos == 0 && $registros_actualizados == 0) {
                        $estado = 'fallido';
                    } elseif ($registros_con_error > 0) {
                        $estado = 'con_errores';
                    }
                    
                    // Registrar en historial
                    if (isset($usuario_id) && isset($usuario_nombre)) {
                        $cargaMasiva->registrarHistorial(
                            $nombre_archivo,
                            $tipo_carga,
                            $usuario_id,
                            $usuario_nombre,
                            $tamanio_archivo,
                            $total_registros,
                            $registros_exitosos,
                            $registros_actualizados,
                            $registros_con_error,
                            $estado
                        );
                    }
                    
                    $mensaje = $resultado ? 'success' : 'error';
                } // Cerrar else de validación de tipo_carga
            } // Cerrar else de validación de archivo
        } catch (Exception $e) {
            error_log("Error en carga masiva: " . $e->getMessage() . " - " . $e->getTraceAsString());
            if (!isset($errores)) {
                $errores = [];
            }
            $errores[] = 'Error al procesar la carga: ' . $e->getMessage();
            if (!isset($exitos)) {
                $exitos = [];
            }
            if (!isset($mensaje)) {
                $mensaje = 'error';
            }
        } catch (Error $e) {
            error_log("Error fatal en carga masiva: " . $e->getMessage() . " - " . $e->getTraceAsString());
            if (!isset($errores)) {
                $errores = [];
            }
            $errores[] = 'Error fatal al procesar la carga: ' . $e->getMessage();
            if (!isset($exitos)) {
                $exitos = [];
            }
            if (!isset($mensaje)) {
                $mensaje = 'error';
            }
        }
    }
}

// Inicializar variables para el HTML (necesarias para mostrar resultados)
if (!isset($mensaje)) {
    $mensaje = null;
}
if (!isset($errores)) {
    $errores = [];
}
if (!isset($exitos)) {
    $exitos = [];
}
if (!isset($estadisticas)) {
    $estadisticas = [];
}
if (!isset($registros_firmados)) {
    $registros_firmados = 0;
}
if (!isset($registros_exitosos)) {
    $registros_exitosos = 0;
}

// Solo mostrar HTML si se accede directamente
if (basename($_SERVER['PHP_SELF']) === 'carga_masiva_excel.php') {
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carga Masiva Excel - Insignias TecNM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: 
                radial-gradient(circle at 15% 25%, rgba(0, 102, 204, 0.18) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(0, 51, 102, 0.15) 0%, transparent 45%),
                radial-gradient(circle at 50% 15%, rgba(74, 144, 226, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 25% 85%, rgba(0, 102, 204, 0.1) 0%, transparent 45%),
                linear-gradient(135deg, 
                  #e8f0f8 0%, 
                  #d5e3f0 20%, 
                  #c5d8ec 40%, 
                  #d5e3f0 60%, 
                  #e8f0f8 80%, 
                  #f0f5fa 100%);
            background-attachment: fixed;
            background-size: 100% 100%;
            min-height: 100vh;
            padding-top: 100px;
            padding-bottom: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        /* HEADER AZUL COMO LOGIN */
        header {
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
        
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .section {
            margin-bottom: 40px;
            padding: 30px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            background: #f8f9fa;
        }
        
        .section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.8em;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group select,
        .form-group input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group select:focus,
        .form-group input[type="file"]:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            margin-right: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 5px solid;
        }
        
        .alert-success {
            background: #d4edda;
            border-color: #27ae60;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .alert-info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        
        .results {
            margin-top: 20px;
        }
        
        .results h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .results ul {
            list-style: none;
            padding: 0;
        }
        
        .results li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .results li:last-child {
            border-bottom: none;
        }
        
        .help-text {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border-left: 4px solid #2196f3;
        }
        
        .help-text h4 {
            color: #1976d2;
            margin-bottom: 10px;
        }
        
        .help-text ul {
            margin-left: 20px;
        }
        
        .help-text li {
            margin-bottom: 5px;
        }
        
        .back-btn {
            position: fixed;
            top: 100px;
            left: 20px;
            background: linear-gradient(135deg, 
                #003366 0%, 
                #0066CC 50%, 
                #003366 100%);
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0,51,102,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,51,102,0.4);
        }
        
        /* FOOTER AZUL COMO LOGIN */
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
            margin: 0;
            opacity: 0.9;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        /* RESPONSIVE - Tablet */
        @media (max-width: 1024px) {
            header {
                padding: 25px 0;
            }
            
            .header-logo {
                height: 50px;
                left: -180px;
            }
            
            header h1 {
                font-size: 24px;
            }
            
            body {
                padding-top: 90px;
            }
        }
        
        /* RESPONSIVE - Móviles y tablets pequeñas */
        @media (max-width: 768px) {
            header {
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
            
            header h1 {
                font-size: 20px;
                margin: 0;
            }
            
            body {
                padding-top: 80px;
            }
            
            .back-btn {
                top: 90px;
                left: 10px;
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .footer-links {
                flex-direction: column;
                align-items: center;
                gap: 12px;
            }
        }
        
        /* RESPONSIVE - Móviles pequeños */
        @media (max-width: 480px) {
            header {
                padding: 15px 0;
            }
            
            .header-content {
                padding: 0 10px;
                gap: 8px;
            }
            
            .header-logo {
                height: 35px;
            }
            
            header h1 {
                font-size: 18px;
            }
            
            body {
                padding-top: 70px;
            }
            
            .back-btn {
                top: 80px;
                left: 5px;
                padding: 8px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER AZUL -->
    <header>
        <div class="header-content">
            <img src="imagen/logo.png" alt="TecNM Logo" class="header-logo">
            <h1>Insignias TecNM</h1>
        </div>
    </header>
    
    <a href="modulo_de_administracion.php" class="back-btn">← Volver al Panel</a>
    
    <div class="container">
        <div class="header">
            <h1>📊 Carga Masiva de Datos</h1>
            <p>Sistema de Insignias TecNM - Importación desde Excel</p>
        </div>
        
        <div class="content">
            <?php if (isset($mensaje)): ?>
                <?php if ($mensaje === 'success'): ?>
                    <div class="alert alert-success">
                        <strong>✅ Carga completada!</strong> Los datos se han procesado correctamente.
                        <?php if (isset($registros_firmados) && $registros_firmados > 0): ?>
                            <br><strong>✍️ Insignias firmadas:</strong> <?php echo $registros_firmados; ?> de <?php echo $registros_exitosos; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-error">
                        <strong>❌ Error en la carga:</strong> Revise los errores mostrados abajo.
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($exitos)): ?>
                    <div class="results">
                        <h3>✅ Registros Exitosos (<?php echo count($exitos); ?>)</h3>
                        <ul>
                            <?php foreach ($exitos as $exito): ?>
                                <li><?php echo htmlspecialchars($exito); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errores)): ?>
                    <div class="results">
                        <h3>❌ Errores Encontrados (<?php echo count($errores); ?>)</h3>
                        <ul>
                            <?php foreach ($errores as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <strong>❌ Error:</strong> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                </div>
            <?php endif; ?>
            
            <!-- Sección 1: Generar Plantillas -->
            <div class="section">
                <h2>📋 Generar Plantillas Excel</h2>
                <p>Descarga plantillas con el formato correcto para cada tipo de datos:</p>
                
                <form method="POST" style="display: inline-block;">
                    <div class="form-group">
                        <label for="tipo_plantilla">Tipo de Plantilla:</label>
                        <select name="tipo_plantilla" id="tipo_plantilla" required>
                            <option value="">Seleccione una opción</option>
                            <option value="todas_las_plantillas" style="background: #28a745; color: white; font-weight: bold;">🚀 TODAS LAS PLANTILLAS</option>
                            <option value="insignias_otorgadas">Insignias Otorgadas</option>
                            <option value="destinatarios">Destinatarios</option>
                            <option value="centros_it">Centros IT</option>
                            <option value="tipos_insignia">Tipos de Insignia</option>
                            <option value="categorias_insignia">Categorías de Insignia</option>
                            <option value="periodos_emision">Periodos de Emisión</option>
                            <option value="estatus">Estatus</option>
                            <option value="responsables_emision">Responsables de Emisión</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="generar_plantilla" class="btn btn-warning">
                        📥 Descargar Plantilla
                    </button>
                </form>
                
            </div>
            
            <!-- Sección 2: Cargar Datos -->
            <div class="section">
                <h2>📤 Cargar Datos desde Excel</h2>
                <p>Seleccione el archivo Excel y el tipo de datos a cargar:</p>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="tipo_carga">Tipo de Datos:</label>
                        <select name="tipo_carga" id="tipo_carga" required>
                            <option value="">Seleccione una opción</option>
                            <option value="todas_las_tablas" style="background: #28a745; color: white; font-weight: bold;">🚀 TODAS LAS TABLAS (Carga Completa)</option>
                            <option value="insignias_otorgadas">Insignias Otorgadas</option>
                            <option value="destinatarios">Destinatarios</option>
                            <option value="centros_it">Centros IT</option>
                            <option value="tipos_insignia">Tipos de Insignia</option>
                            <option value="categorias_insignia">Categorías de Insignia</option>
                            <option value="periodos_emision">Periodos de Emisión</option>
                            <option value="estatus">Estatus</option>
                            <option value="responsables_emision">Responsables de Emisión</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="archivo_excel">Archivo Excel:</label>
                        <input type="file" name="archivo_excel" id="archivo_excel" 
                               accept=".xlsx,.xls" required>
                    </div>
                    
                    <button type="submit" name="cargar_datos" class="btn btn-success">
                        🚀 Procesar Archivo
                    </button>
                </form>
                
            </div>
            
        </div>
    </div>
    
    <!-- FOOTER AZUL -->
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
    
    <script>
        // Validación del lado del cliente
        document.getElementById('archivo_excel').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('El archivo es demasiado grande. Máximo 10MB.');
                    e.target.value = '';
                }
                
                const extension = file.name.split('.').pop().toLowerCase();
                if (!['xlsx', 'xls'].includes(extension)) {
                    alert('Formato de archivo no válido. Use Excel (.xlsx o .xls)');
                    e.target.value = '';
                }
            }
        });
        
        // Confirmación antes de procesar
        document.querySelector('form[enctype="multipart/form-data"]').addEventListener('submit', function(e) {
            const tipoCarga = document.getElementById('tipo_carga').value;
            const archivo = document.getElementById('archivo_excel').files[0];
            
            if (!tipoCarga || !archivo) {
                e.preventDefault();
                alert('Por favor seleccione el tipo de datos y el archivo Excel.');
                return;
            }
            
            const mensaje = `¿Está seguro de procesar el archivo "${archivo.name}" para ${tipoCarga}?`;
            if (!confirm(mensaje)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
<?php
} // Fin del if que verifica acceso directo
?>
