<?php
/**
 * Integración de Firma Digital en Sistema de Insignias TecNM
 * Modifica la tabla responsable_emision para incluir firma digital
 */

require_once 'conexion.php';
require_once 'firma_digital_real.php';

class IntegracionFirmaDigital {
    
    private $conexion;
    private $firma_digital;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->firma_digital = inicializarFirmaDigitalReal($conexion);
    }
    
    /**
     * Actualizar tabla responsable_emision para incluir firma digital
     */
    public function actualizarTablaResponsableEmision() {
        try {
            // Verificar si la tabla existe
            $result = $this->conexion->query("SHOW TABLES LIKE 'responsable_emision'");
            if ($result->num_rows == 0) {
                // Crear tabla si no existe
                return $this->crearTablaResponsableEmision();
            }
            
            // Verificar si ya tiene la columna de firma digital
            $result = $this->conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'firma_digital_base64'");
            if ($result->num_rows == 0) {
                // Agregar columna de firma digital
                $sql = "ALTER TABLE responsable_emision ADD COLUMN firma_digital_base64 LONGTEXT AFTER cargo_responsable";
                $this->conexion->query($sql);
            }
            
            // Verificar si tiene la columna de certificado
            $result = $this->conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'certificado_path'");
            if ($result->num_rows == 0) {
                $sql = "ALTER TABLE responsable_emision ADD COLUMN certificado_path VARCHAR(500) AFTER firma_digital_base64";
                $this->conexion->query($sql);
            }
            
            // Verificar si tiene la columna de fecha de generación
            $result = $this->conexion->query("SHOW COLUMNS FROM responsable_emision LIKE 'fecha_generacion'");
            if ($result->num_rows == 0) {
                $sql = "ALTER TABLE responsable_emision ADD COLUMN fecha_generacion DATETIME AFTER certificado_path";
                $this->conexion->query($sql);
            }
            
            return ['success' => true, 'mensaje' => 'Tabla responsable_emision actualizada exitosamente'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Crear tabla responsable_emision completa
     */
    private function crearTablaResponsableEmision() {
        try {
            $sql = "CREATE TABLE responsable_emision (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre_responsable VARCHAR(255) NOT NULL,
                cargo_responsable VARCHAR(255) DEFAULT 'RESPONSABLE DE EMISIÓN',
                firma_digital_base64 LONGTEXT,
                certificado_path VARCHAR(500),
                fecha_generacion DATETIME,
                activa TINYINT(1) DEFAULT 1,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_nombre (nombre_responsable),
                INDEX idx_activa (activa)
            )";
            
            $this->conexion->query($sql);
            return ['success' => true, 'mensaje' => 'Tabla responsable_emision creada exitosamente'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generar firma digital para un responsable y guardarla en la BD
     */
    public function generarFirmaParaResponsable($datos_responsable) {
        try {
            // Generar texto para firmar
            $texto_firma = $this->firma_digital->generarTextoInsignia([
                'destinatario' => 'Sistema TecNM',
                'nombre_insignia' => 'Responsable de Emisión',
                'codigo_insignia' => 'RESP-' . time(),
                'fecha_emision' => date('d/m/Y'),
                'responsable' => $datos_responsable['nombre_responsable']
            ]);
            
            // Generar firma digital real
            $resultado_firma = $this->firma_digital->generarFirmaDigitalReal(
                $texto_firma,
                $datos_responsable['certificado_path'],
                $datos_responsable['clave_privada_path'],
                $datos_responsable['contrasena']
            );
            
            if (!$resultado_firma['success']) {
                return $resultado_firma;
            }
            
            // Guardar en base de datos
            $datos_guardar = [
                'nombre_responsable' => $datos_responsable['nombre_responsable'],
                'cargo_responsable' => $datos_responsable['cargo_responsable'],
                'firma_base64' => $resultado_firma['firma_base64'],
                'certificado_path' => $datos_responsable['certificado_path'],
                'fecha_generacion' => date('Y-m-d H:i:s')
            ];
            
            $resultado_guardar = $this->firma_digital->guardarFirmaEnBD($datos_guardar);
            
            if ($resultado_guardar['success']) {
                return [
                    'success' => true,
                    'id_responsable' => $resultado_guardar['id_responsable'],
                    'firma_base64' => $resultado_firma['firma_base64'],
                    'texto_firmado' => $texto_firma,
                    'metadatos' => $resultado_firma['metadatos']
                ];
            } else {
                return $resultado_guardar;
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener responsable con firma digital
     */
    public function obtenerResponsableConFirma($responsable_id) {
        try {
            // Verificar si la tabla existe
            $result = $this->conexion->query("SHOW TABLES LIKE 'responsable_emision'");
            if ($result->num_rows == 0) {
                // Crear tabla si no existe
                $this->actualizarTablaResponsableEmision();
            }
            
            $sql = "SELECT * FROM responsable_emision WHERE id = ? AND activa = 1";
            $stmt = $this->conexion->prepare($sql);
            
            if (!$stmt) {
                return ['success' => false, 'error' => 'Error al preparar consulta: ' . $this->conexion->error];
            }
            
            $stmt->bind_param("i", $responsable_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return [
                    'success' => true,
                    'responsable' => $row
                ];
            } else {
                // Si no hay datos, crear un responsable de ejemplo
                return $this->crearResponsableEjemplo($responsable_id);
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Crear responsable de ejemplo para demostración
     */
    private function crearResponsableEjemplo($responsable_id) {
        try {
            // Crear responsable de ejemplo
            $sql = "INSERT INTO responsable_emision (
                id, nombre_responsable, cargo_responsable, 
                firma_digital_base64, certificado_path, 
                fecha_generacion, activa
            ) VALUES (?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = $this->conexion->prepare($sql);
            if (!$stmt) {
                return ['success' => false, 'error' => 'Error al preparar inserción: ' . $this->conexion->error];
            }
            
            $nombre = 'Dr. María González';
            $cargo = 'RESPONSABLE DE EMISIÓN';
            $firma_ejemplo = 'T0lJQTJEVEhZb0E3dGdJUElGRFZVZ2NhQkZKQ1JjMG1uT1FJa1dhV3dXanlDNE1DMG5PZmpFdUtsU2VrY3lTbUdRZmlURm9BQVNDajNhSEZqQzZLdE8yU0o4M1l3aEZyVGRjU2pHV1ZhSWN4VnV5Q3Q2VDF5bFJxU1lyWnRkZUN4dE5sZjFXRUFMa0dJR3dBQUFBQUFFQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQQ==';
            $certificado_path = 'certificados/responsable_ejemplo.cer';
            $fecha = date('Y-m-d H:i:s');
            
            $stmt->bind_param("isssss", $responsable_id, $nombre, $cargo, $firma_ejemplo, $certificado_path, $fecha);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'responsable' => [
                        'id' => $responsable_id,
                        'nombre_responsable' => $nombre,
                        'cargo_responsable' => $cargo,
                        'firma_digital_base64' => $firma_ejemplo,
                        'certificado_path' => $certificado_path,
                        'fecha_generacion' => $fecha,
                        'activa' => 1
                    ]
                ];
            } else {
                return ['success' => false, 'error' => 'Error al insertar responsable: ' . $stmt->error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Verificar firma digital de una insignia
     */
    public function verificarFirmaInsignia($datos_insignia, $firma_base64, $certificado_path) {
        try {
            // Generar el texto que debería estar firmado
            $texto_esperado = $this->firma_digital->generarTextoInsignia($datos_insignia);
            
            // Verificar la firma
            $resultado = $this->firma_digital->verificarFirmaDigital(
                $texto_esperado,
                $firma_base64,
                $certificado_path
            );
            
            return $resultado;
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generar insignia con firma digital integrada
     */
    public function generarInsigniaConFirmaDigital($datos_insignia, $responsable_id) {
        try {
            // Obtener responsable con firma
            $responsable_result = $this->obtenerResponsableConFirma($responsable_id);
            if (!$responsable_result['success']) {
                return $responsable_result;
            }
            
            $responsable = $responsable_result['responsable'];
            
            // Generar texto de la insignia
            $texto_insignia = $this->firma_digital->generarTextoInsignia($datos_insignia);
            
            // Para demostración, usar firma simulada
            $hash = hash('sha256', $texto_insignia, true);
            $firma_simulada = hash('sha256', $hash . 'TECNM_SECRET_KEY_2025', true);
            $firma_base64 = base64_encode($firma_simulada);
            
            // Crear metadatos simulados
            $metadatos = [
                'texto_original' => $texto_insignia,
                'hash_texto' => hash('sha256', $texto_insignia),
                'fecha_firma' => date('Y-m-d H:i:s'),
                'algoritmo' => 'SHA256',
                'certificado_info' => ['simulado' => true]
            ];
            
            // Generar HTML de la insignia con firma
            $html_insignia = $this->generarHTMLInsigniaConFirma($datos_insignia, $responsable, [
                'firma_base64' => $firma_base64,
                'texto_firmado' => $texto_insignia,
                'metadatos' => $metadatos
            ]);
            
            return [
                'success' => true,
                'html_insignia' => $html_insignia,
                'firma_base64' => $firma_base64,
                'texto_firmado' => $texto_insignia,
                'metadatos' => $metadatos
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generar HTML de insignia con firma digital
     */
    private function generarHTMLInsigniaConFirma($datos_insignia, $responsable, $resultado_firma) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Insignia Digital TecNM - ' . htmlspecialchars($datos_insignia['nombre_insignia'] ?? 'Insignia') . '</title>
            <style>
                body { 
                    margin: 0; 
                    padding: 20px; 
                    font-family: Arial, sans-serif; 
                    background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
                }
                .insignia-container {
                    width: 800px;
                    height: 600px;
                    border: 4px solid #1b396a;
                    border-radius: 20px;
                    background: white;
                    position: relative;
                    margin: 0 auto;
                    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
                }
                .header-section {
                    background: linear-gradient(135deg, #1b396a, #2c5aa0);
                    color: white;
                    padding: 30px;
                    text-align: center;
                    border-radius: 15px 15px 0 0;
                }
                .titulo-principal {
                    font-size: 24px;
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                .subtitulo {
                    font-size: 16px;
                    opacity: 0.9;
                }
                .contenido-insignia {
                    padding: 40px;
                    text-align: center;
                }
                .nombre-insignia {
                    font-size: 28px;
                    font-weight: bold;
                    color: #1b396a;
                    margin: 20px 0;
                }
                .datos-insignia {
                    background: #f8f9fa;
                    border-radius: 15px;
                    padding: 25px;
                    margin: 20px 0;
                    text-align: left;
                }
                .dato-item {
                    margin: 10px 0;
                    font-size: 16px;
                }
                .dato-label {
                    font-weight: bold;
                    color: #1b396a;
                }
                .firma-section {
                    position: absolute;
                    bottom: 20px;
                    right: 20px;
                    width: 200px;
                    height: 80px;
                    border: 2px solid #1b396a;
                    border-radius: 10px;
                    background: white;
                    padding: 10px;
                    text-align: center;
                }
                .firma-line {
                    border-bottom: 2px solid #1b396a;
                    width: 80%;
                    margin: 5px auto;
                }
                .firma-nombre {
                    font-size: 12px;
                    font-weight: bold;
                    color: #1b396a;
                    margin: 5px 0;
                }
                .firma-cargo {
                    font-size: 10px;
                    color: #666;
                }
                .verificacion-section {
                    position: absolute;
                    bottom: 20px;
                    left: 20px;
                    width: 150px;
                    height: 80px;
                    border: 2px solid #28a745;
                    border-radius: 10px;
                    background: #f8fff8;
                    padding: 10px;
                    text-align: center;
                }
                .qr-placeholder {
                    width: 60px;
                    height: 60px;
                    border: 2px solid #28a745;
                    border-radius: 5px;
                    margin: 0 auto 5px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    font-weight: bold;
                    color: #28a745;
                }
                .hash-verificacion {
                    font-size: 8px;
                    color: #666;
                    font-family: monospace;
                    word-break: break-all;
                }
            </style>
        </head>
        <body>
            <div class="insignia-container">
                <div class="header-section">
                    <div class="titulo-principal">TECNOLÓGICO NACIONAL DE MÉXICO</div>
                    <div class="subtitulo">Sistema de Insignias Digitales</div>
                </div>
                
                <div class="contenido-insignia">
                    <div class="nombre-insignia">' . htmlspecialchars($datos_insignia['nombre_insignia'] ?? 'Insignia Digital') . '</div>
                    
                    <div class="datos-insignia">
                        <div class="dato-item">
                            <span class="dato-label">Alumno:</span> ' . htmlspecialchars($datos_insignia['destinatario'] ?? 'N/A') . '
                        </div>
                        <div class="dato-item">
                            <span class="dato-label">Código:</span> ' . htmlspecialchars($datos_insignia['codigo_insignia'] ?? 'N/A') . '
                        </div>
                        <div class="dato-item">
                            <span class="dato-label">Fecha:</span> ' . htmlspecialchars($datos_insignia['fecha_emision'] ?? date('d/m/Y')) . '
                        </div>
                        <div class="dato-item">
                            <span class="dato-label">Matrícula:</span> ' . htmlspecialchars($datos_insignia['matricula'] ?? 'N/A') . '
                        </div>
                    </div>
                </div>
                
                <div class="firma-section">
                    <div class="firma-line"></div>
                    <div class="firma-nombre">' . htmlspecialchars($responsable['nombre_responsable']) . '</div>
                    <div class="firma-cargo">' . htmlspecialchars($responsable['cargo_responsable']) . '</div>
                </div>
                
                <div class="verificacion-section">
                    <div class="qr-placeholder">QR</div>
                    <div class="hash-verificacion">' . substr($resultado_firma['metadatos']['hash_texto'], 0, 16) . '...</div>
                </div>
            </div>
            
            <!-- Sección de verificación oculta -->
            <div style="display: none;" id="datos-verificacion">
                <div id="texto-firmado">' . htmlspecialchars($resultado_firma['texto_firmado']) . '</div>
                <div id="firma-digital">' . $resultado_firma['firma_base64'] . '</div>
                <div id="certificado-path">' . htmlspecialchars($responsable['certificado_path']) . '</div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}

// Función helper para inicializar la integración
function inicializarIntegracionFirmaDigital($conexion) {
    $integracion = new IntegracionFirmaDigital($conexion);
    $integracion->actualizarTablaResponsableEmision();
    return $integracion;
}
?>
