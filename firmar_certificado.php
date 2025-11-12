<?php
// Endpoint para firmar un certificado con e.firma (SAT)
// Requiere: certificado .cer, clave .key, contraseña y datos de la insignia

session_start();
require_once 'conexion.php';
require_once 'firma_digital_real.php';

header('Content-Type: text/html; charset=utf-8');

function responder($ok, $msg, $conexion = null, $datos_firma = null) {
    if ($ok) {
        // Si hay datos de firma, guardarlos en sesión
        if ($datos_firma) {
            $_SESSION['firma_digital_base64'] = $datos_firma['firma_base64'] ?? '';
            $_SESSION['certificado_info'] = $datos_firma['certificado_info'] ?? '';
            $_SESSION['hash_verificacion'] = $datos_firma['hash_verificacion'] ?? '';
        }
        
        // Siempre redirigir al formulario para que el usuario pueda registrar
        $url_redirect = 'metadatos_formulario.php?firma_guardada=1';
        
        echo "<script>
            alert('" . addslashes($msg) . "');
            window.location.href = '" . $url_redirect . "';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . addslashes($msg) . "');
            window.location.href = 'metadatos_formulario.php';
        </script>";
    }
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responder(false, 'Método no permitido', $conexion);
    }

    // Validar archivos
    if (empty($_FILES['certificado']['tmp_name']) || empty($_FILES['clave']['tmp_name'])) {
        responder(false, 'Debes cargar el .cer y el .key', $conexion);
    }
    $contrasena = $_POST['contrasena'] ?? '';
    if (empty($contrasena)) {
        responder(false, 'Debes proporcionar la contraseña de la e.firma', $conexion);
    }

    // Usar archivos temporales del sistema (NO guardar permanentemente)
    // Crear archivos temporales únicos para procesar
    $tempDir = sys_get_temp_dir();
    $cerPath = tempnam($tempDir, 'cert_') . '.cer';
    $keyPath = tempnam($tempDir, 'key_') . '.key';
    
    // Copiar archivos subidos a ubicaciones temporales
    if (!copy($_FILES['certificado']['tmp_name'], $cerPath)) {
        responder(false, 'No se pudo procesar el archivo .cer', $conexion);
    }
    if (!copy($_FILES['clave']['tmp_name'], $keyPath)) {
        @unlink($cerPath); // Limpiar en caso de error
        responder(false, 'No se pudo procesar el archivo .key', $conexion);
    }

    // Construir texto a firmar basado en la insignia
    $codigo = $_POST['codigo_insignia'] ?? '';
    $destinatario = $_POST['destinatario'] ?? '';
    $nombreInsignia = $_POST['nombre_insignia'] ?? '';
    $fechaEmision = $_POST['fecha_emision'] ?? '';
    $responsable = $_POST['responsable'] ?? 'Responsable de Emisión';
    $cargo = $_POST['cargo'] ?? 'RESPONSABLE DE EMISIÓN';

    $texto = "Certificado de Insignia Digital - TecNM\n" .
             "Código: $codigo\n" .
             "Destinatario: $destinatario\n" .
             "Insignia: $nombreInsignia\n" .
             "Fecha de emisión: $fechaEmision\n" .
             "Responsable: $responsable\n" .
             "Cargo: $cargo";

    // Generar firma real
    $firma = new FirmaDigitalReal($conexion);
    $resultado = $firma->generarFirmaDigitalReal($texto, $cerPath, $keyPath, $contrasena);
    
    // IMPORTANTE: Eliminar archivos temporales INMEDIATAMENTE después de generar la firma
    // No guardar permanentemente los archivos .cer y especialmente el .key
    // Esto se hace ANTES de verificar el resultado para asegurar la limpieza en cualquier caso
    @unlink($cerPath);
    @unlink($keyPath);
    
    if (!$resultado['success']) {
        responder(false, $resultado['error'] ?? 'No se pudo generar la firma', $conexion);
    }

    // Obtener información del certificado para referencia (solo metadatos, no el archivo)
    $certInfo = $resultado['metadatos']['certificado_info'] ?? null;

    // Guardar la firma con el RESPONSABLE que realmente quedó asociado a la insignia
    $conexion->select_db("insignia");

    // 1) Intentar obtener el responsable asociado al código de insignia
    $responsable_id = null;
    $stmt_find_resp = $conexion->prepare("SELECT Responsable_Emision FROM insigniasotorgadas WHERE Codigo_Insignia = ? LIMIT 1");
    if ($stmt_find_resp) {
        $stmt_find_resp->bind_param("s", $codigo);
        $stmt_find_resp->execute();
        $res_find = $stmt_find_resp->get_result();
        if ($res_find && $res_find->num_rows > 0) {
            $row = $res_find->fetch_assoc();
            $responsable_id = intval($row['Responsable_Emision']);
        }
        $stmt_find_resp->close();
    }

    // 2) Si no lo encontramos por el código, buscar/crear por nombre (fallback)
    if (!$responsable_id) {
        $sql_resp = "SELECT ID_responsable FROM responsable_emision WHERE Nombre_Completo = ? LIMIT 1";
        $stmt_resp = $conexion->prepare($sql_resp);
        if ($stmt_resp) {
            $stmt_resp->bind_param("s", $responsable);
            $stmt_resp->execute();
            $result_resp = $stmt_resp->get_result();
            if ($result_resp && $result_resp->num_rows > 0) {
                $row_resp = $result_resp->fetch_assoc();
                $responsable_id = $row_resp['ID_responsable'];
            } else {
                $sql_insert_resp = "INSERT INTO responsable_emision (Nombre_Completo, Cargo, Adscripcion) VALUES (?, ?, 1)";
                $stmt_insert_resp = $conexion->prepare($sql_insert_resp);
                if ($stmt_insert_resp) {
                    $stmt_insert_resp->bind_param("ss", $responsable, $cargo);
                    if ($stmt_insert_resp->execute()) {
                        $responsable_id = intval($conexion->insert_id);
                    }
                    $stmt_insert_resp->close();
                }
            }
            $stmt_resp->close();
        }
    }
    
    // Guardar la firma digital en el responsable si existe
    if ($responsable_id) {
        // Verificar si el campo firma_digital_base64 existe, si no, crearlo
        $check_field = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'firma_digital_base64'");
        if (!$check_field || $check_field->num_rows == 0) {
            // Crear el campo si no existe
            $conexion->query("ALTER TABLE responsable_emision ADD COLUMN firma_digital_base64 LONGTEXT NULL");
        }
        
        // Guardar el SELLO DIGITAL REAL del SAT (SHA256 Base64), no una imagen
        // El sello digital real es el resultado de openssl_sign con SHA256
        $sello_digital_real = $resultado['firma_base64']; // Este es el sello real del SAT
        
        // NOTA: NO guardamos la ruta del certificado/key en la BD por seguridad
        // Los archivos .cer y .key se eliminan inmediatamente después de generar la firma
        // Solo guardamos la firma digital Base64 generada
        
        // Actualizar el responsable con el SELLO DIGITAL REAL (Base64 del SAT)
        // Verificar si el campo certificado_path existe (opcional, solo para info del .cer público)
        $check_cert = $conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'certificado_path'");
        $certificado_path_db = null; // No guardar ruta de archivos sensibles
        
        // Solo guardar info de metadatos del certificado si está disponible (opcional)
        $certificado_info_text = null;
        if ($certInfo && isset($certInfo['subject'])) {
            $certificado_info_text = json_encode([
                'subject' => $certInfo['subject'] ?? [],
                'serial_number' => $certInfo['serial_number'] ?? '',
                'valid_to' => $certInfo['valid_to'] ?? ''
            ]);
        }
        
        if ($check_cert && $check_cert->num_rows > 0) {
            // Si el campo existe, actualizar solo la firma (certificado_path puede ser NULL)
            $sql_update_firma = "UPDATE responsable_emision 
                                SET firma_digital_base64 = ?
                                WHERE ID_responsable = ?";
            $stmt_update = $conexion->prepare($sql_update_firma);
            
            if ($stmt_update) {
                $stmt_update->bind_param("si", $sello_digital_real, $responsable_id);
                $stmt_update->execute();
                $stmt_update->close();
            }
        } else {
            // Si no existe el campo certificado_path, solo actualizar la firma
            $sql_update_firma = "UPDATE responsable_emision 
                                SET firma_digital_base64 = ?
                                WHERE ID_responsable = ?";
            $stmt_update = $conexion->prepare($sql_update_firma);
            
            if ($stmt_update) {
                $stmt_update->bind_param("si", $sello_digital_real, $responsable_id);
                $stmt_update->execute();
                $stmt_update->close();
            }
        }
    }

    // Preparar datos de firma para guardar en sesión y base de datos
    $certificado_info_text = null;
    if ($certInfo && isset($certInfo['subject'])) {
        $certificado_info_text = json_encode([
            'subject' => $certInfo['subject'] ?? [],
            'serial_number' => $certInfo['serial_number'] ?? '',
            'valid_to' => $certInfo['valid_to'] ?? ''
        ]);
    }
    
    $hash_verificacion = hash('sha256', $resultado['firma_base64']);
    
    $datos_firma = [
        'firma_base64' => $resultado['firma_base64'],
        'certificado_info' => $certificado_info_text,
        'hash_verificacion' => $hash_verificacion
    ];
    
    // Mostrar resultado exitoso
    $mensaje_exito = '✅ Firma generada correctamente. Ahora puedes registrar el reconocimiento.';
    
    responder(true, $mensaje_exito, $conexion, $datos_firma);
} catch (Throwable $e) {
    // Asegurar que los archivos temporales se eliminen incluso si hay excepción
    if (isset($cerPath) && file_exists($cerPath)) {
        @unlink($cerPath);
    }
    if (isset($keyPath) && file_exists($keyPath)) {
        @unlink($keyPath);
    }
    responder(false, $e->getMessage(), $conexion ?? null);
}


