<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=sesion_invalida');
    exit();
}

require_once 'conexion.php';
$conexion->select_db("insignia");

$mensaje_exito = '';
$mensaje_error = '';

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>✅ FORMULARIO RECIBIDO COMO POST</h2>";
    
    $tipo_insignia = $_POST['tipo_insignia'] ?? '';
    $destinatario_id = $_POST['destinatario_id'] ?? '';
    $programa = $_POST['programa'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $criterios = $_POST['criterios'] ?? '';
    $evidencia = $_POST['evidencia'] ?? '';
    $periodo_id = $_POST['periodo_id'] ?? '';
    
    echo "<p><strong>Datos recibidos:</strong></p>";
    echo "<ul>";
    echo "<li>Tipo insignia: $tipo_insignia</li>";
    echo "<li>Destinatario ID: $destinatario_id</li>";
    echo "<li>Programa: $programa</li>";
    echo "<li>Descripción: $descripcion</li>";
    echo "<li>Criterios: $criterios</li>";
    echo "<li>Período ID: $periodo_id</li>";
    echo "</ul>";
    
    if (!empty($tipo_insignia) && !empty($destinatario_id) && !empty($descripcion) && !empty($criterios)) {
        try {
            // Insertar en insignias
            $stmt = $conexion->prepare("
                INSERT INTO insignias (Tipo_Insignia, Propone_Insignia, Programa, Descripcion, Criterio, 
                                       Fecha_Creacion, Fecha_Autorizacion, Nombre_gen_ins, Estatus, Archivo_Visual)
                VALUES (?, ?, ?, ?, ?, CURDATE(), CURDATE(), ?, 1, ?)
            ");
            
            $archivo_visual = "Insig_TecNM-ITSM-20251-" . rand(100, 999) . ".jpg";
            $nombre_generador = 'Administrador';
            
            $stmt->bind_param("iisssss", $tipo_insignia, 1, $programa, 
                             $descripcion, $criterios, $nombre_generador, $archivo_visual);
            
            if ($stmt->execute()) {
                $insignia_id = $conexion->insert_id;
                echo "<p>✅ Insignia creada con ID: $insignia_id</p>";
                
                // Insertar en insigniasotorgadas
                $stmt2 = $conexion->prepare("
                    INSERT INTO insigniasotorgadas (Codigo_Insignia, Destinatario, Responsable_Emision, Periodo_Emision, Estatus, Fecha_Emision, Fecha_Vencimiento, Fecha_Creacion) 
                    VALUES (?, ?, ?, ?, ?, CURDATE(), CURDATE(), CURDATE())
                ");
                
                $codigo_insignia = 'TECNM-ITSM-' . date('Y') . '-' . rand(100, 999) . '-PRU';
                $responsable_emision_id = 2;
                $estatus_id = 5;
                
                $stmt2->bind_param("siiii", $codigo_insignia, $destinatario_id, $responsable_emision_id, $periodo_id, $estatus_id);
                
                if ($stmt2->execute()) {
                    $otorgada_id = $conexion->insert_id;
                    echo "<p>✅ Insignia otorgada creada con ID: $otorgada_id</p>";
                    echo "<p><strong>Código generado:</strong> $codigo_insignia</p>";
                    
                    echo "<h3>🎯 REDIRIGIENDO A LA INSIGNIA COMPLETA...</h3>";
                    echo "<p>En 3 segundos serás redirigido a: ver_insignia_completa.php?insignia=$codigo_insignia</p>";
                    
                    // Redirigir después de 3 segundos para que puedas ver el mensaje
                    echo "<script>setTimeout(function() { window.location.href = 'ver_insignia_completa.php?insignia=" . urlencode($codigo_insignia) . "'; }, 3000);</script>";
                    
                } else {
                    echo "<p>❌ Error al insertar en insigniasotorgadas: " . $stmt2->error . "</p>";
                }
            } else {
                echo "<p>❌ Error al insertar en insignias: " . $stmt->error . "</p>";
            }
            
        } catch (Exception $e) {
            echo "<p>❌ Excepción: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ Faltan campos obligatorios</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='registrar_reconocimiento_simple.php'>← Probar de nuevo</a></p>";
    exit();
}

// Obtener datos para los selectores
$tipos_insignia = $conexion->query("SELECT * FROM tipo_insignia ORDER BY Nombre_insignia");
$destinatarios = $conexion->query("SELECT * FROM destinatario ORDER BY Nombre_Completo");
$periodos = $conexion->query("SELECT * FROM periodo_emision ORDER BY periodo");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Reconocimiento - Prueba Simple</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background: #007bff;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        .debug-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>🧪 Registrar Reconocimiento - Prueba Simple</h1>
        
        <div class="debug-info">
            <h3>📋 Información de Debug:</h3>
            <p><strong>Método actual:</strong> <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
            <p><strong>Usuario ID:</strong> <?php echo $_SESSION['usuario_id']; ?></p>
            <p><strong>Rol:</strong> <?php echo $_SESSION['rol']; ?></p>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="tipo_insignia">Tipo de Insignia *</label>
                <select name="tipo_insignia" id="tipo_insignia" required>
                    <option value="">Selecciona un tipo</option>
                    <?php while ($tipo = $tipos_insignia->fetch_assoc()): ?>
                        <option value="<?php echo $tipo['id']; ?>">
                            <?php echo $tipo['Nombre_insignia']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="destinatario_id">Destinatario *</label>
                <select name="destinatario_id" id="destinatario_id" required>
                    <option value="">Selecciona un destinatario</option>
                    <?php while ($destinatario = $destinatarios->fetch_assoc()): ?>
                        <option value="<?php echo $destinatario['ID_destinatario']; ?>">
                            <?php echo $destinatario['Nombre_Completo']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="programa">Programa Académico</label>
                <input type="text" name="programa" id="programa" value="Ingeniería en Sistemas Computacionales">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción *</label>
                <textarea name="descripcion" id="descripcion" required>Reconocimiento por méritos académicos destacados</textarea>
            </div>

            <div class="form-group">
                <label for="criterios">Criterios *</label>
                <textarea name="criterios" id="criterios" required>Promedio superior a 9.0 y participación activa</textarea>
            </div>

            <div class="form-group">
                <label for="evidencia">Evidencia</label>
                <input type="text" name="evidencia" id="evidencia" value="Certificado de excelencia académica">
            </div>

            <div class="form-group">
                <label for="periodo_id">Período *</label>
                <select name="periodo_id" id="periodo_id" required>
                    <option value="">Selecciona un período</option>
                    <?php while ($periodo = $periodos->fetch_assoc()): ?>
                        <option value="<?php echo $periodo['ID_periodo']; ?>">
                            <?php echo $periodo['periodo']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit">
                🚀 Registrar Reconocimiento
            </button>
        </form>
        
        <hr>
        <p><a href="modulo_de_administracion.php">← Volver al Módulo de Administración</a></p>
    </div>
</body>
</html>
