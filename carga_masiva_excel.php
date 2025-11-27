<?php
// ========================================
// SISTEMA DE CARGA MASIVA VIA EXCEL
// Proyecto Insignias TecNM
// ========================================

session_start();
require_once 'conexion.php';

// Verificar sesión de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Admin') {
    header('Location: login.php');
    exit();
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
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
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
            'periodos_emision' => ['periodos', 'periodos emision', 'periodos de emision']
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
                $stmt->bind_param("iisii", 
                    $datos['Id_Insignia'],
                    $datos['Id_Destinatario'],
                    $datos['Fecha_Emision'],
                    $datos['Id_Periodo_Emision'],
                    $datos['Id_Estatus']
                );
                
                if ($stmt->execute()) {
                    $procesados++;
                    $this->exitos[] = "Fila " . ($fila + 2) . ": Insignia otorgada registrada correctamente";
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
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            
            try {
                $datos = $this->validarDatosCentroIT($row, $headers, $fila + 2);
                if (!$datos) continue;
                
                // Construir SQL dinámicamente según los campos disponibles
                $campos_sql = ['Nombre_itc', 'Acron', 'Estado', 'Clave_ct', 'Tipo_itc'];
                $valores_sql = [];
                $tipos = '';
                $parametros = [];
                
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
                
                $campos_str = implode(', ', $campos_sql);
                $placeholders = implode(', ', array_fill(0, count($campos_sql), '?'));
                
                $sql = "INSERT INTO it_centros ($campos_str) VALUES ($placeholders)";
                
                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param($tipos, ...$valores_sql);
                
                if ($stmt->execute()) {
                    $procesados++;
                    $this->exitos[] = "Fila " . ($fila + 2) . ": Centro IT registrado correctamente";
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
    public function generarPlantilla($tipo) {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        switch ($tipo) {
            case 'insignias_otorgadas':
                $headers = ['Id_Insignia', 'Id_Destinatario', 'Fecha_Emision', 'Id_Periodo_Emision', 'Id_Estatus'];
                $ejemplos = [1, 1, '2024-01-15', 1, 1];
                break;
            case 'destinatarios':
                $headers = ['Id_Centro', 'Nombre_Completo', 'Nombre', 'Apellido_Paterno', 'Apellido_Materno', 'Genero', 'Curp', 'Matricula', 'Correo', 'Telefono', 'Rol'];
                $ejemplos = [1, 'Juan Pérez Gómez', 'Juan', 'Pérez', 'Gómez', 'Masculino', 'PERJ800101HDFRGN01', '2024001', 'juan.perez@tecnm.mx', '5551234567', 'Estudiante'];
                break;
            case 'centros_it':
                $headers = ['Nombre_itc', 'Acron', 'Estado', 'Clave_ct', 'Tipo_itc'];
                $ejemplos = ['Instituto Tecnológico de Celaya', 'ITC', 'Guanajuato', '11DIT0001A', 'Federal'];
                break;
            default:
                return false;
        }
        
        // Escribir headers
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // Escribir ejemplo
        $col = 'A';
        foreach ($ejemplos as $ejemplo) {
            $sheet->setCellValue($col . '2', $ejemplo);
            $col++;
        }
        
        // Guardar archivo
        $filename = "plantilla_$tipo.xlsx";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filename);
        
        return $filename;
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

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cargaMasiva = new CargaMasivaExcel($conexion);
    
    if (isset($_POST['generar_plantilla'])) {
        $tipo = $_POST['tipo_plantilla'];
        $archivo = $cargaMasiva->generarPlantilla($tipo);
        
        if ($archivo) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $archivo . '"');
            readfile($archivo);
            unlink($archivo);
            exit();
        }
    }
    
    if (isset($_POST['cargar_datos']) && isset($_FILES['archivo_excel'])) {
        $tipo_carga = $_POST['tipo_carga'];
        $resultado = $cargaMasiva->procesarArchivo($_FILES['archivo_excel'], $tipo_carga);
        
        $mensaje = $resultado ? 'success' : 'error';
        $errores = $cargaMasiva->getErrores();
        $exitos = $cargaMasiva->getExitos();
    }
}
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
            
            <!-- Sección 1: Generar Plantillas -->
            <div class="section">
                <h2>📋 Generar Plantillas Excel</h2>
                <p>Descarga plantillas con el formato correcto para cada tipo de datos:</p>
                
                <form method="POST" style="display: inline-block;">
                    <div class="form-group">
                        <label for="tipo_plantilla">Tipo de Plantilla:</label>
                        <select name="tipo_plantilla" id="tipo_plantilla" required>
                            <option value="">Seleccione una opción</option>
                            <option value="insignias_otorgadas">Insignias Otorgadas</option>
                            <option value="destinatarios">Destinatarios</option>
                            <option value="centros_it">Centros IT</option>
                            <option value="tipos_insignia">Tipos de Insignia</option>
                            <option value="categorias_insignia">Categorías de Insignia</option>
                            <option value="periodos_emision">Periodos de Emisión</option>
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
        
        // Confirmación antes de procesar
        document.querySelector('form[enctype="multipart/form-data"]').addEventListener('submit', function(e) {
            const tipoCarga = document.getElementById('tipo_carga').value;
            const archivo = document.getElementById('archivo_excel').files[0];
            
            if (!tipoCarga || !archivo) {
                e.preventDefault();
                alert('Por favor seleccione el tipo de datos y el archivo Excel.');
                return;
            }
            
            if (!confirm(`¿Está seguro de procesar el archivo "${archivo.name}" para ${tipoCarga}?`)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
