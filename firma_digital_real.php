<?php
/**
 * Sistema de Firma Digital Real para Insignias TecNM
 * Implementa firma digital usando certificados .cer, .key y contraseña
 * Genera firmas en formato Base64 como en el ejemplo proporcionado
 */

class FirmaDigitalReal {
    
    private $conexion;
    private $directorio_certificados = 'certificados/';
    private $directorio_firmas = 'firmas_digitales/';
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
        
        // Crear directorios si no existen
        if (!file_exists($this->directorio_certificados)) {
            mkdir($this->directorio_certificados, 0755, true);
        }
        if (!file_exists($this->directorio_firmas)) {
            mkdir($this->directorio_firmas, 0755, true);
        }
    }
    
    /**
     * Obtener ruta de OpenSSL
     * @return string|false - Ruta a openssl o false si no se encuentra
     */
    private function obtenerRutaOpenSSL() {
        // Detectar si estamos en Windows o Linux
        $es_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        
        if ($es_windows) {
            // Rutas comunes de OpenSSL en Windows
            $rutas_posibles = [
                'C:\\xampp\\apache\\bin\\openssl.exe',
                'C:\\xampp\\bin\\openssl.exe',
                'C:\\Program Files\\OpenSSL-Win64\\bin\\openssl.exe',
                'C:\\Program Files\\OpenSSL-Win32\\bin\\openssl.exe',
                'openssl.exe' // Intentar en PATH
            ];
            
            foreach ($rutas_posibles as $ruta) {
                if (file_exists($ruta)) {
                    return $ruta;
                }
            }
            
            // Verificar si está en PATH (Windows)
            if (shell_exec('where openssl.exe 2>nul')) {
                return 'openssl.exe';
            }
        } else {
            // Rutas comunes de OpenSSL en Linux/Unix
            $rutas_posibles = [
                '/usr/bin/openssl',
                '/usr/local/bin/openssl',
                '/bin/openssl',
                'openssl' // Intentar en PATH
            ];
            
            foreach ($rutas_posibles as $ruta) {
                if (file_exists($ruta) || $ruta === 'openssl') {
                    // Verificar si el comando funciona
                    $test = @shell_exec("$ruta version 2>&1");
                    if ($test && strpos($test, 'OpenSSL') !== false) {
                        return $ruta;
                    }
                }
            }
            
            // Verificar si está en PATH (Linux)
            $which_openssl = @shell_exec('which openssl 2>&1');
            if ($which_openssl && trim($which_openssl) !== '') {
                return trim($which_openssl);
            }
        }
        
        return false;
    }
    
    /**
     * Convertir clave privada a formato PEM si es necesario
     * @param string $clave_privada_data - Contenido de la clave privada
     * @param string $clave_privada_path - Ruta al archivo de clave privada
     * @param string $contrasena - Contraseña de la clave privada
     * @return string|false - Clave privada en formato PEM o false si falla
     */
    private function convertirClavePrivadaAPEM($clave_privada_data, $clave_privada_path, $contrasena) {
        // Verificar si ya está en formato PEM (tiene los encabezados)
        if (strpos($clave_privada_data, '-----BEGIN') !== false) {
            // Ya está en formato PEM, retornar tal cual
            return $clave_privada_data;
        }
        
        // Intentar cargar directamente (puede que sea PEM sin espacios)
        $pkey_test = @openssl_pkey_get_private($clave_privada_data, $contrasena);
        if ($pkey_test) {
            // Si se puede cargar directamente, está bien formateada
            openssl_pkey_free($pkey_test);
            return $clave_privada_data;
        }
        
        // Si no tiene formato PEM, intentar convertir desde DER/binario
        // Crear archivo temporal para procesar
        $temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('key_') . '.tmp';
        file_put_contents($temp_file, $clave_privada_data);
        
        // Intentar cargar directamente como archivo (OpenSSL puede detectar el formato)
        // Intentar sin contraseña primero
        $pkey_file = @openssl_pkey_get_private("file://$temp_file");
        if ($pkey_file) {
            openssl_pkey_export($pkey_file, $clave_pem_file);
            openssl_pkey_free($pkey_file);
            @unlink($temp_file);
            if ($clave_pem_file) {
                return $clave_pem_file;
            }
        }
        
        // Intentar con contraseña
        $pkey_pass = @openssl_pkey_get_private("file://$temp_file", $contrasena);
        if ($pkey_pass) {
            openssl_pkey_export($pkey_pass, $clave_pem_pass, $contrasena);
            openssl_pkey_free($pkey_pass);
            @unlink($temp_file);
            if ($clave_pem_pass) {
                return $clave_pem_pass;
            }
        }
        
        // Intentar convertir usando OpenSSL desde línea de comandos
        $openssl_exe = $this->obtenerRutaOpenSSL();
        
        if (!$openssl_exe) {
            @unlink($temp_file);
            return false;
        }
        
        // Escapar la contraseña para línea de comandos
        $pass_escaped = escapeshellarg($contrasena);
        $temp_file_escaped = escapeshellarg($temp_file);
        $temp_output = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('pem_') . '.pem';
        $temp_output_escaped = escapeshellarg($temp_output);
        
        // Intentar como PKCS#8 DER (formato común de FIEL mexicana)
        $cmd_pkcs8 = "$openssl_exe pkcs8 -inform DER -in $temp_file_escaped -out $temp_output_escaped -passin pass:$pass_escaped 2>&1";
        @exec($cmd_pkcs8, $output_pkcs8, $return_pkcs8);
        
        if ($return_pkcs8 === 0 && file_exists($temp_output) && filesize($temp_output) > 0) {
            $pem_content = file_get_contents($temp_output);
            @unlink($temp_output);
            @unlink($temp_file);
            if ($pem_content && strpos($pem_content, '-----BEGIN') !== false) {
                return $pem_content;
            }
        }
        
        // Intentar como RSA sin contraseña
        $cmd_rsa1 = "$openssl_exe rsa -in $temp_file_escaped -out $temp_output_escaped -outform PEM 2>&1";
        @exec($cmd_rsa1, $output_rsa1, $return_rsa1);
        
        if ($return_rsa1 === 0 && file_exists($temp_output) && filesize($temp_output) > 0) {
            $pem_content = file_get_contents($temp_output);
            @unlink($temp_output);
            @unlink($temp_file);
            if ($pem_content && strpos($pem_content, '-----BEGIN') !== false) {
                return $pem_content;
            }
        }
        
        // Intentar como RSA con contraseña
        $cmd_rsa2 = "$openssl_exe rsa -in $temp_file_escaped -out $temp_output_escaped -outform PEM -passin pass:$pass_escaped 2>&1";
        @exec($cmd_rsa2, $output_rsa2, $return_rsa2);
        
        if ($return_rsa2 === 0 && file_exists($temp_output) && filesize($temp_output) > 0) {
            $pem_content = file_get_contents($temp_output);
            @unlink($temp_output);
            @unlink($temp_file);
            if ($pem_content && strpos($pem_content, '-----BEGIN') !== false) {
                return $pem_content;
            }
        }
        
        @unlink($temp_output);
        
        // Intentar como PKCS#12 (puede que sea formato .p12)
        $cmd_p12 = "$openssl_exe pkcs12 -in $temp_file_escaped -nocerts -nodes -passin pass:$pass_escaped 2>&1";
        @exec($cmd_p12, $output_p12, $return_p12);
        
        if ($return_p12 === 0 && !empty($output_p12)) {
            $pem_content_p12 = implode("\n", $output_p12);
            // Extraer solo la parte de la clave privada
            if (preg_match('/-----BEGIN PRIVATE KEY-----.*?-----END PRIVATE KEY-----/s', $pem_content_p12, $matches)) {
                @unlink($temp_file);
                return $matches[0];
            }
            if (preg_match('/-----BEGIN RSA PRIVATE KEY-----.*?-----END RSA PRIVATE KEY-----/s', $pem_content_p12, $matches)) {
                @unlink($temp_file);
                return $matches[0];
            }
        }
        
        // Limpiar archivo temporal
        @unlink($temp_file);
        
        // Si todo falla, el archivo puede estar en formato binario DER
        // Intentar como último recurso decodificar base64 si está codificado
        if (strlen($clave_privada_data) > 100) {
            // Verificar si es base64 válido
            $decoded = base64_decode($clave_privada_data, true);
            if ($decoded !== false && $decoded !== $clave_privada_data) {
                // Parece ser binario codificado en base64
                $temp_file2 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('key2_') . '.tmp';
                file_put_contents($temp_file2, $decoded);
                
                $pkey_final = @openssl_pkey_get_private("file://$temp_file2", $contrasena);
                if ($pkey_final) {
                    openssl_pkey_export($pkey_final, $clave_pem_final, $contrasena);
                    openssl_pkey_free($pkey_final);
                    @unlink($temp_file2);
                    if ($clave_pem_final) {
                        return $clave_pem_final;
                    }
                }
                @unlink($temp_file2);
            }
        }
        
        return false;
    }
    
    /**
     * Generar firma digital real en formato Base64
     * @param string $texto_a_firmar - El texto que se va a firmar
     * @param string $certificado_path - Ruta al archivo .cer
     * @param string $clave_privada_path - Ruta al archivo .key
     * @param string $contrasena - Contraseña del certificado
     * @return array - Resultado con la firma en Base64
     */
    public function generarFirmaDigitalReal($texto_a_firmar, $certificado_path, $clave_privada_path, $contrasena) {
        try {
            // Verificar que los archivos existan
            if (!file_exists($certificado_path)) {
                return ['success' => false, 'error' => 'Certificado .cer no encontrado'];
            }
            if (!file_exists($clave_privada_path)) {
                return ['success' => false, 'error' => 'Clave privada .key no encontrada'];
            }
            
            // Leer el certificado
            $certificado_data = file_get_contents($certificado_path);
            if (!$certificado_data) {
                return ['success' => false, 'error' => 'No se pudo leer el certificado'];
            }
            
            // Leer la clave privada
            $clave_privada_data = file_get_contents($clave_privada_path);
            if (!$clave_privada_data) {
                return ['success' => false, 'error' => 'No se pudo leer la clave privada'];
            }
            
            // Verificar si la clave privada está en formato PEM
            // Si no tiene los encabezados PEM, intentar convertirla
            $clave_privada_pem = $this->convertirClavePrivadaAPEM($clave_privada_data, $clave_privada_path, $contrasena);
            if (!$clave_privada_pem) {
                $openssl_ruta = $this->obtenerRutaOpenSSL();
                $error_detalle = '';
                
                if (!$openssl_ruta) {
                    $error_detalle = ' No se encontró OpenSSL en el sistema. Verifica que XAMPP esté instalado correctamente.';
                } else {
                    $error_detalle = ' Posibles causas: 1) Contraseña incorrecta, 2) Archivo .key corrupto o en formato no soportado, 3) El archivo no es una clave privada válida de FIEL.';
                }
                
                return [
                    'success' => false, 
                    'error' => 'No se pudo convertir la clave privada a formato PEM.' . $error_detalle . ' Intenta convertir el archivo manualmente usando: C:\\xampp\\apache\\bin\\openssl.exe pkcs8 -inform DER -in "ruta\\archivo.key" -out "ruta\\archivo.pem" -passin pass:TU_CONTRASENA'
                ];
            }
            
            // Crear contexto de firma
            $pkey = openssl_pkey_get_private($clave_privada_pem, $contrasena);
            if (!$pkey) {
                $error_openssl = openssl_error_string();
                $error_completo = 'Error al cargar la clave privada: ' . ($error_openssl ?: 'Formato de clave inválido o contraseña incorrecta');
                return ['success' => false, 'error' => $error_completo];
            }
            
            // Generar hash del texto
            $hash = hash('sha256', $texto_a_firmar, true);
            
            // Firmar el hash
            $firma_binaria = '';
            $resultado_firma = openssl_sign($hash, $firma_binaria, $pkey, OPENSSL_ALGO_SHA256);
            
            if (!$resultado_firma) {
                return ['success' => false, 'error' => 'Error al firmar: ' . openssl_error_string()];
            }
            
            // Convertir firma a Base64
            $firma_base64 = base64_encode($firma_binaria);
            
            // Generar metadatos de la firma
            $metadatos = [
                'texto_original' => $texto_a_firmar,
                'hash_texto' => hash('sha256', $texto_a_firmar),
                'fecha_firma' => date('Y-m-d H:i:s'),
                'algoritmo' => 'SHA256',
                'certificado_info' => $this->obtenerInfoCertificado($certificado_data)
            ];
            
            // Liberar recursos
            openssl_pkey_free($pkey);
            
            return [
                'success' => true,
                'firma_base64' => $firma_base64,
                'metadatos' => $metadatos,
                'texto_firmado' => $texto_a_firmar
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Verificar firma digital real
     * @param string $texto_original - El texto original que se firmó
     * @param string $firma_base64 - La firma en formato Base64
     * @param string $certificado_path - Ruta al certificado .cer
     * @return array - Resultado de la verificación
     */
    public function verificarFirmaDigital($texto_original, $firma_base64, $certificado_path) {
        try {
            // Verificar que el certificado exista
            if (!file_exists($certificado_path)) {
                return ['success' => false, 'error' => 'Certificado .cer no encontrado'];
            }
            
            // Leer el certificado
            $certificado_data = file_get_contents($certificado_path);
            if (!$certificado_data) {
                return ['success' => false, 'error' => 'No se pudo leer el certificado'];
            }
            
            // Decodificar la firma desde Base64
            $firma_binaria = base64_decode($firma_base64);
            if (!$firma_binaria) {
                return ['success' => false, 'error' => 'Error al decodificar la firma Base64'];
            }
            
            // Generar hash del texto original
            $hash = hash('sha256', $texto_original, true);
            
            // Verificar la firma
            $resultado_verificacion = openssl_verify($hash, $firma_binaria, $certificado_data, OPENSSL_ALGO_SHA256);
            
            if ($resultado_verificacion === 1) {
                return [
                    'success' => true,
                    'valida' => true,
                    'mensaje' => 'Firma digital válida y auténtica',
                    'certificado_info' => $this->obtenerInfoCertificado($certificado_data)
                ];
            } elseif ($resultado_verificacion === 0) {
                return [
                    'success' => true,
                    'valida' => false,
                    'mensaje' => 'Firma digital inválida'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error en la verificación: ' . openssl_error_string()
                ];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener información del certificado
     * @param string $certificado_data - Datos del certificado
     * @return array - Información del certificado
     */
    private function obtenerInfoCertificado($certificado_data) {
        try {
            $cert_info = openssl_x509_parse($certificado_data);
            if (!$cert_info) {
                return ['error' => 'No se pudo parsear el certificado'];
            }
            
            return [
                'subject' => $cert_info['subject'] ?? [],
                'issuer' => $cert_info['issuer'] ?? [],
                'valid_from' => date('Y-m-d H:i:s', $cert_info['validFrom_time_t'] ?? 0),
                'valid_to' => date('Y-m-d H:i:s', $cert_info['validTo_time_t'] ?? 0),
                'serial_number' => $cert_info['serialNumberHex'] ?? '',
                'fingerprint' => $cert_info['extensions']['subjectKeyIdentifier'] ?? ''
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Generar texto estándar para firmar insignias
     * @param array $datos_insignia - Datos de la insignia
     * @return string - Texto formateado para firmar
     */
    public function generarTextoInsignia($datos_insignia) {
        $texto = "Certificado de Insignia Digital - TecNM\n";
        $texto .= "Alumno: " . ($datos_insignia['destinatario'] ?? 'N/A') . "\n";
        $texto .= "Insignia: " . ($datos_insignia['nombre_insignia'] ?? 'N/A') . "\n";
        $texto .= "Código: " . ($datos_insignia['codigo_insignia'] ?? 'N/A') . "\n";
        $texto .= "Fecha: " . ($datos_insignia['fecha_emision'] ?? date('d/m/Y')) . "\n";
        $texto .= "Responsable: " . ($datos_insignia['responsable'] ?? 'N/A') . "\n";
        $texto .= "Institución: Tecnológico Nacional de México\n";
        $texto .= "Sistema: Insignias Digitales TecNM v1.0";
        
        return $texto;
    }
    
    /**
     * Guardar firma digital en la base de datos
     * @param array $datos_firma - Datos de la firma
     * @return array - Resultado de la operación
     */
    public function guardarFirmaEnBD($datos_firma) {
        try {
            $sql = "INSERT INTO responsable_emision (
                nombre_responsable, 
                cargo_responsable, 
                firma_digital_base64, 
                certificado_path, 
                fecha_generacion, 
                activa
            ) VALUES (?, ?, ?, ?, ?, 1)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sssss", 
                $datos_firma['nombre_responsable'],
                $datos_firma['cargo_responsable'],
                $datos_firma['firma_base64'],
                $datos_firma['certificado_path'],
                $datos_firma['fecha_generacion']
            );
            
            if ($stmt->execute()) {
                $id_insertado = $this->conexion->insert_id;
                $stmt->close();
                return [
                    'success' => true,
                    'id_responsable' => $id_insertado,
                    'mensaje' => 'Firma digital guardada exitosamente'
                ];
            } else {
                $stmt->close();
                return ['success' => false, 'error' => 'Error al guardar en base de datos'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener firma digital de un responsable
     * @param int $responsable_id - ID del responsable
     * @return array - Datos de la firma
     */
    public function obtenerFirmaResponsable($responsable_id) {
        try {
            $sql = "SELECT * FROM responsable_emision WHERE id = ? AND activa = 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $responsable_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return [
                    'success' => true,
                    'firma' => $row
                ];
            } else {
                return ['success' => false, 'error' => 'No se encontró firma para el responsable'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Crear tabla responsable_emision si no existe
     */
    public function crearTablaResponsableEmision() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS responsable_emision (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre_responsable VARCHAR(255) NOT NULL,
                cargo_responsable VARCHAR(255) DEFAULT 'RESPONSABLE DE EMISIÓN',
                firma_digital_base64 LONGTEXT,
                certificado_path VARCHAR(500),
                fecha_generacion DATETIME NOT NULL,
                activa TINYINT(1) DEFAULT 1,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_nombre (nombre_responsable),
                INDEX idx_activa (activa)
            )";
            
            $this->conexion->query($sql);
            return true;
        } catch (Exception $e) {
            error_log("Error al crear tabla responsable_emision: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generar ejemplo de firma digital (para pruebas)
     * @return array - Ejemplo de firma Base64
     */
    public function generarEjemploFirma() {
        $texto_ejemplo = "Certificado de Insignia Digital - TecNM\n";
        $texto_ejemplo .= "Alumno: Jonathan Castro\n";
        $texto_ejemplo .= "Insignia: Desarrollador Destacado\n";
        $texto_ejemplo .= "Fecha: 22/10/2025\n";
        
        // Generar firma simulada (en producción usar certificados reales)
        $hash = hash('sha256', $texto_ejemplo, true);
        $firma_simulada = hash('sha256', $hash . 'TECNM_SECRET_KEY_2025', true);
        $firma_base64 = base64_encode($firma_simulada);
        
        return [
            'success' => true,
            'texto_ejemplo' => $texto_ejemplo,
            'firma_base64_ejemplo' => $firma_base64,
            'hash_texto' => hash('sha256', $texto_ejemplo)
        ];
    }
}

// Función helper para inicializar el sistema
function inicializarFirmaDigitalReal($conexion) {
    $firma_digital = new FirmaDigitalReal($conexion);
    $firma_digital->crearTablaResponsableEmision();
    return $firma_digital;
}
?>
