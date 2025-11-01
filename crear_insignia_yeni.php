<?php
/**
 * CREAR INSIGNIA DE RESPONSABILIDAD SOCIAL PARA YENI CASTRO SÁNCHEZ
 * Este archivo crea una insignia específica para probar el sistema de correos
 */

session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo "<h2>❌ Error: Sesión no válida</h2>";
    echo "<p>Debes iniciar sesión primero.</p>";
    echo "<p><a href='login.php'>Iniciar Sesión</a></p>";
    exit();
}

// Incluir conexión a la base de datos y funciones de correo
require_once 'conexion.php';
require_once 'funciones_correo_real.php';

echo "<h2>🎖️ CREAR INSIGNIA DE RESPONSABILIDAD SOCIAL</h2>";
echo "<h3>👤 Estudiante: Yeni Castro Sánchez</h3>";

// Datos específicos para la insignia de Yeni Castro Sánchez
$datos_insignia = [
    'estudiante' => 'Yeni Castro Sánchez',
    'matricula' => '211230002',
    'curp' => 'CASY950315MDFRCN01',
    'correo' => '211230001@smarcos.tecnm.mx', // Usando tu correo para la prueba
    'nombre_insignia' => 'Responsabilidad Social',
    'categoria' => 'Formación Integral',
    'codigo_insignia' => 'TECNM-ITSM-2025-RS-' . date('His'), // Código único con timestamp
    'periodo' => '2025-2',
    'fecha_otorgamiento' => date('Y-m-d'),
    'responsable' => 'Dr. María González - Directora Académica',
    'descripcion' => 'Reconocimiento por su destacada participación en actividades de responsabilidad social, demostrando un compromiso excepcional con el bienestar de la comunidad y el desarrollo sostenible. Su liderazgo en proyectos sociales, voluntariado comunitario y promoción de valores éticos ha contribuido significativamente al fortalecimiento del tejido social y al cumplimiento de la misión institucional del Tecnológico Nacional de México.'
];

echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>📋 Datos de la Insignia:</h4>";
echo "<p><strong>Estudiante:</strong> " . htmlspecialchars($datos_insignia['estudiante']) . "</p>";
echo "<p><strong>Matrícula:</strong> " . htmlspecialchars($datos_insignia['matricula']) . "</p>";
echo "<p><strong>CURP:</strong> " . htmlspecialchars($datos_insignia['curp']) . "</p>";
echo "<p><strong>Correo:</strong> " . htmlspecialchars($datos_insignia['correo']) . "</p>";
echo "<p><strong>Insignia:</strong> " . htmlspecialchars($datos_insignia['nombre_insignia']) . "</p>";
echo "<p><strong>Categoría:</strong> " . htmlspecialchars($datos_insignia['categoria']) . "</p>";
echo "<p><strong>Código:</strong> " . htmlspecialchars($datos_insignia['codigo_insignia']) . "</p>";
echo "<p><strong>Período:</strong> " . htmlspecialchars($datos_insignia['periodo']) . "</p>";
echo "<p><strong>Fecha:</strong> " . htmlspecialchars($datos_insignia['fecha_otorgamiento']) . "</p>";
echo "<p><strong>Responsable:</strong> " . htmlspecialchars($datos_insignia['responsable']) . "</p>";
echo "</div>";

// Generar URL de verificación
$datos_insignia['url_verificacion'] = generarUrlVerificacion($datos_insignia['codigo_insignia']);

echo "<h3>💾 Guardando en la base de datos...</h3>";

