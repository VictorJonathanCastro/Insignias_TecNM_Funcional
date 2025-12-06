<?php
/**
 * Script para corregir automáticamente los IDs en la plantilla Excel
 * Ejecutar desde el navegador: http://localhost/Insignias_TecNM_Funcional/corregir_plantilla_excel.php
 */

header('Content-Type: text/html; charset=UTF-8');

require_once 'conexion.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Si se subió un archivo, usarlo
if (isset($_FILES['archivo_excel']) && $_FILES['archivo_excel']['error'] === UPLOAD_ERR_OK) {
    $archivo_temporal = $_FILES['archivo_excel']['tmp_name'];
    $archivo_excel = $archivo_temporal;
} elseif (isset($_POST['ruta_archivo']) && !empty($_POST['ruta_archivo'])) {
    // Si se especificó una ruta
    $archivo_excel = $_POST['ruta_archivo'];
} else {
    // Ruta por defecto
    $archivo_excel = 'C:\Users\vc556\Downloads\Plantilla_Completa_Todas_Las_Tablas.xlsx';
}

// Si no existe el archivo, mostrar formulario
if (!file_exists($archivo_excel) && !isset($_FILES['archivo_excel'])) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Corregir Plantilla Excel</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            form { background: #f5f5f5; padding: 20px; border-radius: 8px; }
            input[type="file"], input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; }
            button { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
            button:hover { background: #5568d3; }
        </style>
    </head>
    <body>
        <h2>🔧 Corregir Plantilla Excel</h2>
        <p>Este script corregirá automáticamente los IDs incorrectos en tu plantilla Excel.</p>
        <form method="POST" enctype="multipart/form-data">
            <h3>Opción 1: Subir archivo</h3>
            <input type="file" name="archivo_excel" accept=".xlsx,.xls" required>
            <h3>Opción 2: Especificar ruta</h3>
            <input type="text" name="ruta_archivo" placeholder="C:\ruta\al\archivo.xlsx" value="C:\Users\vc556\Downloads\Plantilla_Completa_Todas_Las_Tablas.xlsx">
            <br><br>
            <button type="submit">Corregir Archivo</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

if (!file_exists($archivo_excel)) {
    die("❌ Error: No se encontró el archivo en: $archivo_excel");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corrigiendo Plantilla Excel</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        h3 { color: #667eea; margin-top: 20px; }
        p { line-height: 1.6; }
        ul { line-height: 1.8; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; }
        a { color: #667eea; }
        a.download-btn { background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px; }
        a.download-btn:hover { background: #5568d3; }
    </style>
</head>
<body>
<div class="container">
<?php
echo "<h2>🔧 Corrigiendo Plantilla Excel</h2>";
echo "<p>Archivo: <strong>" . htmlspecialchars($archivo_excel) . "</strong></p>";

try {
    // Cargar el archivo Excel
    $spreadsheet = IOFactory::load($archivo_excel);
    
    // Obtener IDs disponibles de la base de datos
    echo "<h3>📋 Obteniendo IDs disponibles de la base de datos...</h3>";
    
    // Obtener IDs de estatus
    $check_estatus = $conexion->query("SHOW COLUMNS FROM estatus LIKE 'id'");
    $campo_id_estatus = ($check_estatus && $check_estatus->num_rows > 0) ? 'id' : 'ID_estatus';
    $check_nombre = $conexion->query("SHOW COLUMNS FROM estatus LIKE 'Nombre_Estatus'");
    $campo_nombre = ($check_nombre && $check_nombre->num_rows > 0) ? 'Nombre_Estatus' : 'Estatus';
    
    $sql_estatus = "SELECT $campo_id_estatus as id, $campo_nombre as nombre FROM estatus ORDER BY $campo_id_estatus";
    $result_estatus = $conexion->query($sql_estatus);
    $estatus_disponibles = [];
    if ($result_estatus) {
        while ($row = $result_estatus->fetch_assoc()) {
            $estatus_disponibles[$row['id']] = $row['nombre'];
        }
    }
    
    // Obtener primer ID de estatus disponible (preferir "Activo" o el más bajo)
    $primer_id_estatus = null;
    foreach ($estatus_disponibles as $id => $nombre) {
        if (stripos($nombre, 'Activo') !== false || $primer_id_estatus === null) {
            $primer_id_estatus = $id;
            if (stripos($nombre, 'Activo') !== false) break;
        }
    }
    if ($primer_id_estatus === null && !empty($estatus_disponibles)) {
        $primer_id_estatus = array_key_first($estatus_disponibles);
    }
    
    echo "<p>✅ Estatus disponibles: " . implode(', ', array_map(function($id, $nom) { return "$id ($nom)"; }, array_keys($estatus_disponibles), $estatus_disponibles)) . "</p>";
    echo "<p>📌 Usando Id_Estatus: <strong>$primer_id_estatus</strong> para reemplazar el valor 1</p>";
    
    // Obtener IDs de T_insignias
    $sql_insignias = "SELECT id FROM T_insignias ORDER BY id";
    $result_insignias = $conexion->query($sql_insignias);
    $insignias_disponibles = [];
    if ($result_insignias) {
        while ($row = $result_insignias->fetch_assoc()) {
            $insignias_disponibles[] = $row['id'];
        }
    }
    
    echo "<p>✅ Insignias disponibles: " . implode(', ', $insignias_disponibles) . "</p>";
    
    if (empty($insignias_disponibles)) {
        die("❌ Error: No hay insignias disponibles en la base de datos");
    }
    
    // Procesar la hoja "insignias otorgadas"
    echo "<h3>📝 Procesando hoja 'insignias otorgadas'...</h3>";
    
    $hoja_nombres = $spreadsheet->getSheetNames();
    $hoja_encontrada = false;
    
    foreach ($hoja_nombres as $nombre_hoja) {
        // Buscar hoja que contenga "insignias" o "otorgadas"
        if (stripos($nombre_hoja, 'insignias') !== false || stripos($nombre_hoja, 'otorgadas') !== false) {
            $hoja = $spreadsheet->getSheetByName($nombre_hoja);
            $hoja_encontrada = true;
            
            echo "<p>✅ Hoja encontrada: <strong>$nombre_hoja</strong></p>";
            
            // Leer datos
            $data = $hoja->toArray();
            if (empty($data)) {
                echo "<p>⚠️ La hoja está vacía</p>";
                continue;
            }
            
            // Encontrar índices de columnas
            $headers = $data[0];
            $col_id_estatus = null;
            $col_id_insignia = null;
            
            foreach ($headers as $idx => $header) {
                if (stripos($header, 'Id_Estatus') !== false || stripos($header, 'Estatus') !== false) {
                    $col_id_estatus = $idx;
                }
                if (stripos($header, 'Id_Insignia') !== false || stripos($header, 'Insignia') !== false) {
                    $col_id_insignia = $idx;
                }
            }
            
            if ($col_id_estatus === null) {
                echo "<p>⚠️ No se encontró la columna Id_Estatus</p>";
            } else {
                echo "<p>✅ Columna Id_Estatus encontrada en índice: $col_id_estatus</p>";
            }
            
            if ($col_id_insignia === null) {
                echo "<p>⚠️ No se encontró la columna Id_Insignia</p>";
            } else {
                echo "<p>✅ Columna Id_Insignia encontrada en índice: $col_id_insignia</p>";
            }
            
            // Corregir datos (empezar desde la fila 2, índice 1)
            $correcciones_estatus = 0;
            $correcciones_insignia = 0;
            
            for ($fila = 1; $fila < count($data); $fila++) {
                $row = $data[$fila];
                
                // Corregir Id_Estatus si es 1 o está vacío
                if ($col_id_estatus !== null && isset($row[$col_id_estatus])) {
                    $valor_actual = trim($row[$col_id_estatus]);
                    if ($valor_actual == '1' || $valor_actual == '' || !in_array((int)$valor_actual, array_keys($estatus_disponibles))) {
                        $hoja->setCellValueByColumnAndRow($col_id_estatus + 1, $fila + 1, $primer_id_estatus);
                        $correcciones_estatus++;
                        echo "<p>  ✓ Fila " . ($fila + 1) . ": Id_Estatus cambiado de '$valor_actual' a '$primer_id_estatus'</p>";
                    }
                }
                
                // Corregir Id_Insignia si es mayor a los disponibles
                if ($col_id_insignia !== null && isset($row[$col_id_insignia])) {
                    $valor_actual = trim($row[$col_id_insignia]);
                    $valor_int = (int)$valor_actual;
                    
                    if (!in_array($valor_int, $insignias_disponibles)) {
                        // Usar el ID disponible más cercano o el primero disponible
                        $nuevo_id = $insignias_disponibles[0]; // Usar el primero disponible
                        // Si el valor es mayor, intentar usar el último disponible
                        if ($valor_int > max($insignias_disponibles)) {
                            $nuevo_id = max($insignias_disponibles);
                        } else {
                            // Buscar el ID más cercano disponible
                            foreach ($insignias_disponibles as $id_disponible) {
                                if ($id_disponible >= $valor_int) {
                                    $nuevo_id = $id_disponible;
                                    break;
                                }
                            }
                        }
                        
                        $hoja->setCellValueByColumnAndRow($col_id_insignia + 1, $fila + 1, $nuevo_id);
                        $correcciones_insignia++;
                        echo "<p>  ✓ Fila " . ($fila + 1) . ": Id_Insignia cambiado de '$valor_actual' a '$nuevo_id'</p>";
                    }
                }
            }
            
            echo "<p><strong>✅ Correcciones realizadas:</strong></p>";
            echo "<ul>";
            echo "<li>Id_Estatus: $correcciones_estatus correcciones</li>";
            echo "<li>Id_Insignia: $correcciones_insignia correcciones</li>";
            echo "</ul>";
            
            break;
        }
    }
    
    if (!$hoja_encontrada) {
        echo "<p>⚠️ No se encontró una hoja con 'insignias' o 'otorgadas' en el nombre</p>";
        echo "<p>Hojas disponibles: " . implode(', ', $hoja_nombres) . "</p>";
    }
    
    // Guardar el archivo corregido
    $nombre_original = isset($_FILES['archivo_excel']) ? $_FILES['archivo_excel']['name'] : basename($archivo_excel);
    $nombre_sin_ext = pathinfo($nombre_original, PATHINFO_FILENAME);
    $nombre_archivo_corregido = $nombre_sin_ext . '_CORREGIDA.xlsx';
    
    // Intentar guardar en directorio temp
    $directorio_temp = __DIR__ . '/temp';
    $archivo_corregido = $directorio_temp . '/' . $nombre_archivo_corregido;
    $guardado_exitoso = false;
    
    // Crear directorio temp si no existe
    if (!is_dir($directorio_temp)) {
        $permisos_creacion = @mkdir($directorio_temp, 0755, true);
        if (!$permisos_creacion) {
            echo "<p class='warning'>⚠️ No se pudo crear el directorio temp en: <code>" . htmlspecialchars($directorio_temp) . "</code></p>";
            echo "<p>Verificando directorio temporal del sistema...</p>";
        }
    }
    
    // Verificar permisos de escritura
    $puede_escribir = false;
    if (is_dir($directorio_temp)) {
        $puede_escribir = is_writable($directorio_temp);
        if (!$puede_escribir) {
            echo "<p class='warning'>⚠️ El directorio temp existe pero no tiene permisos de escritura.</p>";
            echo "<p>Intenta ejecutar: <code>chmod 755 " . htmlspecialchars($directorio_temp) . "</code> o <code>chmod 777 " . htmlspecialchars($directorio_temp) . "</code></p>";
        }
    }
    
    if ($puede_escribir) {
        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($archivo_corregido);
            $guardado_exitoso = true;
            echo "<h3>✅ Archivo corregido guardado</h3>";
            echo "<p><strong>Archivo: " . htmlspecialchars($nombre_archivo_corregido) . "</strong></p>";
            echo "<p><a href='temp/" . urlencode($nombre_archivo_corregido) . "' download class='download-btn'>⬇️ Descargar Archivo Corregido</a></p>";
        } catch (Exception $e) {
            echo "<p class='warning'>⚠️ No se pudo guardar en el servidor: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>Ofreciendo descarga directa...</p>";
        }
    } else {
        echo "<p class='warning'>⚠️ El directorio temp no tiene permisos de escritura. Ofreciendo descarga directa...</p>";
    }
    
    // Si no se pudo guardar, guardar en memoria y ofrecer descarga mediante formulario
    if (!$guardado_exitoso) {
        // Intentar usar sys_get_temp_dir() que generalmente tiene permisos
        $temp_sistema = sys_get_temp_dir();
        $archivo_temp = $temp_sistema . '/' . uniqid('excel_') . '_' . $nombre_archivo_corregido;
        
        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($archivo_temp);
            
            echo "<h3>📥 Descarga del Archivo Corregido</h3>";
            echo "<p>El archivo se guardó temporalmente. Haz clic en el botón para descargarlo:</p>";
            echo "<form method='POST' action='descargar_excel_corregido.php'>";
            echo "<input type='hidden' name='archivo' value='" . htmlspecialchars(base64_encode($archivo_temp)) . "'>";
            echo "<input type='hidden' name='nombre' value='" . htmlspecialchars($nombre_archivo_corregido) . "'>";
            echo "<button type='submit' class='download-btn'>⬇️ Descargar Archivo Corregido</button>";
            echo "</form>";
            echo "<p class='warning'>⚠️ Nota: Este archivo temporal se eliminará después de descargarlo.</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ No se pudo guardar el archivo en ninguna ubicación.</p>";
            echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>Solución:</strong> Por favor, verifica los permisos del directorio temp o contacta al administrador del servidor.</p>";
        }
    }
    
    echo "<p>📝 Puedes usar este archivo para la carga masiva.</p>";
    
    // También intentar guardar en Downloads si es posible (solo en Windows)
    if (PHP_OS_FAMILY === 'Windows') {
        $downloads_path = 'C:\Users\vc556\Downloads\\' . $nombre_archivo_corregido;
        if (is_writable('C:\Users\vc556\Downloads') && $guardado_exitoso) {
            try {
                copy($archivo_corregido, $downloads_path);
                echo "<p>✅ También guardado en: <strong>" . htmlspecialchars($downloads_path) . "</strong></p>";
            } catch (Exception $e) {
                // Ignorar si no se puede escribir en Downloads
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

$conexion->close();
?>
</div>
</body>
</html>

