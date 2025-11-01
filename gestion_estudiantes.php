<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=sesion_invalida');
    exit();
}

// Verificar permisos (solo Admin y SuperUsuario pueden gestionar estudiantes)
if ($_SESSION['rol'] !== 'Admin' && $_SESSION['rol'] !== 'SuperUsuario') {
    header('Location: login.php?error=acceso_denegado');
    exit();
}

include("conexion.php");

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = $_POST['nombre_completo'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $curp = $_POST['curp'] ?? '';
    $matricula = $_POST['matricula'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $it_centro_id = $_POST['it_centro_id'] ?? '';
    
    if (!empty($nombre_completo) && !empty($matricula) && !empty($correo) && !empty($it_centro_id)) {
        try {
            $stmt = $conexion->prepare("INSERT INTO destinatario (nombre_completo, genero, curp, matricula, correo, telefono, it_centro_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $nombre_completo, $genero, $curp, $matricula, $correo, $telefono, $it_centro_id);
            
            if ($stmt->execute()) {
                $mensaje_exito = "✅ Estudiante registrado exitosamente";
            } else {
                $mensaje_error = "❌ Error al registrar el estudiante";
            }
            $stmt->close();
        } catch (Exception $e) {
            $mensaje_error = "❌ Error: " . $e->getMessage();
        }
    } else {
        $mensaje_error = "❌ Por favor completa todos los campos obligatorios";
    }
}

// Obtener lista de centros
$centros = [];
try {
    $stmt = $conexion->query("SELECT id, nombre_itc, acronimo FROM it_centros ORDER BY nombre_itc");
    if ($stmt && $stmt->num_rows > 0) {
        while($row = $stmt->fetch_assoc()) {
            $centros[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error al obtener centros: " . $e->getMessage());
}

// Obtener lista de estudiantes existentes
$estudiantes = [];
try {
    $stmt = $conexion->query("
        SELECT d.*, itc.nombre_itc, itc.acronimo 
        FROM destinatario d 
        LEFT JOIN it_centros itc ON d.it_centro_id = itc.id 
        ORDER BY d.nombre_completo
    ");
    if ($stmt && $stmt->num_rows > 0) {
        while($row = $stmt->fetch_assoc()) {
            $estudiantes[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error al obtener estudiantes: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Estudiantes - Insignias TecNM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css_profesional.css">
    <style>
        /* Estilos específicos para gestión de estudiantes */
        
        /* Formulario de registro */
        .form-section {
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.08) 0%, 
            rgba(255, 255, 255, 0.03) 100%);
          backdrop-filter: blur(30px);
          border-radius: 20px;
          padding: 40px;
          margin-bottom: 40px;
          box-shadow: 
            0 15px 30px rgba(0,0,0,0.15),
            inset 0 1px 0 rgba(255,255,255,0.1);
          border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-section h2 {
          font-size: 24px;
          font-weight: 800;
          color: rgba(255, 255, 255, 0.95);
          margin-bottom: 30px;
          text-shadow: 0 2px 4px rgba(0,0,0,0.3);
          border-bottom: 2px solid rgba(255, 255, 255, 0.2);
          padding-bottom: 15px;
        }
        
        .form-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 25px;
        }
        
        .form-group.full-width {
          grid-column: 1 / -1;
        }
        
        .required {
          color: #ff6b6b;
        }
        
        /* Tabla de estudiantes */
        .students-table {
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.06) 0%, 
            rgba(255, 255, 255, 0.02) 100%);
          backdrop-filter: blur(30px);
          border-radius: 20px;
          padding: 30px;
          box-shadow: 
            0 20px 40px rgba(0,0,0,0.15),
            inset 0 1px 0 rgba(255,255,255,0.1);
          border: 1px solid rgba(255, 255, 255, 0.08);
          overflow-x: auto;
        }
        
        .students-table h2 {
          font-size: 24px;
          font-weight: 800;
          color: rgba(255, 255, 255, 0.95);
          margin-bottom: 25px;
          text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        /* Botones de acción */
        .action-buttons {
          display: flex;
          gap: 15px;
          margin-top: 30px;
          justify-content: center;
        }
        
        .btn-back {
          background: linear-gradient(135deg, #6c757d, #495057);
          color: white;
          border: none;
          padding: 15px 30px;
          border-radius: 12px;
          font-size: 16px;
          font-weight: 600;
          cursor: pointer;
          transition: var(--transition);
          text-decoration: none;
          display: inline-flex;
          align-items: center;
          gap: 8px;
          box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
        }
        
        .btn-back:hover {
          transform: translateY(-2px) scale(1.05);
          box-shadow: 0 12px 30px rgba(108, 117, 125, 0.4);
          color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
          .form-grid {
            grid-template-columns: 1fr;
            gap: 20px;
          }
          
          .form-section {
            padding: 30px 20px;
          }
          
          .students-table {
            padding: 20px;
          }
          
          .action-buttons {
            flex-direction: column;
            align-items: center;
          }
        }
        
        /* ==== ENCABEZADO ==== */
        header {
            background: #1b396a;
            color: white;
            text-align: center;
            padding: 20px 0;
        }
        header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .content {
            padding: 40px;
        }
        
        .nav-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #002855;
            text-decoration: none;
            font-weight: bold;
        }
        
        .nav-link:hover {
            text-decoration: underline;
        }
        
        h1 {
            color: #002855;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .form-section h2 {
            color: #002855;
            margin-bottom: 20px;
            border-bottom: 2px solid #002855;
            padding-bottom: 10px;
        }
        
        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .required {
            color: #dc3545;
        }
        
        select, input[type="text"], input[type="email"], input[type="tel"] {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: #002855;
        }
        
        button {
            background: #002855;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            grid-column: 1 / -1;
            margin-top: 20px;
            transition: background 0.3s ease;
        }
        
        button:hover {
            background: #1b396a;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .students-section {
            margin-top: 40px;
        }
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .students-table th,
        .students-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .students-table th {
            background: #002855;
            color: white;
            font-weight: bold;
        }
        
        .students-table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        /* ==== PIE DE PÁGINA ==== */
        footer {
            font-size: 14px;
            margin-top: 40px;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #1b396a;
            color: white;
            text-align: center;
            padding: 10px 0;
            z-index: 10;
        }
        
        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr;
            }
            
            .content {
                padding: 20px;
            }
            
            .students-table {
                font-size: 12px;
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
            <div class="card-title">👨‍🎓 Gestión de Estudiantes</div>
            
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
            
            <!-- Formulario de Registro -->
            <div class="form-section">
                <h2><i class="fas fa-user-plus"></i> Registrar Nuevo Estudiante</h2>
                
                <form method="POST" class="form-grid">
                    <div class="form-group">
                        <label for="nombre_completo">Nombre Completo <span class="required">*</span></label>
                        <input type="text" id="nombre_completo" name="nombre_completo" required 
                               placeholder="Ej: José Alfredo Jiménez Fernández">
                    </div>
                    
                    <div class="form-group">
                        <label for="genero">Género</label>
                        <select id="genero" name="genero">
                            <option value="">Seleccionar género</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="curp">CURP</label>
                        <input type="text" id="curp" name="curp" maxlength="18" 
                               placeholder="18 caracteres">
                    </div>
                    
                    <div class="form-group">
                        <label for="matricula">Matrícula <span class="required">*</span></label>
                        <input type="text" id="matricula" name="matricula" required 
                               placeholder="Ej: 2025001234">
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Correo Electrónico <span class="required">*</span></label>
                        <input type="email" id="correo" name="correo" required 
                               placeholder="estudiante@tecnm.edu.mx">
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" 
                               placeholder="Ej: 555-123-4567">
                    </div>
                    
                    <div class="form-group">
                        <label for="it_centro_id">Instituto/Centro <span class="required">*</span></label>
                        <select id="it_centro_id" name="it_centro_id" required>
                            <option value="">Seleccionar instituto</option>
                            <?php foreach ($centros as $centro): ?>
                                <option value="<?php echo $centro['id']; ?>">
                                    <?php echo htmlspecialchars($centro['nombre_itc']); ?> 
                                    (<?php echo htmlspecialchars($centro['acronimo']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit">
                        <i class="fas fa-save"></i> Registrar Estudiante
                    </button>
                </form>
            </div>
            
            <!-- Lista de Estudiantes -->
            <div class="students-section">
                <h2><i class="fas fa-list"></i> Estudiantes Registrados (<?php echo count($estudiantes); ?>)</h2>
                
                <?php if (count($estudiantes) > 0): ?>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Matrícula</th>
                                <th>Correo</th>
                                <th>Instituto</th>
                                <th>Género</th>
                                <th>Teléfono</th>
                                <th>Fecha Registro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $estudiante): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($estudiante['nombre_completo']); ?></strong>
                                        <?php if ($estudiante['curp']): ?>
                                            <br><small class="badge badge-info">CURP: <?php echo htmlspecialchars($estudiante['curp']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-success"><?php echo htmlspecialchars($estudiante['matricula']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($estudiante['correo']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($estudiante['acronimo'] ?? 'N/A'); ?>
                                        <br><small><?php echo htmlspecialchars($estudiante['nombre_itc'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($estudiante['genero'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($estudiante['telefono'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                        $fecha = new DateTime($estudiante['fecha_creacion']);
                                        echo $fecha->format('d/m/Y');
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-user-graduate" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <h3>No hay estudiantes registrados</h3>
                        <p>Usa el formulario de arriba para registrar el primer estudiante.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <footer>
        <p>Copyright 2025 - TecNM<br>
        Última actualización - Octubre 2025</p>
    </footer>
</body>
</html>