try {
    // 1. Crear o actualizar destinatario
    $destinatario_id = null;
    
    // Buscar por nombre completo
    $sql_buscar_nombre = "SELECT ID_destinatario FROM destinatario WHERE Nombre_Completo = ? LIMIT 1";
    $stmt_nombre = $conexion->prepare($sql_buscar_nombre);
    if ($stmt_nombre) {
        $stmt_nombre->bind_param("s", $datos_insignia['estudiante']);
        $stmt_nombre->execute();
        $result_nombre = $stmt_nombre->get_result();
        if ($result_nombre && $result_nombre->num_rows > 0) {
            $row_nombre = $result_nombre->fetch_assoc();
            $destinatario_id = $row_nombre['ID_destinatario'];
        }
        $stmt_nombre->close();
    }
    
    // Si existe el destinatario, actualizar datos
    if ($destinatario_id) {
        $sql_update = "UPDATE destinatario SET Nombre_Completo = ?, Curp = ?, Correo = ?, Matricula = ? WHERE ID_destinatario = ?";
        $stmt_update = $conexion->prepare($sql_update);
        if ($stmt_update) {
            $stmt_update->bind_param("ssssi", 
                $datos_insignia['estudiante'], 
                $datos_insignia['curp'], 
                $datos_insignia['correo'], 
                $datos_insignia['matricula'], 
                $destinatario_id
            );
            $stmt_update->execute();
            $stmt_update->close();
        }
    } else {
        // Crear nuevo destinatario
        $sql_insert = "INSERT INTO destinatario (Nombre_Completo, Curp, Correo, Matricula, ITCentro) VALUES (?, ?, ?, ?, 1)";
        $stmt_insert = $conexion->prepare($sql_insert);
        if ($stmt_insert) {
            $stmt_insert->bind_param("ssss", 
                $datos_insignia['estudiante'], 
                $datos_insignia['curp'], 
                $datos_insignia['correo'], 
                $datos_insignia['matricula']
            );
            if ($stmt_insert->execute()) {
                $destinatario_id = $conexion->insert_id;
            } else {
                throw new Exception("No se pudo crear destinatario: " . $stmt_insert->error);
            }
            $stmt_insert->close();
        }
    }
    
    // 2. Obtener o crear periodo
    $sql_periodo_id = "SELECT ID_periodo FROM periodo_emision WHERE periodo = ? LIMIT 1";
    $stmt_periodo = $conexion->prepare($sql_periodo_id);
    $periodo_id = null;
    
    if ($stmt_periodo) {
        $stmt_periodo->bind_param("s", $datos_insignia['periodo']);
        $stmt_periodo->execute();
        $result_periodo = $stmt_periodo->get_result();
        
        if ($result_periodo && $result_periodo->num_rows > 0) {
            $row_periodo = $result_periodo->fetch_assoc();
            $periodo_id = $row_periodo['ID_periodo'];
        } else {
            // Crear periodo si no existe
            $sql_insert_periodo = "INSERT INTO periodo_emision (periodo) VALUES (?)";
            $stmt_insert_periodo = $conexion->prepare($sql_insert_periodo);
            if ($stmt_insert_periodo) {
                $stmt_insert_periodo->bind_param("s", $datos_insignia['periodo']);
                if ($stmt_insert_periodo->execute()) {
                    $periodo_id = $conexion->insert_id;
                } else {
                    throw new Exception("No se pudo crear periodo: " . $stmt_insert_periodo->error);
                }
                $stmt_insert_periodo->close();
            }
        }
        $stmt_periodo->close();
    }
    
    // 3. Obtener o crear responsable
    $sql_resp_emision = "SELECT ID_responsable FROM responsable_emision WHERE Nombre_Completo = ? LIMIT 1";
    $stmt_resp = $conexion->prepare($sql_resp_emision);
    $responsable_id = null;
    
    if ($stmt_resp) {
        $stmt_resp->bind_param("s", $datos_insignia['responsable']);
        $stmt_resp->execute();
        $result_resp = $stmt_resp->get_result();
        
        if ($result_resp && $result_resp->num_rows > 0) {
            $row_resp = $result_resp->fetch_assoc();
            $responsable_id = $row_resp['ID_responsable'];
        } else {
            // Crear responsable si no existe
            $sql_insert_resp = "INSERT INTO responsable_emision (Nombre_Completo, Cargo) VALUES (?, 'Responsable')";
            $stmt_insert_resp = $conexion->prepare($sql_insert_resp);
            if ($stmt_insert_resp) {
                $stmt_insert_resp->bind_param("s", $datos_insignia['responsable']);
                if ($stmt_insert_resp->execute()) {
                    $responsable_id = $conexion->insert_id;
                } else {
                    throw new Exception("No se pudo crear responsable: " . $stmt_insert_resp->error);
                }
                $stmt_insert_resp->close();
            }
        }
        $stmt_resp->close();
    }
    
    // 4. Insertar en insigniasotorgadas
    $sql = "INSERT INTO insigniasotorgadas (
        Codigo_Insignia, 
        Destinatario, 
        Periodo_Emision, 
        Responsable_Emision,
        Estatus, 
        Fecha_Emision, 
        Fecha_Vencimiento
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $estatus_id = 2; // Autorizado
    $fecha_autorizacion = date('Y-m-d', strtotime('+1 year')); // Vence en 1 año
    
    $stmt->bind_param("siiiiss", 
        $datos_insignia['codigo_insignia'],
        $destinatario_id, 
        $periodo_id, 
        $responsable_id,
        $estatus_id, 
        $datos_insignia['fecha_otorgamiento'], 
        $fecha_autorizacion
    );
    
    if ($stmt->execute()) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>✅ Insignia guardada exitosamente</h4>";
        echo "<p><strong>ID:</strong> " . $conexion->insert_id . "</p>";
        echo "<p><strong>Código:</strong> " . htmlspecialchars($datos_insignia['codigo_insignia']) . "</p>";
        echo "</div>";
        
        $stmt->close();
        
        echo "<h3>📧 Enviando notificación por correo...</h3>";
        
        // Enviar correo de notificación
        $correo_enviado = enviarNotificacionInsigniaCompleta($datos_insignia['correo'], $datos_insignia);
        
        if ($correo_enviado) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4>🎉 ¡CORREO ENVIADO EXITOSAMENTE!</h4>";
            echo "<p><strong>✅ Notificación enviada a:</strong> " . htmlspecialchars($datos_insignia['correo']) . "</p>";
            echo "<p><strong>Asunto:</strong> 🎖️ Insignia Otorgada - " . htmlspecialchars($datos_insignia['nombre_insignia']) . "</p>";
            echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
            echo "</div>";
            
            echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4>📧 ¿Dónde revisar el correo?</h4>";
            echo "<p><strong>1. Bandeja de entrada:</strong> Revisa tu Outlook</p>";
            echo "<p><strong>2. Carpeta de spam:</strong> A veces va ahí</p>";
            echo "<p><strong>3. Busca:</strong> 🎖️ Insignia Otorgada - Responsabilidad Social</p>";
            echo "<p><strong>4. Estudiante:</strong> Yeni Castro Sánchez</p>";
            echo "</div>";
            
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4>⚠️ Error al enviar correo</h4>";
            echo "<p>El correo no se pudo enviar, pero se guardó en simulación.</p>";
            echo "<p>Revisa el archivo <strong>correos_enviados.txt</strong> para ver el correo generado.</p>";
            echo "</div>";
        }
        
        echo "<h3>🔍 Verificar Insignia:</h3>";
        echo "<p><a href='ver_insignia_completa.php?insignia=" . urlencode($datos_insignia['codigo_insignia']) . "' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>🔍 Ver Insignia Completa</a></p>";
        
        echo "<h3>📋 Resumen:</h3>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<p><strong>✅ Insignia creada:</strong> Responsabilidad Social</p>";
        echo "<p><strong>✅ Estudiante:</strong> Yeni Castro Sánchez</p>";
        echo "<p><strong>✅ Código:</strong> " . htmlspecialchars($datos_insignia['codigo_insignia']) . "</p>";
        echo "<p><strong>✅ Correo:</strong> " . ($correo_enviado ? "Enviado exitosamente" : "Guardado en simulación") . "</p>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>❌ Error al guardar en la base de datos</h4>";
        echo "<p><strong>Error:</strong> " . $stmt->error . "</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Error general</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔄 Acciones adicionales:</h3>";
echo "<p><a href='metadatos_formulario.php' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>📝 Crear más insignias</a></p>";
echo "<p><a href='probar_envio_real.php' style='display: inline-block; background: #17a2b8; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>🧪 Probar envío de correos</a></p>";
echo "<p><a href='prueba_simple.php' style='display: inline-block; background: #6c757d; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 5px;'>📧 Prueba simple de correo</a></p>";

echo "<hr>";
echo "<p><strong>Fecha de creación:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
