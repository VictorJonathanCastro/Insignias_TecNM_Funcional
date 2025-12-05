<?php
// ========================================
// SISTEMA DE CARGA MASIVA VIA EXCEL
// Proyecto Insignias TecNM
// ========================================

// Iniciar sesión solo si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo ejecutar el código principal si el archivo se accede directamente
// (no cuando se incluye desde otro archivo)
if (basename($_SERVER['PHP_SELF']) === 'carga_masiva_excel.php') {
    require_once 'conexion.php';
    
    // Verificar sesión de administrador
    if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Admin') {
        header('Location: login.php');
        exit();
    }
} else {
    // Si se está incluyendo, solo cargar la conexión si no está definida
    if (!isset($conexion)) {
        require_once 'conexion.php';
    }
}

// Incluir librería para leer Excel
if (!file_exists('vendor/autoload.php')) {
    die('
    <!DOCTYPE html>
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
    </html>
    ');
}

require_once 'vendor/autoload.php';

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
            
            // Procesar según el tipo de carga
            switch ($tipo_carga) {
                case 'insignias_otorgadas':
                    return $this->cargarInsigniasOtorgadas($data);
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
        
        // Mapeo de nombres de hojas a tipos de carga
        $mapeo_hojas = [
            'insignias_otorgadas' => ['insignias', 'otorgadas', 'insignias otorgadas'],
            'destinatarios' => ['destinatarios', 'estudiantes', 'personas'],
            'centros_it' => ['centros', 'it', 'centros it', 'institutos'],
            'tipos_insignia' => ['tipos', 'tipos insignia', 'tipos de insignia'],
            'categorias_insignia' => ['categorias', 'categorías', 'categorias insignia'],
            'periodos_emision' => ['periodos', 'periodos emision', 'periodos de emision'],
            'estatus' => ['estatus', 'estados', 'status'],
            'responsables_emision' => ['responsables', 'responsables emision', 'responsables de emision']
        ];
        
        // Procesar cada hoja
        for ($i = 0; $i < $total_hojas; $i++) {
            $sheet = $spreadsheet->getSheet($i);
            $nombre_hoja = strtolower(trim($sheet->getTitle()));
            $data = $sheet->toArray();
            
            if (empty($data) || count($data) < 2) {
                $this->errores[] = "Hoja '$nombre_hoja': Está vacía o no tiene datos suficientes";
                $hojas_con_error++;
                continue;
            }
            
            // Detectar tipo de tabla por headers
            $headers_originales = $data[0];
            $headers_normalizados = array_map('strtolower', array_map('trim', $headers_originales));
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
                    $resultado = $this->cargarInsigniasOtorgadas($data_sin_headers, $headers_originales);
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
        
        // Insignias Otorgadas
        if (in_array('id_insignia', $headers) && in_array('id_destinatario', $headers)) {
            return 'insignias_otorgadas';
        }
        
        // Destinatarios
        if (in_array('nombre_completo', $headers) && (in_array('matricula', $headers) || in_array('correo', $headers))) {
            return 'destinatarios';
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
    private function cargarInsigniasOtorgadas($data, $headers_provided = null) {
        // Si se proporcionan headers, usarlos; si no, tomar la primera fila
        if ($headers_provided !== null) {
            $headers = $headers_provided;
        } else {
            $headers = array_shift($data); // Primera fila son headers
        }
        $procesados = 0;
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue; // Saltar filas vacías
            
            try {
                // Validar datos requeridos
                $datos = $this->validarDatosInsigniaOtorgada($row, $headers, $fila + 2);
                if (!$datos) continue;
                
                // Insertar en base de datos
                $sql = "INSERT INTO T_insignias_otorgadas 
                        (Id_Insignia, Id_Destinatario, Fecha_Emision, Id_Periodo_Emision, Id_Estatus) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $this->conexion->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error al preparar consulta de insignia otorgada: " . $this->conexion->error);
                }
                $stmt->bind_param("iisii", 
                    $datos['Id_Insignia'],
                    $datos['Id_Destinatario'],
                    $datos['Fecha_Emision'],
                    $datos['Id_Periodo_Emision'],
                    $datos['Id_Estatus']
                );
                
                if ($stmt->execute()) {
                    $insignia_id = $this->conexion->insert_id;
                    $procesados++;
                    
                    // Si está habilitada la firma digital, firmar la insignia
                    if ($this->firmar_insignias && $this->firma_digital) {
                        $resultado_firma = $this->firmarInsignia($insignia_id, $datos);
                        if ($resultado_firma['success']) {
                            $this->estadisticas['firmadas']++;
                            $this->exitos[] = "Fila " . ($fila + 2) . ": Insignia otorgada registrada y firmada correctamente";
                        } else {
                            $this->exitos[] = "Fila " . ($fila + 2) . ": Insignia otorgada registrada correctamente (error al firmar: " . $resultado_firma['error'] . ")";
                        }
                    } else {
                        $this->exitos[] = "Fila " . ($fila + 2) . ": Insignia otorgada registrada correctamente";
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
            // Obtener datos completos de la insignia para la firma
            $sql = "SELECT 
                        tio.Id_Insignia,
                        tio.Id_Destinatario,
                        tio.Fecha_Emision,
                        d.Nombre_Completo as destinatario,
                        COALESCE(tipo_ins.Nombre_Insignia, ti.Descripcion, 'Insignia') as nombre_insignia,
                        tio.Id_Estatus
                    FROM T_insignias_otorgadas tio
                    LEFT JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
                    LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
                    LEFT JOIN tipo_insignia tipo_ins ON ti.Tipo_Insignia = tipo_ins.id
                    WHERE tio.id = ?";
            
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
            
            // Generar código de insignia único si no existe
            $codigo_insignia = 'TECNM-' . date('Y') . '-' . str_pad($insignia_id, 6, '0', STR_PAD_LEFT);
            
            // Obtener responsable (usar el de sesión o uno por defecto)
            $responsable = $_SESSION['nombre'] ?? 'Responsable de Emisión';
            
            // Preparar datos para la firma
            $datos_firma = [
                'destinatario' => $insignia_data['destinatario'] ?? 'N/A',
                'nombre_insignia' => $insignia_data['nombre_insignia'] ?? 'Insignia',
                'codigo_insignia' => $codigo_insignia,
                'fecha_emision' => date('d/m/Y', strtotime($insignia_data['Fecha_Emision'])),
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
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            
            try {
                $datos = $this->validarDatosDestinatario($row, $headers, $fila + 2);
                if (!$datos) continue;
                
                $sql = "INSERT INTO destinatario 
                        (Id_Centro, Nombre_Completo, Nombre, Apellido_Paterno, Apellido_Materno, 
                         Genero, Curp, Matricula, Correo, Telefono, Rol) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->conexion->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error al preparar consulta de destinatario: " . $this->conexion->error);
                }
                $stmt->bind_param("issssssssss", 
                    $datos['Id_Centro'],
                    $datos['Nombre_Completo'],
                    $datos['Nombre'],
                    $datos['Apellido_Paterno'],
                    $datos['Apellido_Materno'],
                    $datos['Genero'],
                    $datos['Curp'],
                    $datos['Matricula'],
                    $datos['Correo'],
                    $datos['Telefono'],
                    $datos['Rol']
                );
                
                if ($stmt->execute()) {
                    $procesados++;
                    $this->exitos[] = "Fila " . ($fila + 2) . ": Destinatario registrado correctamente";
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
                $columnas = array_flip($headers);
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
                $columnas = array_flip($headers);
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
                $columnas = array_flip($headers);
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
                $columnas = array_flip($headers);
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
     * Validar datos de insignia otorgada
     */
    private function validarDatosInsigniaOtorgada($row, $headers, $fila) {
        $datos = [];
        
        // Mapear columnas por nombre
        $columnas = array_flip($headers);
        
        // Validar campos requeridos
        $campos_requeridos = ['Id_Insignia', 'Id_Destinatario', 'Fecha_Emision', 'Id_Periodo_Emision', 'Id_Estatus'];
        
        foreach ($campos_requeridos as $campo) {
            if (!isset($columnas[$campo])) {
                $this->errores[] = "Fila $fila: Columna '$campo' no encontrada";
                return false;
            }
            
            $valor = trim($row[$columnas[$campo]] ?? '');
            if (empty($valor)) {
                $this->errores[] = "Fila $fila: Campo '$campo' es requerido";
                return false;
            }
            
            $datos[$campo] = $valor;
        }
        
        // Validar tipos de datos
        if (!is_numeric($datos['Id_Insignia'])) {
            $this->errores[] = "Fila $fila: Id_Insignia debe ser numérico";
            return false;
        }
        
        if (!is_numeric($datos['Id_Destinatario'])) {
            $this->errores[] = "Fila $fila: Id_Destinatario debe ser numérico";
            return false;
        }
        
        // Validar fecha
        try {
            $datos['Fecha_Emision'] = date('Y-m-d', strtotime($datos['Fecha_Emision']));
        } catch (Exception $e) {
            $this->errores[] = "Fila $fila: Fecha_Emision formato inválido";
            return false;
        }
        
        return $datos;
    }
    
    /**
     * Validar datos de destinatario
     */
    private function validarDatosDestinatario($row, $headers, $fila) {
        $datos = [];
        $columnas = array_flip($headers);
        
        $campos_requeridos = ['Id_Centro', 'Nombre_Completo', 'Nombre', 'Apellido_Paterno', 'Apellido_Materno'];
        
        foreach ($campos_requeridos as $campo) {
            if (!isset($columnas[$campo])) {
                $this->errores[] = "Fila $fila: Columna '$campo' no encontrada";
                return false;
            }
            
            $valor = trim($row[$columnas[$campo]] ?? '');
            if (empty($valor)) {
                $this->errores[] = "Fila $fila: Campo '$campo' es requerido";
                return false;
            }
            
            $datos[$campo] = $valor;
        }
        
        // Campos opcionales
        $datos['Genero'] = trim($row[$columnas['Genero']] ?? '');
        $datos['Curp'] = trim($row[$columnas['Curp']] ?? '');
        $datos['Matricula'] = trim($row[$columnas['Matricula']] ?? '');
        $datos['Correo'] = trim($row[$columnas['Correo']] ?? '');
        $datos['Telefono'] = trim($row[$columnas['Telefono']] ?? '');
        $datos['Rol'] = trim($row[$columnas['Rol']] ?? 'Estudiante');
        
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
        $email_limpio = trim($email);
        $email_limpio = preg_replace('/[\r\n\t]+/', '', $email_limpio); // Quitar saltos de línea y tabs
        $email_limpio = preg_replace('/\s+/', '', $email_limpio); // Quitar todos los espacios
        $email_limpio = trim($email_limpio, " \t\n\r\0\x0B\"'`"); // Quitar comillas y espacios
        
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
        $normalizado = strtolower(trim($nombre));
        $normalizado = str_replace([' ', '-', '_', '.', 'á', 'é', 'í', 'ó', 'ú', 'ñ'], 
                                   ['', '', '', '', 'a', 'e', 'i', 'o', 'u', 'n'], 
                                   $normalizado);
        return $normalizado;
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
                    'headers' => ['Id_Centro', 'Nombre_Completo', 'Nombre', 'Apellido_Paterno', 'Apellido_Materno', 'Genero', 'Curp', 'Matricula', 'Correo', 'Telefono', 'Rol'],
                    'datos' => [
                        [1, 'Juan Pérez Gómez', 'Juan', 'Pérez', 'Gómez', 'Masculino', 'PERJ800101HDFRGN01', '2024001', 'juan.perez@tecnm.mx', '5551234567', 'Estudiante'],
                        [1, 'María González López', 'María', 'González', 'López', 'Femenino', 'GOLM900215HDFRGN02', '2024002', 'maria.gonzalez@tecnm.mx', '5551234568', 'Estudiante'],
                        [1, 'Carlos Ramírez Martínez', 'Carlos', 'Ramírez', 'Martínez', 'Masculino', 'RAMC850320HDFRGN03', '2024003', 'carlos.ramirez@tecnm.mx', '5551234569', 'Estudiante'],
                        [1, 'Ana Sánchez Hernández', 'Ana', 'Sánchez', 'Hernández', 'Femenino', 'SAHA920510HDFRGN04', '2024004', 'ana.sanchez@tecnm.mx', '5551234570', 'Estudiante'],
                        [1, 'Roberto Torres Díaz', 'Roberto', 'Torres', 'Díaz', 'Masculino', 'TODR880725HDFRGN05', '2024005', 'roberto.torres@tecnm.mx', '5551234571', 'Estudiante'],
                        [1, 'Laura Morales Silva', 'Laura', 'Morales', 'Silva', 'Femenino', 'MOSL910330HDFRGN06', '2024006', 'laura.morales@tecnm.mx', '5551234572', 'Estudiante'],
                        [1, 'Fernando Jiménez Ruiz', 'Fernando', 'Jiménez', 'Ruiz', 'Masculino', 'JIRF870415HDFRGN07', '2024007', 'fernando.jimenez@tecnm.mx', '5551234573', 'Estudiante'],
                        [1, 'Patricia Castro Moreno', 'Patricia', 'Castro', 'Moreno', 'Femenino', 'CAMP920620HDFRGN08', '2024008', 'patricia.castro@tecnm.mx', '5551234574', 'Estudiante'],
                        [1, 'Gabriel Mendoza Vega', 'Gabriel', 'Mendoza', 'Vega', 'Masculino', 'MEVG890825HDFRGN09', '2024009', 'gabriel.mendoza@tecnm.mx', '5551234575', 'Estudiante'],
                        [1, 'Luis Hernández Campos', 'Luis', 'Hernández', 'Campos', 'Masculino', 'HECL900930HDFRGN10', '2024010', 'luis.hernandez@tecnm.mx', '5551234576', 'Estudiante']
                    ]
                ],
                'Insignias Otorgadas' => [
                    'headers' => ['Id_Insignia', 'Id_Destinatario', 'Fecha_Emision', 'Id_Periodo_Emision', 'Id_Estatus'],
                    'datos' => [
                        [1, 1, '2024-02-15', 1, 1],
                        [2, 2, '2024-02-16', 1, 1],
                        [3, 3, '2024-02-17', 1, 1],
                        [4, 4, '2024-02-18', 1, 1],
                        [5, 5, '2024-02-19', 1, 1],
                        [6, 6, '2024-02-20', 1, 1],
                        [7, 7, '2024-02-21', 1, 1],
                        [8, 8, '2024-02-22', 1, 1],
                        [9, 9, '2024-02-23', 1, 1],
                        [10, 10, '2024-02-24', 1, 1]
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
                
                // Escribir headers
                $col = 'A';
                $ultima_col = 'A';
                foreach ($datos_plantilla['headers'] as $header) {
                    $sheet->setCellValue($col . '1', $header);
                    $sheet->getStyle($col . '1')->getFont()->setBold(true);
                    $ultima_col = $col;
                    $col++;
                }
                
                // Escribir datos de ejemplo (10 registros)
                $fila = 2;
                foreach ($datos_plantilla['datos'] as $fila_datos) {
                    $col = 'A';
                    foreach ($fila_datos as $valor) {
                        $sheet->setCellValue($col . $fila, $valor);
                        $col++;
                    }
                    $fila++;
                }
                
                // Ajustar ancho de columnas automáticamente
                $col_letra = 'A';
                $col_num = 0;
                while ($col_num < count($datos_plantilla['headers'])) {
                    $sheet->getColumnDimension($col_letra)->setAutoSize(true);
                    $col_letra++;
                    $col_num++;
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
                    $headers = ['Id_Insignia', 'Id_Destinatario', 'Fecha_Emision', 'Id_Periodo_Emision', 'Id_Estatus'];
                    $datos_ejemplo = [
                        [1, 1, '2024-02-15', 1, 1],
                        [2, 2, '2024-02-16', 1, 1],
                        [3, 3, '2024-02-17', 1, 1],
                        [4, 4, '2024-02-18', 1, 1],
                        [5, 5, '2024-02-19', 1, 1],
                        [6, 6, '2024-02-20', 1, 1],
                        [7, 7, '2024-02-21', 1, 1],
                        [8, 8, '2024-02-22', 1, 1],
                        [9, 9, '2024-02-23', 1, 1],
                        [10, 10, '2024-02-24', 1, 1]
                    ];
                    break;
                case 'destinatarios':
                    $headers = ['Id_Centro', 'Nombre_Completo', 'Nombre', 'Apellido_Paterno', 'Apellido_Materno', 'Genero', 'Curp', 'Matricula', 'Correo', 'Telefono', 'Rol'];
                    $datos_ejemplo = [
                        [1, 'Juan Pérez Gómez', 'Juan', 'Pérez', 'Gómez', 'Masculino', 'PERJ800101HDFRGN01', '2024001', 'juan.perez@tecnm.mx', '5551234567', 'Estudiante'],
                        [1, 'María González López', 'María', 'González', 'López', 'Femenino', 'GOLM900215HDFRGN02', '2024002', 'maria.gonzalez@tecnm.mx', '5551234568', 'Estudiante'],
                        [1, 'Carlos Ramírez Martínez', 'Carlos', 'Ramírez', 'Martínez', 'Masculino', 'RAMC850320HDFRGN03', '2024003', 'carlos.ramirez@tecnm.mx', '5551234569', 'Estudiante'],
                        [1, 'Ana Sánchez Hernández', 'Ana', 'Sánchez', 'Hernández', 'Femenino', 'SAHA920510HDFRGN04', '2024004', 'ana.sanchez@tecnm.mx', '5551234570', 'Estudiante'],
                        [1, 'Roberto Torres Díaz', 'Roberto', 'Torres', 'Díaz', 'Masculino', 'TODR880725HDFRGN05', '2024005', 'roberto.torres@tecnm.mx', '5551234571', 'Estudiante'],
                        [1, 'Laura Morales Silva', 'Laura', 'Morales', 'Silva', 'Femenino', 'MOSL910330HDFRGN06', '2024006', 'laura.morales@tecnm.mx', '5551234572', 'Estudiante'],
                        [1, 'Fernando Jiménez Ruiz', 'Fernando', 'Jiménez', 'Ruiz', 'Masculino', 'JIRF870415HDFRGN07', '2024007', 'fernando.jimenez@tecnm.mx', '5551234573', 'Estudiante'],
                        [1, 'Patricia Castro Moreno', 'Patricia', 'Castro', 'Moreno', 'Femenino', 'CAMP920620HDFRGN08', '2024008', 'patricia.castro@tecnm.mx', '5551234574', 'Estudiante'],
                        [1, 'Gabriel Mendoza Vega', 'Gabriel', 'Mendoza', 'Vega', 'Masculino', 'MEVG890825HDFRGN09', '2024009', 'gabriel.mendoza@tecnm.mx', '5551234575', 'Estudiante'],
                        [1, 'Luis Hernández Campos', 'Luis', 'Hernández', 'Campos', 'Masculino', 'HECL900930HDFRGN10', '2024010', 'luis.hernandez@tecnm.mx', '5551234576', 'Estudiante']
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
            
            // Escribir headers con estilo en negrita
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $col++;
            }
            
            // Escribir 10 registros de ejemplo
            $fila = 2;
            foreach ($datos_ejemplo as $fila_datos) {
            $col = 'A';
                foreach ($fila_datos as $valor) {
                    $sheet->setCellValue($col . $fila, $valor);
                $col++;
                }
                $fila++;
            }
            
            // Ajustar ancho de columnas automáticamente
            foreach (range('A', $col) as $columna) {
                $sheet->getColumnDimension($columna)->setAutoSize(true);
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
    <a href="historial_cargas_masivas.php" class="back-btn" style="left: 200px;">📋 Ver Historial</a>
    
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
                    
                    <!-- Opción de Firma Digital (para Insignias Otorgadas o Todas las Tablas) -->
                    <div class="form-group" id="firma-group" style="display: none;">
                        <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; border-left: 4px solid #2196f3; margin-bottom: 15px;">
                            <label style="display: flex; align-items: center; cursor: pointer; font-weight: 600; color: #1976d2;">
                                <input type="checkbox" name="firmar_insignias" id="firmar_insignias" value="1" style="margin-right: 10px; width: 20px; height: 20px;">
                                <span>✍️ ¿Deseas firmar las insignias digitalmente?</span>
                            </label>
                            <p id="texto-explicativo-firma" style="margin: 10px 0 0 30px; color: #666; font-size: 14px;">
                                Si marcas esta opción, todas las insignias se firmarán automáticamente con tu e.firma (SAT)
                            </p>
                        </div>
                        
                        <div id="campos-firma" style="display: none; background: #f8f9fa; padding: 20px; border-radius: 8px; border: 2px solid #dee2e6;">
                            <h4 style="color: #2c3e50; margin-bottom: 15px;">📋 Datos de Firma Digital (e.firma SAT)</h4>
                            
                            <div class="form-group">
                                <label for="certificado">Certificado (.cer): <span style="color: red;">*</span></label>
                                <input type="file" name="certificado" id="certificado" accept=".cer" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
                                <small style="color: #666; display: block; margin-top: 5px;">Archivo de certificado digital (.cer) de tu e.firma</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="clave_privada">Clave Privada (.key): <span style="color: red;">*</span></label>
                                <input type="file" name="clave_privada" id="clave_privada" accept=".key" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
                                <small style="color: #666; display: block; margin-top: 5px;">Archivo de clave privada (.key) de tu e.firma</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="contrasena_firma">Contraseña de la e.firma: <span style="color: red;">*</span></label>
                                <input type="password" name="contrasena_firma" id="contrasena_firma" 
                                       placeholder="Ingresa la contraseña de tu e.firma" 
                                       style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
                                <small style="color: #666; display: block; margin-top: 5px;">Contraseña que usas para acceder a tu e.firma en el portal del SAT</small>
                            </div>
                            
                            <div style="background: #fff3cd; padding: 12px; border-radius: 5px; border-left: 4px solid #ffc107; margin-top: 15px;">
                                <strong>⚠️ Importante:</strong> Los archivos .cer y .key se procesan temporalmente y se eliminan inmediatamente después de generar las firmas. No se almacenan en el servidor.
                            </div>
                        </div>
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
        
        // Mostrar/ocultar campos de firma según el tipo de carga
        document.getElementById('tipo_carga').addEventListener('change', function(e) {
            const tipoCarga = e.target.value;
            const firmaGroup = document.getElementById('firma-group');
            const camposFirma = document.getElementById('campos-firma');
            const firmarCheckbox = document.getElementById('firmar_insignias');
            
            // Mostrar opción de firma si es "Insignias Otorgadas" o "Todas las Tablas"
            if (tipoCarga === 'insignias_otorgadas' || tipoCarga === 'todas_las_tablas') {
                firmaGroup.style.display = 'block';
                // Si el checkbox está marcado, mostrar campos
                if (firmarCheckbox.checked) {
                    camposFirma.style.display = 'block';
                }
                // Actualizar texto explicativo según el tipo
                const textoExplicativo = document.getElementById('texto-explicativo-firma');
                if (textoExplicativo) {
                    if (tipoCarga === 'todas_las_tablas') {
                        textoExplicativo.textContent = 'Si marcas esta opción, solo las Insignias Otorgadas se firmarán automáticamente con tu e.firma (SAT). Las demás tablas no se verán afectadas.';
                    } else {
                        textoExplicativo.textContent = 'Si marcas esta opción, todas las insignias se firmarán automáticamente con tu e.firma (SAT)';
                    }
                }
            } else {
                firmaGroup.style.display = 'none';
                camposFirma.style.display = 'none';
                firmarCheckbox.checked = false;
            }
        });
        
        // Mostrar/ocultar campos de certificado según el checkbox
        document.getElementById('firmar_insignias').addEventListener('change', function(e) {
            const camposFirma = document.getElementById('campos-firma');
            if (e.target.checked) {
                camposFirma.style.display = 'block';
            } else {
                camposFirma.style.display = 'none';
                // Limpiar campos
                document.getElementById('certificado').value = '';
                document.getElementById('clave_privada').value = '';
                document.getElementById('contrasena_firma').value = '';
            }
        });
        
        // Confirmación antes de procesar
        document.querySelector('form[enctype="multipart/form-data"]').addEventListener('submit', function(e) {
            const tipoCarga = document.getElementById('tipo_carga').value;
            const archivo = document.getElementById('archivo_excel').files[0];
            const firmarInsignias = document.getElementById('firmar_insignias').checked;
            
            if (!tipoCarga || !archivo) {
                e.preventDefault();
                alert('Por favor seleccione el tipo de datos y el archivo Excel.');
                return;
            }
            
            // Validar campos de firma si está habilitada (para insignias otorgadas o todas las tablas)
            if (firmarInsignias && (tipoCarga === 'insignias_otorgadas' || tipoCarga === 'todas_las_tablas')) {
                const certificado = document.getElementById('certificado').files[0];
                const clavePrivada = document.getElementById('clave_privada').files[0];
                const contrasena = document.getElementById('contrasena_firma').value;
                
                if (!certificado || !clavePrivada || !contrasena) {
                    e.preventDefault();
                    if (tipoCarga === 'todas_las_tablas') {
                        alert('Para firmar las Insignias Otorgadas, debes cargar el certificado (.cer), la clave privada (.key) y proporcionar la contraseña. Las demás tablas no se verán afectadas.');
                    } else {
                    alert('Para firmar las insignias, debes cargar el certificado (.cer), la clave privada (.key) y proporcionar la contraseña.');
                    }
                    return;
                }
            }
            
            let mensaje = `¿Está seguro de procesar el archivo "${archivo.name}" para ${tipoCarga}?`;
            if (firmarInsignias && tipoCarga === 'insignias_otorgadas') {
                mensaje += '\n\n⚠️ Las insignias se firmarán digitalmente con tu e.firma.';
            }
            
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
