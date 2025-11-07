<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=sesion_invalida');
    exit();
}

// Verificar permisos (solo Admin y SuperUsuario pueden registrar reconocimientos)
if ($_SESSION['rol'] !== 'Admin' && $_SESSION['rol'] !== 'SuperUsuario') {
    header('Location: login.php?error=acceso_denegado');
    exit();
}

require_once 'conexion.php';
require_once 'funciones_correo_real.php';
$conexion->select_db("insignia");

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_insignia = $_POST['tipo_insignia'] ?? '';
    $destinatario_id = $_POST['destinatario_id'] ?? '';
    $programa = $_POST['programa'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $criterios = $_POST['criterios'] ?? '';
    $evidencia = $_POST['evidencia'] ?? '';
    $periodo_id = $_POST['periodo_id'] ?? '';
    
    if (!empty($tipo_insignia) && !empty($destinatario_id) && !empty($descripcion) && !empty($criterios)) {
        try {
        // Insertar en T_insignias (tabla original)
        $stmt = $conexion->prepare("
            INSERT INTO T_insignias (Tipo_Insignia, Propone_Insignia, Programa, Descripcion, Criterio, 
                                   Fecha_Creacion, Fecha_Autorizacion, Nombre_gen_ins, Estatus, Archivo_Visual)
            VALUES (?, ?, ?, ?, ?, CURDATE(), CURDATE(), ?, 1, ?)
        ");
        
        $archivo_visual = "Insig_TecNM-ITSM-20251-" . rand(100, 999) . ".jpg";
        $nombre_generador = $_SESSION['nombre'] . ' ' . $_SESSION['apellido_paterno'];
        
        $stmt->bind_param("iisssss", $tipo_insignia, $_SESSION['It_Centro_Id'], $programa, 
                             $descripcion, $criterios, $nombre_generador, $archivo_visual);
            
            if ($stmt->execute()) {
                $insignia_id = $conexion->insert_id;
                
                // Insertar en insigniasotorgadas (tabla correcta)
                $stmt2 = $conexion->prepare("
                    INSERT INTO insigniasotorgadas (Codigo_Insignia, Destinatario, Responsable_Emision, Periodo_Emision, Estatus, Fecha_Emision, Fecha_Vencimiento, Fecha_Creacion) 
                    VALUES (?, ?, ?, ?, ?, CURDATE(), CURDATE(), CURDATE())
                ");
                
                // Generar código de insignia único
                $codigo_insignia = 'TECNM-ITSM-' . date('Y') . '-' . rand(100, 999) . '-' . strtoupper(substr($tipo_insignia, 0, 3));
                $responsable_emision_id = 2; // ID de responsable existente
                $estatus_id = 5; // ID de estatus activo
                
                $stmt2->bind_param("siiii", $codigo_insignia, $destinatario_id, $responsable_emision_id, $periodo_id, $estatus_id);
                
                if ($stmt2->execute()) {
                    $otorgada_id = $conexion->insert_id;
                    
                    // Obtener datos del destinatario para enviar correo
                    $stmt_destinatario = $conexion->prepare("SELECT Nombre_Completo, Correo, Matricula, Curp FROM destinatario WHERE ID_destinatario = ?");
                    $stmt_destinatario->bind_param("i", $destinatario_id);
                    $stmt_destinatario->execute();
                    $result_destinatario = $stmt_destinatario->get_result();
                    $destinatario_data = $result_destinatario->fetch_assoc();
                    $stmt_destinatario->close();
                    
                    // Obtener nombre de la insignia
                    $stmt_tipo = $conexion->prepare("SELECT Nombre_insignia FROM tipo_insignia WHERE id = ?");
                    $stmt_tipo->bind_param("i", $tipo_insignia);
                    $stmt_tipo->execute();
                    $result_tipo = $stmt_tipo->get_result();
                    $tipo_data = $result_tipo->fetch_assoc();
                    $nombre_insignia = $tipo_data['Nombre_insignia'] ?? 'Insignia TecNM';
                    $stmt_tipo->close();
                    
                    // Obtener nombre del período
                    $stmt_periodo = $conexion->prepare("SELECT Nombre_Periodo FROM periodo_emision WHERE id = ?");
                    $stmt_periodo->bind_param("i", $periodo_id);
                    $stmt_periodo->execute();
                    $result_periodo = $stmt_periodo->get_result();
                    $periodo_data = $result_periodo->fetch_assoc();
                    $nombre_periodo = $periodo_data['Nombre_Periodo'] ?? date('Y') . '-1';
                    $stmt_periodo->close();
                    
                    // Generar URL de verificación
                    $server_ip = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    if (empty($server_ip) || $server_ip === '::1') {
                        $server_ip = 'localhost';
                    }
                    $port = $_SERVER['SERVER_PORT'] ?? '80';
                    $base_url = "http://" . $server_ip . ($port != '80' ? ':' . $port : '');
                    $url_verificacion = $base_url . "/Insignias_TecNM_Funcional/validacion.php?insignia=" . urlencode($codigo_insignia);
                    
                    $mensaje_exito = "Reconocimiento registrado exitosamente. <a href='ver_metadatos_insignia.php?id=" . $otorgada_id . "' style='color: white; text-decoration: underline;'>Ver insignia completa</a>";
                    
                    // ENVIAR NOTIFICACIÓN POR CORREO si el destinatario tiene correo
                    if (!empty($destinatario_data['Correo']) && filter_var($destinatario_data['Correo'], FILTER_VALIDATE_EMAIL)) {
                        $datos_correo = [
                            'estudiante' => $destinatario_data['Nombre_Completo'] ?? 'Estudiante',
                            'matricula' => $destinatario_data['Matricula'] ?? 'No especificada',
                            'curp' => $destinatario_data['Curp'] ?? 'No especificada',
                            'nombre_insignia' => $nombre_insignia,
                            'categoria' => 'Formación Integral', // Puedes obtenerla de la BD si es necesario
                            'codigo_insignia' => $codigo_insignia,
                            'periodo' => $nombre_periodo,
                            'fecha_otorgamiento' => date('Y-m-d'),
                            'responsable' => $_SESSION['nombre'] . ' ' . $_SESSION['apellido_paterno'],
                            'descripcion' => $descripcion,
                            'url_verificacion' => $url_verificacion
                        ];
                        
                        $correo_enviado = enviarNotificacionInsigniaCompleta($destinatario_data['Correo'], $datos_correo);
                        
                        if ($correo_enviado) {
                            $mensaje_exito .= " | ✅ Notificación enviada por correo a: " . htmlspecialchars($destinatario_data['Correo']);
                        } else {
                            $mensaje_exito .= " | ⚠️ Error al enviar correo a: " . htmlspecialchars($destinatario_data['Correo']);
                        }
                    } else {
                        $mensaje_exito .= " | ⚠️ No se pudo enviar correo: el destinatario no tiene correo válido registrado";
                    }
                } else {
                    $mensaje_error = "Error al registrar el reconocimiento: " . $stmt2->error;
                }
            } else {
                $mensaje_error = "Error al crear la insignia: " . $stmt->error;
            }
        } catch (Exception $e) {
            $mensaje_error = "Error: " . $e->getMessage();
        }
    } else {
        $mensaje_error = "Por favor, completa todos los campos obligatorios";
    }
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
    <title>Registrar Reconocimiento - Insignias TecNM</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css_profesional.css">
    <style>
        /* Estilos específicos para registrar reconocimiento */
        
        /* Formulario de reconocimiento */
        .reconocimiento-form {
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.08) 0%, 
            rgba(255, 255, 255, 0.03) 100%);
          backdrop-filter: blur(30px);
          border-radius: 20px;
          padding: 40px;
          box-shadow: 
            0 15px 30px rgba(0,0,0,0.15),
            inset 0 1px 0 rgba(255,255,255,0.1);
          border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .reconocimiento-form h2 {
          font-size: 28px;
          font-weight: 800;
          color: rgba(255, 255, 255, 0.95);
          margin-bottom: 30px;
          text-align: center;
          text-shadow: 0 2px 4px rgba(0,0,0,0.3);
          border-bottom: 2px solid rgba(255, 255, 255, 0.2);
          padding-bottom: 15px;
        }
        
        .form-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
          gap: 25px;
        }
        
        .form-group.full-width {
          grid-column: 1 / -1;
        }
        
        .required {
          color: #ff6b6b;
        }
        
        /* Botones de acción */
        .action-buttons {
          display: flex;
          gap: 20px;
          margin-top: 40px;
          justify-content: center;
          flex-wrap: wrap;
        }
        
        .btn-reconocimiento {
          background: linear-gradient(135deg, 
            #1b396a 0%, 
            #3b82f6 25%, 
            #8b5cf6 50%, 
            #3b82f6 75%, 
            #1b396a 100%);
          color: white;
          border: none;
          padding: 18px 36px;
          border-radius: 16px;
          font-size: 18px;
          font-weight: 700;
          cursor: pointer;
          transition: var(--transition);
          text-transform: uppercase;
          letter-spacing: 1px;
          box-shadow: 
            0 20px 40px rgba(27, 57, 106, 0.4),
            inset 0 1px 0 rgba(255,255,255,0.2);
          border: 1px solid rgba(255,255,255,0.2);
          position: relative;
          overflow: hidden;
        }
        
        .btn-reconocimiento::before {
          content: '';
          position: absolute;
          top: 0;
          left: -100%;
          width: 100%;
          height: 100%;
          background: linear-gradient(90deg, 
            transparent, 
            rgba(255,255,255,0.3), 
            transparent);
          transition: left 0.6s;
        }
        
        .btn-reconocimiento:hover {
          transform: translateY(-3px) scale(1.02);
          box-shadow: 
            0 25px 50px rgba(27, 57, 106, 0.5),
            inset 0 1px 0 rgba(255,255,255,0.3);
        }
        
        .btn-reconocimiento:hover::before {
          left: 100%;
        }
        
        .btn-secondary {
          background: linear-gradient(135deg, #6c757d, #495057);
        }
        
        .btn-secondary:hover {
          box-shadow: 
            0 25px 50px rgba(108, 117, 125, 0.5),
            inset 0 1px 0 rgba(255,255,255,0.3);
        }
        
        /* Información adicional */
        .info-section {
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.06) 0%, 
            rgba(255, 255, 255, 0.02) 100%);
          backdrop-filter: blur(30px);
          border-radius: 16px;
          padding: 25px;
          margin-top: 30px;
          box-shadow: 
            0 10px 25px rgba(0,0,0,0.1),
            inset 0 1px 0 rgba(255,255,255,0.1);
          border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .info-section h3 {
          font-size: 20px;
          font-weight: 700;
          color: rgba(255, 255, 255, 0.95);
          margin-bottom: 15px;
          text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .info-section p {
          font-size: 16px;
          color: rgba(255, 255, 255, 0.8);
          line-height: 1.6;
          margin-bottom: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
          .form-grid {
            grid-template-columns: 1fr;
            gap: 20px;
          }
          
          .reconocimiento-form {
            padding: 30px 20px;
          }
          
          .action-buttons {
            flex-direction: column;
            align-items: center;
          }
          
          .btn-reconocimiento {
            width: 100%;
            max-width: 300px;
          }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .content {
            padding: 40px;
        }

        .form-section {
            background: var(--bg-light);
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
        }

        .form-section h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            background: var(--bg-white);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 40, 85, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .btn {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: var(--bg-white);
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-transform: none;
            letter-spacing: 0px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
        }

        .alert {
            padding: 15px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            font-weight: 500;
        }

        .alert-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .alert-error {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .nav-link:hover {
            color: var(--secondary-color);
        }

        .user-info {
            background: var(--bg-light);
            padding: 15px 20px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .content {
                padding: 20px;
            }
            
            .header {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>SISTEMA DE INSIGNIAS TECNM</h1>
    </header>
    
    <div class="main-container">
        <div class="card">
            <div class="card-title">🏆 Registrar Reconocimiento</div>
            
            <a href="modulo_de_administracion.php" class="btn btn-secondary" style="margin-bottom: 30px;">
                <i class="fas fa-arrow-left"></i>
                Volver al Módulo
            </a>
            
            <?php if (isset($mensaje_exito)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $mensaje_exito; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($mensaje_error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $mensaje_error; ?>
                </div>
            <?php endif; ?>

            <div class="reconocimiento-form">
                <h2><i class="fas fa-plus-circle"></i> Nuevo Reconocimiento</h2>
                
                <form method="POST" action="" class="form-grid">
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
                                    <option value="<?php echo $destinatario['id']; ?>">
                                        <?php echo $destinatario['Nombre_Completo']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="programa">Programa Académico</label>
                            <input type="text" name="programa" id="programa" placeholder="Ej: Ingeniería en Sistemas Computacionales">
                        </div>

                        <div class="form-group">
                            <label for="periodo_id">Periodo de Emisión *</label>
                            <select name="periodo_id" id="periodo_id" required>
                                <option value="">Selecciona un periodo</option>
                                <?php while ($periodo = $periodos->fetch_assoc()): ?>
                                    <option value="<?php echo $periodo['id']; ?>">
                                        <?php echo $periodo['periodo']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="descripcion">Descripción de la Insignia *</label>
                            <textarea name="descripcion" id="descripcion" required 
                                      placeholder="Describe el propósito y significado de la insignia..."></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="criterios">Criterios para su Emisión *</label>
                            <textarea name="criterios" id="criterios" required 
                                      placeholder="Especifica los requisitos y criterios que debe cumplir el destinatario..."></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="evidencia">Evidencia</label>
                            <textarea name="evidencia" id="evidencia" 
                                      placeholder="Folios de certificación, documentos de respaldo, etc..."></textarea>
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i>
                            Registrar Reconocimiento
                        </button>
                        
                        <a href="modulo_de_administracion.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const camposObligatorios = ['tipo_insignia', 'destinatario_id', 'descripcion', 'criterios', 'periodo_id'];
            let valido = true;
            
            camposObligatorios.forEach(campo => {
                const input = document.getElementById(campo);
                if (!input.value.trim()) {
                    input.style.borderColor = '#dc3545';
                    valido = false;
                } else {
                    input.style.borderColor = '#e0e0e0';
                }
            });
            
            if (!valido) {
                e.preventDefault();
                alert('Por favor, completa todos los campos obligatorios marcados con *');
            }
        });
    </script>
    
    <footer>
        <p>Copyright 2025 - TecNM<br>
        Última actualización - Octubre 2025</p>
    </footer>
</body>
</html>
