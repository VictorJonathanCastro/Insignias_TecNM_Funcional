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
    private function cargarInsigniasOtorgadas($data) {
        $headers = array_shift($data); // Primera fila son headers
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
    private function cargarDestinatarios($data) {
        $headers = array_shift($data);
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
    private function cargarCentrosIT($data) {
        $headers = array_shift($data);
        $procesados = 0;
        
        foreach ($data as $fila => $row) {
            if (empty(array_filter($row))) continue;
            
            try {
                $datos = $this->validarDatosCentroIT($row, $headers, $fila + 2);
                if (!$datos) continue;
                
                $sql = "INSERT INTO it_centros 
                        (Nombre_itc, Acron, Estado, Clave_ct, Tipo_itc) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param("sssss", 
                    $datos['Nombre_itc'],
                    $datos['Acron'],
                    $datos['Estado'],
                    $datos['Clave_ct'],
                    $datos['Tipo_itc']
                );
                
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
     * Validar datos de centro IT
     */
    private function validarDatosCentroIT($row, $headers, $fila) {
        $datos = [];
        $columnas = array_flip($headers);
        
        $campos_requeridos = ['Nombre_itc', 'Acron', 'Estado', 'Clave_ct', 'Tipo_itc'];
        
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
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
            position: absolute;
            top: 20px;
            left: 20px;
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
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
                
                <div class="help-text">
                    <h4>💡 Instrucciones para Plantillas:</h4>
                    <ul>
                        <li>Las plantillas incluyen las columnas necesarias</li>
                        <li>La primera fila contiene los nombres de las columnas</li>
                        <li>La segunda fila muestra un ejemplo de datos</li>
                        <li>Complete los datos desde la tercera fila en adelante</li>
                        <li>No modifique los nombres de las columnas</li>
                    </ul>
                </div>
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
                
                <div class="help-text">
                    <h4>⚠️ Consideraciones Importantes:</h4>
                    <ul>
                        <li>El archivo debe tener el formato correcto según la plantilla</li>
                        <li>Los datos se validarán antes de insertarse</li>
                        <li>Se mostrarán errores detallados para cada fila problemática</li>
                        <li>Los registros válidos se procesarán aunque haya errores</li>
                        <li>Se recomienda hacer una copia de seguridad antes de cargar</li>
                    </ul>
                </div>
            </div>
            
            <!-- Sección 3: Información Adicional -->
            <div class="section">
                <h2>ℹ️ Información del Sistema</h2>
                <div class="help-text">
                    <h4>📊 Tipos de Carga Disponibles:</h4>
                    <ul>
                        <li><strong>Insignias Otorgadas:</strong> Registro masivo de insignias entregadas</li>
                        <li><strong>Destinatarios:</strong> Carga masiva de estudiantes y personal</li>
                        <li><strong>Centros IT:</strong> Registro de institutos tecnológicos</li>
                        <li><strong>Tipos de Insignia:</strong> Catálogo de tipos de insignias</li>
                        <li><strong>Categorías de Insignia:</strong> Clasificación de insignias</li>
                        <li><strong>Periodos de Emisión:</strong> Periodos escolares</li>
                    </ul>
                </div>
                
                <div class="help-text">
                    <h4>🔧 Características Técnicas:</h4>
                    <ul>
                        <li>Soporte para archivos Excel (.xlsx, .xls)</li>
                        <li>Validación automática de datos</li>
                        <li>Procesamiento por lotes eficiente</li>
                        <li>Reporte detallado de resultados</li>
                        <li>Manejo de errores robusto</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
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
